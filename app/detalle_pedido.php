<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detalle_pedido extends Model
{
    use HasFactory;
    protected $table = 'detalle_pedido';
    protected $fillable=[
        'id_repuesto',
        'id_proveedor',
        'cod_rep_prov',
        'cantidad',
        'usuario_id',
        'fecha_emision'
    ];
}
