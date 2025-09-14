<?php
require_once '../includes/auth.php';
require_once '../includes/upload.php';

$sesion = verificarAuth();
verificarUsuario($sesion);

$ninos = obtenerNinosUsuario($sesion['documento']);
$mensaje = $error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $resultado = subirDocumento($_FILES['archivo'], $_POST, $sesion['documento']);
    
    if ($resultado['success']) {
        $mensaje = "✅ Documento subido correctamente. ID: " . $resultado['id'];
    } else {
        $error = "❌ Error: " . $resultado['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Documento - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/subirdocumentos.css"> <!-- Enlace al CSS -->
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
                        <a href="subir_documento.php" class="list-group-item active">Subir Documentos</a>
                        <a href="mis_documentos.php" class="list-group-item">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item">Contactanos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h2>📤 Subir Documento</h2>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Formulario de Subida</h5>
                    </div>
                    <div class="card-body">
                        <form action="subir_documento.php" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Seleccionar Niño</label>
                                        <select class="form-select" name="registro_civil" required>
                                            <option value="">-- Seleccione un niño --</option>
                                            <?php foreach ($ninos as $nino): ?>
                                                <option value="<?php echo $nino['Registro_Civil']; ?>">
                                                    <?php echo htmlspecialchars($nino['Nombre_Completo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($ninos)): ?>
                                            <div class="form-text text-warning">
                                                No tienes niños registrados. 
                                                <a href="registrar_nino.php">Registrar niño primero</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Tipo de Documento</label>
                                        <select class="form-select" name="tipo_documento" required>
                                            <option value="">-- Seleccione el tipo --</option>
                                            <option value="1">📝 Registro Civil</option>
                                            <option value="2">🪪 Cédula de Ciudadanía</option>
                                            <option value="3">📸 Foto del Niño</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required">Archivo</label>
                                <input type="file" class="form-control" name="archivo" 
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                <div class="form-text">
                                    Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX. 
                                    Tamaño máximo: 10MB
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones (opcional)</label>
                                <textarea class="form-control" name="observaciones" rows="3" 
                                          placeholder="Agregue alguna observación importante..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    📤 Subir Documento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Información importante -->
                <div class="alert alert-info">
                    <h6>📋 Información importante:</h6>
                    <ul>
                        <li>Los documentos serán revisados por administración</li>
                        <li>El estado cambiará a "Aprobado" una vez verificado</li>
                        <li>Puede subir múltiples documentos por niño</li>
                        <li>Verifique el estado en "Mis Documentos"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación de tamaño de archivo
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (file && file.size > maxSize) {
                alert('❌ El archivo es demasiado grande. Máximo: 10MB');
                e.target.value = '';
            }
        });
    </script>
</body>
</html>