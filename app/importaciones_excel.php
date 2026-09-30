<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class importaciones_excel extends Model
{
    use HasFactory;

    protected $table = 'importaciones_excel';

    protected $fillable = [
        'nombre_archivo',
        'tipo_importacion',
        'total_filas',
        'filas_procesadas',
        'total_errores',
        'estado',
        'errores_json',
        'usuarios_id',
        'id_proveedor'
    ];

    protected $casts = [
        'errores_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con usuarios
    public function usuario()
    {
        return $this->belongsTo('App\User', 'usuarios_id');
    }

    // Relación con proveedores
    public function proveedor()
    {
        return $this->belongsTo('App\proveedor', 'id_proveedor');
    }
}
