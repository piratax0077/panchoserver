<div class="tabla-scroll-y-300">
  <table id="tbl_repuestos" class="table table-sm table-hover table-striped">
    <thead>
    <th width="4%" scope="col" class="text-center">Id</th>
    <th width="4%" scope="col" class="text-center">Cod Int</th>
      <th width="10%" scope="col" class="text-center">Cod Rep Prov</th>
      <th width="12%" scope="col" class="text-center">Proveedor</th>
      <th width="35%" scope="col" class="text-center">Descripción</th>
      <th width="10%" scope="col" class="text-center">Marca Repuesto</th>
      <th width="10%" scope="col" class="text-center">Origen</th>
      <th width="7%" scope="col" class="text-center">Pr.Venta</th>
      <th width="7%"></th> <!-- VER DETALLE en ventana MODAL-->
      <th width="7%"></th> <!-- MODIFICAR -->
    </thead>
    <tbody>
    @foreach ($repuestos as $repuesto)
    <tr>
        <td class="letra-chica text-center">
          @if(Auth::user()->rol->nombrerol === "Administrador") 
          <a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank">{{$repuesto->id}}</a>
          @else()
          {{$repuesto->id}}
          @endif
        </td>
        <td class="letra-chica text-center">{{$repuesto->codigo_interno}} </td>
      <td class="letra-chica text-center">{{$repuesto->cod_repuesto_proveedor}}</td>
      <td class="letra-chica text-center">{{$repuesto->proveedor->empresa_nombre}}</td>
    <td class="letra-chica text-center">{{$repuesto->descripcion}} Med: {{$repuesto->medidas}}</td>
      <td class="letra-chica text-center">{{strtoupper($repuesto->marcarepuesto->marcarepuesto)}}</td>
      <td class="letra-chica text-center">{{$repuesto->pais->nombre_pais}}</td>
      <td class="letra-chica text-center"><strong><p id="precio_venta">{{$repuesto->precio_venta}}</p></strong></td>

<td><button class="btn btn-warning btn-sm" onclick="mas_detalle({{$repuesto->id}})">Más Detalle</button>
<!--
          <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#detalle-modal">Más Detalle</a>
-->
      </td>
      <td>
        @if(Auth::user()->rol->nombrerol === "Administrador") 
          <a href="{{url('repuesto/'.$repuesto->id.'/modificar')}}" class="btn btn-primary btn-sm">Modificar</a>
        @endif
        
      </td>
    </tr>
    @endforeach
    </tbody>
  </table>
</div>

<div class="row">
  <div class="col-md-3">
    <div id="zona_fotos_r">
    
    </div>
  </div>
  <div class="col-md-5">
    <div id="zona_similares_r">
    
    </div>
  </div>
  <div class="col-md-2">
    <div id="zona_oem_r">

    </div>
  </div>
  <div class="col-md-2">
    <div id="zona_fab_r">
      
    </div>
  </div>
</div>
  