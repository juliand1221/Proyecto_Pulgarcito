<?php
require_once '../includes/auth.php';
$sesion = verificarAuth();
verificarUsuario($sesion);

$mensaje = $error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Recoger y validar datos
    $registro_civil = trim($_POST['registro_civil']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $observaciones = trim($_POST['observaciones']);
    $documento_usuario = $sesion['documento'];
    
    try {
        // Insertar niño
        $query = "INSERT INTO Ninos (Registro_Civil, Nombre_Completo, Fecha_Nacimiento, Genero, Observaciones, Documento) 
                  VALUES (:registro_civil, :nombre_completo, :fecha_nacimiento, :genero, :observaciones, :documento)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":registro_civil", $registro_civil);
        $stmt->bindParam(":nombre_completo", $nombre_completo);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":genero", $genero);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":documento", $documento_usuario);
        
        if ($stmt->execute()) {
            $mensaje = "✅ Niño registrado exitosamente!";
            // Limpiar formulario
            $_POST = array();
        } else {
            $error = "❌ Error al registrar el niño";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "❌ Ya existe un niño con este número de registro civil";
        } else {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

$ninos = obtenerNinosUsuario($sesion['documento']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Niño - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/registrar_nino.css"> <!-- Enlace al CSS -->

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
                        <a href="registrar_nino.php" class="list-group-item active">Registrar Niño</a>
                        <a href="subir_documento.php" class="list-group-item">Subir Documentos</a>
                        <a href="mis_documentos.php" class="list-group-item">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item">Contactanos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h2>👶 Registrar Niño</h2>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Datos del Niño</h5>
                    </div>
                    <div class="card-body">
                        <form action="registrar_nino.php" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Número de Registro Civil</label>
                                        <input type="number" class="form-control" name="registro_civil" 
                                               value="<?php echo $_POST['registro_civil'] ?? ''; ?>" 
                                               required placeholder="Ej: 123456789">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Nombre Completo</label>
                                        <input type="text" class="form-control" name="nombre_completo" 
                                               value="<?php echo $_POST['nombre_completo'] ?? ''; ?>" 
                                               required placeholder="Nombre y apellidos">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" name="fecha_nacimiento" 
                                               value="<?php echo $_POST['fecha_nacimiento'] ?? ''; ?>" 
                                               required max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Género</label>
                                        <select class="form-select" name="genero" required>
                                            <option value="">-- Seleccione --</option>
                                            <option value="M" <?php echo (($_POST['genero'] ?? '') == 'M') ? 'selected' : ''; ?>>Masculino</option>
                                            <option value="F" <?php echo (($_POST['genero'] ?? '') == 'F') ? 'selected' : ''; ?>>Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Edad</label>
                                        <input type="text" class="form-control" id="edad_calculada" readonly 
                                               placeholder="Se calculará automáticamente">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones Médicas</label>
                                <textarea class="form-control" name="observaciones" rows="3" 
                                          placeholder="Alergias, condiciones médicas, medicamentos, etc."><?php echo $_POST['observaciones'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">👶 Registrar Niño</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de niños registrados -->
                <?php if (!empty($ninos)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">👦👧 Niños Registrados</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Registro Civil</th>
                                            <th>Nombre</th>
                                            <th>Edad</th>
                                            <th>Género</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ninos as $nino): 
                                            $edad = calcularEdad($nino['Fecha_Nacimiento']);
                                        ?>
                                            <tr>
                                                <td><?php echo $nino['Registro_Civil']; ?></td>
                                                <td><?php echo htmlspecialchars($nino['Nombre_Completo']); ?></td>
                                                <td><?php echo $edad; ?> años</td>
                                                <td><?php echo ($nino['Genero'] == 'M') ? '👦 Masculino' : '👧 Femenino'; ?></td>
                                                <td>
                                                    <a href="editar_nino.php?id=<?php echo $nino['Registro_Civil']; ?>" 
                                                       class="btn btn-sm btn-warning">Editar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-4">
                        <strong>📝 Información:</strong> Aún no has registrado ningún niño. 
                        Completa el formulario arriba para registrar el primero.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Calcular edad automáticamente
        document.querySelector('input[name="fecha_nacimiento"]').addEventListener('change', function() {
            const fechaNacimiento = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            
            // Ajustar si aún no ha pasado el cumpleaños este año
            const mes = hoy.getMonth() - fechaNacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }
            
            document.getElementById('edad_calculada').value = edad + ' años';
        });

        // Validar que el registro civil sea numérico
        document.querySelector('input[name="registro_civil"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>