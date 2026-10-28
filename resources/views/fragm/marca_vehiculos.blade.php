@if($marcas->count()>0)
<h4>MARCAS:</h4>
<ul style="list-style:none; overflow:auto; height:410px; width:100%;font-size:15px;padding-left:5px;">
    @foreach($marcas as $marca)
        <li class="mb-2 mt-2">
            <a href="javascript:void(0);" onclick="cargar_Modelos({{$marca->idmarcavehiculo}})">
                <img src="{{route('storage.public', ['path' => $marca->urlfoto], false)}}" width="100" height="100" alt="Imagen de {{$marca->marcanombre}}"/>&nbsp;{{$marca->marcanombre}}
            </a>
        </li>
        <hr style="margin:1px;background-color:black">
    @endforeach
</ul>

@else
<h4>NO HAY MARCAS DEFINIDAS </h4>
@endif
