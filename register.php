<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Jardín Pulgarcito</title>
    <link rel="stylesheet" href="./Styles/register.css"> <!-- Enlace al CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-register">
                    <div class="logo-container">
                        <a href="index.php" class="logo-link">
                            <img src="images/Adobe Express - file.png" alt="Logo del Jardín" class="login-icon">
                        </a>
                    </div>
                    <h2>Registro de Padres</h2>
                    <form action="procesar_registro.php" method="post" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label">Documento de Identidad</label>
                            <input type="number" class="form-control" name="documento">
                            <div class="invalid-feedback">Por favor ingrese un documento válido</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre_completo" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{5,}">
                            <div class="invalid-feedback">Por favor ingrese su nombre completo</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                            <div class="invalid-feedback">Por favor ingrese un email válido</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" pattern="[0-9]{7,10}">
                            <div class="invalid-feedback">Por favor ingrese un número de teléfono válido</div>
                        </div>
                        
                        <!-- Checkbox de Términos y Condiciones -->
                        <div class="mb-3 terms-container">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terminos" name="terminos" required>
                                <label class="form-check-label" for="terminos">
                                    Acepto los <a href="#" id="verTerminos">Términos y Condiciones</a>
                                </label>
                                <div class="invalid-feedback">Debe aceptar los términos y condiciones</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Registrarse</button>
                    </form>
                    <p class="mt-3 text-center">
                        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Términos y Condiciones -->
    <div id="terminosModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Términos y Condiciones</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <h4>Jardín Infantil Pulgarcito - Términos y Condiciones de Uso</h4>
                
                <h5>1. Aceptación de los Términos</h5>
                <p>Al registrarse en nuestro sistema, usted acepta cumplir con estos términos y condiciones, así como con nuestras políticas de privacidad.</p>
                
                <h5>2. Uso del Sistema</h5>
                <p>El sistema está destinado exclusivamente para:</p>
                <ul>
                    <li>Registro de información de niños matriculados</li>
                    <li>Gestión de documentación requerida</li>
                    <li>Comunicación con la administración del jardín</li>
                    <li>Consulta de estados de matrícula y documentos</li>
                </ul>
                
                <h5>3. Responsabilidades del Usuario</h5>
                <p>Usted se compromete a:</p>
                <ul>
                    <li>Proporcionar información verídica y actualizada</li>
                    <li>Mantener la confidencialidad de su contraseña</li>
                    <li>Notificar cualquier uso no autorizado de su cuenta</li>
                    <li>Subir únicamente documentos válidos y legítimos</li>
                </ul>
                
                <h5>4. Protección de Datos</h5>
                <p>Toda la información personal se maneja de acuerdo con la Ley de Protección de Datos Personales. 
                Los datos de los niños son tratados con máxima confidencialidad y solo se utilizan para fines educativos y administrativos.</p>
                
                <h5>5. Propiedad Intelectual</h5>
                <p>Todo el contenido del sistema es propiedad del Jardín Infantil Pulgarcito y está protegido por derechos de autor.</p>
                
                <h5>6. Limitación de Responsabilidad</h5>
                <p>El jardín no se hace responsable por:</p>
                <ul>
                    <li>Problemas técnicos fuera de nuestro control</li>
                    <li>Uso indebido de las credenciales de acceso por parte del usuario</li>
                    <li>Información incorrecta proporcionada por el usuario</li>
                </ul>
                
                <h5>7. Modificaciones</h5>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Las changes serán notificadas mediante el sistema.</p>
                
                <h5>8. Contacto</h5>
                <p>Para preguntas sobre estos términos, contacte a: administracion@jardinpulgarcito.com</p>
                
                <div class="text-center">
                    <button type="button" id="aceptarTerminos" class="btn btn-primary">Aceptar Términos</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación básica del formulario
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required]');
            const terminos = document.getElementById('terminos');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            // Validar checkbox de términos
            if (!terminos.checked) {
                isValid = false;
                terminos.classList.add('is-invalid');
                document.querySelector('.terms-container .invalid-feedback').style.display = 'block';
            } else {
                terminos.classList.remove('is-invalid');
                document.querySelector('.terms-container .invalid-feedback').style.display = 'none';
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll al primer error
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Validación en tiempo real
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    if (this.type === 'checkbox') {
                        document.querySelector('.terms-container .invalid-feedback').style.display = 'none';
                    }
                }
            });
        });

        // Modal de Términos y Condiciones
        const modal = document.getElementById("terminosModal");
        const btn = document.getElementById("verTerminos");
        const span = document.getElementsByClassName("close")[0];
        const btnAceptar = document.getElementById("aceptarTerminos");
        const checkTerminos = document.getElementById("terminos");

        btn.onclick = function(e) {
            e.preventDefault();
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        btnAceptar.onclick = function() {
            checkTerminos.checked = true;
            checkTerminos.classList.remove('is-invalid');
            document.querySelector('.terms-container .invalid-feedback').style.display = 'none';
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                modal.style.display = "none";
            }
        });
    </script>
</body>
</html>