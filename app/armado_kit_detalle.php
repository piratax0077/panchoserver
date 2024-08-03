<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class armado_kit_detalle extends Model
{
    protected $table='armado_kit_detalle';
    protected $fillable=[
        'id_kit',
        'estado',
        'id_repuesto',
        'cantidad',
        'precio_unitario',
        'usuario_id',
        'local_id',
        'total',
        'activo'
    ];
}
