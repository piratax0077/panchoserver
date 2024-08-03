<table class="table">
    <thead class="thead-dark">
        <th scope="col">N° de NC</th>
        <th scope="col">Repuesto</th>
        <th scope="col">Cantidad</th>
    </thead>
    <tbody>
        @foreach($nc_detalle as $d)
        <tr>
            <td>{{$nc->num_nota_credito}}</td>
            <td>{{$d->descripcion}}</td>
            <td>{{$d->cantidad}}</td>
        </tr>
        @endforeach
    </tbody>
</table>