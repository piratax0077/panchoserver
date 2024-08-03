<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class familia_medidas extends Model
{
    //
    protected $table = 'familia_medidas';
    protected $fillable=[
        'id_familia',
        'descripcion'
    ];
}
