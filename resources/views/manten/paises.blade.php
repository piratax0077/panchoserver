@extends('plantillas.app')
  @section('titulo','Crear Paises')
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

        window.onload=function() {
            $('#tabla-datos').DataTable({
                "scrollY":        "300px",
                "scrollCollapse": true,
                "paging":false,
                "searching":false,
                "info":false
            });
        };
    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Crear Países de Origen</h4></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="frmpais" method="post" action="{{ url('pais') }}">
      {{ csrf_field() }}
      <input type="hidden" name="donde" value="pais">
      <div class="row">
        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
          <label for="pais">Nombre del País de Origen:</label>
            <input type="text" name="pais" id="pais" size="20" maxlength="20" value="{{old('pais')}}"class="form-control" style="width:100%">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
          <input type="submit" name="btnGuardarPais" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($paises->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <table class="table table-hover" id="tabla-datos" height="10">
                <thead>
                  <th width="56" scope="col" style="padding-top:2px;padding-bottom:2px">ID</th>
                  <th width="32" scope="col" style="padding-top:2px;padding-bottom:2px">País</th>
                  <th width="59" scope="col" style="padding-top:2px;padding-bottom:2px"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($paises as $pais)
                <tr>
                  <td>{{$pais->id}}</td>
                  <td>{{$pais->nombre_pais}}</td>
                  <td>
    <a href="{{url('pais/'.$pais->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                    </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay Países definidos.
        </div>

      @endif
  @endsection
