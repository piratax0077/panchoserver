<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Debugbar;

class EnviarCorreo extends Mailable
{
    use Queueable, SerializesModels;

    private $correo_destino;
    private $archivo_ruta;
    private $archivo_xml_ruta;
    private $docu_nombre;
    private $num_doc;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($c,$a,$x,$d,$n)
    {
        $this->correo_destino=$c;
        $this->archivo_ruta=$a;
        $this->archivo_xml_ruta=$x;
        $this->docu_nombre=$d;
        $this->num_doc=$n;
    }

    /**
     * Build the message.
     *
     * @return $this
     *
     */
    public function build()
    {
            $correo_origen='facturacion@panchorepuestos.cl';
            $asunto='Se adjunta '.$this->docu_nombre.' N° '.$this->num_doc.' PANCHO REPUESTOS ARICA';
            try {
                return $this->view('correos.facturacion',compact('asunto')) //esta vista es el cuerpo del correo
                ->from($correo_origen)
                ->to($this->correo_destino)
                ->subject($asunto)
                ->attach($this->archivo_ruta, ['mime' => 'application/pdf'])
                ->attach($this->archivo_xml_ruta, ['mime' => 'application/xml']);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            



    }
}
