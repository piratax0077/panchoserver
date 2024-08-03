<!DOCTYPE html>
<html lang="es">
<head>
    <!-- OJO: mpdf no soporta bootstrap asi que estructurar con tablas o grilla css -->
    <title>NOTA CRÉDITO N° {{$doc_num}}</title>
    <style>

        p{
            margin-top:2px;
            margin-bottom: 2px;
        }

        .bordear1{
            border: 1px solid black;
        }

        .bordear2{
            border: 2px solid black;
        }

        .rut{
            border-collapse: collapse;
            width:8cm;
        }

        #datos{
            border-collapse: collapse;
            width:8cm;
        }


        .letra_freesans{
            font-family: 'freesans';
        }


        .letra_dejavu{
            font-family: 'dejavusanscondensed';
        }

        .letra_chikita{
            font-family: 'arialn';
            font-size:14px;
        }

        body{
            font-family:'arialn';
        }

        #datos td,th{
            vertical-align: text-top;
        }

    </style>
</head>
<body onload="formatear_rut();">
    <table class="rut">
        <tr>
        <th width="1cm" scope="col"></th>
        <th width="5cm" scope="col"></th>
        <th width="1cm" scope="col"></th>
    </tr>
        <tr>
            <td></td>
            <td class="bordear2" align="center">
                <p><strong>R.U.T. {{Session::get('PARAM_RUT')}}</strong></p>
                <p><strong>NOTA CRÉDITO</strong></p>
                <p><strong>ELECTRÓNICA</strong></p>
            <p><strong>N° {{$doc_num}}</strong></p>
            </td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                <strong>S.I.I - ARICA</strong><br>

            </td>
            <td></td>
        </tr>
    </table>
    <!--
        localhost problems / Vista 64 bit
        (https://mpdf.github.io/troubleshooting/blank-screen.html)
        ... To generate images and retrieve external stylesheets, mPDF needs to access files
         using fopen() etc. Changing localhost to 127.0.0.1 resolved the problem.

         Cuando esté en la nube, poner esto el acceso de img con asset storage imagenes logo png
    -->
    <table>
        <tr>
            <th width="2cm"></th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                <!-- cargarlo con el parámetro {{Session::get('PARAM_LOGO')}} -->
                <img src="file:///{{public_path('storage/imagenes/logo_pos.png')}}" width="150px" />
            </td>
        </tr>
    </table>
    <br>
    <strong>{{Session::get('PARAM_RAZ_SOC')}}</strong><br>
    <strong>Casa Matriz:</strong> {{Session::get('PARAM_DOM_MATRIZ')}}<br>
    <strong>Giro:</strong> {{Session::get('PARAM_GIRO')}}
    <strong>Sucursal:</strong>&nbsp;Principal<strong>&nbsp;&nbsp;Caja:</strong>&nbsp;N° 01<br>
    <br>
    <strong>
        @if($cliente->empresa=="---")
            Sin cliente
        @else
            @if($codigo_motivo!=2)
                {{strtoupper($cliente->empresa)}}
            @else
                @php
                    list($cliente_razon_social,$cliente_giro,$cliente_direccion,$cliente_comuna,$cliente_ciudad)=explode("*",$texto_modificado);
                @endphp
                {{$cliente_razon_social}}
            @endif
        @endif
    </strong><br>
    <strong>RUT:</strong>
    @php
        //Formatear el rut
        $r=trim($cliente->rut);
        $rut="";
        if(strlen($r)==8) $rut=substr($r,0,1).".".substr($r,1,3).".".substr($r,4,3)."-".substr($r,7,1);
        if(strlen($r)==9) $rut=substr($r,0,2).".".substr($r,2,3).".".substr($r,5,3)."-".substr($r,8,1);
        echo $rut."<br>";
    @endphp
    @if($codigo_motivo!=2)
        <strong>Giro:</strong> {{$cliente->giro}}<br>
        <strong>Dirección:</strong> {{$cliente->direccion}}<br>
        <strong>Comuna:</strong> {{$cliente->direccion_comuna}}&nbsp;<strong>Ciudad:</strong> {{$cliente->direccion_ciudad}}<br>
    @else
        <strong>Giro:</strong> {{$cliente_giro}}<br>
        <strong>Dirección:</strong> {{$cliente_direccion}}<br>
        <strong>Comuna:</strong> {{$cliente_comuna}}&nbsp;<strong>Ciudad:</strong> {{$cliente_ciudad}}<br>
    @endif
    <strong>Fecha de Emisión:</strong>&nbsp;{{$fecha_emision}}<br>
    @if($hay_referencia==1)
        <p align="center">___________________________________________</p>
        <p><strong>Doc. Referencia:</strong>&nbsp;{{$referencia}}</p>
        <p><strong>Folio:</strong>{{$referencia_folio}}&nbsp;&nbsp;<strong>Fecha:</strong>&nbsp;{{$referencia_fecha}}</p>
        <p><strong>Razón:</strong>&nbsp;{{$referencia_motivo}}</p>
    @endif
    @if($codigo_motivo!=2)
        <p align="center">___________________________________________</p>
        <table id="datos">
            <tr>
                <th width="6cm" scope="col" class="letra_chikita">ARTICULO</th>
                <th width="0.5cm" scope="col" class="letra_chikita">CANT</th>
                <th width="0.5cm" scope="col" class="letra_chikita">VALOR</th>
            </tr>
            @foreach ($carrito as $item)
            <tr>
                <td class="letra_chikita">{{$item->descripcion}}<br>($ {{number_format($item->pu,0,',','.')}} c/u)</td>
                <td align="center" class="letra_chikita">{{$item->cantidad}}</td>
                <td align="right" class="letra_chikita">{{number_format($item->total_item,0,',','.')}}</td>
            </tr>
            @endforeach
        </table>
        <p align="center">___________________________________________</p>
        <table align="right">
            <tr>
                <th width="3cm"></th>
                <th width="0.5cm"></th>
                <th width="1.5cm"></th>
            </tr>
            <tr>
                <td align="right"><strong>TOTAL NETO:</strong></td><td align="right">$</td><td align="right">{{number_format($neto,0,',','.')}}</td>
            </tr>
            <tr>
                <td align="right"><strong>IVA ({{Session::get('PARAM_IVA')*100}}&nbsp;%):</strong></td><td align="right">$</td><td align="right">{{number_format($iva,0,',','.')}}</td>
            </tr>
            <tr>
                <td align="right"><strong>TOTAL:</strong></td><td align="right">$</td><td align="right">{{number_format($total,0,',','.')}}</td>
            </tr>
        </table>
        <p align="center">___________________________________________</p>
    @endif
    <br>
    <p align="center"><img src="{{$timbre_url}}" width="300px" height="180px"></p>
    <br>
    <p align="center">Timbre Electrónico SII<br>
    {{Session::get('PARAM_RESOLUCION')}}<br>
    Verifique documento en {{Session::get('PARAM_WEB')}}</p>
</body>
</html>
