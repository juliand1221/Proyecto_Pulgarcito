<?php
require_once '../includes/auth.php';
require_once '../includes/upload.php';

$sesion = verificarAuth();
verificarUsuario($sesion);

$mensaje = $error = '';
$usuario = obtenerUsuarioPorDocumento($sesion['documento']);
$ninos = obtenerNinosUsuario($sesion['documento']);
$documentos = obtenerDocumentosUsuario($sesion['documento']);

// Función para obtener datos del usuario
function obtenerUsuarioPorDocumento($documento) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM Usuarios WHERE Documento = :documento";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documento);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_perfil'])) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    
    try {
        $query = "UPDATE Usuarios SET Nombre_Completo = :nombre, Email = :email, Telefono = :telefono 
                  WHERE Documento = :documento";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":nombre", $nombre_completo);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":documento", $sesion['documento']);
        
        if ($stmt->execute()) {
            $mensaje = "✅ Perfil actualizado correctamente";
            // Actualizar datos en sesión
            $_SESSION['nombre'] = $nombre_completo;
            // Refrescar datos del usuario
            $usuario = obtenerUsuarioPorDocumento($sesion['documento']);
        } else {
            $error = "❌ Error al actualizar el perfil";
        }
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_password'])) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $password_actual = $_POST['password_actual'];
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Verificar contraseña actual
    if (password_verify($password_actual, $usuario['Contrasena'])) {
        if ($nueva_password === $confirmar_password) {
            if (strlen($nueva_password) >= 6) {
                $nueva_password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                
                $query = "UPDATE Usuarios SET Contrasena = :password WHERE Documento = :documento";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":password", $nueva_password_hash);
                $stmt->bindParam(":documento", $sesion['documento']);
                
                if ($stmt->execute()) {
                    $mensaje = "✅ Contraseña cambiada correctamente";
                } else {
                    $error = "❌ Error al cambiar la contraseña";
                }
            } else {
                $error = "❌ La nueva contraseña debe tener al menos 6 caracteres";
            }
        } else {
            $error = "❌ Las contraseñas nuevas no coinciden";
        }
    } else {
        $error = "❌ Contraseña actual incorrecta";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/perfil.css"> <!-- Enlace al CSS -->
    </head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Adobe Express - file.png" alt="Logo">
                Jardín Pulgarcito
            </a>
            <div class="navbar-nav">
                <span class="navbar-text">Hola, <?php echo htmlspecialchars($sesion['nombre']); ?></span>
                <a href="../logout.php" class="btn-outline-light">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Sidebar -->
                <div class="card">
                    <div class="card-header">Menú de Usuario</div>
                    <div class="list-group">
                        <a href="dashboard.php" class="list-group-item">Inicio</a>
                        <a href="registrar_nino.php" class="list-group-item">Registrar Niño</a>
                        <a href="subir_documento.php" class="list-group-item">Subir Documentos</a>
                        <a href="mis_documentos.php" class="list-group-item">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item active">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item">Contactanos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h2>👤 Mi Perfil</h2>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Tabs -->
                <ul class="nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#datos-personales">Datos Personales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cambiar-password">Cambiar Contraseña</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#ninos-matriculados">Niños Matriculados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#estado-documentos">Estado de Documentos</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Datos Personales -->
                    <div class="tab-pane active" id="datos-personales">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📋 Información Personal</h5>
                            </div>
                            <div class="card-body">
                                <form action="perfil.php" method="post">
                                    <input type="hidden" name="actualizar_perfil" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">Documento de Identidad</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['Documento']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">Nombre Completo</label>
                                                <input type="text" class="form-control" name="nombre_completo" 
                                                       value="<?php echo htmlspecialchars($usuario['Nombre_Completo']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">Email</label>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?php echo htmlspecialchars($usuario['Email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" name="telefono" 
                                                       value="<?php echo htmlspecialchars($usuario['Telefono'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Registro</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d/m/Y', strtotime($usuario['Fecha_Registro'])); ?>" readonly>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">💾 Actualizar Perfil</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div class="tab-pane" id="cambiar-password">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">🔒 Cambiar Contraseña</h5>
                            </div>
                            <div class="card-body">
                                <form action="perfil.php" method="post">
                                    <input type="hidden" name="cambiar_password" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label required">Contraseña Actual</label>
                                        <input type="password" class="form-control" name="password_actual" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label required">Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="nueva_password" required minlength="6">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label required">Confirmar Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="confirmar_password" required minlength="6">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">🔑 Cambiar Contraseña</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Niños Matriculados -->
                    <div class="tab-pane" id="ninos-matriculados">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">👦👧 Niños Matriculados</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($ninos)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Registro Civil</th>
                                                    <th>Fecha Nacimiento</th>
                                                    <th>Edad</th>
                                                    <th>Género</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ninos as $nino): 
                                                    $edad = calcularEdad($nino['Fecha_Nacimiento']);
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($nino['Nombre_Completo']); ?></td>
                                                        <td><?php echo $nino['Registro_Civil']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($nino['Fecha_Nacimiento'])); ?></td>
                                                        <td><?php echo $edad; ?> años</td>
                                                        <td><?php echo ($nino['Genero'] == 'M') ? '👦 Masculino' : '👧 Femenino'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No tienes niños matriculados. 
                                        <a href="registrar_nino.php">Registrar un niño</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Estado de Documentos -->
                    <div class="tab-pane" id="estado-documentos">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📁 Estado de Documentos</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($documentos)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Niño</th>
                                                    <th>Fecha Subida</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documentos as $doc): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($doc['Nombre_Doc']); ?></td>
                                                        <td><?php echo htmlspecialchars($doc['Nombre_Nino']); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($doc['Fecha_Carga'])); ?></td>
                                                        <td>
                                                            <?php 
                                                            $badgeClass = [
                                                                'Pendiente' => 'bg-warning',
                                                                'aprobado' => 'bg-success',
                                                                'Rechazo' => 'bg-danger'
                                                            ][$doc['Estado']] ?? 'bg-secondary';
                                                            ?>
                                                            <span class="badge <?php echo $badgeClass; ?>">
                                                                <?php echo $doc['Estado']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <strong>📋 Leyenda de estados:</strong><br>
                                        <span class="badge bg-warning">Pendiente</span> - En revisión por administración<br>
                                        <span class="badge bg-success">Aprobado</span> - Documento aceptado<br>
                                        <span class="badge bg-danger">Rechazo</span> - Requiere correcciones
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No hay documentos subidos. 
                                        <a href="subir_documento.php">Subir documentos</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad de tabs
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover clase active de todos los tabs
                document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                // Agregar clase active al tab clickeado
                this.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const target = this.getAttribute('href');
                document.querySelector(target).classList.add('active');
            });
        });

        // Validación de contraseñas
        document.querySelector('form[name="cambiar_password"]')?.addEventListener('submit', function(e) {
            const nueva = this.querySelector('input[name="nueva_password"]');
            const confirmar = this.querySelector('input[name="confirmar_password"]');
            
            if (nueva.value !== confirmar.value) {
                e.preventDefault();
                alert('❌ Las contraseñas no coinciden');
            }
            
            if (nueva.value.length < 6) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 6 caracteres');
            }
        });
    </script>
</body>
</html>