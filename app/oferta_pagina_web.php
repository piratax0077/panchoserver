<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class oferta_pagina_web extends Model
{
    protected $table='ofertas_pagina_web';
    protected $fillable=[
        'id_repuesto',
        'descuento',
        'precio_antiguo',
        'precio_actualizado',
        'usuario_id',
        'desde',
        'hasta',
        'activo'
    ];
}
