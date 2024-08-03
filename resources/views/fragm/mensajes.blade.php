  <div class="row" style="width: 100%;">
  <div class="col-sm-12" id="mensajes">
  <!--MENSAJES NORMALES-->
  @if(!empty($msgGuardado))
    <div class="alert alert-success col-sm-12 text-center">{{ $msgGuardado }}</div>
  @else
    <div >&nbsp;</div>
  @endif

  <!--MENSAJES DE ERROR-->
  @if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <p>ATENCIÓN!!!</p>
    <ul>
      @foreach($errors->all() as $error)
        <li>{{$error}}</li>
      @endforeach
    </ul>
  </div>
  @endif

  </div> <!-- Fin del col-sm-12 -->
</div> <!-- Fin del row -->
