<!-- PARA USARLO DESPUES COMO FRAGMENTO --> 
<div class="container-fluid">
  <div class="row"> <!-- ROW GENERAL -->
    <div class="col-md-5" style="background-color:#FFFF7F;">
<!-- TITULO ROW COL 1 -->      
      <div class="row">
        <p class="text-center"><strong>DATOS DEL REPUESTO</strong></p>
      </div>
        
<!-- PRIMER ROW COL 1 -->
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

<!-- SEGUNDO ROW COL 1 -->
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
            <label>Pais Origen:</label>
              <p>{{$datos['pais']}}</p>
          </div>
        </div> <!--FIN DEL SEGUNDO ROW COL 1 -->

<!-- FILA PARA LA INGRESAR LA DESCRIPCIÓN -->
        <div class="row">
          <div class="col-md-8">
            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" value="{{old('descripcion')}}" id="descripcion" class="form-control">
          </div>
        </div>

<!-- TERCER ROW COL 1 -->
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

<!-- CUARTO ROW COL 1 -->
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

<!-- QUINTO ROW COL 1 -->
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

<!-- SEXTO ROW COL 1 -->
        <div class="row" style="margin-top:10px">
          <div class="col-md-4">
            <label for="stock_actual">Stock Actual:</label>
            <input type="text" name="stock_actual" value="{{old('stock_actual')}}" id="stock_actual" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="codigo_barras">Código Barras:</label>
            <input type="text" name="codigo_barras" value="{{old('codigo_barras')}}" id="codigo_barras" class="form-control">
          </div>
        </div> <!--FIN DEL SEXTO ROW COL 1 -->

<!-- SETIMO ROW COL 1 -->
        <div class="row" style="margin-top:20px">
          <div class="col-md-3">
            <input type="submit" name="btnGuardarRepuesto" id="button" value="Guardar Datos" class="btn btn-primary btn-md"/>
          </div>
        </div> <!--FIN DEL SETIMO ROW COL 1 -->


    </div> <!--FIN DE COLUMNA 1: Datos de repuesto-->
  </div> <!-- FIN ROW GENERAL -->
</div> <!-- FIN CONTAINER FLUID -->
@else
<div class="container-fluid">
  <div class="row"> <!-- ROW GENERAL -->
    <div class="col-md-8" style="background-color:#FFFF7F;">
      <p>NO HAY DATOS...
    </div>
  </div>
</div>
@endif
