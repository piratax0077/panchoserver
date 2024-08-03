<form action="{{url('loginvs')}}" method="post">
    {{ csrf_field() }}
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre">
    <input type="submit" value="GO">
</form>

@if(isset($resp))
<p>SI que tal {{$resp}}</p>
@else
<p>NO que tal</p>
@endif