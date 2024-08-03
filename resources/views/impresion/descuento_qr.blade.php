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
    <div class="codigo_barras">
            {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
            {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
            <img src="data:image/png;base64,{{DNS2D::getBarcodePNG("panchorepuestos123","QRCODE")}}" alt="barcode" /><br>
    </div>
    <div id="detalle">
        
    </div>
</body>
</html>