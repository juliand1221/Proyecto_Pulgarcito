<?php
require_once 'database.php';

// Datos específicos del administrador
$documento_admin = "1144068304";
$nueva_contrasena = "admin123456"; // Tu nueva contraseña
$email_admin = "jdt1221@gmail.com";

// Encriptar la nueva contraseña
$contrasena_hasheada = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

// Conexión a la base de datos
$database = new Database();
$conn = $database->getConnection();

try {
    // Query para actualizar la contraseña usando el documento como llave primaria
    $query = "UPDATE Usuarios SET Contrasena = :contrasena WHERE Documento = :documento";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':contrasena', $contrasena_hasheada);
    $stmt->bindParam(':documento', $documento_admin);
    
    if ($stmt->execute()) {
        echo "✅ Contraseña del administrador actualizada correctamente";
        echo "<br><strong>Documento:</strong> " . $documento_admin;
        echo "<br><strong>Email:</strong> " . $email_admin;
        echo "<br><strong>Nueva contraseña:</strong> " . $nueva_contrasena;
        echo "<br><strong>Contraseña encriptada:</strong> " . $contrasena_hasheada;
        
        // Verificación adicional
        echo "<br><br>🔍 <strong>Verificación:</strong>";
        $query_verify = "SELECT Documento, Email, Contrasena FROM Usuarios WHERE Documento = :documento";
        $stmt_verify = $conn->prepare($query_verify);
        $stmt_verify->bindParam(':documento', $documento_admin);
        $stmt_verify->execute();
        
        $usuario = $stmt_verify->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            echo "<br>✅ Usuario encontrado: " . $usuario['Email'];
            if (password_verify($nueva_contrasena, $usuario['Contrasena'])) {
                echo "<br>✅ Contraseña verificada correctamente";
            } else {
                echo "<br>❌ Error en la verificación de contraseña";
            }
        }
    } else {
        echo "❌ Error al actualizar la contraseña";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    
    // Información adicional para debugging
    echo "<br><br>📋 <strong>Debug info:</strong>";
    echo "<br>Documento: " . $documento_admin;
    echo "<br>Email: " . $email_admin;
    echo "<br>Nueva contraseña: " . $nueva_contrasena;
}
?>