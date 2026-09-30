<?php
namespace Tests\Unit;

use App\Services\NormalizadorStockLegado;
use PHPUnit\Framework\TestCase;

class NormalizadorStockLegadoTest extends TestCase
{
    private function fila(array $valores = [])
    {
        return (object) array_merge(['stock_actual' => 4, 'stock_actual_dos' => 2, 'stock_actual_tres' => 0,
            'local_id' => 1, 'local_id_dos' => 3, 'local_id_tres' => 4], $valores);
    }

    public function testNegativosQuedanEnCeroSinCambiarPositivos()
    {
        $this->assertSame(['stock_actual_tres' => 0], (new NormalizadorStockLegado)->cambios($this->fila(['stock_actual_tres' => -5])));
    }

    public function testDuplicadosSeReasignanPorColumnaSinSumarStocks()
    {
        $this->assertSame(['local_id_dos' => 3], (new NormalizadorStockLegado)->cambios($this->fila(['local_id_dos' => 1])));
    }

    public function testLocalVacioConStockQuedaAsignadoAunqueSeaNegativo()
    {
        $this->assertSame(['stock_actual_tres' => 0, 'local_id_tres' => 4], (new NormalizadorStockLegado)->cambios($this->fila(['local_id_tres' => null, 'stock_actual_tres' => -1])));
    }

    public function testAsociacionExplicitaValidaSeConserva()
    {
        $this->assertSame([], (new NormalizadorStockLegado)->cambios($this->fila(['local_id' => 3, 'local_id_dos' => 1])));
    }

    public function testNormalizacionEsIdempotente()
    {
        $fila = $this->fila(['local_id_dos' => 1, 'stock_actual_tres' => -1, 'local_id_tres' => null]);
        $service = new NormalizadorStockLegado;
        foreach ($service->cambios($fila) as $campo => $valor) $fila->$campo = $valor;
        $this->assertSame([], $service->cambios($fila));
    }
}
