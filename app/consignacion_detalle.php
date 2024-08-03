<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class consignacion_detalle extends Model
{
    protected $table='consignaciones_detalle';
    protected $fillable=[
        'id_consignacion',
        'id_repuestos',
        'id_unidad_venta',
        'id_local',
        'precio_venta',
        'cantidad',
        'subtotal',
        'descuento',
        'total',
        'activo',
        'usuarios_id'
    ];
}
