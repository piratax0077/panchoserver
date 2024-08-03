@if($modelos->count()>0)
<h4>MODELOS:</h4>
<ul style="list-style:none; overflow:auto; height:410px; width:100%;font-size:15px;padding-left:5px;">
    @foreach($modelos as $modelo)
        <li class="mb-2 mt-2"><a href="javascript:void(0);" onclick="buscar_por_modelo({{$modelo->id}})">
            <img src="{{asset('storage/'.$modelo->urlfoto)}}"  width="100px"/>&nbsp;{{$modelo->modelonombre}} {{$modelo->anios_vehiculo}}</a></li>
        <!--
        <li><a href="#" onclick="damefamilias({{$modelo->id}})"><img src="{{asset('storage/'.$modelo->urlfoto)}}" onmouseover="abrir_foto_modal('{{$modelo->urlfoto}}');" width="80px"/>&nbsp;{{$modelo->modelonombre}} {{$modelo->anios_vehiculo}}</a></li>
        -->
        <hr style="margin:1px;background-color:black">
    @endforeach
</ul>

@else
    <h4>NO HAY MODELOS DEFINIDOS</h4>
@endif
