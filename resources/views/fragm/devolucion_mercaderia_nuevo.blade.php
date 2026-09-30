@php
  $esNota = !empty($nc->num_nota_credito);
  $numeroOperacion = $esNota ? $nc->num_nota_credito : $documento->num_doc;
  $tipoOperacion = $esNota ? 'nc' : (strtolower($tipo_documento) === 'boleta' ? 'bo' : 'fa');
  $fecha = $nc->fecha_emision ?? $documento->fecha_emision ?? null;
  $items = $detalle->unique('id_repuestos');
  $totalDocumento = $detalle->sum(function($item){ return (float)$item->precio_venta * (int)$item->cantidad; });
  $agregados = $devoluciones->pluck('repuesto_id')->map(function($id){ return (int)$id; })->all();
@endphp

<style>
  .return-workspace{background:#fff;border:1px solid #dbe4ee;border-radius:18px;overflow:hidden;box-shadow:0 12px 32px rgba(15,23,42,.08)}.return-summary{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0}.return-summary h4{font-weight:700;color:#13244a;margin:0 0 5px}.return-summary-meta{display:flex;gap:9px;flex-wrap:wrap}.return-chip{background:#fff;border:1px solid #d7e0ea;border-radius:999px;padding:5px 11px;color:#475569;font-size:.86rem}.return-total{text-align:right}.return-total small{display:block;color:#64748b;text-transform:uppercase;font-weight:700}.return-total strong{font-size:1.45rem;color:#0f172a}
  .return-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:0}.return-products{padding:18px;min-width:0}.return-products-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}.return-products-title h5{font-weight:700;margin:0}.return-card{border:1px solid #dbe4ee;border-radius:14px;padding:16px;margin-bottom:12px}.return-card-top{display:grid;grid-template-columns:100px minmax(200px,1.5fr) minmax(130px,.7fr) minmax(120px,.6fr);gap:14px}.return-label{display:block;color:#64748b;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px}.return-code{font-size:1.1rem;font-weight:800;color:#1d4ed8}.return-description{font-weight:600;color:#172554}.return-card-bottom{display:grid;grid-template-columns:130px 130px minmax(170px,1fr) 130px;gap:12px;align-items:end;padding-top:13px;margin-top:13px;border-top:1px solid #eef2f7}.return-card .form-control{height:40px;border-radius:9px}.return-card .btn{height:40px;border-radius:9px;font-weight:700}.return-price{font-size:1.05rem;font-weight:700}.return-sidebar{background:#f8fafc;border-left:1px solid #e2e8f0;padding:18px}.return-sidebar h5{font-weight:700}.return-help{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:10px;color:#1e40af;font-size:.86rem;margin-bottom:12px}.return-sidebar .btn-danger{width:100%;border-radius:10px;font-weight:700;padding:11px}.return-empty{color:#64748b;text-align:center;padding:16px;border:1px dashed #cbd5e1;border-radius:10px}
  @media(max-width:1100px){.return-layout{grid-template-columns:1fr}.return-sidebar{border-left:0;border-top:1px solid #e2e8f0}.return-card-top{grid-template-columns:120px 1fr 1fr}.return-card-top>div:nth-child(2){grid-column:span 2}}@media(max-width:700px){.return-summary{align-items:flex-start;flex-direction:column}.return-total{text-align:left}.return-card-top,.return-card-bottom{grid-template-columns:1fr}.return-card-top>div:nth-child(2){grid-column:auto}}
</style>

<div class="return-workspace">
  <header class="return-summary">
    <div><h4>{{ $tipo_documento }} N.º {{ $documento->num_doc }}</h4><div class="return-summary-meta"><span class="return-chip"><i class="far fa-calendar-alt"></i> {{ $fecha ? \Carbon\Carbon::parse($fecha)->format('d-m-Y') : 'Sin fecha' }}</span><span class="return-chip"><i class="fas fa-box"></i> {{ $items->count() }} productos</span>@if($esNota && $nc->motivo_correccion)<span class="return-chip"><i class="fas fa-comment-alt"></i> {{ $nc->motivo_correccion }}</span>@endif</div></div>
    <div class="return-total"><small>Total documento</small><strong>$ {{ number_format($totalDocumento,0,',','.') }}</strong></div>
  </header>
  <div class="return-layout">
    <main class="return-products">
      <div class="return-products-title"><h5>Productos disponibles</h5><small class="text-muted">Selecciona cantidad y destino</small></div>
      @foreach($items as $d)
        @php
          $cantidadVendida = (int)$detalle->where('id_repuestos',$d->id_repuestos)->sum('cantidad');
          $yaAgregado = in_array((int)$d->id_repuestos,$agregados,true);
          $destinos = $locales->whereIn('id', \App\Services\StockPorLocalService::habilitado() ? \App\RepuestoStock::where('repuesto_id', $d->id_repuestos)->pluck('local_id')->all() : array_filter([$d->local_id,$d->local_id_dos,$d->local_id_tres]));
        @endphp
        <article class="return-card">
          <div class="return-card-top">
            <div><span class="return-label">Código</span><span class="return-code">{{ $d->codigo_interno }}</span></div>
            <div><span class="return-label">Descripción</span><span class="return-description">{{ $d->descripcion }}</span></div>
            <div><span class="return-label">Origen de venta</span>{{ $d->local_nombre }}</div>
            <div><span class="return-label">Precio unitario</span><span class="return-price">$ {{ number_format($d->precio_venta,0,',','.') }}</span></div>
          </div>
          <div class="return-card-bottom">
            <div><span class="return-label">Vendido</span><strong>{{ $cantidadVendida }} unidades</strong><input type="hidden" id="merma-{{ $d->id_repuestos }}" value="{{ $cantidadVendida }}"></div>
            <div><label class="return-label" for="cantidad-{{ $d->id_repuestos }}">A devolver</label><input type="number" class="form-control" min="1" max="{{ $cantidadVendida }}" value="1" id="cantidad-{{ $d->id_repuestos }}" {{ $yaAgregado?'disabled':'' }}></div>
            <div><label class="return-label" for="local_id-{{ $d->id_repuestos }}">Destino del producto</label><select class="form-control" id="local_id-{{ $d->id_repuestos }}" {{ $yaAgregado?'disabled':'' }}>@foreach($destinos as $local)<option value="{{ $local->id }}">{{ $local->local_nombre }}</option>@endforeach</select></div>
            <button id="agregar-{{ $d->id_repuestos }}" class="btn {{ $yaAgregado?'btn-outline-success':'btn-success' }}" onclick="confirmar_devolucion({{ $d->id_repuestos }})" {{ $yaAgregado?'disabled':'' }}>@if($yaAgregado)<i class="fas fa-check"></i> Agregado @else<i class="fas fa-plus"></i> Agregar @endif</button>
          </div>
        </article>
      @endforeach
    </main>
    <aside class="return-sidebar">
      <h5><i class="fas fa-clipboard-check"></i> Devolución preparada</h5>
      <div class="return-help">Revisa el listado. Al enviarlo quedará pendiente de aprobación y el stock todavía no cambiará.</div>
      <div id="table_devolucion_">@include('fragm.devoluciones_table',['devoluciones'=>$devoluciones])</div>
      <button id="cerrar-devolucion" class="btn btn-danger mt-3" onclick='cerrar_devolucion(@json($esNota ? (string)$numeroOperacion : null))' {{ $devoluciones->isEmpty()?'disabled':'' }}><i class="fas fa-paper-plane"></i> Enviar a revisión</button>
    </aside>
  </div>
  <input type="hidden" id="num_nc" value="{{ $esNota ? $numeroOperacion : '' }}">
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Devolución enviada</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><div class="alert alert-info"><strong>Solicitud registrada.</strong> Inventario debe aprobar cada producto antes de sumarlo al stock.</div><div id="listado_repuestos_devolucion"></div></div><div class="modal-footer">@if($esNota)<button id="btn-imprimir" class="btn btn-warning" onclick="imprimir_devolucion({{ $numeroOperacion }})"><i class="fas fa-print"></i> Imprimir</button>@endif<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div></div></div></div>
