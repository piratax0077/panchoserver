
<table class="table">
    <thead class="thead-dark">
        <tr>
            <th scope="col">Código interno</th>
            <th scope="col">Código Proveedor</th>
            <th scope="col">Repuesto</th>
            <th scope="col">Stock actual</th>
            
            <th scope="col">Precio Venta </th>
            
        </tr>
    </thead>
    <tbody>
        @php
            $total = $repuestos->sum('precio_venta');
        @endphp
        <h4 class="alert-success">Valorización: $ {{number_format($total)}}</h4>
        @foreach($repuestos as $repuesto)
            <tr>
                <td>{{$repuesto->codigo_interno}}</td>
                <td>{{$repuesto->cod_repuesto_proveedor}}</td>
                <td>{{$repuesto->descripcion}}
                    @if($repuesto->local_id == $local_id && $repuesto->stock_actual < 2 && $repuesto->stock_actual > 0 || $repuesto->local_id_dos == $local_id && $repuesto->stock_actual_dos < 2 && $repuesto->stock_actual_dos > 0) 
                    <span class="badge badge-warning">Bajo stock</span>    
                    
                    @elseif($repuesto->local_id == $local_id && $repuesto->stock_actual == 0 || $repuesto->local_id_dos == $local_id && $repuesto->stock_actual_dos == 0)
                        <span class="badge bg-danger text-white">Sin stock</span>
                    @endif
                </td>
                <td>
                   @if($repuesto->local_id == $local_id) 
                    {{$repuesto->stock_actual}}
                    @elseif($repuesto->local_id_dos == $local_id)
                    {{$repuesto->stock_actual_dos}}
                    @elseif($repuesto->local_id_tres == $local_id)
                    {{$repuesto->stock_actual_tres}}
                   @endif
                </td>
                
                <td>$ {{number_format($repuesto->precio_venta)}}</td>
                
            </tr>
        @endforeach
        
    </tbody>
</table>