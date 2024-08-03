<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vale_consignacion extends Model
{
    protected $table='vale_consignacion';
    protected $fillable=[
        'fecha_emision',
        'fecha_expira',
        'rut_cliente',
        'nombre_cliente',
        'telefono_cliente',
        'descripcion',
        'numero_documento',
        'tipo_doc',
        'numero_boucher',
        'usuarios_id',
        'url_pdf',
        'valor',
        'activo'
    ];
}
