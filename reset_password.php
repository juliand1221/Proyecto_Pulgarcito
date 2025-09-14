<?php
require_once './database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Crear instancia de Database y obtener conexión
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar token válido
    $stmt = $db->prepare("SELECT documento, expiration, used FROM password_reset_tokens WHERE token = ?");
    $stmt->bindParam(1, $token, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() === 1) {
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el token ha expirado o ya fue usado
        if (strtotime($tokenData['expiration']) < time()) {
            $error = "El enlace de recuperación ha expirado.";
        } elseif ($tokenData['used'] == 1) {
            $error = "Este enlace ya ha sido utilizado.";
        } else {
            // Token válido, mostrar formulario para nueva contraseña
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nuevaContrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);
                $documento = $tokenData['documento'];
                
                // Actualizar contraseña
                $stmt = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE documento = ?");
                $stmt->bindParam(1, $nuevaContrasena, PDO::PARAM_STR);
                $stmt->bindParam(2, $documento, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    // Marcar token como usado
                    $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                    $stmt->bindParam(1, $token, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    $success = "Contraseña actualizada correctamente. Ya puedes <a href='login.php'>iniciar sesión</a>.";
                } else {
                    $error = "Error al actualizar la contraseña.";
                }
            }
        }
    } else {
        $error = "Token de recuperación inválido.";
    }
} else {
    $error = "No se proporcionó token de recuperación.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="./Styles/login.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-login">
                    <h2>Restablecer Contraseña</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php elseif (isset($tokenData) && !$error): ?>
                        <form method="post" onsubmit="return validarContrasena()">
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" name="nueva_contrasena" id="nueva_contrasena" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="confirmar_contrasena" id="confirmar_contrasena" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Establecer Nueva Contraseña</button>
                        </form>
                    <?php endif; ?>
                    
                    <p class="mt-3 text-center">
                        <a href="login.php">Volver al inicio de sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validarContrasena() {
            const nueva = document.getElementById('nueva_contrasena').value;
            const confirmar = document.getElementById('confirmar_contrasena').value;
            
            if (nueva.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            if (nueva !== confirmar) {
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>