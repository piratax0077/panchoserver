<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Validator;

class ResetPasswordMail extends Mailable
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
        // Crear un arreglo simulando una petición con dos inputs
        $requestData = [
            'correo_origen' => $this->correo_origen,
            'correo_destino' => $this->correo_destino,
        ];
        // Validación de datos
        $validator = Validator::make($requestData, [
            'correo_origen' => 'required|email',
            'correo_destino' => 'required|mail',
        ]);
        return $this->subject('Restablecimiento de contraseña')
                    ->to($this->correo_destino)
                    ->from($this->correo_origen)
                    ->view('auth.passwords.reset')
                    ->with(['token' => $this->token]);
    }
}
