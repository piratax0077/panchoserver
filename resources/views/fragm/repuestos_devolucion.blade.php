
@if($repuestos->count()>0)
<table class="table mt-3">
    <h3 class="mt-3">Detalle</h3>
    <thead class="thead-dark">
      <tr>
        <th scope="col">Cod. Int</th>
        <th scope="col">Descripción</th>
        <th scope="col">Cantidad</th>
        <th scope="col">Ubicación</th>
        <th scope="col">Enviado por</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
        @foreach($repuestos as $repuesto)
        <tr>
            <td scope="row">{{$repuesto->codigo_interno}}</td>
            <td>{{$repuesto->descripcion}}</td>
            <td>{{$repuesto->cantidad}}</td>
            <td>{{$repuesto->local_nombre}}</td>
            <td>{{$repuesto->name}}</td>
            <td class="d-flex"><button class="btn btn-success btn-sm" onclick="opciones_devolucion({{$repuesto->id}},{{$repuesto->cantidad}},{{$repuesto->local_id}},'+')">Aceptar</button> <button class="btn btn-danger btn-sm" onclick="opciones_devolucion({{$repuesto->id}},{{$repuesto->cantidad}},{{$repuesto->local_id}},'-')">Rechazar</button></td>
          </tr>
        @endforeach
      
      
    </tbody>
  </table>
  @else
  <p class="text-danger">No hay más repuestos</p>
  @endif

  <input type="hidden" name="num_nc" id="num_nc" value="{{$num_nc}}">