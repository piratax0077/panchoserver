@php
    $uno_iva=1+Session::get('PARAM_IVA');

    $boletas_neto=round($boletas_suma/$uno_iva,0);
    $boletas_iva=round($boletas_suma-$boletas_neto,0);
    $boletas_transbank_neto=round($boletas_transbank_suma/$uno_iva,0);
    $boletas_transbank_iva=round($boletas_transbank_suma-$boletas_transbank_neto,0);
    $boletas_efectivo_neto=$boletas_neto-$boletas_transbank_neto;
    $boletas_efectivo_iva=$boletas_iva-$boletas_transbank_iva;
    $boletas_efectivo_suma=$boletas_suma-$boletas_transbank_suma;
    $boletas_efectivo_cuantas=$boletas_cuantas-$boletas_transbank_cuantas;

    $facturas_neto=round($facturas_suma/$uno_iva,0);
    $facturas_iva=round($facturas_suma-$facturas_neto,0);
    $facturas_transbank_neto=round($facturas_transbank_suma/$uno_iva,0);
    $facturas_transbank_iva=round($facturas_transbank_suma-$facturas_transbank_neto,0);
    $facturas_efectivo_neto=$facturas_neto-$facturas_transbank_neto;
    $facturas_efectivo_iva=$facturas_iva-$facturas_transbank_iva;
    $facturas_efectivo_suma=$facturas_suma-$facturas_transbank_suma;
    $facturas_efectivo_cuantas=$facturas_cuantas-$facturas_transbank_cuantas;


    $notas_credito_neto=round($notas_credito_suma/$uno_iva,0);
    $notas_credito_iva=round($notas_credito_suma-$notas_credito_neto,0);

    $notas_debito_neto=round($notas_debito_suma/$uno_iva,0);
    $notas_debito_iva=round($notas_debito_suma-$notas_debito_neto,0);

    $total_neto=$boletas_neto+$facturas_neto;//+$notas_credito_neto+$notas_debito_neto;
    $total_iva=$boletas_iva+$facturas_iva;//+$notas_credito_iva+$notas_debito_iva;
    $total_suma=$boletas_suma+$facturas_suma;//+$notas_credito_suma+$notas_debito_suma;
@endphp
<p style="color:blue">Todos son documentos electrónicos</p>
<table class="table table-bordered table-hover table-sm">
    <thead>
        <th class="text-center" width='200px'>Documento</th>
        <th class="text-center" width='50px'>Cantidad</th>
        <th class="text-center">Neto</th>
        <th class="text-center">IVA</th>
        <th class="text-center">Total</th>
    </thead>
    <tbody>
        <tr>
            <td><a href="javascript:void(0);" onclick="dame_detalle(39)">Boleta (39)</a></td>
            <td class="text-center">{{$boletas_cuantas}}</td>
            <td class="text-right">{{number_format($boletas_neto,0,',','.')}}</td>
            <td class="text-right">{{number_format($boletas_iva,0,',','.')}}</td>
            <td class="text-right">{{number_format($boletas_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td class="transbank">Efectivo Boleta</td>
            <td class="text-center transbank">{{$boletas_efectivo_cuantas}}</td>
            <td class="text-right transbank">{{number_format($boletas_efectivo_neto,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($boletas_efectivo_iva,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($boletas_efectivo_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td class="transbank">Transbank Boleta (48)</td>
            <td class="text-center transbank">{{$boletas_transbank_cuantas}}</td>
            <td class="text-right transbank">{{number_format($boletas_transbank_neto,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($boletas_transbank_iva,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($boletas_transbank_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td><a href="javascript:void(0);" onclick="dame_detalle(33)">Factura (33)</a></td>
            <td class="text-center">{{$facturas_cuantas}}</td>
            <td class="text-right">{{number_format($facturas_neto,0,',','.')}}</td>
            <td class="text-right">{{number_format($facturas_iva,0,',','.')}}</td>
            <td class="text-right">{{number_format($facturas_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td class="transbank">Efectivo Factura</td>
            <td class="text-center transbank">{{$facturas_efectivo_cuantas}}</td>
            <td class="text-right transbank">{{number_format($facturas_efectivo_neto,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($facturas_efectivo_iva,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($facturas_efectivo_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td class="transbank">Transbank Factura (48)</td>
            <td class="text-center transbank">{{$facturas_transbank_cuantas}}</td>
            <td class="text-right transbank">{{number_format($facturas_transbank_neto,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($facturas_transbank_iva,0,',','.')}}</td>
            <td class="text-right transbank">{{number_format($facturas_transbank_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td><a href="javascript:void(0);" onclick="dame_detalle(61)">Nota de Crédito (61)</a></td>
            <td class="text-center">{{$notas_credito_cuantas}}</td>
            <td class="text-right">{{number_format($notas_credito_neto,0,',','.')}}</td>
            <td class="text-right">{{number_format($notas_credito_iva,0,',','.')}}</td>
            <td class="text-right">{{number_format($notas_credito_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td><a href="javascript:void(0);" onclick="dame_detalle(56)">Nota de Débito (56)</a></td>
            <td class="text-center">{{$notas_debito_cuantas}}</td>
            <td class="text-right">{{number_format($notas_debito_neto,0,',','.')}}</td>
            <td class="text-right">{{number_format($notas_debito_iva,0,',','.')}}</td>
            <td class="text-right">{{number_format($notas_debito_suma,0,',','.')}}</td>
        </tr>
        <tr>
            <td class="text-right"><b>TOTALES (bol+fac):</b></td>
            <td class="text-center"></td>
            <td class="text-right"><b>{{number_format($total_neto,0,',','.')}}</b></td>
            <td class="text-right"><b>{{number_format($total_iva,0,',','.')}}</b></td>
            <td class="text-right"><b>{{number_format($total_suma,0,',','.')}}</b></td>
        </tr>
    </tbody>
</table>
@php
    //Caso de documentos manuales
    if($mes==11 && $año==2020){
        echo "Boletas Manuales (35): Total Doc: 357 Neto: 9.566.303 IVA: 1.817.597 Total: 11.383.900<br>";
        echo "Transbank como boletas (48): Total Doc: 372 Neto: 3.951.849 IVA: 750.851 Total: 4.702.700<br><br><b>DECLARADO EN SII EL 18DIC2020</b><br>";
        echo "<img src='".asset('storage/imagenes/RCV_11_2020.png')."' width='750px'>";
    }

@endphp
