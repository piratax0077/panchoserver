@if($dato->region) <h2> Despacho a domicilio</h2> @else <h2>Retiro en tienda</h2> @endif
<table class="table">
    <thead>
        <tr>
            <th scope="col">Id</th>
            <th scope="col">Región</th>
            <th scope="col">Cómuna</th>
            <th scope="col">Dirección</th>
            <th scope="col">Telefono</th>
            <th scope="col">Persona que recibe</th>
            <th scope="col">Fecha de pedido</th>
           <th scope="col">Estado actual</th> 
           <th scope="col">Estado</th> 
           <th scope="col"></th> 
        </tr>
    </thead>
    <tbody>
        @if($dato->region)
        <tr>
            <td>{{$dato->id}}</td>
            <td>{{$dato->region}}</td>
            <td>{{$dato->comuna}}</td>
            <td>{{$dato->direccion_despacho}}</td>
            <td>{{$dato->telefono_despacho}}</td>
            <td>{{$dato->persona}}</td>
            <td>{{$carrito->created_at}}</td>
            @if($dato->estado == 0) <td id="estado_envio">En espera</td> @elseif($dato->estado == 1) <td id="estado_envio">Enviado</td> @else <td id="estado_envio">Entregado</td> @endif
            <td>
                <select name="estado_envio_select" id="estado_envio_select" class="form-control">
                    <option value="0">En espera</option>
                    <option value="1">Enviado</option>
                    <option value="2">Entregado</option>
                </select>
            </td>
            <td><button class="btn btn-success btn-sm" onclick="confirmar_envio({{$carrito->numero_carrito}},1)">Confirmar</button></td>
        </tr>
        @else
        <tr>
            <td>{{$dato->id}}</td>
            <td>---</td>
            <td>---</td>
            <td>---</td>
            <td>---</td>  
            <td>{{$dato->nombre}}</td>
            <td>{{$carrito->created_at}}</td>
            @if($dato->estado == 0) <td id="estado_retiro">En espera</td> @else <td id="estado_retiro">Entregado</td> @endif
            <td>
                <select name="estado_envio_select_retiro" id="estado_envio_select_retiro" class="form-control">
                    <option value="0">En espera</option>
                    <option value="1">Entregado</option>
                </select>
            </td>
            <td><button class="btn btn-success btn-sm" onclick="confirmar_envio({{$carrito->numero_carrito}},2)">Confirmar</button></td>
        </tr>
        @endif
    </tbody>
</table>
<table class="table">
    <thead>
      <tr>
        <th scope="col">Código interno</th>
        <th scope="col">Descripción</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Precio venta</th>
        <th scope="col">Total</th>
        <th scope="col">Estado</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
        @foreach($detalle as $d)
        <tr>
            <td>{{$d->codigo_interno}}</td>
            <td>{{$d->descripcion}}</td>
            <td>{{$d->cantidad}}</td>
            <td>{{number_format($d->precio_venta,0,',','.')}}</td>
            <td>{{number_format($d->precio_venta * $d->cantidad,0,',','.')}}</td>
            @if($d->estado == 0) <td>Sin descontar</td> @else <td>Descontado</td> @endif
            
            <td><button class="btn btn-success btn-sm" onclick="descontar_stock_bodega('{{$d->codigo_interno}}',{{$d->cantidad}},{{$carrito->numero_carrito}})">Descontar stock</button></td>
          </tr>
        @endforeach
      
    </tbody>
  </table>
