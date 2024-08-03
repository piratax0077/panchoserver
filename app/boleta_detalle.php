<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class boleta_detalle extends Model
{
    protected $table='boletas_detalle';
    protected $fillable=[
        'id_boleta',
        'id_repuestos',
        'id_unidad_venta',
        'id_local',
        'pu_neto',
        'precio_venta',
        'cantidad',
        'subtotal',
        'descuento',
        'total',
        'activo',
        'usuarios_id'
    ];
}
