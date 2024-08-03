<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class consignacion extends Model
{
    protected $table='consignaciones';
    protected $fillable=[
        'num_consignacion',
        'nombre_consignacion',
        'fecha_emision',
        'fecha_expira',
        'id_cliente',
        'neto',
        'iva',
        'total',
        'activo',
        'usuarios_id',
        'url_pdf'
    ];

    public function cliente()
    {
        return $this->belongsTo('App\cliente_modelo','id_cliente');
    }
}
