<div class="col-12 tabla-scroll-y-300">
	<p align="center"><strong>Fabricantes: {{$fabs->count()}}</strong></p>
	@if($fabs->count()>0)
	<table class="table table-sm tabla-scroll-y-200 letra-chica" style="overflow-x: hidden; border: 0px;">
	  	<thead>
	    	<tr>
                  <th scope="col">Código</th><th scope="col">Fabric.</th>
	    	</tr>
	  	</thead>
	  	<tbody>
	  	@foreach($fabs as $fab)
	      <tr>
            <td>{{$fab->codigo_fab}}</td><td>{{$fab->marcarepuesto}}</td>
	      </tr>
	  	@endforeach
		</tbody>
	</table>
	@else
		<p>No hay Fabricantes agregados...</p>
	@endif
</div>
