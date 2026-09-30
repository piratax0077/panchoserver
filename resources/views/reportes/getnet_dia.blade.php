@if($documentos->count() > 0)
<style>
    .getnet-detalle-wrap {
        width: 100%;
    }

    .getnet-resumen-dia {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .getnet-resumen-dia .dato {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        padding: 8px 12px;
        min-width: 145px;
    }

    .getnet-resumen-dia .dato small {
        display: block;
        color: #6c757d;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .getnet-resumen-dia .dato strong {
        display: block;
        margin-top: 2px;
        font-size: 15px;
    }

    .tabla-getnet-detalle {
        width: 100%;
        min-width: 860px;
        margin-bottom: 0;
    }

    .tabla-getnet-detalle thead th {
        border-top: 0;
        border-bottom: 2px solid #dee2e6;
        background: #fff;
        color: #222;
        font-weight: 700;
        padding: 11px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .tabla-getnet-detalle tbody td {
        padding: 12px 10px;
        vertical-align: middle;
    }

    .tabla-getnet-detalle .monto {
        text-align: right;
        white-space: nowrap;
    }

    .otros-medios-item {
        display: block;
        white-space: nowrap;
        margin-bottom: 2px;
    }

    .estado-getnet {
        font-weight: 600;
        white-space: nowrap;
    }

    .estado-mixto {
        color: #8a5a00;
    }

    .estado-nc {
        color: #8b1e2d;
    }

    .estado-diferencia {
        color: #b02a37;
    }

    .estado-normal {
        color: #343a40;
        font-weight: 400;
    }

    .operaciones-getnet {
        background: #fbfbfc;
    }

    .operaciones-getnet td {
        padding: 0 !important;
        border-top: 0 !important;
    }

    .operaciones-getnet-contenido {
        margin: 0 10px 10px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
    }

    .operacion-getnet-linea {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .operacion-getnet-linea:last-child {
        border-bottom: 0;
    }

    .operacion-getnet-linea .referencia-op {
        min-width: 160px;
        word-break: break-word;
    }
</style>

<div class="getnet-detalle-wrap">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
        <div>
            <h5 class="mb-0">Detalle Getnet · {{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}</h5>
            <small class="text-muted">Conciliación por documento de venta</small>
        </div>
        <a href="/reportes/imprimir_detalle_dia_getnet/{{$fecha}}" class="btn btn-success btn-sm mt-1 mt-md-0" title="Exportar detalle">
            <i class="fa-solid fa-file-excel"></i> Excel
        </a>
    </div>

    <div class="getnet-resumen-dia">
        <div class="dato">
            <small>Operaciones Getnet</small>
            <strong>{{ number_format($cantidad_operaciones, 0, ',', '.') }}</strong>
        </div>
        <div class="dato">
            <small>Documentos</small>
            <strong>{{ number_format($documentos->count(), 0, ',', '.') }}</strong>
        </div>
        <div class="dato">
            <small>Monto Getnet</small>
            <strong>${{ number_format($total_dia, 0, ',', '.') }}</strong>
        </div>
        <div class="dato">
            <small>Total notas de crédito</small>
            <strong class="text-danger">-${{ number_format($total_notas_credito, 0, ',', '.') }}</strong>
        </div>
        <div class="dato">
            <small>Total neto</small>
            <strong class="{{ $total_neto < 0 ? 'text-danger' : 'text-success' }}">
                ${{ number_format($total_neto, 0, ',', '.') }}
            </strong>
        </div>
        <div class="dato">
            <small>Pagos mixtos</small>
            <strong>{{ number_format($cantidad_pagos_mixtos, 0, ',', '.') }}</strong>
        </div>
        <div class="dato">
            <small>Con nota de crédito</small>
            <strong>{{ number_format($cantidad_con_nc, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover tabla-getnet-detalle">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th class="text-right">Total venta</th>
                    <th class="text-right">Getnet</th>
                    <th>Otros medios</th>
                    <th>Estado</th>
                    <th class="text-center">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentos as $doc)
                    <tr>
                        <td>
                            <strong>{{ $doc['tipo_doc'] }} {{ $doc['num_doc'] }}</strong>
                        </td>
                        <td class="monto">
                            ${{ number_format($doc['total_venta'], 0, ',', '.') }}
                        </td>
                        <td class="monto">
                            <strong>${{ number_format($doc['monto_getnet'], 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            @if(count($doc['otros_medios']) > 0)
                                @foreach($doc['otros_medios'] as $otro)
                                    <span class="otros-medios-item">
                                        {{ $otro['nombre'] }}
                                        <strong>${{ number_format($otro['monto'], 0, ',', '.') }}</strong>
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(count($doc['notas_credito']) > 0)
                                @foreach($doc['notas_credito'] as $nc)
                                    <span class="estado-getnet estado-nc d-block">
                                        NC N° {{ $nc['numero'] }} - ${{ number_format($nc['monto'], 0, ',', '.') }}
                                    </span>
                                @endforeach
                            @elseif($doc['es_pago_mixto'])
                                <span class="estado-getnet estado-mixto">Pago mixto</span>
                            @elseif(abs($doc['diferencia']) > 0.5)
                                <span class="estado-getnet estado-diferencia">
                                    Diferencia ${{ number_format(abs($doc['diferencia']), 0, ',', '.') }}
                                </span>
                            @else
                                <span class="estado-getnet estado-normal">Normal</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    data-toggle="collapse"
                                    data-target="#ops-getnet-{{ $loop->index }}"
                                    aria-expanded="false"
                                    aria-controls="ops-getnet-{{ $loop->index }}">
                                <i class="fas fa-eye"></i>
                                {{ count($doc['operaciones']) }}
                            </button>
                        </td>
                    </tr>
                    <tr id="ops-getnet-{{ $loop->index }}" class="collapse operaciones-getnet">
                        <td colspan="6">
                            <div class="operaciones-getnet-contenido">
                                <strong class="d-block mb-1">Operaciones Getnet del documento</strong>
                                @foreach($doc['operaciones'] as $operacion)
                                    <div class="operacion-getnet-linea">
                                        <span><strong>Hora:</strong> {{ $operacion['hora'] }}</span>
                                        <span><strong>Monto:</strong> ${{ number_format($operacion['monto'], 0, ',', '.') }}</span>
                                        <span class="referencia-op"><strong>Operación:</strong> {{ $operacion['referencia'] ?: 'Sin referencia' }}</span>
                                        <button type="button"
                                                class="btn btn-warning btn-sm ml-auto"
                                                data-id-pago="{{ $operacion['id_pago'] }}"
                                                data-referencia="{{ $operacion['referencia'] }}"
                                                onclick="editar_operacion_desde_boton(this)"
                                                title="Editar número de operación">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalEditarOperacionPago" tabindex="-1" aria-labelledby="modalEditarOperacionPagoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarOperacionPagoLabel">Editar operación de pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label for="referencia" class="col-form-label">Referencia / número de operación:</label>
                    <input type="text" class="form-control" id="referencia" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardar_operacion()">Guardar</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="id_pago" value="">

<script>
    function editar_operacion_desde_boton(btn) {
        $('#id_pago').val($(btn).data('id-pago'));
        $('#referencia').val($(btn).attr('data-referencia') || '');
        $('#modalEditarOperacionPago').modal('show');
    }

    function guardar_operacion() {
        let nueva_referencia = $.trim($('#referencia').val());
        let id = $('#id_pago').val();

        if (!id) {
            Vue.swal({
                title: 'ERROR',
                text: 'No se pudo identificar el pago a modificar.',
                icon: 'error'
            });
            return;
        }

        if (nueva_referencia === '') {
            Vue.swal({
                title: 'ERROR',
                text: 'La referencia no puede estar vacía.',
                icon: 'error'
            });
            return;
        }

        let url = '{{ url("/reportes/editar_operacion_pago_getnet") }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                id_pago: id,
                referencia: nueva_referencia
            },
            success: function(resp) {
                if (resp === 'OK') {
                    $('#modalEditarOperacionPago').modal('hide');
                    $('body').css('padding-right', '0px');
                    dame_detalle('{{$fecha}}');

                    Vue.swal({
                        title: 'OK',
                        text: 'Operación actualizada',
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false
                    });
                } else {
                    Vue.swal({
                        title: 'ERROR',
                        text: (resp && resp.message) ? resp.message : resp,
                        icon: 'error'
                    });
                }
            },
            error: function(error) {
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error'
                });
            }
        });
    }
</script>
@else
<div class="alert alert-info mb-0">
    No existen operaciones Getnet asociadas a boletas o facturas para el día
    <strong>{{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}</strong>.
</div>
@endif
