<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/databaseprueba.php';

class RegistroPrueba {
    private $conn;

    public function __construct() {
        $database = new DatabasePrueba();
        $this->conn = $database->getConnection();
    }

    // Función para registrar usuario
    public function registrarUsuario($documento, $nombre_completo, $email, $contrasena, $telefono) {
        // Verificar si el email ya existe
        $stmt = $this->conn->prepare("SELECT documento FROM usuarios WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return false; // Usuario ya existe
        }

        // Insertar usuario
        $stmt = $this->conn->prepare("
    INSERT INTO usuarios (documento, nombre_completo, email, contrasena, telefono, Id_Rol)
    VALUES (:documento, :nombre_completo, :email, :contrasena, :telefono, :id_rol)
");
$id_rol = 2; // Usuario estándar
$stmt->bindParam(":id_rol", $id_rol);

        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":nombre_completo", $nombre_completo);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":contrasena", $contrasena);
        $stmt->bindParam(":telefono", $telefono);

        return $stmt->execute();
    }
}

class RegistroTest extends TestCase {
    private $registro;

    protected function setUp(): void {
        $this->registro = new RegistroPrueba();

        // Limpiar usuario de prueba antes de cada test
        $conn = (new DatabasePrueba())->getConnection();
        $conn->exec("DELETE FROM usuarios WHERE email = 'maria@test.com'");
    }

    public function testRegistroExitoso() {
        $resultado = $this->registro->registrarUsuario(
            '87654321',
            'Maria Perez',
            'maria@test.com',
            'abcd1234',
            '3007654321'
        );
        $this->assertTrue($resultado, "El registro debería ser exitoso");
    }

    public function testRegistroUsuarioExistente() {
        // Insertar usuario primero
        $this->registro->registrarUsuario(
            '87654321',
            'Maria Perez',
            'maria@test.com',
            'abcd1234',
            '3007654321'
        );

        // Intentar registrar el mismo email nuevamente
        $resultado = $this->registro->registrarUsuario(
            '87654321',
            'Maria Perez',
            'maria@test.com',
            'abcd1234',
            '3007654321'
        );

        $this->assertFalse($resultado, "No debería permitir registrar un usuario con email repetido");
    }
}
