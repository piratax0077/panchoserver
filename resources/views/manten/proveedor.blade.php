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

        function mostrarModalEditarProveedor(proveedor){
   
            console.log(proveedor);
            $('#modalEditarProveedor').modal('show');
            $('#id_proveedor').val(proveedor.id);
            $('#empresa_nombre').val(proveedor.empresa_nombre);
            $('#empresa_nombre_corto').val(proveedor.empresa_nombre_corto);
            $('#empresa_direccion').val(proveedor.empresa_direccion);
            $('#empresa_web').val(proveedor.empresa_web);
            $('#empresa_telefono').val(proveedor.empresa_telefono);
            $('#empresa_correo').val(proveedor.empresa_correo);
            $('#vendedor_nombres').val(proveedor.vendedor_nombres);
            $('#vendedor_correo').val(proveedor.vendedor_correo);
            $('#vendedor_telefono').val(proveedor.vendedor_telefono);
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
                  <th width="5%" scope="col"></th> <!-- ACCIONES -->
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
                  <td class="d-flex">
                    <!-- editar -->
                    <a href="javascript: void(0)" onclick="mostrarModalEditarProveedor({{ json_encode($proveedor)}})" class="btn btn-warning btn-sm">Editar</a>
                     <!-- eliminar -->
    				          <a href="{{url('proveedor/'.$proveedor->id.'/eliminar')}}" class="btn btn-danger btn-sm" onclick="return confirmacion()">Eliminar</a>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
          </div>
       </div>
       <!-- modal editar proveedor -->
        <div class="modal fade" id="modalEditarProveedor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Editar Proveedor</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <!-- FORMULARIO PARA EDITAR EL PROVEEDOR -->
                  <form method="POST" action="{{ url('proveedor/editar') }}">
                    @csrf
                    <input type="hidden" name="id_proveedor" id="id_proveedor" value="0">
                    <div class="row">
                      <div class="col-6">
                        <label for="empresa_nombre">Nombre del Proveedor:</label>
                          <input type="text" name="empresa_nombre" value="{{old('empresa_nombre')}}"  id="empresa_nombre" class="form-control">
                      </div>
                      <div class="col-6">
                        <label for="empresa_nombre_corto">Nombre Corto:</label>
                          <input type="text" name="empresa_nombre_corto" value="{{old('empresa_nombre_corto')}}"  id="empresa_nombre_corto" class="form-control">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <label for="empresa_direccion">Dirección:</label>
                          <input type="text" name="empresa_direccion" value="{{old('empresa_direccion')}}"  id="empresa_direccion" class="form-control">
                      </div>
                      <div class="col-6">
                        <label for="empresa_web">Web:</label>
                          <input type="text" name="empresa_web" value="{{old('empresa_web')}}"  id="empresa_web" class="form-control">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <label for="empresa_telefono">Teléfono:</label>
                          <input type="text" name="empresa_telefono" value="{{old('empresa_telefono')}}"  id="empresa_telefono" class="form-control">
                      </div>
                      <div class="col-6">
                        <label for="empresa_correo">Correo:</label>
                          <input type="text" name="empresa_correo" value="{{old('empresa_correo')}}"  id="empresa_correo" class="form-control">
                      </div>
                    </div>
                    <!-- datos del vendedor -->
                    <hr>
                    <h5>Datos del Vendedor</h5><br>
                    <div class="row">
                      <div class="col-6">
                        <label for="vendedor_nombres">Nombres:</label>
                          <input type="text" name="vendedor_nombres" value="{{old('vendedor_nombres')}}"  id="vendedor_nombres" class="form-control">
                      </div>
                      <div class="col-6">
                        <label for="vendedor_correo">Correo:</label>
                          <input type="text" name="vendedor_correo" value="{{old('vendedor_correo')}}"  id="vendedor_correo" class="form-control">
                      </div>
                      
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <label for="vendedor_telefono">Teléfono:</label>
                          <input type="text" name="vendedor_telefono" value="{{old('vendedor_telefono')}}"  id="vendedor_telefono" class="form-control">
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
    @else

      <div class="alert alert-danger">
        No hay Proveedores definidos
      </div>

    @endif
  @endsection
