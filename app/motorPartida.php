<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class motorPartida extends Model
{
    use HasFactory;
    protected $table='motorpartida';
    protected $fillable=[
        'id_repuesto',
        'motor',
        'activo'
    ];
}
