<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stock_tienda extends Model
{
    protected $table='solicitudes';
    protected $fillable=[
        'stock_actual',
        'local_nombre',
        'id',
        'id_local'
    ];
}
