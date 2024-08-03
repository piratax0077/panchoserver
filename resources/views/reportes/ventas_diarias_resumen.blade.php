<h5>TOTALES</h5>
    @php
        $totales_forma=0;
        $boletas_forma=0;
        $facturas_forma=0;
        $abonos_forma=0;
        $total=0;
        $total_boletas=0;
        $total_facturas=0;
        $total_transbank=0;
        $total_getnet=0;
        $total_abonos = 0;
        $total_bc = 0;
        $total_fc = 0;
        $total_nc=0;
        $total_rechazados=0;
        $total_delivery_pendientes=0;
        $total_delivery_pagado=0;
        $boletas_credito_forma=0;
        $facturas_credito_forma=0;
    @endphp
@foreach($usuarios as $usuario)
    @php
        $totales_usuario[$usuario->name]=0;
        $boletas_usuario[$usuario->name]=0;
        $facturas_usuario[$usuario->name]=0;
        $abonos_usuario[$usuario->name]=0;
        $getnet_usuario[$usuario->name]=0;
        //$boletas_credito[$usuario->name]=0;
        //$facturas_credito[$usuario->name]=0;
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
                    <td class="letra-chica text-right">
                        @if($forma->formapago == "Efectivo")
                            <a href="javascript:void(0)" onclick="calcularValores({{$totales_forma}})" data-toggle="modal" data-target="#exampleModal">
                                @php echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" @endphp 
                            </a>
                        @else
                        @php 
                            echo $totales_forma>0?number_format($totales_forma,0,',','.'):"" 
                        @endphp
                        @endif
                        
                    </td>
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
                        }else{

                            echo "<b>".number_format($total,0,',','.')."</b>";
                        }

                    @endphp
                </td>
            </tr>
    </tbody>
</table>
@foreach($usuarios as $usuario)
    @php
        $totales_usuario[$usuario->name]=0;
    @endphp
@endforeach
<br>
<h5>BANCO ESTADO RESUMEN</h5>
<table class="table table-sm table-hover table-bordered">
    <thead>
        <th>Banco Estado</th>
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
                                        $valor=$transbank[$usuario->name][$forma->formapago];
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
<h5>GETNET RESUMEN</h5>

    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th>Getnet</th>
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
                    @php $getnet_forma=0; $total_getnet=0;@endphp
                    @foreach($usuarios as $usuario)
                        <td class="text-right letra-chica">
                                @php
                                    $valor_getnet=$getnet[$usuario->name][$forma->formapago];
                                    if($valor_getnet>0){
                                        echo number_format(intval($valor_getnet),0,',','.');
                                        $getnet_forma+=intval($valor_getnet);
                                        $getnet_usuario[$usuario->name]+=intval($valor_getnet);
                                    }
                                @endphp
                        </td>
                    @endforeach
                        <td class="letra-chica text-right">@php echo $getnet_forma>0?number_format($getnet_forma,0,',','.'):"" @endphp</td>
                </tr>
            @endif
        @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($getnet_usuario[$usuario->name]>0){
                                echo number_format($getnet_usuario[$usuario->name],0,',','.');
                                $total_getnet+=$getnet_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_getnet,0,',','.')."</b>" @endphp</td>
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
<input type="hidden" name="totalNc" id="totalNc" value="{{$total_nc}}">
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

@if($abonos_realizados->count() > 0)
    <h5>ABONOS</h5>
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
                @php $abonos_forma=0; @endphp
                @foreach($usuarios as $usuario)
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
                @endforeach
                <td class="text-right">@php echo $abonos_forma>0?number_format($abonos_forma,0,',','.'):"" @endphp</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($abonos_usuario[$usuario->name]>0){
                                echo number_format($abonos_usuario[$usuario->name],0,',','.');
                                $total_abonos+=$abonos_usuario[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_abonos,0,',','.')."</b>" @endphp</td>
            </tr>
        </tbody>
    </table>
@endif

@if($boletas_a_credito->count() > 0)
    <h5>BOLETAS A CRÉDITO</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th>Forma</th>
            @foreach($usuarios as $usuario)
                <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
            @endforeach
            <th class="text-center">Total</th>
        </thead>
        <tbody>
            
            <tr>
                <td class="letra-chica">Crédito</td>
                @php $bc_forma=0; @endphp
                @foreach($usuarios as $usuario)
                    <td class="text-right letra-chica">
                        <a href="javascript:detalle('bo_credito','{{$usuario->id}}','1')">
                            @php
                            try {
                                $valor=$boletas_credito[$usuario->name];
                                if($boletas_credito[$usuario->name]>0){
                                    $bc_forma+=intval($boletas_credito[$usuario->name]);
                                    echo number_format(intval($boletas_credito[$usuario->name]),0,',','.');
                                    
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }
                                
                            @endphp
                        </a>
                    </td>
               
                
                @endforeach
                <td class="text-right">@php echo $bc_forma>0?number_format($bc_forma,0,',','.'):"" @endphp</td>
            </tr>
            
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($boletas_credito[$usuario->name]>0){
                                echo number_format($boletas_credito[$usuario->name],0,',','.');
                                $total_bc+=$boletas_credito[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_bc,0,',','.')."</b>" @endphp</td>
            </tr>
        </tbody>
    </table>
@endif

@if($facturas_a_credito->count() > 0)
    <h5>FACTURAS A CRÉDITO</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th>Forma</th>
            @foreach($usuarios as $usuario)
                <th scope="col" class="text-center letra-chica">{{$usuario->name}}</th>
            @endforeach
            <th class="text-center">Total</th>
        </thead>
        <tbody>
            
            <tr>
                <td class="letra-chica">Crédito</td>
                @php $fc_forma=0; @endphp
                @foreach($usuarios as $usuario)
                    <td class="text-right letra-chica">
                        <a href="javascript:detalle('fa_credito',{{$usuario->id}},'1')">
                            @php
                           try {
                                $valor=$facturas_credito[$usuario->name];
                               
                                if($facturas_credito[$usuario->name]>0){
                                    $fc_forma+=intval($facturas_credito[$usuario->name]);
                                    echo number_format(intval($facturas_credito[$usuario->name]),0,',','.');
                                    
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }
                                
                            @endphp
                        </a>
                    </td>
                @endforeach
                <td class="text-right">@php echo $fc_forma>0?number_format($fc_forma,0,',','.'):"" @endphp</td>
            </tr>
            
            <tr>
                <td style="background-color: rgb(219, 219, 255)"><b>TOTAL</b></td>
                @foreach($usuarios as $usuario)
                    <td class="text-right" style="background-color: rgb(219, 219, 255)">
                        @php
                            if($facturas_credito[$usuario->name]>0){
                                echo number_format($facturas_credito[$usuario->name],0,',','.');
                                $total_fc+=$facturas_credito[$usuario->name];
                            }
                        @endphp
                    </td>
                @endforeach
                <td class="text-right">@php echo "<b>".number_format($total_fc,0,',','.')."</b>" @endphp</td>
            </tr>
        </tbody>
    </table>
@endif

@if($delivery_pendientes->count()>0)
    <h5>DELIVERYS PENDIENTES</h5>
    <table class="table table-sm table-hover table-bordered">
        <thead>
            <th class="text-center">Fecha</th>
            <th>Documento</th>
            <th>Cliente</th>
            <th class="text-center">Total</th>
            <th></th> <!-- Boton pagar -->
            <th>Usuario</th>
        </thead>
        <tbody>
                @foreach($delivery_pendientes as $dp)
                @php
                    $total_delivery_pendientes+=$dp->totaldoc;
                @endphp
                <tr>
                    <td class="text-center">{{\Carbon\Carbon::parse($dp->fechadoc)->format("d-m-Y")}}</td>
                    @if(substr($dp->xmldoc,0,2)=='39')
                        <td><a href="javascript:imprimir_xml('{{$dp->xmldoc}}')">Boleta N° {{$dp->numdoc}}</a></td>
                    @else
                        <td><a href="javascript:imprimir_xml('{{$dp->xmldoc}}')">Factura N° {{$dp->numdoc}}</a></td>
                    @endif
                    @if ($dp->rut=="60803000K")
                        <td>Sin Cliente</td>
                    @else
                        @if ($dp->tipo_cliente==0)
                            <td>{{$dp->nombres}} {{$dp->apellidos}}</td>
                        @else
                            <td>{{$dp->razon_social}}</td>
                        @endif
                    @endif

                    <td class="text-right">{{number_format($dp->totaldoc,0,',','.')}}</td>
                    <td class="text-center"><button class="btn btn-sm btn-success" onclick="pedir_clave('{{substr($dp->xmldoc,0,2)}}_{{$dp->iddoc}}_{{$dp->numdoc}}_{{$dp->totaldoc}}_{{$dp->id_cliente}}')">PAGAR</button></td>
                    <td>{{$dp->usuario}}</td>
                </tr>
                @endforeach
                <tr style="background-color: rgb(219, 219, 255)">
                    <td></td>
                    <td></td>
                    <td class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{number_format($total_delivery_pendientes,0,',','.')}}</strong></td>
                    <td></td>
                    <td></td>
                </tr>

        </tbody>
    </table>
    <br>
@endif

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Deglose del efectivo</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Total Efectivo</th>
                    <th scope="col">Total Notas de Crédito</th>
                    <th scope="col">Total</th>
                  </tr>
                </thead>
                <tbody id="tbodyTotalEfectivo">
                  
                </tbody>
              </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function calcularValores(totalEfectivo){
    
        let totalNc = $('#totalNc').val();
        let resultado = parseInt(totalEfectivo) - parseInt(totalNc);

        $('#tbodyTotalEfectivo').empty();
        $('#tbodyTotalEfectivo').append(`
            <tr>
                <td>$ `+parseInt(totalEfectivo).toLocaleString()+` </td>
                <td>$ `+parseInt(totalNc).toLocaleString()+` </td>
                <td>$ `+resultado.toLocaleString()+` </td>
            </tr>
        `    
        );
        console.log(resultado);
    }
  </script>
