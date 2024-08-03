<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\EnviarCorreo;
use Illuminate\Mail\Events\MessageSending;

use Debugbar;

class correos_controlador extends Controller
{
    public function enviar_correo(Request $r)
    {
        /*
        Se trata de implementar eventos y lísteners en laravel.

        Para ver el estado del envío del mail hay que registrar unos eventos y listeners
        (MessageSending y MessageSent) en app\providers\EventServiceProvider.php.

        Luego en consola se ejecuta php  artisan event:generate para que se creen los eventos y listeners

        */
        try {
            if(strtolower($r->tipo_doc)=="boleta" || $r->tipo_doc=="39"){
                $docu_nombre="Boleta";
                $docu="boleta_".$r->num_doc.".pdf";
                $docu_xml="39_".$r->num_doc.".xml";
                $archivo_ruta=base_path('storage/app/public/pdf').'/boletas/'.$docu;
                $archivo_xml_ruta=base_path('xml/generados').'/boletas/'.$docu_xml;
            }

            if(strtolower($r->tipo_doc)=="factura" || $r->tipo_doc=="33"){
                $docu_nombre="Factura";
                $docu="factura_".$r->num_doc.".pdf";
                $docu_xml="33_".$r->num_doc.".xml";
                $archivo_ruta=base_path('storage/app/public/pdf').'/facturas/'.$docu;
                $archivo_xml_ruta=base_path('xml/generados').'/facturas/'.$docu_xml;
            }

            if(strtolower($r->tipo_doc)=="guia_despacho" || $r->tipo_doc=="52"){
                $docu_nombre="Guía de Despacho";
                $docu="guia_despacho_".$r->num_doc.".pdf";
                $docu_xml="52_".$r->num_doc.".xml";
                $archivo_ruta=base_path('storage/app/public/pdf').'/guias_despacho/'.$docu;
                $archivo_xml_ruta=base_path('xml/generados').'/guias_de_despacho/'.$docu_xml;
            }

            if(strtolower($r->tipo_doc)=="nota_credito" || $r->tipo_doc=="61"){
                $docu_nombre="Nota de Crédito";
                $docu="nota_credito_".$r->num_doc.".pdf";
                $docu_xml="61_".$r->num_doc.".xml";
                $archivo_ruta=base_path('storage/app/public/pdf').'/notas_credito/'.$docu;
                $archivo_xml_ruta=base_path('xml/generados').'/notas_de_credito/'.$docu_xml;
            }

            //VERIFICAR LA RUTA DEL ARCHIVO A ADJUNTAR
            if(file_exists($archivo_ruta)){
              
                if(file_exists($archivo_xml_ruta)){
                    $correo=new EnviarCorreo($r->correo_destino,$archivo_ruta,$archivo_xml_ruta,$docu_nombre,$r->num_doc);
                    //event(new MessageSending($correo));
                    
                    \Mail::send($correo); //Devuelve void
                    $rpta=$r->correo_destino." enviado.";
                    
                    if( count(\Mail::failures()) > 0 ) {

                        $rpta= "Error al enviar: <br>";

                        foreach(\Mail::failures() as $email_malo) {
                            $rpta.=" - $email_malo <br>";
                        }

                    }
                }else{
                    //Debugbar::info("NO Existe XML: ".$archivo_xml_ruta);
                    $rpta= "NO se ha generado el archivo XML.".$docu_xml;
                }


            }else{
                //El archivo PDF solo se genera cuando envía a IMPRIMIR, antes
                //Debugbar::info("NO Existe PDF: ".$archivo_ruta);
                $rpta= "NO se ha generado el archivo PDF. Imprímalo ".$docu;
            }

            return $rpta;



        } catch (\Swift_TransportException $e) {
            return $e->getMessage();
        }
    }

    
}
