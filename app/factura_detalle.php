<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class factura_detalle extends Model
{
    protected $table='facturas_detalle';
    protected $fillable=[
        'id_factura',
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
