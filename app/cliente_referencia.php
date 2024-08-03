<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_referencia extends Model
{
    protected $table='clientes_referencias';
    protected $fillable=[
        'id_cliente',
        'id_tipo_documento',
        'activo',
        'usuarios_id'
    ];
}
