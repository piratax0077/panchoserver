<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vale por mercadería</title>
    <style>
        *{
            margin:0px;
            padding: 0px;
        }
        .contenedor{
            text-align: center;
            width: 90%;
            margin: 0px auto;
            
        }

        #datos{
            width: 90%;
            margin: 10px auto;
           
        }

        strong{
            font-size: 35px;
        }

        .title{
            border-bottom: 1px solid black;
            font-weight: bold;
            font-style: italic;
        }

        .logo_{
            width: 150px;
        }
    </style>
</head>
<body>
    @php 
        $fechaActual = date('d-m-Y');
    @endphp
    <div class="contenedor">
        
        <h5>VENTA DE REPUESTOS HYUNDAI - KIA</h5>
        <h5>ORIGINALES, ALTERNATIVOS Y DE OTRAS MARCAS</h5>
        <h5>+56990980457</h5>
        <h5>DIRECCIONES: V. MACKENNA 1048</h5>
        <h4>N°{{$vm['numero_boucher']}}</h4>
        <h5>CLIENTE {{$vm['nombre_cliente']}}</h5>
        <div class="title">VALE POR MERCADERIA</div>
    </div>
    <div id="datos">
        <h5>Fecha: {{$fechaActual}}</h5>
        <h5>Total Vale de Mercadería: ${{number_format($vm['valor'],0,',','.')}}</h5>
        @php
            $total = 0;
        @endphp
        <table>
            <tr>
              <th>Descripcion</th>
              <th>Cantidad</th>
              <th>Origen</th>
              <th>Total</th>
            </tr>
            @foreach($detalles_vale_mercaderia as $d)
            @php
            $total += ($d['cantidad'] * $d['precio_venta']);
            @endphp
            <tr>
              <td>{{$d['codigo_interno']}}</td>
              <td>{{$d['cantidad']}}</td>
              <td>{{$d['local_nombre']}}</td>
              <td>{{number_format(($d['cantidad'] * $d['precio_venta']),0,',','.')}}</td>
            </tr>
            @endforeach
          </table> 
          <h5>Total Repuestos: ${{number_format($total,0,',','.')}}</h5>
          <h5>Diferencia: ${{number_format(($vm['valor'] - $total),0,',','.')}}</h5>
    </div>
        
    
</body>
</html>