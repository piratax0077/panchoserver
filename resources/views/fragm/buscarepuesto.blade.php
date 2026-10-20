<!-- Carga en el div con id mostrar_repuestos en repuestos.blade.php -->
@if($repuestos->count()>0)
<div style="padding: 30px;">
  <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #667eea;">
    <h5 style="font-weight: 700; color: #2c3e50; margin: 0;"><i class="fa fa-list"></i> Resultados de Búsqueda ({{$repuestos->total()}} repuestos)</h5>
    <small style="color: #7f8c8d;">Se muestran {{$repuestos->count()}} de {{$repuestos->total()}} resultados</small>
  </div>

  <div class="table-responsive">
    <table class="table table-hover mb-0" style="font-size: 13px;">
      <thead>
        <tr style="background-color: #2c3e50;">
          <th style="font-weight: 700; padding: 14px 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Código Interno</th>
          <th style="font-weight: 700; padding: 14px 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Proveedor</th>
          <th style="font-weight: 700; padding: 14px 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Marca</th>
          <th style="font-weight: 700; padding: 14px 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Descripción</th>
          <th style="font-weight: 700; padding: 14px 10px; text-align: right; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Precio Venta</th>
          <th style="font-weight: 700; padding: 14px 10px; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; color: #000; border: none;">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($repuestos as $repuesto)
      <tr style="border-bottom: 1px solid #ecf0f1; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f8f9ff'" onmouseout="this.style.backgroundColor=''">
        <td style="padding: 12px 10px; vertical-align: middle;">
          <strong style="color: #667eea; font-size: 12px;">{{$repuesto->codigo_interno}}</strong>
        </td>
        <td style="padding: 12px 10px; vertical-align: middle;">
          <span style="background: #e8f4ff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; color: #2c3e50;">{{$repuesto->proveedor->empresa_nombre}}</span>
        </td>
        <td style="padding: 12px 10px; vertical-align: middle;">
          <span style="background: #fff3e0; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #e67e22;">{{strtoupper($repuesto->marcarepuesto->marcarepuesto)}}</span>
        </td>
        <td style="padding: 12px 10px; vertical-align: middle;">
          <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{$repuesto->descripcion}}">
            {{$repuesto->descripcion}}
          </div>
          <small style="color: #95a5a6; font-size: 11px;">Med: {{$repuesto->medidas}}</small>
        </td>
        <td style="padding: 12px 10px; vertical-align: middle; text-align: right; font-weight: 700; color: #27ae60;">
          $ {{number_format($repuesto->precio_venta, 0, ',', '.')}}
        </td>
        <td style="padding: 12px 10px; vertical-align: middle; text-align: center;">
          <div class="d-flex justify-content-center align-items-center" style="gap: 8px; flex-wrap: nowrap;">
            <button class="btn btn-sm btn-info" onclick="abrir_modal({{$repuesto->id}})" title="Ver detalle" style="border-radius: 6px; padding: 6px 10px; font-size: 12px; line-height: 1;"><i class="fa fa-eye"></i></button>
            <button class="btn btn-sm btn-warning" onclick="abrir_modal_modificar_precio({{$repuesto->id}}, {{$repuesto->precio_venta}})" title="Modificar precio" style="border-radius: 6px; padding: 6px 10px; font-size: 12px; line-height: 1;"><i class="fa fa-edit"></i></button>
          </div>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <!-- Paginación -->
  <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ecf0f1; display: flex; justify-content: center;">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        @if ($repuestos->onFirstPage())
          <li class="page-item disabled">
            <span class="page-link">← Anterior</span>
          </li>
        @else
          <li class="page-item">
            <a class="page-link pagination-link" href="javascript:void(0)" data-page="{{ $repuestos->currentPage() - 1 }}" role="button">← Anterior</a>
          </li>
        @endif

        @foreach ($repuestos->getUrlRange(max(1, $repuestos->currentPage() - 2), min($repuestos->lastPage(), $repuestos->currentPage() + 2)) as $page => $url)
          @if ($page == $repuestos->currentPage())
            <li class="page-item active">
              <span class="page-link">{{ $page }}</span>
            </li>
          @else
            <li class="page-item">
              <a class="page-link pagination-link" href="javascript:void(0)" data-page="{{ $page }}" role="button">{{ $page }}</a>
            </li>
          @endif
        @endforeach

        @if ($repuestos->hasMorePages())
          <li class="page-item">
            <a class="page-link pagination-link" href="javascript:void(0)" data-page="{{ $repuestos->currentPage() + 1 }}" role="button">Siguiente →</a>
          </li>
        @else
          <li class="page-item disabled">
            <span class="page-link">Siguiente →</span>
          </li>
        @endif
      </ul>
    </nav>
  </div>
</div>
@else
<div style="padding: 60px 30px; text-align: center;">
  <div style="margin-bottom: 20px;">
    <i class="fa fa-search fa-3x" style="color: #bdc3c7;"></i>
  </div>
  <h5 style="color: #2c3e50; font-weight: 700;">Sin resultados</h5>
  <p style="color: #7f8c8d; font-size: 14px;">No se encontraron repuestos con los filtros seleccionados.<br>Intenta cambiar los criterios de búsqueda.</p>
</div>
</div>
@endif
