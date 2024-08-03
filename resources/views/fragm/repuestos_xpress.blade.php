@if($desde == 'i')  
    <h5 class="text-center">Busqueda de repuestos inactivos</h5>
@elseif($desde == 'd' || $desde=='p' || $desde=='m')
    <h5 class="text-center">Búsqueda de repuestos express</h5>
@endif
<div class="tabla-scroll-y-500 h-100 table-container">
    <table id="tbl_repuestos" class="table table-sm table-hover" width="100%">
    <thead class="sticky-top">
        @if($desde=='d' || $desde=='p' || $desde=='m' || $desde =='i')
            <th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
        @endif
        @if($desde=='o')
            <th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
            <th width="5%" scope="col" class="letra_pequeña">OEM</th>
        @endif
        @if($desde=='f')
            <th width="8%" scope="col" class="letra_pequeña">Cod Fab</th>
        @endif
      <th width="18%" scope="col" class="letra_pequeña">Descripción</th>
      
      <th width="6%" scope="col" class="letra_pequeña">Precio Venta</th>
      <th width="5%" scope="col" class="letra_pequeña">Stock total</th>
      <th width="5%" scope="col" class="letra_pequeña">Fecha Ingreso</th>
      @if($desde == 'i')<th width="5%" scope="col" class="letra_pequeña"></th>@endif
    </thead>
    <tbody>
        @foreach($repuestos as $rep)
        <tr>
            <td>@if($desde == 'i') <a href="/repuesto/modificar/{{$rep->id}}" target="_blank">{{$rep->codigo_interno}}</a>  @else {{$rep->codigo_interno}}  @endif</td>
            <td>{{$rep->descripcion}}</td>
            <td>$ {!!number_format($rep->precio_venta,0,',','.')!!}</td>
            <td>{{$rep->stock_actual + $rep->stock_actual_dos + $rep->stock_actual_tres}}</td>
            <td>{{$rep->created_at}}</td>
            @if($desde == 'i')<td><button class="btn btn-success btn-sm" onclick="activarRepuesto('{{$rep->codigo_interno}}')">Activar</button></td>@endif
        </tr>
        @endforeach
    </tbody>
    </table>
</div>