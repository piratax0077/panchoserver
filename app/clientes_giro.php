<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clientes_giro extends Model
{
    protected $table='clientes_giro';
    protected $fillable=[
        'id_cliente',
        'giro',
        'usuarios_id'
    ];
}
