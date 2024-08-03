<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vale_mercaderia extends Model
{
    protected $table='vale_mercaderia';
    protected $fillable=[
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
