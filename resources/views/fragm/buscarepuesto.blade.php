<!-- Carga en el div con id mostrar_repuestos en repuestos.blade.php -->
@if($repuestos->count()>0)
<div class="row mt-5" style="line-height: 14px">
	<div class="col-md-12" style="padding-right:15px;padding-left:2px;">
	  <table id="tbl_repuestos" class="display compact table">
	    <thead class="thead-dark">
		<th width="4%" scope="col">Id</th>
		<th width="4%" scope="col">Cod Int</th>
		  <th width="10%" scope="col">Cod Rep Prov</th>
		  <th width="12%" scope="col">Proveedor</th>
	      <th width="35%" scope="col">Descripción</th>
	      <th width="10%" scope="col">Marca Repuesto</th>
		  <th width="10%" scope="col">Origen</th>
	      <th width="7%" scope="col">Pr.Venta</th>
	      <th width="7%"></th> <!-- VER DETALLE en ventana MODAL-->
	      <th width="7%"></th> <!-- MODIFICAR -->
	    </thead>
	    <tbody>
	    @foreach ($repuestos as $repuesto)
	    <tr>
			<td><a href="">{{$repuesto->id}}</a> </td>
			<td>{{$repuesto->codigo_interno}}</td>
		  	<td>{{$repuesto->cod_repuesto_proveedor}}</td>
		  	<td>{{$repuesto->proveedor->empresa_nombre}}</td>
			<td>{{$repuesto->descripcion}} Med: {{$repuesto->medidas}}</td>
	      	<td>{{strtoupper($repuesto->marcarepuesto->marcarepuesto)}}</td>
	      	<td>{{$repuesto->pais->nombre_pais}}</td>
	      	<td><strong><p id="precio_venta">$ {{str_replace(",",".",number_format($repuesto->precio_venta))}}</p></strong></td>

			<td><button class="btn btn-warning btn-sm" onclick="abrir_modal({{$repuesto->id}})">Más Detalle</button>
<!--
	      	<a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#detalle-modal">Más Detalle</a>
-->
	      </td>
	      <td>
	        <a href="{{url('repuesto/modificar/'.$repuesto->id)}}" target="_blank" class="btn btn-primary btn-sm">Modificar</a>
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
	<h4><center>Sin resultados.</center></h4>
</div>
</div>
@endif
