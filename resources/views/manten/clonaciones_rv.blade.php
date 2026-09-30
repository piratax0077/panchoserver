@extends('plantillas.app')
@section('titulo','Clonaciones RV')
@section('javascript')
<script>

    function enter_buscar(op){
        if(event.key === 'Enter'){
            buscar_repuesto(op);
        }
    }

    function buscar_repuesto(op){
        var cod_int;
        if(op == 1)
            cod_int = document.getElementById('cod_origen').value;
        else
            cod_int = document.getElementById('cod_destino').value;

        dame_repuesto(cod_int, op);
        dame_rv(cod_int, op);
    }

    function dame_repuesto(codigo_interno, op){
        let url = '/repuesto/buscarcodint_clonar_oems/' + codigo_interno;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: url,
            success: function(resp){
                console.log(resp);
                // El controlador retorna ['error'] o [htmlString, id]
                if(resp === 'error' || resp == 'error'){
                    return Vue.swal({ title: 'Error', text: 'No se encontró el repuesto', icon: 'error', confirmButtonText: 'Aceptar' });
                }
                // resp[0] = HTML del repuesto, resp[1] = id
                let htmlRep = resp[0];
                if(op == 1){
                    $('#repuesto_origen').html(htmlRep);
                }else{
                    $('#repuesto_destino').html(htmlRep);
                    $('#btn_clonar').prop('disabled', false);
                }
            },
            error: function(error){
                // Si no es JSON (responde string 'error'), también manejarlo
                if(error.responseText === 'error'){
                    return Vue.swal({ title: 'Error', text: 'No se encontró el repuesto', icon: 'error', confirmButtonText: 'Aceptar' });
                }
                Vue.swal({ title: 'ERROR', text: error.responseText, icon: 'error' });
            }
        });
    }

    function dame_rv(codigo_interno, op){
        let url = '/repuesto/' + codigo_interno + '/dame_rv_clonar';
        $.ajax({
            type: 'GET',
            url: url,
            success: function(html){
                if(op == 1)
                    $('#rv_origen').html(html);
                else
                    $('#rv_destino').html(html);
            },
            error: function(error){
                Vue.swal({ title: 'ERROR', text: error.responseText, icon: 'error' });
            }
        });
    }

    function clonar_rv(){
        let opt = confirm('¿Está seguro de clonar los reguladores de voltaje?');
        if(!opt) return;

        let cod_origen  = document.getElementById('cod_origen').value;
        let cod_destino = document.getElementById('cod_destino').value;

        if(!cod_origen || !cod_destino){
            return Vue.swal({ title: 'Atención', text: 'Debe ingresar ambos códigos internos', icon: 'warning' });
        }

        let url = '/repuesto/clonar_rv/' + cod_origen + '/' + cod_destino;
        $.ajax({
            type: 'GET',
            url: url,
            beforeSend: function(){
                Vue.swal({ title: 'Clonando...', text: 'Espere un momento', icon: 'info', allowOutsideClick: false, onOpen: () => Vue.swal.showLoading() });
            },
            success: function(resp){
                Vue.swal.close();
                if(resp.mensaje == 'error'){
                    return Vue.swal({ title: 'Error', text: 'No se pudo clonar. Puede que ya exista esta clonación.', icon: 'error' });
                }
                // actualizar RV del destino
                dame_rv(document.getElementById('cod_destino').value, 2);
                // actualizar tabla de historial
                actualizar_tabla(resp.clonaciones);
                Vue.swal({ title: 'Éxito', text: 'Clonación realizada correctamente', icon: 'success' });
            },
            error: function(error){
                Vue.swal({ title: 'ERROR', text: error.responseText, icon: 'error' });
            }
        });
    }

    function deshacer(id){
        let opt = confirm('¿Está seguro de deshacer esta clonación? Se eliminarán los RVs del repuesto destino.');
        if(!opt) return;

        let url = '/repuesto/deshacer_clonacion_rv/' + id;
        $.ajax({
            type: 'GET',
            url: url,
            success: function(resp){
                if(resp.mensaje == 'error'){
                    return Vue.swal({ title: 'Error', text: 'No se pudo deshacer', icon: 'error' });
                }
                actualizar_tabla(resp.clonaciones);
                Vue.swal({ title: 'Éxito', text: 'Clonación deshecha correctamente', icon: 'success' });
            },
            error: function(error){
                Vue.swal({ title: 'ERROR', text: error.responseText, icon: 'error' });
            }
        });
    }

    function actualizar_tabla(clonaciones){
        $('#info_clonaciones').html('Historial: ' + clonaciones.length + ' clonacion(es)');
        $('#tbody_clonaciones').empty();
        clonaciones.forEach(c => {
            $('#tbody_clonaciones').append(
                '<tr>' +
                '<td>' + c.codigo_origen + '</td>' +
                '<td>' + c.descripcion_origen + '</td>' +
                '<td>' + c.codigo_destino + '</td>' +
                '<td>' + c.descripcion_destino + '</td>' +
                '<td>' + c.fecha_emision + '</td>' +
                '<td><button class="btn btn-danger btn-sm" onclick="deshacer(' + c.id + ')"><i class="fas fa-trash"></i></button></td>' +
                '</tr>'
            );
        });
    }

</script>
@endsection
@section('contenido_ingresa_datos')
<h4 class="titulazo">Clonaciones Regulador de Voltaje</h4>
<div class="container-fluid">
    <a class="btn btn-secondary btn-sm my-2" href="/repuesto/clonaciones"><i class="fas fa-arrow-left"></i> Volver a Clonaciones</a>

    {{-- Formulario de clonación --}}
    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            <strong>Nueva clonación de Reguladores de Voltaje</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <p class="font-weight-bold">Repuesto Origen</p>
                    <div class="d-flex">
                        <input type="text" class="form-control form-control-sm" id="cod_origen"
                               onkeyup="enter_buscar(1)" placeholder="Cód. interno origen">
                        <button class="btn btn-success btn-sm ml-1" onclick="buscar_repuesto(1)">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="repuesto_origen" class="mt-2"></div>
                    <div id="rv_origen" class="mt-2"></div>
                </div>
                <div class="col-md-6">
                    <p class="font-weight-bold">Repuesto Destino</p>
                    <div class="d-flex">
                        <input type="text" class="form-control form-control-sm" id="cod_destino"
                               onkeyup="enter_buscar(2)" placeholder="Cód. interno destino">
                        <button class="btn btn-success btn-sm ml-1" onclick="buscar_repuesto(2)">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="repuesto_destino" class="mt-2"></div>
                    <div id="rv_destino" class="mt-2"></div>
                </div>
            </div>
            <button class="btn btn-warning mt-3" id="btn_clonar" onclick="clonar_rv()" disabled>
                <i class="fas fa-copy"></i> Clonar RV
            </button>
        </div>
    </div>

    {{-- Historial de clonaciones --}}
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <strong id="info_clonaciones">Historial: {{ $clonaciones->count() }} clonacion(es)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cód. Origen</th>
                            <th>Descripción Origen</th>
                            <th>Cód. Destino</th>
                            <th>Descripción Destino</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tbody_clonaciones">
                        @foreach($clonaciones as $c)
                        <tr>
                            <td>{{ $c->codigo_origen }}</td>
                            <td>{{ $c->descripcion_origen }}</td>
                            <td>{{ $c->codigo_destino }}</td>
                            <td>{{ $c->descripcion_destino }}</td>
                            <td>{{ $c->fecha_emision }}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="deshacer({{ $c->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
