<table class="table">
    <thead>
      <tr>
        <th scope="col">Numero Boucher</th>
        <th scope="col">Código Interno</th>
        <th scope="col">Precio Venta</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
        @foreach($detalles as $d)
      <tr>
        <th scope="row">{{$d->vale_mercaderia_id}}</th>
        <td>{{$d->codigo_interno}}</td>
        <td>$ {{$d->precio_venta}}</td>
        <td><button class="btn btn-danger btn-sm">Eliminar</button></td>
      </tr>
      @endforeach
    </tbody>
    
  </table>

  <p>Total: $ {{$detalles->sum('precio_venta')}}</p>
  <p>Total VM: $ {{$vale_mercaderia->valor}}</p>
  <p>Diferencia: {{$vale_mercaderia->valor - $detalles->sum('precio_venta')}}</p>