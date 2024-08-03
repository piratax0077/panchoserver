<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class armado_kit extends Model
{
    protected $table='armado_kit';
    protected $fillable=[
        'nombre_kit',
        'fecha_emision',
        'id_cliente',
        'activo',
        'id_usuario'
    ];
}
