<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\marcarepuesto;
use Illuminate\Support\Facades\Auth;
use App\permissions_detail;
use Session;
use Debugbar;

class marcarepuestocontrolador extends Controller
{
    private $marcarepuestos;

    private function validaSesion()
    {
        //Valida sesión: Revisar Handler.php en app\Exception, método render()
        // repuestos/Exceptions/Handler.php
        // abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
    }

    private function damemarcarepuestos()
    {
        $m=marcarepuesto::orderBy('marcarepuesto')->get();
        return $m;
    }

    public function dame_marca_repuestos()
    {

        $user = Auth::user();
        if($user->rol->nombrerol == "Bodeguer@" || $user->rol->nombrerol == "bodega-venta" || $user->rol->nombrerol == "Administrador" || $user->rol->nombrerol == "jefe de bodega" || $user->rol->nombrerol == "estándar" ){
            return $this->damemarcarepuestos()->toJson();
        }else{
            $this->validaSesion();
        }
        
        
    }
    /**
     * Display a listing of the  resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function confirmaringreso($path){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == $path){
                        return true;
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return true;
        } else {
            return false;
        }
    }
    public function index()
    {
        //$this->validaSesion();
        $confirma = $this->confirmaringreso('/marcarepuesto');
        if($confirma){
            $marcarepuestos=$this->damemarcarepuestos();
            return view('manten.marcarepuesto',compact('marcarepuestos'));
        }else return redirect('home');
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $this->validaSesion();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->validaSesion();

        if(isset($request->btnGuardarMarcaRepuesto))
        {
            $reglas=array(
                'marcarepuesto'=>'required|max:20|unique:marcarepuestos,marcarepuesto'
            );

            $mensajes=array(
                'marcarepuesto.required'=>'Debe Ingresar la marca de repuesto',
                'marcarepuesto.max'=>'El nombre de la marca de repuesto debe tener como máximo 20 caracteres.',
                'marcarepuesto.unique'=>'El nombre de la marca de repuesto ya existe.'
            );

            $this->validate($request,$reglas,$mensajes);

            $marcarepuesto=new marcarepuesto;
            $marcarepuesto->marcarepuesto=$request->marcarepuesto;
            $marcarepuesto->save();
            $marcarepuestos=$this->damemarcarepuestos();
            if($request->donde=="marcarepuesto")
                return view('manten.marcarepuesto',compact('marcarepuestos'))->with('msgGuardado','Marca de Repuesto Guardada.');
            if($request->donde=="factuprodu") return "OK";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->validaSesion();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->validaSesion();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validaSesion();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->validaSesion();
        marcarepuesto::destroy($id);
        $marcarepuestos=$this->damemarcarepuestos();
        return view('manten.marcarepuesto',compact('marcarepuestos'))->with('msgGuardado','Marca de Repuesto Eliminada.');
    }

    public function destruir($id)
    {
        $this->validaSesion();
        try {
            $d=marcarepuesto::destroy($id);
            return "OK";
        } catch (\Exception $error) {
            //return $error.getMessage();
            //            Debugbar::error($error->getCode());
            if($error->getCode()==23000) return "Marca de Repuesto se esta utilizando y no se puede borrar.";
            return "error raro...";
        }

    }
}
