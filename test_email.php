<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jdt1221@gmail.com';
    $mail->Password = 'iecl ppjh eycg pcbk';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->setFrom('tu_email@gmail.com', 'Prueba');
    $mail->addAddress('jdt1221@gmail.com');
    $mail->Subject = '✅ Prueba de PHPMailer';
    $mail->Body = '¡Funciona correctamente! ' . date('Y-m-d H:i:s');
    
    if ($mail->send()) {
        echo "✅ EMAIL ENVIADO EXITOSAMENTE";
    } else {
        echo "❌ Error al enviar";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $mail->ErrorInfo;
}
?>