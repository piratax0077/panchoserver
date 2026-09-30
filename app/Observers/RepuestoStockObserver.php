<?php

namespace App\Observers;

use App\repuesto;
use App\Services\StockAuditContext;
use App\StockMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RepuestoStockObserver
{
    private const UBICACIONES = [
        1 => ['stock' => 'stock_actual', 'local' => 'local_id', 'responsable' => 'id_responsable'],
        2 => ['stock' => 'stock_actual_dos', 'local' => 'local_id_dos', 'responsable' => 'id_responsable_dos'],
        3 => ['stock' => 'stock_actual_tres', 'local' => 'local_id_tres', 'responsable' => 'id_responsable_tres'],
    ];

    public function created(repuesto $repuesto): void
    {
        if (\App\Services\StockPorLocalService::habilitado()) {
            app(\App\Services\StockPorLocalService::class)->sincronizarLegado($repuesto, function ($diferencia) { return $this->inferirOrigen($diferencia); });
            return;
        }
        foreach (self::UBICACIONES as $indice => $config) {
            $nuevo = (int) $repuesto->{$config['stock']};
            if ($nuevo !== 0) {
                $this->registrarMovimiento($repuesto, $indice, $config, 0, $nuevo);
            }
        }
    }

    public function updated(repuesto $repuesto): void
    {
        if (\App\Services\StockPorLocalService::habilitado()) {
            app(\App\Services\StockPorLocalService::class)->sincronizarLegado($repuesto, function ($diferencia) { return $this->inferirOrigen($diferencia); });
            return;
        }
        foreach (self::UBICACIONES as $indice => $config) {
            if (!$repuesto->wasChanged($config['stock'])) {
                continue;
            }

            $anterior = (int) $repuesto->getOriginal($config['stock']);
            $nuevo = (int) $repuesto->{$config['stock']};

            if ($anterior === $nuevo) {
                continue;
            }

            $this->registrarMovimiento($repuesto, $indice, $config, $anterior, $nuevo);
        }
    }

    private function registrarMovimiento(
        repuesto $repuesto,
        int $indice,
        array $config,
        int $anterior,
        int $nuevo
    ): void {
        $context = StockAuditContext::current();
        $inferencia = $this->inferirOrigen($nuevo - $anterior);
        $usuarioId = array_key_exists('usuario_id', $context)
            ? $context['usuario_id']
            : Auth::id();

        StockMovimiento::create([
            'repuesto_id' => $repuesto->id,
            'local_id' => $repuesto->{$config['local']} ?: null,
            'ubicacion_indice' => $indice,
            'campo_stock' => $config['stock'],
            'stock_anterior' => $anterior,
            'cantidad_movimiento' => $nuevo - $anterior,
            'stock_nuevo' => $nuevo,
            'tipo' => $context['tipo'] ?? $inferencia['tipo'],
            'motivo' => $context['motivo'] ?? $inferencia['motivo'],
            'usuario_id' => $usuarioId,
            'responsable_id' => $repuesto->{$config['responsable']} ?: null,
            'referencia_tipo' => $context['referencia_tipo'] ?? $inferencia['referencia_tipo'],
            'referencia_id' => $context['referencia_id'] ?? $inferencia['referencia_id'],
            'grupo_uuid' => $context['grupo_uuid'] ?? StockAuditContext::requestGroupUuid(),
            'ip' => $context['ip'] ?? request()->ip(),
            'user_agent' => substr((string) ($context['user_agent'] ?? request()->userAgent()), 0, 500),
        ]);
    }

    private function inferirOrigen(int $diferencia): array
    {
        $route = request()->route();
        $action = $route ? (string) $route->getActionMethod() : 'proceso_sistema';
        $controller = $route ? (string) ($route->getAction()['controller'] ?? '') : '';
        $controllerName = Str::lower(class_basename(Str::before($controller, '@')));
        $actionName = Str::lower($action);
        $tipo = 'MOVIMIENTO_SISTEMA';

        if (Str::contains($controllerName, 'factuprodu')) {
            $tipo = 'COMPRA';
        } elseif (Str::contains($controllerName, 'ventas')) {
            if (Str::contains($actionName, ['anular', 'eliminar', 'devolver', 'devolucion'])) {
                $tipo = $diferencia >= 0 ? 'ANULACION_VENTA' : 'VENTA';
            } else {
                $tipo = $diferencia < 0 ? 'VENTA' : 'DEVOLUCION_VENTA';
            }
        } elseif (Str::contains($controllerName, ['guia_despacho', 'inventario'])) {
            $tipo = Str::contains($actionName, ['traspas', 'transfer'])
                ? 'TRASPASO'
                : 'MOVIMIENTO_INVENTARIO';
        } elseif (Str::contains($controllerName, 'api')) {
            $tipo = 'MOVIMIENTO_API';
        } elseif (Str::contains($controllerName, 'reportes')) {
            $tipo = 'AJUSTE_REPORTE';
        }

        $referenciaId = null;
        if ($route) {
            foreach ($route->parameters() as $parameter) {
                if (is_numeric($parameter)) {
                    $referenciaId = (int) $parameter;
                    break;
                }
            }
        }

        return [
            'tipo' => $tipo,
            'motivo' => 'Movimiento automático generado por '.$controllerName.'@'.$action,
            'referencia_tipo' => $controllerName ?: 'sistema',
            'referencia_id' => $referenciaId,
        ];
    }
}
