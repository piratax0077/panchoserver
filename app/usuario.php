<?php

namespace repuestos;

use Illuminate\Database\Eloquent\Model;

class usuario extends Model
{
    protected $table='usuarios';
    protected $fillable=[
        'nombreusuario',
        'clave',
        'activo'
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

    public function compras_cab()
    {
        return $this->hasMany('App\compras_cab');
    }

    public function compras_det()
    {
        return $this->hasMany('App\compras_det');
    }

}
