<?php
class DatabasePrueba {
    private $host = "localhost";
    private $db_name = "proyecto_pulgarcito_test"; // ⚡ Base de datos de pruebas
    private $username = "root"; // cambia si tienes usuario
    private $password = "";     // cambia si tienes password
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
