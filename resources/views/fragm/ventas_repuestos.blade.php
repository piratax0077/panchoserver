<!-- Carga en el div con id zona_grilla en ventas_principal.blade.php
abajo en el modal -->

<style>
.letra_pequeña {
    font-family: 'Arial Narrow';
    font-size: 14px;
}

.imagen_pequeña{
    width: 140px;
    height: 130px;
}
.row_cero_margen{
    margin-left:0px;
    margin-right:0px;
}
/*
.table-sm tbody tr:hover td {
	cursor:pointer;
}
*/
.table-sm tbody tr td{
    padding-left:3px;
}
.modal-body{
    max-height: calc(100vh - 180px);
    overflow-y: auto;
}

</style>
@if($repuestos->count()>0)
	@php
		$hay_foto=[];
	@endphp
	@foreach($tienen_foto as $f)
		@php $hay_foto[]=$f['id_repuestos']@endphp
	@endforeach

<div class="row row_cero_margen">
	<div class="col-sm-10">
        <p class="text-sm-center" style="margin-bottom:-7px;">Mostrando {{$repuestos->count()}} resultados {{$criterio}}</p>
	</div>
    <div class="col-sm-2">
        <div class="row">
            <div class="col-sm-6"><a href="javascript:void(0);" onclick="buscar_imagenes({{$repuestos}})"><i class="fas fa-images"></i></a> </div>
            <div class="col-sm-6"><a href="javascript:void(0);" onclick="buscar_info()"><i class="fas fa-list"></i></a> </div>
        </div>
    </div>
</div>
<div class="row row_cero_margen" id="tabla_info_completa">

	<div class="col-12" style="padding-right: 0px;padding-left:0px" id="rezultadoz">
        <fieldset id="rezultadoz_fieldset">
        <div class="tabla-scroll-y-300 h-100">
        <table id="tbl_repuestos" class="table table-sm table-hover" width="100%">
	    <thead>
			@if($desde=='d' || $desde=='p' || $desde=='m')
				<th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
			@endif
			@if($desde=='o')
				<th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
				<th width="5%" scope="col" class="letra_pequeña">OEM</th>
			@endif
			@if($desde=='f')
				<th width="8%" scope="col" class="letra_pequeña">Cod Fab</th>
			@endif
		 <th width="7%" scope="col" class="letra_pequeña">Proveedor</th>
	      <th width="9%" scope="col" class="letra_pequeña">Cod Rep Prov</th>
	      <th width="18%" scope="col" class="letra_pequeña">Descripción</th>
		  <th width="15%" scope="col" class="letra_pequeña">Medida</th>
          <th width="8%" scope="col" class="letra_pequeña">Marca</th>
		  <th width="7%" scope="col" class="letra_pequeña">Origen</th>
		  
		  <th width="6%" scope="col" class="letra_pequeña">Precio Venta</th>
		  <th width="1%" scope="col"></th> <!-- Agregar al carrito -->
		  <th width="5%" scope="col" class="letra_pequeña">Stock total</th>
		  <th width="8%" scope="col" class="letra_pequeña">Stock detallado</th>
          <th width="2%" scope="col" class="letra_pequeña">Stock Actualizado</th>
          <th width="5%" scope="col" class="letra_pequeña">Actualización de Precio</th>
          <th width="2%"></th> <!-- VER DETALLE -->
          <th width="1%"></th> <!-- MODIFICAR PRECIO -->
          <th width="1%"></th>
	    </thead>
	    <tbody>
	    @foreach ($repuestos as $repuesto)

        @php
            // ocultar los repuestos que no tengan stock a los usuarios que tengan el rol vendedor
            // if(Auth::user()->rol->nombrerol === "vendedor"){
            //     if($repuesto->stock_actual<=0 && $repuesto->stock_actual_dos<=0 && $repuesto->stock_actual_tres<=0){
            //         continue;
            //     }
            // }
        @endphp
        @php
            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);
        @endphp
		@if($repuesto->stock_actual>0 || $repuesto->stock_actual_dos>0 || $repuesto->stock_actual_tres>0)
			<tr>
		@else
			<tr bgcolor="#ff9999">
		@endif
		@if($desde=='d' || $desde=='p' || $desde=='m')
			<td class="letra_pequeña">
                @php $opt = false; @endphp
                @foreach(Auth::user()->dame_permisos_mantenimiento() as $permiso)
                    @if($permiso->path_ruta == '/modificarRepuesto')
                        @php $opt = true; @endphp
                    @endif
                @endforeach
                @if(Auth::user()->rol->nombrerol === "Administrador" || $opt == true)
                    <a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank" style="color:black">{{$repuesto->codigo_interno}}</a>
                @else
                    {{$repuesto->codigo_interno}}
                @endif
            </td>
		@endif
		@if($desde=='o' )
			<td class="letra_pequeña">{{$repuesto->codigo_interno}}</td>
			<td class="letra_pequeña">{{$repuesto->codigo_oem}}</td>
		@endif
		@if($desde=='f' )
			<td class="letra_pequeña">{{$repuesto->codigo_fab}}</td>
		@endif
		<td class="letra_pequeña">{{$repuesto->empresa_nombre_corto}}</td>
		<td class="letra_pequeña">{{$repuesto->cod_repuesto_proveedor}}</td>
		<td class="letra_pequeña">
            @if($repuesto->observaciones=='@@@')
                {{$repuesto->descripcion}}
            @else
                {{$repuesto->descripcion}} <i class="text-danger" style="font-weight: bold;">{{$repuesto->observaciones}}</i>
            @endif
        </td>
		 @if($repuesto->medidas=='No Definidas' || $repuesto->medidas=='' || strlen($repuesto->medidas)==0  || $repuesto->medidas=='0')
		 	<td></td>
		@else

        @php
           //$porciones =explode(',',$repuesto->medidas);
           // Usando explode con múltiples delimitadores
            $porciones = preg_split('/[:,]/', $repuesto->medidas);
        @endphp
			<td class="letra_pequeña" style="background-color:#eee;">
                @foreach($porciones as $p)
                @if($p == 'DISCO' || $p == 'PRENSA')
                    <span style="font-weight: bold">{{$p}}</span><br>
                @else
                    <span>{{$p}}</span> <br>
                @endif
                
                @endforeach
            </td>
		@endif
            <td class="letra_pequeña">{{strtoupper($repuesto->marcarepuesto)}}</td>
			<td class="letra_pequeña">{{$repuesto->nombre_pais}}</td>
			
            <td class="letra_pequeña" style="text-align: right">
                 @if(Auth::user()->rol->nombrerol !== "Bodeguer@")              
                @if($repuesto->oferta == 1)
                    <p id="ppv-{!!$repuesto->id!!}" class="d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-danger">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                @elseif($repuesto->oferta == 2)
                    <p id="ppv-{!!$repuesto->id!!}" class="d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-primary">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                @else
                    @if(Auth::user()->rol->nombrerol == "Administrador")
                    <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                        <a style="color: black;" href="javascript:void(0)" onclick="modifikar_precio({{$repuesto->id}});">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                    </p>
                    @else
                    <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                        <a style="color: black;" href="javascript:void(0)">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                    </p>
                    @endif
                @endif
               

                <input type="hidden" id="pv-{!!$repuesto->id!!}" value=" {!!number_format($repuesto->precio_venta,0,',','.')!!}">
                @endif
            </td>

            @if($repuesto->stock_actual>0 || $repuesto->stock_actual_dos > 0 || $repuesto->stock_actual_tres > 0)
                <td class="text-right letra_pequeña"><button class="btn btn-primary btn-sm" id="btn_agregar_carrito" style="padding-bottom:0.8px;padding-top:unset;height:20px" onclick="agregar_carrito({{$repuesto->id}})">+</button></td>
                <td class="letra_pequeña">
                        <input type="hidden" value="{{$repuesto->stock_actual}}" id="stock-{{$repuesto->id}}">
                        <input class="form-control form-control-sm letra_pequeña" style="text-align:center;padding:1px;height:20px" type="text" id="cant-{{$repuesto->id}}" onkeyup="enter_agregar_carrito(event,{{$repuesto->id}})" maxlength="3" placeholder="Stk:{{$repuesto->stock_actual + $repuesto->stock_actual_dos + $repuesto->stock_actual_tres}}">
                        @if($repuesto->stock_minimo >= intval($repuesto->stock_actual + $repuesto->stock_actual_dos + $repuesto->stock_actual_tres)) <span class="badge badge-danger"><a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="text-decoration-none text-white" data-target="#modalStockMinimo" data-toggle="modal">Stock Mínimo {{$repuesto->estado}}</a> </span> @endif
                </td>
                <td class="letra_pequeña">
                        <select name="cboLocal" class="form-control form-control-sm letra_pequeña" id="local-{{$repuesto->id}}" style="padding:1px;height:20px">
                            @foreach($stocks_repuesto as $stock_repuesto)
                           
                                @if(!empty($stock_repuesto))
                                        @if($stock_repuesto->id==$repuesto->id)
                                            <option value="{{$stock_repuesto->id_local}}">{{$stock_repuesto->local_nombre}}({{$stock_repuesto->stock_actual}}) - {{$stock_repuesto->responsable == "Bodega" ? 'Jael Rojas' : $stock_repuesto->responsable}}</option>
                                        @endif
                                @endif
                            @endforeach
                        </select>
                </td>
            @else
                <td></td>
                <td class="letra_pequeña"><span class="badge badge-danger"><a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="text-decoration-none text-white" data-target="#modalStockMinimo" data-toggle="modal">Sin Stock {{$repuesto->estado}} </a></span>  </td>
                <td></td>
            @endif
           @php 
           $clase = "";
           if($repuesto->fecha_ultima === "2022-05-25"){
            $clase = "bg-danger text-white rounded"; 
           }
                
           @endphp
            <td class="letra_pequeña {{$clase}}"><strong>{{Carbon\Carbon::createFromFormat('Y-m-d', $repuesto->fecha_ultima)->format('d-m-Y');}} </strong></td>
            @if ($dias <= 30 && Auth::user()->rol->nombrerol !== "Bodeguer@")
                <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: green; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
            @elseif($dias > 30 && $dias <= 60  && Auth::user()->rol->nombrerol !== "Bodeguer@")
                <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: yellow; text-align:center; border-radius: 5px;">{{$dias}}</td>
            @else
            @if(Auth::user()->rol->nombrerol !== "Bodeguer@")
                <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: red; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
                @endif
            @endif

            

            @if(in_array($repuesto->id,$hay_foto))
                <td class="letra_pequeña"><button class="btn btn-success btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal">Detalle</button></td>
            @else
                <td class="letra_pequeña"><button class="btn btn-success btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal"><abbr title="Repuesto Sin fotos">Detalle</abbr></button></td>
            @endif
            @if(Auth::user()->rol->nombrerol === "Administrador" && Auth::user()->rol->nombrerol !== "Bodeguer@"  || $permiso_modificar == true)
                <td class="letra_pequeña"><a href="javascript:void(0);" class="btn btn-warning btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="modifikar_precio({{$repuesto->id}});">M</a></td>
                @if(Auth::user()->rol->nombrerol === "Administrador")<td class="letra_pequeña"><a href="javascript:void(0);" class="btn btn-danger btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="generate_codebar({{$repuesto->id}})">CB</a></td> @endif
            @endif
	    </tr>
	    @endforeach
		</tbody>
      </table>
      
        </div>
    </fieldset>
    </div>

    <div class="col" id="modifikar_precio" style="display:none; padding-right:1px;padding-left:10px">
        <h5 class="d-flex justify-content-center" style="margin-top:0px">
        <b>MODIFICAR PRECIO</b></h5>
        <p style="color:red" class="letra_pequeña">
            <b>ATENCIÓN:</b>
            <abbr title="Debe quitar el item y agregarlo luego de modificar el precio.">Esta operación no modifica los precios del carrito de compras. </abbr>
        </p>
        <div class="row">
            <div class="col-3 letra_pequeña" style="text-align: right;padding-left:1px;padding-right:1px">Precio Actual:</div>
            <div class="col-3" style="text-align: left; padding-left:1px;padding-right:1px"><input type="text" value="0" id="precio_a_modificar" style="width: 85px;text-align:right;margin-bottom:5px;"></div>
            <div class="col-3 d-flex" style="text-align: center;padding-left:1px;padding-right:1px">
                <button class="btn btn-sm btn-success letra_pequeña" onclick="guardar_nuevo_precio()"><i class="fa-solid fa-floppy-disk"></i></button>
                @if(Auth::user()->rol->nombrerol == "Administrador" || $permiso_actualizar == true)<button class="btn btn-sm btn-secondary" onclick="resetear_tiempo_precio()"><i class="fa-solid fa-arrows-rotate"></i></button>@endif
            </div>
            <div class="col-3" style="text-align: center; padding-left:5px;padding-right:1px">
                <button class="btn btn-sm btn-danger letra_pequeña" onclick="cerrar_panel_modifikar()"><i class="fa-solid fa-circle-xmark"></i></button>
            </div>
        </div>
        <div class="row" style="width:100%;margin-right:1px">
            <p><b>Compras Realizadas:</b></p>
            <div class="col-12" id="compras_repuesto" style="width:100%;padding-right:1px"></div>
        </div>



    </div>
</div>
@if(count($arreglo) > 0)
<div id="tabla_info_imagenes" style="display: none;" class="row row_cero_margen">
    
    <div class="col-12" style="padding-right: 0px;padding-left:0px" id="rezultadoz">
        <fieldset id="rezultadoz_fieldset">
            <div class="tabla-scroll-y-300 h-100">
                
                    <table id="tbl_repuestos" class="table table-sm table-hover" width="100%">
                        <thead>
                            <th width="8%" scope="col" class="letra_pequeña">Imagen</th>
                            <th width="8%" scope="col" class="letra_pequeña">Código interno</th>
                            <th width="7%" scope="col" class="letra_pequeña">Proveedor</th>
                            <th width="9%" scope="col" class="letra_pequeña">Cod Rep Prov</th>
                            <th width="8%" scope="col" class="letra_pequeña">Descripción</th> 
                            <th width="7%" scope="col" class="letra_pequeña">Marca</th> 
                            <th width="10%" scope="col" class="letra_pequeña">Origen</th>
	                        <th width="6%" scope="col" class="letra_pequeña">Precio Venta</th>
                            <th width="3%" scope="col" class="letra_pequeña"></th>
                            <th width="5%" scope="col" class="letra_pequeña">Stock total</th>
		                    <th width="8%" scope="col" class="letra_pequeña">Stock detallado</th>
                            <th width="2%" scope="col" class="letra_pequeña">Stock Actualizado</th>
                            <th width="5%" scope="col" class="letra_pequeña">Actualización de Precio</th>
                            <th width="2%"></th> <!-- VER DETALLE -->
                            <th width="1%"></th> <!-- MODIFICAR PRECIO -->
                            <th width="1%"></th>
                        </thead>
                       
                        <tbody>
                            @foreach ($arreglo as $repuesto)
                            @php
                                //Fecha ultima actualización del precio del repuesto
                                $firstDate = $repuesto->fecha_actualiza_precio;
                                $secondDate = date('d-m-Y H:i:s');

                                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                                $minutos = floor(abs($dateDifference / 60));
                                $horas = floor($minutos / 60);
                                $dias = floor($horas / 24);
                            @endphp
                            @if($repuesto->stock_actual>0 || $repuesto->stock_actual_dos>0 || $repuesto->stock_actual_tres>0)
                            <tr>
                            @else
                                <tr bgcolor="#ff9999">
                            @endif
                            
                                    <td><a href="javascript:void(0)" onclick="abrir_foto_modal('{{$repuesto->id}}')"><img src="{{asset('storage/'.$repuesto->urlfoto)}}" alt="foto repuesto"  class="imagen_pequeña"> </a> </td>
                                    <td class="letra_pequeña">{{$repuesto->codigo_interno}}</td>
                                    <td class="letra_pequeña">{{$repuesto->empresa_nombre_corto}}</td>
                                    <td class="letra_pequeña">{{$repuesto->cod_repuesto_proveedor}}</td>
                                    <td class="letra_pequeña"> {{$repuesto->descripcion}} </td>
                                    <td class="letra_pequeña">{{strtoupper($repuesto->marcarepuesto)}}</td>
                                    <td class="letra_pequeña">{{$repuesto->nombre_pais}}</td>
                                    <td class="letra_pequeña" style="text-align: right;">
                                        @if($repuesto->oferta == 1)
                                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex justify-content-beetwen"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-danger">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                                        @elseif($repuesto->oferta == 2)
                                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex justify-content-beetwen"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-primary">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                                        @else
                                        @if(Auth::user()->rol->nombrerol == "Administrador")
                                            <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                                                <a style="color: black;" href="javascript:void(0)" onclick="modifikar_precio({{$repuesto->id}});">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                                            </p>
                                            @else
                                            <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                                                <a style="color: black;" href="javascript:void(0)">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                                            </p>
                                        @endif
                                        @endif
                                    </td>
                                    @if($repuesto->stock_actual>0 || $repuesto->stock_actual_dos > 0 || $repuesto->stock_actual_tres > 0)
                                        <td class="text-right letra_pequeña"><button class="btn btn-primary btn-sm" id="btn_agregar_carrito" style="padding-bottom:0.8px;padding-top:unset;height:20px" onclick="agregar_carrito({{$repuesto->id}},4)">+</button></td>
                                        <td class="letra_pequeña">
                                                <input type="hidden" value="{{$repuesto->stock_actual}}" id="stock-dos-{{$repuesto->id}}">
                                                <input class="form-control form-control-sm letra_pequeña" style="text-align:center;padding:1px;height:20px" type="text" id="cant-dos-{{$repuesto->id}}" onkeyup="enter_cant_dos(event,{{$repuesto->id}})" maxlength="3" placeholder="Stk:{{$repuesto->stock_actual + $repuesto->stock_actual_dos + $repuesto->stock_actual_tres}}">
                                                @if($repuesto->stock_minimo >= intval($repuesto->stock_actual + $repuesto->stock_actual_dos + $repuesto->stock_actual_tres)) <span class="badge badge-danger"><a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="text-decoration-none text-white" data-toggle="modal" data-target="#modalStockMinimo">Stock Mínimo</a> </span> @endif
                                            </td>
                                        <td class="letra_pequeña">
                                                <select name="cboLocal" class="form-control form-control-sm letra_pequeña" id="local-dos-{{$repuesto->id}}" style="padding:1px;height:20px">
                                                    @foreach($stocks_repuesto as $stock_repuesto)
                                                    @if(!empty($stock_repuesto))
                                                            @if($stock_repuesto->id==$repuesto->id)
                                                                <option value="{{$stock_repuesto->id_local}}">{{$stock_repuesto->local_nombre}}({{$stock_repuesto->stock_actual}})</option>
                                                            @endif
                                                    @endif
                                                    @endforeach
                                            </select>
                                        </td>
                                    @else
                                        <td></td>
                                        <td class="letra_pequeña">Sin Stock</td>
                                        <td></td>
                                    @endif
                                    <td class="letra_pequeña"><strong>{{Carbon\Carbon::createFromFormat('Y-m-d', $repuesto->fecha_actualizacion_stock)->format('d-m-Y');}} </strong></td>
                                    @if ($dias <= 30)
                                    <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: green; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
                                    @elseif($dias > 30 && $dias <= 60)
                                        <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: yellow; text-align:center; border-radius: 5px;">{{$dias}}</td>
                                    @else
                                        <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background: red; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
                                    @endif

                                    @if(in_array($repuesto->id,$hay_foto))
                                        <td class="letra_pequeña"><button class="btn btn-success btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal">Detalle</button></td>
                                    @else
                                        <td class="letra_pequeña"><button class="btn btn-success btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal"><abbr title="Repuesto Sin fotos">Detalle</abbr></button></td>
                                    @endif
                                    @if(Auth::user()->rol->nombrerol === "Administrador" || $permiso_modificar == true)
                                        <td class="letra_pequeña"><a href="javascript:void(0);" class="btn btn-warning btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="modifikar_precio({{$repuesto->id}});">M</a></td>
                                        @if(Auth::user()->rol->nombrerol === "Administrador")<td class="letra_pequeña"><a href="javascript:void(0);" class="btn btn-danger btn-sm" style="padding-bottom:0.8px;padding-top:unset;height:30px" onclick="generate_codebar({{$repuesto->id}})">CB</a></td> @endif
                                    @endif
                                </tr>
                            
                            @endforeach
                        </tbody>
                    </table>
                
            </div>
    </div>
</div>
@endif
@else
<div class="row row_cero_margen">
	<div class="col-12 alert alert-info">
		@if($criterio=='nadita')
			<h4><p style="color:#FF0000;text-align:center">Tu petición no cumple los criterios de búsqueda...</p></h4>
			<h5><strong>CRITERIOS:</strong></h5>
			<table border="1" class="table table-sm table-hover">
				<thead>
					<tr>
						<th scope='col' class="d-flex justify-content-center">1 término</th>
						<th scope='col' class="d-flex justify-content-center">2 términos</th>
						<th scope='col' class="d-flex justify-content-center">3 términos</th>
					</tr>
				</thead>
				<tbody>
					<tr><td>Código Interno (amo16)</td><td>familia familia (disco embrague)</td><td>fam fam marcaVeh (bomba agua fiat)</td></tr>
					<tr><td>Código Fabricante (GWM-100A)</td><td>familia marcaVeh (amortiguador hyundai)</td><td>fam fam modeloVeh (bomba freno aveo)</td></tr>
					<tr><td>Código Proveedor (001050095)</td><td>familia modeloVeh (alternador porter)</td><td>fam fam marcaRep (biela motor netmotors)</td></tr>
					<tr><td>Código OEM (28113-A5800)</td><td>familia marcaRep (disco valeo)</td><td>fam marcaVeh marcaVeh (bujia land rover)</td></tr>
					<tr><td>Medidas (140x14)</td><td>marcaVeh marcaVeh (mercedes benz)</td><td>fam marcaVeh modeloVeh (culata toyota dyna)</td></tr>
					<tr><td>Descripción (MANILLA)</td><td>marcaVeh modeloVeh (hyundai accent)</td><td>fam marcaVeh marcaRep (tensor kia valeo)</td></tr>
					<tr><td></td><td>modeloVeh modeloVeh (santa fe)</td><td>fam modeloVeh modeloVeh (rodamiento santa fe)</td></tr>
					<tr><td></td><td>modeloVeh marcaRep (sorento netmotors)</td><td>fam modeloVeh marcaRep (disco canter valeo)</td></tr>
					<tr><td></td><td></td><td>fam marcaRep marcaRep (amortiguador fabricas chinas)</td></tr>
				</tbody>

			</table>
		@else
			<h4><p style="color:#FF0000";>No se encontraron repuestos.</p></h4>
		@endif
    </div>
</div>
@endif
