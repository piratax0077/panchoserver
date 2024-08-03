@if($cotizacion)
<table class="table my-1">
  <thead>

  </thead>
  <tbody>
    <tr>
      <td style="background: #eee">N° Cotización</td>
      <td>{{$cotizacion->num_cotizacion}}</td>
    </tr>
    <tr>
      <td style="background: #eee">Nombre</td>
      <td>{{$cotizacion->nombre_cotizacion}}</td>
    </tr>
    <tr>
      <td style="background: #eee">Fecha emisión</td>
      <td>{{$cotizacion->fecha_emision}}</td>
    </tr>

    <tr>
      <td style="background: #eee">Total</td>
      <td>${{number_format($cotizacion->total)}}</td>
    </tr>
  </tbody>
</table>
<table class="table">
    <thead>
      <tr>
        <th scope="col">Codigo interno</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Descripcion</th>
        <th scope="col">P.U</th>
        <th scope="col">Total</th>
      </tr>
    </thead>
    <tbody>
        @foreach($detalle as $d)
        <tr>
            <td>{{$d->codigo_interno}}</td>
            <td>{{$d->cantidad}}</td>
            <td>{{$d->descripcion}}</td>
            <td>${{number_format($d->precio_venta)}}</td>
            <td>${{number_format($d->cantidad * $d->precio_venta)}}</td>
        </tr>

        @endforeach
    </tbody>
  </table>
  <div class="d-flex justify-content-around">
      <button class="btn btn-success" onclick="cambiar_estado({{$cotizacion->id}},2)">Confirmar</button>
      <button class="btn btn-danger" onclick="cambiar_estado({{$cotizacion->id}},0)">Rechazar</button>
  </div>
@endif