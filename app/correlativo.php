<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class correlativo extends Model
{
    protected $table='correlativos';
    protected $fillable=[
        'documento',
        'id_local',
        'serie',
        'correlativo',
        'fecha_autorizacion',
        'tipo_dte_sii',
        'desde',
        'hasta',
        'alarma',
        'caf_xml',
        'usuarios_id',
        'activo'
    ];
}
