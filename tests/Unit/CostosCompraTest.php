<?php

namespace Tests\Unit;

use App\Services\CostosCompra;
use PHPUnit\Framework\TestCase;

class CostosCompraTest extends TestCase
{
    /**
     * @dataProvider casosFlete
     */
    public function testConservaElFleteUnitarioDelXml($unitario, $cantidad, $total)
    {
        $guardado = CostosCompra::fleteUnitario($unitario);
        $this->assertEqualsWithDelta($total, $guardado * $cantidad, 0.00001);
    }

    public function casosFlete()
    {
        return [
            'una unidad' => ['1352.42', 1, 1352.42],
            '36 unidades sin multiplicar dos veces' => ['1352.42', 36, 48687.12],
            'caja convertida a 12 unidades' => ['125.25', 12, 1503.00],
            'sin transporte' => ['0', 36, 0.0],
            'decimales' => ['44.53', 10, 445.30],
        ];
    }

    public function testElFleteEsElDelXmlSinAplicarUtilidadNiPrecioVenta()
    {
        $this->assertSame(250.75, CostosCompra::fleteUnitario('250.75'));
    }

    public function testRedondeaSoloACentavos()
    {
        $this->assertSame(123.46, CostosCompra::fleteUnitario('123.456'));
    }

    /**
     * @dataProvider valoresInvalidos
     */
    public function testRechazaFleteAusenteOInvalido($valor)
    {
        $this->expectException(\InvalidArgumentException::class);
        CostosCompra::fleteUnitario($valor);
    }

    public function valoresInvalidos()
    {
        return [['incorrecto'], [-1], [null], [''], [INF]];
    }
}