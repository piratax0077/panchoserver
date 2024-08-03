<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\pais;
use App\permissions_detail;
use Session;
use Illuminate\Support\Facades\Auth; 

class pais_controlador extends Controller
{

    private $paises;

    private function validaSesion()
    {
        //Valida sesión: Revisar Handler.php en app\Exception, método render()
        // repuestos/Exceptions/Handler.php
        abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
    }

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

    private function damepaises()
    {
        $p=pais::orderBy('nombre_pais')->get();
        return $p;
    }

    public function dame_paises()
    {
        $user = Auth::user();
        if($user->rol->nombrerol == "Bodeguer@" || $user->rol->nombrerol == "Administrador" || $user->rol->nombrerol == "bodega-venta" || $user->rol->nombrerol == "jefe de bodega" || $user->rol->nombrerol == "estándar"){
            return $this->damepaises()->toJson();
        }else{
            $this->validaSesion();
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$this->validaSesion();
        $confirma = $this->confirmaringreso('/pais');
        if($confirma){
            $paises=$this->damepaises();
            return view('manten.paises',compact('paises'));
        }else return redirect('home');
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->validaSesion();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //$this->validaSesion();
try {
    if(isset($request->btnGuardarPais))
        {
            $reglas=array(
                'pais'=>'required|max:20|unique:paises,nombre_pais'
            );

            $mensajes=array(
                'pais.required'=>'Debe Ingresar el País',
                'pais.max'=>'El nombre del País debe tener como máximo 20 caracteres.',
                'pais.unique'=>'El nombre del País ya existe.'
            );

            $this->validate($request,$reglas,$mensajes);

            $pais=new pais;
            $pais->nombre_pais=strtoupper($request->pais);
            
            $pais->save();
            $paises=$this->damepaises();
            if($request->donde=="pais")
                return view('manten.paises',compact('paises'))->with('msgGuardado','Nombre de País Guardado.');
            if($request->donde=="factuprodu") return "OK";
        }
} catch (\Exception $e) {
    //throw $th;
    return $e->getMessage();
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
        pais::destroy($id);
        $paises=$this->damepaises();
        return view('manten.paises',compact('paises'))->with('msgGuardado','Nombre de País Eliminado.');
    }
}
