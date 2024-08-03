<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ot extends Model
{
    protected $table='orden_transporte';
    protected $fillable=[
        'id_transportista',
        'numero_ot',
        'fecha_ot',
        'fecha_recepcion',
        'receptor_ot',
        'origen_ot',
        'observaciones_ot',
        'activo',
        'usuarios_id'
    ];
}
