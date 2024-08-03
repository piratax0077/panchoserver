<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Boleta de Venta</title>
    <style>
        @page{
            margin-top:1cm; 
            margin-left:1cm;
        }
        
        table {
            border-collapse: collapse;
            width:7cm;
        }
            
        table, td, th {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <p>DATOS DE CABECERA DE BOLETA</p>
    <p>BOLETA ELECTRÓNICA NUBE</p>
    <div align="center">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" width="100px" />
    </div> 
    
    <table>
        <tr>
            <th>CODIGO</th>
            <th>DESCRIPCION</th>
            <th>PRECIO</th>
        </tr>
        @foreach ($carrito as $item)
        <tr>
            <td>{{$item->codigo_interno}}</td>
            <td>{{$item->descripcion}}</td>
            <td>{{$item->total_item}}</td>
        </tr>
        @endforeach
    </table>
    <div style="page-break-after: always;">FIN</div>
</body>
</html>