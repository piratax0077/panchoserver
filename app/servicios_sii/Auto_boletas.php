<?php

namespace App\servicios_sii;
use Debugbar;
class Auto_boletas
{

    public static function getSeed()
    {
        $xml = Sii_boletas::request('GET','/boleta.electronica.semilla');
        if ($xml===false or (string)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]!=='00') {
            return false;
        }
        return (string)$xml->xpath('/SII:RESPUESTA/SII:RESP_BODY/SEMILLA')[0];
    }

    public static function getToken($Firma = [])
    {
        if (!$Firma){
            $estado=['estado'=>'ERROR_FIRMA','mensaje'=>'No tengo la firma'];
            return $estado;
        }
        $semilla = self::getSeed();
        if(!$semilla){
            $estado=['estado'=>'ERROR_SEMILLA','mensaje'=>'No obtuve la semilla'];
            return $estado;
        }
        $requestFirmado = (string)self::getTokenRequest($semilla, $Firma);
        if (!$requestFirmado){
            $estado=['estado'=>'ERROR_FIRMADO','mensaje'=>'No pude firma la semilla'];
            return $estado;
        }

        $xml =Sii_boletas::request('POST', '/boleta.electronica.token', $requestFirmado);
        if ($xml===false or (string)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]!=='00') {
            $estado=['estado'=>'ERROR_TOKEN','mensaje'=>'SII no me dió Token'];
            return $estado;
        }
        $estado=['estado'=>'OK','mensaje'=>'Tengo el token','token'=>(string)$xml->xpath('/SII:RESPUESTA/SII:RESP_BODY/TOKEN')[0]];
        //return (string)$xml->xpath('/SII:RESPUESTA/SII:RESP_BODY/TOKEN')[0];
        return $estado;
    }

    private static function getTokenRequest($seed, $Firma = [])
    {
        if (is_array($Firma))
            $Firma = new FirmaElectronica($Firma);
        $seedSigned = $Firma->signXML(
            (new XML())->generate([
                'getToken' => [
                    'item' => [
                        'Semilla' => $seed
                    ]
                ]
            ])->saveXML()
        );
        if (!$seedSigned) {
            Log::write(
                Estado::AUTH_ERROR_FIRMA_SOLICITUD_TOKEN,
                Estado::get(Estado::AUTH_ERROR_FIRMA_SOLICITUD_TOKEN)
            );
            return false;
        }
        return $seedSigned;
    }



}
