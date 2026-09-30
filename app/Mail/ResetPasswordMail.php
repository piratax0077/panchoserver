<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    private $token;
    private $correo_destino;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($correo_destino, $token)
    {
        $this->token = $token;
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
                    ->view('correos.reset_password')
                    ->with([
                        'resetUrl' => route('password.reset', [
                            'token' => $this->token,
                            'email' => $this->correo_destino,
                        ]),
                    ]);
    }
}
