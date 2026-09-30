<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage; //lo agregué
use Illuminate\Support\Facades\File; //lo agregué
use Carbon\Carbon; // para tratamiento de fechas
use App\repuesto;
use App\descuento;
use App\devolucion_mercaderia;
use App\oferta_pagina_web;
use App\saldo;
use App\familia;
use App\factura_detalle;
use App\boleta_detalle;
use App\traspaso_mercaderia_detalle;
use App\vale_mercaderia;
use App\vale_mercaderia_detalle;
use App\permissions_detail;
use App\compras_det;
use App\boleta;
use App\factura;
use App\correlativo;
use App\local;
use App\marcavehiculo;
use App\modelovehiculo;
use App\marcarepuesto;
use App\nota_de_credito;
use App\regulador_voltaje;
use App\proveedor;
use App\similar;
use App\repuestofoto;
use App\pais;
use App\oem;
use App\fabricante;
use App\traspaso_mercaderia;
use App\motorPartida;
use App\clonacion_oems;
use App\clonacion_fabs;
use App\clonacion_similares;
use App\stock_minimo;
use App\detalle_pedido;
use App\User;
use App\Exports\Repuestos_actualizadosExport;
use App\Exports\RepuestosStockMinimo;
use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\Mail;
use App\Mail\RepuestosStockMinimoMail;
use Illuminate\Support\Facades\Auth;

use Session;
use Debugbar;

// ...existing code...


class repuestocontrolador extends Controller
{

    // Guarda la familia de un repuesto desde el armado de kit
    public function guardar_familia_kit(Request $request)
    {
        try {
            $repuesto = repuesto::findOrFail($request->id_repuesto);
            $familia = familia::findOrFail($request->id_familia);
            $repuesto->id_familia = $familia->id;
            // Opcional: actualizar el código interno si la lógica de tu sistema lo requiere
            // $repuesto->codigo_interno = $familia->prefijo . (nuevo correlativo...);
            $repuesto->save();
            return response()->json(['estado' => 'OK', 'mensaje' => 'Familia actualizada']);
        } catch (\Exception $e) {
            return response()->json(['estado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    private $repuestos;
    private $familias;
    private $marcas;
    private $modelos;
    private $marcarepuestos;
    private $proveedores;
    private $similares;

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
        try {
            $hay=repuesto::where('id',$idrep)->where('activo',1)->first();
     
            $locales = $this->damelocales_activos();
            if(is_null($hay)){ //No hay
                $estado=['estado'=>'ERROR','mensaje'=>'El código '.$codint. ' no existe o está desactivado'];
            }else{
                $repuesto=$this->dame_un_repuesto($hay->id);
                $fotos = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
                $similares = $this->damesimilares($idrep);
                if($fotos->count() > 0){
                    $v = view('fragm.repuesto_modificar_por_familia',['repuesto' => $repuesto[0],'fotos' => $fotos,'locales' => $locales,'aplicaciones' => $similares]);
                    return $v;
                }else{
                    $v = view('fragm.repuesto_modificar_por_familia',['repuesto' => $repuesto[0],'locales' => $locales,'aplicaciones' => $similares]);
                    return $v;
                }
                
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
                    $r->stock_actual + $r->stock_actual_dos + $r->stock_actual_tres
                    < $r->stock_minimo
                ){
                    $repuestos_sin_stock_proveedor[] = $r;
                }
            }
    
    
            // ✅ verificar si sigue en lista
            $sigue = false;
    
            if(
                $repuesto->estado == 'Sin stock en proveedor' &&
                ($repuesto->stock_actual + $repuesto->stock_actual_dos + $repuesto->stock_actual_tres)
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
            $repuestos = repuesto::where('id_familia','<>',312)
                                    ->where('descripcion','like','%'.$buscado_original.'%')
                                    ->where('activo',0)
                                    ->orderBy('id','desc')
                                    ->get();
            // si no encuentra repuestos con el buscado original, buscar con el buscado sin guiones
            if(count($repuestos) == 0){
                $repuestos = repuesto::where('id_familia','<>',312)
                                    ->where('descripcion','like','%'.$buscado_sin_guiones.'%')
                                    ->where('activo',0)
                                    ->orderBy('id','desc')
                                    ->get();
            }

            $desde = 'i';
            
            $v = view('fragm.repuestos_xpress',compact('repuestos','quien_busca','desde'))->render();
            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
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
            //$this->validate($request,$reglas,$mensajes);


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
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, falta actualizar el correlativo de la familia
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
            $rep->precio_antiguo = $rep->precio_venta;
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

// ...otros métodos y lógica...

}
                
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
                $repuesto->stock_disponible = $stock_actual + $stock_actual_dos + $stock_actual_tres;
                
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

    public function clonar_oems($codigo_origen, $codigo_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$codigo_origen)->first();
        $repuesto_destino = repuesto::where('codigo_interno',$codigo_destino)->first();

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

    public function clonar_fabricantes($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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

    public function clonar_aplicaciones($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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
        }else{
            $clonacion = clonacion_fabs::where('id_repuesto_origen',$id_repuesto_origen)
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
                                    ->orderBy('marcarepuesto')
                                    ->get();
        $v=view('fragm.repuesto_fabs',compact('fabs'))->render();
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
            //$this->validate($request,$reglas,$mensajes);


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
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, falta actualizar el correlativo de la familia
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
            $rep->precio_antiguo = $rep->precio_venta;
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
                $repuesto->stock_disponible = $stock_actual + $stock_actual_dos + $stock_actual_tres;
                
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

    public function revisar_stock_critico($id_repuesto){
        $repuesto = repuesto::find($id_repuesto);
        if($repuesto->stock_actual <= $repuesto->stock_minimo){
            return 'critico';
        }else{
            return 'ok';
        }
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

    public function clonar_oems($codigo_origen, $codigo_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$codigo_origen)->first();
        $repuesto_destino = repuesto::where('codigo_interno',$codigo_destino)->first();

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

    public function clonar_fabricantes($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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

    public function clonar_aplicaciones($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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
        }else{
            $clonacion = clonacion_fabs::where('id_repuesto_origen',$id_repuesto_origen)
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
                                    ->orderBy('marcarepuesto')
                                    ->get();
        $v=view('fragm.repuesto_fabs',compact('fabs'))->render();
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
            //$this->validate($request,$reglas,$mensajes);


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
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, falta actualizar el correlativo de la familia
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
            $rep->precio_antiguo = $rep->precio_venta;
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
                $repuesto->stock_disponible = $stock_actual + $stock_actual_dos + $stock_actual_tres;
                
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

    public function clonar_oems($codigo_origen, $codigo_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$codigo_origen)->first();
        $repuesto_destino = repuesto::where('codigo_interno',$codigo_destino)->first();

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

    public function clonar_fabricantes($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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

    public function clonar_aplicaciones($cod_origen, $cod_destino){
        try {
            $repuesto_origen = repuesto::where('codigo_interno',$cod_origen)->first();
            $repuesto_destino = repuesto::where('codigo_interno',$cod_destino)->first();
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
        }else{
            $clonacion = clonacion_fabs::where('id_repuesto_origen',$id_repuesto_origen)
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
                                    ->orderBy('marcarepuesto')
                                    ->get();
        $v=view('fragm.repuesto_fabs',compact('fabs'))->render();
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
            //$this->validate($request,$reglas,$mensajes);


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
            $repuesto->usuarios_id=Auth::user()->id;
            $repuesto->activo=1;
            $repuesto->save();

            //Luego de guardar, falta actualizar el correlativo de la familia
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
            $rep->precio_antiguo = $rep->precio_venta;
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
                
               