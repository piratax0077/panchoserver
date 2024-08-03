<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class abono_estado extends Model
{
    protected $table='abono_estado';
    protected $fillable=[
        'descripcion'
    ];

    public function abono(){
        return $this->hasMany('App\abono');
    }
}
