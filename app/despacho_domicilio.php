<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class despacho_domicilio extends Model
{
    protected $table='despacho_domicilio';
    protected $fillable=[
        'region',
        'comuna',
        'numero_carrito',
        'direccion_despacho',
        'telefono_despacho',
        'persona',
        'referencia'
    ];
}