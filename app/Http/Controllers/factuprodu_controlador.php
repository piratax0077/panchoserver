<?php







namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\compras_cab;
use App\compras_det;
use App\repuesto;
use App\repuestofoto;
use Carbon\Carbon; // para tratamiento de fechas
use App\similar;
use App\oem;
use App\fabricante;
use App\proveedor;
use App\local;
use App\familia;
use App\familia_medidas;
use App\marcavehiculo;
use App\modelovehiculo;
use App\marcarepuesto;
use App\pais;
use App\saldo;
use App\ot;
use App\ot_detalle;
use App\ot_detalle_grupo;
use App\factura;
use App\regulador_voltaje;
use App\permissions_detail;
use App\misClases\factu_produ; //Custom class



use Session;



use Debugbar;



use App\Imports\OemsImport;



use Maatwebsite\Excel\Facades\Excel;







use Illuminate\Support\Facades\Auth;



class factuprodu_controlador extends Controller



{







    private function dameOEMs($id_repuesto)
    {
        $o = oem::select('id', 'codigo_oem')
            ->where('activo', 1)
            ->where('id_repuestos', $id_repuesto)
            ->orderBy('id', 'DESC')
            ->get();
        return $o;
    }


    private function dameOEMs_x_codint($codinterno)
    {
        $repuesto = repuesto::select('id')
            ->where('codigo_interno', $codinterno)
            ->first();
        $id_repuesto = $repuesto->id;
        $o = oem::select('id', 'codigo_oem')
            ->where('activo', 1)
            ->where('id_repuestos', $id_repuesto)
            ->orderBy('id', 'DESC')
            ->get();
        return $o;
    }


    public function guardar_edicion_medida(Request $req){
        try {
            $medida = $req->medida_nombre;
            $id = $req->id_medida;
            
            // buscar la medida en la tabla familia_medidas
            $familia_medidas = familia_medidas::find($id);

            
            $familia_medidas->descripcion = $medida;
            $familia_medidas->save();

            return 1;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }


    private function dameFABs($id_repuesto)



    {



        $f = fabricante::select('repuestos_fabricantes.id', 'repuestos_fabricantes.codigo_fab', 'marcarepuestos.marcarepuesto')



            ->join('marcarepuestos', 'repuestos_fabricantes.id_marcarepuestos', 'marcarepuestos.id')



            ->where('repuestos_fabricantes.activo', 1)



            ->where('repuestos_fabricantes.id_repuestos', $id_repuesto)



            ->orderBy('repuestos_fabricantes.id', 'DESC')



            ->get();



        return $f;
    }







    private function damesimilares($id_repuesto)



    {



        $s = similar::select('marcavehiculos.marcanombre', 'modelovehiculos.modelonombre', 'similares.anios_vehiculo', 'similares.id')



            ->where('similares.id_repuestos', $id_repuesto)



            ->where('similares.activo', 1)



            ->join('marcavehiculos', 'similares.id_marca_vehiculo', 'marcavehiculos.idmarcavehiculo')



            ->join('modelovehiculos', 'similares.id_modelo_vehiculo', 'modelovehiculos.id')



            ->orderBy('similares.id', 'DESC')



            ->get();



        return $s;
    }







    private function damefotosrepuesto($id_repuesto)
    {
        $rf = repuestofoto::select('id', 'urlfoto')
            ->where('id_repuestos', $id_repuesto)
            ->where('activo', 1)
            ->orderBy('id', 'DESC')
            ->get();
        return $rf;
    }







    private function damepaises()



    {



        $p = pais::orderBy('nombre_pais')->get();



        return $p;
    }











    private function damemarcarepuestos()



    {



        $m = marcarepuesto::orderBy('marcarepuesto')->get();



        return $m;
    }







    private function dameultimoitem($id_factura)



    {







        $ultimo = compras_det::where('id_factura_cab', $id_factura)->latest()->value('item');



        if (is_null($ultimo)) {



            $ultimo = 0;
        }



        return $ultimo;
    }







    private function dame_items($id_factura)



    {



        $items = compras_det::select(
            'compras_det.id',



            'compras_det.item',



            'repuestos.codigo_interno',



            'repuestos.descripcion',



            'compras_det.cantidad',



            'compras_det.pu',



            'compras_det.subtotal',



            'compras_det.costos',



            'compras_det.precio_sugerido'
        )



            ->where('compras_det.id_factura_cab', '=', $id_factura)



            ->join('repuestos', 'compras_det.id_repuestos', 'repuestos.id')



            ->get();







        return $items;
    }







    private function damemarcas()



    {



        $m = marcavehiculo::where('activo', '=', 1)->select('idmarcavehiculo', 'marcanombre', 'urlfoto')->orderBy('marcanombre')->get();



        return $m;
    }







    private function dameproveedores()



    {



        $p = proveedor::where('activo', 1)



            ->where('es_transportista', 0)



            ->orderBy('empresa_nombre_corto')



            ->get();







        return $p;
    }







    private function damelocales()
    {
        $l = local::where('activo',1)->get();
        return $l;
    }







    public function verifica_factura($input)
    {
        $a = explode("*", $input);
        $idproveedor = $a[0];
        $numfac = $a[1];
        $f = compras_cab::where('factura_id_proveedor', $idproveedor)
            ->whereRaw('cast(factura_numero as unsigned)=?', [intval(trim($numfac))])
            ->first();

        //buscar el ID en detalle y contabilizar num_item_documento
        if (!is_null($f)) {
            $cr = $this->cuenta_repuestos($f->id, $numfac, $idproveedor);
            //Mandamos el ID de compras_cab para actualizar más items
            $resp = "EXISTE*" . $f->id . "*" . $f->factura_numero . "*" . $f->factura_fecha . "*" . $cr['total_repuestos_factura'] . "*" . $cr['total_repuestos_ot'];
        } else {

            $cr = $this->cuenta_repuestos(0, $numfac, $idproveedor);
            $resp = "NO EXISTE*0*0*" . $cr['total_repuestos_factura'] . "*" . $cr['total_repuestos_ot'];
        }
        return $resp;
    }



    public function dame_factura($num)
    {
        $factura = factura::Where('num_factura', $num)->get();
        return $factura;
    }



    private function cuenta_repuestos($id_fact, $numfac, $idproveedor)
    {
        //cantidad repuestos digitados
        if ($id_fact > 0) {
            $total_repuestos_factura = compras_det::where('activo', 1)
                ->where('id_factura_cab', $id_fact)
                ->sum('cantidad');
        } else {
            $total_repuestos_factura = 0;
        }

        //verificar si existe OT.

        $total_repuestos_ot = 0;

        //buscar el num factura en ot_detalle o en ot_detalle_grupo, si encuentra guardar el ID

        $otd = ot_detalle::where('numero_doc_detalle', $numfac)
            ->where('id_proveedor', $idproveedor)
            ->first();

        if (!is_null($otd)) {
            $total_repuestos_ot = $otd->num_item_documento;
        } else {
            $otdg = ot_detalle_grupo::where('numero_doc_detalle_grupo', $numfac)
                ->where('id_proveedor_grupo', $idproveedor)
                ->first();

            if (!is_null($otdg)) {
                $total_repuestos_ot = $otdg->num_item_documento_grupo;
            }
        }

        return ['total_repuestos_factura' => $total_repuestos_factura, 'total_repuestos_ot' => $total_repuestos_ot];
    }







    public function dameporcentaje($id_familia)

    {

        $f = familia::find($id_familia);



        $porcentaje = $f->porcentaje;



        return $porcentaje;
    }







    public function crear()
    {
        $proveedores = $this->dameproveedores();
        $locales = $this->damelocales();
        //$familias=$this->damefamilias();
        $marcas = $this->damemarcas();
        //$modelos=$this->damemodelos();
        //$marcarepuestos=$this->damemarcarepuestos();
        //$paises=$this->damepaises();

        $id_repuesto = 0; //Esto lo pongo temporalmente porque las fotos necesitan un ID.
        //$fotos=$this->damefotosrepuesto($id_repuesto);
        //$similares=$this->damesimilares($id_repuesto);
        //return view('inventario.factu_produ',compact('proveedores','locales','marcas','fotos','similares'));

        $permisos = permissions_detail::all();
        foreach ($permisos as $p) {
            if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/factuprodu/crear'){
                return view('inventario.factu_produ', compact('proveedores', 'locales', 'marcas'));
            }
        }

        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('inventario.factu_produ', compact('proveedores', 'locales', 'marcas'));
        }else return redirect('home');
        
    }











    public function buscarepuesto($cod, $idprov)
    {
        try {
            $campo = substr($cod, 0, 1);
            $buscado = substr($cod, 1);
            $data = [$campo, $buscado,$cod, $idprov];
      
            if ($campo == "0") //Busca por cod oem
            {
                $repuestos = repuesto::where('repuestos.codigo_OEM_repuesto', 'LIKE', '%' . $buscado . '%')
                    ->where('repuestos.id_proveedor', $idprov)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->leftjoin('users','repuestos.usuario_id_modifica','users.id')
                    ->select('proveedores.empresa_nombre', 'paises.nombre_pais', 'repuestos.*','users.name')
                    ->get();
            } else { //busca por cod proveedor
                $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%' . $buscado . '%')
                    ->where('repuestos.id_proveedor', $idprov)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->leftjoin('users','repuestos.usuario_id_modifica','users.id')
                    ->select('proveedores.empresa_nombre', 'paises.nombre_pais', 'repuestos.*','users.name')
                    ->get();
            }

            
            foreach($repuestos as $repuesto){
                //Traemos el detalle de la ultima compra del repuesto
                $detalle = compras_det::where('id_repuestos',$repuesto->id)->latest('id')->first();
                $repuesto->precio_real = number_format($repuesto->precio_venta,0,',','.');
                if($repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_dos && $repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_tres){
                    $fecha_ultima = $repuesto->fecha_actualizacion_stock;
                    $repuesto->fecha_ultima = $fecha_ultima;
                }elseif($repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock && $repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock_tres){
                        $fecha_ultima = $repuesto->fecha_actualizacion_stock_dos;
                        $repuesto->fecha_ultima = $fecha_ultima;
                }else{
                        $fecha_ultima = $repuesto->fecha_actualizacion_stock_tres;
                        $repuesto->fecha_ultima = $fecha_ultima;
                }
                if(!empty($detalle)){
                    $repuesto->precio_compra = $detalle->pu;
                }
                
            }

            
            $v = view('fragm.factuprodu_buscado', compact('repuestos'))->render();
            return $v;
            /*
            $debug=$repuestos;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
*/
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }


    public function guardarcabecera(Request $r)



    {



        try {



            $cabecera = new compras_cab;



            $cabecera->factura_id_proveedor = $r->idproveedor;



            $cabecera->factura_numero = $r->numerofactura;



            $cabecera->factura_fecha = $r->fechafactura;



            $cabecera->factura_es_credito = ($r->escredito == "true") ? 1 : 0; //checkbox







            if ($cabecera->factura_es_credito == 1) {



                $cabecera->factura_fecha_venc = $r->vencefactura;
            } else {



                $cabecera->factura_fecha_venc = null;
            }







            $cabecera->factura_subtotal = 0.0;



            $cabecera->factura_iva = 0.0;



            $cabecera->factura_total = 0.0;



            $cabecera->factura_observaciones = "";



            $cabecera->activo = 1;



            $cabecera->usuarios_id = Auth::user()->id;







            $g = $cabecera->save();
        } catch (\Exception $error) {







            return ($error->getMessage());



            //$debug=$error->getMessage();



            //$v=view('errors.debug_ajax',compact('debug'))->render();



            //return $v;



        }







        return $cabecera->id;
    }











    public function verificacodprov($crp, $idprov, $idfac)
    {
        $idrep = repuesto::where('cod_repuesto_proveedor', trim(strtoupper($crp)))
            ->where('id_proveedor', $idprov)
            ->value('id');
        // return $idrep;
        if (!is_null($idrep)) {
            //Verificar en el detalle de la compra
            $d = compras_det::where('id_repuestos', $idrep)
                ->where('id_factura_cab', $idfac)
                ->value('id');
                // return $idrep;
            if (!is_null($d)) {
                return "EXISTE";
            } else {
                return "NO EXISTE";
            }
        } else {
            return "NO EXISTE";
        }
    }







    public function guardaritem(Request $request)
    {
      
        //Guardar repuesto en el catálogo.
        $resp = "xuxa*no guardó";
        if ($request->nuevo == "SI") {
            $id_rep = $this->guardar_repuesto($request);
            $r = repuesto::find($id_rep);
            $codigo_interno = $r->codigo_interno;
            $resp = "1*" . $codigo_interno . "*" . $id_rep;
        } else { // Ya existe
            $id_rep = $request->idrep;
            $r = repuesto::find($id_rep);
            $r->id_responsable = null;
            $r->id_responsable_dos = null;
            $r->id_responsable_tres = null;
            if ($r->activo == 0) $r->activo = 1;
            if ($request->preciosug > $r->precio_venta) // Consultado con pancho el 22jun2020: guardar siempre el precio mayor
            {
                $r->precio_venta = $request->preciosug;
                $r->pu_neto = round($request->preciosug / (1 + 0.19), 2);
                $r->precio_compra = $request->pu;
                $r->fecha_actualiza_precio = Carbon::today()->toDateString();
                $resp = "2*" . $r->codigo_interno . "*" . $r->descripcion . "*" . $r->precio_compra . "*" . $r->precio_venta . "*" . $request->idrep;
            } else {
                $resp = "3*" . $r->codigo_interno;
            }
            

            $r->fecha_reposicion = Carbon::today()->toDateString();
            $r->fecha_actualizacion_stock = Carbon::today()->toDateString();
            $r->usuarios_id = Auth::user()->id;
            //Si la ubicacion es 1 significa que es bodega y se guarda en la primera ubicacion
            if($request->idLocal == 1){
                $r->local_id = $request->idLocal;
                $r->ubicacion = 'Bodega';
                $r->stock_actual += $request->cantidad;
            }
            //Si la ubicacion es 3, significa que es tienda y se guarda en la segunda ubicacion

            if($request->idLocal == 3){
                $r->local_id_dos = $request->idLocal;
                $r->ubicacion_dos = 'Tienda - p1 - 0 - b0 - p0';          
                $r->stock_actual_dos += $request->cantidad;
            }

            //Si la ubicacion es 4, significa que es casa matriz y se guarda en la tercera ubicacion

            if($request->idLocal == 4){
                $r->local_id_tres = $request->idLocal;
                $r->ubicacion_tres = 'CM - p1 - 0 - b0 - p0';
                $r->stock_actual_tres += $request->cantidad;
            }
            $r->stock_minimo = $request->stockmin;
            $r->stock_maximo = $request->stockmax;
            $r->save();
        }

        //Guardar Factura y actualiza saldo
        $this->guardar_factura($id_rep, $request); //kiki

        return $resp;
    }
    
    public function guardar_repuesto($item)
    {
        try {
            $repuesto = new repuesto;
            $repuesto->id_familia = $item->idFamilia;
            $repuesto->id_marca_repuesto = $item->idMarcaRepuesto;
            $repuesto->id_proveedor = $item->idProveedor;
            $repuesto->id_pais = $item->idPais;

            $repuesto->descripcion = strtoupper($item->descripcion);
            $repuesto->observaciones = strlen(trim($item->observaciones)) > 0 ? trim($item->observaciones) : "";
            $repuesto->medidas = $item->medidas;
            $repuesto->cod_repuesto_proveedor = $item->cod_repuesto_proveedor;
            $repuesto->version_vehiculo = "---"; // $item->cod2_repuesto_proveedor;
            $repuesto->codigo_OEM_repuesto = $item->cod_oem;
            $repuesto->precio_compra = $item->pu;
            $repuesto->precio_venta = $item->preciosug; //intval(round($repuesto->pu_neto*1.19,0));
            $repuesto->pu_neto = intval(round($item->preciosug / (1 + 0.19), 0));
            $repuesto->stock_minimo = $item->stockmin;
            $repuesto->stock_maximo = $item->stockmax;
            //Si viene del bodega se guarda en la primera ubicación.
            if($item->idLocal == 1){
                $repuesto->local_id = $item->idLocal;
                $repuesto->stock_actual = $item->cantidad;
                $repuesto->ubicacion = 'Bodega';
            }elseif($item->idLocal == 3){ //Si viene de tienda, se guarda en la segunda ubicación.
                $repuesto->local_id_dos = $item->idLocal;
                $repuesto->stock_actual_dos = $item->cantidad;
                $repuesto->ubicacion_dos = 'Tienda - p1 - 0 - b0 - p0';
            }else{ //Sino, vendrá de Casa matríz y se guardará en la tercera ubicación.
                $repuesto->local_id_tres = $item->idLocal;
                $repuesto->stock_actual_tres = $item->cantidad;
                $repuesto->ubicacion_tres = 'CM - p1 - 0 - b0 - p0';
            }
            
            $repuesto->codigo_barras = $item->codbar;
            
            $familia_datos = familia::find($item->idFamilia);
            $newval = $familia_datos->correlativo + 1;
            $codinterno = $familia_datos->prefijo . $newval;
            $repuesto->codigo_interno = $codinterno;

            $repuesto->usuarios_id = Auth::user()->id;
            
            $repuesto->activo = 1;
            $repuesto->fecha_reposicion = Carbon::today()->toDateString();
            $repuesto->fecha_actualizacion_stock = Carbon::today()->toDateString();
            $repuesto->save();
            //Luego de guardar, actualizar el correlativo de la familia
            $familia_datos->correlativo = $newval;
            $familia_datos->save();

            if ($item->idrep_clonado > 0) {
                $this->clonar_repuesto($item->idrep_clonado, $repuesto->id);
            }

            return $repuesto->id;
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();

            return $v;
        }
    }

    private function clonar_repuesto($idrep_clonado, $idrep)
    {
        //los datos de idrep_clonado pasarlo a idrep

        //fotos
        $fotos_clon = repuestofoto::where('id_repuestos', $idrep_clonado)->get();
        if ($fotos_clon->count() > 0) {
            foreach ($fotos_clon as $fotos) {
                $fotos_new = $fotos->replicate();
                $fotos_new->id_repuestos = $idrep;
                $fotos_new->save();
            }
        }

        //aplicaciones similar
        $aplicaciones_clon = similar::where('id_repuestos', $idrep_clonado)->get();
        if ($aplicaciones_clon->count() > 0) {
            foreach ($aplicaciones_clon as $aplicacion) {
                $aplicacion_new = $aplicacion->replicate();
                $aplicacion_new->id_repuestos = $idrep;
                $aplicacion_new->save();
            }
        }


        //oems oem
        $oems_clon = oem::where('id_repuestos', $idrep_clonado)->get();
        if ($oems_clon->count() > 0) {
            foreach ($oems_clon as $oem) {
                $oems_new = $oem->replicate();
                $oems_new->id_repuestos = $idrep;
                $oems_new->save();
            }
        }


        //fabricantes fabricante
        $fabricantes_clon = fabricante::where('id_repuestos', $idrep_clonado)->get();
        if ($fabricantes_clon->count() > 0) {
            foreach ($fabricantes_clon as $fabricante) {
                $fabricantes_new = $fabricante->replicate();
                $fabricantes_new->id_repuestos = $idrep;
                $fabricantes_new->save();
            }
        }
    }

    public function guardar_factura($id_repu, $datos)
    {
        $idrep = $id_repu;

        //Guardar item de factura
        $detalle = new compras_det;
        $detalle->id_factura_cab = $datos->idFactura;
        $item = $this->dameultimoitem($datos->idFactura);
        $detalle->item = $item + 1;

        $detalle->id_repuestos = $idrep;
        $detalle->cantidad = $datos->cantidad; //keke
        $detalle->pu = $datos->pu;
        $detalle->subtotal = $datos->subtotalitem;
        $detalle->costos = $datos->flete;
        $detalle->costos_descripcion = "Flete";
        $detalle->precio_sugerido = $datos->preciosug;
        $detalle->id_local = $datos->idLocal;
        $detalle->activo = 1;
        $detalle->usuarios_id = Auth::user()->id;

        try {
            $detalle->save();
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }

        //Calculamos para actualizar cabecera de factura con subtotal, iva y total

        try {
            $subt = compras_det::where('id_factura_cab', $datos->idFactura)->sum('subtotal');
            $iva = $subt * 0.19; //parametro iva
            $tot = $subt + $iva;

            $cc = compras_cab::find($datos->idFactura);
            $cc->factura_subtotal = $subt;
            $cc->factura_iva = $iva;
            $cc->factura_total = $tot;
            $cc->save();

            // Actualizar tabla saldos considerando el local
            // id_repuestos,id_local,saldo,activo,usuarios_id
            $r = new repuestocontrolador();
            $r->actualiza_saldos('I', $detalle->id_repuestos, $detalle->id_local, $detalle->cantidad);
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();

            return $v;
        }
    }

    public function guardarfoto(Request $r)
    {
        try {
            $archivo = $r->file('archivo');
            $nombre_archivo = $archivo->getClientOriginalName();
            //aqui comprobar si existe ya la misma imagen
            $buscado = 'fotozzz/' . $nombre_archivo;
            $fotito = repuestofoto::where('urlfoto', $buscado)
                ->where('id_repuestos', $r->idrep)
                ->first();

            if (is_null($fotito)) {
                $repuestofoto = new repuestofoto;
                $repuestofoto->id_repuestos = $r->idrep;
                $repuestofoto->urlfoto = $archivo->storeAs('fotozzz', $nombre_archivo, 'public');
                $repuestofoto->usuarios_id = Auth::user()->id;
                $repuestofoto->activo = 1;
                $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/fotozzz/".$nombre_archivo;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/fotozzz/".$nombre_archivo;;
                copy($ruta_origen, $ruta_destino);
                $repuestofoto->save();
                
                $fotos = $this->damefotosrepuesto($r->idrep);
                $v = view('fragm.factuprodu_fotos', compact('fotos'))->render();
                return $v;
            } else {
                return "EXISTE";
            }
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }

    public function borrar_foto($idfoto, $idrep)
    {
        repuestofoto::destroy($idfoto);
        $fotos = $this->damefotosrepuesto($idrep);
        $v = view('fragm.factuprodu_fotos', compact('fotos'))->render();
        return $v;
    }

    public function borrar_similar($idsimilar, $idrep)
    {

        similar::destroy($idsimilar);
        $similares = $this->damesimilares($idrep);
        $v = view('fragm.factuprodu_similares', compact('similares'))->render();
        return $v;
    }

    public function borrar_oem($idoem, $idrep)
    {
        oem::destroy($idoem);
        $oems = $this->dameOEMs($idrep);
        $v = view('fragm.factuprodu_oems', compact('oems'))->render();
        return $v;
    }

    public function borrar_oem_x_codint($idoem,$codigo_interno,$tipo){
        try {
            oem::destroy($idoem);
            $oems = $this->dameOEMs_x_codint($codigo_interno);
            $v = view('fragm.factuprodu_oems', compact('oems','tipo'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function borrarrv($idrv,$idrep){
        regulador_voltaje::destroy($idrv);
        $datos = $this->dameRVs($idrep);
        $v = view('fragm.regulador_voltaje', compact('datos'))->render();
        return $v;
    }


    public function dameRVs($idrep){
        $rvs = regulador_voltaje::where('id_repuesto',$idrep)->get();
        return $rvs;
    }


    public function borrar_fab($idfab, $idrep)
    {
        fabricante::destroy($idfab);
        $fabs = $this->dameFABs($idrep);
        $v = view('fragm.factuprodu_fabs', compact('fabs'))->render();
        return $v;
    }







    public function guardarsimilar(Request $r)
    {

        try {
            //Verificar que no exista
            $existe = similar::where('id_repuestos', $r->idrep)
                ->where('id_marca_vehiculo', $r->idMarca)
                ->where('id_modelo_vehiculo', $r->idModelo)
                ->first();
            if (!is_null($existe)) {
                return "EXISTE";
            }

            $similar = new similar;
            $similar->codigo_OEM_repuesto = "---";
            $similar->anios_vehiculo = $r->anios;
            $similar->activo = 1;
            $similar->id_repuestos = $r->idrep;
            $similar->id_marca_vehiculo = $r->idMarca;
            $similar->id_modelo_vehiculo = $r->idModelo;
            $similar->usuarios_id = Auth::user()->id;
            $similar->save();
            $similares = $this->damesimilares($r->idrep);

            $v = view('fragm.factuprodu_similares', compact('similares'))->render();
            return $v;
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }

    public function guardaroem(Request $r)
    {

        try {
            $existe = oem::where('id_repuestos', $r->idrep)
                ->where('codigo_oem', trim($r->cod_oem))
                ->first();
            if (!is_null($existe)) return "EXISTE";

            $oem = new oem;
            $oem->codigo_oem = trim($r->cod_oem);
            $oem->id_repuestos = $r->idrep;
            $oem->usuarios_id = Auth::user()->id;
            $oem->activo = 1;
            $oem->save();
            $oems = $this->dameOEMs($r->idrep);

            $v = view('fragm.factuprodu_oems', compact('oems'))->render();



            return $v;
        } catch (\Exception $error) {



            $debug = $error;



            $v = view('errors.debug_ajax', compact('debug'))->render();



            return $v;
        }
    }







    public function guardarfab(Request $r)
    {

        try {
            $existe = fabricante::where('id_repuestos', $r->idrep)
                ->where('codigo_fab', trim($r->cod_fab))
                ->where('id_marcarepuestos', $r->idfab)
                ->first();


            if (!is_null($existe)) return "EXISTE";

            $fab = new fabricante;
            $fab->codigo_fab = trim($r->cod_fab);
            $fab->id_repuestos = $r->idrep;
            $fab->id_marcarepuestos = $r->idfab;
            $fab->usuarios_id = Auth::user()->id;
            $fab->activo = 1;
            $fab->save();
            $fabs = $this->dameFABs($r->idrep);

            $v = view('fragm.factuprodu_fabs', compact('fabs'))->render();
            return $v;
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }


    public function dameitemsfactura($id_factura)
    {

        $items = $this->dame_items($id_factura);
        $st = 0.0;

        foreach ($items as $item) {
            $st = $st + $item->subtotal;
        }

        $iva = $st * 0.19;
        $total = $st + $iva;
        $view = view('fragm.compras_items', compact('items', 'st', 'iva', 'total'))->render();
        return $view;
    }

    public function eliminaritem($id) //Es el id de la tabla compras_det, no es de la factura.
    {
        compras_det::destroy($id);
        return "<strong>Item Eliminado...</strong>";
    }

    public function dame_compras($idrep)
    {
        $compras = compras_det::select(
            'compras_cab.factura_fecha as fecha',
            'compras_cab.factura_numero as numero',
            'compras_det.pu as precio_compra',
            'compras_det.precio_sugerido as precio_venta',
            'compras_det.costos as costos',
            'compras_det.cantidad as cantidad',
            'users.name'
        )
            ->join('compras_cab', 'compras_det.id_factura_cab', 'compras_cab.id')
            ->leftjoin('users','compras_det.usuarios_id','users.id')
            ->where('compras_det.id_repuestos', $idrep)
            ->orderBy('compras_cab.factura_numero', 'DESC')
            ->get();

        $view = view('fragm.ventas_repuestos_comprados', compact('compras'))->render();
        if(Auth::user()->rol->nombrerol == "Administrador") return $view;
        
    }


    public function damemedidas($id)
    {
        //Familia 206 es de regulador de voltaje
        if($id == 206 || $id == 282){ 
            return 'rv';
        }

        // Familia 166 es de Bendix
        // Familia 67 es de Solenoide
        if($id == 166 || $id == 67){
            return 'bendix';
        }

        if($id == 34 || $id == 338){
            return 'aceite';
        }
        
        if($id == 423 ||  $id == 470){
            //Buscamos las familia de disco y buscamos la familia de prensa, cuyos id son 3 y 4 respectivamente.
            $medidas_disco = familia_medidas::where('id_familia', 3)->get();
            $medidas_prensa = familia_medidas::where('id_familia', 4)->get();

            return ['pd',$medidas_disco, $medidas_prensa];
        }else{
            $medidas = familia_medidas::where('id_familia', $id)->orderBy('descripcion','asc')->get();
            return $medidas;
        }

        
    }

    public function eliminar_medida_familia(Request $req){
        try {
            $idmedida = $req->idmedida;
            $idfamilia = $req->idfamilia;
            $medida = familia_medidas::find($idmedida);
            $medida->delete();
            
            return 'OK';
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }



    public function import(Request $request)
    {
        $import = new OemsImport;
        $proveedores = $this->dameproveedores();
        $locales = $this->damelocales();
        //$familias=$this->damefamilias();
        $marcas = $this->damemarcas();
        try {

            Excel::import($import, request()->file('excel'));

            $numRows = $import->getRowCount();

            return view('inventario.factu_produ', compact('proveedores', 'locales', 'marcas', 'numRows'));
        } catch (\Exception $e) {

            return $e;
        }
    }
}
