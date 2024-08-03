<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class compra_transbank extends Model
{
    protected $table='compra_transbank';
    protected $fillable=[
        'session_id',
        'total',
        'status',
        'usuario_id',
        'numero_carrito',
        'token_ws'
    ];
}
