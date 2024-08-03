<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class codigos_oem extends Model
{
    protected $table = 'codigos_oem';
    protected $fillable=[
        'marca',
        'modelo',
        'producto',
        'cod_producto',
        'cantidad'
    ];
}
