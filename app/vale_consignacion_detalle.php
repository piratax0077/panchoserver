<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vale_consignacion_detalle extends Model
{
    protected $table='vale_consignacion_detalle';
    protected $fillable=[
        'id_doc',
        'id_repuestos',
        'id_local',
        'cantidad',
        'devuelto'
    ];
}
