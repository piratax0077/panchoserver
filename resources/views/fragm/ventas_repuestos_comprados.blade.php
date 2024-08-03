@if($compras->count()>0)
<table class="table table-bordered table-sm">
    <thead>
        <th width="15%" scope="col" class="letra_pequeña">FECHA</th>
        <th width="15%" scope="col" class="letra_pequeña">USUARIO</th>
        <th width="15%" scope="col" class="letra_pequeña">FACTURA</th>
        <th width="15%" scope="col" class="letra_pequeña">PRECIO COMPRA</th>
        <th width="15%" scope="col" class="letra_pequeña">PRECIO VENTA</th>
        <th width="15%" scope="col" class="letra_pequeña">FLETE</th>
        <th width="10%" scope="col" class="letra_pequeña">CANTIDAD</th>
    </thead>
    <tbody>
        @foreach($compras as $item)
            <tr>
                <td class="letra_pequeña">{!!\Carbon\Carbon::parse($item->fecha)->format("d-m-Y")!!}</td>
                <td class="letra_pequeña">{{$item->name}}</td>
                <td class="letra_pequeña">{!!$item->numero!!}</td>
                <td class="letra_pequeña">{!!number_format($item->precio_compra,0,',','.')!!}</td>
                <td class="letra_pequeña"><b>{!!number_format($item->precio_venta,0,',','.')!!}</b></td>
                <td class="letra_pequeña">{!!number_format($item->costos,0,',','.')!!}</td>
                <td class="letra_pequeña">{!!number_format($item->cantidad,0,',','.')!!}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
    <h3><b>NO HAY DATOS...</b></h3>
@endif
