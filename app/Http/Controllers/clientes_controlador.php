<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\abono;
use App\abono_detalle;
use App\abono_estado;
use Carbon\Carbon; // para tratamiento de fechas
use App\cliente_modelo;
use App\cliente_xpress;
use App\cliente_xpress_boleta;
use App\cliente_cuenta;
use App\correlativo;
use App\factura;
use App\boleta;
use App\familia;
use App\limite;
use App\dia;
use App\descuento;
use App\descfamtemp;
use App\cliente_referencia;
use App\repuesto;
use App\pago;
use App\proveedor;
use App\pais;
use App\marcarepuesto;
use App\clientes_giro;
use App\vale_mercaderia;
use App\vale_consignacion;
use App\consignacion;
use App\consignacion_detalle;
use App\permissions_detail;
use Session;
use Debugbar;

use Illuminate\Support\Facades\Auth;

class clientes_controlador extends Controller
{
    private function validaSesion()
    {
        //Valida sesión: Revisar repuestos/Exceptions/Handler.php, método render()

        abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
        
        
    }
    
    
    private function damedias()
    {
        $d=dia::orderBy('valor')->get();
        return $d;
    }

    public function clientes_morosos(){
        try {
            $cuentas_clientes = cliente_modelo::select('clientes.nombres','clientes.apellidos','clientes_cuenta.fecha_operacion','clientes_cuenta.id_cliente','clientes_cuenta.activo')
                                                ->join('clientes_cuenta','clientes.id','clientes_cuenta.id_cliente')
                                                ->where('clientes.tipo_cliente',0)
                                                ->groupBy('clientes_cuenta.id_cliente')
                                                ->get();
            
            $morosos = [];
            foreach($cuentas_clientes as $cc){
                //El id cliente 4 es el SII, por lo tanto no se recomienda colocarlo en la lista de morosos
                if($cc->id_cliente !== 4 && $cc->activo == 1){
                    //Saco la fecha de la ultima operación realizada por el cliente
                    $cliente = cliente_cuenta::where('id_cliente',$cc->id_cliente)
                                                
                                                ->orderBy('fecha_operacion','desc')
                                                ->take(1)
                                                ->first();
                    
                    $firstDate = $cliente->fecha_operacion;
                    $secondDate = date('Y-m-d');
                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
    
                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
    
                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    if($dias > 30){
                        array_push($morosos,$cc);
                    }
                } 
            }
            return $morosos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    private function damelimites()
    {
        $l=limite::orderBy('valor')->get();
        return $l;
    }

    private function damefamilias()
    {
        $f=familia::orderBy('nombrefamilia')->get();
        return $f;
    }

    /**
     * Display a listing of the resource.
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
        $user = Auth::user();
        $confirma = $this->confirmaringreso('/clientes');
 
        if($confirma){
            $dias=$this->damedias();
            $limites=$this->damelimites();
            $familias=$this->damefamilias();
            // $this->validaSesion();
            return view('manten.clientes',compact('familias','limites','dias'));
        }else return redirect('home');
        
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
    public function store(Request $r)
    {
        try{
            if($r->modifika==0)
            {
                //Verificar que el rut no exista
                $hay=cliente_modelo::where('rut','LIKE',$r->rut)->first();
                if(!is_null($hay))
                    return "<strong><p style='color:red'>NO GUARDÓ porque ya existe el rut ".$hay->rut." perteneciente a ".$hay->nombres." ".$hay->apellidos."</p></strong>";
                $c=new cliente_modelo;
            }else{
                $c=cliente_modelo::find($r->id_cliente);
            }
            $c->rut=$r->rut;
            $c->tipo_cliente=$r->tipo_cliente;

            if($r->tipo_cliente==0){ //cliente natural
                $nnn=strip_tags($r->nombres);
                $aaa=strip_tags($r->apellidos);
                $eee="---";
            }

            if($r->tipo_cliente==1){ //cliente empresa
                $nnn="---";
                $aaa="---";
                $eee=strip_tags($r->empresa);
            }

            $ggg=strip_tags($r->giro);
            $ddd=strip_tags($r->direccion);
            $ddco=strip_tags($r->direccion_comuna);
            $ddci=strip_tags($r->direccion_ciudad);
            $te1=strip_tags($r->telf1);
            $te2=strip_tags($r->telf2);
            $ema=strip_tags($r->email);
            $ccc=strip_tags($r->contacto);
            $tec=strip_tags($r->telfc);


            $c->nombres=strlen($nnn)>0 ? $nnn : "---";
            $c->apellidos=strlen($aaa)>0 ? $aaa : "---";
            $c->empresa=strlen($eee)>0 ? $eee : "---";

            $c->razon_social=strlen($eee)>0 ? $eee : "---";
            $c->giro=strlen($ggg)>0 ? $ggg : "---";
            $c->direccion=strlen($ddd)>0 ? $ddd : "---";
            $c->direccion_comuna=strlen($ddco)>0 ? $ddco : "---";
            $c->direccion_ciudad=strlen($ddci)>0 ? $ddci : "---";
            $c->telf1=strlen($te1)>0 ? $te1 : "---";
            $c->telf2=strlen($te2)>0 ? $te2 : "---";
            $c->email=strlen($ema)>0 ? $ema : "---";
            $c->contacto=strlen($ccc)>0 ? $ccc : "---";
            $c->telfc=strlen($tec)>0 ? $tec : "---";

            $c->credito=$r->credito;
            $c->limite=$r->limite;
            $c->dias=$r->dia;
            $c->descuento=$r->descuento;
            $c->tipo_descuento=$r->tipodescuento;
            $c->porcentaje=$r->porcentaje;
            // el campo veces_buscado tiene por defecto = 0;
            $c->activo=1;
            $c->usuarios_id=Auth::user()->id;
            $c->save();

            //Verificamos si tiene descuentos por familia
            if($r->tipodescuento==3)
            {
                $descs=descfamtemp::where('usuarios_id',Auth::user()->id)
                        ->get();

                //borramos los descuentos anteriores
                descuento::where('id_cliente',$c->id)->delete();

                foreach($descs as $des) // id_familia,porcentaje
                {
                    $df=new descuento;
                    $df->id_cliente=$c->id;
                    $df->id_familia=$des->id_familia;
                    $df->porcentaje=$des->porcentaje;
                    $df->activo=1;
                    $df->usuarios_id=Auth::user()->id;
                    $df->save();
                }
                descfamtemp::where('usuarios_id',Auth::user()->id)
                ->delete();
            }

            if($r->modifika==1)
            {
                $id_cliente=$c->id;
            }else{
                $id_cliente=$c->id;
            }

            return $id_cliente;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function cliente_xpress_guardar(Request $r){
        try {
            $cx=new cliente_xpress;
            $cx->rut_xpress=$r->rut_xpress;
            $cx->nombres_xpress=$r->nombres_xpress;
            $cx->apellidos_xpress=$r->apellidos_xpress;
            $cx->empresa_xpress=$r->empresa_xpress;
            $cx->telf1_xpress=$r->telf1_xpress;
            $cx->email_xpress=$r->email_xpress;
            $cx->documento_xpress=$r->documento_xpress;
            $cx->usuarios_id=Auth::user()->id;
            $cx->save();
            if($cx->id>0){
                return $cx->id;
            }else{
                return "Cliente Xpress No Guardado.";
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function guardar_cliente_xpress_nc(Request $req){
        try {
            $rut = $req->rut;
            $nombre = $req->nombre;
            $apellidos = $req->apellidos;
            $direccion = $req->direccion;
            $email = $req->email;
            $telefono = $req->telefono;

            // verificar si existe el cliente
            $cliente = cliente_modelo::where('rut',$rut)->first();
            if(!is_null($cliente)){
                return $cliente;
            }

            $cliente = new cliente_modelo;
            $cliente->rut = $rut;
            
            $cliente->nombres = $nombre;
            $cliente->apellidos = $apellidos;
            $cliente->empresa = 'SIN INFORMACION';
            $cliente->giro = "SIN INFORMACION";
            $cliente->direccion = $direccion;
            $cliente->direccion_comuna = 'SIN INFORMACION';
            $cliente->direccion_ciudad = 'SIN INFORMACION';
            $cliente->telf1 = $telefono;
            $cliente->telf2 = 'SIN INFORMACION';
            $cliente->email = $email;
            $cliente->contacto = 'SIN INFORMACION';
            $cliente->telfc = 'SIN INFORMACION';
            $cliente->usuarios_id = Auth::user()->id;
            $cliente->save();
            return $cliente;
            
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function cliente_xpress_abrir(){
        $user = Auth::user();
        if($user->rol->nombrerol === "Administrador"){
            $v=view('manten.clientes_xpress');
            return $v;
        }else{
            return "En construcción";
        }


    }

    public function cliente_xpress_listar_todos(){
        $clientes_xpress=cliente_xpress::orderBy('updated_at')->get();
        $v=view('fragm.clientes_xpress_todos',compact('clientes_xpress'));
        return $v;
    }

    public function cliente_xpress_actualizar_estado_envio($dato){
        list($id_cliente_xpress,$que,$valor)=explode("&",$dato);
        $cx=cliente_xpress::find($id_cliente_xpress);
        if($cx->estado="---"){
            if($que=="C" && $valor==1){
                $cx->estado="C--";
                $cx->save();
            }
        }
    }

    private function cliente_xpress_buscar(Request $r){
        //campos: rut, nombres, empresa, apellidos, celular, email
        //No busca rut porque si es ingresado (es diferente a 999999999), ya lo evalua.

        //buscar empresa
        if(strlen($r->empresa)>0){
            $empresa=cliente_modelo::where('empresa',$r->empresa)->first();
            if(!is_null($empresa)){ //hay esa empresa
                return $empresa->id; //devuelve el id del cliente.
            }else{
                return 0;
            }

        }


        //buscar nombres y apellidos

        //buscar celular

        //buscar email

    }

    private function dame_cuenta($idc){
        $cuenta=cliente_cuenta::select('clientes_cuenta.*','users.name as usuario')
                                ->join('users','clientes_cuenta.usuarios_id','users.id')
                                ->where('clientes_cuenta.id_cliente',$idc)
                                ->where('clientes_cuenta.activo',1)
                                ->orderBy('clientes_cuenta.created_at','DESC')
                                ->get();

        foreach($cuenta as $c){
            $c->fecha_operacion = Carbon::parse($c->fecha_operacion)->format("d-m-Y");
        }
        
        return $cuenta;
    }

    private function dame_cuenta_vista($idc){
        $cliente = cliente_modelo::find($idc);
        $cuenta=$this->dame_cuenta($idc);
        if($cuenta->count()==0){
            $total_pagos=0;
            $total_deuda=0;
            $diferencia=0;
        }else{
            $total_pagos=$cuenta->sum('pago');
            $total_deuda=$cuenta->sum('deuda');;
            $diferencia=$total_deuda-$total_pagos;
        }
        $facturas=factura::where('activo',1)
                        ->where('estado_sii','ACEPTADO')
                        ->where('id_cliente',$idc)
                        ->orderBy('fecha_emision','DESC')
                        ->get();
        $facturas_suma=factura::where('activo',1)
                        ->where('estado_sii','ACEPTADO')
                        ->where('id_cliente',$idc)
                        ->sum('total');

        $boletas=boleta::where('activo',1)
                        ->where('estado_sii','ACEPTADO')
                        ->where('id_cliente',$idc)
                        ->orderBy('fecha_emision','DESC')
                        ->get();
        $boletas_suma=boleta::where('activo',1)
                        ->where('estado_sii','ACEPTADO')
                        ->where('id_cliente',$idc)
                        ->sum('total');
        $v=view('fragm.clientes_cuenta',compact('cuenta','total_pagos','total_deuda','diferencia','facturas','facturas_suma','boletas','boletas_suma','cliente'))->render();
        return $v;
    }

    public function damecuenta($idc){
        return $this->dame_cuenta_vista($idc);
    }

    public function dame_documentos_cliente($idc){
        $dc=cliente_referencia::select('clientes_referencias.id','tipo_documentos.nombre_documento')
                                ->join('tipo_documentos','clientes_referencias.id_tipo_documento','tipo_documentos.id')
                                ->where('clientes_referencias.id_cliente',$idc)
                                ->where('clientes_referencias.activo',1)
                                ->get();
        return $dc;
    }

    public function agregar_documento($datos){
        list($idc,$id_docu)=explode("&",$datos);
        $hay=cliente_referencia::where('id_cliente',$idc)
                                ->where('id_tipo_documento',$id_docu)
                                ->first();
        if(!is_null($hay)){
            return -1;
        }

        $cr=new cliente_referencia;
        $cr->id_cliente=$idc;
        $cr->id_tipo_documento=$id_docu;
        $cr->activo=1;
        $cr->usuarios_id=Session::get("usuario_id");
        $cr->save();
        $dc=$this->dame_documentos_cliente($idc);
        if($dc->count()>0){
            return $dc;
        }else{
            return -2;
        }
    }

    public function borrar_documento_cliente($datos){
        list($idc,$id_docu)=explode("&",$datos);
        cliente_referencia::destroy($id_docu);
        $dc=$this->dame_documentos_cliente($idc);
        if($dc->count()>0){
            return $dc;
        }else{
            return -2;
        }
    }

    public function agregacuenta(Request $r){
        /*
            En ventas_controlador método enviar_sii linea aprox 3907 esta if($r->venta=='credito')..
            Allí hay similar código a este para agregar la deuda al cliente cuando la venta es a crédito.
        */
        $cuenta=new cliente_cuenta;
        $cuenta->id_cliente=$r->id_cliente;
        $cuenta->fecha_operacion=Carbon::today()->toDateString(); //Solo la fecha;
        if($r->tipo_operacion==1){
            $cuenta->pago=$r->monto;
            $cuenta->deuda=0;
        }else{
            $cuenta->pago=0;
            $cuenta->deuda=$r->monto;
        }
        $cuenta->referencia=$r->referencia;
        $cuenta->activo=1;
        $cuenta->usuarios_id=Session::get("usuario_id");
        $cuenta->save();
        return $this->dame_cuenta_vista($r->id_cliente);
    }

    public function borrarcuenta($data){
        list($id_cliente,$idop)=explode("*",$data);
        $cc=cliente_cuenta::find($idop);
        $cc->usuarios_id=Session::get("usuario_id");
        $cc->activo=0;
        $cc->save();
        return $this->dame_cuenta_vista($id_cliente);
    }

    public function damefacturas($idc){
        
    }

    public function borrar_deuda_cero($idc){
        $c=cliente_cuenta::where('id_cliente',$idc)
                            ->update(['activo'=>0]);
        return $this->dame_cuenta_vista($idc);
    }

    public function descfam(Request $r)
    {
        //Verificar que idfamilia exista
        $hay=descfamtemp::where('id_familia',$r->id_familia)->first();
        if(!is_null($hay)){
            $existe="SI";
         }else{
             $existe="NO";
         }

        try{
            if($existe=="NO"){
                $d=new descfamtemp;
                $d->id_familia=$r->id_familia;
                $d->porcentaje=$r->porcentaje;
                $d->usuarios_id=$r->usuarios_id;
                $d->save();
            }
        //Devolver la vista personalizada para mostrar la grilla en clientes
        //enviar campos: nombrefamilia y porcentaje en descuentosfam
        $descuentosfam=descfamtemp::select('familias.nombrefamilia','descfamtemp.porcentaje','descfamtemp.id')
            ->where('usuarios_id',Auth::user()->id)
            ->join('familias','descfamtemp.id_familia','familias.id')
            ->get();
        $v=view('fragm.descfam',compact('existe','descuentosfam'))->render();
        return $v;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }



    }


    public function borrarfam($id)
    {
        $existe="NO";
        descfamtemp::destroy($id);
        $descuentosfam=descfamtemp::select('familias.nombrefamilia','descfamtemp.porcentaje','descfamtemp.id')
            ->where('usuarios_id',Auth::user()->id)
            ->join('familias','descfamtemp.id_familia','familias.id')
            ->get();
        $v=view('fragm.descfam',compact('existe','descuentosfam'))->render();
        return $v;
    }

    public function borrarfamtodo()
    {
        try{
        $dx=descfamtemp::where('usuarios_id',Auth::user()->id)
        ->delete();
        return $dx;
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }


    public function buscar(Request $r)
    {
        //Validar sesion
        // $this->validaSesion();
        $user = Auth::user();
        if($user->rol->nombrerol === "Administrador" || $user->rol->nombrerol ==="vendedor" || $user->rol->nombrerol === "Cajer@" || $user->rol->nombrerol === "bodega-venta"){
            try{
                if($r->buscax=="nombres")
                {
                    $clientes=cliente_modelo::where('nombres','LIKE','%'.$r->buscado.'%')
                    ->orWhere('apellidos','LIKE','%'.$r->buscado.'%')
                    ->orWhere('empresa','LIKE','%'.$r->buscado.'%')
                    ->get();
                }else{
                    $clientes=cliente_modelo::where('rut','like','%'.$r->buscado.'%')
                    ->get();
                }
        
                //Entrega la vista para inventario.ventas_principal.blade
                if($r->quien=="clientes" || $r->quien=="pedidos")
                {
                    $v=view('fragm.clientes_buscados',compact('clientes'))->render();
                }

                //Entrega la vista para manten.estadocliente.blade
                if($r->quien=="estado")
                {
                    $v= view('fragm.clientes_estado',compact('clientes'))->render();
                }
        
        
                //Entrega la vista para manten.clientes.blade
                if($r->quien=="ventas")
                {
                   
                    $v=view('fragm.clientes_ventas',compact('clientes'))->render();
                }

                return $v;
        
                }catch (\Exception $error){
                    $debug=$error;
                    $v=view('errors.debug_ajax',compact('debug'))->render();
                    return $v;
                }
        
        }else{
            return redirect('home');
        }
        


    }

    public function cuenta_busqueda_clientes($idc)
    {
        // $this->validaSesion();
        $user = Auth::user();
        if($user->rol->nombrerol === "Administrador" || $user->rol->nombrerol ==="vendedor" || $user->rol->nombrerol === "Cajer@" || $user->rol->nombrerol === "bodega-venta"){
            $c=cliente_modelo::find($idc);
            $c->veces_buscado=$c->veces_buscado+1;
            $c->save();
        }
        
    }

    public function dame_giros($idc){
        $giros = clientes_giro::where('id_cliente',$idc)->get();
        return $giros;
    }

    public function cargar($idc)
    {
        //Validar sesion
        // $this->validaSesion();
        $user = Auth::user();
        $data = [];
        $giros = clientes_giro::where('id_cliente',$idc)->get();
        if($user->rol->nombrerol === "Administrador" || $user->rol->nombrerol ==="vendedor" || $user->rol->nombrerol === "Cajer@" || $user->rol->nombrerol === "bodega-venta"){
            try{
                $cliente=cliente_modelo::find($idc)->toJson();
                

                $cuenta=$this->dame_cuenta($idc);
                

                if($cuenta->count()==0){
                    $total_pagos=0;
                    $total_deuda=0;
                    $diferencia=0;
                }else{
                    $total_pagos=$cuenta->sum('pago');
                    $total_deuda=$cuenta->sum('deuda');;
                    $diferencia=$total_deuda-$total_pagos;
                }
                $data = [$cliente, $total_deuda,$giros];
                
                return $data;
            }catch (\Exception $error){
                $debug=$error;
                $v=view('errors.debug_ajax',compact('debug'))->render();
                return $v;
            }
        }
    }

    public function damedescuentos($idc)
    {
        // id, usuarios_id, id_familia, porcentaje
        try{
            //Verificar si ya hay agregados
            //$dd=descfamtemp::where('usuarios_id',Auth::user()->id)->count();

            $dd=descfamtemp::where('usuarios_id',Auth::user()->id)->delete();
            $desc=descuento::where('id_cliente',$idc)
                ->where('usuarios_id',Auth::user()->id)
                ->get();

            foreach($desc as $d)
            {
                $dt=new descfamtemp;
                $dt->usuarios_id=Auth::user()->id;
                $dt->id_familia=$d->id_familia;
                $dt->porcentaje=$d->porcentaje;
                $dt->save();
            }

            $existe="NO";
            $descuentosfam=descfamtemp::select('familias.nombrefamilia','descfamtemp.porcentaje','descfamtemp.id')
                    ->where('usuarios_id',Auth::user()->id)
                    ->join('familias','descfamtemp.id_familia','familias.id')
                    ->get();
                $v=view('fragm.descfam',compact('existe','descuentosfam'))->render();
                return $v;
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
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

    }

    /**
     * Remove the  specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $c=cliente_modelo::destroy($id);
            $d=descuento::where('id_cliente',$id)->delete();
            return $c;
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    public function estadocliente(){
        $confirma = $this->confirmaringreso('/clientes/estado');
        if($confirma){
            $dias=$this->damedias();
            $limites=$this->damelimites();
            $familias=$this->damefamilias();
            // $this->validaSesion();
            return view('manten.estadocliente',compact('familias','limites','dias'));
        }else return redirect('home');
       
        
    }

    public function abonar(Request $r){
        try {
            $repuesto=new repuesto;
            $fsd=familia::where('prefijo','FSD')->value('id');
            $repuesto->id_familia=$fsd;
            
            $msd=marcarepuesto::where('marcarepuesto','SIN DEFINIR')->value('id');
             
            $repuesto->id_marca_repuesto=$msd;
            
            $psd=proveedor::where('empresa_codigo','13.412.179-3')->value('id'); //pancho
            $repuesto->id_proveedor=$psd;
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
            $repuesto->local_id = 1;
            $repuesto->save();
                    //Luego de guardar, actualizar el correlativo de la familia
                    $familia_datos->correlativo=$newval;
                    $familia_datos->save();

        } catch (\Exception $error) {
            return $error;
        }
            

        $abono_detalle = new abono_detalle;
        $abono_detalle->id_abono = $r->id_abono;
        $abono_detalle->id_repuesto = $repuesto->id;
        $abono_detalle->id_proveedor = $r->id_proveedor;
        $abono_detalle->cantidad = $r->cantidad;
        $abono_detalle->total = $r->total;
        $abono_detalle->precio_unitario = $r->precio;
        $repuesto_buscado = repuesto::where('codigo_interno',$r->descripcion)
                            ->orWhere('descripcion','LIKE',"%{$r->descripcion}%")
                            ->get();
        if(count($repuesto_buscado) > 0){
            $abono_detalle->estado = 2;
        }else{
            $abono_detalle->estado = 1;
        }
        try {
            
            $abono_detalle->save();
            $abono = abono::select('id')->where('id',$r->id_abono)->first();
            $abonos = abono_detalle::select('abono_detalle.*','abono_estado.descripcion_estado','repuestos.descripcion','repuestos.id as idrep')
                ->where('abono_detalle.id_abono',$r->id_abono)
                ->join('abono_estado','abono_estado.id','abono_detalle.estado')
                ->join('repuestos','repuestos.id','abono_detalle.id_repuesto')
                ->get();
            
            $data = ['OK',$abono->id, $abonos];
            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }

    public function abonar_detalle(Request $r){
        try {
            
            $id_abono = intval($r->id_abono);
            //El id 8 es equivalente al correlativo de pedidos
          
            $correlativo = correlativo::find(8);
            
            $abono = abono::where('id',$id_abono)->first();
            // $abono->num_abono = intval($r->id_abono);
            $num_abono = intval($correlativo->correlativo) + 1;
            $abono->num_abono = $num_abono;
            $abono->fecha_emision = Carbon::today()->toDateString();
            $abono->por_encargo = $r->por_encargo;
            $abono->por_cobrar = $r->por_cobrar;
            $abono->precio_lista = $r->precio_lista;
            $abono->abono = intval($r->abono);
            $abono->saldo_pendiente = intval($r->saldo_pendiente);
            
            try {
                if($abono->por_cobrar === "si"){
                    $pago = new pago;
                    $pago->tipo_doc = "ab";
                    $pago->id_doc = intval($r->id_abono);
                    $pago->id_cliente = 4;
                    $pago->id_forma_pago = 1;
                    $pago->fecha_pago = Carbon::today()->toDateString();
                    $pago->referencia="abono*".$id_abono."*fecha*".$abono->fecha_emision;
                    $pago->monto = $abono->abono;
                    $pago->usuarios_id = Auth::user()->id;
                    $pago->activo = 1;
                    $pago->save();
                }else{
                    $pago = new pago;
                    $pago->tipo_doc = "ab";
                    $pago->id_doc = intval($r->id_abono);
                    $pago->id_cliente = 4;
                    $pago->id_forma_pago = 1;
                    $pago->fecha_pago = Carbon::today()->toDateString();
                    $pago->referencia="abono*".$id_abono."*fecha*".$abono->fecha_emision;
                    $pago->monto = $abono->abono;
                    $pago->usuarios_id = Auth::user()->id;
                    $pago->activo = 1;
                    $pago->save();
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            
            //Avanzamos el correlativo del abono en 1
            $correlativo->correlativo = intval($correlativo->correlativo) + 1;
            
            $correlativo->save();
            $abono->activo = 1;
            
            $abono->url_pdf = 'pedido_'.$abono->num_abono.'.pdf';
            $abono->save();
            return "OK";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_abonos(){
        $abonos = abono::select('abono.*','users.name as responsable')
                        ->join('users','users.id','abono.id_responsable')
                        ->distinct()
                        ->groupBy('abono.nombre_cliente')
                        ->orderBy('abono.id')
                        ->get();
        foreach($abonos as $a){
            $a->fecha_emision = Carbon::parse($a->fecha_emision)->format("d-m-Y");
        }
        return $abonos;
    }

    public function dame_vales_mercaderia(){
        $vales = vale_mercaderia::orderBy('numero_boucher','asc')->get();
        foreach($vales as $v){
            //Separamos el valor de updated_at para que solo me muestre la fecha en formato YYYY-MM-DD que será guardado en la primera posición
            $porciones = explode("T", $v->updated_at);
            //Creamos un nuevo atributo al objeto v que contendrá la fecha formateada correctamente.
            $v->fecha = Carbon::parse($porciones[0])->format("d-m-Y");
        }
        return $vales;
    }

    public function dame_vales_consignacion(){
        $vales = consignacion::where('activo',1)->orderBy('fecha_emision','asc')->get();
       foreach($vales as $v){
        $v->fecha_emision= Carbon::parse($v->fecha_emision)->format("d-m-Y");
       }
        return $vales;
    }

    public function agregar_giro(Request $req){
        try {
            $nuevo_giro = new clientes_giro;
            $nuevo_giro->id_cliente = intval($req->id_cliente);
            $nuevo_giro->giro = $req->giro;
            $nuevo_giro->usuarios_id = Auth::user()->id;
            $nuevo_giro->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e-getMessage();
        }
        
    }
}
