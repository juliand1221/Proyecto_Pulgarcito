<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener el Registro Civil del niño a visualizar
$registro_civil = isset($_GET['id']) ? $_GET['id'] : null;

if (!$registro_civil) {
    header("Location: gestion_ninos.php?error=Niño no especificado");
    exit();
}

// Obtener datos del niño
$query_nino = "SELECT n.*, u.Nombre_Completo as Nombre_Acudiente, u.Documento as Doc_Acudiente, 
               u.Email as Email_Acudiente, u.Telefono as Telefono_Acudiente
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

// Calcular edad
$fecha_nac = new DateTime($nino['Fecha_Nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nac);

// Obtener documentos del niño
$query_documentos = "SELECT d.*, td.Nombre_Doc as Tipo_Documento, 
                    u_admin.Nombre_Completo as Revisor,
                    CASE 
                        WHEN d.Estado = 'aprobado' THEN 'Aprobado'
                        WHEN d.Estado = 'Rechazo' THEN 'Rechazado'
                        ELSE 'Pendiente'
                    END as Estado_Texto
                    FROM Documentos d
                    INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
                    LEFT JOIN Usuarios u_admin ON d.Documento_Del_Revisor = u_admin.Documento
                    WHERE d.Registro_Civil = ?
                    ORDER BY d.Fecha_Carga DESC";
$stmt_documentos = $db->prepare($query_documentos);
$stmt_documentos->bindValue(1, $registro_civil, PDO::PARAM_INT);
$stmt_documentos->execute();
$documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas de documentos
$total_documentos = count($documentos);
$documentos_aprobados = 0;
$documentos_pendientes = 0;
$documentos_rechazados = 0;

foreach ($documentos as $doc) {
    if ($doc['Estado'] == 'aprobado') $documentos_aprobados++;
    if ($doc['Estado'] == 'Pendiente') $documentos_pendientes++;
    if ($doc['Estado'] == 'Rechazo') $documentos_rechazados++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Niño - Admin</title>
    <link rel="stylesheet" href="../Styles/ver_nino.css ">
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1>👶 Información del Niño</h1>
                                <p class="mb-0">Detalles completos y documentos de <?php echo htmlspecialchars($nino['Nombre_Completo']); ?></p>
                            </div>
                            <div class="btn-group">
                                <a href="gestion_ninos.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left"></i> Volver a Niños
                                </a>
                                <a href="editar_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5>Total Documentos</h5>
                                <h3><?php echo $total_documentos; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h5>Aprobados</h5>
                                <h3><?php echo $documentos_aprobados; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5>Pendientes</h5>
                                <h3><?php echo $documentos_pendientes; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body text-center">
                                <h5>Rechazados</h5>
                                <h3><?php echo $documentos_rechazados; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Información del niño -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-child"></i> Información Personal
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Registro Civil:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($nino['Registro_Civil']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre Completo:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($nino['Nombre_Completo']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Género:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge badge-gender-<?php echo $nino['Genero']; ?>">
                                            <?php echo $nino['Genero'] == 'M' ? '👦 Niño' : '👧 Niña'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha Nacimiento:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo date('d/m/Y', strtotime($nino['Fecha_Nacimiento'])); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Edad:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-secondary">
                                            <?php echo $edad->y . ' años, ' . $edad->m . ' meses'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (!empty($nino['Observaciones'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Observaciones:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo nl2br(htmlspecialchars($nino['Observaciones'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Información del acudiente -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-user"></i> Acudiente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <a href="ver_usuario.php?id=<?php echo $nino['Doc_Acudiente']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($nino['Nombre_Acudiente']); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Documento:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($nino['Doc_Acudiente']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Email:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($nino['Email_Acudiente']); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Teléfono:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($nino['Telefono_Acudiente'] ?: 'No especificado'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos del niño -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-file"></i> Documentos
                                    <span class="badge bg-info"><?php echo $total_documentos; ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($total_documentos > 0): ?>
                                    <div class="list-group">
                                        <?php foreach ($documentos as $doc): ?>
                                            <div class="documento-card">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6><?php echo htmlspecialchars($doc['Tipo_Documento']); ?></h6>
                                                    <span class="badge bg-<?php 
                                                        echo $doc['Estado'] == 'aprobado' ? 'success' : 
                                                             ($doc['Estado'] == 'Rechazo' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo $doc['Estado_Texto']; ?>
                                                    </span>
                                                </div>
                                                <p>
                                                    <strong>Subido:</strong> <?php echo date('d/m/Y', strtotime($doc['Fecha_Carga'])); ?><br>
                                                    <?php if ($doc['Fecha_Revision'] != '0000-00-00'): ?>
                                                        <strong>Revisado:</strong> <?php echo date('d/m/Y', strtotime($doc['Fecha_Revision'])); ?><br>
                                                        <strong>Por:</strong> <?php echo htmlspecialchars($doc['Revisor'] ?: 'N/A'); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if (!empty($doc['Observaciones'])): ?>
                                                    <small class="text-muted">
                                                        <strong>Observaciones:</strong> <?php echo htmlspecialchars($doc['Observaciones']); ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <a href="ver_documento.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <a href="../includes/descargar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-download"></i> Descargar
                                                    </a>
                                                    <?php if ($doc['Estado'] == 'Pendiente'): ?>
                                                        <a href="revisar_documentos.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-check-circle"></i> Revisar
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-file-alt fa-3x mb-2"></i>
                                        <p>No hay documentos subidos para este niño</p>
                                        <a href="documentos_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Subir Documento
                                        </a>
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
        // Funcionalidad para cerrar alertas (si las hubiera)
        document.querySelectorAll('.btn-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>