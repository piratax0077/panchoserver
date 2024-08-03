<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class guia_de_despacho_detalle extends Model
{
    protected $table='guias_de_despacho_detalle';
    protected $fillable=[
        'id_guia_despacho',
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
