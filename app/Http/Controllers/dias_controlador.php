<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dia;
use Session;
use Illuminate\Support\Facades\Auth; 

class dias_controlador extends Controller
{
     private $dias;

    private function damedias()
    {
        $d=dia::orderBy('valor','ASC')->get();
        return $d;
    }

    /**
     * Display a  listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->rol->nombrerol !== "Administrador")
        {
            return view('errors.noAutenticado');
        }
        $dias=$this->damedias();
        //$debug=$dias;
        //$v=view('errors.debug_ajax',compact('debug'))->render();
        //return $v;
        return view('manten.dias_credito',compact('dias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //verificar sesion
        if($request->modifika==0)
        {
            $l=new dia;
            $l->valor=$request->valor;
            $l->save();
        }else{
            $l=dia::find($request->ide);
            $l->valor=$request->valor;
            $l->save();
        }
        return "OK";
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
        dia::destroy($id);
        $dias=$this->damedias();
        return view('manten.dias_credito',compact('dias'));
    }
}
