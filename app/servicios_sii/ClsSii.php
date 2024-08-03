<?php
namespace App\servicios_sii;
use Debugbar;
use SoapClient;
use SoapParam;
use Session;
use App\correlativo;

/**
 * Esta clase sirve de enlace entre mi sistema y lo de LibreDTE
 *
 */

class ClsSii
{
    const SALUDO="holitas camarones";
    const ARRAY1=['nombre1'=>'pepe'];

    public static function prueba($nombre)
    {
        return "hola ".$nombre;
    }

    public static function generar_xml($Receptor,$Detalle,$Datos)
    {
        $estado=['estado'=>'ERROR_INDEFINIDO','mensaje'=>'HA OCURRIDO UN ERROR NO CONTEMPLADO'];
        $tipo_doc="nada"; //temporalmente despues borrarla
        $base_caf=base_path()."/xml/caf/";
        //buscar en correlativos
        $archivo_caf=\App\correlativo::where("tipo_dte_sii",$Datos['tipo_dte'])->value('caf_xml');
        if(is_null($archivo_caf)){
            $estado=['estado'=>'ERROR_CAF','mensaje'=>'No se encontró archivo CAF en los correlativos'];
            return $estado;
        }
        $folio_documento=$base_caf.$archivo_caf;

        if($Datos['tipo_dte']=='39')
        {
            $documento=FormatosDocSii::dameBoleta($Receptor,$Detalle,$Datos);
            //$folio_documento='/xml/folios/39.xml';
            //$tipo_dte='39';
            $tipo_doc="Boleta";
            $donde_enviado=base_path().'/xml/generados/boletas/';
        }

        if($Datos['tipo_dte']=='33')
        {
            $documento=FormatosDocSii::dameFactura($Receptor,$Detalle,$Datos);
            //$folio_documento='/xml/folios/33.xml';
            $tipo_doc="Factura";
            $donde_enviado=base_path().'/xml/generados/facturas/';
        }

        if($Datos['tipo_dte']=='61')
        {
            $documento=FormatosDocSii::dameNotaCredito($Receptor,$Detalle,$Datos);
            //$folio_documento='/xml/folios/61.xml';
            $tipo_doc="Nota Crédito";
            $donde_enviado=base_path().'/xml/generados/notas_de_credito/';
        }

        if($Datos['tipo_dte']=='52')
        {
            $documento=FormatosDocSii::dameGuiaDespacho($Receptor,$Detalle,$Datos);
            //$folio_documento='/xml/folios/52.xml';
            $tipo_doc="Guía Despacho";
            $donde_enviado=base_path().'/xml/generados/guias_de_despacho/';
        }

        if($Datos['tipo_dte']=='56')
        {
            $documento=FormatosDocSii::dameNotaDebito($Receptor,$Detalle,$Datos);
            //$folio_documento='/xml/folios/56.xml';
            $tipo_doc="Nota Débito";
            $donde_enviado=base_path().'/xml/generados/notas_de_debito/';
        }

        //OJO: El rut receptor en la carátula es el rut a quien se le envía el DTE. en este caso SII 60803000-K
        // Y el rut del cliente va en el encabezado del XML.

        //FALTA: Poner una opción para determinar a quien se le envía el documento, muy distinto al cliente receptor.
        $caratula = [
                'RutEnvia' =>str_replace(".","",Session::get('PARAM_RUT_ENVIA')),
                'RutReceptor' => '60803000-K',
                'FchResol' => Session::get('PARAM_RESOL_FEC'), //https://maullin.sii.cl/cvc_cgi/dte/ee_empresa_rut
                'NroResol' => intval(Session::get('PARAM_RESOL_NUM')),
            ];


        // generar XML del DTE
        $DTE = new Dte($documento);
        //aki... Cómo verificar que se haya construido bien el DTE para
        //en caso de error avisar al usuario. Revisar constructor de Dte.php

        try {
            //$path_folio=base_path().$folio_documento;
            $fff=file_get_contents($folio_documento);
        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR_CAF','mensaje'=>'No se pudo leer el CAF de SII ('.$folio_documento.")"];
            return $estado;
        }

        $Folios = new Folios($fff);
        //FALTA: En el constructor de FOLIOS se verifica la firma del CAF de SII pero no hay forma de retornar error
        //en caso de que esté mal firmado.

        if($DTE->timbrar($Folios)===false) // objeto XML
        {
            $estado=['estado'=>'ERROR_TIMBRAR','mensaje'=>'No se pudo Timbrar el DTE'];
            return $estado;
        }

        $Firma=self::dame_firma();
        if($DTE->firmar($Firma)===false)
        {
            $estado=['estado'=>'ERROR_FIRMA_DTE','mensaje'=>'No se pudo firmar el DTE'];
            return $estado;
        }


        // generar sobre con el envío del DTE
        $EnvioDTE = new EnvioDte();
        if($EnvioDTE->agregar($DTE)===false)
        {
            $estado=['estado'=>'ERROR_AGREGAR_DTE','mensaje'=>'No se pudo agregar DTE al envío'];
            return $estado;
        }


         //Firmando el envío
         $EnvioDTE->setFirma($Firma); //No devuelve nada, herencia desde Envio.php y abstracción en Documento.php

        if($EnvioDTE->setCaratula($caratula)===false)
        {
            $estado=['estado'=>'ERROR_CARATULA','mensaje'=>'No se pudo poner la carátula al envío'];
            return $estado;
        }

        $xml=$EnvioDTE->generar(); // generando xml para enviarlo
        if($xml===false){
            $estado=['estado'=>'ERROR_GENERAR_ENVIO_DTE','mensaje'=>'No se pudo generar el XML EnvioDTE'];
            return $estado;
        }
        //Validar Esquema del Envío
        if ($EnvioDTE->schemaValidate()===false) {
            $estado=['estado'=>'ERROR_ESQUEMA_ENVIODTE','mensaje'=>'Esquema EnvioDTE Inválido'];
            return $estado;
        }


        //Guardar el XML
        $doc=$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml";
        $nombre_archivo_xml=$donde_enviado.$doc;
        if(!file_put_contents($nombre_archivo_xml,$xml))
        {
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo guardar el XML de respuesta SII'];
        }else{
            $estado=['estado'=>'GENERADO','mensaje'=>''.$tipo_doc.' N° '.$Datos['folio_dte']];
        }
        return $estado;

    } // fin de la clase

    public static function enviar_sii($RutEnvia, $RutEmisor,$xml)
    {
        
        $Firma=self::dame_firma();
        $Token=Auto::getToken($Firma);

        if($Token===false){
            $estado=['estado'=>'ERROR_TOKEN','mensaje'=>'SII no me entregó TOKEN de seguridad'];
            return $estado;
        }

        try {
            $resultado = Sii::enviar($RutEnvia, $RutEmisor, $xml, $Token);
           
        } catch (\Exception $e) {
            $estado=['estado'=>'ClsSii_ENVIAR_SII','mensaje'=>$e->getMessage()];
            return $estado;
        }

        if($resultado['estado']!="OK"){
            return $resultado;
        }
        //$estado=['estado'=>'XUXA'.$resultado->STATUS,'mensaje'=>$resultado['mensaje']];

        $resultado=$resultado['mensaje']; //El xml  de respuesta

        // Mostrar resultado del envío
        if ($resultado->STATUS!='0') {
            $status_mensaje_error="Error desconocido en servidor SII";
            if($resultado->STATUS==1) $status_mensaje_error="No tiene permisos para enviar (status=1)";
            if($resultado->STATUS==2) $status_mensaje_error="Tamaño del archivo inapropiado (status=2)";
            if($resultado->STATUS==3) $status_mensaje_error="Archivo cortado (status=3)";
            if($resultado->STATUS==4) $status_mensaje_error="Error 4 sin definir";
            if($resultado->STATUS==5) $status_mensaje_error="No esta autenticado";
            if($resultado->STATUS==6) $status_mensaje_error="Empresa no autorizada a enviar archivos";
            if($resultado->STATUS==7) {
                $status_mensaje_error="Esquema Inválido: ";
                foreach($resultado->DETAIL->ERROR as $error)
                {
                    $status_mensaje_error.=$error;
                }
            }
            if($resultado->STATUS==8) $status_mensaje_error="Documento mal firmado";
            if($resultado->STATUS==9) $status_mensaje_error="Sistema Bloqueado";
            if($resultado->STATUS==99) {
                $status_mensaje_error="Otro: ";
                foreach($resultado->DETAIL->ERROR as $error)
                {
                    $status_mensaje_error.=$error;
                }
            }
            if($resultado->STATUS<0){
                    $status_mensaje_error="Error del servidor SII (-)";
            }
            $estado=['estado'=>'ERROR_STATUS_'.$resultado->STATUS,'mensaje'=>$status_mensaje_error];
        }else{
            $TrackID=strval($resultado->TRACKID);
            $estado=['estado'=>'OK','mensaje'=>'Recibido por el SII','trackid'=>$TrackID];
        }
        return $estado;
    }

    public static function enviar_sii_boleta($RutEnvia, $RutEmisor,$xml)
    {
        $Firma=self::dame_firma();
        $estado=Auto_boletas::getToken($Firma);

        if($estado['estado']=='OK'){
            $Token=$estado['token'];
        }else{
            return $estado;
        }

        try {
            $resultado = Sii_boletas::enviar($RutEnvia, $RutEmisor, $xml, $Token);
        } catch (\Exception $e) {
            $estado=['estado'=>'ClsSii_ENVIAR_SII','mensaje'=>$e->getMessage()];
            return $estado;
        }

        if($resultado['estado']!="REC"){
            $estado=['estado'=>'ERROR_ENVIO','mensaje'=>$resultado['mensaje']];
            return $estado;
        }else{
            $TrackID=strval($resultado['trackid']);
            $estado=['estado'=>'OK','mensaje'=>'Recibido por el SII','trackid'=>$TrackID];
        }
        return $estado;
    }

    public static function revisar_mail_estado($param)
    {
        $c=0;
        do{
            $c++;
            $rs=Imap::revisar_mail($param['trackid'],$param['tipo_dte'],$param['folio_dte']);
            sleep(1);
            if($c>10) break;
        }while(!in_array($rs['estado'],['ACEPTADO','RECHAZADO','REPARO']));
        return $rs;
    }

    public static function dame_firma()
    {
        $clave1="Pan831";
        $clave2="Administrador2023";
        $archivo_firma=base_path().'/cert/josetroncoso2023.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
            return $Firma;
        }else{
            return false;
        }
    }

    public static function dame_token()
    {
        return Auto::getToken(self::dame_firma());
    }

    public static function dame_estado_dteUP($TrackID,$rut,$dv,$Token)
    {
        $args=[
            'Rut'           => $rut,
            'Dv'            => $dv,
            'TrackId'   => $TrackID,
            'Token'      => $Token
        ];
        try {
            $xml=Sii::request('QueryEstUp', 'getEstUp',$args);
            return ($xml);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $xml=Sii::request('QueryEstUp', 'getEstUp',$args);
        return $xml;
    }

    public static function object2array($object) { return @json_decode(@json_encode($object),1); }


    public static function dame_estado_dte($tipodte,$foliodte,$fechadte,$montodte,$Token)
    {
        $wsdl="https://maullin.sii.cl/DTEWS/QueryEstDte.jws?WSDL";
        //$Token=self::dame_token();

        $options['stream_context'] = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        $options = ['cache_wsdl' => WSDL_CACHE_DISK, 'keep_alive' => false];
        $params=[
            'RutConsultante'    => '05483206',
            'DvConsultante'     => '0',
            'RutCompania'       => '05483206',
            'DvCompania'        => '0',
            'RutReceptor'       => '60803000',
            'DvReceptor'        => 'K',
            'TipoDte'           => $tipodte,
            'FolioDte'          => $foliodte,
            'FechaEmisionDte'   => $fechadte, //'06-07-2020',
            'MontoDte'          => $montodte, //228002
            'Token'             => $Token,
        ];

        $t=new SoapClient($wsdl,$options);

        for($i=1;$i<11;$i++)
        {
            try{
                $p=call_user_func_array([$t,'getEstDte'],$params);
            }catch(\Exception $e){
                $p = null;
                usleep(300000); // pausa de 0.3 segundos antes de volver a intentar el envío
            }
        }

        if(is_null($p))
        {
            return "nulo";
        }

        $rr=new \SimpleXMLElement($p);

        return $rr;
    }

    public static function dame_estado()
    {
        $wsdl="https://maullin.sii.cl/DTEWS/QueryEstDte.jws?WSDL";
        //$Token=self::dame_token();
        $t=new SoapClient($wsdl);
        $p=$t->getState();
        $q=simplexml_load_string($p,null,0,"SII",true) or die("carajooooo");


        //Estado y Glosa
        $rpta=$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]." - ".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
        return $rpta;
    }

    public static function dame_estado_dte_libreDTE()
    {
        $Token=self::dame_token();
        //Ver estado del DTE: 003-estadoDte.php de los ejemplos LibreDTE
        $get_estado = Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => '05483206',
            'DvConsultante'     => '0',
            'RutCompania'       => '05483206',
            'DvCompania'        => '0',
            'RutReceptor'       => '60803000',
            'DvReceptor'        => 'K',
            'TipoDte'           => '33',
            'FolioDte'          => '1',
            'FechaEmisionDte'   => '02-07-2020',
            'MontoDte'          => '228002', //228002
            'Token'             => $Token,
        ]);

        return $get_estado;
        //return $get_estado->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];

    }
}


?>
