@if($opcion=='y')
    @if(count($datos)>0)
        <div class="row">
            <div class="col-3">
                <b>Total Documentos: {{$total_docus}}</b><br>
                Total Doc Contado: {{$total_docus_contado}}<br>
                Total Doc Crédito: {{$total_docus_credito}}
            </div>
            <div class="col-3">
                <b>Total Oper. Transbank: {{$total_operaciones_transbank}}</b><br>
                Total Oper. Tran. Crédito: {{$total_operaciones_transbank_tc}}<br>
                Total Oper. Tran. Débito: {{$total_operaciones_transbank_td}}<br><br>
            </div>
        </div>

        <div>
            <table class="table table-sm table-hover table-bordered table-wrapper">
                <thead>
                    <th scope='col'>HORA</th>
                    <th scope='col'>NUM</th>
                    <th scope='col'>TOTAL DOC</th>
                    <th scope='col'>CREDITO</th>
                    <th scope='col'>TOTAL PAGO</th>
                    @foreach($formas_de_pago as $fp)
                        <th scope='col'>{{trim($fp->formapago)}}</th>
                    @endforeach
                    <th scope='col'>ESTADO SII</th>
                    <th scope='col'>USUARIO</th>
                </thead>
                <tbody>
                    @foreach($datos as $i)
                        <tr>
                            @if($i['num_docu']=="TOTALES:")
                                <td class="text-center"></td>
                            @else
                                <td class="text-center">{{date('H:i',strtotime($i['fecha_docu']))}}</td>
                            @endif

                            <td class="text-center">{{$i['num_docu']}}</td>
                            <td class="text-right">{{$i['total_docu']}}</td>
                            <td class="text-center">{{$i['es_credito']}}</td>
                            <td class="text-right">{{$i['total_pagos']}}</td>
                            @foreach($formas_de_pago as $fp)
                                @if($fp->formapago=='Efectivo')
                                    <td class="text-right">{{str_replace("&EFECTIVO","",$i[$fp->formapago])}}</td>
                                @else
                                    @php
                                        if(strpos($i[$fp->formapago],"&")===false){
                                            echo "<td class=\"text-right\">".$i[$fp->formapago]."</td>";
                                        }else{
                                            list($pago,$referencia)=explode("&",$i[$fp->formapago]);
                                            echo "<td class=\"text-right\">".$pago."<br>ref: ".$referencia."</td>";
                                        }
                                    @endphp
                                @endif

                            @endforeach
                            <td>{{$i['estado_sii']}}</td>
                            <td>{{$i['usuario_docu']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <center><h3 style="color:red">NO HAY DATOS</h3></center>
    @endif
@else
    <H2>PRONTO</H2>
@endif
