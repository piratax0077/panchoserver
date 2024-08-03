<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class retiro_tienda extends Model
{
    protected $table='retiro_tienda';
    protected $fillable=[
        'numero_carrito',
        'nombre',
        'estado'
    ];
}
