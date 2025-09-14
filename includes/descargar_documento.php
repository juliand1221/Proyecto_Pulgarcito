<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../database.php';

$sesion = verificarAuth();
if ($sesion['rol'] != 1) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT Nombre_Archivo, Ruta_Archivo FROM Documentos WHERE Id_Documento = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($documento && file_exists($documento['Ruta_Archivo'])) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($documento['Nombre_Archivo']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($documento['Ruta_Archivo']));
        readfile($documento['Ruta_Archivo']);
        exit;
    } else {
        header("Location: ../admin/documentos.php?error=Documento no encontrado");
        exit;
    }
} else {
    header("Location: ../admin/documentos.php?error=ID no especificado");
    exit;
}
?>