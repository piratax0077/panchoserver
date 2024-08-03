<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\parametro;
use Illuminate\Support\Facades\File;
use Session;
use App\permissions_detail;
use Illuminate\Support\Facades\Auth; 

class parametros_controlador extends Controller
{
    private function validaSesion()
    {
        //Valida sesión: Revisar repuestos/Exceptions/Handler.php, método render()

        abort_if(Auth::user()->rol->nombrerol !== "Administrador",403);
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

    public function index()
    {
        //$this->validaSesion();
        $confirma = $this->confirmaringreso('/parametros');
        if($confirma){
            $parametros=parametro::orderBy('codigo','ASC')->get();
        
            return view('manten.parametros',compact('parametros'));
        }else return redirect('home');
        
    }

    public function dameparametro($id)
    {
        $this->validaSesion();
        $parametro=parametro::find($id)->toJson();
        return $parametro;
    }

    public function guardar(Request $r)
    {
        $this->validaSesion();

        //Reglas de Validación
        $reglas=[
            'codigo'=>'required|max:100',
            'nombre'=>'required|max:100'
        ];

        if($r->foto==1)
        {
            $archivo=$r->file('imagen');
            if(is_null($archivo))
            {
                return response()->json(["ERROR"=>"No ha elegido imagen..."],500);
            }else{
                if(filesize($archivo)>500000)
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
        }
        //Mensajes de error para validación
        $mensajes=[
            'codigo.required'=>'Debe Ingresar el código del parámetro.',
            'codigo.max'=>'El código del parámetro debe tener como máximo 100 caracteres.',
            'nombre.required'=>'Debe Ingresar el nombre del parámetro.',
            'nombre.max'=>'El nombre del parámetro debe tener como máximo 100 caracteres.',
            'imagen.required'=>'Debe elegir una imagen.',
            'imagen.max'=>'El tamaño de archivo no debe ser mayor a 200Kb.',
            'imagen.mimes'=>'El tipo de archivo debe ser una imagen jpg o png.'
        ];

        //

        $this->validate($r,$reglas,$mensajes); //Validación

        try
        {
        if($r->modifika==0) //nuevo
        {
            $p=new parametro;
            $p->codigo=$r->codigo;
            $p->nombre=$r->nombre;
            $p->descripcion=$r->descripcion;
            $p->usuarios_id=Auth::user()->id;

            if($r->foto==0)
            {
                $p->valor=$r->valor;
            }else{
                $p->valor=$archivo->store('fotozzz','public');
                $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$p->valor;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$p->valor;
                copy($ruta_origen, $ruta_destino);
            }

        }else{ //Modificación
            $p=parametro::find($r->ide);
            $p->codigo=$r->codigo;
            $p->nombre=$r->nombre;
            $p->descripcion=$r->descripcion;

            if($r->foto==0)
            {
                $p->valor=$r->valor;
            }else{
                if(!is_null($archivo))
                {
                    $p->valor=$archivo->store('fotozzz','public');
                    $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$p->valor;
                    $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$p->valor;
                    copy($ruta_origen, $ruta_destino);
                }
            }


        }
        $p->save();
        //recargar valores de los parámetros a las variables d sesion
        $parametros=parametro::select('codigo','valor')->get();
        foreach($parametros as $p)
        {
            Session::put($p->codigo,$p->valor);
        }
        return "OK";
    }catch (\Exception $error){
        
        $debug=$error;
        $v=view('errors.debug_ajax',compact('debug'))->render();
        return $v;
    }

    }

    public function eliminarparametro($id)
    {
        $this->validaSesion();
        //primero borrar la imagen del storage
        $p=parametro::find($id);
        if(substr($p->valor,0,4)=="foto")
        {
            $path= storage_path().'/app/public/'.$p->valor;
            //unlink($path);
            //$debug=$path;
            //return view('errors.debug_ajax',compact('debug'));
            $r=File::delete($path);
            $p->delete();
        }
        $p->delete();
        $parametros=parametro::all();
        return view('manten.parametros',compact('parametros'));



    }

}
