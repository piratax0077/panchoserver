<?php
namespace Tests\Unit;
use App\Services\PrecioOferta;
use PHPUnit\Framework\TestCase;

class PrecioOfertaTest extends TestCase
{
    /** @dataProvider preciosValidos */
    public function testCalculaDescuentosYRedondeaPesos($precio, $tipo, $valor, $esperado)
    {
        $this->assertSame($esperado, PrecioOferta::calcular($precio, $tipo, $valor));
    }
    public function preciosValidos()
    {
        return [
            [10000,'monto',1500,['descuento'=>1500,'precio_actualizado'=>8500]],
            [19990,'porcentaje',12.5,['descuento'=>2499,'precio_actualizado'=>17491]],
            [1000,'porcentaje',10,['descuento'=>100,'precio_actualizado'=>900]],
        ];
    }
    /** @dataProvider preciosInvalidos */
    public function testRechazaPreciosQueNoDejanUnValorPositivo($precio, $tipo, $valor)
    {
        $this->expectException(\InvalidArgumentException::class);
        PrecioOferta::calcular($precio, $tipo, $valor);
    }
    public function preciosInvalidos()
    {
        return [[1000,'monto',1000],[1000,'monto',2000],[1000,'porcentaje',100],
            [1000,'porcentaje',0],[1000,'monto',-1],[1000,'monto','abc'],
            [0,'monto',5],[1000,'otro',10],[1,'porcentaje',1],[1000,'monto',INF]];
    }
}
