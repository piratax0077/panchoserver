<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class folio extends Model
{
    protected $table='folios';
    protected $fillable=[
        'id_correlativos',
        'fecha_autorizacion',
        'tipo_dte_sii',
        'id_local',
        'desde',
        'hasta',
        'autorizacion_xml',
        'activo',
        'usuarios_id'
    ];
}
