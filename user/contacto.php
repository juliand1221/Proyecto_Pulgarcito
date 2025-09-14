<?php
require_once '../includes/auth.php';
$sesion = verificarAuth();
verificarUsuario($sesion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="../Styles/userdashboard.css">
    <link rel="stylesheet" href="../Styles/contacto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Adobe Express - file.png" alt="Logo" width="30">
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
                        <a href="registrar_nino.php" class="list-group-item">Registrar Niño</a>
                        <a href="subir_documento.php" class="list-group-item">Subir Documentos</a>
                        <a href="mis_documentos.php" class="list-group-item">Mis Documentos</a>
                        <a href="perfil.php" class="list-group-item">Mi Perfil</a>
                        <a href="contacto.php" class="list-group-item active">Contacto</a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <!-- Tarjeta de contacto -->
                <div class="card">
                    <div class="card-header">Contacto - Jardín Pulgarcito</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="contact-info">
                                    <h3><i class="fas fa-info-circle"></i> Información de Contacto</h3>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>Email: jdt1221@gmail.com</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span>Teléfono: (602) 373-0133</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>Celular: (321) 721-9328</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Dirección: Cra 26 I3 # 121-123, Cali</span>
                                    </div>
                                </div>
                                
                                <div class="social-media mt-4">
                                    <h3><i class="fas fa-share-alt"></i> Síguenos en Redes Sociales o Escribenos</h3>
                                    <div class="social-icons">
                                        <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="whatsapp-section">
                                    <h3><i class="fab fa-whatsapp"></i> Contáctanos por WhatsApp</h3>
                                    <p>Envíanos un mensaje directo a nuestro WhatsApp para consultas rápidas:</p>
                                    
                                    <div class="whatsapp-form">
                                        <div class="form-group">
                                            <label for="name">Nombre:</label>
                                            <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($sesion['nombre']); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="message">Mensaje:</label>
                                            <textarea id="message" class="form-control" rows="4" placeholder="Escribe tu mensaje aquí..."></textarea>
                                        </div>
                                        <button id="whatsapp-btn" class="btn btn-success w-100">
                                            <i class="fab fa-whatsapp"></i> Enviar por WhatsApp
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="business-hours mt-4">
                                    <h3><i class="fas fa-clock"></i> Horario de Atención</h3>
                                    <ul class="hours-list">
                                        <li>Lunes a Viernes: 7:00 AM - 6:00 PM</li>
                                        <li>Sábados: 8:00 AM - 1:00 PM</li>
                                        <li>Domingos: Cerrado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const whatsappBtn = document.getElementById('whatsapp-btn');
            
            whatsappBtn.addEventListener('click', function() {
                const message = document.getElementById('message').value;
                const name = document.getElementById('name').value;
                
                if (message.trim() === '') {
                    alert('Por favor, escribe un mensaje antes de enviar.');
                    return;
                }
                
                // Número de teléfono (cambiar por el número real del jardín)
                const phoneNumber = "3217219328";
                
                // Mensaje codificado para URL
                const encodedMessage = encodeURIComponent(`Hola, soy ${name}. ${message}`);
                
                // Abrir WhatsApp con el mensaje
                window.open(`https://wa.me/${phoneNumber}?text=${encodedMessage}`, '_blank');
            });
        });
    </script>
</body>
</html>