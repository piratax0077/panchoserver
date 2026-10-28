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
                <img class="d-block w-75 mx-auto" src="{{route('storage.public', ['path' => $foto->urlfoto], false)}}" alt="Fotografía del repuesto">
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
  <div style="width:100%; text-align:center;">
    <img class="d-block w-75 mx-auto" src="{{route('storage.public', [], false)}}" alt="Sin imagen disponible">
  </div>
  @endif
  @endif
