{{-- @if(Auth::user()->id == 5 )  --}}
<div class="col-12">
	<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px;">
		<i class="fa fa-barcode"></i> OEM's ({{$oems->count()}})
	</div>
	@if($oems->count()>0)
	<div style="background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #cfd8dc;">
		<div class="tabla-scroll-y-200">
			<table class="table table-sm letra-chica mb-0" style="width:100%; table-layout: fixed; border: 0px;">
				<thead>
					<tr style="background: #cfd8dc;">
						<th scope="col" style="color: #37474f;">OEM</th>
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
		</div>
	</div>
	@else
		<p style="color: #78909c; font-style: italic; margin: 0;">Sin OEM's registrados.</p>
	@endif
</div>
{{-- @else
	<p align="center"><strong>OEM's: 0</strong></p>
@endif --}}
