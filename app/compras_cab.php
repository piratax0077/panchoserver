<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class compras_cab extends Model
{
    protected $table='compras_cab';
    protected $fillable=[
        'factura_id_proveedor',
        'factura_numero',
        'factura_fecha',
        'factura_es_credito',
        'factura_fecha_venc',
        'factura_subtotal',
        'factura_iva',
        'factura_total',
        'factura_observaciones',
        'en_ot',
        'id_transportista',
        'num_fac_transportista',
        'pagada',
        'activo',
        'usuarios_id',
    ];

    public function compras_det()
    {
    	return $this->hasMany('App\compras_det');
    }

}
