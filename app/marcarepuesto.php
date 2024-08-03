<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class marcarepuesto extends Model
{
    protected $table='marcarepuestos';
    protected $fillable=[
        'marcarepuesto',
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }
}
