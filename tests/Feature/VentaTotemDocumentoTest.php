<?php
namespace Tests\Feature;

use App\Http\Controllers\ventas_controlador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class VentaTotemDocumentoTest extends TestCase
{
    private $directory;
    private $file;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'inventario.stock_por_local' => false, 'app.debug' => true]);
        DB::purge('sqlite');
        \Illuminate\Support\Facades\Log::shouldReceive('error')->andReturnNull();
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(407);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 407]);
        config(['session.driver' => 'array']);
        Session::put('PARAM_IVA', 0.19);
        foreach (['boletas','facturas'] as $table) {
            Schema::create($table, function ($t) use ($table) {
                $t->increments('id'); $t->string($table === 'boletas' ? 'num_boleta' : 'num_factura');
                foreach (['fecha_emision','estado_sii','resultado_envio','trackid','url_xml'] as $c) $t->string($c)->nullable();
                foreach (['es_credito','es_delivery','id_cliente','estado','activo','usuarios_id'] as $c) $t->integer($c)->nullable();
                foreach (['total','neto','exento','iva'] as $c) $t->decimal($c,14,2)->nullable();
                $t->timestamps();
            });
        }
        foreach (['boletas_detalle','facturas_detalle'] as $table) {
            Schema::create($table, function ($t) use ($table) {
                $t->increments('id');
                foreach ([$table === 'boletas_detalle' ? 'id_boleta' : 'id_factura','id_repuestos','id_unidad_venta','id_local','activo','usuarios_id'] as $c) $t->integer($c)->nullable();
                foreach (['precio_venta','pu_neto','cantidad','subtotal','descuento','total'] as $c) $t->decimal($c,14,2)->nullable();
                $t->timestamps();
            });
        }
        Schema::create('pagos', function ($t) {
            $t->increments('id');
            foreach (['tipo_doc','fecha_pago','referencia','referencia_pago'] as $c) $t->string($c)->nullable();
            foreach (['id_doc','id_cliente','id_forma_pago','activo','usuarios_id'] as $c) $t->integer($c)->nullable();
            $t->decimal('monto',14,2); $t->timestamps();
        });
        Schema::create('repuestos', function ($t) {
            $t->increments('id'); $t->string('descripcion'); $t->string('codigo_interno');
            foreach (['id_familia','id_marca_repuesto','local_id','local_id_dos','local_id_tres','stock_actual','stock_actual_dos','stock_actual_tres','oferta','precio_venta'] as $c) $t->integer($c)->default(0);
            $t->string('cod_repuesto_proveedor')->nullable(); $t->timestamps();
        });
        Schema::create('stock_movimientos', function ($t) {
            $t->increments('id');
            foreach (['repuesto_id','local_id','repuesto_stock_id','ubicacion_indice','stock_anterior','stock_nuevo','cantidad_movimiento','usuario_id','responsable_id','referencia_id'] as $c) $t->integer($c)->nullable();
            foreach (['campo_stock','tipo','motivo','referencia_tipo','grupo_uuid','ip','user_agent'] as $c) $t->string($c)->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('carrito_transferido', function ($t) {
            $t->increments('id');
            foreach (['cajeros_id','cliente_id','usuarios_id','estado','item','id_repuestos','id_local','id_unidad_venta','cantidad','pu','descuento_item'] as $c) $t->integer($c)->default(0);
        });
        foreach (['users','marcarepuestos','locales','ofertas_pagina_web'] as $table) {
            Schema::create($table, function ($t) {
                $t->increments('id'); $t->string('name')->nullable(); $t->string('marcarepuesto')->nullable();
                $t->string('local_nombre')->nullable(); $t->integer('id_repuesto')->nullable(); $t->integer('activo')->nullable();
            });
        }
        DB::table('users')->insert(['id'=>5, 'name'=>'Totem']);
        DB::table('marcarepuestos')->insert(['id'=>1]);
        DB::table('locales')->insert(['id'=>1]);
        for ($n=1; $n<=2; $n++) {
            DB::table('repuestos')->insert(['id'=>$n,'codigo_interno'=>'T'.$n,'descripcion'=>'PRODUCTO '.$n,'id_marca_repuesto'=>1,'local_id'=>1,'stock_actual'=>10]);
            DB::table('carrito_transferido')->insert(['id'=>$n,'cajeros_id'=>407,'cliente_id'=>4,'usuarios_id'=>5,'estado'=>1,'item'=>$n===1 ? 1 : 3,'id_repuestos'=>$n,'id_local'=>1,'id_unidad_venta'=>1,'cantidad'=>$n,'pu'=>100]);
        }
        $this->directory=sys_get_temp_dir().'/venta-totem-'.bin2hex(random_bytes(8));
        $this->app->setBasePath($this->directory);
    }

    protected function tearDown(): void
    {
        if ($this->file && is_file($this->file)) unlink($this->file);
        if ($this->file) {
            rmdir(dirname($this->file)); rmdir(dirname(dirname($this->file)));
            rmdir($this->directory.'/xml'); rmdir($this->directory);
        }
        parent::tearDown();
    }

    private function emitir($tipo, $fallarPago = false, $enviar = true)
    {
        $folder=$tipo==='39' ? 'boletas' : 'facturas';
        mkdir($this->directory.'/xml/generados/'.$folder,0777,true);
        $this->file=$this->directory.'/xml/generados/'.$folder.'/test.xml';
        file_put_contents($this->file, '<EnvioDTE><SetDTE><DTE><Documento><Encabezado><IdDoc><Folio>123</Folio><FchEmis>2026-10-14</FchEmis></IdDoc><Totales><MntNeto>252</MntNeto><IVA>48</IVA><MntTotal>300</MntTotal></Totales></Encabezado><Detalle><NmbItem>PRODUCTO 1</NmbItem><PrcItem>100</PrcItem><QtyItem>1</QtyItem><MontoItem>100</MontoItem></Detalle><Detalle><NmbItem>PRODUCTO 2</NmbItem><PrcItem>100</PrcItem><QtyItem>2</QtyItem><MontoItem>200</MontoItem></Detalle></Documento></DTE></SetDTE></EnvioDTE>');
        $sii=\Mockery::mock('alias:App\servicios_sii\ClsSii');
        $sii->shouldReceive($tipo==='39' ? 'enviar_sii_boleta' : 'enviar_sii')
            ->times($enviar ? 1 : 0)->andReturn(['estado'=>'OK','mensaje'=>'Simulado','trackid'=>'test']);
        if ($fallarPago) {
            \App\pago::creating(function () { throw new \RuntimeException('Fallo de pago simulado'); });
        }
        $controller=new class extends ventas_controlador {
            public function revisar_stock_minimo($idrepuesto) {}
        };
        return $controller->enviar_sii(new Request([
            'xml'=>'test.xml','tipo_dte'=>$tipo,'folio_dte'=>123,'idcliente'=>4,'cliente_id'=>4,
            'vendedor_id'=>5,'venta'=>'contado','docu'=>$tipo==='39' ? 'boleta' : 'factura',
            'consignacion'=>'','forma_pago'=>[1],'monto'=>[300],'referencia'=>['test'],
        ]));
    }

    public function testBoletaConSaltosGuardaTodosLosDetallesYElPago()
    {
        $response=$this->emitir('39');
        $this->assertSame(200,$response->getStatusCode(),$response->getContent());
        $this->assertSame(2,DB::table('boletas_detalle')->count());
        $this->assertEquals(2,DB::table('boletas_detalle')->where('id_repuestos',2)->value('cantidad'));
        $this->assertSame(1,DB::table('pagos')->count());
        $this->assertEquals(8,DB::table('repuestos')->where('id',2)->value('stock_actual'));
    }

    public function testFacturaConSaltosGuardaTodosLosDetallesYElPago()
    {
        $response=$this->emitir('33');
        $this->assertSame(200,$response->getStatusCode(),$response->getContent());
        $this->assertSame(2,DB::table('facturas_detalle')->count());
        $this->assertEquals(2,DB::table('facturas_detalle')->where('id_repuestos',2)->value('cantidad'));
        $this->assertSame(1,DB::table('pagos')->count());
    }

    public function testItemsRepetidosNoConfundenLasLineasXml()
    {
        DB::table('carrito_transferido')->where('id',2)->update(['item'=>1]);
        $response=$this->emitir('39');
        $this->assertSame(200,$response->getStatusCode(),$response->getContent());
        $this->assertSame(2,DB::table('boletas_detalle')->count());
        $this->assertEquals(2,DB::table('boletas_detalle')->where('id_repuestos',2)->value('cantidad'));
    }

    public function testCarritoModificadoSeRechazaAntesDeEnviarAlSii()
    {
        DB::table('carrito_transferido')->where('id',2)->update(['cantidad'=>4]);
        $response=$this->emitir('39',false,false);
        $this->assertSame(500,$response->getStatusCode());
        $this->assertFalse(json_decode($response->getContent(),true)['recibido_sii']);
        $this->assertSame(0,DB::table('boletas')->count());
        $this->assertEquals(20,DB::table('repuestos')->sum('stock_actual'));
    }

    public function testGiftcardGuardaDetallesSinVolverADescontarStock()
    {
        Session::put('numero_giftcard','test-giftcard');
        $response=$this->emitir('39');
        $this->assertSame(200,$response->getStatusCode(),$response->getContent());
        $this->assertSame(2,DB::table('boletas_detalle')->count());
        $this->assertEquals(20,DB::table('repuestos')->sum('stock_actual'));
        $this->assertSame(1,DB::table('pagos')->count());
    }

    public function testFolioRegistradoNoSeVuelveAEnviar()
    {
        DB::table('boletas')->insert(['num_boleta'=>'123']);
        $response=$this->emitir('39',false,false);
        $this->assertSame(500,$response->getStatusCode());
        $this->assertSame(1,DB::table('boletas')->count());
        $this->assertSame(0,DB::table('pagos')->count());
    }

    public function testFalloEnSegundoProductoRevierteElPrimero()
    {
        \App\repuesto::updating(function ($repuesto) {
            if ($repuesto->id === 2) throw new \RuntimeException('Fallo segundo producto');
        });
        $response=$this->emitir('33');
        $this->assertSame(500,$response->getStatusCode());
        $this->assertStringContainsString('Fallo segundo producto',$response->getContent());
        $this->assertSame(0,DB::table('facturas')->count());
        $this->assertSame(0,DB::table('facturas_detalle')->count());
        $this->assertSame(0,DB::table('pagos')->count());
        $this->assertEquals(20,DB::table('repuestos')->sum('stock_actual'));
    }

    public function testFalloDePagoRevierteCabeceraDetallesYStock()
    {
        $response=$this->emitir('39',true);
        $this->assertSame(500,$response->getStatusCode());
        $this->assertTrue(json_decode($response->getContent(),true)['recibido_sii']);
        $this->assertStringContainsString('Fallo de pago simulado', $response->getContent());
        $this->assertSame(0,DB::table('stock_movimientos')->count());
        $this->assertSame(0,DB::table('boletas')->count());
        $this->assertSame(0,DB::table('boletas_detalle')->count());
        $this->assertSame(0,DB::table('pagos')->count());
        $this->assertEquals(20,DB::table('repuestos')->sum('stock_actual'));
    }
}