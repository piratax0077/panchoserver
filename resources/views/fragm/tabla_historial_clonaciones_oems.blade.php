<table class="table">
    <thead>
        <tr>
            <th>Origen</th>
            <th>Destino</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($clonaciones as $historial)
        <tr>
            <td>{{$historial->codigo_interno_origen}}</td>
            <td>{{$historial->codigo_interno_destino}}</td>
            <td><button class="btn btn-danger btn-sm" onclick="deshacer_clonacion({{$historial->id}})">X</button></td>
        </tr>
        @endforeach
</table>