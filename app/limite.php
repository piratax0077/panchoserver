<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class limite extends Model
{
    protected $table='limites';
    protected $fillable=[
        'valor',
    ];
}
