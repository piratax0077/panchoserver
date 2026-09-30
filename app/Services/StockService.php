<?php

namespace App\Services;

use App\repuesto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    private const UBICACIONES = [
        1 => [
            'campo_stock' => 'stock_actual',
            'campo_local' => 'local_id',
            'campo_responsable' => 'id_responsable',
            'campo_fecha' => 'fecha_actualizacion_stock',
        ],
        2 => [
            'campo_stock' => 'stock_actual_dos',
            'campo_local' => 'local_id_dos',
            'campo_responsable' => 'id_responsable_dos',
            'campo_fecha' => 'fecha_actualizacion_stock_dos',
        ],
        3 => [
            'campo_stock' => 'stock_actual_tres',
            'campo_local' => 'local_id_tres',
            'campo_responsable' => 'id_responsable_tres',
            'campo_fecha' => 'fecha_actualizacion_stock_tres',
        ],
    ];

    public function ajustar(
        int $repuestoId,
        int $ubicacionIndice,
        int $stockNuevo,
        int $usuarioId,
        string $motivo,
        ?int $responsableId = null,
        array $contexto = []
    ): array {
        if (!isset(self::UBICACIONES[$ubicacionIndice])) {
            throw new InvalidArgumentException('La ubicación de stock no es válida.');
        }

        return DB::transaction(function () use (
            $repuestoId,
            $ubicacionIndice,
            $stockNuevo,
            $usuarioId,
            $motivo,
            $responsableId,
            $contexto
        ) {
            $config = self::UBICACIONES[$ubicacionIndice];
            $repuesto = repuesto::whereKey($repuestoId)->lockForUpdate()->firstOrFail();
            $stockAnterior = (int) $repuesto->{$config['campo_stock']};

            if ($stockNuevo < $stockAnterior && empty($contexto['disminucion_autorizada'])) {
                throw new InvalidArgumentException('Debe autorizar la disminución de stock con la clave correspondiente.');
            }

            StockAuditContext::run(array_merge($contexto, [
                'tipo' => $contexto['tipo'] ?? 'AJUSTE_MANUAL',
                'motivo' => trim($motivo),
                'usuario_id' => $usuarioId,
            ]), function () use ($repuesto, $config, $stockNuevo, $responsableId, $usuarioId) {
                $repuesto->{$config['campo_stock']} = $stockNuevo;
                $repuesto->{$config['campo_responsable']} = $responsableId;
                $repuesto->{$config['campo_fecha']} = now()->toDateString();
                $repuesto->usuario_id_modifica = $usuarioId;
                $repuesto->save();
            });

            return [
                'repuesto' => $repuesto->fresh(),
            ];
        }, 3);
    }
}
