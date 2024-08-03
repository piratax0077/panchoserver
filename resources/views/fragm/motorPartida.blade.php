@if($datos->count() > 0)
<table class="table">
    <thead>
        <th scope="col">Motor Partida</th>
        <th scope="col"></th>
    </thead>
    <tbody>
        @foreach($datos as $d)
        <tr>
            <td>{{$d->motor}}</td>
            <td><button class="btn btn-danger btn-sm" onclick="borrarmotor({{$d->id}})">X</button></td>
        </tr>
    @endforeach
    </tbody>
</table>
@else
<p>No hay registros</p>
@endif

