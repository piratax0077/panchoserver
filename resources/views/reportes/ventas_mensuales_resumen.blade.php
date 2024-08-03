<h5>TOTALES DE {{$nombre_mes}} DE {{$year}}</h5>
    @php
        $totales_forma=0;
        $boletas_forma=0;
        $facturas_forma=0;
        $total=0;
        $total_boletas=0;
        $total_facturas=0;
        $total_transbank=0;
        $total_nc=0;
        $total_rechazados=0;
        $total_delivery_pendientes=0;
        $total_delivery_pagado=0;
        
    @endphp
@foreach($usuarios as $usuario)
    @php
        $totales_usuario[$usuario->name]=0;
        $promedios_usuario[$usuario->name]=0;
        $boletas_usuario[$usuario->name]=0;
        $facturas_usuario[$usuario->name]=0;
    @endphp
@endforeach

<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Forma</th>
        @foreach($usuarios as $usuario)
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        @endforeach
        <th class="text-center">Total</th>
    </thead>
    <tbody>
        @foreach($formas_pago as $forma)
        <tr>
            <td class="letra-chica">{{$forma->formapago}}</td>
            @php $totales_forma=0; @endphp
            @foreach($usuarios as $usuario)
                <td class="text-right letra-chica">
                        @php
                            $valor=$totales[$usuario->name][$forma->formapago];
                            if($valor>0){
                                echo number_format(intval($valor),0,',','.');
                                $totales_forma+=intval($valor);
                                $totales_usuario[$usuario->name]+=intval($valor);
                            }
                        @endphp
                </td>
            @endforeach
                <td class="letra-chica text-right">@php echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" @endphp</td>
        </tr>
        @endforeach
        <tr>
            <td>Delivery</td>
            @foreach($usuarios as $usuario)

                <td class="letra-chica text-right">
                    @php
                        $totales_usuario[$usuario->name]+=$totales[$usuario->name]['delivery'];
                        $total_delivery_pagado+=$totales[$usuario->name]['delivery'];
                        echo $totales[$usuario->name]['delivery']>0?number_format($totales[$usuario->name]['delivery'],0,',','.'):"";
                    @endphp
                </td>
            @endforeach
            <td class="letra-chica text-right">
                @php
                  echo $total_delivery_pagado>0?number_format($total_delivery_pagado,0,',','.'):"";
                @endphp

            </td>
        </tr>
        <tr>
            <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
            @foreach($usuarios as $usuario)
                <td class="text-right" style="background-color: rgb(219, 219, 255)">
                    @php
                        if($totales_usuario[$usuario->name]>0){
                            echo number_format($totales_usuario[$usuario->name],0,',','.');
                            $total+=$totales_usuario[$usuario->name];
                        }
                    @endphp
                </td>
            @endforeach
            <td class="text-right">
                @php
                    if($notcred_total>0){
                        echo number_format($total,0,',','.')."<br> - ".number_format($notcred_total,0,',','.')."<br><b>".number_format(($total-$notcred_total),0,',','.')."</b>";
                        $total_fijo =$total-$notcred_total;
                    }else{

                        echo "<b>".number_format($total,0,',','.')."</b>";
                        $total_fijo =$total;
                    }
                    
                @endphp
            </td>
        </tr>
        <tr>
            <td style="background-color: #6395ec"><b>RENDIMIENTO</b></td>
            @foreach($usuarios as $usuario)
                <td class="text-right" style="background-color: #6395ec;">
                    @php
                        if($totales_usuario[$usuario->name]>0){
                            $promedios_usuario[$usuario->name] = ($totales_usuario[$usuario->name]/$total_fijo)*100;
                            echo (number_format($promedios_usuario[$usuario->name],2,',','.'));
                            echo ' % ';
                            $total+=$totales_usuario[$usuario->name];
                        }
                    @endphp
                </td>
            @endforeach
        </tr>
</tbody>
</table>
<canvas id="grafico"  height="80px"></canvas>
@foreach($usuarios as $usuario)
    @php
        $totales_usuario[$usuario->name]=0;
    @endphp
@endforeach
<br>
<h5>TRANSBANK RESUMEN</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Transbank</th>
        @foreach($usuarios as $usuario)
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        @endforeach
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
                @if($forma->id==2 || $forma->id==5) <!-- tarj crédito o tarj débito -->
                    <tr>
                        <td class="letra-chica">{{$forma->formapago}}</td>
                        @php $totales_forma=0; $total=0;@endphp
                        @foreach($usuarios as $usuario)
                            <td class="text-right letra-chica">
                                    @php
                                        $valor=$totales[$usuario->name][$forma->formapago];
                                        if($valor>0){
                                            echo number_format(intval($valor),0,',','.');
                                            $totales_forma+=intval($valor);
                                            $totales_usuario[$usuario->name]+=intval($valor);
                                        }
                                    @endphp
                            </td>
                        @endforeach
                            <td class="letra-chica text-right">@php echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" @endphp</td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($totales_usuario[$usuario->name]>0){
                                echo number_format($totales_usuario[$usuario->name],0,',','.');
                                $total+=$totales_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total,0,',','.')."</b>" @endphp</td>
            </tr>
    </tbody>
</table>
@if($notcred->count()>0)
    <br>
    <h5>NOTAS DE CRÉDITO</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th class="text-center" width="50px" scope="col">N°</th>
            <th width="200px" scope="col">Referencia</th>
            <th width="200px" scope="col">Motivo</th>
            <th width="70px" scope="col">Total</th>
            <th width="100px" scope="col">Pago</th>
            <th width="100px" scope="col">Usuario</th>
        </thead>
        <tbody>
                @foreach($notcred as $nc)
                @php $total_nc+=$nc->total; @endphp
                <tr>
                    <td class="letra-chica text-center"><a href="javascript:imprimir_xml('{{$nc->url_xml}}')">{{$nc->num_nota_credito}}</a></td>
                    <td class="letra-chica">
                        <a href="javascript:detalle('bo','0','0')">
                            @php
                                list($doc,$ref,$fec)=explode("*",$nc->docum_referencia);
                                if($doc=='bo') $docu="Boleta";
                                if($doc=='fa') $docu="Factura";
                                echo "<a href=\"javascript:detalle('".$doc."','0','-".$ref."')\">". $docu." N° ".$ref."</a> del ".\Carbon\Carbon::parse($fec)->format("d-m-Y");
                            @endphp
                    </td>
                    <td class="letra-chica">
                        @php
                            echo substr($nc->motivo_correccion,2);
                        @endphp
                    </td>
                    <td class="letra-chica text-right">{{number_format(intval($nc->total),0,',','.')}}</td>
                    <td class="letra-chica">{{$nc->url_pdf}}</td>
                    <td class="letra-chica">{{trim($nc->usuario)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <td class="text-right" ><b>TOTAL:</b></td>
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">@php echo "<b>".number_format($total_nc,0,',','.')."</b>"@endphp</td>
                    <td></td>
                </tr>
        </tbody>
    </table>
    <br>
@endif
@if($rechazados->count()>0)
    <h5>RECIBIDOS NO ACEPTADOS</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th>Docum</th>
            <th class="text-center">N°</th>
            <th>Resultado</th>
            <th>Total</th>
            <th>Pago</th>
            <th>Usuario</th>
        </thead>
        <tbody>
                @foreach($rechazados as $re)
                @php $total_rechazados+=$re->total;@endphp

                <tr>

                    @if(substr($re->xml,0,2)=='39')
                        <td>Boleta</td>
                    @else
                        <td>Factura</td>
                    @endif

                    <td class="letra-chica text-center"><a href="javascript:imprimir_xml('{{$re->xml}}')">{{$re->num_doc}}</a></td>
                    <td class="letra-chica">
                        {{$re->estado_sii}}: {{$re->resultado}}
                    </td>
                    <td class="letra-chica text-right">{{number_format($re->total,0,',','.')}}</td>
                    <td class="letra-chica">{{$re->url_pdf}}</td>
                    <td class="letra-chica">{{$re->usuario}}</td>
                </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <td class="text-right" ><b>TOTAL:</b></td>
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">@php echo "<b>".number_format($total_rechazados,0,',','.')."</b>" @endphp</td>
                    <td></td>
                </tr>
        </tbody>
    </table>
    <br>
@endif
<h5>BOLETAS</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Forma</th>
        @foreach($usuarios as $usuario)
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        @endforeach
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $boletas_forma=0; @endphp
                @foreach($usuarios as $usuario)
                    <td class="text-right letra-chica">
                        <a href="javascript:detalle('bo','{{$usuario->id}}','{{$forma->id}}')">
                            @php
                                $valor=$boletas[$usuario->name][$forma->formapago];
                                if($valor>0){
                                    echo number_format(intval($valor),0,',','.');
                                    $boletas_forma+=intval($valor);
                                    $boletas_usuario[$usuario->name]+=intval($valor);
                                }
                            @endphp
                        </a>
                    </td>
                @endforeach
                <td class="letra-chica text-right">@php echo $boletas_forma>0?number_format($boletas_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($boletas_usuario[$usuario->name]>0){
                                echo number_format($boletas_usuario[$usuario->name],0,',','.');
                                $total_boletas+=$boletas_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_boletas,0,',','.')."</b>" @endphp</td>
            </tr>
    </tbody>
</table>

<h5>FACTURAS</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Forma</th>
        @foreach($usuarios as $usuario)
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        @endforeach
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $facturas_forma=0; @endphp
                @foreach($usuarios as $usuario)
                    <td class="text-right">
                        <a href="javascript:detalle('fa','{{$usuario->id}}','{{$forma->id}}')">
                            @php
                                $valor=$facturas[$usuario->name][$forma->formapago];
                                if($valor>0){
                                    echo number_format(intval($valor),0,',','.');
                                    $facturas_forma+=intval($valor);
                                    $facturas_usuario[$usuario->name]+=intval($valor);
                                }
                            @endphp
                        </a>
                    </td>
                @endforeach
                <td class="letra-chica text-right">@php echo $facturas_forma>0?number_format($facturas_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($facturas_usuario[$usuario->name]>0){
                                echo number_format($facturas_usuario[$usuario->name],0,',','.');
                                $total_facturas+=$facturas_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_facturas,0,',','.')."</b>" @endphp</td>
            </tr>
    </tbody>
</table>
<!--
<input type="hidden" name="promedio_jorge" id="promedio_jorge" value="<?php //echo $promedios_usuario['Jorge Saavedra'] ?>">

<input type="hidden" name="promedio_jose" id="promedio_jose" value="<?php //echo $promedios_usuario['José Troncoso'] ?>">
<input type="hidden" name="promedio_mauro" id="promedio_mauro" value="<?php //echo $promedios_usuario['Mauricio Eguren'] ?>">
<input type="hidden" name="promedio_mj" id="promedio_mj" value="<?php //echo $promedios_usuario['Maria Jose Rojas Valdes'] ?>">
<input type="hidden" name="promedio_marveise" id="promedio_marveise" value="<?php //echo $promedios_usuario['Marveise Albarracin'] ?>">
{{-- <input type="hidden" name="promedio_matias" id="promedio_matias" value="<?php //echo $promedios_usuario['Matias Alfaro'] ?>"> --}}
<input type="hidden" name="promedio_celimar" id="promedio_celimar" value="<?php //echo $promedios_usuario['CELIMAR BURGOS'] ?>">
<script>
    var promedio_jorge = $('#promedio_jorge').val();
    var promedio_mauro = $('#promedio_mauro').val();
    var promedio_jose = $('#promedio_jose').val();
    var promedio_mj = $('#promedio_mj').val();
    var promedio_marveise = $('#promedio_marveise').val();
    // var promedio_matias = $('#promedio_matias').val();
    var promedio_celimar = $('#promedio_celimar').val();
    var mi_primer_grafico ={
        type:"bar",
        data:{
          datasets:[{
              label:'Promedio de ventas mensuales',
            data:[promedio_celimar,promedio_jorge,promedio_jose,promedio_mj,promedio_marveise,promedio_mauro],
            backgroundColor: [
              "#04B404","#FFBF00",  "#FF0000",  "#04B4AE","#eee","aqua"
             ],
          }],
          labels: [
            "Celimar Burgos","Jorge Saavedra", "José Troncoso", "Maria Jose Rojas","Marveise Albarracin","Mauricio Eguren"
             ]
        },
        options:{
          responsive: true,
        }
      }
      var primer_grafico = document.getElementById('grafico').getContext('2d');
      window.pie = new Chart(primer_grafico,mi_primer_grafico);
</script>

-->