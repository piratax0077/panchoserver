<div class="col-12 tabla-scroll-y-300">
	<p align="center"><strong>Reguladores: {{$reguladores->count()}}</strong></p>
	@if($reguladores->count()>0)
	<table class="table table-sm tabla-scroll-y-200 letra-chica" style="overflow-x: hidden; border: 0px;">
	  	<thead>
	    	<tr>
	      		<th scope="col" width="70%">Rectificador</th>
                <th scope="col" width="30%">Alternador</th>                  
	    	</tr>
	  	</thead>
	  	<tbody>
	  	@foreach($reguladores as $rv)
	      <tr>
	        <td>{{$rv->rectificador}}</td>
            <td>{{$rv->alternador}}</td>
	      </tr>
	  	@endforeach
		</tbody>
	</table>
	@else
		<p>No hay RVs agregados...</p>
	@endif
</div>