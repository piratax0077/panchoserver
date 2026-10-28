(function () {
    'use strict';
    const productos = new Map();
    let resultados = new Map();
    let lote = null;
    let buscando = false;
    let guardando = false;
    const canales = {1:'Solo local',2:'Solo web',3:'Local + web',4:'Solo tótem',5:'Local + tótem',6:'Web + tótem'};
    const dinero = valor => '$ ' + Number(valor).toLocaleString('es-CL', {maximumFractionDigits:0});
    const texto = valor => $('<span>').text(valor == null ? '' : valor).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    window.mostrar_descuentos_familia = function (descuentos) {
        const cuerpo = $('#tbody_descuentos').empty();
        descuentos.forEach(d => {
            const id = Number(d.id);
            const fila = $('<tr>');
            const imagen = $('<td>');
            if (d.image_path) imagen.append($('<img>', {src:'{{route('storage.public', [], false)}}?path=' + encodeURIComponent('imagenes/familias/' + d.image_path), alt:'Referencia de familia', class:'logoHeader'}));
            fila.append(imagen);
            fila.append($('<td>').append($('<button>', {type:'button',class:'btn btn-link btn-sm'}).text(d.nombrefamilia).on('click', function () { editar_descuento_familia(id); })));
            [d.porcentaje + ' %', d.desde, d.hasta, d.activo == 1 ? 'Vigente' : 'Caducado', canales[d.id_local] || 'Sin canal'].forEach(valor => fila.append($('<td>').text(valor)));
            fila.append($('<td>').append($('<button>', {type:'button',class:'btn btn-outline-danger btn-sm'}).text('Eliminar').on('click', function () {
                Vue.swal({icon:'warning',title:'Eliminar descuento de familia',text:d.nombrefamilia,showCancelButton:true,confirmButtonText:'Eliminar',cancelButtonText:'Cancelar'}).then(function (res) { if (res.isConfirmed || res.value === true) eliminar_descuento(id); });
            })));
            cuerpo.append(fila);
        });
        if (!descuentos.length) cuerpo.append('<tr><td colspan="8" class="ofertas-empty">No hay descuentos por familia.</td></tr>');
    };
    function errorAjax(xhr) {
        const datos = xhr.responseJSON || {};
        if (datos.errors) return Object.values(datos.errors).reduce((todos, mensajes) => todos.concat(mensajes), []).join(' ');
        return xhr.status === 403 ? 'No tiene permiso para modificar ofertas.' : xhr.status === 419 ? 'Su sesión venció. Recargue la página.' : datos.message || 'No se pudo completar la operación. Intente nuevamente.';
    }
    function filas() { return Array.from(document.querySelectorAll('#tbody_nuevas_ofertas tr')); }
    function actualizarSeleccion() {
        const visibles = filas().filter(fila => !fila.hidden);
        const seleccionadas = visibles.filter(fila => fila.querySelector('.seleccion-oferta').checked);
        $('#cantidad_seleccionadas').text(seleccionadas.length + ' seleccionadas');
        $('#btn_editar_lote').prop('disabled', !seleccionadas.length);
        $('#cantidad_visibles').text(visibles.length + ' ofertas visibles');
        const todas = document.getElementById('seleccionar_visibles');
        todas.checked = visibles.length > 0 && seleccionadas.length === visibles.length;
        todas.indeterminate = seleccionadas.length > 0 && seleccionadas.length < visibles.length;
        filas().forEach(fila => fila.classList.toggle('oferta-seleccionada', fila.querySelector('.seleccion-oferta').checked));
        $('#ofertas_sin_resultados').toggleClass('d-none', visibles.length > 0);
    }
    function filtrar() {
        const consulta = $('#filtro_ofertas').val().trim().toLocaleLowerCase('es');
        const estado = $('#filtro_estado').val();
        const canal = $('#filtro_canal').val();
        filas().forEach(fila => {
            const datos = fila.dataset;
            fila.hidden = !(String(datos.codigo + ' ' + datos.descripcion + ' ' + datos.usuario).toLocaleLowerCase('es').includes(consulta) && (!estado || estado === datos.estado) && (!canal || canal === datos.local));
            if (fila.hidden) fila.querySelector('.seleccion-oferta').checked = false;
        });
        actualizarSeleccion();
    }
    window.limpiar_seleccion = function () {
        $('.seleccion-oferta').prop('checked', false);
        actualizarSeleccion();
    };
    function dibujarProductos() {
        const cuerpo = $('#productos_lote').empty();
        productos.forEach(producto => {
            cuerpo.append('<tr><td><strong>' + texto(producto.codigo) + '</strong><br>' + texto(producto.descripcion) + '</td><td>' + dinero(producto.precio) + '</td><td><button type="button" class="btn btn-outline-secondary btn-sm quitar-producto" data-id="' + producto.id + '" aria-label="Quitar producto ' + texto(producto.codigo) + '">Quitar</button></td></tr>');
        });
        if (!productos.size) cuerpo.append('<tr><td class="ofertas-empty">Busque y agregue productos para comenzar.</td></tr>');
        $('#cantidad_productos').text(productos.size);
        $('#btn_crear_lote').prop('disabled', !productos.size);
        $('#tbody_resultados .agregar-producto').each(function () {
            const agregado = productos.has(Number(this.dataset.id));
            $(this).prop('disabled', agregado).text(agregado ? 'Agregado' : 'Agregar');
        });
    }
    window.vaciar_productos = function () { productos.clear(); dibujarProductos(); };
    window.buscar_repuesto = function () {
        if (buscando) return;
        const codigo = $('#codigo_repuesto').val().trim();
        if (!codigo) { $('#mensajes').text('Ingrese el código del repuesto.'); return; }
        buscando = true;
        $('#btn_buscar_ofertas').prop('disabled', true);
        $('#mensajes').text('Buscando productos...');
        $('#busqueda_resultado').addClass('d-none');
        $.ajax({type:'POST', url:'/ventas/damerepuesto_oferta', data:{codigo_repuesto:codigo, option:$('input[name=flexRadioDefault]:checked').val()}}).done(function (resp) {
            if (!Array.isArray(resp) || resp[0] === 'error' || !Array.isArray(resp[0])) {
                $('#mensajes').text(Array.isArray(resp) && resp[0] === 'error' ? resp[1] : 'No se pudo obtener el resultado de la búsqueda.');
                return;
            }
            resultados = new Map();
            const cuerpo = $('#tbody_resultados').empty();
            resp[0].forEach(producto => {
                const id = Number(producto.id);
                resultados.set(id, {id:id, codigo:producto.codigo_interno, descripcion:producto.descripcion, precio:Number(producto.precio_venta)});
                const existente = filas().some(fila => Number(fila.dataset.producto) === id);
                cuerpo.append('<tr><td><strong>' + texto(producto.codigo_interno) + '</strong><br>' + texto(producto.descripcion) + '<br><small>' + texto(producto.marcarepuesto) + ' · Stock: ' + texto(producto.stock_actual) + '</small></td><td>' + dinero(producto.precio_venta) + '</td><td>' + (existente ? '<span class="ofertas-muted">Ya tiene oferta</span>' : '<button type="button" class="btn btn-outline-primary btn-sm agregar-producto" data-id="' + id + '">Agregar</button>') + '</td></tr>');
            });
            $('#busqueda_resultado').removeClass('d-none');
            $('#mensajes').text(resp[0].length + ' productos encontrados. Elija cuáles agregar.');
            dibujarProductos();
        }).fail(function (xhr) { $('#mensajes').text(errorAjax(xhr)); }).always(function () {
            buscando = false;
            $('#btn_buscar_ofertas').prop('disabled', false);
        });
    };
    function ofertaDeFila(fila) {
        const d = fila.dataset;
        return {id:Number(d.id), codigo:d.codigo, descripcion:d.descripcion, precio:Number(d.precio), actualizado:Number(d.actualizado), desde:d.desde, hasta:d.hasta, local:d.local};
    }
    window.editar_oferta = function (id) {
        const fila = filas().find(fila => Number(fila.dataset.id) === Number(id));
        if (fila) prepararLote('editar', [ofertaDeFila(fila)]);
    };
    window.abrir_lote = function (accion) {
        const items = accion === 'crear' ? Array.from(productos.values()) : filas().filter(fila => !fila.hidden && fila.querySelector('.seleccion-oferta').checked).map(ofertaDeFila);
        if (items.length) prepararLote(accion, items);
    };
    function prepararLote(accion, items) {
        if (items.length > 500) { Vue.swal({icon:'warning',text:'Seleccione como máximo 500 ofertas por operación.'}); return; }
        lote = {accion:accion, items:items};
        $('#lote_desde, #lote_hasta, #lote_valor').val('');
        $('#lote_local').val(accion === 'crear' ? '2' : '');
        $('#lote_tipo').val(accion === 'crear' ? 'porcentaje' : '');
        $('#lote_valor').prop('disabled', accion !== 'crear');
        $('#titulo_lote').text((accion === 'crear' ? 'Crear' : 'Editar') + ' ' + items.length + (items.length === 1 ? ' oferta' : ' ofertas'));
        $('#ayuda_lote').text(accion === 'crear' ? 'Complete las fechas, el canal y el descuento para todos los productos seleccionados.' : 'Complete solo los datos que desea cambiar. Los campos vacíos conservan el valor de cada oferta.');
        $('#error_lote').addClass('d-none').text('');
        $('#btn_guardar_lote').prop('disabled', false).text(accion === 'crear' ? 'Crear ofertas' : 'Aplicar cambios');
        actualizarPreview();
        $('#modalLoteOfertas').modal('show');
    }
    function datosLote() {
        const datos = {accion:lote.accion, ids:lote.items.map(item => item.id)};
        ['desde','hasta','local'].forEach(campo => { const valor = $('#lote_' + campo).val(); if (valor) datos[campo] = valor; });
        const tipo = $('#lote_tipo').val();
        if (tipo) { datos.tipo_descuento = tipo; datos.valor_descuento = $('#lote_valor').val(); }
        return datos;
    }
    function calcularPreview(item, datos) {
        const desde = datos.desde || item.desde;
        const hasta = datos.hasta || item.hasta;
        let actualizado = lote.accion === 'crear' ? item.precio : item.actualizado;
        let error = '';
        if (desde && hasta && hasta < desde) error = 'La fecha final debe ser igual o posterior a la inicial.';
        if (datos.tipo_descuento) {
            const valor = Number(datos.valor_descuento);
            const descuento = Math.round(datos.tipo_descuento === 'porcentaje' ? Math.round(item.precio) * valor / 100 : valor);
            actualizado = Math.round(item.precio) - descuento;
            if (!Number.isFinite(valor) || valor <= 0 || (datos.tipo_descuento === 'porcentaje' && valor >= 100) || descuento <= 0 || actualizado <= 0) error = 'El descuento debe dejar un precio mayor a cero en todos los productos.';
        }
        return {desde:desde, hasta:hasta, local:datos.local || item.local, actualizado:actualizado, error:error};
    }
    function actualizarPreview() {
        if (!lote) return;
        const datos = datosLote();
        const cuerpo = $('#preview_lote').empty();
        lote.items.forEach(item => {
            const preview = calcularPreview(item, datos);
            cuerpo.append('<tr' + (preview.error ? ' class="table-danger"' : '') + '><td>' + texto(item.codigo) + '<br><small>' + texto(item.descripcion) + '</small></td><td>' + dinero(item.precio) + '</td><td>' + dinero(preview.actualizado) + '</td><td>' + texto(preview.desde || 'Pendiente') + '<br>' + texto(preview.hasta || 'Pendiente') + '</td><td>' + texto(canales[preview.local] || 'Pendiente') + '</td></tr>');
        });
        $('#resumen_lote').text('(' + lote.items.length + ')');
    }
    function validarLote(datos) {
        if (lote.accion === 'crear' && (!datos.desde || !datos.hasta || !datos.local || !datos.tipo_descuento)) return 'Complete fechas, canal y descuento.';
        if (lote.accion === 'editar' && !datos.desde && !datos.hasta && !datos.local && !datos.tipo_descuento) return 'Indique al menos un dato para modificar.';
        for (const item of lote.items) { const preview = calcularPreview(item, datos); if (preview.error) return item.codigo + ': ' + preview.error; }
        return '';
    }
    window.guardar_lote = function () {
        if (!lote || guardando) return;
        const datos = datosLote();
        const error = validarLote(datos);
        if (error) { $('#error_lote').text(error).removeClass('d-none'); return; }
        $('#error_lote').addClass('d-none');
        const cambios = [];
        if (datos.desde) cambios.push('Desde: ' + datos.desde);
        if (datos.hasta) cambios.push('Hasta: ' + datos.hasta);
        if (datos.local) cambios.push('Canal: ' + canales[datos.local]);
        if (datos.tipo_descuento) cambios.push('Descuento: ' + (datos.tipo_descuento === 'porcentaje' ? datos.valor_descuento + '%' : dinero(datos.valor_descuento)));
        guardando = true;
        $('#btn_guardar_lote').prop('disabled', true);
        Vue.swal({icon:'question',title:'Aplicar a ' + datos.ids.length + (datos.ids.length === 1 ? ' oferta' : ' ofertas'),text:cambios.join(' · '),showCancelButton:true,confirmButtonText:'Aplicar',cancelButtonText:'Cancelar'}).then(function (respuesta) {
            if (!(respuesta.isConfirmed || respuesta.value === true)) { guardando = false; $('#btn_guardar_lote').prop('disabled', false); return; }
            $('#btn_guardar_lote').text('Guardando...');
            $.ajax({type:'POST',url:'/ventas/ofertas/lote',data:datos}).done(function (resp) {
                guardando = false;
                $('#modalLoteOfertas').modal('hide');
                Vue.swal({icon:'success',text:resp.mensaje}).then(function () { window.location.reload(); });
            }).fail(function (xhr) {
                $('#error_lote').text(errorAjax(xhr)).removeClass('d-none');
            }).always(function () { guardando = false; $('#btn_guardar_lote').prop('disabled', false).text('Aplicar cambios'); });
        }).catch(function () { guardando = false; $('#btn_guardar_lote').prop('disabled', false); });
    };
    window.eliminar_oferta = function (id) {
        const fila = filas().find(fila => Number(fila.dataset.id) === Number(id));
        Vue.swal({icon:'warning',title:'Eliminar oferta',text:'Se quitará la oferta del producto ' + (fila ? fila.dataset.codigo : id) + '.',showCancelButton:true,confirmButtonText:'Eliminar',cancelButtonText:'Cancelar'}).then(function (respuesta) {
            if (!(respuesta.isConfirmed || respuesta.value === true)) return;
            $.ajax({type:'GET',url:'/ventas/eliminar_oferta/' + id}).done(function (resp) {
                if (!Array.isArray(resp)) { Vue.swal({icon:'error',text:'No se pudo eliminar la oferta.'}); return; }
                window.location.reload();
            }).fail(function (xhr) { Vue.swal({icon:'error',text:errorAjax(xhr)}); });
        });
    };
    $(function () {
        $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}});
        $('#filtro_ofertas').on('input', filtrar);
        $('#filtro_estado, #filtro_canal').on('change', filtrar);
        $('#seleccionar_visibles').on('change', function () { const checked = this.checked; filas().filter(fila => !fila.hidden).forEach(fila => { fila.querySelector('.seleccion-oferta').checked = checked; }); actualizarSeleccion(); });
        $('#tbody_nuevas_ofertas').on('change', '.seleccion-oferta', actualizarSeleccion);
        $('#tbody_resultados').on('click', '.agregar-producto', function () { const id = Number(this.dataset.id); if (resultados.has(id)) productos.set(id, resultados.get(id)); dibujarProductos(); });
        $('#productos_lote').on('click', '.quitar-producto', function () { productos.delete(Number(this.dataset.id)); dibujarProductos(); });
        $('#lote_desde, #lote_hasta, #lote_local, #lote_valor').on('input change', actualizarPreview);
        $('#lote_tipo').on('change', function () { const mantener = !this.value; $('#lote_valor').prop('disabled', mantener); if (mantener) $('#lote_valor').val(''); actualizarPreview(); });
        $('#modalLoteOfertas').on('hide.bs.modal', function (evento) { if (guardando) evento.preventDefault(); });
        // Family management uses the same network error feedback as product offers.
        $(document).ajaxError(function (event, xhr, settings) {
            if (/familiascondescuento|guardar_dcto_familia|editar_descuento_familia|editar_nueva_familia/.test(settings.url)) {
                Vue.swal({icon:'error', text:errorAjax(xhr)});
            }
        });
        filtrar();
        dibujarProductos();
    });
})();
