<?php
require_once '../includes/auth.php';
$sesion = verificarAuth();
verificarUsuario($sesion);

// Obtener datos reales de la base de datos
$total_ninos = contarNinosUsuario($sesion['documento']);
$total_documentos = contarDocumentosUsuario($sesion['documento']);
$total_pendientes = contarDocumentosPendientesUsuario($sesion['documento']);
$documentos_recientes = obtenerDocumentosRecientesUsuario($sesion['documento'], 5);

// Obtener estado de matrícula de los niños
$estado_matricula = obtenerEstadoMatricula($sesion['documento']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/userdashboard.css"> <!-- Enlace al CSS -->
    </head>
    <body>
         <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Adobe Express - file.png" alt="Logo" width="30">
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
                        <a href="dashboard.php" class="list-group-item active">Inicio</a>
                        <a href="registrar_nino.php" class="list-group-item">Registrar Niño</a>
                        <a href="subir_documento.php" class="list-group-item">Subir Documentos</a>
                        <a href="mis_documentos.php" class="list-group-item">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item">Contactanos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <!-- Estadísticas rápidas -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Niños Registrados</h5>
                                <p class="card-text"><?php echo $total_ninos; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Documentos Subidos</h5>
                                <p class="card-text"><?php echo $total_documentos; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Pendientes</h5>
                                <p class="card-text"><?php echo $total_pendientes; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado de Matrícula -->
                <div class="card">
                    <div class="card-header">Estado de Matrícula</div>
                    <div class="card-body">
                        <?php if (count($estado_matricula) > 0): ?>
                            <div class="row">
                                <?php foreach ($estado_matricula as $nino): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card <?php echo ($nino['documentos_aprobados'] >= 3) ? 'border-success' : 'border-warning'; ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($nino['nombre_nino']); ?></h5>
                                                <p class="card-text">
                                                    <strong>Estado:</strong> 
                                                    <?php if ($nino['documentos_aprobados'] >= 3): ?>
                                                        <span class="text-success">✅ Matriculado</span>
                                                    <?php else: ?>
                                                        <span class="text-warning">⏳ Pendiente por matricular</span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="card-text">
                                                    <small>
                                                        Documentos aprobados: <?php echo $nino['documentos_aprobados']; ?>/3<br>
                                                        <?php if ($nino['documentos_rechazados'] > 0): ?>
                                                            <span class="text-danger">Documentos rechazados: <?php echo $nino['documentos_rechazados']; ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                </p>
                                                <?php if ($nino['documentos_aprobados'] < 3): ?>
                                                    <a href="subir_documento.php?id_nino=<?php echo $nino['id_nino']; ?>" class="btn btn-sm btn-primary">Subir documentos</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No tienes niños registrados. <a href="registrar_nino.php">Registra tu primer niño</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información de Matrícula -->
                <div class="card bg-light">
                    <div class="card-header">📋 Información Importante sobre Matrícula</div>
                    <div class="card-body">
                        <p>Para que un niño quede <strong>completamente matriculado</strong> en el Jardín Pulgarcito, debe tener <strong>3 documentos aprobados</strong>:</p>
                        <ul>
                            <li>✅ Documento de identidad del niño</li>
                            <li>✅ Carné de vacunación</li>
                            <li>✅ Certificado médico</li>
                        </ul>
                        <p><strong>Importante:</strong> Si algún documento es rechazado, deberás subirlo nuevamente. Los documentos rechazados impiden que el niño complete su matrícula.</p>
                        <div class="alert alert-warning">
                            <strong>⚠️ Atención:</strong> Hasta que los 3 documentos estén aprobados, el niño no estará formalmente matriculado y no podrá asistir al jardín.
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card">
                    <div class="card-header">Acciones Rápidas</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="registrar_nino.php" class="btn btn-primary w-100 mb-2">Registrar Nuevo Niño</a>
                            </div>
                            <div class="col-md-6">
                                <a href="subir_documento.php" class="btn btn-success w-100 mb-2">Subir Documento</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos recientes -->
                <div class="card">
                    <div class="card-header">Documentos Recientes</div>
                    <div class="card-body">
                        <?php if (count($documentos_recientes) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Niño</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos_recientes as $documento): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($documento['Nombre_Doc']); ?></td>
                                                <td><?php echo htmlspecialchars($documento['Nombre_Nino']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($documento['Fecha_Carga'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = '';
                                                    switch ($documento['Estado']) {
                                                        case 'Aprobado': $badge_class = 'bg-success'; break;
                                                        case 'Rechazado': $badge_class = 'bg-danger'; break;
                                                        default: $badge_class = 'bg-warning';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($documento['Estado']); ?>
                                                    </span>
                                                </td>
                                                <td><a href="ver_documento.php?id=<?php echo $documento['Id_Documento']; ?>" class="btn btn-sm btn-info">Ver</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert">
                                No hay documentos registrados. <a href="subir_documento.php">Sube tu primer documento</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>