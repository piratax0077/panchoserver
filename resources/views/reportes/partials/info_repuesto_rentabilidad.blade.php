<div class="text-dark">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
        <div>
            <h5 class="mb-1">{{ $repuesto->codigo_interno }} · {{ $repuesto->descripcion }}</h5>
            <small class="text-muted">Código proveedor: {{ $repuesto->cod_repuesto_proveedor ?: '—' }}</small>
        </div>
        <span class="badge badge-primary p-2">Stock total: {{ number_format($stockTotal, 0, ',', '.') }} unidades</span>
    </div>

    <h6><i class="fas fa-boxes mr-2" aria-hidden="true"></i>Stock detallado actual</h6>
    <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light"><tr><th scope="col">Local</th><th scope="col" class="text-right">Stock</th><th scope="col">Ubicación física</th><th scope="col">Responsable</th><th scope="col">Actualizado</th></tr></thead>
            <tbody>
                @forelse($stocks as $stock)
                <tr>
                    <td>{{ $stock['local'] }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($stock['cantidad'], 0, ',', '.') }}</td>
                    <td>{{ $stock['ubicacion'] ?: 'Sin asignar' }}</td>
                    <td>{{ $stock['responsable'] ?: 'Sin asignar' }}</td>
                    <td class="text-nowrap">{{ $stock['actualizado'] && substr((string) $stock['actualizado'], 0, 10) !== '0000-00-00' ? \Carbon\Carbon::parse($stock['actualizado'])->format('d-m-Y') : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Este repuesto no tiene ubicaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h6><i class="fas fa-history mr-2" aria-hidden="true"></i>Historial de compras <small class="text-muted">({{ $compras->total() }} registros)</small></h6>
    <p class="small text-muted mb-2">Ordenado desde la compra más reciente. Los precios corresponden al registro de cada compra.</p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="thead-light"><tr>
                <th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Factura</th>
                <th scope="col">Proveedor</th><th scope="col">Local</th>
                <th scope="col" class="text-right">Precio compra</th><th scope="col" class="text-right">Precio venta</th>
                <th scope="col" class="text-right">Flete</th><th scope="col" class="text-right">Cantidad</th>
            </tr></thead>
            <tbody>
                @forelse($compras as $compra)
                <tr>
                    <td class="text-nowrap">{{ $compra->fecha && substr((string) $compra->fecha, 0, 10) !== '0000-00-00' ? \Carbon\Carbon::parse($compra->fecha)->format('d-m-Y') : '—' }}</td>
                    <td>{{ $compra->usuario ?: '—' }}</td>
                    <td>{{ $compra->numero ?: '—' }}@if(!$compra->compra_activa || !$compra->detalle_activo)<br><span class="badge badge-secondary">Inactiva</span>@endif</td>
                    <td>{{ $compra->proveedor ?: '—' }}</td>
                    <td>{{ $compra->local ?: '—' }}</td>
                    <td class="text-right text-nowrap">{{ $compra->precio_compra === null ? '—' : '$ '.number_format($compra->precio_compra, 0, ',', '.') }}</td>
                    <td class="text-right text-nowrap font-weight-bold">{{ $compra->precio_venta === null ? '—' : '$ '.number_format($compra->precio_venta, 0, ',', '.') }}</td>
                    <td class="text-right text-nowrap">{{ $compra->costos === null ? '—' : '$ '.number_format($compra->costos, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($compra->cantidad, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No hay compras registradas para este repuesto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($compras->hasPages())
    <nav class="d-flex justify-content-between align-items-center" aria-label="Páginas del historial de compras">
        <button type="button" class="btn btn-sm btn-outline-primary rentabilidad-compras-pagina" data-repuesto="{{ $repuesto->id }}" data-pagina="{{ $compras->currentPage() - 1 }}" {{ $compras->onFirstPage() ? 'disabled' : '' }}>Anterior</button>
        <small>Página {{ $compras->currentPage() }} de {{ $compras->lastPage() }}</small>
        <button type="button" class="btn btn-sm btn-outline-primary rentabilidad-compras-pagina" data-repuesto="{{ $repuesto->id }}" data-pagina="{{ $compras->currentPage() + 1 }}" {{ $compras->hasMorePages() ? '' : 'disabled' }}>Siguiente</button>
    </nav>
    @endif
</div>
