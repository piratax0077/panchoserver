@if($repuesto->count()>0)
@php
  $stock_total = intval($repuesto[0]->stock_actual) + intval($repuesto[0]->stock_actual_dos) + intval($repuesto[0]->stock_actual_tres);
@endphp
<strong>Repuesto Principal</strong>
<div class="row letra-chica">

  <div class="col-sm-2"><b>Cod. Int:</b><br>{{$repuesto[0]->codigo_interno}}</div>
  <div class="col-sm-2"><b>Fam:</b><br>{{$repuesto[0]->nombrefamilia}}</div>
  <div class="col-sm-2"><b>Stock:</b><br>{{$stock_total}}</div>
  <div class="col-sm-6"><b>Descrip:</b><br>{{$repuesto[0]->descripcion}}</div>
</div>
<div class="row letra-chica">
  <div class="col-sm-2"><b>Cod Rep:</b><br>{{$repuesto[0]->cod_repuesto_proveedor}}</div>
  <div class="col-sm-2"><b>Marca Rep:</b><br>{{$repuesto[0]->marcarepuesto}}</div>
  <div class="col-sm-2"><b>Med:</b><br>{{$repuesto[0]->medidas}}</div>
  <div class="col-sm-2"><b>Año:</b><br>{{$repuesto[0]->anios_vehiculo}}</div>
  <div class="col-sm-2"></div>
</div>
<hr>
@else
<div class="row">
  <div class="col-sm-12">
    <label><strong>Elija un repuesto principal para asignarle los relacionados</strong></label>
  </div>
</div>
@endif
