<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class fabricante extends Model
{
    protected $table='repuestos_fabricantes';
    protected $fillable=[
        'id_repuestos',
        'id_marcarepuestos',
        'codigo_fab',
        'usuarios_id',
        'activo'
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

}
