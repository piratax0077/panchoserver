<!-- Carga en el div con id zona_familia en ventas_principal.blade.php
abajo en el modal
retorna un array-->
@if(count($familias)>0)
<div class="row">
	<div class="col-sm-12">
		<strong><center>FAMILIAS</center></strong>
        <small style="color:green">{{$quemodelo}}</small>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 tabla-scroll-y-200">
	  <table id="tbl_repuestos" class="table table-hover table-sm">
	    <thead>
	      <th width="25%" scope="col"></th>
	    </thead>
	    <tbody>
			<tr>
				<!-- El cero indica que desea todos los repuestos para ese modelo -->
				<td class="letra_pequeña"><a href="javascript:void(0);" onclick="damerepuestos(0,'{{$dato}}');">TODOS (@php echo $total_repuestos @endphp)</a></td>
			</tr>
	    @foreach ($familias as $familia)
	    <tr>
	      <td class="letra_pequeña"><a href="javascript:void(0);" onclick="damerepuestos({{$familia->id_familia}},'{{$dato}}');">{{$familia->nombrefamilia}}({{$familia->total}})</a></td>
	    </tr>
	    @endforeach
		</tbody>
	  </table>
	</div>
</div>
@else
<div class="row">
		<div class="alert alert-info">
			<h4><center>No se encontraron repuestos.</center></h4>
		</div>
</div>
@endif
