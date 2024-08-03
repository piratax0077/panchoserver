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
            width: 100px;
        }
    </style>
</head>
<body>
    @php 
        $fechaActual = date('d-m-Y');
    @endphp
    <div class="contenedor">
        <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" alt="Logo" srcset="" class="logo_">
        <h5>VENTA DE REPUESTOS HYUNDAI - KIA</h5>
        <h5>ORIGINALES, ALTERNATIVOS Y DE OTRAS MARCAS</h5>
        <h5>+56990980457</h5>
        <h5>DIRECCIONES: RIQUELME 831</h5>
        <h4>N°{{$numero_boucher}}</h4>
        <div class="title">VALE POR MERCADERIA</div>
    </div>
    <div id="datos">
        <h5>Fecha: {{$fechaActual}}</h5>
        <h5>Rut del cliente: {{$rut}}</h5>
        <h5>Nombre del cliente: {{$nombre_cliente}}</h5>
        <h5>Telefono del cliente: {{$telefono}}</h5>
        <h5>Descripción del repuesto: {{$descripcion}}</h5>
        @if($tipo_doc == 'bo')
        <h5>Número de Boleta:{{$numero_documento}}</h5>
        @else
        <h5>Número de Factura:{{$numero_documento}}</h5>
        @endif
        <h5>Boucher: {{$numero_boucher}}</h5>
        <h5>Valor: ${{number_format($valor,0,',','.')}}</h5>
        <h5>Firma _________________________________________</h5>
    </div>
        
    
</body>
</html>