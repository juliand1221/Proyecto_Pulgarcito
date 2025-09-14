<?php
function subirDocumento($archivo, $datos, $documentoUsuario) {
    $uploadDir = '../uploads/';
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    // Crear carpeta si no existe
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'error' => 'No se pudo crear la carpeta de uploads'];
        }
    }
    
    // Verificar errores de subida
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error en la subida del archivo: ' . $archivo['error']];
    }
    
    // Validar tipo de archivo
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido. Use: ' . implode(', ', $allowedTypes)];
    }
    
    // Validar tamaño
    if ($archivo['size'] > $maxSize) {
        return ['success' => false, 'error' => 'El archivo es demasiado grande. Máximo: 10MB'];
    }
    
    // Generar nombre único
    $nombreUnico = uniqid() . '_' . time() . '.' . $extension;
    $rutaCompleta = $uploadDir . $nombreUnico;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        // Guardar en base de datos
        require_once '../database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO Documentos 
                 (Nombre_Archivo, Ruta_Archivo, Fecha_Carga, Estado, Observaciones, Registro_Civil, Id_Tipo_Doc) 
                 VALUES (:nombre, :ruta, NOW(), 'Pendiente', :observaciones, :registro_civil, :tipo_doc)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nombre', $archivo['name']);
        $stmt->bindParam(':ruta', $rutaCompleta);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':registro_civil', $datos['registro_civil']);
        $stmt->bindParam(':tipo_doc', $datos['tipo_documento']);
        
        if ($stmt->execute()) {
            // Crear relación en Usuarios_Documentos
            $idDocumento = $db->lastInsertId();
            $queryRelacion = "INSERT INTO Usuarios_Documentos (Documento, Id_Documento) VALUES (:documento, :id_documento)";
            $stmtRelacion = $db->prepare($queryRelacion);
            $stmtRelacion->bindParam(':documento', $documentoUsuario);
            $stmtRelacion->bindParam(':id_documento', $idDocumento);
            $stmtRelacion->execute();
            
            return ['success' => true, 'id' => $idDocumento, 'ruta' => $rutaCompleta];
        } else {
            // Eliminar archivo si falla la BD
            unlink($rutaCompleta);
            return ['success' => false, 'error' => 'Error al guardar en base de datos'];
        }
    } else {
        return ['success' => false, 'error' => 'Error al mover el archivo'];
    }
}

// Función para obtener documentos del usuario
function obtenerDocumentosUsuario($documentoUsuario) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT d.*, td.Nombre_Doc, n.Nombre_Completo as Nombre_Nino 
              FROM Documentos d
              INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
              INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
              INNER JOIN Usuarios_Documentos ud ON d.Id_Documento = ud.Id_Documento
              WHERE ud.Documento = :documento
              ORDER BY d.Fecha_Carga DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>