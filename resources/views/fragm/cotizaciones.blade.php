<div class="row" style="width: 100%;">
@foreach($cotizaciones as $c)
                  <div class="col-md-3">
                    <div class="card mb-3">
                      <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="Card image cap" srcset="" class="card-img-top">
                      
                      <div class="card-body text-center">
                        <h5 class="card-title">{{$c->num_cotizacion}}</h5>
                        <div class="card-text letra_pequeña">
                            <p><a href="javascript:void(0)" onclick="cargar_cotizacion({{$c->num_cotizacion}})">{{$c->nombre_cotizacion}}</a> </p>
                            <p>{{$c->fecha}}</p>
                          </div>
                      </div>
                    </div>
                  </div>
                  
@endforeach
</div>
