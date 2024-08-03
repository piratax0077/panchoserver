<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .logo{
            width: 30%;
            margin: 0px auto;
            margin-bottom: 10px;
        }
        .codigo_barras, .codigo_interno{
            width: 100%;
            text-align: center;
            margin-bottom: 5px;
        }
        #detalle{
            width: 60%;
            margin: 0px auto;
            text-align: center;
        }
        table{
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="logo" >
        <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_negro_200.jpeg" alt="">
    </div>
    <div class="codigo_barras">
            {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
            {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($num_abono,"C128",1.4,22)}}" alt="barcode" /><br>
    </div>
    <div id="detalle">
        <table border="1">
            <tr>
                <th>Saldo pendiente</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>${{number_format($saldo_pendiente);}}</td>
                <td>${{number_format($total);}}</td>
            </tr>
        </table>
    </div>
</body>
</html>