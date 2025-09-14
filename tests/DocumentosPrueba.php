<?php
require_once __DIR__ . '/databaseprueba.php';

class DocumentosPrueba {
    private $conn;

    public function __construct() {
        $database = new DatabasePrueba();
        $this->conn = $database->getConnection();
    }

    // Función para "subir" documento
    public function subirDocumento($nombre_archivo) {
        // Evitar duplicados (opcional)
        $stmt = $this->conn->prepare("SELECT id_documento FROM documentos WHERE nombre_archivo = :nombre");
        $stmt->bindParam(":nombre", $nombre_archivo);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return false; // Documento ya existe
        }

        // Insertar documento
        $stmt = $this->conn->prepare("INSERT INTO documentos (nombre_archivo) VALUES (:nombre)");
        $stmt->bindParam(":nombre", $nombre_archivo);
        return $stmt->execute();
    }
}
