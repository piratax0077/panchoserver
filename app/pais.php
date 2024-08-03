<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class pais extends Model
{
    protected $table='paises';
    protected $fillable=[
        'nombre_pais',
        'activo'
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

}
