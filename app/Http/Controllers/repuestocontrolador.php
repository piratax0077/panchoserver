<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage; //lo agregué
use Illuminate\Support\Facades\File; //lo agregué
use Carbon\Carbon; // para tratamiento de fechas
use App\repuesto;
use App\descuento;
use App\clonacion_fabs;
use App\clonacion_similares;
use App\oferta_pagina_web;
use App\saldo;
use App\familia;
use App\factura_detalle;
use App\boleta_detalle;
use App\traspaso_mercaderia_detalle;
use App\vale_mercaderia;
use App\vale_mercaderia_detalle;
use App\devolucion_mercaderia;
use App\permissions_detail;
use App\compras_det;
use App\boleta;
use App\factura;
use App\correlativo;
use App\local;
use App\marcavehiculo;
use App\modelovehiculo;
use App\marcarepuesto;
use App\regulador_voltaje;
use App\proveedor;
use App\similar;
use App\repuestofoto;
use App\pais;
use App\oem;
use App\fabricante;
use App\traspaso_mercaderia;
use App\motorPartida;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\clonacion_oems;
use App\clonacion_rv;
use App\stock_minimo;
use App\StockMovimiento;
use App\detalle_pedido;
use App\User;
use App\Services\StockService;
use App\Exports\Repuestos_actualizadosExport;
use App\Exports\RepuestosStockMinimo;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use App\Mail\RepuestosStockMinimoMail;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\Auth;

use Session;
use Debugbar;

class repuestocontrolador extends Controller
{

    private $repuestos;
    private $familias;
    private $marcas;
    private $modelos;
    private $marcarepuestos;
    private $proveedores;
    private $similares;

    // Guarda la familia de un repuesto desde el armado de kit
    public function guardar_familia_kit(Request $request)
    {
        try {
            $repuesto = repuesto::findOrFail($request->id_repuesto);
            $familia = familia::findOrFail($request->id_familia);
            $repuesto->id_familia = $familia->id;
            $repuesto->save();
            return response()->json(['estado' => 'OK', 'mensaje' => 'Familia actualizada']);
        } catch (\Exception $e) {
            return response()->json(['estado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function dame_repuesto_x_cod_int($codint){
        $hay=repuesto::where('codigo_interno',$codint)->first();
        if(is_null($hay)){ //No hay
            $estado=['estado'=>'ERROR','mensaje'=>'El código '.$codint. ' no existe o está desactivado'];
        }else{
            $repuesto=$this->dame_un_repuesto($hay->id);
            $estado=['estado'=>'OK','repuesto'=>$repuesto];
        }
        return json_encode($estado);
    }

     public function dame_repuesto_clonar_oem($codint){
        try {
            $repuesto = repuesto::where('codigo_interno',$codint)->first();
          
            if($repuesto){
                return [$this->dame_repuesto_x_id_html_carrito($repuesto->id), $repuesto->id];
            }else{
                return 'error';
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_repuesto_x_id_html($idrep){
        $hay=repuesto::where('id',$idrep)->where('activo',1)->first();
        $locales = $this->damelocales_activos();
        if(is_null($hay)){ //No hay
            $estado=['estado'=>'ERROR','mensaje'=>'El código '.$codint. ' no existe o está desactivado'];
        }else{
            $repuesto=$this->dame_un_repuesto($hay->id);
            $fotos = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
            if($fotos->count() > 0){
                $v = view('fragm.repuesto_modificar_por_familia',['repuesto' => $repuesto[0],'fotos' => $fotos,'locales' => $locales]);
                return $v;
            }else{
                $v = view('fragm.repuesto_modificar_por_familia',['repuesto' => $repuesto[0],'locales' => $locales]);
                return $v;
            }
            
        }
    }

    public function dame_aplicaciones_clonar($codigo_interno){
        try {
            //code...
            $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
            if($repuesto){
                $aplicaciones = $this->dame_similares($repuesto->id);
                return $aplicaciones;
            }else{
                return 'error';
            }
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function dame_repuesto_x_id_html_carrito($idrep){
        try {
            //code...
            $hay=repuesto::where('id',$idrep)->where('activo',1)->first();
        if(is_null($hay)){ //No hay
            $codint = $idrep;
            $estado=['estado'=>'ERROR','mensaje'=>'El código '.$codint. ' no existe o está desactivado'];
        }else{
            $repuesto=$this->dame_un_repuesto($hay->id);
            $fotos = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
            $fabs=fabricante::select('codigo_fab','marcarepuesto')
                                ->where('repuestos_fabricantes.id_repuestos',$idrep)
                                ->join('marcarepuestos','repuestos_fabricantes.id_marcarepuestos','marcarepuestos.id')
                                ->orderBy('codigo_fab')
                                ->get();
            $oems = oem::select('codigo_oem')
                        ->where('id_repuestos',$idrep)
                        ->orderBy('codigo_oem')
                        ->get();
            $aplicaciones = $this->damesimilares($idrep);
            if($fotos->count() > 0){
                $v = view('fragm.repuesto_modificar_por_familia_carrito',['repuesto' => $repuesto[0],'fotos' => $fotos,'fabs' => $fabs,'oems' => $oems,'aplicaciones' => $aplicaciones]);
                return $v;
            }else{
                $v = view('fragm.repuesto_modificar_por_familia_carrito',['repuesto' => $repuesto[0],'fabs' => $fabs,'oems' => $oems,'aplicaciones' => $aplicaciones]);
                return $v;
            }
            
        }
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();    
        }
        
    }

    public function dame_repuesto_x_id($idrep){
        try {
            $hay=repuesto::select('repuestos.*','ofertas_pagina_web.desde','ofertas_pagina_web.hasta','ofertas_pagina_web.precio_actualizado','descuentos.porcentaje')
            ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
            ->leftjoin('descuentos','descuentos.id_familia','repuestos.id_familia')
            ->where('repuestos.id',$idrep)
            ->where('repuestos.activo',1)
            ->first();
            $hay->desde = Carbon::parse($hay->desde)->format("d-m-Y");
            $hay->hasta = Carbon::parse($hay->hasta)->format("d-m-Y");
            return $hay;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
       
    }

    private function dame_un_repuesto($id)
    {
        try {
            $repuesto=repuesto::select('repuestos.id',
            'repuestos.id_familia',
            'repuestos.id_marca_repuesto',
            'repuestos.id_pais',
            'repuestos.descripcion',
            'repuestos.observaciones',
            'repuestos.medidas',
            'repuestos.cod_repuesto_proveedor',
            'repuestos.stock_minimo',
            'repuestos.stock_maximo',
            'repuestos.codigo_barras',
            'repuestos.precio_venta',
            'repuestos.stock_actual',
            'repuestos.stock_actual_dos',
            'repuestos.stock_actual_tres',
            'repuestos.local_id',
            'repuestos.local_id_dos',
            'repuestos.local_id_tres',
            'repuestos.codigo_interno',
            'repuestos.pu_neto',
            'repuestos.oferta',
            'repuestos.precio_compra',
            'repuestos.estado',
            'repuestos.codigo_OEM_repuesto',
            'paises.nombre_pais',
            'familias.nombrefamilia',
            'proveedores.empresa_nombre_corto',
            )
        ->where('repuestos.id',$id)
        ->where('repuestos.activo',1)
        ->join('familias','repuestos.id_familia','familias.id')
        ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
        ->join('proveedores','repuestos.id_proveedor','proveedores.id')
        ->join('paises','repuestos.id_pais','paises.id')
        ->get();

        //Revisamos si está en oferta
        $oferta = oferta_pagina_web::where('id_repuesto',$id)->first();
        $descuento = descuento::where('id_familia',$repuesto[0]->id_familia)->where('activo',1)->first();
        $repuesto[0]->precio_real = number_format($repuesto[0]->precio_venta,0,',','.');
        if($oferta){
            
            $repuesto[0]->precio_venta = $oferta->precio_actualizado;
            
        }else if(!$oferta && $descuento){
            $repuesto[0]->precio_venta = $repuesto[0]->precio_venta - (($descuento->porcentaje/100) * $repuesto[0]->precio_venta);
        }
        

        return $repuesto;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function guardar_nuevo_stock_minimo($id, $nuevo_stock_minimo,$estado){

        try {
    
            $repuesto = repuesto::find($id);
    
            $repuesto->stock_minimo = $nuevo_stock_minimo;
    
            if($estado == "Aprobado"){
                $repuesto->estado = null;
            }else{
                $repuesto->estado = $estado;
            }
    
            $repuesto->id_modifica_estado = Auth::user()->id;
            $repuesto->fecha_modificacion_estado = Carbon::now()->toDateTimeString();
            $repuesto->save();
    
    
            $repuestos = repuesto::select(
                    'repuestos.*',
                    'familias.nombrefamilia',
                    'marcarepuestos.marcarepuesto',
                    'proveedores.empresa_nombre_corto'
                )
                ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                ->join('familias','familias.id','repuestos.id_familia')
                ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                ->where('repuestos.estado','Sin stock en proveedor')
                ->get();
    
    
            $repuestos_sin_stock_proveedor = [];
    
            foreach($repuestos as $r){
    
                if(
                    \App\Services\StockPorLocalService::total($r)
                    < $r->stock_minimo
                ){
                    $repuestos_sin_stock_proveedor[] = $r;
                }
            }
    
    
            // ✅ verificar si sigue en lista
            $sigue = false;
    
            if(
                $repuesto->estado == 'Sin stock en proveedor' &&
                (\App\Services\StockPorLocalService::total($repuesto))
                < $repuesto->stock_minimo
            ){
                $sigue = true;
            }
    
    
            $v = view(
                'fragm.tabla_rep_sin_prov',
                ['repuestos' => $repuestos_sin_stock_proveedor]
            )->render();
    
    
            return [
                'estado' => 'OK',
                'mensaje' => 'Stock mínimo actualizado',
    
                // vista blade
                'v' => $v,
    
                // datos extra
                'id' => $repuesto->id,
                'nuevo_estado' => $repuesto->estado,
                'stock_minimo' => $repuesto->stock_minimo,
                'sigue' => $sigue,
                'cantidad_repuestos' => count($repuestos_sin_stock_proveedor) 
            ];
    
        } catch (\Exception $e) {
    
            return [
                'estado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ];
    
        }
    
    }

    public function cambiar_estado(Request $request, $id){
        try {
            $repuesto = repuesto::find($id);
            $nuevo_estado = $request->input('estado');

            if(!$repuesto){
                return response()->json(['estado' => 'ERROR','mensaje' => 'Repuesto no encontrado'], 404);
            }

            if(empty($nuevo_estado)){
                return response()->json(['estado' => 'ERROR','mensaje' => 'Estado vacío'], 400);
            }

            if($nuevo_estado == "Aprobado"){
                $repuesto->estado = null;
            }else{
                $repuesto->estado = $nuevo_estado;
            }

            $repuesto->save();
            return response()->json(['estado' => 'OK','mensaje' => 'Estado actualizado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['estado' => 'ERROR','mensaje' => $e->getMessage()], 500);
        }
    }

    private function damesimilares($id_repuesto)
    {
        $s=similar::select('marcavehiculos.marcanombre','modelovehiculos.modelonombre','modelovehiculos.zofri','similares.id','similares.anios_vehiculo')
        ->where('similares.id_repuestos',$id_repuesto)
        ->where('similares.activo',1)
        ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
        ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
        ->orderBy('marcavehiculos.marcanombre','ASC')
        ->get();
        return $s;
    }

    private function damefotosrepuesto($id_repuesto)
    {
        $rf=repuestofoto::select('urlfoto')
        ->where('id_repuestos',$id_repuesto)
        ->where('activo',1)
        ->get();
        return $rf;
    }

    private function damerepuestos($f)
    {
        $r=repuesto::where('id_familia',$f)
                    ->where('repuestos.activo',1)
                    ->orderBy('id','desc')
                    ->get();
        return $r;
        //->orderByraw('substr(codigo_interno,1,3)')
    }

    public function damerv($idrep){
        $datos = regulador_voltaje::where('id_repuesto',$idrep)->get();
        $v = view('fragm.regulador_voltaje',compact('datos'))->render();
        
        return $v;
    }

    public function dameMotorPartida($idrep){
        $datos = motorPartida::where('id_repuesto',$idrep)->get();
        $v = view('fragm.motorPartida',compact('datos'))->render();
        
        return $v;
    }

    public function borrarmotor($id){
        try {
            $motor = motorPartida::find($id);
            $idrep = $motor->id_repuesto;
            $motor->delete();
            return $this->dameMotorPartida($idrep);
        } catch (\Exception $e) {
            return ['estado' => 'ERROR','mensaje' => $e->getMessage()];
        }
    }

    public function xpress(){
        return view('manten.repuestos_xpress');
    }

    public function buscarRepuestoExpress($dato){
        try {
            $op = substr($dato, 0, 1);
            $desc = substr(trim($dato), 1);
            $de = array(" de ", " DE ", " dE ", " De ");
            $descripcion = str_replace($de, " ", $desc);
            $descripcion= str_replace("  "," ",$descripcion);
            $descripcion=str_replace("_&_","/",$descripcion);
            $descripcion_original=$descripcion;
            $descripcion_sin_guiones= str_replace("-","",$descripcion);
            $buscado_original=trim($descripcion_original);
            $buscado_sin_guiones=$descripcion_sin_guiones;
            $terminos=explode(" ",$descripcion);
    
            $quien_busca=Auth::user()->name;

            // buscar los repuestos con id_familia = 312 y que la descripcion contenga el buscado original
            $repuestos = repuesto::where('id_familia',312)
                                    ->where('descripcion','like','%'.$buscado_original.'%')
                                    ->where('activo',1)
                                    ->orderBy('id','desc')
                                    ->get();
            // si no encuentra repuestos con el buscado original, buscar con el buscado sin guiones
            if(count($repuestos) == 0){
                $repuestos = repuesto::where('id_familia',312)
                                    ->where('descripcion','like','%'.$buscado_sin_guiones.'%')
                                    ->where('activo',1)
                                    ->orderBy('id','desc')
                                    ->get();
            }

            $desde = 'd';
            
            $v = view('fragm.repuestos_xpress',compact('repuestos','quien_busca','desde'))->render();
            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function inactivos(){
        return view('manten.repuestos_inactivos');
    }
    public function buscarRepuestoInactivos($dato){
        try {
            // La vista antepone un carácter de opción (actualmente "0").
            $busqueda = trim(substr($dato, 1));
            $busqueda = str_replace('_&_', '/', $busqueda);
            $busqueda = preg_replace('/\s+/', ' ', $busqueda);

            if($busqueda === ''){
                return response()->json([
                    'mensaje' => 'error',
                    'detalle' => 'Debe ingresar un criterio de búsqueda.'
                ], 422);
            }

            $sinGuiones = str_replace('-', '', $busqueda);
            $quien_busca = Auth::user()->name;

            $repuestos = repuesto::where('activo', 0)
                ->where(function($consulta) use ($busqueda, $sinGuiones){
                    $consulta->where('codigo_interno', 'like', '%'.$busqueda.'%')
                        ->orWhere('cod_repuesto_proveedor', 'like', '%'.$busqueda.'%')
                        ->orWhere('descripcion', 'like', '%'.$busqueda.'%');

                    if($sinGuiones !== $busqueda){
                        $consulta->orWhereRaw("REPLACE(codigo_interno, '-', '') LIKE ?", ['%'.$sinGuiones.'%'])
                            ->orWhereRaw("REPLACE(cod_repuesto_proveedor, '-', '') LIKE ?", ['%'.$sinGuiones.'%'])
                            ->orWhereRaw("REPLACE(descripcion, '-', '') LIKE ?", ['%'.$sinGuiones.'%']);
                    }
                })
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();

            $desde = 'i';

            return view('fragm.repuestos_xpress', compact(
                'repuestos',
                'quien_busca',
                'desde'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Error buscando repuestos inactivos: '.$e->getMessage());
            return response()->json([
                'mensaje' => 'error',
                'detalle' => 'No fue posible buscar los repuestos inactivos.'
            ], 500);
        }
    }
    public function activarRepuesto($codigo_interno){
        try {
            $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
            $repuesto->activo = 1;
            $repuesto->save();
            return 'ok';
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
    }
    public function damefamilias()
    {
        //$f=familia::orderBy('nombrefamilia')->get();
        try{
            $s="SELECT repuestos.id_familia, familias.id, familias.nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos inner join familias on repuestos.id_familia=familias.id  GROUP by repuestos.id_familia order by familias.nombrefamilia";
            $familias=\DB::select($s);
            return $familias;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    private function damemarcas()
    {
        $m=marcavehiculo::where('activo','=',1)->select('idmarcavehiculo','marcanombre','urlfoto')->orderBy('marcanombre')->get();
        return $m;
    }

    private function damemodelos()
    {
        $m=modelovehiculo::where('activo','=',1)->get();
        return $m;
    }

    private function damemarcarepuestos()
    {
        $m=marcarepuesto::orderBy('marcarepuesto')->get();
        return $m;
    }

    private function dameproveedores()
    {
        $p=proveedor::orderBy('empresa_nombre')->get();
        return $p;
    }

    private function damepaises()
    {
        $p=pais::orderBy('nombre_pais')->get();
        return $p;
    }



/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        $repuestos=$this->damerepuestos(0,0,0);
        $familias=$this->damefamilias();
        $marcas=$this->damemarcas();
        $modelos=$this->damemodelos();
        $marcarepuestos=$this->damemarcarepuestos();
        $proveedores=$this->dameproveedores();
        $paises=$this->damepaises();

        $datos=array('id_repuesto'=>'',
                          'familia'=>'',
                          'marcavehiculo'=>'',
                          'modelovehiculo'=>'',
                          'marcarepuesto'=>'',
                          'proveedor'=>'',
                          'pais'=>'',
                          'descripcion'=>'',
                          'medidas'=>'',
                          'anios_vehiculo'=>'',
                          'cod_repuesto_proveedor'=>'',
                          'precio_compra'=>'',
                          'precio_venta'=>'',
                          'stock_minimo'=>'',
                          'stock_maximo'=>'',
                          'codigo_barras'=>''
            );
        $guardado="NO";
        return view('manten.repuestos_crear',compact('guardado','repuestos','familias','marcas','modelos','marcarepuestos','proveedores','paises','datos'));
    }


    public function datos(Request $request)
    {


        if(isset($request->btnGuardarRepuesto))
        {
            $stockmin=$request->stock_minimo+1;
            $preciocompra=$request->precio_compra+1;
            $familia_datos=familia::find($request->cboFamilia);
            $newval=$familia_datos->correlativo+1;
            $codinterno=$familia_datos->prefijo.$newval;
            $reglas=array(
                'descripcion'=>'required|max:50',
                'medidas'=>'required|max:500',
                'anios_vehiculo'=>'required|max:50',
                'cod_repuesto_proveedor'=>'required|max:50',
                'precio_compra'=>'required|numeric',
                'precio_venta'=>'required|numeric|min:'.$preciocompra.'\'',
                'stock_minimo'=>'required|integer',
                'stock_maximo'=>'required|integer'
            );

            //'stock_maximo'=>'required|integer|min:'.$stockmin.'\''
            //FALTA PONER LOS MENSAJES DE ERROR
            $mensajes=array(
                'nombrefamilia.required'=>'Debe Ingresar un nombre de familia',
                'nombrefamilia.max'=>'El nombre de la familia debe tener como máximo 30 caracteres.',
                'nombrefamilia.unique'=>'El nombre de la familia ya existe.',
                'porcentaje.required'=>'Falta Ingresar el Porcentaje',
                'porcentaje.min'=>'El porcentaje debe ser mayor a 0.',
                'porcentaje.max'=>'El porcentaje debe ser menor a 100.',
                'porcentaje.numeric'=>'El porcentaje debe ser un número entero.',
                'prefijo.required'=>'Debe ingresar el prefijo.',
                'prefijo.alpha'=>'El prefijo debe ser caracteres.',
                'prefijo.size'=>'El prefijo debe tener 3 caracteres.'
            );


            $this->validate($request,$reglas);
            //$this->validate($request,$reglas,$mensajes);


            $repuesto=new repuesto;
            $repuesto->codigo_interno=$codinterno;
            $repuesto->descripcion=$request->descripcion;
            $repuesto->medidas=$request->medidas;
            $repuesto->anios_vehiculo=$request->anios_vehiculo;
            $repuesto->version_vehiculo="---";
            $repuesto->cod_repuesto_proveedor=$request->cod_repuesto_proveedor;
            $repuesto->version_vehiculo=$request->version_vehiculo; // Es el cod rep2
            $repuesto->codigo_OEM_repuesto="---";
            $repuesto->precio_compra=$request->precio_compra;
            $repuesto->precio_venta=$request->precio_venta;
            $repuesto->stock_minimo=$request->stock_minimo;
            $repuesto->stock_maximo=$request->stock_maximo;
            $repuesto->codigo_barras=$request->codigo_barras;
            //$repuesto->id_unidad_venta=$request->cboUnidadVenta; // Implementar...
            $repuesto->id_familia=$request->cboFamilia;
            $repuesto->id_marca_vehiculo=$request->cboMarca;
            $repuesto->id_modelo_vehiculo=$request->cboModelo;
            $repuesto->id_marca_repuesto=$request->cboMarcaRepuesto;
            $repuesto->id_proveedor=$request->cboProveedor;
            $repuesto->id_pais=$request->cboPais;
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, actualizar el correlativo de la familia
            $familia_datos->correlativo=$newval;
            $familia_datos->save();


            //Preparamos los datos para enviar a los controles de los datos ya guardados
            //y mostrarlos desactivados

            $familia=familia::find($repuesto->id_familia);
            $marcavehiculo=marcavehiculo::find($repuesto->id_marca_vehiculo);
            $modelovehiculo=modelovehiculo::find($repuesto->id_modelo_vehiculo);
            $marcarepuesto=marcarepuesto::find($repuesto->id_marca_repuesto);
            $proveedor=proveedor::find($repuesto->id_proveedor);
            $pais=pais::find($repuesto->id_pais);

            $datos=array('guardado'=>'SI',
                          'id_repuesto'=>$repuesto->id,
                          'familia'=>$familia->nombrefamilia,
                          'marcavehiculo'=>$marcavehiculo->marcanombre,
                          'modelovehiculo'=>$modelovehiculo->modelonombre,
                          'marcarepuesto'=>$marcarepuesto->marcarepuesto,
                          'proveedor'=>$proveedor->empresa_nombre,
                          'pais'=>$pais->nombre_pais,
                          'descripcion'=>$repuesto->descripcion,
                          'medidas'=>$repuesto->medidas,
                          'anios_vehiculo'=>$repuesto->anios_vehiculo,
                          'cod_repuesto_proveedor'=>$repuesto->cod_repuesto_proveedor,
                          'version_vehiculo'=>$repuesto->version_vehiculo,
                          'precio_compra'=>$repuesto->precio_compra,
                          'precio_venta'=>$repuesto->precio_venta,
                          'stock_minimo'=>$repuesto->stock_minimo,
                          'stock_maximo'=>$repuesto->stock_maximo,
                          'codigo_barras'=>$repuesto->codigo_barras
            );

            Session::put('datos',$datos);

            return redirect('repuesto/crea_fotos');
            //return view('manten.repuestos_crear',compact('guardado','datos','familias','marcas','modelos','marcarepuestos','proveedores'))->with('msgGuardado','Agregue Fotos');
        }



    }

    public function guardar_repuesto_modificado(Request $item)
    {

        $resp=-1;
        try{
            $repuesto=repuesto::find($item->idrep);
            $id_familia_old=$repuesto->id_familia;
            $repuesto->id_familia=$item->idFamilia;
            $repuesto->id_marca_repuesto=$item->idMarcaRepuesto;
            $repuesto->id_pais=$item->idPais;
            $repuesto->descripcion=strtoupper($item->descripcion);
            $repuesto->observaciones=strlen(trim($item->observaciones))>0?trim($item->observaciones):"";
            $repuesto->medidas=$item->medidas;
            $repuesto->cod_repuesto_proveedor=$item->cod_repuesto_proveedor;
            $repuesto->version_vehiculo="---";
            $repuesto->codigo_OEM_repuesto='modificado';
            $repuesto->precio_compra=$item->pu;
            
            $repuesto->stock_minimo=$item->stockmin;
            $repuesto->stock_maximo=$item->stockmax;
            $repuesto->codigo_barras=$item->codbar;
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=$item->activo;

            //Guardamos el nuevo precio y actualizamos la fecha de actualizacion de precio del repuesto
            if(intval($repuesto->precio_venta) != intval($item->preciosug)){
                $repuesto->precio_venta=$item->preciosug;
                $repuesto->fecha_actualiza_precio = Carbon::today()->toDateString();
            }

            

            //Verificamos: si cambió de familia entonces le asignamos nuevo codigo_interno
            $modificóFamilia=false;
            if($id_familia_old!=$item->idFamilia)
            {
                $familia_datos=familia::find($item->idFamilia);
                $newval=$familia_datos->correlativo+1;
                $codinterno=$familia_datos->prefijo.$newval;
                $repuesto->codigo_interno=$codinterno;
                $modificóFamilia=true;
            }
            $s=$repuesto->save();
            if($s)
            {
                $resp=$repuesto->id;
                //Actualizar el correlativo de la familia si se modificó familia
                if($modificóFamilia)
                {
                    $familia_datos->correlativo=$newval;
                    $familia_datos->save();
                    $resp=$repuesto->codigo_interno;
                }

            }
            return $resp;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function guardar_precio_venta($dato)
    {
        $rpta="XUXA";
        $usuario = Auth::user();
        try{
            $d=explode("&",$dato);
            $idrep=$d[0];
            $nuevo_precio=$d[1];
            $antiguo_precio = $d[2];
            if(intval($antiguo_precio) > intval($nuevo_precio)){
                if($usuario->rol->nombrerol == "bodega-venta" || $usuario->rol->nombrerol == "vendedor"){
                    return [$rpta,1];
                }
            }
            
            $rep=repuesto::find($idrep);
            $rep->precio_venta=$nuevo_precio;
            $rep->fecha_actualiza_precio = Carbon::today()->toDateString();
            $rep->save();
            $rpta=number_format($nuevo_precio,0,',','.');
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

        //Fecha ultima actualización del precio del repuesto
        $firstDate = $rep->fecha_actualiza_precio;
        $secondDate = date('d-m-Y H:i:s');

        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

        $minutos = floor(abs($dateDifference / 60));
        $horas = floor($minutos / 60);
        $dias = floor($horas / 24);
        return [$rpta, $dias];
    }

    public function crea_fotos()
    {


        $datos=Session::get('datos');
        $fotos=Session::get('fotos');

        return view('manten.repuestos_agregar_fotos',compact('datos','fotos'));

    }




    public function fotos(Request $request)
    {


        $idrepuestos=$request->id_repuesto;
        $elusuarioID=Auth::user()->id;

        if(isset($request->btnGuardarFotos))
        {
            $reglas=array(
                'archivo'=>'required|max:200|mimes:jpeg,png' //maximo 200 kilobytes
            );

            $mensajes=array(
                'archivo.required'=>'Debe elegir una imagen.',
                'archivo.mimes'=>'El tipo de archivo debe ser una imagen jpg o png.',
                'archivo.max'=>'El tamaño de archivo no debe ser mayor a 200Kb.'
            );

            $this->validate($request,$reglas,$mensajes);



            $archivo=$request->file('archivo');
            $repuestofoto= new repuestofoto;
            $repuestofoto->urlfoto=$archivo->store('fotozzz','public');
            $repuestofoto->usuarios_id=$elusuarioID;
            $repuestofoto->id_repuestos=$idrepuestos;
            $repuestofoto->activo=1;

            $repuestofoto->save();


            $fotos=repuestofoto::where('id_repuestos','=',$idrepuestos)->get();



            return redirect('repuesto/crea_fotos')->with('fotos',$fotos);

            //return view('manten.repuestos_agregar_similares',compact('datos'));

            //return view('manten.repuestos_crear',compact('guardado','datos','familias','marcas','modelos','marcarepuestos','proveedores','fotos'))->with('msgGuardado','Foto Agregada.');
        }

        if(isset($request->btnAgregarSimilares))
        {


            $datos=Session::get('datos');
            $idrepuestos=$datos['id_repuesto'];

            $fotos=repuestofoto::select('urlfoto')->where('id_repuestos','=',$idrepuestos)->get()->toArray();
            Session::put('fotos',$fotos);


            return redirect('repuesto/crea_similares')->with('fotos',$fotos);
        }

    }

    public function crea_similares()
    {

        $datos=Session::get('datos');
        $fotos=Session::get('fotos');
        $marcas=$this->damemarcas();
        $modelos=$this->damemodelos();
        $similares=$this->damesimilares($datos['id_repuesto']);
        return view('manten.repuestos_agregar_similares',compact('datos','fotos','similares','marcas','modelos'));
    }


public function similares(Request $request)
    {


        $idrepuestos=$request->id_repuesto;
        $elusuarioID=Auth::user()->id;
        $repuesto=repuesto::find($idrepuestos);
        if(isset($request->btnGuardarSimilar))
        {

            $reglas=array(
                'anios_vehiculo_sim'=>'required|max:20'
            );

            $mensajes=array(
                'anios_vehiculo_sim.required'=>'Falta años.',
                'anios_vehiculo_sim.max'=>'Máximo 20 caracteres.'
            );

            $this->validate($request,$reglas,$mensajes);

            $similar=new similar;
            $similar->codigo_OEM_repuesto="---";
            $similar->anios_vehiculo=$request->anios_vehiculo_sim;
            $similar->activo=1;
            $similar->id_repuestos=$idrepuestos;
            $similar->id_marca_vehiculo=$request->cboMarcaSim;
            $similar->id_modelo_vehiculo=$request->cboModeloSim;
            $similar->usuarios_id=$elusuarioID;
            $similar->save();

            $similares=$this->damesimilares($idrepuestos);
            $fotos=repuestofoto::where('id_repuestos','=',$idrepuestos)->get();

            return redirect('repuesto/crea_similares')->with('similares',$similares)->with('fotos',$fotos);
            //return redirect('repuesto/crea_similares')->with('datos',$datos)->with('fotos',$fotos)->with('similares',$similares);
         }

    }

    public function actualizar_anio_similares($dato){
        list($id_similar,$anio_nuevo)=explode("_",$dato);
        $similar=similar::where('id',$id_similar)->update(['anios_vehiculo'=>$anio_nuevo]);
        if($similar>0){
            return "OK";
        }else{
            return "NO";
        }
    }

    public function dame_rv($idrep){
        
        $reguladores = regulador_voltaje::where('id_repuesto',$idrep)->get();
        if(count($reguladores) > 0){
            $v=view('fragm.repuesto_rv',compact('reguladores'))->render();
            return $v;
        }
        
    }

    public function rec_alt(Request $r){
        
        if(is_null($r->idrep)){
            return 'no hay repuesto';
        }else{
            try {
                $repuesto = repuesto::find($r->idrep);
                // revisar si rectificador y alternador ya existen en ese repuesto
                $rv = regulador_voltaje::where('id_repuesto',$repuesto->id)->get();
                
                foreach($rv as $r_){
                    if($r_->rectificador == trim($r->rec) && $r_->alternador == trim($r->alt)){
                        return 'existe';
                    }
                }
            
            
                
                $regulador_voltaje = new regulador_voltaje;
                
                $regulador_voltaje->id_repuesto = $repuesto->id;
                
                $regulador_voltaje->rectificador = trim($r->rec);
                
                $regulador_voltaje->alternador = trim($r->alt);
                
                $regulador_voltaje->save();

                $datos = regulador_voltaje::where('id_repuesto',$repuesto->id)->get();
                $v = view('fragm.regulador_voltaje',compact('datos'))->render();
                
                return $v;
                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        
        
        
    }

    public function guardaMotor(Request $r){
        
        if(is_null($r->idrep)){
            return 'no hay repuesto';
        }else{
            // si el motor ya existe en ese repuesto no se guarda y se retorna el mensaje
            $motor = motorPartida::where('id_repuesto',$r->idrep)->where('motor',$r->motor)->get();
            if(count($motor) > 0){
                return 'existe';
            }
            $repuesto = repuesto::find($r->idrep);
            
            try {
                $motor = new motorPartida;
                $motor->id_repuesto = $repuesto->id;
                $motor->motor = $r->motor;
                $motor->activo = 1;
                $motor->save();
                $datos = motorPartida::where('id_repuesto',$repuesto->id)->get();
                $v = view('fragm.motorPartida',compact('datos'))->render();
                
                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        
        
        
    }

    //Viene de repuestos_agregar_similares.blade AJAX function agregar_OEM()
    public function oems(Request $r)
    {


        //Comprobar que no esté repetido con validaciones


        $oem=new oem;
        $oem->codigo_oem=$r->cod_OEM;
        $oem->id_repuestos=$r->id_repuesto;
        $oem->usuarios_id=Auth::user()->id;
        $oem->activo=1;

        try{
            $oem->save();
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }


        //devolver vista oems
        $oems=$this->dame_oems($r->id_repuesto);


        return $oems;

    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //Listar repuestos
    {


        $familias=$this->damefamilias();
        $proveedores=$this->dameproveedores();
        $permisos = permissions_detail::all();
        foreach ($permisos as $p) {
            if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/repuesto'){
                return view('manten.repuestos',compact('familias','proveedores'));
            }
        }

        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('manten.repuestos',compact('familias','proveedores'));
        }else return redirect('home');
        
    }

    public function buscarepuestos(Request $r)
    {
        try {
                $fam=$r->idFa;
                $repuestos=$this->damerepuestos($fam);
                $vista=view('fragm.buscarepuesto',compact('repuestos'))->render();
                return $vista;
        } catch (\Exception $e) {
                return $e->getMessage();
        }
        
    }


    /**
     * Búsqueda AJAX para Select2 en el informe de rentabilidad.
     * Permite buscar por código interno, código de proveedor o descripción.
     */
    public function buscar_repuestos_select2(Request $request)
    {
        try {
            $termino = trim((string) $request->get('q', ''));

            $query = repuesto::select(
                    'repuestos.id',
                    'repuestos.codigo_interno',
                    'repuestos.cod_repuesto_proveedor',
                    'repuestos.descripcion'
                )
                ->where('repuestos.activo', 1);

            if ($termino !== '') {
                $query->where(function ($q) use ($termino) {
                    $q->where('repuestos.codigo_interno', 'LIKE', '%' . $termino . '%')
                      ->orWhere('repuestos.cod_repuesto_proveedor', 'LIKE', '%' . $termino . '%')
                      ->orWhere('repuestos.descripcion', 'LIKE', '%' . $termino . '%');
                });
            }

            $repuestos = $query
                ->orderBy('repuestos.codigo_interno')
                ->limit(30)
                ->get();

            $resultados = $repuestos->map(function ($repuesto) {
                $codigoProveedor = trim((string) $repuesto->cod_repuesto_proveedor);

                $texto = trim(
                    $repuesto->codigo_interno . ' - ' .
                    $repuesto->descripcion .
                    ($codigoProveedor !== ''
                        ? ' | Cód. proveedor: ' . $codigoProveedor
                        : '')
                );

                return [
                    'id' => $repuesto->id,
                    'text' => $texto,
                    'codigo_interno' => $repuesto->codigo_interno,
                    'descripcion' => $repuesto->descripcion,
                    'codigo_proveedor' => $codigoProveedor,
                ];
            })->values();

            return response()->json([
                'results' => $resultados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function dameTodoRepuestos(){
        $repuestos = repuesto::orderBy('id')->limit(90)->get();;
        return $repuestos;
    }

    public function buscar(){
        $locales=local::where('activo',1)->get();
        $value_modificar_precio = 0;
        return view('manten.repuesto_buscar',['locales' => $locales,'value_modificar_precio' => $value_modificar_precio]);
    }

    public function buscar_por_medida(){
        $familias = familia::orderBy('nombrefamilia','asc')->get();
        return view('manten.repuesto_buscar_medida',['familias' => $familias]);
    }

    public function damerepuestosmedida($medida){
        $repuestos = repuesto::where('medidas','LIKE',"%$medida%")->get();
        $v=view('fragm.repuesto_medidas',compact('repuestos'));
        return $v;
    }

     public function repuestos_por_familia($idfamilia){
        try {
            $repuestos = repuesto::select('repuestos.codigo_interno','repuestos.descripcion','repuestos.medidas','repuestos.id','familias.nombrefamilia','marcarepuestos.marcarepuesto','repuestos.stock_actual','repuestos.stock_actual_dos','repuestos.stock_actual_tres')
                                ->where('repuestos.id_familia',$idfamilia)
                                ->where('repuestos.activo',1)
                                ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->get();
            
            // Agregar campos calculados a cada repuesto ANTES de ordenar
            foreach($repuestos as $repuesto) {
                // Calcular stock disponible
                $stock_actual = $repuesto->stock_actual ?? 0;
                $stock_actual_dos = $repuesto->stock_actual_dos ?? 0;
                $stock_actual_tres = $repuesto->stock_actual_tres ?? 0;
                $repuesto->stock_disponible = \App\Services\StockPorLocalService::total($repuesto);
                
                // Ordenar las medidas del repuesto por valor numérico (mayor a menor)
                if ($repuesto->medidas) {
                    $porciones = preg_split('/[:,]/', $repuesto->medidas);
                    
                    // Ordenar las porciones por el valor numérico que contienen (de mayor a menor)
                    usort($porciones, function($a, $b) {
                        // Extraer números de cada porción
                        preg_match_all('/\d+(?:[.,]\d+)?/', $a, $matches_a);
                        preg_match_all('/\d+(?:[.,]\d+)?/', $b, $matches_b);
                        
                        // Obtener el valor máximo de cada porción
                        $max_a = 0;
                        if (!empty($matches_a[0])) {
                            $numeros_a = array_map(function($num) {
                                return (float) str_replace(',', '.', $num);
                            }, $matches_a[0]);
                            $max_a = max($numeros_a);
                        }
                        
                        $max_b = 0;
                        if (!empty($matches_b[0])) {
                            $numeros_b = array_map(function($num) {
                                return (float) str_replace(',', '.', $num);
                            }, $matches_b[0]);
                            $max_b = max($numeros_b);
                        }
                        
                        // Ordenar de mayor a menor
                        return $max_b <=> $max_a;
                    });
                    
                    // Reconstruir el string de medidas ordenadas
                    $repuesto->medidas = implode(',', $porciones);
                }
                
                // Extraer el valor numérico máximo de las medidas
                preg_match_all('/\d+(?:[.,]\d+)?/', $repuesto->medidas, $matches);
                if (!empty($matches[0])) {
                    $numeros = array_map(function($num) {
                        return (float) str_replace(',', '.', $num);
                    }, $matches[0]);
                    $repuesto->valor_orden = max($numeros);
                } else {
                    $repuesto->valor_orden = 0;
                }
            }
            
            // Ordenar los repuestos por el valor numérico más grande (de menor a mayor)
            $repuestos = $repuestos->sortBy('valor_orden')->values();

            $v=view('fragm.repuestos_por_familia',compact('repuestos'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function busqueda_rapida($medidas,$idfamilia){
        
        $repuestos = repuesto::select('repuestos.codigo_interno','repuestos.descripcion','repuestos.medidas','repuestos.id','familias.nombrefamilia')
                                ->where('repuestos.medidas','like','%'.$medidas.'%')
                                ->where('repuestos.id_familia',$idfamilia)
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->get();
        if($repuestos->count() == 0){
            // en la variable medidas agregar un espacio despues del caracter -
            $medidas_ = str_replace('-','- ',$medidas);

            $repuestos = repuesto::select('repuestos.codigo_interno','repuestos.descripcion','repuestos.medidas','repuestos.id','familias.nombrefamilia')
                                ->where('repuestos.medidas','like','%'.$medidas_.'%')
                                ->where('repuestos.id_familia',$idfamilia)
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->get();
        }
        
        
        $v=view('fragm.repuestos_por_familia',compact('repuestos'));
        return $v;
    }

    public function dame_repuestos_x_proveedor($id_prov)
    {
        try{

            /*
            $repuestos=repuesto::where('repuestos.id_proveedor',$id_prov)
            ->join('familias','repuestos.id_familia','familias.id')
            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
            ->join('proveedores','repuestos.id_proveedor','proveedores.id')
            ->join('paises','repuestos.id_pais','paises.id')
            ->get();
            */
            $repuestos=repuesto::where('repuestos.id_proveedor',$id_prov)
                        ->where('repuestos.activo',1)->paginate(20);
            //$vista=view('fragm.buscarepuesto',compact('repuestos'))->render();
            //return $vista;
            $provv=$id_prov;
            $familias=$this->damefamilias();
            $proveedores=$this->dameproveedores();
            return view('manten.repuestos',compact('familias','proveedores','repuestos'))->with('id_prov',$provv)->render();

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    public function buscar_por_codigo($dato)
    {
        $quien=substr($dato,0,1);
        $codigo=substr($dato,1);
        
        
        if($quien=='1')
            $repuestos=repuesto::where('repuestos.codigo_interno',$codigo)
                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                    ->join('users','repuestos.usuarios_id','users.id')
                    ->select('proveedores.empresa_nombre','repuestos.*','users.name')
                    ->get();

        if($quien=='2')
        {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', $codigo)
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('users','repuestos.usuarios_id','users.id')
                ->select('proveedores.empresa_nombre','repuestos.*','users.name')
                ->get();
        }


        if($repuestos->count()>0)
        {
            $firstDate = $repuestos[0]->created_at;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);

            $locales = local::where('activo',1)->get();

            $result = $years." años,  ".$months." meses y ".$days." dias";
            $data = [$repuestos->toJson(), $dias,$locales];
            return $data;
        }else{
            return "-1";
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // el codigo_interno procesarlo aquí según el prefijo y correlativo de la familia


        if(isset($request->btnGuardarRepuesto))
        {
            $stockmin=$request->stock_minimo+1;
            $preciocompra=$request->precio_compra+1;
            $familia_datos=familia::find($request->cboFamilia);
            $newval=$familia_datos->correlativo+1;
            $codinterno=$familia_datos->prefijo.$newval;
            $reglas=array(
                'descripcion'=>'required|max:50',
                'medidas'=>'required|max:500',
                'anios_vehiculo'=>'required|max:50',
                'version_vehiculo'=>'required|max:20',
                'cod_repuesto_proveedor'=>'required|max:50',
                'codigo_OEM_repuesto'=>'required|max:50',
                'precio_compra'=>'required|numeric',
                'precio_venta'=>'required|numeric|min:'.$preciocompra.'\'',
                'stock_minimo'=>'required|integer',
                'stock_maximo'=>'required|integer'



            );

            //'stock_maximo'=>'required|integer|min:'.$stockmin.'\''
            //FALTA PONER LOS MENSAJES DE ERROR
            $mensajes=array(
                'nombrefamilia.required'=>'Debe Ingresar un nombre de familia',
                'nombrefamilia.max'=>'El nombre de la familia debe tener como máximo 30 caracteres.',
                'nombrefamilia.unique'=>'El nombre de la familia ya existe.',
                'porcentaje.required'=>'Falta Ingresar el Porcentaje',
                'porcentaje.min'=>'El porcentaje debe ser mayor a 0.',
                'porcentaje.max'=>'El porcentaje debe ser menor a 100.',
                'porcentaje.numeric'=>'El porcentaje debe ser un número entero.',
                'prefijo.required'=>'Debe ingresar el prefijo.',
                'prefijo.alpha'=>'El prefijo debe ser caracteres.',
                'prefijo.size'=>'El prefijo debe tener 3 caracteres.'
            );


            $this->validate($request,$reglas);
            //$this->validate($request,$reglas,$mensajes) ;


            $repuesto=new repuesto;
            $repuesto->codigo_interno=$codinterno;
            $repuesto->descripcion=$request->descripcion;
            $repuesto->medidas=$request->medidas;
            $repuesto->anios_vehiculo=$request->anios_vehiculo;
            $repuesto->version_vehiculo=$request->version_vehiculo;
            $repuesto->cod_repuesto_proveedor=$request->cod_repuesto_proveedor;
            $repuesto->codigo_OEM_repuesto=$request->codigo_OEM_repuesto;
            $repuesto->precio_compra=$request->precio_compra;
            $repuesto->precio_venta=$request->precio_venta;
            $repuesto->stock_minimo=$request->stock_minimo;
            $repuesto->stock_maximo=$request->stock_maximo;
            $repuesto->codigo_barras=$request->codigo_barras;
            //$repuesto->id_unidad_venta=$request->cboUnidadVenta; // Implementar...
            $repuesto->id_familia=$request->cboFamilia;
            $repuesto->id_marca_vehiculo=$request->cboMarca;
            $repuesto->id_modelo_vehiculo=$request->cboModelo;
            $repuesto->id_marca_repuesto=$request->cboMarcaRepuesto;
            $repuesto->id_proveedor=$request->cboProveedor;
            $repuesto->id_pais=$request->cboPais;
            $repuesto->usuarios_id=Auth::user()->id; ;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, falta actualizar el correlativo de la familia
            $repuestos=$this->damerepuestos();
            $familias=$this->damefamilias();
            $marcas=$this->damemarcas();
            $modelos=$this->damemodelos();
            $marcarepuestos=$this->damemarcarepuestos();
            $proveedores=$this->dameproveedores();
            $paises=$this->damepaises();

            return view('manten.repuestos_crear',compact('codinterno','repuestos','familias','marcas','modelos','marcarepuestos','proveedores','paises'))->with('msgGuardado','Repuesto Guardado '.'('.$codinterno.")");

        }

    }

    public function guardar_xpress(Request $r){
        //llega idcliente, codigo, descripcion, precio
        //desde ventas_principal.blade function agregar_repuesto_xpress
        
        try{
            
            $repuesto=new repuesto;
            $fsd=familia::where('prefijo','FSD')->value('id');
            $repuesto->id_familia=$fsd;
            $msd=marcarepuesto::where('marcarepuesto','SIN DEFINIR')->value('id');
            $repuesto->id_marca_repuesto=$msd;
            $psd=proveedor::where('empresa_codigo','13.412.179-3')->value('id'); //pancho
            $repuesto->id_proveedor=$psd;
            $repuesto->local_id = 1;
            $ppsd=pais::where('nombre_pais','SIN DEFINIR')->value('id');
            $repuesto->id_pais=$ppsd;
            $repuesto->descripcion=strtoupper($r->descripcion);
            $repuesto->medidas="No definidas";
            
            if(empty($r->codigo)){
                $repuesto->cod_repuesto_proveedor="P".time();
            }else{
                $repuesto->cod_repuesto_proveedor=$r->codigo;
            }
            
            $repuesto->version_vehiculo="---"; // $item->cod2_repuesto_proveedor;
            $repuesto->codigo_OEM_repuesto="XPRESS";
            $repuesto->precio_compra=0;
            $repuesto->precio_venta=$r->precio;
            $repuesto->pu_neto=round($repuesto->precio_venta/(1+Session::get('PARAM_IVA')),2);
            $repuesto->stock_minimo=3;
            $repuesto->stock_maximo=10;
            $repuesto->stock_actual=20;
            $repuesto->codigo_barras=0;

            

            $familia_datos=familia::find($fsd);
            $newval=$familia_datos->correlativo+1;
            $codinterno=$familia_datos->prefijo.$newval;
            $repuesto->codigo_interno=$codinterno;

            

            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;

           
        try {
            $repuesto->save();
                    //Luego de guardar, actualizar el correlativo de la familia
                    $familia_datos->correlativo=$newval;
                    $familia_datos->save();

                    return $repuesto->id;
        } catch (\Exception $error) {
            return $error;
        }
            

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();

            return $v;
        }
    }

    public function actualiza_saldos($operacion,$idrep,$idlocal,$cantidad)
    {
        try{
            $b=saldo::where('id_repuestos',$idrep)
                                ->where('id_local',$idlocal)
                                ->first();
            //En caso de que sea la primera vez que se agrega a saldos.
            if(is_null($b))
            {
                $sald=new saldo;
                $sald->id_repuestos=$idrep;
                $sald->id_local=$idlocal;
                $sald->saldo=$cantidad;
                $sald->activo=1;
                $sald->usuarios_id=Auth::user()->id;
                $sald->save();
            }else{
                //Actualiza saldos por local
                switch ($operacion)
                {
                    case "I":  //Ingresos
                        $b->saldo=$b->saldo+$cantidad;

                    break;
                    case "E": //Egresos
                        $dif=$b->saldo-$cantidad;
                        if($dif<0) $dif=0; //prevenir saldos negativos
                        $b->saldo=$dif;
                    break;
                    default:
                }
                $b->save();
            }



            //Actualizamos saldos en repuestos (sumatoria de locales)
            
                $sumita=saldo::where('id_repuestos',$idrep)
                                    ->sum('saldo');
                $r=repuesto::find($idrep);
                $r->stock_actual=$sumita;
                // $r->save();
            
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    /**
     * Era el método show, le cambié de nombre
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


     public function dame_datos_repuesto($id)
    {


        $repuesto=$this->dame_un_repuesto($id);
        //Devuelve la vista renderizada para que la petición
        //AJAX en repuestos.blade.php pueda recibirlo y
        //mostrarlo.

        $estados = [
            'Pedido a otro proveedor', 
            'Sin stock en proveedor',
            'Con Stock', 
            'Rechazado', 
            'En transito',
            'Aprobado',
            'Cargado en carro',
            'Inactivo',
            'Pedido',
            'Pendiente',
            'En bodega, desembalando'
        ];
        $view=view('fragm.repuesto_datos',compact('repuesto','estados'))->render();
        return $view;
    }

    

    public function dame_similares($id)
    {


        $similares=$this->damesimilares($id);
        //dd($similares->toJson());
        $view=view('fragm.repuesto_similares',compact('similares'))->render();
        return $view;
        //return $similares->toJSON();
    }

    public function dame_similares_modificar($id_repuesto)
    {
        $similares=$this->damesimilares($id_repuesto);
        /*
        $similares=similar::select('marcavehiculos.marcanombre','modelovehiculos.modelonombre','similares.anios_vehiculo','similares.id')
                            ->where('similares.id_repuestos',$id_repuesto)
                            ->where('similares.activo',1)
                            ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
                            ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
                            ->orderBy('similares.id','DESC')
                            ->get();
        */
        $v=view('fragm.factuprodu_similares',compact('similares'))->render();
        return $v;

    }

    public function fotos_repuesto($id)
    {
        $fotos=$this->damefotosrepuesto($id);
        $id_repuesto = $id;
        $view=view('fragm.repuesto_fotos',compact('fotos','id_repuesto'))->render();
        return $view;
    }

    public function dame_fotos_modificar($id_repuesto)
    {
        $fotos=repuestofoto::select('id','urlfoto')
                    ->where('id_repuestos',$id_repuesto)
                    ->where('activo',1)
                    ->orderBy('id','DESC')
                    ->get();
        $v=view('fragm.factuprodu_fotos',compact('fotos'))->render();
        return $v;
    }

    public function dame_fotos_repuesto($id_repuesto){
        $fotos=repuestofoto::select('id','urlfoto')
        ->where('id_repuestos',$id_repuesto)
        ->where('activo',1)
        ->orderBy('id','ASC')
        ->get();
        $v=view('fragm.carrusel_fotos_repuesto',compact('fotos'))->render();
        return $v;
    }

    public function dame_oems($id)
    {

        $oems=oem::where('id_repuestos',$id)->orderBy('codigo_oem')->get();
        $v=view('fragm.repuesto_oems',compact('oems'))->render();
        return $v;
    }

    public function dame_oems_modificar($id_repuesto)
    {

        $oems=oem::select('id','codigo_oem')
                    ->where('activo',1)
                    ->where('id_repuestos',$id_repuesto)
                    ->orderBy('codigo_oem','ASC')
                    ->get();
        $v=view('fragm.factuprodu_oems',compact('oems'))->render();
        return $v;
    }

    public function dame_oems_clonar($codigo_interno,$tipo)
    {
        if($tipo == 1){
            $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
            if($repuesto){
                $oems=oem::select('id','codigo_oem')
                ->where('activo',1)
                ->where('id_repuestos',$repuesto->id)
                ->orderBy('codigo_oem','ASC')
                ->get();
            $v=view('fragm.factuprodu_oems',compact('oems','tipo'))->render();
            return $v;
            }else{
                return 'error';
            }
        }else{
            $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
        if($repuesto){
            $oems=oem::select('id','codigo_oem')
            ->where('activo',1)
            ->where('id_repuestos',$repuesto->id)
            ->orderBy('codigo_oem','ASC')
            ->get();
        $v=view('fragm.factuprodu_oems',compact('oems','tipo'))->render();
        return $v;
        }else{
            return 'error';
        }
        }
        
        
    }

    public function clonar_oems($codigo_origen, $codigo_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$codigo_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$codigo_destino)->first();

            if(!$repuesto_origen || !$repuesto_destino){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen o destino no existe.'], 404);
            }

            if($repuesto_origen->id === $repuesto_destino->id){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen y destino deben ser diferentes.'], 422);
            }

            $existe = $this->revisar_clonacion($repuesto_origen->id,$repuesto_destino->id,1);
        
            if(!$existe){
                if($repuesto_origen && $repuesto_destino){
                    $oems = oem::where('id_repuestos',$repuesto_origen->id)->get();
                    foreach($oems as $oem){
                            // si el oem no existe lo creamos
                            if(!oem::where('id_repuestos',$repuesto_destino->id)->where('codigo_oem',$oem->codigo_oem)->first()){
                                $oem_nuevo = new oem();
                                $oem_nuevo->id_repuestos = $repuesto_destino->id;
                                $oem_nuevo->codigo_oem = $oem->codigo_oem;
                                $oem_nuevo->save();
                            }
                    }
                    // guardamos la informacion de la cloncacion
                    $clonacion = new clonacion_oems();
                    $clonacion->id_repuesto_origen = $repuesto_origen->id;
                    $clonacion->id_repuesto_destino = $repuesto_destino->id;
                    $clonacion->fecha_emision = Carbon::today()->toDateString();
                    $clonacion->save();
                    return 'ok';
                }else{
                    return 'error';
                }
            
            }else{
                return 'error';
            }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        
        }

    public function clonar_rv($codigo_origen, $codigo_destino){
        try {
            $repuesto_origen  = repuesto::where('codigo_interno', $codigo_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno', $codigo_destino)->first();

            $existe = $this->revisar_clonacion($repuesto_origen->id, $repuesto_destino->id, 4);

            if (!$existe) {
                if ($repuesto_origen && $repuesto_destino) {
                    $reguladores = regulador_voltaje::where('id_repuesto', $repuesto_origen->id)->get();
                    foreach ($reguladores as $rv) {
                        if (!regulador_voltaje::where('id_repuesto', $repuesto_destino->id)
                                ->where('rectificador', $rv->rectificador)
                                ->where('alternador', $rv->alternador)
                                ->first()) {
                            $rv_nuevo = new regulador_voltaje();
                            $rv_nuevo->id_repuesto  = $repuesto_destino->id;
                            $rv_nuevo->rectificador = $rv->rectificador;
                            $rv_nuevo->alternador   = $rv->alternador;
                            $rv_nuevo->save();
                        }
                    }
                    // guardamos la información de la clonación
                    $clonacion = new clonacion_rv();
                    $clonacion->id_repuesto_origen  = $repuesto_origen->id;
                    $clonacion->id_repuesto_destino = $repuesto_destino->id;
                    $clonacion->fecha_emision = Carbon::today()->toDateString();
                    $clonacion->save();

                    $rv_destino  = regulador_voltaje::where('id_repuesto', $repuesto_destino->id)->get();
                    $clonaciones = $this->dame_clonaciones_rv();
                    return ['mensaje' => 'ok', 'reguladores' => $rv_destino, 'clonaciones' => $clonaciones];
                } else {
                    return ['mensaje' => 'error'];
                }
            } else {
                return ['mensaje' => 'error'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function resetearDiasPrecioRepuestos(){
        try {
            $id_familia_sin_definir = 312;
            $repuestos = repuesto::select(
                'repuestos.*',
                'marcarepuestos.id as idmarca',
                'marcarepuestos.marcarepuesto',
                'proveedores.empresa_nombre_corto'
            )
            ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
            ->where('repuestos.activo',1)
            // ->where('repuestos.estado','')
            ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
            ->join('proveedores','proveedores.id','repuestos.id_proveedor')
            ->get();
         
            foreach($repuestos as $repuesto){
                $repuesto->fecha_actualiza_precio = Carbon::today()->toDateString();
                $repuesto->save();
            }
            return 'ok';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

     public function clonar_fabricantes($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
            if(!$repuesto_origen || !$repuesto_destino){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen o destino no existe.'], 404);
            }
            if($repuesto_origen->id === $repuesto_destino->id){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen y destino deben ser diferentes.'], 422);
            }
            $existe = $this->revisar_clonacion($repuesto_origen->id,$repuesto_destino->id,3);
            if(!$existe){
                if($repuesto_origen && $repuesto_destino){
                    $fabricantes = fabricante::where('id_repuestos',$repuesto_origen->id)->get();
                    foreach($fabricantes as $fabricante){
                        if(!fabricante::where('id_repuestos',$repuesto_destino->id)->where('id_marcarepuestos',$fabricante->id_marcarepuestos)->where('codigo_fab',$fabricante->codigo_fab)->first()){
                            $fabricante_nuevo = new fabricante();
                            $fabricante_nuevo->id_repuestos = $repuesto_destino->id;
                            $fabricante_nuevo->id_marcarepuestos = $fabricante->id_marcarepuestos;
                            $fabricante_nuevo->codigo_fab = $fabricante->codigo_fab;
                            $fabricante_nuevo->save();
                        }
                    }
                    // guardamos la informacion de la cloncacion
                    $clonacion = new clonacion_fabs();
                    $clonacion->id_repuesto_origen = $repuesto_origen->id;
                    $clonacion->id_repuesto_destino = $repuesto_destino->id;
                    $clonacion->fecha_emision = Carbon::today()->toDateString();
                    $clonacion->save();
                    $fabs_destino = $this->dame_fabricantes($repuesto_destino->id);
                    $clonaciones = $this->dame_clonaciones_fabricantes();
                    return ['mensaje' => 'ok', 'fabricantes' => $fabs_destino, 'clonaciones' => $clonaciones];
                }else{
                    return ['mensaje' => 'error'];
                }
            }else{
                return ['mensaje' => 'error'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function descargarRepuestosStockMinimo(Request $request){
        try {
            $fecha_inicial = $request->get('fecha_inicial_excel', date('Y-m-d'));
            $fecha_final = $request->get('fecha_final_excel', date('Y-m-d'));
            $id_proveedor = $request->get('id_proveedor_excel', 0);

            // Subconsulta: un solo registro de stock_minimo por repuesto (ultima fecha del rango)
            $stockMinimoSub = stock_minimo::select(
                    'id_repuesto',
                    \DB::raw('MAX(fecha_emision) as fecha_emision')
                )
                ->whereBetween('fecha_emision', [
                    $fecha_inicial.' 00:00:00',
                    $fecha_final.' 23:59:59'
                ])
                ->groupBy('id_repuesto');

            // Subconsulta: obtener una sola última compra por repuesto
            $ultimaCompraSub = compras_det::select(
                    'compras_det.id_repuestos',
                    'compras_det.cantidad as cantidad_solicitada',
                    'compras_cab.factura_numero as numero_pedido',
                    'compras_cab.factura_fecha as fecha_compra',
                    'compras_cab.factura_id_proveedor'
                )
                ->join('compras_cab', 'compras_det.id_factura_cab', '=', 'compras_cab.id')
                ->whereRaw('compras_det.id = (
                    SELECT cd2.id
                    FROM compras_det cd2
                    JOIN compras_cab cc2 ON cd2.id_factura_cab = cc2.id
                    WHERE cd2.id_repuestos = compras_det.id_repuestos
                    ORDER BY cc2.factura_fecha DESC, cd2.id DESC
                    LIMIT 1
                )');

            // Construir query para obtener stock mínimo con datos de la última compra
            $query = repuesto::select(
                'proveedores.empresa_nombre_corto as proveedor',
                'sm.fecha_emision',
                'repuestos.codigo_interno',
                'repuestos.cod_repuesto_proveedor as codigo_proveedor',
                'repuestos.descripcion',
                \DB::raw(\App\Services\StockPorLocalService::totalSql() . ' as stock_actual'),
                'repuestos.precio_compra',
                'uc.cantidad_solicitada',
                'uc.numero_pedido',
                'uc.fecha_compra'
            )
            ->joinSub($stockMinimoSub, 'sm', function($join){
                $join->on('sm.id_repuesto', '=', 'repuestos.id');
            })
            ->leftJoinSub($ultimaCompraSub, 'uc', function($join){
                $join->on('repuestos.id', '=', 'uc.id_repuestos');
            })
            ->leftJoin('proveedores', 'uc.factura_id_proveedor', '=', 'proveedores.id')
            ->where('repuestos.activo', 1);

            // Filtrar por proveedor si no es "Todos" (0)
            if($id_proveedor != 0){
                $query->where('uc.factura_id_proveedor', $id_proveedor);
            }

            $repuestos = $query->get();

            // Obtener el proveedor para enviar el correo
            $proveedor = null;
            if($id_proveedor != 0){
                $proveedor = proveedor::find($id_proveedor);
            }

            // Generar el archivo Excel en memoria
            $nombreArchivo = 'repuestos_stock_minimo_'.date('Ymd_His').'.xlsx';
            
            // Generar contenido Excel en memoria usando storage temporal
            $tempPath = storage_path('app/temp/');
            if(!is_dir($tempPath)) mkdir($tempPath, 0755, true);
            
            $filePath = $tempPath.$nombreArchivo;
            Excel::store(new RepuestosStockMinimo($repuestos), 'temp/'.$nombreArchivo);
         
            // Enviar correo al proveedor si existe
            if($proveedor && $proveedor->vendedor_correo){
                try {
                    $excelContent = file_get_contents($filePath);

                    $correos_cc = array_values(array_filter([
                        'francisco.rojo.gallardo@gmail.com',
                        'josefranciscott@gmail.com',
                    ]));

                    $mail = Mail::to($proveedor->vendedor_correo);
                    if(!empty($correos_cc)){
                        $mail->cc($correos_cc);
                    }
                    $mail->send(new RepuestosStockMinimoMail($excelContent, $nombreArchivo));
                } catch (\Exception $e) {
                    // Log del error pero continuar con la descarga
                    \Log::error('Error al enviar email de stock mínimo: '.$e->getMessage());
                }
            }

            // Retornar descarga del archivo
            return Excel::download(new RepuestosStockMinimo($repuestos), $nombreArchivo);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function clonar_aplicaciones($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
            if(!$repuesto_origen || !$repuesto_destino){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen o destino no existe.'], 404);
            }
            if($repuesto_origen->id === $repuesto_destino->id){
                return response()->json(['mensaje' => 'error', 'detalle' => 'El repuesto de origen y destino deben ser diferentes.'], 422);
            }
            $existe = $this->revisar_clonacion($repuesto_origen->id,$repuesto_destino->id,2);
            if(!$existe){
                if($repuesto_origen && $repuesto_destino){
                    $similares = similar::where('id_repuestos',$repuesto_origen->id)->get();
                    foreach($similares as $similar){
                        if(!similar::where('id_repuestos',$repuesto_destino->id)->where('id_marca_vehiculo',$similar->id_marca_vehiculo)->where('id_modelo_vehiculo',$similar->id_modelo_vehiculo)->where('anios_vehiculo',$similar->anios_vehiculo)->first()){
                            $similar_nuevo = new similar();
                            $similar_nuevo->id_repuestos = $repuesto_destino->id;
                            $similar_nuevo->id_marca_vehiculo = $similar->id_marca_vehiculo;
                            $similar_nuevo->id_modelo_vehiculo = $similar->id_modelo_vehiculo;
                            $similar_nuevo->anios_vehiculo = $similar->anios_vehiculo;
                            $similar_nuevo->activo = 1;
                            $similar_nuevo->save();
                        }
                    }
                    // guardamos la informacion de la cloncacion
                    $clonacion = new clonacion_similares();
                    $clonacion->id_repuesto_origen = $repuesto_origen->id;
                    $clonacion->id_repuesto_destino = $repuesto_destino->id;
                    $clonacion->fecha_emision = Carbon::today()->toDateString();
                    $clonacion->save();
                    // retornar las aplicaciones del repuesto de destino
                    $aplicaciones = $this->dame_similares($repuesto_destino->id);
                    $clonaciones = $this->dame_clonaciones_similares();
                    return ['mensaje' => 'ok', 'aplicaciones' => $aplicaciones, 'clonaciones' => $clonaciones];
                }else{
                    return ['mensaje' => 'error'];
                }
            }else{
                return ['mensaje' => 'error'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function dame_clonaciones_similares(){
        $clonaciones = clonacion_similares::select('clonacion_similares.id','clonacion_similares.id_repuesto_origen','clonacion_similares.id_repuesto_destino','clonacion_similares.fecha_emision','repuestos.codigo_interno as codigo_origen','repuestos.descripcion as descripcion_origen','repuestos_2.codigo_interno as codigo_destino','repuestos_2.descripcion as descripcion_destino')
                        ->join('repuestos','clonacion_similares.id_repuesto_origen','repuestos.id')
                        ->join('repuestos as repuestos_2','clonacion_similares.id_repuesto_destino','repuestos_2.id')
                        ->orderBy('clonacion_similares.id','DESC')
                        ->get();
        return $clonaciones;
    }

    private function dame_clonaciones_rv(){
        $clonaciones = clonacion_rv::select(
                            'clonacion_rv.id',
                            'clonacion_rv.id_repuesto_origen',
                            'clonacion_rv.id_repuesto_destino',
                            'clonacion_rv.fecha_emision',
                            'repuestos.codigo_interno as codigo_origen',
                            'repuestos.descripcion as descripcion_origen',
                            'repuestos_2.codigo_interno as codigo_destino',
                            'repuestos_2.descripcion as descripcion_destino')
                        ->join('repuestos','clonacion_rv.id_repuesto_origen','repuestos.id')
                        ->join('repuestos as repuestos_2','clonacion_rv.id_repuesto_destino','repuestos_2.id')
                        ->orderBy('clonacion_rv.id','DESC')
                        ->get();
        return $clonaciones;
    }

    private function dame_clonaciones_fabricantes(){
        $clonaciones = clonacion_fabs::select('clonacion_fabs.id','clonacion_fabs.id_repuesto_origen','clonacion_fabs.id_repuesto_destino','clonacion_fabs.fecha_emision','repuestos.codigo_interno as codigo_origen','repuestos.descripcion as descripcion_origen','repuestos_2.codigo_interno as codigo_destino','repuestos_2.descripcion as descripcion_destino')
                        ->join('repuestos','clonacion_fabs.id_repuesto_origen','repuestos.id')
                        ->join('repuestos as repuestos_2','clonacion_fabs.id_repuesto_destino','repuestos_2.id')
                        ->orderBy('clonacion_fabs.id','DESC')
                        ->get();
        return $clonaciones;
    }

     private function revisar_clonacion($id_repuesto_origen,$id_repuesto_destino,$op){
        if($op == 1){
            $clonacion = clonacion_oems::where('id_repuesto_origen',$id_repuesto_origen)
                                    ->where('id_repuesto_destino',$id_repuesto_destino)
                                    ->first();
            if($clonacion){
                return true;
            }else{
                return false;
            }
        }else if($op == 2){
            $clonacion = clonacion_similares::where('id_repuesto_origen',$id_repuesto_origen)
                                    ->where('id_repuesto_destino',$id_repuesto_destino)
                                    ->first();
            if($clonacion){
                return true;
            }else{
                return false;
            }
        }else if($op == 3){
            $clonacion = clonacion_fabs::where('id_repuesto_origen',$id_repuesto_origen)
                                    ->where('id_repuesto_destino',$id_repuesto_destino)
                                    ->first();
            if($clonacion){
                return true;
            }else{
                return false;
            }
        }else{
            // op == 4: regulador de voltaje
            $clonacion = clonacion_rv::where('id_repuesto_origen',$id_repuesto_origen)
                                    ->where('id_repuesto_destino',$id_repuesto_destino)
                                    ->first();
            if($clonacion){
                return true;
            }else{
                return false;
            }
        }
    }


    public function dame_fabricantes($id)
    {

        $fabs=fabricante::select('codigo_fab','marcarepuesto')
                                    ->where('repuestos_fabricantes.id_repuestos',$id)
                                    ->join('marcarepuestos','repuestos_fabricantes.id_marcarepuestos','marcarepuestos.id')
                                    ->orderBy('codigo_fab')
                                    ->get();
        $v=view('fragm.repuesto_fabs',compact('fabs'))->render();
        return $v;
    }

    public function dame_fabricantes_modificar($id_repuesto)
    {

        $fabs=fabricante::select('repuestos_fabricantes.id','repuestos_fabricantes.codigo_fab','marcarepuestos.marcarepuesto')
                            ->join('marcarepuestos','repuestos_fabricantes.id_marcarepuestos','marcarepuestos.id')
                            ->where('repuestos_fabricantes.activo',1)
                            ->where('repuestos_fabricantes.id_repuestos',$id_repuesto)
                            ->orderBy('marcarepuestos.marcarepuesto','ASC')
                            ->get();
        $v=view('fragm.factuprodu_fabs',compact('fabs'))->render();
        return $v;
    }

    public function dame_fabricantes_clonar($codigo_interno){
        $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
        if($repuesto){
            $fabs=fabricante::select('codigo_fab','marcarepuesto')
                                    ->where('repuestos_fabricantes.id_repuestos',$repuesto->id)
                                    ->join('marcarepuestos','repuestos_fabricantes.id_marcarepuestos','marcarepuestos.id')
                                    ->orderBy('codigo_fab')
                                    ->get();
            $v=view('fragm.repuesto_fabs',compact('fabs'))->render();
            return $v;
        }else{
            return 'error';
        }
    }

    private function damelocales()
    {
    	$l=local::all();
    	return $l;
    }

    public function damelocales_activos(){
        $l=local::where('activo',1)->get();
    	return $l;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
    	$locales=$this->damelocales();
        $marcas=$this->damemarcas();
        $repuesto=Collect();
        return view('manten.repuestos_modificar',compact('locales','marcas','repuesto'));
    }

    public function editar($id_repuesto)
    {
    	$locales=$this->damelocales();
        $marcas=$this->damemarcas();
        $repuesto=repuesto::find($id_repuesto);

        $fecha_actualizacion = Carbon::parse($repuesto->updated_at)->format("d-m-Y");

        return view('manten.repuestos_modificar',compact('locales','marcas','repuesto','fecha_actualizacion'));
    }

    public function cambiaprecio($id,$precio_compra,$precio_venta)
    {

        $r=repuesto::find($id);
        $r->precio_compra=$precio_compra;
        $r->precio_venta=$precio_venta;
        $r->save();
        return "OK";
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        /* Borrar los hijos
        similares y fotos

        */
        similar::where('id_repuestos',$id)->delete();
        repuestofoto::where('id_repuestos',$id)->delete();
        repuesto::destroy($id);

        $familias=$this->damefamilias();
        $marcas=$this->damemarcas();
        $modelos=$this->damemodelos();
        $paises=$this->damepaises();
        return view('manten.repuestos',compact('familias','marcas','modelos','paises'))->with('msgGuardado','Repuesto Eliminado...');


        //return redirect()->action('repuestocontrolador@index');

        //return view('fragm.mensajes')->with('msgGuardado','DESTROY ID: '.$id);
    }

    public function resetear_tiempo_precio($idrep){
        $repuesto = repuesto::find($idrep);
        $repuesto->fecha_actualiza_precio = Carbon::today()->toDateString();
        $repuesto->save();
        return $repuesto;
    }

    function damestock_vista(){
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/repuesto/stockrepuesto'){
                        $bodegas = $this->damelocales_activos();
                        $ultimos_repuestos_vendidos = boleta_detalle::join('repuestos','boletas_detalle.id_repuestos','repuestos.id')->orderBy('boletas_detalle.created_at','desc')->take(5)->distinct()->get();
                        $repuestos_mas_vendidos = repuesto::select('descripcion','id','codigo_interno','stock_actual','precio_venta')->take(5)->get(); 
                        $ultimos_repuestos = repuesto::select('descripcion','id','codigo_interno','stock_actual','precio_venta')->where('id_familia','<>',312)->orderBy('id','desc')->take(5)->get();
                        $ultimas_ventas = boleta::orderBy('id','desc')->take(5)->get();
                        foreach($ultimas_ventas as $u){
                            $u->fecha_formateada = Carbon::parse($u->created_at)->format("d-m-Y");
                        }
                        $v = view(\App\Services\StockPorLocalService::habilitado() ? 'inventario.stock_por_local' : 'inventario.repuestos_buscar_stock',compact('bodegas','repuestos_mas_vendidos','ultimos_repuestos','ultimas_ventas','ultimos_repuestos_vendidos'))->render();
                        return $v;
                    }
            }
            $user = Auth::user();
            if ($user->rol->nombrerol == "Administrador") {
                $bodegas = $this->damelocales_activos();
                $ultimos_repuestos_vendidos_boleta = boleta_detalle::join('repuestos','boletas_detalle.id_repuestos','repuestos.id')->orderBy('boletas_detalle.created_at','desc')->take(20)->distinct()->get();
                $ultimos_repuestos_vendidos_factura = factura_detalle::join('repuestos','facturas_detalle.id_repuestos','repuestos.id')->orderBy('facturas_detalle.created_at','desc')->take(20)->distinct()->get();
                // unir las dos colecciones y mostrar 5 de ellas y ordenarlas por el created_at
                $ultimos_repuestos_vendidos = $ultimos_repuestos_vendidos_boleta->merge($ultimos_repuestos_vendidos_factura)->sortByDesc('created_at')->take(10);

                $repuestos_mas_vendidos = repuesto::select('descripcion','id','codigo_interno','stock_actual','precio_venta')->take(5)->get(); 
                $ultimos_repuestos = repuesto::select('descripcion','id','codigo_interno','stock_actual','precio_venta')->where('id_familia','<>',312)->orderBy('id','desc')->take(10)->get();
                $ultimas_ventas_boletas = boleta::orderBy('id','desc')->take(30)->get();
                foreach($ultimas_ventas_boletas as $ub){
                    $ub->tipo_doc = "Boleta";
                }
                $ultimas_ventas_factura = factura::orderBy('id','desc')->take(30)->get();
                foreach($ultimas_ventas_factura as $uf){
                    $uf->tipo_doc = "Factura";
                }

                // unir las dos colecciones y mostrar 5 de ellas y ordenarlas por el created_at
                $ultimas_ventas = $ultimas_ventas_boletas->merge($ultimas_ventas_factura)->sortByDesc('created_at')->take(10);

                
                foreach($ultimas_ventas as $u){
                    $u->fecha_formateada = Carbon::parse($u->created_at)->format("d-m-Y");
                    $u->hora = Carbon::parse($u->created_at)->format("H:i");
                }
                $v = view(\App\Services\StockPorLocalService::habilitado() ? 'inventario.stock_por_local' : 'inventario.repuestos_buscar_stock',compact('bodegas','repuestos_mas_vendidos','ultimos_repuestos','ultimas_ventas','ultimos_repuestos_vendidos'))->render();
                return $v;
            } else {
                return redirect('home');
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    function dameultimosrepuestos(){
        try {
        
            if(Auth::user()->dame_permisos_inventario()->count() > 0){
                foreach (Auth::user()->dame_permisos_inventario() as $permiso) {
                    if($permiso->path_ruta == '/ultimos_repuestos'){
                        $fotos = [];
                        $repuestos = repuesto::select('repuestos.*','users.name')
                                ->join('users','repuestos.usuarios_id','users.id')
                                ->orderBy('repuestos.id','desc')->where('repuestos.id_familia','<>',312)->take(24)->get();
                            
                        $placeholder = 'https://placehold.co/100x100';
                foreach($repuestos as $r){
                            $foto = repuestofoto::where('id_repuestos','=',$r->id)->first();
                            if(isset($foto) && !empty($foto->urlfoto)){
                                array_push($fotos,$foto->urlfoto);
                            }else{
                                array_push($fotos,$placeholder);
                            }
                            
                        }
                        
                        return [$repuestos,$fotos];
                    }
                }
            }
          
            if(Auth::user()->rol->nombrerol !== 'Administrador'){
                return 0;
            }else{
   
                $fotos = [];
                $repuestos = repuesto::select('repuestos.*','users.name')
                        ->join('users','repuestos.usuarios_id','users.id')
                        ->orderBy('repuestos.id','desc')->where('repuestos.id_familia','<>',312)->take(24)->get();
                
                $placeholder = 'https://placehold.co/100x100';
                foreach($repuestos as $r){
                    
                    $foto = repuestofoto::where('id_repuestos','=',$r->id)->first();
                    if(isset($foto) && !empty($foto->urlfoto)){
                        array_push($fotos,$foto->urlfoto);
                    }else{
                        array_push($fotos,$placeholder);
                    }
                    
                }
                
                return [$repuestos,$fotos];
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    function damestockrepuesto(Request $req){
        try {
       
            $id = $req->codigo_repuesto;
            $opt = $req->option;
            $responsables = User::where('activo',1)->whereIn('role_id', [10, 11,12,13,14,16])->get();

            //Si recibe el parametro 'desde' es que viene de otra vista (Ingresados vs Vendidos)
            if($req->desde){
                if($opt === 'cod_int'){
                    $data = [];
                    $repuesto = repuesto::select('repuestos.id',
                                                'repuestos.codigo_interno',
                                                // 'repuestos.codigo_fab',
                                                'repuestos.codigo_barras',
                                                'repuestos.precio_compra',
                                                'repuestos.descripcion',
                                                'repuestos.stock_actual',
                                                'repuestos.stock_actual_dos',
                                                'repuestos.stock_actual_tres',
                                                'repuestos.precio_venta',
                                                'repuestos.precio_antiguo',
                                                'repuestos.id_marca_repuesto',
                                                'marcarepuestos.marcarepuesto',
                                                'paises.nombre_pais',
                                                'users.name',
                                                'responsables.name as nombre_responsable_uno',
                                                'responsables2.name as nombre_responsable_dos',
                                                'responsables3.name as nombre_responsable_tres')
                                            ->leftjoin('users','repuestos.usuario_id_modifica','users.id')
                                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                            ->leftjoin('users as responsables','repuestos.id_responsable','responsables.id')
                                            ->leftjoin('users as responsables2','repuestos.id_responsable_dos','responsables2.id')
                                            ->leftjoin('users as responsables3','repuestos.id_responsable_tres','responsables3.id')
                                            ->join('paises','repuestos.id_pais','paises.id')
                                            ->where('repuestos.codigo_interno',$id)
                                            ->get();
                
                    if($repuesto->count() > 0){
                        try {
                            $factura_detalle = compras_det::select('compras_det.*','compras_cab.factura_numero','proveedores.empresa_nombre_corto')
                                                    ->join('compras_cab','compras_det.id_factura_cab','compras_cab.id')
                                                    ->leftjoin('proveedores','compras_cab.factura_id_proveedor','proveedores.id')
                                                    ->where('compras_det.id_repuestos',$repuesto[0]->id)->orderBy('created_at','desc')
                                                    ->get();
                            $boleta = boleta_detalle::select('boletas_detalle.cantidad','boletas_detalle.precio_venta','boletas.fecha_emision as fecha_emision','users.name','boletas.num_boleta as num_doc','locales.local_nombre','clientes.nombres as nombre_cliente','boletas.estado')
                                                    ->where('boletas_detalle.id_repuestos',$repuesto[0]->id)
                                                    ->join('locales','boletas_detalle.id_local','locales.id')
                                                    ->leftjoin('users','boletas_detalle.usuarios_id','users.id')
                                                    ->join('boletas','boletas.id','boletas_detalle.id_boleta')
                                                    ->leftjoin('clientes','boletas.id_cliente','clientes.id')
                                                    ->where('boletas.estado',1)
                                                    ->orderBy('boletas.fecha_emision','asc')
                                                    ->get();

                            foreach($boleta as $b){
                                $b->tipo_doc='boleta';
                            }
                     
                            $factura = factura_detalle::select('facturas_detalle.cantidad','facturas_detalle.precio_venta','facturas.fecha_emision as fecha_emision','users.name','facturas.num_factura as num_doc','locales.local_nombre','clientes.nombres as nombre_cliente','facturas.estado')
                                                        ->where('facturas_detalle.id_repuestos',$repuesto[0]->id)
                                                        ->join('locales','facturas_detalle.id_local','locales.id')
                                                        ->leftjoin('users','facturas_detalle.usuarios_id','users.id')
                                                        ->join('facturas','facturas.id','facturas_detalle.id_factura')
                                                        ->join('clientes','facturas.id_cliente','clientes.id')
                                                        ->whereIn('facturas.estado', [1, 2])
                                                        ->orderBy('facturas.fecha_emision','asc')
                                                        ->get();


                            foreach($factura as $f){
                                $f->tipo_doc='factura';
                            }
                
                            $valemercaderia = vale_mercaderia_detalle::select('vale_mercaderia_detalle.cantidad','vale_mercaderia.created_at as fecha_emision','users.name','vale_mercaderia.numero_boucher as num_doc')
                                                                        ->where('vale_mercaderia_detalle.repuesto_id',$repuesto[0]->id)
                                                                        ->leftjoin('users','vale_mercaderia_detalle.usuario_id','users.id')
                                                                        ->join('vale_mercaderia','vale_mercaderia.id','vale_mercaderia_detalle.vale_mercaderia_id')
                                                                        ->orderBy('vale_mercaderia.created_at','asc')
                                                                        ->get();

                            foreach($valemercaderia as $v){
                                $v->tipo_doc='valemercaderia';
                            }

                            $traspasos = traspaso_mercaderia_detalle::select('traspaso_mercaderia_detalle.*','users.name')
                                                                    ->join('traspaso_mercaderia','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                                    ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                                    ->where('traspaso_mercaderia_detalle.repuesto_id',$repuesto[0]->id)
                                                                    ->get();

                            foreach($factura_detalle as $f){
                                //Le damos formato a la fecha
                                $f->created_at = Carbon::parse($f->created_at)->format("d-m-Y");
                            }
                            //Juntamos todas las ventas, tanto boletas, facturas y vale de mercaderia
                            $boleta_detalle = ($boleta->mergeRecursive($factura)->mergeRecursive($valemercaderia));

                            // Ordenar ANTES de formatear las fechas (cuando aún son DateTime/Carbon)
                            $boleta_detalle = $boleta_detalle->sortByDesc('fecha_emision')->values();

                            // Ahora formateamos las fechas
                            foreach($boleta_detalle as $b){
                                $b->fecha_emision = Carbon::parse($b->fecha_emision)->format("d-m-Y");
                            }

                            foreach($traspasos as $t){
                                $t->fecha_emision = Carbon::parse($t->fecha_emision)->format("d-m-Y");
                            }
                            $imagenes_repuesto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
                            //Formateamos la fecha de modificación del stock
                            $repuesto[0]->fecha_ultima = Carbon::parse($repuesto[0]->fecha_ultima)->format("d-m-Y");
             
                            foreach($factura_detalle as $f){
                                //Separamos el valor de created_at para que solo me muestre la fecha en formato YYYY-MM-DD que será guardado en la primera posición
                                $porciones = explode("T", $f->created_at);
                                //Creamos el atributo fecha para asignarle la nueva fecha formateada
                                $f->fecha = Carbon::parse($porciones[0])->format("d-m-Y");
                            }

                            $devoluciones_mercaderia = devolucion_mercaderia::select('devolucion_mercaderia.cantidad','devolucion_mercaderia.created_at as fecha_emision','users.name','notas_de_credito.num_nota_credito as num_nc','devolucion_mercaderia.tipo_doc','notas_de_credito.docum_referencia')
                            ->where('devolucion_mercaderia.repuesto_id',$repuesto[0]->id)
                            ->where('devolucion_mercaderia.activo',0)
                            ->join('notas_de_credito','devolucion_mercaderia.num_nc','notas_de_credito.num_nota_credito')
                            ->join('users','devolucion_mercaderia.usuario_id','users.id')
                            ->get();
                        
                            $total_devoluciones = 0;
                     
                            foreach($devoluciones_mercaderia as $d){
                                $total_devoluciones += $d->cantidad;
                                if($d->tipo_doc == 'bo'){
                                    $d->id_doc = boleta::where('num_boleta',$d->num_nc)->value('id');
                                }else if($d->tipo_doc == 'fa'){
                                    $d->id_doc = factura::where('num_factura',$d->num_nc)->value('id');
                                }else if($d->tipo_doc == 'nc'){
                                 
                                    $doc = nota_de_credito::where('id',$d->num_nc)->first();
                                    if($doc){
                                        $d->id_nc = $doc->id;
                                        $d->id_doc = $doc->id;
                                    }
                              
                                }
                            }
                            $fechas_caida_stock_minimo = stock_minimo::where('id_repuesto', $repuesto[0]->id)
                                                                    ->orderBy('created_at', 'desc')
                                                                    ->get();

                            foreach($fechas_caida_stock_minimo as $f){
                                $f->fecha = Carbon::parse($f->created_at)->format("d-m-Y");
                            }

                            array_push(
                                $data,
                                $factura_detalle,
                                $boleta_detalle,
                                $repuesto[0],
                                $traspasos,
                                $fechas_caida_stock_minimo,
                                $devoluciones_mercaderia,
                                $total_devoluciones,
                                $imagenes_repuesto
                            );
                            return $data;
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }

                    }else{
                        return ["error","Verifique el codigo interno"];
                    }

                }

            }else{
                //Recibido desde la vista stockrepuesto
                if($opt === 'cod_int'){
                    $encontrados=repuesto::where('repuestos.codigo_interno',$id)
                    ->where('repuestos.activo',1)
                    ->leftjoin('users','repuestos.id_responsable','users.id')
                    ->leftjoin('users as responsables2','repuestos.id_responsable_dos','responsables2.id')
                    ->leftjoin('users as responsables3','repuestos.id_responsable_tres','responsables3.id')
                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    // ->join('repuestos_fotos','repuestos.id','repuestos_fotos.id_repuestos')
                    ->select('repuestos.*','users.name as nombre_responsable','responsables2.name as nombre_responsable_dos','responsables3.name as nombre_responsable_tres','marcarepuestos.marcarepuesto','paises.nombre_pais')
                    ->get();

                    if($encontrados->count()>0){
                        $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$encontrados[0]->id)->first();
                        $repuesto=$encontrados;
                        $locales = $this->damelocales_activos();
                        if($urlfoto){
                            return [$repuesto,$urlfoto, $responsables, $locales];
                        }else{
                            $urlfoto = 'fotozzz/notfound.png';

                            return [$repuesto,$urlfoto, $responsables, $locales];
                        }

                    }else{
                        return ["error","Verifique el codigo interno"];
                    }

                }else if($opt === 'oem'){

                        $oem = oem::select('id_repuestos')
                                    ->where('oems.codigo_oem','LIKE','%'.$id.'%')
                                    ->where('oems.activo',1)
                                    ->get()
                                    ->toArray();

                        if(count($oem)>0){
                            $encontrados=repuesto::wherein('repuestos.id',$oem)
                                ->where('repuestos.activo',1)
                                ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                ->get();

                        }else{
                            return ["error","Verifique el codigo OEM"];
                        }


                    if($encontrados->count()>0){
                        $repuesto=$encontrados;
                        return [$repuesto,'', $responsables];
                    }else{
                        return ["error","Verifique el codigo OEM"];
                    }

                }else{
                    try {
                        $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$id . '%')
                        ->where('repuestos.activo',1)
                        ->select('repuestos.*')
                        ->get();

                        if($repuestos->count() > 0){
                            $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuestos[0]->id)->first();
                            return [$repuestos,$urlfoto, $responsables];
                        }else{
                            return ["error","Verifique el codigo de proveedor"];
                        }

                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }

                }
            }
            } catch (\Exception $e) {
                return $e->getMessage();
            }



    }

    function revisar_stock_proveedor(){
        $repuestos = repuesto::select('repuestos.*','familias.nombrefamilia','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto')
                                                ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                                ->join('familias','familias.id','repuestos.id_familia')
                                                ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                                ->where('repuestos.estado','Sin stock en proveedor')
                                                ->where('repuestos.activo',1)
                                                 ->where('repuestos.id_familia','<>',312)
                                                ->get();

            $repuestos_sin_stock_proveedor = [];

            // revisar si los repuestos tienen stock en la bodega
            foreach($repuestos as $r){
                $stock_minimo = stock_minimo::where('id_repuesto',$r->id)->first();
                if($stock_minimo && \App\Services\StockPorLocalService::total($r) < $r->stock_minimo){
                    array_push($repuestos_sin_stock_proveedor,$r);
                }
            }  

            return count($repuestos_sin_stock_proveedor);
    }

      public function guardar_nuevo_stock_minimo_critico($id, $nuevo_stock_minimo, $estado){
        try {
    
            $repuesto = repuesto::find($id);

            $repuesto->stock_minimo = $nuevo_stock_minimo;
    
            if($estado == "Aprobado"){
                $repuesto->estado = null;
            }else{
                $repuesto->estado = $estado;
            }
    
            $repuesto->id_modifica_estado = Auth::user()->id;
            $repuesto->fecha_modificacion_estado = Carbon::now()->toDateTimeString();
            $repuesto->save();
    
    
            return [
                'estado' => 'OK',
                'mensaje' => 'Stock mínimo crítico actualizado',
                'id' => $repuesto->id,
                'nuevo_estado' => $repuesto->estado,
                'stock_minimo' => $repuesto->stock_minimo
            ];
    
        } catch (\Exception $e) {
    
            return [
                'estado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ];
    
        }
    }

    function revisar_stock_critico(){
        $id_familia_sin_definir = 312;
            $rep = repuesto::select(
                'repuestos.*',
                'marcarepuestos.id as idmarca',
                'marcarepuestos.marcarepuesto',
                'proveedores.empresa_nombre_corto'
            )
            ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
            ->where('repuestos.activo',1)
            ->where('repuestos.estado','')
            ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' < 1')
            ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
            ->join('proveedores','proveedores.id','repuestos.id_proveedor')
            ->get();
            return count($rep);
    }

    function listar_oems(){
        try {
            $clonaciones = clonacion_oems::all();
            if($clonaciones->count() > 0){
                foreach ($clonaciones as $clonacion) {
                    $repuesto = repuesto::find($clonacion->id_repuesto_destino);
                    $repuesto_ = repuesto::find($clonacion->id_repuesto_origen);
                    $clonacion->codigo_interno_destino = $repuesto ? $repuesto->codigo_interno : 'NO EXISTE';
                    $clonacion->codigo_interno_origen = $repuesto_ ? $repuesto_->codigo_interno : 'NO EXISTE';
                }
            }else{
                $clonaciones = [];
            }
            return view('manten.listar_oems',[
                'clonaciones' => $clonaciones
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function deshacer_clonacion_oems($id){
        try {
            $clonacion = clonacion_oems::find($id);

            if(!$clonacion){
                return response()->json(['mensaje' => 'error', 'detalle' => 'La clonación OEM ya no existe.'], 404);
            }

            // El historial no permite distinguir los OEM clonados de los preexistentes.
            // Por seguridad solo se elimina la relación del historial.
            $clonacion->delete();

            return response()->json([
                'mensaje' => 'ok',
                'detalle' => 'La clonación fue eliminada del historial.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error eliminando clonación OEM: '.$e->getMessage());
            return response()->json(['mensaje' => 'error', 'detalle' => 'No fue posible eliminar la clonación OEM.'], 500);
        }
    }

    public function revisar_stock_minimo(){
        try {
                $hoy = Carbon::today();
                $id_familia_sin_definir = 312;
                try {
                   //Subconsulta para obtener la ultima fecha de emision de cada repuesto
                    $subquery = stock_minimo::select('id_repuesto', \DB::raw('MAX(fecha_emision) as ultima_fecha_emision'))
                    ->groupBy('id_repuesto');

                    // unir la subconsulta con la tabla de repuestos 
                    $reps = repuesto::select('repuestos.*', 'stock_minimo.ultima_fecha_emision')
                        ->joinSub($subquery, 'stock_minimo', function ($join) {
                            $join->on('repuestos.id', '=', 'stock_minimo.id_repuesto');
                        })
                        ->where('repuestos.activo',1)
                        ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                        ->where('repuestos.estado','')
                        ->orderBy('stock_minimo.ultima_fecha_emision')
                        ->groupBy('repuestos.id')
                        ->get();
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
                
                $reps_ = [];
                foreach($reps as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                    $r->ultima_fecha_emision = Carbon::parse($r->ultima_fecha_emision)->format('d-m-Y');
                    $repuesto = repuesto::find($r->id);
                    
                    $stock_total = \App\Services\StockPorLocalService::total($repuesto);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($reps_, $r);
                    } 
                }

            return count($reps_);
        } catch (\Exception $e) {
           return $e->getMessage();
        }
        
    }

    public function clonaciones_rv(){
        try {
            $clonaciones = $this->dame_clonaciones_rv();
            return view('manten.clonaciones_rv', compact('clonaciones'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_rv_clonar($codigo_interno){
        try {
            $repuesto = repuesto::where('codigo_interno', $codigo_interno)->first();
            if($repuesto){
                return $this->dame_rv($repuesto->id);
            }else{
                return 'error';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function deshacer_clonacion_rv($id){
        try {
            $clonacion = clonacion_rv::find($id);
            $reguladores = regulador_voltaje::where('id_repuesto', $clonacion->id_repuesto_destino)->get();
            foreach ($reguladores as $rv) {
                $rv->delete();
            }
            $clonacion->delete();
            $clonaciones = $this->dame_clonaciones_rv();
            return ['mensaje' => 'ok', 'clonaciones' => $clonaciones];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function eliminar_clonacion_aplicaciones($id){
        try {
            $clonacion = clonacion_similares::find($id);
            if(!$clonacion){
                return response()->json(['mensaje' => 'error', 'detalle' => 'La clonación de aplicaciones ya no existe.'], 404);
            }
            $clonacion->delete();
            return response()->json([
                'mensaje' => 'ok',
                'detalle' => 'La clonación fue eliminada del historial.',
                'clonaciones' => $this->dame_clonaciones_similares(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error eliminando clonación de aplicaciones: '.$e->getMessage());
            return response()->json(['mensaje' => 'error', 'detalle' => 'No fue posible eliminar la clonación.'], 500);
        }
    }

    public function eliminar_clonacion_fabricantes($id){
        try {
            $clonacion = clonacion_fabs::find($id);
            if(!$clonacion){
                return response()->json(['mensaje' => 'error', 'detalle' => 'La clonación de fabricantes ya no existe.'], 404);
            }
            $clonacion->delete();
            return response()->json([
                'mensaje' => 'ok',
                'detalle' => 'La clonación fue eliminada del historial.',
                'clonaciones' => $this->dame_clonaciones_fabricantes(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error eliminando clonación de fabricantes: '.$e->getMessage());
            return response()->json(['mensaje' => 'error', 'detalle' => 'No fue posible eliminar la clonación.'], 500);
        }
    }
    public function clonaciones(){
        try {
            $clonaciones = clonacion_oems::all();
            // recorrer las clonaciones y obtener los datos de los repuestos
            foreach ($clonaciones as $clonacion) {
                $repuesto = repuesto::find($clonacion->id_repuesto_destino);
                // $clonacion->codigo_interno_destino = $repuesto->codigo_interno;
                $repuesto_ = repuesto::find($clonacion->id_repuesto_origen);
                
                    $clonacion->codigo_interno_destino = $repuesto ? $repuesto->codigo_interno : 'NO EXISTE';
                    $clonacion->codigo_interno_origen = $repuesto_ ? $repuesto_->codigo_interno : 'NO EXISTE';
            }

            $fabs=clonacion_fabs::all();

            foreach($fabs as $fab){
                $repuesto = repuesto::find($fab->id_repuesto_destino);
                // $clonacion->codigo_interno_destino = $repuesto->codigo_interno;
                $repuesto_ = repuesto::find($fab->id_repuesto_origen);
                
                    $fab->codigo_interno_destino = $repuesto ? $repuesto->codigo_interno : 'NO EXISTE';
                    $fab->codigo_interno_origen = $repuesto_ ? $repuesto_->codigo_interno : 'NO EXISTE';
            }

            $similares = clonacion_similares::all();

            foreach($similares as $similar){
                $repuesto = repuesto::find($similar->id_repuesto_destino);
                $repuesto_ = repuesto::find($similar->id_repuesto_origen);
                
                    $similar->codigo_interno_destino = $repuesto ? $repuesto->codigo_interno : 'NO EXISTE';
                    $similar->codigo_interno_origen = $repuesto_ ? $repuesto_->codigo_interno : 'NO EXISTE';
            }

            return view('manten.clonaciones',[
                'fabs' => $fabs,
                'similares' => $similares
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_stock_minimo_fecha(){
        try {
            
            // ahora queremos todos los repuestos... cambios realizados el 03 de Octubre de 2023
        $repuestos = stock_minimo::select('stock_minimo.fecha_emision','repuestos.id as id_repuesto','repuestos.codigo_interno','repuestos.estado')
        ->join('repuestos','stock_minimo.id_repuesto','repuestos.id')
        ->groupBy('repuestos.codigo_interno')
        ->orderBy('stock_minimo.fecha_emision')
        ->get();
        
        // creamos un array para guardar los repuestos con stock minimo
        $a = [];

        foreach($repuestos as $r){
        $r->fecha_emision = Carbon::parse($r->fecha_emision)->format("d-m-Y");
        // formateamos la fecha de emision
        //$r->fecha_emision = Carbon::today()->format('d-m-Y');

        $repuesto = repuesto::find($r->id_repuesto);

        $stock_total = \App\Services\StockPorLocalService::total($repuesto);

        // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
        if($repuesto->stock_minimo >= $stock_total){
        array_push($a, $r);
        } 
        }

        return view('fragm.tabla_stock_minimo_fecha',[
        'repuestos' => $a
        ]);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function dame_historial_clonaciones_oems(){
        try {
            $clonaciones = clonacion_oems::all();
            // recorrer las clonaciones y obtener los datos de los repuestos
            foreach ($clonaciones as $clonacion) {
                $repuesto = repuesto::find($clonacion->id_repuesto_destino);
              
                $repuesto_ = repuesto::find($clonacion->id_repuesto_origen);
                
                $clonacion->codigo_interno_origen = $repuesto_ ? $repuesto_->codigo_interno : 'NO EXISTE';
                $clonacion->codigo_interno_destino = $repuesto ? $repuesto->codigo_interno : 'NO EXISTE';
            }
           
            $v = view('fragm.tabla_historial_clonaciones_oems',[
                'clonaciones' => $clonaciones
            ]);
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function actualizar_stock($id){
        
        $repuesto = repuesto::select('stock_actual')->where('id',$id)->get();
        return $repuesto;
    }

    function guardarstock(Request $req, StockService $stockService){
        if (\App\Services\StockPorLocalService::habilitado()) {
            abort_unless(optional(Auth::user()->rol)->nombrerol === 'Administrador', 403);
        }

        $datos = $req->validate([
            'stock' => 'required|integer|min:0|max:65535',
            'id_repuesto' => 'required|integer|exists:repuestos,id',
            'idresponsable' => 'nullable|integer',
            'opt' => 'nullable|in:stock2,stock3',
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'motivo.required' => 'Debe indicar el motivo del ajuste.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'stock.required' => 'Debe ingresar el stock nuevo.',
            'stock.integer' => 'El stock debe ser un número entero.',
        ]);

        $ubicacionIndice = $req->opt === 'stock2' ? 2 : ($req->opt === 'stock3' ? 3 : 1);

        try {
            $autorizadoHasta = (int) $req->session()->pull('autoriza_disminuir_stock_hasta', 0);

            $resultado = $stockService->ajustar(
                (int) $datos['id_repuesto'],
                $ubicacionIndice,
                (int) $datos['stock'],
                (int) Auth::id(),
                $datos['motivo'],
                isset($datos['idresponsable']) ? (int) $datos['idresponsable'] : null,
                [
                    'tipo' => 'AJUSTE_MANUAL',
                    'disminucion_autorizada' => $autorizadoHasta >= now()->timestamp,
                    'ip' => $req->ip(),
                    'user_agent' => substr((string) $req->userAgent(), 0, 500),
                ]
            );

            $repuesto = $resultado['repuesto'];
            $campo = $ubicacionIndice === 1
                ? 'stock_actual'
                : ($ubicacionIndice === 2 ? 'stock_actual_dos' : 'stock_actual_tres');

            $vc = new ventas_controlador();
            $vc->revisar_stock_minimo($repuesto->id);

            $respuesta = ['OK', (int) $repuesto->{$campo}, $repuesto->id];
            if ($ubicacionIndice > 1) {
                $respuesta[] = 'stock'.$ubicacionIndice;
            }

            return response()->json($respuesta);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'No fue posible registrar el ajuste de stock.'], 500);
        }
    }

    function historial_stock(Request $req, $id){
        $req->validate([
            'ubicacion' => 'nullable|integer|between:1,3',
        ]);

        repuesto::findOrFail($id);

        $consulta = StockMovimiento::with('usuario:id,name')
            ->where('repuesto_id', $id)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($req->filled('ubicacion')) {
            $consulta->where('ubicacion_indice', (int) $req->ubicacion);
        }

        $movimientos = $consulta->limit(100)->get();
        $responsables = User::whereIn(
            'id',
            $movimientos->pluck('responsable_id')->filter()->unique()
        )->pluck('name', 'id');

        return response()->json($movimientos->map(function ($movimiento) use ($responsables) {
            return [
                'id' => $movimiento->id,
                'ubicacion_indice' => $movimiento->ubicacion_indice,
                'stock_anterior' => $movimiento->stock_anterior,
                'cantidad_movimiento' => $movimiento->cantidad_movimiento,
                'stock_nuevo' => $movimiento->stock_nuevo,
                'tipo' => $movimiento->tipo,
                'motivo' => $movimiento->motivo,
                'usuario' => optional($movimiento->usuario)->name ?: 'Proceso automático del sistema',
                'responsable' => $responsables->get($movimiento->responsable_id),
                'fecha' => optional($movimiento->created_at)->format('d-m-Y H:i:s'),
                'ip' => $movimiento->ip,
                'grupo_uuid' => $movimiento->grupo_uuid,
                'referencia_tipo' => $movimiento->referencia_tipo,
                'referencia_id' => $movimiento->referencia_id,
            ];
        }));
    }

    function modificar_ubicacion(Request $req){
        if (\App\Services\StockPorLocalService::habilitado()) {
            abort_unless(optional(Auth::user()->rol)->nombrerol === 'Administrador', 403);
        }

        //Si viene opt es que es la ubicación dos
        if($req->opt == "ubicacion2"){
            try {
                $id_repuesto = $req->id_repuesto;
               
                $repuesto = repuesto::find($id_repuesto);
                // No se pueden guardar 2 ubicaciones en el mismo lugar. Tienda a Bodega o Bodega a Tienda
                // if($repuesto->local_id === intval($req->ubicacion)){
                //     return 'ERROR';
                // }
                
                if($req->ubicacion === '1'){
                    $ubicacion = "Bodega - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }elseif($req->ubicacion === '3'){
                    $ubicacion = "Tienda - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }else{
                    $ubicacion = "CM - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }
                $repuesto->ubicacion_dos = $ubicacion;
                $repuesto->local_id_dos = $req->ubicacion;
                $repuesto->save();
                return ['OK',$repuesto->ubicacion_dos,$repuesto->id];
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }elseif($req->opt == "ubicacion3"){
            try {
                $id_repuesto = $req->id_repuesto;
               
                $repuesto = repuesto::find($id_repuesto);
                // No se pueden guardar 2 ubicaciones en el mismo lugar. Tienda a Bodega o Bodega a Tienda
                // if($repuesto->local_id === intval($req->ubicacion)){
                //     return 'ERROR';
                // }
                
                if($req->ubicacion === '1'){
                    $ubicacion = "Bodega - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }elseif($req->ubicacion === '3'){
                    $ubicacion = "Tienda - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }else{
                    $ubicacion = "CM - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }
                $repuesto->ubicacion_tres = $ubicacion;
                $repuesto->local_id_tres = $req->ubicacion;
                $repuesto->save();
                return ['OK',$repuesto->ubicacion_tres,$repuesto->id];
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else{
            try {
                $id_repuesto = $req->id_repuesto;
                $repuesto = repuesto::find($id_repuesto);
                // No se pueden guardar 2 ubicaciones en el mismo lugar. Tienda a Bodega o Bodega a Tienda
                // if($repuesto->local_id_dos === intval($req->ubicacion)){
                //     return 'ERROR';
                // }
              
                if($req->ubicacion === '1'){
                    $ubicacion = "Bodega - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }elseif($req->ubicacion === '3'){
                    $ubicacion = "Tienda - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }else{
                    $ubicacion = "CM - ".$req->piso." - ".$req->estanteria." - ".$req->bandeja." - ".$req->pasillo;
                }
                $repuesto->ubicacion = $ubicacion;
                $repuesto->local_id = $req->ubicacion;
                $repuesto->save();
                
                return ['OK',$ubicacion,$repuesto->id];
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    function ingresados_vendidos_vista(){
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/repuesto/ingresados_vendidos'){
                    return view('inventario.ingresados_vendidos');
                }
            }
            if(Auth::user()->rol->nombrerol === "Administrador"){
                return view('inventario.ingresados_vendidos');
            }else{
                return redirect('home');
            }
        } catch (\Exception $e) {
           return $e->getMessage();
        }
        
    }

    function ingresados_vendidos_datos(Request $req){
        $esAdministrador = Auth::user()->rol->nombrerol === 'Administrador';
        $tienePermiso = permissions_detail::where('permission_id', 4)
            ->where('usuarios_id', Auth::id())
            ->where('path_ruta', '/repuesto/ingresados_vendidos')
            ->exists();

        abort_unless($esAdministrador || $tienePermiso, 403, 'No tiene permiso para consultar esta información.');

        $datos = $req->validate([
            'codigo' => 'required|string|max:200',
            'tipo_busqueda' => 'required|in:cod_int,oem,cod_prov',
        ]);

        $codigo = trim($datos['codigo']);
        $consulta = repuesto::query()
            ->where('repuestos.activo', 1)
            ->leftJoin('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->leftJoin('paises', 'repuestos.id_pais', 'paises.id')
            ->leftJoin('users as modificador', 'repuestos.usuario_id_modifica', 'modificador.id')
            ->leftJoin('users as responsable1', 'repuestos.id_responsable', 'responsable1.id')
            ->leftJoin('users as responsable2', 'repuestos.id_responsable_dos', 'responsable2.id')
            ->leftJoin('users as responsable3', 'repuestos.id_responsable_tres', 'responsable3.id');

        if ($datos['tipo_busqueda'] === 'cod_int') {
            $consulta->where('repuestos.codigo_interno', $codigo);
        } elseif ($datos['tipo_busqueda'] === 'oem') {
            $consulta->whereIn('repuestos.id', oem::where('activo', 1)
                ->where('codigo_oem', 'like', '%'.$codigo.'%')
                ->pluck('id_repuestos'));
        } else {
            $consulta->where('repuestos.cod_repuesto_proveedor', 'like', '%'.$codigo.'%');
        }

        $coincidencias = (clone $consulta)->select(
                'repuestos.codigo_interno',
                'repuestos.descripcion',
                'repuestos.cod_repuesto_proveedor',
                'marcarepuestos.marcarepuesto'
            )
            ->distinct()
            ->orderBy('repuestos.codigo_interno')
            ->limit(25)
            ->get();

        if ($coincidencias->count() > 1 && $datos['tipo_busqueda'] !== 'cod_int') {
            return response()->json([
                'message' => 'Se encontraron varios repuestos. Seleccione el código interno correcto.',
                'coincidencias' => $coincidencias,
            ], 409);
        }

        $repuesto = $consulta->select(
            'repuestos.*',
            'marcarepuestos.marcarepuesto',
            'paises.nombre_pais',
            'modificador.name as modificado_por',
            'responsable1.name as responsable_uno',
            'responsable2.name as responsable_dos',
            'responsable3.name as responsable_tres'
        )->orderBy('repuestos.codigo_interno')->first();

        if (!$repuesto) {
            return response()->json(['message' => 'No se encontró un repuesto activo con ese criterio.'], 404);
        }

        $ingresos = compras_det::select(
                'compras_det.id',
                'compras_det.cantidad',
                'compras_det.created_at',
                'compras_cab.factura_numero',
                'proveedores.empresa_nombre_corto as proveedor'
            )
            ->join('compras_cab', 'compras_det.id_factura_cab', 'compras_cab.id')
            ->leftJoin('proveedores', 'compras_cab.factura_id_proveedor', 'proveedores.id')
            ->where('compras_det.id_repuestos', $repuesto->id)
            ->orderByDesc('compras_det.created_at')
            ->get()
            ->map(function ($ingreso) {
                return [
                    'id' => $ingreso->id,
                    'fecha' => optional($ingreso->created_at)->format('d-m-Y H:i'),
                    'cantidad' => (int) $ingreso->cantidad,
                    'documento' => $ingreso->factura_numero,
                    'proveedor' => $ingreso->proveedor,
                ];
            });

        $ventas = collect();

        // La cabecera puede quedar anulada aunque la nota de credito sea parcial.
        // El detalle de la NC indica el repuesto y las unidades devueltas reales.
        $cantidadesConNotaCredito = nota_de_credito_detalle::select(
                'notas_de_credito.docum_referencia',
                'notas_de_credito_detalle.id_facturas_detalle',
                'notas_de_credito_detalle.cantidad'
            )
            ->join('notas_de_credito', 'notas_de_credito.id', 'notas_de_credito_detalle.id_nota_de_credito')
            ->where('notas_de_credito_detalle.id_repuestos', $repuesto->id)
            ->where('notas_de_credito_detalle.activo', 1)
            ->where('notas_de_credito.activo', 1)
            ->where('notas_de_credito.estado_sii', 'ACEPTADO')
            ->get()
            ->reduce(function ($cantidades, $detalle) {
                $referencia = explode('*', (string) $detalle->docum_referencia);
                if (count($referencia) >= 2) {
                    $clave = strtolower(trim($referencia[0])).'|'.(int) $detalle->id_facturas_detalle;
                    $cantidades[$clave] = ($cantidades[$clave] ?? 0) + (int) $detalle->cantidad;
                }
                return $cantidades;
            }, []);

        $datosVenta = function ($tipoDocumento, $detalleId, $cantidadOriginal, $estadoDocumento) use ($cantidadesConNotaCredito) {
            $cantidadOriginal = (int) $cantidadOriginal;
            $cantidadDevuelta = min($cantidadOriginal, (int) ($cantidadesConNotaCredito[strtolower($tipoDocumento).'|'.(int) $detalleId] ?? 0));
            $cantidadNeta = max(0, $cantidadOriginal - $cantidadDevuelta);

            return [
                'cantidad' => $cantidadNeta,
                'cantidad_original' => $cantidadOriginal,
                'cantidad_devuelta' => $cantidadDevuelta,
                'estado' => $cantidadNeta > 0 ? 1 : 0,
                'estado_documento' => (int) $estadoDocumento,
                'estado_venta' => $cantidadDevuelta === 0 ? 'vigente' : ($cantidadNeta === 0 ? 'devuelto' : 'parcial'),
            ];
        };

        $boletas = boleta_detalle::select(
                'boletas_detalle.id as detalle_id',
                'boletas.id as documento_id',
                'boletas.num_boleta as numero',
                'boletas.fecha_emision',
                'boletas.estado',
                'boletas_detalle.cantidad',
                'boletas_detalle.precio_venta',
                'users.name as usuario',
                'locales.local_nombre as local'
            )
            ->join('boletas', 'boletas.id', 'boletas_detalle.id_boleta')
            ->leftJoin('users', 'boletas_detalle.usuarios_id', 'users.id')
            ->leftJoin('locales', 'boletas_detalle.id_local', 'locales.id')
            ->where('boletas_detalle.id_repuestos', $repuesto->id)
            ->get();

        foreach ($boletas as $venta) {
            $ventas->push(array_merge([
                'tipo' => 'Boleta',
                'tipo_dte' => 39,
                'documento_id' => $venta->documento_id,
                'numero' => $venta->numero,
                'fecha' => Carbon::parse($venta->fecha_emision)->format('d-m-Y'),
                'fecha_orden' => Carbon::parse($venta->fecha_emision)->timestamp,
                'precio' => (float) $venta->precio_venta,
                'usuario' => $venta->usuario,
                'local' => $venta->local,
            ], $datosVenta('bo', $venta->detalle_id, $venta->cantidad, $venta->estado)));
        }

        $facturas = factura_detalle::select(
                'facturas_detalle.id as detalle_id',
                'facturas.id as documento_id',
                'facturas.num_factura as numero',
                'facturas.fecha_emision',
                'facturas.estado',
                'facturas_detalle.cantidad',
                'facturas_detalle.precio_venta',
                'users.name as usuario',
                'locales.local_nombre as local'
            )
            ->join('facturas', 'facturas.id', 'facturas_detalle.id_factura')
            ->leftJoin('users', 'facturas_detalle.usuarios_id', 'users.id')
            ->leftJoin('locales', 'facturas_detalle.id_local', 'locales.id')
            ->where('facturas_detalle.id_repuestos', $repuesto->id)
            ->get();

        foreach ($facturas as $venta) {
            $ventas->push(array_merge([
                'tipo' => 'Factura',
                'tipo_dte' => 33,
                'documento_id' => $venta->documento_id,
                'numero' => $venta->numero,
                'fecha' => Carbon::parse($venta->fecha_emision)->format('d-m-Y'),
                'fecha_orden' => Carbon::parse($venta->fecha_emision)->timestamp,
                'precio' => (float) $venta->precio_venta,
                'usuario' => $venta->usuario,
                'local' => $venta->local,
            ], $datosVenta('fa', $venta->detalle_id, $venta->cantidad, $venta->estado)));
        }

        $vales = vale_mercaderia_detalle::select(
                'vale_mercaderia.id as documento_id',
                'vale_mercaderia.numero_boucher as numero',
                'vale_mercaderia.created_at as fecha_emision',
                'vale_mercaderia_detalle.cantidad',
                'users.name as usuario'
            )
            ->join('vale_mercaderia', 'vale_mercaderia.id', 'vale_mercaderia_detalle.vale_mercaderia_id')
            ->leftJoin('users', 'vale_mercaderia_detalle.usuario_id', 'users.id')
            ->where('vale_mercaderia_detalle.repuesto_id', $repuesto->id)
            ->get();

        foreach ($vales as $venta) {
            $ventas->push([
                'tipo' => 'Vale',
                'tipo_dte' => null,
                'documento_id' => $venta->documento_id,
                'numero' => $venta->numero,
                'fecha' => Carbon::parse($venta->fecha_emision)->format('d-m-Y'),
                'fecha_orden' => Carbon::parse($venta->fecha_emision)->timestamp,
                'cantidad' => (int) $venta->cantidad,
                'cantidad_original' => (int) $venta->cantidad,
                'cantidad_devuelta' => 0,
                'precio' => null,
                'usuario' => $venta->usuario,
                'local' => 'Vale de mercadería',
                'estado' => 1,
                'estado_documento' => 1,
                'estado_venta' => 'vigente',
            ]);
        }

        $ventas = $ventas->sortByDesc('fecha_orden')->values();
        $totalVendido = $ventas->sum('cantidad');
        $totalAnulado = $ventas->sum('cantidad_devuelta');

        $devoluciones = devolucion_mercaderia::select(
                'devolucion_mercaderia.id',
                'devolucion_mercaderia.cantidad',
                'devolucion_mercaderia.created_at',
                'devolucion_mercaderia.tipo_doc',
                'notas_de_credito.num_nota_credito',
                'notas_de_credito.docum_referencia',
                'users.name as usuario'
            )
            ->leftJoin('notas_de_credito', 'devolucion_mercaderia.num_nc', 'notas_de_credito.num_nota_credito')
            ->leftJoin('users', 'devolucion_mercaderia.usuario_id', 'users.id')
            ->where('devolucion_mercaderia.repuesto_id', $repuesto->id)
            ->where('devolucion_mercaderia.activo', 0)
            ->orderByDesc('devolucion_mercaderia.created_at')
            ->get()
            ->map(function ($devolucion) {
                return [
                    'id' => $devolucion->id,
                    'fecha' => optional($devolucion->created_at)->format('d-m-Y H:i'),
                    'cantidad' => (int) $devolucion->cantidad,
                    'nota_credito' => $devolucion->num_nota_credito,
                    'referencia' => $devolucion->docum_referencia,
                    'usuario' => $devolucion->usuario,
                ];
            });

        $traspasos = traspaso_mercaderia_detalle::select(
                'traspaso_mercaderia_detalle.cantidad',
                'traspaso_mercaderia_detalle.fecha_emision',
                'traspaso_mercaderia_detalle.locaciones',
                'traspaso_mercaderia_detalle.estado',
                'users.name as usuario'
            )
            ->join('traspaso_mercaderia', 'traspaso_mercaderia_detalle.id_traspaso_mercaderia', 'traspaso_mercaderia.id')
            ->leftJoin('users', 'traspaso_mercaderia.usuario_id', 'users.id')
            ->where('traspaso_mercaderia_detalle.repuesto_id', $repuesto->id)
            ->orderByDesc('traspaso_mercaderia_detalle.fecha_emision')
            ->get()
            ->map(function ($traspaso) {
                return [
                    'fecha' => Carbon::parse($traspaso->fecha_emision)->format('d-m-Y'),
                    'cantidad' => (int) $traspaso->cantidad,
                    'locaciones' => (int) $traspaso->locaciones,
                    'estado' => (int) $traspaso->estado,
                    'usuario' => $traspaso->usuario,
                ];
            });

        $movimientos = StockMovimiento::with('usuario:id,name')
            ->where('repuesto_id', $repuesto->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function ($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'fecha' => optional($movimiento->created_at)->format('d-m-Y H:i:s'),
                    'ubicacion' => (int) $movimiento->ubicacion_indice,
                    'anterior' => (int) $movimiento->stock_anterior,
                    'diferencia' => (int) $movimiento->cantidad_movimiento,
                    'nuevo' => (int) $movimiento->stock_nuevo,
                    'tipo' => $movimiento->tipo,
                    'motivo' => $movimiento->motivo,
                    'usuario' => optional($movimiento->usuario)->name ?: 'Proceso automático',
                    'grupo_uuid' => $movimiento->grupo_uuid,
                ];
            });

        $foto = repuestofoto::where('id_repuestos', $repuesto->id)->value('urlfoto');
        $stockTotal = \App\Services\StockPorLocalService::total($repuesto);

        return response()->json([
            'repuesto' => [
                'id' => $repuesto->id,
                'codigo_interno' => $repuesto->codigo_interno,
                'codigo_proveedor' => $repuesto->cod_repuesto_proveedor,
                'descripcion' => $repuesto->descripcion,
                'marca' => $repuesto->marcarepuesto,
                'pais' => $repuesto->nombre_pais,
                'precio_venta' => (float) $repuesto->precio_venta,
                'precio_anterior' => (float) $repuesto->precio_antiguo,
                'stock_minimo' => (int) $repuesto->stock_minimo,
                'stock_total' => $stockTotal,
                'modificado_por' => $repuesto->modificado_por,
                'foto' => $foto ? asset('storage/'.$foto) : null,
                'ubicaciones' => \App\Services\StockPorLocalService::habilitado()
                    ? \App\RepuestoStock::with(['local','responsable'])->where('repuesto_id', $repuesto->id)->get()->map(function ($stock) {
                        return [
                            'indice' => $stock->local_id, 'nombre' => optional($stock->local)->local_nombre,
                            'stock' => $stock->cantidad, 'ubicacion' => $stock->ubicacion_fisica,
                            'responsable' => optional($stock->responsable)->name,
                            'actualizado' => optional($stock->updated_at)->format('d-m-Y'),
                        ];
                    })->all()
                    : [
                    [
                        'indice' => 1,
                        'nombre' => 'Bodega',
                        'stock' => (int) $repuesto->stock_actual,
                        'ubicacion' => $repuesto->ubicacion,
                        'responsable' => $repuesto->responsable_uno,
                        'actualizado' => $repuesto->fecha_actualizacion_stock
                            ? Carbon::parse($repuesto->fecha_actualizacion_stock)->format('d-m-Y')
                            : null,
                    ],
                    [
                        'indice' => 2,
                        'nombre' => 'Tienda',
                        'stock' => (int) $repuesto->stock_actual_dos,
                        'ubicacion' => $repuesto->ubicacion_dos,
                        'responsable' => $repuesto->responsable_dos,
                        'actualizado' => $repuesto->fecha_actualizacion_stock_dos
                            ? Carbon::parse($repuesto->fecha_actualizacion_stock_dos)->format('d-m-Y')
                            : null,
                    ],
                    [
                        'indice' => 3,
                        'nombre' => 'Casa Matriz',
                        'stock' => (int) $repuesto->stock_actual_tres,
                        'ubicacion' => $repuesto->ubicacion_tres,
                        'responsable' => $repuesto->responsable_tres,
                        'actualizado' => $repuesto->fecha_actualizacion_stock_tres
                            ? Carbon::parse($repuesto->fecha_actualizacion_stock_tres)->format('d-m-Y')
                            : null,
                    ],
                ],
            ],
            'resumen' => [
                'ingresado' => (int) $ingresos->sum('cantidad'),
                'vendido' => (int) $totalVendido,
                'anulado' => (int) $totalAnulado,
                'devuelto' => (int) $devoluciones->sum('cantidad'),
                'stock_actual' => $stockTotal,
                'bajo_minimo' => $stockTotal <= (int) $repuesto->stock_minimo,
            ],
            'ingresos' => $ingresos,
            'ventas' => $ventas,
            'devoluciones' => $devoluciones,
            'traspasos' => $traspasos,
            'movimientos' => $movimientos,
            'stock_minimo_fechas' => stock_minimo::where('id_repuesto', $repuesto->id)
                ->orderByDesc('created_at')
                ->limit(30)
                ->get()
                ->map(function ($registro) {
                    return optional($registro->created_at)->format('d-m-Y H:i');
                }),
        ]);
    }

    public function repuestos_sin_ubicacion(){
        try {
            $repuestos = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto')
                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                    ->where('repuestos.ubicacion','SIN INFORMACION')
                    ->where('repuestos.activo',1)
                    ->take(1000)
                    ->get();
            return $repuestos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function eliminar_repuesto($id){
        try {
            $repuesto = repuesto::find($id);

            if(!$repuesto){
                return response()->json([
                    'mensaje' => 'error',
                    'detalle' => 'El repuesto no existe o ya fue eliminado.'
                ], 404);
            }

            if((int) $repuesto->activo === 0){
                return response()->json([
                    'mensaje' => 'error',
                    'detalle' => 'El repuesto ya se encuentra inactivo.'
                ], 409);
            }

            // Se conserva el registro y todas sus relaciones para mantener
            // la trazabilidad de ventas, movimientos, fotografías y similares.
            $repuesto->activo = 0;
            $repuesto->save();

            return response()->json([
                'mensaje' => 'ok',
                'detalle' => 'El repuesto fue desactivado correctamente.',
                'codigo_interno' => $repuesto->codigo_interno
            ]);
        } catch (\Exception $e) {
            \Log::error('Error desactivando repuesto '.$id.': '.$e->getMessage());

            return response()->json([
                'mensaje' => 'error',
                'detalle' => 'No fue posible desactivar el repuesto.'
            ], 500);
        }
    }
    function avanzarNumTraspaso(){
        try {
            $usuario = Auth::user();
            //Evaluamos si ya existe una solicitud por parte del usuario que inicio sesión
            $value = traspaso_mercaderia::where('usuario_id',$usuario->id)->where('activo',1)->first();
            if(($value)){
                $value->activo = 2;
                $value->save();
                
                return 'OK';
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    
    public function actualizados(){
        try {
            
            // $rs = repuesto::where(function ($query) {
            //     $query->whereBetween('fecha_actualiza_precio', ['2022-06-01', now()]);
            // })->orderBy('fecha_actualiza_precio','desc')
            // ->where('id_familia','<>',312)
            // ->get();

            // Obtener los repuestos actualizados entre junio del 2022,
            $rs = repuesto::whereDate('fecha_actualizacion_stock', '>', '2022-06-01')
                    ->whereDate('fecha_actualizacion_stock_dos', '>', '2022-06-01')
                    ->whereDate('fecha_actualizacion_stock_tres', '>', '2022-06-01')
                    ->where('id_familia','<>',312)
                    ->where('precio_venta','>',0)
                    ->orderBy('fecha_actualiza_precio','desc')
                    ->get();

            $repuestos = [];
            foreach($rs as $r){
                $stock = \App\Services\StockPorLocalService::total($r);
                if($stock > 0){
                    array_push($repuestos, $r);
                }
            }
            $v = view('inventario.actualizados', compact('repuestos'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function descargarExcel(){
        
        try {
            // Obtener los datos de los repuestos
            // Obtén los datos de los repuestos desde la base de datos usando Eloquent ORM
            $rs = repuesto::whereDate('fecha_actualizacion_stock', '>', '2022-06-01')
                        ->whereDate('fecha_actualizacion_stock_dos', '>', '2022-06-01')
                        ->whereDate('fecha_actualizacion_stock_tres', '>', '2022-06-01')
                        ->where('id_familia','<>',312)
                        ->where('precio_venta','>',0)
                        ->select('id','codigo_interno','descripcion','stock_actual','stock_actual_dos','stock_actual_tres','precio_venta')
                        ->orderBy('fecha_actualiza_precio','desc')
                        ->get();

            $repuestos = [];
            foreach($rs as $r){
                $stock = \App\Services\StockPorLocalService::total($r);
                if($stock > 0){
                    array_push($repuestos, $r);
                }
            }

            // Convertir el arreglo de repuestos a una colección de Laravel
            $coleccionRepuestos = collect($repuestos);

            // Generar el archivo Excel utilizando el paquete Laravel Excel
            return Excel::download(new Repuestos_actualizadosExport($coleccionRepuestos), 'repuestos.xlsx');
            
            }catch(\Exception $e) {
            // Puedes personalizar la respuesta de error según tus necesidades
            return response()->json([
                'message' => 'Error al descargar el archivo Excel.',
                'error' => $e->getMessage() // Puedes obtener el mensaje de error de la excepción
            ], 500);
            }
       
        
    }

    public function detalle_pedido($idrep){
        try {
            $detalle_pedido = detalle_pedido::select('detalle_pedido.*','repuestos.codigo_interno','repuestos.descripcion','proveedores.empresa_nombre_corto')
                                ->join('repuestos','detalle_pedido.id_repuesto','repuestos.id')
                                ->join('proveedores','detalle_pedido.id_proveedor','proveedores.id')
                                ->where('detalle_pedido.id_repuesto',$idrep)
                                ->first();
            if($detalle_pedido) {
                // formatear la fecha de emision con formato d-m-y con la libreria carbon
                $detalle_pedido->fecha_emision = Carbon::parse($detalle_pedido->fecha_emision)->format('d-m-Y');
            }
            
            return $detalle_pedido;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function guardar_detalle_pedido($idrepuesto,$idproveedor, $cantidad){
        try {
            // preguntar si ya existe un pedido para el repuesto y proveedor
            $detalle_pedido_proveedor = detalle_pedido::where('id_repuesto',$idrepuesto)->where('id_proveedor',$idproveedor)->first();
            $solo_pedido_repuesto = detalle_pedido::where('id_repuesto',$idrepuesto)->first();
            if($detalle_pedido_proveedor){
                $detalle_pedido_proveedor->cantidad = $cantidad;
                $detalle_pedido_proveedor->save();
               
            }elseif($solo_pedido_repuesto){
                return 'ERROR';
            }else{
                $usuario = Auth::user();
                $detalle_pedido = new detalle_pedido;
                $detalle_pedido->id_repuesto = $idrepuesto;
                $detalle_pedido->id_proveedor = $idproveedor;
                $detalle_pedido->cantidad = $cantidad;
                $detalle_pedido->usuario_id = $usuario->id;
                $detalle_pedido->fecha_emision = Carbon::today()->toDateString();
                $detalle_pedido->save();
                
            }
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
