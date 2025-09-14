<?php

function enviarNotificacion($documento_usuario, $titulo, $mensaje) {
    require_once 'database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO Notificaciones 
              (Titulo, Mensaje, Fecha_Envio, Leido, Tipo, Documento_Usuario) 
              VALUES (:titulo, :mensaje, NOW(), 'No', :tipo, :documento)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindParam(":mensaje", $mensaje);
    $stmt->bindParam(":tipo", $titulo); // Puedes usar el título como tipo
    $stmt->bindParam(":documento", $documento_usuario);
    
    return $stmt->execute();
}
?>