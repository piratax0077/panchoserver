<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\local;
use App\repuesto;
use App\solicitudes;
use App\pais;
use App\permissions_detail;
use App\devolucion_mercaderia;
use App\ticket;
use Carbon\Carbon; // para tratamiento de fechas
use Illuminate\Support\Facades\Auth; 

class inventario_controlador extends Controller
{
    //
    public function index(){
        $locales=local::where('activo',1)->get();
        if(count($locales)>0){
            return view('inventario.principal',['locales' => $locales]);
        }else{
            return "cero";
        }
        
    }

    public function inventario_por_local($id){
        /* 1 == Bodega 
           3 == Tienda
        
            id_familia = 312 ES FAMILIA SI DEFINIR
           */
          $id_familia_sin_definir = 312;
        try{
            $repuestos_primera_ubicacion = repuesto::where('local_id', $id)->where('local_id_dos','!=',$id)->where('local_id_tres','!=',$id)->where('id_familia','!=',$id_familia_sin_definir)->where('stock_actual','>',0)->where('activo',1)->get();
            $repuestos_segunda_ubicacion = repuesto::where('local_id_dos', $id)->where('local_id','!=',$id)->where('local_id_tres','!=',$id)->where('id_familia','!=',$id_familia_sin_definir)->where('stock_actual_dos','>',0)->where('activo',1)->get();
            $repuestos_tercera_ubicacion = repuesto::where('local_id_tres', $id)->where('local_id','!=',$id)->where('local_id_dos','!=',$id)->where('id_familia','!=',$id_familia_sin_definir)->where('stock_actual_tres','>',0)->where('activo',1)->get();
            $repuestos = $repuestos_primera_ubicacion->mergeRecursive($repuestos_segunda_ubicacion)->mergeRecursive($repuestos_tercera_ubicacion);
            $local_id = $id;
            $v = view('fragm.inventario_por_bodega',compact('repuestos','local_id'))->render();
            return $v;
        }catch(Exception $e){
            return $e->getMessage();
        }
            
    }

    public function traslado(Request $request){
        $repuesto_id= $request->id_repuesto;
        $cantidad = $request->cantidad;
        try {
            $nueva_solicitud = new solicitudes;
            $nueva_solicitud->id_repuestos = intval($repuesto_id);
            $nueva_solicitud->usuario_id = Auth::user()->id;
            $nueva_solicitud->cantidad = intval($cantidad);
            $nueva_solicitud->activo = 1;
            $nueva_solicitud->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function ticket(){
        $tickets = ticket::select('ticket.*','users.name')->join('users','ticket.usuario_id','users.id')->orderBy('ticket.id','desc')->get();
        $completados = 0;
        $ingresados = 0;
        $en_proceso = 0;
        foreach($tickets as $t){
            if($t->estado == 3){
                $completados++;
            }elseif($t->estado == 1){
                $en_proceso++;
            }else{
                $ingresados++;
            }
        }
        return view('inventario.ticket',[
            'tickets' => $tickets,
            'completados' => $completados, 
            'ingresados' => $ingresados, 
            'en_proceso' => $en_proceso
        ]);
    }

    public function dameticket($id){
        $ticket = ticket::find($id)->toJson();
        return $ticket;
    }

    public function guardar_ticket(Request $r){
        
        //Reglas de Validación
        $reglas=[
            'descripcion'=>'required|max:600',
            'imagen' => 'required'
        ];

        // Validación de imagen
        
        
     
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
            'descripcion.required'=>'Debe Ingresar una descripción para el ticket',
            'descripcion.max'=>'El nombre del parámetro debe tener como máximo 600 caracteres.',
        ];

        //
        
        //$this->validate($r,$reglas,$mensajes); //Validación
        
        try
        {
        if($r->modifika==0) //nuevo
        {
            $t=new ticket;
            $t->usuario_id=Auth::user()->id;
            $t->descripcion=$r->descripcion;
            $t->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $t->activo = 1;
            $t->estado = $r->estado;
            if($r->foto==0)
            {
                $t->image_path=$r->valor;
            }else{
                $t->image_path=$archivo->store('fotozzz','public');
                $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$t->image_path;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$t->image_path;
                copy($ruta_origen, $ruta_destino);
            }

        }else{ //Modificación
            $t=ticket::find($r->ide);
            return $t;
            $t->usuario_id=Auth::user()->id;
            $t->descripcion=$r->descripcion;
            $t->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $t->activo = 1;
            $t->estado = $r->estado;
            if($r->foto==0)
            {
                $t->image_path=$r->valor;
            }else{
                if(!is_null($archivo))
                    {
                        $t->image_path=$archivo->store('fotozzz','public');
                        $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$t->image_path;
                        $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$t->image_path;
                        copy($ruta_origen, $ruta_destino);
                    }
                }


            }
            $t->save();
           
            return "OK";
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    public function damerepuesto($id){
        // $repuesto = repuesto::where('id', $id)
        // ->get();

        $repuesto=repuesto::where('repuestos.id',$id)
                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                    ->select('proveedores.empresa_nombre','repuestos.*')
                    ->get();
        
        return $repuesto;
    }

    public function eliminarticket($id){
        $ticket = ticket::find($id);
        $ticket->delete();
        return redirect('/ticket');
    }

    

    public function pedidos_a_bodega_vista(){
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso->path_ruta == '/solicitudes'){
                    $solicitudes = devolucion_mercaderia::where('activo',1)->orderBy('created_at','desc')->groupBy('num_nc')->get();
                    
                    return view('inventario.pedidos_a_bodega',['solicitudes' => $solicitudes]);
                    }
            }
            $user = Auth::user();
            if($user->rol->nombrerol === "Administrador"){
                $solicitudes = devolucion_mercaderia::where('activo',1)->orderBy('created_at','desc')->groupBy('num_nc')->get();
                
                return view('inventario.pedidos_a_bodega',['solicitudes' => $solicitudes]);
            }else{
                return redirect('home');
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }
    public function revisar_solicitud(){
        $user_id = Auth::user()->id;
        $solicitudes = devolucion_mercaderia::where('activo',1)->get();
        return count($solicitudes);
    }

    public function eliminar_solicitud($id){

        $solicitud = solicitudes::where('id',$id)->first();
        $solicitud->activo = 0;
        $solicitud->save();
        $solicitudes = solicitudes::where('activo',1)->get();
        
        $v = view('fragm.tarjetas_pedidos',compact('solicitudes'))->render();
        return $v;
    }

    public function dame_solicitudes(){
        try {
            $solicitudes = devolucion_mercaderia::where('activo',1)->orderBy('created_at','desc')->groupBy('num_nc')->get();
            $v = view('fragm.tarjetas_pedidos',compact('solicitudes'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function procesar_solicitud(Request $req){
        $id_repuesto = $req->id_repuesto;
        $id_solicitud = $req->id_solicitud;
        $cantidad = intval($req->cantidad);
        
        try {
            $repuesto = repuesto::find($id_repuesto);
            $solicitud = solicitudes::find($id_solicitud);
                if($repuesto->local_id === 1){
                    $repuesto->stock_actual = $repuesto->stock_actual-$cantidad;
                    $repuesto->local_id_dos = 3;
                    $repuesto->stock_actual_dos = $repuesto->stock_actual_dos + $cantidad;

                    $solicitud->activo = 0;

                    $solicitud->save();
                    $repuesto->save();
                }
                return $repuesto;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
