
<script>
	$('#monto-1').val({{$total}});
	$('#total_venta_vuelto').val({{$total}});
</script>
@if(isset($oferta) && $oferta == true)
<input type="hidden" name="es_oferta" id="es_oferta" value="1">
@else
<input type="hidden" name="es_oferta" id="es_oferta" value="0">
@endif
<input type="hidden" name="es_transferido" id="es_transferido" value="1">
<input type="hidden" name="confirmado" id="confirmado" value="">
<div class="row">
	<div class="col-sm-12 tabla-scroll-y-300">
	  <table id="tbl_carrito" class="table table-hover table-bordered table-sm letra-chica">
	    <thead>
			<th width="5%" scope="col" class="alin-cen">Cod Int</th>
			 <th width="33%" scope="col" class="alin-cen">Descripción</th>
			 <th width="8%" scope="col" class="alin-cen">Marca</th>
			 <th width="7%" scope="col" class="alin-cen">Cod Prov</th>
			 <th width="3%" scope="col" class="alin-cen">Cant</th>
			 <th width="7%" scope="col" class="alin-cen">Precio</th>
			 <th width="8%" scope="col" class="alin-cen">SubTotal</th>
			 <th width="7%" scope="col" class="alin-cen">Descuento</th>
			 <th width="8%" scope="col" class="alin-cen">Local</th>
			 <th width="8%" scope="col" class="alin-cen">Total</th>
			 <th width="3%" scope="col"></th> <!-- Ver Relacionados -->
			 <th width="3%" scope="col"></th> <!-- Borrar Item -->
		   </thead>
	    <tbody>
	    @foreach ($carrito_transferido as $item)
	    <tr>

			<td class="alin-cen"><span style="font-weight: bold;">{{$item->codigo_interno}}</span> </td>
	      <td>{{$item->descripcion}}</td>
		  <td>{{$item->marcarepuesto}}</td>
		  <td><span style="font-weight: bold; font-size:13px;">{{$item->cod_repuesto_proveedor}}</span></td>
	      <td class="alin-cen">{{$item->cantidad}}</td>
		  @if(isset($oferta) && $item->oferta == 1)
		  <td class="d-flex justify-content-between"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-danger">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>
		  @elseif($item->oferta == 2)
		  <td class="d-flex justify-content-between"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-primary">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>	
		  @else
				<td class="alin-der justify-content-between">{{number_format($item->pu,0,',','.')}}</td>
			@endif
	      <td class="alin-der">{{number_format($item->subtotal_item,0,',','.')}}</td>
          <td class="alin-der">{{number_format($item->descuento_item,0,',','.')}}</td>
		  <td class="alin-der">{{$item->local_nombre}}</td>
          <td class="alin-der">{{number_format($item->total_item,0,',','.')}}</td>
      <!--
		  <td class="alin-cen"><a href="javascript:void(0);" onclick="ver_relacionados({{$item->id_repuestos}})"><abbr title="Repuestos Relacionados">R</abbr></a> </td>
        -->
        <td>R</td>
          <td class="alin-cen"><a href="javascript:void(0);" onclick="borrar_item_carrito_transferido({{$item->id}})"><abbr title="Borrar Item"><b style="color:red">X</b></abbr></span></a></td>
	    	<!-- Input para guardar el vendedor -->
			<input type="hidden" name="vendedor_id" id="vendedor_id" value="{{$item->usuarios_id}}">
			<input type="hidden" name="cliente_id" id="cliente_id" value="{{$item->cliente_id}}">
			<input type="hidden" name="carrito_id" id="carrito_id" value="{{$item->id}}">
		</tr>
	    @endforeach
		</tbody>
	  </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-sm-8">
        <input type="hidden" id="items_carrito" value="{{$carrito_transferido->count()}}">
        <input type="hidden" id="total_carrito" value="{{$total}}">
        Total Items: {{$carrito_transferido->count()}}
    </div>
    <div class="col-sm-4" style="color:blue"><b>Total Venta: {{number_format($total,0,',','.')}}</b></div>
</div>


