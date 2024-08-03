<!DOCTYPE html>
<html lang="es">
<head>
    <!-- OJO: mpdf no soporta bootstrap asi que estructurar con tablas o grilla css -->
    <title>Arqueo</title>
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

        .firma{
            margin-top: 70px;
        }

    </style>
</head>
<body>
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
                <p><strong>ARQUEO ELECTRÓNICO</strong></p>
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
    <table>
        <tr>
            <th width="2cm"></th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                <!-- cargarlo con el parámetro {{Session::get('PARAM_LOGO')}} -->
                <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" width="100px" alt="logo" />
            </td>
        </tr>
    </table>

    <br>
    <strong>{{Session::get('PARAM_RAZ_SOC')}}</strong><br>
    <strong>Dirección:</strong> {{Session::get('PARAM_DOM_MATRIZ')}}<br>
    <strong>Giro:</strong> {{Session::get('PARAM_GIRO')}}
    <strong>Sucursal:</strong>&nbsp;Principal<strong>&nbsp;&nbsp;Caja:</strong>&nbsp;N° 01<br><strong>&nbsp;Fecha:</strong>&nbsp;{{date("Y/m/d")}}<br>
    <div class="bordear2">
        <p>Boletas: $ {{number_format($total_boletas,0,',','.')}}</p>
        <p>Facturas: $ {{number_format($total_facturas,0,',','.')}}</p>
        <p>Transbank: $ {{number_format($total_transbank,0,',','.')}}</p>
    </div>
    
    <div class="firma">
        <p align="center">___________________________________________</p>
        <p align="center">Marveise Albarracin</p>
    </div>
    
</body>
</html>