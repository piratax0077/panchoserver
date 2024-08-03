@if(isset($nc))
<p>Nota de crédito N° {{$nc->num_nota_credito}} emitida por {{$nc->name}}</p>

<table class="table">
    <thead>
      <tr>
        <th scope="col">Código Interno</th>
        <th scope="col">Descripción</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Destino</th>
        <th scope="col">Fecha emisión</th>
        <th scope="col">Usuario</th>
      </tr>
    </thead>
    <tbody>
      @foreach($devoluciones_realizadas as $d)
      <tr>
        <th scope="row">{{$d->codigo_interno}}</th>
        <td>{{$d->descripcion}}</td>
        <td>{{$d->cantidad}}</td>
        <td>{{$d->local_nombre}}</td>
        <td>{{$d->fecha_actualizacion}}</td>
        <td>{{$d->name}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <p class="alert-danger">No existen devoluciones para esa nota de crédito</p>
  @endif