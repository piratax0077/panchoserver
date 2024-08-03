<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\familia;
use App\repuesto;
use App\marcavehiculo;


class cargadiv_controlador extends Controller
{
    public function ver()
    {
		$marcas=marcavehiculo::all();
		$repuestos=repuesto::select('codigo_interno','descripcion')->where('id_marca_vehiculo',3)->orderBy('codigo_interno')->get();
		$view=view('errors.cargardiv',compact('repuestos','marcas'))->render();
		return $view;
    	//return view('errors.cargardiv',compact('repuestos','marcas'));
    }

    public function verr()
	{
		$hola="que tal...";
		$repuestos=repuesto::select('codigo_interno','descripcion')->where('id_marca_vehiculo',2)->orderBy('codigo_interno')->get();
    	$view=view('errors.resultado',compact('repuestos'))->render();
    	return $view;
    	//return view('errors.resultado',compact('familias'));
    }


    public function cargar(Request $r)
    {
    	$idbuscado=$r->idmarcavehiculo;
    	$nombre=$r->nombre;
    	$repuestos=repuesto::select('codigo_interno','descripcion','id_marca_vehiculo')->where('id_marca_vehiculo',$idbuscado)->orderBy('codigo_interno')->get();
    	$view=view('errors.resultado',compact('repuestos','nombre'))->render();
    	return $view;
    }

}
