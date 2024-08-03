<!-- Carga en el div con id mostrar_repuestos en compras_ingreso.blade.php -->
@if($items->count()>0)
<div class="row">
	<div class="col-md-12">
	  <table id="tbl_items">
	    <thead>
	      <th width="4%" scope="col">Cod Int</th>
		  <th width="20%" scope="col">Descripción</th>
		  <th width="10%" scope="col">Local</th>
	      <th width="10%" scope="col">Marca</th>
	      <th width="10%" scope="col">Modelo</th>
	      <th width="8%" scope="col">Años</th>
	      <th width="7%" scope="col">Cant</th>
	      <th width="7%" scope="col">P.U.</th>
	      <th width="10%" scope="col">SubTotal</th>
	      <th width="5%" scope="col">Flete</th>
	      <th width="11%" scope="col">Precio Sug.</th>
	      <th width="4%"></th> <!-- Borrar -->

	    </thead>
	    <tbody>
	    @foreach ($items as $item)
	    <tr>
	      <td>{{$item->codigo_interno}}</td>
		  <td>{{$item->descripcion}}</td>
		  <td>{{$item->local_nombre}}</td>
	      <td>{{$item->marcanombre}}</td>
	      <td>{{$item->modelonombre}}</td>
	      <td>{{$item->anios_vehiculo}}</td>
	      <td>{{$item->cantidad}}</td>
	      <td>{{$item->pu}}</td>
	      <td>{{$item->subtotal}}</td>
	      <td>{{$item->costos}}</td>
	      <td>{{$item->precio_sugerido}}</td>

			<td>
	<button class="btn btn-danger btn-sm" onclick="eliminaritem({{$item->id}})">X</button>
	      </td>

	    </tr>
		@endforeach
		</tbody>
	  </table>
	</div>
</div>
<!-- TOTALES FACTURA DE COMPRA -->
<div class="row">
	<div class="col-md-4">
		SubTotal: {{$st}}
	</div>
	<div class="col-md-4">
		IVA: {{$iva}}
	</div>
	<div class="col-md-4">
		Total: {{$total}}
	</div>
</div>
@else
<div class="row">
	<div class="alert alert-info">
		<h3><center>Sin Items</center></h3>
	</div>
</div>
@endif
