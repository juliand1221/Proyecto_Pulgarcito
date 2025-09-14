<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener el Registro Civil del niño a editar
$registro_civil = isset($_GET['id']) ? $_GET['id'] : null;

if (!$registro_civil) {
    header("Location: gestion_ninos.php?error=Niño no especificado");
    exit();
}

// Obtener datos del niño
$query_nino = "SELECT n.*, u.Nombre_Completo as Nombre_Acudiente, u.Documento as Doc_Acudiente
               FROM Ninos n 
               INNER JOIN Usuarios u ON n.Documento = u.Documento 
               WHERE n.Registro_Civil = ?";
$stmt_nino = $db->prepare($query_nino);
$stmt_nino->bindValue(1, $registro_civil, PDO::PARAM_INT);
$stmt_nino->execute();
$nino = $stmt_nino->fetch(PDO::FETCH_ASSOC);

if (!$nino) {
    header("Location: gestion_ninos.php?error=Niño no encontrado");
    exit();
}

// Obtener lista de acudientes (padres) para el select
$query_acudientes = "SELECT Documento, Nombre_Completo 
                     FROM Usuarios 
                     WHERE Id_Rol = 2 AND Estado = 'activo'
                     ORDER BY Nombre_Completo";
$stmt_acudientes = $db->query($query_acudientes);
$acudientes = $stmt_acudientes->fetchAll(PDO::FETCH_ASSOC);

// Mensajes de éxito/error
$mensaje = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Procesar formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_completo = trim($_POST['nombre_completo']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $observaciones = trim($_POST['observaciones']);
    $documento_acudiente = $_POST['documento_acudiente'];
    
    // Validaciones básicas
    if (empty($nombre_completo) || empty($fecha_nacimiento) || empty($genero)) {
        $error = "Todos los campos obligatorios deben ser llenados";
    } else {
        // Actualizar en la base de datos
        $query_update = "UPDATE Ninos 
                        SET Nombre_Completo = ?, Fecha_Nacimiento = ?, Genero = ?, 
                            Observaciones = ?, Documento = ?
                        WHERE Registro_Civil = ?";
        
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bindValue(1, $nombre_completo, PDO::PARAM_STR);
        $stmt_update->bindValue(2, $fecha_nacimiento, PDO::PARAM_STR);
        $stmt_update->bindValue(3, $genero, PDO::PARAM_STR);
        $stmt_update->bindValue(4, $observaciones, PDO::PARAM_STR);
        $stmt_update->bindValue(5, $documento_acudiente, PDO::PARAM_INT);
        $stmt_update->bindValue(6, $registro_civil, PDO::PARAM_INT);
        
        if ($stmt_update->execute()) {
            header("Location: editar_nino.php?id=" . $registro_civil . "&success=Niño actualizado correctamente");
            exit();
        } else {
            $error = "Error al actualizar el niño: " . $stmt_update->errorInfo()[2];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Niño - Admin</title>
    <link rel="stylesheet" href="../Styles/editar_nino.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-header {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff9a8b 100%);
            color: white;
        }
        .form-label {
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .badge-gender-M { 
            background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
            color: white;
        }
        .badge-gender-F { 
            background: linear-gradient(135deg, #ff6b9d 0%, #e74c8c 100%);
            color: white;
        }
    </style>
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
                    <h1>👶 Editar Niño</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="gestion_ninos.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Niños
                        </a>
                    </div>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-edit"></i> Editar Información de <?php echo htmlspecialchars($nino['Nombre_Completo']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required-field">Registro Civil</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nino['Registro_Civil']); ?>" disabled>
                                                <div class="form-text">El registro civil no se puede modificar</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required-field">Acudiente</label>
                                                <select class="form-select" name="documento_acudiente" required>
                                                    <option value="">Seleccionar acudiente...</option>
                                                    <?php foreach ($acudientes as $acudiente): ?>
                                                        <option value="<?php echo $acudiente['Documento']; ?>" 
                                                            <?php echo $acudiente['Documento'] == $nino['Documento'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($acudiente['Nombre_Completo'] . ' (' . $acudiente['Documento'] . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label required-field">Nombre Completo del Niño</label>
                                        <input type="text" class="form-control" name="nombre_completo" 
                                               value="<?php echo htmlspecialchars($nino['Nombre_Completo']); ?>" 
                                               required maxlength="100">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required-field">Fecha de Nacimiento</label>
                                                <input type="date" class="form-control" name="fecha_nacimiento" 
                                                       value="<?php echo $nino['Fecha_Nacimiento']; ?>" 
                                                       required max="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required-field">Género</label>
                                                <select class="form-select" name="genero" required>
                                                    <option value="M" <?php echo $nino['Genero'] == 'M' ? 'selected' : ''; ?>>👦 Niño</option>
                                                    <option value="F" <?php echo $nino['Genero'] == 'F' ? 'selected' : ''; ?>>👧 Niña</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Observaciones Médicas o Especiales</label>
                                        <textarea class="form-control" name="observaciones" rows="4" 
                                                  placeholder="Alergias, condiciones médicas, observaciones importantes..."><?php echo htmlspecialchars($nino['Observaciones']); ?></textarea>
                                        <div class="form-text">Información importante para el cuidado del niño</div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="ver_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-secondary me-md-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Información actual -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Información Actual
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Registro Civil:</strong><br>
                                    <?php echo htmlspecialchars($nino['Registro_Civil']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Acudiente Actual:</strong><br>
                                    <?php echo htmlspecialchars($nino['Nombre_Acudiente']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Fecha de Nacimiento:</strong><br>
                                    <?php echo date('d/m/Y', strtotime($nino['Fecha_Nacimiento'])); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Género:</strong><br>
                                    <span class="badge <?php echo $nino['Genero'] == 'M' ? 'badge-gender-M' : 'badge-gender-F'; ?>">
                                        <?php echo $nino['Genero'] == 'M' ? '👦 Niño' : '👧 Niña'; ?>
                                    </span>
                                </div>
                                <?php if (!empty($nino['Observaciones'])): ?>
                                    <div>
                                        <strong>Observaciones:</strong><br>
                                        <small class="text-muted"><?php echo nl2br(htmlspecialchars($nino['Observaciones'])); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-bolt"></i> Acciones Rápidas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="ver_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver Información Completa
                                    </a>
                                    <a href="documentos_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-folder"></i> Gestionar Documentos
                                    </a>
                                    <a href="ver_usuario.php?id=<?php echo $nino['Documento']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-user"></i> Ver Acudiente
                                    </a>
                                </div>
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

        // Validación de fecha de nacimiento
        document.querySelector('form').addEventListener('submit', function(e) {
            const fechaNacimiento = new Date(document.querySelector('[name="fecha_nacimiento"]').value);
            const hoy = new Date();
            
            if (fechaNacimiento > hoy) {
                e.preventDefault();
                alert('La fecha de nacimiento no puede ser futura');
                return false;
            }
            
            // Calcular edad
            const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            if (edad > 18) {
                if (!confirm('⚠️ El niño tiene más de 18 años. ¿Está seguro de que la fecha es correcta?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>