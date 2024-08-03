<h5>TOTALES</h5>
    @php
        $totales_forma=0;
        $boletas_forma=0;
        $facturas_forma=0;
        $abonos_forma=0;
        $total=0;
        $total_boletas=0;
        $total_facturas=0;
        $total_abonos=0;
        $total_transbank=0;
        $total_nc=0;
        $total_rechazados=0;
        $total_delivery_pendientes=0;
        $total_delivery_pagado=0;
    @endphp

    @php
        $totales_usuario[$usuario->name]=0;
        $boletas_usuario[$usuario->name]=0;
        $facturas_usuario[$usuario->name]=0;
        $abonos_usuario[$usuario->name]=0;
    @endphp

<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Forma</th>
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $totales_forma=0; @endphp
                
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
                
                    <td class="letra-chica text-right">@php echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
             
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($totales_usuario[$usuario->name]>0){
                                echo number_format($totales_usuario[$usuario->name],0,',','.');
                                $total+=$totales_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
              
                <td class="text-right">
                    @php
                        if($notcred_total>0){
                            echo number_format($total,0,',','.')."<br> - ".number_format($notcred_total,0,',','.')."<br><b>".number_format(($total-$notcred_total),0,',','.')."</b>";
                        }else{

                            echo "<b>".number_format($total,0,',','.')."</b>";
                        }

                    @endphp
                </td>
            </tr>
    </tbody>
</table>
@php
    $totales_usuario[$usuario->name]=0;
@endphp
<br>
<h5>TRANSBANK RESUMEN</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Transbank</th>
        
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
     
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
                @if($forma->id==2 || $forma->id==5) <!-- tarj crédito o tarj débito -->
                    <tr>
                        <td class="letra-chica">{{$forma->formapago}}</td>
                        @php $totales_forma=0; $total=0;@endphp
                       
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
                        
                            <td class="letra-chica text-right">@php echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" @endphp</td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($totales_usuario[$usuario->name]>0){
                                echo number_format($totales_usuario[$usuario->name],0,',','.');
                                $total+=$totales_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
              
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
        
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $boletas_forma=0; @endphp
                
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
                
                <td class="letra-chica text-right">@php echo $boletas_forma>0?number_format($boletas_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
               
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($boletas_usuario[$usuario->name]>0){
                                echo number_format($boletas_usuario[$usuario->name],0,',','.');
                                $total_boletas+=$boletas_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
              
                <td class="text-right">@php echo "<b>".number_format($total_boletas,0,',','.')."</b>" @endphp</td>
            </tr>
    </tbody>
</table>

<h5>FACTURAS</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Forma</th>
        
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        
        <th class="text-center">Total</th>
    </thead>
    <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $facturas_forma=0; @endphp
                
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
               
                <td class="letra-chica text-right">@php echo $facturas_forma>0?number_format($facturas_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
               
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($facturas_usuario[$usuario->name]>0){
                                echo number_format($facturas_usuario[$usuario->name],0,',','.');
                                $total_facturas+=$facturas_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                
                <td class="text-right">@php echo "<b>".number_format($total_facturas,0,',','.')."</b>" @endphp</td>
            </tr>
    </tbody>
</table>

<h5>ABONOS</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th>Forma</th>
        
            <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
        
            <th class="text-center">Total</th>
        </thead>
        <tbody>
            @foreach($formas_pago as $forma)
            <tr>
                <td class="letra-chica">{{$forma->formapago}}</td>
                @php $abonos_forma=0; @endphp
                
                    <td class="text-right letra-chica">
                        <a href="javascript:detalle('ab','{{$usuario->id}}','{{$forma->id}}')">
                            @php
                            try {
                                $valor=$abonos[$usuario->name][$forma->formapago];
                                if($valor>0){
                                    echo number_format(intval($valor),0,',','.');
                                    $abonos_forma+=intval($valor);
                                    $abonos_usuario[$usuario->name]+=intval($valor);
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }
                                
                            @endphp
                        </a>
                    </td>
                
                <td class="text-right">@php echo $abonos_forma>0?number_format($abonos_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($abonos_usuario[$usuario->name]>0){
                                echo number_format($abonos_usuario[$usuario->name],0,',','.');
                                $total_abonos+=$abonos_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                
                <td class="text-right">@php echo "<b>".number_format($total_abonos,0,',','.')."</b>" @endphp</td>
            </tr>
        </tbody>
    </table>

<!-- Datos de vital importancia -->
<input type="hidden" name="total_boletas" id="total_boletas" value="{{$total_boletas}}">
<input type="hidden" name="total_facturas" id="total_facturas" value="{{$total_facturas}}">
<input type="hidden" name="total_nc" id="total_nc" value="{{$total_nc}}">
<input type="hidden" name="total_transbank" id="total_transbank" value="{{$total}}">