<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\repuesto;

class repuesto_codigo_controlador extends Controller
{
    public function index($id_repuesto){
        try {
           
            $repuestos = repuesto::select('id','descripcion','codigo_interno')->where('id',$id_repuesto)->get();
            return view('repuesto_codigo', compact('repuestos'))->render();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function buscar_repuesto_vista(){
        
        return view('repuesto_buscar');
    }

    public function buscar_repuesto(Request $request){
        $codigo_escaneado = $request->codigo_escaneado;
        // return $codigo_escaneado;
        try {
            $repuesto=repuesto::select('repuestos.*','familias.nombrefamilia','paises.nombre_pais','proveedores.empresa_nombre_corto')
            ->where('repuestos.codigo_interno',$codigo_escaneado)
            ->where('repuestos.activo',1)
            ->join('familias','repuestos.id_familia','familias.id')
            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
            ->join('proveedores','repuestos.id_proveedor','proveedores.id')
            ->join('paises','repuestos.id_pais','paises.id')
            ->get();
           
            $v = view('fragm.repuesto_escaneado',compact('repuesto'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
