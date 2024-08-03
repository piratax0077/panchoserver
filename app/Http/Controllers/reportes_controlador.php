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
use App\registro_login;
use App\repuesto;
use App\pago;
use App\User;
use App\local;
use App\permissions_detail;
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
                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $bol->fecha_emision, 'referencia' => '', 'monto' => $bol->total, 'activo' => $bol->activo, 'es_pago' => 0, 'xml' => $bol->url_xml];
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
                                $respuesta_registro = ['orden' => $orden_nivel2, 'documento' => 'Crédito', 'fecha' => $fac->fecha_emision, 'referencia' => '', 'monto' => $fac->total, 'activo' => $fac->activo, 'es_pago' => 0, 'xml' => $fac->url_xml];
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

    public function reporte_pagos_mensuales($fecha){
        try {
         
            $fecha_separada = explode("-",$fecha);
            $year = $fecha_separada[0];
            $mes = $fecha_separada[1];
    
            switch($mes){
                case '01':
                    $nombre_mes = 'ENERO';
                    break;
                case '02':
                    $nombre_mes = 'FEBRERO';
                    break;
                case '03':
                    $nombre_mes = 'MARZO';
                    break;
                case '04':
                    $nombre_mes = 'ABRIL';
                    break;
                case '05':
                    $nombre_mes = 'MAYO';
                    break;
                case '06':
                    $nombre_mes = 'JUNIO';
                    break;
                case '07':
                    $nombre_mes = 'JULIO';
                    break;
                case '08':
                    $nombre_mes = 'AGOSTO';
                    break;
                case '09':
                    $nombre_mes = 'SEPTIEMBRE';
                    break;
                case '10':
                    $nombre_mes = 'OCTUBRE';
                    break;
                case '11':
                    $nombre_mes = 'NOVIEMBRE';
                    break;
                case '12':
                    $nombre_mes = 'DICIEMBRE';
                    break;
            }

            
    
            // $usuarios = User::select('id', 'name')
            //     ->where('activo', 1)
            //     ->where('id',9)
            //     ->orWhere('id',5)
            //     ->orWhere('id',6)
            //     ->orWhere('id',8)
            //     ->orWhere('id',12)
            //     ->orWhere('id',17)
            //     ->orderBy('name')
            //     ->get();
            $fecha_salida_jorge = "2023-03-20";
            $fecha_salida_marveise = "2023-07-14";
            if($fecha > $fecha_salida_marveise){
                $u = User::select('users.id','users.name','users.activo')
                                ->leftjoin('permissions_detail','permissions_detail.usuarios_id','users.id')
                                
                                ->where('users.role_id',13)
                                // ->where('id',9)
                                ->orWhere('users.id',4)
                                ->orWhere('users.id',5)
                                ->orWhere('users.id',6)
                                ->orWhere('users.id',12)
                                // ->orWhere('id',16)
                                ->orWhere('users.id',17)
                                ->orWhere('users.id',34)
                                ->orWhere('users.id',72)
                                ->orWhere('permissions_detail.permission_id',3)
                                ->where('users.activo', 1)
                                ->orderBy('users.name')
                                ->groupBy('users.name')
                                ->get();
            $usuarios = [];
            foreach($u as $user){
                //Sacamos al usuario jorge Saavedra
                if($user->id !== 9){
                    array_push($usuarios, $user);
                }
            }
            }else{
                $usuarios = User::select('users.id','users.name')
                                ->leftjoin('permissions_detail','permissions_detail.usuarios_id','users.id')
                                
                                ->where('users.activo', 1)
                                ->where('users.role_id',13)
                                // ->where('id',9)
                                ->orWhere('users.id',4)
                                ->orWhere('users.id',5)
                                ->orWhere('users.id',6)
                                ->orWhere('users.id',8)
                                ->orWhere('users.id',12)
                                // ->orWhere('id',16)
                                ->orWhere('users.id',17)
                                ->orWhere('users.id',34)
                                ->orWhere('permissions_detail.permission_id',3)
                                ->orderBy('users.name')
                                ->groupBy('users.name')
                                ->get();
            }
    
            $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();
            //$formas_pago: id, formapago
            $fp_array = $formas_pago->toArray();
  
            
            foreach ($usuarios as $usuario) {
                $totales[$usuario->name]['delivery'] = 0;
                foreach ($formas_pago as $forma) {
                    $pago_bol = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                        ->join('boletas', 'pagos.id_doc', 'boletas.id')
                        ->where('pagos.activo', 1)
                        ->whereYear('pagos.fecha_pago',$year)
                        ->whereMonth('pagos.fecha_pago','=', $mes)
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
                    $boletas[$usuario->name][$forma->formapago] = $pago_bol->sum('monto') - $pago_bol_delivery - $pago_bol_resta - $pago_bol_rechazados;
    
    
                    $pago_fac = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                        ->join('facturas', 'pagos.id_doc', 'facturas.id')
                        ->where('pagos.activo', 1)
                        ->whereYear('pagos.fecha_pago', $year)
                        ->whereMonth('pagos.fecha_pago','=',$mes)
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
                    $facturas[$usuario->name][$forma->formapago] = $pago_fac->sum('monto') - $pago_fac_delivery - $pago_fac_resta - $pago_fac_rechazados;
    
                    $totales[$usuario->name][$forma->formapago] = $boletas[$usuario->name][$forma->formapago] + $facturas[$usuario->name][$forma->formapago];
                } //fin forma pago
    
            } //fin usuarios


    
            $notcred = nota_de_credito::select('notas_de_credito.*', 'users.name as usuario', 'users.id as id_user')
                ->where('notas_de_credito.activo', 1)
                ->where('notas_de_credito.estado_sii', 'ACEPTADO')
                ->whereYear('notas_de_credito.fecha_emision',$year)
                ->whereMonth('notas_de_credito.fecha_emision','=', $mes)
                ->join('users', 'notas_de_credito.usuarios_id', 'users.id')
                ->get();
                $notcred_total = 0;
                if ($notcred->count() > 0) {
                    foreach ($notcred as $nc) {
                        list($tipo_doc, $num_doc, $fecha_doc) = explode("*", $nc->docum_referencia);
                        //Sumar solo las NC que son anteriores a la fecha
                        if ($fecha_doc <> $fecha) {
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
                $bol_rech = boleta::select('boletas.id', 'boletas.num_boleta as num_doc', 'boletas.total', 'boletas.resultado_envio as resultado', 'boletas.url_xml as xml', 'boletas.estado_sii', 'boletas.url_pdf', 'users.name as usuario')
                ->join('users', 'boletas.usuarios_id', 'users.id')
                ->where('boletas.activo', 1)
                ->where('boletas.estado_sii', '<>', 'ACEPTADO')
                ->whereYear('boletas.fecha_emision',$year)
                ->whereMonth('boletas.fecha_emision','=', $mes)
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
                ->whereYear('facturas.fecha_emision',$year)
                ->whereMonth('facturas.fecha_emision','=', $mes)
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
        
            $v = view('reportes.ventas_mensuales_resumen', compact('boletas', 'facturas', 'totales', 'formas_pago', 'usuarios', 'notcred', 'notcred_total', 'rechazados', 'delivery_pendientes','nombre_mes','year'));
        
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
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
       
       // Si la fecha es posterior a su destitución no se debe mostrar el usuario Matias Alfaro ni Jorge Saavedra.
       //En caso contrario, si la fecha es anterior a la indicada, se debe mostrar al usuario Matiar Alfaro y Jorge Saavedra.
        
        //Ultima fecha en que se desvinculo un cajero, en este caso fue Marveisse Albarracin.
        $match_date = "2023-07-14";
        if($fecha > $match_date){
            $u = User::select('users.id','users.name','users.activo')
                                ->leftjoin('permissions_detail','permissions_detail.usuarios_id','users.id')
                                
                                ->where('users.role_id',13)
                                // ->where('id',9)
                                ->orWhere('users.id',4)
                                ->orWhere('users.id',5)
                                ->orWhere('users.id',6)
                                ->orWhere('users.id',12)
                                // ->orWhere('id',16)
                                ->orWhere('users.id',17)
                                ->orWhere('users.id',34)
                                ->orWhere('users.id',72)
                                ->orWhere('permissions_detail.permission_id',3)
                                ->where('users.activo', 1)
                                ->orderBy('users.name')
                                ->groupBy('users.name')
                                ->get();
            $usuarios = [];
            foreach($u as $user){
                //Sacamos al usuario jorge Saavedra y Marveisse
                if($user->id !== 9 && $user->id !== 6){
                    array_push($usuarios, $user);
                }
            }
            
        }else{
            $usuarios = User::select('users.id','users.name')
                                ->leftjoin('permissions_detail','permissions_detail.usuarios_id','users.id')
                                ->where('users.activo', 1)
                                ->where('users.role_id',13)
                                // ->where('id',9)
                                ->orWhere('users.id',4)
                                ->orWhere('users.id',5)
                                ->orWhere('users.id',6)
                                ->orWhere('users.id',8)
                                ->orWhere('users.id',12)
                                // ->orWhere('id',16)
                                ->orWhere('users.id',17)
                                ->orWhere('users.id',34)
                                ->orWhere('permissions_detail.permission_id',3)
                                ->orderBy('users.name')
                                ->groupBy('users.name')
                                ->get();
        }

        $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();
        //$formas_pago: id, formapago
        $fp_array = $formas_pago->toArray();

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
                                    
                    $pago_getnet = pago::where('referencia_pago',2)
                                        ->where('activo',1)
                                        ->where('fecha_pago',$fecha)
                                        ->where('usuarios_id',$usuario->id)
                                        ->where('id_forma_pago',$forma->id)
                                        ->get();

                    $getnet[$usuario->name][$forma->formapago] = $pago_getnet->sum('monto');
                    
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
            
            $v = view('reportes.ventas_diarias_resumen', compact('boletas', 'facturas', 'totales', 'formas_pago', 'usuarios', 'notcred', 'notcred_total', 'rechazados', 'delivery_pendientes','abonos','abonos_realizados','getnet','transbank','boletas_a_credito','facturas_a_credito','boletas_credito','facturas_credito'));
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
        if($usuario->rol->nombrerol == "Administrador"){
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
                    
                    $stock = intval($r->stock_actual + $r->stock_actual_dos + $r->stock_actual_tres);
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

                $repuestos_bajo_stock_todos = stock_minimo::select('stock_minimo.fecha_emision','repuestos.codigo_interno','repuestos.id as id_repuesto','repuestos.estado')
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
                    
                    $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_dos) + intval($repuesto->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($b, $r);
                    } 
                }

                $repuestos_bajo_stock = stock_minimo::select('stock_minimo.fecha_emision','repuestos.codigo_interno','repuestos.id as id_repuesto','repuestos.estado')
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
                    
                    $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_dos) + intval($repuesto->stock_actual_tres);
                 
                    // si el stock total es inferior al minimo lo guardamos en el nuevo arreglo
                    if($repuesto->stock_minimo >= $stock_total){
                       array_push($a, $r);
                    } 
                }

                $estados = ['Pedido', 'Sin stock en proveedor', 'Rechazado', 'En curso','Aprobado','Inactivo'];

                $v = view('reportes.bajo_stock',[
                    'proveedores' => $proveedores,
                    'familias' => $familias,
                    'repuestos'=> $a,
                    'repuestos_todos' => $b,
                    'estados' => $estados
                ]);
                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else{
            return redirect('/home');
        }
        
    }

    public function rep_sin_prov(){
        try {
            $pc = new proveedorcontrolador();
            $rc = new repuestocontrolador();

            $proveedores_array = $pc->dame_proveedores();
             
            $proveedores = json_decode($proveedores_array["proveedores"]);
            $familias = $rc->damefamilias();
            $estados = ['Pedido', 'Sin stock en proveedor', 'Rechazado', 'En curso','Aprobado','Inactivo'];
            $repuestos_sin_stock_proveedor = repuesto::select('repuestos.*','familias.nombrefamilia','marcarepuestos.marcarepuesto','proveedores.empresa_nombre_corto')
                                                ->where('repuestos.estado','Sin stock en proveedor')
                                                ->join('proveedores','proveedores.id','repuestos.id_proveedor')
                                                ->join('familias','familias.id','repuestos.id_familia')
                                                ->join('marcarepuestos','marcarepuestos.id','repuestos.id_marca_repuesto')
                                                ->get();
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
        list($mes, $año) = explode("&", $data);
        $mes = pago::selectRaw('fecha_pago as fecha, count(fecha_pago) as num_oper, sum(monto) as total')
            ->whereRaw('MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?) AND referencia_pago=?', [$mes, $año, 1, 2, 5,2])
            ->groupBy('fecha_pago')
            ->orderBy('id', 'ASC')
            ->get();
        $v = view('reportes.getnet_mes', compact('mes'));
        return $v;
    }

    public function getnet_dia($fecha)
    {
        $dia_bol = pago::selectRaw('CONVERT(pagos.created_at,TIME) as hora, pagos.monto as total, pagos.referencia as referencia, "boleta" as tipo_doc, boletas.num_boleta as num_doc')
            ->join('boletas', 'pagos.id_doc', 'boletas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?) AND referencia_pago=?', ['bo', $fecha, 1, 2, 5,2])
            ->orderBy('pagos.created_at', 'ASC')
            ->get();

        $dia_fac = pago::selectRaw('CONVERT(pagos.created_at,TIME)  as hora, pagos.monto as total, pagos.referencia as referencia, "factura" as tipo_doc, facturas.num_factura as num_doc')
            ->join('facturas', 'pagos.id_doc', 'facturas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?) AND referencia_pago=?', ['fa', $fecha, 1, 2, 5,2])
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
}
