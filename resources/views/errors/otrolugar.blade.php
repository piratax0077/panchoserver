@extends('maestro')
  @section('titulo','Crear Marca Vehículo')
  @section('javascript')
    <script type="text/javascript">
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
<center><h2>Guardar Imagenes en otro lugar</h2></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="marcavehiculo" method="post" action="{{ url('otrolugar') }}" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="row">
        <div class="col-md-3">
          <label>Marca del Vehiculo:</label>
            <input type="text" name="marcavehiculo" id="marcavehiculo" value="{{old('marcavehiculo')}}" class="form-control">
        </div>
        <div class="col-md-4">
            <label>Subir Foto (jpg,jpeg,png):</label>
            <input type="file" name="archivo" id="archivo" class="form-control-file">
        </div>
      </div>
      <br>

      <div class="row">
        <div class="col-md-2">
          <input type="submit" name="btnGuardarMarcaVehiculo" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($marcas->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-4 tabla-scroll-y-400">
              <table class="table table-hover">
                <thead>
                  <th width="56" scope="col">Logo</th>
                  <th width="32" scope="col">Marca</th>
                  <th width="59" scope="col"></th> <!-- ELIMINAR  -->
                </thead>
                <tbody>
                @foreach ($marcas as $marca)
                <tr>
                  <td><img src="{{asset('storage/'.$marca->urlfoto)}}" width=50px /></td>
                  <td>{{$marca->marcanombre}}</td>
                  <td>
    <a href="{{url('marcavehiculo/'.$marca->idmarcavehiculo.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                    </td>
                </tr>
                @endforeach
              </tbody>
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay Marcas de Vehículos Ingresados
        </div>

      @endif
  @endsection
