<?php
namespace Tests\Feature;
use App\Http\Controllers\ventas_controlador;
use App\descuento;
use App\familia;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
class OfertasFamiliasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'familias_test','database.connections.familias_test'=>[
            'driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true,
        ]]);
        Schema::create('familias', function(Blueprint $t){$t->increments('id');$t->string('nombrefamilia');$t->integer('activo')->default(1);$t->timestamps();});
        Schema::create('descuentos',function(Blueprint $t){
            $t->increments('id');$t->integer('id_familia');$t->integer('id_cliente');$t->integer('usuarios_id');
            $t->decimal('porcentaje',8,2);$t->integer('id_local');$t->date('desde');$t->date('hasta');
            $t->integer('activo');$t->string('image_path')->nullable();$t->timestamps();
        });
        familia::create(['nombrefamilia'=>'Aceites','activo'=>1]);familia::create(['nombrefamilia'=>'Baterias','activo'=>1]);
        Carbon::setTestNow('2026-10-06');Auth::shouldReceive('id')->andReturn(9);
    }
    protected function tearDown(): void { Carbon::setTestNow();parent::tearDown(); }
    private function controller($permiso=true){return new class($permiso) extends ventas_controlador {
        private $permiso;public function __construct($p){$this->permiso=$p;}public function confirmaringreso($path){return $this->permiso;}
    };}
    private function ejecutar($datos){return $this->controller()->ofertas_familias_lote(Request::create('/ventas/ofertas/familias/lote','POST',$datos));}
    private function crear(){return $this->ejecutar(['accion'=>'crear','ids'=>[1,2],'desde'=>'2026-10-01','hasta'=>'2026-10-31','id_local'=>6,'porcentaje'=>12.5]);}
    public function testCreaOfertasEnConjuntoConPorcentajeCanalYAutor(){
        $this->assertSame(2,$this->crear()->getData()->cantidad);
        $this->assertEquals([12.5,12.5],descuento::pluck('porcentaje')->all());
        $this->assertEquals([6,6],descuento::pluck('id_local')->all());
        $this->assertEquals([9,9],descuento::pluck('usuarios_id')->all());
        $this->assertEquals([1,1],descuento::pluck('activo')->all());
    }
    public function testEdicionParcialConservaPorcentajeFechasEImagen(){
        $this->crear();descuento::where('id',1)->update(['image_path'=>'aceite.jpg']);
        $this->ejecutar(['accion'=>'editar','ids'=>[1,2],'id_local'=>2]);
        $this->assertEquals([12.5,12.5],descuento::pluck('porcentaje')->all());
        $this->assertEquals(['2026-10-01','2026-10-01'],descuento::pluck('desde')->all());
        $this->assertEquals('aceite.jpg',descuento::find(1)->image_path);
        $this->assertEquals([2,2],descuento::pluck('id_local')->all());
    }
    public function testFechasInvalidasReviertenTodoElLote(){
        $this->crear();descuento::where('id',2)->update(['hasta'=>'2026-10-05']);
        try{$this->ejecutar(['accion'=>'editar','ids'=>[1,2],'desde'=>'2026-10-10','porcentaje'=>20]);$this->fail('Invalid dates accepted');}
        catch(ValidationException $e){$this->assertEquals([12.5,12.5],descuento::pluck('porcentaje')->all());$this->assertEquals(['2026-10-01','2026-10-01'],descuento::pluck('desde')->all());}
    }
    public function testNoDuplicaFamiliasConOfertas(){
        $this->crear();try{$this->crear();$this->fail('Duplicate accepted');}catch(ValidationException $e){$this->assertSame(2,descuento::count());}
    }
    public function testNoModificaLotesConRegistrosAusentes(){
        $this->crear();try{$this->ejecutar(['accion'=>'editar','ids'=>[1,999],'id_local'=>1]);$this->fail('Missing ID accepted');}
        catch(ValidationException $e){$this->assertEquals([6,6],descuento::pluck('id_local')->all());}
    }
    /** @dataProvider porcentajesInvalidos */
    public function testRechazaPorcentajesInvalidos($valor){$this->expectException(ValidationException::class);$this->ejecutar(['accion'=>'editar','ids'=>[1],'porcentaje'=>$valor]);}
    public function porcentajesInvalidos(){return[[0],[-1],[100],[101],['abc']];}
    public function testOfertaProgramadaNoSeActivaAntesDeTiempo(){
        $this->ejecutar(['accion'=>'crear','ids'=>[1],'desde'=>'2026-11-01','hasta'=>'2026-11-30','id_local'=>2,'porcentaje'=>10]);
        $this->assertEquals(0,descuento::first()->activo);
    }
    public function testCatalogoDevuelveFechasUtilesParaLaVista(){
        $this->crear();$data=$this->controller()->ofertas_familias_datos()->getData();
        $this->assertCount(2,$data->familias);$this->assertCount(2,$data->ofertas);$this->assertSame('2026-10-01',$data->ofertas[0]->desde);
    }
    public function testRequierePermisoDeOfertas(){
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller(false)->ofertas_familias_datos();
    }
    public function testCreacionRequiereTodosLosDatos(){
        $this->expectException(ValidationException::class);$this->ejecutar(['accion'=>'crear','ids'=>[1],'porcentaje'=>10]);
    }
    public function testEdicionRequiereUnCambio(){
        $this->expectException(ValidationException::class);$this->ejecutar(['accion'=>'editar','ids'=>[1]]);
    }
    public function testNoCreaOfertasParaFamiliasInactivas(){
        familia::where('id',2)->update(['activo'=>0]);
        try{$this->crear();$this->fail('Inactive accepted');}catch(ValidationException $e){$this->assertSame(0,descuento::count());}
    }
    public function testRutasDeFamiliasExigenAutenticacion(){
        foreach([['GET','/ventas/ofertas/familias'],['POST','/ventas/ofertas/familias/lote']] as $r){$route=$this->app['router']->getRoutes()->match(Request::create($r[1],$r[0]));$this->assertContains('auth',$route->gatherMiddleware());}
    }

    public function testDetalleIndividualMuestraElCanalActual()
    {
        $this->crear();
        $oferta=descuento::first();$oferta->nombrefamilia='Aceites';
        $html=view('fragm.modal_editar_descuento_familia',['descuento'=>$oferta,'familia'=>1,'opcionesLocales'=>[]])->render();
        $this->assertStringContainsString('<option value="6" selected>', $html);
        $this->assertStringContainsString('step="0.01"', $html);
    }
    public function testRequierePermisoParaCambiosMasivos()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller(false)->ofertas_familias_lote(Request::create('/ventas/ofertas/familias/lote','POST',[]));
    }
}
