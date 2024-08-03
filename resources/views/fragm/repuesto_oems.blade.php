<div class="col-12 tabla-scroll-y-300">
	<p align="center"><strong>OEM's: {{$oems->count()}}</strong></p>
	@if($oems->count()>0)
	<table class="table table-sm tabla-scroll-y-200 letra-chica" style="overflow-x: hidden; border: 0px;">
	  	<thead>
	    	<tr>
	      		<th scope="col">OEM</th>
	    	</tr>
	  	</thead>
	  	<tbody>
	  	@foreach($oems as $oem)
	      <tr>
	        <td>{{$oem->codigo_oem}}</td>
	      </tr>
	  	@endforeach
		</tbody>
	</table>
	@else
		<p>No hay OEMs agregados...</p>
	@endif
</div>
