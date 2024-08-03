<div class="card mt-2" style="width: 100%;">
    @if(isset($fotos))
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100" src="{{asset('storage/'.$fotos[0]->urlfoto)}}" alt="First slide">
              </div>
              
              @foreach($fotos as $foto)
              <div class="carousel-item">
                <img class="d-block w-100" src="{{asset('storage/'.$foto->urlfoto)}}" alt="Second slide">
              </div>
              @endforeach
              
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev" style="background: #eee">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next" style="background: #eee">
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
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev" style="background: #eee">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next" style="background: #eee">
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
            <td>Medidas</td>
            @php
           //$porciones =explode(',',$repuesto->medidas);
           // Usando explode con múltiples delimitadores
            $porciones = preg_split('/[:,]/', $repuesto->medidas);
            @endphp
            <td>
                      @foreach($porciones as $p)
                        @if($p == 'DISCO' || $p == 'PRENSA')
                            <span class="card-text">{{$p}}</span><br>
                        @else
                            <span class="card-text">{{$p}}</span><br>
                        @endif
                      
                      @endforeach
            </td>
          
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
      
      <div class="row">
        <div class="col-md-6">
          <select name="idlocal" id="idlocal" class="form-control">
            @foreach($locales as $local)
              <option value="{{$local->id}}">{{$local->local_nombre}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <input type="number" min="0" class="form-control" id="cantidad" placeholder="Ingrese cantidad" /><button class="btn btn-success btn-sm mt-2" onclick="agregar_carrito({{$repuesto->id}})"><i class="fa-solid fa-cart-arrow-down"></i></button>
        </div>
      </div>
      
      
    </div>
    @if($repuesto->local_id == 1 && $repuesto->local_id_dos == 3)
      <input type="hidden" name="" id="stock-bodega" value="{{$repuesto->stock_actual}}">
      <input type="hidden" name="" id="stock-tienda" value="{{$repuesto->stock_actual_dos}}">
      <input type="hidden" name="" id="stock-cm" value="{{$repuesto->stock_actual_tres}}">
    @elseif($repuesto->local_id == 3 && $repuesto->local_id_dos == 1)
      <input type="hidden" name="" id="stock-bodega" value="{{$repuesto->stock_actual_dos}}">
      <input type="hidden" name="" id="stock-tienda" value="{{$repuesto->stock_actual}}">
      <input type="hidden" name="" id="stock-cm" value="{{$repuesto->stock_actual_tres}}">
    @elseif($repuesto->local_id == 1 && is_null($repuesto->local_id_dos))
      <input type="hidden" name="" id="stock-bodega" value="{{$repuesto->stock_actual}}">
      <input type="hidden" name="" id="stock-tienda" value="0">
      <input type="hidden" name="" id="stock-cm" value="{{$repuesto->stock_actual_tres}}">
    @elseif($repuesto->local_id == 3 && is_null($repuesto->local_id_dos))
      <input type="hidden" name="" id="stock-bodega" value="0">
      <input type="hidden" name="" id="stock-tienda" value="{{$repuesto->stock_actual}}">
      <input type="hidden" name="" id="stock-cm" value="{{$repuesto->stock_actual_tres}}">
    @endif
  </div>