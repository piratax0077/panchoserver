@if(count($respuesta)>0)
    <div>
        <table class="table table-sm table-hover table-bordered table-wrapper">
            <thead>
                <th scope='col' width="5%" class="text-center">ORDEN</th>
                <th scope='col' width="20%" class="text-center">DOCUMENTO</th>
                <th scope='col' width="10%" class="text-center">FECHA</th>
                <th scope='col' width="25%" class="text-center">REFERENCIA</th>
                <th scope='col' width="10%" class="text-center">MONTO</th>
                <th scope='col' width="5%" class="text-center">ACTIVO</th>
            </thead>
            <tbody>
                @foreach($respuesta as $r)
                    @if($r['orden']%100==0)
                        @if($r['activo']==0)
                            <tr style="background-color:rgb(255, 126, 126)">
                        @else
                            <tr style="background-color:rgb(251, 255, 0)">
                        @endif

                        <td class="text-center">{{$r['orden']/100}}</td>
                    @else
                        @if($r['activo']==0)
                            <tr style="background-color:rgb(255, 126, 126)">
                        @else
                            <tr>
                        @endif
                        <td>
                            @if(isset($r['es_pago']))
                                @if($r['es_pago']>0)
                                    <button class="btn btn-primary btn-sm" onclick="cargar_pago({{$r['es_pago']}})">EDITAR</button>
                                @endif
                            @endif
                        </td>
                    @endif

                        <td>
                            @if(isset($r['es_pago']))
                                @if($r['es_pago']==0)
                                    @if(isset($r['xml']))
                                        @php $xml=$r['xml']; @endphp
                                        <a href="javascript:void(0);" onclick="imprimir_xml('{{$xml}}')">{{$r['documento']}}</a>
                                    @else
                                        {{$r['documento']}}
                                    @endif
                                @else
                                    {{$r['documento']}}
                                @endif
                            @endif
                        </td>
                        <td class="text-center">{{\Carbon\Carbon::parse($r['fecha'])->format("d-m-Y")}}</td>
                        <td>{{$r['referencia']}}</td>
                        <td class="text-right">{{number_format($r['monto'],0,',','.')}}</td>
                        <td class="text-center">
                            @if($r['activo']==1)
                                SI
                            @else
                                NO
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <center><h3 style="color:red">NO HAY DATOS</h3></center>
@endif
