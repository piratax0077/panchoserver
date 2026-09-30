<div class="row">
  @foreach($solicitudes as $c)
    <div class="col-md-4 mb-2">
      <div class="card">
        <div class="card-header">
          {{$c->id}} - {{$c->titulo}}
        </div>
        <div class="card-body">
         
          <p>{{$c->usuario_nombre}}</p>
          <p>{{$c->fecha}} <br>{{$c->hora}}</p>
          <p>{{$c->bodeguero_nombre}}</p>
        </div>
        <div class="card-footer">
          @if($c->id_bodeguero == null || $c->id_bodeguero == Auth::user()->id)
          <button class="btn btn-outline-success btn-sm" onclick="cargar_venta({{$c->cliente_id}})">Abrir</button>
          <a href="javascript:borrar_carrito_transferido({{$c->cliente_id}})" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></a>
          @endif
        </div>
      </div>
    </div>
  @endforeach 
</div>