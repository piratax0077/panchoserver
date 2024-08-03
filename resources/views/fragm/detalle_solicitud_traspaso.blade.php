<table class="table">
    <thead>
      <tr>
        <th scope="col">Codigo interno</th>
        <th scope="col">Destinos</th>
        <th scope="col">Repuesto</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Enviado por</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
        @foreach($detalle as $d)
        <tr>
            <td>{{$d->codigo_interno}}</td>
            @if($d->locaciones == 1)
            <td>Bodega a Tienda</td>
            @elseif($d->locaciones == 2)
            <td>Bodega a Casa Matríz</td>
            @else
            <td>Casa Matríz a Tienda</td>
            @endif
            <td>{{$d->descripcion}}</td>
            <td>{{$d->cantidad}}</td>
            <td>{{$solicitud->name}}</td>
            <td><button class="btn btn-sm btn-primary" onclick="aceptar_traspaso_repuesto({{$d->id}},{{$solicitud_id}},{{$d->cantidad}},{{$d->locaciones}})">Aceptar</button>
                <button class="btn btn-sm btn-danger" onclick="rechazar_traspaso_repuesto({{$d->id}},{{$solicitud_id}})">Rechazar</button></td>
        </tr>
    @endforeach
    </tbody>
  </table>

