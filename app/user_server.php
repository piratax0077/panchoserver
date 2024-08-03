<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_server extends Model
{
    protected $table='user_server';
    protected $fillable=[
        'user',
        'password',
        'fecha_actualizacion_password',
        'activo'
    ];
}
