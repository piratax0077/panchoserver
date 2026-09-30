<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_potencial extends Model
{
    protected $table = 'cliente_potencial';
    protected $fillable = [
        'rut',
        'nombre',
        'telefono',
        'cotizacion_id',
    ];
}
