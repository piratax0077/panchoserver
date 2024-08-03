<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cajero extends Model
{
    protected $table='cajeros';
    protected $fillable=[
        'id_usuario',
        'fecha_emision'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'id_usuario');
    }
}
