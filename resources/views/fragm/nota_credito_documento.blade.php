<table class="table table-sm" style="margin-bottom: 10px;">
    <thead>
        <tr>
            <th scope="col" width="33%" style="padding:0px"></th>
            <th scope="col" width="33%" style="padding:0px"></th>
            <th scope="col" width="34%" style="padding:0px"></th>
        </tr>
    </thead>
    <tbody>
        @if($mot==2)
            <tr>
                <input type="hidden" id="fec_documento_h" value="{{$fecha_documento}}">
                <td style="padding:0px">{{$documento}} N° <b>{{$num_documento}}</b> &nbsp;&nbsp; Fecha: <b>{{$fecha_documento}}</b></td>
                <td style="padding:0px"></td>
                <td style="padding:0px"></td>
            </tr>
            <tr>
                <td style="padding:0px" colspan="2">Cliente: <b>{{$cliente_rut}}</b> &nbsp;&nbsp;  Rz Sc: <input type="text" id="cliente_razon_social" value="{{$cliente_razon_social}}" style="width:300px"></td>
                <td style="padding:0px">Giro: <input type="text" id="cliente_giro" value="{{$cliente_giro}}" style="width:85%"> </td>
            </tr>
            <tr>
                <td style="padding:0px">Dirección: <input type="text" name="" id="cliente_direccion" value="{{$cliente_direccion}}" style="width:75%"> </td>
                <td style="padding:0px">Comuna:  <input type="text" name="" id="cliente_comuna" value="{{$cliente_comuna}}"></td>
                <td style="padding:0px">Ciudad:  <input type="text" name="" id="cliente_ciudad" value="{{$cliente_ciudad}}"></td>
            </tr>
        @else
            <tr>
                <input type="hidden" id="fec_documento_h" value="{{$fecha_documento}}">
                <td style="padding:0px">{{$documento}} N° <b>{{$num_documento}}</b> &nbsp;&nbsp; Fecha: <b>{{$fecha_documento}}</b></td>
                <td style="padding:0px">@if($cliente_id == 4)<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exampleModal">Agregar cliente</button>@endif</td>
                <td style="padding:0px"></td>
            </tr>
            <tr>
                <td style="padding:0px" id="rut_razon_cliente">Cliente: <b>{{$cliente_rut}}&nbsp;{{$cliente_razon_social}}</b></td>
                <td style="padding:0px" id=giro_cliente>Giro: {{$cliente_giro}}</td>
                <td style="padding:0px"></td>
            </tr>
            <tr>
                <td style="padding:0px" id="direccion_cliente">Dirección: {{$cliente_direccion}}</td>
                <td style="padding:0px"></td>
                <td style="padding:0px"></td>
            </tr>
        @endif
    </tbody>
</table>
<table class="table table-bordered table-sm table-hover">
    <thead>
        <tr>
            <th scope="col" width="9%" style="text-align:center">Cod. Int.</th>
            <th scope="col" width="40%" style="text-align:center">Descripción</th>
            <th scope="col" width="8%" style="text-align:center">p.u.</th>
            <th scope="col" width="7%" style="text-align:center">Cant.</th>
            <th scope="col" width="8%" style="text-align:center">Devuelve</th>
            <th scope="col" width="10%" style="text-align:center">SubTotal</th>
            <th scope="col" width="8%" style="text-align:center">Dscto</th>

            <th scope="col" width="10%" style="text-align:center">Total</th>
        </tr>
    </thead>
    <tbody>
    <input type="hidden" id="id_documento_h" value="{{$id_documento}}">
    <input type="hidden" id="id_cliente_h" value="{{$cliente_id}}">
    @php
        $tot=0;
        $dcto=0;
        $subtota=0;
    @endphp
        @foreach ($detalle as $item)
            @if($mot==1) <!-- Anular documento completo -->
                <tr>
                    <input type="hidden" class="nc_item_id" id="{{$item->id}}" value="{{$item->id}}">
                    <input type="hidden" class="nc_item_idrep" value="{{$item->id_repuestos}}">
                    <input type="hidden" class="nc_item_descripcion" value="{{$item->descripcion}}">
                    <input type="hidden" class="nc_item_cantidad" value="{{$item->cantidad}}">
                    <input type="hidden" class="nc_item_precio" id="precio-{{$item->id}}" value="{{$item->precio_venta}}">
                    <input type="hidden" class="nc_item_subtotal" id="nc_item_subtotal_{{$item->id}}" value="0">
                    <input type="hidden" class="nc_item_descuento" id="nc_item_descuento_{{$item->id}}" value="{{$item->descuento}}">
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center" id="cantidad-{{$item->id}}">{{$item->cantidad}}</td>
                    <td align="center"><input class="nc_item_dev" type="text" value="{{$item->cantidad}}" size="5px" id="item-{{$item->id}}" style="text-align:center" disabled></td>
                    <td align="center" id="zubtotal-{{$item->id}}">{{number_format($item->precio_venta*$item->cantidad,0,',','.')}}</td>
                    <td align="center" id="descuento-{{$item->id}}">{{number_format($item->descuento)}}</td>
                    <td align="center" id="total-{{$item->id}}">{{number_format(($item->precio_venta*$item->cantidad)-($item->descuento),0,',','.')}}</td>
                </tr>
                @php
                    $subtota+=$item->precio_venta*$item->cantidad;
                    $dcto+=$item->descuento;
                @endphp
            @endif
            @if($mot==2) <!-- Corregir Textos -->
                <tr>
                    <input type="hidden" class="nc_item_id" id="{{$item->id}}" value="{{$item->id}}">
                    <input type="hidden" class="nc_item_idrep" value="{{$item->id_repuestos}}">
                    <input type="hidden" class="nc_item_descripcion" value="{{$item->descripcion}}">
                    <input type="hidden" class="nc_item_cantidad" value="{{$item->cantidad}}">
                    <input type="hidden" class="nc_item_precio" id="precio-{{$item->id}}" value="{{$item->precio_venta}}">
                    <input type="hidden" class="nc_item_subtotal" id="nc_item_subtotal_{{$item->id}}" value="0">
                    <input type="hidden" class="nc_item_descuento" id="nc_item_descuento_{{$item->id}}" value="{{$item->descuento}}">
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center">{{$item->cantidad}}</td>
                    <td align="center"><input class="nc_item_dev" type="text" value="0" size="5px" id="item-{{$item->id}}" style="text-align:center" disabled></td>
                <td align="center" id="zubtotal-{{$item->id}}">0</td>
                </tr>
            @endif
            @if($mot==3) <!-- Corregir Montos -->
                <tr>
                    <input type="hidden" class="nc_item_id" id="{{$item->id}}" value="{{$item->id}}">
                    <input type="hidden" class="nc_item_idrep" value="{{$item->id_repuestos}}">
                    <input type="hidden" class="nc_item_descripcion" value="{{$item->descripcion}}">
                    <input type="hidden" class="nc_item_cantidad" value="{{$item->cantidad}}">
                    <input type="hidden" class="nc_item_precio" id="precio-{{$item->id}}" value="{{$item->precio_venta}}">
                    <input type="hidden" class="nc_item_subtotal" id="nc_item_subtotal_{{$item->id}}" value="0">
                    <input type="hidden" class="nc_item_descuento" id="nc_item_descuento_{{$item->id}}" value="{{$item->descuento}}">
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center" ><p id="kaka-{{$item->id}}">{{$item->cantidad}}</p></td>
                    <td align="center"><input class="nc_item_dev" type="number" min="0" max="{{$item->cantidad}}" size="10px" id="item-{{$item->id}}" value="0" style="text-align:center" onchange="recalcular_valor({{$item->id}},{{$item->precio_venta}},{{$item->cantidad}})"></td>
                    <td align="center" id="zubtotal-{{$item->id}}">0</td>
                    <td align="center" id="descuento-{{$item->id}}">0</td>
                    <td align="center" id="total-{{$item->id}}">0</td>
                </tr>
            @endif

        @endforeach
        <tr>
            <td></td><td></td><td></td><td></td>
            <td style="text-align: right"><b>TOTALES:</b></td>
            <td style="text-align: center"><b><p id="netito">@php echo number_format($subtota,0,',','.') @endphp</p></b></td>
            <td style="text-align: center"><b><p id="dsctito">@php echo number_format($dcto,0,',','.') @endphp</p></b></td>
            <td style="text-align: center"><b><p id="totalzito">@php $tot=$subtota-$dcto;echo number_format($tot,0,',','.') @endphp</p></b></td>
        </tr>
    </tbody>
</table>

@php
    if($mot==1) $motivo="Anular $documento N° ".$num_documento;
    if($mot==2) $motivo="$documento N° ".$num_documento.". Corregir Texto";
    if($mot==3) $motivo="$documento N° ".$num_documento.". Corregir Montos";
    $pago_motivo="";
@endphp

<div class="row ml-1">
    <strong>PAGOS:</strong><br>

        @if($pago_buscado_doc->count()>0)
            @foreach($pago_buscado_doc as $pago)
            @php
                $pago_motivo.=$pago->formapago." ";
                if($pago->referencia=='EFECTIVO'){
                    $pago_motivo.=" -> ";
                }else{
                    $pago_motivo.=$pago->referencia." ";
                }
                $pago_motivo.=$pago->monto."; ";
            @endphp
            {{$pago->formapago}} @if($pago->referencia=='EFECTIVO') -> @else {{$pago->referencia}} @endif {{$pago->monto}}<br>
            @endforeach
        @else
            No hay pagos. Es crédito.
        @endif

</div>
<hr>
<div class="row ml-1">
    MOTIVO:<input type="text" id="motivo" size="40px" value=@php echo "\"".$motivo."\""; @endphp >
    <button class="btn btn-primary" onclick="generar_xml();">PROCESAR</button>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Cliente xpress</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="">Rut <span class="text-danger font-italic">(Sin puntos ni guion)</span></label>
            <input type="text" name="" id="rut_xpress" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Nombre</label>
            <input type="text" name="" id="nombre_xpress" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Apellidos</label>
            <input type="text" name="" id="apellido_xpress" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Dirección</label>
            <input type="text" name="" id="direccion_xpress" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Email</label>
            <input type="email" name="" id="email_xpress" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Telefono</label>
            <input type="text" name="" id="telefono_xpress" class="form-control" value="+569">
          </div>
          <div class="form-group">
            <label for="reembolso_referencia" class="form-label">Referencia de reembolso</label>
            <select name="reembolso_referencia" id="reembolso_referencia" class="form-control">
                <option value="1">Efectivo</option>
                <option value="2">Transferencia Bancaria</option>
            </select>
        </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="agregar_cliente_boleta()">Agregar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Dato de vital importancia -->
  <input type="hidden" name="nc_boleta_usuario" id="nc_boleta_usuario">