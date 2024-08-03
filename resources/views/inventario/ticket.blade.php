@extends('plantillas.app')
@section('titulo','Ticket de requerimientos')
@section('contenido_titulo_pagina')
<div class="titulazo">
    <h4>Ticket</h4>
  </div>
 @endsection
@section('javascript')
<script>

var modifica=false;
      var elid=0;
        function eliminar(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }

        function modificar(id)
        {
          modifica=true;
          elid=id;
          var url='{{url("dameticket")}}'+'/'+id;

        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Obteniendo Parámetro...");
            },
        url:url,
        success:function(datos){
          $("#mensajes").html("Puede Modificar y guardar...");
          var d=JSON.parse(datos);
          document.getElementById("descripcion").value=d.descripcion;
          if(d.image_path.substring(0,4)=='foto')
          {
            document.getElementById("imagen_check").checked=true;
            document.getElementById("valor").value=d.image_path;
            var fot="<img src='"+"{{asset('storage')}}"+"/"+d.image_path+"' width='60px'>";
            $("#fotito").html(fot);
            ///document.getElementById("imagen").value=d.valor;
            document.getElementById("imagen").disabled=false;
            document.getElementById("valor").disabled=true;
          }else{
            document.getElementById("imagen").disabled=true;
            document.getElementById("valor").disabled=false;
            document.getElementById("imagen_check").checked=false;
            $("#fotito").html("");
            document.getElementById("valor").value=d.image_path;
          }

          },
          error: function(error){
            $('#mensajes').html(error.responseText);
          }

          }); //Fin ajax
        }
    function activarImagen()
        {
          var imagen_check=document.getElementById("imagen_check");
          var imagen=document.getElementById("imagen");
          var valor_text=document.getElementById("valor");
          valor_text.value="";
          imagen.value="";
          if(imagen_check.checked)
          {
            imagen.disabled=false;
            valor_text.disabled=true;
          }else{
            imagen.disabled=true;
            valor_text.disabled=false;
            valor_text.focus();
          }
        }

        function guardar()
        {

          var url="{{url('/guardar_ticket')}}";
      
          var descripcion=document.getElementById("descripcion").value;
          var valor=document.getElementById("valor").value;
          var imagen_check=document.getElementById("imagen_check");
          var imagen=$('#imagen')[0].files[0];
          var imagen_nombre=document.getElementById("imagen").value.trim();
          var estado = $('select[name=estado] option').filter(':selected').val();

          var datos=new FormData();
          datos.append('descripcion',descripcion);
  

          if(modifica)
          {
            datos.append('modifika',1);
            datos.append('ide',elid);
            datos.append('estado',estado);
            if(imagen_check.checked)
            {
              datos.append('imagen',imagen);
              datos.append('foto',1);
            }else{
              datos.append('valor',valor);
              datos.append('foto',0);
            }

          }else{
            datos.append('modifika',0);
            datos.append('estado', estado);
            if(imagen_check.checked)
            {
              if(imagen_nombre.length==0)
              {
                alert("Elija una Imagen");
                return false;
              }
              datos.append('imagen',imagen);
              datos.append('foto',1);
            }else{
              datos.append('valor',valor);
              datos.append('foto',0);
            }

          }

          $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
          });

          $.ajax({
           type:'POST',
           beforeSend: function () {
            $("#mensajes").html("Guardando, espere por favor...");
          },
          url:url,
          data:datos,
          cache:false,
          contentType:false,
          processData:false,
          success:function(resp){
            console.log(resp);
            if(resp=="OK")
            {
              $("#mensajes").html("OK...");
              modifica=false;
              elid=0;
              location.reload(true);
            }else{
              $("#mensajes").html(resp);
            }
          },
          error: function(error){
                var errores=JSON.parse(error.responseText);
                var salida="";
                for(var indice in errores)
                {
                  salida=salida+errores[indice]+"<br>";
                }
                $('#mensajes').html("<p style='color:red'>"+salida+"</p>");
              }
            });
        }

        function abrir_foto_modal(link)

          {

            var base='{{asset("storage/")}}';

            var enlace=base+'/'+link;

            $("#aqui_foto").html("<img src='"+enlace+"' width='100%' onmouseout='cerrar_foto_modal();'>");

            $('#foto-modal').modal({backdrop: 'static', keyboard: false})

            $("#foto-modal").modal("show");

          }

        function cerrar_foto_modal()

          {

            $("#foto-modal").modal("hide");

          }
</script>
@endsection

@section('style')
<style>
  .imagen_pequeña{
    width: 400px;
  }
</style>
@endsection

@section('contenido_ingresa_datos')
<div id="mensajes"></div>

<div class="container-fluid">
  <div class="busqueda_principal mb-3">
    <article>Sección dedicada al ingreso de solicitudes para el mantenimiento del sistema.</article>
    <h3>Instrucciones</h3>
    <ol>
      <li>Ingresar una descripción de los requerimientos funcionales que se solicitan.</li>
      <li>Seleccionar el estado de la solicitud.</li>
      <li>En caso de querer adjuntar una imagen se selecciona el checkbox correspondiente.</li>
      <li>Se activa el boton de buscar imagén, ante lo cual debe seleccionar la imagen.</li>
      <li>En caso de que este todo bien se presiona el botón Guardar.</li>
     
    </ol>
<hr>
  </div>
    

<div class="form-group">
    <div class="row mb-3 p-3" style="border: 1px solid black; border-radius: 10px;">
        
        <div class="col-sm-5">
            <label for="descripcion">Descripción:</label>
            <textarea  maxlength="900" id="descripcion" name="descripcion" class="form-control form-control-sm" placeholder="Se admiten máximo 900 caracteres."> </textarea>
        </div>
        <div class="col-sm-1">
          <label for="descripcion">Estado:</label>
          <select name="estado" id="estado" class="form-control">
            <option value="1">Ingresado</option>
            <option value="2">En proceso</option>
            <option value="3">Completado</option>
          </select>
      </div>
        <div class="col-sm-1">
          <div class="form-group form-control-sm" style="margin-top:20px">
            <input type="checkbox" class="form-check-input form-control-sm" id="imagen_check" onclick="activarImagen()">
            <label class="form-check-label" for="imagen">Es Imagen</label>
          </div>
        </div>
        <div class="col-sm-2">
            <label for="valor">Valor:</label>
            <input type="text" maxlength="200" id="valor" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3">
            <label>Subir Imagen (jpg,jpeg,png):</label>
            <input type="file" id="imagen" class="form-control-file" disabled>
        </div>
    </div>
      <div class="row">
          <div class="col-sm-4">
              <input type="submit" name="btnGuardarTicket" id="btnGuardarTicket" value="Guardar Ticket" onclick="guardar()" class="btn btn-info btn-sm"/>
          </div>
          <div class="col-sm-4">
          <a href="{{url('ticket')}}" class="btn btn-success btn-sm">Nuevo Ticket</a>
        </div>
          <div class="col-sm-4" id="fotito"></div>
      </div>
      <div class="container my-5">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Cantidad de tickets</th>
              <th scope="col">Completados</th>
              <th scope="col">Pendientes</th>
              <th scope="col">En proceso</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">{{$tickets->count()}}</th>
              <td>{{$completados}}</td>
              <td>{{$ingresados}}</td>
              <td>{{$en_proceso}}</td>
            </tr>
            
          </tbody>
        </table>
      </div>
  </div> <!-- fin form-group -->
  @if($tickets->count()>0)
  <div class="container-fluid">
      <div class="row">
        <div class="col-md-12 tabla-centrar tabla-scroll-y-500">
          <table class="table table-hover table-sm  letra-chica">
            <thead>
              <th width="40%" scope="col">Descripción</th>
              <th width="30%" scope="col">Imagen</th>
              <th width="10%" scope="col">Autor</th>
              <th width="10%" scope="col">Estado</th>
              <th width="5%" scope="col"></th> <!-- MODIFICAR -->
              <th width="5%" scope="col"></th> <!-- ELIMINAR -->
            </thead>
            @foreach ($tickets as $t)
            <tr>
              <td>{{$t->descripcion}}</td>
              @php
                $valoor=$t->image_path;
                $valor=substr($valoor,0,4);
               @endphp
              @if($valor=='foto')
              <td><a href="javascript:void(0)" onclick="abrir_foto_modal('{{$t->image_path}}')"><img src="{{asset('storage/'.$t->image_path)}}" alt="foto repuesto"  class="imagen_pequeña"> </a> </td>
              @else
              <td>{{$t->valor}}</td>
              @endif
              <td>{{$t->name}}</td>
              @if($t->estado == 1)
              <td>Ingresado</td>
              @elseif($t->estado == 2)
              <td>En proceso</td>
              @else
              <td>Completado</td>
              @endif
              
              <td>
                <a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="modificar({{$t->id}});">Modificar</a>
              </td>
              
              <td>
                <a href="{{url('eliminarticket/'.$t->id)}}" class="btn btn-danger btn-sm" onclick="return eliminar();">Eliminar</a>
              </td>
            </tr>
            @endforeach
          </table>
        </div>
      </div>
   </div>
  @else

    <div class="alert alert-danger">
      No hay tickets definidos
    </div>

  @endif
</div>

<div role="dialog" tabindex="-1" class="modal fade" id="foto-modal">

  <div class="modal-dialog" role="document" >

    <div class="modal-content">

      <div class="modal-body"> <!-- CONTENIDO -->

       <div id="aqui_foto"></div>

     </div> <!-- FIN DE modal-body -->

    </div> <!-- modal-content -->

  </div> <!-- modal-dialog -->

</div> <!-- FIN VENTANA MODAL FOTO GRANDE"-->
@endsection