<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class repuesto extends Model
{
    protected $table='repuestos';
    //FALTA PAIS DE ORIGEN Y STOCK
    protected $fillable=[
        'codigo_interno',
        'descripcion',
        'medidas',
        'version_vehiculo',
        'cod_repuesto_proveedor',
        'codigo_OEM_repuesto',
        'precio_compra',
        'pu_neto',
        'precio_venta',
        'precio_antiguo',
        'exento_iva',
        'stock_minimo',
        'stock_maximo',
        'stock_actual',
        'stock_actual_dos',
        'stock_actual_tres',
        'id_responsable',
        'id_responsable_dos',
        'id_responsable_tres',
        'fecha_actualiza_precio',
        'codigo_barras',
        'id_unidad_venta',
        'id_familia',
        'id_marca_repuesto',
        'id_proveedor',
        'id_pais',
        'ubicacion',
        'ubicacion_dos',
        'ubicacion_tres',
        'local_id',
        'local_id_dos',
        'local_id_tres',
        'usuarios_id',
        'activo',
        'fecha_reposicion',
        'fecha_actualizacion_stock',
        'fecha_actualizacion_stock_dos',
        'fecha_actualizacion_stock_tres',
        'usuario_id_modifica',
        'oferta',
        'estado'
    ];

  	public function familia()
  	{
  		return $this->belongsTo('App\familia','id_familia');
  	}

    public function marcarepuesto()
    {
    	return $this->belongsTo('App\marcarepuesto','id_marca_repuesto');
    }

    public function proveedor()
    {
    	return $this->belongsTo('App\proveedor','id_proveedor');
    }

    public function pais()
    {
        return $this->belongsTo('App\pais','id_pais');
    }

    public function solicitud()
    {
        return $this->hasMany('App\solicitudes');
    }
}
