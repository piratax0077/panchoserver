<?php

namespace App\Helpers;

use App\vale_mercaderia_movimiento;
use Illuminate\Support\Facades\Auth;

class MovimientoValeHelper
{
    /**
     * Registra un movimiento de repuesto en vale de mercadería
     * 
     * @param int $vale_id ID del vale de mercadería
     * @param int $repuesto_id ID del repuesto
     * @param int $cantidad Cantidad de unidades
     * @param string $tipo_movimiento Tipo: 'agregar', 'eliminar', 'devolver'
     * @param int|null $local_id ID del local/origen (1=Bodega, 3=Segunda ubicación, 4=Tercera ubicación)
     * @param string|null $numero_voucher Número de comprobante
     * @param string|null $observaciones Observaciones adicionales
     * 
     * @return vale_mercaderia_movimiento
     */
    public static function registrarMovimiento(
        $vale_id,
        $repuesto_id,
        $cantidad,
        $tipo_movimiento,
        $local_id = null,
        $numero_voucher = null,
        $observaciones = null
    ) {
        return vale_mercaderia_movimiento::create([
            'vale_mercaderia_id' => $vale_id,
            'repuesto_id' => $repuesto_id,
            'usuario_id' => Auth::check() ? Auth::user()->id : null,
            'local_id' => $local_id,
            'cantidad' => $cantidad,
            'tipo_movimiento' => $tipo_movimiento,
            'numero_voucher' => $numero_voucher,
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Obtiene el historial de movimientos de un repuesto en un período
     * 
     * @param int $repuesto_id ID del repuesto
     * @param string $fecha_inicio Fecha inicial (Y-m-d)
     * @param string $fecha_fin Fecha final (Y-m-d)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function historicoRepuesto($repuesto_id, $fecha_inicio, $fecha_fin)
    {
        return vale_mercaderia_movimiento::delRepuesto($repuesto_id)
            ->entreFechas($fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calcula el balance de stock de un repuesto por origen
     * 
     * @param int $repuesto_id ID del repuesto
     * @param string $fecha_inicio Fecha inicial (Y-m-d)
     * @param string $fecha_fin Fecha final (Y-m-d)
     * 
     * @return array Balance por tipo de movimiento
     */
    public static function balanceRepuesto($repuesto_id, $fecha_inicio, $fecha_fin)
    {
        $movimientos = self::historicoRepuesto($repuesto_id, $fecha_inicio, $fecha_fin);
        
        $balance = [
            'total_salidas' => 0,
            'total_entradas' => 0,
            'balance_neto' => 0,
            'por_local' => [
                1 => ['salidas' => 0, 'entradas' => 0],
                3 => ['salidas' => 0, 'entradas' => 0],
                4 => ['salidas' => 0, 'entradas' => 0],
            ]
        ];

        foreach ($movimientos as $mov) {
            if ($mov->tipo_movimiento === 'agregar') {
                $balance['total_entradas'] += $mov->cantidad;
                if (isset($balance['por_local'][$mov->local_id])) {
                    $balance['por_local'][$mov->local_id]['entradas'] += $mov->cantidad;
                }
            } else { // eliminar o devolver son salidas
                $balance['total_salidas'] += $mov->cantidad;
                if (isset($balance['por_local'][$mov->local_id])) {
                    $balance['por_local'][$mov->local_id]['salidas'] += $mov->cantidad;
                }
            }
        }

        $balance['balance_neto'] = $balance['total_entradas'] - $balance['total_salidas'];

        return $balance;
    }

    /**
     * Obtiene los repuestos más sacados en un período
     * 
     * @param string $fecha_inicio Fecha inicial (Y-m-d)
     * @param string $fecha_fin Fecha final (Y-m-d)
     * @param int $limite Número máximo de registros
     * 
     * @return array
     */
    public static function repuestosMasSacados($fecha_inicio, $fecha_fin, $limite = 10)
    {
        return vale_mercaderia_movimiento::entreFechas($fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59')
            ->whereIn('tipo_movimiento', ['eliminar', 'devolver'])
            ->groupBy('repuesto_id')
            ->selectRaw('repuesto_id, SUM(cantidad) as total_cantidad')
            ->orderByRaw('total_cantidad DESC')
            ->limit($limite)
            ->get();
    }
}
