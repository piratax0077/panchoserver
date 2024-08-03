@extends('plantillas.app')
  @section('titulo','Crear Repuestos')

  @section('javascript')
  @endsection

  @section('contenido_titulo_pagina')
  <div class="row" style="background-color:#FAC47F;">
    <center><h3>Crear Repuestos</h3></center>
  </div>
 @endsection
  @section('contenido_ingresa_datos')
    @include('fragm.mensajes')
    @if(!empty($fotos))
      @if($fotos->count()>0)
      <div class="row" style="background-color:#FAC47F;">
      <div class="col-md-4">
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
    <div class="col-md-3" style="background-color:#20FF9E;">
      <div class="row">
        <p class="text-center"><strong>FOTOS DEL REPUESTO</strong></p>
      </div>
      <div class="row" style="margin-top:10px;padding-left:5px;">
        <form name="fotos" method="post" action="{{ url('repuesto/fotos') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <input type="hidden" value="{{isset($datos['id_repuesto']) ? $datos['id_repuesto'] : old('id_repuesto') }}" name="id_repuesto">
          <input type="hidden" value="{{isset($datos['familia']) ? $datos['familia'] : old('familia') }}" name="familia">
          <input type="hidden" value="{{isset($datos['marcavehiculo']) ? $datos['marcavehiculo'] : old('marcavehiculo') }}" name="marcavehiculo">
          <input type="hidden" value="{{isset($datos['modelovehiculo']) ? $datos['modelovehiculo'] : old('modelovehiculo') }}" name="modelovehiculo">
          <input type="hidden" value="{{isset($datos['marcarepuesto']) ? $datos['marcarepuesto'] : old('marcarepuesto') }}" name="marcarepuesto">
          <input type="hidden" value="{{isset($datos['proveedor']) ? $datos['proveedor'] : old('proveedor') }}" name="proveedor">
          <input type="hidden" value="{{isset($datos['pais']) ? $datos['pais'] : old('pais') }}" name="pais">

          <input type="hidden" value="{{isset($datos['descripcion']) ? $datos['descripcion'] : old('descripcion') }}" name="descripcion">
          <input type="hidden" value="{{isset($datos['medidas']) ? $datos['medidas'] : old('medidas') }}" name="medidas">
          <input type="hidden" value="{{isset($datos['anios_vehiculo']) ? $datos['anios_vehiculo'] : old('anios_vehiculo') }}" name="anios_vehiculo">
          <input type="hidden" value="{{isset($datos['version_vehiculo']) ? $datos['version_vehiculo'] : old('version_vehiculo') }}" name="version_vehiculo">
          <input type="hidden" value="{{isset($datos['cod_repuesto_proveedor']) ? $datos['cod_repuesto_proveedor'] : old('cod_repuesto_proveedor') }}" name="cod_repuesto_proveedor">
          <input type="hidden" value="{{isset($datos['codigo_OEM_repuesto']) ? $datos['codigo_OEM_repuesto'] : old('codigo_OEM_repuesto') }}" name="codigo_OEM_repuesto">
          <input type="hidden" value="{{isset($datos['precio_compra']) ? $datos['precio_compra'] : old('precio_compra') }}" name="precio_compra">
          <input type="hidden" value="{{isset($datos['precio_venta']) ? $datos['precio_venta'] : old('precio_venta') }}" name="precio_venta">
          <input type="hidden" value="{{isset($datos['stock_minimo']) ? $datos['stock_minimo'] : old('stock_minimo') }}" name="stock_minimo">
          <input type="hidden" value="{{isset($datos['stock_maximo']) ? $datos['stock_maximo'] : old('stock_maximo') }}" name="stock_maximo">
          <input type="hidden" value="{{isset($datos['codigo_barras']) ? $datos['codigo_barras'] : old('codigo_barras') }}" name="codigo_barras">

          <div class="row">
            <div class="col-md-12">
              <label>Subir Foto (jpg,jpeg,png):</label>
              <input type="file" name="archivo" id="archivo" class="form-control-file">
            </div>
          </div>

          <div class="row" style="margin-top:10px">
            <div class="col-md-4">
              <input type="submit" name="btnGuardarFotos" id="button" value="Agregar Foto" class="btn btn-primary btn-md"/>
            </div>
            <div class="col-md-4">
              <input type="submit" name="btnAgregarSimilares" id="button" value="Agregar Similares" class="btn btn-success btn-md"/>
            </div>
          </div>

          <div class="row" style="margin-top:10px">
            @if(!empty($fotos))
              @if($fotos->count()>0)
              <div class="col-md-12">
                @foreach($fotos as $foto)
                  <img src="{{asset('storage/'.$foto->urlfoto)}}" width=100px/>
                @endforeach
              </div>
              @else
                <p>No hay fotos agregadas</p>
              @endif
            @endif



          </div>
        </form>
      </div>
    </div> <!--FIN DE COLUMNA 2: Fotos de repuesto -->


<!-- Columna 3: INGRESO DE SIMILARES -->
    <div class="col-md-3" style="background-color:#ACCAFF;">
      <div class="row">
        <p class="text-center"><strong>SIMILARES DEL REPUESTO</strong></p>
      </div>
      <div class="row" style="margin-top:5px">
          <div class="col-md-6">
            <label for="Marca">Marca:</label>
            <select name="cboMarcaSim" class="form-control" id="MarcaSim">
                 <option value="0">Seleccionar</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="modelo">Modelo:</label>
              <select name="cboModeloSim" id="ModeloSim" class="form-control">
                    <option value="0">Seleccionar</option>
              </select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-8">
            <label for="anios_vehiculo">Años Vehículo:</label>
            <input type="text" name="anios_vehiculo_sim" id="anios_vehiculo_sim" class="form-control" readonly>
          </div>
        </div>
      <div class="row" style="margin-top:10px"> <!--Para mostrar los similares una tabla pequeña-->
        <div class="col-md-12">
        </div>
      </div>
    </div> <!--FIN DE COLUMNA 3: Similares repuesto -->

<!-- Columna 4: Códigos OEM extra -->
<div class="col-md-3" style="background-color:#ABDAFF;">
<div class="row">
          <div class="col-md-8">
            <label for="anios_vehiculo">Códigos OEM:</label>
            <input type="text" name="codigos_OEM" id="codigos_OEM" class="form-control" readonly>
          </div>
        </div>
</div> <!--FIN DE COLUMNA 4: Códigos OEM extra -->


  </div> <!--FIN DEL ROW PRINCIPAL-->
</div> <!--FIN DE CONTAINER-FLUID -->
  @endsection

  @section('contenido_ver_datos')

  @endsection
