<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class descfamtemp extends Model
{
    protected $table='descfamtemp';
    protected $fillable=[
        'id_familia',
        'porcentaje',
        'usuarios_id',
    ];
}
