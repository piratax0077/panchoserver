<p>TITULO: {{$nombre}}</p>
<table class="table table-hover">
	<thead class="thead-dark">
		<th>Cod. Int.</th>
		<th>Descripción</th>
	</thead>
		@foreach($repuestos as $repuesto)
		<tr>
			<td>{{$repuesto->codigo_interno}}({{$repuesto->id_marca_vehiculo}})</td>
			<td>{{$repuesto->descripcion}}</td>
		</tr>
		@endforeach
</table>