<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_xpress extends Model
{
    protected $table='clientes_xpress';
    protected $fillable=[
        'rut_xpress',
        'nombres_xpress',
        'apellidos_xpress',
        'empresa_xpress',
        'telf1_xpress',
        'email_xpress',
        'documento_xpress',
        'estado',
        'activo',
        'usuarios_id'
    ];
}
