<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ticket extends Model
{
    protected $table='ticket';
    protected $fillable=[
        'usuario_id',
        'descripcion',
        'activo',
        'fecha_emision',
        'image_path',
        'estado'
    ];
}
