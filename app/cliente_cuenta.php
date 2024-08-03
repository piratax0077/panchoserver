<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_cuenta extends Model
{
    protected $table='clientes_cuenta';
    protected $fillable=[
        'id_cliente', 
        'fecha_operacion',
        'pago',
        'deuda',
        'referencia',
        'usuarios_id',
        'activo'
    ];
}
