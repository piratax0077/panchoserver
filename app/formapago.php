<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class formapago extends Model
{
    protected $table='formapago';
    protected $fillable=[
        'formapago',
        'usuarios_id',
        'activo'
    ];
}
