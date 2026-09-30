@extends('plantillas.app')
@section('titulo','Stock por ubicación')
@section('contenido_titulo_pagina')
<div class="d-flex justify-content-between align-items-center">
    <div><h4>Stock de repuestos</h4><p class="text-muted">Existencias, ubicaciones físicas y movimientos por local.</p></div>
    @if(Auth::user()->rol->nombrerol === 'Administrador')
    <button class="btn btn-outline-primary" id="gestionar-locales">Administrar locales</button>
    @endif
</div>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid py-3" id="stock-app">
    <form id="buscar-stock" class="form-inline mb-4">
        <label for="codigo-stock" class="mr-2">Código interno, proveedor u OEM</label>
        <input id="codigo-stock" class="form-control mr-2" required maxlength="100" autocomplete="off">
        <button class="btn btn-primary">Buscar</button>
    </form>
    <div id="stock-mensaje" role="status" aria-live="polite"></div>
    <div id="stock-resultados"></div>
</div>
<div class="modal fade" id="stock-dialogo" tabindex="-1" role="dialog" aria-labelledby="stock-dialogo-titulo">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form id="stock-formulario">
            <div class="modal-header"><h5 id="stock-dialogo-titulo" class="modal-title"></h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button></div>
            <div class="modal-body" id="stock-dialogo-cuerpo"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button id="stock-guardar" class="btn btn-primary">Guardar</button></div>
        </form>
    </div></div>
</div>
@endsection
@section('javascript')
<script>
$(function () {
    const base = @json(url('/inventario/ubicaciones'));
    const admin = @json(Auth::user()->rol->nombrerol === 'Administrador');
    let catalogo = {locales: [], responsables: []}, productos = [], enviar = null, buscando = false;
    const esc = value => $('<span>').text(value == null ? '' : String(value)).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const api = (ruta, metodo = 'GET', data = {}) => $.ajax({
        url: base + ruta, method: metodo, data: data, dataType: 'json',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json'}
    });
    function error(e) {
        const datos = e.responseJSON || {};
        const mensaje = datos.errors ? Object.values(datos.errors).flat().join(' ') : (datos.message || e.message || 'No fue posible completar la operación.');
        $('#stock-mensaje').attr('class', 'alert alert-danger').text(mensaje);
        $('#stock-dialogo-error').text(mensaje);
    }
    function modal(titulo, cuerpo, accion) {
        enviar = accion;
        $('#stock-dialogo-titulo').text(titulo);
        $('#stock-dialogo-cuerpo').html('<div id="stock-dialogo-error" class="text-danger mb-2" role="alert"></div>' + cuerpo);
        $('#stock-guardar').toggle(!!accion).prop('disabled', false);
        $('#stock-dialogo').modal('show');
    }
    function locales(selected, excluir) {
        return catalogo.locales.filter(l => l.activo && Number(l.id) !== Number(excluir)).map(l =>
            '<option value="' + l.id + '"' + (Number(l.id) === Number(selected) ? ' selected' : '') + '>' + esc(l.local_nombre) + '</option>').join('');
    }
    function responsables(selected) {
        return '<option value="">Sin responsable</option>' + catalogo.responsables.map(r =>
            '<option value="' + r.id + '"' + (Number(r.id) === Number(selected) ? ' selected' : '') + '>' + esc(r.name) + '</option>').join('');
    }
    function campo(label, input) { return '<div class="form-group"><label>' + label + input + '</label></div>'; }
    function motivo() { return campo('Motivo', '<textarea name="motivo" class="form-control" required minlength="5" maxlength="500" rows="3"></textarea>'); }
    function render() {
        $('#stock-resultados').html(productos.map(p => {
            const total = p.stocks.reduce((n, s) => n + s.cantidad, 0);
            return '<section class="card mb-4"><div class="card-header d-flex justify-content-between flex-wrap"><div><h5>' +
                esc(p.codigo_interno) + ' · ' + esc(p.descripcion) + '</h5><span>Stock total: <b>' + total + '</b> · ' + p.stocks.length +
                ' ubicaciones</span></div>' + (admin ? '<button class="btn btn-outline-primary accion-stock" data-accion="asociar" data-producto="' + p.id + '">Agregar ubicación</button>' : '') +
                '</div><div class="card-body"><div class="row">' + p.stocks.map(s => {
                    const activo = s.local && s.local.activo;
                    const botones = admin && activo ? ['ajustar', 'editar', 'trasladar'] : [];
                    botones.push('historial');
                    return '<div class="col-12 col-md-6 col-xl-4 mb-3"><article class="border rounded p-3 h-100"><h5>' +
                        esc(s.local ? s.local.local_nombre : 'Local no disponible') + (!activo ? ' (inactivo)' : '') +
                        '</h5><div class="display-4 text-primary">' + s.cantidad + '</div><small>unidades</small><hr><p><b>Ubicación física:</b> ' +
                        esc(s.ubicacion_fisica || 'Sin asignar') + '</p><p><b>Responsable:</b> ' + esc(s.responsable ? s.responsable.name : 'Sin asignar') +
                        '</p><div class="d-flex flex-wrap">' + botones.map(a => '<button class="btn btn-sm btn-outline-primary mr-1 mb-1 accion-stock" data-accion="' + a +
                        '" data-producto="' + p.id + '" data-stock="' + s.id + '">' + ({ajustar:'Ajustar stock',editar:'Editar ubicación',trasladar:'Trasladar',historial:'Historial'}[a]) +
                        '</button>').join('') + '</div></article></div>';
                }).join('') + (p.stocks.length ? '' : '<p class="text-muted px-3">Este repuesto todavía no tiene ubicaciones asignadas.</p>') + '</div></div></section>';
        }).join(''));
    }
    async function buscar() {
        if (buscando) return;
        buscando = true;
        $('#buscar-stock button').prop('disabled', true);
        $('#stock-mensaje').attr('class', 'text-muted').text('Buscando…');
        try {
            const data = await api('/buscar', 'GET', {codigo: $('#codigo-stock').val().trim()});
            productos = data.repuestos;
            render();
            $('#stock-mensaje').text(productos.length ? '' : 'No se encontraron repuestos activos.');
        } catch(e) { error(e); }
        finally { buscando = false; $('#buscar-stock button').prop('disabled', false); }
    }
    $('#buscar-stock').on('submit', e => { e.preventDefault(); buscar(); });
    $('#stock-formulario').on('submit', async function(e) {
        e.preventDefault();
        if (!enviar || $('#stock-guardar').prop('disabled')) return;
        $('#stock-guardar').prop('disabled', true);
        const datos = Object.fromEntries(new FormData(this).entries());
        try {
            const completado = await enviar(datos);
            if (completado === false) return;
            $('#stock-dialogo').modal('hide');
            catalogo = await api('/catalogo');
            if ($('#codigo-stock').val().trim()) await buscar();
        } catch(e) { error(e); }
        finally { $('#stock-guardar').prop('disabled', false); }
    });
    $('#stock-resultados').on('click', '.accion-stock', async function() {
        const p = productos.find(p => p.id == $(this).data('producto'));
        const s = p.stocks.find(s => s.id == $(this).data('stock'));
        const accion = $(this).data('accion'), ruta = '/repuestos/' + p.id;
        if (accion === 'asociar' || accion === 'editar') {
            const disponibles = catalogo.locales.filter(l => l.activo && (s || !p.stocks.some(x => x.local_id == l.id)));
            if (!s && !disponibles.length) { error({message:'Todos los locales activos ya están asociados. Puede crear otro en Administrar locales.'}); return; }
            modal(s ? 'Editar ubicación' : 'Agregar ubicación', (s ?
                '<input type="hidden" name="local_id" value="' + s.local_id + '"><p>' + esc(s.local.local_nombre) + '</p>' :
                campo('Local', '<select name="local_id" class="form-control" required>' + disponibles.map(l => '<option value="' + l.id + '">' + esc(l.local_nombre) + '</option>').join('') + '</select>')) +
                campo('Posición física (piso, pasillo, estante)', '<input name="ubicacion_fisica" class="form-control" maxlength="255" value="' + esc(s ? s.ubicacion_fisica : '') + '">') +
                campo('Responsable', '<select name="responsable_id" class="form-control">' + responsables(s ? s.responsable_id : null) + '</select>'),
                d => api(ruta + '/asociar', 'POST', d));
        } else if (accion === 'ajustar') {
            modal('Ajustar stock · ' + s.local.local_nombre, '<p>Stock registrado: <b>' + s.cantidad + '</b></p>' +
                campo('Nueva cantidad', '<input name="cantidad" type="number" min="0" max="2147483647" step="1" required class="form-control" value="' + s.cantidad + '">') +
                '<p id="stock-diferencia">Diferencia: 0</p>' + motivo() +
                campo('Clave de autorización (solo si disminuye)', '<input name="clave" type="password" autocomplete="off" class="form-control">'),
                async d => {
                    if (Number(d.cantidad) < s.cantidad) {
                        const respuesta = await $.ajax({url:@json(url('/clave')), method:'POST', data:{clave:d.clave,accion:'disminuir_stock'}, headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
                        if (respuesta !== 'OK') throw new Error('La clave ingresada no es válida.');
                    }
                    return api(ruta + '/ajustar', 'POST', {local_id:s.local_id,cantidad:d.cantidad,stock_esperado:s.cantidad,motivo:d.motivo});
                });
            $('#stock-dialogo-cuerpo input[name="cantidad"]').on('input', function() { $('#stock-diferencia').text('Diferencia: ' + (Number(this.value) - s.cantidad)); });
        } else if (accion === 'trasladar') {
            modal('Trasladar desde ' + s.local.local_nombre,
                '<p>Disponible: <b>' + s.cantidad + '</b> unidades</p>' +
                campo('Local de destino', '<select class="form-control" name="destino" required>' + locales(null, s.local_id) + '</select>') +
                campo('Cantidad', '<input name="cantidad" type="number" min="1" max="' + s.cantidad + '" step="1" required class="form-control">') + motivo(),
                d => api(ruta + '/trasladar', 'POST', {...d, origen:s.local_id}));
        } else {
            try { await historial(ruta, s, 1); } catch(e) { error(e); }
        }
    });
    async function historial(ruta, s, pagina) {
        const data = await api(ruta + '/historial', 'GET', {local_id:s.local_id,page:pagina});
        modal('Historial · ' + s.local.local_nombre, '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Tipo</th><th>Antes</th><th>Cambio</th><th>Después</th><th>Usuario y motivo</th></tr></thead><tbody>' +
            data.data.map(m => '<tr><td>' + esc(m.created_at) + '</td><td>' + esc(m.tipo) + '</td><td>' + m.stock_anterior + '</td><td>' + m.cantidad_movimiento + '</td><td>' + m.stock_nuevo +
                '</td><td>' + esc(m.usuario ? m.usuario.name : 'Sistema') + '<br>' + esc(m.motivo) + '</td></tr>').join('') +
            '</tbody></table></div><p>Página ' + data.current_page + ' de ' + data.last_page + '</p><button type="button" id="hist-anterior" class="btn btn-light">Anterior</button> <button type="button" id="hist-siguiente" class="btn btn-light">Siguiente</button>', null);
        $('#hist-anterior').prop('disabled', pagina <= 1).on('click', () => historial(ruta,s,pagina-1).catch(error));
        $('#hist-siguiente').prop('disabled', pagina >= data.last_page).on('click', () => historial(ruta,s,pagina+1).catch(error));
    }
    function editarLocal(local) {
        modal(local ? 'Editar local' : 'Crear local',
            campo('Nombre', '<input class="form-control" name="local_nombre" required maxlength="50" value="' + esc(local ? local.local_nombre : '') + '">') +
            campo('Dirección', '<input class="form-control" name="local_direccion" maxlength="100" value="' + esc(local ? local.local_direccion : '') + '">') +
            campo('Teléfono', '<input class="form-control" name="local_telefono" maxlength="20" value="' + esc(local ? local.local_telefono : '') + '">') +
            campo('Estado', '<select class="form-control" name="activo"><option value="1">Activo</option><option value="0"' + (local && !local.activo ? ' selected' : '') + '>Inactivo</option></select>'),
            d => api('/locales' + (local ? '/' + local.id : ''), local ? 'PUT' : 'POST', d));
    }
    $('#gestionar-locales').on('click', async () => {
        try {
            catalogo = await api('/catalogo');
            modal('Administrar locales', '<button type="button" id="crear-local" class="btn btn-primary mb-3">Crear local</button><div class="list-group">' +
                catalogo.locales.map(l => '<button type="button" class="list-group-item list-group-item-action editar-local" data-id="' + l.id + '">' + esc(l.local_nombre) +
                    ' · ' + (l.activo ? 'Activo' : 'Inactivo') + '</button>').join('') + '</div>', null);
            $('#crear-local').on('click', () => editarLocal(null));
            $('.editar-local').on('click', function() { editarLocal(catalogo.locales.find(l => l.id == $(this).data('id'))); });
        } catch(e) { error(e); }
    });
    api('/catalogo').then(data => { catalogo = data; }).catch(error);
});
</script>
@endsection
