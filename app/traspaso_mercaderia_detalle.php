<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class traspaso_mercaderia_detalle extends Model
{
    protected $table='traspaso_mercaderia_detalle';
    protected $fillable=[
        'id_traspaso_mercaderia',
        'repuesto_id',
        'fecha_emision',
        'cantidad',
        'activo',
        'locaciones',
        'estado'
    ];
}
