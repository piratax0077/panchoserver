<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nota_de_debito extends Model
{
    protected $table='notas_de_debito';
    protected $fillable=[
        'num_nota_debito',
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
