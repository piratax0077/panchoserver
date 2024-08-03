@if($cotizacion)
<p>Numero de cotizacion {{$cotizacion->num_cotizacion}}</p>
<ul>
    @foreach($detalle as $d)
    <li>{{$d->descripcion}}</li>
    @endforeach
</ul>
@endif