<?php
namespace Tests\Feature;

use App\Http\Controllers\ventas_controlador;
use App\oferta_pagina_web;
use App\repuesto;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfertasLoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Dedicated in-memory connection: never run these fixtures against business data.
        config(['database.default' => 'ofertas_test', 'database.connections.ofertas_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        Schema::create('repuestos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('precio_venta');
            $table->integer('activo')->default(1);
            $table->integer('oferta')->default(0);
            $table->timestamps();
        });
        Schema::create('ofertas_pagina_web', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_repuesto');
            $table->integer('usuario_id');
            $table->integer('precio_antiguo');
            $table->integer('precio_actualizado');
            $table->integer('descuento');
            $table->integer('local');
            $table->date('desde');
            $table->date('hasta');
            $table->integer('activo');
            $table->timestamps();
        });
        Carbon::setTestNow('2026-10-06 12:00:00');
        Auth::shouldReceive('id')->andReturn(9);
        foreach ([10000, 20000] as $precio) {
            $producto = new repuesto;
            $producto->precio_venta = $precio;
            $producto->activo = 1;
            $producto->oferta = 0;
            $producto->save();
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function ejecutar(array $datos, $permitido = true)
    {
        $controller = new class($permitido) extends ventas_controlador {
            private $permitido;
            public function __construct($permitido) { $this->permitido = $permitido; }
            public function confirmaringreso($path) { return $this->permitido; }
        };
        return $controller->ofertas_lote(Request::create('/ventas/ofertas/lote', 'POST', $datos));
    }

    private function crear()
    {
        return $this->ejecutar(['accion'=>'crear', 'ids'=>[1,2], 'desde'=>'2026-10-01', 'hasta'=>'2026-10-31',
            'local'=>6, 'tipo_descuento'=>'porcentaje', 'valor_descuento'=>10]);
    }

    public function testCreaOfertasConPrecioCanalYVigenciaComunes()
    {
        $this->assertSame(2, $this->crear()->getData()->cantidad);
        $this->assertEquals([9000,18000], oferta_pagina_web::orderBy('id')->pluck('precio_actualizado')->all());
        $this->assertEquals([6,6], oferta_pagina_web::pluck('local')->all());
        $this->assertEquals([1,1], oferta_pagina_web::pluck('activo')->all());
        $this->assertEquals([1,1], repuesto::pluck('oferta')->all());
    }

    public function testEditarSoloCanalConservaFechasYPrecios()
    {
        $this->crear();
        $antes = oferta_pagina_web::first();
        $this->ejecutar(['accion'=>'editar','ids'=>[1,2],'local'=>2]);
        $despues = oferta_pagina_web::first();
        $this->assertEquals($antes->desde, $despues->desde);
        $this->assertEquals($antes->hasta, $despues->hasta);
        $this->assertEquals($antes->precio_actualizado, $despues->precio_actualizado);
        $this->assertEquals(2, $despues->local);
    }

    public function testDescuentoSeCalculaSobreBaseSinAcumularDescuentos()
    {
        $this->crear();
        $this->ejecutar(['accion'=>'editar','ids'=>[1,2],'tipo_descuento'=>'porcentaje','valor_descuento'=>20]);
        $this->assertEquals([8000,16000], oferta_pagina_web::orderBy('id')->pluck('precio_actualizado')->all());
    }

    public function testUnaOfertaInvalidaRevierteTodoElLote()
    {
        $this->crear();
        oferta_pagina_web::where('id',2)->update(['precio_antiguo'=>1000]);
        try {
            $this->ejecutar(['accion'=>'editar','ids'=>[1,2],'local'=>1,'tipo_descuento'=>'monto','valor_descuento'=>1500]);
            $this->fail('Expected invalid discount.');
        } catch (ValidationException $e) {
            $this->assertEquals([6,6], oferta_pagina_web::pluck('local')->all());
            $this->assertEquals([9000,18000], oferta_pagina_web::orderBy('id')->pluck('precio_actualizado')->all());
        }
    }

    public function testFechaParcialInvalidaNoGuardaNingunaOferta()
    {
        $this->crear();
        oferta_pagina_web::where('id',2)->update(['hasta'=>'2026-10-05']);
        try {
            $this->ejecutar(['accion'=>'editar','ids'=>[1,2],'desde'=>'2026-10-10']);
            $this->fail('Expected invalid dates.');
        } catch (ValidationException $e) {
            $this->assertEquals(['2026-10-01','2026-10-01'], oferta_pagina_web::pluck('desde')->all());
        }
    }

    public function testNoDuplicaOfertasExistentes()
    {
        $this->crear();
        try { $this->crear(); $this->fail('Expected duplicate rejection.'); }
        catch (ValidationException $e) { $this->assertSame(2, oferta_pagina_web::count()); }
    }

    public function testOfertaFuturaSeCreaInactiva()
    {
        $this->ejecutar(['accion'=>'crear','ids'=>[1],'desde'=>'2026-11-01','hasta'=>'2026-11-30',
            'local'=>2,'tipo_descuento'=>'monto','valor_descuento'=>1000]);
        $this->assertEquals(0, oferta_pagina_web::first()->activo);
    }

    public function testRechazaRegistrosAusentesSinCambios()
    {
        $this->crear();
        try {
            $this->ejecutar(['accion'=>'editar','ids'=>[1,999],'local'=>1]);
            $this->fail('Expected missing record rejection.');
        } catch (ValidationException $e) { $this->assertEquals([6,6], oferta_pagina_web::pluck('local')->all()); }
    }

    public function testRequierePermisoDeOfertas()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ejecutar(['accion'=>'editar','ids'=>[1],'local'=>1], false);
    }

    public function testRechazaCanalInvalido()
    {
        $this->expectException(ValidationException::class);
        $this->ejecutar(['accion'=>'editar','ids'=>[1],'local'=>7]);
    }

    public function testLaVistaRenderizaOfertasYModalesConIdentificadoresUnicos()
    {
        $ofertas = collect([(object) [
            'id'=>1,'id_repuesto'=>1,'codigo_interno'=>'ABC','descripcion'=>'Producto <especial>',
            'name'=>'Usuario','desde'=>'01-10-2026','hasta'=>'31-10-2026','local'=>6,
            'precio_antiguo'=>10000,'precio_actualizado'=>9000,'descuento'=>1000,
        ]]);
        $source = file_get_contents(resource_path('views/ventas/ofertas.blade.php'));
        $source = preg_replace('/@extends\([^\r\n]+\)/', '', $source);
        $compiled = $this->app['blade.compiler']->compileString($source);
        $temp = tempnam(sys_get_temp_dir(), 'offers-render-').'.php';
        file_put_contents($temp, $compiled.'<?php echo $__env->yieldContent("javascript"); echo $__env->yieldContent("contenido"); ?>');
        try { $html = $this->app['view']->file($temp, ['ofertas' => $ofertas])->render(); }
        finally { unlink($temp); }
        $this->assertStringContainsString("$('#tbody_nuevas_ofertas').on('change'", $html);
        $this->assertStringNotContainsString('asset(\'js/ofertas.js\')', $html);
        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($dom);
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $node) {
            $id = $node->getAttribute('id');
            $this->assertNotContains($id, $ids, 'Duplicate ID: '.$id);
            $ids[] = $id;
        }
        $row = $xpath->query('//tr[@data-id="1"]')->item(0);
        $this->assertSame('2026-10-01', $row->getAttribute('data-desde'));
        $this->assertSame('vigente', $row->getAttribute('data-estado'));
        $this->assertStringContainsString('Producto &lt;especial&gt;', $html);
        $this->assertSame(1, $xpath->query('//*[@id="modalOfertasFamilias"]//button[@data-dismiss="modal"]')->length >= 1 ? 1 : 0);
        $this->assertSame(0, $xpath->query('//*[@id="modalNuevoDescuentoFamilia"]//*[@id="modalLoteOfertas"]')->length);
    }

    public function testRutaDeLoteEstaProtegidaPorAutenticacion()
    {
        $route = $this->app['router']->getRoutes()->match(Request::create('/ventas/ofertas/lote', 'POST'));
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertStringContainsString('ofertas_lote', $route->getActionName());
    }
}
