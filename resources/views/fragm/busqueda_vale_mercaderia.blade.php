@if(isset($vale))
<div id="vale_mercaderia_detalle" >
  <h3 class="text-center">Información Vale de Mercadería</h3>
  <hr>
    <div class="row" style="padding-top: 5px; padding-bottom: 5px; border-radius: 10px; background: #f2f4a9; border: 1px solid gray;">
        <div class="col-md-5">
            <table class="table">
                
                <tbody>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Total</td>
                    <td class="py-3">$ {{number_format($vale->valor,0,',','.')}}</td>
                  </tr>
                  <tr>
                    <td class="bg-dark text-white text-center py-3">Usuario</td>
                    <td class="py-3">{{$vale->name}}</td>
                  </tr>
                  <tr> 
                    <td class="bg-dark text-white text-center py-3">Cliente</td> 
                    <td class="py-3">{{$vale->nombre_cliente}}</td>
                  </tr>
                  <tr> 
                    <td class="bg-dark text-white text-center py-3">Descripción</td> 
                    <td class="py-3">{{$vale->descripcion}}</td>
                  </tr>
                </tbody>
              </table>
              @if($vale->valor > 0)
              <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exampleModal">Agregar repuesto</button>
              @endif
        </div>
        <div class="col-md-7">
            <div>
                @if($detalles->count() > 0)
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cód Int</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Origen</th>
                        <th scope="col">Precio Venta</th>
                        <th scope="col">Total</th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>
                        @php
                          $total = 0;
                        @endphp
                        @foreach($detalles as $d)
                        @php
                          $total = $total + $d->precio_venta * $d->cantidad;
                          
                        @endphp
                      <tr>
                        <td>{{$d->codigo_interno}}</td>
                        <td>{{$d->cantidad}}</td>
                        <td>{{$d->local_nombre}}</td>
                        <td>$ {{number_format($d->precio_venta,0,',','.')}}</td>
                        <td>$ {{number_format($d->precio_venta * $d->cantidad,0,',','.')}}</td>
                        <td>@if($vale->valor > 0)<button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_devolucion({{$d->id}})">x</button>@endif</td>
                      </tr>
                      @endforeach
                      
                      
                    </tbody>
                    
                  </table>
                  @php
                    $total_vale_mercaderia = number_format($vale->valor,0,',','.');
                    $total_ = number_format($total,0,',','.');
                  @endphp
                  <table class="table">
                    <tbody>
                      <tr>
                        <td>Total Vale Mercadería</td>
                        <td>$ {{$total_vale_mercaderia}}</td>
                      </tr>
                      <tr>
                        <td>Total</td>
                        <td>$ {{$total_}}</td>
                      </tr>
                      
                      <tr> 
                        <td>Diferencia</td> 
                        <td>$ {{number_format($total - $vale->valor,0,',','.')}}</td>
                      </tr>
                    </tbody>
                  </table>
                  @if($vale->valor > 0)
                  <button class="btn btn-warning btn-sm" onclick="imprimir_resultado({{$vale}},{{$detalles}})">Procesar</button>
                  @endif
                  @else
                      <p class="alert-danger">No hay repuestos ingresados ...</p>
                  @endif
            </div>
        </div>
    </div>
    
      <!-- Busqueda Repuesto Xpress MOdal -->
    <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="exampleModal">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header" style="background: #000; color: white;">
              <h5 class="modal-title">Busqueda de Repuestos Xpress</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" style="background: rgb(242, 244, 169);">
                <div class="row form-group-sm">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="" id="repuesto-xpress-codigo" placeholder="Cód. Ref." style="width:100%">&nbsp;
                    </div>
                    <div class="col-md-6">
                      <input type="button" value="Buscar" class="btn btn-warning btn-sm" onclick="buscar_repuesto_xpress()">
                    </div>
                </div>
                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">Descripción</th>
                      <th scope="col">Ubicación</th>
                      <th scope="col">Ubicación</th>
                      <th scope="col">Ubicación</th>
                      <th scope="col">Cantidad</th>
                      <th scope="col">Procedencia</th>
                      <th scope="col">Precio Venta</th>
                    </tr>
                  </thead>
                  <tbody id="informacion_repuesto">
                    
                  </tbody>
                </table>
                
            </div>
            <div class="modal-footer" style="background: #000; color: white;">
              
              <button type="button" id="btn_agregar_repuesto_buscado" class="btn btn-primary" >Agregar</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            </div>
            <hr>
            <p id="mensaje_modal" class="ml-3" >Esperando ...</p>
          </div>
        </div>
      </div> <!-- FIN Repuesto Xpress MOdal -->
</div>

<input type="hidden" name="numero_vale_mercaderia" id="numero_vale_mercaderia" value="{{$vale->numero_boucher}}">
@else
<p class="text-danger">Vale de mercadería procesado</p>
@endif
