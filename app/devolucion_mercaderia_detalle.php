<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devolucion_mercaderia_detalle extends Model
{
    protected $table='devolucion_mercaderia_detalle';
    protected $fillable=[
        'id_devolucion_mercaderia',
        'repuesto_id',
        'fecha_emision',
        'cantidad',
        'activo',
        'local_id'
    ];
}
