<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class dia extends Model
{
    protected $table='dias';
    protected $fillable=[
        'valor',
    ];
}
