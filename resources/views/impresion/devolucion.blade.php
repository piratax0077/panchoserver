<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laravel Generate Barcode Examples</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<style>
    
    .contenedor{
        width: 90%;
        margin:10px auto;
        border: 1px solid black;
    }
    .logo{
        width: 30%;
        margin: 0px auto;
    }

    .codigo_barras, .codigo_interno{
        width: 100%;
        text-align: center;
    }
    .descripcion{
        font-weight: bold;
        text-transform: uppercase;
        font-size: 9px;
        text-align: center;
    }

    .codigo_interno{
        font-size: 8px;
    }

    .header{
        background: #000;
        color: #fff;
    }
</style>
<body>
    @php 
        $fechaActual = date('d-m-Y');
    @endphp
    <div class="logo" >
        <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="">
    </div>
    <p align="center"><strong>DEVOLUCION DOCUMENTO N° {{$num_nc}}</strong></p>
    
    <p align="center"><strong>{{$fechaActual}}</strong></p>
    <div class="contenedor">
        <table style="width:100%" border="1">
            <tr>
              <th>Codigo Interno</th>
              <th>Cantidad</th>
              <th>Destino</th>
              <th>Autor</th>
            </tr>
            @foreach($devoluciones as $d)
            <tr>
              <td>{{$d->codigo_interno}}</td>
              <td>{{$d->cantidad}}</td>
              <td>{{$d->local_nombre}}</td>
              <td>{{$d->name}}</td>
            </tr>
            @endforeach
          </table>
    </div>
        
    
</body>
</html>