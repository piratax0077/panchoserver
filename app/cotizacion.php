<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cotizacion extends Model
{
    protected $table='cotizaciones';
    protected $fillable=[
        'num_cotizacion',
        'nombre_cotizacion',
        'fecha_emision',
        'fecha_expira',
        'id_cliente',
        'neto',
        'iva',
        'total',
        'activo',
        'usuarios_id'
    ];

    public function cliente()
    {
        return $this->belongsTo('App\cliente_modelo','id_cliente');
    }
}
