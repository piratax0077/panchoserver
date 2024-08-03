<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class registro_login extends Model
{
    use HasFactory;
    protected $table='registro_login';

    protected $fillable=[
        'usuario_id_servidor',
        'fecha_ingreso',
        'fecha_login',
        'fecha_logout',
        'direccion_ip'
    ];
}
