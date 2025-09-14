<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas generales
$stats = [];

// Total de usuarios
$query_usuarios = "SELECT COUNT(*) as total, 
                  SUM(CASE WHEN Estado = 'activo' THEN 1 ELSE 0 END) as activos,
                  SUM(CASE WHEN Id_Rol = 1 THEN 1 ELSE 0 END) as administradores,
                  SUM(CASE WHEN Id_Rol = 2 THEN 1 ELSE 0 END) as padres
                  FROM Usuarios";
$stmt_usuarios = $db->query($query_usuarios);
$stats['usuarios'] = $stmt_usuarios->fetch(PDO::FETCH_ASSOC);

// Total de niños
$query_ninos = "SELECT COUNT(*) as total,
               SUM(CASE WHEN Genero = 'M' THEN 1 ELSE 0 END) as ninos,
               SUM(CASE WHEN Genero = 'F' THEN 1 ELSE 0 END) as ninas
               FROM Ninos";
$stmt_ninos = $db->query($query_ninos);
$stats['ninos'] = $stmt_ninos->fetch(PDO::FETCH_ASSOC);

// Total de documentos
$query_documentos = "SELECT COUNT(*) as total,
                    SUM(CASE WHEN Estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
                    SUM(CASE WHEN Estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN Estado = 'Rechazo' THEN 1 ELSE 0 END) as rechazados
                    FROM Documentos";
$stmt_documentos = $db->query($query_documentos);
$stats['documentos'] = $stmt_documentos->fetch(PDO::FETCH_ASSOC);

// Usuarios registrados por mes (últimos 6 meses)
$query_registros = "SELECT DATE_FORMAT(Fecha_Registro, '%Y-%m') as mes, 
                   COUNT(*) as cantidad
                   FROM Usuarios 
                   WHERE Fecha_Registro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                   GROUP BY DATE_FORMAT(Fecha_Registro, '%Y-%m')
                   ORDER BY mes DESC
                   LIMIT 6";
$stmt_registros = $db->query($query_registros);
$stats['registros_mensuales'] = $stmt_registros->fetchAll(PDO::FETCH_ASSOC);

// Documentos por tipo
$query_docs_tipo = "SELECT td.Nombre_Doc, COUNT(d.Id_Documento) as cantidad,
                   SUM(CASE WHEN d.Estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados
                   FROM Tipos_Doc td
                   LEFT JOIN Documentos d ON td.id_Tipos_Doc = d.Id_Tipo_Doc
                   GROUP BY td.id_Tipos_Doc
                   ORDER BY td.Nombre_Doc";
$stmt_docs_tipo = $db->query($query_docs_tipo);
$stats['documentos_tipo'] = $stmt_docs_tipo->fetchAll(PDO::FETCH_ASSOC);

// Fecha para el reporte
$fecha_reporte = date('d/m/Y H:i:s');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Admin</title>
    <link rel="stylesheet" href="../Styles/reportes.css"> <!-- Enlace al CSS -->
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
            <div>
                <h2>📊 Reportes del Sistema</h2>
                <p class="text-muted mb-0">Estadísticas y métricas de Jardín Pulgarcito</p>
            </div>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <span class="icon icon-print"></span> Imprimir
                </button>
                <span class="ms-2 text-muted"><?php echo $fecha_reporte; ?></span>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="row mb-4 g-4">
            <div style="flex: 1; min-width: 200px;">
                <div class="card stat-card card-usuarios">
                    <div class="card-body text-center">
                        <span class="icon icon-users"></span>
                        <h4><?php echo $stats['usuarios']['total']; ?></h4>
                        <h6 class="text-muted">Usuarios Totales</h6>
                        <div class="mt-2">
                            <span class="badge bg-success"><?php echo $stats['usuarios']['activos']; ?> activos</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <div class="card stat-card card-ninos">
                    <div class="card-body text-center">
                        <span class="icon icon-child"></span>
                        <h4><?php echo $stats['ninos']['total']; ?></h4>
                        <h6 class="text-muted">Niños Registrados</h6>
                        <div class="mt-2">
                            <span class="badge bg-info"><?php echo $stats['ninos']['ninos']; ?> niños</span>
                            <span class="badge bg-pink"><?php echo $stats['ninos']['ninas']; ?> niñas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <div class="card stat-card card-documentos">
                    <div class="card-body text-center">
                        <span class="icon icon-file"></span>
                        <h4><?php echo $stats['documentos']['total']; ?></h4>
                        <h6 class="text-muted">Documentos</h6>
                        <div class="mt-2">
                            <span class="badge bg-success"><?php echo $stats['documentos']['aprobados']; ?> aprobados</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <div class="card stat-card card-aprobados">
                    <div class="card-body text-center">
                        <span class="icon icon-chart-pie"></span>
                        <h4>
                            <?php 
                            $porcentaje = $stats['documentos']['total'] > 0 ? 
                                round(($stats['documentos']['aprobados'] / $stats['documentos']['total']) * 100, 1) : 0;
                            echo $porcentaje . '%';
                            ?>
                        </h4>
                        <h6 class="text-muted">Tasa de Aprobación</h6>
                        <div class="mt-2">
                            <span class="badge bg-warning"><?php echo $stats['documentos']['pendientes']; ?> pendientes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Distribución de Usuarios -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="icon icon-chart-bar"></span> Distribución de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <span class="icon icon-user-shield"></span>
                                    <h4><?php echo $stats['usuarios']['administradores']; ?></h4>
                                    <small class="text-muted">Administradores</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <span class="icon icon-user"></span>
                                    <h4><?php echo $stats['usuarios']['padres']; ?></h4>
                                    <small class="text-muted">Padres/Acudientes</small>
                                </div>
                            </div>
                        </div>
                        <div class="mini-chart mt-3">
                            <?php
                            $total_usuarios = $stats['usuarios']['total'];
                            $width_admin = $total_usuarios > 0 ? ($stats['usuarios']['administradores'] / $total_usuarios) * 100 : 0;
                            $width_padres = $total_usuarios > 0 ? ($stats['usuarios']['padres'] / $total_usuarios) * 100 : 0;
                            ?>
                            <div class="chart-bar bg-danger" style="width: <?php echo $width_admin; ?>%">
                                <?php if ($width_admin > 15): ?>Admin<?php endif; ?>
                            </div>
                            <div class="chart-bar bg-primary" style="width: <?php echo $width_padres; ?>%">
                                <?php if ($width_padres > 15): ?>Padres<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Documentos -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="icon icon-file-contract"></span> Estado de Documentos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <span class="icon icon-check-circle"></span>
                                <h5><?php echo $stats['documentos']['aprobados']; ?></h5>
                                <small class="text-muted">Aprobados</small>
                            </div>
                            <div class="col-4">
                                <span class="icon icon-clock"></span>
                                <h5><?php echo $stats['documentos']['pendientes']; ?></h5>
                                <small class="text-muted">Pendientes</small>
                            </div>
                            <div class="col-4">
                                <span class="icon icon-times-circle"></span>
                                <h5><?php echo $stats['documentos']['rechazados']; ?></h5>
                                <small class="text-muted">Rechazados</small>
                            </div>
                        </div>
                        <div class="mini-chart mt-3">
                            <?php
                            $total_docs = $stats['documentos']['total'];
                            $width_aprobados = $total_docs > 0 ? ($stats['documentos']['aprobados'] / $total_docs) * 100 : 0;
                            $width_pendientes = $total_docs > 0 ? ($stats['documentos']['pendientes'] / $total_docs) * 100 : 0;
                            $width_rechazados = $total_docs > 0 ? ($stats['documentos']['rechazados'] / $total_docs) * 100 : 0;
                            ?>
                            <div class="chart-bar bg-success" style="width: <?php echo $width_aprobados; ?>%">
                                <?php if ($width_aprobados > 15): ?>✓<?php endif; ?>
                            </div>
                            <div class="chart-bar bg-warning" style="width: <?php echo $width_pendientes; ?>%">
                                <?php if ($width_pendientes > 15): ?>⏳<?php endif; ?>
                            </div>
                            <div class="chart-bar bg-danger" style="width: <?php echo $width_rechazados; ?>%">
                                <?php if ($width_rechazados > 15): ?>✗<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Documentos por Tipo -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="icon icon-list-alt"></span> Documentos por Tipo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tipo de Documento</th>
                                        <th>Total</th>
                                        <th>Aprobados</th>
                                        <th>% Aprobación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['documentos_tipo'] as $tipo): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tipo['Nombre_Doc']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $tipo['cantidad']; ?></span></td>
                                            <td><span class="badge bg-success"><?php echo $tipo['aprobados']; ?></span></td>
                                            <td>
                                                <?php
                                                $porcentaje = $tipo['cantidad'] > 0 ? 
                                                    round(($tipo['aprobados'] / $tipo['cantidad']) * 100, 1) : 0;
                                                ?>
                                                <span class="badge bg-info"><?php echo $porcentaje; ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registros Mensuales -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="icon icon-chart-line"></span> Registros Mensuales (Últimos 6 meses)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stats['registros_mensuales'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Registros</th>
                                            <th>Gráfico</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $max_registros = max(array_column($stats['registros_mensuales'], 'cantidad'));
                                        foreach ($stats['registros_mensuales'] as $registro): 
                                            $mes = DateTime::createFromFormat('Y-m', $registro['mes']);
                                        ?>
                                            <tr>
                                                <td><?php echo $mes->format('F Y'); ?></td>
                                                <td><span class="badge bg-primary"><?php echo $registro['cantidad']; ?></span></td>
                                                <td>
                                                    <div style="width: 100%; height: 20px; background: #f8f9fa; border-radius: 3px;">
                                                        <div style="width: <?php echo $max_registros > 0 ? ($registro['cantidad'] / $max_registros) * 100 : 0; ?>%; 
                                                            height: 100%; background: #0d6efd; border-radius: 3px;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No hay datos de registros mensuales</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen General -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="icon icon-info-circle"></span> Resumen General
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Usuarios activos
                                <span class="badge bg-primary rounded-pill"><?php echo $stats['usuarios']['activos']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Niños registrados
                                <span class="badge bg-success rounded-pill"><?php echo $stats['ninos']['total']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Documentos pendientes de revisión
                                <span class="badge bg-warning rounded-pill"><?php echo $stats['documentos']['pendientes']; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Tasa de aprobación de documentos
                                <span class="badge bg-info rounded-pill">
                                    <?php 
                                    $porcentaje = $stats['documentos']['total'] > 0 ? 
                                        round(($stats['documentos']['aprobados'] / $stats['documentos']['total']) * 100, 1) : 0;
                                    echo $porcentaje . '%';
                                    ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Proporción niños/niñas
                                <span class="badge bg-pink rounded-pill">
                                    <?php 
                                    $proporcion = $stats['ninos']['total'] > 0 ? 
                                        round(($stats['ninos']['ninos'] / $stats['ninos']['total']) * 100, 1) : 0;
                                    echo $proporcion . '% niños';
                                    ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Fecha del reporte
                                <span class="badge bg-secondary rounded-pill"><?php echo $fecha_reporte; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>