<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class abono_detalle extends Model
{
    protected $table='abono_detalle';
    protected $fillable=[
        'id_abono',
        'estado',
        'id_repuesto',
        'id_proveedor',
        'cantidad',
        'precio_unitario',
        'total',
        'activo'
    ];

    public function abono_estado(){
        return $this->belongsTo('App\abono_estado','estado');
}
}
