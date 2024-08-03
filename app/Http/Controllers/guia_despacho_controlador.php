<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Debugbar;
use Session;
use App\cliente_modelo;
use Carbon\Carbon; // para tratamiento de fechas
use App\proveedor;
use App\cotizacion;
use App\cotizacion_detalle;
use App\correlativo;
use App\guia_de_despacho;
use App\guia_de_despacho_detalle;
use App\repuesto;
use App\local;
use App\traspaso_mercaderia;
use App\traspaso_mercaderia_detalle;
use App\permissions_detail;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\nota_de_debito;
use App\nota_de_debito_detalle;
use App\servicios_sii\ClsSii;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Auto;
use App\servicios_sii\Sii;
use App\boleta;
use App\boleta_detalle;
use App\factura;
use App\factura_detalle;
use App\pago;
use App\devolucion_mercaderia;
use App\devolucion_mercaderia_detalle;

use Illuminate\Support\Facades\Auth;

class guia_despacho_controlador extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ventas.guia_despacho');
    }

    public function traspaso_mercaderia(){
        $usuario = Auth::user();
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/guiadespacho/traspaso_mercaderia'){
                    try {
                        $locales = local::where('activo',1)->get();
                        
                        $traspaso_mercaderia = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->first();
                        if($traspaso_mercaderia){
                            $num_solicitud = $traspaso_mercaderia->num_solicitud;
                            $detalle = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                        ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$traspaso_mercaderia->id)
                                                        ->where('traspaso_mercaderia_detalle.activo',1)
                                                        ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                        ->get();
                            $historial = traspaso_mercaderia::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                            ->where('traspaso_mercaderia.usuario_id',$usuario->id)
                                                            ->where('traspaso_mercaderia.activo',0)
                                                            ->where('traspaso_mercaderia_detalle.activo',0)
                                                            ->where('traspaso_mercaderia_detalle.estado',1)
                                                            ->join('traspaso_mercaderia_detalle','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                            ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                            ->get();
            
                                                            
            
                            $v = view('ventas.traspaso_mercaderia',compact('locales','traspaso_mercaderia','detalle','num_solicitud','historial'))->render();
                            return $v;
                        }else{
                            $historial = traspaso_mercaderia::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                            ->where('traspaso_mercaderia.usuario_id',$usuario->id)
                                                            ->where('traspaso_mercaderia.activo',0)
                                                            ->where('traspaso_mercaderia_detalle.activo',0)
                                                            ->where('traspaso_mercaderia_detalle.estado',1)
                                                            ->join('traspaso_mercaderia_detalle','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                            ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                            ->get();
            
                            $v = view('ventas.traspaso_mercaderia',compact('locales','traspaso_mercaderia','historial'))->render();
                            return $v;
                        }
                        
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                    }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if($usuario->rol->nombrerol == "Administrador"){
            try {
                $locales = local::where('activo',1)->get();
                $usuario = Auth::user();
                $traspaso_mercaderia = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->first();
                if($traspaso_mercaderia){
                    $num_solicitud = $traspaso_mercaderia->num_solicitud;
                    $detalle = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$traspaso_mercaderia->id)
                                                ->where('traspaso_mercaderia_detalle.activo',1)
                                                ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                ->get();
                    $historial = traspaso_mercaderia::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                    ->where('traspaso_mercaderia.usuario_id',$usuario->id)
                                                    ->where('traspaso_mercaderia.activo',0)
                                                    ->where('traspaso_mercaderia_detalle.activo',0)
                                                    ->where('traspaso_mercaderia_detalle.estado',1)
                                                    ->join('traspaso_mercaderia_detalle','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                    ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                    ->get();
    
                                                    
    
                    $v = view('ventas.traspaso_mercaderia',compact('locales','traspaso_mercaderia','detalle','num_solicitud','historial'))->render();
                    return $v;
                }else{
                    $historial = traspaso_mercaderia::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                                    ->where('traspaso_mercaderia.usuario_id',$usuario->id)
                                                    ->where('traspaso_mercaderia.activo',0)
                                                    ->where('traspaso_mercaderia_detalle.activo',0)
                                                    ->where('traspaso_mercaderia_detalle.estado',1)
                                                    ->join('traspaso_mercaderia_detalle','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                    ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                    ->get();
    
                    $v = view('ventas.traspaso_mercaderia',compact('locales','traspaso_mercaderia','historial'))->render();
                    return $v;
                }
                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else{
            return redirect('/home');
        }
        
        
    }

    public function recepcion_mercaderia(){
        try {

            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/guiadespacho/traspaso_mercaderia'){
                    return view('ventas.recepcion_mercaderia');
                }
            }

            if(Auth::user()->rol->nombrerol == "Administrador"){
                return view('ventas.recepcion_mercaderia');
            }else return redirect('home');
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_nc($dato){
        list($doc,$n)=explode("-",$dato);
        $tip_doc=substr($doc,0,2); //bo
        $num_doc=$n; //el número buscado
        $locales = local::where('activo',1)->get();
      
        try {
            if($tip_doc=='bo')
            {
                $documento="Boleta";
                $num_documento=$num_doc;
                //Buscar si no se emitió nota de crédito para este documento
                $nc=nota_de_credito::where('docum_referencia','LIKE','bo*'.$num_documento.'%')->first();
                
                if(is_null($nc))
                {
                    return "rLa boleta N° ".$num_doc." NO tiene Nota de Crédito";
                }

                
                $buscado_doc=boleta::where('num_boleta',$num_doc)
                                    ->where('estado_sii','ACEPTADO')
                                    ->first();
         
              
                if(!is_null($buscado_doc))
                {
                    $buscado_doc=$buscado_doc->toArray();
                    $id_documento=$buscado_doc['id'];
                    $fecha_documento=$buscado_doc['fecha_emision'];//Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                    $idcliente=$buscado_doc['id_cliente'];

                    if($idcliente==0){ //boleta sin cliente
                        $cliente=cliente_modelo::where('rut','666666666')->first()->toArray();
                    }else{
                        $cliente=cliente_modelo::where('id',$idcliente)->first()->toArray();
                    }

                    $cliente_id=$cliente['id'];
                    $cliente_rut=$cliente['rut'];
                    $cliente_razon_social=$cliente['razon_social'];
                    $cliente_giro=$cliente['giro'];

                    $cliente_direccion=$cliente['direccion'];
                    $cliente_comuna=$cliente['direccion_comuna'];
                    $cliente_ciudad=$cliente['direccion_ciudad'];
                    $detalle=boleta_detalle::select('boletas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                            ->where('boletas_detalle.id_boleta',$buscado_doc['id'])
                                                            ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                            ->get();



                    $v = view('fragm.devolucion_mercaderia_documento',
                        compact('documento','id_documento',
                                        'num_documento',
                                        'fecha_documento',
                                        'cliente_id',
                                        'cliente_rut',
                                        'cliente_razon_social',
                                        'cliente_giro',
                                        'cliente_direccion',
                                        'cliente_comuna',
                                        'cliente_ciudad',
                                        'detalle',
                                        'nc',
                                        'locales'
                                        ))->render();
                    return $v;

                }else{
                    return "rBoleta N° ".$num_doc. " no existe o no fue aceptada por el SII.";
                }
            }

            if($tip_doc=='fa')
            {
                $documento="Factura";
                $num_documento=$num_doc;
                //Buscar si no se emitió nota de crédito para este documento
                $nc=nota_de_credito::where('docum_referencia','LIKE','fa*'.$num_documento.'%')->first();
                if(is_null($nc))
                {
                    return "rLa factura N° ".$num_doc." NO tiene Nota de Crédito";
                }

                $buscado_doc=factura::where('num_factura',$num_doc)
                                    ->where('estado_sii','ACEPTADO')
                                    ->first();
                if(!is_null($buscado_doc))
                {
                    $buscado_doc=$buscado_doc->toArray();
                    $id_documento=$buscado_doc['id'];
                    $fecha_documento=$buscado_doc['fecha_emision']; //Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                    $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                    $cliente_id=$cliente['id'];
                    $cliente_rut=$cliente['rut'];
                    $cliente_giro=$cliente['giro'];
                    $cliente_razon_social=$cliente['razon_social'];
                    if(substr($cliente_rut,0,5)=='00000')
                    {
                        $cliente_id="0";
                        $cliente_rut="Sin Cliente";
                        $cliente_razon_social="";
                    }

                    $cliente_direccion=$cliente['direccion'];
                    $cliente_comuna=$cliente['direccion_comuna'];
                    $cliente_ciudad=$cliente['direccion_ciudad'];
                    $detalle=factura_detalle::select('facturas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                            ->where('facturas_detalle.id_factura',$buscado_doc['id'])
                                            ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                            ->get();

                    //dd($buscado_doc);
                    //return $buscado_doc['fecha_emision'];
                    $v = view('fragm.devolucion_mercaderia_documento',
                        compact('documento','id_documento',
                                        'num_documento',
                                        'fecha_documento',
                                        'cliente_id',
                                        'cliente_rut',
                                        'cliente_razon_social',
                                        'cliente_giro',
                                        'cliente_direccion',
                                        'cliente_comuna',
                                        'cliente_ciudad',
                                        'detalle',
                                        'nc',
                                        'locales'
                                        ))->render();
                    return $v;

                }else{
                    return "rFactura N° ".$num_doc. " no existe o no fue aceptada por el SII.";
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_nc_nueva($numero_nc){
        $nc=nota_de_credito::select('notas_de_credito.*','users.name')
                            ->join('users','notas_de_credito.usuarios_id','users.id')
                            ->where('notas_de_credito.num_nota_credito',$numero_nc)
                            ->where('notas_de_credito.devuelto',0)
                            ->first();
        if($nc){
            list($doc,$n,$fecha)=explode("*",$nc->docum_referencia);
            $num_doc=$n; //el número buscado
            $locales = local::where('activo',1)->get();
            try {
                if($doc == "bo"){
                    $tipo_documento = "Boleta";
                    $buscado_doc=boleta::select('num_boleta as num_doc','id')
                                        ->where('num_boleta',$num_doc)
                                        ->where('estado_sii','ACEPTADO')
                                        ->first();
                    if(!is_null($buscado_doc))
                    {
                        
                        $detalle = boleta_detalle::select('boletas_detalle.id_boleta as id_doc','boletas_detalle.id_repuestos','boletas_detalle.cantidad','repuestos.*','locales.local_nombre')
                                                    ->join('locales','boletas_detalle.id_local','locales.id')
                                                    ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                    ->where('boletas_detalle.id_boleta',$buscado_doc->id)
                                                    ->get();
                       $devolucion = devolucion_mercaderia::where('usuario_id',Auth::user()->id)->where('activo',1)->first();
                       $devoluciones_detalle = $this->damedevoluciones($numero_nc);

                       $total = 0;

                       foreach($devoluciones_detalle as $d){
                            $total += $d->precio_venta * $d->cantidad;
                       }
                       
                        $v = view('fragm.devolucion_mercaderia_nuevo',[
                            'documento' => $buscado_doc,
                            'tipo_documento' => $tipo_documento,
                            'detalle' => $detalle,
                            'locales' => $locales,
                            'nc' => $nc,
                            'devoluciones' => $devoluciones_detalle,
                            'total' => $total
                            ])->render();
                        
                        return $v;
                       
                        
                        
                    }else{
                        return "rBoleta N° ".$num_doc. " no existe o no fue aceptada por el SII.";
                    }
                }elseif($doc == "fa"){
                    $tipo_documento = "Factura";
                    $buscado_doc=factura::select('num_factura as num_doc','id')
                                        ->where('num_factura',$num_doc)
                                        ->where('estado_sii','ACEPTADO')
                                        ->first();
                    if(!is_null($buscado_doc))
                    {
                        
                        $detalle = factura_detalle::select('facturas_detalle.id_factura as id_doc','facturas_detalle.id_repuestos','facturas_detalle.cantidad','repuestos.*','locales.local_nombre')
                                                    ->join('locales','facturas_detalle.id_local','locales.id')
                                                    ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                    ->where('facturas_detalle.id_factura',$buscado_doc->id)
                                                    ->get();

                        $devoluciones_detalle = $this->damedevoluciones($numero_nc);


                        $v = view('fragm.devolucion_mercaderia_nuevo',[
                            'documento' => $buscado_doc,
                            'tipo_documento' => $tipo_documento,
                            'detalle' => $detalle,
                            'locales' => $locales,
                            'nc' => $nc,
                            'devoluciones' => $devoluciones_detalle
                            ])->render();

                        return $v;
                    }else{
                        return "rFactura N° ".$num_doc. " no existe o no fue aceptada por el SII.";
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else{
            return ['error' => 'No existe NC'];
        }
        
        
    }

    public function dame_doc_nuevo($numero_doc, $tipo_doc){
    
        
            $locales = local::where('activo',1)->get();
            try {
                if($tipo_doc == "bo"){
                    $tipo_documento = "Boleta";
                    $buscado_doc=boleta::select('boletas.*','boletas.num_boleta as num_doc')
                                        ->where('boletas.num_boleta',$numero_doc)
                                        ->where('boletas.estado_sii','ACEPTADO')
                                        ->where('boletas.devuelto',0)
                                        ->first();
                    if(!is_null($buscado_doc))
                    {
                       
                        $detalle = boleta_detalle::select('boletas_detalle.id_boleta as id_doc','boletas_detalle.id_repuestos','boletas_detalle.cantidad','repuestos.*','locales.local_nombre')
                                                    ->join('locales','boletas_detalle.id_local','locales.id')
                                                    ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                    ->where('boletas_detalle.id_boleta',$buscado_doc->id)
                                                    ->get();
                      
                       $devolucion = devolucion_mercaderia::where('usuario_id',Auth::user()->id)->where('activo',1)->first();
                       $devoluciones_detalle = $this->damedevoluciones($numero_doc);
                       
                        $v = view('fragm.devolucion_mercaderia_nuevo',[
                            'documento' => $buscado_doc,
                            'tipo_documento' => $tipo_documento,
                            'detalle' => $detalle,
                            'locales' => $locales,
                            'nc' => $buscado_doc,
                            'devoluciones' => $devoluciones_detalle
                            ])->render();
                        
                        return $v;
                       
                        
                        
                    }else{
                        return ['error' => "Documento N° ".$numero_doc. " no existe o ya fue procesada."];
                      
                    }
                }elseif($tipo_doc == "fa"){
                    $tipo_documento = "Factura";
                    $buscado_doc=factura::select('facturas.*','facturas.num_factura as num_doc')
                                        ->where('facturas.num_factura',$numero_doc)
                                        ->where('facturas.estado_sii','ACEPTADO')
                                        ->first();
                    if(!is_null($buscado_doc))
                    {
                        
                        $detalle = factura_detalle::select('facturas_detalle.id_factura as id_doc','facturas_detalle.id_repuestos','facturas_detalle.cantidad','repuestos.*','locales.local_nombre')
                                                    ->join('locales','facturas_detalle.id_local','locales.id')
                                                    ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                    ->where('facturas_detalle.id_factura',$buscado_doc->id)
                                                    ->get();

                        $devoluciones_detalle = $this->damedevoluciones($numero_doc);


                        $v = view('fragm.devolucion_mercaderia_nuevo',[
                            'documento' => $buscado_doc,
                            'tipo_documento' => $tipo_documento,
                            'detalle' => $detalle,
                            'locales' => $locales,
                            'nc' => $buscado_doc,
                            'devoluciones' => $devoluciones_detalle
                            ])->render();

                        return $v;
                    }else{
                        return "rFactura N° ".$numero_doc. " no existe o no fue aceptada por el SII.";
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        
        
        
    }

    public function devolucion(Request $req){
       
        try {
            $id_repuesto = $req->repuesto_id;
            $ubicacion = $req->local_id;
            $cantidad = $req->cantidad;
            $usuario_id = Auth::user()->id;
            $num_nc = $req->num_nc;
            $tipo_doc = $req->tipo_doc;

            $repuesto = repuesto::where('id',$id_repuesto)
                                ->where('local_id',1)
                                
                                ->where('activo',1)
                                ->first();

            $value = devolucion_mercaderia::where('num_nc',$num_nc)->where('repuesto_id',$id_repuesto)->where('activo',1)->first();
            if(isset($value)){
                //Repuesto ya esta ingresado a la devolución
                return 'error';
            }else{
                try {   
                    $correlativo = correlativo::find(10);
                    
                    $dm = new devolucion_mercaderia;
                    $dm->num_devolucion = intval($correlativo->correlativo) + 1;
                    $dm->tipo_doc = $tipo_doc;
                    $dm->num_nc= $num_nc;
                    $dm->repuesto_id = $id_repuesto;
                    $dm->usuario_id = $usuario_id;
                    $dm->local_id = $ubicacion;
                    $dm->cantidad = $cantidad;
                    $dm->fecha_emision = Carbon::today()->toDateString();
                    $dm->activo = 1;

                    $dm->save();

                    $devoluciones_detalle = $this->damedevoluciones($num_nc);

                    $correlativo->correlativo = intval($correlativo->correlativo) + 1;
                    $correlativo->save();

                    return view('fragm.devoluciones_table',['devoluciones' => $devoluciones_detalle])->render();
                

                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            }

            
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function cerrar_devolucion($num_nc){
       
        try {

            $devoluciones = $this->damedevoluciones($num_nc);
            if($devoluciones->count() == 0){
                return ['ERROR'];
            }
            $nc = nota_de_credito::where('num_nota_credito',$num_nc)->first();
            $nc->devuelto = 1;
            $nc->save();
            $v = view('fragm.devoluciones_table',['devoluciones' => $devoluciones,'admin' => true])->render();
            return ['OK',$v];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //Función creada para devoluciones de mercadería por vale de mercadería
    public function cerrar_devolucion_($num_doc,$tipo_doc){
        
        try {

            $devoluciones = $this->damedevoluciones($num_doc);
            if($devoluciones->count() == 0){
                return ['ERROR'];
            }
            if($tipo_doc == 'bo'){
                $b = boleta::where('num_boleta',$num_doc)->first();
                $b->devuelto = 1;
                $b->save();
            }else{
                $f = factura::where('num_factura',$num_doc)->first();
                $f->devuelto = 1;
                $f->save();
            }
            
            
            $v = view('fragm.devoluciones_table',['devoluciones' => $devoluciones,'admin' => true])->render();
            return ['OK',$v];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_devoluciones($num_nc){
        try {
            $repuestos = $this->dame_repuestos_devolucion($num_nc);
            $v = view('fragm.repuestos_devolucion',['repuestos' => $repuestos,'num_nc' => $num_nc])->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function opcion_devolucion(Request $req){
        $repuesto_id = $req->repuesto_id;
        $cantidad = $req->cantidad;
        $id_local = $req->local_id;
        $num_nc = $req->num_nc;
        $opcion = $req->opcion;
       if($opcion === '+'){
        try {
            $repuesto = repuesto::where('id',$repuesto_id)->where('activo',1)->first();
         
            if($repuesto->local_id == $id_local){
                $repuesto->stock_actual += $cantidad;
            }elseif($repuesto->local_id_dos == $id_local){
                $repuesto->stock_actual_dos += $cantidad;
            }elseif($repuesto->local_id_tres == $id_local){
                $repuesto->stock_actual_tres += $cantidad;
            }

            $repuesto->save();
            $devolucion = devolucion_mercaderia::where('num_nc',$num_nc)->where('repuesto_id',$repuesto_id)->where('activo',1)->first();
         
            $devolucion->activo = 0;
   
            $devolucion->save();
            $repuestos_devolucion = $this->dame_repuestos_devolucion($num_nc);
            $v = view('fragm.repuestos_devolucion',['repuestos' => $repuestos_devolucion,'num_nc' => $num_nc])->render();
            return $v;
            
            
            } catch (\Exception $e) {
                return $e->getMessage();
            }
       }else{
            $devolucion = devolucion_mercaderia::where('num_nc',$num_nc)->where('repuesto_id',$repuesto_id)->first();
            $devolucion->activo = 0;
            $devolucion->save();
            $repuestos_devolucion = $this->dame_repuestos_devolucion($num_nc);
            $v = view('fragm.repuestos_devolucion',['repuestos' => $repuestos_devolucion,'num_nc' => $num_nc])->render();
            return $v;
       }
        
        
    }

    public function dame_repuestos_devolucion($num_nc){
        
        $repuestos = devolucion_mercaderia::select('repuestos.*','devolucion_mercaderia.cantidad','locales.local_nombre','users.name','locales.id as local_id')
                                        ->join('repuestos','devolucion_mercaderia.repuesto_id','repuestos.id')
                                        ->join('locales','devolucion_mercaderia.local_id','locales.id')
                                        ->join('users','devolucion_mercaderia.usuario_id','users.id')
                                        ->where('devolucion_mercaderia.num_nc',$num_nc)
                                        ->where('devolucion_mercaderia.activo',1)
                                        ->get();
        return $repuestos;
    }

    public function devolucion_mercaderia_detalle($value,$repuesto,$cantidad,$opcion){
       
        $resp = devolucion_mercaderia_detalle::where('repuesto_id',$repuesto->id)->where('activo',1)->first();
        if(!$resp){
            $dm_detalle = new devolucion_mercaderia_detalle;
            $dm_detalle->id_devolucion_mercaderia = $value->id;
            $dm_detalle->repuesto_id = $repuesto->id;
            $dm_detalle->fecha_emision = Carbon::today()->toDateString();
            $dm_detalle->cantidad = $cantidad;
            $dm_detalle->activo = 1;
            $dm_detalle->local_id = $opcion;
            //Estado = 0 ----> Rechazado
            //Estado = 1 ----> Aceptado
            //Estado = 2 ----> Esperando
            $dm_detalle->estado = 2;
            $dm_detalle->save();
            return $dm_detalle;
        }else{
            return 'error';
        }
        
    }

    public function damedevoluciones($num_nc){
        $devoluciones = devolucion_mercaderia::select('repuestos.*','devolucion_mercaderia.cantidad','locales.local_nombre','users.name')
                                            ->join('repuestos','devolucion_mercaderia.repuesto_id','repuestos.id')
                                            ->join('locales','devolucion_mercaderia.local_id','locales.id')
                                            ->join('users','devolucion_mercaderia.usuario_id','users.id')
                                            ->where('devolucion_mercaderia.usuario_id',Auth::user()->id)
                                            ->where('devolucion_mercaderia.num_nc',$num_nc)
                                            ->where('devolucion_mercaderia.activo',1)
                                            ->get();
        return $devoluciones;
    }

    public function damedevoluciones_realizadas($num_nc){
        $devoluciones_realizadas = devolucion_mercaderia::select('repuestos.*','devolucion_mercaderia.cantidad','locales.local_nombre','users.name','devolucion_mercaderia.fecha_emision','devolucion_mercaderia.updated_at as fecha_actualizacion')
                                            ->join('repuestos','devolucion_mercaderia.repuesto_id','repuestos.id')
                                            ->join('locales','devolucion_mercaderia.local_id','locales.id')
                                            ->join('users','devolucion_mercaderia.usuario_id','users.id')
                                            ->where('devolucion_mercaderia.num_nc',$num_nc)
                                            ->where('devolucion_mercaderia.activo',0)
                                            ->get();
        return $devoluciones_realizadas;
    }

    public function traspasar_mercaderia(Request $request){
        
        $usuario = Auth::user();
        $codigo_interno = $request->codigo_interno;
        $cantidad = $request->cantidad;
        $opcion = $request->opcion;

        $repuesto = repuesto::where('codigo_interno',$codigo_interno)
                                ->where('local_id',1)
                                
                                ->where('activo',1)
                                ->first();
        
        if($repuesto){
            if($opcion != 3){
                if($repuesto->local_id == 1 && $repuesto->stock_actual > 0){
                    if($cantidad > $repuesto->stock_actual){
                        return 'Cantidad sobrepasa el stock';
                    }
                    
                    //Evaluamos si ya existe una solicitud por parte del usuario que inicio sesión
                    $value = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->get();
                    
                    try {   

                        if(count($value) == 0){
                            $correlativo = correlativo::find(9);
                            
                            $tm = new traspaso_mercaderia;
                            $tm->num_solicitud = intval($correlativo->correlativo) + 1;
                            $tm->usuario_id = Auth::user()->id;
                            $tm->activo = 1;
                            
                            $tm->save();

                            $resp = $this->traspasar_detalle_mercaderia($tm,$repuesto,$cantidad, $opcion);

                            $num_solicitud = $tm->num_solicitud;

                            $correlativo->correlativo = intval($correlativo->correlativo) + 1;
                            $correlativo->save();
                        }else{
                            
                            $resp = $this->traspasar_detalle_mercaderia($value[0],$repuesto,$cantidad, $opcion);
                            
                            if($resp == 'error'){
                                return $resp;
                            }

                            $num_solicitud = $value[0]->num_solicitud;

                        }

                        
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                    //Se resta de bodega
                    $repuesto->stock_actual -= $cantidad;
                    $repuesto->local_id = 1;
                    $repuesto->ubicacion = "Bodega";
                    //Se agrega a tienda
                    $repuesto->stock_actual_dos += $cantidad;
                    $repuesto->local_id_dos = 3;
                    $repuesto->ubicacion_dos = "Tienda";
                    // $repuesto->save();
                }
                else{
                    return ['No existe stock en bodega'];
                }
            }else{
           
                if($repuesto->local_id_tres == 4 && $repuesto->stock_actual_tres > 0){
                    if($cantidad > $repuesto->stock_actual_tres){
                        return 'Cantidad sobrepasa el stock';
                    }
                   
                    //Evaluamos si ya existe una solicitud por parte del usuario que inicio sesión
                    $value = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->get();
                    
                    try {   

                        if(count($value) == 0){
                            $correlativo = correlativo::find(9);
                            
                            $tm = new traspaso_mercaderia;
                            $tm->num_solicitud = intval($correlativo->correlativo) + 1;
                            $tm->usuario_id = Auth::user()->id;
                            $tm->activo = 1;
                            
                            $tm->save();

                            $resp = $this->traspasar_detalle_mercaderia($tm,$repuesto,$cantidad, $opcion);

                            $num_solicitud = $tm->num_solicitud;

                            $correlativo->correlativo = intval($correlativo->correlativo) + 1;
                            $correlativo->save();
                        }else{
                            
                            $resp = $this->traspasar_detalle_mercaderia($value[0],$repuesto,$cantidad, $opcion);
                            
                            if($resp == 'error'){
                                return $resp;
                            }

                            $num_solicitud = $value[0]->num_solicitud;

                        }

                        
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
                else{
                    return ['No existe stock en Casa Matriz'];
                }
            }
            
            
        }else{
            return ['No encontrado'];
        }

       try {
            $repuestos = $this->dame_repuestos_traspaso();
            return ['OK',$repuesto,$repuestos,$num_solicitud];
       } catch (\Exception $e) {
            return $e->getMessage();
       }

       
        
    }

    public function resumen_solicitudes(){
        try {
            $resumen = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno','traspaso_mercaderia_detalle.estado','users.name','traspaso_mercaderia_detalle.created_at','traspaso_mercaderia.num_solicitud','traspaso_mercaderia_detalle.locaciones')
                                                    ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                    ->join('traspaso_mercaderia','traspaso_mercaderia.id','traspaso_mercaderia_detalle.id_traspaso_mercaderia')
                                                    ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                    ->get();
            
            $total = $resumen->count();
            $html = [];
            array_push($html,view('fragm.resumen_solicitudes',compact('resumen','total'))->render(),$total);
            return $html;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function buscarsolicitud($num_solicitud){
        try {
            $solicitud = traspaso_mercaderia::select('users.name','traspaso_mercaderia.*')
                                            ->where('traspaso_mercaderia.num_solicitud',$num_solicitud)
                                            ->where('traspaso_mercaderia.activo',2)
                                            ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                            ->first();
            
            if($solicitud){
                $solicitud_id = $solicitud->id;
                $detalle = traspaso_mercaderia_detalle::select('repuestos.codigo_interno','repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.id','traspaso_mercaderia_detalle.locaciones')
                                ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$solicitud_id)
                                ->where('traspaso_mercaderia_detalle.activo',1)
                                ->where('traspaso_mercaderia_detalle.estado',2)
                                ->join('repuestos','traspaso_mercaderia_detalle.repuesto_id','repuestos.id')
                                ->get();

                $v = view('fragm.detalle_solicitud_traspaso',compact('solicitud','detalle','solicitud_id'))->render();
                return $v;
            }else{
                return 'error';
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function devolucion_mercaderia(){
        try {
            $permisos = permissions_detail::all();
            foreach ($permisos as $p) {
                if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/guiadespacho/devolucion_mercaderia'){
                    return view('inventario.devolucion_mercaderia');
                }
            }
            if(Auth::user()->rol->nombrerol == "Administrador"){
                return view('inventario.devolucion_mercaderia');
            }else return redirect('home');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function aceptar_traspaso_repuesto(Request $req){
        try {
            $repuesto_id = $req->repuesto_id;
            $solicitud_id = $req->solicitud_id;
            $cantidad = $req->cantidad;
            $locaciones = $req->locaciones;

            $repuesto = repuesto::where('id',$repuesto_id)->first();
            if($locaciones == 1){
                //Se resta de bodega
                $repuesto->local_id = 1;
                $repuesto->stock_actual -= $cantidad;
                //Se agrega a tienda
                $repuesto->local_id_dos = 3;
                $repuesto->stock_actual_dos += $cantidad;
                // $repuesto->ubicacion = 'Bodega';
                // $repuesto->ubicacion_dos = 'Tienda';
                // $repuesto->fecha_actualizacion_stock = Carbon::today()->toDateString();
            }elseif($locaciones == 2){
                //Se resta de bodega
                $repuesto->local_id = 1;
                $repuesto->stock_actual -= $cantidad;
                //Se agrega a CAMA MATRIZ
                $repuesto->local_id_tres = 4;
                $repuesto->stock_actual_tres += $cantidad;
            }elseif($locaciones == 3){
                // Se saca de Casa Matriz  a TIENDA
                $repuesto->stock_actual_tres -= $cantidad;
                $repuesto->local_id_dos = 3;
                $repuesto->stock_actual_dos += $cantidad;
                $repuesto->local_id_tres = 4;
                $repuesto->ubicacion_tres = 'CM - p1 - 0 - b0 - p0';
            }
            
            $repuesto->save();
            
            $value = traspaso_mercaderia_detalle::where('id_traspaso_mercaderia',$solicitud_id)
                                                ->where('repuesto_id',$repuesto_id)
                                                ->where('activo',1)
                                                ->where('estado',2)
                                                ->first();
            //Pasamos el repuestos solicitado a procesado
            $value->activo = 0;
            //Pasamos el estado del repuesto a ACEPTADO
            // 0 = rechazado
            // 1 = aceptado
            // 2 = esperando
            $value->estado = 1;
            //Guardamos el nuevo estado del repuesto solicitado
            
            $value->save();
            
            try {
                $solicitud = traspaso_mercaderia::select('users.name','traspaso_mercaderia.*')
                                                ->where('traspaso_mercaderia.id',$solicitud_id)
                                                ->where('traspaso_mercaderia.activo',1)
                                                ->orWhere('traspaso_mercaderia.activo',2)
                                                ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                ->first();
                if($solicitud){
                    $solicitud_id = $solicitud->id;
                    $detalle = traspaso_mercaderia_detalle::select('repuestos.codigo_interno','repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.id','traspaso_mercaderia_detalle.locaciones')
                                    ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$solicitud_id)
                                    ->where('traspaso_mercaderia_detalle.activo',1)
                                    ->join('repuestos','traspaso_mercaderia_detalle.repuesto_id','repuestos.id')
                                    ->get();
                    /* Si no existen repuestos solicitados por el usuario, 
                    el pedido pasa a estado inactivo y no se muestra en el sistema.*/

                    if($detalle->count() == 0){
                        $solicitud->activo = 0;
                        $solicitud->save();
                    }
                  
    
                    $v = view('fragm.detalle_solicitud_traspaso',compact('solicitud','detalle','solicitud_id'))->render();
                    
                    return $v;
                }else{
                    return 'error';
                }
                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function pendientes(){
        try {
            $resumen = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno','traspaso_mercaderia_detalle.estado','users.name','traspaso_mercaderia_detalle.created_at','traspaso_mercaderia.num_solicitud','traspaso_mercaderia_detalle.locaciones')
            ->where('traspaso_mercaderia_detalle.estado',2)
            ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
            ->join('traspaso_mercaderia','traspaso_mercaderia.id','traspaso_mercaderia_detalle.id_traspaso_mercaderia')
            ->join('users','traspaso_mercaderia.usuario_id','users.id')
            ->get();
            
            $v = view('fragm.resumen_solicitudes',compact('resumen'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function rechazar_traspaso_repuesto(Request $req){
        $repuesto_id = $req->repuesto_id;
        $solicitud_id = $req->solicitud_id;

        $value = traspaso_mercaderia_detalle::where('id_traspaso_mercaderia',$solicitud_id)
                                                ->where('repuesto_id',$repuesto_id)
                                                ->where('activo',1)
                                                ->orWhere('activo',2)
                                                ->first();
        //Pasamos el repuestos solicitado a procesado
        $value->activo = 0;
        $value->estado = 0;
        //Guardamos el nuevo estado del repuesto solicitado
        $value->save();

        try {
            $solicitud = traspaso_mercaderia::select('users.name','traspaso_mercaderia.*')
                                                ->where('traspaso_mercaderia.id',$solicitud_id)
                                                ->where('traspaso_mercaderia.activo',1)
                                                ->orWhere('traspaso_mercaderia.activo',2)
                                                ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                ->first();
            if($solicitud){
                $solicitud_id = $solicitud->id;
                $detalle = traspaso_mercaderia_detalle::select('repuestos.codigo_interno','repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.id','traspaso_mercaderia_detalle.locaciones')
                                ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$solicitud_id)
                                ->where('traspaso_mercaderia_detalle.activo',1)
                                ->join('repuestos','traspaso_mercaderia_detalle.repuesto_id','repuestos.id')
                                ->get();
                /* Si no existen repuestos solicitados por el usuario, 
                el pedido pasa a estado inactivo y no se muestra en el sistema.*/

                if($detalle->count() == 0){
                    $solicitud->activo = 0;
                    $solicitud->save();
                }
                  
    
                $v = view('fragm.detalle_solicitud_traspaso',compact('solicitud','detalle','solicitud_id'))->render();
                
                return $v;
            }else{
                return 'error';
            }
                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }

    public function detalle($fecha){
        
        $resumen = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno','traspaso_mercaderia_detalle.estado','users.name','traspaso_mercaderia_detalle.created_at','traspaso_mercaderia.num_solicitud','traspaso_mercaderia_detalle.locaciones','traspaso_mercaderia_detalle.fecha_emision')
                                                ->where('traspaso_mercaderia_detalle.fecha_emision',$fecha)
                                                ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                ->join('traspaso_mercaderia','traspaso_mercaderia.id','traspaso_mercaderia_detalle.id_traspaso_mercaderia')
                                                ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                ->get();

        $total = $resumen->count();

        $html = [];
        
        array_push($html,view('fragm.resumen_solicitudes',compact('resumen','total'))->render(), $total);
        return $html;
    }

    public function detalle_solicitud($num_solicitud){
        
        try {
            $resumen = traspaso_mercaderia::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno','traspaso_mercaderia_detalle.estado','users.name','traspaso_mercaderia_detalle.created_at','traspaso_mercaderia.num_solicitud','traspaso_mercaderia_detalle.locaciones')
                                                ->where('traspaso_mercaderia.num_solicitud',$num_solicitud)
                                                ->join('traspaso_mercaderia_detalle','traspaso_mercaderia.id','traspaso_mercaderia_detalle.id_traspaso_mercaderia')
                                                ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                                ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                ->get();
        
        return view('fragm.resumen_solicitudes',compact('resumen'))->render();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function traspasar_detalle_mercaderia($value,$repuesto,$cantidad,$opcion){
       
        $resp = traspaso_mercaderia_detalle::where('repuesto_id',$repuesto->id)->where('activo',1)->first();
        if(!$resp){
            $tm_detalle = new traspaso_mercaderia_detalle;
            $tm_detalle->id_traspaso_mercaderia = $value->id;
            $tm_detalle->repuesto_id = $repuesto->id;
            $tm_detalle->fecha_emision = Carbon::today()->toDateString();
            $tm_detalle->cantidad = $cantidad;
            $tm_detalle->activo = 1;
            $tm_detalle->locaciones = $opcion;
            //Estado = 0 ----> Rechazado
            //Estado = 1 ----> Aceptado
            //Estado = 2 ----> Esperando
            $tm_detalle->estado = 2;
            $tm_detalle->save();
            return $tm_detalle;
        }else{
            return 'error';
        }
        
    }

    public function dame_repuestos_traspaso(){
        try {
            $usuario = Auth::user();
            $tm = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->first();
            
            $repuestos = traspaso_mercaderia_detalle::select('repuestos.descripcion','traspaso_mercaderia_detalle.cantidad','repuestos.codigo_interno')
                                        ->where('traspaso_mercaderia_detalle.id_traspaso_mercaderia',$tm->id)
                                        ->where('traspaso_mercaderia_detalle.activo',1)
                                        ->join('repuestos','repuestos.id','traspaso_mercaderia_detalle.repuesto_id')
                                        ->get();

            return $repuestos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_cotizacion_num($num_cotizacion)
    {
        try {
            $id_cotizacion=cotizacion::where('num_cotizacion',$num_cotizacion)
                                    ->value('id');
            $cotizacion=cotizacion_detalle::select('repuestos.descripcion','cotizaciones_detalle.cantidad','cotizaciones_detalle.precio_venta','cotizaciones_detalle.subtotal','cotizaciones_detalle.descuento')
                                            ->join('repuestos','cotizaciones_detalle.id_repuestos','repuestos.id')
                                            ->where('id_cotizacion',$id_cotizacion)->get();
            if($cotizacion->count()==0){
                $estado=['estado'=>'ERROR','mensaje'=>'No existe cotización '+$num_cotizacion];
            }else{
                $estado=['estado'=>'OK','cotizacion'=>$cotizacion];
            }

        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
        }
        return json_encode($estado);
    }

    public function dame_cliente($rut){

        //Buscar en Proveedores
        $rut_proveedor=str_replace(".","",$rut);
        $rut_proveedor=str_replace("-","",$rut_proveedor);
        $rut_proveedor=substr($rut_proveedor,0,strlen($rut_proveedor)-1)."-".substr($rut_proveedor,strlen($rut_proveedor)-1);
        $a="nada";
        if(strlen($rut_proveedor)==9){
            //9811490-4
            $rut_proveedor=substr($rut_proveedor,0,1).".".substr($rut_proveedor,1,3).".".substr($rut_proveedor,4,3).substr($rut_proveedor,7);
        }elseif(strlen($rut_proveedor)==10){
            //26775605-8
            $rut_proveedor=substr($rut_proveedor,0,2).".".substr($rut_proveedor,2,3).".".substr($rut_proveedor,5,3).substr($rut_proveedor,8);
        }

        $p=proveedor::where('empresa_codigo',$rut_proveedor)
                        ->where('activo',1)
                        ->first();

        //Buscar en Clientes
        $rut_cliente=str_replace(".","",$rut);
        $rut_cliente=str_replace("-","",$rut_cliente);

        $c=cliente_modelo::where('rut',$rut_cliente)
                            ->where('activo',1)
                            ->first();

        $r=0;
        if(is_null($p) && is_null($c)){ //no hay nada

        }
        if(!is_null($p) && is_null($c)){ //hay solo proveedor
            $r=1;
        }
        if(is_null($p) && !is_null($c)){ //hay solo cliente
            $r=2;
        }
        if(!is_null($p) && !is_null($c)){ //hay ambos
            $r=3;
        }
        $estado['status']=$r;
        $estado['cliente']=$c;
        $estado['proveedor']=$p;
        return json_encode($estado);
    }

    public function cargar_documento($doc)
    {
        $tip_doc=substr($doc,0,2); //bo
        $num_doc=trim(substr($doc,2)); //el número buscado
        
        if($tip_doc=='bo')
        {
            $documento="Boleta";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            return 'hola';
            $hay=nota_de_debito::where('docum_referencia','bo'.$num_documento)->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "La boleta N° ".$num_doc." ya tiene nota de débito N° <b>".$h['num_nota_debito']."</b> por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=boleta::where('num_boleta',$num_doc)->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_razon_social=$cliente['razon_social'];
                if(substr($cliente_rut,0,5)=='00000')
                {
                    $cliente_id="0";
                    $cliente_rut="Sin Cliente";
                    $cliente_razon_social="";
                }

                $cliente_direccion=$cliente['direccion']."      Comuna: ".$cliente['direccion_comuna']."     Ciudad: ".$cliente['direccion_ciudad'];
                $detalle=boleta_detalle::select('boletas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                        ->where('boletas_detalle.id_boleta',$buscado_doc['id'])
                                                        ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                        ->get();


                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_debito_boleta',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_direccion',
                                    'detalle'
                                    ))->render();
                return $v;

            }else{
                return "No Existe la Boleta N° ".$num_doc;
            }
        }

        if($tip_doc=='fa')
        {
            $documento="Factura";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            $hay=nota_de_debito::where('docum_referencia','fa'.$num_documento)->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "La factura N° ".$num_doc." ya tiene nota de débito N° <b>".$h['num_nota_debito']."</b> por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=factura::where('num_factura',$num_doc)->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_razon_social=$cliente['razon_social'];
                if(substr($cliente_rut,0,5)=='00000')
                {
                    $cliente_id="0";
                    $cliente_rut="Sin Cliente";
                    $cliente_razon_social="";
                }

                $cliente_direccion=$cliente['direccion']."      Comuna: ".$cliente['direccion_comuna']."     Ciudad: ".$cliente['direccion_ciudad'];
                $detalle=factura_detalle::select('facturas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                        ->where('facturas_detalle.id_factura',$buscado_doc['id'])
                                                        ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                        ->get();


                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_debito_factura',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_direccion',
                                    'detalle'
                                    ))->render();
                return $v;

            }else{
                return "No Existe la Factura N° ".$num_doc;
            }
        }

    }

    private function dame_correlativo()
    {

        $tipo_dte='52';
        $num=-1;
        $id_local = Session::get("local"); // es el local donde se ejecuta el terminal

        $fila=correlativo::where('id_local', $id_local)
                                    ->where('tipo_dte_sii', $tipo_dte)
                                    ->first();
        if(!is_null($fila))
        {
            $corr=$fila->correlativo;
            $max_folio=$fila->hasta;
            if($max_folio>=($corr+1)) $num=$corr;
        }
        return $num;
    }

    private function actualizar_correlativo($num)
    {
        $co = correlativo::where('tipo_dte_sii', '52')
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $co->save();
    }

    private function dame_cliente_0()
    {
        $rpta=-1; //No esta definido el cliente 0000000
        $c0=cliente_modelo::where('rut','LIKE','00000%')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    public function generar_xml(Request $r)
    {
        
        $Datos['tipo_despacho']=$r->tipo_despacho;
        $Datos['tipo_traslado']=$r->tipo_traslado;

        $ref1=json_decode($r->ref1);
        $ref2=json_decode($r->ref2);
        $ref3=json_decode($r->ref3);

        $referencias=[];
        if(count($ref1)>0){
            array_push($referencias,$ref1);
        }
        if(count($ref2)>0){
            array_push($referencias,$ref2);
        }
        if(count($ref3)>0){
            array_push($referencias,$ref3);
        }

        $Datos['referencias']=$referencias;

        $datos=json_decode($r -> input('datos'));
        
        if(count($datos)==0){
            $estado=['estado'=>'ERROR','mensaje'=>'No se enviaron datos'];
            return json_encode($estado);
        }

        $Detalle=[];
        foreach($datos as $i){
            /*
            $item=array('CdgItem'=>['TpoCodigo'=>'INT1','VlrCodigo'=>$i->id_repuestos],
                                    'NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>$i->pu_neto);
            */
            $precio_neto=round($i->precio);
            $item=array('NmbItem'=>$i->descripcion,
                        'QtyItem'=>intval($i->cantidad),
                        'PrcItem'=>$precio_neto);
            array_push($Detalle,$item);

        }


        //Obtener cliente
        $cliente=cliente_modelo::where('rut',$r->rut)->first();
    
        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
       
        if($cliente->tipo_cliente==0){ //persona natural
            $rz=$cliente->nombres." ".$cliente->apellidos;

        }
        if($cliente->tipo_cliente==1){ //empresa
            $rz=$cliente->empresa;
        }

      

        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                'RznSocRecep'=>$rz,
                'GiroRecep'=>trim($cliente->giro),
                'DirRecep'=>trim($cliente->direccion),
                'CmnaRecep'=>$cliente->direccion_comuna,
                'CiudadRecep'=>$cliente->direccion_ciudad
            ];

        $transporte=[
                'Patente'=>$r->patente,
                'RUTTrans'=>$r->rut_transportista,
                'Chofer'=>[
                    'RUTChofer'=>$r->rut_chofer,
                    'NombreChofer'=>$r->nombre_chofer,
                ],
                'DirDest'=>$r->cliente_direccion,
                'CmnaDest'=>$r->comuna,
                'CiudadDest'=>$r->ciudad
        ];

        $Datos['transporte']=$transporte;

        $nume=$this->dame_correlativo();
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $estado=['estado'=>'ERROR_CAF','mensaje'=>"Guía de Despacho: No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++;
            $Datos['folio_dte']=$nume;
        }

        $Datos['tipo_dte']='52';
        $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array
        
        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre','Nota de Débito'); //OJO: Para que se necesita?
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente', $r->id_cliente);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
        }

        return json_encode($estado);
    } // fin generar_xml

    public function enviar_sii(Request $r)
    {
        $id_cliente=$r->id_cliente;
       

        $d=Session::get('xml');
        if($d==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT'));
        $RutEmisor = $RutEnvia;

        $tipo_dte=Session::get('tipo_dte');
        $doc=base_path().'/xml/generados/guias_de_despacho/'.$d;

        $tipo_docu="nada";
        $num_docu=0;

       //Recuperar el XML Generado para enviar
        try {
            $envio=file_get_contents($doc);
            $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
            if($rs['estado']=='OK'){
                $resultado_envio=$rs['mensaje'];
                $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                $estado=0;
                $TrackID=$rs['trackid'];
                $estado_sii='RECIBIDO';
            }else{
                return json_encode($rs);
            }
            //guardar guia de despacho

            $gd=new guia_de_despacho;
            $gd->num_guia_despacho=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
            $gd->fecha_emision=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
            $gd->TipoDespacho=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->TipoDespacho);
            $gd->IndTraslado=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->IndTraslado);
            $gd->TpoTranVenta=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->TpoTranVenta);
            $gd->id_cliente=$id_cliente;
            $gd->neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
            $gd->exento=0.0;
            $gd->iva=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
            $gd->total=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
            $gd->patente=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->Patente);
            $gd->RUTTrans=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->RUTTrans);
            $gd->RUTChofer=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->Chofer->RUTChofer);
            $gd->NombreChofer=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->Chofer->NombreChofer);
            $gd->DirDest=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->DirDest);
            $gd->CmnaDest=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->CmnaDest);
            $gd->CiudadDest=strval($xml->SetDTE->DTE->Documento->Encabezado->Transporte->CiudadDest);
            $gd->trackid=$TrackID;
            $gd->url_xml=$d;
            $gd->estado = $estado;
            $gd->estado_sii=$estado_sii;
            $gd->resultado_envio=$resultado_envio;

            $gd->activo=1;
            $gd->usuarios_id=Auth::user()->id;
            $gd->save();

            //detalle guia despacho

            foreach($xml->SetDTE->DTE->Documento->Detalle as $Det){
                $pu=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                $total_item=round(intval($Det->MontoItem)*(1+Session::get('PARAM_IVA')),2);
                $num_item_xml=$Det->NroLinDet;
                $gdd=new guia_de_despacho_detalle;
                $gdd->id_guia_despacho=$gd->id;
                $gdd->id_repuestos=0;
                $gdd->id_unidad_venta=0;
                $gdd->id_local=Session::get('local');
                $gdd->precio_venta=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                $gdd->cantidad=intval($Det->QtyItem);
                $gdd->subtotal=$total_item;
                $gdd->descuento=0;
                $gdd->total=$gdd->subtotal-$gdd->descuento;
                $gdd->activo=1;
                $gdd->usuarios_id=Auth::user()->id;
                $gdd->save();

                //actualizar saldos FALTA: Poner en la GUI y Traer el codigo del repuesto para poder actualizar el inventario
                //$rc = new repuestocontrolador();
                //$rc->actualiza_saldos("E", $gdd->id_repuestos, $gdd->id_local, $gdd->cantidad);
            }
            $this->actualizar_correlativo($gd->num_guia_despacho);

        } catch (\Exception $e) {
            $ee=substr($e->getMessage(),0,300);
            $estado=['estado'=>'ERROR','mensaje'=>$ee];
            return json_encode($estado);
        }

        return json_encode($rs);
    } // fin enviar SII

    public function actualizar_estado(Request $r){
        //viene TrackID, estado
        $gd=guia_de_despacho::where('trackid',$r->TrackID)->first();
        if(!is_null($gd)){
            $gd->estado_sii=$r->estado;
            $gd->save();
            $estado=['estado'=>'OK','mensaje'=>'Estado actualizado...'];
        }else{
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo actualizar estado'];
        }
        return json_encode($estado);
    }

    public function existe_nc($nc){
        $hay=nota_de_debito::where('docum_referencia','LIKE','nc*'.$nc.'%')->first();
        if(is_null($hay)){
            $estado=['estado'=>'NO','mensaje'=>'No existe...'];
        }else{
            $estado=['estado'=>'SI','mensaje'=>'La Nota de Crédito N° '.$nc.' YA TIENE Nota de Débito N° '.$hay->num_nota_debito.' de fecha '.$hay->fecha_emision];
        }
        return json_encode($estado);
    }

    public function historial_nc($num_nc){
        try {
            $nc=nota_de_credito::select('notas_de_credito.*','users.name')
                            ->join('users','notas_de_credito.usuarios_id','users.id')
                            ->where('notas_de_credito.num_nota_credito',$num_nc)
                            ->where('notas_de_credito.devuelto',1)
                            ->first();

            $devoluciones_realizadas = $this->damedevoluciones_realizadas($num_nc);
            $v = view('fragm.historial_nc',compact('nc','devoluciones_realizadas'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
