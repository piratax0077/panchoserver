@if($conexiones->count() > 0)
@if($conexiones->count() == 1)
<span class="badge badge-warning mb-3" style="font-size: 16px;">Se ha registrado {{$conexiones->count()}} conexión con fecha {{$conexiones[0]->fecha_ingreso}}.</span>
@else
<span class="badge badge-warning mb-3" style="font-size: 16px;">Se han registrado {{$conexiones->count()}} conexiones con fecha {{$conexiones[0]->fecha_ingreso}}.</span>
@endif
@endif
<table class="table">
    <thead>
      <tr>
        <th scope="col">Nombre</th>
        <th scope="col">Dirección IP</th>
        <th scope="col">Hora</th>
      </tr>
    </thead>
    <tbody>
       @if($conexiones->count() > 0)
        @foreach($conexiones as $c)
        <tr>
            <td>{{$c->user}}</td>
            <td>{{$c->direccion_ip}}</td>
            <td>{{date('H:i',strtotime($c->fecha_login))}}</td>
        </tr>
        @endforeach
        @else
        <tr>
          <td>Sin conexiones.</td>
        </tr>
        @endif
    </tbody>
  </table>