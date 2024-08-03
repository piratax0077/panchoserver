@if($tipodte == 33)
    <p>Detalle de factura N° {{$boleta->num_factura}} con fecha {{$boleta->fecha_emision}} creada por {{$boleta->name}}</p>
@elseif($tipodte == 39)
    <p>Detalle de boleta N° {{$boleta->num_boleta}} con fecha {{$boleta->fecha_emision}} creada por {{$boleta->name}}</p>
@elseif($tipodte == 61)
    <p>Detalle de boleta N° {{$boleta->num_nota_credito}} con fecha {{$boleta->fecha_emision}} creada por {{$boleta->name}}</p>
@endif
<button class="btn btn-success btn-sm m-2" data-toggle="modal" data-target="#modalModificarBoleta">Modificar</button>
@if($boleta->es_credito == 1)
<input type="hidden" name="es_credito" id="es_credito" value="1">
@else
<input type="hidden" name="es_credito" id="es_credito" value="0">
@endif
<table class="table">
    <thead class="thead-dark">
        <tr>
          <th scope="col">Cód int</th>
          <th scope="col">Repuesto</th>
          <th scope="col">Cantidad</th>
          <th scope="col">P.U.</th>
          <th scope="col">Subtotal</th>
        </tr>
      </thead>
      <tbody>
          
            @foreach($boleta_detalle as $b)
            <tr>
                <td>{{$b->codigo_interno}}</td>
                <td>{{$b->descripcion}}</td>
                <td>{{$b->cantidad}}</td>
                <td>${{number_format($b->precio_venta)}}</td>
                <td>${{number_format($b->subtotal)}}</td>
            </tr>
            
            @endforeach
            <tr style="background: #353a40; color: #fff;">
                <td></td>
                <td></td>
                <td></td>
                <td>TOTAL</td>
                <td>${{number_format($boleta->total)}}</td>
            </tr>
        
      </tbody>
</table>
