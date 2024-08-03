<div class="col-12">

  <div class="row" id="opciones">
    <div class="btn-toolbar">
      <div class="btn-group btn-group-md" role="group">
        @if(isset($pagos_factura))
        @if($pagos_factura->count() == 0)
        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#formCabFac" onclick="dameFormasPago()">Pagada</button>
        @else
        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#formCabFac" onclick="dameFormasPago()" disabled>Pagada</button>
        @endif
        @endif
        
      </div>
      <div class="btn-group btn-group-md" role="group">
        <button class="btn btn-danger" type="button" onclick="eliminar_factura({{$cabecera['id']}})">Eliminar</button>
      </div>
      <div class="btn-group btn-group-md" role="group">
        <button class="btn btn-secondary" type="button">Opcion 3</button>
        <button class="btn btn-secondary" type="button">Opción 4</button>
      </div>
    </div> <!-- btn-toolbar -->
  </div>

  <div class="row">
    <div class="col-12" style="margin:0px">
        <table  class="table table-borderless table-responsive-sm table-sm">
            <tbody>
                <tr>
                    <td><strong>Fact. Número:</strong>{{$cabecera['factura_numero']}}</td>
                    <td><strong>Fecha: </strong>{{\Carbon\Carbon::parse($cabecera['factura_fecha'])->format("d-m-Y")}}</td>
                    <td><strong>Total:</strong> {{number_format($cabecera['factura_total'],2,',','.')}}</td>
                    <td><strong>Flete:</strong> {{number_format($suma_flete,2,',','.')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Valor Neto:</strong> {{number_format($cabecera['factura_subtotal'],2,',','.')}} </td>
                    <td><strong>IVA:</strong> {{number_format($cabecera['factura_iva'],2,',','.')}}</td>
                    <td id="idpagada">@if($cabecera->pagada == 1) <span class="badge badge-success">Pagada</span> @else <span class="badge badge-danger">No pagada</span> @endif</td>
                    <td></td>
                    <td></td>
                    
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
  <div class="row">
    <div class="col-12 tabla-scroll-y-400">
      <table class="table table-hover table-bordered table-sm">
        <thead>
          <tr>

            <th scope="col" width="10%" class="text-center">Cod. Proveedor</th>
            <th scope="col" width="30%" class="text-center">Descripción</th>
            <th scope="col" width="3%" class="text-center">Cant</th>
            <th scope="col" width="9%" class="text-center"><abbr title="Precio Unitario">P.U.</abbr></th>
            <th scope="col" width="9%" class="text-center"><abbr title="P.U. + utilidad + iva">Costo</abbr></th>
            <th scope="col" width="8%" class="text-center"><abbr title="Flete Unidad: Precio Venta - Costo">F.U.</abbr></th>
            <th scope="col" width="8%" class="text-center"><abbr title="Flete Total: Cant x F.U.">F.T.</abbr></th>
            <th scope="col" width="9%" class="text-center"><abbr title="Cant x P.U.">Subtotal</abbr></th>
            <th scope="col" width="7%" class="text-center">Precio Venta</th>
            <th scope="col" width="3%"></th> <!-- ELIMINAR ITEM -->
            <th scope="col" width="2%"></th>
            <th scope="col" width="2%"></th>  
          </tr>
        </thead>
        <tbody>
          @foreach($items as $item)
                    <tr>
                      <td class="text-center letra-chica">{{$item->cod_repuesto_proveedor}}</td>
                      <td class="letra-chica">{{$item->descripcion}}</td>
                      <td class="text-center letra-chica">{{$item->cantidad}}</td>
                      <td class="text-right letra-chica">{{number_format($item->pu,2,',','.')}}</td>
                      <td class="text-right letra-chica">{{number_format($item->precio_sugerido-$item->flete,2,',','.')}}</td>
                      <td class="text-right letra-chica">{{number_format($item->flete,2,',','.')}}</td>
                      <td class="text-right letra-chica">{{number_format($item->cantidad * $item->flete,2,',','.')}}</td>
                      <td class="text-right letra-chica">{{number_format(($item->cantidad * $item->pu),2,',','.')}}</td>
                      <td class="text-right letra-chica">{{number_format($item->precio_sugerido,2,',','.')}}</td>
                      <td class="text-center" style="cursor: pointer;" onclick="eliminar_repuesto({{$cabecera['factura_numero']}},{{$item->id}})">X</td>
                      <td class="text-center">@if(isset($value)) @if($value == 1 || Auth::user()->rol->nombrerol == "Administrador")<button class="btn btn-warning btn-sm" onclick="editar_item({{$item->id}},{{$cabecera->id}})" data-toggle="modal" data-target="#modalEditarItem">Editar</button>@endif @endif</td>
                      <td class="text-center"><button class="btn btn-primary btn-sm" onclick="moverProducto({{$item->id}})" data-toggle="modal" data-target="#modalMoverProducto">Mover</button></td>
                    </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @if(isset($pagos_factura))
  @if($pagos_factura->count() > 0)
  <div id="pagos_factura">
    <div class="row">
      <div class="col-12 tabla-scroll-y-400">
        <table class="table table-hover table-bordered table-sm">
          <thead>
            <tr>
  
              <th scope="col" width="15%" class="text-center">N° Factura</th>
              <th scope="col" width="20%" class="text-center">Forma de Pago</th>
              <th scope="col" width="15%" class="text-center">Feche de Pago</th>
              <th scope="col" width="20%" class="text-center">Monto</th>
              <th scope="col" width="20%" class="text-center">Referencia</th>
              <th scope="col" width="30%" class="text-center">Usuario</th>
              
            </tr>
          </thead>
          <tbody>
          @foreach($pagos_factura as $item)
            <tr>
              <td class="text-center letra-chica">{{$item->factura_numero}}</td>
              <td class="letra-chica">{{$item->formapago}}</td>
              <td class="text-center letra-chica">{{\Carbon\Carbon::parse($item->fecha_pago)->format("d-m-Y")}}</td>
              <td class="text-right letra-chica">{{number_format($item->monto,2,',','.')}}</td>
              <td class="text-right letra-chica">{{$item->referencia}}</td>
              <td class="text-right letra-chica">{{$item->name}}</td>
              
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
  @endif
  @endif
</div>



<!-- Modal -->
<div class="modal fade" id="formCabFac" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="formasPago">

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="pagada({{$cabecera->id}})">Pagada</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="valor_total_factura" id="valor_total_factura" value="{{$cabecera['factura_total']}}">




