<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Obtener el Registro Civil del niño
$registro_civil = isset($_GET['id']) ? $_GET['id'] : null;

if (!$registro_civil) {
    header("Location: gestion_ninos.php?error=Niño no especificado");
    exit();
}

// Obtener datos del niño
$query_nino = "SELECT n.*, u.Nombre_Completo as Nombre_Acudiente 
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

// Obtener tipos de documento para el select
$query_tipos = "SELECT * FROM Tipos_Doc ORDER BY Nombre_Doc";
$stmt_tipos = $db->query($query_tipos);
$tipos_documento = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);

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

// Procesar subida de documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['archivo'])) {
    $id_tipo_doc = $_POST['tipo_documento'];
    $observaciones = trim($_POST['observaciones']);
    
    // Validar tipo de documento
    $tipo_valido = false;
    foreach ($tipos_documento as $tipo) {
        if ($tipo['id_Tipos_Doc'] == $id_tipo_doc) {
            $tipo_valido = true;
            break;
        }
    }
    
    if (!$tipo_valido) {
        $error = "Tipo de documento no válido";
    } else {
        // Procesar archivo
        $archivo = $_FILES['archivo'];
        $nombre_archivo = $archivo['name'];
        $tipo_archivo = $archivo['type'];
        $tamaño_archivo = $archivo['size'];
        $archivo_tmp = $archivo['tmp_name'];
        
        // Validaciones
        $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $tamaño_maximo = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($extension, $extensiones_permitidas)) {
            $error = "Tipo de archivo no permitido. Formatos aceptados: " . implode(', ', $extensiones_permitidas);
        } elseif ($tamaño_archivo > $tamaño_maximo) {
            $error = "El archivo es demasiado grande. Máximo 5MB permitidos";
        } else {
            // Crear directorio de uploads si no existe
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generar nombre único para el archivo
            $nuevo_nombre = uniqid() . '_' . time() . '.' . $extension;
            $ruta_destino = $upload_dir . $nuevo_nombre;
            
            if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
                // Insertar en base de datos
                $query_insert = "INSERT INTO Documentos 
                                (Nombre_Archivo, Ruta_Archivo, Fecha_Carga, Estado, Observaciones, 
                                 Registro_Civil, Id_Tipo_Doc, Documento_Del_Revisor, Fecha_Revision)
                                VALUES (?, ?, NOW(), 'Pendiente', ?, ?, ?, NULL, NULL)";
                
                $stmt_insert = $db->prepare($query_insert);
                $stmt_insert->bindValue(1, $nombre_archivo, PDO::PARAM_STR);
                $stmt_insert->bindValue(2, $ruta_destino, PDO::PARAM_STR);
                $stmt_insert->bindValue(3, $observaciones, PDO::PARAM_STR);
                $stmt_insert->bindValue(4, $registro_civil, PDO::PARAM_INT);
                $stmt_insert->bindValue(5, $id_tipo_doc, PDO::PARAM_INT);
                
                if ($stmt_insert->execute()) {
                    $mensaje = "Documento subido correctamente";
                    // Recargar documentos
                    $stmt_documentos->execute();
                    $documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $error = "Error al guardar en la base de datos";
                    // Eliminar archivo subido
                    unlink($ruta_destino);
                }
            } else {
                $error = "Error al subir el archivo";
            }
        }
    }
}

// Estadísticas
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
    <title>Documentos del Niño - Admin</title>
    <link rel="stylesheet" href="../Styles/documentos_nino.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .documento-card {
            transition: transform 0.2s;
            border-left: 4px solid transparent;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
            margin-bottom: 10px;
            background: white;
        }
        .documento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .documento-aprobado {
            border-left-color: #27ae60 !important;
        }
        .documento-pendiente {
            border-left-color: #f39c12 !important;
        }
        .documento-rechazado {
            border-left-color: #e74c3c !important;
        }
        .badge-documento {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .list-group-flush .list-group-item {
            border: none;
            padding: 10px 0;
        }
    </style>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1>📂 Documentos de <?php echo htmlspecialchars($nino['Nombre_Completo']); ?></h1>
                        <p class="text-muted mb-0">Acudiente: <?php echo htmlspecialchars($nino['Nombre_Acudiente']); ?></p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="ver_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Volver al Niño
                        </a>
                        <a href="gestion_ninos.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Todos los Niños
                        </a>
                    </div>
                </div>

                <?php if (isset($mensaje)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center py-3">
                                <h6>Total Documentos</h6>
                                <h4><?php echo $total_documentos; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center py-3">
                                <h6>Aprobados</h6>
                                <h4><?php echo $documentos_aprobados; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center py-3">
                                <h6>Pendientes</h6>
                                <h4><?php echo $documentos_pendientes; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body text-center py-3">
                                <h6>Rechazados</h6>
                                <h4><?php echo $documentos_rechazados; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Formulario de subida -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-upload"></i> Subir Nuevo Documento
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Documento *</label>
                                        <select class="form-select" name="tipo_documento" required>
                                            <option value="">Seleccionar tipo...</option>
                                            <?php foreach ($tipos_documento as $tipo): ?>
                                                <option value="<?php echo $tipo['id_Tipos_Doc']; ?>">
                                                    <?php echo htmlspecialchars($tipo['Nombre_Doc']); ?>
                                                    <?php if ($tipo['Obligatorio']): ?>
                                                        (Obligatorio)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Archivo *</label>
                                        <input type="file" class="form-control" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                        <div class="form-text">
                                            Formatos permitidos: PDF, JPG, PNG, DOC, DOCX. Máximo 5MB.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="observaciones" rows="3" 
                                                  placeholder="Observaciones sobre este documento..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-upload"></i> Subir Documento
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Documentos obligatorios -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exclamation-circle"></i> Documentos Obligatorios
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($tipos_documento as $tipo): ?>
                                        <?php if ($tipo['Obligatorio']): ?>
                                            <?php
                                            $tiene_documento = false;
                                            foreach ($documentos as $doc) {
                                                if ($doc['Id_Tipo_Doc'] == $tipo['id_Tipos_Doc'] && $doc['Estado'] == 'aprobado') {
                                                    $tiene_documento = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo htmlspecialchars($tipo['Nombre_Doc']); ?>
                                                <span class="badge bg-<?php echo $tiene_documento ? 'success' : 'danger'; ?>">
                                                    <?php echo $tiene_documento ? '✅' : '❌'; ?>
                                                </span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de documentos -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-files"></i> Documentos Existentes
                                    <span class="badge bg-primary"><?php echo $total_documentos; ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($total_documentos > 0): ?>
                                    <div class="list-group">
                                        <?php foreach ($documentos as $doc): ?>
                                            <div class="documento-card documento-<?php echo strtolower($doc['Estado_Texto']); ?>">
                                                <div class="d-flex w-100 justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6><?php echo htmlspecialchars($doc['Tipo_Documento']); ?></h6>
                                                        <p class="text-muted">
                                                            <strong>Archivo:</strong> <?php echo htmlspecialchars($doc['Nombre_Archivo']); ?><br>
                                                            <strong>Subido:</strong> <?php echo date('d/m/Y H:i', strtotime($doc['Fecha_Carga'])); ?>
                                                        </p>
                                                        <?php if ($doc['Fecha_Revision'] && $doc['Fecha_Revision'] != '0000-00-00'): ?>
                                                            <p class="text-muted">
                                                                <strong>Revisado:</strong> <?php echo date('d/m/Y', strtotime($doc['Fecha_Revision'])); ?>
                                                                <?php if ($doc['Revisor']): ?>
                                                                    por <?php echo htmlspecialchars($doc['Revisor']); ?>
                                                                <?php endif; ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($doc['Observaciones'])): ?>
                                                            <p>
                                                                <strong>Observaciones:</strong> 
                                                                <?php echo htmlspecialchars($doc['Observaciones']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-<?php 
                                                            echo $doc['Estado'] == 'aprobado' ? 'success' : 
                                                                 ($doc['Estado'] == 'Rechazo' ? 'danger' : 'warning'); 
                                                        ?> badge-documento">
                                                            <?php echo $doc['Estado_Texto']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
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
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmarEliminacion(<?php echo $doc['Id_Documento']; ?>)">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-file-alt fa-4x mb-3"></i>
                                        <h5>No hay documentos subidos</h5>
                                        <p>Comienza subiendo el primer documento usando el formulario</p>
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
        // Funcionalidad para cerrar alertas
        document.querySelectorAll('.btn-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        function confirmarEliminacion(idDocumento) {
            if (confirm('¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.')) {
                window.location.href = '../includes/eliminar_documento.php?id=' + idDocumento + '&redirect=documentos_nino.php?id=<?php echo $nino['Registro_Civil']; ?>';
            }
        }
        
        // Validación de tamaño de archivo
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file && file.size > maxSize) {
                alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
                e.target.value = '';
            }
        });
    </script>
</body>
</html>