<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vale_mercaderia_movimiento extends Model
{
    protected $table = 'vale_mercaderia_movimientos';
    
    protected $fillable = [
        'vale_mercaderia_id',
        'repuesto_id',
        'usuario_id',
        'local_id',
        'cantidad',
        'tipo_movimiento',
        'numero_voucher',
        'observaciones'
    ];

    /**
     * Relación con el vale de mercadería
     */
    public function vale()
    {
        return $this->belongsTo(vale_mercaderia::class, 'vale_mercaderia_id');
    }

    /**
     * Relación con el repuesto
     */
    public function repuesto()
    {
        return $this->belongsTo(repuesto::class, 'repuesto_id');
    }

    /**
     * Relación con el usuario que realizó la operación
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope para obtener movimientos de un vale específico
     */
    public function scopeDelVale($query, $vale_id)
    {
        return $query->where('vale_mercaderia_id', $vale_id);
    }

    /**
     * Scope para obtener movimientos de un repuesto específico
     */
    public function scopeDelRepuesto($query, $repuesto_id)
    {
        return $query->where('repuesto_id', $repuesto_id);
    }

    /**
     * Scope para obtener movimientos de un tipo específico
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    /**
     * Scope para obtener movimientos en un rango de fechas
     */
    public function scopeEntreFechas($query, $fecha_inicio, $fecha_fin)
    {
        return $query->whereBetween('created_at', [$fecha_inicio, $fecha_fin]);
    }

    /**
     * Obtiene el nombre del local/origen
     */
    public function getNombreLocal()
    {
        $locales = [
            1 => 'Bodega Principal',
            3 => 'Segunda Ubicación',
            4 => 'Tercera Ubicación'
        ];
        return $locales[$this->local_id] ?? 'Desconocido';
    }
}
