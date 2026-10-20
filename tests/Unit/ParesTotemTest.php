<?php
namespace Tests\Unit;

use App\Http\Controllers\api_controlador;
use PHPUnit\Framework\TestCase;

class ParesTotemTest extends TestCase
{
    private function invoke($method, ...$args)
    {
        $reflection = new \ReflectionMethod(api_controlador::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs(new api_controlador, $args);
    }

    private function product($id, $description, $family = 344, $brand = 'BHORKE')
    {
        return (object) ['id' => $id, 'descripcion' => $description, 'id_familia' => $family,
            'marcarepuesto' => $brand, 'nombrefamilia' => 'AMORTIGUADOR', 'nombre_pais' => 'CHINA',
            'stock_actual' => 3, 'codigo_interno' => 'AMD53'];
    }

    public function testUniversalUsesSameCodeForBothPositions()
    {
        $p = $this->product(53, 'AMORTIGUADOR DELANTERO');
        $pairs = $this->invoke('armarParesDerechoIzquierdo', [$p]);
        $this->assertCount(1, $pairs);
        $this->assertEquals($p, $pairs[0]['derecho']);
        $this->assertEquals($p, $pairs[0]['izquierdo']);
        $this->assertNotSame($pairs[0]['derecho'], $pairs[0]['izquierdo']);
        $this->assertTrue($this->invoke('cantidadAmortiguadorValida', $p, 2));
        $this->assertFalse($this->invoke('cantidadAmortiguadorValida', $p, 3));
        $this->assertFalse($this->invoke('cantidadAmortiguadorValida', $p, 1.5));
        $this->assertFalse($this->invoke('cantidadAmortiguadorValida', $p, 0));
    }

    public function testExplicitSidesRemainOneUnitAndAcceptAbbreviations()
    {
        foreach (['DERECHO', 'IZQUIERDA', 'DER.', 'LH'] as $side) {
            $p = $this->product(1, 'AMORTIGUADOR '.$side);
            $this->assertTrue($this->invoke('cantidadAmortiguadorValida', $p, 1));
            $this->assertFalse($this->invoke('cantidadAmortiguadorValida', $p, 2));
        }
        $this->assertNull($this->invoke('detectarLadoPar', 'AMORTIGUADOR DERECHO E IZQUIERDO'));
    }

    public function testSuspensionTraysPairBySideAndBrand()
    {
        foreach (['SUPERIOR', 'INFERIOR'] as $position) {
            $this->assertTrue($this->invoke('familiaUsaPares', (object) [
                'id' => 999, 'nombrefamilia' => 'BANDEJA DE SUSPENSION '.$position]));
            $right = $this->product(1, 'BANDEJA '.$position.' DERECHA COMPLETA', 999);
            $left = $this->product(2, 'BANDEJA '.$position.' IZQUIERDA COMPLETA', 999);
            $otherBrand = $this->product(3, $right->descripcion, 999, 'HYNOVA');
            $pairs = $this->invoke('armarParesDerechoIzquierdo', [$otherBrand, $left, $right]);
            $this->assertTrue($pairs[0]['izquierdo']->no_disponible);
            $this->assertSame(1, $pairs[1]['derecho']->id);
            $this->assertSame(2, $pairs[1]['izquierdo']->id);
        }
    }

    public function testRetenesPairBySideAndLeaveNeutralProductsSingle()
    {
        $this->assertTrue($this->invoke('familiaUsaPares', (object) [
            'id' => 87, 'nombrefamilia' => 'RETEN']));
        foreach ([['DERECHO', 'IZQUIERDO'], ['RH', 'LH']] as $sides) {
            $right = $this->product(28, 'RETEN HOMOCINETICA '.$sides[0], 87);
            $left = $this->product(29, 'RETEN HOMOCINETICA '.$sides[1], 87);
            $neutral = $this->product(107, 'RETEN HOMOCINETICA', 87);
            $pairs = $this->invoke('armarParesDerechoIzquierdo', [$left, $neutral, $right]);
            $this->assertCount(2, $pairs);
            $this->assertSame(28, $pairs[0]['derecho']->id);
            $this->assertSame(29, $pairs[0]['izquierdo']->id);
            $this->assertSame($neutral, $pairs[1]);
        }
    }

    public function testOtherNeutralProductsStaySingleAndFamiliesDoNotMix()
    {
        $p = $this->product(1, 'DISCO FRENO', 374);
        $this->assertSame([$p], $this->invoke('armarParesDerechoIzquierdo', [$p]));
        $pairs = $this->invoke('armarParesDerechoIzquierdo', [
            $this->product(1, 'BANDEJA DERECHA', 998), $this->product(2, 'BANDEJA IZQUIERDA', 999)]);
        $this->assertCount(2, $pairs);
        $this->assertTrue($pairs[0]['izquierdo']->no_disponible);
        $this->assertTrue($pairs[1]['derecho']->no_disponible);
    }
}