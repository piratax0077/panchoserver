<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cotizacion_detalle extends Model
{
    protected $table='cotizaciones_detalle';
    protected $fillable=[
        'id_cotizacion',
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
