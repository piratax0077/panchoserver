<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class pago extends Model
{
    protected $table='pagos';
    protected $fillable=[
        'tipo_doc',
        'id_doc',
        'id_cliente',
        'id_forma_pago',
        'referencia_pago',
        'fecha_pago',
        'referencia',
        'monto',
        'usuarios_id',
        'activo'
    ];
}
