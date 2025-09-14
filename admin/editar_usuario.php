<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del usuario a editar
$documento_usuario = isset($_GET['id']) ? $_GET['id'] : null;

if (!$documento_usuario) {
    header("Location: gestion_usuarios.php?error=Usuario no especificado");
    exit();
}

// Obtener datos del usuario
$query = "SELECT u.*, r.Nombre_Rol as Rol 
          FROM Usuarios u 
          INNER JOIN Roles r ON u.Id_Rol = r.id_Rol 
          WHERE u.Documento = ?";
$stmt = $db->prepare($query);
$stmt->bindValue(1, $documento_usuario, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: gestion_usuarios.php?error=Usuario no encontrado");
    exit();
}

// Obtener roles para el select
$query_roles = "SELECT * FROM Roles";
$stmt_roles = $db->query($query_roles);
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Mensajes de éxito/error
$mensaje = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Admin</title>
    <link rel="stylesheet" href="../Styles/editar_usuario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
            
            <div class="col-md-9">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Editar Usuario</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="gestion_usuarios.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-success alert-dismissible">
                        <?php echo htmlspecialchars($mensaje); ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="procesar_editar_usuario.php" method="post">
                            <input type="hidden" name="documento_original" value="<?php echo $usuario['Documento']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Documento de Identidad *</label>
                                        <input type="number" class="form-control" name="documento" 
                                               value="<?php echo htmlspecialchars($usuario['Documento']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre Completo *</label>
                                        <input type="text" class="form-control" name="nombre_completo" 
                                               value="<?php echo htmlspecialchars($usuario['Nombre_Completo']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($usuario['Email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" name="telefono" 
                                               value="<?php echo htmlspecialchars($usuario['Telefono']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Rol *</label>
                                        <select class="form-select" name="id_rol" required>
                                            <?php foreach ($roles as $rol): ?>
                                                <option value="<?php echo $rol['id_Rol']; ?>" 
                                                    <?php echo $usuario['Id_Rol'] == $rol['id_Rol'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rol['Nombre_Rol']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estado *</label>
                                        <select class="form-select" name="estado" required>
                                            <option value="activo" <?php echo $usuario['Estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactivo" <?php echo $usuario['Estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña (dejar vacío para no cambiar)</label>
                                <input type="password" class="form-control" name="nueva_contrasena" 
                                       placeholder="Ingrese solo si desea cambiar la contraseña">
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Registro</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d/m/Y', strtotime($usuario['Fecha_Registro'])); ?>" 
                                               disabled>
                                        <input type="hidden" name="fecha_registro" value="<?php echo $usuario['Fecha_Registro']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="gestion_usuarios.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">Información del Usuario</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Rol Actual:</strong> <?php echo htmlspecialchars($usuario['Rol']); ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Estado:</strong> 
                                <span class="badge <?php echo $usuario['Estado'] == 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($usuario['Estado']); ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Registrado desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['Fecha_Registro'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad para cerrar alertas
        document.querySelectorAll('.btn-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>