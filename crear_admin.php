<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Datos del administrador
$admin_data = [
    'documento' => 1144068304,
    'nombre' => 'Administrador Principal',
    'email' => 'judt1221@hotmail.com',
    'password' => 'Hulk-1221', // Cambia esta contraseña
    'telefono' => '3217219328'
];

// Generar hash de la contraseña
$password_hash = password_hash($admin_data['password'], PASSWORD_DEFAULT);

// Insertar en la base de datos
$query = "INSERT INTO Usuarios (
            Documento, Nombre_Completo, Email, Contrasena, 
            Telefono, Fecha_Registro, Estado, Id_Rol
          ) VALUES (
            :documento, :nombre, :email, :contrasena,
            :telefono, CURDATE(), 'activo', 1
          )";

$stmt = $db->prepare($query);
$stmt->bindParam(":documento", $admin_data['documento']);
$stmt->bindParam(":nombre", $admin_data['nombre']);
$stmt->bindParam(":email", $admin_data['email']);
$stmt->bindParam(":contrasena", $password_hash);
$stmt->bindParam(":telefono", $admin_data['telefono']);

try {
    if ($stmt->execute()) {
        echo "✅ Administrador creado exitosamente!<br>";
        echo "Documento: " . $admin_data['documento'] . "<br>";
        echo "Contraseña: " . $admin_data['password'] . "<br>";
        echo "Email: " . $admin_data['email'] . "<br>";
        
        // Eliminar este archivo después de usarlo por seguridad
        // unlink(__FILE__);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "⚠️ El administrador ya existe. Puedes actualizarlo:<br>";
        echo "<a href='phpmyadmin' target='_blank'>Abrir phpMyAdmin</a>";
    } else {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>