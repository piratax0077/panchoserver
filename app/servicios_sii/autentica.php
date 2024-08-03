<?php

namespace App\servicios_sii;

use Debugbar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SoapClient;
use SoapParam;
use Session;
use Storage;

class autentica extends Controller
{

    public function entrada()
    {


        //return paso2::aca2(); //para probar llamadas a clases php con métodos estáticos en el namespace especificado
/*
        $sii=base_path().'/app/Http/Controllers/servicios_sii/Sii.php';
        if(is_readable($sii))
        {
            $rpta="SII";
        }
        return $rpta;

        */
        $depurar=false;

        $rpta="Caca";
        $clave1="juana206"; // juana206 //panchorepuestos8311048
        $clave2="panchorepuestos8311048";
        $archivo_firma=base_path().'/cert/juanita_libreDTE.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
            $Token=Auto::getToken($Firma);
            $rpta='Token: '.$Token;
            if($depurar) Debugbar::info($rpta);
        }
        $ndoc=3;
        $factura = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'Folio' => $ndoc,
                ],
                'Emisor' => [
                    'RUTEmisor' => '5483206-0',
                    'RznSoc' => 'JUANA EUSEBIA TRONCOSO SANCHEZ',
                    'GiroEmis' => 'Venta Repuestos Automotrices',
                    'Acteco' => 503000, //453000 ciuu
                    'DirOrigen' => 'Arica',
                    'CmnaOrigen' => 'Arica',
                ],
                'Receptor' => [
                    'RUTRecep' => '60803000-K',
                    'RznSocRecep' => 'Servicio de Impuestos Internos',
                    'GiroRecep' => 'Gobierno',
                    'DirRecep' => 'Alonso Ovalle 680',
                    'CmnaRecep' => 'Santiago',
                ],
            ],
            'Detalle' => [
                [
                    'NmbItem' => 'Cajón AFECTO',
                    'QtyItem' => 123,
                    'PrcItem' => 923,
                ],
                [
                    'NmbItem' => 'Relleno AFECTO',
                    'QtyItem' => 53,
                    'PrcItem' => 1473,
                ],
            ],
        ];
        $caratula = [
            //'RutEnvia' => '11222333-4', // se obtiene de la firma
            'RutReceptor' => '60803000-K',
            'FchResol' => '2020-06-17', //https://maullin.sii.cl/cvc_cgi/dte/ee_empresa_rut
            'NroResol' => 0,
        ];

        // generar XML del DTE timbrado y firmado
        $DTE = new Dte($factura);

        $Folios = new Folios(file_get_contents(base_path().'/xml/folios/33.xml'));
        if($DTE->timbrar($Folios))
        {
            if($depurar) Debugbar::info('Timbrado...');
        }else{
            Debugbar::error('NO Timbrado...');
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }

        if($DTE->firmar($Firma))
        {
            if($depurar) Debugbar::info('Firmado...');
        }else{
            Debugbar::error('NO Firmado...');
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }

        // generar sobre con el envío del DTE y enviar al SII
        $EnvioDTE = new EnvioDte();
        if($EnvioDTE->agregar($DTE))
        {
            if($depurar) Debugbar::info('DTE agregado a EnvioDTE...');
        }else{
            Debugbar::error('DTE No agregado al EnvioDTE...');
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }

        $EnvioDTE->setFirma($Firma); //No devuelve nada
        if($EnvioDTE->setCaratula($caratula))
        {
            if($depurar) Debugbar::info('Se puso caratula al EnvioDTE...');
        }else{
            Debugbar::error('No se puso Caratula al EnvioDTE...');
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }

        $xml=$EnvioDTE->generar(); //lo que se enviará

/****************** otra verificación ************************** */
//008-verificar_enviodte.php ejemplos
// crear objeto con XML del DTE que se desea validar
/*
$XML = new XML();
//$XML->loadXML(file_get_contents('xml/archivoFirmado.xml')); //original
$XML->loadXML($xml); //mio
// listado de firmas del XML
$Signatures = $XML->documentElement->getElementsByTagName('Signature');

// verificar firma de SetDTE
$SetDTE = $XML->documentElement->getElementsByTagName('SetDTE')->item(0)->C14N();
$SignedInfo = $Signatures->item($Signatures->length-1)->getElementsByTagName('SignedInfo')->item(0);
$DigestValue = $Signatures->item($Signatures->length-1)->getElementsByTagName('DigestValue')->item(0)->nodeValue;
$SignatureValue = $Signatures->item($Signatures->length-1)->getElementsByTagName('SignatureValue')->item(0)->nodeValue;
$X509Certificate = $Signatures->item($Signatures->length-1)->getElementsByTagName('X509Certificate')->item(0)->nodeValue;
$X509Certificate = '-----BEGIN CERTIFICATE-----'."\n".wordwrap(trim($X509Certificate), 64, "\n", true)."\n".'-----END CERTIFICATE----- ';

$valid = openssl_verify($SignedInfo->C14N(), base64_decode($SignatureValue), $X509Certificate) === 1 ? true : false;
echo 'Verificando SetDTE:',"\n";
echo '  Digest SetDTE: ',base64_encode(sha1($SetDTE, true)),"\n";
echo '  Digest SignedInfo: ',base64_encode(sha1($SignedInfo->C14N(), true)),"\n";
echo '  Digest SignedInfo: ',bin2hex(sha1($SignedInfo->C14N(), true)),"\n";
echo '  Digest SetDTE valido: ',($DigestValue===base64_encode(sha1($SetDTE, true))?'si':'no'),"\n";
echo '  Digest SignedInfo valido: ',($valid?'si':'no'),"\n\n";

// verificar firma de documentos
$i = 0;
$documentos = $XML->documentElement->getElementsByTagName('Documento');
foreach ($documentos as $D) {
    $Documento = new XML();
    $Documento->loadXML($D->C14N());
    $Documento->documentElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
    $Documento->documentElement->removeAttributeNS('http://www.sii.cl/SiiDte', '');
    $SignedInfo = new XML();
    $SignedInfo->loadXML($Signatures->item($i)->getElementsByTagName('SignedInfo')->item(0)->C14N());
    $SignedInfo->documentElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
    $DigestValue = $Signatures->item($i)->getElementsByTagName('DigestValue')->item(0)->nodeValue;
    $SignatureValue = $Signatures->item($i)->getElementsByTagName('SignatureValue')->item(0)->nodeValue;
    $X509Certificate = $Signatures->item($i)->getElementsByTagName('X509Certificate')->item(0)->nodeValue;
    $X509Certificate = '-----BEGIN CERTIFICATE-----'."\n".wordwrap(trim($X509Certificate), 64, "\n", true)."\n".'-----END CERTIFICATE----- ';

    $valid = openssl_verify($SignedInfo->C14N(), base64_decode($SignatureValue), $X509Certificate) === 1 ? true : false;
    echo 'Verificando Documento:',"\n";
    echo '  Digest Documento: ',base64_encode(sha1($Documento->C14N(), true)),"\n";
    echo '  Digest SignedInfo: ',base64_encode(sha1($SignedInfo->C14N(), true)),"\n";
    echo '  Digest Documento valido: ',($DigestValue===base64_encode(sha1($Documento->C14N(), true))?'si':'no'),"\n";
    echo '  Digest SignedInfo valido: ',($valid?'si':'no'),"\n\n";
    $i++;
}

// si hubo errores mostrar
foreach (Log::readAll() as $error)
    echo $error,"\n";

    $ajo=base_path().'/xml/enviados/facturas/ajo.xml';
    if(file_put_contents($ajo,$xml))
    {
        echo "xml guardado\n";
    }else{
        echo "no guardó xml\n";
    }

    */
 //****************************************************/

 /*
        // Verifica SetDTE que es el documento, EnvioDTE es todo el sobre
        $vf=$Firma->verifyXML($xml,'SetDTE');

        if($vf===true)
        {
            Debugbar::info("Verificación de firma en XML OK");
        }
        if($vf===false)
        {
            Debugbar::info("pooor la xuxa... Verificación de firma en XML");
            //Debugbar::error(Log::readAll(LOG_ERR,false));
        }
*/

        $RutEnvia = '5483206-0';
        $RutEmisor = $RutEnvia;

        if($depurar) Debugbar::info("Enviando...");
        $resultado = Sii::enviar($RutEnvia, $RutEmisor, $xml, $Token); //devuelve un xml
        //$r=simplexml_load_string($resultado,null,0,"SII",true) or die("carajooOOO");
        //Debugbar::info("resultado");



        // si hubo algún error al enviar al servidor mostrar kaka
        if ($resultado===false) {
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }

        // Mostrar resultado del envío
        if ($resultado->STATUS!='0') {
            Debugbar::error(Log::readAll(LOG_ERR,false));
            return false;
        }else{
            $tipo_doc="F";
            $num_doc=$ndoc;
            $doc=$tipo_doc.$num_doc.'.xml';
            $enviado=base_path().'/xml/enviados/facturas/'.$doc;
            if(file_put_contents($enviado,$xml))
            {
                if($depurar) Debugbar::info("Guardado en: ".$enviado.' con Track ID: '.$resultado->TRACKID);
            }else{
                Debugbar::error(Log::readAll(LOG_ERR,false));
                return false;
            }

            //Ver estado del DTE: 005-estadoDteEnviado.php de los ejemplos LibreDTE
            $rut = '05483206';
            $dv = '0';
            $trackID = $resultado->TRACKID;
            Debugbar::info("Track ID: ".$trackID);
            $dame_estado = Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $trackID, $Token]);
            if($depurar)  Debugbar::info("dame_estado:");
            if($depurar)  Debugbar::info($dame_estado);  //05jul2020 - Devuelve vacio... TAMAREEE!!!


        }

        //haber yo...


        /*
        $Tokencito=Auto::getToken($Firma);


        $wsdl="https://maullin.sii.cl/DTEWS/QueryEstUp.jws?WSDL";
        $s=new SoapClient($wsdl);
        $x=$s->getEstUp(new SoapParam('RutCompania',$rut),
                                    new SoapParam('DvCompania',$dv),
                                    new SoapParam('TrackId',$trackID),
                                    new SoapParam('Token',$Tokencito)
                                    );
        $r=simplexml_load_string($x,null,0,"SII",true) or die("carajoo");
        Debugbar::info("Mi Token: ".$Tokencito);
        Debugbar::info("Estado:".$r->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]);
        Debugbar::info("Glosa:".$r->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]);

        //EL otro
        $wsdl="https://maullin.sii.cl/DTEWS/QueryEstDte.jws?WSDL";
        $t=new SoapClient($wsdl);
        $p=$t->getEstDte(new SoapParam('RutConsultante','05483206'),
                                     new SoapParam('DvConsultante','0'),
                                     new SoapParam('RutCompania','05483206'),
                                     new SoapParam('DvCompania','0'),
                                     new SoapParam('RutReceptor','60803000'),
                                     new SoapParam('DvReceptor','K'),
                                     new SoapParam('TipoDte','33'),
                                     new SoapParam('FolioDte','1'),
                                     new SoapParam('FechaEmisionDte','02-07-2020'),
                                     new SoapParam('MontoDte','228002'),
                                     new SoapParam('Token',$Tokencito)
                                    );
        $q=simplexml_load_string($p,null,0,"SII",true) or die("carajooooo");
        $ete=base_path().'/xml/enviados/facturas/xuxa.xml';
*/
        //file_put_contents($ete,$p);

        //Debugbar::info($q);
        if($depurar)
        {
            Debugbar::info("Estado2:".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]);
            Debugbar::info("Glosa2:".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]);
            Debugbar::info("Cód. Error:".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/ERR_CODE')[0]);
            Debugbar::info("Glosa Error:".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA_ERR')[0]);
            Debugbar::info("N° Atención:".$q->xpath('/SII:RESPUESTA/SII:RESP_HDR/NUM_ATENCION')[0]);
        }

    } //FIN DE ENTRADA

    public function object2array($object) { return @json_decode(@json_encode($object),1); }

    public function dame_semilla()
    {
        //Debugbar::addMessage('autentica_controlador: semilla: '.$semilla);
        $this->validaSesion();
        $wsdl="https://maullin.sii.cl/DTEWS/CrSeed.jws?WSDL";
        $ws_semilla=new SoapClient($wsdl);
        try{
            $xml_semilla=$ws_semilla->getSeed();
            $r=simplexml_load_string($xml_semilla,null,0,"SII",true) or die("carajoo");
            //este esta mejor...
            $estado=$r->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0];
            $semilla=$r->xpath('/SII:RESPUESTA/SII:RESP_BODY/SEMILLA')[0];

            //Debugbar::warning($r->getDocNamespaces()); //funciona
            /* funciona
            foreach($r->children("SII",true) as $h)
            {
                //Debugbar::warning("h->getName(): ".$h->getName()); //funciona
                foreach($h->children() as $i)
                {
                    //Debugbar::warning($i->getName()." = ".$i); //funciona
                    if($i->getName()=="SEMILLA") $semilla=$i;
                    if($i->getName()=="ESTADO") $estado=$i;
                }
            }
            */
            //return $estado." ".$semilla;

        }catch (\Exception $error){
            return "Error: ".$error->getMessage();
        }
        //Debugbar::addMessage('autentica_controlador: semilla: '.$semilla);
        //Debugbar::info("semilla: ".$semilla);



        //Verificar si esta el archivo de firma

        $archivo_firma=base_path().'/cert/juanita_libreDTE.p12';

        if(is_readable($archivo_firma))
        {
            $msg="/ is_readable: si esta el CERTIFICADO";
            $firma=file_get_contents($archivo_firma);
            $clave="juana206"; // juana206 //panchorepuestos8311048
            $x=openssl_pkcs12_read($firma,$certificado,$clave);
            if($x===true)
            {
                //Debugbar::info($certificado); //bien
                $cert=$certificado['cert'];
                $pkey=$certificado['pkey'];
                $data=openssl_x509_parse($cert);
                //En $data hay diversa información del certificado digital
                //Debugbar::info($data); //bien

                $xml_a_firmar=(new XML())->generate([
                    'getToken' => [
                        'item' => [
                            'Semilla' => $semilla
                        ]
                    ]
                ])->saveXML();

                $firma_config=['file'=>$archivo_firma,'pass'=>'juana206'];
                $Firma=new FirmaElectronica($firma_config);

                $Semilla_Firmada=$Firma->signXML($xml_a_firmar);
                $xml = Sii::request('GetTokenFromSeed', 'getToken', $Semilla_Firmada);
                $token=(string)$xml->xpath('/SII:RESPUESTA/SII:RESP_BODY/TOKEN')[0];
                $glosa=(string)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
                Debugbar::info("Glosa: ".$glosa." ----- Token: ".$token);
                $bitacora=Log::readAll();
                Debugbar::info($bitacora);
            }else{
                Debugbar::info("Xuxa, no abrí el certificado...");
            }

        }else{
            $msg="/ is_readable: tamare no es...";
        }
/*
        $imagen=base_path()."/cert/ya.png";
        if(is_readable($imagen))
        {
            $msg.="/ is_readable: IMAGEN si esta el archivo";
        }else{
            $msg.="/ is_readable: IMAGEN TAmare no esta...";
        }


        $existe_archivo=Storage::disk('local')->exists('cert');
        if($existe_archivo)
        {
            $msg.="/ exists: si esta la carpeta CERT - ".$existe_archivo;
        }else{
            $msg.="/ exists: tamare no es - ".$existe_archivo;
        }
        */
//        Debugbar::warning('msg: '.$msg);
    }

    public function dame_token(Request $r)
    {
        $this->validaSesion();


        $url="https://maullin.sii.cl/DTEWS/GetTokenFromSeed.jws?WSDL";
        $semilla_firmada="";
        $ws_token=new SoapClient($url,$semilla_firmada);
        try{
            $token=$ws_token->getToken();
        }catch (\Exception $error){
            return "Error: ".$error->getMessage();
            /*
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
            */
        }

    }

    private function validaSesion()
    {
        abort_if(Session::get('acceso')!='SI', 403);
    }

}
