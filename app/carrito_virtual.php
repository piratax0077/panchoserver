<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carrito_virtual extends Model
{
    protected $table='carrito_virtual';
    protected $fillable=[
        'fecha_emision',
        'numero_carrito',
        'activo',
        'usuario_id'
    ];
}
