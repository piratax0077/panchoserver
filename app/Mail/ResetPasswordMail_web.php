<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail_web extends Mailable
{
    use Queueable, SerializesModels;
    private $token;
    private $correo_origen;
    private $correo_destino;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($correo_origen,$correo_destino,$token)
    {
        $this->token = $token;
        $this->correo_origen = $correo_origen;
        $this->correo_destino = $correo_destino;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Restablecimiento de contraseña')
                    ->to($this->correo_destino)
                    ->from($this->correo_origen)
                    ->view('correos.reset_password')
                    ->with(['token' => $this->token,'asunto' => 'Restablecimiento de contraseña']);
    }
}
