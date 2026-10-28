<div class="tabla-scroll-y-400">
@if(empty($fotos))
	<div class="col-12">
		<img src="{{route('storage.public', [], false)}}" width="200" height="150" alt="Sin imagen disponible" style="object-fit:contain; border:1px solid #cfd8dc; border-radius:6px;"/>
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
				<a href="{{ route('factuprodu.foto', ['idfoto' => $foto->id], false) }}" target="_blank" title="Ver imagen completa">
					<img
						src="{{ route('factuprodu.foto', ['idfoto' => $foto->id], false) }}"
						alt="Fotografía del repuesto"
						loading="lazy"
						style="display:block; width:200px; max-width:100%; height:150px; object-fit:contain; background:#fff; border:1px solid #cfd8dc; border-radius:6px;"
					/>
				</a>
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
		<img src="{{route('storage.public', [], false)}}" width="200" height="150" alt="Sin imagen disponible" style="object-fit:contain; border:1px solid #cfd8dc; border-radius:6px;"/>
	</div>

	@endif
@endif
