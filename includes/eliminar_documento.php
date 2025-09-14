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
    
    // Primero obtener la ruta del archivo
    $query = "SELECT Ruta_Archivo FROM Documentos WHERE Id_Documento = ?";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($documento) {
        // Eliminar el archivo físico
        if (file_exists($documento['Ruta_Archivo'])) {
            unlink($documento['Ruta_Archivo']);
        }
        
        // Eliminar de la base de datos
        $query_delete = "DELETE FROM Documentos WHERE Id_Documento = ?";
        $stmt_delete = $db->prepare($query_delete);
        $stmt_delete->bindValue(1, $_GET['id'], PDO::PARAM_INT);
        
        if ($stmt_delete->execute()) {
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../admin/gestion_ninos.php';
            header("Location: $redirect?success=Documento eliminado correctamente");
        } else {
            header("Location: ../admin/gestion_ninos.php?error=Error al eliminar el documento");
        }
    } else {
        header("Location: ../admin/gestion_ninos.php?error=Documento no encontrado");
    }
} else {
    header("Location: ../admin/gestion_ninos.php?error=ID no especificado");
}
?>