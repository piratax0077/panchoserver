<!-- Carga en el div con id mostrar_repuestos en compras_ingreso.blade.php -->
@if($repuestos->count()>0)
<div class="row">
	<div class="col-md-12">
	  <table id="tbl_repuestos" class="display">
	    <thead>
	      <th width="4%" scope="col">Cod Int</th>
		  <th width="10%" scope="col">Cod Rep Prov1</th>
		  <th width="10%" scope="col">Cod Rep Prov2</th>
	      <th width="23%" scope="col">Descripción</th>
	      <th width="23%" scope="col">Marca Repuesto</th>
	      <th width="10%" scope="col">Medidas</th>
	      <th width="10%" scope="col">Años</th>
	      <th width="10%"></th> <!-- ELEGIR-->

	    </thead>
	    <tbody>
	    @foreach ($repuestos as $repuesto)
	    <tr>
	      <td>{{$repuesto->codigo_interno}}</td>
		  <td>{{$repuesto->cod_repuesto_proveedor}}</td>
		  <td>{{$repuesto->version_vehiculo}}</td>
	      <td>{{$repuesto->descripcion}}</td>
	      <td>{{$repuesto->marcarepuesto->marcarepuesto}}</td>
	      <td>{{$repuesto->medidas}}</td>
	      <td>{{$repuesto->anios_vehiculo}}</td>

			<td>
<button class="btn btn-warning btn-sm" onclick="elegir({{$repuesto->id}},'{{$repuesto->codigo_interno}}','{{$repuesto->descripcion}}','{{$repuesto->id_familia}}')">Elegir</button>
	      </td>

	    </tr>
	    @endforeach
		</tbody>
	  </table>
	</div>
</div>
@else
<div class="row">
<div class="alert alert-info">
	<h4><center>Sin resultados.</center></h4>
</div>
</div>
@endif
