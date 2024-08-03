<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vale_mercaderia_detalle extends Model
{
    protected $table='vale_mercaderia_detalle';
    protected $fillable=[
        'vale_mercaderia_id',
        'repuesto_id',
        'local_id',
        'cantidad',
        'usuario_id',
        'activo'
    ];
}
