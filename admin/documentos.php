<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

$database = new Database();
$db = $database->getConnection();

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// CONSULTA PRINCIPAL
$query = "SELECT 
    d.Id_Documento,
    d.Nombre_Archivo,
    d.Estado,
    d.Fecha_Carga,
    td.Nombre_Doc as Tipo_Documento,
    u.Nombre_Completo as Usuario_Nombre,
    n.Nombre_Completo as Niño_Nombre
FROM Documentos d
INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
LEFT JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
LEFT JOIN Usuarios u ON n.Documento = u.Documento
WHERE 1=1";

$params = [];

if (!empty($filtro_estado)) {
    $query .= " AND d.Estado = :estado";
    $params[':estado'] = $filtro_estado;
}

if (!empty($filtro_tipo)) {
    $query .= " AND d.Id_Tipo_Doc = :tipo";
    $params[':tipo'] = $filtro_tipo;
}

if (!empty($filtro_usuario)) {
    $query .= " AND u.Documento = :usuario";
    $params[':usuario'] = $filtro_usuario;
}

// CONTAR TOTAL
$query_count = "SELECT COUNT(*) as total 
                FROM Documentos d
                INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
                LEFT JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
                LEFT JOIN Usuarios u ON n.Documento = u.Documento
                WHERE 1=1";

if (!empty($filtro_estado)) {
    $query_count .= " AND d.Estado = :estado";
}

if (!empty($filtro_tipo)) {
    $query_count .= " AND d.Id_Tipo_Doc = :tipo";
}

if (!empty($filtro_usuario)) {
    $query_count .= " AND u.Documento = :usuario";
}

$stmt_count = $db->prepare($query_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}

$stmt_count->execute();
$result_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total_documentos = $result_count['total'];
$total_paginas = ceil($total_documentos / $por_pagina);

// Agregar paginación a la consulta principal
$query .= " ORDER BY d.Fecha_Carga DESC LIMIT :offset, :por_pagina";

// Preparar y ejecutar consulta principal
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);

$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para filtros
$query_tipos = "SELECT * FROM Tipos_Doc";
$stmt_tipos = $db->query($query_tipos);
$tipos_documento = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);

$query_usuarios = "SELECT Documento, Nombre_Completo FROM Usuarios WHERE Id_Rol = 2";
$stmt_usuarios = $db->query($query_usuarios);
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos - Admin</title>
    <link rel="stylesheet" href="../Styles/admindocumentos.css"> <!-- Enlace al CSS -->
    </head>
<body>
     <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2>📋 Gestión de Documentos</h2>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="Pendiente" <?php echo $filtro_estado == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="aprobado" <?php echo $filtro_estado == 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                                    <option value="Rechazo" <?php echo $filtro_estado == 'Rechazo' ? 'selected' : ''; ?>>Rechazado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tipos_documento as $tipo): ?>
                                        <option value="<?php echo $tipo['id_Tipos_Doc']; ?>" <?php echo $filtro_tipo == $tipo['id_Tipos_Doc'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['Nombre_Doc']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="usuario">
                                    <option value="">Todos los usuarios</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?php echo $usuario['Documento']; ?>" <?php echo $filtro_usuario == $usuario['Documento'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($usuario['Nombre_Completo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    🔍 Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Documentos -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Documento</th>
                                        <th>Niño</th>
                                        <th>Usuario</th>
                                        <th>Fecha Carga</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($documentos) > 0): ?>
                                        <?php foreach ($documentos as $doc): ?>
                                            <tr>
                                                <td><?php echo $doc['Id_Documento']; ?></td>
                                                <td><?php echo htmlspecialchars($doc['Tipo_Documento']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['Niño_Nombre'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($doc['Usuario_Nombre'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($doc['Fecha_Carga'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo ($doc['Estado'] == 'aprobado') ? 'success' : 
                                                             (($doc['Estado'] == 'Rechazo') ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($doc['Estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="ver_documento.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-info">
                                                            👁️ Ver
                                                        </a>
                                                        <?php if ($doc['Estado'] != 'Rechazo'): ?>
                                                            <a href="revisar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-warning">
                                                                📝 Revisar
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="../includes/descargar_documento.php?id=<?php echo $doc['Id_Documento']; ?>" class="btn btn-sm btn-success">
                                                            ⬇️ Descargar
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No se encontraron documentos</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link" href="?pagina=<?php echo $i; ?>&estado=<?php echo $filtro_estado; ?>&tipo=<?php echo $filtro_tipo; ?>&usuario=<?php echo $filtro_usuario; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5>Total Documentos</h5>
                                <h3><?php echo $total_documentos; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5>Pendientes</h5>
                                <h3>
                                    <?php
                                    $query_pendientes = "SELECT COUNT(*) as total FROM Documentos WHERE Estado = 'Pendiente'";
                                    $stmt_pendientes = $db->query($query_pendientes);
                                    $result_pendientes = $stmt_pendientes->fetch(PDO::FETCH_ASSOC);
                                    echo $result_pendientes['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h5>Aprobados</h5>
                                <h3>
                                    <?php
                                    $query_aprobados = "SELECT COUNT(*) as total FROM Documentos WHERE Estado = 'aprobado'";
                                    $stmt_aprobados = $db->query($query_aprobados);
                                    $result_aprobados = $stmt_aprobados->fetch(PDO::FETCH_ASSOC);
                                    echo $result_aprobados['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body text-center">
                                <h5>Rechazados</h5>
                                <h3>
                                    <?php
                                    $query_rechazados = "SELECT COUNT(*) as total FROM Documentos WHERE Estado = 'Rechazo'";
                                    $stmt_rechazados = $db->query($query_rechazados);
                                    $result_rechazados = $stmt_rechazados->fetch(PDO::FETCH_ASSOC);
                                    echo $result_rechazados['total'];
                                    ?>
                                </h3>
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