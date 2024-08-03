<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nota_de_credito_detalle extends Model
{
    protected $table='notas_de_credito_detalle';
    protected $fillable=[
        'id_nota_de_credito',
        'id_facturas_detalle',
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
        'usuarios_id',
        'repuesto_devuelto'
    ];
}
