<?php
require_once '../includes/auth.php';
$sesion = verificarAuth();
verificarAdmin($sesion);

$estadisticas = obtenerEstadisticasDocumentos();
$documentos_pendientes = obtenerDocumentosPendientes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/admindashboard.css">
</head>
<body>
             <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2>📊 Dashboard de Administración</h2>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <?php 
                    $total_pendientes = 0;
                    $total_aprobados = 0;
                    $total_rechazados = 0;
                    
                    foreach ($estadisticas as $stat) {
                        if ($stat['Estado'] == 'Pendiente') $total_pendientes = $stat['cantidad'];
                        if ($stat['Estado'] == 'aprobado') $total_aprobados = $stat['cantidad'];
                        if ($stat['Estado'] == 'Rechazo') $total_rechazados = $stat['cantidad'];
                    }
                    ?>
                    
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5 class="card-title">⏰ Pendientes</h5>
                                <h3 class="card-text"><?php echo $total_pendientes; ?></h3>
                                <small>Documentos por revisar</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h5 class="card-title">✅ Aprobados</h5>
                                <h3 class="card-text"><?php echo $total_aprobados; ?></h3>
                                <small>Documentos validados</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body text-center">
                                <h5 class="card-title">❌ Rechazados</h5>
                                <h3 class="card-text"><?php echo $total_rechazados; ?></h3>
                                <small>Documentos rechazados</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos Pendientes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Documentos Pendientes de Revisión</h5>
                        <span class="badge"><?php echo count($documentos_pendientes); ?> pendientes</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos_pendientes)): ?>
                            <div class="alert alert-success">
                                ✅ ¡No hay documentos pendientes de revisión!
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Niño</th>
                                            <th>Padre/Acudiente</th>
                                            <th>Fecha Subida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos_pendientes as $doc): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($doc['Nombre_Doc']); ?></strong>
                                                    <br><small><?php echo htmlspecialchars($doc['Nombre_Archivo']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($doc['Nombre_Nino']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($doc['Nombre_Padre']); ?>
                                                    <br><small>Doc: <?php echo $doc['Doc_Padre']; ?></small>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($doc['Fecha_Carga'])); ?></td>
                                                <td>
                                                    <a href="ver_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                       class="btn btn-sm btn-info" target="_blank">
                                                       👁️ Ver
                                                    </a>
                                                    <a href="revisar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                       class="btn btn-sm btn-warning">
                                                       🔍 Revisar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-primary">📋</div>
                                <h5>Gestión de Documentos</h5>
                                <a href="documentos.php" class="btn btn-primary mt-2">Ver Todos</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-success">👥</div>
                                <h5>Gestión de Usuarios</h5>
                                <a href="gestion_usuarios.php" class="btn btn-success mt-2">Administrar</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-info">📈</div>
                                <h5>Reportes</h5>
                                <a href="reportes.php" class="btn btn-info mt-2">Generar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Efectos de hover para las cards de estadísticas
        document.querySelectorAll('.card.text-white').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'all 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>