<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class repuesto_catalogo extends Model
{
    protected $fillable=[
        'id',
        'descripcion',
        'urlfoto',
        'precio_venta',
        'marcarepuesto',
        'nombrefamilia',
        'stock_actual'
    ];
}
