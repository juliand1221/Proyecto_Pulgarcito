<?php
require_once '../includes/auth.php';
require_once '../database.php';
require_once '../includes/functions.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id_documento = $_GET['id'];
$mensaje = $error = '';

// Obtener información del documento
$database = new Database();
$db = $database->getConnection();

$query = "SELECT d.*, td.Nombre_Doc, n.Nombre_Completo as Nombre_Nino, 
                 u.Nombre_Completo as Nombre_Padre, u.Email as Email_Padre, u.Documento as Documento_Usuario
          FROM Documentos d
          INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
          INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
          INNER JOIN Usuarios u ON n.Documento = u.Documento
          WHERE d.Id_Documento = :id_documento";

$stmt = $db->prepare($query);
$stmt->bindParam(":id_documento", $id_documento);
$stmt->execute();
$documento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$documento) {
    die("Documento no encontrado");
}

// Procesar revisión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estado = $_POST['estado'];
    $observaciones = trim($_POST['observaciones']);
    
    // Validar que se ingresen observaciones si se rechaza el documento
    if ($estado == 'Rechazo' && empty($observaciones)) {
        $error = "❌ Debe ingresar las razones del rechazo";
    } else {
        if (actualizarEstadoDocumento($id_documento, $estado, $observaciones, $sesion['documento'])) {
            $mensaje = "✅ Documento " . strtolower($estado) . " correctamente";
            header("refresh:2;url=documentos.php");
        } else {
            $error = "❌ Error al actualizar el documento";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Documento - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/revisar_documento.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const estadoRadios = document.querySelectorAll('input[name="estado"]');
            const observacionesTextarea = document.querySelector('textarea[name="observaciones"]');
            
            // Función para validar el formulario
            function validarFormulario() {
                const estadoSeleccionado = document.querySelector('input[name="estado"]:checked');
                
                if (estadoSeleccionado && estadoSeleccionado.value === 'Rechazo') {
                    if (observacionesTextarea.value.trim() === '') {
                        alert('Debe ingresar las razones del rechazo');
                        return false;
                    }
                }
                return true;
            }
            
            // Mostrar/ocultar requerimiento de observaciones
            estadoRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Rechazo') {
                        observacionesTextarea.setAttribute('required', 'required');
                    } else {
                        observacionesTextarea.removeAttribute('required');
                    }
                });
            });
            
            // Validar formulario antes de enviar
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (!validarFormulario()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2>🔍 Revisar Documento</h2>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Información del Documento -->
                <div class="card mb-4">
                    <div class="card-header bg-info">
                        <h5 class="mb-0">Información del Documento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>📄 Tipo de Documento:</strong> <?php echo htmlspecialchars($documento['Nombre_Doc']); ?></p>
                                <p><strong>📁 Archivo:</strong> <?php echo htmlspecialchars($documento['Nombre_Archivo']); ?></p>
                                <p><strong>📅 Fecha de Subida:</strong> <?php echo date('d/m/Y H:i', strtotime($documento['Fecha_Carga'])); ?></p>
                                <p><strong>📊 Estado Actual:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $documento['Estado'] == 'aprobado' ? 'success' : 
                                             ($documento['Estado'] == 'Rechazo' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($documento['Estado']); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>👶 Niño:</strong> <?php echo htmlspecialchars($documento['Nombre_Nino']); ?></p>
                                <p><strong>👨‍👩‍👧‍👦 Padre/Acudiente:</strong> <?php echo htmlspecialchars($documento['Nombre_Padre']); ?></p>
                                <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($documento['Email_Padre']); ?></p>
                            </div>
                        </div>
                        
                        <!-- Mostrar observaciones si el documento fue rechazado -->
                        <?php if ($documento['Estado'] == 'Rechazo' && !empty($documento['Observaciones'])): ?>
                            <div class="alert alert-danger mt-3">
                                <strong>📝 Razones del rechazo:</strong><br>
                                <?php echo nl2br(htmlspecialchars($documento['Observaciones'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <a href="ver_documento.php?id=<?php echo $documento['Id_Documento']; ?>" 
                               class="btn btn-primary" target="_blank">
                               👁️ Ver Documento Completo
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Revisión -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Formulario de Revisión</h5>
                    </div>
                    <div class="card-body">
                        <form action="revisar_documento.php?id=<?php echo $id_documento; ?>" method="post" onsubmit="return validarFormulario()">
                            <div class="mb-3">
                                <label class="form-label"><strong>Estado de Revisión</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="estado" id="aprobado" value="aprobado" required>
                                    <label class="form-check-label text-success" for="aprobado">
                                        ✅ Aprobar Documento
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="estado" id="rechazo" value="Rechazo">
                                    <label class="form-check-label text-danger" for="rechazo">
                                        ❌ Rechazar Documento
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Observaciones</strong></label>
                                <textarea class="form-control" name="observaciones" rows="4" 
                                          placeholder="Escriba aquí las observaciones de la revisión..."><?php echo htmlspecialchars($documento['Observaciones'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    Para documentos rechazados, explique claramente las correcciones necesarias.
                                    <span id="obligatorio-text" style="color: red; display: none;"> (Obligatorio)</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="documentos.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    ✅ Enviar Revisión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        function validarFormulario() {
            const estadoSeleccionado = document.querySelector('input[name="estado"]:checked');
            const observaciones = document.querySelector('textarea[name="observaciones"]').value.trim();
            
            if (estadoSeleccionado && estadoSeleccionado.value === 'Rechazo') {
                if (observaciones === '') {
                    alert('Debe ingresar las razones del rechazo antes de enviar');
                    return false;
                }
            }
            return true;
        }

        // Mostrar campo obligatorio cuando se selecciona rechazo
        document.addEventListener('DOMContentLoaded', function() {
            const estadoRadios = document.querySelectorAll('input[name="estado"]');
            const obligatorioText = document.getElementById('obligatorio-text');
            
            estadoRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Rechazo') {
                        obligatorioText.style.display = 'inline';
                    } else {
                        obligatorioText.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>