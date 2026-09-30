<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\vale_mercaderia_movimiento;
use App\vale_mercaderia;
use App\repuesto;
use App\Helpers\MovimientoValeHelper;
use Carbon\Carbon;

class MovimientoValeController extends Controller
{
    /**
     * Muestra el historial de movimientos con filtros
     */
    public function index(Request $request)
    {
        $query = vale_mercaderia_movimiento::with('vale', 'repuesto', 'usuario');

        // Filtro por tipo de movimiento
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        // Filtro por repuesto
        if ($request->has('repuesto_id') && !empty($request->repuesto_id)) {
            $query->where('repuesto_id', $request->repuesto_id);
        }

        // Filtro por vale
        if ($request->has('vale_id') && !empty($request->vale_id)) {
            $query->where('vale_mercaderia_id', $request->vale_id);
        }

        // Filtro por local/origen
        if ($request->has('local_id') && !empty($request->local_id)) {
            $query->where('local_id', $request->local_id);
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && !empty($request->fecha_inicio)) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && !empty($request->fecha_fin)) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Paginación
        $movimientos = $query->orderBy('created_at', 'desc')->paginate(50);

        // Datos para dropdowns
        $repuestos = repuesto::orderBy('codigo_interno')->get();
        $vales = vale_mercaderia::orderBy('numero_boucher', 'desc')->get();
        $tipos = ['agregar', 'eliminar', 'devolver'];
        $locales = [1 => 'Bodega Principal', 3 => 'Segunda Ubicación', 4 => 'Tercera Ubicación'];

        return view('movimientos_vales.index', compact('movimientos', 'repuestos', 'vales', 'tipos', 'locales'));
    }

    /**
     * Muestra el historial de un repuesto específico
     */
    public function historicoRepuesto($repuesto_id, Request $request)
    {
        $repuesto = repuesto::findOrFail($repuesto_id);
        
        $fecha_inicio = $request->input('fecha_inicio', now()->subMonths(12)->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $movimientos = MovimientoValeHelper::historicoRepuesto(
            $repuesto_id,
            $fecha_inicio,
            $fecha_fin
        );

        $balance = MovimientoValeHelper::balanceRepuesto(
            $repuesto_id,
            $fecha_inicio,
            $fecha_fin
        );

        return view('movimientos_vales.historico_repuesto', compact('repuesto', 'movimientos', 'balance', 'fecha_inicio', 'fecha_fin'));
    }

    /**
     * Muestra el historial de un vale específico
     */
    public function historicoVale($vale_id)
    {
        $vale = vale_mercaderia::findOrFail($vale_id);
        
        $movimientos = vale_mercaderia_movimiento::delVale($vale_id)
            ->with('repuesto', 'usuario')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular totales
        $totales = [
            'agregar' => $movimientos->where('tipo_movimiento', 'agregar')->sum('cantidad'),
            'eliminar' => $movimientos->where('tipo_movimiento', 'eliminar')->sum('cantidad'),
            'devolver' => $movimientos->where('tipo_movimiento', 'devolver')->sum('cantidad'),
        ];

        return view('movimientos_vales.historico_vale', compact('vale', 'movimientos', 'totales'));
    }

    /**
     * Reporte de repuestos más sacados
     */
    public function repuestosMasSacados(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', now()->subMonths(1)->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $limite = $request->input('limite', 20);

        $repuestos_sacados = vale_mercaderia_movimiento::entreFechas(
            $fecha_inicio . ' 00:00:00',
            $fecha_fin . ' 23:59:59'
        )
            ->whereIn('tipo_movimiento', ['eliminar', 'devolver'])
            ->with('repuesto')
            ->groupBy('repuesto_id')
            ->selectRaw('repuesto_id, SUM(cantidad) as total_cantidad, COUNT(*) as total_movimientos')
            ->orderByRaw('total_cantidad DESC')
            ->limit($limite)
            ->get();

        return view('movimientos_vales.repuestos_sacados', compact('repuestos_sacados', 'fecha_inicio', 'fecha_fin', 'limite'));
    }

    /**
     * Reporte de movimientos por local
     */
    public function movimientosPorLocal(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', now()->subMonths(1)->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $movimientos = vale_mercaderia_movimiento::entreFechas(
            $fecha_inicio . ' 00:00:00',
            $fecha_fin . ' 23:59:59'
        )
            ->get()
            ->groupBy('local_id');

        $locales = [1 => 'Bodega Principal', 3 => 'Segunda Ubicación', 4 => 'Tercera Ubicación'];

        $resumen = [];
        foreach ($movimientos as $local_id => $items) {
            $resumen[$local_id] = [
                'nombre' => $locales[$local_id] ?? 'Desconocido',
                'salidas' => $items->where('tipo_movimiento', 'eliminar')->sum('cantidad'),
                'entradas' => $items->where('tipo_movimiento', 'agregar')->sum('cantidad'),
                'devoluciones' => $items->where('tipo_movimiento', 'devolver')->sum('cantidad'),
                'total_movimientos' => $items->count(),
            ];
        }

        return view('movimientos_vales.por_local', compact('resumen', 'fecha_inicio', 'fecha_fin'));
    }

    /**
     * Exporta movimientos a CSV
     */
    public function exportar(Request $request)
    {
        $query = vale_mercaderia_movimiento::with('vale', 'repuesto', 'usuario');

        // Aplicar filtros
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        if ($request->has('repuesto_id') && !empty($request->repuesto_id)) {
            $query->where('repuesto_id', $request->repuesto_id);
        }

        if ($request->has('fecha_inicio') && !empty($request->fecha_inicio)) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && !empty($request->fecha_fin)) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $movimientos = $query->orderBy('created_at', 'desc')->get();

        // Crear CSV
        $filename = 'movimientos_vales_' . date('Y_m_d_His') . '.csv';
        $handle = fopen('php://memory', 'r+');

        // Encabezados
        fputcsv($handle, [
            'Fecha',
            'Vuelo (Vale)',
            'Código Repuesto',
            'Repuesto',
            'Cantidad',
            'Tipo Movimiento',
            'Local/Origen',
            'Usuario',
            'Observaciones'
        ]);

        // Datos
        foreach ($movimientos as $mov) {
            fputcsv($handle, [
                $mov->created_at->format('Y-m-d H:i:s'),
                $mov->numero_voucher ?? 'N/A',
                $mov->repuesto->codigo_interno ?? 'N/A',
                $mov->repuesto->descripcion ?? 'N/A',
                $mov->cantidad,
                $mov->tipo_movimiento,
                $mov->getNombreLocal(),
                $mov->usuario->name ?? 'N/A',
                $mov->observaciones ?? ''
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Dashboard de resumen de movimientos
     */
    public function dashboard(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', now()->subMonths(1)->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Estadísticas generales
        $movimientos_totales = vale_mercaderia_movimiento::entreFechas(
            $fecha_inicio . ' 00:00:00',
            $fecha_fin . ' 23:59:59'
        )->count();

        $total_cantidad = vale_mercaderia_movimiento::entreFechas(
            $fecha_inicio . ' 00:00:00',
            $fecha_fin . ' 23:59:59'
        )->sum('cantidad');

        $repuestos_unicos = vale_mercaderia_movimiento::entreFechas(
            $fecha_inicio . ' 00:00:00',
            $fecha_fin . ' 23:59:59'
        )->distinct('repuesto_id')->count();

        // Top 5 repuestos más sacados
        $top5 = MovimientoValeHelper::repuestosMasSacados($fecha_inicio, $fecha_fin, 5);

        return view('movimientos_vales.dashboard', compact(
            'fecha_inicio',
            'fecha_fin',
            'movimientos_totales',
            'total_cantidad',
            'repuestos_unicos',
            'top5'
        ));
    }
}
