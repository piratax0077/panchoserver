<!-- ES LO MISMO??? fragm.factuprodu_similares.blade -->
<div class="container-fluid" style="margin-top:5px;padding:2px">
 <div class="col-12 tabla-scroll-y-300">
        <p align="center"><strong>APLICACIONES: {{$similares->count()}}</strong></p>
        @if($similares->count()>0)
        <table class="table table-sm tabla-scroll-y-200 letra-chica" style="overflow-x: hidden; border: 0px;">
          <thead>
            <tr>
              <th scope="col" style="width:17%">Marca</th>
              <th scope="col" style="width:63%">Modelo</th>
              <th scope="col" style="width:20%">Años</th>
            </tr>
          </thead>
          <tbody>
          @foreach($similares as $similar)
              <tr>
                <td>{{$similar->marcanombre}}</td>
                @if($similar->zofri==1)
                  <td>{{$similar->modelonombre}} - ZOFRI</td>
                @else
                  <td>{{$similar->modelonombre}}</td>
                @endif
                <td class="text-right">
                    @if(Session::get('rol')=="S")
                        <input type="text" id="anio_simi_{{$similar->id}}" value="{{$similar->anios_vehiculo}}" style="width:100%;text-align:center" readonly ondblclick="editar_anio_similares({{$similar->id}});" onkeyup="enter_anio_similar(event,{{$similar->id}})" onblur="guardar_anio_similares({{$similar->id}})">
                    @else
                        {{$similar->anios_vehiculo}}
                    @endif
                </td>
              </tr>
          @endforeach
        </tbody>
          </table>
        @endif
        </div>
      </div>
