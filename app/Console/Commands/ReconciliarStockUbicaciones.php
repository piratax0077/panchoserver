<?php
namespace App\Console\Commands;

use App\Services\NormalizadorStockLegado;
use App\Services\StockPorLocalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReconciliarStockUbicaciones extends Command
{
    protected $signature = 'stock:reconciliar-ubicaciones {--aplicar : Aplicar el criterio confirmado, con respaldo e historial}';
    protected $description = 'Negativos a cero; locales repetidos y sin asignar según columna. Sin --aplicar solo informa.';

    public function handle(NormalizadorStockLegado $normalizador)
    {
        if (StockPorLocalService::habilitado() ||
            (Schema::hasTable('repuesto_stocks') && DB::table('repuesto_stocks')->exists())) {
            $this->error('La reconciliación solo puede ejecutarse antes de migrar las existencias.');
            return 1;
        }
        if ($this->option('aplicar') && !app()->isDownForMaintenance()) {
            $this->error('Debe activar mantenimiento antes de aplicar.');
            return 1;
        }
        if (DB::table('locales')->whereIn('id', [1,3,4])->where('activo', 1)->count() !== 3) {
            $this->error('Los locales 1, 3 y 4 deben existir y estar activos.');
            return 1;
        }
        $planes = [];
        $negativos = 0;
        $locales = 0;
        DB::table('repuestos')->orderBy('id')->chunkById(1000, function ($filas) use ($normalizador, &$planes, &$negativos, &$locales) {
            foreach ($filas as $fila) {
                $cambios = $normalizador->cambios($fila);
                if (!$cambios) continue;
                $antes = [];
                foreach (StockPorLocalService::CAMPOS as $campos) {
                    foreach ($campos as $campo) $antes[$campo] = $fila->$campo;
                }
                foreach ($cambios as $campo => $valor) {
                    if (strpos($campo, 'stock_actual') === 0) $negativos++; else $locales++;
                }
                $planes[] = ['id' => $fila->id, 'antes' => $antes, 'cambios' => $cambios];
            }
        });
        $this->info(count($planes).' repuestos a normalizar; '.$negativos.' cantidades negativas; '.$locales.' asignaciones de local.');
        if (!$this->option('aplicar') || !$planes) return 0;

        $grupo = (string) Str::uuid();
        $directorio = storage_path('app/stock_ubicaciones_respaldos');
        if (!is_dir($directorio) && !mkdir($directorio, 0700, true)) throw new \RuntimeException('No se pudo crear el directorio de respaldo.');
        $archivo = $directorio.'/reconciliacion_'.date('Ymd_His').'_'.$grupo.'.jsonl';
        $handle = fopen($archivo, 'xb');
        if (!$handle) throw new \RuntimeException('No se pudo crear el respaldo.');
        try {
            foreach ($planes as $plan) {
                $linea = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
                if (fwrite($handle, $linea) !== strlen($linea)) throw new \RuntimeException('Respaldo incompleto. No se modificarán datos.');
            }
            if (!fflush($handle)) throw new \RuntimeException('No se pudo completar el respaldo.');
        } finally {
            fclose($handle);
        }
        $hash = hash_file('sha256', $archivo);
        if (file_put_contents($archivo.'.sha256', $hash."\n") === false) throw new \RuntimeException('No se pudo verificar el respaldo.');

        DB::transaction(function () use ($planes, $grupo) {
            foreach (array_chunk($planes, 500) as $lote) {
                $actuales = DB::table('repuestos')->whereIn('id', array_column($lote, 'id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $movimientos = [];
                foreach ($lote as $plan) {
                    $actual = $actuales->get($plan['id']);
                    if (!$actual) throw new \RuntimeException('Un repuesto desapareció. Operación revertida.');
                    foreach ($plan['antes'] as $campo => $valor) {
                        if ((string) $actual->$campo !== (string) $valor) throw new \RuntimeException('El repuesto '.$plan['id'].' cambió. Operación revertida.');
                    }
                    DB::table('repuestos')->where('id', $plan['id'])->update($plan['cambios'] + ['updated_at' => now()]);
                    foreach (StockPorLocalService::CAMPOS as $indice => $campos) {
                        list($stock, $local, $ubicacion, $responsable) = $campos;
                        if (!array_key_exists($stock, $plan['cambios']) && !array_key_exists($local, $plan['cambios'])) continue;
                        $anterior = (int) $actual->$stock;
                        $nuevo = (int) ($plan['cambios'][$stock] ?? $anterior);
                        $localNuevo = (int) ($plan['cambios'][$local] ?? $actual->$local);
                        $movimientos[] = [
                            'repuesto_id' => $plan['id'], 'local_id' => $localNuevo ?: null,
                            'ubicacion_indice' => $indice, 'campo_stock' => $stock,
                            'stock_anterior' => $anterior, 'cantidad_movimiento' => $nuevo - $anterior,
                            'stock_nuevo' => $nuevo, 'tipo' => 'NORMALIZACION_INVENTARIO',
                            'motivo' => 'Criterio autorizado: negativos a cero; locales según columna. Local anterior: '.((int) $actual->$local).'; local nuevo: '.$localNuevo.'.',
                            'usuario_id' => null, 'responsable_id' => $actual->$responsable ?: null,
                            'referencia_tipo' => 'reconciliacion_stock', 'referencia_id' => $plan['id'],
                            'grupo_uuid' => $grupo, 'created_at' => now(),
                        ];
                    }
                }
                if ($movimientos) DB::table('stock_movimientos')->insert($movimientos);
            }
        }, 3);
        $this->info('Normalización aplicada con bitácora. Respaldo: '.$archivo);
        $this->info('SHA256: '.$hash);
        return 0;
    }
}
