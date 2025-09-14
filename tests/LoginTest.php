<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/authprueba.php';

class LoginTest extends TestCase {

    public function testLoginExitoso() {
        $auth = new AuthPrueba();

        // ⚡ Aquí coloca un documento y contraseña que EXISTAN en tu base de datos de pruebas
        $resultado = $auth->login("1144068304", "Hulk-1221");
        $this->assertTrue($resultado, "El login debería ser exitoso con credenciales válidas");
    }

    public function testLoginFallido() {
        $auth = new AuthPrueba();

        $resultado = $auth->login("00000000", "incorrecta");
        $this->assertFalse($resultado, "El login debería fallar con credenciales incorrectas");
    }
}


