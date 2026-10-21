@if($familias->count())
  <div class="small font-weight-bold text-uppercase text-muted mb-2">Familias disponibles</div>
  <div class="list-group list-group-flush border rounded">
    @foreach($familias as $familia)
      <button type="button"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3"
              onclick="damerepuestos({{$familia->id_familia}})">
        <span>{{$familia->nombrefamilia}}</span>
        <span class="badge badge-primary badge-pill">{{$familia->total}}</span>
      </button>
    @endforeach
  </div>
@else
  <div class="alert alert-light border text-center text-muted mb-0">
    <i class="fas fa-info-circle mr-1"></i> No hay familias con repuestos para este modelo.
  </div>
@endif
