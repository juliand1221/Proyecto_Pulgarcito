<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="./Styles/login.css"> <!-- Enlace al CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-login">
                    <h2>Iniciar Sesión</h2>
                    <a href="index.php" class="logo-link">
                        <img src="images/Adobe Express - file.png" alt="Logo del Jardín" class="login-icon">
                    </a>
                    <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
                        <div class="alert alert-success">¡Registro exitoso! Ya puedes iniciar sesión.</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'credenciales'): ?>
                        <div class="alert alert-danger" id="errorAlert">❌ Documento o contraseña incorrectos</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['recuperacion']) && $_GET['recuperacion'] == 'enviado'): ?>
                        <div class="alert alert-success" id="successAlert">✅ Se ha enviado un correo con instrucciones para recuperar tu contraseña</div>
                    <?php endif; ?>
                    
                    <form action="procesar_login.php" method="post" onsubmit="return validarFormulario()">
                        <div class="mb-3">
                            <label class="form-label">Documento de Identidad</label>
                            <input type="number" class="form-control" name="documento" id="documento" required>
                        </div>
                        <div class="mb-3 password-container">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="contrasena" id="contrasena" required>
                                <span class="input-group-text toggle-password" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                    </form>
                    
                    <div class="login-links">
                        <p class="text-center">
                            <a href="#" id="forgotPasswordLink">¿Olvidaste tu contraseña?</a>
                        </p>
                        <p class="text-center">
                            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para recuperación de contraseña -->
    <div id="recoveryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Recuperar Contraseña</h3>
            <p>Ingresa tu documento de identidad para recuperar tu contraseña.</p>
            <form id="recoveryForm">
                <div class="mb-3">
                    <label class="form-label">Documento de Identidad</label>
                    <input type="number" class="form-control" id="recoveryDocument" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
        </div>
    </div>

    <script>
        // Mostrar alerta de error si existe
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['error']) && $_GET['error'] == 'credenciales'): ?>
                const errorAlert = document.getElementById('errorAlert');
                if (errorAlert) {
                    errorAlert.style.display = 'block';
                    
                    // Ocultar automáticamente después de 5 segundos
                    setTimeout(() => {
                        errorAlert.style.display = 'none';
                    }, 5000);
                }
            <?php endif; ?>
            
            <?php if (isset($_GET['recuperacion']) && $_GET['recuperacion'] == 'enviado'): ?>
                const successAlert = document.getElementById('successAlert');
                if (successAlert) {
                    successAlert.style.display = 'block';
                    
                    // Ocultar automáticamente después de 5 segundos
                    setTimeout(() => {
                        successAlert.style.display = 'none';
                    }, 5000);
                }
            <?php endif; ?>
        });

        // Validación básica del formulario
        function validarFormulario() {
            const documento = document.getElementById('documento').value;
            const contrasena = document.getElementById('contrasena').value;
            
            if (documento.length < 5) {
                alert('⚠️ El documento debe tener al menos 5 dígitos');
                return false;
            }
            
            if (contrasena.length < 4) {
                alert('⚠️ La contraseña debe tener al menos 4 caracteres');
                return false;
            }
            
            return true;
        }

        // Funcionalidad para mostrar/ocultar contraseña
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#contrasena');
            
            togglePassword.addEventListener('click', function() {
                // Cambiar el tipo de input
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Cambiar el icono
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Modal para recuperación de contraseña
            const modal = document.getElementById("recoveryModal");
            const btn = document.getElementById("forgotPasswordLink");
            const span = document.getElementsByClassName("close")[0];
            const recoveryForm = document.getElementById("recoveryForm");
            
            btn.onclick = function(e) {
                e.preventDefault();
                modal.style.display = "block";
            }
            
            span.onclick = function() {
                modal.style.display = "none";
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            recoveryForm.onsubmit = function(e) {
                e.preventDefault();
                const documento = document.getElementById('recoveryDocument').value;
                
                if (documento.length < 5) {
                    alert('⚠️ El documento debe tener al menos 5 dígitos');
                    return false;
                }
                
                // Crear formulario dinámico para enviar por POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'recuperar_contrasena.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'documento';
                input.value = documento;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
                
                return false;
            };
        });
    </script>
</body>
</html>