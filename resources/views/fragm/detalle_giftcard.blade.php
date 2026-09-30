@if(isset($giftcard))
<div id="giftcard_detalle" >
  <h3 class="text-center">Detalle Giftcard - {{$giftcard->num_abono}}</h3>
  <hr>
    <div class="row" style="padding-top: 5px; padding-bottom: 5px; border-radius: 10px; background: #f2f4a9; border: 1px solid gray;">
        <div class="col-md-5">
            <table class="table">
                <tbody>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Número</td>
                    <td class="py-3">{{$giftcard->num_abono}}</td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Cliente</td>
                    <td class="py-3">{{$giftcard->nombre_cliente}}</td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Monto Total</td>
                    <td class="py-3">$ {{number_format($giftcard->abono,0,',','.')}}</td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Saldo Disponible</td>
                    <td class="py-3"><strong>$ {{number_format($giftcard->saldo_pendiente,0,',','.')}}</strong></td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Teléfono</td>
                    <td class="py-3">{{$giftcard->telefono}}</td>
                  </tr>
                </tbody>
              </table>
        </div>
        <div class="col-md-7">
            <div>
                @if($detalles->count() > 0)
                <table class="table table-sm">
                    <thead>
                      <tr style="background: #e8e8e8;">
                        <th scope="col">Código</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Precio Unitario</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $d)
                        <tr>
                            <td>{{$d->codigo_interno}}</td>
                            <td>{{$d->descripcion ?? 'Sin descripción'}}</td>
                            <td>{{$d->cantidad}}</td>
                            <td>$ {{number_format($d->precio_unitario,0,',','.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                  </table>
                @else
                <div class="alert alert-info">No hay detalles en este giftcard</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
