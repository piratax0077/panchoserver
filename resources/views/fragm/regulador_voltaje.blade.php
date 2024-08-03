@if($datos->count() > 0)
<table class="table">
    <thead>
        <th scope="col">Rectificador</th>
        <th scope="col">Alternador</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        @foreach($datos as $d)
        <tr>
            <td>{{$d->rectificador}}</td>
            <td>{{$d->alternador}}</td>
            <td><button class="btn btn-danger btn-sm" onclick="borrarrv({{$d->id}})">X</button></td>
        </tr>
    @endforeach
    </tbody>
</table>
@else
<p>No hay registros</p>
@endif

