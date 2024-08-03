<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class traspaso_mercaderia extends Model
{
    protected $table='traspaso_mercaderia';
    protected $fillable=[
        'num_solicitud',
        'usuario_id',
        'repuesto_id',
        'fecha_emision',
        'activo'
    ];
}
