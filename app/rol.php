<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class rol extends Model
{
    protected $table='roles';
    protected $fillable=[
        'nombrerol',
    ];

    public function user()
    {
        return $this->hasMany('App\User');
    }
}
