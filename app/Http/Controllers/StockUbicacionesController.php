<?php
namespace App\Http\Controllers;

use App\local;
use App\repuesto;
use App\RepuestoStock;
use App\StockMovimiento;
use App\User;
use App\permissions_detail;
use App\Services\StockPorLocalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockUbicacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(StockPorLocalService::habilitado(), 404);
            $admin = optional(Auth::user()->rol)->nombrerol === 'Administrador';
            $puedeLeer = $admin || permissions_detail::where('permission_id', 4)->where('usuarios_id', Auth::id())
                ->where('path_ruta', '/repuesto/stockrepuesto')->exists();
            abort_unless($request->isMethod('get') ? $puedeLeer : $admin, 403);
            return $next($request);
        });
    }

    public function catalogo()
    {
        return ['locales' => local::orderBy('local_nombre')->get(),
            'responsables' => User::where('activo', 1)->orderBy('name')->get(['id', 'name'])];
    }

    public function buscar(Request $request)
    {
        $datos = $request->validate(['codigo' => 'required|string|max:100']);
        $repuestos = repuesto::where('activo', 1)->where(function ($q) use ($datos) {
            $q->where('codigo_interno', $datos['codigo'])
                ->orWhere('cod_repuesto_proveedor', $datos['codigo'])
                ->orWhereIn('id', DB::table('oems')->where('activo', 1)->where('codigo_oem', $datos['codigo'])->select('id_repuestos'));
        })->with(['stocks.local', 'stocks.responsable', 'marcarepuesto', 'pais'])->limit(50)->get();
        return ['repuestos' => $repuestos];
    }

    public function guardarLocal(Request $request, $id = null)
    {
        $datos = $request->validate([
            'local_nombre' => ['required', 'string', 'max:50', Rule::unique('locales', 'local_nombre')->ignore($id)],
            'local_direccion' => 'nullable|string|max:100',
            'local_telefono' => 'nullable|string|max:20',
            'activo' => 'required|boolean',
        ]);
        return DB::transaction(function () use ($id, $datos) {
            $local = $id ? local::whereKey($id)->lockForUpdate()->firstOrFail() : new local;
            if (!$datos['activo'] && $id && RepuestoStock::where('local_id', $id)->where('cantidad', '>', 0)->exists()) {
                throw ValidationException::withMessages(['activo' => 'Debe trasladar o ajustar las existencias antes de desactivar el local.']);
            }
            $datos['local_direccion'] = $datos['local_direccion'] ?? '';
            $datos['local_telefono'] = $datos['local_telefono'] ?? '';
            $local->fill($datos)->save();
            return $local;
        }, 3);
    }

    public function asociar(Request $request, $repuesto, StockPorLocalService $service)
    {
        $datos = $request->validate([
            'local_id' => 'required|integer|exists:locales,id',
            'ubicacion_fisica' => 'nullable|string|max:255',
            'responsable_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('activo', 1)],
        ]);
        return $service->asociar($repuesto, $datos['local_id'], $datos);
    }

    public function ajustar(Request $request, $repuesto, StockPorLocalService $service)
    {
        $datos = $request->validate([
            'local_id' => 'required|integer|exists:locales,id', 'cantidad' => 'required|integer|min:0|max:2147483647',
            'stock_esperado' => 'required|integer|min:0', 'motivo' => 'required|string|min:5|max:500',
        ]);
        if ($datos['cantidad'] < $datos['stock_esperado']) {
            $autorizadoHasta = (int) $request->session()->pull('autoriza_disminuir_stock_hasta', 0);
            if ($autorizadoHasta < now()->timestamp) {
                throw ValidationException::withMessages(['cantidad' => 'Debe autorizar la disminución de stock con la clave correspondiente.']);
            }
        }
        $resultado = $service->ajustar($repuesto, $datos['local_id'], (int) $datos['cantidad'], (int) $datos['stock_esperado'],
            ['tipo' => 'AJUSTE_MANUAL', 'motivo' => $datos['motivo']]);
        (new ventas_controlador)->revisar_stock_minimo($repuesto);
        return $resultado;
    }

    public function trasladar(Request $request, $repuesto, StockPorLocalService $service)
    {
        $datos = $request->validate([
            'origen' => 'required|integer|exists:locales,id', 'destino' => 'required|integer|different:origen|exists:locales,id',
            'cantidad' => 'required|integer|min:1|max:2147483647', 'motivo' => 'required|string|min:5|max:500',
        ]);
        $service->trasladar($repuesto, $datos['origen'], $datos['destino'], (int) $datos['cantidad'], ['motivo' => $datos['motivo']]);
        return ['ok' => true];
    }

    public function historial(Request $request, $repuesto)
    {
        $datos = $request->validate(['local_id' => 'required|integer|exists:locales,id']);
        return StockMovimiento::with('usuario:id,name')->where('repuesto_id', $repuesto)
            ->where('local_id', $datos['local_id'])->orderByDesc('id')->paginate(30);
    }
}
