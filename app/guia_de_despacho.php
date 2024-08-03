<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class guia_de_despacho extends Model
{
    protected $table='guias_de_despacho';
    protected $fillable=[
        'num_guia_despacho',
        'fecha_emision',
        'TipoDespacho',
        'IndTraslado',
        'TpoTranVenta',
        'id_cliente',
        'neto',
        'exento',
        'iva',
        'descuento',
        'total',
        'patente',
        'RUTTrans',
        'RUTChofer',
        'NombreChofer',
        'DirDest',
        'CmnaDest',
        'CiudadDest',
        'trackid',
        'url_xml',
        'url_pdf',
        'estado',
        'estado_sii',
        'resultado_envio',
        'activo',
        'usuarios_id'
    ];
}
