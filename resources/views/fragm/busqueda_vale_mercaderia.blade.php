@if(isset($vale) && $vale)
@php
    /*
     * El controlador entrega directamente:
     *
     * $detallesPendientes  -> productos que participarán en la próxima entrega.
     * $detallesProcesados  -> productos utilizados anteriormente.
     */
    $detallesPendientes = collect($detallesPendientes ?? []);
    $detallesProcesados = collect($detallesProcesados ?? []);

    /*
     * El total actual considera solamente los productos pendientes.
     * Después de procesar una entrega parcial, el nuevo carrito comienza en $0.
     */
    $total = $detallesPendientes->sum(function ($detalle) {
        return round(
            (float) $detalle->precio_venta * (int) $detalle->cantidad,
            0
        );
    });

    $total = round((float) $total, 0);

    /*
     * $vale->valor representa el saldo actualmente disponible.
     */
    $saldoDisponible = round(
        (float) $vale->valor - $total,
        0
    );

    $puedeProcesar =
        $detallesPendientes->count() > 0 &&
        $total > 0 &&
        (float) $vale->valor > 0;
@endphp

<div id="vale_mercaderia_detalle" class="mt-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center flex-wrap px-4 py-3">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-gift mr-1"></i>
                    Vale de mercadería N° {{ $vale->numero_boucher }}
                </h5>
                <small class="text-white-50">
                    Información del vale y productos asociados.
                </small>
            </div>

            @if($vale->valor > 0)
                <span class="badge badge-success px-3 py-2">
                    Disponible
                </span>
            @else
                <span class="badge badge-secondary px-3 py-2">
                    Procesado
                </span>
            @endif
        </div>

        <div class="card-body p-4" style="background:#f8fafc;">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Información del vale
                            </h6>

                            <div class="list-group list-group-flush border rounded">
                                <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="font-weight-bold text-muted">Saldo disponible</span>
                                    <span class="font-weight-bold text-primary">
                                        $ {{ number_format($vale->valor,0,',','.') }}
                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <div class="small font-weight-bold text-muted mb-1">Usuario</div>
                                    <div class="text-dark">{{ $vale->name }}</div>
                                </div>

                                <div class="list-group-item">
                                    <div class="small font-weight-bold text-muted mb-1">Cliente</div>
                                    <div class="text-dark">{{ $vale->nombre_cliente }}</div>
                                </div>

                                <div class="list-group-item">
                                    <div class="small font-weight-bold text-muted mb-1">Descripción</div>
                                    <div class="text-dark" style="line-height:1.45;">
                                        {{ $vale->descripcion }}
                                    </div>
                                </div>
                            </div>

                            @if($vale->valor > 0)
                                <button type="button"
                                        class="btn btn-success btn-sm mt-3"
                                        data-toggle="modal"
                                        data-target="#exampleModal">
                                    <i class="fas fa-plus mr-1"></i>
                                    Agregar repuesto
                                </button>
                                 <button type="button"
                                        class="btn btn-warning btn-sm mt-3 ml-2"
                                        onclick="abrir_modal_repuesto_xpress_vale(
                                            {{ $vale->numero_boucher }},
                                            {{ (float) $saldoDisponible }}
                                        )">
                                    <i class="fas fa-bolt mr-1"></i>
                                    Agregar Repuesto Express
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            @if($detallesPendientes->count() > 0)
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                    <div>
                                        <h6 class="font-weight-bold mb-1">
                                            <i class="fas fa-boxes mr-1"></i>
                                            Productos asociados
                                        </h6>
                                        <small class="text-muted">
                                            {{ $detallesPendientes->count() }} producto(s) pendiente(s) de procesar.
                                        </small>
                                    </div>

                                    <span class="badge badge-light border px-3 py-2">
                                        Total unidades:
                                        <strong>{{ $detallesPendientes->sum('cantidad') }}</strong>
                                    </span>
                                </div>

                                <div class="list-group mb-3">
                                    @foreach($detallesPendientes as $d)
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-3 mb-2 mb-md-0">
                                                    <small class="text-muted d-block">Código</small>
                                                    <strong>{{ $d->codigo_interno }}</strong>
                                                    <small class="text-muted d-block">ID {{ $d->idrep }}</small>
                                                </div>

                                                <div class="col-md-2 mb-2 mb-md-0">
                                                    <small class="text-muted d-block">Cantidad</small>
                                                    <span class="badge badge-primary px-3 py-2">
                                                        {{ $d->cantidad }}
                                                    </span>
                                                </div>

                                                <div class="col-md-3 mb-2 mb-md-0">
                                                    <small class="text-muted d-block">Origen</small>
                                                    <span class="badge badge-light border px-2 py-2">
                                                        <i class="fas fa-warehouse mr-1"></i>
                                                        {{ $d->local_nombre }}
                                                    </span>
                                                </div>

                                                <div class="col-md-2 mb-2 mb-md-0">
                                                    <small class="text-muted d-block">Precio unitario</small>
                                                    <strong>$ {{ number_format($d->precio_venta,0,',','.') }}</strong>
                                                </div>

                                                <div class="col-md-2 text-md-right">
                                                    <small class="text-muted d-block">Subtotal</small>
                                                    <strong>$ {{ number_format($d->precio_venta * $d->cantidad,0,',','.') }}</strong>

                                                    @if($vale->valor > 0)
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm ml-2"
                                                                title="Eliminar repuesto"
                                                                onclick="eliminar_repuesto_devolucion({{ $d->id }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="list-group mb-3">
                                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <span class="font-weight-bold text-muted">Saldo disponible del vale</span>
                                        <strong class="text-primary">
                                            $ {{ number_format($vale->valor,0,',','.') }}
                                        </strong>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <span class="font-weight-bold text-muted">Total productos</span>
                                        <strong>
                                            $ {{ number_format($total,0,',','.') }}
                                        </strong>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap {{ $saldoDisponible < 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                        <span class="font-weight-bold">
                                            {{ $saldoDisponible < 0 ? 'Exceso sobre el vale' : 'Saldo restante' }}
                                        </span>
                                        <strong>
                                            $ {{ number_format(abs($saldoDisponible),0,',','.') }}
                                        </strong>
                                    </div>
                                </div>

                                @if($saldoDisponible < 0)
                                    <div class="alert alert-warning d-flex align-items-start mb-3">
                                        <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                                        <div>
                                            <strong>El total supera el valor disponible del vale.</strong>
                                            <div class="small mt-1">
                                                Existe un exceso de
                                                <strong>$ {{ number_format(abs($saldoDisponible),0,',','.') }}</strong>.
                                                Puede procesar igualmente la entrega, pero el vale quedará sin saldo.
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($puedeProcesar)
                                    <div class="d-flex justify-content-end">
                                        <button type="button"
                                                class="btn {{ $saldoDisponible < 0 ? 'btn-danger' : 'btn-warning' }}"
                                                onclick="imprimir_resultado(
                                                    {{ $vale->numero_boucher }},
                                                    {{ (float) $vale->valor }},
                                                    {{ (float) $total }}
                                                )">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ $saldoDisponible < 0 ? 'Procesar con exceso' : 'Procesar entrega' }}
                                        </button>
                                    </div>
                                @elseif($vale->valor > 0)
                                    <div class="alert alert-info mb-0">
                                        Debe agregar al menos un producto antes de procesar.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    No hay productos pendientes de procesar. El nuevo uso del vale comienza en $0.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>


    @if($detallesProcesados->count() > 0)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap px-4 py-3">
                <div>
                    <h6 class="mb-1 font-weight-bold">
                        <i class="fas fa-history mr-1"></i>
                        Historial de productos utilizados
                    </h6>
                    <small class="text-muted">
                        Estos productos ya fueron procesados y no se consideran en el saldo actual.
                    </small>
                </div>

                <span class="badge badge-secondary px-3 py-2">
                    {{ $detallesProcesados->count() }} registro(s)
                </span>
            </div>

            <div class="card-body p-4">
                <div class="list-group">
                    @foreach($detallesProcesados as $procesado)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Código</small>
                                    <strong>{{ $procesado->codigo_interno }}</strong>
                                    <small class="text-muted d-block">
                                        ID {{ $procesado->idrep }}
                                    </small>
                                </div>

                                <div class="col-md-2 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Cantidad</small>
                                    <span class="badge badge-secondary px-3 py-2">
                                        {{ $procesado->cantidad }}
                                    </span>
                                </div>

                                <div class="col-md-3 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Origen</small>
                                    <span class="badge badge-light border px-2 py-2">
                                        <i class="fas fa-warehouse mr-1"></i>
                                        {{ $procesado->local_nombre }}
                                    </span>
                                </div>

                                <div class="col-md-2 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Precio unitario</small>
                                    <strong>
                                        $ {{ number_format($procesado->precio_venta, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="col-md-2 text-md-right">
                                    <small class="text-muted d-block">Total utilizado</small>
                                    <strong>
                                        $ {{
                                            number_format(
                                                $procesado->precio_venta * $procesado->cantidad,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </strong>
                                </div>
                            </div>

                            @if(!empty($procesado->updated_at))
                                <div class="text-right mt-2">
                                    <small class="text-muted">
                                        Procesado:
                                        {{
                                            \Carbon\Carbon::parse($procesado->updated_at)
                                                ->format('d-m-Y H:i')
                                        }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Modal búsqueda de repuestos -->
    <div class="modal fade"
         tabindex="-1"
         role="dialog"
         id="exampleModal"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">

                <div class="modal-header bg-dark text-white px-4 py-3">
                    <div>
                        <h5 class="modal-title mb-1" id="exampleModalLabel">
                            <i class="fas fa-search mr-2"></i>
                            Buscar repuesto por código interno
                        </h5>
                        <small class="text-white-50">
                            Ingrese el código, seleccione cantidad y origen de stock.
                        </small>
                    </div>

                    <button type="button"
                            class="close text-white"
                            data-dismiss="modal"
                            aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4" style="background:#f7f8c4;">

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <label for="repuesto-xpress-codigo"
                                   class="font-weight-bold mb-2">
                                Código interno
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                </div>

                                <input type="text"
                                       class="form-control"
                                       id="repuesto-xpress-codigo"
                                       placeholder="Ingrese código de referencia"
                                       onkeyup="if(event.key === 'Enter'){ buscar_repuesto_xpress(); }">

                                <div class="input-group-append">
                                    <button type="button"
                                            id="btn_buscar_repuesto_vale"
                                            class="btn btn-warning px-4"
                                            onclick="buscar_repuesto_xpress()">
                                        <i class="fas fa-search mr-1"></i>
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-2">
                                Presione Enter o el botón Buscar para consultar el repuesto.
                            </small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h6 class="mb-1 font-weight-bold">
                                    <i class="fas fa-box-open mr-1"></i>
                                    Resultado de búsqueda
                                </h6>
                                <small class="text-muted">
                                    Revise stock, cantidad y procedencia antes de agregar.
                                </small>
                            </div>

                            <span class="badge badge-light border px-3 py-2">
                                Seleccione un origen
                            </span>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width:260px;">Descripción</th>
                                            <th style="min-width:150px;">Ubicación 1</th>
                                            <th style="min-width:150px;">Ubicación 2</th>
                                            <th style="min-width:150px;">Ubicación 3</th>
                                            <th class="text-center" style="min-width:120px;">Cantidad</th>
                                            <th style="min-width:170px;">Procedencia</th>
                                            <th class="text-right" style="min-width:130px;">Precio venta</th>
                                        </tr>
                                    </thead>

                                    <tbody id="informacion_repuesto">
                                        <tr id="fila_sin_resultados">
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                                Ingrese un código para comenzar la búsqueda.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="mensaje_modal"
                         class="alert alert-light border mt-3 mb-0 py-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esperando búsqueda...
                    </div>
                </div>

                <div class="modal-footer bg-white px-4 py-3">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>

                    <button type="button"
                            id="btn_agregar_repuesto_buscado"
                            class="btn btn-primary px-4"
                            disabled>
                        <i class="fas fa-plus mr-1"></i>
                        Agregar repuesto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden"
       name="numero_vale_mercaderia"
       id="numero_vale_mercaderia"
       value="{{ $vale->numero_boucher }}">
@else
<div class="alert alert-warning mt-3">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    {{ $mensaje ?? 'No se encontró el vale de mercadería solicitado.' }}
</div>
@endif