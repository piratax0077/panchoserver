@extends('maestro')
  @section('titulo','Crear Modelo Vehículo')
  @section('javascript')
    <script type="text/javascript">
      function ver()
      {
        //al iniciar la página, no cargará los modelos automáticamente ya que son demasiados y ralentizan la página
        // por eso se cargarán solo los que se desean ver según la marca
        // campos id y porcentaje de la tabla familias
        var idFamilia=document.getElementById("familia").value;
        //Petición AJAX para obtener el porcentaje respectivo

        var url_familia='{{url("modelovehiculo")}}'+'/'+idMarca+'/ver';

          $.ajax({
            type:'GET',
            beforeSend: function () {
              $("#mensajes").html("Cargando modelos...");
            },
            url:url_familia,
            success:function(utilidad){

              $("#mensajes").html("Listo...");
            },
            error: function(error){
              $('#mensajes').html(error.responseText);
            }

          }); //Fin ajax

      }

        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido eliminado correctamente!!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }
    </script>
  @endsection
  @section('contenido_titulo_pagina')
    <center><h2>Crear Modelo de Vehículo</h2></center><br>
  @endsection
@section('contenido_ingresa_datos')

<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="modelovehiculo" method="post" action="{{ url('modelovehiculo') }}" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="row">
        <div class="col-md-2">
          <label for="sel1">Marca:</label>

          <select name="cboMarcaVehiculo" class="form-control form-control-sm" id="sel1">
            @foreach ($marcas as $marca)
               <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
            @endforeach
          </select>

        </div>
        <div class="col-md-4">
          <label>Modelo del Vehículo:
            <input type="text" name="modelovehiculo" value="{{old('modelovehiculo')}}"  id="modelovehiculo" class="form-control" size="100%">
          </label>

        </div>
        <div class="col-md-4">
            <label>Subir Foto (jpg,jpeg,png):</label>
            <input type="file" name="archivo" id="archivo" class="form-control-file">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-4">
          <button class="btn btn-success btn-md" id="btnVer" onclick="ver()">Ver Modelos</button>
        </div>
        <div class="col-md-2">
          <input type="submit" name="btnGuardarModeloVehiculo" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
  <hr>
    @if($modelos->count()>0)
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 tabla-scroll-y-400">
            <table class="table table-hover" id="tbl_modelos">
              <thead>
                <th width="10%" scope="col">Marca</th>
                <th width="60%" scope="col">Modelo</th>
                <th width="10%" scope="col">Imagen</th>
                <th width="10%" scope="col"></th> <!-- Modificar -->
                <th width="10%" scope="col"></th> <!-- Eliminar -->
              </thead>

              @foreach ($modelos as $modelo)
              <tr>
<!--
  ->marcamodelo es el método en el  modelo modelovehiculo
  ->marcanombre es el campo en el modelo marcavehiculo
-->
                <td>{{$modelo->marcavehiculo->marcanombre}}</td>
                <td>{{$modelo->modelonombre}}</td>
                <td><img src="{{asset('storage/'.$modelo->urlfoto)}}" width=80px /></td>
                <td>
                  <button class="btn btn-warning btn-sm" onclick="modificar({{$modelo->id}})">Modificar</button>
                </td>
                <td>
                  <a href="{{url('modelovehiculo/'.$modelo->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                </td>
              </tr>
              @endforeach
            </table>
          </div>
        </div>
      </div>
      @else
        <div class="alert alert-danger">
          <p>No hay Modelos de Vehículos Ingresados.</p>
        </div>
      @endif
  @endsection
