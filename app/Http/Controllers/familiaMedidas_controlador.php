<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\familia_medidas;

class familiaMedidas_controlador extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
        
        $id_familia = $request->id_familia;
        $medida = $request->medida;

        

        if(isset($request->btnGuardarMedida))
        {
            $reglas=array(
                'medida'=>'required|max:50'
            );

            $mensajes=array(
                'medida.required'=>'Debe Ingresar  Medida',
                'medida.max'=>'El nombre de la medida debe tener como máximo 20 caracteres.'
            );

            

            $this->validate($request,$reglas,$mensajes);

            $familia_medidas=new familia_medidas;
            
            $familia_medidas->id_familia = $id_familia;
            $familia_medidas->descripcion=strtoupper($medida);
            try {
                $familia_medidas->save();
                return ["OK",$familia_medidas];
            } catch (\Exception $e) {
                return $e;
            }
            
            
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
        $medida = familia_medidas::find($id);
        return $medida;
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
        //
    }

    public function dame_medidas($id){
        $medidas = familia_medidas::where('id_familia',$id)->orderBy('descripcion','asc')->get();
        return $medidas;
    }
}
