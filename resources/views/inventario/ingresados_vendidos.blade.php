@extends('plantillas.app')

@section('titulo', 'Ingresados vs Vendidos')

@section('contenido_titulo_pagina')
<div class="titulazo"><h4>Ingresados vs Vendidos</h4></div>
@endsection

@section('style')
<style>
  .iv-page { --iv-primary:#2563eb; --iv-text:#172554; --iv-muted:#64748b; --iv-border:#dfe6ef; padding:18px; background:#f5f7fb; min-height:calc(100vh - 130px); }
  .iv-card { border:1px solid var(--iv-border); border-radius:16px; background:#fff; box-shadow:0 8px 25px rgba(15,23,42,.06); }
  .iv-search { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:14px; padding:18px; }
  .iv-search-main { display:grid; grid-template-columns:auto minmax(250px,1fr); gap:14px; align-items:end; }
  .iv-search-types { display:flex; flex-wrap:wrap; gap:7px; }
  .iv-search-types input { position:absolute; opacity:0; }
  .iv-search-types label { margin:0; padding:10px 13px; border:1px solid var(--iv-border); border-radius:9px; color:#475569; background:#fff; font-size:12px; font-weight:700; cursor:pointer; }
  .iv-search-types input:checked + label { border-color:#93c5fd; color:#1d4ed8; background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.08); }
  .iv-search label.iv-label { display:block; margin-bottom:6px; color:#334155; font-size:12px; font-weight:700; }
  .iv-search .form-control { height:43px; border-radius:9px; }
  .iv-search .btn { min-width:130px; height:43px; border-radius:9px; font-weight:700; }
  .iv-empty { display:flex; min-height:300px; flex-direction:column; align-items:center; justify-content:center; gap:9px; margin-top:16px; color:var(--iv-muted); text-align:center; }
  .iv-empty i { font-size:42px; color:#93c5fd; }
  .iv-candidates { width:100%; max-width:900px; padding:16px; text-align:left; }
  .iv-candidates h5 { margin:0 0 10px; color:var(--iv-text); text-align:center; }
  .iv-candidate { display:grid; grid-template-columns:110px minmax(0,1fr) 180px auto; gap:10px; align-items:center; width:100%; margin-top:7px; padding:10px 12px; border:1px solid var(--iv-border); border-radius:9px; color:#334155; background:#fff; text-align:left; cursor:pointer; }
  .iv-candidate:hover { border-color:#93c5fd; background:#eff6ff; }
  .iv-candidate strong { color:#1d4ed8; }
  .iv-result { display:none; margin-top:16px; }
  .iv-product { display:grid; grid-template-columns:190px minmax(0,1fr); gap:18px; padding:18px; }
  .iv-product-image { display:flex; height:170px; align-items:center; justify-content:center; overflow:hidden; border:1px solid #e5eaf1; border-radius:13px; background:#f8fafc; }
  .iv-product-image img { max-width:94%; max-height:155px; object-fit:contain; }
  .iv-product-image i { color:#94a3b8; font-size:42px; }
  .iv-product-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
  .iv-product-heading small { color:var(--iv-primary); font-weight:800; text-transform:uppercase; }
  .iv-product-heading h3 { margin:3px 0 5px; color:var(--iv-text); font-size:22px; font-weight:800; }
  .iv-product-heading p { margin:0; color:var(--iv-muted); }
  .iv-product-price { white-space:nowrap; color:#0f172a; font-size:22px; font-weight:800; }
  .iv-product-meta { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; margin-top:17px; }
  .iv-product-meta div { padding:10px 12px; border-radius:9px; background:#f8fafc; }
  .iv-product-meta span { display:block; color:var(--iv-muted); font-size:10px; font-weight:700; text-transform:uppercase; }
  .iv-product-meta strong { display:block; margin-top:3px; color:#334155; font-size:13px; overflow-wrap:anywhere; }
  .iv-summary { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:10px; margin-top:16px; }
  .iv-summary-card { padding:14px; border:1px solid var(--iv-border); border-radius:13px; background:#fff; }
  .iv-summary-card span { display:block; color:var(--iv-muted); font-size:10px; font-weight:800; text-transform:uppercase; }
  .iv-summary-card strong { display:block; margin-top:5px; color:var(--iv-text); font-size:24px; line-height:1; }
  .iv-summary-card small { color:var(--iv-muted); }
  .iv-summary-card.blue { border-color:#bfdbfe; background:#eff6ff; }
  .iv-summary-card.green { border-color:#bbf7d0; background:#f0fdf4; }
  .iv-summary-card.red { border-color:#fecaca; background:#fef2f2; }
  .iv-summary-card.orange { border-color:#fed7aa; background:#fff7ed; }
  .iv-stock-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-top:16px; }
  .iv-stock { padding:15px; border:1px solid var(--iv-border); border-radius:14px; background:#fff; }
  .iv-stock-head { display:flex; align-items:center; justify-content:space-between; }
  .iv-stock-head h6 { margin:0; color:var(--iv-primary); font-weight:800; }
  .iv-stock-value { margin:12px 0; color:#1d4ed8; font-size:31px; font-weight:800; }
  .iv-stock-value.low { color:#dc2626; }
  .iv-stock-value small { color:var(--iv-muted); font-size:12px; font-weight:500; }
  .iv-stock-detail { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
  .iv-stock-detail div { min-width:0; padding:9px; border-radius:8px; background:#f8fafc; }
  .iv-stock-detail span { display:block; color:var(--iv-muted); font-size:9px; font-weight:700; text-transform:uppercase; }
  .iv-stock-detail strong { display:block; margin-top:3px; color:#334155; font-size:11px; overflow-wrap:anywhere; }
  .iv-tabs { margin-top:16px; overflow:hidden; }
  .iv-tab-nav { display:flex; flex-wrap:wrap; gap:5px; padding:12px; border-bottom:1px solid var(--iv-border); }
  .iv-tab-nav button { padding:8px 12px; border:0; border-radius:8px; color:#475569; background:#f1f5f9; font-size:12px; font-weight:700; cursor:pointer; }
  .iv-tab-nav button.active { color:#fff; background:var(--iv-primary); }
  .iv-tab-pane { display:none; padding:0; }
  .iv-tab-pane.active { display:block; }
  .iv-table-wrap { max-height:430px; overflow:auto; }
  .iv-table { width:100%; margin:0; font-size:12px; }
  .iv-table thead th { position:sticky; top:0; z-index:2; padding:10px; border:0; color:#475569; background:#f8fafc; font-size:10px; text-transform:uppercase; }
  .iv-table tbody td { padding:10px; border-top:1px solid #eef2f7; color:#334155; vertical-align:middle; }
  .iv-table tbody tr:hover { background:#f8fbff; }
  .iv-no-data { padding:42px 15px; color:var(--iv-muted); text-align:center; }
  .iv-badge { display:inline-flex; padding:4px 7px; border-radius:999px; font-size:10px; font-weight:800; }
  .iv-badge.success { color:#15803d; background:#dcfce7; }
  .iv-badge.danger { color:#dc2626; background:#fee2e2; }
  .iv-badge.info { color:#1d4ed8; background:#dbeafe; }
  .iv-movement { display:grid; grid-template-columns:125px 115px 130px minmax(180px,1fr) 150px; gap:10px; align-items:center; padding:11px 14px; border-bottom:1px solid #eef2f7; font-size:12px; }
  .iv-movement-change { display:flex; align-items:center; gap:6px; font-weight:700; }
  .iv-movement-diff { font-weight:800; }
  .iv-movement-diff.positive { color:#15803d; }
  .iv-movement-diff.negative { color:#dc2626; }
  .iv-loading { display:flex; align-items:center; justify-content:center; gap:10px; min-height:240px; color:var(--iv-muted); }
  @media(max-width:1100px){ .iv-summary{grid-template-columns:repeat(3,1fr)} .iv-product-meta{grid-template-columns:repeat(2,1fr)} .iv-stock-grid{grid-template-columns:1fr} }
  @media(max-width:700px){ .iv-page{padding:10px} .iv-search,.iv-search-main{grid-template-columns:1fr} .iv-product{grid-template-columns:1fr} .iv-product-image{height:140px} .iv-summary{grid-template-columns:repeat(2,1fr)} .iv-movement{grid-template-columns:1fr 1fr} }
</style>
@endsection

@section('javascript')
<script>
  function ivEscape(value){
    return $('<div>').text(value === null || value === undefined ? '—' : value).html();
  }

  function ivMoney(value){
    return '$' + new Intl.NumberFormat('es-CL').format(Number(value || 0));
  }

  function ivEmpty(columns, message){
    return '<tr><td colspan="'+columns+'" class="iv-no-data"><i class="fas fa-inbox mr-2"></i>'+ivEscape(message)+'</td></tr>';
  }

  function ivLocationName(index){
    return index === 1 ? 'Bodega' : (index === 2 ? 'Tienda' : 'Casa Matriz');
  }

  function ivShowTab(tab){
    $('.iv-tab-nav button').removeClass('active');
    $('.iv-tab-nav button[data-tab="'+tab+'"]').addClass('active');
    $('.iv-tab-pane').removeClass('active');
    $('#iv-tab-'+tab).addClass('active');
  }

  function dame_info(){
    let codigo = $('#codigo_repuesto').val().trim();
    let tipo = $('input[name="tipo_busqueda"]:checked').val();
    if(!codigo){
      Vue.swal({title:'Código requerido', text:'Ingrese un código para realizar la búsqueda.', icon:'warning'});
      return;
    }

    $('#iv-empty').hide();
    $('#iv-result').hide();
    $('#iv-loading').show();

    $.ajax({
      type:'POST',
      url:'/repuesto/ingresados-vendidos/datos',
      data:{codigo:codigo, tipo_busqueda:tipo},
      headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
      success:function(data){
        $('#iv-loading').hide();
        renderIngresadosVendidos(data);
      },
      error:function(error){
        $('#iv-loading').hide();
        $('#iv-empty').show();
        if(error.status===409 && error.responseJSON && error.responseJSON.coincidencias){
          renderIvCandidates(error.responseJSON.coincidencias);
          return;
        }
        let message = error.responseJSON && error.responseJSON.message ? error.responseJSON.message : 'No fue posible consultar la información.';
        Vue.swal({title:'Búsqueda sin resultados', text:message, icon:'error'});
      }
    });
  }

  function renderIvCandidates(items){
    let html='<div class="iv-candidates"><h5>Selecciona el repuesto correcto</h5>';
    items.forEach(function(item){
      html+='<button type="button" class="iv-candidate" data-codigo="'+ivEscape(item.codigo_interno)+'"><strong>'+ivEscape(item.codigo_interno)+'</strong><span>'+ivEscape(item.descripcion)+'</span><span>'+ivEscape(item.cod_repuesto_proveedor)+'</span><small>'+ivEscape(item.marcarepuesto)+'</small></button>';
    });
    html+='</div>';
    $('#iv-empty').html(html);
  }

  function ivSelectCandidate(codigo){
    $('#codigo_repuesto').val(codigo);
    $('#iv-cod-int').prop('checked',true);
    dame_info();
  }

  function renderIngresadosVendidos(data){
    let r=data.repuesto, resumen=data.resumen;
    $('#iv-product-image').html('<img src="'+ivEscape(r.foto || '{{route('storage.public', [], false)}}')+'" alt="Imagen del repuesto">');
    $('#iv-product-code').text(r.codigo_interno);
    $('#iv-product-title').text(r.descripcion);
    $('#iv-product-subtitle').text((r.marca || 'Sin marca')+' · '+(r.pais || 'Sin país'));
    $('#iv-product-price').text(ivMoney(r.precio_venta));
    $('#iv-product-meta').html(
      '<div><span>Código proveedor</span><strong>'+ivEscape(r.codigo_proveedor)+'</strong></div>'+
      '<div><span>Stock mínimo</span><strong>'+r.stock_minimo+' unidades</strong></div>'+
      '<div><span>Último modificador</span><strong>'+ivEscape(r.modificado_por || 'Proceso automático')+'</strong></div>'+
      '<div><span>Estado</span><strong>'+(resumen.bajo_minimo ? '<span class="iv-badge danger">Stock bajo</span>' : '<span class="iv-badge success">Disponible</span>')+'</strong></div>'
    );

    let summary=[
      ['Stock actual',resumen.stock_actual,'unidades','blue'],
      ['Ingresado',resumen.ingresado,'unidades','green'],
      ['Vendido',resumen.vendido,'unidades',''],
      ['Devuelto',resumen.devuelto,'unidades','orange'],
      ['Con NC',resumen.anulado,'unidades','red'],
      ['Movimientos',data.movimientos.length,'registros','']
    ];
    $('#iv-summary').html(summary.map(x=>'<div class="iv-summary-card '+x[3]+'"><span>'+x[0]+'</span><strong>'+x[1]+'</strong><small>'+x[2]+'</small></div>').join(''));

    $('#iv-stock-grid').html(r.ubicaciones.map(u=>
      '<article class="iv-stock"><div class="iv-stock-head"><h6><i class="fas fa-warehouse mr-2"></i>'+ivEscape(u.nombre)+'</h6><span class="iv-badge '+(u.stock<10?'danger':'success')+'">'+(u.stock<10?'Stock bajo':'Disponible')+'</span></div>'+
      '<div class="iv-stock-value '+(u.stock<10?'low':'')+'">'+u.stock+' <small>unidades</small></div>'+
      '<div class="iv-stock-detail"><div><span>Ubicación</span><strong>'+ivEscape(u.ubicacion)+'</strong></div><div><span>Responsable</span><strong>'+ivEscape(u.responsable || 'Sin asignar')+'</strong></div><div><span>Actualizado</span><strong>'+ivEscape(u.actualizado)+'</strong></div><div><span>Participación</span><strong>'+(r.stock_total ? Math.round((u.stock/r.stock_total)*100) : 0)+'%</strong></div></div></article>'
    ).join(''));

    $('#iv-ingresos').html(data.ingresos.length ? data.ingresos.map(i=>'<tr><td>'+ivEscape(i.fecha)+'</td><td>'+ivEscape(i.documento)+'</td><td>'+ivEscape(i.proveedor)+'</td><td class="text-right"><strong>+'+i.cantidad+'</strong></td></tr>').join('') : ivEmpty(4,'No existen ingresos por factura.'));
    $('#iv-ventas').html(data.ventas.length ? data.ventas.map(v=>'<tr class="'+(v.estado_venta==='devuelto'?'table-danger':'')+'"><td><span class="iv-badge '+(v.estado_venta==='devuelto'?'danger':'info')+'">'+ivEscape(v.tipo)+'</span></td><td>'+(v.tipo_dte?'<a href="javascript:void(0)" onclick="ivOpenDocument('+v.tipo_dte+','+v.documento_id+')">'+ivEscape(v.numero)+'</a>':ivEscape(v.numero))+'</td><td>'+ivEscape(v.fecha)+'</td><td class="text-right">'+ivSaleQuantity(v)+'</td><td>'+ivMoney(v.precio)+'</td><td>'+ivEscape(v.usuario)+'</td><td>'+ivEscape(v.local)+'</td><td>'+ivSaleStatus(v)+'</td></tr>').join('') : ivEmpty(8,'No existen ventas registradas.'));
    $('#iv-devoluciones').html(data.devoluciones.length ? data.devoluciones.map(d=>'<tr><td>'+ivEscape(d.fecha)+'</td><td>'+ivEscape(d.nota_credito)+'</td><td>'+ivEscape(d.referencia)+'</td><td class="text-right"><strong>+'+d.cantidad+'</strong></td><td>'+ivEscape(d.usuario)+'</td></tr>').join('') : ivEmpty(5,'No existen devoluciones registradas.'));
    $('#iv-traspasos').html(data.traspasos.length ? data.traspasos.map(t=>'<tr><td>'+ivEscape(t.fecha)+'</td><td>'+ivEscape(ivTransferName(t.locaciones))+'</td><td class="text-right">'+t.cantidad+'</td><td>'+ivEscape(t.usuario)+'</td><td>'+ivTransferStatus(t.estado)+'</td></tr>').join('') : ivEmpty(5,'No existen traspasos registrados.'));
    $('#iv-minimos').html(data.stock_minimo_fechas.length ? data.stock_minimo_fechas.map(f=>'<tr><td>'+ivEscape(f)+'</td></tr>').join('') : ivEmpty(1,'No existen alertas de stock mínimo.'));
    $('#iv-movimientos').html(data.movimientos.length ? data.movimientos.map(m=>{
      let positive=m.diferencia>0, sign=positive?'+':'';
      return '<div class="iv-movement"><span>'+ivEscape(m.fecha)+'</span><span class="iv-badge info">'+ivEscape(String(m.tipo).replace(/_/g,' '))+'</span><span>'+ivEscape(ivLocationName(m.ubicacion))+'</span><div><strong>'+ivEscape(m.motivo)+'</strong><br><small>'+ivEscape(m.usuario)+'</small></div><div class="iv-movement-change"><span>'+m.anterior+'</span><i class="fas fa-arrow-right"></i><span>'+m.nuevo+'</span><b class="iv-movement-diff '+(positive?'positive':'negative')+'">'+sign+m.diferencia+'</b></div></div>';
    }).join('') : '<div class="iv-no-data">Todavía no existen movimientos en la nueva bitácora.</div>');

    $('#iv-result').show();
    ivShowTab('movimientos');
  }

  function ivSaleQuantity(venta){
    return venta.cantidad_devuelta>0 ? '<strong>'+venta.cantidad+'</strong><br><small>'+venta.cantidad_original+' original / '+venta.cantidad_devuelta+' devuelta</small>' : venta.cantidad;
  }
  function ivSaleStatus(venta){
    if(venta.estado_venta==='parcial') return '<span class=\'iv-badge info\'>NC parcial</span>';
    if(venta.estado_venta==='devuelto') return '<span class=\'iv-badge danger\'>Devuelto</span>';
    return '<span class=\'iv-badge success\'>Vigente</span>';
  }

  function ivTransferName(value){
    return value===1?'Bodega → Tienda':(value===2?'Bodega → Casa Matriz':'Casa Matriz → Tienda');
  }
  function ivTransferStatus(value){
    return value===1?'<span class="iv-badge success">Aceptado</span>':(value===0?'<span class="iv-badge danger">Rechazado</span>':'<span class="iv-badge info">Pendiente</span>');
  }
  function ivOpenDocument(tipo, id){
    let codigo=$('#iv-product-code').text();
    $.get('/ventas/damedetalleboleta/'+tipo+'/'+id+'/'+codigo)
      .done(function(html){ $('#modal_body_detalle_boleta').html(html); $('#exampleModal_boleta').modal('show'); })
      .fail(function(){ Vue.swal({title:'Error',text:'No fue posible cargar el documento.',icon:'error'}); });
  }

  $(document).on('click','.iv-tab-nav button',function(){ ivShowTab($(this).data('tab')); });
  $(document).on('click','.iv-candidate',function(){ ivSelectCandidate($(this).data('codigo')); });
</script>
@endsection

@section('contenido_ingresa_datos')
<main class="iv-page">
  <form class="iv-card iv-search" onsubmit="event.preventDefault();dame_info();">
    <div class="iv-search-main">
      <div>
        <label class="iv-label">Buscar por</label>
        <div class="iv-search-types">
          <input type="radio" name="tipo_busqueda" id="iv-cod-int" value="cod_int" checked><label for="iv-cod-int">Código interno</label>
          <input type="radio" name="tipo_busqueda" id="iv-oem" value="oem"><label for="iv-oem">OEM</label>
          <input type="radio" name="tipo_busqueda" id="iv-cod-prov" value="cod_prov"><label for="iv-cod-prov">Código proveedor</label>
        </div>
      </div>
      <div><label class="iv-label" for="codigo_repuesto">Código del repuesto</label><input class="form-control" id="codigo_repuesto" autocomplete="off" placeholder="Ej.: KEM41"></div>
    </div>
    <button class="btn btn-primary" type="submit"><i class="fas fa-search mr-2"></i>Consultar</button>
  </form>

  <section id="iv-loading" class="iv-card iv-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i>Consultando inventario...</section>
  <section id="iv-empty" class="iv-card iv-empty"><i class="fas fa-chart-bar"></i><h5>Consulta el movimiento de un repuesto</h5><p>Visualiza entradas, ventas, devoluciones, stock por ubicación y auditoría.</p></section>

  <div id="iv-result" class="iv-result">
    <section class="iv-card iv-product">
      <div class="iv-product-image" id="iv-product-image"></div>
      <div>
        <div class="iv-product-heading"><div><small id="iv-product-code"></small><h3 id="iv-product-title"></h3><p id="iv-product-subtitle"></p></div><div class="iv-product-price" id="iv-product-price"></div></div>
        <div class="iv-product-meta" id="iv-product-meta"></div>
      </div>
    </section>
    <section class="iv-summary" id="iv-summary"></section>
    <section class="iv-stock-grid" id="iv-stock-grid"></section>

    <section class="iv-card iv-tabs">
      <nav class="iv-tab-nav">
        <button type="button" data-tab="movimientos" class="active"><i class="fas fa-history mr-1"></i>Movimientos</button>
        <button type="button" data-tab="ingresos">Ingresados</button>
        <button type="button" data-tab="ventas">Vendidos</button>
        <button type="button" data-tab="devoluciones">Devoluciones</button>
        <button type="button" data-tab="traspasos">Traspasos</button>
        <button type="button" data-tab="minimos">Stock mínimo</button>
      </nav>
      <div id="iv-tab-movimientos" class="iv-tab-pane active"><div id="iv-movimientos"></div></div>
      <div id="iv-tab-ingresos" class="iv-tab-pane"><div class="iv-table-wrap"><table class="iv-table"><thead><tr><th>Fecha</th><th>Factura</th><th>Proveedor</th><th>Cantidad</th></tr></thead><tbody id="iv-ingresos"></tbody></table></div></div>
      <div id="iv-tab-ventas" class="iv-tab-pane"><div class="iv-table-wrap"><table class="iv-table"><thead><tr><th>Documento</th><th>Número</th><th>Fecha</th><th>Cantidad</th><th>Precio</th><th>Usuario</th><th>Local</th><th>Estado</th></tr></thead><tbody id="iv-ventas"></tbody></table></div></div>
      <div id="iv-tab-devoluciones" class="iv-tab-pane"><div class="iv-table-wrap"><table class="iv-table"><thead><tr><th>Fecha</th><th>Nota crédito</th><th>Referencia</th><th>Cantidad</th><th>Usuario</th></tr></thead><tbody id="iv-devoluciones"></tbody></table></div></div>
      <div id="iv-tab-traspasos" class="iv-tab-pane"><div class="iv-table-wrap"><table class="iv-table"><thead><tr><th>Fecha</th><th>Trayecto</th><th>Cantidad</th><th>Usuario</th><th>Estado</th></tr></thead><tbody id="iv-traspasos"></tbody></table></div></div>
      <div id="iv-tab-minimos" class="iv-tab-pane"><div class="iv-table-wrap"><table class="iv-table"><thead><tr><th>Fecha de alerta</th></tr></thead><tbody id="iv-minimos"></tbody></table></div></div>
    </section>
  </div>
</main>

<div class="modal fade" id="exampleModal_boleta" tabindex="-1" role="dialog" aria-labelledby="ivDocumentTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="ivDocumentTitle">Detalle del documento</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div><div class="modal-body" id="modal_body_detalle_boleta"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div></div></div>
</div>
@endsection
