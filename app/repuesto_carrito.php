<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class repuesto_carrito extends Model
{
    protected $fillable=[
        'id',
        'descripcion',
        'urlfoto',
        'precio_venta',
        'numero_carrito',
        'cantidad',
        'oferta'
    ];
}
