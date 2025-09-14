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

// Búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$filtro_genero = isset($_GET['genero']) ? $_GET['genero'] : '';

// Construir consulta
$query = "SELECT n.*, u.Nombre_Completo as Nombre_Acudiente, u.Documento as Doc_Acudiente
          FROM Ninos n 
          INNER JOIN Usuarios u ON n.Documento = u.Documento 
          WHERE 1=1";

$params = [];
$types = [];

if (!empty($busqueda)) {
    $query .= " AND (n.Nombre_Completo LIKE ? OR n.Registro_Civil LIKE ? OR u.Nombre_Completo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $types = array_merge($types, [PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]);
}

if (!empty($filtro_genero)) {
    $query .= " AND n.Genero = ?";
    $params[] = $filtro_genero;
    $types[] = PDO::PARAM_STR;
}

// Contar total para paginación
$query_count = "SELECT COUNT(*) as total FROM Ninos n INNER JOIN Usuarios u ON n.Documento = u.Documento WHERE 1=1";
$count_params = [];
$count_types = [];

if (!empty($busqueda)) {
    $query_count .= " AND (n.Nombre_Completo LIKE ? OR n.Registro_Civil LIKE ? OR u.Nombre_Completo LIKE ?)";
    $count_params[] = "%$busqueda%";
    $count_params[] = "%$busqueda%";
    $count_params[] = "%$busqueda%";
    $count_types = array_merge($count_types, [PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]);
}

if (!empty($filtro_genero)) {
    $query_count .= " AND n.Genero = ?";
    $count_params[] = $filtro_genero;
    $count_types[] = PDO::PARAM_STR;
}

$stmt_count = $db->prepare($query_count);
foreach ($count_params as $index => $value) {
    $stmt_count->bindValue($index + 1, $value, $count_types[$index] ?? PDO::PARAM_STR);
}
$stmt_count->execute();
$total_ninos = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_ninos / $por_pagina);

// Agregar paginación a la consulta principal
$query .= " ORDER BY n.Nombre_Completo ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $por_pagina;
$types = array_merge($types, [PDO::PARAM_INT, PDO::PARAM_INT]);

// Ejecutar consulta
$stmt = $db->prepare($query);
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value, $types[$index] ?? PDO::PARAM_STR);
}
$stmt->execute();
$ninos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Genero = 'M' THEN 1 ELSE 0 END) as ninos,
    SUM(CASE WHEN Genero = 'F' THEN 1 ELSE 0 END) as ninas
    FROM Ninos";
$stmt_stats = $db->query($query_stats);
$estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Niños - Admin</title>
    <link rel="stylesheet" href="../Styles/gestion_ninos.css"> <!-- Enlace al CSS -->
    </head>
<body>
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../includes/sidebar_admin.php'; ?>
            </div>
    
            <main class="col-md-9">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>👶 Gestión de Niños</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="exportar_ninos.php" class="btn btn-outline-light">
                            <span class="icon icon-download"></span> Exportar
                        </a>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4 g-4">
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5>Total Niños</h5>
                                <h3><?php echo $estadisticas['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-info">
                            <div class="card-body text-center">
                                <h5>Niños (👦)</h5>
                                <h3><?php echo $estadisticas['ninos']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-pink">
                            <div class="card-body text-center">
                                <h5>Niñas (👧)</h5>
                                <h3><?php echo $estadisticas['ninas']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y Búsqueda -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div style="flex: 1; min-width: 200px;">
                                <input type="text" class="form-control" name="busqueda" placeholder="Buscar por nombre, registro civil o acudiente..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <select class="form-select" name="genero">
                                    <option value="">Todos los géneros</option>
                                    <option value="M" <?php echo $filtro_genero == 'M' ? 'selected' : ''; ?>>👦 Niño</option>
                                    <option value="F" <?php echo $filtro_genero == 'F' ? 'selected' : ''; ?>>👧 Niña</option>
                                </select>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <button type="submit" class="btn btn-primary w-100">
                                    <span class="icon icon-filter"></span> Filtrar
                                </button>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <a href="gestion_ninos.php" class="btn btn-secondary w-100">
                                    <span class="icon icon-sync"></span> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Niños -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Registro Civil</th>
                                        <th>Nombre del Niño</th>
                                        <th>Género</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Edad</th>
                                        <th>Acudiente</th>
                                        <th>Documentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($ninos) > 0): ?>
                                        <?php foreach ($ninos as $nino): ?>
                                            <?php
                                            // Calcular edad
                                            $fecha_nac = new DateTime($nino['Fecha_Nacimiento']);
                                            $hoy = new DateTime();
                                            $edad = $hoy->diff($fecha_nac);
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($nino['Registro_Civil']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($nino['Nombre_Completo']); ?></td>
                                                <td>
                                                    <span class="badge badge-gender-<?php echo $nino['Genero']; ?>">
                                                        <?php echo $nino['Genero'] == 'M' ? '👦 Niño' : '👧 Niña'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($nino['Fecha_Nacimiento'])); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo $edad->y . ' años'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="ver_usuario.php?id=<?php echo $nino['Doc_Acudiente']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($nino['Nombre_Acudiente']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Contar documentos del niño
                                                    $query_docs = "SELECT COUNT(*) as total, 
                                                                  SUM(CASE WHEN Estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados
                                                                  FROM Documentos WHERE Registro_Civil = ?";
                                                    $stmt_docs = $db->prepare($query_docs);
                                                    $stmt_docs->bindValue(1, $nino['Registro_Civil'], PDO::PARAM_INT);
                                                    $stmt_docs->execute();
                                                    $docs = $stmt_docs->fetch(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <span class="badge bg-info" title="Total documentos: <?php echo $docs['total']; ?>">
                                                        📄 <?php echo $docs['total']; ?>
                                                    </span>
                                                    <span class="badge bg-success" title="Documentos aprobados: <?php echo $docs['aprobados']; ?>">
                                                        ✅ <?php echo $docs['aprobados']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="ver_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-sm btn-info" title="Ver información">
                                                            <span class="icon icon-eye"></span>
                                                        </a>
                                                        <a href="editar_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                            <span class="icon icon-edit"></span>
                                                        </a>
                                                        <a href="documentos_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" class="btn btn-sm btn-primary" title="Ver documentos">
                                                            <span class="icon icon-folder"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <span class="icon icon-child"></span>
                                                <p class="text-muted">No se encontraron niños registrados</p>
                                                <?php if (!empty($busqueda) || !empty($filtro_genero)): ?>
                                                    <a href="gestion_ninos.php" class="btn btn-primary mt-2">
                                                        <span class="icon icon-sync"></span> Limpiar filtros
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&genero=<?php echo $filtro_genero; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>