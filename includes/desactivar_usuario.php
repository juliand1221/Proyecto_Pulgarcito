<?php
require_once 'auth.php';
require_once '../database.php';

$sesion = verificarAuth();
if ($sesion['rol'] != 1) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE Usuarios SET Estado = 'inactivo' WHERE Documento = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    
    if ($stmt->execute()) {
        header("Location: ../admin/gestion_usuarios.php?success=Usuario desactivado");
    } else {
        header("Location: ../admin/gestion_usuarios.php?error=Error al desactivar");
    }
}
?>