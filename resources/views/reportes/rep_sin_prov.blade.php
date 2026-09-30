@extends('plantillas.app')
@section('titulo','SIN STOCK PROVEEDOR')

@section('javascript')
    <script>
        function espere(){
            Vue.swal({
                title: 'Espere por favor',
                html: 'Procesando la información',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Vue.swal.showLoading()
                },
            });
        }

        function dameRepuestosProveedorSinStockProveedor(){
            Vue.swal({
                title: 'Cargando',
                text: 'Espere por favor',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    Vue.swal.showLoading()
                }
            });
        }

        function dameRepuestosFamiliaSinStockProveedor(){
            Vue.swal({
                title: 'Cargando',
                text: 'Espere por favor',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    Vue.swal.showLoading()
                }
            });
        }

        function damerepuesto(idRepuesto){
            // Guardamos el id del repuesto
            document.getElementById("id_repuesto").value = idRepuesto;

            var nuevaurl = "/repuesto/"+idRepuesto+"/damerepuesto";
            $.ajax({
                type:'get',
                url:nuevaurl,
                beforeSend: function(){
                espere();
                },
                success: function(html){
                    Vue.swal.close();
                    dameStockMinimo(idRepuesto);
                    $('#modalBodyNuevoStockMinimo').empty();
                    $('#modalBodyNuevoStockMinimo').append(html);
                },
                error: function(error){
                    console.log(error);
                }
            });
        }

        function dameStockMinimo(idRepuesto){
            let url = '/repuesto/buscaridrep/'+idRepuesto;
            $.ajax({
                type:'get',
                url: url,
                beforeSend: function(){
                    espere();
                },
                success: function(repuesto){
                    Vue.swal.close();
                    // 
                    // limpiar el input de stock mínimo
                    document.getElementById("nuevo_stock_minimo").value = repuesto.stock_minimo;
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

    function guardarNuevoStockMinimo(){
        var idRepuesto = document.getElementById("id_repuesto").value;
        var nuevoStockMinimo = document.getElementById("nuevo_stock_minimo").value;
        //recuperar el valor del select estado
        var estado = document.getElementById("estado").value;
        // validar que el stock mínimo no sea vacío
        if(nuevoStockMinimo == ""){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar un stock mínimo',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        var url = "{{url('/repuesto/guardar_nuevo_stock_minimo')}}"+"/"+idRepuesto+"/"+nuevoStockMinimo+"/"+estado;
        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(resp){
                console.log(resp);
                Vue.swal.close();

                if(resp.estado == 'OK'){

                    $('#modalNuevoStockMinimo').modal('hide');

                    // cambiar estado
                    $('#estado_'+resp.id).text(resp.nuevo_estado);

                    $('#mensaje').html('Se han encontrado '+resp.cantidad_repuestos+' repuestos.');
                    // si ya no debe estar en la lista → eliminar fila
                    if(resp.sigue == false){

                        $('#fila_'+resp.id).fadeOut(200,function(){
                            $(this).remove();
                        });

                    }

                }
                repuestos_sin_stock_proveedor();
            },
            error: function(error){
                console.log(error);
            }
        });
    }
    </script>
@endsection

@section('contenido_ingresa_datos')
<h4 class="titulazo">Repuestos sin stock proveedor</h4>
@php
            $año_actual=date("Y");
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <h4>Filtros</h4>
            <div class="form-group">
                <label for="proveedores" class="form-label">Proveedores</label>
                <select name="proveedores" id="proveedores" class="form-select form-control" onchange="dameRepuestosProveedorSinStockProveedor()">
                    <option value="0">Seleccione proveedor</option>
                    @foreach($proveedores as $proveedor)
                    <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
                    @endforeach
                </select>
            </div>
           
            <div class="form-group">
                <label for="proveedores" class="form-label">Familias</label>
                <select name="familias" id="familias" class="form-select form-control" onchange="dameRepuestosFamiliaSinStockProveedor()">
                    <option value="0">Seleccione familia</option>
                    @foreach($familias as $familia)
                    <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                    @endforeach
                </select>
            </div>
            <p id="mensaje">Se han encontrado {{count($repuestos) }} repuestos.</p>
            
            <hr>
            
        </div>
        <div class="col-md-9">
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
            
                    <tr id="fila_{{$r->id}}">
            
                        <td>
                            {{$r->codigo_interno}}
                        </td>
            
                        <td>
                            {{$r->descripcion}}
                        </td>
            
                        <td>
                            {{$r->empresa_nombre_corto}}
                        </td>
            
                        <td>
                            {{$r->nombrefamilia}}
                        </td>
            
                        <td id="estado_{{$r->id}}">
                            {{$r->estado}}
                        </td>
            
                        <td>
            
                            <a class="btn btn-secondary btn-sm"
                               href="javascript:void(0)"
                               onclick="damerepuesto({{$r->id}})"
                               data-target="#modalNuevoStockMinimo"
                               data-toggle="modal">
            
                                <i class="fa-solid fa-arrows-rotate"></i>
            
                            </a>
            
                        </td>
            
                    </tr>
            
                    @endforeach
            
                    </tbody>
            
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalNuevoStockMinimo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Nuevo Stock Mínimo</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="modalBodyNuevoStockMinimo">

            </div>
            <div class="form-group">
                <label for="nuevo_stock_minimo" class="form-label">Nuevo Stock Mínimo</label>
                <input type="text" onkeypress="return soloNumeros(event)" name="nuevo_stock_minimo" id="nuevo_stock_minimo" class="form-control">
            </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success btn-sm" onclick="guardarNuevoStockMinimo()">Guardar</button>
        </div>
      </div>
    </div>
</div>
<!-- Se guarda el id del repuesto en un campo hidden -->
<input type="hidden" id="id_repuesto">
@endsection