<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Debugbar;

class EnviarCorreo_comentario extends Mailable
{
    use Queueable, SerializesModels;

    private $correo_destino;
    private $nombre;
    private $apellidos;
    private $email;
    private $telefono;
    private $comentario;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($c,$nombre,$apellidos,$email,$telefono,$comentario)
    {
        $this->correo_destino=$c;
        $this->nombre=$nombre;
        $this->apellidos=$apellidos;
        $this->email=$email;
        $this->telefono=$telefono;
        $this->comentario=$comentario;
    }

    /**
     * Build the message.
     *
     * @return $this
     *
     */
    public function build()
    {
            $correo_origen=$this->email;
            $asunto='El cliente '.$this->nombre.' '.$this->apellidos.' ha dejado un comentario';
            $telefono = $this->telefono;
            $comentario = $this->comentario;
            try {
                return $this->view('correos.comentarios',compact('asunto','comentario','telefono')) //esta vista es el cuerpo del correo
                ->from($correo_origen)
                ->to($this->correo_destino)
                ->subject($asunto);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            



    }
}
