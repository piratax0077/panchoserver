<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class oferta_catalogo extends Model
{
    protected $fillable=[
        'id',
        'codigo_interno',
        'descripcion',
        'urlfoto',
        'precio_venta',
        'precio_antiguo',
        'descuento',
        'marcarepuesto',
        'nombrefamilia',
        'stock_actual'
    ];
}
