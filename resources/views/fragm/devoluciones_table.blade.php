
@if($devoluciones->count())
<div class="table-responsive"><table class="table table-sm mb-0">
    <thead>
      <tr>
        <th scope="col">Código</th>
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
  </table></div>
@else
<div class="return-empty"><i class="fas fa-box-open d-block mb-2"></i>Aún no has agregado productos.</div>
@endif
