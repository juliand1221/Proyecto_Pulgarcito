<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del usuario a visualizar
$documento_usuario = isset($_GET['id']) ? $_GET['id'] : null;

if (!$documento_usuario) {
    header("Location: gestion_usuarios.php?error=Usuario no especificado");
    exit();
}

// Obtener datos del usuario
$query_usuario = "SELECT u.*, r.Nombre_Rol as Rol 
                 FROM Usuarios u 
                 INNER JOIN Roles r ON u.Id_Rol = r.id_Rol 
                 WHERE u.Documento = ?";
$stmt_usuario = $db->prepare($query_usuario);
$stmt_usuario->bindValue(1, $documento_usuario, PDO::PARAM_INT);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: gestion_usuarios.php?error=Usuario no encontrado");
    exit();
}

// Obtener niños matriculados por este usuario
$query_ninos = "SELECT n.* 
               FROM Ninos n 
               WHERE n.Documento = ? 
               ORDER BY n.Nombre_Completo";
$stmt_ninos = $db->prepare($query_ninos);
$stmt_ninos->bindValue(1, $documento_usuario, PDO::PARAM_INT);
$stmt_ninos->execute();
$ninos = $stmt_ninos->fetchAll(PDO::FETCH_ASSOC);

// Obtener documentos subidos por este usuario (a través de sus niños)
$query_documentos = "SELECT d.*, td.Nombre_Doc as Tipo_Documento, n.Nombre_Completo as Nombre_Nino
                    FROM Documentos d
                    INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
                    INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
                    WHERE n.Documento = ?
                    ORDER BY d.Fecha_Carga DESC";
$stmt_documentos = $db->prepare($query_documentos);
$stmt_documentos->bindValue(1, $documento_usuario, PDO::PARAM_INT);
$stmt_documentos->execute();
$documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);

// Contar estadísticas
$total_ninos = count($ninos);
$total_documentos = count($documentos);
$documentos_pendientes = 0;
$documentos_aprobados = 0;
$documentos_rechazados = 0;

foreach ($documentos as $doc) {
    if ($doc['Estado'] == 'Pendiente') $documentos_pendientes++;
    if ($doc['Estado'] == 'aprobado') $documentos_aprobados++;
    if ($doc['Estado'] == 'Rechazo') $documentos_rechazados++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuario - Admin</title>
    <link rel="stylesheet" href="../Styles/gestion_usuarios.css">
    <link rel="stylesheet" href="../Styles/ver_usuario.css">
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
                <!-- Header -->
                <div class="profile-header">
                    <div class="container">
                        <div class="d-flex justify-content-between flex-wrap align-items-center">
                            <div>
                                <h1>Información del Usuario</h1>
                                <p>Detalles completos del usuario y sus niños matriculados</p>
                            </div>
                            <div class="btn-group">
                                <a href="gestion_usuarios.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left"></i> Volver a Usuarios
                                </a>
                                <a href="editar_usuario.php?id=<?php echo $usuario['Documento']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar Usuario
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5>Niños Matriculados</h5>
                                <h3><?php echo $total_ninos; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-white bg-info">
                            <div class="card-body text-center">
                                <h5>Total Documentos</h5>
                                <h3><?php echo $total_documentos; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-white bg-success">
                            <div class="card-body text-center">
                                <h5>Documentos Aprobados</h5>
                                <h3><?php echo $documentos_aprobados; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5>Documentos Pendientes</h5>
                                <h3><?php echo $documentos_pendientes; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del usuario -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-user"></i> Información Personal
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Documento:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($usuario['Documento']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre Completo:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($usuario['Nombre_Completo']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Email:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($usuario['Email']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Teléfono:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($usuario['Telefono'] ?: 'No especificado'); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Rol:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-<?php echo $usuario['Id_Rol'] == 1 ? 'danger' : 'info'; ?>">
                                            <?php echo htmlspecialchars($usuario['Rol']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Estado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-<?php echo $usuario['Estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($usuario['Estado']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Fecha Registro:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo date('d/m/Y', strtotime($usuario['Fecha_Registro'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Niños matriculados -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-child"></i> Niños Matriculados
                                    <span class="badge bg-primary"><?php echo $total_ninos; ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($total_ninos > 0): ?>
                                    <div class="children-list">
                                        <?php foreach ($ninos as $nino): ?>
                                            <div class="list-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6><?php echo htmlspecialchars($nino['Nombre_Completo']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo $nino['Genero'] == 'M' ? '👦 Niño' : '👧 Niña'; ?>
                                                    </small>
                                                </div>
                                                <p>
                                                    <strong>Registro Civil:</strong> <?php echo $nino['Registro_Civil']; ?><br>
                                                    <strong>Fecha Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($nino['Fecha_Nacimiento'])); ?><br>
                                                    <strong>Edad:</strong> 
                                                    <?php 
                                                        $fecha_nac = new DateTime($nino['Fecha_Nacimiento']);
                                                        $hoy = new DateTime();
                                                        $edad = $hoy->diff($fecha_nac);
                                                        echo $edad->y . ' años';
                                                    ?>
                                                </p>
                                                <?php if (!empty($nino['Observaciones'])): ?>
                                                    <small class="text-muted">
                                                        <strong>Observaciones:</strong> <?php echo htmlspecialchars($nino['Observaciones']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-child fa-3x mb-2"></i>
                                        <p>No hay niños matriculados</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos del usuario -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-file"></i> Documentos Subidos
                            <span class="badge bg-info"><?php echo $total_documentos; ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($total_documentos > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Niño</th>
                                            <th>Fecha Carga</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc['Tipo_Documento']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['Nombre_Nino']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($doc['Fecha_Carga'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $doc['Estado'] == 'aprobado' ? 'success' : 
                                                             ($doc['Estado'] == 'Rechazo' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($doc['Estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="ver_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                       class="btn btn-sm btn-info" title="Ver documento">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="../includes/descargar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                       class="btn btn-sm btn-success" title="Descargar">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-3x mb-2"></i>
                                <p>No hay documentos subidos</p>
                            </div>
                        <?php endif; ?>
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