@extends('plantillas.app')
  @section('titulo','Parámetros del Sistema')
  @section('javascript')
    <script type="text/javascript">
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
          var url='{{url("dameparametro")}}'+'/'+id;

        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Obteniendo Parámetro...");
            },
        url:url,
        success:function(datos){
          $("#mensajes").html("Puede Modificar y guardar...");
          var d=JSON.parse(datos);
          document.getElementById("codigo").value=d.codigo;
          document.getElementById("nombre").value=d.nombre;
          document.getElementById("descripcion").value=d.descripcion;
          if(d.valor.substring(0,4)=='foto')
          {
            document.getElementById("imagen_check").checked=true;
            document.getElementById("valor").value=d.valor;
            var fot="<img src='"+"{{asset('storage')}}"+"/"+d.valor+"' width='60px'>";
            $("#fotito").html(fot);
            ///document.getElementById("imagen").value=d.valor;
            document.getElementById("imagen").disabled=false;
            document.getElementById("valor").disabled=true;
          }else{
            document.getElementById("imagen").disabled=true;
            document.getElementById("valor").disabled=false;
            document.getElementById("imagen_check").checked=false;
            $("#fotito").html("");
            document.getElementById("valor").value=d.valor;
          }

          },
          error: function(error){
            $('#mensajes').html(error.responseText);
          }

          }); //Fin ajax
        }

        function guardar()
        {

          var url="{{url('parametros/guardar')}}";
          var codigo=document.getElementById("codigo").value;
          var nombre=document.getElementById("nombre").value;
          var descripcion=document.getElementById("descripcion").value;
          var valor=document.getElementById("valor").value;
          var imagen_check=document.getElementById("imagen_check");
          var imagen=$('#imagen')[0].files[0];
          var imagen_nombre=document.getElementById("imagen").value.trim();


          var datos=new FormData();
          datos.append('codigo',codigo);
          datos.append('nombre',nombre);
          datos.append('descripcion',descripcion);



          if(modifica)
          {
            datos.append('modifika',1);
            datos.append('ide',elid);

            if(imagen_check.checked)
            {
              /*
              if(imagen_nombre.length==0)
              {
                alert("Elija una Imagen.");
                return false;
              }
              */
              datos.append('imagen',imagen);
              datos.append('foto',1);
            }else{
              datos.append('valor',valor);
              datos.append('foto',0);
            }

          }else{
            datos.append('modifika',0);
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

    </script>
@endsection
@section('style')
<style>
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

.tabla-centrar{
    display:flex;
    justify-content: center;
}
</style>
@endsection

  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Parámetros del Sistema</h4></center><br>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div id="mensajes"></div>
  <div class="form-group">
    <div class="row">
        <div class="col-sm-2">
            <label for="codigo">Código:</label>
            <input type="text" maxlength="20" id="codigo"  class="form-control form-control-sm">
        </div>
        <div class="col-sm-2">
            <label for="nombre">Nombre:</label>
            <input type="text" maxlength="50" id="nombre" class="form-control form-control-sm">
        </div>
        <div class="col-sm-2">
            <label for="descripcion">Descripción:</label>
            <input type="text" maxlength="200" id="descripcion" class="form-control form-control-sm">
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
              <input type="submit" name="btnGuardarParametro" id="btnGuardarParametro" value="Guardar Parámetro" onclick="guardar()" class="btn btn-info btn-sm"/>
          </div>
          <div class="col-sm-4">
          <a href="{{url('parametros')}}" class="btn btn-success btn-sm">Nuevo Parámetro</a>
        </div>
          <div class="col-sm-4" id="fotito"></div>
      </div>

  </div> <!-- fin form-group -->
</div> <!-- fin container-fluid -->
  @endsection

  @section('contenido_ver_datos')
    @if($parametros->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-12 tabla-centrar tabla-scroll-y-500">
              <table class="table table-hover table-sm  letra-chica">
                <thead>
                  <th width="15%" scope="col">Cod</th>
                  <th width="20%" scope="col">Nombre</th>
                  <th width="30%" scope="col">Descripción</th>
                  <th width="20%" scope="col">Valor</th>
                  <th width="5%" scope="col"></th> <!-- MODIFICAR -->
                  <th width="5%" scope="col"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($parametros as $parametro)
                <tr>
                  <td>{{$parametro->codigo}}</td>
                  <td>{{$parametro->nombre}}</td>
                  <td>{{$parametro->descripcion}}</td>
                  @php
                    $valoor=$parametro->valor;
                    $valor=substr($valoor,0,4);
                   @endphp
                  @if($valor=='foto')
                  <td><img src="{{asset('storage/'.$parametro->valor)}}" width=50px /></td>
                  @else
                  <td>{{$parametro->valor}}</td>
                  @endif
                  <td>
    <a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="modificar({{$parametro->id}});">Modificar</a>
                  </td>
                  <td>
    <a href="{{url('eliminarparametro/'.$parametro->id)}}" class="btn btn-danger btn-sm" onclick="return eliminar();">Eliminar</a>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay parámetros definidos
        </div>

      @endif
  @endsection
