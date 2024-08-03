<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class oem extends Model
{
    protected $table='oems';
    protected $fillable=[
        'codigo_oem',
        'id_repuestos',
        'usuarios_id',
        'activo'
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }
}
