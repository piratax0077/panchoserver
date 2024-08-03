<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pedidos/Abonos</title>
    <style>
        .contenedor{
            text-align: center;
            width: 90%;
            margin: 0px auto;
            
        }

        

        #datos , .info_cliente, #datos_finales{
            width: 95%;
            margin: 0px auto;
            padding-left: 1px;
        }

        #datos table{
            width: 97%;
            border: 1px solid black;
            text-align: center;
            padding-left: 1px;
        }

        table th{
            background: #000;
            color: #fff;
        }

        .title{
            border-bottom: 1px solid blue;
            font-weight: bold;
            font-style: italic;
        }


        .nombre, .telefono{
            width: 48%;
            border: 1px solid black;
            float: left;
        }


        #abono, #saldo_pendiente, #precio_lista{
            width: 32%;
            border: 1px solid black;
            float: left;
            text-align: center;
        }

        #abono h4, #saldo_pendiente h4, #precio_lista h4, #datos_finales p{
            background: #000;
            color: #fff;
        }
        .logo_{
            width: 150px;
        }
        li{
            font-size: 11px;
        }
    </style>
</head>
<body>
    @php 
        $fechaActual = date('d-m-Y');
    @endphp
    <div class="contenedor">
        
        <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" alt="Logo" class="logo_" />
        <p>{{$abono->fecha_emision}} {{$abono->hora}}</p>
        <h4>VENTA DE REPUESTOS HYUNDAI - KIA</h4>
        <h5>ORIGINALES, ALTERNATIVOS Y DE OTRAS MARCAS</h5>
        <h4>+56990980457</h4>
        <h4>DIRECCIONES: RIQUELME 831</h4>
        <h4>N°{{$abono->num_abono}}</h4>
    </div>
    <hr>
    <div class="info_cliente">
        <table border="1" style="width: 100%">
            <tr>
                <th>Nombre del cliente</th>
                <th>Telefono</th>
            </tr>
            <tr>
                <td>{{$abono->nombre_cliente}}</td>
                <td>{{$abono->telefono}} </td>
                
            </tr>
        </table>
        <table border="1" style="width: 100%">
        <tr>
            <th>Email</th>
        </tr>
        <tr>
            <td style="text-transform: uppercase">{{$abono->email}}</td>
        </tr>
        </table>
    </div>
    <div id="datos">
        <table border="1">
            <tr>
                <th>Cantidad</th>
                <th>Descripcion</th>
                <th>Precio unitario</th>
                <th>Total</th>
              </tr>
              @foreach($abono_detalle as $repuesto)
              <tr>
                  <td>{{$repuesto->cantidad}}</td>
                  <td>{{$repuesto->descripcion}}</td>
                  <td>${{number_format($repuesto->precio_unitario,0,',','.')}}</td>
                  <td>${{number_format($repuesto->total,0,',','.')}}</td>
              </tr>
              @endforeach
        </table>
     
    </div>
        
    <div id="datos_finales">
        <table border="1" style="width: 100%">
            <tr>
                <th>Abono</th>
                <th>Saldo pendiente</th>
                <th>Precio lista</th>
              </tr>
              <tr>
                  <td>${{number_format($abono->abono,0,',','.')}}  </td>
                  <td>${{number_format($abono->saldo_pendiente,0,',','.')}} </td>
                  <td>${{number_format($abono->precio_lista,0,',','.')}}</td>
              </tr>
        </table>
        
    </div>
    <div id="select">
        <p style="font-size: 13px;">Servicio por encargo: {{$abono->por_encargo}}</p>
        
        <p style="font-size: 13px;">Por cobrar: {{$abono->por_cobrar}}</p>
        
        <p style="font-size: 13px;">Condiciones de pedido:</p>
        <ul>
            <li>Tiene 30 días, desde el momento que se comunican con usted, para retirar el producto de
                nuestra bodega.</li>
            <li>Pasando los 30 días de corrido, si usted no hace el retiro correspondiente, se comenzará a
                cobrar bodegaje con un valor de $1.000 pesos diarios.</li>
            <li>Cumpliéndose los 90 días desde el aviso, quedara sin efecto el pedido solicitado, sin
                devolución del dinero.</li>
            <li>
                Todos los pedidos se cancelarán  en efectivo y/o transferencia. No se aceptarán tarjetas bancarias 
                ( Débito y/o Crédito).
            </li>
        </ul>
        <p style="font-size: 13px; margin-top: 10px;">Nombre ________________________________</p>
        <p style="font-size: 13px; margin-top: 10px;">Rut ___________________________________</p>
        
        <p style="font-size: 13px; margin-top: 10px;">Firma _________________________________</p>
    </div>
</body>
</html>