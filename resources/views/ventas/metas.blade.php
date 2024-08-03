@extends('plantillas.app')
@section('titulo','HOME')
@section('javascript')
<script>

    window.onload=function(){
        var xpress_precio=document.getElementById("meta_inicial");
        var precio_mitad = document.getElementById("meta_mitad");
        var precio_final = document.getElementById("meta_final");
        Inputmask({"mask":"$ 999.999.999",numericInput: true}).mask(xpress_precio);
        Inputmask({"mask":"$ 999.999.999",numericInput: true}).mask(precio_mitad);
        Inputmask({"mask":"$ 999.999.999",numericInput: true}).mask(precio_final);
    }
    function ver_ventas(){
        // abrir modal
        $('#modalVentas').modal('show');
    }

    function guardar_meta_mensual(){
        let mes=$('#periodo_mes').val();
        let año=$('#periodo_año').val();
        let prec_e=document.getElementById("meta_inicial");
        let prec_mitad = document.getElementById("meta_mitad");
        let prec_final = document.getElementById("meta_final");
        let prec=0;
        let prec_m=0;
        let prec_i=0;
        if (prec_e.inputmask){
            prec=prec_e.inputmask.unmaskedvalue();
        }else{
            prec=prec_e.value.trim();
        }
        if (prec_mitad.inputmask){
            prec_m=prec_mitad.inputmask.unmaskedvalue();
        }else{
            prec_m=prec_mitad.value.trim();
        }
        if (prec_final.inputmask){
            prec_i=prec_final.inputmask.unmaskedvalue();
        }else{
            prec_i=prec_final.value.trim();
        }
        
        if(prec==''){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar la meta',
                icon: 'error'
            
            });
            return false;
        }

        if(prec_m==''){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar la meta',
                icon: 'error'
            
            });
            return false;
        }

        if(prec_i==''){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar la meta',
                icon: 'error'
            
            });
            return false;
        }

        $.ajax({
            url: "{{route('guardar_meta')}}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mes: mes,
                año: año,
                meta: prec_i,
                meta_mitad: prec_m,
                meta_inicial: prec
            },
            success: function(data){
                console.log(data);
                if(data.estado=='OK'){
                    Vue.swal({
                        title: 'Guardado',
                        text: 'Meta guardada correctamente',
                        icon: 'success'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    })
                }else{
                    Vue.swal({
                        title: 'Error',
                        text: 'Ocurrio un error al guardar la meta',
                        icon: 'error'
                    })
                }
            }
        });
    }
    
</script>

@endsection

@section('style')
<style>
    #periodo{
        display: flex;
        align-items: flex-start;
    }
    tr td{
        font-size: 12px;
        text-align: left;
    }
</style>
@endsection

@section('contenido')
<h3 class="titulazo">Metas</h3>
@php
 $año_actual=date("Y");
@endphp
<div class="container-fluid">
    <div class="row w-100">
        <div id="periodo">
            <label for="periodo_mes">Periodo:</label>
            <select name="periodo_mes" id="periodo_mes" class="form-control form-control-sm">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ (date('n') == $i) ? 'selected' : '' }}>
                        {{ strftime('%B', mktime(0, 0, 0, $i, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="periodo_año" id="periodo_año" class="form-control form-control-sm">
                @for($an=2020;$an<=$año_actual;$an++)
                    @if($an==$año_actual)
                        <option value="{{$an}}" selected>{{$an}}</option>
                    @else
                        <option value="{{$an}}">{{$an}}</option>
                    @endif
                @endfor
            </select>
            <input type="text" class="form-control form-control-sm" placeholder="Meta Inicial" id="meta_inicial">
            <input type="text" class="form-control form-control-sm" placeholder="Meta Mitad" id="meta_mitad">
            <input type="text" class="form-control form-control-sm" placeholder="Meta Final" id="meta_final">
            <button class="btn btn-primary btn-sm" onclick="guardar_meta_mensual()"><i class="fas fa-save"></i> Guardar</button>
            <button class="btn btn-success btn-sm" onclick="buscar_meta_mensual()"><i class="fas fa-search"></i> Buscar</button>
        </div>
    </div>
    <div class="row w-100 mt-3">
        <div class="col-md-3 tabla-scroll-y-600">
            @foreach($usuarios as $u)
            <div class="card mb-2">
                <div class="card-header d-flex justify-content-between letra_pequeña">
                    <span>{{$u->name}}</span>
                    <span>{{$u->nombrerol}}</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <img src="{{url('usuarios/avatar/'.$u->image_path)}}" alt="" class="logoInicio">
                        <div class="letra_pequeña">
                            <p class="card-text font-weight-bold">Ventas: ${{number_format($u->total_ventas,0,',','.')}} </p>
                            <p class="card-text text-danger">Porcentaje: {{number_format($u->porcentaje_venta_usuario,2)}} %</p>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
    
            @endforeach
        </div>
        <div class="col-md-3">
            <div class="info">
                <h4>Meta de ventas - {{date('F')}} {{date('Y')}}</h4>
                @if(isset($meta))
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Meta Final</strong> </td>
                            <td >$ {{ number_format($meta->meta,0,',','.')}}</td>
                            <td>$ {{number_format($porcentaje,0)}} % @if($porcentaje > 100) <span class="badge badge-success">Superada</span> @endif</td>
                        </tr>
                        <tr>
                            <td><strong>Meta a mitad de mes</strong></td>
                            <td >$ {{ number_format($meta->meta_mitad,0,',','.')}}</td>
                            <td>$ {{number_format($porcentaje_mitad,0)}} % @if($porcentaje_mitad > 100) <span class="badge badge-success">Superada</span> @endif</td>
                        </tr>
                        <tr>
                            <td><strong>Meta Inicial</strong></td>
                            <td >$ {{ number_format($meta->meta_inicial,0,',','.')}}</td>
                            <td>$ {{number_format($porcentaje_inicial,0)}} % @if($porcentaje_inicial > 100) <span class="badge badge-success">Superada</span> @endif</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Ventas Neto</strong></td>
                            <td >$ {{ number_format($total_neto,0,',','.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>IVA</strong></td>
                            <td >$ {{ number_format($iva_total,0,',','.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td >$ {{ number_format($total,0,',','.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Meta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody_metas">
                    @if(isset($metas))
                    @foreach($metas as $m)
                    <tr>
                        <td>{{$m->mes}}-{{$m->año}}</td>
                        <td>{{number_format($m->meta,0,',','.')}}</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" onclick="buscar_meta_mensual({{$m->id}})"><i class="fas fa-search"></i></button>
                            <button class="btn btn-outline-danger btn-sm" onclick="eliminar_meta({{$m->id}})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <canvas id="myChart"></canvas>

            
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalVentas" tabindex="-1" aria-labelledby="modalVentasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVentasLabel">Ventas de: </h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2021-09-01</td>
                            <td>Producto 1</td>
                            <td>10</td>
                            <td>100</td>
                            <td>1000</td>
                        </tr>
                        <tr>
                            <td>2021-09-01</td>
                            <td>Producto 2</td>
                            <td>5</td>
                            <td>200</td>
                            <td>1000</td>
                        </tr>
                        <tr>
                            <td>2021-09-01</td>
                            <td>Producto 3</td>
                            <td>2</td>
                            <td>300</td>
                            <td>600</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@if(isset($meta))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Inicio', 'Meta Final', 'Meta Mitad', 'Meta Inicial', 'Total'],
            datasets: [{
                label: 'Metas',
                data: [@json($meta->inicio), @json($meta->meta), @json($meta->meta_mitad), @json($meta->meta_inicial), @json($total_neto)],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Include a dollar sign in the ticks
                        callback: function(value, index, values) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    beginAtZero: true
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';

                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.yLabel.toLocaleString();
                        return label;
                    }
                }
            }
        }
    });
});

function eliminar_meta(id){
    Vue.swal({
        title: 'Eliminar',
        text: '¿Desea eliminar la meta seleccionada?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{route('eliminar_meta')}}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(data){
                    console.log(data);
                    if(data.mensaje=='OK'){
                        let metas = data.metas;
                        $('#tbody_metas').empty();
                        metas.forEach(meta => {
                            $('#tbody_metas').append(`<tr>
                                <td>${meta.mes}-${meta.año}</td>
                                <td>${meta.meta}</td>
                                <td><button class="btn btn-outline-danger btn-sm" onclick="eliminar_meta(${meta.id})"><i class="fas fa-trash"></i></button></td>
                            </tr>`);
                        });
                        Vue.swal({
                            title: 'Eliminado',
                            text: 'Meta eliminada correctamente',
                            icon: 'success'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        })
                    }else{
                        Vue.swal({
                            title: 'Error',
                            text: 'Ocurrio un error al eliminar la meta',
                            icon: 'error'
                        })
                    }
                }
            });
        }
    });
}

function buscar_meta_mensual(id){
    Vue.swal({
        title: 'Buscar',
        text: '¿Desea buscar la meta seleccionada?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{route('buscar_meta')}}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(data){
                    console.log(data);
                    if(data.mensaje=='OK'){
                        let meta = data.meta;
                        $('#meta_inicial').val(meta.meta_inicial);
                        $('#meta_mitad').val(meta.meta_mitad);
                        $('#meta_final').val(meta.meta);
                    }else{
                        Vue.swal({
                            title: 'Error',
                            text: 'Ocurrio un error al buscar la meta',
                            icon: 'error'
                        })
                    }
                }
            });
        }
    });
}
</script>
@endif
@endsection