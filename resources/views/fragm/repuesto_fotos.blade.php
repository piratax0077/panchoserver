
@if(empty($fotos))
<div class="col-sm-12">
	<p style="color: #78909c; font-style: italic; margin: 0;">Sin fotos</p>
</div>
@else
	@if($fotos->count()>0)

		<div class="col-sm-12" style="padding: 0 0 15px 0;">
		<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px;">
			<i class="fa fa-images"></i> Fotos ({{$fotos->count()}})
		</div>
		<div style="display: flex; flex-wrap: wrap; gap: 10px;">
			@foreach($fotos as $foto)
			<a href="javascript:void(0);" onclick="abrir_foto_modal('{{$id_repuesto}}')" style="display:inline-block;">
			  <img src="{{asset('storage/'.$foto->urlfoto)}}" width="100" height="100" style="object-fit: cover; border-radius: 8px; border: 1px solid #cfd8dc; box-shadow: 0 1px 4px rgba(0,0,0,0.1);"/>
			</a>
			@endforeach
		</div>
		</div>

	@else
	<div class="col-sm-12">
		<p style="color: #78909c; font-style: italic; margin: 0;">No hay fotos agregadas.</p>
	</div>

	@endif
@endif