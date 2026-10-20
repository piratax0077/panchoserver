@extends('plantillas.app')
@section('titulo','Ofertas')
@section('javascript')
<script>
    function soloNumeros(e)
    {
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
    }

    function editar_descuento_familia(idfamilia){
      let url = '/ventas/editar_descuento_familia/'+idfamilia;
      $.ajax({
        type:'get',
        url:url,
        success: function(resp){
          
          $('#modal_editar_descuento_familia').modal('show');
         
          $('#modal_body_editar_descuento').empty();
          $('#modal_body_editar_descuento').append(resp);
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
      
    }

    function eliminar_descuento(id_familia){
           
      let url = "/ventas/eliminar_descuento_familia/"+id_familia;

      $.ajax({
        type:'get',
        url: url,
        success: function(descuentos){
          console.log(descuentos);
          $('#tbody_descuentos').empty();

          mostrar_descuentos_familia(descuentos);
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function editar_nueva_familia(){
      
      let porcentaje_nuevo = $('#modal_editar_descuento_familia #porcentaje').val();
      let idfamilia = $('#modal_editar_descuento_familia #idfamilia_editar').val();
      let fecha_inicio = $('#modal_editar_descuento_familia #fecha_inicio').val();
      let fecha_fin = $('#modal_editar_descuento_familia #fecha_fin').val();
      // obtener el valor del input de tipo file con id imagenReferencia
      let imagen = $('#modal_editar_descuento_familia #referenciaImagen')[0].files[0];


      if(porcentaje_nuevo == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar un porcentaje',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(fecha_inicio == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha inicial',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(fecha_fin == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha final',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(imagen == undefined){
        // return Vue.swal({
        //   icon:'error',
        //   text:'Debe ingresar una imagen',
        //   timer:1000,
        //   showConfirmButton:false,
        //   position:'top-right',
        //   toast:true
        // });
      }
     
      if (!Number.isFinite(Number(porcentaje_nuevo)) || Number(porcentaje_nuevo) <= 0 || Number(porcentaje_nuevo) >= 100 || fecha_fin < fecha_inicio) {
        return Vue.swal({icon:'error',text:'Revise el porcentaje (mayor a 0 y menor a 100) y el orden de las fechas.'});
      }

      let url = '/ventas/editar_nueva_familia';

      // preparar el objeto data para enviarle un archivo adjunto
      var data = new FormData();
      data.append('porcentaje_nuevo', porcentaje_nuevo);
      data.append('idfamilia', idfamilia);
      data.append('fecha_inicio', fecha_inicio);
      data.append('fecha_fin', fecha_fin);
      data.append('local', $('#modal_editar_descuento_familia #local').val());
      if (imagen) data.append('imagen', imagen);


      // enviar el objeto data el cual contiene una imagen mediante ajax
      $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
      });

      $.ajax({
        type:'post',
        url:url,
        data: data,
        contentType: false,
        processData: false,
        success: function(resp){
         console.log(resp);
          if(resp == 'ok'){
            $('#modal_editar_descuento_familia').modal('hide');
            Vue.swal({
              icon:'success',
              text:'Se ha modificado el descuento'
            });
            setTimeout(function(){
              descuento_familia();
            }, 1000);
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
      });

      
      
    }


function ocultar_resultado(){ $('#busqueda_resultado').addClass('d-none').removeClass('d-block'); }
</script>
<script>
@include('ventas.partials.ofertas_script')
@include('ventas.partials.ofertas_familias_script')
</script>
@endsection
@section('style')
<style>
.ofertas-page{padding:16px; color:#343a40;}
.ofertas-page .ofertas-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
.ofertas-page h3{font-size:22px;margin:0 0 6px;font-weight:600;}
.ofertas-page h4{font-size:16px;font-weight:600;margin:0 0 16px;}
.ofertas-page .ofertas-muted{color:#6c757d;font-size:13px;}
.ofertas-page .ofertas-layout{display:grid;grid-template-columns:minmax(280px,1fr) minmax(0,2fr);gap:16px;align-items:start;}
.ofertas-page .ofertas-panel{background:#fff;border:1px solid #c8d4dc;border-radius:8px;padding:18px;margin-bottom:16px;}
.ofertas-page .ofertas-search{background:#f1f6f7;border-color:#b7ccd0;}
.ofertas-page .ofertas-basket{background:#f1f7f3;border-color:#b6cebf;}
.ofertas-page .ofertas-tools{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;}
.ofertas-page .ofertas-tools input{flex:1;min-width:150px;}
.ofertas-page .ofertas-tools select{width:auto;max-width:100%;}
.ofertas-page .ofertas-selection{background:#f5f3f9;border:1px solid #c6bdd7;border-radius:6px;padding:12px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;}
.ofertas-page .table{font-size:13px;margin-bottom:0;}
.ofertas-page .table th{background:#f3f5f7;color:#495057;white-space:nowrap;border-top:0;}
.ofertas-page .table td{vertical-align:middle;}
.ofertas-page .table tbody tr:hover{background:#f1f6fb;}
.ofertas-page .oferta-seleccionada{background:#edf4ff;}
.ofertas-page .ofertas-status{display:inline-block;border-radius:12px;padding:3px 9px;font-size:12px;white-space:nowrap;}
.ofertas-page .status-vigente{background:#e4f3e9;color:#22663e;}
.ofertas-page .status-programada{background:#e9effb;color:#355785;}
.ofertas-page .status-caducada{background:#f8e9e6;color:#8d433b;}
.ofertas-page .form-control{font-size:14px;}
.ofertas-page .ofertas-empty{text-align:center;padding:24px;color:#6c757d;}
.ofertas-page .ofertas-resultados{max-height:330px;overflow:auto;}
.ofertas-page .ofertas-actions{display:flex;gap:6px;}
.ofertas-page .ofertas-code{font-weight:600;white-space:nowrap;}
.ofertas-page input[type=checkbox]{width:16px;height:16px;cursor:pointer;}
.ofertas-page .logoHeader{width:55px;height:55px;object-fit:contain;}
#modalLoteOfertas .ofertas-help{font-size:13px;color:#6c757d;}
#modalLoteOfertas .table{font-size:13px;}
#modalLoteOfertas .ofertas-preview{max-height:240px;overflow:auto;}
@media(max-width:1100px){.ofertas-page .ofertas-layout{grid-template-columns:minmax(0,1fr);}}
@media(max-width:640px){.ofertas-page{padding:8px;}.ofertas-page .ofertas-panel{padding:12px;}.ofertas-page .ofertas-tools select{width:100%;}}

#modalOfertasFamilias .modal-dialog{max-width:1450px;width:95%;}
#modalOfertasFamilias .modal-body{background:#f8fafb;}
#modalOfertasFamilias .ofertas-page{padding:0;}
#modalOfertasFamilias .familias-lista{max-height:360px;overflow:auto;}
#modalOfertasFamilias .familias-editor{background:#f5f3f9;border-color:#c6bdd7;}
#modalOfertasFamilias .familias-preview{max-height:200px;overflow:auto;}
@media(max-width:640px){#modalOfertasFamilias .modal-dialog{width:auto;margin:8px;}}
</style>
@endsection
@section('contenido')
<div class="ofertas-page">
  <div class="ofertas-heading">
    <div><h3>Ofertas</h3><p class="ofertas-muted mb-0">Organice los productos y aplique fechas, canales y descuentos en común.</p></div>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="descuento_familia()">Descuentos por familia</button>
  </div>
  <div class="ofertas-layout">
    <div>
      <section class="ofertas-panel ofertas-search">
        <h4>Buscar productos para ofertar</h4>
        <div class="mb-3">
          <label class="mr-3"><input type="radio" name="flexRadioDefault" value="cod_int" checked> Código interno</label>
          <label class="mr-3"><input type="radio" name="flexRadioDefault" value="oem"> OEM</label>
          <label><input type="radio" name="flexRadioDefault" value="cod_prov"> Código proveedor</label>
        </div>
        <label for="codigo_repuesto">Código del repuesto</label>
        <div class="input-group mb-2"><input type="text" class="form-control" id="codigo_repuesto" placeholder="Ingrese un código" onkeydown="if(event.key==='Enter'){event.preventDefault();buscar_repuesto();}"><div class="input-group-append"><button type="button" id="btn_buscar_ofertas" class="btn btn-primary" onclick="buscar_repuesto()">Buscar</button></div></div>
        <p id="mensajes" class="ofertas-muted mb-0" role="status" aria-live="polite">Puede agregar productos de varias búsquedas.</p>
        <div id="busqueda_resultado" class="d-none mt-3">
          <div class="table-responsive ofertas-resultados"><table class="table"><thead><tr><th>Producto</th><th>Precio</th><th></th></tr></thead><tbody id="tbody_resultados"></tbody></table></div>
        </div>
      </section>
      <section class="ofertas-panel ofertas-basket">
        <h4>Productos para nuevas ofertas <span id="cantidad_productos" class="badge badge-light">0</span></h4>
        <p class="ofertas-muted">Agregue los productos y cree sus ofertas con los mismos datos.</p>
        <div class="table-responsive ofertas-resultados"><table class="table"><tbody id="productos_lote"></tbody></table></div>
        <div class="ofertas-tools mt-3 mb-0"><button type="button" id="btn_crear_lote" class="btn btn-success btn-sm" onclick="abrir_lote('crear')" disabled>Crear ofertas en conjunto</button><button type="button" class="btn btn-outline-secondary btn-sm" onclick="vaciar_productos()">Vaciar selección</button></div>
      </section>
    </div>
    <section class="ofertas-panel">
      <h4>Ofertas registradas <span class="badge badge-light">{{ count($ofertas) }}</span></h4>
      <div class="ofertas-tools">
        <input id="filtro_ofertas" class="form-control form-control-sm" placeholder="Buscar código, descripción o usuario" aria-label="Buscar ofertas">
        <select id="filtro_estado" class="form-control form-control-sm" aria-label="Filtrar por estado"><option value="">Todos los estados</option><option value="vigente">Vigentes</option><option value="programada">Programadas</option><option value="caducada">Caducadas</option></select>
        <select id="filtro_canal" class="form-control form-control-sm" aria-label="Filtrar por canal"><option value="">Todos los canales</option><option value="1">Solo local</option><option value="2">Solo web</option><option value="3">Local + web</option><option value="4">Solo tótem</option><option value="5">Local + tótem</option><option value="6">Web + tótem</option></select>
      </div>
      <div class="ofertas-selection"><strong id="cantidad_seleccionadas" aria-live="polite">0 seleccionadas</strong><button type="button" id="btn_editar_lote" class="btn btn-primary btn-sm" onclick="abrir_lote('editar')" disabled>Editar seleccionadas</button><button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiar_seleccion()">Limpiar selección</button><span id="cantidad_visibles" class="ofertas-muted ml-auto"></span></div>
      <div class="table-responsive"><table class="table" id="tabla_ofertas"><thead><tr><th><input type="checkbox" id="seleccionar_visibles" aria-label="Seleccionar todas las ofertas visibles"></th><th>Producto</th><th>Vigencia</th><th>Precio oferta</th><th>Canal</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody id="tbody_nuevas_ofertas">
      @php
        $canales = [1=>'Solo local',2=>'Solo web',3=>'Local + web',4=>'Solo tótem',5=>'Local + tótem',6=>'Web + tótem'];
        $hoyOfertas = \Carbon\Carbon::today()->toDateString();
      @endphp
      @foreach($ofertas as $oferta)
        @php
          $inicio = \Carbon\Carbon::createFromFormat('d-m-Y', $oferta->desde)->toDateString();
          $fin = \Carbon\Carbon::createFromFormat('d-m-Y', $oferta->hasta)->toDateString();
          $estado = $inicio > $hoyOfertas ? 'programada' : ($fin < $hoyOfertas ? 'caducada' : 'vigente');
        @endphp
        <tr data-producto="{{ $oferta->id_repuesto }}" data-id="{{ $oferta->id }}" data-codigo="{{ $oferta->codigo_interno }}" data-descripcion="{{ $oferta->descripcion }}" data-usuario="{{ $oferta->name }}" data-estado="{{ $estado }}" data-local="{{ $oferta->local }}" data-desde="{{ $inicio }}" data-hasta="{{ $fin }}" data-precio="{{ $oferta->precio_antiguo }}" data-actualizado="{{ $oferta->precio_actualizado }}">
          <td><input type="checkbox" class="seleccion-oferta" value="{{ $oferta->id }}" aria-label="Seleccionar oferta {{ $oferta->codigo_interno }}"></td>
          <td><div class="ofertas-code">{{ $oferta->codigo_interno }}</div><div>{{ $oferta->descripcion }}</div><small class="ofertas-muted">{{ $oferta->name }}</small></td>
          <td>{{ $oferta->desde }}<br>{{ $oferta->hasta }}</td>
          <td><strong>$ {{ number_format($oferta->precio_actualizado,0,',','.') }}</strong><br><small class="ofertas-muted">Descuento $ {{ number_format($oferta->descuento,0,',','.') }}</small></td>
          <td>{{ $canales[$oferta->local] ?? 'Sin canal' }}</td>
          <td><span class="ofertas-status status-{{ $estado }}">{{ ucfirst($estado) }}</span></td>
          <td><div class="ofertas-actions"><button type="button" class="btn btn-outline-primary btn-sm" onclick="editar_oferta({{ $oferta->id }})">Editar</button><button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminar_oferta({{ $oferta->id }})" aria-label="Eliminar oferta {{ $oferta->codigo_interno }}">Eliminar</button></div></td>
        </tr>
      @endforeach
      </tbody></table></div>
      <p id="ofertas_sin_resultados" class="ofertas-empty d-none">No hay ofertas que coincidan con los filtros.</p>
    </section>
  </div>

</div>
<div class="modal fade" id="modalLoteOfertas" tabindex="-1" role="dialog" aria-labelledby="titulo_lote" aria-hidden="true"><div class="modal-dialog modal-lg" role="document"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="titulo_lote">Editar ofertas</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
  <div class="modal-body">
    <p id="ayuda_lote" class="ofertas-help"></p>
    <div class="row"><div class="col-md-4 form-group"><label for="lote_desde">Desde</label><input type="date" id="lote_desde" class="form-control"></div><div class="col-md-4 form-group"><label for="lote_hasta">Hasta</label><input type="date" id="lote_hasta" class="form-control"></div><div class="col-md-4 form-group"><label for="lote_local">Canal</label><select id="lote_local" class="form-control"><option value="">Mantener actual</option><option value="1">Solo local</option><option value="2">Solo web</option><option value="3">Local + web</option><option value="4">Solo tótem</option><option value="5">Local + tótem</option><option value="6">Web + tótem</option></select></div></div>
    <div class="row"><div class="col-md-6 form-group"><label for="lote_tipo">Descuento común</label><select id="lote_tipo" class="form-control"><option value="">Mantener descuento actual</option><option value="monto">Monto en pesos</option><option value="porcentaje">Porcentaje</option></select></div><div class="col-md-6 form-group"><label for="lote_valor">Valor del descuento</label><input type="number" min="0.01" step="0.01" id="lote_valor" class="form-control" disabled></div></div>
    <p class="ofertas-help">El descuento se calcula sobre el precio original de cada oferta; al crearla, sobre el precio de venta del producto.</p>
    <div id="error_lote" class="alert alert-danger d-none" role="alert"></div>
    <h6>Vista previa <span id="resumen_lote"></span></h6>
    <div class="table-responsive ofertas-preview"><table class="table table-sm"><thead><tr><th>Producto</th><th>Precio base</th><th>Precio oferta</th><th>Desde / hasta</th><th>Canal</th></tr></thead><tbody id="preview_lote"></tbody></table></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button type="button" id="btn_guardar_lote" class="btn btn-primary btn-sm" onclick="guardar_lote()">Aplicar cambios</button></div>
</div></div></div>
<div class="modal fade bd-example-modal-lg" id="modal_editar_descuento_familia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar el descuento a familia</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_editar_descuento">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" onclick="editar_nueva_familia()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
@include('ventas.partials.ofertas_familias_modal')
<input type="hidden" id="id_familia" value="">
@endsection
