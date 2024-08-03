@if($rcof->count()>0)
    <div class="fcontenedor">
        <table class="table table-bordered table-sm table-hover">
            <thead>
                <th>Sel</th>
                <th>Fecha</th>
                <th>Sec</th>
                <th>Estado</th>
                <th>TotalBol</th>
                <th>TotalNC</th>
                <th>TrackID</th>
                <th>estado SII</th>
                <th>detalle</th>
            </thead>
            <tbody>
                @foreach($rcof as $dia)
                <tr>
                    <td>
                        <input type="radio" name="fecha" id="{{$dia->fecha_emision}}" onclick="seleccionado('{{$dia->fecha_emision}}')">
                        <input type="hidden" id="trackid-{{$dia->fecha_emision}}" value="{{$dia->trackid}}">
                    </td>
                    <td>{{\Carbon\Carbon::parse($dia->fecha_emision)->format("d-m-Y")}}</td>
                    <td>{{$dia->secuencia}}</td>
                    @if($dia->estado==0)
                        <td style="color:yellow">SIN PROCESAR</td>
                    @elseif($dia->estado==1)
                        <td style="color:orange">PROCESADO</td>
                    @elseif($dia->estado==2 ||$dia->estado==3)
                        <td style="color:blue">REVISADO</td>
                    @else
                        <td style="color:red">ERROR</td>
                    @endif
                    <td>{{intval($dia->total_39)}}</td>
                    <td>{{intval($dia->total_61)}}</td>
                    <td>{{$dia->trackid}}</td>
                    <td>{{$dia->estado_sii}}</td>
                    <td>{{$dia->detalle}}</td>
                    <!--
                    <td>
                        @if($dia->estado==1)
                            <button class="btn btn-sm btn-warning" onclick="enviar_sii('{{$dia->url_xml}}')">ENVIAR A SII</button>
                        @else
                            @if($dia->estado_sii=='NO RECIBIDO')
                                <button class="btn btn-sm btn-success" onclick="procesar('{{$dia->fecha_emision}}')">PROCESAR</button>
                            @elseif($dia->estado_sii=="RECIBIDO")
                                <button class="btn btn-sm btn-primary" onclick="ver_estado()">VER ESTADO</button>
                            @elseif($dia->estado_sii=="RECHAZADO" || $dia->estado_sii=="REPARO" || $dia->estado_sii=="ACEPTADO")
                                <p style="color:blue">CERRADO</p>
                            @endif
                        @endif
                    </td>
                -->
                </tr>


                @endforeach
            </tbody>
        </table>

    </div>
@else
    <br>
    <center>
        <h3 style="color:red">{{$mensaje}}</h3><br><br>
        @if($crear===true)
            <button class="btn btn-primary btn-lg" onclick="crear_rcof()">Crear</button>
        @endif
    </center>
@endif
