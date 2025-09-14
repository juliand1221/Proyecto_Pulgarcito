<?php
require_once 'Database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $conn = $database->getConnection();

    // Recoger y validar datos
    $documento = $_POST['documento'];
    $nombre_completo = $_POST['nombre_completo'];
    $email = $_POST['email'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $telefono = $_POST['telefono'];
    $fecha_registro = date('Y-m-d');
    $estado = 'activo';
    $id_rol = 2; // Rol de Padre

    // Verificar si el usuario ya existe
    $query = "SELECT Documento FROM Usuarios WHERE Documento = :documento OR Email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':documento', $documento);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "El usuario ya existe.";
    } else {
        // Insertar nuevo usuario
        $query = "INSERT INTO Usuarios (Documento, Nombre_Completo, Email, Contrasena, Telefono, Fecha_Registro, Estado, Id_Rol) 
                  VALUES (:documento, :nombre_completo, :email, :contrasena, :telefono, :fecha_registro, :estado, :id_rol)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':fecha_registro', $fecha_registro);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_rol', $id_rol);

        if ($stmt->execute()) {
            header("Location: login.php?registro=exitoso");
            exit();
        } else {
            echo "Error al registrar.";
        }
    }
}
?>