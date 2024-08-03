<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\modelovehiculo; //lo agregué
use App\marcavehiculo; //lo agregué
use Illuminate\Support\Facades\Storage; //lo agregué
use Illuminate\Support\Facades\File; //lo agregué
use Illuminate\Support\Facades\Auth; 
use Session; //lo agregué

class modelovehiculocontrolador extends Controller
{

    private $modelos;
    private $marcas;

    private function validaSesion()
    {
        //Valida sesión: Revisar Handler.php en app\Exception, método render()
        // repuestos/Exceptions/Handler.php
        abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
    }

    public function vermodelos($idMarca)
    {
        //$this->validaSesion();
        $modelos=$this->dame_modelos_por_marca($idMarca);
        $v=view('fragm.modelovehiculo_ver',compact('modelos'))->render();
        return $v;
    }

    public function dame_modelos($idMarca)
    {
        // $this->validaSesion();
        $modelos=$this->dame_modelos_por_marca($idMarca);
        return $modelos->toJson();
    }

    public function dame_un_modelo($id)
    {
        //$this->validaSesion();
        $mm=modelovehiculo::find($id)->toJson();
        return $mm;
    }

    public function dame_modelos_por_marca($idMarca)
    {
        //$mm=modelovehiculo::where('activo','=',1)->paginate(3);
        $mm=modelovehiculo::where('activo','=',1)
                ->where('marcavehiculos_idmarcavehiculo',$idMarca)
                ->orderBy('modelonombre','ASC')
                ->get();
        return $mm;
    }

    private function damemarcas()
    {
        $m=marcavehiculo::where('activo','=',1)->select('idmarcavehiculo','marcanombre')->orderBy('marcanombre')->get();
        return $m;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$this->validaSesion();

        //$modelos=$this->damemodelos();
        $marcas=$this->damemarcas();
        //dd($modelos);
        return view('manten.modelovehiculo',compact('marcas'));
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

        // $this->validaSesion();

            //Reglas de Validación
            $reglas=[
                'modelovehiculo'=>'required|max:200',
                'aniosvehiculo'=>'required|size:9'
            ];



            //$reglas['archivo']='required|max:200|mimes:jpeg,png'; //maximo 200 kilobytes

            $mensajes=array(
                'modelovehiculo.required'=>'Debe Ingresar el Modelo.',
                'modelovehiculo.max'=>'El modelo debe tener como máximo 200 caracteres.',
                'aniosvehiculo.required'=>'Debe ingresar años',
                'aniosvehiculo.size'=>'Debe tener 9 digitos en formato 9999-9999',
                'archivo.required'=>'Debe elegir una imagen.',
                'archivo.max'=>'El tamaño de archivo no debe ser mayor a 200Kb.',
                'archivo.mimes'=>'El tipo de archivo debe ser una imagen jpg o png.'
            );

            $this->validate($request,$reglas,$mensajes);


            $archivo=$request->file('archivo');

            if($request->modifika==0) //nuevo
            {


                if(is_null($archivo))
                {
                    return response()->json(["ERROR"=>"No ha elegido imagen..."],500);
                }else{
                    if(filesize($archivo)>200000)
                    {
                        return response()->json(["ERROR"=>"Imagen muy grande..."],500);
                    }else{
                        $tipos=['jpg','jpeg','png'];
                        $m=substr($archivo->getClientMimeType(),6); // viene así: image/jpeg, image/png
                        //return response()->json(["ERROR"=>$m],500);
                        if(!in_array($m,$tipos))
                            return response()->json(["ERROR"=>"Elija tipo de imagen JPG o PNG"],500);
                    }

                }
                //return response()->json(["ERROR"=>"YUPI"],500);
                $modelovehiculo= new modelovehiculo;


            }else{ //Modifica

                if(!is_null($archivo))
                {
                    if(filesize($archivo)>200000)
                    {
                        return response()->json(["ERROR"=>"Imagen muy grande..."],500);
                    }else{
                        $tipos=['jpg','jpeg','png'];
                        $m=substr($archivo->getClientMimeType(),6); // viene así: image/jpeg, image/png
                        //return response()->json(["ERROR"=>$m],500);
                        if(!in_array($m,$tipos))
                            return response()->json(["ERROR"=>"Elija tipo de imagen JPG o PNG"],500);
                    }

                }
                $modelovehiculo=modelovehiculo::find($request->ide);

            }

            //$nombreArchivo=$archivo->getClientOriginalName();
            //$archivo=$request->file('archivo');
            if(!is_null($archivo))
            {
                $modelovehiculo->urlfoto=$archivo->store('fotozzz','public');
            }

            $modelovehiculo->usuarios_id=Auth::user()->id;
            $modelovehiculo->marcavehiculos_idmarcavehiculo=$request->cboMarcaVehiculo;
            $modelovehiculo->modelonombre=$request->modelovehiculo;
            $modelovehiculo->anios_vehiculo=$request->aniosvehiculo;
            $modelovehiculo->zofri=($request->zofri=="true") ? 1 : 0 ;
            $modelovehiculo->activo=1;

            $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$modelovehiculo->urlfoto;
            $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$modelovehiculo->urlfoto;
            copy($ruta_origen, $ruta_destino);

            $modelovehiculo->save();

            $modelos=$this->dame_modelos_por_marca($modelovehiculo->marcavehiculos_idmarcavehiculo);
            //return view('manten.modelovehiculo',compact('modelos','marcas'))->with('msgGuardado','Modelo '.$request->modelovehiculo.' Guardado');
            $v=view('fragm.modelovehiculo_ver',compact('modelos'))->render();
            return $v;

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
        return "SOY EDIT";
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
        $this->validaSesion();

        $imagen=modelovehiculo::find($id);
        $idmarca=$imagen->marcavehiculos_idmarcavehiculo;
        //Aquí antes de borrar debe verificarse en repuestos
        //que no se ha utilizado el ID de la marca del repuesto
        //por el tema de la integridad referencial.

        $hay=modelovehiculo::where('urlfoto','=',$imagen->urlfoto)->count();
        //Cuando subo la misma imagen dos veces, no genera otro archivo con
        //nombre aleatorio, utiliza el mismo... entonces
        //al borrar un registro, borra la imagen para ambos...
        //30set2019 CONSIDERAR QUE otras paginas como repuestos, marcas, parametros, etc, pueden utilizar la misma imagen
        //por ello hay que ver la forma de subsanar.
        //01oct2019 y si no borro físicamente las imágenes?
        if($hay==1)
        {
            //Borra el archivo del storage
            $path= storage_path().'/app/public/'.$imagen->urlfoto;
            $r=File::delete($path);
        }
        $imagen->delete(); //Borra el registro de la TABLA
        $modelos=$this->dame_modelos_por_marca($idmarca);
        $v=view('fragm.modelovehiculo_ver',compact('modelos'))->render();
        return $v;
    }
}
