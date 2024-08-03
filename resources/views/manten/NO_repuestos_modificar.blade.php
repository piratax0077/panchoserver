@extends('plantillas.app')
  @section('titulo','Modificar Repuesto')
  @section('javascript')
  <script type="text/javascript">
    function cargarModelos()
    {
      $('#modelo option').remove();
      var xMarca=document.getElementById('Marca').value;

      @foreach($modelos as $modelo)
        var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
        if(idMarca==xMarca)
        {
          $('#modelo').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
        }
      @endforeach
    }
  </script>
  @endsection
  @section('contenido_titulo_pagina')
<div class="row" style="background-color:#FAC47F;">
  <center><h3>Crear Repuestos</h3></center>
</div>
@endsection

  @section('contenido_ingresa_datos')
    @if(empty($guardado))
      @php $guardado='NO';@endphp
    @endif
    @include('fragm.mensajes')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-5" style="background-color:#FFFF7F;">
      <div class="row">
        <p class="text-center"><strong>DATOS DEL REPUESTO</strong></p>
      </div>
@if($guardado=='SI')

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Familia:</label>
              <p>{{$datos['familia']}}</p>
          </div>
          <div class="col-md-4">
            <label>Marca:</label>
            <p>{{$datos['marcavehiculo']}}</p>
          </div>
          <div class="col-md-4">
            <label>Modelo:</label>
              <p>{{$datos['modelovehiculo']}}</p>
          </div>
        </div> <!--FIN DEL PRIMER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Marca del Repuesto:</label>
            <p>{{$datos['marcarepuesto']}}</p>
          </div>
          <div class="col-md-4">
            <label>Proveedor:</label>
            <p>{{$datos['proveedor']}}</p>
          </div>
          <div class="col-md-4">
            <label>Descripción:</label>
              <p>{{$datos['descripcion']}}</p>
          </div>
        </div> <!--FIN DEL SEGUNDO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Medidas:</label>
              <p>{{$datos['medidas']}}</p>
          </div>
          <div class="col-md-4">
            <label>Años:</label>
              <p>{{$datos['anios_vehiculo']}}</p>
          </div>
          <div class="col-md-4">
            <label>Versión (Trim):</label>
              <p>{{$datos['version_vehiculo']}}</p>
          </div>
        </div> <!--FIN DEL TERCER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Cód. Repuesto:</label>
            <p>{{$datos['cod_repuesto_proveedor']}}</p>
          </div>
          <div class="col-md-4">
            <label>Código OEM:</label>
            <p>{{$datos['codigo_OEM_repuesto']}}</p>
          </div>
          <div class="col-md-4">
            <label for="precio_compra">Precio de Compra:</label>
            <p>{{$datos['precio_compra']}}</p>
          </div>
        </div> <!--FIN DEL CUARTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label>Precio de Venta:</label>
            <p>{{$datos['precio_venta']}}</p>
          </div>
          <div class="col-md-4">
            <label for="stock_minimo">Stock Mínimo:</label>
            <p>{{$datos['stock_minimo']}}</p>
          </div>
          <div class="col-md-4">
            <label>Stock Máximo:</label>
            <p>{{$datos['stock_maximo']}}</p>
          </div>
        </div> <!--FIN DEL QUINTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="codigo_barras">Código Barras:</label>
            <p>{{$datos['codigo_barras']}}</p>
          </div>
        </div> <!--FIN DEL SEXTO ROW COL 1 -->

        <div class="row" style="margin-top:20px">
         <p>Datos Guardados</p>
        </div> <!--FIN DEL SETIMO ROW COL 1 -->

@else

      <form name="repuestos" method="post" action="{{ url('repuesto/datos') }}">
        {{ csrf_field() }}
        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="familia">Familia:</label>
              <select name="cboFamilia" class="form-control" id="familia">
                @foreach ($familias as $familia)
                   <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                @endforeach
              </select>
          </div>
          <div class="col-md-4">
            <label for="Marca">Marca:</label>
            <select name="cboMarca" class="form-control" id="Marca" onchange="cargarModelos()">
              @foreach ($marcas as $marca)
                 <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
              @endforeach
            </select>
          </div>
          <!-- El primer elemento de la colección -->
          <?
            $xMar=$marcas[0]->idmarcavehiculo;
          ?>
  		    <div class="col-md-4">
            <label for="modelo">Modelo:</label>
              <select name="cboModelo" id="modelo" class="form-control">

                @foreach($modelos as $modelo)
                  @if($modelo->marcavehiculos_idmarcavehiculo==$xMar)
                    <option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>
                  @endif
                @endforeach


              </select>
          </div>
        </div> <!--FIN DEL PRIMER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
  		    <div class="col-md-4">
            <label for="MarcaRepuesto">Marca del Repuesto:</label>
            <select name="cboMarcaRepuesto" class="form-control" id="MarcaRepuesto">
              @foreach ($marcarepuestos as $marcarepuesto)
                 <option value="{{$marcarepuesto->id}}">{{$marcarepuesto->marcarepuesto}}</option>
              @endforeach
            </select>
          </div>
  		    <div class="col-md-4">
            <label for="Proveedor">Proveedor:</label>
            <select name="cboProveedor" class="form-control" id="Proveedor">
              @foreach ($proveedores as $proveedor)
                 <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="descripcion">Descripción:</label>
              <input type="text" name="descripcion" value="{{old('descripcion')}}" id="descripcion" class="form-control">
          </div>
        </div> <!--FIN DEL SEGUNDO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="medidas">Medidas:</label>
              <input type="text" name="medidas" value="{{old('medidas')}}" id="medidas" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="anios_vehiculo">Años:</label>
              <input type="text" name="anios_vehiculo" value="{{old('anios_vehiculo')}}" id="anios_vehiculo" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="version_vehiculo">Versión (Trim):</label>
              <input type="text" name="version_vehiculo" value="{{old('version_vehiculo')}}" id="version_vehiculo" class="form-control">
          </div>
        </div> <!--FIN DEL TERCER ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="cod_repuesto_proveedor">Cód. Repuesto:</label>
            <input type="text" name="cod_repuesto_proveedor" value="{{old('cod_repuesto_proveedor')}}" id="cod_repuesto_proveedor" placeholder="Del proveedor..." class="form-control">
          </div>
          <div class="col-md-4">
            <label for="codigo_OEM_repuesto">Código OEM:</label>
            <input type="text" name="codigo_OEM_repuesto" value="{{old('codigo_OEM_repuesto')}}" id="codigo_OEM_repuesto" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="precio_compra">Precio de Compra:</label>
            <input type="text" name="precio_compra" value="{{old('precio_compra')}}" id="precio_compra" class="form-control">
          </div>
        </div> <!--FIN DEL CUARTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="precio_venta">Precio de Venta:</label>
            <input type="text" name="precio_venta" value="{{old('precio_venta')}}" id="precio_venta" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="stock_minimo">Stock Mínimo:</label>
            <input type="text" name="stock_minimo" value="{{old('stock_minimo')}}" id="stock_minimo" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="stock_maximo">Stock Máximo:</label>
            <input type="text" name="stock_maximo" value="{{old('stock_maximo')}}" id="stock_maximo" class="form-control">
          </div>
        </div> <!--FIN DEL QUINTO ROW COL 1 -->

        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="codigo_barras">Código Barras:</label>
            <input type="text" name="codigo_barras" value="{{old('codigo_barras')}}" id="codigo_barras" class="form-control">
          </div>
        </div> <!--FIN DEL SEXTO ROW COL 1 -->

        <div class="row" style="margin-top:20px">
          <div class="col-md-3">
            <input type="submit" name="btnGuardarRepuesto" id="button" value="Guardar Datos" class="btn btn-primary btn-md"/>
          </div>
        </div> <!--FIN DEL SETIMO ROW COL 1 -->

      </form>
@endif

    </div> <!--FIN DE COLUMNA 1: Datos de repuesto-->

<!-- FOTOS DEL REPUESTO -->
    <div class="col-md-4" style="background-color:#20FF9E;">
      <div class="row">
        <p class="text-center"><strong>FOTOS DEL REPUESTO {{$datos['id_repuesto']}}</strong></p>
      </div>
      <div class="row" style="margin-top:10px;padding-left:5px;">
        <form name="fotos" method="post" action="{{ url('repuesto/fotos') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <input type="hidden" name="id_repuesto" value="{{$datos['id_repuesto']}}">
          <div class="row">
            <div class="col-md-12">
              @if($guardado=="SI")
                <label>Subir Foto (jpg,jpeg,png):</label>
                <input type="file" name="archivo" id="archivo" class="form-control-file">
              @else
                <p><center>Ingrese datos primero</center></p>
              @endif
            </div>
          </div>

          <div class="row" style="margin-top:10px">
            @if($guardado=="SI")
            <div class="col-md-4">
              <input type="submit" name="btnGuardarFotos" id="button" value="Agregar Foto" class="btn btn-primary btn-md"/>
            </div>
            @else
            <div class="col-md-4">

            </div>

            @endif
          </div>

          <div class="row" style="margin-top:10px">

            @if(!empty($fotos))
              @if($fotos->count()>0)
                @foreach($fotos as $foto)
                <div class="col-md-12">
                  <img src="{{asset('storage/'.$foto->urlfoto)}}" width=100px/>
                </div>
                @endforeach
              @else
                <p>No hay fotos agregadas</p>
              @endif
            @endif



          </div>
        </form>
      </div>
    </div> <!--FIN DE COLUMNA 2: Fotos de repuesto -->


<!-- INGRESO DE SIMILARES -->
    <div class="col-md-3" style="background-color:#ACCAFF;">
      <div class="row">
        <p class="text-center"><strong>SIMILARES DEL REPUESTO</strong></p>
      </div>
      <div class="row" style="padding-left:5px;">
        <form name="similares" method="post" action="{{ url('repuesto/similares') }}">
        {{ csrf_field() }}

        <div class="row">
          <div class="col-md-8">
            <label for="codigo_OEM_repuesto">Código OEM:</label>
            <input type="text" name="codigo_OEM_repuesto" value="{{old('codigo_OEM_repuesto')}}" id="codigo_OEM_repuesto" class="form-control">
          </div>
        </div>
        <div class="row">
          <div class="col-md-8">
            <label for="anios_vehiculo">Años Vehículo:</label>
            <input type="text" name="anios_vehiculo" value="{{old('anios_vehiculo')}}" id="anios_vehiculo" class="form-control">
          </div>
        </div>
        <div class="row" style="margin-top:5px">
          <div class="col-md-8">
            @if($guardado=="SI")
            <input type="submit" name="btnGuardarSimilar" id="button" value="Agregar Similar" class="btn btn-primary btn-md"/>
            @else
            <p>Ingrese datos</p>
            @endif

          </div>
        </div>
      </form>
      </div>
      <div class="row" style="margin-top:10px"> <!--Para mostrar los similares una tabla pequeña-->
        <div class="col-md-12">
        <p>Mostrar los similares en una tabla
        </div>
      </div>
    </div> <!--FIN DE COLUMNA 3: Similares repuesto -->

  </div> <!--FIN DEL ROW PRINCIPAL-->
</div> <!--FIN DE CONTAINER-FLUID -->
  @endsection

  @section('contenido_ver_datos')

  @endsection
