<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .contenedor{
            text-align: center;
            width: 90%;
            margin: 0px auto;
            
        }
        .logo{
            width: 30%;
            margin: 0px auto;
            margin-bottom: 10px;
        }
        .codigo_barras, .codigo_interno{
            width: 100%;
            text-align: center;
            margin-bottom: 5px;
            margin-top: 5px;
        }
        #detalle{
            width: 80%;
            margin: 0px auto;
            text-align: center;
        }
        table{
            font-size: 13px;
        }
    </style>
</head>
<body>
    @php 
        $fechaActual = date('d-m-Y');
    @endphp
    <div class="contenedor">
        <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" alt="Logo" srcset="" class="logo_">
        <p>{{$vale->fecha_emision}} {{ $vale->hora }}</p>
        <h5>VENTA DE REPUESTOS HYUNDAI - KIA</h5>
        <h5>ORIGINALES, ALTERNATIVOS Y DE OTRAS MARCAS</h5>
        <h5>+56990980457</h5>
        <h5>DIRECCIONES: RIQUELME 831</h5>
        <h4>N°{{$vale->num_consignacion}}</h4>
        <div class="title">VALE POR CONSIGNACION</div>
    </div>
    <hr>
    
    <div id="detalle">
       
        <table style="width:100%" border="1">
            <tr>
              <th>Descripcion</th>
              <th>Valor</th>
              <th>Cantidad</th>
              <th>Total</th>
            </tr>
            @foreach($detalle as $d)
            <tr>
              <td>{{$d['descripcion']}}</td>
                <td>${{number_format($d['pu'],0,',','.')}}</td>
              <td>{{$d['cantidad']}}</td>
              <td>${{number_format(($d['cantidad'] * $d['pu']),0,',','.')}}</td>
            </tr>
            @endforeach
          </table> 
    </div>
    <div class="codigo_barras">
        {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
        {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($num_consignacion,"C128",1.4,22)}}" alt="barcode" /><br>
</div>
</body>
</html>