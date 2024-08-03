<!-- Carga en el div con id zona_grilla en ventas_principal.blade.php
abajo en el modal -->

@if($repuestos->count()>0)
<div class="row">
	<div class="col-sm-12 tabla-scroll-y-300">
	  <table id="tbl_repuestos" class="table table-hover table-sm letra-chica">
	    <thead>

		 <th width="4%" scope="col">Cod Int</th>
	      <th width="15%" scope="col">Cod Rep Prov</th>
	      <th width="24%" scope="col">Descripción</th>
	      <th width="15%" scope="col">Medidas</th>
		  <th width="7%" scope="col">Años</th>
		  <th width="2%" scope="col"></th> <!-- Agregar al carrito -->
	      <th width="7%"></th> <!-- VER DETALLE -->
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
			  <button class="btn btn-success btn-sm" type="button" data-dismiss="modal" onclick="elegir({{$repuesto->id}})">Elegir</button>
		</td>
        <td><button class="btn btn-info btn-sm" onclick="mas_detalle({{$repuesto->id}})">Más Detalle</button></td>

	    </tr>
	    @endforeach
		</tbody>
	  </table>
	</div>
</div>
@else
<div class="row">
<div class="alert alert-info">
	<h4><center>Sin resultados.</center></h4>
</div>
</div>
@endif
