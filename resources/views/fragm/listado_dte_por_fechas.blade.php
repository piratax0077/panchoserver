@php
    $num_doc="0";
@endphp
@if($dtes->count()>0)
    <table class="table table-sm table-hover">
        <thead>
            <th>FECHA</th>
            <th>NUM</th>
            <th>RUT</th>
            <th style="width: 30%">RAZON SOCIAL</th>
            <th>TRACKID</th>
            <th>TOTAL</th>
            <th>ESTADO SII</th>
            <th>USUARIO</th>
            <th></th><!-- imprimir -->
            <th></th><!-- enviar email -->
            <th></th><!-- ver estado -->
        </thead>
        <tbody>
            @foreach($dtes as $dte)
            <tr>
                <td>{{\Carbon\Carbon::parse($dte->fecha_emision)->format("d-m-Y")}} {{\Carbon\Carbon::parse($dte->created_at)->format("H:i")}}</td>
                <td>
                    @php
                        if($tipodte=='33'){
                            $num_doc=$dte->num_factura;
                        }
                        if($tipodte=='39'){
                            $num_doc=$dte->num_boleta;
                        }
                        if($tipodte=='52'){
                            $num_doc=$dte->num_guia_despacho;
                        }
                        if($tipodte=='61'){
                            $num_doc=$dte->num_nota_credito;
                        }
                        echo $num_doc;
                    @endphp

                </td>
                <td>{{$dte->rut}}</td>
                @if($dte->tipo_cliente==0)
                    <td>{{$dte->nombres}} {{$dte->apellidos}}</td>
                @else
                    <td>{{$dte->razon_social}}</td>
                @endif
                <td>{{$dte->trackid}}</td>
                <td>{{number_format($dte->total,0,',','.')}}</td>
                <td>
                    @if($dte->estado_sii=='ACEPTADO')
                        <p style="color:blue">{{$dte->estado_sii}}</p>
                    @elseif($dte->estado_sii=='RECIBIDO')
                        <p style="color:orange">{{$dte->estado_sii}}</p>
                    @else
                        <p style="color:red">{{$dte->estado_sii}}</p>
                    @endif

                </td>

                @if($dte->estado_sii=="ACEPTADO")
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-info btn-sm" onclick="imprimir('{{$dte->url_xml}}')">Imprimir</button></td>
                    <td><button class="btn btn-warning btn-sm" onclick="enviar_correo('{{$num_doc}}&{{$dte->email}}')">Enviar Correo</button></td>

                @elseif($dte->estado_sii=="RECIBIDO" || $dte->estado_sii=="EN REVISION")
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-success btn-sm" onclick="ver_estado('{{$dte->trackid}}')">Ver Estado</button></td>
                    <td></td>
                @else
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-success btn-sm" onclick="ver_estado('{{$dte->trackid}}')">Ver Estado</button></td>
                    <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

@else
<center><h3><p style="color:red">NO SE ENCONTRARON DOCUMENTOS</p></h3></center>

@endif
