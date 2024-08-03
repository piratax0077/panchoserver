@extends('plantillas.app')
  @section('titulo','Listar Proveedores')
  @section('javascript')
    <script type="text/javascript">
        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido  eliminado correctamente!! !');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }
    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Listar Proveedores</h4></center><br>
@endsection
  @section('contenido_ingresa_datos')
   @include('fragm.mensajes')
  <p><a href="{{ url('proveedor/create') }}" class="btn btn-warning btn-sm active" role="button">Nuevo Proveedor</a></p>
  @endsection

  @section('contenido_ver_datos')
    @if($proveedores->count()>0)
      <div class="container-fluid">
          <div class="row">
            <div class="col-12 tabla-scroll-y-300">
              <table class="table table-hover table-sm">
                <thead>
                  <th width="8%" scope="col"  class="text-center">Código</th>
                  <th width="6%" scope="col" class="text-center">Transp.</th>
                  <th width="15%" scope="col">Nombre</th>
                  <th width="5%" scope="col">Nombre Corto</th>
                  <th width="16%" scope="col">Dirección</th>
                  <th width="5%" scope="col">Web</th>
                  <th width="8%" scope="col">Teléfono</th>
                  <th width="8%" scope="col">Correo</th>
                  <th width="8%" scope="col">Vendedor</th>
                  <th width="8%" scope="col">Vend Correo</th>
                  <th width="8%" scope="col">Vend Teléfono</th>
                  <th width="5%" scope="col"></th> <!-- ELIMINAR -->
                </thead>
                @foreach ($proveedores as $proveedor)
                <tr>
                  <td class="letra-chica text-center">{{$proveedor->empresa_codigo}}</td>
                  <td class="letra-chica text-center">
                        @if($proveedor->es_transportista==1)
                            SI
                        @else
                            NO
                        @endif
                  </td>
                  <td class="letra-chica">{{$proveedor->empresa_nombre}}</td>
                  <td class="letra-chica">{{$proveedor->empresa_nombre_corto}}</td>
                  <td class="letra-chica">{{$proveedor->empresa_direccion}}</td>
                  <td class="letra-chica">{{$proveedor->empresa_web}}</td>
                  <td class="letra-chica">{{$proveedor->empresa_telefono}}</td>
                  <td class="letra-chica">{{$proveedor->empresa_correo}}</td>
                  <td class="letra-chica">{{$proveedor->vendedor_nombres}}</td>
                  <td class="letra-chica">{{$proveedor->vendedor_correo}}</td>
                  <td class="letra-chica">{{$proveedor->vendedor_telefono}}</td>
                  <td>
    				<a href="{{url('proveedor/'.$proveedor->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
      @else

    <div class="alert alert-danger">
      No hay Proveedores definidos
    </div>

      @endif
  @endsection
