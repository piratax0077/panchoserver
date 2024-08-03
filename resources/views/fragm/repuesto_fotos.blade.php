
@if(empty($fotos))
<div class="col-sm-10">
	<p>Sin Fotos</p>
</div>
@else
	@if($fotos->count()>0)

		<div class="col-sm-12" style="padding:2px">
		<p align="center"><strong>FOTOS: {{$fotos->count()}}</strong></p>
		<table class="table table-sm tabla-scroll-y-200" style="overflow-x: hidden; border: 0px;">
			<tbody>
			@foreach($fotos as $foto)
			<tr><td>
			<!--El modal donde  se abrirá esta en repuestos.blade.php -->
			<!-- {{asset('storage/'.$foto->urlfoto)}} -->
			<a href="javascript:void(0);" onclick="abrir_foto_modal('{{$id_repuesto}}')">
			  <img src="{{asset('storage/'.$foto->urlfoto)}}" width=180px/>
			</a>
			</td></tr>
			@endforeach
		</tbody>
		</table>
		</div>

	@else
	<div class="col-sm-12">
		<p>No hay fotos agregadas.</p>
	</div>

	@endif
@endif