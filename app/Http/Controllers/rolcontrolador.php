<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\rol;
use Session;
//use App\misClases\ejemplo; // CUSTOM CLASS

class rolcontrolador extends Controller
{
    private $roles;

    private function dameroles()
    {
        $r=rol::orderBy('nombrerol')->get();
        return $r;
    }
    /**
     * Display a listing of the  resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('welcome');
        // if(Session::get('acceso')!='SI')
        // {
        //     return view('errors.noAutenticado');
        // }
        
        /* EJEMPLO DE USO DE UNA CUSTOM CLASS
        $j=new ejemplo();
        $j->set1("hola");
        $j->set2("amigo");
        $k=$j->get1()." - ".$j->get2();
        return view('manten.rol',compact('roles','k'));
        */
        $roles = rol::all();
        return view('manten.rol',['roles' => $roles]);
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
        // if(Session::get('acceso')!='SI')
        // {
        //     return view('errors.noAutenticado');
        // }

            
        if(isset($request->btnGuardarRol))
        {
            $reglas=array(
                'nombrerol'=>'required|max:20|unique:roles,nombrerol'
            );

            $mensajes=array(
                'nombrerol.required'=>'Debe Ingresar un nombre de rol',
                'nombrerol.max'=>'El nombre del rol debe tener como máximo 20 caracteres.',
                'nombrerol.unique'=>'El nombre de rol ya existe.'
            );

            

            $rol=new rol;
            $rol->nombrerol=$request->nombrerol;

            
           $this->validate($request,$reglas,$mensajes);
        
            
            $rol->save();
            
            $roles=$this->dameroles();
            return view('manten.rol',compact('roles'))->with('msgGuardado','Rol Guardado.');
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
        // if(Session::get('acceso')!='SI')
        // {
        //     return view('errors.noAutenticado');
        // }

        rol::destroy($id);
        $roles=$this->dameroles();
            return view('manten.rol',compact('roles'))->with('msgGuardado','Rol Eliminado.');
    }
}
