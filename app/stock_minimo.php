<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stock_minimo extends Model
{
    protected $table='stock_minimo';
    protected $fillable=[
        'id_repuesto',
        'id_usuario',
        'fecha_emision'
    ];
}
