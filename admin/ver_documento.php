<?php
require_once '../includes/auth.php';
require_once '../database.php';

$sesion = verificarAuth();
verificarAdmin($sesion);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id_documento = $_GET['id'];

// Obtener información del documento
$query = "SELECT d.*, 
                 td.Nombre_Doc as Tipo_Documento,
                 u.Nombre_Completo as Usuario,
                 n.Nombre_Completo as Niño,
                 d.Ruta_Archivo,
                 d.Nombre_Archivo
          FROM Documentos d
          INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
          INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
          INNER JOIN Usuarios u ON n.Documento = u.Documento
          WHERE d.Id_Documento = :id";

$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id_documento);
$stmt->execute();
$documento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$documento) {
    header("Location: documentos.php?error=Documento no encontrado");
    exit();
}

// Verificar que el archivo existe
if (!file_exists($documento['Ruta_Archivo'])) {
    header("Location: documentos.php?error=El archivo no existe");
    exit();
}

// Determinar el tipo de contenido
$extension = strtolower(pathinfo($documento['Ruta_Archivo'], PATHINFO_EXTENSION));
$content_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
];

$content_type = $content_types[$extension] ?? 'application/octet-stream';

// Mostrar el documento
header('Content-Type: ' . $content_type);
header('Content-Disposition: inline; filename="' . basename($documento['Nombre_Archivo']) . '"');
header('Content-Length: ' . filesize($documento['Ruta_Archivo']));
readfile($documento['Ruta_Archivo']);
exit;
?>