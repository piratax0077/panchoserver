@extends('plantillas.app')

@section('titulo','Devolución de mercadería')

@section('style')
<style>
  .returns-page{max-width:1500px;margin:0 auto;padding:18px}.returns-hero{background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;border-radius:18px;padding:24px 28px;margin-bottom:18px;box-shadow:0 14px 35px rgba(15,23,42,.16)}
  .returns-hero h2{font-weight:700;margin:0 0 6px}.returns-hero p{margin:0;color:#cbd5e1}.returns-search-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.return-search-card{background:#fff;border:1px solid #dbe4ee;border-radius:16px;padding:20px;box-shadow:0 8px 24px rgba(15,23,42,.06)}
  .return-search-card h5{font-weight:700;color:#13244a;margin-bottom:5px}.return-search-card p{color:#64748b;min-height:42px}.return-search-row{display:flex;gap:10px;align-items:flex-end}.return-search-row .form-group{margin:0;flex:1}.return-search-row label{font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em}.return-search-row .form-control{height:44px;border-radius:10px}.return-search-row .btn{height:44px;border-radius:10px;font-weight:700;padding:0 22px}
  .document-type{display:flex;gap:8px;margin-bottom:12px}.document-type label{border:1px solid #d7e0ea;border-radius:9px;padding:7px 14px;margin:0;cursor:pointer;background:#f8fafc}.document-type input{margin-right:6px}.returns-status{display:flex;align-items:center;gap:8px;color:#475569;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:11px 14px;margin:16px 0}.returns-status i{color:#2563eb}.returns-content:empty{display:none}
  @media(max-width:850px){.returns-search-grid{grid-template-columns:1fr}.return-search-row{align-items:stretch;flex-direction:column}.return-search-row .btn{width:100%}}
</style>
@endsection

@section('javascript')
<script>
function mensajeError(xhr, fallback) {
  let mensaje = fallback || 'No fue posible completar la operación.';
  if (xhr.responseJSON) {
    if (xhr.responseJSON.message) mensaje = xhr.responseJSON.message;
    if (xhr.responseJSON.errors) mensaje = Object.values(xhr.responseJSON.errors).flat()[0];
  } else if (xhr.responseText) mensaje = xhr.responseText;
  Vue.swal({icon:'error', title:'No se pudo continuar', text:mensaje});
}

function cargar_documento(tipoBusqueda) {
  const esNc = tipoBusqueda === 1;
  const numero = $(esNc ? '#numero_nc' : '#numero_doc').val().trim();
  const tipoDoc = $('input[name="tipo_documento"]:checked').val();
  if (!numero) {
    Vue.swal({icon:'warning', text: esNc ? 'Ingrese el número de la nota de crédito.' : 'Ingrese el número del documento.'});
    return;
  }
  const url = esNc ? '/guiadespacho/detalle_nc_nueva/'+encodeURIComponent(numero) : '/guiadespacho/detalle_doc_nuevo/'+encodeURIComponent(numero)+'/'+tipoDoc;
  const boton = $(esNc ? '#buscar-nc' : '#buscar-doc').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando');
  $('#contenido').empty();
  $.get(url).done(function(resp){
    if (resp && resp.error) return Vue.swal({icon:'error',text:resp.error});
    $('#contenido').html(resp);
  }).fail(function(xhr){ mensajeError(xhr, 'No se encontró el documento.'); })
    .always(function(){ boton.prop('disabled',false).html('<i class="fas fa-search"></i> Buscar'); });
}

function confirmar_devolucion(repuestoId) {
  const cantidad = Number($('#cantidad-'+repuestoId).val());
  const disponible = Number($('#merma-'+repuestoId).val());
  const localId = $('#local_id-'+repuestoId).val();
  if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > disponible) {
    Vue.swal({icon:'warning', text:'Ingrese una cantidad entre 1 y '+disponible+'.'}); return;
  }
  if (!localId) { Vue.swal({icon:'warning',text:'Seleccione la ubicación de destino.'}); return; }
  Vue.swal({title:'Agregar a la devolución',text:'Se solicitarán '+cantidad+' unidad(es). El stock aún no cambiará.',icon:'question',showCancelButton:true,confirmButtonText:'Sí, agregar',cancelButtonText:'Cancelar'}).then(function(result){
    if (!result.isConfirmed) return;
    const numNc = $('#num_nc').val();
    const data = {repuesto_id:repuestoId,cantidad:cantidad,local_id:localId,num_nc:numNc || $('#numero_doc').val(),tipo_doc:numNc ? 'nc' : $('input[name="tipo_documento"]:checked').val()};
    $.post({url:'/guiadespacho/devolucion',data:data,headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}})
      .done(function(resp){ $('#table_devolucion_').html(resp.html); $('#agregar-'+repuestoId).prop('disabled',true).html('<i class="fas fa-check"></i> Agregado'); $('#cantidad-'+repuestoId+',#local_id-'+repuestoId).prop('disabled',true); $('#cerrar-devolucion').prop('disabled',false); Vue.swal({icon:'success',text:resp.mensaje,timer:2200,showConfirmButton:false}); })
      .fail(function(xhr){ mensajeError(xhr); });
  });
}

function cerrar_devolucion(numNc) {
  const esNc = numNc !== undefined && numNc !== null && numNc !== '';
  const numero = esNc ? numNc : $('#numero_doc').val();
  const tipo = $('input[name="tipo_documento"]:checked').val();
  const url = esNc ? '/guiadespacho/cerrar_devolucion/'+numero : '/guiadespacho/cerrar_devolucion_/'+numero+'/'+tipo;
  Vue.swal({title:'Enviar a revisión',text:'Inventario deberá aceptar los productos antes de que aumente el stock.',icon:'warning',showCancelButton:true,confirmButtonText:'Enviar devolución',cancelButtonText:'Seguir editando'}).then(function(result){
    if (!result.isConfirmed) return;
    $.post({url:url,data:{_token:$('meta[name="csrf-token"]').attr('content')}}).done(function(resp){
      $('#listado_repuestos_devolucion').html(resp.html); $('#exampleModal').modal('show'); $('#cerrar-devolucion').prop('disabled',true); Vue.swal({icon:'success',text:resp.mensaje,timer:1800,showConfirmButton:false});
    }).fail(function(xhr){ mensajeError(xhr); });
  });
}

function imprimir_devolucion(numNc){
  $.post({url:'/imprimir_devolucion',data:{num_nc:numNc},headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}}).done(function(resp){const r=typeof resp==='string'?JSON.parse(resp):resp;if(r.estado==='OK'){window.open(r.mensaje,'_blank','location=yes,height=570,width=720,scrollbars=yes,status=yes');}else{Vue.swal({icon:'error',text:r.mensaje});}}).fail(function(xhr){mensajeError(xhr);});
}

$(document).on('keydown','#numero_nc,#numero_doc',function(e){if(e.key==='Enter'){e.preventDefault();cargar_documento(this.id==='numero_nc'?1:2);}});
</script>
@endsection

@section('contenido_titulo_pagina')<h4 class="titulazo">Devoluciones</h4>@endsection
@section('mensajes')@include('fragm.mensajes')@endsection

@section('contenido_ingresa_datos')
<div class="returns-page">
  <div class="returns-hero"><h2><i class="fas fa-undo-alt"></i> Devolución de mercadería</h2><p>Busca el documento, selecciona los productos y envíalos a revisión de inventario.</p></div>
  <div class="returns-search-grid">
    <section class="return-search-card">
      <h5><i class="fas fa-file-invoice-dollar text-primary"></i> Con nota de crédito</h5><p>Utiliza una nota de crédito emitida y pendiente de devolución.</p>
      <div class="return-search-row"><div class="form-group"><label for="numero_nc">N.º nota de crédito</label><input id="numero_nc" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Ej.: 1254"></div><button id="buscar-nc" class="btn btn-primary" onclick="cargar_documento(1)"><i class="fas fa-search"></i> Buscar</button></div>
    </section>
    <section class="return-search-card">
      <h5><i class="fas fa-receipt text-success"></i> Con documento de venta</h5><p>Gestiona una devolución mediante boleta o factura aceptada.</p>
      <div class="document-type"><label><input type="radio" name="tipo_documento" value="bo" checked> Boleta</label><label><input type="radio" name="tipo_documento" value="fa"> Factura</label></div>
      <div class="return-search-row"><div class="form-group"><label for="numero_doc">N.º documento</label><input id="numero_doc" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Ej.: 45892"></div><button id="buscar-doc" class="btn btn-success" onclick="cargar_documento(2)"><i class="fas fa-search"></i> Buscar</button></div>
    </section>
  </div>
  <div class="returns-status"><i class="fas fa-shield-alt"></i><span>El stock no se modifica al preparar la devolución. Solo aumenta cuando inventario acepta cada producto.</span></div>
  <div id="contenido" class="returns-content"></div>
</div>
@endsection
