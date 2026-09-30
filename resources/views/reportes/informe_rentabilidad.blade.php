@extends('plantillas.app')

@section('titulo', 'INFORME DE RENTABILIDAD')

@section('style')
<link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
<style>
    .rentabilidad-wrapper {
        margin: 0;
    }

    .rentabilidad-header {
        background: #6495ed;
        color: #fff;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        margin-bottom: 10px;
    }

    .filtros-card,
    .resumen-card,
    .tabla-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .filtros-card .card-body,
    .resumen-card .card-body,
    .tabla-card .card-body {
        padding: 15px;
    }

    .indicador {
        min-height: 112px;
        border-left: 4px solid #6495ed;
        padding: 12px;
        background: #f8f9fa;
        margin-bottom: 12px;
        border-radius: 3px;
    }

    .indicador .titulo {
        color: #6c757d;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .indicador .valor {
        font-size: 22px;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }

    .indicador .detalle {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
    }

    .indicador-exito {
        border-left-color: #28a745;
    }

    .indicador-advertencia {
        border-left-color: #ffc107;
    }

    .indicador-error {
        border-left-color: #dc3545;
    }

    .tabla-card .card-header {
        background: #f1f3f5;
        border-bottom: 1px solid #dfe3e8;
        padding: 10px 15px;
        font-weight: 700;
    }

    .tabla-card .card-body{
        padding:0;
    }

    .tabla-responsive{
        max-height:650px;
        overflow:auto;
    }

    .tabla-rentabilidad {
        width: 100%;
        min-width: 950px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .tabla-rentabilidad thead th {
        position: sticky;
        top: 0;
        z-index: 100;
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
        background: #bff7ea !important;
        color: #212529;
        border-top: 1px solid #9fdccc;
        border-bottom: 2px solid #5bb9a6;
        box-shadow: 0 2px 4px rgba(0,0,0,.15);
    }

    .tabla-rentabilidad td {
        vertical-align: middle;
        font-size: 13px;
    }

    .texto-derecha {
        text-align: right;
        white-space: nowrap;
    }

    .margen-positivo {
        color: #218838;
        font-weight: 700;
    }

    .margen-negativo {
        color: #c82333;
        font-weight: 700;
    }

    .sin-costo {
        color: #c82333;
        font-weight: 700;
    }

    .cargando {
        display: none;
        padding: 20px;
        text-align: center;
        font-weight: 700;
        color: #6c757d;
    }

    .sin-resultados {
        padding: 25px;
        text-align: center;
        color: #6c757d;
    }

    .tabs-rentabilidad .nav-link {
        cursor: pointer;
    }


    /* Select2 - buscador de repuestos */
    .select2-container {
        width: 100% !important;
        display: block;
    }

    .select2-container--default .select2-selection--single {
        height: 39px;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        background-color: #fff;
        outline: 0;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff;
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .15);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 37px;
        padding-left: 12px;
        padding-right: 38px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 37px;
        width: 34px;
        top: 1px;
        right: 1px;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        float: none;
        position: absolute;
        right: 32px;
        top: 6px;
        font-size: 20px;
        color: #6c757d;
        z-index: 2;
    }

    .select2-dropdown {
        border: 1px solid #80bdff;
        border-radius: 0 0 .25rem .25rem;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .15);
        overflow: hidden;
        z-index: 99999;
    }

    .select2-search--dropdown {
        padding: 8px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .select2-search--dropdown .select2-search__field {
        width: 100% !important;
        height: 36px;
        padding: 6px 10px;
        border: 1px solid #ced4da !important;
        border-radius: .25rem;
        outline: 0;
    }

    .select2-results__options {
        max-height: 320px !important;
    }

    .select2-results__option {
        padding: 8px 12px;
        font-size: 13px;
        line-height: 1.35;
        border-bottom: 1px solid #f1f3f5;
    }

    .select2-results__option:last-child {
        border-bottom: 0;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #6495ed;
        color: #fff;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e9f2ff;
        color: #212529;
    }

    .repuesto-select2-opcion strong {
        display: block;
        font-size: 13px;
    }

    .repuesto-select2-descripcion {
        margin-top: 2px;
        font-size: 12px;
    }

    .repuesto-select2-codigo-proveedor {
        margin-top: 2px;
        font-size: 11px;
        color: #6c757d;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected]
    .repuesto-select2-codigo-proveedor {
        color: #eef4ff;
    }


    .indicador-bruto {
        border-left-color: #17a2b8;
    }

    .indicador-getnet {
        border-left-color: #6f42c1;
    }

    .indicador-nota {
        border-left-color: #fd7e14;
    }

    .tasa-baja {
        color: #218838;
        font-weight: 700;
    }

    .tasa-media {
        color: #d39e00;
        font-weight: 700;
    }

    .tasa-alta {
        color: #c82333;
        font-weight: 700;
    }

    .campo-comision {
        max-width: 140px;
    }

    @media (max-width: 767px) {
        .indicador .valor {
            font-size: 18px;
        }
    }
</style>
@endsection

@section('javascript_footer')
<script>
    let ultimoInformeRentabilidad = null;

    $(document).ready(function () {
        const hoy = new Date();
        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

        $('#fecha_inicio').val(formatearFechaInput(primerDia));
        $('#fecha_termino').val(formatearFechaInput(hoy));

        cargarSelect2(function () {
            inicializarBuscadorRepuestos();
        });

        $('#btn_consultar').on('click', function () {
            consultarInformeRentabilidad();
        });

        $('#btn_limpiar').on('click', function () {
            limpiarFiltros();
        });
    });



    function cargarSelect2(callback) {
        if (typeof window.jQuery === 'undefined') {
            console.error('jQuery no está disponible.');
            return;
        }

        if (typeof window.jQuery.fn.select2 === 'function') {
            callback();
            return;
        }

        const urlSelect2 = '{{ asset("vendor/select2/select2.min.js") }}';

        window.jQuery.ajax({
            url: urlSelect2,
            dataType: 'script',
            cache: true
        })
        .done(function () {
            if (typeof window.jQuery.fn.select2 === 'function') {
                callback();
            } else {
                console.error(
                    'El archivo Select2 cargó, pero no se registró en la instancia actual de jQuery.'
                );
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error(
                'No fue posible cargar Select2:',
                textStatus,
                errorThrown
            );
        });
    }

    function inicializarBuscadorRepuestos() {
        const $selector = $('#id_repuesto');

        if (!$selector.length) {
            console.error('No existe el selector #id_repuesto.');
            return;
        }

        if (typeof window.jQuery === 'undefined') {
            console.error('jQuery no está disponible al inicializar Select2.');
            return;
        }

        if (typeof window.jQuery.fn.select2 !== 'function') {
            console.error('Select2 no está disponible después de intentar cargarlo.');
            console.log('Versión jQuery:', window.jQuery.fn.jquery);
            return;
        }

        if ($selector.hasClass('select2-hidden-accessible')) {
            return;
        }

        window.jQuery($selector).select2({
            width: '100%',
            placeholder: 'Buscar por código interno, proveedor o descripción',
            allowClear: true,
            minimumInputLength: 2,
            language: {
                inputTooShort: function () {
                    return 'Escriba al menos 2 caracteres';
                },
                searching: function () {
                    return 'Buscando...';
                },
                noResults: function () {
                    return 'No se encontraron repuestos';
                },
                errorLoading: function () {
                    return 'No fue posible cargar los resultados';
                }
            },
            ajax: {
                url: '{{ url("/repuesto/buscar-select2") }}',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function (respuesta) {
                    return {
                        results: respuesta.results || []
                    };
                },
                cache: true
            },
            templateResult: function (repuesto) {
                if (repuesto.loading) {
                    return repuesto.text;
                }

                const codigo = escaparHtml(repuesto.codigo_interno || '');
                const descripcion = escaparHtml(repuesto.descripcion || '');
                const codigoProveedor = escaparHtml(repuesto.codigo_proveedor || '');

                let html = '<div class="repuesto-select2-opcion">' +
                    '<strong>' + codigo + '</strong>' +
                    '<div class="repuesto-select2-descripcion">' + descripcion + '</div>';

                if (codigoProveedor) {
                    html += '<div class="repuesto-select2-codigo-proveedor">' +
                        'Código proveedor: ' + codigoProveedor +
                    '</div>';
                }

                html += '</div>';

                return $(html);
            },
            templateSelection: function (repuesto) {
                return repuesto.text || repuesto.codigo_interno || '';
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });
    }

    function formatearFechaInput(fecha) {
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return año + '-' + mes + '-' + dia;
    }

    function consultarInformeRentabilidad() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaTermino = $('#fecha_termino').val();

        if (!fechaInicio || !fechaTermino) {
            Vue.swal({
                title: 'Faltan fechas',
                text: 'Debe seleccionar una fecha inicial y una fecha final.',
                icon: 'warning'
            });
            return;
        }

        if (fechaInicio > fechaTermino) {
            Vue.swal({
                title: 'Rango inválido',
                text: 'La fecha inicial no puede ser posterior a la fecha final.',
                icon: 'warning'
            });
            return;
        }

        // Conservar las selecciones enviadas aunque se editen durante la consulta.
        const resumenBusqueda = obtenerResumenBusqueda();
        bloquearConsulta(true);

        $.ajax({
            type: 'POST',
            url: '{{ route("reportes.informe-rentabilidad.consultar") }}',
            data: {
                _token: '{{ csrf_token() }}',
                fecha_inicio: fechaInicio,
                fecha_termino: fechaTermino,
                id_vendedor: $('#id_vendedor').val() || null,
                id_proveedor: $('#id_proveedor').val() || null,
                id_local: $('#id_local').val() || null,
                id_repuesto: $('#id_repuesto').val() || null,
                comision_getnet_credito:
                    $('#comision_getnet_credito').val() || 0,
                comision_getnet_debito:
                    $('#comision_getnet_debito').val() || 0
            },
            success: function (respuesta) {
                ultimoInformeRentabilidad = respuesta;
                renderizarInforme(respuesta);
                if (respuesta && respuesta.estado === 1) {
                    $('#resumen_busqueda_texto').text(resumenBusqueda);
                    $('#resumen_busqueda').show();
                }
            },
            error: function (error) {
                let mensaje = 'No fue posible generar el informe.';

                if (error.responseJSON && error.responseJSON.mensaje) {
                    mensaje = error.responseJSON.mensaje;
                } else if (error.responseText) {
                    mensaje = error.responseText;
                }

                Vue.swal({
                    title: 'ERROR',
                    text: mensaje,
                    icon: 'error'
                });
            },
            complete: function () {
                bloquearConsulta(false);
            }
        });
    }

    function obtenerResumenBusqueda() {
        const partes = ['Desde ' + formatearFecha($('#fecha_inicio').val()) +
            ' hasta ' + formatearFecha($('#fecha_termino').val())];
        [
            ['id_vendedor', 'Vendedor', 'Todos'],
            ['id_proveedor', 'Proveedor', 'Todos'],
            ['id_local', 'Local', 'Todos'],
            ['id_repuesto', 'Repuesto', 'Todos']
        ].forEach(function (filtro) {
            const campo = $('#' + filtro[0]);
            const nombre = campo.val() ? campo.find('option:selected').text().trim() : filtro[2];
            partes.push(filtro[1] + ': ' + nombre);
        });
        const credito = Number($('#comision_getnet_credito').val() || 0);
        const debito = Number($('#comision_getnet_debito').val() || 0);
        if (credito || debito) {
            partes.push('Comisión Getnet: crédito ' + credito + '% / débito ' + debito + '%');
        }
        return partes.join(' · ');
    }

    function bloquearConsulta(bloquear) {
        $('#btn_consultar').prop('disabled', bloquear);
        $('#btn_limpiar').prop('disabled', bloquear);
        $('#cargando_informe').toggle(bloquear);

        if (bloquear) {
            $('#btn_consultar').html('<i class="fa fa-spinner fa-spin"></i> Consultando');
        } else {
            $('#btn_consultar').html('<i class="fa fa-search"></i> Consultar');
        }
    }

    function renderizarInforme(respuesta) {
        if (!respuesta || respuesta.estado !== 1) {
            Vue.swal({
                title: 'ERROR',
                text: respuesta && respuesta.mensaje
                    ? respuesta.mensaje
                    : 'La respuesta del servidor no es válida.',
                icon: 'error'
            });
            return;
        }

        const resumen = respuesta.resumen || {};

        $('#valor_venta_bruta').text(formatearMoneda(resumen.venta_bruta));
        $('#detalle_venta_bruta').text(
            formatearMoneda(resumen.venta_bruta_facturas) + ' facturas / ' +
            formatearMoneda(resumen.venta_bruta_boletas) + ' boletas'
        );

        $('#valor_venta_neta').text(formatearMoneda(resumen.venta_neta));
        $('#detalle_venta_neta').text(
            formatearMoneda(resumen.venta_neta_facturas) + ' facturas / ' +
            formatearMoneda(resumen.venta_neta_boletas) + ' boletas'
        );

        $('#valor_costo').text(formatearMoneda(resumen.costo_mercaderia));
        $('#detalle_costo').text(
            formatearPorcentaje(resumen.porcentaje_cobertura_costos) +
            ' de cobertura de precio de compra histórico'
        );

        $('#valor_ganancia').text(formatearMoneda(resumen.ganancia_bruta));
        $('#valor_margen').text(formatearPorcentaje(resumen.margen_porcentaje));
        aplicarClaseMargen($('#valor_margen'), resumen.margen_porcentaje);

        $('#valor_documentos').text(formatearNumero(resumen.cantidad_documentos));
        $('#detalle_documentos').text(
            formatearNumero(resumen.cantidad_facturas) + ' facturas / ' +
            formatearNumero(resumen.cantidad_boletas) + ' boletas'
        );

        $('#valor_unidades').text(formatearNumero(resumen.cantidad_unidades));
        $('#valor_sin_costo').text(formatearMoneda(resumen.venta_sin_costo));
        $('#detalle_sin_costo').text(
            formatearNumero(resumen.cantidad_items_sin_costo) + ' líneas sin costo histórico'
        );

        $('#valor_getnet').text(formatearMoneda(resumen.venta_getnet));
        $('#detalle_getnet').text(
            formatearPorcentaje(resumen.porcentaje_cobros_getnet) +
            ' de los cobros asociados al filtro'
        );

        $('#valor_getnet_operaciones').text(
            formatearNumero(resumen.operaciones_getnet)
        );
        $('#detalle_getnet_operaciones').text(
            formatearNumero(resumen.operaciones_getnet_credito) +
            ' crédito / ' +
            formatearNumero(resumen.operaciones_getnet_debito) +
            ' débito'
        );

        $('#valor_comision_getnet').text(
            formatearMoneda(resumen.comision_getnet_total)
        );
        $('#detalle_comision_getnet').text(
            'Crédito ' +
            formatearPorcentaje(
                resumen.comision_getnet_credito_porcentaje
            ) +
            ' / Débito ' +
            formatearPorcentaje(
                resumen.comision_getnet_debito_porcentaje
            )
        );

        $('#valor_ganancia_getnet').text(
            formatearMoneda(resumen.ganancia_despues_getnet)
        );
        $('#detalle_ganancia_getnet').text(
            'Margen ' +
            formatearPorcentaje(resumen.margen_despues_getnet)
        );
        aplicarClaseMargen(
            $('#valor_ganancia_getnet'),
            resumen.ganancia_despues_getnet
        );

        const notasCredito = respuesta.notas_credito || {};
        $('#valor_notas_credito').text(
            formatearNumero(notasCredito.cantidad_total)
        );
        $('#detalle_notas_credito').text(
            formatearMoneda(notasCredito.monto_total)
        );

        renderizarVendedores(respuesta.vendedores || []);
        renderizarProveedores(respuesta.proveedores || []);
        renderizarProductos(respuesta.productos || []);
        renderizarNotasCredito(
            (respuesta.notas_credito || {}).por_vendedor_original || [],
            (respuesta.notas_credito || {}).por_emisor || [],
            (respuesta.notas_credito || {}).detalle || []
        );
        renderizarDetalle(respuesta.detalle || []);

        $('#contenido_informe').show();

        if (Number(resumen.venta_sin_costo || 0) > 0) {
            $('#alerta_sin_costo').show();
            $('#alerta_sin_costo_texto').text(
                'Existen ventas por ' + formatearMoneda(resumen.venta_sin_costo) +
                ' cuyo costo histórico no pudo determinarse. Estas ventas no se incluyen en el margen calculado.'
            );
        } else {
            $('#alerta_sin_costo').hide();
        }
    }

    function renderizarVendedores(datos) {
        let html = '';

        datos.forEach(function (item) {
            html += '<tr>' +
                '<td>' + escaparHtml(item.vendedor) + '</td>' +
                '<td class="texto-derecha">' + formatearNumero(item.cantidad_unidades) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_neta) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.costo_mercaderia) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.ganancia) + '</td>' +
                '<td class="texto-derecha ' + claseMargen(item.margen_porcentaje) + '">' +
                    formatearPorcentaje(item.margen_porcentaje) +
                '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_sin_costo) + '</td>' +
            '</tr>';
        });

        $('#tbody_vendedores').html(html || filaSinResultados(7));
    }

    function renderizarProveedores(datos) {
        let html = '';

        datos.forEach(function (item) {
            html += '<tr>' +
                '<td>' + escaparHtml(item.proveedor) + '</td>' +
                '<td class="texto-derecha">' + formatearNumero(item.cantidad_unidades) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_neta) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.costo_mercaderia) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.ganancia) + '</td>' +
                '<td class="texto-derecha ' + claseMargen(item.margen_porcentaje) + '">' +
                    formatearPorcentaje(item.margen_porcentaje) +
                '</td>' +
            '</tr>';
        });

        $('#tbody_proveedores').html(html || filaSinResultados(6));
    }

    function renderizarProductos(datos) {
        let html = '';

        datos.forEach(function (item) {
            html += '<tr>' +
                '<td><a href="javascript:void(0)" onclick="dameInfoRepuestoModal(\'' + escaparHtml(item.id_repuesto) + '\')" data-target="#modalInfoRepuesto" data-toggle="modal">' + escaparHtml(item.codigo_interno) + '</a></td>' +
                '<td>' + escaparHtml(item.descripcion) + '</td>' +
                '<td>' + escaparHtml(item.proveedor) + '</td>' +
                '<td class="texto-derecha">' + formatearNumero(item.cantidad_vendida) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_bruta) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_neta) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.costo_mercaderia) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.ganancia) + '</td>' +
                '<td class="texto-derecha ' + claseMargen(item.margen_porcentaje) + '">' +
                    formatearPorcentaje(item.margen_porcentaje) +
                '</td>' +
            '</tr>';
        });

        $('#tbody_productos').html(html || filaSinResultados(9));
    }

    var peticionInfoRepuestoRentabilidad = null;
    var secuenciaInfoRepuestoRentabilidad = 0;

    function dameInfoRepuestoModal(idRepuesto, pagina = 1){
        const secuencia = ++secuenciaInfoRepuestoRentabilidad;
        if (peticionInfoRepuestoRentabilidad) peticionInfoRepuestoRentabilidad.abort();
        const url = @json(route('reportes.informe-rentabilidad.repuesto', ['id' => '__REPUESTO__'], false))
            .replace('__REPUESTO__', encodeURIComponent(idRepuesto));
        $('#modalBodyInfoRepuesto').attr('aria-busy', 'true').html(
            '<p class="text-center text-muted py-4" role="status">Cargando stock e historial de compras…</p>'
        );
        peticionInfoRepuestoRentabilidad = $.ajax({
            type: 'GET',
            url: url,
            data: {page: pagina},
            dataType: 'html',
            headers: {'Accept': 'text/html'},
            success: function(html){
                if (secuencia === secuenciaInfoRepuestoRentabilidad) $('#modalBodyInfoRepuesto').html(html);
            },
            error: function(xhr, estado){
                if (estado === 'abort' || secuencia !== secuenciaInfoRepuestoRentabilidad) return;
                const mensaje = xhr.status === 401 || xhr.status === 419
                    ? 'La sesión no está disponible para esta consulta. Recarga el informe e inicia sesión nuevamente si el sistema lo solicita.'
                    : xhr.status === 403 ? 'No tienes permiso para consultar esta información.'
                    : xhr.status === 404 ? 'No se encontró el repuesto.'
                    : 'No fue posible cargar la información. Cierra el modal y vuelve a intentarlo.';
                $('#modalBodyInfoRepuesto').empty().append($('<p>', {
                    'class': 'alert alert-danger mb-0', role: 'alert', text: mensaje
                }));
            },
            complete: function(){
                if (secuencia === secuenciaInfoRepuestoRentabilidad) {
                    $('#modalBodyInfoRepuesto').attr('aria-busy', 'false');
                    peticionInfoRepuestoRentabilidad = null;
                }
            }
        });
    }

    $(document).on('click', '#modalInfoRepuesto .rentabilidad-compras-pagina', function(){
        dameInfoRepuestoModal($(this).data('repuesto'), $(this).data('pagina'));
    });
    $(document).on('hide.bs.modal', '#modalInfoRepuesto', function(){
        ++secuenciaInfoRepuestoRentabilidad;
        if (peticionInfoRepuestoRentabilidad) peticionInfoRepuestoRentabilidad.abort();
        peticionInfoRepuestoRentabilidad = null;
        $('#modalBodyInfoRepuesto').empty().attr('aria-busy', 'false');
    });

    function renderizarNotasCredito(
        porVendedor,
        porEmisor,
        detalle
    ) {
        let htmlVendedor = '';

        porVendedor.forEach(function (item) {
            htmlVendedor += '<tr>' +
                '<td>' + escaparHtml(item.vendedor) + '</td>' +
                '<td class="texto-derecha">' +
                    formatearNumero(item.cantidad_documentos) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearNumero(item.cantidad_notas_credito) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearMoneda(item.monto_notas_credito) +
                '</td>' +
                '<td class="texto-derecha ' +
                    claseTasaNota(item.tasa_notas_credito) + '">' +
                    formatearPorcentaje(item.tasa_notas_credito) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearMoneda(item.promedio_nota_credito) +
                '</td>' +
            '</tr>';
        });

        $('#tbody_notas_vendedor').html(
            htmlVendedor || filaSinResultados(6)
        );

        let htmlEmisor = '';

        porEmisor.forEach(function (item) {
            htmlEmisor += '<tr>' +
                '<td>' + escaparHtml(item.usuario) + '</td>' +
                '<td class="texto-derecha">' +
                    formatearNumero(item.cantidad_notas_credito) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearMoneda(item.monto_notas_credito) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearMoneda(item.promedio_nota_credito) +
                '</td>' +
            '</tr>';
        });

        $('#tbody_notas_emisor').html(
            htmlEmisor || filaSinResultados(4)
        );

        let htmlDetalle = '';

        detalle.forEach(function (item) {
            let referencia = '-';

            if (item.tipo_documento_referencia &&
                item.numero_documento_referencia) {
                referencia =
                    String(item.tipo_documento_referencia).toUpperCase() +
                    ' N° ' +
                    item.numero_documento_referencia;
            }

            htmlDetalle += '<tr>' +
                '<td>' + formatearFecha(item.fecha_emision) + '</td>' +
                '<td>' +
                    escaparHtml(item.numero_nota_credito) +
                '</td>' +
                '<td>' + escaparHtml(referencia) + '</td>' +
                '<td>' +
                    escaparHtml(item.usuario_emisor) +
                '</td>' +
                '<td>' +
                    escaparHtml(item.vendedor_documento_original) +
                '</td>' +
                '<td class="texto-derecha">' +
                    formatearMoneda(item.total) +
                '</td>' +
                '<td>' +
                    escaparHtml(item.motivo_correccion || '-') +
                '</td>' +
            '</tr>';
        });

        $('#tbody_notas_detalle').html(
            htmlDetalle || filaSinResultados(7)
        );
    }

    function claseTasaNota(valor) {
        const tasa = Number(valor || 0);

        if (tasa < 1) {
            return 'tasa-baja';
        }

        if (tasa <= 3) {
            return 'tasa-media';
        }

        return 'tasa-alta';
    }

    function renderizarDetalle(datos) {
        let html = '';

        datos.forEach(function (item) {
            const precioCompraUnitario = item.tiene_costo
                ? formatearMoneda(item.precio_compra)
                : '<span class="sin-costo">Sin costo</span>';

            const costoTotalCompra = item.tiene_costo
                ? formatearMoneda(item.costo_total)
                : '<span class="sin-costo">Sin costo</span>';

            const fleteRegistrado = item.tiene_costo
                ? formatearMoneda(item.flete_registrado)
                : '-';

            const facturaCompra = item.tiene_costo && item.factura_compra
                ? escaparHtml(item.factura_compra)
                : '-';

            const fechaCompra = item.tiene_costo
                ? formatearFecha(item.fecha_compra)
                : '-';

            const ganancia = item.tiene_costo
                ? formatearMoneda(item.ganancia)
                : '-';

            const margen = item.tiene_costo
                ? '<span class="' + claseMargen(item.margen_porcentaje) + '">' +
                    formatearPorcentaje(item.margen_porcentaje) +
                  '</span>'
                : '-';

            html += '<tr>' +
                '<td>' + formatearFecha(item.fecha_emision) + '</td>' +
                '<td>' + escaparHtml(item.tipo_documento) + ' N° ' + escaparHtml(item.numero_documento) + '</td>' +
                '<td>' + escaparHtml(item.codigo_interno) + '</td>' +
                '<td>' + escaparHtml(item.descripcion) + '</td>' +
                '<td>' + escaparHtml(item.vendedor) + '</td>' +
                '<td>' + escaparHtml(item.proveedor) + '</td>' +
                '<td class="texto-derecha">' + formatearNumero(item.cantidad) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_bruta) + '</td>' +
                '<td class="texto-derecha">' + formatearMoneda(item.venta_neta) + '</td>' +
                '<td class="texto-derecha">' + precioCompraUnitario + '</td>' +
                '<td class="texto-derecha">' + costoTotalCompra + '</td>' +
                '<td class="texto-derecha">' + fleteRegistrado + '</td>' +
                '<td>' + facturaCompra + '</td>' +
                '<td>' + fechaCompra + '</td>' +
                '<td class="texto-derecha">' + ganancia + '</td>' +
                '<td class="texto-derecha">' + margen + '</td>' +
            '</tr>';
        });

        $('#tbody_detalle').html(html || filaSinResultados(16));
    }

    function filaSinResultados(columnas) {
        return '<tr><td colspan="' + columnas + '" class="sin-resultados">No se encontraron resultados para los filtros seleccionados.</td></tr>';
    }

    function formatearMoneda(valor) {
        return new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP',
            maximumFractionDigits: 0
        }).format(Number(valor || 0));
    }

    function formatearNumero(valor) {
        return new Intl.NumberFormat('es-CL', {
            maximumFractionDigits: 2
        }).format(Number(valor || 0));
    }

    function formatearPorcentaje(valor) {
        return new Intl.NumberFormat('es-CL', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(valor || 0)) + '%';
    }

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        const partes = String(fecha).substring(0, 10).split('-');
        return partes.length === 3
            ? partes[2] + '-' + partes[1] + '-' + partes[0]
            : fecha;
    }

    function claseMargen(valor) {
        return Number(valor || 0) >= 0
            ? 'margen-positivo'
            : 'margen-negativo';
    }

    function aplicarClaseMargen(elemento, valor) {
        elemento.removeClass('margen-positivo margen-negativo');
        elemento.addClass(claseMargen(valor));
    }

    function escaparHtml(valor) {
        return $('<div>').text(valor === null || valor === undefined ? '-' : valor).html();
    }

    function limpiarFiltros() {
        const hoy = new Date();
        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

        $('#fecha_inicio').val(formatearFechaInput(primerDia));
        $('#fecha_termino').val(formatearFechaInput(hoy));
        $('#id_vendedor').val('');
        $('#id_proveedor').val('');
        $('#id_local').val('');
        $('#comision_getnet_credito').val('0');
        $('#comision_getnet_debito').val('0');

        if ($('#id_repuesto').hasClass('select2-hidden-accessible')) {
            $('#id_repuesto').val(null).trigger('change');
        } else {
            $('#id_repuesto').val('');
        }

        $('#contenido_informe').hide();
        $('#resumen_busqueda').hide();
        $('#resumen_busqueda_texto').empty();
        $('#alerta_sin_costo').hide();
        ultimoInformeRentabilidad = null;
    }
</script>
@endsection

@section('contenido_ingresa_datos')
<div class="rentabilidad-wrapper">
    <div class="rentabilidad-header">
        <h4 class="mb-0">
            <i class="fa fa-line-chart"></i>
            Informe de ventas y rentabilidad
        </h4>
    </div>

    <div class="filtros-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="fecha_inicio">Fecha inicial</label>
                    <input type="date" id="fecha_inicio" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="fecha_termino">Fecha final</label>
                    <input type="date" id="fecha_termino" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="id_vendedor">Vendedor</label>
                    <select id="id_vendedor" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($vendedores ?? []) as $vendedor)
                            <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="id_proveedor">Proveedor</label>
                    <select id="id_proveedor" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($proveedores ?? []) as $proveedor)
                            <option value="{{ $proveedor->id }}">
                                {{ $proveedor->empresa_nombre_corto ?: $proveedor->empresa_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="id_local">Local</label>
                    <select id="id_local" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($locales ?? []) as $local)
                            <option value="{{ $local->id }}">{{ $local->local_nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="id_repuesto">Repuesto</label>
                    <select id="id_repuesto"
                            class="form-control"
                            style="width:100%;">
                        <option value=""></option>
                    </select>
                    <small class="text-muted">
                        Busque por código interno, código de proveedor o descripción.
                    </small>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="comision_getnet_credito">
                        Comisión Getnet crédito (%)
                    </label>
                    <input type="number"
                           id="comision_getnet_credito"
                           class="form-control form-control-sm campo-comision"
                           min="0"
                           max="100"
                           step="0.01"
                           value="0">
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="comision_getnet_debito">
                        Comisión Getnet débito (%)
                    </label>
                    <input type="number"
                           id="comision_getnet_debito"
                           class="form-control form-control-sm campo-comision"
                           min="0"
                           max="100"
                           step="0.01"
                           value="0">
                </div>

                <div class="col-md-6 d-flex align-items-end mb-2">
                    <button type="button" id="btn_consultar" class="btn btn-sm btn-success mr-2">
                        <i class="fa fa-search"></i> Consultar
                    </button>

                    <button type="button" id="btn_limpiar" class="btn btn-sm btn-secondary">
                        <i class="fa fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="resumen_busqueda" class="alert alert-light border" style="display:none; overflow-wrap:anywhere;" role="status" aria-live="polite">
        <strong class="d-block mb-1"><i class="fa fa-filter" aria-hidden="true"></i> Filtros aplicados al informe</strong>
        <span id="resumen_busqueda_texto"></span>
    </div>

    <div id="cargando_informe" class="cargando">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
        <div class="mt-2">Calculando ventas, costos y márgenes...</div>
    </div>

    <div id="alerta_sin_costo" class="alert alert-warning" style="display:none;">
        <strong>Atención:</strong>
        <span id="alerta_sin_costo_texto"></span>
    </div>

    <div class="alert alert-info">
        <strong>Criterio de costo:</strong>
        el informe utiliza exclusivamente el precio de compra histórico.
        El flete registrado se muestra como referencia y no se suma al costo
        ni se descuenta nuevamente de la utilidad.
    </div>

    <div id="contenido_informe" style="display:none;">
        <div class="resumen-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-bruto">
                            <div class="titulo">Ventas brutas (con IVA)</div>
                            <div class="valor" id="valor_venta_bruta">$0</div>
                            <div class="detalle" id="detalle_venta_bruta">$0 facturas / $0 boletas</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador">
                            <div class="titulo">Ventas netas (sin IVA)</div>
                            <div class="valor" id="valor_venta_neta">$0</div>
                            <div class="detalle" id="detalle_venta_neta">$0 facturas / $0 boletas</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador">
                            <div class="titulo">Costo de compra de productos vendidos</div>
                            <div class="valor" id="valor_costo">$0</div>
                            <div class="detalle" id="detalle_costo">Solo precio de compra histórico, sin sumar flete</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-exito">
                            <div class="titulo">Utilidad bruta</div>
                            <div class="valor" id="valor_ganancia">$0</div>
                            <div class="detalle">Venta neta menos precio de compra histórico</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-exito">
                            <div class="titulo">Margen bruto</div>
                            <div class="valor" id="valor_margen">0%</div>
                            <div class="detalle">Calculado sobre ventas con costo conocido</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador">
                            <div class="titulo">Documentos</div>
                            <div class="valor" id="valor_documentos">0</div>
                            <div class="detalle" id="detalle_documentos">0 facturas / 0 boletas</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador">
                            <div class="titulo">Unidades vendidas</div>
                            <div class="valor" id="valor_unidades">0</div>
                            <div class="detalle">Suma de cantidades de los detalles</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-advertencia">
                            <div class="titulo">Ventas sin costo (Repuestos express)</div>
                            <div class="valor" id="valor_sin_costo">$0</div>
                            <div class="detalle" id="detalle_sin_costo">0 líneas sin costo histórico</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-getnet">
                            <div class="titulo">Cobros Getnet</div>
                            <div class="valor" id="valor_getnet">$0</div>
                            <div class="detalle" id="detalle_getnet">0% de los cobros asociados al filtro</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-getnet">
                            <div class="titulo">Operaciones Getnet</div>
                            <div class="valor" id="valor_getnet_operaciones">0</div>
                            <div class="detalle" id="detalle_getnet_operaciones">0 crédito / 0 débito</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-getnet">
                            <div class="titulo">Comisión estimada Getnet</div>
                            <div class="valor" id="valor_comision_getnet">$0</div>
                            <div class="detalle" id="detalle_comision_getnet">Crédito 0% / Débito 0%</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-exito">
                            <div class="titulo">Utilidad después de comisión Getnet</div>
                            <div class="valor" id="valor_ganancia_getnet">$0</div>
                            <div class="detalle" id="detalle_ganancia_getnet">Margen 0%</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="indicador indicador-nota">
                            <div class="titulo">Notas de crédito</div>
                            <div class="valor" id="valor_notas_credito">0</div>
                            <div class="detalle" id="detalle_notas_credito">$0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs tabs-rentabilidad" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab_vendedores" role="tab">Vendedores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab_proveedores" role="tab">Proveedores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab_productos" role="tab">Productos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab_notas_credito" role="tab">Notas de crédito</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab_detalle" role="tab">Detalle de ventas</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab_vendedores" role="tabpanel">
                <div class="tabla-card">
                    <div class="card-header">Rentabilidad por vendedor</div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="texto-derecha">Unidades</th>
                                    <th class="texto-derecha">Venta neta</th>
                                    <th class="texto-derecha">Costo compra</th>
                                    <th class="texto-derecha">Ganancia</th>
                                    <th class="texto-derecha">Margen</th>
                                    <th class="texto-derecha">Venta sin costo</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_vendedores"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab_proveedores" role="tabpanel">
                <div class="tabla-card">
                    <div class="card-header">Rentabilidad por proveedor</div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th class="texto-derecha">Unidades</th>
                                    <th class="texto-derecha">Venta neta</th>
                                    <th class="texto-derecha">Costo compra</th>
                                    <th class="texto-derecha">Ganancia</th>
                                    <th class="texto-derecha">Margen</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_proveedores"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab_productos" role="tabpanel">
                <div class="tabla-card">
                    <div class="card-header">Productos más vendidos y su rentabilidad</div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Proveedor</th>
                                    <th class="texto-derecha">Cantidad</th>
                                    <th class="texto-derecha">Venta bruta</th>
                                    <th class="texto-derecha">Venta neta</th>
                                    <th class="texto-derecha">Costo compra</th>
                                    <th class="texto-derecha">Ganancia</th>
                                    <th class="texto-derecha">Margen</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_productos"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab_notas_credito" role="tabpanel">
                <div class="tabla-card">
                    <div class="card-header">
                        Incidencia por vendedor del documento original
                    </div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="texto-derecha">Documentos vendidos</th>
                                    <th class="texto-derecha">Cantidad NC</th>
                                    <th class="texto-derecha">Monto NC</th>
                                    <th class="texto-derecha">Tasa NC</th>
                                    <th class="texto-derecha">Promedio NC</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_notas_vendedor"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tabla-card">
                    <div class="card-header">
                        Usuario que emitió la nota de crédito
                    </div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Usuario emisor</th>
                                    <th class="texto-derecha">Cantidad NC</th>
                                    <th class="texto-derecha">Monto NC</th>
                                    <th class="texto-derecha">Promedio NC</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_notas_emisor"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tabla-card">
                    <div class="card-header">
                        Detalle de notas de crédito
                    </div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Nota de crédito</th>
                                    <th>Documento original</th>
                                    <th>Usuario emisor</th>
                                    <th>Vendedor original</th>
                                    <th class="texto-derecha">Monto</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_notas_detalle"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab_detalle" role="tabpanel">
                <div class="tabla-card">
                    <div class="card-header">Detalle de productos vendidos</div>
                    <div class="card-body tabla-responsive">
                        <table class="table table-sm table-striped table-bordered tabla-rentabilidad">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Documento</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Vendedor</th>
                                    <th>Proveedor</th>
                                    <th class="texto-derecha">Cantidad</th>
                                    <th class="texto-derecha">Venta bruta</th>
                                    <th class="texto-derecha">Venta neta</th>
                                    <th class="texto-derecha">Precio compra unit.</th>
                                    <th class="texto-derecha">Costo compra total</th>
                                    <th class="texto-derecha">Flete registrado</th>
                                    <th>Factura compra</th>
                                    <th>Fecha compra</th>
                                    <th class="texto-derecha">Ganancia</th>
                                    <th class="texto-derecha">Margen</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_detalle"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <div class="modal fade" id="modalInfoRepuesto" tabindex="-1" role="dialog" aria-labelledby="tituloInfoRepuestoRentabilidad" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content bg-white text-dark">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloInfoRepuestoRentabilidad">Información del Repuesto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modalBodyInfoRepuesto">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
@endsection
