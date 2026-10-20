<?php
namespace Tests\Unit;
use App\Services\ReconstruccionDetalleBoleta;
use PHPUnit\Framework\TestCase;

class ReconstruccionDetalleBoletaTest extends TestCase
{
    private function xml()
    {
        return new \SimpleXMLElement('<Envio><Documento><Detalle><NroLinDet>1</NroLinDet><NmbItem>DERECHO</NmbItem><QtyItem>1</QtyItem><PrcItem>20000</PrcItem><MontoItem>20000</MontoItem></Detalle><Detalle><NroLinDet>2</NroLinDet><NmbItem>IZQUIERDO</NmbItem><QtyItem>1</QtyItem><PrcItem>20000</PrcItem><MontoItem>20000</MontoItem></Detalle></Documento></Envio>');
    }
    private function cart()
    {
        return [(object)['id'=>10,'item'=>2,'id_repuestos'=>73,'id_unidad_venta'=>0,'id_local'=>1,'descripcion'=>'DERECHO','cantidad'=>1,'pu'=>20000,'total_item'=>20000],
            (object)['id'=>11,'item'=>3,'id_repuestos'=>72,'id_unidad_venta'=>0,'id_local'=>1,'descripcion'=>'IZQUIERDO','cantidad'=>1,'pu'=>20000,'total_item'=>20000]];
    }
    public function testReconstruyeSoloFaltantesYConservaElVinculoDelCarrito()
    {
        $plan=ReconstruccionDetalleBoleta::plan($this->cart(),[],$this->xml());
        $existing=(object)($plan[0]['datos']+['id'=>100]);
        $plan=ReconstruccionDetalleBoleta::plan($this->cart(),[$existing],$this->xml());
        $this->assertSame(100,$plan[0]['detalle_existente_id']);
        $this->assertNull($plan[1]['detalle_existente_id']);
        $this->assertSame(11,$plan[1]['carrito_transferido_id']);
        $this->assertSame(72,$plan[1]['datos']['id_repuestos']);
        $all=[$existing,(object)($plan[1]['datos']+['id'=>101])];
        $repeat=ReconstruccionDetalleBoleta::plan($this->cart(),$all,$this->xml());
        $this->assertSame([100,101],array_column($repeat,'detalle_existente_id'));
    }
    public function testNoSobrescribeDetalleInconsistente()
    {
        $plan=ReconstruccionDetalleBoleta::plan($this->cart(),[],$this->xml());
        $row=(object)($plan[0]['datos']+['id'=>100]);$row->cantidad=2;
        $this->expectException(\RuntimeException::class);
        ReconstruccionDetalleBoleta::plan($this->cart(),[$row],$this->xml());
    }
    public function testNoAceptaCarritoQueNoCuadraConXml()
    {
        $cart=$this->cart();$cart[1]->pu=10000;
        $this->expectException(\RuntimeException::class);
        ReconstruccionDetalleBoleta::plan($cart,[],$this->xml());
    }
}