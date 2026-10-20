@if($repuesto->count()>0)
@php
  $stock_total = intval($repuesto[0]->stock_actual) + intval($repuesto[0]->stock_actual_dos) + intval($repuesto[0]->stock_actual_tres);
@endphp
<div style="background: #ffffff; border: 1px solid #cfd8dc; border-radius: 8px; padding: 14px 18px;">
  <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 8px; border-bottom: 2px solid #cfd8dc; padding-bottom: 6px;">
    <i class="fa fa-box"></i> Repuesto Principal
  </div>
  <div style="font-size: 14px; color: #263238; font-weight: 600; margin-bottom: 12px;">{{$repuesto[0]->descripcion}}</div>
  <div style="display: flex; flex-wrap: wrap; gap: 14px 32px;">
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Código Interno</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->codigo_interno}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Familia</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->nombrefamilia}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Stock Total</div>
      <div style="font-size: 13px; color: #2e7d32; font-weight: 700;">{{$stock_total}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Cód. Proveedor</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->cod_repuesto_proveedor}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Marca Repuesto</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->marcarepuesto}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Medidas</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->medidas}}</div>
    </div>
    <div>
      <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #90a4ae;">Año</div>
      <div style="font-size: 13px; color: #263238; font-weight: 600;">{{$repuesto[0]->anios_vehiculo}}</div>
    </div>
  </div>
</div>
@else
<div style="padding: 20px; text-align: center;">
  <label style="color: #78909c;"><strong>Elija un repuesto principal para asignarle los relacionados</strong></label>
</div>
@endif
