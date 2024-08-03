<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clonacion_fabs extends Model
{
    protected $table = 'clonacion_fabs';
    protected $fillable = [
        'id_repuesto_origen',
        'id_repuesto_destino',
        'fecha_emision'
    ];
}
