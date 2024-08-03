@if($repuestos->count()>0)
<br>
<div class="row">
    <center><strong>REPUESTOS RELACIONADOS</strong></center>
</div>
<div class="row">
	<div class="col-sm-12 tabla-scroll-y-300">
	  <table id="tbl_repuestos" class="table table-hover table-sm letra-chica">
	    <thead>

		 <th width="4%" scope="col">Cod Int</th>
	      <th width="15%" scope="col">Cod Rep Prov</th>
	      <th width="24%" scope="col">Descripción</th>
	      <th width="15%" scope="col">Medidas</th>
		  <th width="7%" scope="col">Años</th>
		  <th width="2%" scope="col"></th> <!-- Eliminar -->
	    </thead>
	    <tbody>
	    @foreach ($repuestos as $repuesto)
		<tr>
		<td>{{$repuesto->codigo_interno}}</td>
	      <td>{{$repuesto->cod_repuesto_proveedor}}</td>
	      <td>{{$repuesto->descripcion}}</td>
	      <td>{{$repuesto->medidas}}</td>
		  <td>{{$repuesto->anios_vehiculo}}</td>
		  <td>
			  <button class="btn btn-danger btn-sm" type="button"  onclick="eliminar({{$repuesto->id_relacionado}})">X</button>
		</td>

	    </tr>
	    @endforeach
		</tbody>
	  </table>
	</div>
</div>
@else
<div class="row">
<div class="alert alert-info">
	<h4><center>Sin Repuestos Relacionados.</center></h4>
</div>
</div>
@endif
