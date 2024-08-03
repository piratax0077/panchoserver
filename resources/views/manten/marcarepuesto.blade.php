@extends('plantillas.app')
  @section('titulo','Crear Marcas de Repuesto')
  @section('javascript')
    <script type="text/javascript">
        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido  eliminado correctamente !!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }
    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Crear Marca de Repuestos</h4></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="marcarepuesto" method="post" action="{{ url('marcarepuesto') }}">
      {{ csrf_field() }}
      <input type="hidden" name="donde" value="marcarepuesto">
      <div class="row">
        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
          <label for="marcarepuesto">Marca del Repuesto:</label>
            <input type="text" name="marcarepuesto" id="marcarepuesto" size="20" maxlength="20" value="{{old('marcarepuesto')}}"class="form-control" style="width:100%">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
          <input type="submit" name="btnGuardarMarcaRepuesto" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($marcarepuestos->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-4 tabla-scroll-y-300">
              <table class="table table-hover" height="172">
                <thead>
                  <th width="56" scope="col">ID</th>
                  <th width="32" scope="col">Marca</th>
                  <th width="59" scope="col"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($marcarepuestos as $marcarepuesto)
                <tr>
                  <td>{{$marcarepuesto->id}}</td>
                  <td>{{$marcarepuesto->marcarepuesto}}</td>
                  <td>
    <a href="{{url('marcarepuesto/'.$marcarepuesto->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                    </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay Marcas de Repuestos definidos.
        </div>

      @endif
  @endsection
