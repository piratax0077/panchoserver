<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>COTIZACIÓN N° {{$cab_cotizacion->num_cotizacion}}</title>
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
            font-size:10px;
        }

        body{
            font-family:'arialn';
        }

        table, th, td {
            /* border: 1px solid black; */
            border: 0px;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<br>
    <table>
        <tr>
            <th width="1cm"></th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                @php
                    $entorno=App::environment();
                @endphp
                @if($entorno=='local')
                    <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" width="200px" />
                @endif
                @if($entorno=='production')
                    <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" width="200px" />
                @endif


            </td>
        </tr>
    </table>

    <br>
    <p align="center"><strong>COTIZACION N° {{$cab_cotizacion->num_cotizacion}}</strong></p>
    <div style="margin-left:10px;margin-right:15px">
        <p><strong>CLIENTE:</strong>
            @php
                $rut="";
                if(isset($cliente->rut)){
                    //Formatear el rut
                    $r=trim($cliente->rut);
                    if(strlen($r)==8) $rut=substr($r,0,1).".".substr($r,1,3).".".substr($r,4,3)."-".substr($r,7,1);
                    if(strlen($r)==9) $rut=substr($r,0,2).".".substr($r,2,3).".".substr($r,5,3)."-".substr($r,8,1);
                }
                echo $rut."<br>";
            @endphp
        </p>
        <p>
        @if(isset($cliente->rut))
            @if($cliente->tipo_cliente==0)
                {{$cliente->nombres}} {{$cliente->apellidos}}</p>
            @else
                {{$cliente->empresa}}</p>
            @endif
        @else
            {{$cab_cotizacion->nombre_cotizacion}}</p>
        @endif
        <strong>Fecha de Emisión:</strong>&nbsp;{{\Carbon\Carbon::parse($cab_cotizacion->fecha_emision)->format("d-m-Y")}}<br>
    </div>
    <br>
    <table>
        <tr>
            <th scope="col" width="20px" style="padding-left:5px">Cant</th>
            <th scope="col" width="180px">Descripción</th>
            <th scope="col" width="30px">Precio</th>
        </tr>
        <tbody>
            @foreach($det_cotizacion as $item)
            <tr>
                <td align="center">{{$item->cantidad}}</td>
                <td>{{$item->descripcion}}</td>
                <td align="right">@if($item->oferta == 1)*@endif{{number_format($item->total,0,',','.')}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p align="right" style="margin-right:15px;"><strong>TOTAL:</strong>$ {{number_format($cab_cotizacion->total,0,',','.')}}</p>
    <br><br>
    <h5 style="margin-left:15px;margin-right:15px; text-align:center">
        ESTA COTIZACIÓN TIENE UNA VIGENCIA DE 07 DÍAS CALENDARIO A PARTIR DE LA FECHA DE EMISIÓN O HASTA AGOTAR STOCK
    </h5>
    @if(isset($oferta) && $oferta !== false)
    <h5 style="margin-left:15px;margin-right:15px; text-align:center; text-transform: uppercase;">(*)Productos en oferta hasta {{\Carbon\Carbon::parse($cab_cotizacion->fecha_emision)->format("d-m-Y")}}.</h5>
    @endif
    <h4 align="center">PANCHO REPUESTOS</h4>
    <br>
    
    
</body>
</html>
