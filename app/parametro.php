<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class parametro extends Model
{
    protected $table='parametros';
    protected $fillable=[
        'codigo',
        'nombre',
        'descripcion',
        'valor',
        'activo',
        'usuarios_id'
    ];
}
