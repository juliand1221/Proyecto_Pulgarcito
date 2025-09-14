<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Mensajes de éxito/error
$mensaje = '';
$error = '';

// Procesar cambios de configuración
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'cambiar_password':
                $nueva_password = trim($_POST['nueva_password']);
                $confirmar_password = trim($_POST['confirmar_password']);
                
                if (empty($nueva_password) || empty($confirmar_password)) {
                    $error = "Ambos campos de contraseña son obligatorios";
                } elseif ($nueva_password !== $confirmar_password) {
                    $error = "Las contraseñas no coinciden";
                } elseif (strlen($nueva_password) < 6) {
                    $error = "La contraseña debe tener al menos 6 caracteres";
                } else {
                    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                    $query = "UPDATE Usuarios SET Contrasena = ? WHERE Documento = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bindValue(1, $password_hash, PDO::PARAM_STR);
                    $stmt->bindValue(2, $sesion['documento'], PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $mensaje = "Contraseña cambiada correctamente";
                    } else {
                        $error = "Error al cambiar la contraseña";
                    }
                }
                break;
                
            case 'config_general':
                $nombre_jardin = trim($_POST['nombre_jardin']);
                $email_contacto = trim($_POST['email_contacto']);
                $telefono_contacto = trim($_POST['telefono_contacto']);
                
                // Aquí podrías guardar en una tabla de configuración
                $mensaje = "Configuración general actualizada (simulado)";
                break;
                
            case 'limpiar_cache':
                // Simular limpieza de cache
                $mensaje = "Caché limpiado correctamente";
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Obtener información del sistema para mostrar
$query_info = "SELECT 
    (SELECT COUNT(*) FROM Usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM Ninos) as total_ninos,
    (SELECT COUNT(*) FROM Documentos) as total_documentos,
    (SELECT MAX(Fecha_Registro) FROM Usuarios) as ultimo_registro";
$stmt_info = $db->query($query_info);
$info_sistema = $stmt_info->fetch(PDO::FETCH_ASSOC);

// Obtener información del servidor
$info_servidor = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'],
    'bd_version' => $db->getAttribute(PDO::ATTR_SERVER_VERSION),
    'ultimo_backup' => '2024-01-25' // Simulado
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Admin</title>
    <link rel="stylesheet" href="../Styles/configuracion.css"> <!-- Enlace al CSS -->
</head>
<body>
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
    
    <main class="col-md-9">
        <!-- Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h2>⚙️ Configuración del Sistema</h2>
            <div class="btn-toolbar mb-2 mb-md-0">
                <span class="text-muted"><?php echo date('d/m/Y H:i:s'); ?></span>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible">
                <span class="icon icon-check-circle"></span> <?php echo $mensaje; ?>
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <span class="icon icon-exclamation-circle"></span> <?php echo $error; ?>
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Pestañas -->
        <ul class="nav-tabs mb-4" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-target="#general" type="button" role="tab">
                    <span class="icon icon-cog"></span> General
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-target="#security" type="button" role="tab">
                    <span class="icon icon-lock"></span> Seguridad
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-target="#system" type="button" role="tab">
                    <span class="icon icon-info-circle"></span> Sistema
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tools-tab" data-target="#tools" type="button" role="tab">
                    <span class="icon icon-tools"></span> Herramientas
                </button>
            </li>
        </ul>

        <div class="tab-content" id="configTabsContent">
            <!-- Pestaña General -->
            <div class="tab-pane active" id="general" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card config-card card-general mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-school"></span> Información del Jardín
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="accion" value="config_general">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nombre del Jardín</label>
                                        <input type="text" class="form-control" name="nombre_jardin" 
                                               value="Jardín Infantil Pulgarcito" placeholder="Nombre de la institución">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email de Contacto</label>
                                        <input type="email" class="form-control" name="email_contacto" 
                                               value="contacto@pulgarcito.edu.co" placeholder="email@ejemplo.com">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono de Contacto</label>
                                        <input type="tel" class="form-control" name="telefono_contacto" 
                                               value="+57 1 2345678" placeholder="+57 XXX XXX XXXX">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <span class="icon icon-save"></span> Guardar Cambios
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Estadísticas rápidas -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-chart-bar"></span> Resumen del Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Usuarios registrados
                                        <span class="badge bg-primary rounded-pill"><?php echo $info_sistema['total_usuarios']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Niños matriculados
                                        <span class="badge bg-success rounded-pill"><?php echo $info_sistema['total_ninos']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Documentos subidos
                                        <span class="badge bg-info rounded-pill"><?php echo $info_sistema['total_documentos']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Último registro
                                        <span class="badge bg-secondary rounded-pill">
                                            <?php echo $info_sistema['ultimo_registro'] ? date('d/m/Y', strtotime($info_sistema['ultimo_registro'])) : 'N/A'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña Seguridad -->
            <div class="tab-pane" id="security" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card config-card card-password mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-key"></span> Cambiar Contraseña
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="accion" value="cambiar_password">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="nueva_password" 
                                               placeholder="Mínimo 6 caracteres" required minlength="6">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" name="confirmar_password" 
                                               placeholder="Repite la contraseña" required minlength="6">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-danger">
                                        <span class="icon icon-sync-alt"></span> Cambiar Contraseña
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-shield-alt"></span> Seguridad de la Cuenta
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <span class="icon icon-info-circle"></span> 
                                    <strong>Recomendaciones de seguridad:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Usa contraseñas con mínimo 8 caracteres</li>
                                        <li>Combina letras, números y símbolos</li>
                                        <li>No compartas tu contraseña con nadie</li>
                                        <li>Cambia tu contraseña regularmente</li>
                                    </ul>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>Actividad reciente:</h6>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <small>Último inicio de sesión: <?php echo date('d/m/Y H:i', strtotime($sesion['fecha_login'] ?? 'now')); ?></small>
                                        </div>
                                        <div class="list-group-item">
                                            <small>IP de conexión: <?php echo $_SERVER['REMOTE_ADDR']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña Sistema -->
            <div class="tab-pane" id="system" role="tabpanel">
                <div class="card config-card card-system">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="icon icon-server"></span> Información del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Servidor:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>PHP Version:</th>
                                            <td><?php echo $info_servidor['php_version']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Servidor Web:</th>
                                            <td><?php echo $info_servidor['server_software']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>MySQL Version:</th>
                                            <td><?php echo $info_servidor['bd_version']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Último Backup:</th>
                                            <td><?php echo $info_servidor['ultimo_backup']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Estadísticas de la Base de Datos:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Tabla</th>
                                            <th>Registros</th>
                                        </tr>
                                        <tr>
                                            <td>Usuarios</td>
                                            <td><?php echo $info_sistema['total_usuarios']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Niños</td>
                                            <td><?php echo $info_sistema['total_ninos']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Documentos</td>
                                            <td><?php echo $info_sistema['total_documentos']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Roles</td>
                                            <td>2</td> <!-- Fijo -->
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña Herramientas -->
            <div class="tab-pane" id="tools" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card config-card card-tools mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-broom"></span> Mantenimiento
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="accion" value="limpiar_cache">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Limpiar Caché del Sistema</label>
                                        <p class="text-muted small">
                                            Elimina archivos temporales y optimiza el rendimiento del sistema.
                                        </p>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <span class="icon icon-broom"></span> Limpiar Caché
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="icon icon-database"></span> Respaldos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Generar Respaldo de Base de Datos</label>
                                    <p class="text-muted small">
                                        Crea una copia de seguridad de toda la información del sistema.
                                    </p>
                                </div>
                                
                                <a href="../includes/backup_database.php" class="btn btn-success mb-2">
                                    <span class="icon icon-download"></span> Generar Backup
                                </a>
                                
                                <div class="mt-3">
                                    <h6>Últimos respaldos:</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <small>pulgarcito_backup_20240125.sql - 25/01/2024</small>
                                        </li>
                                        <li class="list-group-item">
                                            <small>pulgarcito_backup_20240118.sql - 18/01/2024</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Funcionalidad para las pestañas
        document.addEventListener('DOMContentLoaded', function() {
            // Activar pestañas
            const tabLinks = document.querySelectorAll('.nav-link');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remover clase active de todos los links y paneles
                    tabLinks.forEach(l => l.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    // Agregar clase active al link clickeado
                    this.classList.add('active');
                    
                    // Mostrar el panel correspondiente
                    const target = this.getAttribute('data-target');
                    document.querySelector(target).classList.add('active');
                    
                    // Actualizar URL
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('tab', target.substring(1));
                    window.history.replaceState({}, '', `${window.location.pathname}?${urlParams}`);
                });
            });
            
            // Activar pestaña desde URL
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.querySelector(`[data-target="#${tab}"]`);
                if (tabElement) {
                    tabElement.click();
                }
            }
        });
    </script>
</body>
</html>