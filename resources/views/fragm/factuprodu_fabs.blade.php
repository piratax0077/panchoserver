<div class="col-12 tabla-scroll-y-200" style="padding-left:1px;padding-right:1px;overflow-x:hidden">
	<p><strong>Lista: {{$fabs->count()}}</strong></p>
	@if($fabs->count()>0)
	<table class="table table-sm">
	  	<thead>
               <th scope="col">Código</th>
               <th scope="col">Fab.</th>
			   <th></th>
	  	</thead>
	  	<tbody>
	  	@foreach($fabs as $fab)
	      <tr>
            <td>{{$fab->codigo_fab}}</td>
            <td>{{$fab->marcarepuesto}}</td>
			<td>
				<abbr title="repuestos_fabricantes {{$fab->id}}">
					<button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borrarfab({{$fab->id}})">X</button>
				</abbr>
			</td>
	      </tr>
	  	@endforeach
		</tbody>
	</table>
	@else
		<p>No hay Códigos agregados...</p>
	@endif
</div>
