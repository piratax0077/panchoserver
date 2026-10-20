<!-- ES LO MISMO??? fragm.factuprodu_similares.blade -->
<div class="container-fluid" style="padding:0;">
 <div class="col-12">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78909c; margin-bottom: 10px;">
          <i class="fa fa-car"></i> Aplicaciones ({{$similares->count()}})
        </div>
        @if($similares->count()>0)
        <div style="background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #cfd8dc;">
        <div class="tabla-scroll-y-200">
        <table class="table table-sm letra-chica mb-0" style="width:100%; table-layout: fixed; border: 0px;">
          <thead>
            <tr style="background: #cfd8dc;">
              <th scope="col" style="width:17%; color: #37474f;">Marca</th>
              <th scope="col" style="width:63%; color: #37474f;">Modelo</th>
              <th scope="col" style="width:20%; color: #37474f;">Años</th>
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
        </div>
        </div>
        @else
        <p style="color: #78909c; font-style: italic; margin: 0;">Sin aplicaciones registradas.</p>
        @endif
        </div>
      </div>
