<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tipo_documento extends Model
{
    protected $table='tipo_documentos';
    protected $fillable=[
        'codigo_documento',
        'nombre_documento',
        'activo',
        'usuarios_id'
    ];
}
