<?php
require_once __DIR__ . '/databaseprueba.php';

class AuthPrueba {
    private $conn;

    public function __construct() {
        $database = new DatabasePrueba();
        $this->conn = $database->getConnection();
    }

    public function login($documento, $contrasena) {
        $query = "SELECT * FROM usuarios 
                  WHERE documento = :documento AND contrasena = :contrasena 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":contrasena", $contrasena);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}


