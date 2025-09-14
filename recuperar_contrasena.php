<?php
require_once './database.php'; // Asegúrate de que esta ruta es correcta
require_once 'includes/auth.php';

// Cargar PHPMailer manualmente (sin Composer)
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

// Usar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = trim($_POST['documento']);
    
    // Crear instancia de Database y obtener conexión
    $database = new Database();
    $db = $database->getConnection(); // Esto sí funciona con tu clase actual
    
    // Verificar si el documento existe
    $stmt = $db->prepare("SELECT email, Nombre_Completo FROM usuarios WHERE documento = ?");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $user['email'];
        $nombre = $user['Nombre_Completo'];
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos
        $stmt = $db->prepare("INSERT INTO password_reset_tokens (documento, token, expiration) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $documento, PDO::PARAM_STR);
        $stmt->bindParam(2, $token, PDO::PARAM_STR);
        $stmt->bindParam(3, $expiration, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Configurar y enviar correo con PHPMailer
            if (sendPasswordResetEmail($email, $nombre, $token)) {
                header("Location: login.php?recuperacion=enviado");
                exit();
            } else {
                header("Location: login.php?error=email");
                exit();
            }
        } else {
            header("Location: login.php?error=token");
            exit();
        }
    } else {
        header("Location: login.php?error=documento_no_existe");
        exit();
    }
}

/**
 * Función para enviar correo de recuperación con PHPMailer
 */
function sendPasswordResetEmail($email, $nombre, $token) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jdt1221@gmail.com';
        $mail->Password = 'iecl ppjh eycg pcbk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Configurar caracteres UTF-8
        $mail->CharSet = 'UTF-8';
        
        // Remitente
        $mail->setFrom('no-reply@jardinpulgarcito.com', 'Jardín Pulgarcito');
        $mail->addReplyTo('info@jardinpulgarcito.com', 'Información Jardín Pulgarcito');
        
        // Destinatario
        $mail->addAddress($email, $nombre);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - Jardín Pulgarcito';
        
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/Proyecto_Pulgarcito/reset_password.php?token=" . $token;
        
        $mail->Body = "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #87CEEB 0%, #FFB347 100%); padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .footer { background: #333; color: white; padding: 10px; text-align: center; border-radius: 0 0 10px 10px; }
                    .button { background: linear-gradient(135deg, #87CEEB 0%, #FFB347 100%); color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Jardín Pulgarcito</h2>
                    </div>
                    <div class='content'>
                        <h3>Hola $nombre,</h3>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña en el sistema del Jardín Pulgarcito.</p>
                        <p>Para continuar, haz clic en el siguiente botón:</p>
                        <p style='text-align: center;'>
                            <a href='$resetLink' class='button'>Restablecer Contraseña</a>
                        </p>
                        <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                        <p><small>$resetLink</small></p>
                        <p>Este enlace expirará en 1 hora por seguridad.</p>
                        <p>Si no solicitaste este cambio, ignora este mensaje y tu contraseña permanecerá igual.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Jardín Pulgarcito. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Texto alternativo
        $mail->AltBody = "Hola $nombre,\n\nPara restablecer tu contraseña en el Jardín Pulgarcito, visita el siguiente enlace:\n$resetLink\n\nEste enlace expirará en 1 hora.\n\nSi no solicitaste este cambio, ignora este mensaje.";
        
        // Enviar correo
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}