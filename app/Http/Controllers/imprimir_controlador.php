<?php

namespace App\Http\Controllers;

use Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use App\User;
use App\abono;
use App\abono_detalle;
use App\carrito_compra;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\boleta;
use App\factura;
use App\cliente_modelo;
use App\cotizacion;
use App\cotizacion_detalle;
use App\consignacion;
use App\consignacion_detalle;
use App\repuesto;
use App\vale_mercaderia;
use App\vale_consignacion;
use App\vale_consignacion_detalle;
use App\oferta_pagina_web;
use App\descuento;
use Mpdf\Mpdf;
use App\servicios_sii\Dte;
use App\servicios_sii\DtePDF;
use App\servicios_sii\EnvioDte;
use App\servicios_sii\File;
use Storage;
use BigFish\PDF417\PDF417; // https://github.com/ihabunek/pdf417-php
use BigFish\PDF417\Renderers\ImageRenderer;
//Revisar también "tecnickcom/tcpdf": "6.2.26" usa libredte
use TCPDF; //Viene como dependencia (require) con sasco/libreDTE
use DateTime;
//Importar CONTROLADOR para las devoluciones
use App\Http\Controllers\guia_despacho_controlador;
/*
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
*/

class ClsCar{
    public $descripcion;
    public $cantidad;
    public $pu;
    public $total_item;
}


class imprimir_controlador extends Controller
{
    //INSTALAR LA IMPRESORA POS CON NOMBRE RPT010
    //Y COMPARTIRLA PARA QUE FIGURE EN LA RED

    private function dametotalcarrito()
    {
        //$total=carrito_compra::where('usuarios_id',Session::get('usuario_id'))->sum('total_item');
        $total=(new carrito_compra)->dame_total(Session::get('usuario_id'));
        return $total;
    }


    private function configurarPDF()
    {
            /* NOTA: Por ahora no uso
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            */

            $mpdf = new Mpdf([
                'mode'=>'utf-8',
                'format'=>[80,250],  //en mm ancho x alto //Configurar la impresora tambien
                'margin_header'=>0,
                'margin_top'=>0,
                'margin_footer'=>0,
                'margin_bottom'=>0,
                'margin_left'=>0,
                'margin_right'=>0,
                'orientation'=>'P',
                ]);

            /* OJO: NO FUNCIONA
            $mpdf->AddFontDirectory(resource_path('mis_letras'));

            $mpdf->fontdata['alfredito']=[
                'R'=>'alfredito.ttf'
            ];
            */

            $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
            $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
            return $mpdf;

    }

    private function configurarPDF_arqueo()
    {
            /* NOTA: Por ahora no uso
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            */

            $mpdf = new Mpdf([
                'mode'=>'utf-8',
                'format'=>[80,160],  //en mm ancho x alto //Configurar la impresora tambien
                'margin_header'=>0,
                'margin_top'=>0,
                'margin_footer'=>0,
                'margin_bottom'=>0,
                'margin_left'=>0,
                'margin_right'=>0,
                'orientation'=>'P',
                ]);

            /* OJO: NO FUNCIONA
            $mpdf->AddFontDirectory(resource_path('mis_letras'));

            $mpdf->fontdata['alfredito']=[
                'R'=>'alfredito.ttf'
            ];
            */

            $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
            $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
            return $mpdf;

    }

    public function configurarPDF_codebar(){
        $mpdf = new Mpdf([
            'mode'=>'utf-8',
            'format'=>[60,40],  //en mm ancho x alto //Configurar la impresora tambien
            'margin_header'=>0,
            'margin_top'=>0,
            'margin_footer'=>0,
            'margin_bottom'=>0,
            'margin_left'=>0,
            'margin_right'=>0,
            'orientation'=>'P',
            ]);

        /* OJO: NO FUNCIONA
        $mpdf->AddFontDirectory(resource_path('mis_letras'));

        $mpdf->fontdata['alfredito']=[
            'R'=>'alfredito.ttf'
        ];
        */

        $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
        $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
        return $mpdf;
    }

    private function configurarPDF_vale(){
        $mpdf = new Mpdf([
            'mode'=>'utf-8',
            'format'=>[80,210],  //en mm ancho x alto //Configurar la impresora tambien
            'margin_header'=>0,
            'margin_top'=>0,
            'margin_footer'=>0,
            'margin_bottom'=>0,
            'margin_left'=>0,
            'margin_right'=>2,
            'orientation'=>'P',
            ]);
            $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
            $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
            return $mpdf;
    }

    private function configurarPDF_devolucion(){
        $mpdf = new Mpdf([
            'mode'=>'utf-8',
            'format'=>[80,100],  //en mm ancho x alto //Configurar la impresora tambien
            'margin_header'=>0,
            'margin_top'=>0,
            'margin_footer'=>0,
            'margin_bottom'=>0,
            'margin_left'=>0,
            'margin_right'=>2,
            'orientation'=>'P',
            ]);
            $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
            $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
            return $mpdf;
    }

    private function configurarPDF_pedido(){
        try {
            $mpdf = new Mpdf([
                'mode'=>'utf-8',
                'format'=>[80,290],  //en mm ancho x alto //Configurar la impresora tambien
                'margin_header'=>0,
                'margin_top'=>0,
                'margin_footer'=>0,
                'margin_bottom'=>0,
                'margin_left'=>0,
                'margin_right'=>0,
                'orientation'=>'P',
                ]);
                $mpdf->SetAuthor("Ing. Francisco Rojo Gallardo");
                $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
                return $mpdf;
        } catch (\Exception $e) {
            return $e->getMessage();
        }         
    }

    private function dame_timbre($tipo_doc,$doc_num)
    {
        $donde=base_path('timbre_electronico');
        if($tipo_doc=='33')
        {
            $archivo=base_path().'/xml/generados/facturas/'.$tipo_doc.'_'.$doc_num.'.xml';
            $imagen_ruta=$donde."\\"."factura_".$doc_num.".jpg";
        }
        if($tipo_doc=='39')
        {
            $archivo=base_path().'/xml/generados/boletas/'.$tipo_doc.'_'.$doc_num.'.xml';
            $imagen_ruta=$donde."\\"."boleta_".$doc_num.".jpg";
        }

        if($tipo_doc=='61')
        {
            $archivo=base_path().'/xml/generados/notas_de_credito/'.$tipo_doc.'_'.$doc_num.'.xml';
            $imagen_ruta=$donde."\\"."notacredito_".$doc_num.".jpg";
        }

        if($tipo_doc=='56')
        {
            $archivo=base_path().'/xml/generados/notas_de_debito/'.$tipo_doc.'_'.$doc_num.'.xml';
            $imagen_ruta=$donde."\\"."notadebito_".$doc_num.".jpg";
        }

        try {
            //Cargar el xml enviado
            $doc_xml=file_get_contents($archivo);
            $DTE=new Dte($doc_xml);
            $timbre_texto=$DTE->getTED();
            //$timbre_texto=htmlentities($timbre_texto); //este muestra el codigo xml purito
            //return $timbre_texto;

            //$h='<TED version="1.0"><DD><RE>5483206-0</RE><TD>33</TD><F>27</F><FE>2020-07-23</FE><RR>60803000-K</RR><RSR>---Servicio de Impuestos Internos</RSR><MNT>126999</MNT><IT1>AMORTIGUADOR TRASERO IZQ DER GAS</IT1><CAF version="1.0"><DA><RE>5483206-0</RE><RS>JUANA EUSEBIA TRONCOSO SANCHEZ</RS><TD>33</TD><RNG><D>1</D><H>50</H></RNG><FA>2020-06-17</FA><RSAPK><M>6YpciGcurx9/v98OIrgAdy062Hk+W8LpNXCO0AHGDVTRQ1tayz9wOrw0mFkIwEMYzUe2t1bIArJNqmj7jRMS9Q==</M><E>Aw==</E></RSAPK><IDK>100</IDK></DA><FRMA algoritmo="SHA1withRSA">wXSrFVHqiC/Ieo/EXOFX3yYTgqAh3lCZKbwFMg4qlYPh0N64HUxJMnisSGN41fGWtllw3imP2qPoi8VtSEO5AA==</FRMA></CAF><TSTED>2020-07-23T19:57:34</TSTED></DD><FRMT algoritmo="SHA1withRSA">4/5y5CalqNvUYZvbSybIPZag9B0HP7A24HiFPCNRDC+KLn4YpUDXDJccif4lDJA6kQ+CPVBVkZopLRrVNrzzUA==</FRMT></TED>';
            //Generar el timbre electrónico

            $pdf417=new PDF417();
            //https://github.com/ihabunek/pdf417-php/blob/master/src/PDF417.php
            $pdf417->setColumns(10); //Por defecto el núm de columnas es de 6 lo que abarca un total de 573 caracteres del texto a codificar
            //Entonces le puse 10 (máximo 30) para que cupiera el texto del TED.
            $datos=$pdf417->encode($timbre_texto);
            $render_img = new ImageRenderer([
                'format' => 'jpg'
            ]);
            $imagen = $render_img->render($datos);
            $imagen->save($imagen_ruta);
            return $imagen_ruta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function es_oferta($id_cotizacion){
        $det_cotizacion=cotizacion_detalle::select('repuestos.id as idrepuesto','repuestos.oferta','cotizaciones_detalle.*','repuestos.id_familia')
        ->where('cotizaciones_detalle.id_cotizacion',$id_cotizacion)
        ->join('repuestos','cotizaciones_detalle.id_repuestos','repuestos.id')
        ->get();        

        foreach($det_cotizacion as $d){
            if($d->oferta == 1){
                $oferta = oferta_pagina_web::where('id_repuesto',$d->idrepuesto)->first();
                $oferta->hasta = Carbon::parse($oferta->hasta)->format("d-m-Y");
                return $oferta;
            }
        }

        foreach ($det_cotizacion as $d) {
            # code...
            $repuesto = repuesto::find($d->idrepuesto);
            $descuento = descuento::where('id_familia',$repuesto->id_familia)->first();

            if($descuento){
                $descuento->desde = Carbon::parse($descuento->desde)->format("d-m-Y");
                $descuento->hasta = Carbon::parse($descuento->hasta)->format("d-m-Y");
                return $descuento;
            }

        }

        return false;
    }

    private function damehtml($vista,$tipo_dte,$doc_num)
    {
        if($tipo_dte=='co'){ //cotizaciones
            $cab_cotizacion=cotizacion::where('num_cotizacion',$doc_num)->first();
            if(!is_null($cab_cotizacion)){

                $det_cotizacion=cotizacion_detalle::where('cotizaciones_detalle.id_cotizacion',$cab_cotizacion->id)
                                ->join('repuestos','cotizaciones_detalle.id_repuestos','repuestos.id')
                                ->get();

                foreach ($det_cotizacion as $det) {
                    # code...
                    $repuesto = repuesto::find($det->id);
                    $descuento = descuento::where('id_familia',$repuesto->id_familia)->first();

                    if($descuento) $det->oferta = 1;
                }

                $oferta = $this->es_oferta($cab_cotizacion->id);
                if($det_cotizacion->count()>0){
                    $id_cliente=$cab_cotizacion->id_cliente;
                    $cliente=cliente_modelo::where('id',$id_cliente)->first();
                    $html_cotizacion=view($vista,compact('cab_cotizacion','det_cotizacion','cliente','oferta'))->render();
                    return $html_cotizacion;
                }else{
                    return false;
                }

            }else{
                return false;
            }

        }


        $d=Session::get('xml');
        switch($tipo_dte){
            case '33':
                $doc=base_path().'/xml/generados/facturas/'.$d;
            break;
            case '39':
                $doc=base_path().'/xml/generados/boletas/'.$d;
            break;
            case '61':
                $doc=base_path().'/xml/generados/notas_de_credito/'.$d;
            break;
            case '56':
                $doc=base_path().'/xml/generados/notas_de_debito/'.$d;
            break;
        }
        //sacamos el detalle del xml
        $dat=file_get_contents($doc);
        $xml=new \SimpleXMLElement($dat, LIBXML_COMPACT);
        $carrito=collect();
        $total=0;
        $total_item=0;
        $neto=0;
        $iva=0;
        $hay_referencia=0;
        $modifica_texto=false;

        if($tipo_dte=='33')
        {
            $id_cliente=factura::where('num_factura',$doc_num)->value('id_cliente');
            $neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
            $iva=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
            $total=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
        }
        if($tipo_dte=='39')
        {
            $id_cliente=boleta::where('num_boleta',$doc_num)->value('id_cliente');
            $neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
            $iva=round($neto*Session::get('PARAM_IVA'),0);
            $total=round($neto*(1+Session::get('PARAM_IVA')),0);
        }
        if($tipo_dte=='61')
        {

            $id_cliente=nota_de_credito::where('num_nota_credito',$doc_num)->value('id_cliente');
            $hay_referencia=1;
            if(strval($xml->SetDTE->DTE->Documento->Referencia->CodRef)!='2'){
                $neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
                $iva=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
                $total=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
            }else{
                $modifica_texto=true;
                $neto=0;
                $iva=0;
                $total=0;
            }
            $referencia_fecha=strval($xml->SetDTE->DTE->Documento->Referencia->FchRef);
            $referencia_folio=strval($xml->SetDTE->DTE->Documento->Referencia->FolioRef);
            $fecha_emision=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
            $referencia="Boleta";
            $doku_dte=strval($xml->SetDTE->DTE->Documento->Referencia->TpoDocRef);
            if($doku_dte=='33') $referencia="Factura";
            //2*Boleta N° 5 Anular todo[111*222*333*444*555] //separar ese string para imprimirlo adecuadamente
            $referencia_motivo=strval($xml->SetDTE->DTE->Documento->Referencia->RazonRef);
            $codigo_motivo=strval($xml->SetDTE->DTE->Documento->Referencia->CodRef);

            $texto_modificado=strval($xml->SetDTE->DTE->Documento->Encabezado->Receptor->RznSocRecep);
            $texto_modificado.="*".strval($xml->SetDTE->DTE->Documento->Encabezado->Receptor->GiroRecep);
            $texto_modificado.="*".strval($xml->SetDTE->DTE->Documento->Encabezado->Receptor->DirRecep);
            $texto_modificado.="*".strval($xml->SetDTE->DTE->Documento->Encabezado->Receptor->CmnaRecep);
            $texto_modificado.="*".strval($xml->SetDTE->DTE->Documento->Encabezado->Receptor->CiudadRecep);

        }
        if($tipo_dte=='56')
        {

            $hay_referencia=1; //del xml sacar
        }

        //FALTA: Guia de despacho



        //Construir el detalle del carrito

        if($modifica_texto==true){
            $oCar=new ClsCar();
            $oCar->descripcion=trim(strval($xml->SetDTE->DTE->Documento->Detalle[0]->NmbItem));
            $oCar->cantidad=0;
            $oCar->total_item=0;
            $oCar->pu=0;
            $carrito->push($oCar);
        }else{
            foreach($xml->SetDTE->DTE->Documento->Detalle as $Det){
                $oCar=new ClsCar();
                $oCar->descripcion=trim(strval($Det->NmbItem));
                $oCar->cantidad=intval($Det->QtyItem);
                $oCar->total_item=round(intval($Det->MontoItem)*(1+Session::get('PARAM_IVA')),0);
                $oCar->pu=round($oCar->total_item/$oCar->cantidad,0);
                $carrito->push($oCar);
            }
        }


        $fecha_emision=(string)$xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis;
        $cliente=cliente_modelo::where('id',$id_cliente)->first();

        $timbre_url=$this->dame_timbre($tipo_dte,$doc_num);
        //FALTA: Poner un IF para saber si se generó correctamente el timbre...
        //este es para boletas y facturas
        if($tipo_dte=='33' || $tipo_dte=='39'){
            $html=view($vista,compact('carrito','total','neto','iva','doc_num','cliente','fecha_emision','hay_referencia','timbre_url'))->render();
        }

        if($tipo_dte=='61'){
            $html=view($vista,compact('carrito','total','neto','iva','doc_num','cliente','fecha_emision','hay_referencia','referencia','referencia_folio','referencia_fecha','referencia_motivo','codigo_motivo','texto_modificado','timbre_url'))->render();
        }
        return $html;
    }

    private function damehtml_arqueo($vista,$cajero_id, $total_boletas, $total_facturas, $total_transbank){
        $cajero = User::find($cajero_id);
        $total_facturas = $total_facturas;
        $total_boletas = $total_boletas;
        $total_transbank = $total_transbank;
        $html_arqueo=view($vista,compact('cajero','total_boletas','total_facturas','total_transbank'))->render();
        return $html_arqueo;
    }

    private function damehtml_vale($vista, $descripcion, $numero_documento, $numero_boucher,$nombre_cliente,$rut,$telefono,$tipo_doc,$valor){
        $html_vale = view($vista, compact('descripcion','numero_documento','numero_boucher','nombre_cliente','valor','rut','telefono','tipo_doc'))->render();
        return $html_vale;
    }

    private function damehtml_vale_resultado($vista, $vale_mercaderia, $detalles_vale_mercaderia){
        //Descontamos el stock de los repuestos solicitados
        $total = 0;
        $vm = $vale_mercaderia;
        try {
            foreach($detalles_vale_mercaderia as $d){
                $total += $d['precio_venta'] * $d['cantidad'];
                $repuesto = repuesto::where('codigo_interno',$d['codigo_interno'])->first();
                if($repuesto->local_id == $d['local_id']) {
                    $repuesto->stock_actual -= $d['cantidad'];
                }elseif($repuesto->local_id_dos == $d['local_id']){
                    $repuesto->stock_actual_dos -= $d['cantidad'];
                }elseif($repuesto->local_id_tres == $d['local_id']){
                    $repuesto->stock_actual_tres -= $d['cantidad'];
                }
                
                 
                 
    
                $repuesto->save();
                
            };
            //Buscamos el vale de mercadería y le restamos el total al valor del vale 
            $vale_mercaderia = vale_mercaderia::where('numero_boucher',$vale_mercaderia['numero_boucher'])->first();
            //Si el valor del vale de mercadería es mayor al total se descuenta del valor del vale de mercadería
            if($total < $vale_mercaderia->valor){
                $vale_mercaderia->valor -= $total;
            }else{
                $vale_mercaderia->valor = 0;
                $vale_mercaderia->activo = 0;
            }
            
            $vale_mercaderia->save();

            $html_vale_resultado = view($vista, compact('vm','detalles_vale_mercaderia'))->render();
            return $html_vale_resultado;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }

    private function damehtml_pedido($vista, $id_abono){
        $abono = abono::find($id_abono);
        $abono_detalle = abono_detalle::select('abono_detalle.*','repuestos.descripcion')
                                        ->where('abono_detalle.id_abono',$id_abono)
                                        ->join('repuestos','abono_detalle.id_repuesto','repuestos.id')
                                        ->get();
        // si abono el campo email es null, entonces se le asigna el email del cliente sinemail@gmail.com
        if($abono->email == null){
            $abono->email = 'sinemail@gmail.com';
        }
        // asignar el atributo hora a abono y se saque del created_at
        $abono->hora = Carbon::parse($abono->created_at)->format("H:i:s");
        $abono->fecha_emision = Carbon::parse($abono->fecha_emision)->format("d-m-Y");
        $html_pedido = view($vista, compact('abono','abono_detalle'))->render();
        return $html_pedido;
    }

    private function damehtml_codebar($vista, $id_repuesto){
        $repuestos = repuesto::select('id','descripcion','codigo_interno','ubicacion','ubicacion_dos','local_id','local_id_dos')->where('id',$id_repuesto)->get();
        $html_codebar= view($vista,compact('repuestos'))->render();
        return $html_codebar;
    }

    private function damehtml_consignacion($vista, $num_consignacion){
try {
        $vale = consignacion::where('num_consignacion',$num_consignacion)->first();
    
        $detalle = consignacion_detalle::select('repuestos.*','consignaciones_detalle.cantidad')
                                            ->join('repuestos','consignaciones_detalle.id_repuestos','repuestos.id')
                                            ->where('consignaciones_detalle.id_consignacion',$vale->id)
                                            ->get();

        foreach($detalle as $repuesto){
            //$repuesto->precio_normal = $repuesto->precio_venta;
            $oferta = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
            $familia_dcto = descuento::where('id_familia',$repuesto->id_familia)->first();
            // si el repuesto esta en oferta se le asigna el precio oferta
            if(isset($oferta)){
                $repuesto->pu = $oferta->precio_actualizado;
                
            }elseif(!isset($oferta) && isset($familia_dcto)){
                $repuesto->pu = $repuesto->precio_venta - (($familia_dcto->porcentaje/100) * $repuesto->precio_venta);
                $repuesto->oferta = 2;
            }else{
                $repuesto->pu = $repuesto->precio_venta;
                $repuesto->oferta = 0;
            }
        }
        
        // saber la hora de la consignación
        $vale->hora = Carbon::parse($vale->created_at)->format("H:i:s");
        
        $html_codebar= view($vista,compact('num_consignacion','vale','detalle'))->render();
        return $html_codebar;
} catch (\Exception $e) {
    return $e->getMessage();
}
        
    }

    private function damehtml_qr($vista){
        //$repuestos = repuesto::select('id','descripcion','codigo_interno','ubicacion','ubicacion_dos','local_id','local_id_dos')->where('id',$id_repuesto)->get();
        $html_codebar= view($vista)->render();
        return $html_codebar;
    }

    public function damehtml_giftcard($vista,$num_abono,$saldo_pendiente, $total){
        $html_giftcard = view($vista,compact('num_abono','saldo_pendiente','total'));
        return $html_giftcard;
    }

    public function damehtml_devolucion($vista,$num_nc){
        $controlador = new guia_despacho_controlador;
        $devoluciones = $controlador->damedevoluciones($num_nc);
        $html_devolucion = view($vista,compact('devoluciones','num_nc'));
        return $html_devolucion;
    }

    public function damehtml_solicitud($vista, $num_solicitud){
        $html_solicitud = view($vista,compact('num_solicitud'));
        return $html_solicitud;
    }

    private function imprimirXML($xml){
        try {
            if($xml!=0){
                $xml_dte=$xml; //desde estadodte.blade
            }else{
                $xml_dte=Session::get('xml'); //desde ventas(bol,fac),nc, nd, gdespa
            }


            if($xml_dte==0){ //
                $estado=['estado'=>'ERROR','mensaje'=>'XML no definido para imprimir o ya fue impreso.'];
                return json_encode($estado);
            }

            $tipo_dte=substr($xml_dte,0,2);
            $num_dte=str_replace(".xml","",substr($xml_dte,3));
            $ruta=$this->donde($tipo_dte,$num_dte);

            $carpeta_xml=$ruta['carpeta_xml'];

            if($carpeta_xml=="00"){
                $estado=['estado'=>'Error Archivo XML','mensaje'=>'No se encontró XML para la impresión ('.$xml_dte.')'];
                return json_encode($estado);
            }


            $xml = base_path().'/xml/generados/'.$carpeta_xml.$xml_dte;
            // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
            $EnvioDte = new EnvioDte();
            $EnvioDte->loadXML(file_get_contents($xml));

            // procesar cada DTEs e ir agregándolo al PDF
            $Caratula = $EnvioDte->getCaratula();
            $Documentos = $EnvioDte->getDocumentos();

            foreach ($Documentos as $DTE) {
                if (!$DTE->getDatos())
                    die('No se pudieron obtener los datos del DTE');

                $pdf = new DtePDF(true); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
                $pdf->setFooterText();

                $pdf->setResolucion(['FchResol'=>$Caratula['FchResol'], 'NroResol'=>$Caratula['NroResol']]);
                $pdf->setCedible(false);
                //$pdf->setLogo('https://intranet.ipschile.cl/storage/imagenes/ips_logo.png');
                $pdf->agregar($DTE->getDatos(), $DTE->getTED());
                $guardar_pdf=$ruta['donde'].$ruta['archivo'];
                $guardar_pdf_public = $ruta['donde_public'].$ruta['archivo'];
                $pdf->Output($guardar_pdf, 'F');
                $pdf->Output($guardar_pdf_public,'F');
                $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];

                return json_encode($estado);
            }
        } catch (\Exception $e) {
            $estado=['estado'=>'Error al imprimir XML','mensaje'=>$e->getMessage()];
            return json_encode($estado);
        }



/*
        // directorio temporal para guardar los PDF
        $dir = sys_get_temp_dir().'/dte_'.$Caratula['RutEmisor'].'_'.$Caratula['RutReceptor'].'_'.str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
        if (is_dir($dir))
            File::rmdir($dir);
        if (!mkdir($dir))
            die('No fue posible crear directorio temporal para DTEs');
*/


        // entregar archivo comprimido que incluirá cada uno de los DTEs
        //File::compress($dir, ['format'=>'zip', 'delete'=>true, 'download'=>false]);



    }

    public function imprimir($xml){
        return $this->imprimirXML($xml);
    }

    public function imprimir_pdf($pdf){
        try {
            $mpdf = new Mpdf();
            $base_pdf=base_path('storage/app/public/pdf/pedidos')."/";
            $base_pdf_public = base_path('public_original/storage/pdf/pedidos')."/";

            $guardar_pdf=$base_pdf.$pdf;
            $guardar_pdf_public=$base_pdf_public.$pdf;
            $pdf_ruta=asset('storage/pdf/pedidos')."/".$pdf;
            // return $pdf_ruta;
            // $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            // $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$pdf_ruta];
            return json_encode($estado);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
    
    public function imprimir_pdf_vale_mercaderia($pdf){
        try {
            $mpdf = new Mpdf();
            $base_pdf=base_path('storage/app/public/pdf/vales_mercaderia')."/";
            $base_pdf_public = base_path('public_original/storage/pdf/vales_mercaderia')."/";

            $guardar_pdf=$base_pdf.$pdf;
            $guardar_pdf_public=$base_pdf_public.$pdf;
            $pdf_ruta=asset('storage/pdf/vales_mercaderia')."/".$pdf;
            // return $pdf_ruta;
            // $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            // $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$pdf_ruta];
            return json_encode($estado);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function imprimir_pdf_vale_consignacion($pdf){
        try {
            $mpdf = new Mpdf();
            $base_pdf=base_path('storage/app/public/pdf/consignaciones')."/";
            $base_pdf_public = base_path('public_original/storage/pdf/consignaciones')."/";

            $guardar_pdf=$base_pdf.$pdf;
            $guardar_pdf_public=$base_pdf_public.$pdf;
            $pdf_ruta=asset('storage/pdf/consignaciones')."/".$pdf;
            // return $pdf_ruta;
            // $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            // $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$pdf_ruta];
            return json_encode($estado);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function imprimir_arqueo(Request $req){
        $cajero_id = $req->cajero_id;
        $total_boletas = $req->total_boletas;
        $total_facturas = $req->total_facturas;
        $total_transbank = $req->total_transbank;
        $ruta=$this->dameParametrosImprimirArqueo();
        try {
            $mpdf = $this->configurarPDF_arqueo();
            $mpdf->SetSubject($ruta['archivo']);
            $html=$this->damehtml_arqueo($ruta['vista'],$cajero_id, $total_boletas, $total_facturas, $total_transbank);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
        
    }

    public function imprimir_vale(Request $req){
        $descripcion = $req->descripcion;
        $numero_documento = intval($req->numero_documento);
        $numero_boucher = intval($req->numero_boucher);
        $nombre_cliente = $req->nombre_cliente;
        $valor = $req->valor;
        $rut_cliente = $req->rut;
        $telefono_cliente = $req->telefono;
        $tipo_doc = $req->tipo_doc;
        
        $ruta=$this->dameParametrosImprimirVale($numero_boucher);
        try {
            $mpdf = $this->configurarPDF_vale();
            $mpdf->SetSubject($ruta['archivo']);
            $html=$this->damehtml_vale($ruta['vista'],$descripcion, $numero_documento, $numero_boucher,$nombre_cliente,$rut_cliente, $telefono_cliente,$tipo_doc,$valor);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
    }

    public function imprimir_vale_resultado(Request $req){
        $vale_mercaderia = $req->vale;
        $detalles_vale_mercaderia = $req->detalles;
        

        $ruta=$this->dameParametrosImprimirValeResultado($vale_mercaderia);
      
        try {
            $mpdf = $this->configurarPDF_vale();
            
            $mpdf->SetSubject($ruta['archivo']);
           
            $html=$this->damehtml_vale_resultado($ruta['vista'],$vale_mercaderia, $detalles_vale_mercaderia);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
    }

    public function imprimir_codebar($id_repuesto){
        $ruta = $this->dameParametrosImprimirCodebar();
        
        try {
            $mpdf = $this->configurarPDF_codebar();
            $mpdf->SetSubject($ruta['archivo']);
            $html=$this->damehtml_codebar($ruta['vista'],$id_repuesto);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public = $ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
    }

    public function imprimir_qr(){
        $ruta = $this->dameParametrosImprimirQr();
        
        try {
            //Ocupo el mismo formato del codebar
            $mpdf = $this->configurarPDF_codebar();
            $mpdf->SetSubject($ruta['archivo']);
            $html=$this->damehtml_qr($ruta['vista']);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public = $ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
    }

    public function imprimir_pedido($id_abono){
        $abono = abono::find($id_abono);
        $ruta = $this->dameParametrosImprimirPedido($abono->num_abono);
        try {
            
            $mpdf = $this->configurarPDF_pedido();
            
            $mpdf->SetSubject($ruta['archivo']);
            
            $html=$this->damehtml_pedido($ruta['vista'],$id_abono);
            
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }

    }

    public function imprimir_cotizacion($num_cotizacion)
    {
        $ruta=$this->donde('co',$num_cotizacion);

        try{
            $mpdf=$this->configurarPDF();
            $mpdf->SetSubject($ruta['archivo']);
            $html=$this->damehtml($ruta['vista'],'co',$num_cotizacion);
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public = $ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,'F');
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
        }catch (\Exception $error){
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];

        }
        return json_encode($estado);
        
    }

    public function imprimir_consignacion($num_consignacion)
    {
      
        try{
            $ruta=$this->donde('con',$num_consignacion);
       
            $mpdf=$this->configurarPDF();
            $mpdf->SetSubject($ruta['archivo']);
            //Cargamos el nombre del archivo a la tabla consignaciones
            $c = consignacion::where('num_consignacion',$num_consignacion)->first();
            $c->url_pdf = $ruta['archivo'];
            $c->save();
            $html=$this->damehtml_consignacion($ruta['vista'],$num_consignacion);
            
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public = $ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,'F');
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
        }catch (\Exception $error){
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];

        }
        return json_encode($estado);
        
    }

    public function giftcard(Request $req){
       $giftcard = $req;
       $num_abono = $giftcard->id_abono;
       $pendiente = $giftcard->pendiente;
       $total = $giftcard->total;
       $ruta = $this->dameParametrosImprimirGiftcard($num_abono);
       
       try {
            
        $mpdf = $this->configurarPDF_codebar();
        
        $mpdf->SetSubject($ruta['archivo']);
        
        $html=$this->damehtml_giftcard($ruta['vista'],$num_abono,$pendiente,$total);
        
        if($html===false){
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
            return json_encode($estado);
        }
        $guardar_pdf=$ruta['donde'].$ruta['archivo'];
        $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
        $mpdf->WriteHTML($html);
        $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
        $mpdf->Output($guardar_pdf_public,"F");
        $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
        return json_encode($estado);
    } catch (\Exception $error) {
        $e=$error->getMessage();
        $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
        return json_encode($estado);
    }
    }

    public function solicitud_traspaso(Request $req){
        $num_solicitud = $req->num_solicitud;
        $ruta = $this->dameParametrosImprimirSolicitud($num_solicitud);

        try {
            
            $mpdf = $this->configurarPDF_codebar();
            
            $mpdf->SetSubject($ruta['archivo']);
            
            $html=$this->damehtml_solicitud($ruta['vista'],$num_solicitud);
            
            if($html===false){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
                return json_encode($estado);
            }
            $guardar_pdf=$ruta['donde'].$ruta['archivo'];
            $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
            $mpdf->WriteHTML($html);
            $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
            $mpdf->Output($guardar_pdf_public,"F");
            $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
            return json_encode($estado);
        } catch (\Exception $error) {
            $e=$error->getMessage();
            $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
            return json_encode($estado);
        }
    }

    private function donde($tipo_dte,$doc_num){
        //funciona para imprimirXML, FALTA: Probar para imprimir solo...
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        switch ($tipo_dte)
        {
            case "co" :
                $donde=$base_pdf."cotizaciones/";
                $donde_public = $base_pdf_public."cotizaciones/";
                $archivo="cotizacion_".$doc_num.".pdf";
                $vista="impresion.cotizacion";
                $carpeta_xml="---";
                $pdf=asset('storage/pdf/cotizaciones')."/".$archivo;
            break;
            case "con":
                $donde=$base_pdf."consignaciones/";
                $donde_public = $base_pdf_public."consignaciones/";
                $archivo="consignacion_".$doc_num.".pdf";
                $vista="impresion.consignacion";
                $carpeta_xml="consignaciones/";
                $pdf=asset('storage/pdf/consignaciones')."/".$archivo;
    
            break;
            case "39" :
                $donde=$base_pdf."boletas/";
                $donde_public = $base_pdf_public."boletas/";
                $archivo="boleta_".$doc_num.".pdf";
                $vista="impresion.boleta";
                $carpeta_xml="boletas/";
                $pdf=asset('storage/pdf/boletas')."/".$archivo;
            break;
            case "33" :
                $donde=$base_pdf."facturas/";
                $donde_public = $base_pdf_public."facturas/";
                $archivo="factura_".$doc_num.".pdf";
                $vista="impresion.factura";
                $carpeta_xml="facturas/";
                $pdf=asset('storage/pdf/facturas')."/".$archivo;
            break;
            case "61" :
                $donde=$base_pdf."notas_credito/";
                $donde_public = $base_pdf_public."notas_credito/";
                $archivo="nota_credito_".$doc_num.".pdf";
                $vista="impresion.nota_credito";
                $carpeta_xml="notas_de_credito/";
                $pdf=asset('storage/pdf/notas_credito')."/".$archivo;
            break;
            case "56" :
                $donde=$base_pdf."notas_debito/";
                $donde_public = $base_pdf_public."notas_debito/";
                $archivo="nota_debito_".$doc_num.".pdf";
                $vista="impresion.nota_debito";
                $carpeta_xml="notas_de_debito/";
                $pdf=asset('storage/pdf/notas_debito')."/".$archivo;
            break;
            case "52" :
                $donde=$base_pdf."guias_despacho/";
                $donde_public = $base_pdf_public."guias_despacho/";
                $archivo="guia_despacho_".$doc_num.".pdf";
                $vista="impresion.guia_despacho";
                $carpeta_xml="guias_de_despacho/";
                $pdf=asset('storage/pdf/guias_despacho')."/".$archivo;
            break;
            
            default:
        }

        $rpta=['donde'=>$donde,'donde_public'=>$donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
        return $rpta;

    }

    public function dameParametrosImprimirArqueo(){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."arqueos/";
            $donde_public = $base_pdf_public."arqueos/";
            $archivo="arqueo_.pdf";
            $vista="impresion.arqueo";
            $carpeta_xml="arqueos/";
            $pdf=asset('storage/pdf/arqueos')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirVale($num_boucher){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."vales_mercaderia/";
            $donde_public = $base_pdf_public."vales_mercaderia/";
            $archivo="vale_mercaderia_".$num_boucher.".pdf";
            $vista="impresion.vale_mercaderia";
            $carpeta_xml="vales_mercaderia/";
            $pdf=asset('storage/pdf/vales_mercaderia')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirValeResultado($vale_mercaderia){
       
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."vales_mercaderia_resultado/";
            $donde_public = $base_pdf_public."vales_mercaderia_resultado/";
            $archivo="vale_mercaderia_resultado_".$vale_mercaderia['numero_boucher'].".pdf";
            $vista="impresion.vale_mercaderia_resultado";
            $carpeta_xml="vales_mercaderia_resultado/";
            $pdf=asset('storage/pdf/vales_mercaderia_resultado')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirCodebar(){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."codigos/";
            $donde_public = $base_pdf_public."codigos/";
            $archivo="codebar_.pdf";
            $vista="impresion.repuesto_codigo";
            $carpeta_xml="codigos/";
            $pdf=asset('storage/pdf/codigos')."/".$archivo;
            
            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirConsignacion($id_vale){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."consignaciones/";
            $donde_public = $base_pdf_public."consignaciones/";
            $archivo="consignacion_".$id_vale.".pdf";
            $vista="impresion.consignacion";
            $carpeta_xml="consignaciones/";
            $pdf=asset('storage/pdf/consignaciones')."/".$archivo;
            
            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirQr(){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."codigos/";
            $donde_public = $base_pdf_public."codigos/";
            $archivo="qr_.pdf";
            $vista="impresion.descuento_qr";
            $carpeta_xml="codigos/";
            $pdf=asset('storage/pdf/codigos')."/".$archivo;
            
            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirGiftcard($num_abono){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."saldopendiente/";
            $donde_public = $base_pdf_public."saldopendiente/";
            $archivo="saldopendiente_".$num_abono.".pdf";
            $vista="impresion.saldopendiente";
            $carpeta_xml="saldospendientes/";
            $pdf=asset('storage/pdf/saldopendiente')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirDevolucion($num_nc){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."devoluciones/";
            $donde_public = $base_pdf_public."devoluciones/";
            $archivo="devolucion_".$num_nc.".pdf";
            $vista="impresion.devolucion";
            $carpeta_xml="devoluciones/";
            $pdf=asset('storage/pdf/devoluciones')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirSolicitud($num_solicitud){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."solicitudes/";
            $donde_public = $base_pdf_public."solicitudes/";
            $archivo="solicitud_".$num_solicitud.".pdf";
            $vista="impresion.solicitud";
            $carpeta_xml="solicitudes/";
            $pdf=asset('storage/pdf/solicitudes')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameParametrosImprimirPedido($num_abono){
        $carpeta_xml="00";
        //$base_pdf=public_path("storage/pdf")."/"; //original
        $base_pdf=base_path('storage/app/public/pdf')."/";
        $base_pdf_public = base_path('public_original/storage/pdf')."/";
        try {
            $donde=$base_pdf."pedidos/";
            $donde_public = $base_pdf_public."pedidos/";
            $archivo="pedido_".$num_abono.".pdf";
            $vista="impresion.pedido";
            $carpeta_xml="pedidos/";
            $pdf=asset('storage/pdf/pedidos')."/".$archivo;

            $rpta=['donde'=>$donde,'donde_public' => $donde_public,'archivo'=>$archivo,'vista'=>$vista,'carpeta_xml'=>$carpeta_xml,'pdf'=>$pdf];
            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function mike42($d)
    {
        try {
            $nombreImpresora = "RPT010";
            $conector1 = new WindowsPrintConnector($nombreImpresora);
            //$conector2 = new FilePrintConnector("php://stdout");
            $impresora=new Printer($conector1);

            $fila="Imprimiendo ".$d."\n";
            $impresora->text($fila);
            $impresora->setJustification(Printer::JUSTIFY_CENTER);
            //$impresora->setJustification(Printer::JUSTIFY_LEFT);
            //$impresora->setJustification(Printer::JUSTIFY_RIGHT);

            //RECTANGULO
            $imagen1=public_path().'/storage/imagenes/rectangulo.png';
            $rectangulo = EscposImage::load($imagen1, false);
            $impresora->bitImage($rectangulo);
            $impresora->feedReverse(5);
            $impresora->setEmphasis(true);
            //$impresora->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
            $impresora->setFont(Printer::FONT_A);
            $impresora->text("R.U.T.: 76.881.221-7\n");
            $impresora->text("FACTURA ELECTRÓNICA\n");
            $numfac="N° "."1234567890\n";
            $impresora->text($numfac);
            $impresora->setEmphasis(false);
            //$impresora->selectPrintMode();
            $impresora->setFont();

            //LOGO
            //$imagen=public_path().'/storage/imagenes/logo_pos.png';
            //$logo = EscposImage::load($imagen, false);
            //$impresora->bitImage($logo);



            $impresora->text("Laravel\n");
            $impresora->setTextSize(2, 2);
            $impresora->text("Pancho App\n");
            $impresora->feed(5);
            $impresora -> cut();

        } finally {
            $impresora -> close();
        }
    }

    public function imprimir_devolucion(Request $req){
        
        $ruta = $this->dameParametrosImprimirDevolucion($req->num_nc);
      
       try {
            
        $mpdf = $this->configurarPDF_devolucion();
        
        $mpdf->SetSubject($ruta['archivo']);
        
        $html=$this->damehtml_devolucion($ruta['vista'],$req->num_nc);
        
        if($html===false){
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
            return json_encode($estado);
        }

        $guardar_pdf=$ruta['donde'].$ruta['archivo'];
        $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
        $mpdf->WriteHTML($html);
        $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
        $mpdf->Output($guardar_pdf_public,"F");
        $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
        return json_encode($estado);
    } catch (\Exception $error) {
        $e=$error->getMessage();
        $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
        return json_encode($estado);
    }
    }

    public function imprimir_consignacion_antigua($id_vale){
        $ruta = $this->dameParametrosImprimirConsignacion($id_vale);
      
       try {
            
        $mpdf = $this->configurarPDF_arqueo();
        
        $mpdf->SetSubject($ruta['archivo']);
        //Cargamos el nombre del archivo a la tabla vale_consignacion
        $vale = consignacion::find($id_vale);
        $vale->url_pdf = $ruta['archivo'];
        $vale->save();
        $html=$this->damehtml_consignacion($ruta['vista'],$id_vale);
        
        if($html===false){
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo generar el PDF'];
            return json_encode($estado);
        }

        $guardar_pdf=$ruta['donde'].$ruta['archivo'];
        $guardar_pdf_public=$ruta['donde_public'].$ruta['archivo'];
        $mpdf->WriteHTML($html);
        $mpdf->Output($guardar_pdf,"F"); // OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser
        $mpdf->Output($guardar_pdf_public,"F");
        $estado=['estado'=>'OK','mensaje'=>$ruta['pdf']];
        return json_encode($estado);
    } catch (\Exception $error) {
        $e=$error->getMessage();
        $estado=['estado'=>'ERROR_IMPRESION','mensaje'=>$e];
        return json_encode($estado);
    }
    }

    public function imprimir_pedido_admin($id_pedido){
        try {
            $pedido = abono::where('id',$id_pedido)->first();
            $detalles = abono_detalle::select('abono_detalle.*','repuestos.descripcion','repuestos.codigo_interno')
                                        ->join('repuestos','abono_detalle.id_repuesto','repuestos.id')
                                        ->where('abono_detalle.id_abono',$id_pedido)
                                        ->get();
            return 'en construccion';
            return $detalles;
            $pdf = PDF::loadView('ventas.imprimir_pedido_admin',['pedido' => $pedido,'detalles' => $detalles]);
            return $pdf->stream('pedido.pdf');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
