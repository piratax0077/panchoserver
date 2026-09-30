@if($repuestos->count() == 0)
    <div class="alert alert-warning mb-0">
        No se encontraron repuestos para la búsqueda.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Proveedor</th>
                    <th>Stock</th>
                    <th>Stock Mín.</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repuestos as $r)
                    @php
                        $stock_total = 
                            (int)($r->stock_actual ?? 0) +
                            (int)($r->stock_actual_dos ?? 0) +
                            (int)($r->stock_actual_tres ?? 0);

                        $estado_actual = $r->estado ?? 'Sin estado';
                    @endphp

                    <tr id="fila_repuesto_buscado_{{ $r->id }}">
                        <td>
                            <a href="javascript:void(0)"
                               data-target="#modalDetalleRepuesto"
                               data-toggle="modal"
                               onclick="detalle_repuesto({{ $r->id }})">
                                {{ $r->codigo_interno }}
                            </a>
                        </td>

                        <td>{{ $r->descripcion }}</td>

                        <td>{{ $r->empresa_nombre_corto ?? 'Sin proveedor' }}</td>

                        <td>{{ $stock_total }}</td>

                        <td>{{ $r->stock_minimo ?? 0 }}</td>

                        <td>
                            <select id="estado_buscado_{{ $r->id }}" class="form-control form-control-sm">
                                <option value="0" {{ $estado_actual == '0' || $estado_actual == 'Sin estado' ? 'selected' : '' }}>
                                    Sin estado
                                </option>

                                @foreach($estados as $estado)
                                    <option value="{{ $estado }}" {{ $estado_actual == $estado ? 'selected' : '' }}>
                                        {{ $estado }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="cambiarEstadoRepuestoBuscado({{ $r->id }})">
                                Guardar
                            </button>

                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    data-target="#modalNuevoStockMinimo"
                                    data-toggle="modal"
                                    onclick="damerepuesto({{ $r->id }})">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-warning btn-sm"
                                    onclick="detalle_pedido({{ $r->id }})">
                                D
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif