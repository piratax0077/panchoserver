<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_modelo extends Model
{
    protected $table='clientes';
    protected $fillable=[
        'rut', 
        'tipo_cliente',
        'razon_social',
        'nombres',
        'apellidos',
        'empresa',
        'giro',
        'direccion',
        'direccion_comuna',
        'direccion_ciudad',
        'telf1',
        'telf2',
        'email',
        'contacto',
        'telfc',
        'credito',
        'limite',
        'dias',
        'descuento',
        'tipo_descuento',
        'porcentaje',
        'veces_buscado',
        'activo',
        'usuarios_id'
    ];

    public function cotizacion()
    {
        return $this->hasMany('App\cotizacion');
    }
}
