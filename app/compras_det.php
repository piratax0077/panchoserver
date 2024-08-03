<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class compras_det extends Model
{
    protected $table='compras_det';
    protected $fillable=[
        'id_factura_cab',
        'item',
        'id_repuestos',
        'cantidad',
        'pu',
        'subtotal',
        'costos',
        'costos_descripcion',
        'precio_sugerido',
        'id_local',
        'activo',
        'usuarios_id',
    ];

    public function compras_cab()
  	{
  		//Cuando no se define el campo id de la tabla, laravel
  		//asume el campo llamado 'id'
  		return $this->belongsTo('App\compras_cab');
  	}

}


