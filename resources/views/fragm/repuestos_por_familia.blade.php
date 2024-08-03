<div class="my-2 my-lg-0 mb-3">
    <input class="form-control mr-sm-2 float-left" id="medida_busqueda" type="search" placeholder="Buscar" aria-label="Buscar" style="width: 200px;" >
    <button class="btn btn-outline-success my-2 my-sm-0 float-rigth" role="button" onclick="busqueda_rapida()">Buscar</button>
    <button class="btn btn-outline-primary my-2 my-sm-0 float-right" role="button" onclick="volver()">Volver</button>
</div>
@if($repuestos->count() == 0)
            <div class="alert-danger">
                <p>Medidas no corresponden a la familia seleccionada</p>
                <button class="btn btn-primary btn-sm" onclick="volver()">Volver</button>
            </div>
@else
    <table class="table mt-2">
    <thead>
        <tr>
            <th scope="col">Código interno</th>
            <th scope="col">Descripción</th>
            <th scope="col">Marca</th>
            <th scope="col">Medida</th>
           
            <th scope="col"></th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($repuestos as $repuesto)
            <tr>
              @if(Auth::user()->rol->nombrerol == "Administrador")
                <td class="letra_pequeña"> <a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank" style="color:black"> {{$repuesto->codigo_interno}} </a></td>
              @else
                <td class="letra_pequeña">{{$repuesto->codigo_interno}}</td>
              @endif  
                <td class="letra_pequeña">{{$repuesto->descripcion}}</td>
                <td class="letra_pequeña">{{$repuesto->marcarepuesto}}</td>
                
                @php
           //$porciones =explode(',',$repuesto->medidas);
           // Usando explode con múltiples delimitadores
            $porciones = preg_split('/[:,]/', $repuesto->medidas);
                @endphp
                <td class="letra_pequeña" style="background-color:#eee;">
                        @foreach($porciones as $p)
                        @if($p == 'DISCO' || $p == 'PRENSA')
                            <span style="font-weight: bold">{{$p}}</span><br>
                        @else
                            <span>{{$p}}</span> <br>
                        @endif
                        
                        @endforeach
                </td>
          
               
                {{-- <td><button class="btn btn-warning btn-sm" onclick="edicion_repuesto('{{$repuesto->id}}')">M</button></td> --}}
                <td><button class="btn btn-danger btn-sm" onclick="mas_detalle('{{$repuesto->id}}')" data-toggle = "modal" data-target="#masDetalleModal">Detalle</button></td>
            </tr>
        @endforeach
@endif
    </tbody>
</table>

<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="masDetalleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: #000; color: white; text-align:center;">
          <h5 class="modal-title" id="exampleModalLabel" style="">PanchoRepuestos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="background: rgb(242, 244, 169);">
            <div class="row" style="width: 100%;">
                <div class="col-md-4">
                    <div id="zonaInfoRepuesto_modal">

                    </div>
                </div>
                <div class="col-md-3">
                  <div id="zonaInfoReguladorVoltaje_modal">

                  </div>
                </div>
                <div class="col-md-5">
                    <div id="zonaInfoSimilares_modal">

                    </div>
                    <div class="row" style="width: 100%">
                        <div class="col-md-4">
                          <div id="zonaInfoOems_modal" >
  
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div id="zonaInfoFab_modal">
  
                          </div>
                        </div>
                      </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer bg-dark">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>