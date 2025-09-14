<?php
require_once '../includes/auth.php';
require_once '../includes/upload.php';

$sesion = verificarAuth();
verificarUsuario($sesion);

$documentos = obtenerDocumentosUsuario($sesion['documento']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Documentos - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/mis_documentos.css">
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
                        <a href="mis_documentos.php" class="list-group-item active">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item">Contactanos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h2>📁 Mis Documentos</h2>
                
                <?php if (empty($documentos)): ?>
                    <div class="alert alert-info">
                        No hay documentos subidos. 
                        <a href="subir_documento.php" class="alert-link">Subir primer documento</a>
                    </div>
                <?php else: ?>
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
                                        <td>
                                            <a href="ver_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                class="btn btn-sm btn-info" target="_blank">👁️ Ver</a>
                                            <?php if ($doc['Estado'] == 'Pendiente'): ?>
                                                <a href="../includes/eliminar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Eliminar este documento?')">🗑️ Eliminar</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <!-- Mostrar observaciones si el documento fue rechazado -->
                                    <?php if ($doc['Estado'] == 'Rechazo' && !empty($doc['Observaciones'])): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="alert alert-danger">
                                                <strong>📝 Razones del rechazo:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($doc['Observaciones'])); ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Estados:</strong><br>
                        <span class="badge bg-warning">Pendiente</span> - En revisión<br>
                        <span class="badge bg-success">Aprobado</span> - Documento aceptado<br>
                        <span class="badge bg-danger">Rechazo</span> - Requiere correcciones
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Confirmación para eliminar documento
        function confirmDelete(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este documento?')) {
                e.preventDefault();
            }
        }

        // Asignar evento a los botones de eliminar
        document.querySelectorAll('.btn-danger').forEach(button => {
            button.addEventListener('click', confirmDelete);
        });
    </script>
</body>
</html>