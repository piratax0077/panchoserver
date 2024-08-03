<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Debugbar;
use App\boleta;
use App\factura;
use App\nota_de_credito;
use App\nota_de_debito;
use App\pago;
use App\servicios_sii\LibroCompraVenta;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Dte;
use App\servicios_sii\ClsSii;

use Illuminate\Support\Facades\Auth;

class libros_controlador extends Controller
{
    public function libro_ventas(){
        $user = Auth::user();
        if($user->rol->nombrerol !== "Cajer@"){
            return view('libros.ventas');
        }else{
            return redirect('home');
        }
        
    }

    public function libro_ventas_resumen($data){
        list($mes,$año)=explode("&",$data);
        $facturas_suma = factura::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->sum('total');
        $facturas_cuantas = factura::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->count('total');
        $facturas_transbank_suma=pago::whereRaw('tipo_doc=? AND MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?)', ['fa',$mes, $año,1,2,5])
                            ->sum('monto');
        $facturas_transbank_cuantas=pago::whereRaw('tipo_doc=? AND MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?)', ['fa',$mes, $año,1,2,5])
                            ->count('monto');

        $boletas_suma = boleta::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                        ->sum('total');
        $boletas_cuantas = boleta::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->count('total');
        $boletas_transbank_suma=pago::whereRaw('tipo_doc=? AND MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?)', ['bo',$mes, $año,1,2,5])
                            ->sum('monto');
        $boletas_transbank_cuantas=pago::whereRaw('tipo_doc=? AND MONTH(fecha_pago)=? AND YEAR(fecha_pago)=? AND activo=? AND (id_forma_pago=? OR id_forma_pago=?)', ['bo',$mes, $año,1,2,5])
                            ->count('monto');

        $notas_credito_suma = nota_de_credito::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->sum('total');
        $notas_credito_cuantas = nota_de_credito::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->count('total');

        $notas_debito_suma = nota_de_debito::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->sum('total');
        $notas_debito_cuantas = nota_de_debito::whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND (estado_sii=? OR estado_sii=?)', [$mes, $año,1,'ACEPTADO','REPARO'])
                            ->count('total');

        $v=view('libros.ventas_resumen',compact('facturas_suma','facturas_cuantas','facturas_transbank_suma','facturas_transbank_cuantas','boletas_suma','boletas_cuantas','boletas_transbank_suma','boletas_transbank_cuantas','notas_credito_suma','notas_credito_cuantas','notas_debito_suma','notas_debito_cuantas','mes','año'));
        return $v;
    }

    public function libro_ventas_detalle($data){
        list($mes,$año,$tipo_dte)=explode("&",$data);
        $documento="ninguno";
        if($tipo_dte==33){
            $documento="FACTURAS";
            $docus = factura::selectRaw('num_factura as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                ->orderBy('id','ASC')
                                ->get();
        }

        if($tipo_dte==39){
            $documento="BOLETAS";
            $docus = boleta::selectRaw('num_boleta as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                ->orderBy('id','ASC')
                                ->get();
        }

        if($tipo_dte==61){
            $documento="NOTAS DE CRÉDITO";
            $docus = nota_de_credito::selectRaw('num_nota_credito as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                ->orderBy('id','ASC')
                                ->get();
        }

        if($tipo_dte==56){
            $documento="NOTAS DE DÉBITO";
            $docus = nota_de_debito::selectRaw('num_nota_debito as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                ->orderBy('id','ASC')
                                ->get();
        }

        $v=view('libros.ventas_detalle',compact('docus','documento'));
        return $v;
    }

    public function libro_ventas_generar_xml($data){
        $rs=['estado'=>'ERROR','mensaje'=>'YA NO ES NECESARIO...'];
        return json_encode($rs);


        list($mes,$año)=explode("&",$data);

        // caratula del libro
        $caratula = [
            'RutEmisorLibro' => str_replace(".","",Session::get('PARAM_RUT')),
            'RutEnvia' => str_replace(".","",Session::get('PARAM_RUT_ENVIA')),
            'PeriodoTributario' => $año.'-'.$mes,
            'FchResol' => Session::get('PARAM_RESOL_FEC'),
            'NroResol' => intval(Session::get('PARAM_RESOL_NUM')),
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ];

        // datos del emisor
        $Emisor=[
            'RUTEmisor' => str_replace(".","",Session::get('PARAM_RUT')),
            'RznSoc' => Session::get('PARAM_RAZ_SOC'),
            'GiroEmis' => Session::get('PARAM_GIRO'),
            'Acteco' => Session::get('PARAM_GIRO_COD'), //453000 ciuu //503000acteco
            'DirOrigen' => Session::get('PARAM_DOM_MATRIZ').". ".Session::get('PARAM_DIR_CIUDAD'),
            'CmnaOrigen' => Session::get('PARAM_DIR_COMUNA'),
        ];

        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        try {
            $LibroCompraVenta = new LibroCompraVenta();

            //resumen boletas manuales noviembre 2020:
            if($mes==11 && $año==2020){
                $resumen=[];
                $resumen['35']=['TpoDoc'=>35,'TotDoc'=>357,'TotMntNeto'=>9566303,'TotMntIVA'=>1817597,'TotMntTotal'=>11383900];
                $resumen['48']=['TpoDoc'=>48,'TotDoc'=>372,'TotMntNeto'=>3951849,'TotMntIVA'=>750851,'TotMntTotal'=>4702700];
                $LibroCompraVenta->setResumen($resumen);
            }

            $facturas = factura::selectRaw('num_factura as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                    ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                    ->orderBy('num_doc','ASC')
                                    ->get();
            //$ff="";
            if($facturas->count()>0){
                foreach($facturas as $factura){
                    $url=base_path().'/xml/generados/facturas/'.$factura->xml;
                    $archivo=$this->xml_to_array($url);
                    $data=$archivo['SetDTE']['DTE']['Documento'];
                    $data['Encabezado']['Totales']['MntTotal']=0;
                    $data['Encabezado']['Totales']['IVA']=0;
                    $data['Encabezado']['Totales']['MntNeto']=0;
                    //$data['Encabezado']['Totales']['MntTotal']=0;
                    //$ff.=$data['Encabezado']['IdDoc']['Folio']."&".$data['Encabezado']['Totales']['MntNeto']."&".$data['Encabezado']['Totales']['IVA']."&".$data['Encabezado']['Totales']['MntTotal']."<BR>".PHP_EOL;
                    /*
                    $data['Encabezado']['Totales']['MntTotal']=0;
                    $data['Encabezado']['Totales']['IVA']=0;
                    $data['Encabezado']['Totales']['MntNeto']=0;
                    */
                    $DTE=new Dte($data);
                    $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
                }
            }
            //return $ff;


            $boletas = boleta::selectRaw('num_boleta as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                    ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                    ->orderBy('num_doc','ASC')
                                    ->get();


            if($boletas->count()>0){
                foreach($boletas as $boleta){
                    $url=base_path().'/xml/generados/boletas/'.$boleta->xml;
                    $archivo=$this->xml_to_array($url);
                    $data=$archivo['SetDTE']['DTE']['Documento'];
                    $data['Encabezado']['Totales']['MntTotal']=0;
                    $DTE=new Dte($data);
                    $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
                }
            }


            $notas_de_credito = nota_de_credito::selectRaw('num_nota_credito as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                ->orderBy('num_doc','ASC')
                                ->get();

            if($notas_de_credito->count()>0){
                foreach($notas_de_credito as $nc){
                    $url=base_path().'/xml/generados/notas_de_credito/'.$nc->xml;
                    $archivo=$this->xml_to_array($url);
                    $data=$archivo['SetDTE']['DTE']['Documento'];
                    //$data['Encabezado']['Totales']['MntTotal']=0;
                    $data['Encabezado']['Totales']['MntTotal']=0;
                    $data['Encabezado']['Totales']['IVA']=0;
                    $data['Encabezado']['Totales']['MntNeto']=0;
                    $DTE=new Dte($data);
                    $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
                }
            }

            $notas_de_debito = nota_de_debito::selectRaw('num_nota_debito as num_doc, fecha_emision as fecha_doc,total as total_doc, url_xml as xml')
                                    ->whereRaw('MONTH(fecha_emision)=? AND YEAR(fecha_emision)=? AND activo=? AND estado_sii=?', [$mes, $año,1,'ACEPTADO'])
                                    ->orderBy('num_doc','ASC')
                                    ->get();


            if($notas_de_debito->count()>0){
                foreach($notas_de_debito as $nd){
                    $url=base_path().'/xml/generados/notas_de_debito/'.$nd->xml;
                    $archivo=$this->xml_to_array($url);
                    $data=$archivo['SetDTE']['DTE']['Documento'];
                    //$data['Encabezado']['Totales']['MntTotal']=0;
                    $DTE=new Dte($data);
                    $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
                }
            }


/*
            Debugbar::info($LibroCompraVenta->getResumen());
            $libro_generado=$LibroCompraVenta->generar(false);
            //Debugbar::info($libro_generado['EnvioLibro']['ResumenPeriodo']['TotalesPeriodo'][0]['TpoDoc']);
            $nombre="xuxi";
            file_put_contents(base_path().'/xml/generados/libro_ventas/'.$nombre.'.xml', $libro_generado); // guardar XML en sistema de archivos
            $rs=['estado'=>'OK','archivo'=>'xuxi.xml'];
            return json_encode($rs);
*/
            // Objetos de Firma, Folios y EnvioDTE
            $Firma=$this->dame_firma();
            if($Firma===false){
                $rs=['estado'=>'ERROR','mensaje'=>'Error al obtener firma'];
                return json_encode($rs);
            }

            $LibroCompraVenta->setCaratula($caratula);
            $LibroCompraVenta->setFirma($Firma);
            $nombre='libro_ventas_'.$año.'_'.$mes.'_'.intval(microtime(true));
            file_put_contents(base_path().'/xml/generados/libro_ventas/'.$nombre.'.xml', $LibroCompraVenta->generar(false)); // guardar XML en sistema de archivos
            $rs=['estado'=>'OK','archivo'=>$nombre.'.xml'];

        } catch (\Exception $e) {
            $rs=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
        }
        return json_encode($rs);
    } //fin libro_ventas_generar_xml

    public function libro_ventas_enviar_sii($el_xml){
        //ultimo enviado: 0103847582
        $url=base_path().'/xml/generados/libro_ventas/'.$el_xml;
        $xml=file_get_contents($url);
        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT_ENVIA'));
        $RutEmisor= str_replace(".","",Session::get('PARAM_RUT'));
        $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$xml);
        return json_encode($rs);

    }

    public function libro_compras(){
        return view('libros.compras');
    }

    private function xml_to_array($url){
        $xml=simplexml_load_string(file_get_contents($url));
        $json=json_encode($xml);
        return json_decode($json,true);
    }

    private function dame_firma()
    {
        $clave1="pancho1048";
        $clave2="pancho1048";
        $archivo_firma=base_path().'/cert/josetroncoso.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
            return $Firma;
        }else{
            return false;
        }

    }
}
