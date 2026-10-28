@extends('plantillas.app')
@section('titulo','Ingreso por Compras')
@section('javascript')
  <script type="text/javascript">
    var clonando=false;
    var existe_factura="NO";
    var ampliar_aplicacionez=false;
    var cantidad_repuestos_factura=0;

   let productosImportadosXml = [];
    let cabeceraImportacionXml = null;

    let localesImportacion = [
        @foreach($locales as $local)
            {
                id: "{{ $local->id }}",
                nombre: @json($local->local_nombre ?? $local->nombre ?? $local->descripcion ?? ('Local '.$local->id))
            },
        @endforeach
    ];

    function importarProductosFactura() {
        let form = document.getElementById('form_importar_productos');
        let formData = new FormData(form);

        // Como este modal ahora es solo para XML, forzamos el valor.
        // Esto evita el error de validación: tipo_archivo requerido / inválido.
        $('#tipo_archivo').val('xml');
        formData.set('tipo_archivo', 'xml');

        if (!$('#archivo_importacion').val()) {
            return Vue.swal('Atención', 'Debe adjuntar archivo XML', 'warning');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('factuprodu.importar_productos') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            beforeSend: function () {
                Vue.swal({
                    title: 'Leyendo XML...',
                    text: 'Espere por favor',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
            },

            success: function (resp) {
                Vue.swal.close();

                // Protección adicional: si el backend respondiera HTTP 200 para una factura
                // duplicada, no se deben cargar la cabecera ni los productos en pantalla.
                let facturaDuplicada = resp.codigo === 'FACTURA_DUPLICADA'
                    || (resp.cabecera && resp.cabecera.factura_existe);

                if (facturaDuplicada) {
                    let mensajeDuplicado = resp.mensaje
                        || (resp.cabecera ? resp.cabecera.factura_existente_mensaje : null)
                        || 'Esta factura ya fue ingresada anteriormente.';

                    limpiarImportacionXml();

                    return Vue.swal({
                        title: 'Factura ya importada',
                        text: mensajeDuplicado,
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                }

                if (resp.estado !== 'OK') {
                    limpiarImportacionXml();

                    return Vue.swal({
                        title: 'Atención',
                        text: resp.mensaje || 'No se pudo leer el XML',
                        icon: 'warning'
                    });
                }

                cabeceraImportacionXml = resp.cabecera || null;

                completarCabeceraImportacion(resp);

                productosImportadosXml = [
                    ...(resp.existentes || []),
                    ...(resp.nuevos || [])
                ];

                renderResumenImportacion(resp);
                recalcularResumenImportacion();
            },

            error: function (xhr) {
                Vue.swal.close();

                let respuesta = xhr.responseJSON || {};
                let mensaje = 'Error al importar XML';

                // El backend devuelve HTTP 422 y este código cuando la factura ya existe.
                if (xhr.status === 422 && respuesta.codigo === 'FACTURA_DUPLICADA') {
                    let mensajeDuplicado = respuesta.mensaje
                        || 'Esta factura ya fue ingresada anteriormente.';

                    limpiarImportacionXml();

                    return Vue.swal({
                        title: 'Factura ya importada',
                        html: `
                            <div class="text-left">
                                <p>${escapeHtml(mensajeDuplicado)}</p>
                                ${respuesta.numero_factura
                                    ? `<p class="mb-1"><b>Factura:</b> ${escapeHtml(respuesta.numero_factura)}</p>`
                                    : ''}
                                ${respuesta.proveedor_nombre
                                    ? `<p class="mb-0"><b>Proveedor:</b> ${escapeHtml(respuesta.proveedor_nombre)}</p>`
                                    : ''}
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                }

                // Errores de validación normales de Laravel.
                if (xhr.status === 422 && respuesta.errors) {
                    mensaje = Object.values(respuesta.errors)
                        .flat()
                        .map(function (error) {
                            return escapeHtml(error);
                        })
                        .join('<br>');
                } else if (respuesta.mensaje) {
                    mensaje = escapeHtml(respuesta.mensaje);
                } else if (respuesta.message) {
                    mensaje = escapeHtml(respuesta.message);
                } else if (xhr.responseText) {
                    mensaje = escapeHtml(xhr.responseText);
                }

                Vue.swal({
                    title: 'Error',
                    html: mensaje,
                    icon: 'error'
                });
            }
        });
    }

    function completarCabeceraImportacion(resp) {
        if (resp.id_proveedor) {
            $('#import_id_proveedor').val(resp.id_proveedor).trigger('change');
        }

        if (resp.numero_factura) {
            $('#import_numero_factura').val(resp.numero_factura);
        }

        if (resp.fecha_factura) {
            $('#import_fecha_factura').val(resp.fecha_factura);
        }

        if (resp.fecha_vencimiento) {
            $('#import_fecha_vencimiento').val(resp.fecha_vencimiento);
        }
    }

    function renderResumenImportacion(resp) {
        let totalNeto = numeroImportacion(resp.total_neto || (resp.cabecera ? resp.cabecera.neto : 0));
        let totalIva = numeroImportacion(resp.total_iva || (resp.cabecera ? resp.cabecera.iva : 0));
        let totalFactura = numeroImportacion(resp.total_factura || (resp.cabecera ? resp.cabecera.total : 0));
        let totalFlete = numeroImportacion(resp.total_flete || 0);

        let proveedorEncontrado = resp.proveedor_encontrado || (resp.cabecera && resp.cabecera.proveedor_encontrado);
        let proveedorNombre = resp.proveedor_nombre || (resp.cabecera ? resp.cabecera.proveedor_nombre : '');
        let proveedorRut = (resp.cabecera && resp.cabecera.rut_emisor) ? resp.cabecera.rut_emisor : '';
        let proveedorId = resp.id_proveedor || (resp.cabecera ? resp.cabecera.proveedor_id : '');
        let numeroFactura = resp.numero_factura || (resp.cabecera ? resp.cabecera.folio : '');
        let fechaFactura = resp.fecha_factura || (resp.cabecera ? resp.cabecera.fecha_emision : '');
        let fechaVencimiento = resp.fecha_vencimiento || (resp.cabecera ? resp.cabecera.fecha_vencimiento : '');

        let alertaProveedor = proveedorEncontrado
            ? `<div class="alert alert-success mb-3">
                    <b>Proveedor encontrado:</b> ${escapeHtml(proveedorNombre)}
                    <br><small>RUT XML: ${escapeHtml(proveedorRut)} | ID proveedor: ${escapeHtml(proveedorId)}</small>
               </div>`
            : `<div class="alert alert-warning mb-3">
                    <b>Proveedor no encontrado en el sistema.</b>
                    <br><small>RUT emisor XML: ${escapeHtml(proveedorRut || 'No informado')}</small>
                    <br><small>Revise que el RUT del proveedor exista en proveedores.empresa_codigo.</small>
               </div>`;

        let html = `
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <b>Resumen de importación</b>
                </div>

                <div class="card-body">
                    ${alertaProveedor}

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Factura XML</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(numeroFactura || '')}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Fecha emisión</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(fechaFactura || '')}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Fecha vencimiento</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(fechaVencimiento || '')}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>RUT emisor</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(proveedorRut || '')}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Total XML</label>
                            <input type="text" class="form-control form-control-sm" value="${resp.total_productos || 0}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Existentes</label>
                            <input type="text" class="form-control form-control-sm" value="${resp.cantidad_existentes || 0}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Nuevos</label>
                            <input type="text" class="form-control form-control-sm" value="${resp.cantidad_nuevos || 0}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Total Neto XML</label>
                            <input type="text" id="import_total_neto" class="form-control form-control-sm" value="${totalNeto}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Neto</label>
                            <input type="text" class="form-control form-control-sm" value="${formatoCLP(totalNeto)}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>IVA</label>
                            <input type="text" class="form-control form-control-sm" value="${formatoCLP(totalIva)}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Total factura</label>
                            <input type="text" class="form-control form-control-sm" value="${formatoCLP(totalFactura)}" readonly>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2 align-items-start">
                        <div class="col-md">
                            <label>Total productos factura</label>
                            <input type="number" id="import_factura_productos" class="form-control form-control-sm" value="${totalNeto}" min="0" step="1">
                        </div>

                        <div class="col-md">
                            <label>Total flete factura</label>
                            <input type="number" id="import_factura_flete" class="form-control form-control-sm" value="${totalFlete}" min="0" step="1">
                        </div>

                        <div class="col-md">
                            <label class="d-block">Flete pagado</label>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="import_flete_pagado"
                                       value="1">
                                <label class="custom-control-label" for="import_flete_pagado">
                                    Sí
                                </label>
                            </div>
                            <small class="text-muted">Solo para fines estadísticos.</small>
                        </div>

                        <div class="col-md">
                            <label>
                                % Flete (Costo Logístico)
                                <i class="fa fa-question-circle text-info"
                                   data-toggle="tooltip"
                                   title="Puede utilizar el porcentaje real del flete o un porcentaje de costo logístico interno."></i>
                            </label>
                            <input type="number" id="import_porcentaje_flete" class="form-control form-control-sm" value="0" min="0" step="0.01">
                            <small id="import_alerta_flete" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md">
                            <label>% Utilidad</label>
                            <input type="number" id="import_porcentaje_utilidad" class="form-control form-control-sm" value="30" min="0" max="99" step="1">
                        </div>

                        <div class="col-md">
                            <label>% Descuento</label>
                            <input type="number" id="import_porcentaje_descuento" class="form-control form-control-sm" value="0" min="0" max="100" step="0.01">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="text-muted mb-1">
                                <small>
                                    Total productos factura normalmente corresponde al neto de productos. Total flete factura corresponde solo al valor del flete.
                                    El % Flete puede ser flete real o costo logístico adicional. El % Descuento se aplica al costo unitario antes del flete.
                                    Se guarda el flete por unidad asignado aquí, sin utilizar la utilidad de la familia. El listado multiplica ese valor por la cantidad una sola vez.
                                </small>
                            </p>
                            <p class="text-muted mb-0">
                                <small>
                                    Marque <b>Es caja</b> solo en los productos que realmente vienen en caja.
                                    Si es caja: Cant. Stock = Cant. XML × Unid/Caja y el Precio XML se divide por Unid/Caja.
                                    Si no es caja: Cant. Stock = Cant. XML y el Precio XML queda como costo unitario.
                                </small>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label>Local destino general</label>
                            <select id="import_id_local_general" class="form-control form-control-sm" required>
                                <option value="">Seleccione local</option>
                                ${opcionesLocalesGeneralesImportacion('')}
                            </select>
                            <small class="text-muted">Este local es obligatorio y se usará para todos los productos que indiquen "Usar local general".</small>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-light border mb-0 py-2">
                                <small>
                                    Puede seleccionar un local general para toda la importación y cambiar el local solo en las filas que necesite enviar a otra sucursal.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto; font-size: 13px;">
                        <table class="table table-sm table-bordered table-striped" id="tabla_resumen_importacion">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Estado</th>
                                    <th>Código</th>
                                    <th>Producto XML</th>
                                    <th>Cant. XML</th>
                                    <th>Es caja</th>
                                    <th>Unid/Caja</th>
                                    <th>Cant. Stock</th>
                                    <th>Local destino</th>
                                    <th>Precio XML</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Desc.</th>
                                    <th>Flete Und.</th>
                                    <th>Flete Total</th>
                                    <th>Costo + Flete</th>
                                    <th>Venta Neto</th>
                                    <th>Venta + IVA</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="confirmarImportacionXml()" ${proveedorEncontrado ? '' : 'disabled'}>
                            Confirmar importación
                        </button>
                    </div>

                </div>
            </div>
        `;

        $('#resultado_importacion').html(html);

        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }

        $('#import_factura_flete, #import_factura_productos, #import_porcentaje_utilidad, #import_porcentaje_descuento').off('input').on('input', function () {
            recalcularResumenImportacion(false);
        });

        $('#import_porcentaje_flete').off('input').on('input', function () {
            recalcularResumenImportacion(true);
        });

        $('#import_id_local_general').off('change').on('change', function () {
            let idLocalGeneral = $(this).val();
            productosImportadosXml.forEach(function (item) {
                if (!item.id_local || item.id_local === '') {
                    item.id_local_general_ref = idLocalGeneral;
                }
            });
        });
    }

    function recalcularResumenImportacion(porcentajeManual = false) {
        guardarValoresEditadosImportacion();

        let facturaProductos = numeroImportacion($('#import_factura_productos').val());
        let facturaFlete = numeroImportacion($('#import_factura_flete').val());
        let porcentajeUtilidad = numeroImportacion($('#import_porcentaje_utilidad').val());
        let porcentajeDescuento = numeroImportacion($('#import_porcentaje_descuento').val());

        if (facturaProductos > 0 && facturaFlete > facturaProductos) {
            let productosOriginal = facturaProductos;
            facturaProductos = facturaFlete;
            facturaFlete = productosOriginal;

            $('#import_factura_productos').val(facturaProductos);
            $('#import_factura_flete').val(facturaFlete);
            $('#import_alerta_flete')
                .removeClass('d-none')
                .text('Se detectaron valores invertidos y fueron corregidos automáticamente.');
        } else {
            $('#import_alerta_flete').addClass('d-none').text('');
        }

        let porcentajeFlete = 0;

        if (porcentajeManual) {
            porcentajeFlete = numeroImportacion($('#import_porcentaje_flete').val()) / 100;
            facturaFlete = facturaProductos > 0 ? facturaProductos * porcentajeFlete : 0;
            $('#import_factura_flete').val(Math.round(facturaFlete));
        } else {
            porcentajeFlete = facturaProductos > 0 ? facturaFlete / facturaProductos : 0;
            $('#import_porcentaje_flete').val((porcentajeFlete * 100).toFixed(2));
        }

        let html = '';

        productosImportadosXml.forEach((item, index) => {
            if (item.cantidad_xml === undefined || item.cantidad_xml === null || item.cantidad_xml === '') {
                item.cantidad_xml = item.cantidad || 0;
            }

            if (item.unidades_por_caja === undefined || item.unidades_por_caja === null || item.unidades_por_caja === '') {
                item.unidades_por_caja = 1;
            }

            if (item.es_caja === undefined || item.es_caja === null || item.es_caja === '') {
                item.es_caja = 0;
            }

            let cantidadXml = numeroImportacion(item.cantidad_xml);
            let esCaja = item.es_caja == 1 || item.es_caja === true || item.es_caja === '1';
            let unidadesCaja = numeroImportacion(item.unidades_por_caja);

            if (unidadesCaja <= 0) {
                unidadesCaja = 1;
            }

            let cantidad = esCaja ? (cantidadXml * unidadesCaja) : cantidadXml;
            let precioXml = numeroImportacion(item.precio_xml || item.precio || item.precio_unitario || item.costo);
            // El costo base ya incluye el descuento de la linea XML.
            let precioCompraXml = item.precio_compra_xml !== undefined && item.precio_compra_xml !== null
                ? numeroImportacion(item.precio_compra_xml) : precioXml;
            let precioUnitario = esCaja && unidadesCaja > 0 ? (precioCompraXml / unidadesCaja) : precioCompraXml;
            let descuentoUnitario = precioUnitario * (porcentajeDescuento / 100);
            let costo = precioUnitario - descuentoUnitario;

            let fleteUnd = costo * porcentajeFlete;
            let fleteTotal = fleteUnd * cantidad;
            let costoConFlete = costo + fleteUnd;

            let divisorMargen = 1 - (porcentajeUtilidad / 100);

            let precioVentaNetoCalculado = divisorMargen > 0
                ? costoConFlete / divisorMargen
                : 0;

            let precioVentaNetoSugerido = precioVentaNetoCalculado;
            let precioVentaIvaSugerido = precioVentaNetoCalculado * 1.19;

            item.precio_venta_neto_sugerido = Math.round(precioVentaNetoSugerido);
            item.precio_venta_iva_sugerido = Math.round(precioVentaIvaSugerido);

            let precioVentaNeto = (item.precio_venta_neto_editado && item.precio_venta_neto_manual !== undefined && item.precio_venta_neto_manual !== '')
                ? limpiarPrecioImportacion(item.precio_venta_neto_manual)
                : precioVentaNetoSugerido;

            let precioVentaIva = (item.precio_venta_editado && item.precio_venta_manual !== undefined && item.precio_venta_manual !== '')
                ? limpiarPrecioImportacion(item.precio_venta_manual)
                : precioVentaNeto * 1.19;

            // Marca visual para precios modificados manualmente por el usuario.
            let precioFueEditado = item.precio_venta_neto_editado || item.precio_venta_editado;
            let precioBajoSugerido = precioFueEditado && Math.round(precioVentaIva) < Math.round(precioVentaIvaSugerido);
            let claseFilaPrecio = precioBajoSugerido
                ? 'fila-precio-bajo'
                : (precioFueEditado ? 'fila-precio-modificado' : '');

            let estado = item.id_repuesto ? 'EXISTENTE' : 'NUEVO';
            let badge = item.id_repuesto
                ? '<span class="badge badge-success">Existente</span>'
                : '<span class="badge badge-warning">Nuevo</span>';

            let codigo = escapeHtml(item.codigo || '');
            let nombre = escapeHtml(item.nombre_xml || item.nombre || '');

            html += `
                <tr class="${claseFilaPrecio}" data-precio-iva-sugerido="${Math.round(precioVentaIvaSugerido)}">
                    <td>${badge}</td>
                    <td>
                        ${codigo}
                        <input type="hidden" name="productos[${index}][codigo]" value="${codigo}">
                        <input type="hidden" name="productos[${index}][estado]" value="${estado}">
                        <input type="hidden" name="productos[${index}][id_repuesto]" value="${item.id_repuesto || ''}">
                    </td>
                    <td>
                        ${nombre}
                        <input type="hidden" name="productos[${index}][nombre]" value="${nombre}">
                    </td>
                    <td style="width:90px;">
                        <input type="number"
                               class="form-control form-control-sm cantidad_xml_import text-right"
                               name="productos[${index}][cantidad_xml]"
                               value="${cantidadXml}"
                               min="0"
                               step="1"
                               data-index="${index}">
                    </td>
                    <td class="text-center" style="width:80px;">
                        <input type="checkbox"
                               class="es_caja_import"
                               data-index="${index}"
                               ${esCaja ? 'checked' : ''}>
                        <input type="hidden"
                               class="es_caja_hidden"
                               name="productos[${index}][es_caja]"
                               value="${esCaja ? 1 : 0}">
                    </td>
                    <td style="width:90px;">
                        <input type="number"
                               class="form-control form-control-sm unidades_caja_import text-right"
                               name="productos[${index}][unidades_por_caja]"
                               value="${unidadesCaja}"
                               min="1"
                               step="1"
                               data-index="${index}">
                    </td>
                    <td>
                        <span class="cantidad_stock_text">${cantidad}</span>
                        <input type="hidden" class="cantidad_import" name="productos[${index}][cantidad]" value="${cantidad}" data-index="${index}">
                    </td>
                    <td style="min-width:170px;">
                        <select class="form-control form-control-sm local_import"
                                name="productos[${index}][id_local]"
                                data-index="${index}">
                            ${opcionesLocalesImportacion(item.id_local || '')}
                        </select>
                    </td>
                    <td>
                        ${formatoCLP(precioXml)}
                        <input type="hidden" name="productos[${index}][precio_xml]" value="${precioXml}">
                    </td>
                    <td>
                        ${formatoCLP(precioUnitario)}
                        <input type="hidden" name="productos[${index}][precio_unitario]" value="${precioUnitario}">
                    </td>
                    <td>
                        ${formatoCLP(costo)}
                        <input type="hidden" name="productos[${index}][porcentaje_descuento]" value="${porcentajeDescuento}">
                        <input type="hidden" name="productos[${index}][descuento_unitario]" value="${Math.round(descuentoUnitario)}">
                        <input type="hidden" name="productos[${index}][costo]" value="${costo}">
                    </td>
                    <td>
                        ${formatoCLP(fleteUnd)}
                        <input type="hidden" name="productos[${index}][flete_unitario]" value="${fleteUnd.toFixed(2)}">
                    </td>
                    <td>
                        <span class="flete_total_text">${formatoCLP(fleteTotal)}</span>
                        <input type="hidden" class="flete_total_import" name="productos[${index}][flete_total]" value="${Math.round(fleteTotal)}">
                    </td>
                    <td>
                        ${formatoCLP(costoConFlete)}
                        <input type="hidden" name="productos[${index}][costo_con_flete]" value="${Math.round(costoConFlete)}">
                    </td>
                    <td style="width:120px;">
                        <input type="text"
                              class="form-control form-control-sm precio_venta_neto text-right"
                              name="productos[${index}][precio_venta_neto]"
                              inputmode="numeric"
                              autocomplete="off"
                              value="${formatoMilesImportacion(Math.round(precioVentaNeto))}">
                    </td>

                    <td style="width:120px;">
                        <input type="text"
                              class="form-control form-control-sm precio_venta_iva text-right"
                              name="productos[${index}][precio_venta]"
                              inputmode="numeric"
                              autocomplete="off"
                              value="${formatoMilesImportacion(Math.round(precioVentaIva))}">
                    </td>
                </tr>
            `;
        });

        $('#tabla_resumen_importacion tbody').html(html);

        $('.cantidad_xml_import, .unidades_caja_import').off('input').on('input', function () {
            actualizarCantidadImportacion($(this));
        });

        $('.es_caja_import').off('change').on('change', function () {
            actualizarCantidadImportacion($(this));
        });

        $('.local_import').off('change').on('change', function () {
            let index = $(this).data('index');
            if (index !== undefined && productosImportadosXml[index]) {
                productosImportadosXml[index].id_local = $(this).val();
            }
        });

        $('.precio_venta_neto').off('input').on('input', function () {
            recalcularPrecioVentaIvaDesdeNeto($(this));
        });

        $('.precio_venta_iva').off('input').on('input', function () {
            recalcularPrecioVentaNetoDesdeIva($(this));
        });
    }

    function guardarValoresEditadosImportacion() {
        $('#tabla_resumen_importacion tbody tr').each(function () {
            let row = $(this);
            let index = row.find('.cantidad_import').data('index');

            if (index === undefined || !productosImportadosXml[index]) {
                return;
            }

            let esCaja = row.find('.es_caja_import').is(':checked') ? 1 : 0;

            productosImportadosXml[index].cantidad_xml = numeroImportacion(row.find('.cantidad_xml_import').val());
            productosImportadosXml[index].es_caja = esCaja;
            productosImportadosXml[index].unidades_por_caja = numeroImportacion(row.find('.unidades_caja_import').val()) || 1;
            productosImportadosXml[index].cantidad = numeroImportacion(row.find('.cantidad_import').val());
            productosImportadosXml[index].id_local = row.find('.local_import').val();

            if (productosImportadosXml[index].precio_venta_neto_editado) {
                productosImportadosXml[index].precio_venta_neto_manual = limpiarPrecioImportacion(row.find('.precio_venta_neto').val());
            }

            if (productosImportadosXml[index].precio_venta_editado) {
                productosImportadosXml[index].precio_venta_manual = limpiarPrecioImportacion(row.find('.precio_venta_iva').val());
            }
        });
    }

    function actualizarCantidadImportacion(inputCantidad) {
        let fila = inputCantidad.closest('tr');
        let index = inputCantidad.data('index');

        if (productosImportadosXml[index]) {
            let esCaja = fila.find('.es_caja_import').is(':checked') ? 1 : 0;

            productosImportadosXml[index].cantidad_xml = numeroImportacion(fila.find('.cantidad_xml_import').val());
            productosImportadosXml[index].es_caja = esCaja;
            productosImportadosXml[index].unidades_por_caja = numeroImportacion(fila.find('.unidades_caja_import').val()) || 1;

            productosImportadosXml[index].cantidad = esCaja
                ? productosImportadosXml[index].cantidad_xml * productosImportadosXml[index].unidades_por_caja
                : productosImportadosXml[index].cantidad_xml;
        }

        recalcularResumenImportacion(true);
    }

    function recalcularPrecioVentaIvaDesdeNeto(inputNeto) {
        let fila = inputNeto.closest('tr');
        formatearInputPrecioImportacion(inputNeto);

        let ventaNeto = limpiarPrecioImportacion(inputNeto.val());
        let ventaIva = Math.round(ventaNeto * 1.19);
        let index = fila.find('.cantidad_import').data('index');

        fila.find('.precio_venta_iva').val(formatoMilesImportacion(ventaIva));
        marcarPrecioModificadoImportacion(fila);

        if (index !== undefined && productosImportadosXml[index]) {
            productosImportadosXml[index].precio_venta_neto_editado = true;
            productosImportadosXml[index].precio_venta_editado = true;
            productosImportadosXml[index].precio_venta_neto_manual = ventaNeto;
            productosImportadosXml[index].precio_venta_manual = ventaIva;
        }
    }

    function recalcularPrecioVentaNetoDesdeIva(inputIva) {
        let fila = inputIva.closest('tr');
        formatearInputPrecioImportacion(inputIva);

        let ventaIva = limpiarPrecioImportacion(inputIva.val());
        let ventaNeto = Math.round(ventaIva / 1.19);
        let index = fila.find('.cantidad_import').data('index');

        fila.find('.precio_venta_neto').val(formatoMilesImportacion(ventaNeto));
        marcarPrecioModificadoImportacion(fila);

        if (index !== undefined && productosImportadosXml[index]) {
            productosImportadosXml[index].precio_venta_neto_editado = true;
            productosImportadosXml[index].precio_venta_editado = true;
            productosImportadosXml[index].precio_venta_neto_manual = ventaNeto;
            productosImportadosXml[index].precio_venta_manual = ventaIva;
        }
    }

    function marcarPrecioModificadoImportacion(fila) {
        let precioIvaActual = limpiarPrecioImportacion(fila.find('.precio_venta_iva').val());
        let precioIvaSugerido = limpiarPrecioImportacion(fila.attr('data-precio-iva-sugerido'));

        fila.removeClass('fila-precio-modificado fila-precio-bajo');

        if (precioIvaSugerido > 0 && precioIvaActual > 0 && precioIvaActual < precioIvaSugerido) {
            fila.addClass('fila-precio-bajo');
        } else {
            fila.addClass('fila-precio-modificado');
        }
    }

    function numeroImportacion(valor) {
        if (valor === null || valor === undefined || valor === '') return 0;
        let str = String(valor).trim();

        // Soporta formato chileno: 1.492.470,50
        if (str.indexOf(',') !== -1) {
            str = str.replace(/\./g, '').replace(',', '.');
        } else {
            // Si viene 1.492.470, lo interpreta como miles, no como decimal.
            let partes = str.split('.');
            if (partes.length > 2) {
                str = str.replace(/\./g, '');
            }
        }

        return parseFloat(str) || 0;
    }

    function escapeHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatoCLP(valor) {
        return new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP',
            maximumFractionDigits: 0
        }).format(valor || 0);
    }

    function limpiarPrecioImportacion(valor) {
        if (valor === null || valor === undefined || valor === '') return 0;
        let limpio = String(valor).replace(/[^0-9]/g, '');
        return parseInt(limpio || '0', 10);
    }

    function formatoMilesImportacion(valor) {
        let numero = limpiarPrecioImportacion(valor);
        if (numero <= 0) return '';

        return new Intl.NumberFormat('es-CL', {
            maximumFractionDigits: 0
        }).format(numero);
    }

    function formatearInputPrecioImportacion(input) {
        let valorLimpio = limpiarPrecioImportacion(input.val());
        input.val(valorLimpio > 0 ? formatoMilesImportacion(valorLimpio) : '');
    }

    function opcionesLocalesImportacion(idSeleccionado = '') {
        let opciones = '<option value="">Usar local general</option>';

        localesImportacion.forEach(function (local) {
            let selected = String(local.id) === String(idSeleccionado) ? 'selected' : '';
            opciones += `<option value="${escapeHtml(local.id)}" ${selected}>${escapeHtml(local.nombre)}</option>`;
        });

        return opciones;
    }

    function opcionesLocalesGeneralesImportacion(idSeleccionado = '') {
        let opciones = '';

        localesImportacion.forEach(function (local) {
            let selected = String(local.id) === String(idSeleccionado) ? 'selected' : '';
            opciones += `<option value="${escapeHtml(local.id)}" ${selected}>${escapeHtml(local.nombre)}</option>`;
        });

        return opciones;
    }

    function confirmarImportacionXml() {
        let productos = [];

        $('#tabla_resumen_importacion tbody tr').each(function () {
            let row = $(this);

            productos.push({
                codigo: row.find('input[name*="[codigo]"]').val(),
                estado: row.find('input[name*="[estado]"]').val(),
                id_repuesto: row.find('input[name*="[id_repuesto]"]').val(),
                nombre: row.find('input[name*="[nombre]"]').val(),
                cantidad_xml: row.find('input[name*="[cantidad_xml]"]').val(),
                es_caja: row.find('.es_caja_import').is(':checked') ? 1 : 0,
                unidades_por_caja: row.find('input[name*="[unidades_por_caja]"]').val(),
                cantidad: row.find('input[name*="[cantidad]"]').val(),
                id_local: row.find('select[name*="[id_local]"]').val(),
                precio_xml: row.find('input[name*="[precio_xml]"]').val(),
                precio_unitario: row.find('input[name*="[precio_unitario]"]').val(),
                porcentaje_descuento: row.find('input[name*="[porcentaje_descuento]"]').val(),
                descuento_unitario: row.find('input[name*="[descuento_unitario]"]').val(),
                costo: row.find('input[name*="[costo]"]').val(),
                flete_unitario: row.find('input[name*="[flete_unitario]"]').val(),
                flete_total: row.find('input[name*="[flete_total]"]').val(),
                costo_con_flete: row.find('input[name*="[costo_con_flete]"]').val(),
                precio_venta_neto: limpiarPrecioImportacion(row.find('input[name*="[precio_venta_neto]"]').val()),
                precio_venta: limpiarPrecioImportacion(row.find('input[name*="[precio_venta]"]').val())
            });
        });

        let productosGuardables = productos.filter(function (producto) {
            return producto.id_repuesto && numeroImportacion(producto.cantidad) > 0;
        });

        if (productosGuardables.length === 0) {
            return Vue.swal({
                title: 'No hay productos para guardar',
                text: 'Debe existir al menos un producto asociado a un repuesto del sistema y con cantidad mayor a cero. La cabecera no será guardada.',
                icon: 'warning',
                confirmButtonText: 'Revisar productos'
            });
        }

        let idLocalGeneral = $('#import_id_local_general').val();

        if (!idLocalGeneral) {
            return Vue.swal('Atención', 'Debe seleccionar un local destino general para la importación.', 'warning');
        }

        Vue.swal({
            title: 'Confirmar importación',
            text: 'Se guardarán '+productosGuardables.length+' producto(s). Los productos nuevos no asociados serán omitidos. ¿Desea continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, importar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('factuprodu.confirmar_importacion_xml') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),

                    id_proveedor: $('#import_id_proveedor').val(),
                    numero_factura: $('#import_numero_factura').val(),
                    fecha_factura: $('#import_fecha_factura').val(),
                    fecha_vencimiento: $('#import_fecha_vencimiento').val(),

                    factura_subtotal: cabeceraImportacionXml ? cabeceraImportacionXml.neto : 0,
                    factura_iva: cabeceraImportacionXml ? cabeceraImportacionXml.iva : 0,
                    factura_total: cabeceraImportacionXml ? cabeceraImportacionXml.total : 0,

                    factura_productos: $('#import_factura_productos').val(),
                    factura_flete: $('#import_factura_flete').val(),
                    factura_flete_pagado: $('#import_flete_pagado').is(':checked') ? 1 : 0,
                    porcentaje_utilidad: $('#import_porcentaje_utilidad').val(),
                    porcentaje_descuento: $('#import_porcentaje_descuento').val(),
                    id_local_general: $('#import_id_local_general').val(),

                    productos: productos
                },

                beforeSend: function () {
                    Vue.swal({
                        title: 'Guardando importación...',
                        text: 'Espere por favor',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });
                },

                success: function (resp) {
                    console.log('respuesta de confirmación de importación:', resp);
                    Vue.swal.close();

                    if (resp.estado === 'OK') {
                        if (!resp.detalles_guardados || resp.detalles_guardados < 1) {
                            return Vue.swal('Error', 'La operación no confirmó detalles guardados. La factura no fue aceptada como completa.', 'error');
                        }

                        Vue.swal(
                            'Correcto',
                            resp.mensaje+' Detalles verificados en la base de datos: '+resp.detalles_guardados+'.',
                            'success'
                        );
                        limpiarImportacionXml();
                    } else {
                        Vue.swal('Atención', resp.mensaje || 'No se pudo confirmar la importación.', 'warning');
                    }
                },

                error: function (xhr) {
                    Vue.swal.close();

                    let respuesta = xhr.responseJSON || {};
                    let mensaje = respuesta.mensaje
                        || respuesta.message
                        || 'Error al confirmar importación';

                    // Un 422 corresponde a una validación de negocio, por ejemplo,
                    // una factura que otro usuario ya importó antes de confirmar.
                    if (xhr.status === 422) {
                        return Vue.swal({
                            title: respuesta.codigo === 'FACTURA_DUPLICADA'
                                ? 'Factura ya importada'
                                : 'No se puede importar',
                            text: mensaje,
                            icon: 'warning',
                            confirmButtonText: 'Aceptar'
                        });
                    }

                    Vue.swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error'
                    });
                }
            });
        });
    }

    function limpiarImportacionXml() {
        productosImportadosXml = [];
        cabeceraImportacionXml = null;

        $('#form_importar_productos')[0]?.reset();
        $('#tipo_archivo').val('xml');
        $('#import_id_proveedor').val('');
        $('#import_numero_factura').val('');
        $('#import_fecha_factura').val('');
        $('#import_fecha_vencimiento').val('');
        $('#import_id_local_general').val('');
        $('#import_flete_pagado').prop('checked', false);
        $('#resultado_importacion').html('');
    }

    function ir_a_boton_guardaritem(){
        document.getElementById("btnGuardarItem").focus();
    }

    function espere(mensaje)
    {

        Vue.swal({

                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });
    }


    function enter_press(e)
    {

      var keycode = e.keyCode;

      if(keycode=='13')
      {
        buscarRepuesto();
      }

    }


    function ampliar()
    {
        if(ampliar_aplicacionez==false)
        {

            /* este



                Cuando una propiedad contiene un guión, al acceder a ella con JavaScript se utiliza camel case:



                se quita el guión y la siguiente letra se hace mayúscula.



                Así, en CSS es z-index pero en JS es zIndex, igual ocurre con border-color (borderColor),



                grid-column (gridColumn) o grid-row (gridRow).



                FUENTE: https://es.stackoverflow.com/questions/340390/cambiar-el-valor-de-la-propiedad-grid-column-row-de-css-con-javascript



            */



            document.getElementById("zona_similares").style.gridColumn="2/5"; //este
            document.getElementById("zona_OEMs").style.visibility="hidden";
            document.getElementById("zona_FABs").style.visibility="hidden";

            $("#ampliar").html("<<");

        }else{

            document.getElementById("zona_similares").style.gridColumn="2/3"; //este
            document.getElementById("zona_OEMs").style.visibility="visible";
            document.getElementById("zona_FABs").style.visibility="visible";

            $("#ampliar").html(">>");
        }

        ampliar_aplicacionez=!ampliar_aplicacionez;
    }

  function guardar_rec_alt(){
    var idrep=document.getElementById("id_repuesto").value;
    var rectificador = document.getElementById("rectificador").value;
    var alternador = document.getElementById("alternador").value;

    if(idrep.trim() == 0 || idrep == ''){
      Vue.swal({
        icon:'info',
        text:'Primero debe guardar Item ...',
        position:'top-end',
        timer: 3000,
        toast: true,
        showConfirmButton: false
      });
      return false;
    }

    alternador=alternador.replace(/-/g,"");
    alternador=alternador.toUpperCase();

    rectificador = rectificador.replace(/-/g,"");
    rectificador = rectificador.toUpperCase();

    if(rectificador.trim() == 0 || idrep == ''){
      rectificador = "---";
    }

    if(alternador.trim() == 0 || idrep == ''){
      alternador = "---";
    }

    var parametros = {idrep: idrep, rec: rectificador, alt: alternador};
    var url = '/repuesto/guardaRecAlt';

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
      type:'post',
      data: parametros,
      url: url,
      success: function(html){
        
        document.getElementById("rectificador").value = "";
        document.getElementById("alternador").value = "";
        // $('#rectificador').html("");
        // $('#alternador').html("");
        $('#result').empty();
        $('#result').append(html);
      },
      error: function(error){
        Vue.swal({
          icon:'error',
          text: error.responseText
        });
      }
    })
  }

function guardar_motor(){
  var idrep=document.getElementById("id_repuesto").value;
  let motor = document.getElementById("motorpartida").value;
  if(idrep.trim() == 0 || idrep == ''){
    Vue.swal({
      icon:'info',
      text:'Primero debe guardar Item ...',
      position:'top-end',
      timer: 3000,
      toast: true,
      showConfirmButton: false
    });
    return false;
  }

  if(motor.trim() == 0  || motor == ''){
    Vue.swal({
      icon:'info',
      text:'Debe ingresar motor de partida ...',
      position:'top-end',
      timer: 3000,
      toast: true,
      showConfirmButton: false
    });
    return false;
  }

  let data = {
    idrep: idrep,
    motor: motor
  }

  let url = '/repuesto/guardaMotor';
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $.ajax({
    type:'post',
    data: data,
    url: url,
    success: function(html){
      if(html == 'existe'){
        return Vue.swal({
          icon:'info',
          text:'Motor de partida ya existe ...',
          position:'top-end',
          timer: 3000,
          toast: true,
          showConfirmButton: false
        })
      }
      console.log(html);
      document.getElementById("motorpartida").value = "";
      $('#result').empty();
      $('#result').append(html);
    },
    error: function(error){
      Vue.swal({
        icon:'error',
        text: error.responseText
      });
    }
  })
}


    function agregar_ubicacion(){
      let piso = $('#piso_ubicacion').val();
      let bandeja = $('#bandeja_ubicacion').val();
      let estanteria = $('#estanteria_ubicacion').val();
      let pasillo = $('#pasillo_ubicacion').val();

      document.getElementById('ubicacion_repuesto').value = piso + ' - '+bandeja+' - '+estanteria+' - ';
      console.log($('#ubicacion_repuesto').val());
    }

    // FUNCIONES PARA FORMATEAR NÚMEROS CON FORMATO CHILENO (1.234.567,89)
    function formatNumberChile(value) {
      if (value === '' || value === null || value === undefined) return '';
      let str = String(value);

      // Separar parte entera y decimal por la coma
      let hasComma = str.indexOf(',') !== -1;
      let parts    = str.split(',');

      // Limpiar parte entera: solo dígitos
      let intPart = parts[0].replace(/\D/g, '');
      if (intPart.length > 1) intPart = intPart.replace(/^0+/, '') || '0';

      // Agregar separadores de miles
      intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      if (hasComma) {
        // Parte decimal: máximo 2 dígitos, solo números
        let decPart = (parts[1] || '').replace(/\D/g, '').substring(0, 2);
        return intPart + ',' + decPart;
      }
      return intPart;
    }

    // Limpiar número formateado chileno para obtener valor numérico
    function cleanNumberChile(value) {
      if (value === '' || value === null || value === undefined) return 0;
      let str = String(value).trim();
      if (str === '') return 0;
      // Quitar puntos de miles y cambiar coma decimal por punto
      str = str.replace(/\./g, '').replace(',', '.');
      return parseFloat(str) || 0;
    }

    // Aplicar máscara a input mientras se tipea
    function applyMaskChile(input) {
      // Bloquear teclas no permitidas
      input.addEventListener('keydown', function(e) {
        const navKeys = [8, 9, 13, 27, 46, 37, 38, 39, 40, 35, 36];
        if (navKeys.indexOf(e.keyCode) !== -1) return;
        if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].indexOf(e.keyCode) !== -1) return;
        if (!/^[\d,]$/.test(e.key)) { e.preventDefault(); return; }
        // Solo una coma
        if (e.key === ',' && this.value.indexOf(',') !== -1) e.preventDefault();
      });

      // Formatear mientras se escribe (solo miles, sin forzar decimales)
      input.addEventListener('input', function() {
        let pos    = this.selectionStart;
        let oldLen = this.value.length;
        let formatted = formatNumberChile(this.value);
        if (formatted !== this.value) {
          this.value = formatted;
          let diff   = formatted.length - oldLen;
          let newPos = Math.max(0, pos + diff);
          this.setSelectionRange(newPos, newPos);
        }
      });

      // Al salir del campo: completar con 2 decimales
      input.addEventListener('blur', function() {
        if (this.value.trim() === '') return;
        let num = cleanNumberChile(this.value);
        if (!isNaN(num) && num > 0) {
          let p = num.toFixed(2).split('.');
          p[0]  = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          this.value = p.join(',');
        }
      });
    }


    function clonar()
    {
        clonando=true;
        //console.log("clonar() clonando es ");
        //console.log(clonando?"SI":"NO");
        let codigo_interno=document.getElementById("codigo_interno").value;
        if(codigo_interno.trim().length==0)
        {
            Vue.swal({
                text: 'Ingrese código interno a clonar...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });

            return false;

        }

        //verificar q existe codigo_interno
        let url='{{url("/repuesto/buscarcodint")}}'+'/'+codigo_interno;
        $.ajax({
        type:'GET',
        beforeSend: function () {
          espere("Clonando Repuesto... espere");
        },
        url:url,
        success:function(rpta){ //Viene en formato json
            Vue.swal.close();
            let r=JSON.parse(rpta); 
            
            if(r.estado=='ERROR'){
                Vue.swal({
                    title: r.estado,
                    text: r.mensaje,
                    icon: 'error',
                });

            }

            if(r.estado=='OK'){
                elegir_repuesto(r.repuesto[0]);
                if(r.repuesto[0].id_familia == '206'){
                  console.log('es un regulador de voltaje');
                  $('#rec_alt_rep').addClass('d-block');
                  $('#rec_alt_rep').removeClass('d-none');
                  $('#bendix').addClass('d-none');
                  $('#bendix').removeClass('d-block');
                  dame_regulador_voltaje(r.repuesto[0].id);
                }else if(r.repuesto[0].id_familia == '166'){
                  console.log('es un bendix asfa');
                  $('#bendix').removeClass('d-none');
                  $('#bendix').addClass('d-block');
                  
                  $('#rec_alt_rep').addClass('d-none');
                  $('#rec_alt_rep').removeClass('d-block');
                }else{
                  console.log('no es regulador de voltaje');
                  $('#rec_alt_rep').addClass('d-none');
                  $('#rec_alt_rep').removeClass('d-block');
                  $('#bendix').addClass('d-none');
                  $('#bendix').removeClass('d-block');
                }
            }

        },
        error: function(error){
            Vue.swal.close();
          Vue.swal({
            title: 'ERROR',
            text: error.responseText,
            icon: 'error',
            });
        }
    });

    }

    function dame_regulador_voltaje(idrep){
      var url = '/repuesto/damerv/'+idrep;
      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          $('#result').empty();
          $('#result').append(resp);
        },  
        error: function(error){
          console.log(error.responseText);
        }
      })
    }

    function dame_detalle(idrep){
        dame_fotos(idrep);
        dame_aplicaciones(idrep);
        dame_oems(idrep);
        dame_fabs(idrep);
    }

    function dame_fotos(idrep){
        let url='{{url("/repuesto")}}'+'/'+idrep+'/damefotos_modificar';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#fotos_rep').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                    });
                }

        });

    }

    function dame_aplicaciones(idrep){

        let url='{{url("/repuesto")}}'+'/'+idrep+'/damesimilares_modificar';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#similares_rep').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                    });
                }
        });
    }

    function dame_oems(idrep){
        let url='{{url("/repuesto")}}'+'/'+idrep+'/dameoems_modificar';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#oems_rep').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                    });
                }

        });
    }

    function dame_fabs(idrep){
        let url='{{url("/repuesto")}}'+'/'+idrep+'/damefabricantes_modificar';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){



                $('#fabs_rep').html(resp);



            },



            error: function(error){



                Vue.swal({



                    title: 'ERROR',



                    text: error.responseText,



                    icon: 'error',



                    });



                }



        });



    }







    function cargar_fabricantes()



    {



      $('#cboFabricante option').remove();



      var url='{{url("marcarepuestoJSON")}}';



      $.ajax({



        type:'GET',



        beforeSend: function () {



          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Cargando...");



        },



        url:url,



        success:function(marks){ //Viene en formato json



          var marcas=JSON.parse(marks);



          $('#cboFabricante').append('<option value="">Elegir...</option>');



          marcas.forEach(function(marca){



            $('#cboFabricante').append('<option value="'+marca.id+'">'+marca.marcarepuesto.toUpperCase()+'</option>');



          });







          document.getElementById("cboFabricante").selectedIndex=0;



          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Listo...");



        },



        error: function(error){



          $('#mensajes').html(error.responseText);



          Vue.swal({



            title: 'ERROR',



            text: error.responseText,



            icon: 'error',



            });



        }



      });



    }







    function cargar_familia()
    {
      $('#familia option').remove();
      var url='{{url("familiasJSON")}}';
      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Cargando Familias...");
        },
        url:url,
        success:function(familys){ //Viene en formato json
          var familias=JSON.parse(familys);
          $('#familia').append('<option value="">Elija una Familia</option>');
          familias.forEach(function(familia){
            //console.log(item.modelonombre);
            $('#familia').append('<option value="'+familia.id+'">'+familia.nombrefamilia.toUpperCase()+'</option>');
          });







          document.getElementById("familia").selectedIndex=0;



          //$("#mensajes").html("Familias cargadas...");



        },



        error: function(error){



          $('#mensajes').html(error.responseText);



          Vue.swal({



            title: 'ERROR',



            text: error.responseText,



            icon: 'error',



            });



        }







      });



    }







    function ubicarse_en_anios()
    {
      var modelito=document.getElementById("ModeloSim");
      var texto=modelito.options[modelito.selectedIndex].text.trim();
      $("#nombre_modelo").html("<b>Modelo: </b>"+texto);
      var ini="(";
      var fin=")";
      var años=texto.substring(texto.indexOf(ini)+1,texto.indexOf(fin));
      document.getElementById("anios_vehiculo_sim").value=años;
      document.getElementById("anios_vehiculo_sim").focus();
      document.getElementById("anios_vehiculo_sim").select();
    }







    function cargar_marca_repuesto()
    {
      $('#MarcaRepuesto option').remove();
      var url='{{url("marcarepuestoJSON")}}';
      $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#aplicaciones_msje").html("<b>APLICACIONES: Cargando...</b>");
        },
        url:url,
        success:function(marks){ //Viene en formato json
          var marcas=JSON.parse(marks);
          $('#MarcaRepuesto').append('<option value="">Elija una Marca de Repuesto</option>');
          marcas.forEach(function(marca){
            $('#MarcaRepuesto').append('<option value="'+marca.id+'">'+marca.marcarepuesto.toUpperCase()+'</option>');
          });

          document.getElementById("MarcaRepuesto").selectedIndex=0;
          $("#aplicaciones_msje").html("<b>APLICACIONES: Listo...</b>");
        },
        error: function(error){
          $('#mensajes').html(error.responseText);
          Vue.swal({
            title: 'ERROR',
            text: error.responseText,
            icon: 'error',
            });
        }

      });
    }







    function cargar_pais()



    {



      $('#Pais option').remove();



      var url='{{url("paisJSON")}}';



      $.ajax({



        type:'GET',



        beforeSend: function () {



          //$("#mensajes").html("Cargando Familias...");



        },



        url:url,



        success:function(paizes){ //Viene en formato json



          var paises=JSON.parse(paizes);



          $('#Pais').append('<option value="">Elija País de Origen</option>');



          paises.forEach(function(pais){



            $('#Pais').append('<option value="'+pais.id+'">'+pais.nombre_pais+'</option>');



          });







          document.getElementById("Pais").selectedIndex=0;



          //$("#mensajes").html("Familias cargadas...");



        },



        error: function(error){



          $('#mensajes').html(error.responseText);



          Vue.swal({



            title: 'ERROR',



            text: error.responseText,



            icon: 'error',



            });



        }







      });



    }







    function cargar_medidas(){
      var id = document.getElementById('familia').value;
      console.log(id);
      var url = '{{url("medidasJSON")}}'+'/'+id;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          console.log('Enviando ...');
        },
        success: function(resp){
          
          var cont = 0;
          $('#span_medidas').empty();
           // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
           let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar </th>
              <th scope="col"> </th>
              <th scope="col"> </th>
            </tr>
          </thead>
          <tbody>`;
              resp.forEach(e => {
                cont++;
                html+= `
                <tr>
                  <th scope="row">`+e.descripcion+` </th> 
                  <th scope="row"> 
                    <input type="text" class="form-control dato" value="`+e.descripcion+`-" id="medida`+cont+`" disabled />  
                  </th> 
                  <th scope="row"> 
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" onclick="abrirCampoMedidas(`+cont+`)" id="chkbx`+cont+`"> 
                    </div>  
                  </th>
                  <th>
                    <button class="btn btn-danger btn-sm" onclick="eliminar_medida_familia(`+e.id+`)">X</button>
                    </th>
                    <th>
                      <button class="btn btn-info btn-sm" onclick="editar_medida_familia(`+e.id+`)">Editar</button>
                      </th>
                </tr>`;
              })
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas').append(html);
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          let lista_medidas = `
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);

        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }



    function abrirCampoMedidas(value){
      var check = document.getElementById('chkbx'+value);

      var medida = document.getElementById('medida'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }

    function abrirCampoMedidas_pd(value){
      var check = document.getElementById('chkbx_'+value);

      var medida = document.getElementById('medida_'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }

    function abrirCampoMedidas_pd_dos(value){
      var check = document.getElementById('chkbx_dos'+value);

      var medida = document.getElementById('medida_dos'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }


    function agregar_familia()



    {



      $("#agregar-familia-modal").modal("show");



    }







    function agregar_marca_repuesto()
    {
      $("#agregar-marca-repuesto-modal").modal("show");
    }


    function agregar_pais()
    {
      $("#agregar-pais-modal").modal("show");
    }

    function agregar_medida_familia(){
      $("#agregar-medida-familia-modal").modal("show");
    }

    function guardar_familia()
    {
      var url="{{url('familia')}}";
      var nombrefamilia=document.getElementById("nombre_fam").value;
      if(nombrefamilia.trim().length==0){
        Vue.swal({
                text: 'Ingrese nombre de familia...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }
      var porcentaje=document.getElementById("porcentaje_fam").value;
      if(porcentaje.trim().length==0){
        Vue.swal({
                text: 'Ingrese porcentaje...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
      }



      var prefijo=document.getElementById("prefijo_fam").value;



      if(prefijo.trim().length==0){



        Vue.swal({



                text: 'Ingrese prefijo...',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



            return false;



      }



      var donde="factuprodu";


      var parametros={nombrefamilia:nombrefamilia,
        porcentaje:porcentaje,
        prefijo:prefijo,
        donde:donde,
        btnGuardarFamilia:"ajo"
      };

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        espere("Agregando Familia...");
      },
      url:url,
      data:parametros,
      success:function(resp){
        Vue.swal.close();
        if(resp=="OK"){
          cargar_familia();
          Vue.swal({
            text: 'Familia Agregada...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });



        }else{



            Vue.swal({



                text: 'No guardó Familia',



                position: 'top-end',



                icon: 'error',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        }







          },



          error: function(error){



            Vue.swal.close();



            var errores=JSON.parse(error.responseText);



            var salida="";



            for(var indice in errores)



            {



              salida=salida+errores[indice]+"<br>";



            }



            $('#mensajes').html("<p style='color:red'>"+salida+"</p>");



            Vue.swal({



                title: 'ERROR',



                text: salida,



                icon: 'error',



            });



          }



      });







      $("#agregar-familia-modal").modal("hide");







    }







    function guardar_marca_repuesto()
    {
      var url="{{url('marcarepuesto')}}";
      var marcarepuesto=document.getElementById("marcarepuesto").value;
      if(marcarepuesto.trim().length==0)
      {
        Vue.swal({
            text: 'Ingrese Marca de Repuesto...',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }

      var donde="factuprodu";
      var parametros={marcarepuesto:marcarepuesto,
        donde:donde,
        btnGuardarMarcaRepuesto:"ajo"
      };

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        espere("Agregando Marca");
      },
      url:url,
      data:parametros,
      success:function(resp){
        console.log(resp);
        Vue.swal.close();
        if(resp=="OK"){
          cargar_marca_repuesto();
          cargar_fabricantes();
          Vue.swal({
            text: 'Marca de Repuesto Agregada...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        }else{
            Vue.swal({
                text: 'Uy!!! No guardó...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }

          },
          error: function(error){
            Vue.swal.close();
            
            var errores=JSON.parse(error.responseText);
            console.log(errores.errors.marcarepuesto);
            var salida="";
            for(var indice in errores)
            {
              salida=salida+errores[indice];
            }
            // $('#mensajes').html("<p style='color:red'>"+salida+"</p>");
            Vue.swal({
                title: 'ERROR',
                text: errores.errors.marcarepuesto[0],
                icon: 'error',
            });
          }
      });

      $("#agregar-marca-repuesto-modal").modal("hide");

    }

    function guardar_pais()
    {
      var url="{{url('pais')}}";
      var pais=document.getElementById("pais").value;
      if(pais.trim().length==0){

        Vue.swal({
            text: 'Escriba un País...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }
      var donde="factuprodu";
      var parametros={pais:pais,
        donde:donde,
        btnGuardarPais:"ajo"
      };

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        espere("Agregando País...");
      },
      url:url,
      data:parametros,
      success:function(resp){
        console.log(resp);
        Vue.swal.close();
        if(resp=="OK"){
          cargar_pais();
          Vue.swal({
                text: 'País Agregado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }else{
            Vue.swal({
                text: 'Upss!!! no guardó el país...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }

          },
          error: function(error){
            Vue.swal.close();
            var errores=JSON.parse(error.responseText);
            var salida="";
            for(var indice in errores)
            {
              salida=salida+errores[indice]+"<br>";
            }
            $('#mensajes').html("<p style='color:red'>"+salida+"</p>");
            Vue.swal({
                title: 'ERROR',
                text: salida,
                icon: 'error',
            });
          }
      });







      $("#agregar-pais-modal").modal("hide");







    }







    function guardar_medida(){
      let medida = document.getElementById('medida').value;
      let familia = document.getElementById('familia').value;

      var url="{{url('medidas')}}";

      console.log(familia);
      if(medida.trim().length==0){
        Vue.swal({
            text: 'Escriba una medida...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }

      let data = {medida: medida, id_familia: familia, btnGuardarMedida: 'ajo'};

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        type:'post',
        url:url,
        data:data,
        beforeSend: function(){
          espere("Agregando Medida...");
        },
        success: function(resp){
          console.log(resp[1].descripcion);
          $('#medidas').val(resp[1].descripcion);
          if(resp[0] == 'OK'){
            Vue.swal.close();
            cargar_medidas();

            Vue.swal({
                text: 'Medida Agregada, ahora debe seleccionarla',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,


            });

            // cerrar la ventana modal
            $("#agregar-medida-familia-modal").modal("hide");

        }else{
            Vue.swal({
                text: 'Upss!!! no guardó la medida...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }

        },
        error: function(error){
          console.log(error.responseText);
        }
      })

    }

    function guardar_lista_medidas(){
      let lista = '';
      $('.dato:enabled').each(
        function() {

          let val = $(this).val();

          lista+=','+val;

        }

    );

    console.log(lista);
    $('#medidas').val(lista);
    }

    function guardar_lista_medidas_disco(){
      let lista = '';
      $('.dato_disco:enabled').each(
        function() {

          let val = $(this).val();

          lista+=val+',';

        }

    );

    console.log(lista);
    $('#medida1_pd').val('DISCO:'+lista);
    }

    function guardar_lista_medidas_prensa(){
      let lista = '';
      $('.dato_prensa:enabled').each(
        function() {

          let val = $(this).val();

          lista+=val+',';

        }

    );

    console.log(lista);
    $('#medida2_pd').val('PRENSA:'+lista);
    }







    function soloNumeros(e)
    {
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8))
    }







    function validarCabeceraFactura()
    {
        cantidad_repuestos_factura=0;
        var id_compras_cab = document.getElementById('id_cab_factura').value;
        var prov=document.getElementById("proveedor").value;
      if(prov==0)
      {
        Vue.swal({
            text: 'Elija un Proveedor...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }

      var f=document.getElementById("numerofactura").value;
      if(f.trim().length==0)
      {
        Vue.swal({
                    text: 'Ingrese Núm de Factura',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      var fechafactura = document.getElementById("fechafactura").value;
      if(fechafactura.trim().length==0)
      {
        Vue.swal({
                    text: 'Ingrese Fecha de Factura',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      var vencefactura = document.getElementById("vencefactura").value;
      var check=document.getElementById("credito");
      if(check.checked && vencefactura.trim().length==0)
      {
        Vue.swal({
                    text: 'Ingrese Fecha de Vencimiento de Factura a Crédito',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      var proveedor=document.getElementById("proveedor");
      var idproveedor=proveedor.value;
      var elproveedor=proveedor.options[proveedor.selectedIndex].text;
      var f=document.getElementById("numerofactura").value;
      if(id_compras_cab === ''){
        var url='{{url("factuprodu/verificafactura")}}'+'/'+idproveedor+"*"+f;
        $.ajax({
        type:'GET',
        beforeSend: function () {
        },
        url:url,
        success:function(resp){

          var r=resp.split("*");
          console.log(r[1]);
          $('#id_cab_factura').val(r[1]);
          if(r[0]=="EXISTE")
          {
            //Llega desde el controlador: resultado, id, numfac,fecfac... kaka
            var x=confirm("EXISTE FACTURA N° "+r[2]+" con fecha "+r[3]+"\n \nDesea Agregar más Items??");
            if(x)
            {
                $("#total_repuestos_factura").html("Factura: "+r[4]);
                cantidad_repuestos_factura=parseInt(r[4]);
                if(r[5]==0){
                    $("#total_repuestos_ot").html("No hay OT");
                }else{
                    $("#total_repuestos_ot").html("OT: "+r[5]);
                }

                //Ponemos el total de repuestos digitados
                Vue.swal({
                    text: 'Ingrese Item...',
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
              //r[1] tiene el id de compras_cab
              habilitar_ingreso_items(r[1]);
            }else{
                Vue.swal({
                    text: 'Ingrese otra Factura',
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
            }
          }else{ //No existe factura para ese proveedor
            //Llega desde el controlador: resultado,0,0
            guardarCabeceraFactura();
            $("#total_repuestos_factura").html("Factura: "+r[3]);
            cantidad_repuestos_factura=parseInt(r[4]);
            if(r[4]==0){
                $("#total_repuestos_ot").html("No hay OT");
            }else{
                $("#total_repuestos_ot").html("OT: "+r[4]);
            }
          }

        },
        error: function(error){
          Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

      });
      }else{
        guardarCabeceraFactura();
      }
     
      







    }







  function calculasubtotalitem()



  {



    var cantidad = cleanNumberChile(document.getElementById("cantidad").value);



    var pu = cleanNumberChile(document.getElementById("pu").value);



    if(cantidad>0 && pu>0){



        var subtotal = cantidad * pu;

        document.getElementById("subtotalitem").value = formatNumberChile(subtotal);



    }else{



        Vue.swal({



            text: 'Cantidad y Precio Unitario deben ser mayor a 0',



            position: 'top-end',



            icon: 'warning',



            toast: true,



            showConfirmButton: false,



            timer: 3000,



        });



    }







  }







  function calculapreciosug()
  {
    let cboFAM=document.getElementById("familia").value;
    if(cboFAM=="0" || cboFAM.trim().length==0)
    {
        Vue.swal({
                text: 'Elija una familia...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
      return false;
    }

    var cant = cleanNumberChile(document.getElementById("cantidad").value);
    if(cant=="0" || cant.length==0 || isNaN(cant) || Number(cant)<1)
    {
        Vue.swal({
                text: 'Ingrese Cantidad válida...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
      document.getElementById("cantidad").focus();
      return false;
    }
    var pu = cleanNumberChile(document.getElementById("pu").value);

    if(pu=="0.00" || pu.length==0 || isNaN(pu) || Number(pu)<1)
    {
        Vue.swal({
                text: 'Elija Precio Unitario Válido...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
      document.getElementById("pu").focus();
      return false;
    }

    var fle = cleanNumberChile(document.getElementById("flete").value);
    var flete=0;

    if(isNaN(fle) || Number(fle)<0 || fle.length==0 )
    {
        document.getElementById("flete").value=0;
        /*



        Vue.swal({



                text: 'Ingrese Flete Válido o presione el bóton CALCULAR',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        document.getElementById("flete").focus();



      return false;



      */
    }else{
      flete=parseInt(fle);

    }

    var pu=cleanNumberChile(document.getElementById("pu").value);
    var iva=0.19; // Leer de la tabla parámetros
    var utilidad=parseFloat(document.getElementById("utilidad").value)/100.0;
    var ps=document.getElementById("preciosug");
    var elvalor=pu*(1+utilidad)*(1+iva)+flete;

    /* REDONDEO

    //aplicar redondeo divisor por 100
    var divisor=elvalor/100;
    var entero=Math.trunc(divisor);
    var entero_x_100=entero*100;
    var residuo=elvalor%100;
    var valor_redondeado=0;
    if(residuo>50)
    {
      valor_redondeado=entero_x_100+100;
    }else{
      valor_redondeado=entero_x_100;
    }

    if(flete==0 && ps.value>0) //calcula el flete
    {
        flete=ps.value-valor_redondeado;
        document.getElementById("flete").value=flete;
    }

    if((flete>0 && ps.value==0) || (flete==0 && ps.value==0))
    {
        ps.value=valor_redondeado;
    }
*/
    if(flete==0 && cleanNumberChile(ps.value)>0) //calcula el flete
    {
        flete=Math.round(cleanNumberChile(ps.value)-elvalor);
        document.getElementById("flete").value=flete;
    } else if(cleanNumberChile(ps.value)==0) {
        // Mostrar precio sugerido calculado con formato chileno
        let p = elvalor.toFixed(2).split('.');
        p[0]  = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        ps.value = p.join(',');
    }
    //ps.select();
  }

  function guardarficha(){
    Vue.swal({
      icon:'info',
      text:'En construcción'
    });
  }

  function dameSoloUtilidad(){
    // campos id y porcentaje de la tabla familias
    var idFamilia=document.getElementById("familia").value;
    if(idFamilia=="0" || idFamilia.trim().length==0)
    {
        return false;
    }



    //Petición AJAX para obtener el porcentaje respectivo


    var url_familia='{{url("factuprodu")}}'+'/'+idFamilia+'/utilidad'; //petición

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Obteniendo Utilidad...");
        },
        url:url_familia,
        success:function(utilidad){
          console.log(utilidad);
          document.getElementById("utilidad").value=utilidad;
        },
        error: function(error){
          Vue.swal.close();
          $('#mensajes').html(error.responseText);
          Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



        }

  });
}

  function dameUtilidad(value)
  {
    console.log(value);
    var url_medidas = '{{url("factuprodu")}}'+'/'+value+'/medidas';
    $.ajax({
      type:'get',
      url: url_medidas,
      beforeSend: function(){
        console.log('buscando...');
      },
      success: function(resp){
        console.log(resp);
        $('#rec_alt_rep').empty();
        $('#bendix').empty();
        $('#result').empty();
        $('#span_medidas').empty();
        $('#fotos_ficha_tecnica').empty();
        if(resp == 'bendix'){
          $('#rec_alt_rep').addClass('d-none');
          $('#bendix').removeClass('d-none');
          $('#bendix').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
          
          <div class='form-group'>
              <label>MOTOR DE PARTIDA</label>
              <input class='form-control' id="motorpartida" type='text' placeholder ='Motor de partida' />
          </div>
          
          
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_motor()" id="btn_motor" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
        }else if(resp == 'rv'){
          $('#rec_alt_rep').removeClass('d-none');
          $('#rec_alt_rep').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
          <div>
          <div class='form-group'>
              <label>RECTIFICADOR </label>
              <input class='form-control' id="rectificador" type='text' placeholder ='Rectificador' />
          </div>
          <div class='form-group'>
              <label>ALTERNADOR </label>
              <input class='form-control' id="alternador" type='text' placeholder ='Alternador' />
          </div>
          <div>
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_rec_alt()" id="btn_guardar_rec_alt" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
        }else if(resp == 'aceite'){
          $('#fotos_ficha_tecnica').empty();
          $('#fotos_ficha_tecnica').append(`
          <label>Subir Ficha técnica (pdf):</label>
          <input type="file" name="ficha_tecnica" id="ficha_tecnica" class="form-control-file">
          <div id="ficha_tecnica_submit">
                <input type="submit" name="btnGuardarFicha" id="btnGuardarFicha" value="Agregar Ficha Técnica" class="btn btn-primary btn-sm" onclick="guardarficha()" style="margin-left:2px"/>
            </div>
          `);
          $('#span_medidas').empty();
          $('#span_medidas').append(`
          <label for="medidas">Medidas:</label>
          <div class="clearfix"></div>
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;"></textarea>
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          <input type="hidden" id="familia_length" />
          `);
        }else if(resp[0] == 'pd'){
          let medidas_disco = resp[1];
          let medidas_prensa = resp[2];
          $('#span_medidas').empty();
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
          let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>
            <tr>
                  <th scope='row'>DISCO</th> 
                  <th scope='row'><input type="text" class="form-control dato" id="medida1_pd" value="DISCO-"  /></th> 
                  <th scope='row'> 
                    <button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal_pd' >Seleccionar medidas </button> <input type="hidden" id="medida_value" />
                  </th>
            </tr>
            <tr>
                  <th scope='row'>PRENSA</th> 
                  <th scope='row'><input type="text" class="form-control dato" id="medida2_pd" value="PRENSA-"  /></th> 
                  <th scope='row'> 
                    <button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal_pd_dos' >Seleccionar medidas </button> <input type="hidden" id="medida_value" />
                  </th>
            </tr>
            </tbody>`;
            
          $('#modal_body_medidas').append(html);
          $('#modal_body_medidas_pd').empty();
          let html_ = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>`;
          var cont_ = 0;
            medidas_disco.forEach(e => {
                cont_++;
                html_+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato_disco" id="medida_`+cont_+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value_" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx_`+cont_+`' onclick="abrirCampoMedidas_pd(`+cont_+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                </tr>`;
              });
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas_pd').append(html_);

          $('#modal_body_medidas_pd_dos').empty();
          let html_dos = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>`;
          var cont_dos = 0;
            medidas_prensa.forEach(e => {
                cont_dos++;
                html_dos+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato_prensa" id="medida_dos`+cont_dos+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value_" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx_dos`+cont_dos+`' onclick="abrirCampoMedidas_pd_dos(`+cont_dos+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                </tr>`;
              });
          html_dos+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas_pd_dos').append(html_dos);
        }else {
          
          if(resp.length == 0){
        
          $('#span_medidas').empty();
          $('#span_medidas').append(`
          <label for="medidas">Medidas:</label>
          <div class="clearfix"></div>
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;"></textarea>
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          <input type="hidden" id="familia_length" />
          `);
        }else{
          $('#span_medidas').empty();
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
          let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
              <th scope="col"></th>
              <th scope="col"> </th>
            </tr>
          </thead>
          <tbody>`;
          var cont = 0;
              resp.forEach(e => {
                cont++;
                html+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato" id="medida`+cont+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx`+cont+`' onclick="abrirCampoMedidas(`+cont+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                  <th>
                    <button class="btn btn-danger btn-sm" onclick="eliminar_medida_familia(`+e.id+`)">X</button>
                  </th>
                  <th>
                    <button class="btn btn-info btn-sm" onclick="editar_medida_familia(`+e.id+`)">Editar</button>
                  </th>
                </tr>`;
              });
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas').append(html);
          }
      }
        }
        
    });

    // campos id y porcentaje de la tabla familias
    var idFamilia=document.getElementById("familia").value;
    if(idFamilia=="0" || idFamilia.trim().length==0)
    {
        return false;
    }



    //Petición AJAX para obtener el porcentaje respectivo


    var url_familia='{{url("factuprodu")}}'+'/'+idFamilia+'/utilidad'; //petición

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Obteniendo Utilidad...");
        },
        url:url_familia,
        success:function(utilidad){
          console.log(utilidad);
          document.getElementById("utilidad").value=utilidad;
        },
        error: function(error){
          Vue.swal.close();
          $('#mensajes').html(error.responseText);
          Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

      }); //Fin ajax dameUtilidad

  }



function eliminar_medida_familia(idmedida){
  let familia = $('#familia').val();
  let params = {
    idmedida: idmedida,
    idfamilia: familia
  }
  let url = '{{url("factuprodu")}}'+'/'+'eliminar_medida_familia'; //petición
  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
  });

  $.ajax({
    type:'post',
    data: params,
    url: url,
    success: function(resp){
      console.log(resp);
      dameUtilidad(familia);
      $('#verMedidasModal').modal('hide');
      //espere('EN CONSTRUCCION');
    },
    error: function(error){
      console.log(error.responseText);
    }
  });
  
}

function editar_medida_familia(idmedida){
    $('#modalEditarMedida').modal('show');
    // no mostrar el modal verMedidasModal
    $('#verMedidasModal').modal('hide');
    let url = "{{url('medidas')}}"+'/'+idmedida+'/editar';
    $.ajax({
      type: 'get',
      url: url,
      success: function(resp){
        console.log(resp);
        $('#medida_nombre').val(resp.descripcion);
        $('#medida_id').val(resp.id);
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
}


  function eliminaritem(id)
  {
    if(confirm('Desea eliminar Item?')==true)
    {
        var url_item='{{url("compras")}}'+'/'+id+'/eliminar'; //petición
        var idFactura=document.getElementById("id_factura_cab").value;
        $.ajax({
         type:'GET',
         beforeSend: function () {
            espere("Eliminando Item...");
        },
        url:url_item,
        success:function(datos){
        Vue.swal.close();
          $("#mensajes").html(datos);
            dameItems(idFactura); //Carga los items ESTA PARTE NO  FUNCIONA...
          },
          error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }

          }); //Fin ajax eliminar item
        return true;
      } // fin if confirmacion
    }

    function dameItems(id_factura)
    {
      var url_items='{{url("compras")}}'+'/'+id_factura+'/dameitems'; //petición
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#compras_det").html("Obteniendo Items...");
      },
      url:url_items,
      success:function(datos){
        $("#compras_det").html(datos);

      },

      error: function(error){
        $('#compras_det').html(error.responseText);
        Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

        }); //Fin ajax items

    }

    function existe_en_factura(cod){
      console.log(cod);
        let rpta=false;
        let idFactura=document.getElementById("id_factura_cab").value;
        let idProveedor=document.getElementById("proveedor").value;
        console.log(idProveedor);
        let url='{{url("factuprodu")}}'+'/'+cod+'/proveedor/'+idProveedor+'/factura/'+idFactura; //petición
        console.log(url);
        $.ajax({
            type:'GET',
            url:url,
            async: false,
            cache: false,
            success:function(resp){
              console.log(resp);
                if(resp=="EXISTE") rpta=true;
            },
            error: function(error){
                Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
            }

        }); //Fin ajax items

        return rpta;

    }

    function verificar_cod_prov()
    {
      
        var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;
        if(cod_repuesto_proveedor.trim().length==0)
        {
            Vue.swal({
                    text: 'Ingrese Código de Repuesto...',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
            return false;
        }

        if(existe_en_factura(cod_repuesto_proveedor)){
            document.getElementById("cod_repuesto_proveedor").focus();
            document.getElementById("cod_repuesto_proveedor").select();
            Vue.swal({
                text: 'Código de Repuesto ya existe para la Factura y Proveedor elegidos..',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }else{
            guardarItem();
        }

      /*

      var idFactura=document.getElementById("id_factura_cab").value;
      var idProveedor=document.getElementById("proveedor").value;
      var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;

      if(cod_repuesto_proveedor.trim().length==0)
      {
        Vue.swal({



                text: 'Ingrese Código de Repuesto...',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }







      var url_items='{{url("factuprodu")}}'+'/'+cod_repuesto_proveedor+'/proveedor/'+idProveedor+'/factura/'+idFactura; //petición







      $.ajax({



       type:'GET',



       beforeSend: function () {



        $("#mensajes").html("Verificando Código Repuesto...");



      },



      url:url_items,



      success:function(resp){











        if(resp=="NO EXISTE")



        {



          guardarItem();



        }else{



          document.getElementById("cod_repuesto_proveedor").focus();



          document.getElementById("cod_repuesto_proveedor").select();



          Vue.swal({



                text: 'Código de Repuesto ya existe para la Factura y Proveedor elegidos..',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        }







      },



      error: function(error){



        $('#mensajes').html(error.responseText);



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        }); //Fin ajax items



*/











    }


    function guardarItem() //kaka
    {
      
      //Valores del item de repuesto
      var idFamilia=document.getElementById("familia").value;
      if(idFamilia==0)
      {
        Vue.swal({
            text: 'Seleccione una Familia',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }
      var idMarcaRepuesto=document.getElementById("MarcaRepuesto").value;
      if(idMarcaRepuesto==0)
      {
        Vue.swal({
            text: 'Seleccione una marca de Repuesto',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }

      var idProveedor=document.getElementById("proveedor").value;
      var idPais=document.getElementById("Pais").value;
      if(idPais==0)
      {
        Vue.swal({
            text: 'Seleccione un País de Origen...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }
      var descripcion=document.getElementById("descripcion").value;
      if(descripcion.trim().length==0)
      {
        Vue.swal({
                    text: 'Ingrese una descripción',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      var observaciones=document.getElementById("observaciones").value;
      var medidas=document.getElementById("medidas").value;
      
      if(medidas.trim().length==0) medidas="No Definidas";
      var cod_oem="---"; //document.getElementById("codigo_oem").value;
      var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;
      if(cod_repuesto_proveedor.trim().length==0)
      {
        Vue.swal({
            text: 'Ingrese código de repuesto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }
      //var cod2_repuesto_proveedor=document.getElementById("cod2_repuesto_proveedor").value;
      var stockmin=document.getElementById("stock_minimo").value;
      var stockmax=document.getElementById("stock_maximo").value;
      var codbar=document.getElementById("codigo_barras").value;

      //Valores del item de factura
      var idFactura=document.getElementById("id_factura_cab").value;
      // Leer y limpiar valores con formato chileno
      var cantidad = cleanNumberChile(document.getElementById("cantidad").value);
      if(cantidad==0 || isNaN(cantidad) || cantidad < 1)
      {
        Vue.swal({
            text: 'Ingrese Cantidad...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false
      }

      /* 
      
        El stock máximo se debe respetar, no le puedo colocar una cantidad mayor al stock máximo
      */
     if(cantidad > cleanNumberChile(stockmax)){
      Vue.swal({
            text: 'La cantidad no puede ser mayor que el stock máximo',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false
     }
      var pu = cleanNumberChile(document.getElementById("pu").value);

      if(pu==0 || isNaN(pu) || pu < 1)
      {
        Vue.swal({
            text: 'Ingrese precio unitario...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false
      }

      var subtotalitem = cleanNumberChile(document.getElementById("subtotalitem").value);
      calculapreciosug();
      var flete = cleanNumberChile(document.getElementById("flete").value);
      if(flete<0) flete=0;
      var preciosug = cleanNumberChile(document.getElementById("preciosug").value);

      if(preciosug==0 || isNaN(preciosug) || preciosug < 1)
      {
        Vue.swal({
            text: 'Ingrese o Calcule Precio Sugerido',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false
      }

      var idLocal=document.getElementById("locales").value;
      if(idLocal === null){
        idLocal = 1;
      }
      var ubi = document.getElementById("ubicacion_repuesto").value;
      if(ubi === '' || ubi.trim().length==0){
        ubi = "SIN INFORMACION";
      }
      var nuevo="SI";

      if(document.getElementById("elegir").value=="SI") nuevo="NO";
      var id_repuesto=document.getElementById("id_repuesto").value;
      var id_repuesto_clonado=0;
      if(clonando){
        id_repuesto_clonado=document.getElementById("id_repuesto_clonado").value;
      }
/*
     console.log(pu, preciosug);

      if(pu > preciosug){
        Vue.swal({
            text: 'El precio de venta no puede ser menor al precio de compra',
            position: 'top-end',
            
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }
*/
      
      // LIMPIAR VALORES DE STOCK (solo estos todavía no se han limpiado)
      var stockmin_cleaned = cleanNumberChile(stockmin);
      var stockmax_cleaned = cleanNumberChile(stockmax);

      var parametros={nuevo:nuevo,
        idrep:id_repuesto,
        idrep_clonado:id_repuesto_clonado,
        idFamilia:idFamilia,
        idMarcaRepuesto:idMarcaRepuesto,
        idProveedor:idProveedor,
        idPais:idPais,
        descripcion:descripcion,
        observaciones:observaciones,
        medidas:medidas,
        cod_oem:cod_oem.toUpperCase(),
        cod_repuesto_proveedor:cod_repuesto_proveedor.toUpperCase(),
        stockmin:stockmin_cleaned,
        stockmax:stockmax_cleaned,
        codbar:codbar,
        idFactura:idFactura,
        cantidad:cantidad,
        pu:pu,
        subtotalitem:subtotalitem,
        flete:flete,
        preciosug:preciosug,
        idLocal:idLocal,
        ubicacion: ubi
      };

      // console.log(parametros);

      var url="{{url('factuprodu/guardaritem')}}";

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });


      $.ajax({
       type:'POST',
       beforeSend: function () {
        espere("Guardando Item...");
      },
      url:url,
      data:parametros,
      success:function(resp){
        console.log(resp);
        
        Vue.swal.close();
        var r=resp.split("*");
        cantidad_repuestos_factura+=parseInt(cantidad);
        /*
        console.log("guardaritem clonando es ");
        console.log(clonando?"SI":"NO");
        */
        if(r[0]==1) //1: Todo OK, NUEVO
        {

          if(clonando){
            dame_detalle(r[2]);
          }
          document.getElementById("codigo_interno").value=r[1]; //el último repuesto guardado siempre será clonable
          document.getElementById("id_repuesto").value=r[2];
          //$("#zona_ingreso_repuesto *").prop('disabled',true); //con JQuery
          document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.setAttribute("disabled", "true")); //con JS
          document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.setAttribute("disabled", "true"));
          document.getElementById("btnNuevoItem").disabled=false;
          //Habilitar el ingreso de fotos, similares y otros OEMs
          document.querySelectorAll("#zona_fotos *").forEach(el => el.removeAttribute("disabled"));
          document.querySelectorAll("#zona_similares *").forEach(el => el.removeAttribute("disabled"));
          document.querySelectorAll("#zona_OEMs *").forEach(el => el.removeAttribute("disabled"));
          document.querySelectorAll("#zona_FABs *").forEach(el => el.removeAttribute("disabled"));
          // si existe document.getElementById("btn_guardar_rec_alt") remover el atributo disabled
          if(document.getElementById('btn_guardar_rec_alt')!=null){
            document.getElementById('btn_guardar_rec_alt').removeAttribute('disabled');
          }
          // si existe document.getElementById("btn_motor") remover el atributo disabled
          if(document.getElementById('btn_motor')!=null){
            document.getElementById('btn_motor').removeAttribute('disabled');
          }
          
          Vue.swal({
            text: 'Item Guardado...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
          location.href="#zona_fotos";
        }


        if(r[0]==2) //1: Existe el código... // Consultado con pancho el 22jun2020: guardar siempre el precio mayor
        {
          //var respu=confirm("Ya existe "+r[1]+": "+r[2]+" con \n precio compra: "+r[3]+" \n precio venta:"+r[4]+"\n \nDesea actualizar con \n precio compra: "+pu+" ?\n precio venta: "+preciosug+" ?");
            /*
          if(respu==true)
          {
               modificar_precio_repuesto(pu,preciosug,r[5]);
          }
          */
          limpiar_campos();
          $("#mensajes").html("Item Guardado con precio modificado...");
          location.href="#mensajes";
        }

        if(r[0]==3) //existe pero no se modificó el precio
        {
            limpiar_campos();
            $("#mensajes").html("Item Guardado sin novedad...");
            location.href="#mensajes";
        }

        if(r[0]=="xuxa")
        {
          $("#mensajes").html(r[1]);
        }

        $("#total_repuestos_factura").html("Factura: "+parseInt(cantidad_repuestos_factura));
          },
          error: function(error){
            Vue.swal.close();
            //$('#mensajes').html(error.responseText);
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }
      });

    }

    function modificar_precio_repuesto(precio_compra,precio_venta,id_repu)



    {



      var url='{{url("repuesto")}}'+'/'+id_repu+'/cambiaprecio/'+precio_compra+"/"+precio_venta;



      $.ajax({



       type:'GET',



      url:url,



      success:function(repu){







        Vue.swal.close();



        if(repu=="OK")



        {



            Vue.swal({



                text: 'Precio Modificado',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        }else{



            Vue.swal({



                text: 'No se pudo modificar el precio',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        }







      },



      error: function(error){



        Vue.swal.close();



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        }); //Fin ajax



    }







    function guardarfoto()
    {
      var url="{{url('factuprodu/guardarfoto')}}";
      var archivo=$("#archivo")[0].files[0];
      if(archivo.size > 1020000)
      {
        Vue.swal({
                text: 'Imagen muy grande, no se puede guardar',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }

      var archi=document.getElementById("archivo");
      var archivo_nombre=archi.value;

      if(archivo_nombre.length==0)
      {
        Vue.swal({
                text: 'Elija una imagen...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }

      var idrep=document.getElementById("id_repuesto").value;
      var datos=new FormData();
      datos.append('idrep',idrep);
      datos.append('archivo',archivo);

      $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        $("#fotos_msje").html("<b>FOTOS:</b> Guardando Foto, espere por favor...");
        espere("Subiendo Imagen...");
      },
      url:url,
      data:datos,
      cache:false,
      contentType: false,
      processData: false,
      //timeout:15000, // 15 segundos
      success:function(resp){
        Vue.swal.close();
        if(resp=="EXISTE")
        {
          $("#fotos_msje").html("<b style='font-size:large;color:red'>FOTOS: Imagen ya existe...</b>");
          Vue.swal({
                text: 'Imagen ya existe...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }else{

          document.getElementById("archivo").value="";
          $("#fotos_msje").html("<b>FOTOS:</b> Foto Agregada...");
          Vue.swal({
                text: 'Foto Agregada...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          $("#fotos_rep").html(resp);
        }











          },



      error: function(error){



        Vue.swal.close();



        $('#fotos_msje').html(error.responseText);



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



          }



        });







    }





    function borrarrv(idrv){
      var idrep=document.getElementById("id_repuesto").value;
      var url='{{url("factuprodu")}}'+'/'+idrv+'/borrarrv/'+idrep;
      
      $.ajax({
        type:'get',
        url:url,
        beforeSend: function(){
          
          espere("Borrando RV...");
          
        },
        success: function(resp){
          Vue.swal.close();
          Vue.swal({
                text: 'RV borrado',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          $('#result').empty();
          $('#result').append(resp);
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
    }

    function borrarfoto(idfoto)



    {







      var idrep=document.getElementById("id_repuesto").value;



      var url='{{url("factuprodu")}}'+'/'+idfoto+'/borrarfoto/'+idrep;



      $.ajax({



       type:'GET',



       beforeSend: function () {



        espere("Borrando Foto");



        $("#fotos_rep").html("");



      },



      url:url,



      success:function(resp){



          Vue.swal.close();



          $("#fotos_msje").html("<b>FOTOS:</b> Foto Borrada...");



          Vue.swal({



                text: 'Foto Borrada...',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#fotos_rep").html(resp);



      },



      error: function(error){



        $('#mensajes').html(error.responseText);



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        }); //Fin ajax



    }







    function guardarsimilar()



    {



      var url="{{url('factuprodu/guardarsimilar')}}";



      var idrep=document.getElementById("id_repuesto").value;



      var idMarca=document.getElementById("MarcaSim").value;



      var idModelo=document.getElementById("ModeloSim").value;



      var anios=document.getElementById("anios_vehiculo_sim").value.trim();



      //Validar que anios tenga el formato 9999-9999



      var mensa="Años de la aplicación debe ser en formato 9999-9999 y el año inicial menor igual al año final";



      var n="";



      var nn=0;



      var anios_ok=true;



      if(anios.length!=9)



      {



        Vue.swal({



                text: mensa,



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }







      for (var i=0;i<anios.length;i++)



      {



        n=anios.substring(i,i+1);



        if(i==4)



        {



          if(n!="-")



          {



            anios_ok=false;



            break;



          }



        }else{



          nn=n*1;



          if(isNaN(nn) || !Number.isInteger(nn))



          {



            anios_ok=false;



            break;



          }



        }



      }



      if(anios_ok==false)



      {



        Vue.swal({



                text: mensa,



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }







      var año_actual=(new Date).getFullYear();



      var año_inicial=anios.substring(0,4)*1;



      var año_final=anios.substring(5)*1;



      if(año_inicial>año_actual || año_final>año_actual)



      {



        Vue.swal({



                text: mensa,



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }



      if(año_inicial>año_final)



      {



        Vue.swal({



                text: mensa,



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }







      var parametros={idrep:idrep,idMarca:idMarca,idModelo:idModelo,anios:anios};







      $.ajaxSetup({



      headers: {



        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')



       }



      });







      $.ajax({



       type:'POST',



       beforeSend: function () {







        $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Guardando...");



        espere("Guardando Aplicación...");



      },



      url:url,



      data:parametros,



      success:function(resp){



        Vue.swal.close();



        if(resp=="EXISTE")



        {



          $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Aplicación ya fue Agregada...");



          Vue.swal({



                text: 'Aplicación ya fue agregada...',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        }else{



          document.getElementById("anios_vehiculo_sim").value="";



          $("#nombre_modelo").html("<b>Modelo: </b>");



          $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Aplicación Agregada...");



          Vue.swal({



                text: 'Aplicación Agregada...',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#similares_rep").html(resp);



/*



          let filtro=document.getElementById("filtrar_modelo");



          filtro.value="";



          filtro.dispatchEvent(new KeyboardEvent('keyup',{'key':'Shift'}));



*/



        }



          },



      error: function(error){



        Vue.swal.close();



            $('#aplicaciones_msje').html(error.responseText);



            Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



          }



        });







    }







    function borrarsimilar(idsimilar)



    {



      var idrep=document.getElementById("id_repuesto").value;



      var url='{{url("factuprodu")}}'+'/'+idsimilar+'/borrarsimilar/'+idrep;



      $.ajax({



       type:'GET',



       beforeSend: function () {



        espere("Borrando aplicación...");



        $("#similares_rep").html("");



      },



      url:url,



      success:function(resp){



          Vue.swal.close();



          $("#aplicaciones_msje").html("<b>APLICACIONES:</b> Aplicación Borrada...");



          Vue.swal({



                text: 'Aplicación Borrada...',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#similares_rep").html(resp);



      },



      error: function(error){



          Vue.swal.close();



        $('#mensajes').html(error.responseText);



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        }); //Fin ajax



    }







    function enter_text_oem(event)
    {
      var keycode = event.keyCode;
      if(keycode=='13')
      {
        guardarOEM();
      }
    }







    function guardarOEM()
    {
      var url="{{url('factuprodu/guardaroem')}}";
      var idrep=document.getElementById("id_repuesto").value;
      var cod_oem=document.getElementById("codigos_OEM").value;

      cod_oem=cod_oem.replace(/-/g,"");
      cod_oem=cod_oem.toUpperCase();
      if(cod_oem.trim().length==0)
      {
        Vue.swal({
            text: 'Ingrese Código OEM',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }

      var parametros={cod_oem:cod_oem.toUpperCase(),idrep:idrep};

      $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        $("#oems_msje").html("<b>OEMs:</b> Guardando OEM, espere por favor...");
        espere("Guardando OEM...");
      },
      url:url,
      data:parametros,
      success:function(resp){
        Vue.swal.close();
        if(resp=="EXISTE")
        {
          $("#oems_msje").html("<b>OEMs:</b> OEM ya fue agregado...");
          Vue.swal({
                text: 'OEM ya fue Agregado...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          document.getElementById("codigos_OEM").select();
        }else{
          $("#oems_msje").html("<b>OEMs:</b> OEM Agregado...");
          Vue.swal({
                text: 'OEM Agregado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          $("#oems_rep").html(resp);
          document.getElementById("codigos_OEM").value="";

        }
        document.getElementById("codigos_OEM").focus();
          },
      error: function(error){
        Vue.swal.close();
          $('#oems_msje').html(error.responseText);
          Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }
        });
    }

    function mostrarModalPrincipal(){
      $("#verMedidasModal").modal("show");
      $('#modalEditarMedida').modal('hide');
    }


    function borraroem(idoem)



    {



      var idrep=document.getElementById("id_repuesto").value;



      var url='{{url("factuprodu")}}'+'/'+idoem+'/borraroem/'+idrep;



      $.ajax({



       type:'GET',



       beforeSend: function () {



        $("#oems_msje").html("<b>OEMs:</b> Borrando OEM, espere por favor...");



        espere("Borrando OEM...");



        $("#oems_rep").html("");



      },



      url:url,



      success:function(resp){



          Vue.swal.close();



          $("#oems_msje").html("<b>OEMs:</b> OEM Borrado...");



          Vue.swal({



                text: 'OEM borrado',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#oems_rep").html(resp);



      },



      error: function(error){



          Vue.swal.close();



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        }); //Fin ajax



    }







    function enter_text_FAB(event)



    {



      var keycode = event.keyCode;



      if(keycode=='13')



      {



        guardarFAB();



      }



    }







    function pasar_a_FAB()



    {



      document.getElementById("cboFabricante").value=document.getElementById("MarcaRepuesto").value;



    }







    function guardarFAB()



    {



      var idfab=document.getElementById("cboFabricante").value;



      if(idfab==0)



      {



        Vue.swal({



                text: 'Elija un Fabricante',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        return false;



      }



      var cod_fab=document.getElementById("codigo_FAB").value;



      if(cod_fab=="")



      {



        Vue.swal({



                text: 'Escriba Código de Fabricante',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



        document.getElementById("codigo_FAB").focus();



        return false;



      }







      var url="{{url('factuprodu/guardarfab')}}";



      var idrep=document.getElementById("id_repuesto").value;



      var parametros={cod_fab:cod_fab.toUpperCase(),idrep:idrep,idfab:idfab};



      $.ajaxSetup({



      headers: {



        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')



       }



      });







      $.ajax({



       type:'POST',



       beforeSend: function () {



        $("#fabricantes_msje").html("<b>FABRICANTES:</b> Guardando Código, espere por favor...");



        espere("Guardando Código Fabricante...");



      },



      url:url,



      data:parametros,



      success:function(resp){



        Vue.swal.close();



        if(resp=="EXISTE")



        {



          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código ya fue agregado...");



          Vue.swal({



                text: 'Código ya fue Agregado...',



                position: 'top-end',



                icon: 'warning',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          document.getElementById("codigo_FAB").select();



        }else{



          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código Agregado...");



          Vue.swal({



                text: 'Código Agregado...',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#fabs_rep").html(resp);



          document.getElementById("codigo_FAB").value="";



          document.getElementById("cboFabricante").selectedIndex =0;



        }



        document.getElementById("codigo_FAB").focus();



          },



      error: function(error){



        Vue.swal.close();



          $('#fabricantes_msje').html(error.responseText);



          Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



          }



        });



    }







    function borrarfab(idfab)



    {



      var idrep=document.getElementById("id_repuesto").value;



      var url='{{url("factuprodu")}}'+'/'+idfab+'/borrarfab/'+idrep;



      $.ajax({



       type:'GET',



       beforeSend: function () {



        espere("Borrando Código...");



        $("#fabs_rep").html("");



      },



      url:url,



      success:function(resp){



          Vue.swal.close();



          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código Borrado...");



          Vue.swal({



                text: 'Código Borrado...',



                position: 'top-end',



                icon: 'info',



                toast: true,



                showConfirmButton: false,



                timer: 3000,



            });



          $("#fabs_rep").html(resp);



      },



      error: function(error){



        $('#fabricantes_msje').html(error.responseText);



        Vue.swal({



                title: 'ERROR',



                text: error.responseText,



                icon: 'error',



            });



      }







        });



    }







    function nuevo_item()

    {

      limpiar_campos();

    }







    function limpiar_campos()
    {
      clonando=false;
      //document.getElementById("proveedor").selectedIndex=0;
      document.getElementById("familia").selectedIndex=0;
      document.getElementById("MarcaSim").selectedIndex=0;
      $('#ModeloSim option').remove();
      $('#ModeloSim').append('<option value="">Elija una marca</option>');
      $("#nombre_modelo").html("<b>Modelo: </b>");
      $("#aplicaciones_msje").html("<B>APLICACIONES:</B>");
      $("#fotos_msje").html("<B>FOTOS:</B>");
      $("#oems_msje").html("<B>OEMs:</B>");
      $("#fabricantes_msje").html("<B>FABRICANTES:</B>");

      document.getElementById("ModeloSim").selectedIndex=0;
      document.getElementById("MarcaRepuesto").selectedIndex=0;
      document.getElementById("Pais").selectedIndex=0;
      document.getElementById("txtBuscar").value="";
      document.getElementById("descripcion").value="";
      document.getElementById("observaciones").value="";
      document.getElementById("medidas").value="";
      document.getElementById("cod_repuesto_proveedor").value="";
      //document.getElementById("codigo_oem").value="";
      document.getElementById("stock_minimo").value="0";
      document.getElementById("stock_maximo").value="0";
      document.getElementById("codigo_barras").value="0";
      document.getElementById("cantidad").value="0";
      document.getElementById("pu").value="0.00";
      document.getElementById("subtotalitem").value="0.00";
      document.getElementById("flete").value="0.00";
      document.getElementById("preciosug").value="0.00";

      document.getElementById("id_repuesto").value="";
      document.getElementById("utilidad").value="";
      document.getElementById("elegir").value="NO";

      //habilitar reIngreso de Items

      document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.removeAttribute("disabled"));
      document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.removeAttribute("disabled"));

      //Deshabilitar el ingreso de fotos, similares y otros OEMs
      document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));

      //Limpiamos en caso de que sea una caja reguladora
      

      //Limpiar las fotos, similares, OEMs y Cajas reguladoras

      $("#fotos_rep").html("");
      $("#similares_rep").html("");
      $("#oems_rep").html("");
      $("#fabs_rep").html("");
      $('#rec_alt_rep').html("");
      $('#bendix').html("");
      $('#result').html("");


      document.getElementById("archivo").value="";
      //document.getElementById("anios_vehiculo_sim").value="";
      document.getElementById("codigos_OEM").value="";
      document.getElementById("txtBuscar").focus();


    }


    function elegir_repuesto(repuesto)
    {
      console.log(repuesto);
      // if(repuesto.oferta == 1){
      //   $('#preal').removeClass('d-none');
      //   $('#preal').addClass('d-block');
      // }else{
      //   $('#preal').removeClass('d-block');
      //   $('#preal').addClass('d-none');
      // }
      $('#busca-repuesto-modal').modal('hide');
      document.getElementById("id_repuesto").value=repuesto.id;
      // Establecer el valor de familia sin disparar el evento onchange
      let familiaSelect = document.getElementById("familia");
      let oldOnChange = familiaSelect.onchange;
      familiaSelect.onchange = null;
      familiaSelect.value = repuesto.id_familia;
      familiaSelect.onchange = oldOnChange;
      if(repuesto.id_familia == '206'){
        console.log('regulador de voltaje');
        $('#rec_alt_rep').empty();
        $('#result').empty();
        //MOSTRAMOS FORMULARIO
        $('#rec_alt_rep').removeClass('d-none');
          $('#rec_alt_rep').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
          
          <div class='form-group'>
              <label>RECTIFICADOR </label>
              <input class='form-control' id="rectificador" type='text' placeholder ='Rectificador' />
          </div>
          <div class='form-group'>
              <label>ALTERNADOR </label>
              <input class='form-control' id="alternador" type='text' placeholder ='Alternador' />
          </div>
          
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_rec_alt()" id="btn_guardar_rec_alt" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
      }else if(repuesto.id_familia == '166'){
        console.log('es un bendix');
        $('#bendix').empty();
        $('#result').empty();
        //MOSTRAMOS FORMULARIO
        $('#bendix').removeClass('d-none');
          $('#result').addClass('d-block');
          $('#bendix').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
          <div>
          <div class='form-group'>
              <label>MOTOR DE PARTIDA</label>
              <input class='form-control' id="motorpartida" type='text' placeholder ='Motor de partida' />
          </div>
          
          <div>
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_motor()" id="btn_motor" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
      }else{
        console.log('no regulador de voltaje ni bendix');
        $('#rec_alt_rep').empty();
        $('#bendix').empty();
        $('#result').empty();
        //OCULTAMOS LOS FORMULARIOS
        $('#rec_alt_rep').removeClass('d-block');
        $('#rec_alt_rep').addClass('d-none');
        $('#bendix').removeClass('d-block');
        $('#bendix').addClass('d-none');
      }
      dameSoloUtilidad();
      document.getElementById("MarcaRepuesto").value=repuesto.id_marca_repuesto;
      document.getElementById("Pais").value=repuesto.id_pais;
      document.getElementById("descripcion").value=repuesto.descripcion;

      if(repuesto.observaciones=="@@@"){
        document.getElementById("observaciones").value="";
        }else{
        document.getElementById("observaciones").value=repuesto.observaciones;
    }
      document.getElementById("observaciones").value=repuesto.observaciones;
      document.getElementById("medidas").value=repuesto.medidas;
      console.log("elegir_repuesto clonando es ");
        console.log(clonando?"SI":"NO");
      if(clonando){
        document.getElementById("id_repuesto_clonado").value=repuesto.id;
        document.getElementById("codigo_interno").value=0;
        document.getElementById("cod_repuesto_proveedor").value="";
        document.getElementById("pu").value=0;
        document.getElementById("stock_minimo").value=repuesto.stock_minimo;
        document.getElementById("stock_maximo").value=repuesto.stock_maximo;
        document.getElementById("elegir").value="NO";
        document.getElementById("preal").value = repuesto.precio_real;
        document.getElementById("poferta").value = repuesto.precio_venta;
      }else{
        document.getElementById("id_repuesto_clonado").value=0;
        document.getElementById("codigo_interno").value=repuesto.codigo_interno;
        document.getElementById("cod_repuesto_proveedor").value=repuesto.cod_repuesto_proveedor;
        // Asignar precio de compra con formato chileno (miles con punto, decimales con coma)
        (function(){
          var puNum = parseFloat(repuesto.precio_compra) || 0;
          // Formatear con 2 decimales y separador de miles
          var parts = puNum.toFixed(2).split('.');
          parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          document.getElementById("pu").value = parts.join(',');
          // Disparar evento input para que cualquier máscara aplicada reevalúe estado del campo
          var ev = new Event('input', { bubbles: true });
          document.getElementById("pu").dispatchEvent(ev);
        })();
        document.getElementById("elegir").value="SI";
        document.getElementById("preal").value = repuesto.precio_real;
        document.getElementById("poferta").value = repuesto.precio_venta;
      }




      document.getElementById("stock_minimo").value=repuesto.stock_minimo;
      document.getElementById("stock_maximo").value=repuesto.stock_maximo;
      document.getElementById("codigo_barras").value=repuesto.codigo_barras;
      document.getElementById("cantidad").value=0;
      document.getElementById("flete").value=0;
      document.getElementById("preciosug").value=0;
      document.getElementById("ubicacion_repuesto").value = repuesto.ubicacion !== undefined ? repuesto.ubicacion : 'SIN UBICACIÓN';

      //Cargar fotos




      //Cargar similares


      //Cargar oems


      //cargar fabricantes



      // 26 nov 2019, le pregunté a pancho y me dijo que no era necesario cargarlos luego de la primera vez
      //console.log(repuesto);
      //El focus no funciona porque al cerrar el modal, bootstrap autoenfoca
      //en el elemento que abrió el modal...
      //document.getElementById("id_repuesto").value=id_repuesto
      //document.getElementById("cantidad").select();
    }







    function buscarRepuesto()
    {
      var codigo=document.getElementById("txtBuscar").value;
      if(codigo.trim().length==0){
        Vue.swal({
                text: 'Ingrese Código Repuesto...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }



      let ef=existe_en_factura(codigo);



      if(ef){
        Vue.swal({
                text: 'Código de Repuesto ya existe en la Factura y Proveedor elegidos..',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }
      document.getElementById("cod_repuesto_proveedor").value=codigo;
      var id_prov=document.getElementById("proveedor").value;
      var bb=$('input[name="buscarpor"]:checked').val().trim();
      if(bb=="oem")
      {
        codigo="0"+codigo;
      }else{
        codigo="1"+codigo;
      }




      var url_buscar='{{url("factuprodu")}}'+'/'+codigo+'/buscarepuesto/'+id_prov; //petición

      console.log(url_buscar);


      $.ajax({
       type:'GET',
      url:url_buscar,
      success:function(resp){
        console.log(resp);
        
        //Vue.swal.close();
        $("#mostrar_repuestos").html(resp);
      //$("#mostrar_repuestos").html(resp);
      // resp[0].codigo_interno CUANDO se recibe una colección desde laravel;
      // resp.codigo_interno CUANDO se recibe un array desde laravel;




      //var ci="Cód. Interno: "+resp.codigo_interno;




        //Mostrar los datos en los campos respectivos
        /*
        if(resp==0)
        {



          alert("No existe el codigo "+codigo);



          $("#mensajes").html("No existe el codigo "+codigo);



        }else{



          var ci="Cód. Interno: "+resp[0].codigo_interno; // resp[0].codigo_interno;



          $("#codigo_interno").html(ci);



          $("#familia").val(resp[0].id_familia);



          $("#familia").change();















          //$("#mensajes").html("Encontrado...");



        }



        */

        $("#busca-repuesto-modal").modal("show");
      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        $('#mensajes').html(error.responseText);
        Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }




    });







    }







//APLICACIONES



    function cargarModelosSimilares()
    {
        var idMarcaSim=document.getElementById("MarcaSim").value;

        if(idMarcaSim!="")
        {
          document.getElementById("anios_vehiculo_sim").value="";
            var url='{{url("modelovehiculo/damepormarca")}}'+'/'+idMarcaSim;

            $.ajax({
              type:'GET',
              beforeSend: function () {
                $("#aplicaciones_msje").html("Cargando Marcas de Vehículos...");
                $("#nombre_modelo").html("<b>Modelo: </b>");
                $('#ModeloSim option').remove();
                $('#ModeloSim').append('<option value="">Buscando...</option>');
              },
              url:url,
              success:function(models){ //Viene en formato json
                $('#ModeloSim option').remove();
                var modelos=JSON.parse(models);
                $('#ModeloSim').append('<option value="">Elija un modelo</option>');
                modelos.forEach(function(modelo){
                    if(modelo.zofri==1){
                        $('#ModeloSim').append('<option value="'+modelo.id+'">ZOFRI - '+modelo.modelonombre+' \('+modelo.anios_vehiculo.trim()+'\)</option>');
                    }else{
                        $('#ModeloSim').append('<option value="'+modelo.id+'">'+modelo.modelonombre+' \('+modelo.anios_vehiculo.trim()+'\)</option>');
                    }
                });
                document.getElementById("ModeloSim").selectedIndex=0;
                $("#aplicaciones_msje").html("Listo...");
              },
              error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
              }

            });
        }else{
          $('#ModeloSim option').remove();
          $('#ModeloSim').append('<option value="">Elija una Marca</option>');
        }
    }

    function revisarCredito()



    {



      var check=document.getElementById("credito");



      var fechavenc=document.getElementById("vencefactura");



      if(check.checked)



      {



        fechavenc.disabled=false;



      }else{



        //fechavenc.value="";



        fechavenc.disabled=true;



      }



    }







    function habilitar_ingreso_items(id_compras_cab)
    {

        document.getElementById("id_factura_cab").value=id_compras_cab; //Contiene el id de compras_cab
        document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.removeAttribute("disabled"));
        document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.removeAttribute("disabled"));
        //Habilitar boton de editar la cabecera
        document.getElementById('btnEditarCabeceraFactura').removeAttribute('disabled');
        //desactivar cabecera factura
        document.getElementById("proveedor").disabled=true;
        document.getElementById("numerofactura").disabled=true;
        document.getElementById("fechafactura").disabled=true;
        document.getElementById("credito").disabled=true;
        document.getElementById("vencefactura").disabled=true;
        document.getElementById("btnGuardarCabeceraFactura").disabled=true;

        location.href="#ingreso_datos_titulo";
        document.getElementById("txtBuscar").focus();

    }







    function guardarCabeceraFactura()
    {
      var id_compras_cab = document.getElementById('id_cab_factura').value;
      var idProveedor=document.getElementById("proveedor").value;
      var numerofactura = document.getElementById("numerofactura").value;
      var fechafactura = document.getElementById("fechafactura").value;
      var esCredito=document.getElementById("credito").checked;
      var vencefactura = document.getElementById("vencefactura").value;
      var url="{{url('compras/guardarcabecera')}}";
    
      if(id_compras_cab !== ''){
        
       parametros={
            idproveedor:idProveedor,
            numerofactura:numerofactura,
            fechafactura:fechafactura,
            escredito:esCredito,
            vencefactura:vencefactura,
            id_cabecera: id_compras_cab
        };

        console.log(parametros);
     
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
        type:'POST',
        beforeSend: function () {
          espere("Guardando Factura...");
        },
        url:url,
        data:parametros,
        success:function(id_compras_cab){
          console.log(id_compras_cab);
          $('#btnEditarCabeceraFactura').removeAttr('disabled');
          $('#id_cab_factura').val(id_compras_cab);
          document.getElementById('btn_calcular_preciosug').removeAttribute('disabled');
          Vue.swal.close();

          if(id_compras_cab>0)
          {
            habilitar_ingreso_items(id_compras_cab);
          }else{
            Vue.swal({
                  title: 'ERROR',
                  text: "NO GUARDÓ CABECERA DE FACTURA (compras_cab)",
                  icon: 'error',
              });
          }

          },
          error: function(error){
            $('#mensajes').html(error.responseText);
            Vue.swal({
                  title: 'ERROR',
                  text: error.responseText,
                  icon: 'error',
              });
          }

        });
      }else{
        
       parametros={
            idproveedor:idProveedor,
            numerofactura:numerofactura,
            fechafactura:fechafactura,
            escredito:esCredito,
            vencefactura:vencefactura
        };

        console.log(parametros);
        return false;

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
        type:'POST',
        beforeSend: function () {
          espere("Guardando Factura...");
        },
        url:url,
        data:parametros,
        success:function(id_compras_cab){
          console.log(id_compras_cab);
          $('#btnEditarCabeceraFactura').removeAttr('disabled');
          $('#id_cab_factura').val(id_compras_cab);
          document.getElementById('btn_calcular_preciosug').removeAttribute('disabled');
          Vue.swal.close();

          if(id_compras_cab>0)
          {
            habilitar_ingreso_items(id_compras_cab);
          }else{
            Vue.swal({
                  title: 'ERROR',
                  text: "NO GUARDÓ CABECERA DE FACTURA (compras_cab)",
                  icon: 'error',
              });
          }

          },
          error: function(error){
            $('#mensajes').html(error.responseText);
            Vue.swal({
                  title: 'ERROR',
                  text: error.responseText,
                  icon: 'error',
              });
          }

        });

        }
      
      //var botonguardaritem=document.getElementById("btnGuardarItem");

      


    }

      function editarCabeceraFactura(){
        var url="{{url('compras/guardarcabecera')}}";
      //var botonguardaritem=document.getElementById("btnGuardarItem");
      document.getElementById('proveedor').removeAttribute('disabled');
      document.getElementById("numerofactura").disabled=false;
      document.getElementById("credito").disabled=false;
      document.getElementById("fechafactura").removeAttribute('disabled');
      document.getElementById("vencefactura").removeAttribute('disabled');
      document.getElementById('btnGuardarCabeceraFactura').removeAttribute('disabled');

      }


      //Puede usarse para borrar un item



      function confirmacion(){



        if (confirm('Esta seguro de eliminar el registro?')==true) {



          //alert('El registro ha sido  eliminado correctamente!!!');



          return true;



        }else{



          //alert('Cancelo la eliminacion');



          return false;



        }



      }







//poner onload windows y cargar familias y demas combos

// ========================================
// FUNCIÓN DE BÚSQUEDA DE REPUESTO PARA IMPORTACIÓN
// ========================================

function buscar_repuesto_importacion() {
  var codigo = document.getElementById("txtBuscar_import").value;
  if (codigo.trim().length == 0) {
    Vue.swal({
      text: 'Ingrese Código Repuesto...',
      position: 'top-end',
      icon: 'warning',
      toast: true,
      showConfirmButton: false,
      timer: 3000,
    });
    return false;
  }

  var id_prov = document.getElementById("proveedor").value;
  if (id_prov == 0 || id_prov == '') {
    Vue.swal({
      text: 'Seleccione un Proveedor primero...',
      position: 'top-end',
      icon: 'warning',
      toast: true,
      showConfirmButton: false,
      timer: 3000,
    });
    return false;
  }

  var bb = $('input[name="buscarpor_import"]:checked').val().trim();
  if (bb == "oem") {
    codigo = "0" + codigo;
  } else {
    codigo = "1" + codigo;
  }

  var url_buscar = '{{url("factuprodu")}}' + '/' + codigo + '/buscarepuesto/' + id_prov;

  console.log(url_buscar);

  $.ajax({
    type: 'GET',
    url: url_buscar,
    success: function(resp) {
      console.log(resp);
      $("#mostrar_repuestos_import").html(resp);
      
      // Reemplazar eventos de los botones "Elegir" cuando sea para importación
      $('#mostrar_repuestos_import').find('button').each(function() {
        var btn = $(this);
        var onclick_str = btn.attr('onclick');
        
        // Si el botón contiene "elegir_repuesto", modificarlo para importación
        if (onclick_str && onclick_str.includes('elegir_repuesto(')) {
          // Extraer el objeto repuesto del onclick
          var match = onclick_str.match(/elegir_repuesto\((.*)\)/);
          if (match) {
            var repuesto_str = match[1];
            btn.attr('onclick', 'elegir_repuesto_importacion(' + repuesto_str + '); return false;');
            btn.removeClass('btn-warning').addClass('btn-success');
            btn.text('Seleccionar');
          }
        }
      });
      
      $("#modal-buscar-repuesto-import").modal("show");
    },
    error: function(error) {
      $("#modal-buscar-repuesto-import").modal("hide");
      $('#mensajes').html(error.responseText);
      Vue.swal({
        title: 'ERROR',
        text: error.responseText,
        icon: 'error',
      });
    }
  });
}

// Función para llenar los datos del repuesto buscado
function elegir_repuesto_importacion(repuesto) {
  console.log(repuesto);
  
  $('#modal-buscar-repuesto-import').modal('hide');
  
  // Llenar campos de identificación
  document.getElementById("codigo_proveedor").value = repuesto.cod_repuesto_proveedor || '';
  document.getElementById("codigo").value = repuesto.codigo_interno || '';
  document.getElementById("descripcion").value = repuesto.descripcion || '';
  document.getElementById("descripcion_soft").value = repuesto.descripcion || '';
  document.getElementById("observaciones").value = repuesto.observaciones || '';
  
  // Llenar campos técnicos
  document.getElementById("marca_id").value = repuesto.id_marca_repuesto || '';
  document.getElementById("pais_id").value = repuesto.id_pais || '';
  document.getElementById("stock").value = repuesto.stock_minimo || '0';
  
  // Llenar precios
  var precio_compra = repuesto.precio_compra || '0.00';
  // Convertir formato chileno a decimal si es necesario
  precio_compra = precio_compra.toString().replace(/\./g, '').replace(/,/g, '.');
  
  document.getElementById("costo").value = precio_compra;
  document.getElementById("costo_base_flete").value = precio_compra;
  
  // Limpiar el campo de búsqueda
  document.getElementById("txtBuscar_import").value = '';
  
  // Disparar cálculo de fletes
  calcular_flete();
  
  Vue.swal({
    title: '✓ Repuesto Cargado',
    text: 'Datos del repuesto cargados: ' + repuesto.descripcion,
    icon: 'success',
    confirmButtonText: 'OK'
  });
}


window.onload = function(e){
    document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));
    // document.getElementById("#oems_rep").setAttribute("disabled", "true");
    document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));
    document.getElementById('btnEditarCabeceraFactura').setAttribute("disabled","true");
    document.querySelectorAll('#rec_alt_rep').forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll('#bendix').forEach(el => el.setAttribute("disabled", "true"));
    cargar_familia();
    cargar_fabricantes();
    cargar_marca_repuesto();
    cargar_pais();

    // APLICAR MÁSCARAS DE FORMATO CHILENO A INPUTS
    const inputsConMascara = ['cantidad', 'pu', 'subtotalitem', 'stock_minimo', 'stock_maximo', 'preciosug'];
    inputsConMascara.forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        applyMaskChile(input);
        // Formatear valor inicial si existe
        if (input.value && input.value !== '0' && input.value !== '0.00') {
          input.value = formatNumberChile(input.value);
        }
      }
    });

    $('#filtrar_modelo').keyup(function() {
        let filtro = $(this).val().toUpperCase();
        let opciones = $('#ModeloSim').find('option');
        opciones.each(function() {
            if ($(this).text().indexOf(filtro) != -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
            $('#ModeloSim').val(filtro);
        })
    });
};

function editarNombreMedida(){
    let medida_nombre = document.getElementById('medida_nombre').value;
    let id_medida = document.getElementById('medida_id').value;

    if(medida_nombre.length>0){
        let url="{{url('factuprodu/guardar_edicion_medida')}}";
        let parametros={id_medida:id_medida,medida_nombre:medida_nombre};
      // enviar header de laravel para que ajax lo acepte
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

        $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            success:function(data){
              console.log(data);
                if(data==1){
                    Vue.swal({
                        title: 'OK',
                        text: "Se editó la medida correctamente",
                        icon: 'success',
                    });
                    document.getElementById('medida_nombre').value="";
                    document.getElementById('medida_id').value="";
                    $('#modalEditarMedida').modal('hide');
                    cargar_medidas();
                }else{
                    Vue.swal({
                        title: 'ERROR',
                        text: "No se pudo editar la medida",
                        icon: 'error',
                    });
                }
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
}else{
    Vue.swal({
        title: 'ERROR',
        text: "Debe ingresar un nombre para la medida",
        icon: 'error',
    }); 
  }
}

// Validar que solo se ingrese números y decimales
function validar_solo_numeros(input) {
  let valor = input.value;
  // Reemplazar caracteres no permitidos (solo mantener números, comas y puntos)
  valor = valor.replace(/[^0-9.,]/g, '');
  // Si hay múltiples comas o puntos, dejar solo uno (el primero)
  let partesNumero = valor.split(/[.,]/);
  if (partesNumero.length > 2) {
    valor = partesNumero[0] + ',' + partesNumero.slice(1).join('');
  }
  input.value = valor;
}

// Validar que sea un porcentaje entre 0 y 100
function validar_porcentaje(input) {
  let valor = input.value;
  // Permitir solo números y un punto o coma decimal
  valor = valor.replace(/[^0-9.,]/g, '');
  
  // Si hay múltiples separadores decimales, dejar solo el primero
  let partes = valor.split(/[.,]/);
  if (partes.length > 2) {
    valor = partes[0] + ',' + partes[1];
  } else if (partes.length === 2 && partes[1]) {
    // Limitar a 2 decimales
    valor = partes[0] + ',' + partes[1].substring(0, 2);
  }
  
  input.value = valor;
  
  // Validar rango 0-100
  let numericValue = parseFloat(valor.replace(',', '.'));
  if (!isNaN(numericValue) && (numericValue < 0 || numericValue > 100)) {
    input.classList.add('is-invalid');
    console.warn('Porcentaje debe estar entre 0 y 100');
  } else {
    input.classList.remove('is-invalid');
  }
}

// ========================================
// FUNCIONES DE CÁLCULO DE FLETES
// ========================================

// Función para limpiar valores con formato chileno
function cleanNumberFlete(value) {
  if (value === '' || value === null || value === undefined) return 0;
  let str = String(value).trim();
  if (str === '') return 0;
  str = str.replace(/\./g, '').replace(',', '.');
  return parseFloat(str) || 0;
}

// Función para formatear números con formato chileno
function formatNumberFlete(value) {
  if (value === 0 || isNaN(value)) return '0.00';
  let parts = value.toFixed(2).split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return parts.join(',');
}

// Función principal para calcular fletes
function calcular_flete() {
  // Obtener valores
  let pct_asignacion = cleanNumberFlete(document.getElementById('pct_asignacion_flete').value);
  let costo_base = cleanNumberFlete(document.getElementById('costo_base_flete').value);
  // TOMAR LA CANTIDAD DE LA SECCIÓN DE INGRESO DE ITEMS
  let cantidad = cleanNumberFlete(document.getElementById('cantidad').value);

  // Actualizar el campo de referencia de cantidad
  document.getElementById('cantidad_flete_ref').value = cantidad > 0 ? cantidad.toString() : '0';

  // Validar que tengamos valores
  if (pct_asignacion === 0 || costo_base === 0 || cantidad === 0) {
    limpiar_resultados_fletes();
    return;
  }

  // CÁLCULOS
  // 1. Flete Unitario = Costo × (% Asignación / 100)
  let flete_unitario = costo_base * (pct_asignacion / 100);

  // 2. Flete Total = Flete Unitario × Cantidad
  let flete_total = flete_unitario * cantidad;

  // 3. Costo + Flete = Costo + Flete Unitario
  let costo_mas_flete = costo_base + flete_unitario;

  // MÁRGENES DE UTILIDAD (sobre Costo + Flete)
  // 30%
  let margen_30_sin_iva = costo_mas_flete * 1.30;
  let margen_30_con_iva = margen_30_sin_iva * 1.19; // 19% IVA

  // 40%
  let margen_40_sin_iva = costo_mas_flete * 1.40;
  let margen_40_con_iva = margen_40_sin_iva * 1.19;

  // 50%
  let margen_50_sin_iva = costo_mas_flete * 1.50;
  let margen_50_con_iva = margen_50_sin_iva * 1.19;

  // ACTUALIZAR HTML CON RESULTADOS
  document.getElementById('flete_unitario').value = formatNumberFlete(flete_unitario);
  document.getElementById('flete_total_calc').value = formatNumberFlete(flete_total);
  document.getElementById('costo_mas_flete').value = formatNumberFlete(costo_mas_flete);

  // Márgenes
  document.getElementById('margen_30_sin_iva').textContent = formatNumberFlete(margen_30_sin_iva);
  document.getElementById('margen_30_con_iva').textContent = formatNumberFlete(margen_30_con_iva);

  document.getElementById('margen_40_sin_iva').textContent = formatNumberFlete(margen_40_sin_iva);
  document.getElementById('margen_40_con_iva').textContent = formatNumberFlete(margen_40_con_iva);

  document.getElementById('margen_50_sin_iva').textContent = formatNumberFlete(margen_50_sin_iva);
  document.getElementById('margen_50_con_iva').textContent = formatNumberFlete(margen_50_con_iva);
}

// Función para limpiar resultados
function limpiar_resultados_fletes() {
  document.getElementById('flete_unitario').value = '0.00';
  document.getElementById('flete_total_calc').value = '0.00';
  document.getElementById('costo_mas_flete').value = '0.00';

  document.getElementById('margen_30_sin_iva').textContent = '0.00';
  document.getElementById('margen_30_con_iva').textContent = '0.00';

  document.getElementById('margen_40_sin_iva').textContent = '0.00';
  document.getElementById('margen_40_con_iva').textContent = '0.00';

  document.getElementById('margen_50_sin_iva').textContent = '0.00';
  document.getElementById('margen_50_con_iva').textContent = '0.00';
}

// Función para aplicar un margen específico
function aplicar_margen(porcentaje) {
  let costo_base = cleanNumberFlete(document.getElementById('costo_base_flete').value);
  let flete_unitario = cleanNumberFlete(document.getElementById('flete_unitario').value);
  
  if (costo_base === 0 || flete_unitario === 0) {
    Vue.swal({
      title: 'ADVERTENCIA',
      text: 'Debes calcular los fletes primero',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }

  let costo_mas_flete = costo_base + flete_unitario;
  let precio_venta = costo_mas_flete * (1 + (porcentaje / 100));

  // Mostrar confirmación
  let mensaje = `<strong>Margen: ${porcentaje}%</strong><br>
                 Costo + Flete: $${formatNumberFlete(costo_mas_flete)}<br>
                 Precio Venta S/IVA: $${formatNumberFlete(precio_venta)}<br>
                 Precio Venta C/IVA: $${formatNumberFlete(precio_venta * 1.19)}<br><br>
                 ¿Deseas aplicar este margen?`;

  Vue.swal({
    title: '✓ Aplicar Margen',
    html: mensaje,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Sí, aplicar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#dc3545'
  }).then((result) => {
    if (result.isConfirmed) {
      // Aquí puedes guardar el precio en la base de datos o hacer otra acción
      Vue.swal({
        title: '✓ Éxito',
        text: `Margen ${porcentaje}% aplicado correctamente`,
        icon: 'success',
        confirmButtonText: 'OK'
      });
    }
  });
}

  </script>

<!-- Modal para búsqueda de repuestos en importación -->
<div class="modal fade" id="modal-buscar-repuesto-import" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">🔍 Resultados de Búsqueda de Repuestos</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="mostrar_repuestos_import">
                    <p class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Buscando resultados...
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('style')

<style>

/* ========================================
   ESTILOS BASE Y GENERALES
   ======================================== */
#seccion_importacion_xml {
    scroll-margin-top: 20px;
}

#seccion_importacion_xml .card-body {
    background: #fff;
}



* {
    box-sizing: border-box;
}

html, body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.form-control-sm {
    padding: 4px 8px;
    font-size: 12px;
    height: 28px;
}

.btn-sm {
    padding: 3px 6px;
    font-size: 12px;
    height: 28px;
}

.form-group {
    margin-bottom: 12px;
}

input[type="checkbox"],
.form-check-label {
    float: left;
    line-height: 1.6em;
    height: 1.6em;
    margin: 0px 2px;
    padding: 0px;
    font-size: inherit;
}

.enlinea {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 10px;
}

.dato {
    font-size: 11px;
    color: #666;
}

#global {
    height: 60px;
    width: 85%;
    float: left;
    border: 1px solid #ddd;
    background: #f1f1f1;
    overflow-y: scroll;
}

/* ========================================
   CONTENEDOR PRINCIPAL Y ESTRUCTURA
   ======================================== */

.contenedor {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
    background-color: #f8f9fa;
}

.g-titulo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.titulazo {
    margin: 0;
    color: #333;
    font-weight: 600;
    font-size: 24px;
}

/* ========================================
   CABECERA FACTURA
   ======================================== */

.g-cabecera {
    background: linear-gradient(135deg, #D0FFFF 0%, #E0FFFF 100%);
    padding: 20px;
    border: 2px solid #00BCD4;
    border-radius: 8px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#titulo-factura {
    grid-column: 1 / -1;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

#btnGuardarCabeceraFactura-div {
    grid-column: 1 / -1;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-bottom: 10px;
}

#btnEditarCabeceraFactura {
    margin-left: auto;
}

.g-cabecera .form-group {
    margin: 0;
}

.g-cabecera .form-group.floating {
    display: flex;
    flex-direction: column;
}

.g-cabecera .form-control {
    border: 1px solid #00BCD4;
    border-radius: 4px;
}

.g-cabecera .form-control:focus {
    border-color: #0097A7;
    box-shadow: 0 0 0 0.2rem rgba(0, 188, 212, 0.25);
}

/* ========================================
   SECCIÓN DE REPUESTOS
   ======================================== */

.g-repuestos {
    background-color: #F2F5A9;
    padding: 15px;
    border: 2px solid #FBC02D;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.g-repuestos-titulo {
    background-color: #f0f0f0 !important;
    padding: 12px !important;
    margin: 0 0 20px 0 !important;
    border-left: 4px solid #28a745 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-radius: 4px;
}

.g-repuestos-titulo p {
    margin: 0 !important;
    color: #333;
    font-weight: 600;
}

/* ========================================
   ZONA INGRESO REPUESTO - SECCIONES
   ======================================== */

#zona_ingreso_repuesto {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.g-repuestos-seccion {
    background-color: #ffffff;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.g-repuestos-seccion h6 {
    background-color: #f0f0f0;
    padding: 8px;
    margin: 0 0 15px 0;
    border-left: 4px solid;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.g-repuestos-seccion:nth-child(1) h6 {
    border-left-color: #28a745;
}

.g-repuestos-seccion:nth-child(2) h6 {
    border-left-color: #17a2b8;
}

.g-repuestos-seccion:nth-child(3) h6 {
    border-left-color: #ffc107;
}

.g-repuestos-familia {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    align-items: start;
}

.g-repuestos-familia .form-group {
    margin: 0;
}

.g-repuestos-familia .form-control,
.g-repuestos-familia .form-control.floating {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    font-size: 13px;
}

.g-repuestos-familia .form-control:focus,
.g-repuestos-familia .form-control.floating:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-group.floating {
    position: relative;
}

.form-group.floating .form-control,
.form-group.floating input,
.form-group.floating select,
.form-group.floating textarea {
    width: 100%;
    padding: 8px 8px 8px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    background-color: #fff;
}

.form-group.floating .form-control:focus,
.form-group.floating input:focus,
.form-group.floating select:focus,
.form-group.floating textarea:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-group.floating label {
    position: absolute;
    top: -10px;
    left: 10px;
    background-color: #fff;
    padding: 0 3px;
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

.form-group.floating input:focus ~ label,
.form-group.floating select:focus ~ label,
.form-group.floating textarea:focus ~ label {
    color: #007bff;
}

/* ========================================
   ZONA INGRESO ITEMS FACTURA
   ======================================== */

#zona_ingreso_items_factura {
    background-color: #fff;
    padding: 15px;
    border: 2px solid #dc3545;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#titulo-item-factura {
    background-color: #f0f0f0;
    padding: 12px;
    margin: 0 0 20px 0;
    border-left: 4px solid #dc3545;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 4px;
}

#titulo-item-factura p {
    margin: 0;
    color: #333;
    font-weight: 600;
}

/* Grid de items de factura - Responsive */
#zona_ingreso_items_factura > div:not(#titulo-item-factura) {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

#zona_ingreso_items_factura .form-group {
    margin: 0;
}

#zona_ingreso_items_factura .form-group label {
    display: block;
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 5px;
    color: #333;
}

#zona_ingreso_items_factura .form-group input,
#zona_ingreso_items_factura .form-group select {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

#zona_ingreso_items_factura .form-group input:focus,
#zona_ingreso_items_factura .form-group select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

#zona_ingreso_items_factura input[readonly] {
    background-color: #f5f5f5;
    color: #666;
}

/* ========================================
   SECCIONES DE FOTOS Y APLICACIONES
   ======================================== */

.g-fotos,
.g-aplicaciones,
.g-oems,
.g-fabricantes {
    background-color: #D0FFFF;
    padding: 15px;
    border: 2px solid #00BCD4;
    border-radius: 8px;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.g-fotos h6,
.g-aplicaciones h6,
.g-oems h6,
.g-fabricantes h6 {
    background-color: #f0f0f0;
    padding: 8px;
    margin: 0 0 15px 0;
    border-left: 4px solid #17a2b8;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.fotos_msje,
.aplicaciones_msje,
.oems_msje,
.fabricantes_msje {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

/* ========================================
   BOTONES Y CONTROLES
   ======================================== */

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0051a8;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #333;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bb2d3b;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */

@media (max-width: 1200px) {
    .g-repuestos-familia {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
    
    .g-cabecera {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .contenedor {
        padding: 10px;
        gap: 15px;
    }
    
    .g-repuestos-familia {
        grid-template-columns: 1fr;
    }
    
    .g-cabecera {
        grid-template-columns: 1fr;
    }
    
    #btnGuardarCabeceraFactura-div {
        flex-direction: column;
    }
    
    #btnGuardarCabeceraFactura-div button {
        width: 100%;
    }
    
    #zona_ingreso_items_factura > div:not(#titulo-item-factura) {
        grid-template-columns: 1fr;
    }
}



/* Encabezado fijo para importación XML */
#tabla_resumen_importacion thead th {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #343a40 !important;
    color: #fff !important;
    box-shadow: 0 2px 3px rgba(0, 0, 0, .20);
    white-space: nowrap;
}

#tabla_resumen_importacion {
    margin-bottom: 0;
}

/* Filas con precios editados manualmente */
#tabla_resumen_importacion tr.fila-precio-modificado td {
    background-color: #fff3cd !important;
    box-shadow: inset 4px 0 0 #f0ad4e;
}

#tabla_resumen_importacion tr.fila-precio-bajo td {
    background-color: #f8d7da !important;
    box-shadow: inset 4px 0 0 #dc3545;
}

#tabla_resumen_importacion tr.fila-precio-modificado input,
#tabla_resumen_importacion tr.fila-precio-modificado select {
    background-color: #fff8e1 !important;
    border-color: #f0ad4e !important;
}

#tabla_resumen_importacion tr.fila-precio-bajo input,
#tabla_resumen_importacion tr.fila-precio-bajo select {
    background-color: #fde2e4 !important;
    border-color: #dc3545 !important;
}

#tabla_resumen_importacion tr.fila-precio-bajo .precio_venta_neto,
#tabla_resumen_importacion tr.fila-precio-bajo .precio_venta_iva {
    color: #9f1239 !important;
    font-weight: 700 !important;
}

#tabla_resumen_importacion .precio-modificado {
    font-weight: 700 !important;
}

</style>

@endsection













@section('contenido_ingresa_datos')
<div class="container-fluid">
        <div id="mensajes">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {!! session('error') !!}
                </div>
            @endif
        </div>
        <div class="contenedor">
            <div class="g-titulo">
                <div class="w-100">
                    <center>
                        <h4 class="titulazo">Ingreso por Compras</h4>
                    </center>
                </div>
                <div style="text-align: right;">
                    <a href="{{ url('factuprodu/crear') }}" id="btnNuevaFactura" class="btn btn-success btn-sm">Nueva
                        Factura</a>
                </div>

            </div> {{--  FIN de titulo --}}

            <!-- CAMPOS OCULTOS -->
            <input type="hidden" id="id_factura_cab">
            <input type="hidden" id="id_repuesto">
            <input type="hidden" id="id_repuesto_clonado">
            <input type="hidden" id="elegir" value="NO">
            <input type="hidden" id="utilidad">
            <!-- <input type="hidden" id="codigo_interno" value="0"> -->


            <!-- FIN DE CAMPOS OCULTOS -->

            <div id="seccion_importacion_xml" class="card mb-3 shadow-sm border-info">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <strong>Importar productos desde XML</strong>
                    <button type="button" class="btn btn-light btn-sm" onclick="limpiarImportacionXml()">Limpiar</button>
                </div>

                <div class="card-body">
                    <form id="form_importar_productos" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="tipo_archivo" id="tipo_archivo" value="xml">
                        <input type="hidden" name="id_proveedor" id="import_id_proveedor">
                        <input type="hidden" name="numero_factura" id="import_numero_factura">
                        <input type="hidden" name="fecha_factura" id="import_fecha_factura">
                        <input type="hidden" name="fecha_vencimiento" id="import_fecha_vencimiento">

                        <div class="alert alert-info mb-3">
                            Seleccione el archivo XML de la factura. El sistema leerá automáticamente el proveedor,
                            número de factura, fecha, totales y productos.
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <div class="form-group mb-2">
                                    <label>Archivo XML</label>
                                    <input type="file"
                                           name="archivo"
                                           id="archivo_importacion"
                                           class="form-control-file"
                                           accept=".xml"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-4 text-md-right">
                                <button type="button" class="btn btn-success btn-sm mb-2" onclick="importarProductosFactura()">
                                    Leer XML
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="resultado_importacion" class="mt-3"></div>
                </div>
            </div>
            
        </div>
</div>
@endsection
