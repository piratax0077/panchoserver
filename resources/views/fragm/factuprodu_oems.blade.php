<div class="col-12 tabla-scroll-y-300">
	<p><strong>Lista: {{$oems->count()}}</strong></p>
	@if($oems->count()>0)
	<table class="table table-sm">
	  	<tbody>
	  	@foreach($oems as $oem)
	      <tr>
			<td>{{$oem->codigo_oem}}</td>
			<td>
				<abbr title="oems {{$oem->id}}">
					@if(isset($tipo) && $tipo == 1)
					<button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borraroem_origen({{$oem->id}})">X</button>
					@elseif(isset($tipo) && $tipo == 2)
					<button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borraroem_destino({{$oem->id}})">X</button>
					@else
					<button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borraroem({{$oem->id}})">X</button> 

					@endif
				</abbr>
			</td>
	      </tr>
	  	@endforeach
		</tbody>
	</table>
	@else
		<p>No hay OEMs agregados...</p>
	@endif
</div>
