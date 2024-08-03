<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class relacionado extends Model
{
    protected $table='repuestos_relacionados';
    protected $fillable=[
        'id_repuesto_principal',
        'id_repuesto_relacionado',
        'activo',
        'usuarios_id',
    ];
}
