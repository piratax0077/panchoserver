@extends('plantillas.app')
  @section('titulo','Crear Repuestos')

  @section('javascript')

<script type="text/javascript">
function cargarModelos()
{
  $('#ModeloSim option').remove();
  var xMarca=document.getElementById('MarcaSim').value;

  @foreach($modelos as $modelo)
    var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
    if(idMarca==xMarca)
    {
      $('#ModeloSim').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
    }
  @endforeach
}

function click_btn_oem()
{
  document.getElementById("btnGuardarSimilar").disabled=true;
  document.getElementById("btnAgregarOEMs").disabled=true;
  document.getElementById("codigos_OEM").readOnly=false;
  document.getElementById("codigos_OEM").focus();

}

function agregar_OEM()
{
  var cod_OEM=document.getElementById("codigos_OEM").value;
  var id_repuesto=document.getElementById("id_repuesto").value;
  var url="{{url('repuesto/guardaOEM')}}";
  var parametros={cod_OEM:cod_OEM,id_repuesto:id_repuesto};

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        $("#msje_oem").html("Guardando, espere por favor...");
      },
      url:url,
      data:parametros,
      success:function(data){
        $("#msje_oem").html("OEM Guardado.");
        document.getElementById("codigos_OEM").value="";
        document.getElementById("codigos_OEM").focus();
        $("#tabla_OEM").html(data);


      },
      error: function(xhr, status, error){
        var errorMessage = xhr.status + ': ' + xhr.statusText
        $('#msje_oem').html(errorMessage);
      }

    });
}


</script>

  @endsection

  @section('contenido_titulo_pagina')
  <div class="row" style="background-color:#FAC47F;">
    <center><h3>Crear Repuestos</h3></center>
  </div>
 @endsection
  @section('contenido_ingresa_datos')
    @include('fragm.mensajes')
    @if(!empty($fotos))
      @if(count($fotos)>0)
      <div class="row" style="background-color:#FAC47F;">
      <div class="col-4">
        <p><a href="{{ url('repuesto/create') }}" class="btn btn-warning btn-md active" role="button">Nuevo Repuesto</a></p>
      </div>
    </div>
      @endif
    @endif
<div class="container-fluid">
  <div class="row"> <!-- ROW PRINCIPAL -->
<div class="col-md-3" style="background-color:#FFFF7F;">
      <div class="row">
        <p class="text-center"><strong>DATOS DEL REPUESTO</strong></p>
      </div>
        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Familia:</label>
              <p>{{isset($datos['familia']) ? $datos['familia'] : old('familia') }}</p>
          </div>
          <div class="col-md-4">
            <label>Marca:</label>
            <p>{{isset($datos['marcavehiculo']) ? $datos['marcavehiculo'] : old('marcavehiculo') }}</p>
          </div>
          <div class="col-md-4">
            <label>Modelo:</label>
              <p>{{isset($datos['modelovehiculo']) ? $datos['modelovehiculo'] : old('modelovehiculo') }}</p>
          </div>
        </div> <!--FIN DEL PRIMER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-10">
            <label>Marca Rpto:</label>
            <p>{{isset($datos['marcarepuesto']) ? $datos['marcarepuesto'] : old('marcarepuesto') }}</p>
          </div>
        </div>
        <div class="row" style="margin-top:10px">
          <div class="col-md-10">
            <label>Proveedor:</label>
            <p>{{isset($datos['proveedor']) ? $datos['proveedor'] : old('proveedor') }}</p>
          </div>
        </div>
        <div class="row" style="margin-top:10px">
          <div class="col-md-8">
            <label>Pais Origen:</label>
              <p>{{isset($datos['pais']) ? $datos['pais'] : old('pais') }}</p>
          </div>
        </div> <!--FIN DEL SEGUNDO ROW COL 1 -->
<div class="row">
<div class="col-md-10">
            <label>Descripción:</label>
              <p>{{isset($datos['descripcion']) ? $datos['descripcion'] : old('descripcion') }}</p>
          </div>

</div>

        <div class="row" style="margin-top:10px">
          <div class="col-md-6">
            <label>Medidas:</label>
              <p>{{isset($datos['medidas']) ? $datos['medidas'] : old('medidas') }}</p>
          </div>
          <div class="col-md-6">
            <label>Años:</label>
            <p>{{isset($datos['anios_vehiculo']) ? $datos['anios_vehiculo'] : old('anios_vehiculo') }}</p>
          </div>

        </div> <!--FIN DEL TERCER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-6">
            <label>Cód. Rpto:</label>
            <p>{{isset($datos['cod_repuesto_proveedor']) ? $datos['cod_repuesto_proveedor'] : old('cod_repuesto_proveedor') }}</p>
          </div>
          <div class="col-md-6">
            <label>Cód. Rpto2:</label>
            <p>{{isset($datos['version_vehiculo']) ? $datos['version_vehiculo'] : old('version_vehiculo') }}</p>
          </div>
        </div>

        <div class="row" style="margin-top:10px">
          <div class="col-md-6">
            <label>Precio Compra:</label>
            <p>{{isset($datos['precio_compra']) ? $datos['precio_compra'] : old('precio_compra') }}</p>
          </div>
          <div class="col-md-6">
            <label>Precio Sug.:</label>
            <p>{{isset($datos['precio_venta']) ? $datos['precio_venta'] : old('precio_venta') }}</p>
          </div>
        </div> <!--FIN DEL CUARTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">

          <div class="col-md-6">
            <label for="stock_minimo">Stock Mín.:</label>
            <p>{{isset($datos['stock_minimo']) ? $datos['stock_minimo'] : old('stock_minimo') }}</p>
          </div>
          <div class="col-md-6">
            <label>Stock Máx.:</label>
            <p>{{isset($datos['stock_maximo']) ? $datos['stock_maximo'] : old('stock_maximo') }}</p>
          </div>
        </div> <!--FIN DEL QUINTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-10">
            <label>Código Barras:</label>
            <p>{{isset($datos['codigo_barras']) ? $datos['codigo_barras'] : old('codigo_barras') }}</p>
          </div>
        </div> <!--FIN DEL SEXTO ROW COL 1 -->

        <div class="row" style="margin-top:20px">
         <p>Datos Guardados</p>
        </div> <!--FIN DEL SETIMO ROW COL 1 -->
    </div> <!--FIN DE COLUMNA 1: Datos de repuesto-->

<!-- FOTOS DEL REPUESTO -->
    <div class="col-3" style="background-color:#20FF9E;">
      <div class="row">
        <p class="text-center"><strong>FOTOS DEL REPUESTO</strong></p>
      </div>
      <div class="row" style="margin-top:10px;padding-left:1px;">
          <div class="row tabla-scroll-y-300" style="margin-top:10px">
            @if(count($fotos)>0)
            <div class="col-12">
              @for($i=0;$i<count($fotos);$i++)
                <img src="{{asset('storage/'.$fotos[$i]['urlfoto'])}}" width=100px />
              @endfor
            </div>
            @endif
          </div>
      </div>
    </div> <!--FIN DE COLUMNA 2: Fotos de repuesto -->


<!-- INGRESO DE SIMILARES -->
    <div class="col-3" style="background-color:#ACCAFF;">
      <div class="row">
        <p class="text-center"><strong>SIMILARES DEL REPUESTO</strong></p>
      </div>
      <div class="row" style="padding-left:1px;">
        <form name="similares" method="post" action="{{ url('repuesto/similares') }}">
        {{ csrf_field() }}

<input type="hidden" value="{{isset($datos['id_repuesto']) ? $datos['id_repuesto'] : old('id_repuesto') }}" name="id_repuesto" id="id_repuesto">
          <input type="hidden" value="{{isset($datos['familia']) ? $datos['familia'] : old('familia') }}" name="familia">
          <input type="hidden" value="{{isset($datos['marcavehiculo']) ? $datos['marcavehiculo'] : old('marcavehiculo') }}" name="marcavehiculo">
          <input type="hidden" value="{{isset($datos['modelovehiculo']) ? $datos['modelovehiculo'] : old('modelovehiculo') }}" name="modelovehiculo">
          <input type="hidden" value="{{isset($datos['marcarepuesto']) ? $datos['marcarepuesto'] : old('marcarepuesto') }}" name="marcarepuesto">
          <input type="hidden" value="{{isset($datos['proveedor']) ? $datos['proveedor'] : old('proveedor') }}" name="proveedor">
          <input type="hidden" value="{{isset($datos['pais']) ? $datos['pais'] : old('pais') }}" name="pais">
          <input type="hidden" value="{{isset($datos['descripcion']) ? $datos['descripcion'] : old('descripcion') }}" name="descripcion">
          <input type="hidden" value="{{isset($datos['medidas']) ? $datos['medidas'] : old('medidas') }}" name="medidas">
          <input type="hidden" value="{{isset($datos['anios_vehiculo']) ? $datos['anios_vehiculo'] : old('anios_vehiculo') }}" name="anios_vehiculo">
          <input type="hidden" value="{{isset($datos['cod_repuesto_proveedor']) ? $datos['cod_repuesto_proveedor'] : old('cod_repuesto_proveedor') }}" name="cod_repuesto_proveedor">
          <input type="hidden" value="{{isset($datos['precio_compra']) ? $datos['precio_compra'] : old('precio_compra') }}" name="precio_compra">
          <input type="hidden" value="{{isset($datos['precio_venta']) ? $datos['precio_venta'] : old('precio_venta') }}" name="precio_venta">
          <input type="hidden" value="{{isset($datos['stock_minimo']) ? $datos['stock_minimo'] : old('stock_minimo') }}" name="stock_minimo">
          <input type="hidden" value="{{isset($datos['stock_maximo']) ? $datos['stock_maximo'] : old('stock_maximo') }}" name="stock_maximo">
          <input type="hidden" value="{{isset($datos['codigo_barras']) ? $datos['codigo_barras'] : old('codigo_barras') }}" name="codigo_barras">

          <div class="col-6">
            <label for="Marca">Marca:</label>
            <select name="cboMarcaSim" class="form-control" id="MarcaSim" onchange="cargarModelos()">
              @foreach ($marcas as $marca)
                 <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
              @endforeach
            </select>
          </div>

          <?
            $xMar=$marcas[0]->idmarcavehiculo;
          ?>
          <div class="col-6">
            <label for="modelo">Modelo:</label>
              <select name="cboModeloSim" id="ModeloSim" class="form-control">

                @foreach($modelos as $modelo)
                  @if($modelo->marcavehiculos_idmarcavehiculo==$xMar)
                    <option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>
                  @endif
                @endforeach


              </select>
          </div>
        </div>




        <div class="row">
          <div class="col-8">
            <label for="anios_vehiculo">Años Vehículo:</label>
            <input type="text" name="anios_vehiculo_sim" value="{{old('anios_vehiculo_sim')}}" id="anios_vehiculo_sim" class="form-control">
          </div>
        </div>
        <div class="row" style="margin-top:5px">
          <div class="col-6">
            <input type="submit" name="btnGuardarSimilar" id="btnGuardarSimilar" value="Agregar Similar" class="btn btn-primary btn-sm"/>
          </div>

      </form>
          <div class="col-4">
            <input type="submit" name="btnAgregarOEMs" id="btnAgregarOEMs" value="Agregar OEMs" class="btn btn-success btn-sm" onclick="click_btn_oem()"/>
          </div>
        </div>

<!--Para mostrar los similares una tabla pequeña-->
<div class="row" style="margin-top:5px">
 <div class="col-12 tabla-scroll-y-300">
        <p><strong>Lista:</strong></p>
        @if($similares->count()>0)
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Marca</th>
              <th scope="col">Modelo</th>
              <th scope="col">Años</th>
            </tr>
          </thead>
          <tbody>
          @foreach($similares as $similar)
              <tr>
                <td>{{$similar->marcanombre}}</td>
                <td>{{$similar->modelonombre}}</td>
                <td>{{$similar->anios_vehiculo}}</td>
              </tr>
          @endforeach
        </tbody>
          </table>
        @endif
        </div>
      </div>
</div>

<!-- Columna 4: Códigos OEM extra -->
<div class="col-3" style="background-color:#ABDAFF;">
  <p id="msje_oem"></p>
  <div class="row">
    <div class="col-8">
      <label for="anios_vehiculo">Códigos OEM:</label>
      <input type="text" name="codigos_OEM" id="codigos_OEM" class="form-control" readonly>
    </div>
    <div class="col-4">
      <input type="submit" name="btnGuardarOEM" id="btnGuardarOEM" value="Agregar OEM" class="btn btn-primary btn-sm input-sm" onclick="agregar_OEM()" style="margin-top: 20px"/>
    </div>
  </div>
  <div class="row" id="tabla_OEM">
    <p>aqui tabla para mostrar los OEMs que se van agregando</p>
  </div>
</div> <!--FIN DE COLUMNA 4: Códigos OEM extra -->










  </div> <!--FIN DEL ROW PRINCIPAL-->
</div> <!--FIN DE CONTAINER-FLUID -->
  @endsection

  @section('contenido_ver_datos')

  @endsection
