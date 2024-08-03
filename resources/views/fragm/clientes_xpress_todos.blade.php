@if($clientes_xpress->count()>0)
    <table class="table table-hover table-responsive-sm">
        <thead>
            <th>id</th>
            <th>rut</th>
            <th>Cliente</th>
            <th>Celular</th>
            <th>Correo</th>
            <th>Documento</th>
            <th>Activo</th>
            <th>Estado</th>
            <th></th> <!-- enviar correo -->
            <th></th> <!-- enviar sms -->
        </thead>
        <tbody>
            @foreach ($clientes_xpress as $cx)
                <tr>
                    <input type="hidden" id="correo-{{$cx->id}}" value="{{$cx->email_xpress}}">
                    <input type="hidden" id="documento-{{$cx->id}}" value="{{$cx->documento_xpress}}">
                    <input type="hidden" id="estado-{{$cx->id}}" value="{{$cx->estado}}">
                    <td>{{$cx->id}}</td>
                    <td>
                        @if ($cx->rut_xpress=="999999999")
                            ---
                        @else
                            {{$cx->rut_xpress}}
                        @endif

                    </td>
                    @if ($cx->empresa_xpress=="---")
                        <td>{{$cx->nombres_xpress}} {{$cx->apellidos_xpress}}</td>
                    @else
                        <td>{{$cx->empresa_xpress}}</td>
                    @endif
                    <td>{{$cx->telf1_xpress}}</td>
                    <td>{{$cx->email_xpress}}</td>
                    <td>{{$cx->documento_xpress}}</td>
                    <td>
                        @if ($cx->activo==1)
                            <input type="checkbox" id="cliente-{{$cx->id}}" onclick="activar_cliente_xpress({{$cx->id}})" checked>
                        @else
                            <input type="checkbox" id="cliente-{{$cx->id}}" onclick="activar_cliente_xpress({{$cx->id}})">
                        @endif
                    </td>
                    <td>{{$cx->estado}}</td>
                    <td><button class="btn btn-warning btn-sm" onclick="enviar_correo({{$cx->id}})"><small>CORREO</small></button></td>
                    <td><button class="btn btn-success btn-sm" onclick="enviar_sms({{$cx->id}})"><small>SMS</small></button></td>

                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h3>No hay clientes Xpress</h3>
@endif
