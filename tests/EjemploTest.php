<?php
use PHPUnit\Framework\TestCase;

class EjemploTest extends TestCase
{
    public function testSuma()
    {
        $resultado = 2 + 3;
        $this->assertEquals(5, $resultado);
    }
}

