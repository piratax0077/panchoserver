<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\proveedor;
use App\repuesto;
use App\permissions_detail;
use Carbon\Carbon;
use Session;
use Debugbar;
use Illuminate\Support\Facades\Auth;

class proveedorcontrolador extends Controller
{

    private $proveedores;

    private function dameproveedores()
    {
        $p=proveedor::orderBy('empresa_nombre_corto')
                    ->get();

        return $p;
    }

    /**
     * Display a listing  of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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
        $confirma = $this->confirmaringreso('/proveedor');
        if($confirma){
            $proveedores=$this->dameproveedores();
            return view('manten.proveedor',compact('proveedores'));
        } else return redirect('home');
        
    }

    public function dame_transportistas(){ // para las ORDENES DE TRANSPORTE
        $t=proveedor::select('id','empresa_nombre_corto',)
                    ->where('activo',1)
                    ->where('es_transportista',1)
                    ->orderBy('empresa_nombre_corto')
                    ->get()
                    ->toJson();
        $respuesta=['estado'=>'OK','transportistas'=>$t];
        return $respuesta;
    }

    public function dame_proveedores(){ // para las ORDENES DE TRANSPORTE
        $p=proveedor::select('id','empresa_nombre_corto',)
                    ->where('activo',1)
                    ->where('es_transportista',0)
                    ->orderBy('empresa_nombre_corto')
                    ->get()
                    ->toJson();
        $respuesta=['estado'=>'OK','proveedores'=>$p];
        return $respuesta;
    }

    public function dame_proveedores_array(){ // para las ORDENES DE TRANSPORTE
        try {
            $proveedores=proveedor::select('id','empresa_nombre_corto',)
                    ->where('activo',1)
                    ->where('es_transportista',0)
                    ->orderBy('empresa_nombre_corto')
                    ->get();

            $v = view('fragm.dame_proveedores',compact('proveedores'));
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_stock_minimo_proveedor($idproveedor){
      try {
        $id_familia_sin_definir = 312;
        // todos los repuestos que no sean de la familia sin definir, que esten activos y que sean del proveedor seleccionado y que tengan stock minimo mayor o igual a la suma de los 3 stocks
        $rep = repuesto::select('repuestos.*','marcarepuestos.id as idmarca','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto','paises.nombre_pais')
                                               ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                                               ->where('repuestos.activo',1)
                                               ->where('repuestos.id_proveedor',$idproveedor)
                                               ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                               ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                               ->join('paises','paises.id','repuestos.id_pais')
                                               // los primeros 300 registros
                                                ->limit(300)
                                               ->get();

        $repuestos = [];
      
         foreach($rep as $r){
                    
                    $stock = intval($r->stock_actual + $r->stock_actual_dos + $r->stock_actual_tres);
                    if($r->stock_minimo >= $stock) array_push($repuestos, $r);

                }

                return view('fragm.stockminimoproveedor',[
                    'repuestos' => $repuestos
                ]);
      } catch (\Exception $e) {
        //throw $th;
        return $e->getMessage();
      }
        
    }

    public function dame_stock_minimo_estado($estado,$mes,$anio){
        try {
            $id_familia_sin_definir = 312;
            $fecha_inicio = Carbon::createFromDate($anio, $mes, 1)->format('Y-m-d');
            $fecha_fin = Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->format('Y-m-d');
            if($estado === '0'){
                $reps = repuesto::select('repuestos.*','stock_minimo.fecha_emision')
                ->where('repuestos.activo',1)
                ->where('repuestos.estado',NULL)
                ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                ->whereBetween('stock_minimo.fecha_emision',[$fecha_inicio,$fecha_fin])
                ->join('stock_minimo','stock_minimo.id_repuesto','repuestos.id')
                ->orderBy('stock_minimo.fecha_emision')
                ->groupBy('repuestos.id')
                ->get();
                $repuestos = [];
                foreach($reps as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                  
                    $repuesto = repuesto::find($r->id);
                    
                    $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_dos) + intval($repuesto->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($repuestos, $r);
                    } 
                }
            }else if($estado === 'todos'){
                $reps = repuesto::select('repuestos.*','stock_minimo.fecha_emision')
                ->where('repuestos.activo',1)
                ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                ->whereBetween('stock_minimo.fecha_emision',[$fecha_inicio,$fecha_fin])
                ->join('stock_minimo','stock_minimo.id_repuesto','repuestos.id')
                ->orderBy('stock_minimo.fecha_emision')
                ->groupBy('repuestos.id')
                ->get();
                $repuestos = [];
                foreach($reps as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                  
                    $repuesto = repuesto::find($r->id);
                    
                    $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_dos) + intval($repuesto->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($repuestos, $r);
                    } 
                }
            }else{
                $reps=repuesto::select('repuestos.*','stock_minimo.fecha_emision')
                ->where('repuestos.activo',1)
                ->where('repuestos.estado',$estado)
                ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                ->whereBetween('stock_minimo.fecha_emision',[$fecha_inicio,$fecha_fin])
                ->join('stock_minimo','stock_minimo.id_repuesto','repuestos.id')
                ->orderBy('stock_minimo.fecha_emision')
                ->groupBy('repuestos.id')
                ->get();
                $repuestos = [];
                foreach($reps as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                  
                    $repuesto = repuesto::find($r->id);
                    
                    $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_dos) + intval($repuesto->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($repuestos, $r);
                    } 
                }
            }

            
            $b = [];

            foreach($repuestos as $r){
                $r->id_repuesto = $r->id;
                $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                $stock_total = intval($r->stock_actual) + intval($r->stock_actual_dos) + intval($r->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($r->stock_minimo >= $stock_total){
                       array_push($b, $r);
                    } 
            }
            
            return view('fragm.tabla_stock_minimo_fecha',[
                'repuestos' => $b
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function dame_stock_minimo_familia($idfamilia){
        try {
          $id_familia_sin_definir = 312;
          $rep = repuesto::select('repuestos.*','marcarepuestos.id as idmarca','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto','paises.nombre_pais')
                                                 ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                                                 ->where('repuestos.activo',1)
                                                 ->where('repuestos.id_familia',$idfamilia)
                                                 ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                                 ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                                 ->join('paises','paises.id','repuestos.id_pais')
                                                 ->get();
  
          $repuestos = [];
        
           foreach($rep as $r){
                      
                      $stock = intval($r->stock_actual + $r->stock_actual_dos + $r->stock_actual_tres);
                      if($r->stock_minimo >= $stock) array_push($repuestos, $r);
  
                  }
  
                  return view('fragm.stockminimoproveedor',[
                      'repuestos' => $repuestos
                  ]);
        } catch (\Exception $e) {
          //throw $th;
          return $e->getMessage();
        }
          
      }

    public function guardar_proveedor($idrepuesto, $idproveedor){
        try {
            $repuesto = repuesto::select('repuestos.*','proveedores.empresa_nombre_corto')
                                    ->where('repuestos.id',$idrepuesto)
                                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                    ->first();
            $repuesto->id_proveedor = $idproveedor;
            $repuesto->save();
            $r = $this->damerepuesto($idrepuesto);
            return ['OK',$r];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    private function damerepuesto($idrepuesto){
        $repuesto = repuesto::select('repuestos.*','proveedores.empresa_nombre_corto','proveedores.empresa_nombre')
                            ->where('repuestos.id',$idrepuesto)
                            ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                            ->first();
        return $repuesto;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('manten.proveedor_crear');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if(isset($request->btnGuardarProveedor))
        {
            $reglas=array(
                'empresa_codigo'=>'required|max:20|unique:proveedores,empresa_codigo',
                'empresa_nombre'=>'required|max:100',
                'empresa_nombre_corto'=>'required|max:100',
                'empresa_direccion'=>'required|max:150',
                'empresa_web'=>'required|max:30',
                'empresa_telefono'=>'required|max:20',
                'empresa_correo'=>'required|max:30',
                'vendedor_nombres'=>'required|max:50',
                'vendedor_correo'=>'required|max:30',
                'vendedor_telefono'=>'required|max:20'
            );

            $mensajes=array(
                'empresa_codigo.required'=>'Falta RUT del Proveedor',
                'empresa_codigo.max'=>'Máximo 100 caracteres',
                'empresa_codigo.unique'=>'Código de Proveedor ya existe',
                'empresa_nombre.required'=>'Falta Nombre del Proveedor.',
                'empresa_nombre.max'=>'Nombre del Proveedor como máximo 100 caracteres.',
                'empresa_nombre.required'=>'Falta Nombre de la Empresa',
                'empresa_nombre_corto.max'=>'Nombre Corto del Proveedor como máximo 100 caracteres.',
                'empresa_nombre_corto.required'=>'Falta Nombre Corto del Proveedor',
                'empresa_direccion.max'=>'Dirección como máximo 150 caracteres.',
                'empresa_direccion.required'=>'Falta Dirección del Proveedor.',
                'empresa_web.max'=>'Web del Proveedor como máximo 30 caracteres.',
                'empresa_web.required'=>'Falta Web del Proveedor.',
                'empresa_telefono.required'=>'Falta teléfono del proveedor.',
                'empresa_correo.max'=>'Correo del Proveedor como máximo 30 caracteres.',
                'empresa_correo.required'=>'Falta Correo del Proveedor.',
                'vendedor_nombres.required'=>'Falta nombres del vendedor.',
                'vendedor_nombres.max'=>'Nombres del vendedor como máximo 50 caracteres.',
                'vendedor_telefono.required'=>'Falta teléfono del vendedor.',
                'vendedor_correo.required'=>'Falta Correo del vendedor.'
            );

            $this->validate($request,$reglas,$mensajes);

            $proveedor=new proveedor;
            $proveedor->empresa_codigo=$request->empresa_codigo;
            $proveedor->empresa_nombre=$request->empresa_nombre;
            $proveedor->empresa_nombre_corto=$request->empresa_nombre_corto;
            $proveedor->empresa_direccion=$request->empresa_direccion;
            $proveedor->empresa_web=$request->empresa_web;
            $proveedor->empresa_telefono=$request->empresa_telefono;
            $proveedor->empresa_correo=$request->empresa_correo;
            $proveedor->vendedor_nombres=$request->vendedor_nombres;
            $proveedor->vendedor_correo=$request->vendedor_correo;
            $proveedor->vendedor_telefono=$request->vendedor_telefono;
            $proveedor->es_transportista=($request->empresa_transportista=="on") ? 1 : 0 ;
            $proveedor->activo=1;
            $proveedor->usuarios_id=Auth::user()->id;

            $proveedor->save();
            $proveedores=$this->dameproveedores();
            return view('manten.proveedor',compact('proveedores'))->with('msgGuardado','Proveedor Guardado.');
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
        //
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
        proveedor::destroy($id);
        $proveedores=$this->dameproveedores();
        return view('manten.proveedor',compact('proveedores'))->with('msgGuardado','Proveedor Eliminado.');
    }
}
