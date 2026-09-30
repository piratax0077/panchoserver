@if($solicitudes->count() > 0)<h3>Carritos transferidos</h3>@endif
<div class="row" style="width: 100%;">
  @foreach($solicitudes as $c)
    <div class="col-md-4">
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
          <button class="btn btn-outline-success btn-sm" onclick="cargar_venta({{$c->cliente_id}})">Abrir</button>
        </div>
      </div>
    </div>
  @endforeach 
</div>