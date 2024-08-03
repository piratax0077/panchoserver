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
            <td style="padding:0px" colspan="2">Clienteee: <b>{{$cliente_rut}}</b> &nbsp;&nbsp;  Rz Sc: <input type="text" id="cliente_razon_social" value="{{$cliente_razon_social}}" style="width:300px"></td>
            <td style="padding:0px">Giro: <input type="text" id="cliente_giro" value="{{$cliente_giro}}"> </td>
        </tr>
        <tr>
            <td style="padding:0px">Dirección: <input type="text" name="" id="cliente_direccion" value="{{$cliente_direccion}}"> </td>
            <td style="padding:0px">Comuna:  <input type="text" name="" id="cliente_comuna" value="{{$cliente_comuna}}"></td>
            <td style="padding:0px">Ciudad:  <input type="text" name="" id="cliente_ciudad" value="{{$cliente_ciudad}}"></td>
        </tr>
        @else
            <tr>
                <input type="hidden" id="fec_documento_h" value="{{$fecha_documento}}">
                <td style="padding:0px">{{$documento}} N° <b>{{$num_documento}}</b> &nbsp;&nbsp; Fecha: <b>{{$fecha_documento}}</b></td>
                <td style="padding:0px"></td>
                <td style="padding:0px"></td>
            </tr>
            <tr>
                <td style="padding:0px">Cliente: <b>{{$cliente_rut}}&nbsp;{{$cliente_razon_social}}</b></td>
                <td style="padding:0px">Giro: {{$cliente_giro}}</td>
                <td style="padding:0px"></td>
            </tr>
            <tr>
                <td style="padding:0px">Dirección: {{$cliente_direccion}}</td>
                <td style="padding:0px"></td>
                <td style="padding:0px"></td>
            </tr>
        @endif
    </tbody>
</table>
<table class="table table-bordered table-sm table-hover">
    <thead>
        <tr>
            <th scope="col" width="10%" style="text-align:center">Cod. Int.</th>
            <th scope="col" width="57%" style="text-align:center">Descripción</th>
            <th scope="col" width="8%" style="text-align:center">p.u.</th>
            <th scope="col" width="7%" style="text-align:center">Cant.</th>
            <th scope="col" width="8%" style="text-align:center">Devuelve</th>
            <th scope="col" width="10%" style="text-align:center">Valor</th>
        </tr>
    </thead>
    <tbody>
    <input type="hidden" id="id_documento_h" value="{{$id_documento}}">
    <input type="hidden" id="id_cliente_h" value="{{$cliente_id}}">
    @php
        $tota=0;
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
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center">{{$item->cantidad}}</td>
                    <td align="center"><input class="nc_item_dev" type="text" value="{{$item->cantidad}}" size="5px" id="item-{{$item->id}}" style="text-align:center" disabled></td>
                <td align="center" id="subtotal-{{$item->id}}">{{$item->cantidad*$item->precio_venta}}</td>
                </tr>
                @php
                    $tota+=$item->cantidad*$item->precio_venta;
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
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center">{{$item->cantidad}}</td>
                    <td align="center"><input class="nc_item_dev" type="text" value="0" size="5px" id="item-{{$item->id}}" style="text-align:center" disabled></td>
                <td align="center" id="subtotal-{{$item->id}}">0</td>
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
                    <td>{{$item->codigo_interno}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td align="right">{{number_format($item->precio_venta,0,',','.')}}</td>
                    <td align="center">{{$item->cantidad}}</td>
                    <td align="center"><input class="nc_item_dev" type="number" min="0" max="{{$item->cantidad}}" size="10px" id="item-{{$item->id}}" value="0" style="text-align:center" onchange="recalcular_valor({{$item->id}},{{$item->precio_venta}});"></td>
                    <td align="center" id="subtotal-{{$item->id}}">0</td>
                </tr>
            @endif

        @endforeach
        <tr>
            <td></td><td></td><td></td><td></td><td style="text-align: right"><b>TOTAL:</b></td><td style="text-align: center"><b><p id="total">@php echo $tota @endphp</p></b></td>
        </tr>
    </tbody>
</table>

@php
    if($mot==1) $motivo="Anular $documento N° ".$num_documento;
    if($mot==2) $motivo="$documento N° ".$num_documento.". Corregir Texto";
    if($mot==3) $motivo="$documento N° ".$num_documento.". Corregir Montos";
@endphp

MOTIVO:<input type="text" id="motivo" size="40px" value=@php echo "\"".$motivo."\""; @endphp >
<button class="btn btn-primary" onclick="generar_xml();">PROCESAR</button>
