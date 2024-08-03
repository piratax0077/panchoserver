<div class="mt-3">
    
    <div class="row" style="min-height: 500px;">
      <div class="col-md-3" style="border-right: 10px solid black;background: rgb(242, 244, 169) none repeat scroll 0% 0%; padding: 10px;">
        <table class="table">
          <thead class="thead-dark">
            
          </thead>
          <tbody>
            <tr>
              <td class="bg-dark text-light">Tipo Documento</td>
              <td class="pl-2"> <strong>{{$tipo_documento}}</strong> </td>
            </tr>
            <tr>
              <td class="bg-dark text-light">N° Documento</td>
              <td class="pl-2"> <strong>{{$documento->num_doc}}</strong> </td>
            </tr>
              <tr>
                <td class="bg-dark text-light">Fecha Emisión</td>
                <td class="pl-2"> <strong>{{\Carbon\Carbon::parse($nc->fecha_emision)->format("d-m-Y")}}</strong> </td>
              </tr>
              <tr>
                <td class="bg-dark text-light">Cantidad de Repuestos</td>
                <td class="pl-2"> <strong>{{$detalle->count()}}</strong> </td>
              </tr>
              <tr>
                <td class="bg-dark text-light">Motivo</td>
                <td class="pl-2"><strong>{{$nc->motivo_correccion}}</strong></td>
              </tr>
            
          </tbody>
        </table>
        <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" class="w-100" style="border-radius: 10px;">
      </div>
        <div class="col-md-7">
            <table class="table">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Cod. Int.</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Precio</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Total</th>
                    <th scope="col">A devolver</th>
                    <th scope="col">Origen</th>
                    <th scope="col">Destinos</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($detalle as $d)
                    <tr>
                        <td>{{$d->codigo_interno}}</td>
                        <td style="width: 30%;">{{$d->descripcion}}</td>
                        <td>$ {{number_format($d->precio_venta,0,',','.')}}</td>
                        <td>{{$d->cantidad}} <input type="hidden" name="" id="merma-{{$d->id_repuestos}}" value="{{$d->cantidad}}"></td>
                        <td>$ {{number_format($d->cantidad * $d->precio_venta,0,',','.')}}</td>
                        <td style="margin: 0px auto; width: 9%"><input type="number" min="1" max="{{$d->cantidad}}" name="" class="w-75" id="cantidad-{{$d->id_repuestos}}"  value="{{$d->cantidad}}"></td>
                        <td>{{$d->local_nombre}}</td>
                        <td>
                            <select name="local_id" id="local_id-{{$d->id_repuestos}}" >
                                @foreach($locales as $l)
                                <option value="{{$l->id}}">{{$l->local_nombre}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button class="btn btn-success btn-sm" onclick="confirmar_devolucion({{$d->id_repuestos}})">Agregar</button></td>
                      </tr>
                    @endforeach
                  
                </tbody>
              </table>
        </div>
        <div class="col-md-2">
            <table class="table">
                <thead class="thead-dark">
                  @php
                  $total = 0;
                    foreach ($detalle as $d) {
                      $total += $d->precio_venta * $d->cantidad;
                    }
                  @endphp
                    <tr>
                      <th scope="col">Total Devolución</th>
                      <th scope="col">$ {{number_format($total,0,',','.')}}</th>
                      <th></th>
                    </tr>
                  </thead>
              </table>
              <div id="table_devolucion_">

              </div>
              
              <button class="btn btn-danger" onclick="cerrar_devolucion({{$nc->num_nota_credito}})">Cerrar Devolución</button>
              
        </div>
    </div>
    
      <input type="hidden" name="num_nc" id="num_nc" value="{{$nc->num_nota_credito}}">
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <p>
          @if($nc->num_nota_credito)
          <strong>Listo!</strong>. Solicitud de devolución cerrada para la nota de crédito N°.{{$nc->num_nota_credito}} realizada por {{$nc->name}} con los siguiente repuestos:
          @else()
          <strong>Listo!</strong>. Solicitud de devolución cerrada para el documento N°.{{$nc->num_doc}} realizada por {{$nc->name}} con los siguiente repuestos:
          @endif
        </p>
        <div id="listado_repuestos_devolucion">
          @if(isset($devoluciones))
          <ul>
            @foreach($devoluciones as $d)
            <li>{{$d->descripcion}} - {{$d->cantidad}} - {{$d->local_nombre}}</li>
            @endforeach
          </ul>
          @endif
        </div>
        @if($nc->num_nota_credito)
       <button id="btn-imprimir" class="btn btn-warning sm" onclick="imprimir_devolucion({{$nc->num_nota_credito}})">Imprimir</button>
        @else
        <button id="btn-imprimir" class="btn btn-warning sm" onclick="imprimir_devolucion({{$nc->num_doc}})">Imprimir</button>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


