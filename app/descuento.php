<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class descuento extends Model
{
    protected $table='descuentos';
    protected $fillable=[
    	'id_cliente',
        'id_familia',
        'porcentaje',
        'activo',
        'desde',
        'hasta',
        'usuarios_id',
        'id_local',
        'image_path'
    ];
}
