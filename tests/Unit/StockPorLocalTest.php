<?php
namespace Tests\Unit;

use App\local;
use App\repuesto;
use App\RepuestoStock;
use App\StockMovimiento;
use App\Observers\RepuestoStockObserver;
use App\Services\StockPorLocalService;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class StockPorLocalTest extends TestCase
{
    private $db;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $container = new \Illuminate\Foundation\Application(dirname(__DIR__, 2));
        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $container->instance('config', new Repository(['inventario' => ['stock_por_local' => true]]));
        $this->db = new Capsule($container);
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->db->setEventDispatcher(new Dispatcher($container));
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $container->instance('db', $this->db->getDatabaseManager());
        $container->instance('auth', new class { public function id() { return null; } });
        $container->instance('request', \Illuminate\Http\Request::create('/stock-test'));
        $container->instance('validator', new Factory(new Translator(new ArrayLoader, 'es'), $container));
        $schema = $this->db->schema();
        $schema->create('locales', function ($t) {
            $t->increments('id'); $t->string('local_nombre'); $t->boolean('activo')->default(true); $t->timestamps();
        });
        $schema->create('repuestos', function ($t) {
            $t->increments('id'); $t->string('codigo_interno')->nullable(); $t->timestamps();
            foreach (StockPorLocalService::CAMPOS as $i => $campos) {
                $t->integer($campos[0])->default(0); $t->integer($campos[1])->nullable();
                $t->string($campos[2])->nullable(); $t->integer($campos[3])->nullable();
                $t->date('fecha_actualizacion_stock'.($i === 1 ? '' : ($i === 2 ? '_dos' : '_tres')))->nullable();
            }
        });
        $schema->create('repuesto_stocks', function ($t) {
            $t->increments('id'); $t->integer('repuesto_id'); $t->integer('local_id');
            $t->integer('cantidad')->default(0); $t->string('ubicacion_fisica')->nullable();
            $t->integer('responsable_id')->nullable(); $t->timestamps(); $t->unique(['repuesto_id','local_id']);
        });
        $schema->create('stock_movimientos', function ($t) {
            $t->increments('id');
            foreach (['repuesto_id','local_id','repuesto_stock_id','ubicacion_indice','stock_anterior','stock_nuevo','cantidad_movimiento','usuario_id','responsable_id','referencia_id'] as $c) $t->integer($c)->nullable();
            foreach (['campo_stock','tipo','motivo','referencia_tipo','grupo_uuid','ip','user_agent'] as $c) $t->string($c)->nullable();
            $t->timestamp('created_at')->nullable();
        });
        foreach ([1,3,4,8,9] as $id) local::forceCreate(['id' => $id, 'local_nombre' => 'Local '.$id, 'activo' => true]);
        repuesto::flushEventListeners();
        repuesto::observe(RepuestoStockObserver::class);
        $this->service = new StockPorLocalService;
    }

    protected function tearDown(): void
    {
        repuesto::flushEventListeners();
        $this->db->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    private function producto()
    {
        return repuesto::create(['codigo_interno' => 'TEST', 'local_id' => 1, 'stock_actual' => 10]);
    }

    public function testNuevoLocalAjusteYAuditoriaSinLimiteDeTres()
    {
        $p = $this->producto();
        foreach ([3,4,8,9] as $local) $this->service->asociar($p->id, $local);
        $this->service->ajustar($p->id, 8, 12, 0, ['tipo' => 'AJUSTE_MANUAL', 'motivo' => 'Conteo físico']);
        $this->assertEquals(5, RepuestoStock::count());
        $this->assertSame(12, $this->service->cantidad($p->id, 8));
        $m = StockMovimiento::orderByDesc('id')->first();
        $this->assertSame(12, $m->cantidad_movimiento);
        $this->assertEquals(8, $m->local_id);
        $this->assertNotNull($m->repuesto_stock_id);
    }

    public function testTrasladoConservaTotalYAgrupaDosMovimientos()
    {
        $p = $this->producto();
        $this->service->trasladar($p->id, 1, 8, 4, ['motivo' => 'Reposición del local']);
        $this->assertSame(6, $this->service->cantidad($p->id, 1));
        $this->assertSame(4, $this->service->cantidad($p->id, 8));
        $this->assertEquals(10, RepuestoStock::sum('cantidad'));
        $this->assertEquals(6, $p->fresh()->stock_actual);
        $movimientos = StockMovimiento::where('tipo', 'TRASPASO')->get();
        $this->assertCount(2, $movimientos);
        $this->assertSame($movimientos[0]->grupo_uuid, $movimientos[1]->grupo_uuid);
        $this->assertEquals(0, $movimientos->sum('cantidad_movimiento'));
    }

    public function testTrasladoInsuficienteRevierteTodo()
    {
        $p = $this->producto();
        try { $this->service->trasladar($p->id, 1, 8, 11, ['motivo' => 'Traslado inválido']); $this->fail(); }
        catch (ValidationException $e) {}
        $this->assertEquals(10, RepuestoStock::sum('cantidad'));
        $this->assertSame(1, RepuestoStock::count());
        $this->assertSame(1, StockMovimiento::count());
    }

    public function testAjusteConLecturaObsoletaSeRechaza()
    {
        $p = $this->producto();
        $this->service->mover($p->id, 1, -2, ['tipo' => 'VENTA']);
        try { $this->service->ajustar($p->id, 1, 15, 10, ['motivo' => 'Conteo']); $this->fail(); }
        catch (ValidationException $e) {}
        $this->assertSame(8, $this->service->cantidad($p->id, 1));
    }

    public function testVentaEnCuartoLocalYDevolucion()
    {
        $p = $this->producto();
        $this->service->mover($p->id, 8, 15, ['tipo' => 'COMPRA']);
        $this->service->mover($p->id, 8, -4, ['tipo' => 'VENTA']);
        $this->service->mover($p->id, 8, 2, ['tipo' => 'DEVOLUCION']);
        $this->assertSame(13, $this->service->cantidad($p->id, 8));
        $this->assertSame(10, $this->service->cantidad($p->id, 1));
    }

    public function testEscrituraLegadaActualizaTablaNuevaUnaSolaVez()
    {
        $p = $this->producto();
        $p->refresh();
        $p->stock_actual -= 3;
        $p->save();
        $this->assertSame(7, $this->service->cantidad($p->id, 1));
        $this->assertSame(2, StockMovimiento::count());
    }

    public function testEscrituraLegadaObsoletaNoPierdeVentaConcurrente()
    {
        $p = $this->producto()->fresh();
        $this->service->mover($p->id, 1, -2);
        $p->stock_actual = 15;
        try { $p->save(); $this->fail(); } catch (ValidationException $e) {}
        $this->assertEquals(8, $p->fresh()->stock_actual);
        $this->assertSame(8, $this->service->cantidad($p->id, 1));
    }

    public function testNoPermiteCambiarLocalConStock()
    {
        $p = $this->producto()->fresh();
        $p->local_id = 8;
        try { $p->save(); $this->fail(); } catch (ValidationException $e) {}
        $this->assertSame(10, $this->service->cantidad($p->id, 1));
        $this->assertSame(0, $this->service->cantidad($p->id, 8));
    }

    public function testMigracionRespetaLocalRealYRechazaDuplicados()
    {
        $p = $this->producto()->fresh();
        $p->local_id = 3;
        $filas = $this->service->filasLegadas($p);
        $this->assertSame(10, $filas[3]['cantidad']);
        $p->local_id_dos = 3;
        $this->expectException(ValidationException::class);
        $this->service->filasLegadas($p);
    }

    public function testMigracionUsaCorrespondenciaConfirmadaParaStockSinLocal()
    {
        $p = $this->producto()->fresh();
        $p->local_id = null;
        $p->local_id_dos = null;
        $p->stock_actual_dos = 2;
        $p->local_id_tres = null;
        $p->stock_actual_tres = 3;
        $filas = $this->service->filasLegadas($p);
        $this->assertSame(10, $filas[1]['cantidad']);
        $this->assertSame(2, $filas[3]['cantidad']);
        $this->assertSame(3, $filas[4]['cantidad']);
    }

    public function testNoPermiteOperacionesEnLocalInactivo()
    {
        $p = $this->producto();
        local::whereKey(8)->update(['activo' => false]);
        $this->expectException(ValidationException::class);
        $this->service->mover($p->id, 8, 5);
    }

    public function testRollbackDeTransaccionExternaIncluyeEspejoEHistorial()
    {
        $p = $this->producto();
        try {
            $this->db->getConnection()->transaction(function () use ($p) {
                $this->service->mover($p->id, 1, -3);
                throw new \RuntimeException('Documento fallido');
            });
        } catch (\RuntimeException $e) {}
        $this->assertSame(10, $this->service->cantidad($p->id, 1));
        $this->assertEquals(10, $p->fresh()->stock_actual);
        $this->assertSame(1, StockMovimiento::count());
    }

    public function testLocalInactivoRevierteEscrituraLegada()
    {
        $p = $this->producto()->fresh();
        local::whereKey(1)->update(['activo' => false]);
        $p->stock_actual = 5;
        try { $p->save(); $this->fail(); } catch (ValidationException $e) {}
        $this->assertEquals(10, $p->fresh()->stock_actual);
        $this->assertSame(10, $this->service->cantidad($p->id, 1));
        $this->assertSame(1, StockMovimiento::count());
    }

    public function testNoPermiteReasignarSlotAUnLocalConExistencias()
    {
        $p = $this->producto()->fresh();
        $this->service->mover($p->id, 8, 7);
        $p->local_id_dos = 8;
        try { $p->save(); $this->fail(); } catch (ValidationException $e) {}
        $this->assertSame(7, $this->service->cantidad($p->id, 8));
    }

    public function testActualizacionDeMetadatosNoBorraStockDeLocalNuevo()
    {
        $p = $this->producto()->fresh();
        $this->service->mover($p->id, 8, 7);
        $p->ubicacion = 'Pasillo 2';
        $p->save();
        $this->assertSame(7, $this->service->cantidad($p->id, 8));
        $this->assertSame('Pasillo 2', RepuestoStock::where('local_id', 1)->value('ubicacion_fisica'));
        $this->assertSame(2, StockMovimiento::count());
    }

    public function testModoAnteriorNoConsultaTablaNueva()
    {
        config(['inventario.stock_por_local' => false]);
        $this->db->schema()->drop('repuesto_stocks');
        $p = repuesto::create(['codigo_interno' => 'ANTERIOR', 'local_id' => 1, 'stock_actual' => 2]);
        $p->stock_actual = 3;
        $p->save();
        $this->assertEquals(3, $p->fresh()->stock_actual);
        $this->assertSame(2, StockMovimiento::count());
        $this->assertSame(3, StockPorLocalService::total($p));
    }

    public function testTotalesSqlIncluyenLocalesNuevos()
    {
        $p = $this->producto();
        $this->service->mover($p->id, 8, 7);
        $r = repuesto::selectRaw('id, '.StockPorLocalService::totalSql().' AS stock_total')->first();
        $this->assertEquals(17, $r->stock_total);
        $this->assertSame(17, StockPorLocalService::total($p));
    }

    public function testMiddlewareImpideQueUsuarioNoAdministradorModifiqueStock()
    {
        $this->db->schema()->create('permissions_detail', function ($t) {
            $t->increments('id'); $t->integer('permission_id'); $t->integer('usuarios_id'); $t->string('path_ruta');
        });
        // El middleware de escritura no debe permitir acceso aunque exista permiso de lectura.
        app()->instance('auth', new class {
            public function id() { return 7; }
            public function user() { return (object) ['rol' => (object) ['nombrerol' => 'Vendedor']]; }
        });
        Facade::clearResolvedInstance('auth');
        $controller = new \App\Http\Controllers\StockUbicacionesController;
        $middleware = $controller->getMiddleware()[1]['middleware'];
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware(\Illuminate\Http\Request::create('/stock', 'POST'), function () { $this->fail('Acceso permitido'); });
    }
}
