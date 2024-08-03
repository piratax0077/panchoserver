<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class boleta extends Model
{
    protected $table='boletas';
    protected $fillable=[
        'num_boleta',
        'fecha_emision',
        'es_credito',
        'es_delivery',
        'id_cliente',
        'estado',
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
        'id_resumen',
        'activo',
        'usuarios_id',
        'devuelto'
    ];

    public function user(){
        return $this->belongsTo('App\user','usuarios_id');
}
}
