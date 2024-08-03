<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class familia extends Model
{
    protected $table='familias';
    protected $fillable=[
        'nombrefamilia',
        'porcentaje',
        'porcentaje_flete',
        'prefijo',
        'correlativo',
        'activo'
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

}
