<div class="card mt-2" style="width: 100%;">
    @if(isset($fotos))
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
              
              @foreach($fotos as $foto)
              <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <img class="d-block w-100" src="{{asset('storage/'.$foto->urlfoto)}}" alt="Second slide">
              </div>
              @endforeach
              
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
    </div>
      @else
      <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img class="d-block w-100" src="https://via.placeholder.com/150" alt="First slide">
          </div>
          
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
      @endif
    <div class="card-body">
      <h5 class="card-title">{{$repuesto->descripcion}}</h5>
      <table class="table">
        <thead>

        </thead>
        <tbody>
          <tr>
            <td>Codigo Interno</td>
            <td><p class="card-text">{{$repuesto->codigo_interno}}</p></td>
          </tr>
          <tr>
            <td>Codigo OEM</td>
            <td><p class="card-text">{{$repuesto->codigo_OEM_repuesto}}</p></td>
          </tr>
        
            <tr>
              <td>Codigo Proveedor</td>
              <td><p class="card-text">{{$repuesto->cod_repuesto_proveedor}}</p></td>
            </tr>
            <tr>
              <td>Fabricantes</td>
              @if(count($fabs) > 0)
              <td class="tabla-scroll-y-250">@foreach($fabs as $fab) <span>{{$fab->codigo_fab}} - {{$fab->marcarepuesto}}</span> <br> @endforeach</td>
              @else
              <td><p class="card-text">Sin fabricantes</p></td>
              @endif
            </tr>
            <tr>
              <td>OEMS</td>
              @if(count($oems) > 0)
              <td class="tabla-scroll-y-250">@foreach($oems as $oem) <span>{{$oem->codigo_oem}}</span> <br> @endforeach</td>
              @else
              <td><p class="card-text">Sin Oems</p></td>
              @endif
            </tr>
            <tr>
              <td>Medidas</td>
              <td><p class="card-text">{{$repuesto->medidas}}</p></td>
            </tr>
          <tr>
            <td>Precio venta</td>
            <td><p class="card-text">${{number_format($repuesto->precio_venta)}}</p></td>
          </tr>
          
            @if($repuesto->local_id == 1 && $repuesto->local_id_dos == 3)
            <tr>
              <td>Stock bodega </td> 
              <td>{{$repuesto->stock_actual}}  
                @if($repuesto->stock_actual == 0)
                  <span class="badge badge-danger">Sin stock en bodega</span>
                @elseif($repuesto->stock_actual < 3) 
                  <span class="badge badge-warning">Stock acabandose</span> 
                @endif
              </td>
            </tr>
            <tr>
              <td>Stock tienda </td> 
              <td>{{$repuesto->stock_actual_dos}} 
                @if($repuesto->stock_actual_dos == 0) 
                <span class="badge badge-danger">Sin stock en tienda</span>
                
                @elseif($repuesto->stock_actual_dos < 3)
                <span class="badge badge-warning">Stock acabandose</span> 
                @endif
              </td>
            </tr>
            <tr>
              <td>Stock Casa Matríz</td>
              <td>{{$repuesto->stock_actual_tres}}
                @if($repuesto->stock_actual_tres == 0) 
                <span class="badge badge-danger">Sin stock en Casa Matríz</span>
                
                @elseif($repuesto->stock_actual_tres < 3)
                <span class="badge badge-warning">Stock acabandose</span> 
                @endif</td>
            </tr>
           
            @elseif($repuesto->local_id == 3 && $repuesto->local_id_dos == 1)
            <tr>
              <td>Stock bodega </td>
              <td>{{$repuesto->stock_actual_dos}}  @if($repuesto->stock_actual_dos < 3) <span class="badge badge-warning">Stock acabandose</span> @endif</td>
            </tr>
            <tr>
              <td>Stock tienda </td>
              <td>{{$repuesto->stock_actual}}  @if($repuesto->stock_actual < 3) <span class="badge badge-warning">Stock acabandose</span> @endif</td>
            </tr>
            <tr>
              <td>Stock Casa Matríz</td>
              <td>{{$repuesto->stock_actual_tres}}
                @if($repuesto->stock_actual_tres == 0) 
                <span class="badge badge-danger">Sin stock en Casa Matríz</span>
                
                @elseif($repuesto->stock_actual_tres < 3)
                <span class="badge badge-warning">Stock acabandose</span> 
                @endif</td>
              </td>
            </tr>
            @elseif($repuesto->local_id == 1 && is_null($repuesto->local_id_dos))
            
            <tr>
              <td>Stock bodega </td>
              <td>{{$repuesto->stock_actual}}  @if($repuesto->stock_actual < 3) <span class="badge badge-warning">Stock acabandose</span> @endif</td>
            </tr>
            <tr>
              <td><span class="badge badge-danger">{{$repuesto->stock_actual_dos}}<span class="badge badge-danger">Sin stock en Tienda</span></span></td>
            </tr>
            <tr>
              <td>Stock Casa Matríz</td>
              <td>{{$repuesto->stock_actual_tres}}
                @if($repuesto->stock_actual_tres == 0) 
                <span class="badge badge-danger">Sin stock en Casa Matríz</span>
                
                @elseif($repuesto->stock_actual_tres < 3)
                <span class="badge badge-warning">Stock acabandose</span> 
                @endif</td>
              </td>
            </tr>
            @elseif($repuesto->local_id == 3 && is_null($repuesto->local_id_dos))
            <tr>
              <td><span class="badge badge-danger">Sin stock en tienda</span></td>
            </tr> 
            <tr>
              <td>Stock tienda </td>
              <td>{{$repuesto->stock_actual}}  
                @if($repuesto->stock_actual < 3) 
                <span class="badge badge-warning">Stock acabandose</span>
                @elseif($repuesto->stock_actual_tres == 0)
                  <span class="badge badge-danger">Sin stock</span>
                @endif
              </td>
            </tr>
            <tr>
              <td>Stock Casa Matríz </td>
              <td>{{$repuesto->stock_actual_tres}}  
                @if($repuesto->stock_actual_tres < 3) 
                <span class="badge badge-warning">Stock acabandose</span>
                @elseif($repuesto->stock_actual_tres == 0)
                  <span class="badge badge-danger">Sin stock</span>
                @endif
              </td>
            </tr>
            @elseif($repuesto->local_id_dos == 0)
            <tr>
              <td>Stock bodega </td>
              <td>{{$repuesto->stock_actual}}  @if($repuesto->stock_actual < 3) <span class="badge badge-warning">Stock acabandose</span> @endif</td>
            </tr>
            <tr>
              <td>Stock tienda </td>
              <td>{{$repuesto->stock_actual_dos }}<span class="badge badge-danger">Sin stock en tienda</span> </td>
            </tr>
            <tr>
              <td>Stock Casa Matríz</td>
              <td>{{$repuesto->stock_actual_tres}}
                @if($repuesto->stock_actual_tres < 3) 
                <span class="badge badge-warning">Stock acabandose</span>
                @elseif($repuesto->stock_actual_tres == 0)
                  <span class="badge badge-danger">Sin stock</span>
                @endif</td>
            </tr>
            @endif
        </tbody>
        
      </table>
      
   
    </div>
    <div class="card-footer">

    </div>
</div>