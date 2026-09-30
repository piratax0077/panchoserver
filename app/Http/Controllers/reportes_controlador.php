<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Debugbar;
use Session;
use App\boleta;
use App\repuestofoto;
use App\abono;
use App\factura;
use App\nota_de_credito;
use App\nota_de_debito;
use App\guia_de_despacho;
use App\formapago;
use App\importaciones_excel;
use App\registro_login;
use App\repuesto;
use App\pago;
use App\User;
use App\local;
use App\permissions_detail;
use App\boleta_detalle;
use App\factura_detalle;
use App\compras_cab;
use App\compras_det;
use App\proveedor;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ventasDiariasExports;
use App\Exports\ventasDiariasExportsGetnet;
use App\Exports\RepuestosExport;
use App\Exports\UsersExport;
use App\carrito_virtual;
use App\carrito_virtual_detalle;
use App\retiro_tienda;
use App\despacho_domicilio;
use Carbon\Carbon; // para tratamiento de fechas
use App\stock_minimo;

// DB
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;


class reportes_controlador extends Controller
{

    public function ventasdiarias()
    {
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 8 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/reportes/ventasdiarias'){
                        return view('reportes.ventas_diarias');
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return view('reportes.ventas_diarias');
        } else {
            return redirect('home');
        }
    }

    public function confirmaringreso($path){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 8 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == $path){
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

    public function documentosgenerados()
    {
        $confirma = $this->confirmaringreso('/reportes/documentosgenerados');
        if($confirma){
            return view('reportes.documentos_generados');
        }else{
            return redirect('home');
        }
     
    }

    public function documentosgenera2()
    {
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 8 && $permiso_detalle->usuarios_id == Auth::user()->id){
                        return view('reportes.documentos_genera2');
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol !== "vendedor" && $user->rol->nombrerol !== "bodega-venta" && $user->rol->nombrerol !== "Cajer@") {
            return view('reportes.documentos_genera2');
        } else {
            return redirect('home');
        }
    }

    public function buscar_documentos($info)
    {
    
        list($opcion, $buscado) = explode("&", $info);
        $buscado = str_replace("_slash_", "/", $buscado);
        $buscado = str_replace("_ampersand_", "&", $buscado);
    
        //RESPUESTA
        $respuesta_registro = [];
        $respuesta = [];
        $orden_nivel1 = 100;
        $orden_nivel2 = 10;
        $orden_nivel3 = 1;

        try {

      
            switch ($opcion) {
                case "documento":
                    //boleta
                    $boletas = boleta::where('num_boleta', $buscado)->orderBy('fecha_emision', 'DESC')->get();
                    if ($boletas->count() > 0) {
                        foreach ($boletas as $bol) {
                            if ($bol->docum_referencia == "---" || $bol->docum_referencia == "") {
                                $referencia = $bol->docum_referencia;
                            } else {
                                list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $bol->docum_referencia);
                                if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                            }
                            $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Boleta N° ' . $bol->num_boleta, 'fecha' => $bol->fecha_emision, 'referencia' => $referencia, 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
                            array_push($respuesta, $respuesta_registro);

                            //buscamos pagos
                            $pagos = pago::select('pagos.*', 'formapago.formapago')
                                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                ->where('pagos.tipo_doc', 'bo')
                                ->where('pagos.id_doc', $bol->id)
                                ->get();
                            if ($pagos->count() > 0) {
                                $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                foreach ($pagos as $pago) {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }
                            } else {
                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $bol->fecha_emision, 'referencia' => '', 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml, 'id_doc' => $bol->id, 'tipo_doc' => 'bo'];
                                array_push($respuesta, $respuesta_registro);
                                $orden_nivel2 += 10;
                            }

                            //agregamos las referencias
                            if ($bol->docum_referencia != "---") {
                                if (isset($tipo_ref)) {
                                    if ($tipo_ref == "nc") {
                                        $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                        if (!is_null($nc)) {
                                            $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                            array_push($respuesta, $respuesta_registro);
                                        }
                                    }
                                }
                            }
                            $orden_nivel2 = 10;
                            $orden_nivel1 += 100;
                        }
                    }


                    //factura
                    $facturas = factura::where('num_factura', $buscado)->orderBy('fecha_emision', 'DESC')->get();
                    if ($facturas->count() > 0) {
                        foreach ($facturas as $fac) {
                            if ($fac->docum_referencia == "---" || $fac->docum_referencia == "") {
                                $referencia = $fac->docum_referencia;
                            } else {
                                list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $fac->docum_referencia);
                                if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                            }
                            $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Factura N° ' . $fac->num_factura, 'fecha' => $fac->fecha_emision, 'referencia' => $referencia, 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
                            array_push($respuesta, $respuesta_registro);

                            //buscamos pagos
                            $pagos = pago::select('pagos.*', 'formapago.formapago')
                                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                ->where('pagos.tipo_doc', 'fa')
                                ->where('pagos.id_doc', $fac->id)
                                ->get();
                            if ($pagos->count() > 0) {
                                $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                foreach ($pagos as $pago) {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }
                            } else {
                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $fac->fecha_emision, 'referencia' => '', 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml, 'id_doc' => $fac->id, 'tipo_doc' => 'fa'];
                                array_push($respuesta, $respuesta_registro);
                                $orden_nivel2 += 10;
                            }

                            //agregamos las referencias
                            if ($fac->docum_referencia != "---") {
                                if (isset($tipo_ref)) {
                                    if ($tipo_ref == "nc") {
                                        $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                        if (!is_null($nc)) {
                                            $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                            array_push($respuesta, $respuesta_registro);
                                        }
                                    }
                                }
                            }
                            $orden_nivel2 = 10;
                            $orden_nivel1 += 100;
                        }
                    }

                    //nota de crédito
                    $notas_credito = nota_de_credito::where('num_nota_credito', $buscado)->orderBy('fecha_emision', 'DESC')->get();
                    if ($notas_credito->count() > 0) {
                        foreach ($notas_credito as $nc) {
                            if ($nc->docum_referencia == "---" || $nc->docum_referencia == "") {
                                $referencia = $nc->docum_referencia;
                            } else {
                                list($tipo_ref, $num_ref, $fecha_ref) = explode("*", $nc->docum_referencia);
                                if ($tipo_ref == "fa") $referencia = "Factura N° " . $num_ref;
                                if ($tipo_ref == "bo") $referencia = "Boleta N° " . $num_ref;
                            }
                            $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Nota Crédito N° ' . $nc->num_nota_credito, 'fecha' => $nc->fecha_emision, 'referencia' => $referencia, 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                            array_push($respuesta, $respuesta_registro);


                            //agregamos las referencias
                            if ($nc->docum_referencia != "---") {
                                if (isset($tipo_ref)) {
                                    if ($tipo_ref == "bo") {
                                        $bo = boleta::where('num_boleta', $num_ref)->first();
                                        if (!is_null($bo)) {
                                            if (trim($bo->docum_referencia) == "---" || trim($bo->docum_referencia) == "") {
                                                $referencia_bol = "";
                                            } else {
                                                $referencia_bol = explode("*", $bo->docum_referencia)[4];
                                            }
                                            $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $bo->fecha_emision, 'referencia' => $referencia_bol, 'monto' => $bo->total, 'activo' => $bo->activo, 'es_pago' => 0, 'xml' => $bo->url_xml];
                                            array_push($respuesta, $respuesta_registro);
                                            $orden_nivel2 += 10;
                                            //buscamos pagos de la referencia boletas
                                            $pagos = pago::select('pagos.*', 'formapago.formapago')
                                                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                                ->where('pagos.tipo_doc', 'bo')
                                                ->where('pagos.id_doc', $bo->id)
                                                ->get();

                                            if ($pagos->count() > 0) {
                                                $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                                foreach ($pagos as $pago) {
                                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                                    array_push($respuesta, $respuesta_registro);
                                                    $orden_nivel2 += 10;
                                                }
                                            } else {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $bo->fecha_emision, 'referencia' => '', 'monto' => $bo->total, 'activo' => $bo->activo, 'es_pago' => 0, 'xml' => $bo->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                                $orden_nivel2 += 10;
                                            }
                                        }
                                    }

                                    if ($tipo_ref == "fa") {
                                        $fa = factura::where('num_factura', $num_ref)->first();
                                        if (!is_null($fa)) {
                                            if (trim($fa->docum_referencia) == "---" || trim($fa->docum_referencia == "")) {
                                                $referencia_fac = "";
                                            } else {
                                                $referencia_fac = explode("*", $fa->docum_referencia)[4];
                                            }

                                            $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $fa->fecha_emision, 'referencia' => $referencia_fac, 'monto' => $fa->total, 'activo' => $fa->activo, 'es_pago' => 0, 'xml' => $fa->url_xml];
                                            array_push($respuesta, $respuesta_registro);
                                            $orden_nivel2 += 10;
                                            //buscamos pagos de la referencia boletas
                                            $pagos = pago::select('pagos.*', 'formapago.formapago')
                                                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                                ->where('pagos.tipo_doc', 'fa')
                                                ->where('pagos.id_doc', $fa->id)
                                                ->get();

                                            if ($pagos->count() > 0) {
                                                $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                                foreach ($pagos as $pago) {
                                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                                    array_push($respuesta, $respuesta_registro);
                                                    $orden_nivel2 += 10;
                                                }
                                            } else {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $fa->fecha_emision, 'referencia' => '', 'monto' => $fa->total, 'activo' => $fa->activo, 'es_pago' => 0, 'xml' => $fa->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                                $orden_nivel2 += 10;
                                            }
                                        }
                                    }
                                }
                            }



                            $orden_nivel2 = 10;
                            $orden_nivel1 += 100;
                        }
                    }


                    //nota de débito FALTA:


                    //guía de despacho
                    $guias_despacho = guia_de_despacho::join('clientes', 'guias_de_despacho.id_cliente', 'clientes.id')->where('num_guia_despacho', $buscado)->orderBy('fecha_emision', 'DESC')->get();
                    if ($guias_despacho->count() > 0) {
                        foreach ($guias_despacho as $gd) {
                            if ($gd->tipo_cliente == 0) $referencia_guia = "Cliente: " . $gd->nombres . " " . $gd->apellidos . ". ";
                            if ($gd->tipo_cliente == 1) $referencia_guia = "Cliente: " . $gd->razon_social . ". ";
                            if (strlen(trim($gd->patente)) > 0) $referencia_guia .= " Pat: " . $gd->patente . ". ";
                            if (strlen(trim($gd->NombreChofer)) > 0) $referencia_guia .= " Chofer: " . $gd->NombreChofer . ". ";
                            if (strlen(trim($gd->DirDest)) > 0) $referencia_guia .= " Dir: " . $gd->DirDest;
                            $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Guía Despacho N° ' . $gd->num_guia_despacho, 'fecha' => $gd->fecha_emision, 'referencia' => $referencia_guia, 'monto' => $gd->total, 'activo' => $gd->activo, 'es_pago' => 0, 'xml' => $gd->url_xml];
                            array_push($respuesta, $respuesta_registro);
                            $orden_nivel2 += 10;
                        }
                        $orden_nivel2 = 10;
                        $orden_nivel1 += 100;
                    }

                    break;
                case "fecha":
                    return "<h2>Trae demasiados resultados, pronto se quitará.</h2>";
                    break;
                case "operacion":
                    $pagos_operacion = pago::where('referencia', 'LIKE', '%' . $buscado . '%')->orderBy('tipo_doc')->orderBy('fecha_pago', 'DESC')->get();
                    foreach ($pagos_operacion as $p) {
                        if ($p->tipo_doc == "bo") {
                            $bol = boleta::find($p->id_doc);
                            if (!is_null($bol)) {
                                if ($bol->docum_referencia == "---" || $bol->docum_referencia == "") {
                                    $referencia = $bol->docum_referencia;
                                } else {
                                    list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $bol->docum_referencia);
                                    if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                                }
                                $key = array_search('Boleta N° ' . $bol->num_boleta, array_column($respuesta, 'documento'));
                                if ($key === false) {
                                } else {
                                    continue;
                                }
                                $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Boleta N° ' . $bol->num_boleta, 'fecha' => $bol->fecha_emision, 'referencia' => $referencia, 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
                                array_push($respuesta, $respuesta_registro);

                                //buscamos pagos
                                $pagos = pago::select('pagos.*', 'formapago.formapago')
                                    ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                    ->where('pagos.tipo_doc', 'bo')
                                    ->where('pagos.id_doc', $bol->id)
                                    ->get();
                                if ($pagos->count() > 0) {
                                    $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                    foreach ($pagos as $pago) {
                                        $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                        array_push($respuesta, $respuesta_registro);
                                        $orden_nivel2 += 10;
                                    }
                                } else {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $bol->fecha_emision, 'referencia' => '', 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }

                                //agregamos las referencias
                                if (strlen(trim($bol->docum_referencia)) > 3) {
                                    if (isset($tipo_ref)) {
                                        if ($tipo_ref == "nc") {
                                            $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                            if (!is_null($nc)) {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                            }
                                        }
                                    }
                                }
                                $orden_nivel2 = 10;
                                $orden_nivel1 += 100;
                            }
                        }

                        if ($p->tipo_doc == "fa") {
                            $fac = factura::find($p->id_doc);
                            if (!is_null($fac)) {
                                if ($fac->docum_referencia == "---" || $fac->docum_referencia == "") {
                                    $referencia = $fac->docum_referencia;
                                } else {
                                    list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $fac->docum_referencia);
                                    if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                                }
                                $key = array_search('Factura N° ' . $fac->num_factura, array_column($respuesta, 'documento'));
                                if ($key === false) {
                                } else {
                                    continue;
                                }
                                $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Factura N° ' . $fac->num_factura, 'fecha' => $fac->fecha_emision, 'referencia' => $referencia, 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
                                array_push($respuesta, $respuesta_registro);

                                //buscamos pagos
                                $pagos = pago::select('pagos.*', 'formapago.formapago')
                                    ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                    ->where('pagos.tipo_doc', 'fa')
                                    ->where('pagos.id_doc', $fac->id)
                                    ->get();
                                if ($pagos->count() > 0) {
                                    $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                    foreach ($pagos as $pago) {
                                        $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                        array_push($respuesta, $respuesta_registro);
                                        $orden_nivel2 += 10;
                                    }
                                } else {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $fac->fecha_emision, 'referencia' => '', 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }

                                //agregamos las referencias
                                if (strlen(trim($fac->docum_referencia)) > 3) {
                                    if (isset($tipo_ref)) {
                                        if ($tipo_ref == "nc") {
                                            $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                            if (!is_null($nc)) {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                            }
                                        }
                                    }
                                }
                                $orden_nivel2 = 10;
                                $orden_nivel1 += 100;
                            }
                        }
                    }
                    break;
                case "monto":
                    $buscado = str_replace(".", "", $buscado); //por si ingresa el monto con puntos, los quitamos
                    $pagos_operacion = pago::where('monto', $buscado)->orderBy('tipo_doc')->orderBy('fecha_pago', 'DESC')->get(); // similar a operacion solo que cambia la condicion de pagos_operacion
                    foreach ($pagos_operacion as $p) {
                        if ($p->tipo_doc == "bo") {
                            $bol = boleta::find($p->id_doc);
                            if (!is_null($bol)) {
                                if ($bol->docum_referencia == "---" || $bol->docum_referencia == "") {
                                    $referencia = $bol->docum_referencia;
                                } else {
                                    list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $bol->docum_referencia);
                                    if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                                }
                                $key = array_search('Boleta N° ' . $bol->num_boleta, array_column($respuesta, 'documento'));
                                if ($key === false) {
                                } else {
                                    continue;
                                }
                                $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Boleta N° ' . $bol->num_boleta, 'fecha' => $bol->fecha_emision, 'referencia' => $referencia, 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
                                array_push($respuesta, $respuesta_registro);

                                //buscamos pagos
                                $pagos = pago::select('pagos.*', 'formapago.formapago')
                                    ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                    ->where('pagos.tipo_doc', 'bo')
                                    ->where('pagos.id_doc', $bol->id)
                                    ->get();
                                if ($pagos->count() > 0) {
                                    $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                    foreach ($pagos as $pago) {
                                        $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                        array_push($respuesta, $respuesta_registro);
                                        $orden_nivel2 += 10;
                                    }
                                } else {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $bol->fecha_emision, 'referencia' => '', 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }

                                //agregamos las referencias
                                if (strlen(trim($bol->docum_referencia)) > 3) {
                                    if (isset($tipo_ref)) {
                                        if ($tipo_ref == "nc") {
                                            $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                            if (!is_null($nc)) {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                            }
                                        }
                                    }
                                }
                                $orden_nivel2 = 10;
                                $orden_nivel1 += 100;
                            }
                        }

                        if ($p->tipo_doc == "fa") {
                            $fac = factura::find($p->id_doc);
                            if (!is_null($fac)) {
                                if ($fac->docum_referencia == "---" || $fac->docum_referencia == "") {
                                    $referencia = $fac->docum_referencia;
                                } else {
                                    list($tipo_ref, $num_ref, $fecha_ref, $num_motivo_ref, $motivo_ref) = explode("*", $fac->docum_referencia);
                                    if ($tipo_ref == "nc") $referencia = "Nota Crédito N° " . $num_ref;
                                }
                                $key = array_search('Factura N° ' . $fac->num_factura, array_column($respuesta, 'documento'));
                                if ($key === false) {
                                } else {
                                    continue;
                                }

                                $respuesta_registro = ['orden' => $orden_nivel1, 'documento' => 'Factura N° ' . $fac->num_factura, 'fecha' => $fac->fecha_emision, 'referencia' => $referencia, 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
                                array_push($respuesta, $respuesta_registro);

                                //buscamos pagos
                                $pagos = pago::select('pagos.*', 'formapago.formapago')
                                    ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                                    ->where('pagos.tipo_doc', 'fa')
                                    ->where('pagos.id_doc', $fac->id)
                                    ->get();
                                if ($pagos->count() > 0) {
                                    $orden_nivel2 = $orden_nivel1 + $orden_nivel2;
                                    foreach ($pagos as $pago) {
                                        $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $pago->formapago, 'fecha' => $pago->fecha_pago, 'referencia' => $pago->referencia, 'monto' => $pago->monto, 'activo' => $pago->activo, 'es_pago' => $pago->id];
                                        array_push($respuesta, $respuesta_registro);
                                        $orden_nivel2 += 10;
                                    }
                                } else {
                                    $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $fac->fecha_emision, 'referencia' => '', 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
                                    array_push($respuesta, $respuesta_registro);
                                    $orden_nivel2 += 10;
                                }

                                //agregamos las referencias
                                if (strlen(trim($fac->docum_referencia)) > 3) {
                                    if (isset($tipo_ref)) {
                                        if ($tipo_ref == "nc") {
                                            $nc = nota_de_credito::where('num_nota_credito', $num_ref)->first();
                                            if (!is_null($nc)) {
                                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => $referencia, 'fecha' => $nc->fecha_emision, 'referencia' => explode("*", $nc->motivo_correccion)[1], 'monto' => $nc->total, 'activo' => $nc->activo, 'es_pago' => 0, 'xml' => $nc->url_xml];
                                                array_push($respuesta, $respuesta_registro);
                                            }
                                        }
                                    }
                                }
                                $orden_nivel2 = 10;
                                $orden_nivel1 += 100;
                            }
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            return $e->getMessage(); //." tipo_doc: ".$p->tipo_doc." id: ".$p->id_doc;
        }
        return $v = view('reportes.detalle_documentosgenera2', compact('respuesta'));
    }

    public function detalle_documentosgenerados($data)
    {
        list($fecha, $dte) = explode("&", $data);
        $opcion = "y";

        if ($fecha == "x") { //19marz2021 busqueda full: boleta/factura, núm operación, monto, fecha
            $opcion = "x";


            $v = view('reportes.detalle_documentosgenerados', compact('datos', 'opcion'));
        } else { // busca por fecha y dte
            if ($dte == 33) $tipo_doc = 'fa';
            if ($dte == 39) $tipo_doc = 'bo';
            $total_pagos = 0;
            $total_docus_contado = 0;
            $total_docus_credito = 0;
            $total_operaciones_transbank = 0;
            $total_operaciones_transbank_td = 0;
            $total_operaciones_transbank_tc = 0;
            $datos = [];
            $pago_formapago = [];
            $sumatoria_formapago = [];
            if ($tipo_doc == 'bo') {
                $docus = boleta::select('boletas.id', 'boletas.created_at as fecha_docu', 'boletas.num_boleta as num_docu', 'boletas.total as total_docu', 'boletas.es_credito', 'boletas.estado_sii', 'users.name as usuario')
                    ->join('users', 'boletas.usuarios_id', 'users.id')
                    ->where('boletas.fecha_emision', $fecha)
                    ->where('boletas.activo', 1)
                    ->get();
            }
            if ($tipo_doc == 'fa') {
                $docus = factura::select('facturas.id', 'facturas.created_at as fecha_docu', 'facturas.num_factura as num_docu', 'facturas.total as total_docu', 'facturas.es_credito', 'facturas.estado_sii', 'users.name as usuario')
                    ->join('users', 'facturas.usuarios_id', 'users.id')
                    ->where('facturas.fecha_emision', $fecha)
                    ->where('facturas.activo', 1)
                    ->get();
            }
            if ($docus->count() == 0) {
                $v = view('reportes.detalle_documentosgenerados', compact('datos', 'opcion'));
                return $v;
            }



            $formas_de_pago = formapago::all();
            foreach ($docus as $docu) {
                $item = [];
                $item['num_docu'] = $docu->num_docu;
                $item['fecha_docu'] = $docu->fecha_docu;
                $item['total_docu'] = intval($docu->total_docu);
                $item['usuario_docu'] = $docu->usuario;
                if ($docu->es_credito == 1) {
                    $total_docus_credito += intval($docu->total_docu);
                } else {
                    $total_docus_contado += intval($docu->total_docu);
                }
                $item['es_credito'] = $docu->es_credito == 1 ? "SI" : "NO";
                $item['estado_sii'] = $docu->estado_sii;
                $suma_pagos = 0;
                foreach ($formas_de_pago as $fp) {
                    $elpago = 0;
                    if ($docu->es_credito == 0) {
                        $pago = pago::select('pagos.monto', 'pagos.referencia')
                            ->where('pagos.id_doc', $docu->id)
                            ->where('pagos.tipo_doc', $tipo_doc)
                            ->where('pagos.id_forma_pago', $fp->id)
                            ->first();
                        if (!is_null($pago)) {
                            if ($fp->formapago == 'Tarjeta Crédito') $total_operaciones_transbank_tc++;
                            if ($fp->formapago == 'Tarjeta Débito') $total_operaciones_transbank_td++;
                            $elpago = intval($pago->monto);
                            $suma_pagos += $elpago;
                            $dato = $elpago . "&" . $pago->referencia;
                        } else {
                            $dato = 0;
                        }
                        $pago_formapago[$fp->formapago] = $elpago;
                    } else {
                        $dato = 0;
                        $pago_formapago[$fp->formapago] = 0;
                    }
                    $item[$fp->formapago] = $dato;
                    $sumatoria_formapago[$fp->formapago] = (isset($sumatoria_formapago[$fp->formapago]) ? $sumatoria_formapago[$fp->formapago] : 0) + $pago_formapago[$fp->formapago];
                }
                $total_pagos += $suma_pagos;
                $item['total_pagos'] = $suma_pagos;
                array_push($datos, $item);
            }
            $total_docus = $total_docus_contado + $total_docus_credito;
            $sumatoria_formapago['fecha_docu'] = "";
            $sumatoria_formapago['num_docu'] = "TOTALES:";
            $sumatoria_formapago['total_docu'] = $total_docus;
            $sumatoria_formapago['total_pagos'] = $total_pagos;
            $sumatoria_formapago['es_credito'] = "";
            $sumatoria_formapago['estado_sii'] = "";
            $sumatoria_formapago['usuario_docu'] = "";
            array_push($datos, $sumatoria_formapago);
            $total_operaciones_transbank = $total_operaciones_transbank_td + $total_operaciones_transbank_tc;
            $v = view('reportes.detalle_documentosgenerados', compact('datos', 'formas_de_pago', 'total_docus_contado', 'total_docus_credito', 'total_docus', 'total_operaciones_transbank', 'total_operaciones_transbank_td', 'total_operaciones_transbank_tc', 'opcion'));
        }

        return $v;
    }

    public function reporte_pagos_mensuales($periodo)
    {
        try {
            /*
             * El parámetro esperado es YYYY-MM.
             * Se mantiene compatibilidad con YYYY-MM-DD.
             */
            if (!preg_match('/^\d{4}-\d{2}(-\d{2})?$/', (string) $periodo)) {
                abort(422, 'El período seleccionado no es válido.');
            }

            $fechaPeriodo = Carbon::createFromFormat(
                strlen($periodo) === 7 ? 'Y-m' : 'Y-m-d',
                $periodo
            );

            $fechaInicio = $fechaPeriodo->copy()->startOfMonth()->format('Y-m-d');
            $fechaTermino = $fechaPeriodo->copy()->endOfMonth()->format('Y-m-d');
            $nombreMes = mb_strtoupper(
                $fechaPeriodo->locale('es')->translatedFormat('F')
            );
            $anio = $fechaPeriodo->year;

            $porcentajeIVA = (float) Session::get('PARAM_IVA', 0.19);
            $factorIVA = 1 + $porcentajeIVA;

            /*
             * Las ventas se obtienen desde los detalles, aplicando los
             * mismos criterios utilizados por el informe de rentabilidad.
             */
            $facturas = factura_detalle::from('facturas_detalle as fd')
                ->select(
                    DB::raw("'FACTURA' as tipo_documento"),
                    DB::raw("'fa' as codigo_documento"),
                    'f.id as id_documento',
                    'f.num_factura as numero_documento',
                    'f.fecha_emision',
                    'fd.id_repuestos',
                    'fd.usuarios_id as id_vendedor',
                    'u.name as vendedor',
                    'fd.precio_venta',
                    'fd.pu_neto',
                    'fd.cantidad',
                    'fd.descuento',
                    'fd.total'
                )
                ->join('facturas as f', 'f.id', '=', 'fd.id_factura')
                ->leftJoin('users as u', 'u.id', '=', 'fd.usuarios_id')
                ->whereBetween('f.fecha_emision', [$fechaInicio, $fechaTermino])
                ->where('f.activo', 1)
                ->where('fd.activo', 1)
                ->where('f.es_credito', 0)
                ->whereIn('f.estado_sii', ['ACEPTADO', 'REPARO'])
                ->get();

            $boletas = boleta_detalle::from('boletas_detalle as bd')
                ->select(
                    DB::raw("'BOLETA' as tipo_documento"),
                    DB::raw("'bo' as codigo_documento"),
                    'b.id as id_documento',
                    'b.num_boleta as numero_documento',
                    'b.fecha_emision',
                    'bd.id_repuestos',
                    'bd.usuarios_id as id_vendedor',
                    'u.name as vendedor',
                    'bd.precio_venta',
                    'bd.pu_neto',
                    'bd.cantidad',
                    'bd.descuento',
                    'bd.total'
                )
                ->join('boletas as b', 'b.id', '=', 'bd.id_boleta')
                ->leftJoin('users as u', 'u.id', '=', 'bd.usuarios_id')
                ->whereBetween('b.fecha_emision', [$fechaInicio, $fechaTermino])
                ->where('b.activo', 1)
                ->where('bd.activo', 1)
                ->where('b.es_credito', 0)
                ->whereIn('b.estado_sii', ['ACEPTADO', 'REPARO'])
                ->get();

            $ventas = $facturas->concat($boletas);
            $cacheCostos = [];
            $documentos = [];
            $vendedores = [];

            $resumen = [
                'venta_bruta' => 0,
                'venta_neta' => 0,
                'costo_productos_vendidos' => 0,
                'utilidad_bruta' => 0,
                'margen_bruto' => 0,
                'cantidad_documentos' => 0,
                'cantidad_facturas' => 0,
                'cantidad_boletas' => 0,
                'cantidad_unidades' => 0,
                'venta_sin_costo' => 0,
                'cantidad_items_sin_costo' => 0,
            ];

            foreach ($ventas as $venta) {
                $cantidad = (float) $venta->cantidad;
                $precioVenta = (float) $venta->precio_venta;
                $precioNeto = (float) $venta->pu_neto;
                $descuentoBruto = (float) $venta->descuento;
                $totalDetalle = (float) $venta->total;

                if ($precioNeto > 0) {
                    $ventaNeta = ($precioNeto * $cantidad)
                        - ($descuentoBruto / $factorIVA);
                } elseif ($totalDetalle > 0) {
                    $ventaNeta = $totalDetalle / $factorIVA;
                } else {
                    $ventaNeta = (
                        ($precioVenta * $cantidad) - $descuentoBruto
                    ) / $factorIVA;
                }

                $ventaBruta = $totalDetalle > 0
                    ? $totalDetalle
                    : (($precioVenta * $cantidad) - $descuentoBruto);

                $ventaNeta = max(0, round($ventaNeta, 2));
                $ventaBruta = max(0, round($ventaBruta, 2));

                $claveCosto = $venta->id_repuestos . '_' . $venta->fecha_emision;

                if (!array_key_exists($claveCosto, $cacheCostos)) {
                    $cacheCostos[$claveCosto] =
                        $this->obtenerCostoHistoricoRepuesto(
                            $venta->id_repuestos,
                            $venta->fecha_emision
                        );
                }

                $costoHistorico = $cacheCostos[$claveCosto];
                $tieneCosto = !is_null($costoHistorico);
                $costoTotal = $tieneCosto
                    ? ((float) $costoHistorico['costo_unitario'] * $cantidad)
                    : 0;

                $utilidad = $tieneCosto ? $ventaNeta - $costoTotal : 0;

                $resumen['venta_bruta'] += $ventaBruta;
                $resumen['venta_neta'] += $ventaNeta;
                $resumen['cantidad_unidades'] += $cantidad;

                if ($tieneCosto) {
                    $resumen['costo_productos_vendidos'] += $costoTotal;
                    $resumen['utilidad_bruta'] += $utilidad;
                } else {
                    $resumen['venta_sin_costo'] += $ventaNeta;
                    $resumen['cantidad_items_sin_costo']++;
                }

                $claveDocumento = $venta->codigo_documento . '_' . $venta->id_documento;
                $documentos[$claveDocumento] = $venta->codigo_documento;

                $idVendedor = $venta->id_vendedor ?: 0;
                $nombreVendedor = trim((string) $venta->vendedor)
                    ?: 'SIN VENDEDOR';

                if (!isset($vendedores[$idVendedor])) {
                    $vendedores[$idVendedor] = [
                        'id_vendedor' => $idVendedor,
                        'vendedor' => $nombreVendedor,
                        'venta_bruta' => 0,
                        'venta_neta' => 0,
                        'costo_productos_vendidos' => 0,
                        'utilidad_bruta' => 0,
                        'margen_bruto' => 0,
                        'cantidad_unidades' => 0,
                        'documentos' => [],
                        'cantidad_documentos' => 0,
                        'venta_sin_costo' => 0,
                    ];
                }

                $vendedores[$idVendedor]['venta_bruta'] += $ventaBruta;
                $vendedores[$idVendedor]['venta_neta'] += $ventaNeta;
                $vendedores[$idVendedor]['cantidad_unidades'] += $cantidad;
                $vendedores[$idVendedor]['documentos'][$claveDocumento] = true;

                if ($tieneCosto) {
                    $vendedores[$idVendedor]['costo_productos_vendidos'] +=
                        $costoTotal;
                    $vendedores[$idVendedor]['utilidad_bruta'] += $utilidad;
                } else {
                    $vendedores[$idVendedor]['venta_sin_costo'] += $ventaNeta;
                }
            }

            foreach ($documentos as $tipoDocumento) {
                if ($tipoDocumento === 'fa') {
                    $resumen['cantidad_facturas']++;
                } else {
                    $resumen['cantidad_boletas']++;
                }
            }

            $resumen['cantidad_documentos'] = count($documentos);
            $ventaConCosto = $resumen['venta_neta'] - $resumen['venta_sin_costo'];
            $resumen['margen_bruto'] = $ventaConCosto > 0
                ? ($resumen['utilidad_bruta'] / $ventaConCosto) * 100
                : 0;

            foreach ($vendedores as &$vendedor) {
                $vendedor['cantidad_documentos'] = count($vendedor['documentos']);
                unset($vendedor['documentos']);

                $ventaConCostoVendedor =
                    $vendedor['venta_neta'] - $vendedor['venta_sin_costo'];

                $vendedor['margen_bruto'] = $ventaConCostoVendedor > 0
                    ? (
                        $vendedor['utilidad_bruta']
                        / $ventaConCostoVendedor
                    ) * 100
                    : 0;

                foreach ([
                    'venta_bruta',
                    'venta_neta',
                    'costo_productos_vendidos',
                    'utilidad_bruta',
                    'venta_sin_costo',
                    'margen_bruto'
                ] as $campo) {
                    $vendedor[$campo] = round($vendedor[$campo], 2);
                }
            }
            unset($vendedor);

            $vendedores = collect($vendedores)
                ->sortByDesc('venta_neta')
                ->values();

            /*
             * Resumen de medios de pago del período.
             */
            $pagosPorForma = pago::select(
                    'formapago.formapago',
                    DB::raw('COUNT(pagos.id) as operaciones'),
                    DB::raw('SUM(pagos.monto) as total')
                )
                ->join('formapago', 'formapago.id', '=', 'pagos.id_forma_pago')
                ->where('pagos.activo', 1)
                ->whereBetween('pagos.fecha_pago', [$fechaInicio, $fechaTermino])
                ->whereIn('pagos.tipo_doc', ['bo', 'fa'])
                ->groupBy('pagos.id_forma_pago', 'formapago.formapago')
                ->orderByDesc('total')
                ->get();

            $totalCobrado = (float) $pagosPorForma->sum('total');

            $getnet = pago::selectRaw(
                    'id_forma_pago, COUNT(*) as operaciones, SUM(monto) as total'
                )
                ->where('activo', 1)
                ->where('referencia_pago', 2)
                ->whereBetween('fecha_pago', [$fechaInicio, $fechaTermino])
                ->whereIn('tipo_doc', ['bo', 'fa'])
                ->whereIn('id_forma_pago', [2, 5])
                ->groupBy('id_forma_pago')
                ->get();

            $resumenGetnet = [
                'total' => round((float) $getnet->sum('total'), 2),
                'operaciones' => (int) $getnet->sum('operaciones'),
                'credito' => round(
                    (float) optional($getnet->firstWhere('id_forma_pago', 2))->total,
                    2
                ),
                'debito' => round(
                    (float) optional($getnet->firstWhere('id_forma_pago', 5))->total,
                    2
                ),
                'porcentaje_total' => $totalCobrado > 0
                    ? round(
                        ((float) $getnet->sum('total') / $totalCobrado) * 100,
                        2
                    )
                    : 0,
            ];

            $notasCredito = nota_de_credito::select(
                    'notas_de_credito.*',
                    'users.name as usuario'
                )
                ->leftJoin(
                    'users',
                    'users.id',
                    '=',
                    'notas_de_credito.usuarios_id'
                )
                ->whereBetween(
                    'notas_de_credito.fecha_emision',
                    [$fechaInicio, $fechaTermino]
                )
                ->where('notas_de_credito.activo', 1)
                ->whereIn(
                    'notas_de_credito.estado_sii',
                    ['ACEPTADO', 'REPARO']
                )
                ->orderByDesc('notas_de_credito.fecha_emision')
                ->get();

            $resumenNotasCredito = [
                'cantidad' => $notasCredito->count(),
                'total' => round((float) $notasCredito->sum('total'), 2),
            ];

            foreach ([
                'venta_bruta',
                'venta_neta',
                'costo_productos_vendidos',
                'utilidad_bruta',
                'margen_bruto',
                'venta_sin_costo'
            ] as $campo) {
                $resumen[$campo] = round($resumen[$campo], 2);
            }


            /*
             * =====================================================
             * MATRICES MENSUALES CON EL MISMO PATRÓN DEL REPORTE
             * DIARIO: forma de pago por vendedor y total.
             * =====================================================
             */
            $pagosDetallados = pago::select(
                    'pagos.usuarios_id',
                    'pagos.id_forma_pago',
                    'pagos.tipo_doc',
                    'pagos.monto',
                    'pagos.referencia_pago',
                    'users.name as usuario',
                    'formapago.formapago'
                )
                ->leftJoin('users', 'users.id', '=', 'pagos.usuarios_id')
                ->join(
                    'formapago',
                    'formapago.id',
                    '=',
                    'pagos.id_forma_pago'
                )
                ->where('pagos.activo', 1)
                ->whereBetween(
                    'pagos.fecha_pago',
                    [$fechaInicio, $fechaTermino]
                )
                ->whereIn('pagos.tipo_doc', ['bo', 'fa'])
                ->get();

            $idsUsuariosReporte = collect($ventas)
                ->pluck('id_vendedor')
                ->merge($pagosDetallados->pluck('usuarios_id'))
                ->filter()
                ->unique()
                ->values();

            $usuarios = User::whereIn('id', $idsUsuariosReporte)
                ->orderBy('name')
                ->get();

            $formasPago = formapago::orderBy('id')->get();

            $crearMatriz = function () use ($usuarios, $formasPago) {
                $matriz = [];

                foreach ($usuarios as $usuario) {
                    $matriz[$usuario->name] = [];

                    foreach ($formasPago as $forma) {
                        $matriz[$usuario->name][$forma->formapago] = 0;
                    }
                }

                return $matriz;
            };

            $totales = $crearMatriz();
            $boletasPago = $crearMatriz();
            $facturasPago = $crearMatriz();
            $getnetPorUsuario = $crearMatriz();

            foreach ($pagosDetallados as $pagoDetalle) {
                $nombreUsuario = trim((string) $pagoDetalle->usuario);

                if (
                    $nombreUsuario === ''
                    || !isset($totales[$nombreUsuario])
                    || !isset(
                        $totales[$nombreUsuario][
                            $pagoDetalle->formapago
                        ]
                    )
                ) {
                    continue;
                }

                $montoPago = (float) $pagoDetalle->monto;

                $totales[$nombreUsuario][$pagoDetalle->formapago]
                    += $montoPago;

                if ($pagoDetalle->tipo_doc === 'bo') {
                    $boletasPago[$nombreUsuario][
                        $pagoDetalle->formapago
                    ] += $montoPago;
                }

                if ($pagoDetalle->tipo_doc === 'fa') {
                    $facturasPago[$nombreUsuario][
                        $pagoDetalle->formapago
                    ] += $montoPago;
                }

                if (
                    (int) $pagoDetalle->referencia_pago === 2
                    && in_array(
                        (int) $pagoDetalle->id_forma_pago,
                        [2, 5],
                        true
                    )
                ) {
                    $getnetPorUsuario[$nombreUsuario][
                        $pagoDetalle->formapago
                    ] += $montoPago;
                }
            }

            return view('reportes.ventas_mensuales_resumen', compact(
                'periodo',
                'fechaInicio',
                'fechaTermino',
                'nombreMes',
                'anio',
                'resumen',
                'vendedores',
                'pagosPorForma',
                'totalCobrado',
                'resumenGetnet',
                'notasCredito',
                'resumenNotasCredito',
                'usuarios',
                'formasPago',
                'totales',
                'boletasPago',
                'facturasPago',
                'getnetPorUsuario'
            ));
        } catch (\Exception $e) {
            Log::error('Error reporte mensual de rendimiento', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);

            return response(
                '<div class="alert alert-danger mb-0">'
                . '<strong>No fue posible generar el reporte.</strong><br>'
                . e($e->getMessage())
                . '</div>',
                500
            );
        }
    }

    public function conexiones($fecha){
        try {
            $conexiones = registro_login::select('registro_login.fecha_ingreso','registro_login.fecha_login','user_server.user','registro_login.direccion_ip')
                                        ->join('user_server','registro_login.usuario_id_servidor','user_server.id')
                                        ->where('registro_login.fecha_ingreso',$fecha)
                                        ->where('user_server.user','<>','frojo')
                                        ->orderBy('registro_login.fecha_login')
                                        ->get();

            foreach($conexiones as $c){
                $c->fecha_ingreso = Carbon::parse($c->fecha_ingreso)->format("d-m-Y");
            }
            $v = view('fragm.registro_conexiones',compact('conexiones'));
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function reporte_pagos($fecha)
    {
       
        /*
         * Mostrar únicamente usuarios que tengan pagos activos
         * registrados en la fecha consultada.
         *
         * No se filtra por users.activo, porque un usuario actualmente
         * inactivo puede tener movimientos históricos en la fecha.
         */
        $idsUsuariosConPagos = pago::query()
            ->whereDate('fecha_pago', $fecha)
            ->where('activo', 1)
            ->whereNotNull('usuarios_id')
            ->where('usuarios_id', '>', 0)
            ->distinct()
            ->pluck('usuarios_id');

        $usuarios = User::select('id', 'name')
            ->whereIn('id', $idsUsuariosConPagos)
            ->orderBy('name')
            ->get();

        $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();
        //$formas_pago: id, formapago
        $fp_array = $formas_pago->toArray();

        /*
         * Inicializar las estructuras aunque no existan pagos.
         * Así compact() y la vista pueden responder con tarjetas en cero
         * y tablas vacías, sin lanzar variables indefinidas.
         */
        $totales = [];
        $boletas = [];
        $facturas = [];
        $abonos = [];
        $getnet = [];
        $transbank = [];
        $boletas_credito = [];
        $facturas_credito = [];

        foreach ($usuarios as $usuario) {
            $totales[$usuario->name]['delivery'] = 0;
            foreach ($formas_pago as $forma) {
                $pago_bol = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                    ->join('boletas', 'pagos.id_doc', 'boletas.id')
                    ->where('pagos.activo', 1)
                    ->where('pagos.fecha_pago', $fecha)
                    ->where('pagos.usuarios_id', $usuario->id)
                    ->where('pagos.id_forma_pago', $forma->id)
                    ->where('pagos.tipo_doc', 'bo')
                    ->get();
                $pago_bol_resta = 0;
                $pago_bol_rechazados = 0;
                $pago_bol_delivery = 0;
                if ($pago_bol->count() > 0) {

                    foreach ($pago_bol as $pb) {
                        $nc = nota_de_credito::where('activo', 1)
                            ->where('docum_referencia', 'LIKE', 'bo*' . $pb->num_boleta . '%')
                            ->first();
                        if (!is_null($nc)) {
                            $pago_bol_resta += $pb->monto;
                        }

                        $bol_rech = boleta::where('activo', 1)
                            ->where('estado_sii', '<>', 'ACEPTADO')
                            ->where('id', $pb->id_doc)
                            ->first();
                        if (!is_null($bol_rech)) {
                            $pago_bol_rechazados += $pb->monto;
                        }

                        if ($pb->es_delivery == 2) { //delivery pagado
                            $pago_bol_delivery += $pb->monto;
                            $totales[$usuario->name]['delivery'] += $pb->monto;
                        }
                    }
                }
                //Resta deliverys, notas de crédito y rechazados de la misma fecha
                //Si se requiere se le pueden descontar las notas de credito, usando $pago_bol_resta
                $boletas[$usuario->name][$forma->formapago] = $pago_bol->sum('monto') - $pago_bol_delivery - $pago_bol_rechazados;


                $pago_fac = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                    ->join('facturas', 'pagos.id_doc', 'facturas.id')
                    ->where('pagos.activo', 1)
                    ->where('pagos.fecha_pago', $fecha)
                    ->where('pagos.usuarios_id', $usuario->id)
                    ->where('pagos.id_forma_pago', $forma->id)
                    ->where('pagos.tipo_doc', 'fa')
                    ->get();

                $pago_fac_resta = 0;
                $pago_fac_rechazados = 0;
                $pago_fac_delivery = 0;
                if ($pago_fac->count() > 0) {

                    foreach ($pago_fac as $pf) {
                        $nc = nota_de_credito::where('activo', 1)
                            ->where('docum_referencia', 'LIKE', 'fa*' . $pf->num_factura . '%')
                            ->first();
                        if (!is_null($nc)) {
                            $pago_fac_resta += $pf->monto;
                        }

                        $fac_rech = factura::where('activo', 1)
                            ->where('estado_sii', '<>', 'ACEPTADO')
                            ->where('id', $pf->id_doc)
                            ->first();
                        if (!is_null($fac_rech)) {
                            $pago_fac_rechazados += $pf->monto;
                        }

                        if ($pf->es_delivery == 2) { //delivery pagado
                            $pago_fac_delivery += $pf->monto;
                            $totales[$usuario->name]['delivery'] += $pf->monto;
                        }
                    }
                }
                //Resta notas de crédito y rechazados de la misma fecha
                //Si se requiere se le pueden descontar las notas de credito, usando $pago_fac_resta
                $facturas[$usuario->name][$forma->formapago] = $pago_fac->sum('monto') - $pago_fac_delivery - $pago_fac_rechazados;

                $totales[$usuario->name][$forma->formapago] = $boletas[$usuario->name][$forma->formapago] + $facturas[$usuario->name][$forma->formapago];
            } //fin forma pago

        } //fin usuarios

        $notcred = nota_de_credito::select('notas_de_credito.*', 'users.name as usuario', 'users.id as id_user')
            ->where('notas_de_credito.activo', 1)
            ->where('notas_de_credito.estado_sii', 'ACEPTADO')
            ->where('notas_de_credito.fecha_emision', $fecha)
            ->join('users', 'notas_de_credito.usuarios_id', 'users.id')
            ->get();

        $notcred_total = 0;
        if ($notcred->count() > 0) {
            foreach ($notcred as $nc) {
                list($tipo_doc, $num_doc, $fecha_doc) = explode("*", $nc->docum_referencia);
                //Sumar solo las NC que son anteriores a la fecha
                // if ($fecha_doc == $fecha) {
                //     $notcred_total += $nc->total;
                // }
                //Sumar todas las NC realizadas 

                //Sacamos todas las notas de credito que sean a credito 
                if($tipo_doc == "bo"){
                    $boleta = boleta::where('num_boleta',$num_doc)->first();
                    $boleta->es_credito === 1 ? '' : $notcred_total += $nc->total;
                }elseif($tipo_doc == "fa"){
                    $factura = factura::where('num_factura',$num_doc)->first();
                    $factura->es_credito === 1 ? '' : $notcred_total += $nc->total;
                }else{
                    $notcred_total += $nc->total;
                }


                //Agregar la forma de pago en url_pdf temporalmente no mas
                if ($tipo_doc == 'bo') {
                    $id_bol = boleta::where('num_boleta', $num_doc)->value('id');
                    $pagos_boleta = pago::where('id_doc', $id_bol)
                        ->where('tipo_doc', 'bo')
                        ->get();
                    if ($pagos_boleta->count() == 0) {
                        $nc->url_pdf = "Crédito";
                        $nc->save();
                    }
                    if ($pagos_boleta->count() > 0) $nc->url_pdf = "MultiPago";
                    if ($pagos_boleta->count() == 1) {
                        foreach ($pagos_boleta as $pagbol) {
                            for ($i = 0; $i < count($fp_array); $i++) {
                                if ($pagbol->id_forma_pago == $fp_array[$i]['id']) {
                                    $nc->url_pdf = $fp_array[$i]['formapago'];
                                    $nc->save();
                                }
                            }
                        }
                    }
                }

                if ($tipo_doc == 'fa') {
                    $id_fac = factura::where('num_factura', $num_doc)->value('id');
                    $pagos_factura = pago::where('id_doc', $id_fac)
                        ->where('tipo_doc', 'fa')
                        ->get();
                    if ($pagos_factura->count() == 0) {
                        $nc->url_pdf = "Crédito";
                        $nc->save();
                    }
                    if ($pagos_factura->count() > 0) $nc->url_pdf = "MultiPago";
                    if ($pagos_factura->count() == 1) {

                        foreach ($pagos_factura as $pagfac) {
                            for ($i = 0; $i < count($fp_array); $i++) {
                                if ($pagfac->id_forma_pago == $fp_array[$i]['id']) {
                                    $nc->url_pdf = $fp_array[$i]['formapago'];
                                    $nc->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        // NOTAS DE DÉBITO
        $notdeb = nota_de_debito::select('notas_de_debito.*', 'users.name as usuario', 'users.id as id_user')
            ->where('notas_de_debito.activo', 1)
            ->where('notas_de_debito.estado_sii', 'ACEPTADO')
            ->where('notas_de_debito.fecha_emision', $fecha)
            ->join('users', 'notas_de_debito.usuarios_id', 'users.id')
            ->get();

        $notdeb_total = 0;
        if ($notdeb->count() > 0) {
            foreach ($notdeb as $nd) {
                list($tipo_doc, $num_doc, $fecha_doc) = explode("*", $nd->docum_referencia);
                
                //Sacamos todas las notas de debito que sean a credito 
                if($tipo_doc == "bo"){
                    $boleta = boleta::where('num_boleta',$num_doc)->first();
                    $boleta->es_credito === 1 ? '' : $notdeb_total += $nd->total;
                }elseif($tipo_doc == "fa"){
                    $factura = factura::where('num_factura',$num_doc)->first();
                    $factura->es_credito === 1 ? '' : $notdeb_total += $nd->total;
                }else{
                    $notdeb_total += $nd->total;
                }

                //Agregar la forma de pago en url_pdf temporalmente no mas
                if ($tipo_doc == 'bo') {
                    $id_bol = boleta::where('num_boleta', $num_doc)->value('id');
                    $pagos_boleta = pago::where('id_doc', $id_bol)
                        ->where('tipo_doc', 'bo')
                        ->get();
                    if ($pagos_boleta->count() == 0) {
                        $nd->url_pdf = "Crédito";
                        $nd->save();
                    }
                    if ($pagos_boleta->count() > 0) $nd->url_pdf = "MultiPago";
                    if ($pagos_boleta->count() == 1) {
                        foreach ($pagos_boleta as $pagbol) {
                            for ($i = 0; $i < count($fp_array); $i++) {
                                if ($pagbol->id_forma_pago == $fp_array[$i]['id']) {
                                    $nd->url_pdf = $fp_array[$i]['formapago'];
                                    $nd->save();
                                }
                            }
                        }
                    }
                }

                if ($tipo_doc == 'fa') {
                    $id_fac = factura::where('num_factura', $num_doc)->value('id');
                    $pagos_factura = pago::where('id_doc', $id_fac)
                        ->where('tipo_doc', 'fa')
                        ->get();
                    if ($pagos_factura->count() == 0) {
                        $nd->url_pdf = "Crédito";
                        $nd->save();
                    }
                    if ($pagos_factura->count() > 0) $nd->url_pdf = "MultiPago";
                    if ($pagos_factura->count() == 1) {

                        foreach ($pagos_factura as $pagfac) {
                            for ($i = 0; $i < count($fp_array); $i++) {
                                if ($pagfac->id_forma_pago == $fp_array[$i]['id']) {
                                    $nd->url_pdf = $fp_array[$i]['formapago'];
                                    $nd->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        $bol_rech = boleta::select('boletas.id', 'boletas.num_boleta as num_doc', 'boletas.total', 'boletas.resultado_envio as resultado', 'boletas.url_xml as xml', 'boletas.estado_sii', 'boletas.url_pdf', 'users.name as usuario')
            ->join('users', 'boletas.usuarios_id', 'users.id')
            ->where('boletas.activo', 1)
            ->where('boletas.estado_sii', '<>', 'ACEPTADO')
            ->where('boletas.fecha_emision', $fecha)
            ->get();

        //Si hay pagos en los documentos rechazados, los desactivamos
        foreach ($bol_rech as $br) {
            $cómo_pagó = "Crédito";
            $pbr = pago::where('tipo_doc', 'bo')
                ->where('id_doc', $br->id)
                ->get();

            //Desactivamos cada pago.
            if ($pbr->count() > 0 && $br->estado_sii == 'RECHAZADO') {
                foreach ($pbr as $pbr_temp) {
                    $pbr_temp->activo = 0;
                    $pbr_temp->save();
                }
            }

            //Agregamos la forma de pago
            if ($pbr->count() == 1) {
                $pbr_1 = pago::where('tipo_doc', 'bo')
                    ->where('id_doc', $br->id)
                    ->first();

                for ($i = 0; $i < count($fp_array); $i++) {
                    if ($pbr_1->id_forma_pago == $fp_array[$i]['id']) {
                        $cómo_pagó = $fp_array[$i]['formapago'];
                    }
                }
            }

            if ($pbr->count() > 1) $cómo_pagó = "MultiPago";
            $br->url_pdf = $cómo_pagó;
            $br->save();
        }

        $fac_rech = factura::select('facturas.id', 'facturas.num_factura as num_doc', 'facturas.total', 'facturas.resultado_envio as resultado', 'facturas.url_xml as xml', 'facturas.estado_sii', 'facturas.url_pdf', 'users.name as usuario')
            ->join('users', 'facturas.usuarios_id', 'users.id')
            ->where('facturas.activo', 1)
            ->where('facturas.estado_sii', '<>', 'ACEPTADO')
            ->where('facturas.fecha_emision', $fecha)
            ->get();
        //Si hay pagos en los documentos rechazados, los desactivamos

        foreach ($fac_rech as $fr) {
            $cómo_pagó = "Crédito";
            $pfr = pago::where('tipo_doc', 'fa')
                ->where('id_doc', $fr->id)
                ->get();

            //idem en boletas más arriba
            if ($pfr->count() > 0 && $fr->estado_sii == 'RECHAZADO') {
                foreach ($pfr as $pfr_temp) {
                    $pfr_temp->activo = 0;
                    $pfr_temp->save();
                }
            }

            //Agregamos la forma de pago
            if ($pfr->count() == 1) {
                $pfr_1 = pago::where('tipo_doc', 'fa')
                    ->where('id_doc', $fr->id)
                    ->first();

                for ($i = 0; $i < count($fp_array); $i++) {
                    if ($pfr_1->id_forma_pago == $fp_array[$i]['id']) {
                        $cómo_pagó = $fp_array[$i]['formapago'];
                    }
                }
            }

            if ($pfr->count() > 1) $cómo_pagó = "MultiPago";
            $fr->url_pdf = $cómo_pagó;
            $fr->save();
        }

        if ($fac_rech->count() > 0 && $bol_rech->count() > 0) {
            $rechazados = collect($fac_rech)->merge(collect($bol_rech));
        } else if ($fac_rech->count() == 0 && $bol_rech->count() > 0) {
            $rechazados = $bol_rech;
        } else if ($fac_rech->count() > 0 && $bol_rech->count() == 0) {
            $rechazados = $fac_rech;
        } else if ($fac_rech->count() == 0 && $bol_rech->count() == 0) {
            $rechazados = collect();
        }
        $delivery_pendientes = $this->delivery_pendientes($fecha);
        
        try {
            $abonos_realizados = abono::where('fecha_emision',$fecha)->get();
            foreach($usuarios as $usuario){
                foreach ($formas_pago as $forma) {
                    //Abonos
                    
                    $pago_abono = pago::select('pagos.*','abono.id as id_abono')
                                            ->join('abono','pagos.id_doc','abono.id')
                                            ->where('pagos.activo',1)
                                            ->where('pagos.fecha_pago',$fecha)
                                            ->where('pagos.usuarios_id',$usuario->id)
                                            ->where('pagos.id_forma_pago',$forma->id)
                                            ->where('pagos.tipo_doc','ab')
                                            ->get();

                    $abonos[$usuario->name][$forma->formapago] = $pago_abono->sum('monto');
                    //FIN ABONOS
                }
                
            }

           
            foreach($usuarios as $usuario){
                foreach ($formas_pago as $forma) {
                                    
                    // Getnet: contar/sumar sólo pagos asociados a documentos válidos.
                    $pago_getnet_boletas = pago::query()
                                        ->join('boletas', 'pagos.id_doc', '=', 'boletas.id')
                                        ->where('pagos.tipo_doc', 'bo')
                                        ->where('pagos.referencia_pago', 2)
                                        ->where('pagos.activo', 1)
                                        ->where('pagos.fecha_pago', $fecha)
                                        ->where('pagos.usuarios_id', $usuario->id)
                                        ->where('pagos.id_forma_pago', $forma->id)
                                        ->sum('pagos.monto');

                    $pago_getnet_facturas = pago::query()
                                        ->join('facturas', 'pagos.id_doc', '=', 'facturas.id')
                                        ->where('pagos.tipo_doc', 'fa')
                                        ->where('pagos.referencia_pago', 2)
                                        ->where('pagos.activo', 1)
                                        ->where('pagos.fecha_pago', $fecha)
                                        ->where('pagos.usuarios_id', $usuario->id)
                                        ->where('pagos.id_forma_pago', $forma->id)
                                        ->sum('pagos.monto');

                    $getnet[$usuario->name][$forma->formapago] =
                        $pago_getnet_boletas + $pago_getnet_facturas;
                    
                }
                
            }

            foreach($usuarios as $usuario){
                foreach ($formas_pago as $forma) {
                                    
                    $pago_transbank = pago::where('referencia_pago',1)
                                        ->where('activo',1)
                                        ->where('fecha_pago',$fecha)
                                        ->where('usuarios_id',$usuario->id)
                                        ->where('id_forma_pago',$forma->id)
                                        ->get();

                    $transbank[$usuario->name][$forma->formapago] = $pago_transbank->sum('monto');
                    
                }
                
            }

            $boletas_a_credito = boleta::where('es_credito',1)->where('fecha_emision', $fecha)->get();
            $facturas_a_credito = factura::where('es_credito',1)->where('fecha_emision',$fecha)->get();

            foreach($usuarios as $usuario){
                $boletas_credito[$usuario->name] = 0;
                $facturas_credito[$usuario->name]= 0;
                foreach($boletas_a_credito as $b){
                    if($b->usuarios_id == $usuario->id){
                        $boletas_credito[$usuario->name]++;
                    }
                }
            foreach($facturas_a_credito as $f){
                    if($f->usuarios_id == $usuario->id){
                        $facturas_credito[$usuario->name]++;
                    }
                }
            }
            
            // return [$boletas_credito, $facturas_credito];
            
            
            /*
             * Indicadores diarios de ventas al contado.
             *
             * Los documentos a crédito no se incorporan en ventas, costo,
             * utilidad, margen, documentos ni unidades de estas tarjetas.
             * Se mantienen disponibles en las tablas específicas de crédito.
             */
            $factorIVA = 1 + (float) Session::get('PARAM_IVA', 0.19);

            $ventasFacturaDia = factura_detalle::from('facturas_detalle as fd')
                ->select(
                    DB::raw("'fa' as tipo_doc"),
                    'f.id as id_documento',
                    'f.fecha_emision',
                    'fd.id_repuestos',
                    'fd.precio_venta',
                    'fd.pu_neto',
                    'fd.cantidad',
                    'fd.descuento',
                    'fd.total'
                )
                ->join('facturas as f', 'f.id', '=', 'fd.id_factura')
                ->whereDate('f.fecha_emision', $fecha)
                ->where('f.activo', 1)
                ->where('fd.activo', 1)
                ->where('f.es_credito', 0)
                ->whereIn('f.estado_sii', ['ACEPTADO', 'REPARO'])
                ->get();

            $ventasBoletaDia = boleta_detalle::from('boletas_detalle as bd')
                ->select(
                    DB::raw("'bo' as tipo_doc"),
                    'b.id as id_documento',
                    'b.fecha_emision',
                    'bd.id_repuestos',
                    'bd.precio_venta',
                    'bd.pu_neto',
                    'bd.cantidad',
                    'bd.descuento',
                    'bd.total'
                )
                ->join('boletas as b', 'b.id', '=', 'bd.id_boleta')
                ->whereDate('b.fecha_emision', $fecha)
                ->where('b.activo', 1)
                ->where('bd.activo', 1)
                ->where('b.es_credito', 0)
                ->whereIn('b.estado_sii', ['ACEPTADO', 'REPARO'])
                ->get();

            $ventasDia = $ventasFacturaDia->concat($ventasBoletaDia);
            $cacheCostosDia = [];
            $documentosDia = [];

            $resumenDiario = [
                'venta_bruta' => 0,
                'venta_neta' => 0,
                'costo_productos_vendidos' => 0,
                'utilidad_bruta' => 0,
                'margen_bruto' => 0,
                'cantidad_documentos' => 0,
                'cantidad_facturas' => 0,
                'cantidad_boletas' => 0,
                'cantidad_unidades' => 0,
                'venta_sin_costo' => 0,
                'cantidad_items_sin_costo' => 0,
            ];

            foreach ($ventasDia as $ventaDia) {
                $cantidadDia = (float) $ventaDia->cantidad;
                $descuentoDia = (float) $ventaDia->descuento;
                $totalDia = (float) $ventaDia->total;
                $precioNetoDia = (float) $ventaDia->pu_neto;
                $precioVentaDia = (float) $ventaDia->precio_venta;

                if ($precioNetoDia > 0) {
                    $ventaNetaDia = ($precioNetoDia * $cantidadDia)
                        - ($descuentoDia / $factorIVA);
                } elseif ($totalDia > 0) {
                    $ventaNetaDia = $totalDia / $factorIVA;
                } else {
                    $ventaNetaDia = (
                        ($precioVentaDia * $cantidadDia) - $descuentoDia
                    ) / $factorIVA;
                }

                $ventaBrutaDia = $totalDia > 0
                    ? $totalDia
                    : (($precioVentaDia * $cantidadDia) - $descuentoDia);

                $ventaNetaDia = max(0, round($ventaNetaDia, 2));
                $ventaBrutaDia = max(0, round($ventaBrutaDia, 2));

                $claveCostoDia =
                    $ventaDia->id_repuestos . '_' . $ventaDia->fecha_emision;

                if (!array_key_exists($claveCostoDia, $cacheCostosDia)) {
                    $cacheCostosDia[$claveCostoDia] =
                        $this->obtenerCostoHistoricoRepuesto(
                            $ventaDia->id_repuestos,
                            $ventaDia->fecha_emision
                        );
                }

                $costoHistoricoDia = $cacheCostosDia[$claveCostoDia];
                $tieneCostoDia = !is_null($costoHistoricoDia);
                $costoTotalDia = $tieneCostoDia
                    ? ((float) $costoHistoricoDia['costo_unitario'] * $cantidadDia)
                    : 0;

                $resumenDiario['venta_bruta'] += $ventaBrutaDia;
                $resumenDiario['venta_neta'] += $ventaNetaDia;
                $resumenDiario['cantidad_unidades'] += $cantidadDia;

                if ($tieneCostoDia) {
                    $resumenDiario['costo_productos_vendidos'] += $costoTotalDia;
                    $resumenDiario['utilidad_bruta'] +=
                        $ventaNetaDia - $costoTotalDia;
                } else {
                    $resumenDiario['venta_sin_costo'] += $ventaNetaDia;
                    $resumenDiario['cantidad_items_sin_costo']++;
                }

                $claveDocumentoDia =
                    $ventaDia->tipo_doc . '_' . $ventaDia->id_documento;

                $documentosDia[$claveDocumentoDia] = $ventaDia->tipo_doc;
            }

            foreach ($documentosDia as $tipoDia) {
                if ($tipoDia === 'fa') {
                    $resumenDiario['cantidad_facturas']++;
                } else {
                    $resumenDiario['cantidad_boletas']++;
                }
            }

            $resumenDiario['cantidad_documentos'] = count($documentosDia);

            $ventaConCostoDia =
                $resumenDiario['venta_neta']
                - $resumenDiario['venta_sin_costo'];

            $resumenDiario['margen_bruto'] = $ventaConCostoDia > 0
                ? ($resumenDiario['utilidad_bruta'] / $ventaConCostoDia) * 100
                : 0;

            /*
             * GETNET DEL DÍA
             * Se consideran solamente pagos Getnet realmente asociados a una
             * boleta o factura existente. Esto evita que registros huérfanos
             * (por ejemplo, pagos con tipo_doc = 'bo' pero id_doc = 0) inflen
             * tanto el monto como la cantidad de operaciones del resumen diario.
             */
            $getnetBoletasDia = pago::query()
                ->select('pagos.*')
                ->join('boletas', 'pagos.id_doc', '=', 'boletas.id')
                ->where('pagos.tipo_doc', 'bo')
                ->where('pagos.activo', 1)
                ->whereDate('pagos.fecha_pago', $fecha)
                ->where('pagos.referencia_pago', 2)
                ->whereIn('pagos.id_forma_pago', [2, 5])
                ->get();

            $getnetFacturasDia = pago::query()
                ->select('pagos.*')
                ->join('facturas', 'pagos.id_doc', '=', 'facturas.id')
                ->where('pagos.tipo_doc', 'fa')
                ->where('pagos.activo', 1)
                ->whereDate('pagos.fecha_pago', $fecha)
                ->where('pagos.referencia_pago', 2)
                ->whereIn('pagos.id_forma_pago', [2, 5])
                ->get();

            $getnetDia = $getnetBoletasDia
                ->concat($getnetFacturasDia)
                ->values();

            $totalCobradoDia = pago::where('activo', 1)
                ->whereDate('fecha_pago', $fecha)
                ->whereIn('tipo_doc', ['bo', 'fa'])
                ->sum('monto');

            $resumenDiario['cobros_getnet'] =
                round((float) $getnetDia->sum('monto'), 2);

            $resumenDiario['operaciones_getnet'] =
                $getnetDia->count();
            $resumenDiario['porcentaje_getnet'] = $totalCobradoDia > 0
                ? ($resumenDiario['cobros_getnet'] / $totalCobradoDia) * 100
                : 0;
            $resumenDiario['cantidad_notas_credito'] = $notcred->count();
            $resumenDiario['monto_notas_credito'] =
                round((float) $notcred->sum('total'), 2);

            foreach ([
                'venta_bruta',
                'venta_neta',
                'costo_productos_vendidos',
                'utilidad_bruta',
                'margen_bruto',
                'venta_sin_costo',
                'cobros_getnet',
                'porcentaje_getnet',
                'monto_notas_credito'
            ] as $campoDiario) {
                $resumenDiario[$campoDiario] =
                    round($resumenDiario[$campoDiario], 2);
            }

$v = view('reportes.ventas_diarias_resumen', compact('boletas', 'facturas', 'totales', 'formas_pago', 'usuarios', 'notcred', 'notcred_total', 'notdeb', 'notdeb_total', 'rechazados', 'delivery_pendientes','abonos','abonos_realizados','getnet','transbank','boletas_a_credito','facturas_a_credito','boletas_credito','facturas_credito','resumenDiario'));
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function delivery_pendientes_html($fecha)
    {
        $q = Session::get('rol');
        $user = Auth::user();
        if ($user->rol->nombrerol == 'Administrador') {
            $delivery_pendientes = $this->delivery_pendientes($fecha);
            if ($delivery_pendientes->count() > 0) {
                $v = view('fragm.delivery_pendientes', compact('delivery_pendientes'));
            } else {
                $v = "0";
            }
        } else {
            $v = "0";
        }
        //kaka
        return $v;
    }

    private function delivery_pendientes($fecha)
    {
        $delivery_pendientes = Collect();
        /*EL CAMPO es_delivery si es:
        0: no es delivery
        1: es delivery sin pendiente de pago
        2: es delivery pagado
        */
        $boletas_delivery_pendientes = boleta::select('boletas.id as iddoc', 'boletas.fecha_emision as fechadoc', 'boletas.num_boleta as numdoc', 'boletas.total as totaldoc', 'boletas.url_xml as xmldoc', 'clientes.id as id_cliente', 'clientes.rut', 'clientes.tipo_cliente', 'clientes.razon_social', 'clientes.nombres', 'clientes.apellidos', 'clientes.empresa', 'users.name as usuario', 'users.id as id_user')
            ->where('boletas.es_delivery', 1)
            ->where('boletas.activo', 1)
            ->where('boletas.estado_sii', 'ACEPTADO')
            ->where('boletas.fecha_emision', '<=', $fecha)
            ->join('clientes', 'boletas.id_cliente', 'clientes.id')
            ->join('users', 'boletas.usuarios_id', 'users.id')
            ->orderBy('boletas.fecha_emision', 'ASC')
            ->get();
        if ($boletas_delivery_pendientes->count() > 0) {
            $delivery_pendientes = $delivery_pendientes->merge($boletas_delivery_pendientes);
        }

        $facturas_delivery_pendientes = factura::select('facturas.id as iddoc', 'facturas.fecha_emision as fechadoc', 'facturas.num_factura as numdoc', 'facturas.total as totaldoc', 'facturas.url_xml as xmldoc', 'clientes.id as id_cliente', 'clientes.rut', 'clientes.tipo_cliente', 'clientes.razon_social', 'clientes.nombres', 'clientes.apellidos', 'clientes.empresa', 'users.name as usuario', 'users.id as id_user')
            ->where('facturas.es_delivery', 1)
            ->where('facturas.activo', 1)
            ->where('facturas.estado_sii', 'ACEPTADO')
            ->where('facturas.fecha_emision', '<=', $fecha)
            ->join('clientes', 'facturas.id_cliente', 'clientes.id')
            ->join('users', 'facturas.usuarios_id', 'users.id')
            ->orderBy('facturas.fecha_emision', 'ASC')
            ->get();
        if ($facturas_delivery_pendientes->count() > 0) {
            $delivery_pendientes = $delivery_pendientes->merge($facturas_delivery_pendientes);
        }

        return $delivery_pendientes;
    }

    public function bajo_stock_home(){
        
        try {
            $user = Auth::user();
                if ($user->rol->nombrerol == 'Administrador') {
                    $repuestos = repuesto::where('stock_actual','<=',2)->take(10)->get();
                    $locales = local::where('activo',1)->get();
                    $familias = familia::where('activo',1)->get();
                    return [$repuestos, $locales];
                }else{
                    return "0";
                }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function bajo_stock_html(){
        $usuario = Auth::user();
        if($usuario->rol->nombrerol == "Administrador" || $usuario->id == 250){
            try {           
                $id_familia_sin_definir = 312;
                $rep = repuesto::select('repuestos.*','marcarepuestos.id as idmarca','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto')
                                               ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                                               ->where('repuestos.activo',1)
                                               ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                               ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                               ->get();
                $repuestos = [];
                //$repuestos = $repuestos_primera_ubicacion->mergeRecursive($repuestos_segunda_ubicacion)->mergeRecursive($repuestos_tercera_ubicacion);
                    // 1 = KOREA
                    // 2 = JAPON
                    // 3 = CHINA
                    // 4 = TAIWAN
                    // 5 = TAILANDIA
                    // 6 = MULTIORIGEN
                    // 7 = INDONESIA
                    // 10 = ALEMANIA
                    // 11 = INDIA
                    // 12 = MEXICO
                    // 13 = BRASIL
                    // 14 = ESPAÑA
                    // 17 = ARGENTINA
                    // 18 = TURQUIA
                    // 19 = CHILE
                    // 20 = MALASIA
                    // 21 = PERÚ
                    // 23 = FRANCIA
                    // 24 = USA
                    // 25 = HOLANDA
                    // 26 = INGLATERRA
                    // 29 = ROMANIA
                    // 33 = BULGARIA
                foreach($rep as $r){
                    
                    $stock = intval(\App\Services\StockPorLocalService::total($r));
                    if($r->stock_minimo >= $stock) array_push($repuestos, $r);

                    if($r->id_pais == 3){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Zeng_Liansong%27s_proposal_for_the_PRC_flag.svg/220px-Zeng_Liansong%27s_proposal_for_the_PRC_flag.svg.png";
                      }else if($r->id_pais == 1){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/Flag_of_South_Korea.svg/200px-Flag_of_South_Korea.svg.png";
                      }else if($r->id_pais == 2){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Flag_of_Japan.svg/200px-Flag_of_Japan.svg.png";
                      }else if($r->id_pais == 4){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Flag_of_the_Republic_of_China_construction_sheet.svg/220px-Flag_of_the_Republic_of_China_construction_sheet.svg.png";
                      }else if($r->id_pais == 5){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Flag_of_Thailand.svg/200px-Flag_of_Thailand.svg.png";
                      }else if($r->id_pais == 6){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ef/International_Flag_of_Planet_Earth.svg/200px-International_Flag_of_Planet_Earth.svg.png";
                      }else if($r->id_pais == 7){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_Indonesia.svg/1280px-Flag_of_Indonesia.svg.png";
                      }else if($r->id_pais == 10){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Flag_of_Germany.svg/640px-Flag_of_Germany.svg.png";
                      }else if($r->id_pais == 11){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/4/41/Flag_of_India.svg";
                      }else if($r->id_pais == 12){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Flag_of_Mexico.svg/800px-Flag_of_Mexico.svg.png";
                      }else if($r->id_pais == 13){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/300px-Flag_of_Brazil.svg.png";
                      }else if($r->id_pais == 14){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Bandera_de_Espa%C3%B1a.svg/1200px-Bandera_de_Espa%C3%B1a.svg.png";
                      }else if($r->id_pais == 17){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Flag_of_Argentina.svg/1200px-Flag_of_Argentina.svg.png";
                      }else if($r->id_pais == 18){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Turkey.svg/2560px-Flag_of_Turkey.svg.png";
                      }else if($r->id_pais == 19){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/7/78/Flag_of_Chile.svg";
                      }else if($r->id_pais == 20){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Flag_of_Malaya.svg/360px-Flag_of_Malaya.svg.png";
                      }else if($r->id_pais == 21){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Flag_of_Peru_%281825%E2%80%931884%29.svg/270px-Flag_of_Peru_%281825%E2%80%931884%29.svg.png";
                      }else if($r->id_pais == 23){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/200px-Flag_of_France.svg.png";
                      }else if($r->id_pais == 24){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/1200px-Flag_of_the_United_States.svg.png";
                      }else if($r->id_pais == 25){
                        $r->url_pais ="https://www.paisesbajosytu.nl/binaries/medium/content/gallery/netherlandsandyou/content-afbeeldingen/algemeen/vlag-nederland.png";
                      }else if($r->id_pais == 26){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/b/be/Flag_of_England.svg/200px-Flag_of_England.svg.png";
                      }else if($r->id_pais == 29){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Flag_of_Romania.svg/1200px-Flag_of_Romania.svg.png";
                      }else if($r->id_pais == 33){
                        $r->url_pais ="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Bulgaria.svg/1200px-Flag_of_Bulgaria.svg.png";
                      }
                }

            
            
                // Enviar los proveedores a la vista
                $pc = new proveedorcontrolador();
                $rc = new repuestocontrolador();

                $proveedores_array = $pc->dame_proveedores();
             
                $proveedores = json_decode($proveedores_array["proveedores"]);
                $familias = $rc->damefamilias();

                $hoy = Carbon::today();
                
                $b = [];

                $repuestos_bajo_stock_todos = stock_minimo::select('stock_minimo.fecha_emision','repuestos.codigo_interno','repuestos.id as id_repuesto','repuestos.estado','repuestos.id_modifica_estado')
                                                    ->join('repuestos','stock_minimo.id_repuesto','repuestos.id')
                                                    // ->where('repuestos.estado','<>','Pedido')
                                                    // ->orWhere('repuestos.estado','')
                                                    ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                                                    ->groupBy('repuestos.codigo_interno')
                                                    ->orderBy('stock_minimo.fecha_emision')
                                                    ->get();

                                                    // recorremos los repuestos con bajo stock y borramos los repuestos que ya tengan un stock superior al minimo
                foreach($repuestos_bajo_stock_todos as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                  
                    $repuesto = repuesto::find($r->id_repuesto);
                    
                    $stock_total = \App\Services\StockPorLocalService::total($repuesto);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($b, $r);
                    } 
                }

                $repuestos_bajo_stock = stock_minimo::select('stock_minimo.fecha_emision','repuestos.codigo_interno','repuestos.id as id_repuesto','repuestos.estado','repuestos.id_modifica_estado')
                                                    ->join('repuestos','stock_minimo.id_repuesto','repuestos.id')
                                                    ->where('stock_minimo.fecha_emision',$hoy)
                                                    ->where('repuestos.id_familia','<>',$id_familia_sin_definir)
                                                    ->groupBy('repuestos.codigo_interno')
                                                    ->get();

                // creamos un array para guardar los repuestos con stock minimo
                $a = [];

                // recorremos los repuestos con bajo stock y borramos los repuestos que ya tengan un stock superior al minimo
                foreach($repuestos_bajo_stock as $r){

                    // formateamos la fecha de emision
                    $r->fecha_emision = Carbon::parse($r->fecha_emision)->format('d-m-Y');
                  
                    $repuesto = repuesto::find($r->id_repuesto);
                    
                    $stock_total = \App\Services\StockPorLocalService::total($repuesto);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($a, $r);
                    } 
                }

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

                $hoy = Carbon::today();
                try {
                   //Subconsulta para obtener la ultima fecha de emision de cada repuesto
                    $subquery = stock_minimo::select('id_repuesto', DB::raw('MAX(fecha_emision) as ultima_fecha_emision'))
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

                $importaciones_excel = importaciones_excel::with('proveedor')->with('usuario')->get();
                
                $v = view('reportes.bajo_stock',[
                    'proveedores' => $proveedores,
                    'familias' => $familias,
                    'repuestos'=> $a,
                    'repuestos_todos' => $b,
                    'estados' => $estados,
                    'repuestos_' => $reps_,
                    'importaciones_excel' => $importaciones_excel
                ]);

                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else{
            return redirect('/home');
        }
        
    }

    public function stock_critico(){
        try {
          
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
            $pc = new proveedorcontrolador();
            $rc = new repuestocontrolador();

            $proveedores_array = $pc->dame_proveedores();
        
            $proveedores = json_decode($proveedores_array["proveedores"]);
            $familias = $rc->damefamilias();
            return view('reportes.stock_critico',[
                'repuestos' => $rep,
                'proveedores' => $proveedores,
                'familias' => $familias
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function rep_sin_prov(){
        try {
            $pc = new proveedorcontrolador();
            $rc = new repuestocontrolador();

            $proveedores_array = $pc->dame_proveedores();
             
            $proveedores = json_decode($proveedores_array["proveedores"]);
            $familias = $rc->damefamilias();
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

            return view('reportes.rep_sin_prov',[
                'proveedores' => $proveedores,
                'familias' => $familias,
                'estados' => $estados,
                'repuestos' => $repuestos_sin_stock_proveedor
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function stock_pendiente(){
        try {
            $pc = new proveedorcontrolador();
            $rc = new repuestocontrolador();

            $proveedores_array = $pc->dame_proveedores();
            $proveedores = json_decode($proveedores_array["proveedores"]);
            $familias = $rc->damefamilias();
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

            // Obtener todos los repuestos con estado "Pedido"
            $repuestos = repuesto::select('repuestos.*','familias.nombrefamilia','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto')
                                                ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                                ->join('familias','familias.id','repuestos.id_familia')
                                                ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                                ->where('repuestos.estado','Pendiente')
                                                 ->where('repuestos.id_familia','<>',312)
                                                ->where('repuestos.activo',1)
                                                ->orderBy('repuestos.created_at','desc')
                                                ->get();

            return view('reportes.stock_pendiente',[
                'proveedores' => $proveedores,
                'familias' => $familias,
                'estados' => $estados,
                'repuestos' => $repuestos
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    

    public function dame_rechazados_mes($data)
    {
        list($mes, $año) = explode("&", $data);


        $bol_rech = boleta::selectRaw('num_boleta as num_doc, DATE_FORMAT(fecha_emision, "%d-%m-%Y") as fecha_doc,total as total_doc, url_xml as xml, resultado_envio, estado_sii')
            ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii<>?', [$mes, $año, 1, 'ACEPTADO'])
            ->orderBy('id', 'ASC')
            ->get();



        $fac_rech = factura::selectRaw('num_factura as num_doc, DATE_FORMAT(fecha_emision, "%d-%m-%Y") as fecha_doc,total as total_doc, url_xml as xml, resultado_envio, estado_sii')
            ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii<>?', [$mes, $año, 1, 'ACEPTADO'])
            ->orderBy('id', 'ASC')
            ->get();

        if ($fac_rech->count() > 0 && $bol_rech->count() > 0) {
            $rechazados = collect($fac_rech)->merge(collect($bol_rech));
        } else if ($fac_rech->count() == 0 && $bol_rech->count() > 0) {
            $rechazados = $bol_rech;
        } else if ($fac_rech->count() > 0 && $bol_rech->count() == 0) {
            $rechazados = $fac_rech;
        } else if ($fac_rech->count() == 0 && $bol_rech->count() == 0) {
            $rechazados = collect();
        }

        return json_encode($rechazados);
    }

    public function dame_rechazados_json()
    {
        return json_encode($this->dame_rechazados());
    }

    public function detalle($info)
    {
        list($fecha, $doc, $id_usu, $id_form) = explode("&", $info);
        if (intval($id_form) < 0) {
            $quien = "DETALLE";
            $num_doc = abs(intval($id_form));
            if ($doc == 'bo') $id_doc = boleta::where('activo', 1)->where('num_boleta', $num_doc)->value('id');
            if ($doc == 'fa') $id_doc = factura::where('activo', 1)->where('num_factura', $num_doc)->value('id');
            if ($doc == 'ab') $id_doc = abono::where('activo',1)->where('id',$num_doc)->value('id');
            $docus = pago::select(
                'pagos.created_at as fecha_doc',
                $doc == 'bo' ? 'boletas.num_boleta as num_doc' : 'facturas.num_factura as num_doc',
                $doc == 'bo' ? 'boletas.url_xml as url' : 'facturas.url_xml as url',
                'pagos.monto',
                'formapago.formapago'
            )
                ->join($doc == 'bo' ? 'boletas' : 'facturas', 'pagos.id_doc', $doc == 'bo' ? 'boletas.id' : 'facturas.id')
                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                ->where('pagos.activo', 1)
                ->where('pagos.tipo_doc', $doc)
                ->where('pagos.id_doc', $id_doc)
                ->get();
        } else {

            $quien = User::where('id', $id_usu)->value('name');
            if($doc == 'ab'){
                try {
                    $docus = pago::select(
                        'pagos.created_at as fecha_doc',
                        'abono.num_abono as num_doc',
                        'abono.url_pdf as url',
                        'pagos.monto',
                        'formapago.formapago',
                        'users.name as usuario')
                        ->join('abono','pagos.id_doc','abono.id')
                        ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                        ->join('users', 'pagos.usuarios_id', 'users.id')
                        ->where('pagos.activo', 1)
                        ->where('pagos.fecha_pago', $fecha)
                        ->where('pagos.usuarios_id', $id_usu)
                        ->where('pagos.id_forma_pago', $id_form)
                        ->where('pagos.tipo_doc', $doc)
                        ->get();
                    $v = view('reportes.ventas_diarias_detalle', compact('docus', 'doc', 'quien'));
                    return $v;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
                
            }

            if($doc == 'bo_credito'){
                try {
                    $docus = boleta::select(
                        'boletas.created_at as fecha_doc',
                        'boletas.num_boleta as num_doc',
                        'boletas.url_xml as url',
                        'boletas.total as monto',
                        'users.name as usuario')
                        ->join('users', 'boletas.usuarios_id', 'users.id')
                        ->where('boletas.activo', 1)
                        ->where('boletas.fecha_emision', $fecha)
                        ->where('boletas.usuarios_id', $id_usu)
                        ->where('boletas.es_credito',1)
                        ->get();
                    $v = view('reportes.ventas_diarias_detalle', compact('docus', 'doc', 'quien'));
                    return $v;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            if($doc == 'fa_credito'){
                try {
                    $docus = factura::select(
                        'facturas.created_at as fecha_doc',
                        'facturas.num_factura as num_doc',
                        'facturas.url_xml as url',
                        'facturas.total as monto',
                        'users.name as usuario')
                        ->join('users', 'facturas.usuarios_id', 'users.id')
                        ->where('facturas.activo', 1)
                        ->where('facturas.fecha_emision', $fecha)
                        ->where('facturas.usuarios_id', $id_usu)
                        ->where('facturas.es_credito',1)
                        ->get();
                    $v = view('reportes.ventas_diarias_detalle', compact('docus', 'doc', 'quien'));
                    return $v;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
            $docus = pago::select(
                'pagos.created_at as fecha_doc',
                $doc == 'bo' ? 'boletas.num_boleta as num_doc' : 'facturas.num_factura as num_doc',
                $doc == 'bo' ? 'boletas.url_xml as url' : 'facturas.url_xml as url',
                'pagos.monto',
                'formapago.formapago',
                'users.name as usuario'
            )
                ->join($doc == 'bo' ? 'boletas' : 'facturas', 'pagos.id_doc', $doc == 'bo' ? 'boletas.id' : 'facturas.id')
                ->join('formapago', 'pagos.id_forma_pago', 'formapago.id')
                ->join('users', 'pagos.usuarios_id', 'users.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', $id_usu)
                ->where('pagos.id_forma_pago', $id_form)
                ->where('pagos.tipo_doc', $doc)
                ->get();
        }

        $v = view('reportes.ventas_diarias_detalle', compact('docus', 'doc', 'quien'));
        return $v;
    }

    public function getnet(){
        $confirma = $this->confirmaringreso('/reportes/getnet');
        if($confirma) return view('reportes.getnet'); else return redirect('home');
        
    }

    public function transbank()
    {
        $v = view('reportes.transbank');
        return $v;
    }

    public function transbank_mes($data)
    {
        list($mes, $año) = explode("&", $data);
        $mes = pago::selectRaw('fecha_pago as fecha, count(fecha_pago) as num_oper, sum(monto) as total')
            ->whereRaw('MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?) AND referencia_pago=?', [$mes, $año, 1, 2, 5,1])
            ->groupBy('fecha_pago')
            ->orderBy('id', 'ASC')
            ->get();
        $v = view('reportes.transbank_mes', compact('mes'));
        return $v;
    }

    public function transbank_dia($fecha)
    {
        $dia_bol = pago::selectRaw('CONVERT(pagos.created_at,TIME) as hora, pagos.monto as total, pagos.referencia as referencia, "boleta" as tipo_doc, boletas.num_boleta as num_doc')
            ->join('boletas', 'pagos.id_doc', 'boletas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?) AND referencia_pago=?', ['bo', $fecha, 1, 2, 5,1])
            ->orderBy('pagos.created_at', 'ASC')
            ->get();

        $dia_fac = pago::selectRaw('CONVERT(pagos.created_at,TIME)  as hora, pagos.monto as total, pagos.referencia as referencia, "factura" as tipo_doc, facturas.num_factura as num_doc')
            ->join('facturas', 'pagos.id_doc', 'facturas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?) AND referencia_pago=?', ['fa', $fecha, 1, 2, 5,1])
            ->orderBy('pagos.created_at', 'ASC')
            ->get();
        
        $dia = ($dia_bol->mergeRecursive($dia_fac))->sortBy('hora');
        $total_dia = $dia->sum('total');
        $v = view('reportes.transbank_dia', compact('dia', 'fecha', 'total_dia'));
        return $v;
    }

    public function getnet_mes($data)
    {
        list($numeroMes, $año) = explode("&", $data);

        /*
         * El resumen mensual debe contar exactamente las mismas operaciones
         * que aparecen al abrir el detalle del día.
         *
         * El código anterior contaba directamente desde pagos, mientras que
         * getnet_dia() sólo muestra pagos que realmente están asociados a una
         * boleta o factura existente mediante INNER JOIN. Por eso podían
         * aparecer, por ejemplo, 32 operaciones en el mes y sólo 30 al abrir
         * el detalle.
         */

        $pagosBoletas = pago::query()
            ->selectRaw('pagos.fecha_pago as fecha, pagos.monto as total')
            ->join('boletas', 'pagos.id_doc', '=', 'boletas.id')
            ->where('pagos.tipo_doc', 'bo')
            ->whereMonth('pagos.fecha_pago', $numeroMes)
            ->whereYear('pagos.fecha_pago', $año)
            ->where('pagos.activo', 1)
            ->whereIn('pagos.id_forma_pago', [2, 5])
            ->where('pagos.referencia_pago', 2);

        $pagosFacturas = pago::query()
            ->selectRaw('pagos.fecha_pago as fecha, pagos.monto as total')
            ->join('facturas', 'pagos.id_doc', '=', 'facturas.id')
            ->where('pagos.tipo_doc', 'fa')
            ->whereMonth('pagos.fecha_pago', $numeroMes)
            ->whereYear('pagos.fecha_pago', $año)
            ->where('pagos.activo', 1)
            ->whereIn('pagos.id_forma_pago', [2, 5])
            ->where('pagos.referencia_pago', 2);

        $operacionesGetnet = $pagosBoletas->unionAll($pagosFacturas);

        $mes = DB::query()
            ->fromSub($operacionesGetnet, 'operaciones_getnet')
            ->selectRaw('fecha, COUNT(*) as num_oper, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();

        $v = view('reportes.getnet_mes', compact('mes'));
        return $v;
    }


    public function transferencias_mes($data){
        list($mes, $año) = explode("&", $data);
        $mes = pago::selectRaw('fecha_pago as fecha, count(fecha_pago) as num_oper, sum(monto) as total')
            ->whereRaw('MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND id_forma_pago=? ', [$mes, $año, 1,4])
            ->groupBy('fecha_pago')
            ->orderBy('id', 'ASC')
            ->get();
        $v = view('reportes.getnet_mes', compact('mes'));
        return $v;
    }

    public function getnet_dia($fecha)
    {
        /*
         * El detalle Getnet se presenta por DOCUMENTO, pero la cantidad de
         * operaciones y el monto Getnet siguen saliendo de los registros de
         * pagos. De esta manera el resumen mensual y el detalle diario siguen
         * conciliando entre sí, incluso cuando un documento posee más de una
         * operación Getnet.
         */

        $getnetBoletas = pago::query()
            ->select(
                'pagos.id as id_pago',
                'pagos.id_doc',
                'pagos.monto as monto_getnet',
                'pagos.referencia',
                'pagos.created_at',
                'boletas.num_boleta as num_doc',
                'boletas.total as total_venta'
            )
            ->selectRaw("'bo' as tipo_codigo, 'Boleta' as tipo_doc")
            ->join('boletas', 'pagos.id_doc', '=', 'boletas.id')
            ->where('pagos.tipo_doc', 'bo')
            ->whereDate('pagos.fecha_pago', $fecha)
            ->where('pagos.activo', 1)
            ->whereIn('pagos.id_forma_pago', [2, 5])
            ->where('pagos.referencia_pago', 2)
            ->orderBy('pagos.created_at', 'ASC')
            ->get();

        $getnetFacturas = pago::query()
            ->select(
                'pagos.id as id_pago',
                'pagos.id_doc',
                'pagos.monto as monto_getnet',
                'pagos.referencia',
                'pagos.created_at',
                'facturas.num_factura as num_doc',
                'facturas.total as total_venta'
            )
            ->selectRaw("'fa' as tipo_codigo, 'Factura' as tipo_doc")
            ->join('facturas', 'pagos.id_doc', '=', 'facturas.id')
            ->where('pagos.tipo_doc', 'fa')
            ->whereDate('pagos.fecha_pago', $fecha)
            ->where('pagos.activo', 1)
            ->whereIn('pagos.id_forma_pago', [2, 5])
            ->where('pagos.referencia_pago', 2)
            ->orderBy('pagos.created_at', 'ASC')
            ->get();

        $operacionesGetnet = $getnetBoletas
            ->concat($getnetFacturas)
            ->sortBy('created_at')
            ->values();

        $total_dia = (float) $operacionesGetnet->sum('monto_getnet');
        $cantidad_operaciones = $operacionesGetnet->count();
        $documentos = collect();

        /*
         * Agrupamos por tipo + id de documento. Una boleta y una factura
         * pueden compartir el mismo id, por eso se incluye el tipo en la clave.
         */
        $operacionesAgrupadas = $operacionesGetnet->groupBy(function ($operacion) {
            return $operacion->tipo_codigo . '_' . $operacion->id_doc;
        });

        foreach ($operacionesAgrupadas as $grupo) {
            $primeraOperacion = $grupo->first();
            $tipoCodigo = $primeraOperacion->tipo_codigo;
            $idDocumento = (int) $primeraOperacion->id_doc;
            $numeroDocumento = $primeraOperacion->num_doc;

            /*
             * Para "Otros medios" se consideran los pagos activos del MISMO
             * documento y del MISMO día. Así el detalle sigue siendo una
             * conciliación diaria y no mezcla cobros efectuados en otras fechas.
             */
            $pagosDocumentoDia = pago::query()
                ->select(
                    'pagos.id',
                    'pagos.monto',
                    'pagos.id_forma_pago',
                    'pagos.referencia_pago',
                    'pagos.referencia',
                    'formapago.formapago'
                )
                ->leftJoin('formapago', 'pagos.id_forma_pago', '=', 'formapago.id')
                ->where('pagos.tipo_doc', $tipoCodigo)
                ->where('pagos.id_doc', $idDocumento)
                ->whereDate('pagos.fecha_pago', $fecha)
                ->where('pagos.activo', 1)
                ->get();

            $otrosMediosAgrupados = [];
            $totalOtrosMedios = 0;

            foreach ($pagosDocumentoDia as $pagoDocumento) {
                $esGetnet = in_array((int) $pagoDocumento->id_forma_pago, [2, 5], true)
                    && (int) $pagoDocumento->referencia_pago === 2;

                if ($esGetnet) {
                    continue;
                }

                $nombreFormaPago = trim((string) $pagoDocumento->formapago);
                if ($nombreFormaPago === '') {
                    $nombreFormaPago = 'Otro medio';
                }

                if (!isset($otrosMediosAgrupados[$nombreFormaPago])) {
                    $otrosMediosAgrupados[$nombreFormaPago] = 0;
                }

                $otrosMediosAgrupados[$nombreFormaPago] += (float) $pagoDocumento->monto;
                $totalOtrosMedios += (float) $pagoDocumento->monto;
            }

            $otrosMedios = [];
            foreach ($otrosMediosAgrupados as $nombre => $monto) {
                $otrosMedios[] = [
                    'nombre' => $nombre,
                    'monto' => round($monto, 2),
                ];
            }

            $montoGetnetDocumento = (float) $grupo->sum('monto_getnet');
            $totalVenta = (float) $primeraOperacion->total_venta;
            $totalPagadoDia = $montoGetnetDocumento + $totalOtrosMedios;
            $diferencia = round($totalVenta - $totalPagadoDia, 2);

            /*
             * Notas de crédito asociadas al documento. Se conserva el mismo
             * formato histórico utilizado por el ERP: bo*NRO... / fa*NRO...
             */
            $notasCreditoQuery = nota_de_credito::query()
                ->where('activo', 1)
                ->where('estado_sii', 'ACEPTADO')
                ->where('docum_referencia', 'LIKE', $tipoCodigo . '*' . $numeroDocumento . '*%')
                ->orderBy('fecha_emision', 'ASC');

            $notasCredito = $notasCreditoQuery
                ->get(['num_nota_credito', 'total'])
                ->map(function ($nc) {
                    return [
                        'numero' => $nc->num_nota_credito,
                        'monto' => (float) $nc->total,
                    ];
                })
                ->values()
                ->all();

            $operaciones = $grupo->map(function ($op) {
                return [
                    'id_pago' => (int) $op->id_pago,
                    'hora' => Carbon::parse($op->created_at)->format('H:i:s'),
                    'monto' => (float) $op->monto_getnet,
                    'referencia' => (string) $op->referencia,
                ];
            })->values()->all();

            $documentos->push([
                'tipo_codigo' => $tipoCodigo,
                'tipo_doc' => $primeraOperacion->tipo_doc,
                'id_doc' => $idDocumento,
                'num_doc' => $numeroDocumento,
                'total_venta' => round($totalVenta, 2),
                'monto_getnet' => round($montoGetnetDocumento, 2),
                'otros_medios' => $otrosMedios,
                'total_otros_medios' => round($totalOtrosMedios, 2),
                'total_pagado_dia' => round($totalPagadoDia, 2),
                'diferencia' => $diferencia,
                'es_pago_mixto' => $montoGetnetDocumento > 0 && $totalOtrosMedios > 0,
                'notas_credito' => $notasCredito,
                'operaciones' => $operaciones,
                'primera_hora' => $grupo->min('created_at'),
            ]);
        }

        $documentos = $documentos
            ->sortBy('primera_hora')
            ->values();

        $cantidad_pagos_mixtos = $documentos
            ->where('es_pago_mixto', true)
            ->count();

        $cantidad_con_nc = $documentos
            ->filter(function ($doc) {
                return count($doc['notas_credito']) > 0;
            })
            ->count();

        $total_notas_credito = round((float) $documentos->sum(function ($doc) {
            return collect($doc['notas_credito'])->sum('monto');
        }), 2);

        $total_neto = round($total_dia - $total_notas_credito, 2);

        return view('reportes.getnet_dia', compact(
            'documentos',
            'fecha',
            'total_dia',
            'cantidad_operaciones',
            'cantidad_pagos_mixtos',
            'cantidad_con_nc',
            'total_notas_credito',
            'total_neto'
        ));
    }

    public function editar_operacion_pago_getnet(Request $request){
        try {
         
            $pago = pago::find($request->id_pago);
            $pago->referencia = $request->referencia;
            $pago->save();
            return "OK";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function transferencias_dia($fecha){
        $dia_bol = pago::selectRaw('CONVERT(pagos.created_at,TIME) as hora, pagos.monto as total, pagos.referencia as referencia, "boleta" as tipo_doc, boletas.num_boleta as num_doc')
            ->join('boletas', 'pagos.id_doc', 'boletas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND pagos.id_forma_pago=?', ['bo', $fecha, 1,4])
            ->orderBy('pagos.created_at', 'ASC')
            ->get();

        $dia_fac = pago::selectRaw('CONVERT(pagos.created_at,TIME)  as hora, pagos.monto as total, pagos.referencia as referencia, "factura" as tipo_doc, facturas.num_factura as num_doc')
            ->join('facturas', 'pagos.id_doc', 'facturas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND pagos.id_forma_pago=?', ['fa', $fecha, 1,4])
            ->orderBy('pagos.created_at', 'ASC')
            ->get();
        
        $dia = ($dia_bol->mergeRecursive($dia_fac))->sortBy('hora');
        $total_dia = $dia->sum('total');
        $v = view('reportes.getnet_dia', compact('dia', 'fecha', 'total_dia'));
        return $v;
    }

    public function imprimir_detalle_dia($fecha){
        
        $filename = 'resumen'.$fecha.'.xls';
        // return Excel::download(new pruebaExports($fecha),$filename);
        return Excel::download(new ventasDiariasExports($fecha),$filename);
       
    }

    public function imprimir_detalle_dia_getnet($fecha){
        
        $filename = 'resumen_getnet'.$fecha.'.xls';
      
        // return Excel::download(new pruebaExports($fecha),$filename);
        return Excel::download(new ventasDiariasExportsGetnet($fecha),$filename);
       
    }

    public function imprimir_repuestos_por_proveedor($id_proveedor){
       
        $filename = 'repuestos_por_proveedor_'.$id_proveedor.'.xls';
      
        return Excel::download(new RepuestosExport($id_proveedor),$filename);
    }

    public function totales($fecha)
    {
        $totales = "DIFERENCIAS:<br><br>";
        $boletas_fecha = boleta::where('fecha_emision', $fecha)
            ->where('activo', 1)
            ->where('estado_sii', 'ACEPTADO')
            ->get();
        $dif_bol = "";
        $bol_sin_pagos = "";
        $bsp = 0;
        foreach ($boletas_fecha as $b) {
            $p = pago::where('id_doc', $b->id)
                ->where('activo', 1)
                ->where('tipo_doc', 'bo')
                ->sum('monto');
            if ($p == 0) {
                $bol_sin_pagos .= $b->num_boleta . " ";
                $bsp += $b->total;
            }
            if (intval($b->total) != intval($p)) {
                $dif_bol .= "total bol " . $b->num_boleta . " : " . intval($b->total) . " total pagos: " . intval($p) . " pagos.id_doc: " . $b->id . "<br>";
            }
        }
        $totales .= $dif_bol . "<br>" . "Boletas sin pagos (crédito): " . $bol_sin_pagos . " (" . $bsp . ")" . "<br><br>";

        $facturas_fecha = factura::where('fecha_emision', $fecha)
            ->where('activo', 1)
            ->where('estado_sii', 'ACEPTADO')
            ->get();

        $dif_fac = "";
        $fac_sin_pagos = "";
        $fsp = 0;
        foreach ($facturas_fecha as $f) {
            $p = pago::where('id_doc', $f->id)
                ->where('activo', 1)
                ->where('tipo_doc', 'fa')
                ->sum('monto');
            if ($p == 0) {
                $fac_sin_pagos .= $f->num_factura . " ";
                $fsp += $f->total;
            }
            if (intval($f->total) != intval($p)) {
                $dif_fac .= "total fac " . $f->num_factura . " : " . intval($f->total) . " total pagos: " . intval($p) . " pagos.id_doc: " . $f->id . "<br>";
            }
        }

        $totales .= $dif_fac . "<br>" . "Facturas sin pagos (crédito): " . $fac_sin_pagos . " (" . $fsp . ")" . "<br><br>";

        $total_boletas = boleta::where('activo', 1)
            ->where('fecha_emision', $fecha)
            ->where('estado_sii', 'ACEPTADO')
            ->sum('total');
        $total_facturas = factura::where('activo', 1)
            ->where('fecha_emision', $fecha)
            ->where('estado_sii', 'ACEPTADO')
            ->sum('total');

        $total_facturas = $total_facturas - $fsp; //restamos las boletas y facturas que no tienen pagos porque son a crédito
        $total_boletas = $total_boletas - $bsp;

        $total = intval($total_boletas) + intval($total_facturas);
        $totales .= "SUMATORIAS:<br>";
        $totales .= "<b>Total: " . $total . "</b><br>";
        $totales .= "<br><b>Total Boletas: " . intval($total_boletas) . "</b><br>";

        $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();

        $boletas_fecha = boleta::select('id')->where('fecha_emision', $fecha)
            ->where('activo', 1)
            ->where('estado_sii', 'ACEPTADO')
            ->get()
            ->toArray();
        $detalle_boletas = "";
        $suma_fp_boletas = 0;
        foreach ($formas_pago as $fp) {

            $p = pago::where('activo', 1)
                ->where('tipo_doc', 'bo')
                ->where('id_forma_pago', $fp->id)
                ->wherein('id_doc', $boletas_fecha)
                ->sum('monto');
            if ($p > 0) {
                $detalle_boletas .= $fp->formapago . ": " . intval($p) . "<br>";
                $suma_fp_boletas += intval($p);
            }
        }
        $dif_boletas = intval($total_boletas) - intval($suma_fp_boletas);
        $totales .= $detalle_boletas;
        $totales .= "Diferencia: " . $dif_boletas . "<br>";

        $totales .= "<br><b>Total Facturas: " . intval($total_facturas) . "</b><br>";

        $facturas_fecha = factura::select('id')->where('fecha_emision', $fecha)
            ->where('activo', 1)
            ->where('estado_sii', 'ACEPTADO')
            ->get()
            ->toArray();
        $detalle_facturas = "";
        $suma_fp_facturas = 0;

        foreach ($formas_pago as $fp) {

            $p = pago::where('activo', 1)
                ->where('tipo_doc', 'fa')
                ->where('id_forma_pago', $fp->id)
                ->wherein('id_doc', $facturas_fecha)
                ->sum('monto');
            if ($p > 0) {
                $detalle_facturas .= $fp->formapago . ": " . intval($p) . "<br>";
                $suma_fp_facturas += intval($p);
            }
        }

        $totales .= $detalle_facturas;
        $dif_facturas = intval($total_facturas) - intval($suma_fp_facturas);
        $totales .= "Diferencia: " . $dif_facturas . "<br>";

        // DETALLE POR USUARIOS
        $totales .= "<br><b>USUARIOS: </b>";

        $usuarios = User::All();
        foreach ($usuarios as $usuario) {

            $ubol = boleta::where('activo', 1)
                ->where('fecha_emision', $fecha)
                ->where('estado_sii', 'ACEPTADO')
                ->where('usuarios_id', $usuario->id)
                ->sum('total');


            $ufac = factura::where('activo', 1)
                ->where('fecha_emision', $fecha)
                ->where('estado_sii', 'ACEPTADO')
                ->where('usuarios_id', $usuario->id)
                ->sum('total');


            $suma_usuario = intval($ubol) + intval($ufac);

            if ($suma_usuario > 0) {
                $ubsp = 0;
                $uboletas_fecha = boleta::select('id', 'total')->where('fecha_emision', $fecha)
                    ->where('activo', 1)
                    ->where('estado_sii', 'ACEPTADO')
                    ->where('usuarios_id', $usuario->id)
                    ->get();

                foreach ($uboletas_fecha as $b) {
                    $p = pago::where('id_doc', $b->id)
                        ->where('activo', 1)
                        ->where('tipo_doc', 'bo')
                        ->where('usuarios_id', $usuario->id)
                        ->sum('monto');
                    if ($p == 0) {
                        $ubsp += $b->total;
                    }
                }

                $ufsp = 0;
                $ufacturas_fecha = factura::select('id', 'total')->where('fecha_emision', $fecha)
                    ->where('activo', 1)
                    ->where('estado_sii', 'ACEPTADO')
                    ->where('usuarios_id', $usuario->id)
                    ->get();
                foreach ($ufacturas_fecha as $f) {
                    $p = pago::where('id_doc', $f->id)
                        ->where('activo', 1)
                        ->where('tipo_doc', 'fa')
                        ->where('usuarios_id', $usuario->id)
                        ->sum('monto');
                    if ($p == 0) {
                        $ufsp += $f->total;
                    }
                }

                $suma_usuario = $suma_usuario - $ubsp - $ufsp;
                $totales .= "<br><br><b>" . $usuario->name . " Total: " . $suma_usuario . "</b>";
                if ($ubol > 0) {
                    $utb = intval($ubol) - intval($ubsp);
                    $totales .= "<br><b>Total Boletas: " . $utb . "</b><br>";
                    $uboletas_fecha = boleta::select('id')
                        ->where('fecha_emision', $fecha)
                        ->where('activo', 1)
                        ->where('estado_sii', 'ACEPTADO')
                        ->where('usuarios_id', $usuario->id)
                        ->get()
                        ->toArray();
                    $udetalle_boletas = "";
                    $usuma_fp_boletas = 0;
                    foreach ($formas_pago as $ufp) {

                        $up = pago::where('activo', 1)
                            ->where('tipo_doc', 'bo')
                            ->where('id_forma_pago', $ufp->id)
                            ->where('usuarios_id', $usuario->id)
                            ->wherein('id_doc', $uboletas_fecha)
                            ->sum('monto');
                        if ($up > 0) {
                            $udetalle_boletas .= $ufp->formapago . ": " . intval($up) . "<br>";
                            $usuma_fp_boletas += intval($up);
                        }
                    }
                    $totales .= $udetalle_boletas;
                }
                if ($ufac > 0) {
                    $utf = intval($ufac) - intval($ufsp);
                    $totales .= "<b>Total Facturas: " . $utf . "</b><br>";
                    $ufacturas_fecha = factura::select('id')
                        ->where('fecha_emision', $fecha)
                        ->where('activo', 1)
                        ->where('estado_sii', 'ACEPTADO')
                        ->where('usuarios_id', $usuario->id)
                        ->get()
                        ->toArray();
                    $udetalle_facturas = "";
                    $usuma_fp_facturas = 0;
                    foreach ($formas_pago as $ufp) {

                        $up = pago::where('activo', 1)
                            ->where('tipo_doc', 'fa')
                            ->where('id_forma_pago', $ufp->id)
                            ->where('usuarios_id', $usuario->id)
                            ->wherein('id_doc', $ufacturas_fecha)
                            ->sum('monto');
                        if ($up > 0) {
                            $udetalle_facturas .= $ufp->formapago . ": " . intval($up) . "<br>";
                            $usuma_fp_facturas += intval($up);
                        }
                    }
                    $totales .= $udetalle_facturas . "<br>";
                }
            }
        }
        $totales .= "<br><br>---FIN ---";
        return $totales;
    }

    public function ventas_online_detalle($fecha){
      try {
        list($mes, $año) = explode("&", $fecha);

        $mes = carrito_virtual::selectRaw('fecha_emision as fecha, count(fecha_emision) as num_oper')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=?',[$mes, $año,0])
                                ->groupBy('fecha_emision')
                                ->orderBy('id', 'ASC')
                                ->get();
        $v = view('reportes.ventas_online_mes', compact('mes'));
        return $v;
      } catch (\Exception $e) {
        return $e->getMessage();
      }
        
    }

    public function ventas_online_dia($fecha){
     
        try {
            $dia = carrito_virtual::where('carrito_virtual.fecha_emision',$fecha)
                                    ->where('carrito_virtual.activo',0)
                                    ->join('compra_transbank','carrito_virtual.numero_carrito','compra_transbank.numero_carrito')
                                    ->get();
           
            $v = view('reportes.ventas_online_dia',compact('dia','fecha'));
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function detalle_carrito_virtual($numero_carrito){
        $detalle = carrito_virtual_detalle::where('carrito_numero',$numero_carrito)
                                            ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                                            ->join('compra_transbank','carrito_virtual_detalle.carrito_numero','compra_transbank.numero_carrito')
                                            ->get();

        $entrega = retiro_tienda::where('numero_carrito',$numero_carrito)->first();
        $despacho = despacho_domicilio::where('numero_carrito',$numero_carrito)->first();

        $carrito = carrito_virtual::where('numero_carrito',$numero_carrito)->first();

        if($entrega){
            $dato = $entrega;
        }

        if($despacho){
            $dato = $despacho;
        }

       
        $v = view('fragm.carrito_virtual_detalle',compact('detalle','dato','carrito'))->render();;
        return $v;
    }

    public function confirmar_envio(Request $req){
        $opcion = $req->opcion;
        // 1 = Despacho domicilio     2 = Retiro tienda
       
        if($opcion == 1){
            $despacho = despacho_domicilio::where('numero_carrito',$req->numero_carrito)->first();
            $despacho->estado = $req->estado;
            $despacho->update();
            return ['OK',$despacho,1];
        }else{
            $retiro = retiro_tienda::where('numero_carrito',$req->numero_carrito)->first();
            $retiro->estado = $req->estado;
            $retiro->update();
            return ['OK',$retiro,2];
        }
        
    }

    public function descontar_stock_carrito_virtual(Request $req){
        try {
            $codigo_interno = $req->codigo_interno;
            $cantidad = $req->cantidad;
            $numero_carrito = $req->numero_carrito;

            $repuesto = repuesto::where('codigo_interno',$codigo_interno)->first();
            $repuesto->stock_actual -= $cantidad;
            $repuesto->update();

            $detalle = carrito_virtual_detalle::where('repuesto_id',$repuesto->id)->where('carrito_numero',$numero_carrito)->first();
            $detalle->estado = 1;

            $detalle->update();

            return ['OK',$repuesto];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function buscar_periodo_stock_minimo($mes, $anio){
        try {
            //code...
            $repuestos_bajo_stock = stock_minimo::select('stock_minimo.fecha_emision','repuestos.codigo_interno','repuestos.id as id_repuesto','repuestos.estado')
                                                    ->join('repuestos','stock_minimo.id_repuesto','repuestos.id')
                                                    ->whereMonth('stock_minimo.fecha_emision',$mes)
                                                    ->whereYear('stock_minimo.fecha_emision',$anio)
                                                    ->groupBy('repuestos.codigo_interno')
                                                    ->get();
            // return $repuestos_bajo_stock;
            return view('fragm.tabla_stock_minimo_fecha',[
                'repuestos' => $repuestos_bajo_stock
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function transferencias(){
        return view('reportes.transferencias');
    }

    /**
     * Busca la última compra válida del repuesto realizada
     * en una fecha anterior o igual a la fecha de venta.
     *
     * Por ahora se utiliza el criterio:
     * "último costo conocido antes de vender".
     */
    private function obtenerCostoHistoricoRepuesto($idRepuesto, $fechaVenta)
    {
        $compra = compras_det::from('compras_det as cd')
            ->select(
                'cd.id',
                'cd.id_repuestos',
                'cd.id_factura_cab',
                'cd.pu',
                'cd.costos',
                'cd.precio_sugerido',
                'cc.factura_fecha',
                'cc.factura_numero',
                'cc.factura_id_proveedor',
                'p.empresa_nombre',
                'p.empresa_nombre_corto'
            )
            ->join(
                'compras_cab as cc',
                'cc.id',
                '=',
                'cd.id_factura_cab'
            )
            ->leftJoin(
                'proveedores as p',
                'p.id',
                '=',
                'cc.factura_id_proveedor'
            )
            ->where('cd.id_repuestos', $idRepuesto)
            ->whereDate('cc.factura_fecha', '<=', $fechaVenta)
            ->where('cd.activo', 1)
            ->where('cc.activo', 1)
            ->orderByDesc('cc.factura_fecha')
            ->orderByDesc('cd.id')
            ->first();

        if (!$compra) {
            return null;
        }

        $precioCompra = (float) $compra->pu;
        $fleteRegistrado = (float) $compra->costos;

        /*
         * El costo utilizado por el informe corresponde exclusivamente
         * al precio unitario de compra guardado en compras_det.pu.
         *
         * El flete, Getnet, PPM y otros porcentajes pueden participar
         * en la formación del precio de venta, pero no se vuelven a
         * sumar al costo ni se descuentan nuevamente de la utilidad.
         */
        $costoUnitario = $precioCompra;

        return [
            'id_compra_detalle'    => $compra->id,
            'id_factura_compra'    => $compra->id_factura_cab,
            'numero_factura'       => $compra->factura_numero,
            'fecha_compra'         => $compra->factura_fecha,
            'id_proveedor'         => $compra->factura_id_proveedor,
            'proveedor'            => $compra->empresa_nombre_corto
                ?: $compra->empresa_nombre,
            'precio_compra'        => round($precioCompra, 2),

            // Dato informativo: no participa en el costo del informe.
            'flete_registrado'     => round($fleteRegistrado, 2),

            // Alias temporal para mantener compatibilidad.
            'flete_unitario'       => round($fleteRegistrado, 2),

            'costo_unitario'       => round($costoUnitario, 2),
            'precio_sugerido'      => (float) $compra->precio_sugerido,
        ];
    }

    public function consultarInformeRentabilidad(Request $request)
    {
        abort_unless($this->confirmaringreso('/reportes/informe-rentabilidad'), 403);
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_termino' => 'required|date|after_or_equal:fecha_inicio',

                'id_vendedor' => 'nullable|integer',
                'id_proveedor' => 'nullable|integer',
                'id_local' => 'nullable|integer',
                'id_repuesto' => 'nullable|integer',
                'comision_getnet_credito' => 'nullable|numeric|min:0|max:100',
                'comision_getnet_debito' => 'nullable|numeric|min:0|max:100',
            ]);

            $fechaInicio = Carbon::parse(
                $request->fecha_inicio
            )->format('Y-m-d');

            $fechaTermino = Carbon::parse(
                $request->fecha_termino
            )->format('Y-m-d');

            /*
            * Ejemplo:
            * PARAM_IVA = 0.19
            * factorIVA = 1.19
            */
            $porcentajeIVA = (float) Session::get('PARAM_IVA', 0.19);
            $factorIVA = 1 + $porcentajeIVA;

            /*
            * =====================================================
            * FACTURAS
            * =====================================================
            */
            $queryFacturas = factura_detalle::from('facturas_detalle as fd')
                ->select(
                    DB::raw("'FACTURA' as tipo_documento"),
                    DB::raw("'fa' as codigo_documento"),
                    'f.id as id_documento',
                    'f.num_factura as numero_documento',
                    'f.fecha_emision',
                    'fd.id as id_detalle',
                    'fd.id_repuestos',
                    'fd.id_local',
                    'fd.usuarios_id as id_vendedor',
                    'u.name as vendedor',
                    'r.codigo_interno',
                    'r.descripcion',
                    'r.id_proveedor as id_proveedor_repuesto',
                    'fd.precio_venta',
                    'fd.pu_neto',
                    'fd.cantidad',
                    'fd.subtotal',
                    'fd.descuento',
                    'fd.total'
                )
                ->join('facturas as f', 'f.id', '=', 'fd.id_factura')
                ->join('repuestos as r', 'r.id', '=', 'fd.id_repuestos')
                ->leftJoin('users as u', 'u.id', '=', 'fd.usuarios_id')
                ->whereBetween('f.fecha_emision', [
                    $fechaInicio,
                    $fechaTermino
                ])
                ->where('f.activo', 1)
                ->where('fd.activo', 1)
                ->whereIn('f.estado_sii', [
                    'ACEPTADO',
                    'REPARO'
                ]);

            if ($request->filled('id_vendedor')) {
                $queryFacturas->where(
                    'fd.usuarios_id',
                    $request->id_vendedor
                );
            }

            if ($request->filled('id_local')) {
                $queryFacturas->where(
                    'fd.id_local',
                    $request->id_local
                );
            }

            if ($request->filled('id_repuesto')) {
                $queryFacturas->where(
                    'fd.id_repuestos',
                    $request->id_repuesto
                );
            }

            $ventasFacturas = $queryFacturas->get();

            /*
            * =====================================================
            * BOLETAS
            * =====================================================
            */
            $queryBoletas = boleta_detalle::from('boletas_detalle as bd')
                ->select(
                    DB::raw("'BOLETA' as tipo_documento"),
                    DB::raw("'bo' as codigo_documento"),
                    'b.id as id_documento',
                    'b.num_boleta as numero_documento',
                    'b.fecha_emision',
                    'bd.id as id_detalle',
                    'bd.id_repuestos',
                    'bd.id_local',
                    'bd.usuarios_id as id_vendedor',
                    'u.name as vendedor',
                    'r.codigo_interno',
                    'r.descripcion',
                    'r.id_proveedor as id_proveedor_repuesto',
                    'bd.precio_venta',
                    'bd.pu_neto',
                    'bd.cantidad',
                    'bd.subtotal',
                    'bd.descuento',
                    'bd.total'
                )
                ->join('boletas as b', 'b.id', '=', 'bd.id_boleta')
                ->join('repuestos as r', 'r.id', '=', 'bd.id_repuestos')
                ->leftJoin('users as u', 'u.id', '=', 'bd.usuarios_id')
                ->whereBetween('b.fecha_emision', [
                    $fechaInicio,
                    $fechaTermino
                ])
                ->where('b.activo', 1)
                ->where('bd.activo', 1)
                ->whereIn('b.estado_sii', [
                    'ACEPTADO',
                    'REPARO'
                ]);

            if ($request->filled('id_vendedor')) {
                $queryBoletas->where(
                    'bd.usuarios_id',
                    $request->id_vendedor
                );
            }

            if ($request->filled('id_local')) {
                $queryBoletas->where(
                    'bd.id_local',
                    $request->id_local
                );
            }

            if ($request->filled('id_repuesto')) {
                $queryBoletas->where(
                    'bd.id_repuestos',
                    $request->id_repuesto
                );
            }

            $ventasBoletas = $queryBoletas->get();

            /*
            * Unificamos facturas y boletas.
            */
            $ventas = $ventasFacturas
                ->concat($ventasBoletas)
                ->sortBy('fecha_emision')
                ->values();

            /*
            * Cache para evitar repetir la misma consulta de compra.
            *
            * La clave incluye repuesto + fecha porque el costo histórico
            * puede ser distinto dependiendo del día de la venta.
            */
            $cacheCostos = [];

            $detalleResultado = [];

            $resumen = [
                'venta_bruta' => 0,
                'venta_bruta_facturas' => 0,
                'venta_bruta_boletas' => 0,

                'venta_neta' => 0,
                'venta_neta_facturas' => 0,
                'venta_neta_boletas' => 0,

                'costo_mercaderia' => 0,
                'ganancia_bruta' => 0,
                'margen_porcentaje' => 0,

                'cantidad_unidades' => 0,
                'cantidad_documentos' => 0,
                'cantidad_facturas' => 0,
                'cantidad_boletas' => 0,

                'venta_con_costo' => 0,
                'venta_sin_costo' => 0,
                'cantidad_items_sin_costo' => 0,
                'porcentaje_cobertura_costos' => 0,
            ];

            $resumenVendedores = [];
            $resumenProveedores = [];
            $resumenProductos = [];
            $documentosUnicos = [];

            /*
             * Monto bruto de las líneas que permanecen dentro de los
             * filtros del informe. Se utilizará para asignar de forma
             * proporcional los pagos Getnet cuando un documento contiene
             * productos de distintos proveedores, locales o vendedores.
             */
            $brutoFiltradoPorDocumento = [];

            foreach ($ventas as $venta) {
                $cantidad = (float) $venta->cantidad;
                $precioVenta = (float) $venta->precio_venta;
                $precioNeto = (float) $venta->pu_neto;
                $descuentoBruto = (float) $venta->descuento;
                $totalDetalle = (float) $venta->total;

                /*
                * -------------------------------------------------
                * CALCULAR VENTA NETA
                * -------------------------------------------------
                *
                * Primera opción:
                * usar pu_neto cuando existe.
                *
                * Respaldo:
                * dividir el total del detalle por el factor IVA.
                */
                if ($precioNeto > 0) {
                    $descuentoNeto = $descuentoBruto / $factorIVA;

                    $ventaNeta = (
                        $precioNeto * $cantidad
                    ) - $descuentoNeto;
                } else {
                    if ($totalDetalle > 0) {
                        $ventaNeta = $totalDetalle / $factorIVA;
                    } else {
                        $totalBrutoCalculado = (
                            $precioVenta * $cantidad
                        ) - $descuentoBruto;

                        $ventaNeta = $totalBrutoCalculado / $factorIVA;
                    }
                }

                $ventaNeta = max(0, round($ventaNeta, 2));

                /*
                 * Venta bruta: monto final pagado por el cliente,
                 * incluyendo IVA y descontando rebajas de la línea.
                 */
                if ($totalDetalle > 0) {
                    $ventaBruta = $totalDetalle;
                } else {
                    $ventaBruta = (
                        $precioVenta * $cantidad
                    ) - $descuentoBruto;
                }

                $ventaBruta = max(0, round($ventaBruta, 2));

                /*
                * -------------------------------------------------
                * OBTENER COSTO HISTÓRICO
                * -------------------------------------------------
                */
                $claveCosto = $venta->id_repuestos
                    . '_'
                    . $venta->fecha_emision;

                if (!array_key_exists($claveCosto, $cacheCostos)) {
                    $cacheCostos[$claveCosto] =
                        $this->obtenerCostoHistoricoRepuesto(
                            $venta->id_repuestos,
                            $venta->fecha_emision
                        );
                }

                $costoHistorico = $cacheCostos[$claveCosto];

                /*
                * Si el usuario filtró por proveedor, utilizamos
                * el proveedor real de la compra histórica.
                */
                if ($request->filled('id_proveedor')) {
                    if (
                        !$costoHistorico ||
                        (int) $costoHistorico['id_proveedor']
                            !== (int) $request->id_proveedor
                    ) {
                        continue;
                    }
                }

                $tieneCosto = $costoHistorico !== null;

                $costoUnitario = $tieneCosto
                    ? (float) $costoHistorico['costo_unitario']
                    : null;

                $costoTotal = $tieneCosto
                    ? round($costoUnitario * $cantidad, 2)
                    : null;

                $ganancia = $tieneCosto
                    ? round($ventaNeta - $costoTotal, 2)
                    : null;

                $margen = (
                    $tieneCosto &&
                    $ventaNeta > 0
                )
                    ? round(($ganancia / $ventaNeta) * 100, 2)
                    : null;

                $proveedor = $tieneCosto
                    ? $costoHistorico['proveedor']
                    : 'SIN COSTO HISTÓRICO';

                $idProveedor = $tieneCosto
                    ? $costoHistorico['id_proveedor']
                    : null;

                /*
                * -------------------------------------------------
                * DETALLE
                * -------------------------------------------------
                */
                $detalleResultado[] = [
                    'tipo_documento' => $venta->tipo_documento,
                    'codigo_documento' => $venta->codigo_documento,
                    'id_documento' => $venta->id_documento,
                    'numero_documento' => $venta->numero_documento,
                    'fecha_emision' => $venta->fecha_emision,

                    'id_detalle' => $venta->id_detalle,
                    'id_repuesto' => $venta->id_repuestos,
                    'codigo_interno' => $venta->codigo_interno,
                    'descripcion' => $venta->descripcion,

                    'id_local' => $venta->id_local,
                    'id_vendedor' => $venta->id_vendedor,
                    'vendedor' => $venta->vendedor ?: 'SIN VENDEDOR',

                    'cantidad' => $cantidad,
                    'precio_venta' => round($precioVenta, 2),
                    'precio_neto' => round($precioNeto, 2),
                    'descuento' => round($descuentoBruto, 2),
                    'venta_bruta' => $ventaBruta,
                    'venta_neta' => $ventaNeta,

                    'tiene_costo' => $tieneCosto,
                    'precio_compra' => $tieneCosto
                        ? $costoHistorico['precio_compra']
                        : null,
                    'flete_registrado' => $tieneCosto
                        ? $costoHistorico['flete_registrado']
                        : null,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,

                    'ganancia' => $ganancia,
                    'margen_porcentaje' => $margen,

                    'id_proveedor' => $idProveedor,
                    'proveedor' => $proveedor,

                    'factura_compra' => $tieneCosto
                        ? $costoHistorico['numero_factura']
                        : null,
                    'fecha_compra' => $tieneCosto
                        ? $costoHistorico['fecha_compra']
                        : null,
                    'precio_sugerido' => $tieneCosto
                        ? $costoHistorico['precio_sugerido']
                        : null,
                ];

                /*
                * -------------------------------------------------
                * RESUMEN GENERAL
                * -------------------------------------------------
                */
                $resumen['venta_bruta'] += $ventaBruta;
                $resumen['venta_neta'] += $ventaNeta;
                $resumen['cantidad_unidades'] += $cantidad;

                if ($venta->codigo_documento === 'fa') {
                    $resumen['venta_bruta_facturas'] += $ventaBruta;
                    $resumen['venta_neta_facturas'] += $ventaNeta;
                } else {
                    $resumen['venta_bruta_boletas'] += $ventaBruta;
                    $resumen['venta_neta_boletas'] += $ventaNeta;
                }

                $claveDocumento = $venta->codigo_documento
                    . '_'
                    . $venta->id_documento;

                $documentosUnicos[$claveDocumento] = [
                    'tipo' => $venta->codigo_documento,
                    'id_documento' => (int) $venta->id_documento,
                ];

                /*
                 * El pago se almacena a nivel de documento, mientras que
                 * proveedor/local/repuesto se conocen a nivel de detalle.
                 * Guardamos la porción bruta incluida en el informe para
                 * distribuir posteriormente cada pago en forma proporcional.
                 */
                $brutoDetalleFiltrado = $totalDetalle > 0
                    ? $totalDetalle
                    : max(
                        0,
                        ($precioVenta * $cantidad) - $descuentoBruto
                    );

                if (!isset($brutoFiltradoPorDocumento[$claveDocumento])) {
                    $brutoFiltradoPorDocumento[$claveDocumento] = 0;
                }

                $brutoFiltradoPorDocumento[$claveDocumento]
                    += $brutoDetalleFiltrado;

                if ($tieneCosto) {
                    $resumen['costo_mercaderia'] += $costoTotal;
                    $resumen['ganancia_bruta'] += $ganancia;
                    $resumen['venta_con_costo'] += $ventaNeta;
                } else {
                    $resumen['venta_sin_costo'] += $ventaNeta;
                    $resumen['cantidad_items_sin_costo']++;
                }

                /*
                * -------------------------------------------------
                * RESUMEN POR VENDEDOR
                * -------------------------------------------------
                */
                $idVendedor = $venta->id_vendedor ?: 0;
                $nombreVendedor = $venta->vendedor ?: 'SIN VENDEDOR';

                if (!isset($resumenVendedores[$idVendedor])) {
                    $resumenVendedores[$idVendedor] = [
                        'id_vendedor' => $idVendedor,
                        'vendedor' => $nombreVendedor,
                        'cantidad_unidades' => 0,
                        'venta_neta' => 0,
                        'costo_mercaderia' => 0,
                        'ganancia' => 0,
                        'margen_porcentaje' => 0,
                        'venta_sin_costo' => 0,
                        'documentos' => [],
                        'cantidad_documentos' => 0,
                    ];
                }

                $resumenVendedores[$idVendedor]['cantidad_unidades']
                    += $cantidad;

                $resumenVendedores[$idVendedor]['venta_neta']
                    += $ventaNeta;

                $claveDocumentoVendedor = $venta->codigo_documento
                    . '_' . $venta->id_documento;

                $resumenVendedores[$idVendedor]['documentos']
                    [$claveDocumentoVendedor] = true;

                if ($tieneCosto) {
                    $resumenVendedores[$idVendedor]['costo_mercaderia']
                        += $costoTotal;

                    $resumenVendedores[$idVendedor]['ganancia']
                        += $ganancia;
                } else {
                    $resumenVendedores[$idVendedor]['venta_sin_costo']
                        += $ventaNeta;
                }

                /*
                * -------------------------------------------------
                * RESUMEN POR PROVEEDOR
                * -------------------------------------------------
                */
                $claveProveedor = $idProveedor ?: 0;

                if (!isset($resumenProveedores[$claveProveedor])) {
                    $resumenProveedores[$claveProveedor] = [
                        'id_proveedor' => $idProveedor,
                        'proveedor' => $proveedor,
                        'cantidad_unidades' => 0,
                        'venta_neta' => 0,
                        'costo_mercaderia' => 0,
                        'ganancia' => 0,
                        'margen_porcentaje' => 0,
                    ];
                }

                $resumenProveedores[$claveProveedor]['cantidad_unidades']
                    += $cantidad;

                $resumenProveedores[$claveProveedor]['venta_neta']
                    += $ventaNeta;

                if ($tieneCosto) {
                    $resumenProveedores[$claveProveedor]['costo_mercaderia']
                        += $costoTotal;

                    $resumenProveedores[$claveProveedor]['ganancia']
                        += $ganancia;
                }

                /*
                * -------------------------------------------------
                * RESUMEN POR PRODUCTO
                * -------------------------------------------------
                */
                $idRepuesto = $venta->id_repuestos;

                if (!isset($resumenProductos[$idRepuesto])) {
                    $resumenProductos[$idRepuesto] = [
                        'id_repuesto' => $idRepuesto,
                        'codigo_interno' => $venta->codigo_interno,
                        'descripcion' => $venta->descripcion,
                        'cantidad_vendida' => 0,
                        'venta_bruta' => 0,
                        'venta_neta' => 0,
                        'costo_mercaderia' => 0,
                        'ganancia' => 0,
                        'margen_porcentaje' => 0,
                        'id_proveedor' => $idProveedor,
                        'proveedor' => $proveedor,
                    ];
                }

                $resumenProductos[$idRepuesto]['cantidad_vendida']
                    += $cantidad;

                $resumenProductos[$idRepuesto]['venta_bruta']
                    += $ventaBruta;

                $resumenProductos[$idRepuesto]['venta_neta']
                    += $ventaNeta;

                if ($tieneCosto) {
                    $resumenProductos[$idRepuesto]['costo_mercaderia']
                        += $costoTotal;

                    $resumenProductos[$idRepuesto]['ganancia']
                        += $ganancia;
                }
            }

            /*
            * =====================================================
            * DOCUMENTOS ÚNICOS
            * =====================================================
            */
            foreach ($documentosUnicos as $documento) {
                $resumen['cantidad_documentos']++;

                if ($documento['tipo'] === 'fa') {
                    $resumen['cantidad_facturas']++;
                } else {
                    $resumen['cantidad_boletas']++;
                }
            }

            /*
            * =====================================================
            * CALCULAR MÁRGENES AGRUPADOS
            * =====================================================
            *
            * El margen general se calcula solamente sobre ventas
            * que tienen costo histórico conocido.
            */
            if ($resumen['venta_con_costo'] > 0) {
                $resumen['margen_porcentaje'] = round(
                    (
                        $resumen['ganancia_bruta']
                        / $resumen['venta_con_costo']
                    ) * 100,
                    2
                );
            }

            if ($resumen['venta_neta'] > 0) {
                $resumen['porcentaje_cobertura_costos'] = round(
                    (
                        $resumen['venta_con_costo']
                        / $resumen['venta_neta']
                    ) * 100,
                    2
                );
            }

            foreach ($resumenVendedores as &$vendedor) {
                $ventaConCosto = $vendedor['venta_neta']
                    - $vendedor['venta_sin_costo'];

                if ($ventaConCosto > 0) {
                    $vendedor['margen_porcentaje'] = round(
                        (
                            $vendedor['ganancia']
                            / $ventaConCosto
                        ) * 100,
                        2
                    );
                }

                $vendedor['cantidad_unidades'] = round(
                    $vendedor['cantidad_unidades'],
                    2
                );

                $vendedor['venta_neta'] = round(
                    $vendedor['venta_neta'],
                    2
                );

                $vendedor['costo_mercaderia'] = round(
                    $vendedor['costo_mercaderia'],
                    2
                );

                $vendedor['ganancia'] = round(
                    $vendedor['ganancia'],
                    2
                );

                $vendedor['cantidad_documentos'] = count(
                    $vendedor['documentos']
                );

                unset($vendedor['documentos']);
            }

            unset($vendedor);

            foreach ($resumenProveedores as &$proveedorResumen) {
                if ($proveedorResumen['venta_neta'] > 0) {
                    $proveedorResumen['margen_porcentaje'] = round(
                        (
                            $proveedorResumen['ganancia']
                            / $proveedorResumen['venta_neta']
                        ) * 100,
                        2
                    );
                }

                $proveedorResumen['cantidad_unidades'] = round(
                    $proveedorResumen['cantidad_unidades'],
                    2
                );

                $proveedorResumen['venta_neta'] = round(
                    $proveedorResumen['venta_neta'],
                    2
                );

                $proveedorResumen['costo_mercaderia'] = round(
                    $proveedorResumen['costo_mercaderia'],
                    2
                );

                $proveedorResumen['ganancia'] = round(
                    $proveedorResumen['ganancia'],
                    2
                );
            }

            unset($proveedorResumen);

            foreach ($resumenProductos as &$producto) {
                if ($producto['venta_neta'] > 0) {
                    $producto['margen_porcentaje'] = round(
                        (
                            $producto['ganancia']
                            / $producto['venta_neta']
                        ) * 100,
                        2
                    );
                }

                $producto['cantidad_vendida'] = round(
                    $producto['cantidad_vendida'],
                    2
                );

                $producto['venta_bruta'] = round(
                    $producto['venta_bruta'],
                    2
                );

                $producto['venta_neta'] = round(
                    $producto['venta_neta'],
                    2
                );

                $producto['costo_mercaderia'] = round(
                    $producto['costo_mercaderia'],
                    2
                );

                $producto['ganancia'] = round(
                    $producto['ganancia'],
                    2
                );
            }

            unset($producto);


            /*
            * =====================================================
            * GETNET Y COMISIONES
            * =====================================================
            *
            * Los pagos se registran a nivel de boleta/factura, pero los
            * filtros de proveedor, local, vendedor y repuesto operan sobre
            * las líneas del documento. Para evitar descontar la comisión
            * Getnet completa de un documento mixto, cada pago se asigna en
            * proporción al monto bruto de las líneas incluidas en el informe.
            */
            $comisionCredito = (float) $request->input(
                'comision_getnet_credito',
                0
            );

            $comisionDebito = (float) $request->input(
                'comision_getnet_debito',
                0
            );

            $idsFacturasFiltradas = [];
            $idsBoletasFiltradas = [];

            foreach ($documentosUnicos as $documento) {
                if ($documento['tipo'] === 'fa') {
                    $idsFacturasFiltradas[] = $documento['id_documento'];
                } else {
                    $idsBoletasFiltradas[] = $documento['id_documento'];
                }
            }

            $brutoTotalPorDocumento = [];

            if (count($idsFacturasFiltradas) > 0) {
                $totalesFacturas = factura_detalle::from('facturas_detalle as fd')
                    ->select(
                        'fd.id_factura as id_documento',
                        DB::raw('SUM(fd.total) as total_bruto')
                    )
                    ->whereIn('fd.id_factura', $idsFacturasFiltradas)
                    ->where('fd.activo', 1)
                    ->groupBy('fd.id_factura')
                    ->get();

                foreach ($totalesFacturas as $totalFactura) {
                    $brutoTotalPorDocumento[
                        'fa_' . $totalFactura->id_documento
                    ] = (float) $totalFactura->total_bruto;
                }
            }

            if (count($idsBoletasFiltradas) > 0) {
                $totalesBoletas = boleta_detalle::from('boletas_detalle as bd')
                    ->select(
                        'bd.id_boleta as id_documento',
                        DB::raw('SUM(bd.total) as total_bruto')
                    )
                    ->whereIn('bd.id_boleta', $idsBoletasFiltradas)
                    ->where('bd.activo', 1)
                    ->groupBy('bd.id_boleta')
                    ->get();

                foreach ($totalesBoletas as $totalBoleta) {
                    $brutoTotalPorDocumento[
                        'bo_' . $totalBoleta->id_documento
                    ] = (float) $totalBoleta->total_bruto;
                }
            }

            $queryPagosFiltrados = pago::query()
                ->whereBetween('fecha_pago', [
                    $fechaInicio,
                    $fechaTermino
                ])
                ->where('activo', 1)
                ->where(function ($query) use (
                    $idsFacturasFiltradas,
                    $idsBoletasFiltradas
                ) {
                    if (count($idsFacturasFiltradas) > 0) {
                        $query->orWhere(function ($subQuery) use (
                            $idsFacturasFiltradas
                        ) {
                            $subQuery->where('tipo_doc', 'fa')
                                ->whereIn('id_doc', $idsFacturasFiltradas);
                        });
                    }

                    if (count($idsBoletasFiltradas) > 0) {
                        $query->orWhere(function ($subQuery) use (
                            $idsBoletasFiltradas
                        ) {
                            $subQuery->where('tipo_doc', 'bo')
                                ->whereIn('id_doc', $idsBoletasFiltradas);
                        });
                    }
                });

            $pagosPeriodo = count($documentosUnicos) > 0
                ? $queryPagosFiltrados->get()
                : collect();

            $totalPagosPeriodo = 0;
            $totalGetnetCredito = 0;
            $totalGetnetDebito = 0;
            $operacionesGetnet = 0;
            $operacionesGetnetCredito = 0;
            $operacionesGetnetDebito = 0;

            foreach ($pagosPeriodo as $pagoPeriodo) {
                $claveDocumentoPago = $pagoPeriodo->tipo_doc
                    . '_' . $pagoPeriodo->id_doc;

                $brutoFiltrado = (float) (
                    $brutoFiltradoPorDocumento[$claveDocumentoPago] ?? 0
                );

                $brutoDocumento = (float) (
                    $brutoTotalPorDocumento[$claveDocumentoPago] ?? 0
                );

                $proporcionFiltro = $brutoDocumento > 0
                    ? min(1, max(0, $brutoFiltrado / $brutoDocumento))
                    : 0;

                $montoAsignado = round(
                    (float) $pagoPeriodo->monto * $proporcionFiltro,
                    2
                );

                $totalPagosPeriodo += $montoAsignado;

                $esGetnet = (int) $pagoPeriodo->referencia_pago === 2
                    && in_array(
                        (int) $pagoPeriodo->id_forma_pago,
                        [2, 5],
                        true
                    );

                if (!$esGetnet || $montoAsignado <= 0) {
                    continue;
                }

                $operacionesGetnet++;

                if ((int) $pagoPeriodo->id_forma_pago === 2) {
                    $totalGetnetCredito += $montoAsignado;
                    $operacionesGetnetCredito++;
                } else {
                    $totalGetnetDebito += $montoAsignado;
                    $operacionesGetnetDebito++;
                }
            }

            $totalPagosPeriodo = round($totalPagosPeriodo, 2);
            $totalGetnetCredito = round($totalGetnetCredito, 2);
            $totalGetnetDebito = round($totalGetnetDebito, 2);
            $totalGetnet = round(
                $totalGetnetCredito + $totalGetnetDebito,
                2
            );

            $comisionGetnetCredito = round(
                $totalGetnetCredito * ($comisionCredito / 100),
                2
            );

            $comisionGetnetDebito = round(
                $totalGetnetDebito * ($comisionDebito / 100),
                2
            );

            $comisionGetnetTotal = round(
                $comisionGetnetCredito + $comisionGetnetDebito,
                2
            );

            $resumen['total_pagos_periodo'] = $totalPagosPeriodo;
            $resumen['venta_getnet'] = $totalGetnet;
            $resumen['venta_getnet_credito'] = $totalGetnetCredito;
            $resumen['venta_getnet_debito'] = $totalGetnetDebito;

            $resumen['operaciones_getnet'] = $operacionesGetnet;
            $resumen['operaciones_getnet_credito'] =
                $operacionesGetnetCredito;
            $resumen['operaciones_getnet_debito'] =
                $operacionesGetnetDebito;

            $resumen['porcentaje_cobros_getnet'] =
                $totalPagosPeriodo > 0
                    ? round(
                        ($totalGetnet / $totalPagosPeriodo) * 100,
                        2
                    )
                    : 0;

            $resumen['comision_getnet_credito_porcentaje'] =
                $comisionCredito;
            $resumen['comision_getnet_debito_porcentaje'] =
                $comisionDebito;

            $resumen['comision_getnet_credito'] =
                $comisionGetnetCredito;
            $resumen['comision_getnet_debito'] =
                $comisionGetnetDebito;
            $resumen['comision_getnet_total'] =
                $comisionGetnetTotal;

            $resumen['ganancia_despues_getnet'] = round(
                $resumen['ganancia_bruta'] - $comisionGetnetTotal,
                2
            );

            $resumen['margen_despues_getnet'] =
                $resumen['venta_con_costo'] > 0
                    ? round(
                        (
                            $resumen['ganancia_despues_getnet']
                            / $resumen['venta_con_costo']
                        ) * 100,
                        2
                    )
                    : 0;

            /*
            * =====================================================
            * NOTAS DE CREDITO POR USUARIO
            * =====================================================
            *
            * Se informa tanto el usuario que emitio la nota como
            * el vendedor del documento original cuando es posible.
            */
            $queryNotasCredito = nota_de_credito::select(
                    'notas_de_credito.id',
                    'notas_de_credito.num_nota_credito',
                    'notas_de_credito.fecha_emision',
                    'notas_de_credito.total',
                    'notas_de_credito.docum_referencia',
                    'notas_de_credito.motivo_correccion',
                    'notas_de_credito.usuarios_id',
                    'users.name as usuario_emisor'
                )
                ->leftJoin(
                    'users',
                    'users.id',
                    '=',
                    'notas_de_credito.usuarios_id'
                )
                ->whereBetween(
                    'notas_de_credito.fecha_emision',
                    [$fechaInicio, $fechaTermino]
                )
                ->where('notas_de_credito.activo', 1)
                ->whereIn(
                    'notas_de_credito.estado_sii',
                    ['ACEPTADO', 'REPARO']
                );

            $notasCredito = $queryNotasCredito->get();
            $detalleNotasCredito = [];
            $notasPorEmisor = [];
            $notasPorVendedorOriginal = [];

            foreach ($notasCredito as $nota) {
                $tipoReferencia = null;
                $numeroReferencia = null;
                $idVendedorOriginal = null;
                $vendedorOriginal = 'NO DETERMINADO';

                $partesReferencia = explode(
                    '*',
                    (string) $nota->docum_referencia
                );

                if (count($partesReferencia) >= 2) {
                    $tipoReferencia = trim($partesReferencia[0]);
                    $numeroReferencia = trim($partesReferencia[1]);

                    if ($tipoReferencia === 'bo') {
                        $documentoOriginal = boleta::select(
                                'boletas.id',
                                'boletas.usuarios_id',
                                'users.name as vendedor'
                            )
                            ->leftJoin(
                                'users',
                                'users.id',
                                '=',
                                'boletas.usuarios_id'
                            )
                            ->where(
                                'boletas.num_boleta',
                                $numeroReferencia
                            )
                            ->first();
                    } elseif ($tipoReferencia === 'fa') {
                        $documentoOriginal = factura::select(
                                'facturas.id',
                                'facturas.usuarios_id',
                                'users.name as vendedor'
                            )
                            ->leftJoin(
                                'users',
                                'users.id',
                                '=',
                                'facturas.usuarios_id'
                            )
                            ->where(
                                'facturas.num_factura',
                                $numeroReferencia
                            )
                            ->first();
                    } else {
                        $documentoOriginal = null;
                    }

                    if ($documentoOriginal) {
                        // Las ventas se atribuyen al usuario del detalle, que puede
                        // diferir del usuario que registro la cabecera.
                        $esBoleta = $tipoReferencia === 'bo';
                        $vendedoresDetalle = DB::table($esBoleta ? 'boletas_detalle' : 'facturas_detalle')
                            ->where($esBoleta ? 'id_boleta' : 'id_factura', $documentoOriginal->id)
                            ->where('activo', 1)
                            ->whereNotNull('usuarios_id')
                            ->distinct()->pluck('usuarios_id');
                        if ($vendedoresDetalle->count() === 1) {
                            $documentoOriginal->usuarios_id = $vendedoresDetalle->first();
                            $documentoOriginal->vendedor = DB::table('users')
                                ->where('id', $documentoOriginal->usuarios_id)->value('name');
                        }
                        $idVendedorOriginal =
                            $documentoOriginal->usuarios_id;
                        $vendedorOriginal =
                            $documentoOriginal->vendedor
                            ?: 'SIN VENDEDOR';
                    }
                }

                // Filtrar por el vendedor original, no por quien emitio la NC.
                // La venta original puede ser anterior al periodo consultado.
                if ($request->filled('id_vendedor') &&
                    (string) $idVendedorOriginal !== (string) $request->id_vendedor) {
                    continue;
                }

                $idEmisor = $nota->usuarios_id ?: 0;
                $nombreEmisor = $nota->usuario_emisor
                    ?: 'SIN USUARIO';

                if (!isset($notasPorEmisor[$idEmisor])) {
                    $notasPorEmisor[$idEmisor] = [
                        'id_usuario' => $idEmisor,
                        'usuario' => $nombreEmisor,
                        'cantidad_notas_credito' => 0,
                        'monto_notas_credito' => 0,
                        'promedio_nota_credito' => 0,
                    ];
                }

                $notasPorEmisor[$idEmisor]
                    ['cantidad_notas_credito']++;

                $notasPorEmisor[$idEmisor]
                    ['monto_notas_credito'] += (float) $nota->total;

                $claveVendedorOriginal = $idVendedorOriginal ?: 0;

                if (!isset(
                    $notasPorVendedorOriginal[$claveVendedorOriginal]
                )) {
                    $cantidadDocumentosVendedor = isset(
                        $resumenVendedores[$claveVendedorOriginal]
                    )
                        ? $resumenVendedores[$claveVendedorOriginal]
                            ['cantidad_documentos']
                        : 0;

                    $notasPorVendedorOriginal[
                        $claveVendedorOriginal
                    ] = [
                        'id_vendedor' => $idVendedorOriginal,
                        'vendedor' => $vendedorOriginal,
                        'cantidad_documentos' =>
                            $cantidadDocumentosVendedor,
                        'cantidad_notas_credito' => 0,
                        'monto_notas_credito' => 0,
                        'tasa_notas_credito' => 0,
                        'promedio_nota_credito' => 0,
                    ];
                }

                $notasPorVendedorOriginal[$claveVendedorOriginal]
                    ['cantidad_notas_credito']++;

                $notasPorVendedorOriginal[$claveVendedorOriginal]
                    ['monto_notas_credito'] += (float) $nota->total;

                $detalleNotasCredito[] = [
                    'id' => $nota->id,
                    'numero_nota_credito' =>
                        $nota->num_nota_credito,
                    'fecha_emision' => $nota->fecha_emision,
                    'total' => round((float) $nota->total, 2),
                    'usuario_emisor' => $nombreEmisor,
                    'tipo_documento_referencia' =>
                        $tipoReferencia,
                    'numero_documento_referencia' =>
                        $numeroReferencia,
                    'vendedor_documento_original' =>
                        $vendedorOriginal,
                    'motivo_correccion' =>
                        $nota->motivo_correccion,
                ];
            }

            foreach ($notasPorEmisor as &$resumenEmisor) {
                $cantidadNotas = $resumenEmisor[
                    'cantidad_notas_credito'
                ];

                $resumenEmisor['monto_notas_credito'] = round(
                    $resumenEmisor['monto_notas_credito'],
                    2
                );

                $resumenEmisor['promedio_nota_credito'] =
                    $cantidadNotas > 0
                        ? round(
                            $resumenEmisor['monto_notas_credito']
                            / $cantidadNotas,
                            2
                        )
                        : 0;
            }

            unset($resumenEmisor);

            foreach (
                $notasPorVendedorOriginal
                as &$resumenVendedorNota
            ) {
                $cantidadNotas = $resumenVendedorNota[
                    'cantidad_notas_credito'
                ];

                $cantidadDocumentos = $resumenVendedorNota[
                    'cantidad_documentos'
                ];

                $resumenVendedorNota['monto_notas_credito'] =
                    round(
                        $resumenVendedorNota[
                            'monto_notas_credito'
                        ],
                        2
                    );

                $resumenVendedorNota['promedio_nota_credito'] =
                    $cantidadNotas > 0
                        ? round(
                            $resumenVendedorNota[
                                'monto_notas_credito'
                            ] / $cantidadNotas,
                            2
                        )
                        : 0;

                $resumenVendedorNota['tasa_notas_credito'] =
                    $cantidadDocumentos > 0
                        ? round(
                            (
                                $cantidadNotas
                                / $cantidadDocumentos
                            ) * 100,
                            2
                        )
                        : 0;
            }

            unset($resumenVendedorNota);

            $notasCreditoResumen = [
                'cantidad_total' => count($detalleNotasCredito),
                'monto_total' => round(
                    (float) array_sum(array_column($detalleNotasCredito, 'total')),
                    2
                ),
                'por_emisor' => collect($notasPorEmisor)
                    ->sortByDesc('cantidad_notas_credito')
                    ->values(),
                'por_vendedor_original' =>
                    collect($notasPorVendedorOriginal)
                        ->sortByDesc('tasa_notas_credito')
                        ->values(),
                'detalle' => $detalleNotasCredito,
            ];

            /*
            * Redondeo final del resumen.
            */
            foreach ([
                'venta_bruta',
                'venta_bruta_facturas',
                'venta_bruta_boletas',
                'venta_neta',
                'venta_neta_facturas',
                'venta_neta_boletas',
                'costo_mercaderia',
                'ganancia_bruta',
                'venta_con_costo',
                'venta_sin_costo'
            ] as $campo) {
                $resumen[$campo] = round($resumen[$campo], 2);
            }

            /*
            * Ordenamos los resultados.
            */
            $vendedoresOrdenados = collect($resumenVendedores)
                ->sortByDesc('venta_neta')
                ->values();

            $proveedoresOrdenados = collect($resumenProveedores)
                ->sortByDesc('venta_neta')
                ->values();

            $productosOrdenados = collect($resumenProductos)
                ->sortByDesc('cantidad_vendida')
                ->values();

            return response()->json([
                'estado' => 1,
                'mensaje' => 'Informe generado correctamente.',

                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_termino' => $fechaTermino,
                    'id_vendedor' => $request->id_vendedor,
                    'id_proveedor' => $request->id_proveedor,
                    'id_local' => $request->id_local,
                    'id_repuesto' => $request->id_repuesto,
                    'comision_getnet_credito' => $comisionCredito,
                    'comision_getnet_debito' => $comisionDebito,
                ],

                'resumen' => $resumen,
                'vendedores' => $vendedoresOrdenados,
                'proveedores' => $proveedoresOrdenados,
                'productos' => $productosOrdenados,
                'notas_credito' => $notasCreditoResumen,
                'detalle' => $detalleResultado,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Debe revisar los filtros ingresados.',
                'errores' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error informe de rentabilidad', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);

            return response()->json([
                'estado' => 0,
                'mensaje' => 'No fue posible generar el informe.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function infoRepuestoRentabilidad(Request $request, $id)
    {
        abort_unless($this->confirmaringreso('/reportes/informe-rentabilidad'), 403);
        $request->validate(['page' => 'nullable|integer|min:1']);
        $repuesto = repuesto::findOrFail($id);

        if (\App\Services\StockPorLocalService::habilitado()) {
            $stocks = \App\RepuestoStock::with(['local', 'responsable'])
                ->where('repuesto_id', $repuesto->id)->orderBy('local_id')->get()
                ->map(function ($stock) {
                    return [
                        'local' => optional($stock->local)->local_nombre ?: 'Local sin identificar',
                        'cantidad' => (int) $stock->cantidad,
                        'ubicacion' => $stock->ubicacion_fisica,
                        'responsable' => optional($stock->responsable)->name,
                        'actualizado' => $stock->updated_at ? $stock->updated_at->format('Y-m-d') : null,
                    ];
                });
        } else {
            $configuracion = [
                ['stock_actual', 'local_id', 'ubicacion', 'id_responsable', 'fecha_actualizacion_stock', 1, 'Bodega'],
                ['stock_actual_dos', 'local_id_dos', 'ubicacion_dos', 'id_responsable_dos', 'fecha_actualizacion_stock_dos', 3, 'Tienda'],
                ['stock_actual_tres', 'local_id_tres', 'ubicacion_tres', 'id_responsable_tres', 'fecha_actualizacion_stock_tres', 4, 'Casa Matriz'],
            ];
            $locales = local::whereIn('id', collect($configuracion)->map(function ($campos) use ($repuesto) {
                return $repuesto->{$campos[1]} ?: $campos[5];
            }))->pluck('local_nombre', 'id');
            $responsables = User::whereIn('id', collect($configuracion)->map(function ($campos) use ($repuesto) {
                return $repuesto->{$campos[3]};
            })->filter())->pluck('name', 'id');
            $stocks = collect($configuracion)->map(function ($campos) use ($repuesto, $locales, $responsables) {
                $localId = $repuesto->{$campos[1]} ?: $campos[5];
                return [
                    'local' => $locales->get($localId, $campos[6]),
                    'cantidad' => (int) $repuesto->{$campos[0]},
                    'ubicacion' => $repuesto->{$campos[2]},
                    'responsable' => $responsables->get($repuesto->{$campos[3]}),
                    'actualizado' => $repuesto->{$campos[4]},
                ];
            });
        }

        $compras = compras_det::query()
            ->join('compras_cab', 'compras_det.id_factura_cab', '=', 'compras_cab.id')
            ->leftJoin('users', 'compras_det.usuarios_id', '=', 'users.id')
            ->leftJoin('proveedores', 'compras_cab.factura_id_proveedor', '=', 'proveedores.id')
            ->leftJoin('locales', 'compras_det.id_local', '=', 'locales.id')
            ->where('compras_det.id_repuestos', $repuesto->id)
            ->select([
                'compras_det.id', 'compras_cab.factura_fecha as fecha',
                'compras_cab.factura_numero as numero', 'users.name as usuario',
                'proveedores.empresa_nombre_corto as proveedor', 'locales.local_nombre as local',
                'compras_det.pu as precio_compra', 'compras_det.precio_sugerido as precio_venta',
                'compras_det.costos', 'compras_det.cantidad',
                'compras_det.activo as detalle_activo', 'compras_cab.activo as compra_activa',
            ])
            ->orderByDesc('compras_cab.factura_fecha')->orderByDesc('compras_det.id')->paginate(20);

        return view('reportes.partials.info_repuesto_rentabilidad', [
            'repuesto' => $repuesto, 'stocks' => $stocks,
            'stockTotal' => $stocks->sum('cantidad'), 'compras' => $compras,
        ]);
    }

    public function informeRentabilidad()
    {
        $confirma = $this->confirmaringreso(
            '/reportes/informe-rentabilidad'
        );

        if (!$confirma) {
            return redirect('home');
        }

        $u = User::select('users.id','users.name','users.activo')
                                    ->leftjoin('permissions_detail','permissions_detail.usuarios_id','users.id')
                                    
                                    ->where('users.role_id',13)
                                    // ->where('id',9)
                                    ->orWhere('users.id',4)
                                    ->orWhere('users.id',5)
                                    ->orWhere('users.id',6)
                                    ->orWhere('users.id',12)
                                    ->orWhere('users.id',316)
                                    // ->orWhere('id',16)
                                    // ->orWhere('users.id',17)
                                    ->orWhere('users.id',34)
                                    // ->orWhere('users.id',72)
                                    ->orWhere('permissions_detail.permission_id',3)
                                    ->where('users.activo', 1)
                                    ->orderBy('users.name')
                                    ->groupBy('users.name')
                                    ->get();
        $vendedores = [];
        foreach($u as $user){
            //Sacamos al usuario jorge Saavedra y Marveisse
            if($user->id !== 9 && $user->id !== 6){
                array_push($vendedores, $user);
            }
        }

        $proveedores = proveedor::where('activo', 1)
            ->where('es_transportista', 0)
            ->orderBy('empresa_nombre_corto')
            ->get();

        $locales = local::where('activo',1)->orderBy('local_nombre')->get();

        return view(
            'reportes.informe_rentabilidad',
            compact(
                'vendedores',
                'proveedores',
                'locales'
            )
        );
    }
}
