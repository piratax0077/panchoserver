<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cajero;
use App\carrito_virtual;
use App\carrito_virtual_detalle;
use App\carrito_transferido;
use App\local;
use App\repuesto;
use App\solicitudes;
use App\oem;
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
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/solicitudes'){
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

    public function revisar_totem(){
        $user_id = Auth::user()->id;
        // el usuario 16 corresponde a los carros enviados desde el totem
        $solicitudes = carrito_transferido::select(
            'carrito_transferido.*',
            'repuestos.codigo_interno',
            'repuestos.descripcion',
            'repuestos.oferta',
            'repuestos.precio_venta',
            'repuestos.id as idrepuesto',
            'u1.name as usuario_nombre',
            'u2.name as bodeguero_nombre'
                )
                ->join('repuestos', 'carrito_transferido.id_repuestos', '=', 'repuestos.id')
                ->join('users as u1', 'carrito_transferido.usuarios_id', '=', 'u1.id') // Alias para usuario
                ->leftjoin('users as u2', 'carrito_transferido.id_bodeguero', '=', 'u2.id') // Alias para bodeguero
                ->where('carrito_transferido.estado', 1)
                ->distinct()
                ->groupBy('carrito_transferido.cliente_id')
                ->orderBy('carrito_transferido.id', 'asc')
                ->get();
        
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

    public function dame_ventas_totem(){
        try {
            $solicitudes = carrito_transferido::select(
                'carrito_transferido.*',
                'repuestos.codigo_interno',
                'repuestos.descripcion',
                'repuestos.oferta',
                'repuestos.precio_venta',
                'repuestos.id as idrepuesto',
                'u1.name as usuario_nombre',
                'u2.name as bodeguero_nombre'
                    )
                    ->join('repuestos', 'carrito_transferido.id_repuestos', '=', 'repuestos.id')
                    ->join('users as u1', 'carrito_transferido.usuarios_id', '=', 'u1.id') // Alias para usuario
                    ->leftjoin('users as u2', 'carrito_transferido.id_bodeguero', '=', 'u2.id') // Alias para bodeguero
                    ->where('carrito_transferido.estado', 1)
                    ->distinct()
                    ->groupBy('carrito_transferido.cliente_id')
                    ->orderBy('carrito_transferido.id', 'asc')
                    ->get();


                foreach($solicitudes as $item){
                    // Convertir a objeto Carbon
                    $carbon = Carbon::parse($item->created_at);
                
                    // Asignar fecha y hora correctamente
                    $item->fecha = $carbon->format('d-m-Y');
                    $item->hora = $carbon->format('H:i:s');
                }
            $v = view('fragm.tarjetas_pedido_totem',compact('solicitudes'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function aceptar_totem($id){
        try {
            $el_id_usuario = Auth::user()->id; 
            $cajero = cajero::all();
            $carrito = carrito_virtual::find($id);
            $detalle = carrito_virtual_detalle::where('carrito_numero',$carrito->id)->get();
            $cliente_id = 4;
            foreach($detalle as $cc)
            {
                $repuesto = repuesto::find($cc->repuesto_id);
                $ct=new carrito_transferido;
                $ct->usuarios_id = $el_id_usuario;
                $ct->cajeros_id = $cajero[0]->id;
                $ct->cliente_id = $cliente_id;
                $ct->item=$cc->item;
                $ct->id_repuestos=$cc->repuesto_id;
                $ct->id_local=1; // por defecto
                $ct->id_unidad_venta=$repuesto->id_unidad_venta;
                $ct->cantidad=$cc->cantidad;
                $ct->pu=$cc->pu;
                $ct->subtotal_item=$cc->subtotal_item;
                $ct->descuento_item=$cc->descuento_item;
                $ct->total_item=$cc->total_item;
                $ct->titulo = 'TOTEM';
                $ct->save();
    
                // auth()->user()->notify(new CarritoTransferidoNotification($ct));
    
                // User::all()
                // ->except($ct->usuarios_id)
                // ->each(function(User $user) use ($ct){
                //     $user->notify(new CarritoTransferidoNotification($ct));
                // });
    
            }

            return 'OK';
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
    }

    public function transferir_carrito_completo($id, $cliente_id, $titulo){
        $el_id_usuario=Auth::user()->id;
        $carrito_compra = carrito_compra::where('usuarios_id', $el_id_usuario)->get();
        foreach($carrito_compra as $cc)
        {
            $ct=new carrito_transferido;
            $ct->usuarios_id = $el_id_usuario;
            $ct->cajeros_id = $id;
            $ct->cliente_id = $cliente_id;
            $ct->item=$cc->item;
            $ct->id_repuestos=$cc->id_repuestos;
            $ct->id_local=$cc->id_local;
            $ct->id_unidad_venta=$cc->id_unidad_venta;
            $ct->cantidad=$cc->cantidad;
            $ct->pu=$cc->pu;
            $ct->subtotal_item=$cc->subtotal_item;
            $ct->descuento_item=$cc->descuento_item;
            $ct->total_item=$cc->total_item;
            $ct->titulo = $titulo;
            $ct->save();

            // auth()->user()->notify(new CarritoTransferidoNotification($ct));

            // User::all()
            // ->except($ct->usuarios_id)
            // ->each(function(User $user) use ($ct){
            //     $user->notify(new CarritoTransferidoNotification($ct));
            // });

        }
        return 'OK';
    }

    public function dame_venta_totem($id_carrito){

        try {
    
            $detalle = carrito_transferido::select(
                    'carrito_transferido.*',
                    'repuestos.codigo_OEM_repuesto as codigo_oem',
                    'repuestos.codigo_interno',
                    'repuestos.oferta',
                    'repuestos.descripcion',
                    'repuestos.id_familia',
                    'repuestos.cod_repuesto_proveedor',
                    'repuestos.precio_venta',
                    'repuestos.id as idrepuesto',
                    'ofertas_pagina_web.activo',
                    'marcarepuestos.marcarepuesto',
                    'users.name',
                    'locales.local_nombre'
                )
                ->where('carrito_transferido.cliente_id',$id_carrito)
                ->where('carrito_transferido.estado',1)
                ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                ->join('locales','carrito_transferido.id_local','locales.id')
                ->join('users','carrito_transferido.usuarios_id','users.id')
                ->leftJoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                ->orderBy('carrito_transferido.item','ASC')
                ->get();
    
    
            $productos_sustitutos = [];
    
    
            foreach($detalle as $d){
    
                $d->id_bodeguero = Auth::user()->id;
                $d->save();
    
    
                // ✅ 1) buscar OEM del repuesto actual
    
                $oems = oem::where('id_repuestos',$d->idrepuesto)
                    ->where('activo',1)
                    ->pluck('codigo_oem')
                    ->toArray();
    
    
                if(count($oems) == 0){
                    continue;
                }
    
    
                // ✅ 2) buscar todos los repuestos con esos OEM
    
                $idsRelacionados = oem::whereIn('codigo_oem',$oems)
                    ->where('activo',1)
                    ->pluck('id_repuestos')
                    ->toArray();
    
    
                if(count($idsRelacionados) == 0){
                    continue;
                }
    
    
                // ✅ 3) buscar repuestos sustitutos
    
                $sustitutos = repuesto::whereIn('repuestos.id',$idsRelacionados)
    
                    ->whereRaw('(stock_actual > 0 OR stock_actual_dos > 0 OR stock_actual_tres > 0)')

                    ->where('repuestos.activo',1)
    
                    ->where('repuestos.id','!=',$d->idrepuesto)
    
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
    
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
    
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
    
                    ->select(
                        'proveedores.empresa_nombre_corto',
                        'paises.nombre_pais',
                        'marcarepuestos.marcarepuesto',
                        'repuestos.*'
                    )
    
                    ->get();
    
    
                if($sustitutos->count() > 0){
    
                    $productos_sustitutos[$d->idrepuesto] = $sustitutos;
    
                }
    
            }
    
    
            $v = view(
                'fragm.repuestos_totem',
                [
                    'repuestos' => $detalle,
                    'num_nc' => $id_carrito,
                    'productos_sustitutos' => $productos_sustitutos
                ]
            )->render();
    
    
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
