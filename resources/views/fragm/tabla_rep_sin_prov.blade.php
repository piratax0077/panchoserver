<div id="resultado" class="tabla-scroll-y-500">
    <table class="table letra_pequeña">
        <thead>
          <tr>
            <th scope="col">Cod Int</th>
            <th scope="col">Descripción</th>
            <th scope="col">Proveedor</th>
            <th scope="col">Familia</th>
            <th scope="col">Estado</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($repuestos as $r)
            <tr>
                <td>{{$r->codigo_interno}}</td>
                <td>{{$r->descripcion}}</td>
                <td>{{$r->empresa_nombre_corto}}</td>
                <td>{{$r->nombrefamilia}}</td>
                <td>{{$r->estado}}</td>
                <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
            </tr>
          @endforeach
        </tbody>
    </table>
</div>