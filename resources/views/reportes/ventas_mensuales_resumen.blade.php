<style>
    .rendimiento-resumen .metric-card {
        min-height: 118px;
        background: #f8f9fa;
        border: 1px solid #e7eaed;
        border-left: 4px solid #6495ed;
        border-radius: 4px;
        padding: 13px;
        margin-bottom: 14px;
    }

    .rendimiento-resumen .metric-card.exito {
        border-left-color: #28a745;
    }

    .rendimiento-resumen .metric-card.advertencia {
        border-left-color: #ffc107;
    }

    .rendimiento-resumen .metric-card.getnet {
        border-left-color: #6f42c1;
    }

    .rendimiento-resumen .metric-card.nota {
        border-left-color: #fd7e14;
    }

    .rendimiento-resumen .metric-label {
        color: #6c757d;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .rendimiento-resumen .metric-value {
        color: #212529;
        font-size: 23px;
        font-weight: 700;
        line-height: 1.2;
    }

    .rendimiento-resumen .metric-detail {
        color: #6c757d;
        font-size: 12px;
        margin-top: 7px;
    }

    .rendimiento-seccion {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        margin-bottom: 18px;
    }

    .rendimiento-seccion .seccion-titulo {
        background: #f1f3f5;
        border-bottom: 1px solid #dfe3e8;
        padding: 11px 15px;
        font-weight: 700;
    }

    .tabla-scroll {
        max-height: 580px;
        overflow: auto;
    }

    .tabla-rendimiento {
        min-width: 900px;
        margin-bottom: 0;
    }

    .tabla-rendimiento thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #bff7ea;
        border-bottom: 2px solid #5bb9a6;
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
    }

    .tabla-rendimiento td {
        vertical-align: middle;
    }

    .valor-positivo {
        color: #218838;
        font-weight: 700;
    }

    .valor-negativo {
        color: #c82333;
        font-weight: 700;
    }

    .fila-total td {
        background: rgb(219, 219, 255);
    }

    .fila-rendimiento td {
        background: #b9d3ff;
    }

    .fila-participacion td {
        background: #dce9ff;
    }

    .letra-chica {
        font-size: 12px;
    }

    .cabecera-resumen-mensual {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .cabecera-resumen-mensual .btn {
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .cabecera-resumen-mensual {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="rendimiento-resumen">
    <div class="alert alert-info cabecera-resumen-mensual">
        <div>
            <strong>{{ $nombreMes }} {{ $anio }}</strong>

            <span class="ml-2">
                {{ \Carbon\Carbon::parse($fechaInicio)->format('d-m-Y') }}
                al
                {{ \Carbon\Carbon::parse($fechaTermino)->format('d-m-Y') }}
            </span>
        </div>

        <button type="button"
                id="btn_ver_resumen_mensual"
                class="btn btn-sm btn-outline-secondary"
                onclick="solicitar_clave_resumen_mensual()">
            <i class="fa fa-key"></i>
            Ver indicadores
        </button>
    </div>

    <div id="bloque_resumen_mensual" style="display: none;">
        <div class="row">
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-label">Ventas brutas (con IVA)</div>
                <div class="metric-value">
                    ${{ number_format($resumen['venta_bruta'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">Monto final vendido durante el mes.</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-label">Ventas netas (sin IVA)</div>
                <div class="metric-value">
                    ${{ number_format($resumen['venta_neta'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">Base utilizada para calcular rentabilidad.</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-label">Costo productos vendidos</div>
                <div class="metric-value">
                    ${{ number_format($resumen['costo_productos_vendidos'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">Costo histórico de los productos vendidos.</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card exito">
                <div class="metric-label">Utilidad bruta</div>
                <div class="metric-value {{ $resumen['utilidad_bruta'] >= 0 ? 'valor-positivo' : 'valor-negativo' }}">
                    ${{ number_format($resumen['utilidad_bruta'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">
                    Margen {{ number_format($resumen['margen_bruto'], 2, ',', '.') }}%
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-label">Documentos</div>
                <div class="metric-value">
                    {{ number_format($resumen['cantidad_documentos'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">
                    {{ $resumen['cantidad_facturas'] }} facturas /
                    {{ $resumen['cantidad_boletas'] }} boletas
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-label">Unidades vendidas</div>
                <div class="metric-value">
                    {{ number_format($resumen['cantidad_unidades'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">Suma de cantidades de los detalles.</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card getnet">
                <div class="metric-label">Cobros Getnet</div>
                <div class="metric-value">
                    ${{ number_format($resumenGetnet['total'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">
                    {{ $resumenGetnet['operaciones'] }} operaciones ·
                    {{ number_format($resumenGetnet['porcentaje_total'], 2, ',', '.') }}%
                    del total cobrado
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="metric-card nota">
                <div class="metric-label">Notas de crédito</div>
                <div class="metric-value">
                    {{ number_format($resumenNotasCredito['cantidad'], 0, ',', '.') }}
                </div>
                <div class="metric-detail">
                    ${{ number_format($resumenNotasCredito['total'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    @if($resumen['cantidad_items_sin_costo'] > 0)
        <div class="alert alert-warning">
            Existen {{ $resumen['cantidad_items_sin_costo'] }} líneas por
            ${{ number_format($resumen['venta_sin_costo'], 0, ',', '.') }}
            sin costo histórico. No fueron incluidas en el margen.
        </div>
    @endif
    </div>

    @php
        $totalGeneral = 0;
        $totalGetnet = 0;
        $totalBoletas = 0;
        $totalFacturas = 0;

        $totalesUsuario = [];
        $getnetUsuario = [];
        $boletasUsuario = [];
        $facturasUsuario = [];

        foreach ($usuarios as $usuario) {
            $totalesUsuario[$usuario->name] = 0;
            $getnetUsuario[$usuario->name] = 0;
            $boletasUsuario[$usuario->name] = 0;
            $facturasUsuario[$usuario->name] = 0;
        }
    @endphp

    <div class="rendimiento-seccion">
        <div class="seccion-titulo">
            Totales por forma de pago y vendedor
        </div>

        <div class="tabla-scroll">
            <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                <thead>
                    <tr>
                        <th>Forma</th>
                        @foreach($usuarios as $usuario)
                            <th class="text-center letra-chica">
                                {{ $usuario->name }}
                            </th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($formasPago as $forma)
                        @php $totalForma = 0; @endphp
                        <tr>
                            <td class="letra-chica">
                                {{ $forma->formapago }}
                            </td>

                            @foreach($usuarios as $usuario)
                                @php
                                    $valor = (float) (
                                        $totales[$usuario->name][$forma->formapago]
                                        ?? 0
                                    );

                                    $totalForma += $valor;
                                    $totalesUsuario[$usuario->name] += $valor;
                                @endphp

                                <td class="text-right letra-chica">
                                    {{ $valor > 0
                                        ? number_format($valor, 0, ',', '.')
                                        : '' }}
                                </td>
                            @endforeach

                            <td class="text-right letra-chica">
                                {{ $totalForma > 0
                                    ? number_format($totalForma, 0, ',', '.')
                                    : '' }}
                            </td>
                        </tr>
                    @endforeach

                    <tr class="fila-total">
                        <td><strong>TOTAL</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $totalGeneral +=
                                    $totalesUsuario[$usuario->name];
                            @endphp

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $totalesUsuario[$usuario->name],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right">
                            <strong>
                                {{ number_format($totalGeneral, 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rendimiento-seccion">
        <div class="seccion-titulo">
            Rendimiento por vendedor
        </div>

        <div class="tabla-scroll">
            <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                <thead>
                    <tr>
                        <th>Indicador</th>
                        @foreach($usuarios as $usuario)
                            <th class="text-center letra-chica">
                                {{ $usuario->name }}
                            </th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $vendedoresPorId = $vendedores->keyBy('id_vendedor');

                        $filasRendimiento = [
                            'Venta bruta' => 'venta_bruta',
                            'Venta neta' => 'venta_neta',
                            'Costo productos vendidos' =>
                                'costo_productos_vendidos',
                            'Utilidad bruta' => 'utilidad_bruta',
                            'Unidades vendidas' => 'cantidad_unidades',
                            'Documentos' => 'cantidad_documentos',
                        ];
                    @endphp

                    @foreach($filasRendimiento as $etiqueta => $campo)
                        @php $totalFila = 0; @endphp
                        <tr>
                            <td>{{ $etiqueta }}</td>

                            @foreach($usuarios as $usuario)
                                @php
                                    $datoVendedor = $vendedoresPorId->get(
                                        $usuario->id
                                    );

                                    $valor = $datoVendedor
                                        ? (float) $datoVendedor[$campo]
                                        : 0;

                                    $totalFila += $valor;
                                @endphp

                                <td class="text-right
                                    {{ $campo === 'utilidad_bruta'
                                        ? ($valor >= 0
                                            ? 'valor-positivo'
                                            : 'valor-negativo')
                                        : '' }}">
                                    @if(in_array(
                                        $campo,
                                        ['cantidad_unidades', 'cantidad_documentos']
                                    ))
                                        {{ number_format($valor, 0, ',', '.') }}
                                    @else
                                        ${{ number_format($valor, 0, ',', '.') }}
                                    @endif
                                </td>
                            @endforeach

                            <td class="text-right">
                                <strong>
                                    @if(in_array(
                                        $campo,
                                        ['cantidad_unidades', 'cantidad_documentos']
                                    ))
                                        {{ number_format($totalFila, 0, ',', '.') }}
                                    @else
                                        ${{ number_format($totalFila, 0, ',', '.') }}
                                    @endif
                                </strong>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="fila-rendimiento">
                        <td><strong>Margen bruto</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $datoVendedor = $vendedoresPorId->get(
                                    $usuario->id
                                );

                                $margen = $datoVendedor
                                    ? (float) $datoVendedor['margen_bruto']
                                    : 0;
                            @endphp

                            <td class="text-right
                                {{ $margen >= 0
                                    ? 'valor-positivo'
                                    : 'valor-negativo' }}">
                                <strong>
                                    {{ number_format($margen, 2, ',', '.') }}%
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right
                            {{ $resumen['margen_bruto'] >= 0
                                ? 'valor-positivo'
                                : 'valor-negativo' }}">
                            <strong>
                                {{ number_format(
                                    $resumen['margen_bruto'],
                                    2,
                                    ',',
                                    '.'
                                ) }}%
                            </strong>
                        </td>
                    </tr>

                    <tr class="fila-participacion">
                        <td><strong>Participación en ventas netas</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $datoVendedor = $vendedoresPorId->get(
                                    $usuario->id
                                );

                                $ventaNetaVendedor = $datoVendedor
                                    ? (float) $datoVendedor['venta_neta']
                                    : 0;

                                $participacion = $resumen['venta_neta'] > 0
                                    ? (
                                        $ventaNetaVendedor
                                        / $resumen['venta_neta']
                                    ) * 100
                                    : 0;
                            @endphp

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $participacion,
                                        2,
                                        ',',
                                        '.'
                                    ) }}%
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right"><strong>100,00%</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rendimiento-seccion">
        <div class="seccion-titulo">
            Getnet resumen
        </div>

        <div class="tabla-scroll">
            <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                <thead>
                    <tr>
                        <th>Getnet</th>
                        @foreach($usuarios as $usuario)
                            <th class="text-center letra-chica">
                                {{ $usuario->name }}
                            </th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($formasPago as $forma)
                        @if(in_array((int) $forma->id, [2, 5], true))
                            @php $totalFormaGetnet = 0; @endphp
                            <tr>
                                <td>{{ $forma->formapago }}</td>

                                @foreach($usuarios as $usuario)
                                    @php
                                        $valorGetnet = (float) (
                                            $getnetPorUsuario[
                                                $usuario->name
                                            ][$forma->formapago]
                                            ?? 0
                                        );

                                        $totalFormaGetnet += $valorGetnet;
                                        $getnetUsuario[$usuario->name]
                                            += $valorGetnet;
                                    @endphp

                                    <td class="text-right">
                                        {{ $valorGetnet > 0
                                            ? number_format(
                                                $valorGetnet,
                                                0,
                                                ',',
                                                '.'
                                            )
                                            : '' }}
                                    </td>
                                @endforeach

                                <td class="text-right">
                                    {{ $totalFormaGetnet > 0
                                        ? number_format(
                                            $totalFormaGetnet,
                                            0,
                                            ',',
                                            '.'
                                        )
                                        : '' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    <tr class="fila-total">
                        <td><strong>TOTAL</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $totalGetnet +=
                                    $getnetUsuario[$usuario->name];
                            @endphp

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $getnetUsuario[$usuario->name],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right">
                            <strong>
                                {{ number_format($totalGetnet, 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($notasCredito->count() > 0)
        <div class="rendimiento-seccion">
            <div class="seccion-titulo">
                Notas de crédito
            </div>

            <div class="tabla-scroll">
                <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                    <thead>
                        <tr>
                            <th class="text-center">N°</th>
                            <th>Fecha</th>
                            <th>Referencia</th>
                            <th>Motivo</th>
                            <th class="text-right">Total</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($notasCredito as $nota)
                            @php
                                $partesReferencia = explode(
                                    '*',
                                    (string) $nota->docum_referencia
                                );

                                $tipoReferencia =
                                    $partesReferencia[0] ?? '';

                                $numeroReferencia =
                                    $partesReferencia[1] ?? '';

                                $nombreDocumento =
                                    $tipoReferencia === 'fa'
                                        ? 'Factura'
                                        : (
                                            $tipoReferencia === 'bo'
                                                ? 'Boleta'
                                                : strtoupper(
                                                    $tipoReferencia
                                                )
                                        );
                            @endphp

                            <tr>
                                <td class="text-center">
                                    {{ $nota->num_nota_credito }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse(
                                        $nota->fecha_emision
                                    )->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $nombreDocumento }}
                                    @if($numeroReferencia !== '')
                                        N° {{ $numeroReferencia }}
                                    @endif
                                </td>
                                <td>
                                    {{ $nota->motivo_correccion }}
                                </td>
                                <td class="text-right">
                                    {{ number_format(
                                        $nota->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>
                                <td>
                                    {{ $nota->usuario ?: 'NO DETERMINADO' }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="fila-total">
                            <td colspan="4" class="text-right">
                                <strong>TOTAL:</strong>
                            </td>
                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $resumenNotasCredito['total'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="rendimiento-seccion">
        <div class="seccion-titulo">
            Boletas por forma de pago
        </div>

        <div class="tabla-scroll">
            <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                <thead>
                    <tr>
                        <th>Forma</th>
                        @foreach($usuarios as $usuario)
                            <th class="text-center letra-chica">
                                {{ $usuario->name }}
                            </th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($formasPago as $forma)
                        @php $totalFormaBoleta = 0; @endphp
                        <tr>
                            <td>{{ $forma->formapago }}</td>

                            @foreach($usuarios as $usuario)
                                @php
                                    $valorBoleta = (float) (
                                        $boletasPago[
                                            $usuario->name
                                        ][$forma->formapago]
                                        ?? 0
                                    );

                                    $totalFormaBoleta += $valorBoleta;
                                    $boletasUsuario[$usuario->name]
                                        += $valorBoleta;
                                @endphp

                                <td class="text-right">
                                    {{ $valorBoleta > 0
                                        ? number_format(
                                            $valorBoleta,
                                            0,
                                            ',',
                                            '.'
                                        )
                                        : '' }}
                                </td>
                            @endforeach

                            <td class="text-right">
                                {{ $totalFormaBoleta > 0
                                    ? number_format(
                                        $totalFormaBoleta,
                                        0,
                                        ',',
                                        '.'
                                    )
                                    : '' }}
                            </td>
                        </tr>
                    @endforeach

                    <tr class="fila-total">
                        <td><strong>TOTAL</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $totalBoletas +=
                                    $boletasUsuario[$usuario->name];
                            @endphp

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $boletasUsuario[$usuario->name],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right">
                            <strong>
                                {{ number_format($totalBoletas, 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rendimiento-seccion">
        <div class="seccion-titulo">
            Facturas por forma de pago
        </div>

        <div class="tabla-scroll">
            <table class="table table-sm table-hover table-bordered tabla-rendimiento">
                <thead>
                    <tr>
                        <th>Forma</th>
                        @foreach($usuarios as $usuario)
                            <th class="text-center letra-chica">
                                {{ $usuario->name }}
                            </th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($formasPago as $forma)
                        @php $totalFormaFactura = 0; @endphp
                        <tr>
                            <td>{{ $forma->formapago }}</td>

                            @foreach($usuarios as $usuario)
                                @php
                                    $valorFactura = (float) (
                                        $facturasPago[
                                            $usuario->name
                                        ][$forma->formapago]
                                        ?? 0
                                    );

                                    $totalFormaFactura += $valorFactura;
                                    $facturasUsuario[$usuario->name]
                                        += $valorFactura;
                                @endphp

                                <td class="text-right">
                                    {{ $valorFactura > 0
                                        ? number_format(
                                            $valorFactura,
                                            0,
                                            ',',
                                            '.'
                                        )
                                        : '' }}
                                </td>
                            @endforeach

                            <td class="text-right">
                                {{ $totalFormaFactura > 0
                                    ? number_format(
                                        $totalFormaFactura,
                                        0,
                                        ',',
                                        '.'
                                    )
                                    : '' }}
                            </td>
                        </tr>
                    @endforeach

                    <tr class="fila-total">
                        <td><strong>TOTAL</strong></td>

                        @foreach($usuarios as $usuario)
                            @php
                                $totalFacturas +=
                                    $facturasUsuario[$usuario->name];
                            @endphp

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        $facturasUsuario[$usuario->name],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>
                            </td>
                        @endforeach

                        <td class="text-right">
                            <strong>
                                {{ number_format($totalFacturas, 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
