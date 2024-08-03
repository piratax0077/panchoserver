<!-- ES LO MISMO??? fragm.factuprodu_similares.blade -->
<div class="container-fluid" style="margin-top:5px;padding:2px">
 <div class="col-12 tabla-scroll-y-300">
        <p align="center"><strong>APLICACIONES: {{$similares->count()}}</strong></p>
        @if($similares->count()>0)
        <table class="table table-sm tabla-scroll-y-200 letra-chica" style="overflow-x: hidden; border: 0px;">
          <thead>
            <tr>
              <th scope="col">Marca</th>
              <th scope="col">Modelo</th>
              <th scope="col">Años</th>
            </tr>
          </thead>
          <tbody>
          @foreach($similares as $similar) 
              <tr>
                <td>{{$similar->marcanombre}}</td>
                @if($similar->zofri==1)
                  <td>{{$similar->modelonombre}} - zofri</td>
                @else
                  <td>{{$similar->modelonombre}}</td>
                @endif
                
                <td>{{$similar->anios_vehiculo}}</td>
              </tr>
          @endforeach
        </tbody>
          </table>
        @endif
        </div>
      </div>
