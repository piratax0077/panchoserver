<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class regulador_voltaje extends Model
{
    protected $table='regulador_voltaje';
    protected $fillable=[
        'id_repuesto',
        'rectificador',
        'alternador'
    ];
}
