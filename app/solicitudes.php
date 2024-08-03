<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class solicitudes extends Model
{
    protected $table='solicitudes';
    protected $fillable=[
        'id_repuestos',
        'usuario_id',
        'cantidad',
        'activo'
    ];

    public function repuesto(){
        return $this->belongsTo('App\repuesto','id_repuestos');
}
public function user(){
    return $this->belongsTo('App\user','usuario_id');
}
}
