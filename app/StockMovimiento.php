<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockMovimiento extends Model
{
    protected $table = 'stock_movimientos';

    const UPDATED_AT = null;

    protected $fillable = [
        'repuesto_id',
        'repuesto_stock_id',
        'local_id',
        'ubicacion_indice',
        'campo_stock',
        'stock_anterior',
        'cantidad_movimiento',
        'stock_nuevo',
        'tipo',
        'motivo',
        'usuario_id',
        'responsable_id',
        'referencia_tipo',
        'referencia_id',
        'grupo_uuid',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'stock_anterior' => 'integer',
        'cantidad_movimiento' => 'integer',
        'stock_nuevo' => 'integer',
        'created_at' => 'datetime',
    ];

    public function repuesto()
    {
        return $this->belongsTo(repuesto::class, 'repuesto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
