<table class="table">
    <thead class="thead-dark">
        <tr>
            <th scope="col">ID</th>
            <th scope="col">PROVEEDOR</th>
            <th scope="col">DIRECCION PROVEEDOR</th>
            <th scope="col">TELEFONO</th>
            <th scope="col">FECHA EMISION</th>
            <th scope="col">SUBTOTAL</th>
            
            <th scope="col">IVA</th>
            <th scope="col">TOTAL</th>
            <th scope="col">AUTOR</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="letra_pequeña">{{$cabecera->id}}</td>
            <td class="letra_pequeña">{{$cabecera->empresa_nombre}}</td>
            <td class="letra_pequeña">{{$cabecera->empresa_direccion}}</td>
            <td class="letra_pequeña">{{$cabecera->empresa_telefono}}</td>
            <td class="letra_pequeña">{{$cabecera->factura_fecha}}</td>
            <td class="letra_pequeña">${{number_format($cabecera->factura_subtotal,0,",",".")}}</td>
            <td class="letra_pequeña">${{number_format($cabecera->factura_iva,0,",",".")}}</td>
            <td class="letra_pequeña">${{number_format($cabecera->factura_total,0,",",".")}}</td>
            <td class="letra_pequeña">{{$cabecera->name}}</td>
        </tr>
    </tbody>
</table>