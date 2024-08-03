<table class="table">
    <thead>
      <tr>
        <th>Proveedor</th>
        <th>N° Factura</th>
        <th>Subtotal</th>
        <th>IVA</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody id="tabla_facturas">
        @if($cabecera)
        <tr>
            <td>{{$cabecera->empresa_nombre_corto}}</td>
            <td>{{$cabecera->factura_numero}}</td>
            <td>{{$cabecera->factura_subtotal}}</td>
            <td>{{$cabecera->factura_iva}}</td>
            <td>{{$cabecera->factura_total}}</td>
        </tr>
        @else
        <tr>
            <td colspan="6">Error</td>
        </tr>
        @endif
    </tbody>
  </table>