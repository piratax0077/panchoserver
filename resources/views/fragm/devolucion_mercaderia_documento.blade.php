<table class="table table-sm" style="margin-bottom: 10px;">
    <thead>
        <tr>
            <th scope="col" width="33%" style="padding:0px"></th>
            <th scope="col" width="33%" style="padding:0px"></th>
            <th scope="col" width="34%" style="padding:0px"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <input type="hidden" id="fec_documento_h" value="{{$fecha_documento}}">
            <td style="padding:0px">{{$documento}} N° <b>{{$num_documento}}</b> &nbsp;&nbsp; Fecha: <b>{{$fecha_documento}}</b></td>
            <td style="padding:0px"></td>
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
        <tr>
            <td style="padding: 0px">N° Nota Crédito: <b>{{$nc->num_nota_credito}}</b></td>
            <td style="padding: 0px">Fecha emisión: <b>{{$nc->fecha_emision}}</b></td>
            <td style="padding: 0px">Motivo: <b>{{$nc->motivo_correccion}}</b></td>
        </tr>
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
            <th scope="col" width="18%" style="text-align:center">Locación</th>
            <th scope="col" width="10%" style="text-align: center"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($detalle as $item)
            
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
                    <td align="center"><input class="nc_item_dev" type="text" value="{{$item->cantidad}}" size="5px" id="item-{{$item->id}}" style="text-align:center"></td>
                    <td align="center" id="zubtotal-{{$item->id}}">
                        <select name="" id="ubicacion-{{$item->id_repuestos}}" >
                            @foreach($locales as $local)
                                <option value="{{$local->id}}">{{$local->local_nombre}} ({{$local->local_direccion}})</option>
                            @endforeach
                        </select>
                    </td>
                    <td><button class="btn btn-primary btn-sm" onclick="devolver_repuesto({{$item->id_repuestos}},{{$item->cantidad}})">Aplicar</button></td>
                </tr>
             

        @endforeach
    </tbody>
</table>