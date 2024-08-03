<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cliente_xpress_boleta extends Model
{
    protected $table='cliente_xpress_boleta';
    protected $fillable=[
        'rut',
        'nombre_completo',
        'direccion',
        'telefono',
        'email',
        'activo',
        'estado',
        'usuarios_id'
    ];
}
