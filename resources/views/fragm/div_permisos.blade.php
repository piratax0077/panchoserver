
<div class="form-check">
    @foreach($opciones as $opcion)
    <input class="form-check-input" type="checkbox" value="{{$opcion}}" name="permisos" id="flexCheckDefault"> 
    <label class="form-check-label" for="flexCheckDefault">
        {{$opcion}}
      </label>
      <br>
    @endforeach
</div>

