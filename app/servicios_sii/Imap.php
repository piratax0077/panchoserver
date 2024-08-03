<?php
namespace App\servicios_sii;
use Carbon\Carbon; // para tratamiento de fechas
use Debugbar;

class Imap
{
    //const cuenta="panchorepuestos831@gmail.com"; //activar la función IMAP en la cuenta gmail
    //const clave="riquelme831";
    //const path_inbox="{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX";

    const cuenta="info_sii@panchorepuestos.cl";
    const clave="L4vidaeshermos@";
    const path_inbox="{mail.panchorepuestos.cl:993/imap/ssl/novalidate-cert}INBOX";
    //https://hotexamples.com/examples/-/-/imap_fetchstructure/php-imap_fetchstructure-function-examples.html
    //https://hotexamples.com/de/examples/-/Model_DteEmitido/save/php-model_dteemitido-save-method-examples.html
    //https://github.com/bachors/Email-Inbox-IMAP/blob/master/lib/class.imap.php

    public static function revisar_mail($TrackID,$tipo_dte,$num_doc){
        //A veces el track viene con ceros delante pero en el mail no asi que la comparación no resulta.
        //Por eso lo corregimos.
        $TrackID0=strval(intval($TrackID));
        $hoy = strtoupper(Carbon::now()->format('d-M-Y'));
        $donde=base_path().'/xml/adjuntos/';
        try {
            $inbox=imap_open(self::path_inbox,self::cuenta,self::clave);
        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR_MAIL','mensaje'=>'No se pudo abrir buzon de correo: '.$e->getMessage()];
            return $estado;
        }

        if($inbox===false){
            $estado=['estado'=>'ERROR_MAIL','mensaje'=>'Inbox FALSE'];
            return $estado;
        }else{
            //$emails=imap_search($inbox,'SUBJECT "'.$TrackID.'"');

            $emails=imap_search($inbox,'SINCE "'.$hoy.'"');
            if($emails===false){
                $estado=['estado'=>'SIN_CORREO','mensaje'=>'SII aún no envia respuesta al correo'];
                return $estado;
            }else{
                $hay=false;
                foreach($emails as $email){
                    $overview=imap_fetch_overview($inbox,$email);
                    foreach($overview as $over){
                        if(isset($over->subject)){
                            $asunto=utf8_decode(self::arreglar_texto($over->subject));
                            //Debugbar::info($asunto);
                            if(strpos($asunto,$TrackID)>0 || strpos($asunto,$TrackID0)>0)
                            {
                                $uid_mail=$over->uid;
                                //Debugbar::info("ASUNTO: ".$asunto." UID: ".$uid_mail);
                                $estructura=imap_fetchstructure($inbox,$uid_mail,FT_UID);

                                foreach($estructura->parts as $key=>$value){
                                    $filename = $estructura->parts[$key]->parameters[0]->value;
                                    if(strpos($filename,".xml")>0){
                                        //Debugbar::info($filename);
                                        $message=(imap_fetchbody($inbox,$uid_mail,$key+1,FT_UID));
                                        $xml = new \SimpleXMLElement($message, LIBXML_COMPACT);

                                        if($xml->IDENTIFICACION->TRACKID==$TrackID or $xml->IDENTIFICACION->TRACKID==$TrackID0){
                                            if($xml->ESTADISTICA->SUBTOTAL->TIPODOC==$tipo_dte){
                                                if(isset($xml->ESTADISTICA->SUBTOTAL->ACEPTA)){
                                                    $estado=['estado'=>'ACEPTADO','mensaje'=>(string)$xml->IDENTIFICACION->TMSTRECEPCION];
                                                }elseif (isset($xml->ESTADISTICA->SUBTOTAL->RECHAZO)){
                                                    $folio_rech=(string)$xml->REVISIONENVIO->REVISIONDTE->FOLIO;
                                                    $estado=['estado'=>'RECHAZADO','mensaje'=>(string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO.". ".(string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE.", FOLIO N° ".$folio_rech];
                                                }elseif (isset($xml->ESTADISTICA->SUBTOTAL->REPARO)){
                                                    $folio_rep=strval(intval($xml->REVISIONENVIO->REVISIONDTE->FOLIO));
                                                    $estado=['estado'=>'REPARO','mensaje'=>(string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO.". ".(string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE.", FOLIO N° ".$folio_rep];
                                                }
                                            }else{
                                                $estado=['estado'=>'ERROR_MAIL','mensaje'=>'Track ID coincide pero tipo de dte en XML(SII) es diferente'];
                                            }
                                        }else{
                                            $estado=['estado'=>'ERROR_MAIL','mensaje'=>'Cabecera de Respuesta SII coincide pero TrackID en XML(SII) es diferente'];
                                        }
                                        return $estado;
                                    }
                                }// fin foreach estructura
                            }
                        }
                    } // fin foreach overview
                } //fin foreach de emails
                $estado=['estado'=>'SIN_CORREO','mensaje'=>'SII aún no envia respuesta al correo'];
                return $estado;
            } //fin IF emails as email
        }
    }

    //arregla texto de asunto
    private static function arreglar_texto($str)
    {
        $subject = '';
        $subject_array = imap_mime_header_decode($str);

        foreach ($subject_array AS $obj)
            $subject .= utf8_encode(rtrim($obj->text, "t"));

        return $subject;
    }

}


?>
