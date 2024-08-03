<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class proveedor extends Model
{
    protected $table='proveedores';
    protected $fillable=[
        'empresa_codigo',
        'empresa_nombre',
        'empresa_nombre_corto',
        'empresa_direccion',
        'empresa_web',
        'empresa_telefono',
        'empresa_correo',
        'vendedor_nombres',
        'vendedor_correo',
        'vendedor_telefono',
        'es_transportista',
        'activo',
        'usuarios_id'
    ];


    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

}
