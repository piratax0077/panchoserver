@if(isset($repuesto[0]))
<tr>
    <td>{{$repuesto[0]->descripcion}}</td>
    <td>{{$repuesto[0]->stock_actual}}</td>
    <td>{{$repuesto[0]->nombrefamilia}}</td>
    <td>{{$repuesto[0]->observaciones}}</td>
    <td>{{$repuesto[0]->nombre_pais}}</td>
    <td>{{$repuesto[0]->empresa_nombre_corto}}</td>
    <td>${{$repuesto[0]->precio_venta}}</td>
</tr>
@else
    <tr>
        <td>Error ... codigo de repuesto no encontrado</td>
        <td></td>
    </tr>
@endif