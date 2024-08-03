<!DOCTYPE html>
<html lang="es">
<head>
    <!-- OJO: mpdf no soporta bootstrap asi que estructurar con tablas o grilla css -->
    <title>BOLETA N° {{$doc_num}} panchoserver</title>
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

        .logo{
            width: 30%;
            margin: 0px auto;
        }
    </style>
</head>
<body>
    <div class="logo" >
        <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="">
    </div>
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
                <p><strong>BOLETA ELECTRÓNICA</strong></p>
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
    <strong>Dirección:</strong> {{Session::get('PARAM_DOM_MATRIZ')}}<br>
    <strong>Giro:</strong> {{Session::get('PARAM_GIRO')}}
    <strong>Sucursal:</strong>&nbsp;Principal<strong>&nbsp;&nbsp;Caja:</strong>&nbsp;N° 01<br>
    <strong>Fecha de Emisión:</strong>&nbsp;{{$fecha_emision}}<br>
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
    <strong>Forma de Pago:</strong>&nbsp;Efectivo<br>
    <p align="right"><strong>TOTAL NETO:</strong>$ {{number_format($neto,0,',','.')}}</p>
    <p align="right"><strong>IVA ({{Session::get('PARAM_IVA')*100}}&nbsp;%):</strong>$ {{number_format($iva,0,',','.')}}</p>
    <p align="right"><strong>TOTAL:</strong>$ {{number_format($total,0,',','.')}}</p>
    <p align="center">___________________________________________</p>
    <br>
    <p align="center"><img src="{{$timbre_url}}" width="250px" height="200px"></p>
    <br>
    <p align="center">Timbre Electrónico SII<br>
    {{Session::get('PARAM_RESOLUCION')}}<br>
    Verifique documento en {{Session::get('PARAM_WEB')}}</p>
</body>
</html>
