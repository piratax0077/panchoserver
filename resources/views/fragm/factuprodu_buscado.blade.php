@if($repuestos->count()>0)
<div class="row">
	<div class="col-12 tabla-scroll-y-500">
	  <table id="tbl_repuestos" class="table table-sm table-hover">
	    <thead>
	      <th width="5%" scope="col">Cod Int</th>
		  <th width="5%" scope="col">Cod Proveedor</th>
		  <th width="14%" scope="col">Proveedor</th>
	      <th width="14%" scope="col">Descripción</th>
		  <th width="4%" scope="col">Stock B.</th>
		  <th width="4%" scope="col">Stock T.</th>
		  <th width="4%" scope="col">Stock CM.</th>
          <th width="10%" scope="col">Origen</th>
		  <th width="10%" scope="col">Precio Compra</th>
		  <th width="10%" scope="col">Precio Venta</th>
		  <th width="10%" scope="col">Actualización stock</th> <!-- ELEGIR-->
		  <th width="10%" scope="col">Usuario</th> <!-- ELEGIR-->
	    </thead>
	    <tbody>
	    @foreach ($repuestos as $repuesto)
	    <tr style="font-stretch: condensed">
	      <td><a href="/repuesto/modificar/{{$repuesto->id}}" target="_blank">{{$repuesto->codigo_interno}}</a> </td>
		  <td>{{$repuesto->cod_repuesto_proveedor}}</td>
		  <td>{{$repuesto->empresa_nombre}}</td>
		  <td>{{$repuesto->descripcion}}</td>
		  <td>{{$repuesto->stock_actual}}</td>
		  <td>{{$repuesto->stock_actual_dos}}</td>
		  <td>{{$repuesto->stock_actual_tres}}</td>
	      <td>{{$repuesto->nombre_pais}}</td>
		  <td>$ {{number_format($repuesto->precio_compra)}}</td> <!-- Debería ser precio unitario (PU) en compras_det -->
		  <td>$ {{number_format($repuesto->precio_venta)}}</td>
		  
		  <td>{{Carbon\Carbon::createFromFormat('Y-m-d', $repuesto->fecha_ultima)->format('d-m-Y');}}</td>
		  <td>{{$repuesto->name}}</td>
			<td> 
			<button class="btn btn-warning btn-sm" onclick="elegir_repuesto({{$repuesto}})">Elegir</button>
	      </td>

	    </tr>
	    @endforeach
		</tbody>
	  </table>
	</div>
</div>
@else

<div class="alert alert-info">
	<h4><center>Sin resultados.</center></h4>
</div>

@endif
