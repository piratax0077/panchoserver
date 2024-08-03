<?php

namespace App\Http\Controllers;

use App\relacionado;
use App\repuesto;
use App\marcavehiculo;
use App\modelovehiculo;
use Session;
use Illuminate\Support\Facades\Auth; 

class relacionados_controlador extends Controller
{
    private function validaSesion()
    {
        //Valida sesión: Revisar repuestos/Exceptions/Handler.php, método render()

        abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
    }

    public function index()
    {
        $this->validaSesion();
        $marcas=$this->damemarcas();
        $modelos=$this->damemodelos();
        $relacionados=relacionado::all();
        return view('manten.repuestos_relacionados',compact('relacionados','marcas','modelos'));
    }

    public function dame_un_repuesto($id)
    {
        try {
            $repuesto=repuesto::where('repuestos.id',$id)
            ->join('familias','repuestos.id_familia','familias.id')
            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
            ->get();

            $view=view('fragm.relacionado_principal',compact('repuesto'))->render();
            return $view;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function eliminarrelacionado($id)
    {
        $this->validaSesion();

        $r=relacionado::find($id);
        $idprincipal=$r->id_repuesto_principal;
        $r->delete();
        //Devolver lista de repuestos relacionados
        $repuestos=$this->dame_repuestos_relacionados($idprincipal);
        $view=view('fragm.relacionados_del_principal',compact('repuestos'))->render();
        return $view;
    }

    private function dame_repuestos_relacionados($id)
    {
        $relacionados=repuesto::where('repuestos_relacionados.id_repuesto_principal',$id)
        ->join('repuestos_relacionados','repuestos.id','repuestos_relacionados.id_repuesto_relacionado')
        ->select('repuestos_relacionados.id AS id_relacionado','repuestos.*')
        ->get();
        return $relacionados;
    }

    public function damerelacionados($id)
    {
        $this->validaSesion();
        //Devolver lista de repuestos relacionados
        $repuestos=$this->dame_repuestos_relacionados($id);
        $view=view('fragm.relacionados_del_principal',compact('repuestos'))->render();
        return $view;
    }

    public function guardar_relacionado($id_relacionado,$id_principal)
    {
        $this->validaSesion();
        if($id_relacionado==$id_principal)
        {
            return "<strong>No puede relacionar principal con principal...</strong>";
        }

        //Comprobar si ya existe la relacion principal con relacionado.
        $q=relacionado::where('id_repuesto_principal',$id_principal)
                ->where('id_repuesto_relacionado',$id_relacionado)
                ->first();
        if(!is_null($q)) return "<strong>Ya existe relación...</strong>";

        try{
                $re=new relacionado;
                $re->id_repuesto_principal=$id_principal;
                $re->id_repuesto_relacionado=$id_relacionado;
                $re->usuarios_id=Auth::user()->id;
                $re->save();
            return "<strong>Relacionado Guardado...</strong>";

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function dame_familias_repuestos($m,$n)
    {
        $this->validaSesion(); //Valida sesión
        try{
            $s="SELECT repuestos.id_familia, familias.nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos inner join familias on repuestos.id_familia=familias.id where repuestos.id_marca_vehiculo=".$m." and repuestos.id_modelo_vehiculo=".$n." GROUP by repuestos.id_familia order by familias.nombrefamilia";
            $familias=\DB::select($s);
            $v=view('fragm.ventas_familias',compact('familias'))->render();
            return $v;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    public function dame_repuestos($id_familia,$marca,$modelo)
    {
        $repuestos=repuesto::where('id_familia',$id_familia)
            ->where('id_marca_vehiculo',$marca)
            ->where('id_modelo_vehiculo',$modelo)
            ->get();
        $v=view('fragm.relacionados_repuestos',compact('repuestos'))->render();
        return $v;

    }

    public function damefamilias($marca,$modelo)
    {

        $this->validaSesion(); //Valida sesión

        $familias=$this->dame_familias_repuestos($marca,$modelo);
        $vista=view('fragm.buscarepuesto',compact('familias'))->render();
        return $vista;
    }



    private function damemarcas()
    {
        $m=marcavehiculo::where('activo','=',1)->select('idmarcavehiculo','marcanombre','urlfoto')->orderBy('marcanombre')->get();
        return $m;
    }

    private function damemodelos()
    {
        $m=modelovehiculo::where('activo','=',1)->get();
        return $m;
    }


}
