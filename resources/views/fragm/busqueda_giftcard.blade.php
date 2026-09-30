@if(isset($giftcard))
<div id="giftcard_detalle" >
  <h3 class="text-center">Información Giftcard - {{$giftcard->numero_boucher}}</h3>
  <hr>
    <div class="row" style="padding-top: 5px; padding-bottom: 5px; border-radius: 10px; background: #f2f4a9; border: 1px solid gray;">
        <div class="col-md-5">
            <table class="table">
                
                <tbody>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Número</td>
                    <td class="py-3">{{$giftcard->numero_boucher}}</td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Usuario</td>
                    <td class="py-3">{{$giftcard->name}}</td>
                  </tr>
                  <tr> 
                    <td class="bg-dark text-white text-center py-3">Cliente</td> 
                    <td class="py-3">{{$giftcard->nombre_cliente}}</td>
                  </tr>
                  <tr> 
                    <td class="bg-dark text-white text-center py-3">Total</td> 
                    <td class="py-3">$ {{number_format($giftcard->valor,0,',','.')}}</td>
                  </tr>
                </tbody>
              </table>
              @if($giftcard->valor > 0)
              <button class="btn btn-success btn-sm" onclick="agregar_giftcard_al_carrito()">Agregar al carrito</button>
              @endif
        </div>
        <div class="col-md-7">
            <div>
                @if(count($detalles) > 0)
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cód Int</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Origen</th>
                        <th scope="col">Precio Venta</th>
                        <th scope="col">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                        @php
                          $total = 0;
                        @endphp
                        @foreach($detalles as $d)
                        @php
                          $total = $total + $d->precio_venta * $d->cantidad;
                          
                        @endphp
                      <tr>
                        <td>{{$d->codigo_interno}}</td>
                        <td>{{$d->cantidad}}</td>
                        <td>{{$d->local_nombre}}</td>
                        <td>$ {{number_format($d->precio_venta,0,',','.')}}</td>
                        <td>$ {{number_format($d->precio_venta * $d->cantidad,0,',','.')}}</td>
                      </tr>
                      @endforeach
                      
                      
                    </tbody>
                    
                  </table>
                  @php
                    $total_giftcard = number_format($giftcard->valor,0,',','.');
                    $total_ = number_format($total,0,',','.');
                  @endphp
                  <table class="table">
                    <tbody>
                      <tr>
                        <td>Total Giftcard</td>
                        <td>$ {{$total_giftcard}}</td>
                      </tr>
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
