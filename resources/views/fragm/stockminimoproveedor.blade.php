<p>Tiene un total de {{count($repuestos)}} repuestos con stock mínimo.</p>
<table class="table table-striped">
    <thead>
        <tr>
          <th scope="col">Código Interno</th>
          <th scope="col">Proveedor</th>
          <th scope="col">Cod. Rep. Proveedor</th>
          
          <th scope="col">Descripción</th>
          <th scope="col">Medidas</th>
          <th scope="col">Marca</th>
          <th scope="col">Origen</th>
          <th scope="col">Stock</th>
          <th scope="col" style="width: 50px;">Estado</th>
          <th scope="col"></th>
        </tr>
      </thead>
    <tbody>
        @foreach($repuestos as $rep)
        @php
          if($rep->estado == 'Pedido'){
            $clase = 'bg-success';
          }else if($rep->estado == 'Rechazado'){
            $clase = 'bg-danger';
          }else if($rep->estado == 'Sin stock en proveedor'){
            $clase = 'bg-info';
          }else if($rep->estado == 'En curso'){
            $clase = 'bg-warning';
          }else{
            $clase = 'bg-light';
          }
        @endphp
        <tr class="{{$clase}}">
            <td><a href="{{url('repuesto/modificar/'.$rep->id)}}" target="_blank" class="text-dark text-decoration-none">{{$rep->codigo_interno}}</a> </td>
            <td>{{$rep->empresa_nombre_corto}}</td>
            <td>{{$rep->cod_repuesto_proveedor}}</td>
            
            <td>{{$rep->descripcion}}</td>
            <td>{{$rep->medidas}}</td>
            <td>{{$rep->marcarepuesto}}</td>
            <td>{{$rep->nombre_pais}}</td>
            <td class="text-center">{{$rep->stock_actual + $rep->stock_actual_dos + $rep->stock_actual_tres}}</td>
            <td >{{$rep->estado}}</td>
            <td><a href="javascript:void(0)" onclick="damerepuesto({{$rep->id}})" class="btn btn-sm btn-secondary" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></button></td>
        </tr>
        @endforeach
    </tbody>
</table>