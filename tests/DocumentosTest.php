<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/DocumentosPrueba.php';

class DocumentosTest extends TestCase {
    private $documentos;

    protected function setUp(): void {
        $this->documentos = new DocumentosPrueba();

        // Limpiar documentos de prueba antes de cada test
        $conn = (new DatabasePrueba())->getConnection();
        $conn->exec("DELETE FROM documentos WHERE nombre_archivo = 'archivo_test.pdf'");
    }

    public function testSubidaDocumentoExitosa() {
        $resultado = $this->documentos->subirDocumento('archivo_test.pdf');
        $this->assertTrue($resultado, "El documento debería subirse correctamente");
    }

    public function testSubidaDocumentoDuplicado() {
        // Insertar documento primero
        $this->documentos->subirDocumento('archivo_test.pdf');

        // Intentar subir el mismo documento nuevamente
        $resultado = $this->documentos->subirDocumento('archivo_test.pdf');
        $this->assertFalse($resultado, "No debería permitir subir un documento duplicado");
    }
}
