<div class="tabla-scroll-y-400">
@if(empty($fotos))
	<div class="col-12">
		<p>Fotos vacia.</p>
	</div>
@else
		@if($fotos->count()>0)
		<div class="col-12">
<table class="table table-sm">
	  	<thead>
	    	<tr>
				<th scope="col">Fotos ({{$fotos->count()}})</th>
				<th scope="col"></th> <!--Eliminar-->
			</tr>
	  	</thead>
	  	<tbody>
		@foreach($fotos as $foto)
		<tr>
			<td style="padding: 2px;margin:2px">
		  		<img src="{{asset('storage/'.$foto->urlfoto)}}" width=200px/>
			</td>
			<td style="padding: 2px;margin:2px">
				<abbr title="repuestos_fotos {{$foto->id}}">
					<button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borrarfoto({{$foto->id}})">X</button>
				</abbr>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
		</div>
	@else
	<div class="col-12">
		<p>No hay fotos agregadas.</p>
	</div>

	@endif
@endif
