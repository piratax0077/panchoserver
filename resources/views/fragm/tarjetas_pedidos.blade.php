@if($solicitudes->count() > 0)<h3>Notas de Crédito</h3>@endif
            <div class="row" style="width: 100%;">
              @if(count($solicitudes) > 0)
              @foreach($solicitudes as $solicitud)
              <div class="col-md-3">
                <div class="card mb-3">
                  <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="Card image cap" srcset="" class="card-img-top">
                  
                  <div class="card-body">
                    <h5 class="card-title"><a href="javascript:void(0)" onclick="cargar_devolucion({{$solicitud->num_nc}})">{{$solicitud->num_nc}}</a> </h5>
                    <div class="card-text">
                        
                      </div>
                  </div>
                </div>
              </div>
              
              @endforeach
              @else 
                <h3 class="alert-danger">No hay devoluciones de mercaderías</h3>
              @endif
            </div>