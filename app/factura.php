<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class factura extends Model
{
    protected $table='facturas';
    protected $fillable=[
        'num_factura',
        'fecha_emision',
        'es_credito',
        'es_delivery',
        'id_cliente',
        'estado',
        'docum_referencia',
        'neto',
        'exento',
        'iva',
        'total',
        'trackid',
        'url_xml',
        'url_pdf',
        'url_caf',
        'resultado_envio',
        'estado_sii',
        'activo',
        'usuarios_id',
        'devuelto'
    ];
}
