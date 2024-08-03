<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pagos_factura_compra extends Model
{
    protected $table='pagos_factura_compra';
    protected $fillable=[
        'id_factura',
        'forma_pago',
        'fecha_pago',
        'monto',
        'referencia',
        'activo',
        'usuario_id'
    ];
}
