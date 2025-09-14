<?php
session_start();
require_once 'Database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $conn = $database->getConnection();

    $documento = $_POST['documento'];
    $contrasena = $_POST['contrasena'];

    $query = "SELECT * FROM Usuarios WHERE Documento = :documento";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($contrasena, $usuario['Contrasena'])) {
            $_SESSION['documento'] = $usuario['Documento'];
            $_SESSION['nombre'] = $usuario['Nombre_Completo'];
            $_SESSION['rol'] = $usuario['Id_Rol'];

            if ($usuario['Id_Rol'] == 1) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit();
        } else {
            // Contraseña incorrecta - redirigir al login con error
            header("Location: login.php?error=credenciales");
            exit();
        }
    } else {
        // Usuario no encontrado - redirigir al login con error
        header("Location: login.php?error=credenciales");
        exit();
    }
} else {
    // Si no es POST, redirigir al login
    header("Location: login.php");
    exit();
}
?>