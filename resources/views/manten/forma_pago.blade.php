@extends('plantillas.app')
  @section('titulo','Crear Formas de Pago')
  @section('javascript')
    <script type="text/javascript">
        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido  eliminado correctamente!!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }
    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Crear Formas de Pago</h4></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="forma" method="post" action="{{ url('formapago') }}">
      {{ csrf_field() }}
      <div class="row">
        <div class="col-md-3">
          <label>Formas de Pago:</label>
            <input type="text" name="formapago" id="formapago" value="{{old('formapago')}}" class="form-control">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-2">
          <input type="submit" name="btnGuardarFormaPago" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($formas->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <table class="table table-hover" height="172">
                <thead>
                  <th width="56" scope="col">ID</th>
                  <th width="32" scope="col">Nombre</th>
                  <th width="59" scope="col"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($formas as $forma)
                <tr>
                  <td>{{$forma->id}}</td>
                  <td>{{$forma->formapago}}</td>
                  <td>
    <a href="{{url('formapago/'.$forma->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                    </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay Formas de Pago definidos
        </div>

      @endif
  @endsection
