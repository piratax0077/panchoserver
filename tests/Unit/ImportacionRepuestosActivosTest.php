<?php

namespace Tests\Unit;

use App\Http\Controllers\factuprodu_controlador;
use PHPUnit\Framework\TestCase;

class ImportacionRepuestosActivosTest extends TestCase
{
    private function buscar(array $repuestos, $codigo)
    {
        $controller = (new \ReflectionClass(factuprodu_controlador::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($controller, 'buscarRepuestoExistentePorCodigo');
        $method->setAccessible(true);

        return $method->invoke($controller, collect($repuestos), $codigo);
    }

    public function testSeleccionaActivoAunqueElInactivoAparezcaPrimero()
    {
        $inactivo = (object) ['id' => 1, 'activo' => 0, 'cod_repuesto_proveedor' => 'ABC123'];
        $activo = (object) ['id' => 2, 'activo' => '1', 'cod_repuesto_proveedor' => 'ABC123'];

        $this->assertSame($activo, $this->buscar([$inactivo, $activo], 'ABC123'));
    }

    public function testNoAsociaUnCodigoQueSoloTieneRepuestosInactivos()
    {
        $inactivo = (object) ['id' => 1, 'activo' => 0, 'cod_repuesto_proveedor' => 'ABC123'];

        $this->assertNull($this->buscar([$inactivo], 'ABC123'));
    }

    public function testConservaCoincidenciaExactaConMultiplesCodigos()
    {
        $parcial = (object) ['id' => 1, 'activo' => 1, 'cod_repuesto_proveedor' => 'ABC1234'];
        $exacto = (object) ['id' => 2, 'activo' => 1, 'cod_repuesto_proveedor' => 'OTRO / abc123; XYZ'];

        $this->assertSame($exacto, $this->buscar([$parcial, $exacto], ' abc123 '));
    }
}