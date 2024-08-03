<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dte_rechazados extends Model
{
    protected $table = 'dte_rechazados';

    protected $fillable = [
        'tipo_doc',
        'fecha_emision',
        'folio_doc',
        'id_cliente',
        'track_id',
        'estado'
    ];
}
