<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RepuestosStockMinimoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $archivo;
    public $nombreArchivo;

    public function __construct($archivo, $nombreArchivo)
    {
        $this->archivo = $archivo;
        $this->nombreArchivo = $nombreArchivo;
    }

    public function build()
    {
        return $this->subject('Reporte de repuestos con stock mínimo')
                    ->view('correos.repuestos_stock_minimo')
                    ->attachData(
                        $this->archivo,
                        $this->nombreArchivo,
                        [
                            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]
                    );
    }
}
