<!-- ES LO MISMO??? fragm.repuesto_similares.blade -->
@if($similares->count()>0)
<center><strong>APLICACIONES ({{$similares->count()}})</strong></center>
  <div class="col-12 tabla-scroll-y-300">
    <table class="table table-sm">
      <thead class="thead-dark">
        <tr>
          <th scope="col">Marca</th>
          <th scope="col">Modelo</th>
          <th scope="col">Años</th>
          <th></th>
        </tr>
      </thead>
      @foreach($similares as $similar)
      <tr>
        <td class="letra-chica">{{$similar->marcanombre}}</td>
        @if($similar->zofri==1)
                  <td class="letra-chica">{{$similar->modelonombre}} - zofri</td>
                @else
                  <td class="letra-chica">{{$similar->modelonombre}}</td>
                @endif
        <td class="letra-chica">{{$similar->anios_vehiculo}}</td>
        <td>
          <abbr title="similares {{$similar->id}}">
            <button class="btn btn-danger btn-sm" style="line-height:10px" onclick="borrarsimilar({{$similar->id}})">X</button>
          </abbr>
        </td>
      </tr>
      @endforeach
    </table>
  </div>
@else
  <div class="col-12">
      <center><strong>APLICACIONES</strong></center>
    <p>No tiene similares</p>
  </div>
@endif
