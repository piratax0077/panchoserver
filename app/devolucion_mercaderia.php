<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devolucion_mercaderia extends Model
{
    protected $table='devolucion_mercaderia';
    protected $fillable=[
        'num_devolucion',
        'tipo_doc',
        'num_nc',
        'usuario_id',
        'fecha_emision',
        'repuesto_id',
        'cantidad',
        'activo',
        'local_id'
    ];
}
