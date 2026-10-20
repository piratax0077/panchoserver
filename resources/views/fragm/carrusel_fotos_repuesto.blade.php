@if(isset($fotos))
@if(count($fotos) > 0)
<div style="width: 100%;">
    <div style="text-align:center; margin-bottom: 12px;">
        <span style="background: #eceff1; color: #546e7a; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 12px; border-radius: 12px;">
            <i class="fa fa-images"></i> {{count($fotos)}} {{count($fotos) == 1 ? 'foto' : 'fotos'}}
        </span>
    </div>
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">            
            @foreach($fotos as $foto)
            <div class="carousel-item {{$loop->first ? 'active' : '' }}">
                <img class="d-block w-75 mx-auto" src="{{asset('storage/'.$foto->urlfoto)}}" alt="Second slide">
            </div>
            @endforeach
            
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev" style="background: #eee;">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next" style="background: #eee;">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
        </a>
  </div>
</div>
  @else
  <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img class="d-block w-100" src="..." alt="First slide">
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="..." alt="Second slide">
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="..." alt="Third slide">
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
  @endif