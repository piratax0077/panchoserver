@extends('plantillas.app')
  @section('titulo','Repuestos Relacionados')
  @section('javascript')
    <script type="text/javascript">
      var modifica=false;
      var elid=0;
        function eliminar(idrel){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            eliminar_relacionado(idrel)
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }

        function cargarModelos()
        {
          $('#Modelo option').remove();
          var xMarca=document.getElementById('Marca').value;

          @foreach($modelos as $modelo)
            var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
            if(idMarca==xMarca)
            {
              $('#Modelo').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
            }
          @endforeach
        }

function elegir(idrep)
{
  var rep=document.getElementById('elegido');
  rep.value=$('input:radio[name=repuesto_op]:checked').val(); //para saber cual de los radio esta seleccionado (JQUERY)
  if(rep.value=="principal")
  {
    elegir_principal(idrep);
  }else{ //relacionado
    var id_principal=document.getElementById('id_principal').value;
    if(id_principal=="ninguno")
    {
      alert("Elija un repuesto principal para relacionar");
    }else{
      elegir_relacionado(idrep,id_principal);
    }
  }
}

function limpiar_elegido_principal()
{
  location.href = "{{url('relacionados')}}";
}

function elegir_principal(idrep)
{
    var url='{{url("relacionadoprincipal")}}'+'/'+idrep;
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes").html("Buscando...");
          },
       url:url,
       success:function(repuestos){
        $("#zona_repuesto_principal").html(repuestos);
        document.getElementById('id_principal').value=idrep;
        //Después de seleccionar el principal, automáticamente seleccionar el radio relacionado
        document.getElementById('radio_relacionado').checked=true;
        dame_relacionados(idrep);
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
      $("#zona_repuesto_principal").html(errorMessage);
        }

      });
}

function elegir_relacionado(id_relacionado,id_principal)
{
  var url='{{url("relacionadoguardar")}}'+'/'+id_relacionado+'/'+id_principal;
  $.ajax({
    type:'GET',
    beforeSend: function () {
    $("#mensajes").html("Guardando Relacionado...");
      },
    url:url,
    success:function(relacionados){
      dame_relacionados(id_principal);
    $("#mensajes").html(relacionados);

    },
    error: function(xhr, status, error){
  var errorMessage = xhr.status + ': ' + xhr.statusText
  $("#zona_repuestos_relacionados").html(errorMessage);
    }

  });
}

function dame_relacionados(idrep)
{
  var url='{{url("damerelacionados")}}'+'/'+idrep;
  $.ajax({
    type:'GET',
    beforeSend: function () {
    $("#mensajes").html("Relacionados...");
      },
    url:url,
    success:function(relacionados){
    $("#zona_repuestos_relacionados").html(relacionados);

    },
    error: function(xhr, status, error){
  var errorMessage = xhr.status + ': ' + xhr.statusText
  $("#zona_repuestos_relacionados").html(errorMessage);
    }

  });
}

function eliminar_relacionado(idrel)
{
  var url='{{url("eliminarrelacionado")}}'+'/'+idrel;
  $.ajax({
    type:'GET',
    beforeSend: function () {
    $("#mensajes").html("Eliminar Relacionado...");
      },
    url:url,
    success:function(relacionados){
      $("#zona_repuestos_relacionados").html(relacionados);
      $("#mensajes").html("<strong>Relacionado Eliminado...</strong>");
    },
    error: function(xhr, status, error){
  var errorMessage = xhr.status + ': ' + xhr.statusText
  $("#zona_repuestos_relacionados").html(errorMessage);
    }

  });
}

  function damefamilias()
  {
    var id_marca=document.getElementById("Marca").value
    var id_modelo=document.getElementById("Modelo").value
    // Petición
    var url='{{url("relacionados")}}'+'/'+id_marca+'/y/'+id_modelo+'/familias';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(repuestos){
        $("#mensajes-modal").html("Listo...");
        $("#zona_familia").html(repuestos);
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
            $('#zona_grilla').html(errorMessage);
        }

      }); //Fin petición

  }

function damerepuestos(id_familia)
  {
    // Petición
    var id_marca=document.getElementById("Marca").value
    var id_modelo=document.getElementById("Modelo").value
    var url='{{url("relacionados")}}'+'/'+id_familia+'/'+id_marca+'/'+id_modelo+'/damerepuestos';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(repuestos){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(repuestos);

       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
            $('#zona_grilla').html(errorMessage);
        }

      }); //Fin petición

  }


  function mas_detalle(id_repuesto)
  {
    dame_fotos(id_repuesto);
    dame_similares(id_repuesto);
    dame_oems(id_repuesto);
  }

  function dame_fotos(id_repuesto)
  {
      var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefotos';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(fotos){
        $("#mensajes-modal").html("Listo...");
        $("#zona_fotos").html(fotos);
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
            $('#zona_fotos').html(errorMessage);
        }

      }); //Fin petición
  }

  function abrir_foto_modal(link)
  {
    var base='{{asset("storage/")}}';
    var enlace=base+'/'+link;
    $("#aqui_foto").html("<img src='"+enlace+"' width='800px'>");
    $("#foto-modal").modal("toggle");
  }

  function dame_similares(id_repuesto)
  {
      var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damesimilares';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(similares){
        $("#mensajes-modal").html("Listo...");
        $("#zona_similares").html(similares);
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
            $('#zona_similares').html(errorMessage);
        }

      }); //Fin petición
  }

  function dame_oems(id_repuesto)
  {
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/dameoems';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(oems){
        $("#mensajes-modal").html("Listo...");
        $("#zona_oem").html(oems);
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText
            $('#zona_oem').html(errorMessage);
        }

      }); //Fin petición
  }

  function buscar_repuesto()
  {
    var id_principal=document.getElementById('id_principal').value;
    var elegido=document.getElementById("elegido");
    elegido.value=$('input:radio[name=repuesto_op]:checked').val(); //para saber cual de los radio esta seleccionado (JQUERY)

    if(elegido.value=="relacionado" && id_principal=="ninguno")
    {
      alert("Elija primero un repuesto principal para relacionar");
      return false;
    }
    $("#mod_titulo_header").html("ELEGIR REPUESTO "+elegido.value.toUpperCase());
    $("#buscar-repuesto-modal").modal("show");
  }




//Al cerrar la ventana modal de buscar repuestos
// $("#buscar-repuesto-modal").on('hidden.bs.modal', function () {
//       //hacer algo al cerrar el modal
//       alert('Cerrando');
//     });


    </script>
@endsection
@section('style')
<style>
  .modal-header-40 {
    background-color: #4146D8;
    color: white;
    height: 40px;
}

.modal-ventas {
    width: 95%;
}

.modal-body-alto {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 40px);
}

.col-sm-1,
.col-sm-2,
.col-sm-3
{
  padding-left:3px;
  padding-right:3px;
}

.col-sm-2
{
  width:14%;
}

.rel-page{padding:22px 18px 34px;background:#f4f6f9;min-height:calc(100vh - 185px)}
.rel-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}.rel-heading h3{margin:0;color:#1f2937;font-size:24px;font-weight:700}.rel-heading p{margin:4px 0 0;color:#64748b;font-size:14px}
.rel-workflow{display:grid;grid-template-columns:290px minmax(0,1fr);gap:16px;align-items:stretch}.rel-card{background:#fff;border:1px solid #dbe4ee;border-radius:12px;box-shadow:0 4px 14px rgba(15,23,42,.07);overflow:hidden}.rel-card__header{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #e2e8f0;background:#f8fafc;color:#334155;font-size:15px;font-weight:700}
.rel-step{display:flex;align-items:flex-start;gap:11px;padding:15px 16px;cursor:pointer;margin:0;border-bottom:1px solid #eef2f7}.rel-step input{margin-top:4px}.rel-step__number{display:flex;align-items:center;justify-content:center;flex:0 0 28px;width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#4338ca;font-size:13px;font-weight:700}.rel-step__copy strong{display:block;color:#1e293b;font-size:14px}.rel-step__copy small{display:block;color:#64748b;line-height:1.4;margin-top:2px}.rel-actions{padding:16px}.rel-actions .btn{width:100%;font-weight:600}
.rel-principal{padding:18px;min-height:190px}.rel-placeholder{height:100%;min-height:150px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;color:#94a3b8;border:2px dashed #dbe4ee;border-radius:10px;padding:24px}.rel-placeholder i{font-size:28px;margin-bottom:10px;color:#818cf8}.rel-message{margin-bottom:14px}.rel-message:empty{display:none}#zona_repuestos_relacionados{margin-top:16px}#zona_repuestos_relacionados>.row{margin:0}.rel-page table{margin-bottom:0}.rel-page thead th{background:#e6f7f4;color:#334155;border:0;vertical-align:middle}.rel-page tbody td{vertical-align:middle}
.modal-ventas{width:94%;max-width:1180px;margin:30px auto}.rel-modal .modal-content{border:0;border-radius:12px;overflow:hidden;box-shadow:0 18px 50px rgba(15,23,42,.28)}.rel-modal .modal-header{height:auto;padding:15px 20px;background:#111827;color:#fff;border:0;display:flex;align-items:center;justify-content:space-between}.rel-modal .modal-title{margin:0;font-size:17px;font-weight:700}.rel-modal .close{order:2;color:#fff;opacity:.8;text-shadow:none;margin:0;padding:0}.rel-modal .modal-body{padding:0;background:#f8fafc;max-height:72vh;overflow-y:auto}
.rel-search-layout{display:grid;grid-template-columns:300px minmax(0,1fr);min-height:480px}.rel-search-sidebar{padding:18px;background:#fff;border-right:1px solid #e2e8f0}.rel-search-sidebar label{font-size:12px;text-transform:uppercase;letter-spacing:.35px;font-weight:700;color:#64748b;margin:12px 0 5px}.rel-search-sidebar label:first-child{margin-top:0}.rel-search-sidebar .btn{width:100%;margin-top:14px;font-weight:600}#zona_familia{margin:16px 0 0;max-height:285px;overflow:auto}
.rel-results{padding:18px;min-width:0}.rel-results__grid{background:#fff;border:1px solid #e2e8f0;border-radius:9px;min-height:185px;overflow:hidden}.rel-details{display:grid;grid-template-columns:1fr 1.25fr .8fr;gap:12px;margin-top:14px}.rel-detail-box{background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:12px;min-height:115px;overflow:auto}.rel-detail-box__title{font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:8px}.rel-modal .modal-footer{padding:10px 18px;min-height:48px;background:#fff}.rel-status{color:#64748b;font-size:13px}.rel-status:empty{display:none}
.rel-modal #mod_titulo_header{margin:0;flex:1;text-align:left!important;font-size:17px;font-weight:700}.rel-modal .modal-body:after{content:"";display:table;clear:both}.rel-modal .modal-body>.col-sm-3{width:28%;padding:18px!important;background:#fff!important;border-right:1px solid #e2e8f0;min-height:480px}.rel-modal .modal-body>.col-sm-9{width:72%;padding:18px!important;background:#f8fafc!important;min-height:480px}.rel-modal #zona_elegir{margin:0!important}.rel-modal #zona_elegir label{display:block;width:100%;font-size:12px;text-transform:uppercase;letter-spacing:.35px;font-weight:700;color:#64748b;margin:12px 0 5px}.rel-modal #zona_elegir label:first-child{margin-top:0}.rel-modal #btnBuscarRepuestos{width:100%;margin-top:14px!important;font-weight:600}.rel-modal #zona_grilla{margin:0;min-height:190px;background:#fff;border:1px solid #e2e8f0;border-radius:9px;overflow:auto}.rel-modal #zona_detalle{margin:14px 0 0;display:flex;gap:10px}.rel-modal #zona_detalle>div{flex:1;width:auto!important;min-height:115px;padding:12px!important;background:#fff!important;border:1px solid #e2e8f0;border-radius:9px;color:#64748b}.rel-modal #zona_familia{width:100%}
@media(max-width:767.98px){.rel-workflow,.rel-search-layout{grid-template-columns:1fr}.rel-details{grid-template-columns:1fr}.rel-search-sidebar{border-right:0;border-bottom:1px solid #e2e8f0}.modal-ventas{width:auto;margin:12px}.rel-heading{align-items:flex-start;flex-direction:column}}

</style>
@endsection

@section('contenido_titulo_pagina')
@endsection
@section('contenido_ingresa_datos')
<div class="rel-page">
  <div class="rel-heading">
    <div>
      <h3><i class="fas fa-link mr-2 text-primary"></i>Repuestos relacionados</h3>
      <p>Define un repuesto principal y asocia alternativas o equivalencias para facilitar la venta.</p>
    </div>
  </div>

  <div id="mensajes" class="rel-message"></div>
  <input type="hidden" value="ninguno" id="elegido">
  <input type="hidden" value="ninguno" id="id_principal">

  <div class="rel-workflow">
    <aside class="rel-card">
      <div class="rel-card__header"><i class="fas fa-list-ol text-primary"></i> Flujo de asociación</div>
      <label class="rel-step" for="radio_principal">
        <input type="radio" name="repuesto_op" id="radio_principal" value="principal" checked onclick="limpiar_elegido_principal();">
        <span class="rel-step__number">1</span>
        <span class="rel-step__copy"><strong>Repuesto principal</strong><small>Selecciona el producto que agrupará las relaciones.</small></span>
      </label>
      <label class="rel-step" for="radio_relacionado">
        <input type="radio" name="repuesto_op" id="radio_relacionado" value="relacionado">
        <span class="rel-step__number">2</span>
        <span class="rel-step__copy"><strong>Repuesto relacionado</strong><small>Agrega una alternativa al principal seleccionado.</small></span>
      </label>
      <div class="rel-actions">
        <button type="button" onclick="buscar_repuesto()" id="btnAgregarRepuesto" class="btn btn-primary">
          <i class="fas fa-search mr-1"></i> Buscar repuesto
        </button>
      </div>
    </aside>

    <section class="rel-card">
      <div class="rel-card__header"><i class="fas fa-box text-primary"></i> Repuesto principal seleccionado</div>
      <div class="rel-principal" id="zona_repuesto_principal">
        <div class="rel-placeholder"><i class="fas fa-box-open"></i><strong>Aún no has elegido un repuesto principal</strong><span>Usa el botón “Buscar repuesto” para comenzar.</span></div>
      </div>
    </section>
  </div>

  <div id="zona_repuestos_relacionados"></div>
</div>
  @endsection

  @section('contenido_ver_datos')

<!-- Ventana modal ELEGIR REPUESTO -->
<div role="dialog" tabindex="-1" class="modal fade rel-modal" id="buscar-repuesto-modal">
  <div class="modal-dialog modal-lg modal-ventas" role="document" >
    <div class="modal-content">


      <div class="modal-header modal-header-40"> <!-- CABECERA -->
       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
       <p class="text-center" id="mod_titulo_header">ELEGIR REPUESTO</p>

      </div> <!-- FIN CABECERA -->

      <div class="modal-body modal-body-alto"> <!-- CONTENIDO -->

<!-- Columna 1 : elegir -->
       <div class="col-sm-3" style="background-color: #F2F5A9;">
         <div class="row" id="zona_elegir" style="margin-left:1px;margin-right:1px">
           <label for="Marca">Marca:</label>
           <select name="cMa" class="form-control form-control-sm" id="Marca" onchange="cargarModelos()">
             @foreach ($marcas as $marca)
               <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
             @endforeach
           </select>
           <label for="Modelo">Modelo:</label>
           <select name="cMo" id="Modelo" class="form-control form-control-sm">
             <option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>
           </select>
           <button type="button" id="btnBuscarRepuestos" class="btn btn-primary btn-sm" onclick="damefamilias()"><i class="fas fa-filter mr-1"></i> Buscar familias</button>
         </div>
         <div class="row row-40" id="zona_familia"></div>

         </div> <!-- FIN Columna 1 : elegir -->


<!-- Columna 2 : grilla y detalles -->
       <div class="col-sm-9" style="background-color: #81BEF7;">
         <div class="row row-50" id="zona_grilla"></div>
         <div class="row row-40" id="zona_detalle">
           <div class="col-sm-4" id="zona_fotos" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px">
             fotos
           </div>
           <div class="col-sm-5" id="zona_similares" style="background-color: #b3e6ff;padding-left: 1px;padding-right: 1px">
             similares
           </div>
           <div class="col-sm-3" id="zona_oem" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px">
             oem
           </div>

         </div>
       </div><!-- FIN Columna 2 : grilla y detalles -->

     </div> <!-- FIN DE modal-body -->

      <div class="modal-footer"> <!-- PIE -->
       <div class="col-sm-8 rel-status" id="mensajes-modal"></div>
         <div class="col-sm-4">

         </div>
      </div>

    </div> <!-- modal-content -->


  </div> <!-- modal-dialog -->
</div> <!-- Fin ventana modal -->

<!-- VENTANA MODAL FOTO GRANDE"-->
<div role="dialog" tabindex="-1" class="modal fade" id="foto-modal">
  <div class="modal-dialog modal-lg" role="document" >
    <div class="modal-content">
       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="aqui_foto"></div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL FOTO GRANDE"-->

  @endsection
