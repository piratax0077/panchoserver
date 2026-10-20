<div class="col-12">
	<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px;">
		<i class="fa fa-industry"></i> Fabricantes ({{$fabs->count()}})
	</div>
	@if($fabs->count()>0)
	<div style="background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #cfd8dc;">
		<div class="tabla-scroll-y-200">
			<table class="table table-sm letra-chica mb-0" style="width:100%; table-layout: fixed; border: 0px;">
				<thead>
					<tr style="background: #cfd8dc;">
						<th scope="col" style="width:50%; color: #37474f;">Código</th><th scope="col" style="width:50%; color: #37474f;">Fabric.</th>
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
		</div>
	</div>
	@else
		<p style="color: #78909c; font-style: italic; margin: 0;">Sin fabricantes registrados.</p>
	@endif
</div>
