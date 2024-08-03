@if($dia->count()>0)

<table class="table table-sm table-hover table-bordered" style="width:90%">
    <thead>
        <th class="text-center" width="100px">HORA</th>
        <th class="text-center" width="100px">N° CARRITO</th>
        <th class="text-center" width="180px">MONTO</th>
        <th class="text-center" width="200px">OPERACION</th>
        <th class="text-center" width="180px">ESTADO</th>

    </thead>
    <tbody>
       @foreach($dia as $d)
        <tr>
            <td class="text-center" >{{$d->fecha_emision}}</td>
            <td>{{$d->numero_carrito}}</td>
            <td class="text-right">{{number_format($d->total,0,',','.')}}</td>
            <td class="text-center" ><button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#exampleModal" onclick="dame_detalle_carrito_virtual({{$d->numero_carrito}})">
                Detalle
              </button> </td>
              @if($d->status == 1)
              <td>Rechazado</td>
              @else
              <td>Aceptado</td>
              @endif
        </tr>
        @endforeach
    </tbody>
</table>
<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle carrito virtual</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_detalle_carrito">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>


@endif
