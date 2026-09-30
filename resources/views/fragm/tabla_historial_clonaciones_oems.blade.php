<table class="table table-hover">
    <thead><tr><th>Origen</th><th>Destino</th><th></th></tr></thead>
    <tbody>
        @forelse($clonaciones as $historial)
        <tr>
            <td><strong>{{$historial->codigo_interno_origen}}</strong></td>
            <td><strong>{{$historial->codigo_interno_destino}}</strong></td>
            <td><button type="button" class="btn btn-outline-danger btn-sm oem-delete" onclick="deshacer_clonacion({{$historial->id}},this)" title="Eliminar clonación"><i class="fas fa-trash"></i></button></td>
        </tr>
        @empty
        <tr><td colspan="3" class="oem-empty"><i class="fas fa-inbox mr-1"></i> No hay clonaciones registradas</td></tr>
        @endforelse
    </tbody>
</table>