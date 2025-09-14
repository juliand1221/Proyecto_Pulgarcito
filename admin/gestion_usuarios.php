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
$filtro_rol = isset($_GET['rol']) ? $_GET['rol'] : '';

// Construir consulta
$query = "SELECT u.*, r.Nombre_Rol as Rol 
          FROM Usuarios u 
          INNER JOIN Roles r ON u.Id_Rol = r.id_Rol 
          WHERE 1=1";

$params = [];

if (!empty($busqueda)) {
    $query .= " AND (u.Nombre_Completo LIKE ? OR u.Email LIKE ? OR u.Documento LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%"; 
    $params[] = "%$busqueda%";
}

if (!empty($filtro_rol)) {
    $query .= " AND u.Id_Rol = ?";
    $params[] = $filtro_rol;
}

// Contar total para paginación (CORREGIDO)
$query_count = "SELECT COUNT(*) as total FROM Usuarios u INNER JOIN Roles r ON u.Id_Rol = r.id_Rol WHERE 1=1";
$count_params = [];

if (!empty($busqueda)) {
    $query_count .= " AND (u.Nombre_Completo LIKE ? OR u.Email LIKE ? OR u.Documento LIKE ?)";
    $count_params[] = "%$busqueda%";
    $count_params[] = "%$busqueda%";
    $count_params[] = "%$busqueda%";
}

if (!empty($filtro_rol)) {
    $query_count .= " AND u.Id_Rol = ?";
    $count_params[] = $filtro_rol;
}

$stmt_count = $db->prepare($query_count);
foreach ($count_params as $index => $value) {
    $stmt_count->bindValue($index + 1, $value);
}
$stmt_count->execute();
$total_usuarios = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_usuarios / $por_pagina);

// Agregar paginación a la consulta principal (CORREGIDO)
$query .= " ORDER BY u.Fecha_Registro DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $por_pagina;

// Ejecutar consulta (CORREGIDO)
$stmt = $db->prepare($query);
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener roles para el filtro
$query_roles = "SELECT * FROM Roles";
$stmt_roles = $db->query($query_roles);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    <link rel="stylesheet" href="../Styles/gestion_usuarios.css"> <!-- Enlace al CSS -->
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
                    <h2>Gestión de Usuarios</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="exportar_usuarios.php" class="btn btn-outline-light">
                            <span class="icon icon-download"></span> Exportar
                        </a>
                    </div>
                </div>

                <!-- Filtros y Búsqueda -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div style="flex: 1; min-width: 200px;">
                                <input type="text" class="form-control" name="busqueda" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <select class="form-select" name="rol">
                                    <option value="">Todos los roles</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id_Rol']; ?>" <?php echo $filtro_rol == $rol['id_Rol'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rol['Nombre_Rol']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <button type="submit" class="btn btn-primary w-100">
                                    <span class="icon icon-filter"></span> Filtrar
                                </button>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <a href="gestion_usuarios.php" class="btn btn-secondary w-100">
                                    <span class="icon icon-sync"></span> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Documento</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Rol</th>
                                        <th>Fecha Registro</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($usuarios) > 0): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usuario['Documento']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['Nombre_Completo']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['Email']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['Telefono']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usuario['Id_Rol'] == 1 ? 'danger' : 'info'; ?>">
                                                        <?php echo htmlspecialchars($usuario['Rol']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($usuario['Fecha_Registro'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usuario['Estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($usuario['Estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="editar_usuario.php?id=<?php echo $usuario['Documento']; ?>" class="btn btn-sm btn-warning">
                                                            <span class="icon icon-edit"></span>
                                                        </a>
                                                        <a href="ver_usuario.php?id=<?php echo $usuario['Documento']; ?>" class="btn btn-sm btn-info">
                                                            <span class="icon icon-eye"></span>
                                                        </a>
                                                        <?php if ($usuario['Estado'] == 'activo'): ?>
                                                            <a href="../includes/desactivar_usuario.php?id=<?php echo $usuario['Documento']; ?>" class="btn btn-sm btn-danger">
                                                                <span class="icon icon-ban"></span>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="../includes/activar_usuario.php?id=<?php echo $usuario['Documento']; ?>" class="btn btn-sm btn-success">
                                                                <span class="icon icon-check"></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No se encontraron usuarios</td>
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
                                            <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&rol=<?php echo $filtro_rol; ?>">
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
                <div class="row mt-4 g-4">
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5>Total Usuarios</h5>
                                <h3><?php echo $total_usuarios; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h5>Activos</h5>
                                <h3>
                                    <?php
                                    $query_activos = "SELECT COUNT(*) as total FROM Usuarios WHERE Estado = 'activo'";
                                    $stmt_activos = $db->query($query_activos);
                                    echo $stmt_activos->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5>Administradores</h5>
                                <h3>
                                    <?php
                                    $query_admins = "SELECT COUNT(*) as total FROM Usuarios WHERE Id_Rol = 1";
                                    $stmt_admins = $db->query($query_admins);
                                    echo $stmt_admins->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="card text-white bg-info">
                            <div class="card-body text-center">
                                <h5>Padres</h5>
                                <h3>
                                    <?php
                                    $query_padres = "SELECT COUNT(*) as total FROM Usuarios WHERE Id_Rol = 2";
                                    $stmt_padres = $db->query($query_padres);
                                    echo $stmt_padres->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>