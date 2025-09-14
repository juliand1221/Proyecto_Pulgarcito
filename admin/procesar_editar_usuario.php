<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();

    // Recoger y validar datos
    $documento_original = $_POST['documento_original'];
    $documento = trim($_POST['documento']);
    $nombre = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $id_rol = $_POST['id_rol'];
    $estado = $_POST['estado'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $fecha_registro = $_POST['fecha_registro'];

    // Verificar si el nuevo documento o email ya existen (excluyendo el usuario actual)
    $query_check = "SELECT Documento FROM Usuarios 
                    WHERE (Documento = ? OR Email = ?) AND Documento != ?";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bindValue(1, $documento, PDO::PARAM_INT);
    $stmt_check->bindValue(2, $email, PDO::PARAM_STR);
    $stmt_check->bindValue(3, $documento_original, PDO::PARAM_INT);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        header("Location: editar_usuario.php?id=" . $documento_original . "&error=El documento o email ya existe");
        exit();
    }

    // Construir la consulta de actualización
    if (!empty($nueva_contrasena)) {
        // Si se proporciona nueva contraseña
        $password_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $query = "UPDATE Usuarios 
                  SET Documento = ?, Nombre_Completo = ?, Email = ?, 
                      Telefono = ?, Id_Rol = ?, Estado = ?, Contrasena = ?
                  WHERE Documento = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $documento, PDO::PARAM_INT);
        $stmt->bindValue(2, $nombre, PDO::PARAM_STR);
        $stmt->bindValue(3, $email, PDO::PARAM_STR);
        $stmt->bindValue(4, $telefono, PDO::PARAM_STR);
        $stmt->bindValue(5, $id_rol, PDO::PARAM_INT);
        $stmt->bindValue(6, $estado, PDO::PARAM_STR);
        $stmt->bindValue(7, $password_hash, PDO::PARAM_STR);
        $stmt->bindValue(8, $documento_original, PDO::PARAM_INT);
    } else {
        // Sin cambiar contraseña
        $query = "UPDATE Usuarios 
                  SET Documento = ?, Nombre_Completo = ?, Email = ?, 
                      Telefono = ?, Id_Rol = ?, Estado = ?
                  WHERE Documento = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $documento, PDO::PARAM_INT);
        $stmt->bindValue(2, $nombre, PDO::PARAM_STR);
        $stmt->bindValue(3, $email, PDO::PARAM_STR);
        $stmt->bindValue(4, $telefono, PDO::PARAM_STR);
        $stmt->bindValue(5, $id_rol, PDO::PARAM_INT);
        $stmt->bindValue(6, $estado, PDO::PARAM_STR);
        $stmt->bindValue(7, $documento_original, PDO::PARAM_INT);
    }

    try {
        if ($stmt->execute()) {
            // Si cambió el documento, redirigir con el nuevo ID
            $redirect_id = ($documento != $documento_original) ? $documento : $documento_original;
            header("Location: editar_usuario.php?id=" . $redirect_id . "&success=Usuario actualizado correctamente");
            exit();
        } else {
            header("Location: editar_usuario.php?id=" . $documento_original . "&error=Error al actualizar el usuario");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: editar_usuario.php?id=" . $documento_original . "&error=Error de base de datos: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: gestion_usuarios.php");
    exit();
}
?>