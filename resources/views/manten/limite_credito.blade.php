@extends('plantillas.app')
  @section('titulo','Límite de Crédito - Clientes')
  @section('javascript')
    <script type="text/javascript">
      var modifica=false;
      var elid=0;
        function eliminar(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido  eliminado correctamente!!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }

        function modificar(id,valor)
        {
          document.getElementById("valor").value=valor;
          document.getElementById("valor").focus();
          modifica=true;
          elid=id;
        }

        function guardar()
        {
          var valor=document.getElementById("valor").value;
          var url="{{url('limitecredito/guardar')}}";
          if(modifica)
          {
            var parametros={valor:valor,modifika:1,ide:elid};
          }else{
            var parametros={valor:valor,modifika:0};
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
          data:parametros,
          success:function(resp){
            if(resp=="OK")
            {
              $("#mensajes").html("OK...");
              document.getElementById("valor").value="";
              document.getElementById("valor").focus();
              modifica=0;
              elid=0;
              location.reload(true);
            }
              },
          error: function(xhr, status, error){
                var errorMessage = xhr.status + ': ' + xhr.statusText
                $('#mensajes').html(errorMessage);
              }
            });
        }


    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Límite de Crédito - Clientes</h4></center><br>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div id="mensajes"></div>
      <div class="row">
        <div class="col-sm-1">
          <label>Valor:</label>
            <input type="text" name="valor" size="10" id="valor" value="{{old('valor')}}" class="form-control">
        </div>
        <div class="col-sm-1">
          <input type="submit" name="btnGuardarLimite" id="btnGuardarLimite" value="Guardar" onclick="guardar()" style="margin-top: 25px" class="btn btn-info btn-sm"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($limites->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <table class="table table-hover" height="172">
                <thead>
                  <th width="56" scope="col">ID</th>
                  <th width="32" scope="col">Valor</th>
                  <th width="59" scope="col"></th> <!-- MODIFICAR -->
                  <th width="59" scope="col"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($limites as $limite)
                <tr>
                  <td>{{$limite->id}}</td>
                  <td>{{$limite->valor}}</td>
                  <td>
    <a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="modificar({{$limite->id}},{{$limite->valor}});">Modificar</a>
                  </td>
                  <td>
    <a href="{{url('limitecredito/'.$limite->id.'/borrar')}}" class="btn btn-danger btn-sm" onclick="return eliminar();">Eliminar</a>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

        <div class="alert alert-danger">
          No hay Límites definidos
        </div>

      @endif
  @endsection
