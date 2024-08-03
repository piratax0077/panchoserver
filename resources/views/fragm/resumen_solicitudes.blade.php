<table class="table">
    <thead>
      <tr>
        <th scope="col">Codigo interno</th>
        <th scope="col">N° solicitud</th>
        <th scope="col">Destinos</th>
        <th scope="col">Repuesto</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Estado</th>
        <th scope="col">Fecha emisión</th>
        <th scope="col">Enviado por</th>
      </tr>
    </thead>
    <tbody>
      @if($resumen->count() > 0)
        @foreach($resumen as $d)
        @php
          if($d->estado == 0){
            $class = 'alert-danger';
          }elseif($d->estado == 1){
            $class = 'alert-success';
          }else{
            $class = 'alert-warning';
          }

        @endphp
        <tr class="{{$class}}">
            <td class="letra_pequeña">{{$d->codigo_interno}}</td>
            <td class="letra_pequeña">{{$d->num_solicitud}}</td>
            @if($d->locaciones == 1)
            <td class="letra_pequeña">Bodega a Tienda</td>
            @elseif($d->locaciones == 2)
            <td class="letra_pequeña">Bodega a Casa Matríz</td>
            @else
            <td class="letra_pequeña">Casa Matríz a Tienda</td>
            @endif
            
            <td class="letra_pequeña">{{$d->descripcion}}</td>
            <td class="letra_pequeña">{{$d->cantidad}}</td>
            @if($d->estado == 0)
                <td class="letra_pequeña">Rechazado</td>
            @elseif($d->estado == 1)
                <td class="letra_pequeña">Aceptado</td>
            @else
                <td class="letra_pequeña">Esperando</td>
            @endif
            <td class="letra_pequeña">{{\Carbon\Carbon::parse($d->created_at)->format("d-m-Y")}}</td>
                <td>{{$d->name}}</td>
        </tr>
    @endforeach
    @else
    <tr class="alert-danger">
      <td>No hay solicitudes</td>
    </tr>
        
    @endif
    </tbody>
  </table>