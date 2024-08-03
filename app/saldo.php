<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class saldo extends Model
{
    protected $table='saldos';
    protected $fillable=[
        'id_repuestos',
        'id_local',
        'saldo',
        'activo',
        'usuarios_id'
    ];
}
