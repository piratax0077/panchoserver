<style>
	/* =========================================================
	   CARRITO TRANSFERIDO - DISEÑO PROFESIONAL
	   ========================================================= */
	.carrito-card{
		background:#fff;
		border:1px solid #e2e8f0;
		border-radius:14px;
		overflow:hidden;
		box-shadow:0 4px 14px rgba(15,23,42,.06);
	}

	.carrito-card-header{
		display:flex;
		align-items:center;
		justify-content:space-between;
		gap:12px;
		padding:13px 16px;
		background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);
		border-bottom:1px solid #e2e8f0;
	}

	.carrito-card-title{
		display:flex;
		align-items:center;
		gap:9px;
		margin:0;
		color:#172033;
		font-size:16px;
		font-weight:800;
	}

	.carrito-card-title i{
		color:#7c3aed;
	}

	.carrito-transferido-badge{
		display:inline-flex;
		align-items:center;
		gap:6px;
		padding:5px 10px;
		border-radius:999px;
		color:#6d28d9;
		background:#ede9fe;
		font-size:11px;
		font-weight:850;
		text-transform:uppercase;
	}

	.carrito-badge-items{
		display:inline-flex;
		align-items:center;
		justify-content:center;
		min-width:34px;
		height:28px;
		padding:0 10px;
		border-radius:999px;
		color:#1d4ed8;
		background:#dbeafe;
		font-size:12px;
		font-weight:800;
	}

	.carrito-table-wrap{
		max-height:430px;
		overflow:auto;
	}

	.carrito-table{
		width:100%;
		min-width:1180px;
		margin:0;
		border-collapse:separate;
		border-spacing:0;
	}

	.carrito-table thead th{
		position:sticky;
		top:0;
		z-index:3;
		padding:10px 8px;
		border-top:0;
		border-bottom:1px solid #cbd5e1;
		background:#ede9fe;
		color:#4c1d95;
		font-size:10px;
		font-weight:850;
		letter-spacing:.03em;
		text-align:center;
		text-transform:uppercase;
		white-space:nowrap;
	}

	.carrito-table tbody td{
		padding:9px 8px;
		border-top:0;
		border-bottom:1px solid #e2e8f0;
		color:#334155;
		font-size:12px;
		vertical-align:middle;
	}

	.carrito-table tbody tr{
		transition:background .15s ease, box-shadow .15s ease;
	}

	.carrito-table tbody tr:hover{
		background:#faf8ff;
		box-shadow:inset 3px 0 #7c3aed;
	}

	.carrito-code{
		display:inline-flex;
		align-items:center;
		gap:5px;
		color:#6d28d9;
		font-weight:850;
		white-space:nowrap;
	}

	.carrito-description{
		display:block;
		color:#172033;
		font-weight:700;
		line-height:1.35;
	}

	.carrito-secondary{
		color:#64748b;
		font-size:11px;
	}

	.carrito-money{
		font-variant-numeric:tabular-nums;
		font-weight:700;
		text-align:right;
		white-space:nowrap;
	}

	.carrito-total-item{
		color:#166534;
		font-weight:850;
	}

	.carrito-offer{
		display:inline-flex;
		align-items:center;
		gap:5px;
		margin-right:5px;
		padding:3px 6px;
		border-radius:999px;
		color:#fff;
		background:#dc2626;
		font-size:9px;
		font-weight:850;
		text-transform:uppercase;
	}

	.carrito-offer.blue{
		background:#2563eb;
	}

	.carrito-location{
		display:inline-flex;
		align-items:center;
		gap:5px;
		padding:4px 7px;
		border-radius:999px;
		color:#475569;
		background:#f1f5f9;
		font-size:10px;
		font-weight:700;
		white-space:nowrap;
	}

	.carrito-actions{
		display:flex;
		align-items:center;
		justify-content:center;
		gap:5px;
	}

	.carrito-action-btn{
		display:inline-flex;
		align-items:center;
		justify-content:center;
		width:31px;
		height:31px;
		padding:0;
		border:0;
		border-radius:8px;
		color:#fff;
		transition:transform .14s ease, filter .14s ease;
	}

	.carrito-action-btn:hover{
		color:#fff;
		filter:brightness(.94);
		transform:translateY(-1px);
		text-decoration:none;
	}

	.carrito-action-related{ background:#0ea5e9; }
	.carrito-action-delete{ background:#dc2626; }

	.carrito-summary{
		display:grid;
		grid-template-columns:minmax(0,1fr) auto;
		gap:14px;
		align-items:center;
		padding:14px 16px;
		background:#f8fafc;
		border-top:1px solid #e2e8f0;
	}

	.carrito-summary-left{
		display:flex;
		align-items:center;
		gap:9px;
		color:#475569;
		font-size:13px;
	}

	.carrito-summary-left i{
		color:#7c3aed;
	}

	.carrito-summary-total{
		min-width:260px;
		padding:12px 15px;
		border-radius:10px;
		color:#fff;
		background:linear-gradient(135deg,#166534,#15803d);
		box-shadow:0 4px 12px rgba(21,128,61,.18);
		text-align:right;
	}

	.carrito-summary-total small{
		display:block;
		margin-bottom:3px;
		font-size:10px;
		font-weight:700;
		opacity:.85;
		text-transform:uppercase;
	}

	.carrito-summary-total strong{
		display:block;
		font-size:21px;
		line-height:1.1;
	}

	.carrito-summary-normal{
		display:block;
		margin-top:4px;
		font-size:11px;
		opacity:.9;
	}

	@media(max-width:767.98px){
		.carrito-summary{
			grid-template-columns:1fr;
		}

		.carrito-summary-total{
			width:100%;
			min-width:0;
		}

		.carrito-table-wrap{
			max-height:55vh;
		}
	}
</style>

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

@if($carrito_transferido->count() > 0)
@php
	$cantidad_total_transferida = (int) $carrito_transferido->sum('cantidad');
@endphp

<div class="carrito-card">
	<div class="carrito-card-header">
		<h3 class="carrito-card-title">
			<i class="fa-solid fa-right-left"></i>
			Carrito transferido
		</h3>

		<div class="d-flex align-items-center" style="gap:8px;">
			<span class="carrito-transferido-badge">
				<i class="fa-solid fa-share-nodes"></i>
				Transferido por {{$carrito_transferido[0]->usuario_name}}
			</span>

			<span class="carrito-badge-items">
				{{$cantidad_total_transferida}}
				unidad{{$cantidad_total_transferida == 1 ? '' : 'es'}}
			</span>
		</div>
	</div>

	<div class="carrito-table-wrap">
		<table id="tbl_carrito" class="table carrito-table">
			<colgroup>
				<col style="width:7%">
				<col style="width:27%">
				<col style="width:8%">
				<col style="width:9%">
				<col style="width:5%">
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:8%">
				<col style="width:2%">
				<col style="width:2%">
			</colgroup>

			<thead>
				<tr>
					<th>Código</th>
					<th>Descripción</th>
					<th>Marca</th>
					<th>Cód. proveedor</th>
					<th>Cant.</th>
					<th>Precio</th>
					<th>Subtotal</th>
					<th>Descuento</th>
					<th>Local</th>
					<th>Total</th>
					<th colspan="2">Acciones</th>
				</tr>
			</thead>

			<tbody>
				@foreach ($carrito_transferido as $item)
					<tr>
						<td class="text-center">
							<span class="carrito-code">
								<i class="fa-solid fa-barcode"></i>
								{{$item->codigo_interno}}
							</span>
						</td>

						<td>
							<span class="carrito-description">{{$item->descripcion}}</span>
						</td>

						<td>
							<span class="carrito-secondary">{{$item->marcarepuesto}}</span>
						</td>

						<td>
							<strong>{{$item->cod_repuesto_proveedor}}</strong>
						</td>

						<td class="text-center">
							<strong>{{$item->cantidad}}</strong>
						</td>

						@if(isset($oferta) && $item->oferta == 1)
							<td class="carrito-money">
								<a href="javascript:void(0)"
								   onclick="abrirModalPrecio({{$item->id_repuestos}})">
									<span class="carrito-offer">Oferta</span>
								</a>
								${{number_format($item->pu,0,',','.')}}
							</td>
						@elseif($item->oferta == 2)
							<td class="carrito-money">
								<a href="javascript:void(0)"
								   onclick="abrirModalPrecio({{$item->id_repuestos}})">
									<span class="carrito-offer blue">Oferta</span>
								</a>
								${{number_format($item->pu,0,',','.')}}
							</td>
						@else
							<td class="carrito-money">
								${{number_format($item->pu,0,',','.')}}
							</td>
						@endif

						<td class="carrito-money">
							${{number_format($item->subtotal_item,0,',','.')}}
						</td>

						<td class="carrito-money">
							@if((float)$item->descuento_item > 0)
								<span class="text-danger">
									-${{number_format($item->descuento_item,0,',','.')}}
								</span>
							@else
								$0
							@endif
						</td>

						<td class="text-center">
							<span class="carrito-location">
								<i class="fa-solid fa-warehouse"></i>
								{{$item->local_nombre}}
							</span>
						</td>

						<td class="carrito-money carrito-total-item">
							${{number_format($item->total_item,0,',','.')}}
						</td>

						<td>
							<div class="carrito-actions">
								<a
									href="javascript:void(0);"
									onclick="ver_relacionados({{$item->id_repuestos}})"
									class="carrito-action-btn carrito-action-related"
									title="Ver repuestos relacionados">
									<i class="fa-solid fa-link"></i>
								</a>
							</div>
						</td>

						<td>
							<div class="carrito-actions">
								<a
									href="javascript:void(0);"
									onclick="borrar_item_carrito_transferido({{$item->id}})"
									class="carrito-action-btn carrito-action-delete"
									title="Eliminar del carrito transferido">
									<i class="fa-solid fa-trash"></i>
								</a>
							</div>
						</td>

						<input type="hidden" name="vendedor_id" id="vendedor_id" value="{{$item->usuarios_id}}">
						<input type="hidden" name="cliente_id" id="cliente_id" value="{{$item->cliente_id}}">
						<input type="hidden" name="carrito_id" id="carrito_id" value="{{$item->id}}">
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="carrito-summary">
		<div class="carrito-summary-left">
			<i class="fa-solid fa-boxes-stacked"></i>
			<span>
				<strong>{{$cantidad_total_transferida}}</strong>
				unidad{{$cantidad_total_transferida == 1 ? '' : 'es'}}
				en el carrito transferido
			</span>
		</div>

		<div class="carrito-summary-total">
			<small>Total venta oferta</small>
			<strong>${{number_format($total,0,',','.')}}</strong>

			<span class="carrito-summary-normal">
				Total normal: ${{number_format($total_normal,0,',','.')}}
			</span>
		</div>
	</div>
</div>

<input type="hidden" id="items_carrito" value="{{$carrito_transferido->count()}}">
<input type="hidden" id="unidades_carrito" value="{{$cantidad_total_transferida}}">
<input type="hidden" id="total_carrito" value="{{$total}}">

@else

<div class="alert alert-info text-center mb-0">
	<i class="fa-solid fa-cart-shopping mr-1"></i>
	El carrito transferido está vacío.
</div>

<input type="hidden" id="items_carrito" value="0">
<input type="hidden" id="total_carrito" value="0">

@endif
