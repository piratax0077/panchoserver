<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carrito_virtual_detalle extends Model
{
    protected $table='carrito_virtual_detalle';
    protected $fillable=[
        'repuesto_id',
        'cantidad',
        'carrito_numero',
        'pu',
        'pu_neto',
        'subtotal_item',
        'descuento_item',
        'estado'
    ];
}
