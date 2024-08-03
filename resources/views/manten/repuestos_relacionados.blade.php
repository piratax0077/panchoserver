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
    var url='{{url("ventas")}}'+'/'+id_marca+'/y/'+id_modelo;
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

</style>
@endsection

  @section('contenido_titulo_pagina')
<center><h2>Repuestos Relacionados</h2></center><br>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
    <div id="mensajes"></div>
    <div class="row">
      <div class="col-sm-3" style="background-color: sandybrown">
          <input type="hidden" value="ninguno" id="elegido">
          <input type="hidden" value="ninguno" id="id_principal">
          <input type="radio" name="repuesto_op" id="radio_principal" value="principal" checked onclick="limpiar_elegido_principal();"/> Principal<br>
          <input type="radio" name="repuesto_op" id="radio_relacionado" value="relacionado" />Relacionado<br /><br>
          <button onclick="buscar_repuesto()" id="btnAgregarRepuesto" class="btn btn-success btn-sm">Buscar Repuesto</button>
      </div>
      <div class="col-sm-9" id="zona_repuesto_principal">
              <br><br><br><p>< < < < Busque un repuesto principal para relacionarlo con otros</p>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12" id="zona_repuestos_relacionados"> <!-- Lista de relacionados -->

      </div>
    </div>
</div> <!-- fin container-fluid -->
  @endsection

  @section('contenido_ver_datos')

<!-- Ventana modal ELEGIR REPUESTO -->
<div role="dialog" tabindex="-1" class="modal fade" id="buscar-repuesto-modal">
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
           <input type="submit" id="btnBuscarRepuestos" name="btnBuscarRepuestos" value="Buscar" class="btn btn-primary btn-sm" onclick="damefamilias()" style="margin-top: 5px"/>
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
       <div class="col-sm-8" id="mensajes-modal"></div>
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
