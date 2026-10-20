@if($repuesto->count()>0)
@php
           //$porciones =explode(',',$repuesto->medidas);
           // Usando explode con múltiples delimitadores
            $porciones = preg_split('/[:,]/', $repuesto[0]->medidas);
            $stock_bodega = $repuesto[0]->stock_actual ?? 0;
            $stock_tienda = $repuesto[0]->stock_actual_dos ?? 0;
            $stock_matriz = $repuesto[0]->stock_actual_tres ?? 0;
            $stock_total = $stock_bodega + $stock_tienda + $stock_matriz;
@endphp

<!--Mandamos el título del modal detalle, lo leemos con ajax y lo ponemos -->
<input type="hidden" id="titulo_detalle" value="
<strong>DETALLE DEL REPUESTO</strong>&nbsp;&nbsp;·&nbsp;&nbsp;
<strong>Código:</strong>&nbsp;{{$repuesto[0]->codigo_interno}}&nbsp;&nbsp;&nbsp;&nbsp;
<strong>Familia:</strong>&nbsp;{{$repuesto[0]->nombrefamilia}}
">

<div class="col-12">

  <!-- Descripción destacada (siempre ocupa todo el ancho) -->
  <div style="background: #eceff1; border-left: 4px solid #607d8b; border-radius: 6px; padding: 8px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #607d8b; white-space: nowrap;">Descripción:</span>
    <span style="font-size: 15px; color: #263238; font-weight: 600;">{{$repuesto[0]->descripcion}}</span>
  </div>

  <div class="row">
    <!-- Columna: Identificación -->
    <div class="col-md-6 mb-3">
      <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px; border-bottom: 2px solid #cfd8dc; padding-bottom: 6px;">
        <i class="fa fa-tag"></i> Identificación
      </div>
      <table class="table table-sm mb-0">
        <tbody>
          <tr><th align="left">Código Interno</th><td>{{$repuesto[0]->codigo_interno}}</td></tr>
          <tr><th align="left">Cod. Proveedor</th><td>{{$repuesto[0]->cod_repuesto_proveedor}}</td></tr>
          <tr><th align="left">Marca Repuesto</th><td>{{$repuesto[0]->marcarepuesto->marcarepuesto}}</td></tr>
          <tr><th align="left">Proveedor</th><td>{{$repuesto[0]->empresa_nombre_corto}}</td></tr>
          <tr><th align="left">País Origen</th><td>{{$repuesto[0]->nombre_pais}}</td></tr>
          <tr>
            <th align="left" style="vertical-align: top; padding-top: 10px;">Medidas</th>
            <td>
              @foreach($porciones as $p)
                @if($p == 'DISCO' || $p == 'PRENSA')
                    <span style="font-weight: 700;">{{$p}}</span><br>
                @else
                    <span>{{$p}}</span><br>
                @endif
              @endforeach
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Columna: Precios, Stock y Estado -->
    <div class="col-md-6 mb-3">
      <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px; border-bottom: 2px solid #cfd8dc; padding-bottom: 6px;">
        <i class="fa fa-dollar-sign"></i> Precios
      </div>
      <table class="table table-sm mb-3">
        <tbody>
          <tr><th align="left">Precio Compra</th><td>$ {{str_replace(",",".",number_format($repuesto[0]->precio_compra))}}</td></tr>
          <tr><th align="left">Precio Venta</th><td style="color: #2e7d32; font-weight: 700;">$ {{str_replace(",",".",number_format($repuesto[0]->precio_venta))}}</td></tr>
        </tbody>
      </table>

      <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px; border-bottom: 2px solid #cfd8dc; padding-bottom: 6px;">
        <i class="fa fa-boxes"></i> Stock
      </div>
      <table class="table table-sm mb-3">
        <tbody>
          <tr><th align="left">Stock Mínimo</th><td>{{$repuesto[0]->stock_minimo}}</td></tr>
          <tr><th align="left">Stock Máximo</th><td>{{$repuesto[0]->stock_maximo}}</td></tr>
          <tr><th align="left"><i class="fa fa-warehouse" style="color:#90a4ae;"></i>&nbsp; Bodega</th><td>{{$stock_bodega}}</td></tr>
          <tr><th align="left"><i class="fa fa-store" style="color:#90a4ae;"></i>&nbsp; Tienda</th><td>{{$stock_tienda}}</td></tr>
          <tr><th align="left"><i class="fa fa-building" style="color:#90a4ae;"></i>&nbsp; Casa Matriz</th><td>{{$stock_matriz}}</td></tr>
          <tr><th align="left">Total</th><td style="font-weight: 700; color: #263238;">{{$stock_total}}</td></tr>
        </tbody>
      </table>

      <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px; border-bottom: 2px solid #cfd8dc; padding-bottom: 6px;">
        <i class="fa fa-info-circle"></i> Estado
      </div>
      <select name="estado" id="estado" class="form-control form-control-sm" style="border-radius: 6px;">
        @foreach($estados as $est)
        <option value="{{$est}}" @if($est == $repuesto[0]->estado) selected  @endif>{{$est}}</option>
        @endforeach
      </select>
    </div>
  </div>

</div>

@else
<div class="col-md-12">
  <label><strong>Sin datos</strong></label>
</div>
@endif






