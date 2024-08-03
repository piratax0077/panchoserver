<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bodega extends Model
{
    protected $table='bodega';
    protected $fillable=[
        'descripcion'
    ];
}
