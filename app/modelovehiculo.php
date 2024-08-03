<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class modelovehiculo extends Model
{
    protected $table='modelovehiculos';
    protected $fillable=[
        'modelonombre',
        'zofri',
        'anios_vehiculo',
        'urlfoto',
        'usuarios_id',
        'marcavehiculos_idmarcavehiculo', // ( *)
        'activo',
    ];

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

    public function marcavehiculo()
    {
        //marcavehiculos_idmarcavehiculo (*) es el campo q contiene el id de la marca
        //en la tabla modelovehiculos
        return $this->belongsTo('App\marcavehiculo','marcavehiculos_idmarcavehiculo');
    }


    public function similar()
    {
        return $this->belongsTo('App\similar','id_modelo_vehiculo');
    }
}
