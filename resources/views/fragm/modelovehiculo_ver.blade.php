@if($modelos->count()>0)
<div class="container-fluid">
  <div class="row">
    <div class="col-8 col-sm-8 col-md-8 col-lg-8 tabla-scroll-y-400">
      <table class="table table-hover" id="tbl_modelos">
        <thead>
          <th width="10%" scope="col">Marca</th>
          <th width="60%" scope="col">Modelos <strong>({{$modelos->count()}})</strong></th>
          <th width="10%" scope="col">Imagen</th>
          <th width="10%" scope="col"></th> <!-- Modificar -->
          <th width="10%" scope="col"></th> <!-- Eliminar -->
        </thead>

        @foreach ($modelos as $modelo)
        <tr>
<!--
->marcamodelo es el método en el  modelo modelovehiculo
->marcanombre es el campo en el modelo marcavehiculo
-->
          <td>{{$modelo->marcavehiculo->marcanombre}}</td>
          <td>{{$modelo->modelonombre}}&nbsp;{{$modelo->anios_vehiculo}}
            @if($modelo->zofri==1)
              <b>(ZOFRI)</b>
            @endif
          </td>
          <td><img src="{{asset('storage/'.$modelo->urlfoto)}}" width="80px" /></td>
          <td>
            <button class="btn btn-warning btn-sm" onclick="modificarmodelo({{$modelo->id}})">Modificar</button>
          </td>
          <td>
              <button class="btn btn-danger btn-sm" onclick="eliminarmodelo({{$modelo->id}})">Eliminar</button>
          </td>
        </tr>
        @endforeach
      </table>
    </div>

  </div>
</div>
@else
  <div class="alert alert-danger">
    <p>No hay Modelos de Vehículos Ingresados.</p>
  </div>
@endif
