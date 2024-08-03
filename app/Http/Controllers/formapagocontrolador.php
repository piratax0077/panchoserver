<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\formapago;
use Session;
use Illuminate\Support\Facades\Auth; 

class formapagocontrolador extends Controller
{
    private $roles;

    private function dameformaspago()
    {
        $r=formapago::orderBy('formapago')->get();
        return $r;
    }
    /**
     * Display a listing of the  resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->rol->nombrerol !== "Administrador")
        {
            return view('errors.noAutenticado');
        }
        $formas=$this->dameformaspago();
        return view('manten.forma_pago',compact('formas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Session::get('acceso')!='SI')
        {
            return view('errors.noAutenticado');
        }

        if(isset($request->btnGuardarFormaPago))
        {
            $reglas=array(
                'formapago'=>'required|max:20|unique:formapago,formapago'
            );

            $mensajes=array(
                'formapago.required'=>'Debe Ingresar un nombre',
                'formapago.max'=>'Debe tener como máximo 20 caracteres.',
                'formapago.unique'=>'Ya existe.'
            );

            $this->validate($request,$reglas,$mensajes);

            $forma=new formapago;
            $forma->formapago=$request->formapago;
            $forma->activo=1;
            $forma->usuarios_id=Auth::user()->id;
            $forma->save();
            $formas=$this->dameformaspago();
            return view('manten.forma_pago',compact('formas'))->with('msgGuardado','Dato Guardado.');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Session::get('acceso')!='SI')
        {
            return view('errors.noAutenticado');
        }

        formapago::destroy($id);
        $formas=$this->dameformaspago();
            return view('manten.forma_pago',compact('formas'))->with('msgGuardado','Dato Eliminado.');
    }

}
