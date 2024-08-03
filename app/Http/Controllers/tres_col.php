<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\familia;

class tres_col extends Controller
{
    public function guardardatos(Request $peticion)
    {
		$guardado="SI";
		$peticion=$peticion->all();
        $id_familia=3;

        $dato=familia::select('nombrefamilia')->where('id',$id_familia)->first();
        $nombrefamilia=$dato->nombrefamilia;

        $familia=familia::find($id_familia);
        $nombrefamilia=$familia->nombrefamilia;

        $datoz=array('nombre'=>'javierz','apellido'=>'riveraz','nombrefamilia'=>$nombrefamilia);



		return view('errors.3columnas',compact('datos','guardado','datoz','peticion'))->with('msgGuardado','Sapo Guardado.');
    }

    public function guardarsimilares()
    {

    }

    public function guardarfotos()
    {

    }
}
