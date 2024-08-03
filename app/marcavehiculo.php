<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class marcavehiculo extends Model
{
	//Laravel asume que  el campo clave es entero incremental por eso en la vista muestra valores 0.
    //Al ponerle la instrucción  public $incrementing = false le estoy
    //diciendo a laravel que NO ASUMA EL CAMPO CLAVE como incremental entero sino el que yo lo
    //defino como primaryKey

	public $incrementing = false;
    protected $table='marcavehiculos';
    protected $primaryKey='idmarcavehiculo';
    protected $fillable=[
        'idmarcavehiculo',
        'marcanombre',
        'urlfoto',
        'usuarios_id',
        'activo',
    ];


    public function modelovehiculo()
    {
        return $this->hasMany('App\modelovehiculo');
    }

    public function repuesto()
    {
        return $this->hasMany('App\repuesto');
    }

    public function similar()
    {
        return $this->belongsTo('App\similar','id_marca_vehiculo');
    }

}
