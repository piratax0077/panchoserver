<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class rcof_boletas extends Model
{
    protected $table='rcof_boletas';
    protected $fillable=[
        'fecha_emision',
        'num_rcof',
        'fecha_inicio',
        'fecha_final',
        'secuencia',
        'tipo_dte',
        'estado',
        'neto_39',
        'iva_39',
        'exento_39',
        'total_39',
        'folios_emitidos_39',
        'folios_anulados_39',
        'folios_utilizados_39',
        'rango_inicial_39',
        'rango_final_39',
        'neto_61',
        'iva_61',
        'exento_61',
        'total_61',
        'folios_emitidos_61',
        'folios_anulados_61',
        'folios_utilizados_61',
        'rango_inicial_61',
        'rango_final_61',
        'trackid',
        'url_xml',
        'estado_sii',
        'detalle',
        'activo',
        'usuarios_id'
    ];
}
