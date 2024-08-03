<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nota_de_credito extends Model
{
    protected $table='notas_de_credito';
    protected $fillable=[
        'num_nota_credito',
        'fecha_emision',
        'id_cliente',
        'estado',
        'docum_referencia',
        'motivo_correccion',
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
        'usuarios_id'
    ];
}
