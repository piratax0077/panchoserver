@php
    $suma = 0;
@endphp
<table class="table">
    <thead>
      <tr>
        <th scope="col">Numero Boucher</th>
        <th scope="col">Código Interno</th>
        <th scope="col">Origen</th>  
        <th scope="col">Precio Venta</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Total</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($detalles->count() > 0)
        @foreach($detalles as $d)
        @php
            $total = $d->precio_venta * $d->cantidad;
            $suma+= $total;
        @endphp
      <tr>
        <td>{{$d->num_consignacion}}</td>
        <td>{{$d->codigo_interno}}</td>
        <td>{{$d->local_nombre}}</td>
        <td>$ {{number_format($d->precio_venta)}}</td>
        <td>{{$d->cantidad}}</td>
        <td>$ {{number_format($total)}}</td>
        <td>
         
          @if(!$cargar) 
          <button class="btn btn-danger btn-sm" onclick="eliminar_item_consignacion({{$d->id}},{{$d->num_consignacion}})">Eliminar</button> 
          @else
          <button class="btn btn-success btn-sm" onclick="devolver_item_consignacion({{$d->id}},{{$d->num_consignacion}})">Devolver</button> 
          @endif
      
        </td>
      </tr>
      @endforeach
      
      <tr class="bg-light">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong>Total</strong> </td>
        <td><strong>$ {{number_format($suma)}}</strong> </td>
        <td></td>
      </tr>
      @else

      <tr>
        <td>Sus repuestos fueron devueltos.</td>
      </tr>
      @endif
    </tbody>
    
  </table>

  @if(!$cargar)
  <button class="btn btn-danger btn-sm" onclick="cerrar_consignacion()">Cerrar consignacion</button>
  
  @endif