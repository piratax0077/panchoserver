@if($carrito->count()>0)
<script>
	$('#monto-1').val({{$total}});
	$('#total_venta_vuelto').val({{$total}});
</script>
{{-- @if($carrito_transferido->count() > 0)
	<p>Tiene un carrito pendiente</p>
@endif --}}
@foreach($carrito_transferido as $ct)
<div class="row mb-2">
	<div class="col-md-8">
		<span class="badge badge-danger p-1" style="font-size: 20px">Tiene un carrito pendiente de {{$ct->name}} de nombre {{$ct->titulo}}</span>
	</div>
	<div class="col-md-4">
		<button onclick="abrir_carrito_transferido({{$ct->cliente_id}})" class="btn btn-success btn-sm">Abrir carrito</button>
		<a href="javascript:borrar_carrito_transferido({{$ct->cliente_id}})" class="btn btn-danger btn-sm" >Eliminar carrito</a>
	</div>
</div>
@endforeach
@if(isset($oferta) && $oferta == true)
<input type="hidden" name="es_oferta" id="es_oferta" value="1">
@else
<input type="hidden" name="es_oferta" id="es_oferta" value="0">
@endif

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
	    @foreach ($carrito as $item)
	    <tr>

			<td class="alin-cen"> <a href="javascript:void(0)" onclick="dameInfoRepuestoModal({{$item->id_repuestos}})" data-target="#modalInfoRepuesto" data-toggle="modal">{{$item->codigo_interno}}</a>  </td>
	      <td>{{$item->descripcion}}</td>
		  
		  <td>{{$item->marcarepuesto}}</td>
		  <td><span style="font-weight: bold; font-size: 13px;">{{$item->cod_repuesto_proveedor}}</span></td>
	      <td class="alin-cen">{{$item->cantidad}}</td>
		  	@if(isset($oferta) && $item->oferta == 1 && ($oferta == true))
	      		<td class="alin-der d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-danger">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>
			@elseif(isset($oferta) && $item->oferta == 2 && ($oferta == true))
				<td class="alin-der d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-primary">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>
			@else
		  		<td class="alin-der">{{number_format($item->pu,0,',','.')}}</td>
		  	@endif
	      <td class="alin-der">{{number_format($item->subtotal_item,0,',','.')}}</td>
          <td class="alin-der">{{number_format($item->descuento_item,0,',','.')}}</td>
		  <td class="alin-der">{{$item->local_nombre}}</td>
          <td class="alin-der">{{number_format($item->total_item,0,',','.')}}</td>
      <!--
		  <td class="alin-cen"><a href="javascript:void(0);" onclick="ver_relacionados({{$item->id_repuestos}})"><abbr title="Repuestos Relacionados">R</abbr></a> </td>
        -->
        <td>R</td>
          <td class="alin-cen"><a href="javascript:void(0);" onclick="borrar_item_carrito({{$item->id}})"><abbr title="Borrar Item"><b style="color:red">X</b></abbr></span></a></td>
	    </tr>
	    @endforeach
		</tbody>
	  </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-sm-8">
        <input type="hidden" id="items_carrito" value="{{$carrito->count()}}">
        <input type="hidden" id="total_carrito" value="{{$total}}">
        Total Items: {{$carrito->count()}}
    </div>
    <div class="col-sm-4" style="color:blue"><b>Total Venta: {{number_format($total,0,',','.')}}</b></div>
</div>
@else
@if($carrito_transferido->count() > 0)
	@foreach($carrito_transferido as $ct)
	<div class="row mb-2">
		<div class="col-md-8">
			<span class="badge badge-danger p-1" style="font-size: 20px">Tiene un carrito pendiente de {{$ct->name}} de nombre {{$ct->titulo}}</span>
		</div>
		<div class="col-md-4">
			<button onclick="abrir_carrito_transferido({{$ct->cliente_id}})" class="btn btn-success btn-sm">Abrir carrito</button>
			<a href="javascript:borrar_carrito_transferido({{$ct->cliente_id}})" class="btn btn-danger btn-sm" >Eliminar carrito</a>
		</div>
	</div>
	@endforeach
@endif
<div class="col-sm-12">
    <div class="row">
        <h4 class='alert alert-info text-center'>Carrito vacio</h4><input type='hidden' id='items_carrito' value='0'>
        <input type="hidden" id="items_carrito" value="0">
    </div>
</div>

@endif
