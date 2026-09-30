<?php
namespace App\Services;

use App\local;
use App\repuesto;
use App\RepuestoStock;
use App\StockMovimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockPorLocalService
{
    const CAMPOS = [
        1 => ['stock_actual', 'local_id', 'ubicacion', 'id_responsable'],
        2 => ['stock_actual_dos', 'local_id_dos', 'ubicacion_dos', 'id_responsable_dos'],
        3 => ['stock_actual_tres', 'local_id_tres', 'ubicacion_tres', 'id_responsable_tres'],
    ];

    public static function habilitado(): bool { return (bool) config('inventario.stock_por_local', false); }

    private function error($mensaje)
    {
        throw ValidationException::withMessages(['stock' => $mensaje]);
    }

    public function filasLegadas($repuesto, ?array $localesValidos = null): array
    {
        $filas = [];
        foreach (self::CAMPOS as $indice => $campos) {
            list($stock, $local, $ubicacion, $responsable) = $campos;
            $cantidad = (int) $repuesto->$stock;
            $localId = (int) $repuesto->$local;
            if ($cantidad < 0) $this->error('Repuesto '.$repuesto->id.': stock negativo en posición '.$indice.'.');
            if (!$localId) {
                if ($cantidad === 0) continue;
                // Correspondencia confirmada por administración para columnas sin local.
                $localId = [1 => 1, 2 => 3, 3 => 4][$indice];
            }
            if ($localesValidos !== null ? !isset($localesValidos[$localId]) : !local::whereKey($localId)->exists()) $this->error('Repuesto '.$repuesto->id.': local inexistente '.$localId.'.');
            if (isset($filas[$localId])) $this->error('Repuesto '.$repuesto->id.': local repetido '.$localId.'. Debe reconciliar ambas posiciones.');
            $filas[$localId] = [
                'repuesto_id' => $repuesto->id, 'local_id' => $localId,
                'cantidad' => $cantidad, 'ubicacion_fisica' => $repuesto->$ubicacion,
                'responsable_id' => $repuesto->$responsable ?: null,
            ];
        }
        return $filas;
    }

    // Se ejecuta antes del save legado, dentro de la misma transacción.
    public function validarEscrituraLegada(repuesto $modelo): void
    {
        if (!$modelo->exists) return;
        $actual = DB::table('repuestos')->where('id', $modelo->id)->lockForUpdate()->first();
        foreach (self::CAMPOS as $campos) {
            list($stock, $local, $ubicacion, $responsable) = $campos;
            if ($modelo->isDirty($stock) && (string) $modelo->getRawOriginal($local) !== (string) $actual->$local) {
                $this->error('La ubicación del stock cambió. Recargue y vuelva a intentar.');
            }
            foreach ($campos as $campo) {
                if ($modelo->isDirty($campo) && (string) $modelo->getRawOriginal($campo) !== (string) $actual->$campo) {
                    $this->error('El inventario cambió mientras trabajaba. Recargue y vuelva a intentar.');
                }
            }
            if ($modelo->isDirty($local) && (int) $modelo->$local &&
                $this->cantidad($modelo->id, (int) $modelo->$local) !== 0) {
                $this->error('El local de destino ya tiene stock. Use la gestión de ubicaciones.');
            }
            if ($modelo->isDirty($local) && (int) $actual->$local && (int) $actual->$stock !== 0) {
                $this->error('No puede cambiar el local de una posición con stock. Utilice Trasladar.');
            }
        }
    }

    public function sincronizarLegado(repuesto $modelo, ?callable $inferir = null): void
    {
        $hayCambios = $modelo->wasRecentlyCreated;
        foreach (self::CAMPOS as $campos) {
            foreach ($campos as $campo) $hayCambios = $hayCambios || $modelo->wasChanged($campo);
        }
        if (!$hayCambios) return;
        // Algunos callers usan selects parciales: leer la fila completa ya guardada.
        $completo = DB::table('repuestos')->where('id', $modelo->id)->first();
        foreach ($this->filasLegadas($completo) as $fila) {
            $registro = RepuestoStock::firstOrNew(['repuesto_id' => $modelo->id, 'local_id' => $fila['local_id']]);
            $anterior = (int) $registro->cantidad;
            $registro->fill($fila)->save();
            if ($anterior !== $registro->cantidad) {
                $local = local::whereKey($registro->local_id)->lockForUpdate()->firstOrFail();
                if (!$local->activo) $this->error('No se puede modificar el stock de un local inactivo.');
                $this->registrar($registro, $anterior, array_merge($inferir ? $inferir($registro->cantidad - $anterior) : [], StockAuditContext::current()));
            }
        }
    }

    public function disponibles($repuestoId)
    {
        return RepuestoStock::query()->join('locales', 'locales.id', '=', 'repuesto_stocks.local_id')
            ->leftJoin('users', 'users.id', '=', 'repuesto_stocks.responsable_id')
            ->where('repuesto_id', $repuestoId)->where('locales.activo', 1)
            ->select('repuesto_stocks.repuesto_id as id', 'repuesto_stocks.local_id as id_local',
                'repuesto_stocks.cantidad as stock_actual', 'locales.local_nombre', 'users.name as responsable')->get();
    }

    public static function total($repuesto): int
    {
        if (self::habilitado()) {
            return (int) RepuestoStock::where('repuesto_id', $repuesto->id)->sum('cantidad');
        }
        return (int) $repuesto->stock_actual + (int) $repuesto->stock_actual_dos + (int) $repuesto->stock_actual_tres;
    }

    public static function totalSql(): string
    {
        return self::habilitado()
            ? '(SELECT COALESCE(SUM(rs.cantidad), 0) FROM repuesto_stocks rs WHERE rs.repuesto_id = repuestos.id)'
            : '(CAST(COALESCE(repuestos.stock_actual,0) AS SIGNED) + CAST(COALESCE(repuestos.stock_actual_dos,0) AS SIGNED) + CAST(COALESCE(repuestos.stock_actual_tres,0) AS SIGNED))';
    }

    public function cantidad($repuestoId, $localId): int
    {
        return (int) RepuestoStock::where('repuesto_id', $repuestoId)->where('local_id', $localId)->value('cantidad');
    }

    private function bloquear($repuestoId, array $locales)
    {
        $locales = array_values(array_unique(array_map('intval', $locales)));
        sort($locales);
        foreach ($locales as $localId) {
            $local = local::whereKey($localId)->lockForUpdate()->first();
            if (!$local || !$local->activo) $this->error('El local no existe o está inactivo.');
        }
        return repuesto::whereKey($repuestoId)->lockForUpdate()->firstOrFail();
    }

    public function asociar($repuestoId, $localId, array $datos = [])
    {
        return DB::transaction(function () use ($repuestoId, $localId, $datos) {
            $this->bloquear($repuestoId, [$localId]);
            $fila = RepuestoStock::firstOrCreate(
                ['repuesto_id' => $repuestoId, 'local_id' => $localId], ['cantidad' => 0]
            );
            $fila->fill(\Illuminate\Support\Arr::only($datos, ['ubicacion_fisica', 'responsable_id']))->save();
            $this->reflejar($fila);
            return $fila;
        }, 3);
    }

    public function ajustar($repuestoId, $localId, int $nuevo, int $esperado, array $contexto)
    {
        return DB::transaction(function () use ($repuestoId, $localId, $nuevo, $esperado, $contexto) {
            $this->bloquear($repuestoId, [$localId]);
            $fila = RepuestoStock::where('repuesto_id', $repuestoId)->where('local_id', $localId)->firstOrFail();
            if ($fila->cantidad !== $esperado) $this->error('El stock cambió. Recargue antes de ajustar.');
            return $this->guardarCantidad($fila, $nuevo, $contexto);
        }, 3);
    }

    public function mover($repuestoId, $localId, int $diferencia, array $contexto = [])
    {
        return DB::transaction(function () use ($repuestoId, $localId, $diferencia, $contexto) {
            $this->bloquear($repuestoId, [$localId]);
            $fila = RepuestoStock::firstOrCreate(['repuesto_id' => $repuestoId, 'local_id' => $localId], ['cantidad' => 0]);
            return $this->guardarCantidad($fila, $fila->cantidad + $diferencia, $contexto);
        }, 3);
    }

    public function trasladar($repuestoId, $origen, $destino, int $cantidad, array $contexto)
    {
        if ((int) $origen === (int) $destino || $cantidad <= 0) $this->error('Seleccione locales distintos y una cantidad positiva.');
        return DB::transaction(function () use ($repuestoId, $origen, $destino, $cantidad, $contexto) {
            $this->bloquear($repuestoId, [$origen, $destino]);
            $salida = RepuestoStock::where('repuesto_id', $repuestoId)->where('local_id', $origen)->firstOrFail();
            $entrada = RepuestoStock::firstOrCreate(['repuesto_id' => $repuestoId, 'local_id' => $destino], ['cantidad' => 0]);
            $contexto['grupo_uuid'] = (string) Str::uuid();
            $contexto['tipo'] = 'TRASPASO';
            $this->guardarCantidad($salida, $salida->cantidad - $cantidad, $contexto);
            $this->guardarCantidad($entrada, $entrada->cantidad + $cantidad, $contexto);
        }, 3);
    }

    private function guardarCantidad(RepuestoStock $fila, int $nuevo, array $contexto)
    {
        if ($nuevo < 0) $this->error('Stock insuficiente en el local seleccionado.');
        if ($nuevo > 2147483647) $this->error('La cantidad supera el máximo permitido.');
        $anterior = $fila->cantidad;
        if ($anterior === $nuevo) return $fila;
        $fila->cantidad = $nuevo;
        $fila->save();
        $this->reflejar($fila);
        $this->registrar($fila, $anterior, array_merge(StockAuditContext::current(), $contexto));
        return $fila;
    }

    private function reflejar(RepuestoStock $fila): void
    {
        $repuesto = DB::table('repuestos')->where('id', $fila->repuesto_id)->first();
        foreach (self::CAMPOS as $indice => $campos) {
            list($stock, $local, $ubicacion, $responsable) = $campos;
            // Mantener los espejos existentes; reservar los slots clásicos para los locales originales.
            $predeterminado = [1 => 1, 2 => 3, 3 => 4][$indice];
            if ((int) $repuesto->$local !== $fila->local_id &&
                !(!(int) $repuesto->$local && $fila->local_id === $predeterminado)) continue;
            if ($fila->cantidad > 65535) $this->error('Este local conserva una columna antigua con máximo de 65535 unidades.');
            DB::table('repuestos')->where('id', $fila->repuesto_id)->update([
                $stock => $fila->cantidad, $local => $fila->local_id,
                $ubicacion => $fila->ubicacion_fisica ?? '', $responsable => $fila->responsable_id,
                'fecha_actualizacion_stock'.($indice === 1 ? '' : ($indice === 2 ? '_dos' : '_tres')) => now()->toDateString(),
            ]);
        }
    }

    private function registrar(RepuestoStock $fila, int $anterior, array $contexto): void
    {
        StockMovimiento::create([
            'repuesto_id' => $fila->repuesto_id, 'local_id' => $fila->local_id,
            'repuesto_stock_id' => $fila->id, 'ubicacion_indice' => 0, 'campo_stock' => 'repuesto_stocks.cantidad',
            'stock_anterior' => $anterior, 'stock_nuevo' => $fila->cantidad,
            'cantidad_movimiento' => $fila->cantidad - $anterior,
            'tipo' => $contexto['tipo'] ?? 'MOVIMIENTO_SISTEMA',
            'motivo' => $contexto['motivo'] ?? 'Movimiento de inventario por local',
            'usuario_id' => $contexto['usuario_id'] ?? Auth::id(), 'responsable_id' => $fila->responsable_id,
            'referencia_tipo' => $contexto['referencia_tipo'] ?? null,
            'referencia_id' => $contexto['referencia_id'] ?? null,
            'grupo_uuid' => $contexto['grupo_uuid'] ?? (string) Str::uuid(),
            'ip' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
