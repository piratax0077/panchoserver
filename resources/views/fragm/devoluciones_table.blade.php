
<table class="table">
    <thead>
      <tr>
        <th scope="col">Cod Int</th>
        @if(isset($admin)) <th scope="col">Descripción </th> @endif
        <th scope="col">Cantidad</th>
        <th scope="col">Local</th>
        
      </tr>
    </thead>
    <tbody>
        @foreach($devoluciones as $d)
      <tr>
        <td scope="row">{{$d->codigo_interno}}</td>
        @if(isset($admin))<td>{{$d->descripcion}}</td>@endif
        <td>{{$d->cantidad}}</td>
        <td>{{$d->local_nombre}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
