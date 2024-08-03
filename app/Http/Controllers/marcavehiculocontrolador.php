<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\marcavehiculo;

use Session; //lo agregué
use Illuminate\Support\Facades\Storage; //lo agregué
use Illuminate\Support\Facades\File; //lo agregué
use Illuminate\Support\Facades\Auth; //lo agregué

class marcavehiculocontrolador extends Controller
{

    private $marcas;

    private function damemarcas()
    {
        $m=marcavehiculo::where('activo','=',1)->select('idmarcavehiculo','marcanombre','urlfoto')->orderBy('marcanombre')->get();
        return $m;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // if(Auth::user()->rol->nombrerol !== "Administrador")
        // {
        //     return view('errors.noAutenticado');
        // }
        $marcas=$this->damemarcas();
        return view('manten.marcavehiculo',compact('marcas'));
        //return view('manten.marcavehiculo');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Session::get('acceso')!='SI')
        {
            return view('errors.noAutenticado');
        }
        return view('fragm.mensajes')->with('msgGuardado','CREATE ID: '); //borre .$id
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

        if(isset($request->btnGuardarMarcaVehiculo))
        {

            //Reglas de Validación
            $reglas=array(
                'marcavehiculo'=>'required|max:20|unique:marcavehiculos,marcanombre',
                'archivo'=>'required|max:200|mimes:jpeg,png' //maximo 200 kilobytes
            );

            //Mensajes de error personalizados por que por defecto salen en ingles
            //Fuente: https://styde.net/como-personalizar-los-mensajes-de-error-de-validacion-de-formularios-en-laravel/
            $mensajes=array(
                'marcavehiculo.required'=>'Debe Ingresar la Marca.',
                'marcavehiculo.max'=>'La marca debe tener como máximo 20 caracteres.',
                'marcavehiculo.unique'=>'La marca ya existe.',
                'archivo.required'=>'Debe elegir una imagen.',
                'archivo.mimes'=>'El tipo de archivo debe ser una imagen jpg o png.',
                'archivo.max'=>'El tamaño de archivo no debe ser mayor a 200Kb.'
            );

            $this->validate($request,$reglas,$mensajes);

            $archivo=$request->file('archivo');

            $ultimoIdMarcaVehiculo=marcavehiculo::where('activo',1)->max('idmarcavehiculo');
            if(!isset($ultimoIdMarcaVehiculo)) $ultimoIdMarcaVehiculo=0;
            $marcavehiculo= new marcavehiculo;

            //Almacenar en storage con un enlace simbólico
            //>php artisan storage:link
            /*
            Luego de ejecutar el comando, van a notar que en su carpeta public se creo una nueva «carpeta» llamada storage. Pero no es una carpeta real, si no que es un puntero a nuestra carpeta storage original que nos permitirá acceder a ella.
            https://www.laraveltip.com/como-mostrar-imagenes-de-la-carpeta-storage-en-laravel/
            */

            $nombreArchivo=$archivo->getClientOriginalName();

            /* para usar el store (carpeta storage del proyecto) debe
            usarse enlaces simbólicos escribiendo en la consola:
            >php artisan storage:link
            Fuente: https://laravel.com/docs/5.3/filesystem
            */
            $marcavehiculo->urlfoto=$archivo->store('fotozzz','public');
            $marcavehiculo->usuarios_id=Auth::user()->id;
            $marcavehiculo->idmarcavehiculo=$ultimoIdMarcaVehiculo+1;
            $marcavehiculo->marcanombre=$request->marcavehiculo;
            $marcavehiculo->activo=1;

            
            try {
                $marcavehiculo->save();

                $marcas=$this->damemarcas();
                
                $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$marcavehiculo->urlfoto;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$marcavehiculo->urlfoto;
                copy($ruta_origen, $ruta_destino);

                return view('manten.marcavehiculo',compact('marcas'))->with('msgGuardado','Marca Guardada');
            } catch (\Exception $e) {
                return $e->getMessage();
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
        return view('fragm.mensajes')->with('msgGuardado','SHOW ID: '.$id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('fragm.mensajes')->with('msgGuardado','EDIT ID: '.$id);
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
        return view('fragm.mensajes')->with('msgGuardado','UPDATE ID: '.$id);
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


        //En la ruta(routes\web.php) puse lo siguiente:
        //Route::get('marcavehiculo/{id}/eliminar','marcavehiculocontrolador@destroy');
        //El enlace que puse para borrar en marcavehiculo.blade puse:
        //<a href="{{url('marcavehiculo/'.$marca->idmarcavehiculo.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
        //La ruta de acceso resultante es:
        //http://localhost:8000/marcavehiculo/17/eliminar
        //donde 17 es el ID campo clave de lo q se desea borrar

        //$marcavehiculo=marcavehiculos::find($id);
        //$marcavehiculo->activo=0;
        //$marcavehiculo->save();
        //Se tiene una marca borrada, si se vuelve a ingresar la misma marca
        //indica q ya existe porque solo se cambio el estado a activo=0
        //asi que mejor lo borramos

        //Primero Borrar imagen del storage
        $imagen=marcavehiculo::find($id);
        //Aquí antes de borrar debe verificarse en repuestos
        //que no se ha utilizado el ID de la marca del repuesto
        //por el tema de la integridad referencial.
        $path= storage_path().'/app/public/'.$imagen->urlfoto;
        //unlink($path);
        $r=File::delete($path);


        //Borrar registro
        //marcavehiculo::destroy($id); //borrar sabiendo su id y no es necesario
        //el find de arriba en $imagen. En este caso se usa delete porque primero
        //se borra la imagen del Storage.
        //Arriba se importó use Illuminate\Support\Facades\Storage;
        $imagen->delete();



        $marcas=$this->damemarcas();
        return view('manten.marcavehiculo',compact('marcas'))->with('msgGuardado','Marca Eliminada.');
    }


}
