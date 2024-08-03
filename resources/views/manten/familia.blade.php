@extends('plantillas.app')
  @section('titulo','Crear Familia de Repuestos')
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

        function modificar(id_fam){
            document.getElementById("modificando").value=1;
            document.getElementById("id_familia").value=id_fam;
            document.getElementById("prefijo").disabled=true;
            document.getElementById("nombrefamilia").value=document.getElementById("nf-"+id_fam).value;
            document.getElementById("porcentaje").value=document.getElementById("po-"+id_fam).value;
            document.getElementById("porcentaje_flete").value=document.getElementById("pf-"+id_fam).value;
            document.getElementById("prefijo").value=document.getElementById("pr-"+id_fam).value;
        }
    </script>
  @endsection
  @section('style')
    <style>
        body{
            background-color:rgb(255, 253, 243);
        }
    </style>
  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Crear Familia de Repuestos</h4></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form class="form-horizontal" name="familia" method="post" action="{{ url('familia') }}">
      {{ csrf_field() }}
      <input type="hidden" name="donde" value="familia">
      <input type="hidden" name="modificando" id="modificando" value="0">
      <input type="hidden" name="id_familia" id="id_familia" value="0">
      <div class="row">
        <div class="col-3">
          <label for="nombrefamilia">Nombre de la Familia de Repuestos:</label>
            <input type="text" name="nombrefamilia" value="{{old('nombrefamilia')}}"  id="nombrefamilia" class="form-control">
        </div>
        <div class="col-1">
          <label for="porcentaje">% Familia:</label>
            <input maxlength="2" type="text" name="porcentaje" value="{{old('porcentaje')}}" id="porcentaje" class="form-control" style="width:80px">
        </div>
        <div class="col-1">
            <label for="porcentaje_flete">% Flete:</label>
              <input maxlength="2" type="text" name="porcentaje_flete" value="{{old('porcentaje_flete')}}" id="porcentaje_flete" class="form-control" style="width:80px">
          </div>
        <div class="col-1">
          <label for="prefijo">Prefijo:</label>
            <input maxlength="4" type="text" value="{{old('prefijo')}}" name="prefijo" id="prefijo" class="form-control">
        </div>
        <div class="col-2">
          <input type="submit" name="btnGuardarFamilia" id="button" value="Guardar" class="btn btn-primary" style="margin-top:25px"/>
        </div>
      </div>
    </form>
</div>
  @endsection

  @section('contenido_ver_datos')
    @if($familias->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-8 col-sm-8 col-lg-8 tabla-scroll-y-500" >
              <table class="table table-hover">
                <thead>
                  <th width="5%" scope="col">ID</th>
                  <th width="30%" scope="col">Familia</th>
                  <th width="20%" scope="col">Porcentaje</th>
                  <th width="20%" scope="col">Porcentaje Flete</th>
                  <th width="20%" scope="col">Prefijo</th>
                  <th width="10%" scope="col"></th> <!-- MODIFICAR -->
                  <th width="10%" scope="col"></th> <!-- ELIMINAR -->
                  <th width="10%" scope="col"></th>
                </thead>
                <tbody>
                @foreach ($familias as $familia)
                <tr>
                  <input type="hidden" id="nf-{{$familia->id}}" value="{{$familia->nombrefamilia}}">
                  <input type="hidden" id="po-{{$familia->id}}" value="{{$familia->porcentaje}}">
                  <input type="hidden" id="pf-{{$familia->id}}" value="{{$familia->porcentaje_flete}}">
                  <input type="hidden" id="pr-{{$familia->id}}" value="{{$familia->prefijo}}">
                  <td>{{$familia->id}}</td>
                  <td>{{$familia->nombrefamilia}}</td>
                  <td class="text-center">{{$familia->porcentaje}}</td>
                  <td class="text-center">{{$familia->porcentaje_flete}}</td>
                  <td class="text-center">{{$familia->prefijo}}</td>
                  <td>
                    <button class="btn btn-warning btn-sm" onclick="modificar('{{$familia->id}}')">Modificar</button>
                  </td>
                  <td>
                        <a href="{{url('familia/'.$familia->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                  </td>
                  <td>
                    <a href="{{url('repuesto/descargar-repuestos-familia/'.$familia->id)}}" class="btn btn-primary btn-sm"><i class="fa-solid fa-file-excel"></i></a>
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
          No hay Familias de Repuestos definidas.
        </div>

      @endif
  @endsection
