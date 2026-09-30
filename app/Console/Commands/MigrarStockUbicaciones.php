<?php
namespace App\Console\Commands;

use App\Services\StockPorLocalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarStockUbicaciones extends Command
{
    protected $signature = 'stock:migrar-ubicaciones {--aplicar : Copiar existencias con el sistema en mantenimiento} {--reporte : Exportar inconsistencias a storage/app/revision_stock_ubicaciones.csv}';
    protected $description = 'Valida locales y existencias; sin --aplicar solamente informa inconsistencias.';

    public function handle(StockPorLocalService $servicio)
    {
        if (StockPorLocalService::habilitado()) {
            $this->error('Desactive STOCK_POR_LOCAL antes de migrar. No se sobrescribe inventario activo.');
            return 1;
        }
        if ($this->option('aplicar') && !app()->isDownForMaintenance()) {
            $this->error('Ejecute php artisan down antes de aplicar para impedir escrituras durante la copia.');
            return 1;
        }
        $localesValidos = array_fill_keys(\App\local::pluck('id')->all(), true);
        $reporte = null;
        if ($this->option('reporte')) {
            $reporte = fopen(storage_path('app/revision_stock_ubicaciones.csv'), 'w');
            if (!$reporte) throw new \RuntimeException('No se pudo crear el reporte.');
            fputcsv($reporte, ['repuesto_id', 'local_1', 'stock_1', 'local_2', 'stock_2', 'local_3', 'stock_3', 'problema']);
        }
        $errores = 0;
        $filas = 0;
        DB::table('repuestos')->orderBy('id')->chunkById(500, function ($repuestos) use ($servicio, $localesValidos, $reporte, &$errores, &$filas) {
            foreach ($repuestos as $repuesto) {
                try { $filas += count($servicio->filasLegadas($repuesto, $localesValidos)); }
                catch (\Illuminate\Validation\ValidationException $e) {
                    $errores++;
                    if ($reporte) fputcsv($reporte, [
                        $repuesto->id, $repuesto->local_id, $repuesto->stock_actual,
                        $repuesto->local_id_dos, $repuesto->stock_actual_dos,
                        $repuesto->local_id_tres, $repuesto->stock_actual_tres,
                        implode(' ', \Illuminate\Support\Arr::flatten($e->errors())),
                    ]);
                    if ($errores <= 20) $this->error(implode(' ', \Illuminate\Support\Arr::flatten($e->errors())));
                }
            }
        });
        if ($reporte) { fclose($reporte); $this->info('Reporte: storage/app/revision_stock_ubicaciones.csv'); }
        $this->info($filas.' existencias válidas; '.$errores.' repuestos con inconsistencias.');
        if ($errores) return 1;
        if (!$this->option('aplicar')) return 0;
        if (DB::table('repuesto_stocks')->exists()) {
            $this->error('La tabla destino ya contiene datos. No se sobrescribirá.');
            return 1;
        }
        DB::transaction(function () use ($servicio, $localesValidos) {
            DB::table('repuestos')->orderBy('id')->chunkById(500, function ($repuestos) use ($servicio, $localesValidos) {
                $filas = [];
                foreach ($repuestos as $repuesto) {
                    foreach ($servicio->filasLegadas($repuesto, $localesValidos) as $fila) {
                        $filas[] = $fila + ['created_at' => now(), 'updated_at' => now()];
                    }
                }
                foreach (array_chunk($filas, 1000) as $lote) DB::table('repuesto_stocks')->insert($lote);
            });
            // Solo se enlaza el historial: no se alteran sus datos ni índices originales.
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('UPDATE stock_movimientos m
                    INNER JOIN repuesto_stocks s ON s.repuesto_id = m.repuesto_id AND s.local_id = m.local_id
                    SET m.repuesto_stock_id = s.id WHERE m.repuesto_stock_id IS NULL');
            } else {
                DB::table('stock_movimientos')->whereNull('repuesto_stock_id')->orderBy('id')->chunkById(500, function ($movimientos) {
                    foreach ($movimientos as $movimiento) {
                        $id = DB::table('repuesto_stocks')->where('repuesto_id', $movimiento->repuesto_id)
                            ->where('local_id', $movimiento->local_id)->value('id');
                        if ($id) DB::table('stock_movimientos')->where('id', $movimiento->id)->update(['repuesto_stock_id' => $id]);
                    }
                });
            }
            $sumas = DB::table('repuesto_stocks')->select('repuesto_id')->selectRaw('SUM(cantidad) AS cantidad')->groupBy('repuesto_id');
            $diferencias = DB::table('repuestos as r')->leftJoinSub($sumas, 's', 's.repuesto_id', '=', 'r.id')
                ->whereRaw('(COALESCE(r.stock_actual,0) + COALESCE(r.stock_actual_dos,0) + COALESCE(r.stock_actual_tres,0)) <> COALESCE(s.cantidad,0)')
                ->count();
            if ($diferencias) throw new \RuntimeException('Hay diferencias por repuesto. Copia revertida.');
        });
        $this->info('Copia completada y total verificado. Active STOCK_POR_LOCAL=true, limpie la configuración y ejecute artisan up.');
        return 0;
    }
}
