<style>
	/* =========================================================
	   CARRITO DE VENTA - DISEÑO PROFESIONAL
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
		color:#2563eb;
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

	.carrito-transferidos{
		margin-bottom:12px;
		padding:12px;
		border:1px solid #fde68a;
		border-radius:12px;
		background:#fffbeb;
	}

	.carrito-transferidos-title{
		display:flex;
		align-items:center;
		gap:7px;
		margin-bottom:9px;
		color:#92400e;
		font-size:13px;
		font-weight:800;
	}

	.carrito-transferidos .table{
		margin-bottom:0;
		background:#fff;
		border-radius:8px;
		overflow:hidden;
	}

	.carrito-transferidos .table thead th{
		padding:8px 9px;
		border-top:0;
		background:#fef3c7;
		color:#78350f;
		font-size:11px;
		font-weight:800;
		text-transform:uppercase;
		white-space:nowrap;
	}

	.carrito-transferidos .table tbody td{
		padding:8px 9px;
		vertical-align:middle;
		font-size:12px;
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
		background:#d9f7ef;
		color:#134e4a;
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
		background:#f8fafc;
		box-shadow:inset 3px 0 #3b82f6;
	}

	.carrito-code-link{
		display:inline-flex;
		align-items:center;
		gap:5px;
		color:#2563eb;
		font-weight:800;
		text-decoration:none;
		white-space:nowrap;
	}

	.carrito-code-link:hover{
		color:#1d4ed8;
		text-decoration:none;
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
		color:#2563eb;
	}

	.carrito-summary-total{
		min-width:245px;
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

	.carrito-empty{
		display:flex;
		flex-direction:column;
		align-items:center;
		justify-content:center;
		min-height:250px;
		padding:35px 20px;
		border:1px dashed #cbd5e1;
		border-radius:14px;
		color:#64748b;
		background:#f8fafc;
		text-align:center;
	}

	.carrito-empty-icon{
		display:flex;
		align-items:center;
		justify-content:center;
		width:64px;
		height:64px;
		margin-bottom:13px;
		border-radius:50%;
		color:#2563eb;
		background:#dbeafe;
		font-size:27px;
	}

	.carrito-empty h4{
		margin-bottom:5px;
		color:#1e293b;
		font-size:18px;
		font-weight:800;
	}

	.carrito-empty p{
		margin:0;
		font-size:13px;
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

@if($carrito->count()>0)
@php
	$cantidad_total_carrito = (int) $carrito->sum('cantidad');
@endphp
<script>
	$('#monto-1').val({{$total}});
	$('#total_venta_vuelto').val({{$total}});
</script>

@if($carrito_transferido->count() > 0)
<div class="carrito-transferidos">
	<div class="carrito-transferidos-title">
		<i class="fa-solid fa-share-nodes"></i>
		Carritos transferidos pendientes
	</div>

	<div class="table-responsive">
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Enviado por</th>
					<th>Nombre del carrito</th>
					<th>Revisado por</th>
					<th class="text-center">Acciones</th>
				</tr>
			</thead>
			<tbody>
				@foreach($carrito_transferido as $ct)
				<tr>
					<td>{{$ct->usuario_name}}</td>
					<td><strong>{{$ct->titulo}}</strong></td>
					<td>
						{{$ct->bodeguero_name !== null
							? $ct->bodeguero_name
							: 'Aún no es revisado en bodega.'}}
					</td>
					<td class="text-center">
						<div class="carrito-actions">
							<button
								type="button"
								onclick="abrir_carrito_transferido({{$ct->cliente_id}})"
								class="carrito-action-btn carrito-action-related"
								title="Abrir carrito">
								<i class="fas fa-check"></i>
							</button>

							<a
								href="javascript:borrar_carrito_transferido({{$ct->cliente_id}})"
								class="carrito-action-btn carrito-action-delete"
								title="Eliminar carrito">
								<i class="fas fa-trash"></i>
							</a>
						</div>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endif

@if(isset($oferta) && $oferta == true)
<input type="hidden" name="es_oferta" id="es_oferta" value="1">
@else
<input type="hidden" name="es_oferta" id="es_oferta" value="0">
@endif

<input type="hidden" name="confirmado" id="confirmado" value="">

<div class="carrito-card">
	<div class="carrito-card-header">
		<h3 class="carrito-card-title">
			<i class="fa-solid fa-cart-shopping"></i>
			Detalle del carrito
		</h3>

		<span class="carrito-badge-items">
			{{$cantidad_total_carrito}} unidad{{$cantidad_total_carrito == 1 ? '' : 'es'}}
		</span>
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
				@foreach ($carrito as $item)
				<tr>
					<td class="text-center">
						<a
							href="javascript:void(0)"
							onclick="dameInfoRepuestoModal({{$item->id_repuestos}})"
							data-target="#modalInfoRepuesto"
							data-toggle="modal"
							class="carrito-code-link"
							title="Ver información del repuesto">
							<i class="fa-solid fa-circle-info"></i>
							{{$item->codigo_interno}}
						</a>
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

					@if(isset($oferta) && $item->oferta == 1 && ($oferta == true))
						<td class="carrito-money">
							<a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})">
								<span class="carrito-offer">Oferta</span>
							</a>
							${{number_format($item->pu,0,',','.')}}
						</td>
					@elseif(isset($oferta) && $item->oferta == 2 && ($oferta == true))
						<td class="carrito-money">
							<a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})">
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
								onclick="borrar_item_carrito({{$item->id}})"
								class="carrito-action-btn carrito-action-delete"
								title="Eliminar del carrito">
								<i class="fa-solid fa-trash"></i>
							</a>
						</div>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="carrito-summary">
		<div class="carrito-summary-left">
			<i class="fa-solid fa-boxes-stacked"></i>
			<span>
				<strong>{{$cantidad_total_carrito}}</strong>
				unidad{{$cantidad_total_carrito == 1 ? '' : 'es'}} en el carrito
			</span>
		</div>

		<div class="carrito-summary-total">
			<small>Total venta oferta</small>
			<strong>${{number_format($total,0,',','.')}}</strong>

			@if(Auth::user()->rol->nombrerol !== 'bodega-venta')
				<span class="carrito-summary-normal">
					Total normal: ${{number_format($total_normal,0,',','.')}}
				</span>
			@endif
		</div>
	</div>
</div>

<input type="hidden" id="items_carrito" value="{{$carrito->count()}}">
<input type="hidden" id="unidades_carrito" value="{{$cantidad_total_carrito}}">
<input type="hidden" id="total_carrito" value="{{$total}}">

@else

@if($carrito_transferido->count() > 0)
<div class="carrito-transferidos">
	<div class="carrito-transferidos-title">
		<i class="fa-solid fa-share-nodes"></i>
		Carritos transferidos pendientes
	</div>

	<div class="table-responsive">
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Enviado por</th>
					<th>Nombre del carrito</th>
					<th>Revisado por</th>
					<th class="text-center">Acciones</th>
				</tr>
			</thead>

			<tbody>
				@foreach($carrito_transferido as $ct)
				<tr>
					<td>{{$ct->usuario_name}}</td>
					<td><strong>{{$ct->titulo}}</strong></td>
					<td>
						{{$ct->bodeguero_name !== null
							? $ct->bodeguero_name
							: 'Aún no es revisado en bodega.'}}
					</td>
					<td class="text-center">
						<div class="carrito-actions">
							<button
								type="button"
								onclick="abrir_carrito_transferido({{$ct->cliente_id}})"
								class="carrito-action-btn carrito-action-related"
								title="Abrir carrito">
								<i class="fas fa-check"></i>
							</button>

							<a
								href="javascript:borrar_carrito_transferido({{$ct->cliente_id}})"
								class="carrito-action-btn carrito-action-delete"
								title="Eliminar carrito">
								<i class="fas fa-trash"></i>
							</a>
						</div>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endif

<div class="carrito-empty">
	<div class="carrito-empty-icon">
		<i class="fa-solid fa-cart-shopping"></i>
	</div>

	<h4>El carrito está vacío</h4>
	<p>Busque un repuesto o ingrese su código para comenzar una nueva venta.</p>
</div>

<input type="hidden" id="items_carrito" value="0">
<input type="hidden" id="total_carrito" value="0">

@endif
