<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Jardín Infantil</title>
    <link rel="stylesheet" href="./Styles/principal.css">
    <!-- reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

    <!-- Header -->
    <header class="main-header">
    <div class="header-overlay">
        <div class="header-container">
            <div class="header-content">
                <h1 class="titulo">Jardín Infantil Pulgarcito</h1>
                <img src="images/Adobe Express - file.png" alt="Logo del Jardín" class="logo">
                
            </div>
        </div>
    </div>
    </header>

    <!-- Navegación -->
    <nav>
    <ul>
        <li><a href="./index.php">Inicio</a></li>
        <li><a href="./QuienesSomos.php">Quiénes Somos</a></li>
        <li><a href="./contacto.php">Contacto</a></li>
        <li><a href="./politicas.php">Políticas de Seguridad</a></li>
        <li><a href="login.php" class="login-btn">Iniciar Sesión</a></li>
    </ul>
</nav>

    <!-- Contenido Principal -->
    <main>
        <section class="contact-section">
            <div class="contact-container">
                <h2>Contáctanos</h2>
                
                <!-- Mostrar mensajes de éxito/error -->
                <?php
                if (isset($_GET['status'])) {
                    $status = $_GET['status'];
                    $message = $_GET['message'] ?? '';
                    
                    if ($status === 'success') {
                        echo '<div class="alert alert-success">✅ ' . htmlspecialchars($message) . '</div>';
                    } elseif ($status === 'error') {
                        echo '<div class="alert alert-error">❌ ' . htmlspecialchars($message) . '</div>';
                    }
                }
                ?>
                
                <div class="contact-content">
                    <div class="contact-info">
                        <h3>📞 Información de Contacto</h3>
                        <div class="contact-item">
                            <h4>📍 Dirección</h4>
                            <p>Cra 26 i3 # 121-123, Remansos de Comfandi, Cali, Colombia</p>
                        </div>
                        <div class="contact-item">
                            <h4>📞 Teléfonos</h4>
                            <p>Fijo: (602) 373-0133</p>
                            <p>Móvil: (+57) 311 3362516</p>
                        </div>
                        <div class="contact-item">
                            <h4>✉️ Email</h4>
                            <p>jdt1221@gmail.com</p>
                        </div>
                        <div class="contact-item">
                            <h4>🕒 Horario de Atención</h4>
                            <p>Lunes a Viernes: 7:00 AM - 5:00 PM</p>
                            <p>Sábados: 8:00 AM - 12:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="contact-form">
                        <h3>📝 Envíanos un Mensaje</h3>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="form-group">
                                <input type="text" name="nombre" placeholder="Tu nombre completo" required 
                                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Tu email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <input type="tel" name="telefono" placeholder="Tu teléfono"
                                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <select name="asunto" required>
                                    <option value="">Selecciona el asunto</option>
                                    <option value="inscripcion" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'inscripcion') ? 'selected' : ''; ?>>Inscripciones</option>
                                    <option value="informacion" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'informacion') ? 'selected' : ''; ?>>Información general</option>
                                    <option value="quejas" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'quejas') ? 'selected' : ''; ?>>Quejas o sugerencias</option>
                                    <option value="otros" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'otros') ? 'selected' : ''; ?>>Otros</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="mensaje" placeholder="Escribe tu mensaje aquí..." rows="5" required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                            </div>
                            
                            <!-- CAPTCHA -->
                            <div class="form-group">
                                <div class="g-recaptcha" data-sitekey="6LdZt8ErAAAAALJk8_BfBgeTzGUYIZ8ZBB9oEqE5"></div>
                            </div>
                            
                            <button type="submit" name="enviar" class="contact-button">Enviar Mensaje</button>
                        </form>
                    </div>
                </div>

                <div class="map-section">
                    <h3>🗺️ ¿Dónde Estamos?</h3>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d248.9198603687833!2d-76.46629017690366!3d3.4187837448251837!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses-419!2sco!4v1757299072643!5m2!1ses-419!2sc" 
                                width="100%" 
                                height="300" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
    <div class="footer-content">
        <div class="social-links">
            <h3>Redes Sociales</h3>
            <div class="social-icons">
                <a href="https://facebook.com/tupagina" target="_blank" class="social-link">
                    <img src="./images/facebook.png" alt="Facebook" class="social-icon">
                </a>
                <a href="https://instagram.com/tucuenta" target="_blank" class="social-link">
                    <img src="./images/instagram.png" alt="Instagram" class="social-icon">
                </a>
            </div>
        </div>
        
        <div class="footer-info">
            <p>&copy; 2025 Jardín Infantil Pulgarcito. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

    <!-- PHP para procesar el formulario -->
    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
        // Incluir PHPMailer
        require 'phpmailer/src/Exception.php';
        require 'phpmailer/src/PHPMailer.php';
        require 'phpmailer/src/SMTP.php';

        // 1. Validar CAPTCHA
        $captcha = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = "6LdZt8ErAAAAAKqD9wSrY3AZ5Z8JuN48K7bfcm7Z"; // Clave secreta de prueba
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $data = array(
            'secret' => $secretKey,
            'response' => $captcha
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $responseKeys = json_decode($response, true);
        
        if (!$responseKeys["success"]) {
            echo "<script>
                    alert('Por favor, completa el CAPTCHA correctamente.');
                    window.history.back();
                  </script>";
            exit;
        }

        // 2. Recoger y validar datos
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $asunto = trim($_POST['asunto']);
        $mensaje = trim($_POST['mensaje']);

        if (empty($nombre) || empty($email) || empty($mensaje)) {
            echo "<script>
                    alert('Por favor, completa todos los campos obligatorios.');
                    window.history.back();
                  </script>";
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>
                    alert('Por favor, ingresa un email válido.');
                    window.history.back();
                  </script>";
            exit;
        }

        // 3. Configurar PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jdt1221@gmail.com'; // Cambia esto
            $mail->Password = 'iecl ppjh eycg pcbk'; // Cambia esto
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Destinatarios
            $mail->setFrom($email, $nombre);
            $mail->addAddress('jdt1221@gmail.com'); // Tu email
            $mail->addReplyTo($email, $nombre);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = "Nuevo mensaje de contacto: $asunto";
            
            $mail->Body = "
            <h2>Nuevo mensaje del formulario de contacto</h2>
            <p><strong>Nombre:</strong> $nombre</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Teléfono:</strong> " . ($telefono ? $telefono : 'No proporcionado') . "</p>
            <p><strong>Asunto:</strong> $asunto</p>
            <p><strong>Mensaje:</strong><br>" . nl2br($mensaje) . "</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
            ";

            $mail->AltBody = "Nombre: $nombre\nEmail: $email\nTeléfono: $telefono\nAsunto: $asunto\nMensaje: $mensaje";

            // Enviar email
            $mail->send();
            
            // Redirigir con éxito
            echo "<script>
                    window.location.href = 'contacto.php?status=success&message=Mensaje enviado con éxito. Nos pondremos en contacto pronto.';
                  </script>";
            
        } catch (Exception $e) {
            echo "<script>
                    window.location.href = 'contacto.php?status=error&message=Error al enviar el mensaje. Intenta nuevamente.';
                  </script>";
        }
    }
    ?>
</body>
</html>