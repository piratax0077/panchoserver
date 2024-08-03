<center><strong>DATOS</strong></center>

@if($repuesto->count()>0)
@php
           //$porciones =explode(',',$repuesto->medidas);
           // Usando explode con múltiples delimitadores
            $porciones = preg_split('/[:,]/', $repuesto[0]->medidas);
@endphp
<div class="col-md-12">
  <label><strong>Descripción:</strong></label>
  {{$repuesto[0]->descripcion}}
</div><br>
<!--Mandamos el título del modal detalle, lo leemos con ajax y lo ponemos -->

<input type="hidden" id="titulo_detalle" value="
<center><strong>DETALLE DEL REPUESTO</strong></center>
<strong>Código:</strong>&nbsp;{{$repuesto[0]->codigo_interno}}&nbsp;&nbsp;&nbsp;&nbsp;
<strong>Familia:</strong>&nbsp;{{$repuesto[0]->nombrefamilia}}&nbsp;&nbsp;&nbsp;&nbsp;
">
<table class="table table-hover table-sm">
  <thead>
    {{-- <tr>
      <th scope="col" width="50%"></th><th width="5%" scope="col"></th><th scope="col" width="45%"></th>
    </tr> --}}
  </thead>
  <tbody>
    <tr><th align="left">Código Interno: </th><td></td><td>{{$repuesto[0]->codigo_interno}}</td></tr>
      <tr><th align="left">Medidas:</th><td></td> 
        <td>
          @foreach($porciones as $p)
            @if($p == 'DISCO' || $p == 'PRENSA')
                <span style="font-weight: bold">{{$p}}</span><br>
            @else
                <span>{{$p}}</span> <br>
            @endif
          @endforeach
        </td>
      </tr>
    <tr><th align="left">Cod. Repuesto Proveedor:</td><td></td><td>{{$repuesto[0]->cod_repuesto_proveedor}}</td></tr>
      <tr><th align="left">Marca Repuesto:</td><td></td><td>{{$repuesto[0]->marcarepuesto->marcarepuesto}}</td></tr>
      <tr><th align="left">Proveedor:</td><td></td><td>{{$repuesto[0]->empresa_nombre_corto}}</td></tr>
        <tr><th align="left">País Origen:</td><td></td><td>{{$repuesto[0]->nombre_pais}}</td></tr>
    <tr><th align="left">Precio Compra:</td><td></td><td>$ {{str_replace(",",".",number_format($repuesto[0]->precio_compra))}}</td></tr>
    <tr><th align="left">Precio Venta:</td><td></td><td>$ {{str_replace(",",".",number_format($repuesto[0]->precio_venta))}}</td></tr>
    <tr><th align="left">Stock Mínimo:</td><td></td><td>{{$repuesto[0]->stock_minimo}}</td></tr>
    <tr><th align="left">Stock Máximo:</td><td></td><td>{{$repuesto[0]->stock_maximo}}</td></tr>
    <tr><th align="left">Stock Actual:</td><td></td><td>{{$repuesto[0]->stock_actual + $repuesto[0]->stock_actual_dos}}</td></tr>
      <tr><th align="left">Estado:</th><td> </td><td><select name="estado" id="estado" class="form-control form-control-sm form-select">
        @foreach($estados as $est)
        <option value="{{$est}}" @if($est == $repuesto[0]->estado) selected  @endif>{{$est}}</option>
        @endforeach
      </select></td></tr>

  </tbody>
</table>

@else
<div class="col-md-12">
  <label><strong>Sin datos</strong></label>
</div>
@endif






