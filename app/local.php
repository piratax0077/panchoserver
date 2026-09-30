<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class local extends Model
{
    protected $table='locales';
    protected $casts = ['activo' => 'boolean'];
    protected $fillable=[
        'local_nombre',
        'local_direccion',
        'local_telefono',
        'activo',
    ];
}
