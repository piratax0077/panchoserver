<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class repuestofoto extends Model
{
    protected $table='repuestos_fotos';
    protected $fillable=[
        'urlfoto',
        'activo',
        'id_repuestos',
        'usuarios_id'
    ];

}
