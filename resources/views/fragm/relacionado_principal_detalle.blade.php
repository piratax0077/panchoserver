@if($repuesto->count()>0)
<div class="row letra-chica">

  <div class="col-sm-1"><b>Cod. Int:</b><br>{{$repuesto[0]->codigo_interno}}</div>
  <div class="col-sm-2"><b>Fam:</b><br>{{$repuesto[0]->nombrefamilia}}</div>
  <div class="col-sm-2"><b>Marca:</b><br>{{$repuesto[0]->marcanombre}}</div>
  <div class="col-sm-2"><b>Modelo:</b><br>{{$repuesto[0]->modelonombre}}</div>
  <div class="col-sm-5"><b>Descrip:</b><br>{{$repuesto[0]->descripcion}}</div>
</div>
<div class="row letra-chica">
  <div class="col-sm-2"><b>Cod Rep:</b><br>{{$repuesto[0]->cod_repuesto_proveedor}}</div>
  <div class="col-sm-2"><b>Marca Rep:</b><br>{{$repuesto[0]->marcarepuesto}}</div>
  <div class="col-sm-2"><b>Med:</b><br>{{$repuesto[0]->medidas}}</div>
  <div class="col-sm-2"><b>Año:</b><br>{{$repuesto[0]->anios_vehiculo}}</div>
  <div class="col-sm-2"></div>
</div>

@else
<div class="row">
  <div class="col-sm-12">
    <label><strong>Elija un repuesto principal para asignarle los relacionados</strong></label>
  </div>
</div>
@endif
