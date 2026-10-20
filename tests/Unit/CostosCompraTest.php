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

    /** @dataProvider casosPrecioXml */
    public function testPrecioCompraIncluyeDescuentoXml($xml, $esperado)
    {
        $this->assertEqualsWithDelta($esperado,
            CostosCompra::precioCompraXml(new \SimpleXMLElement($xml)), 0.000001);
    }

    public function casosPrecioXml()
    {
        return [
            'factura 531532' => ['<Detalle><PrcItem>120924.4084</PrcItem><QtyItem>5</QtyItem><DescuentoMonto>62605</DescuentoMonto><MontoItem>542017</MontoItem></Detalle>', 108403.4],
            'sin descuento conserva precision' => ['<Detalle><PrcItem>100.1234</PrcItem><QtyItem>3</QtyItem><MontoItem>300</MontoItem></Detalle>', 100.1234],
            'descuento porcentual' => ['<Detalle><PrcItem>100</PrcItem><QtyItem>2</QtyItem><DescuentoPct>10</DescuentoPct><MontoItem>180</MontoItem></Detalle>', 90],
            'descuento completo' => ['<Detalle><PrcItem>100</PrcItem><QtyItem>2</QtyItem><DescuentoMonto>200</DescuentoMonto><MontoItem>0</MontoItem></Detalle>', 0],
        ];
    }

    public function testDescuentoSinCantidadValidaSeRechaza()
    {
        $this->expectException(\InvalidArgumentException::class);
        CostosCompra::precioCompraXml(new \SimpleXMLElement(
            '<Detalle><PrcItem>100</PrcItem><QtyItem>0</QtyItem><DescuentoMonto>10</DescuentoMonto><MontoItem>90</MontoItem></Detalle>'));
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