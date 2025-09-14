<?php
function verificarAuth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['documento'])) {
        header("Location: ../login.php");
        exit();
    }
    return $_SESSION;
}

function verificarAdmin($sesion) {
    if ($sesion['rol'] != 1) {
        header("Location: ../user/dashboard.php");
        exit();
    }
}

function verificarUsuario($sesion) {
    if ($sesion['rol'] != 2) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}

// Función para obtener los niños del usuario
function obtenerNinosUsuario($documentoUsuario) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM Ninos WHERE Documento = :documento ORDER BY Nombre_Completo";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}


// Función para contar niños del usuario
function contarNinosUsuario($documentoUsuario) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as total FROM Ninos WHERE Documento = :documento";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Función para contar documentos del usuario
function contarDocumentosUsuario($documentoUsuario) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as total 
              FROM Documentos d
              INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
              WHERE n.Documento = :documento";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Función para contar documentos pendientes del usuario
function contarDocumentosPendientesUsuario($documentoUsuario) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as total 
              FROM Documentos d
              INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
              WHERE n.Documento = :documento AND d.Estado = 'Pendiente'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Función para obtener documentos recientes del usuario
function obtenerDocumentosRecientesUsuario($documentoUsuario, $limite = 5) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT d.*, td.Nombre_Doc, n.Nombre_Completo as Nombre_Nino
              FROM Documentos d
              INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
              INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
              WHERE n.Documento = :documento
              ORDER BY d.Fecha_Carga DESC
              LIMIT :limite";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":documento", $documentoUsuario);
    $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}













// Función para calcular edad
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($nacimiento);
    return $diferencia->y;
}
// Función para obtener todos los documentos pendientes
function obtenerDocumentosPendientes() {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT d.*, td.Nombre_Doc, n.Nombre_Completo as Nombre_Nino, 
                     u.Nombre_Completo as Nombre_Padre, u.Documento as Doc_Padre
              FROM Documentos d
              INNER JOIN Tipos_Doc td ON d.Id_Tipo_Doc = td.id_Tipos_Doc
              INNER JOIN Ninos n ON d.Registro_Civil = n.Registro_Civil
              INNER JOIN Usuarios u ON n.Documento = u.Documento
              WHERE d.Estado = 'Pendiente'
              ORDER BY d.Fecha_Carga ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para actualizar estado de documento
function actualizarEstadoDocumento($id_documento, $estado, $observaciones, $documento_revisor) {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE Documentos 
              SET Estado = :estado, 
                  Observaciones = :observaciones,
                  Documento_Del_Revisor = :documento_revisor,
                  Fecha_Revision = NOW()
              WHERE Id_Documento = :id_documento";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":estado", $estado);
    $stmt->bindParam(":observaciones", $observaciones);
    $stmt->bindParam(":documento_revisor", $documento_revisor);
    $stmt->bindParam(":id_documento", $id_documento);
    
    return $stmt->execute();
}

// Función para obtener estadísticas
function obtenerEstadisticasDocumentos() {
    require_once '../database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT 
                Estado,
                COUNT(*) as cantidad,
                (SELECT COUNT(*) FROM Documentos) as total
              FROM Documentos 
              GROUP BY Estado";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// includes/auth.php o donde tengas tus funciones de base de datos
function obtenerEstadoMatricula($documento_usuario) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "
        SELECT 
            n.Registro_Civil as id_nino,
            n.Nombre_Completo AS nombre_nino,
            COUNT(d.Id_Documento) AS total_documentos,
            SUM(CASE WHEN d.Estado = 'aprobado' THEN 1 ELSE 0 END) AS documentos_aprobados,
            SUM(CASE WHEN d.Estado = 'Rechazo' THEN 1 ELSE 0 END) AS documentos_rechazados
        FROM Ninos n
        LEFT JOIN Documentos d ON n.Registro_Civil = d.Registro_Civil
        WHERE n.Documento = ?
        GROUP BY n.Registro_Civil, n.Nombre_Completo
        ORDER BY n.Nombre_Completo
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $documento_usuario);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>