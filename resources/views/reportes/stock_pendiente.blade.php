@extends('plantillas.app')
@section('titulo','STOCK PENDIENTE')

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
                    document.getElementById("nuevo_stock_minimo").value = repuesto.stock_minimo;
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function guardarNuevoEstado(){
            var idRepuesto = document.getElementById("id_repuesto").value;
            var estado = document.getElementById("estado").value;
            
            if(estado == ""){
                Vue.swal({
                    title: 'Error',
                    text: 'Debe seleccionar un estado',
                    icon: 'error',
                    showCancelButton: false,
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                });
                return false;
            }

            $.ajax({
                type: 'POST',
                url: '/repuesto/' + idRepuesto + '/cambiar-estado',
                data: {
                    estado: estado,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function(){
                    espere();
                },
                success: function(data){
                    Vue.swal.close();
                    document.getElementById("estado_"+idRepuesto).innerHTML = estado;
                    
                    Vue.swal({
                        title: 'Éxito',
                        text: 'Estado actualizado correctamente',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    }).then((result)=>{
                        location.reload();
                    });
                },
                error: function(error){
                    console.log(error);
                    Vue.swal.close();
                    Vue.swal({
                        title: 'Error',
                        text: 'Ocurrió un error al actualizar el estado',
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    });
                }
            });
        }

        function soloNumeros(event){
            if ((event.charCode >=48 && event.charCode<=57) || event.charCode==44) {
                return true;
            }else{
                return false;
            }
        }
    </script>
@endsection

@section('contenido')

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa-solid fa-hourglass-end"></i> Stock Pendiente de Llegada
                    </h5>
                    <small class="text-muted">Repuestos que han sido pedidos pero aún no llegan</small>
                </div>
                <div class="card-body">
                    @if(count($repuestos) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabla_stock_pendiente">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Código Interno</th>
                                        <th>Descripción</th>
                                        <th>Proveedor</th>
                                        <th>Familia</th>
                                        <th>Marca</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($repuestos as $r)
                                    <tr>
                                        <td>
                                            <strong>{{$r->codigo_interno}}</strong>
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

                                        <td>
                                            {{$r->marcarepuesto}}
                                        </td>
                                        
                                        <td id="estado_{{$r->id}}">
                                            <span class="badge badge-warning">{{$r->estado}}</span>
                                        </td>
                                        
                                        <td>
                                            <a class="btn btn-info btn-sm"
                                               href="javascript:void(0)"
                                               onclick="damerepuesto({{$r->id}})"
                                               data-target="#modalEstado"
                                               data-toggle="modal"
                                               title="Cambiar estado">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="fa-solid fa-info-circle"></i> No hay repuestos pendientes en este momento.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="modalEstado" tabindex="-1" role="dialog" aria-labelledby="modalEstadoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="modalEstadoLabel">Cambiar Estado del Repuesto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="modalBodyNuevoStockMinimo"></div>
            
            <div class="form-group">
                <label for="estado" class="form-label">Nuevo Estado</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="">-- Seleccionar estado --</option>
                    @foreach($estados as $est)
                        <option value="{{$est}}">{{$est}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success btn-sm" onclick="guardarNuevoEstado()">Actualizar Estado</button>
        </div>
      </div>
    </div>
</div>

<!-- Se guarda el id del repuesto en un campo hidden -->
<input type="hidden" id="id_repuesto">

@endsection

@section('css')
<style>
    .badge-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .card-header {
        padding: 1rem;
    }
</style>
@endsection
