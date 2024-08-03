<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class similar extends Model
{
    protected $table='similares';
    protected $fillable=[
        'codigo_OEM_repuesto',
        'anios_vehiculo',
        'activo',
        'id_repuestos',
        'id_marca_vehiculo',
        'id_modelo_vehiculo',
        'usuarios_id'
    ];

    public function marcavehiculo()
    {
        return $this->belongsTo('App\marcavehiculo','id_marca_vehiculo');
    }

    public function modelovehiculo()
    {
        return $this->belongsTo('App\modelovehiculo','id_modelo_vehiculo');
    }

}
