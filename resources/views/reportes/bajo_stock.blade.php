@extends('plantillas.app')
@section('titulo','BAJO STOCK')
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

    function dameRepuestosPorProveedor(){
        var idProveedor = document.getElementById("proveedores").value;
        var url = "{{url('/proveedor/dame_stock_minimo_proveedor')}}"+"/"+idProveedor;

        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(html){
                Vue.swal.close();
                $('#resultado').empty();
                $('#resultado').append(html);
                // limpiar el input de familias
                document.getElementById("familias").value = "0";
                // limpiar el input de estados_pedidos
                document.getElementById("estados_pedidos").value = "0";
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function dameRepuestosPorFamilia(){
        var idFamilia = document.getElementById("familias").value;
        var url = "{{url('/proveedor/dame_stock_minimo_familia')}}"+"/"+idFamilia;
        

        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(html){
                Vue.swal.close();
                $('#resultado').empty();
                $('#resultado').append(html);
                // limpiar el input de proveedores
                document.getElementById("proveedores").value = "0";
                // limpiar el input de estados_pedidos
                document.getElementById("estados_pedidos").value = "0";
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function dameRepuestosPorEstado(){
        
        var estado = document.getElementById("estados_pedidos").value;
        let periodo_mes = $('#periodo_mes').val();
        let periodo_año = $('#periodo_año').val();
        var url = "{{url('/proveedor/dame_stock_minimo_estado')}}"+"/"+estado+"/"+periodo_mes+"/"+periodo_año;
        console.log(estado);

        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(html){
                Vue.swal.close();
                console.log(html);
                $('#tabla_todos_repuestos').empty();
                $('#tabla_todos_repuestos').append(html);
                // limpiar el input de proveedores
                document.getElementById("proveedores").value = "0";
                // limpiar el input de familias
                document.getElementById("familias").value = "0";
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

    function soloNumeros(e)
    {
        var key = window.Event ? e.which : e.keyCode
        return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
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
                    Vue.swal({
                        title: 'Stock mínimo actualizado',
                        text: 'El stock mínimo se actualizó correctamente',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    });
                    $('#modalNuevoStockMinimo').modal('hide');
                    var idProveedor = document.getElementById("proveedores").value;
                    var idFamilia = document.getElementById("familias").value;
                    var estado_pedido = document.getElementById("estados_pedidos").value;
                    console.log(idFamilia);
                    if(idProveedor !== "0"){
                        console.log("entro a proveedor")
                        dameRepuestosPorProveedor();
                    }else if(idFamilia !== '0'){
                        console.log("entro a familia")
                        dameRepuestosPorFamilia();
                    }else if(estado_pedido !== "0"){
                        console.log("entro a estado");
                        dameRepuestosPorEstado();
                    }else{
                        console.log("entro a else")
                        dameRepuestosPorEvento();
                    }
                }
                
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function repuestos_stock_minimo(){
                let url = '/repuesto/revisar_stock_minimo';
                $.ajax({
                    type:'get',
                    url: url,
                    success: function(resp){
                        if(resp > 0){
                            $('#cantidad_stock_minimo').removeClass('d-none');
                            $('#cantidad_stock_minimo').addClass('d-block');
                            $('#cantidad_stock_minimo').html(resp);
                        }else{
                            $('#cantidad_stock_minimo').removeClass('d-block');
                            $('#cantidad_stock_minimo').addClass('d-none');
                        }
                        
                    },
                    error: function(error){
                        console.log(error.responseText);
                    }
                });
            }

    function buscar_por_fecha(){
        let url = '/repuesto/dame_stock_minimo_fecha';

        $.ajax({
            type:'get',
            url: url,
            success: function(resp){
                console.log(resp);
                $('#tabla_todos_repuestos').empty();
                $('#tabla_todos_repuestos').append(resp);
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

    function dameRepuestosPorEvento(){
        repuestos_stock_minimo();
        buscar_por_fecha();
    }

    function detalle_pedido(idrep){
        // guardar el idrep en un input hidden
        document.getElementById("id_repuesto").value = idrep;
        // limpiar el input de cantidad
        document.getElementById("cantidad").value = "";
        var url = "{{url('/repuesto/detalle_pedido')}}"+"/"+idrep;
        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                espere();
            },
            success: function(html){
                Vue.swal.close();
                console.log(html);
                let detalle = html;
                $('#proveedor_pedido').empty();
                $('#cantidad_pedido').empty();
                $('#usuario_pedido').empty();
                $('#fecha_pedido').empty();
                $('#id_pedido').empty();
                $('#codigo_interno_pedido').empty();
                $('#codigo_proveedor_pedido').empty();
                if(detalle){
                    if(detalle.cod_rep_prov == ''){
                        detalle.cod_rep_prov = detalle.codigo_proveedor;
                    }
                    $('#id_pedido').append(detalle.id);
                    $('#codigo_interno_pedido').append(detalle.codigo_interno);
                    $('#codigo_proveedor_pedido').append(detalle.cod_rep_prov);
                    $('#proveedor_pedido').append(detalle.empresa_nombre_corto);
                    $('#cantidad_pedido').append(detalle.cantidad);
                    $('#usuario_pedido').append(detalle.usuario);
                    $('#fecha_pedido').append(detalle.fecha_emision); 
                    // en el select con id proveedores_ tiene que estar selected el proveedor del pedido
                    $('#proveedores_').val(detalle.id_proveedor);
                }else{
                    $('#proveedor_pedido').append("No hay detalle del pedido");
                }
                $('#modalDetalle').modal('show');
            },
            error: function(error){
                console.log(error);
            }
        });
        
    }

    function guardarDetalle(){
        var idrep = document.getElementById("id_repuesto").value;
        var cantidad = document.getElementById("cantidad").value;
        var idProveedor = document.getElementById("proveedores_").value;
        var cod_rep_prov = document.getElementById("cod_prov").value;
        var url = "{{url('/repuesto/guardar_detalle_pedido')}}"+"/"+idrep+"/"+idProveedor+"/"+cantidad+"/"+cod_rep_prov;
        // validar que la cantidad no sea vacía
        if(cantidad == ""){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar una cantidad',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        // validar que la cantidad sea mayor a 0
        if(cantidad <= 0){
            Vue.swal({
                title: 'Error',
                text: 'La cantidad debe ser mayor a 0',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        // validar que el proveedor no sea vacío
        if(idProveedor == "" || idProveedor == 0){
            Vue.swal({
                title: 'Error',
                text: 'Debe seleccionar un proveedor',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        // validar que el codigo interno del proveedor no sea vacío
        if(cod_prov == ""){
            Vue.swal({
                title: 'Error',
                text: 'Debe ingresar el código interno del proveedor',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(resp){
                Vue.swal.close();
                console.log(resp);
                
                if(resp == 'OK'){
                    Vue.swal({
                        title: 'Detalle guardado',
                        text: 'El detalle se guardó correctamente',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    });
                    $('#modalDetalle').modal('hide');
                    // dameRepuestosPorEvento();
                    if(idProveedor !== "0"){
                        console.log("entro a proveedor")
                        dameRepuestosPorProveedor();
                    }else if(idFamilia !== '0'){
                        console.log("entro a familia")
                        dameRepuestosPorFamilia();
                    }else if(estado_pedido !== "0"){
                        console.log("entro a estado");
                        dameRepuestosPorEstado();
                    }
                }else{
                    return Vue.swal({
                        title: 'Error',
                        text: 'Ya hizo el pedido de este repuesto a otro proveedor',
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    })
                }
                
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function buscar_mes(){
        let periodo_mes = $('#periodo_mes').val();
        let periodo_año = $('#periodo_año').val();

        let url='{{url("/reportes/buscar_periodo_stock_minimo")}}'+'/'+periodo_mes+'/'+periodo_año;

        $.ajax({
            url:url,
            type:'get',
            success: function(html){
                console.log(html);
                Vue.swal.close();
                console.log(html);
                $('#tabla_todos_repuestos').empty();
                $('#tabla_todos_repuestos').append(html);
                // limpiar el input de proveedores
                document.getElementById("proveedores").value = "0";
                // limpiar el input de familias
                document.getElementById("familias").value = "0";
                
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

    function detalle_repuesto(idrep){
        var url='{{url("factuprodu/damecompras")}}'+'/'+idrep;
        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
                espere();
            },
            success: function(html){
                console.log(html);
                Vue.swal.close();
                $('#resultado_').empty();
                $('#resultado_').append(html);
            },
            error: function(error){
                console.log(error);
            }
        });
    }
</script>
@endsection

@section('style')
<style>
.formulario{
            /* display: flex; */
            width: 100%;
            margin: 10px auto;
            text-align: center;
            border: 1px solid black;
            align-items: center;
            padding: 40px;
            background: #f2f4a9;
            border-radius: 30px;
        }

#periodo{
    display: flex;
}
</style>
@endsection

@section('contenido_ingresa_datos')
<h4 class="titulazo">Bajo Stock Mínimo</h4>
@php
            $año_actual=date("Y");
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <h4>Filtros</h4>
            <div class="form-group">
                <label for="proveedores" class="form-label">Proveedores</label>
                <select name="proveedores" id="proveedores" class="form-select form-control" onchange="dameRepuestosPorProveedor()">
                    <option value="0">Seleccione proveedor</option>
                    @foreach($proveedores as $proveedor)
                    <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
                    @endforeach
                </select>
            </div>
            <p id="mensaje"></p>
            <div class="form-group">
                <label for="proveedores" class="form-label">Familias</label>
                <select name="familias" id="familias" class="form-select form-control" onchange="dameRepuestosPorFamilia()">
                    <option value="0">Seleccione familia</option>
                    @foreach($familias as $familia)
                    <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                    @endforeach
                </select>
            </div>
            <p id="mensaje"></p>
            <div class="form-group">
                <label for="periodo_mes">Periodo:</label>
                <div id="periodo">
                    
                    <select name="periodo_mes" id="periodo_mes" class="form-control form-control-sm" onchange="dameRepuestosPorEstado()">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <select name="periodo_año" id="periodo_año" class="form-control form-control-sm" onchange="dameRepuestosPorEstado()">
                        @for($an=2020;$an<=$año_actual;$an++)
                            @if($an==$año_actual)
                                <option value="{{$an}}" selected>{{$an}}</option>
                            @else
                                <option value="{{$an}}">{{$an}}</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>
            <hr>
            <div id="filtros">
                <label for="estados_pedidos" class="form-label">Estados</label>
                <select name="estados_pedidos" id="estados_pedidos" class="form-select form-control" onchange="dameRepuestosPorEstado()">
                    <option value="999">Seleccione un estado</option>
                    <option value="0">Sin estado</option>
                    <option value="todos">Todos</option>
                    @foreach($estados as $estado)
                    <option value="{{$estado}}">{{$estado}}</option>
                    @endforeach
                </select>
            </div>
            <hr>
            
            <button class="btn btn-success btn-sm w-100" onclick="buscar_mes()">Buscar</button>
            <hr>
            <div id="tabla_todos_repuestos" class="tabla-scroll-y-300" style="font-size: 13px;">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($repuestos_todos as $r)
                      @php
                            $r->estado == 'Pedido' ? $clase = 'bg-success text-white' : $clase = 'bg-light';
                      @endphp
                        <tr class="{{$clase}}">
                            <td><a href="javascript:void(0)" style="color: black" data-toggle="modal" data-target="#modalDetalleRepuesto" onclick="detalle_repuesto({{$r->id}})">{{$r->codigo_interno}}</a> </td>
                            <td>{{$r->fecha_emision}}</td>
                            <td>{{$r->estado}}</td>
                            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id_repuesto}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
                            @if($r->estado == 'Pedido')<td><a href="javascript:void(0)" onclick="detalle_pedido({{$r->id_repuesto}})" class="btn btn-warning btn-sm">D</a></td>@endif
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div>
            <hr>
            {{-- <div class="form-group d-flex">
                
                <input type="date" name="fecha_busqueda" id="fecha_busqueda" value="<?php echo date("Y-m-d"); ?>" class="form-control">
                <button class="btn btn-sm btn-success" onclick="buscar_por_fecha()">Buscar</button>
            </div>
            <div id="tabla_repuestos" style="font-size: 13px;">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($repuestos as $r)
                        <tr>
                            <td>{{$r->codigo_interno}}</td>
                            <td>{{$r->fecha_emision}}</td>
                            <td>{{$r->estado}}</td>
                            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id_repuesto}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div> --}}
            
        </div>
        <div class="col-md-9">
            <div id="resultado">

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
<!--MODAL DETALLE -->

<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="modalBodyDetalle">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Cod Prov</th>
                        <th scope="col">Proveedor</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Fecha</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><span id="id_pedido"></span></td>
                        <td><span id="codigo_interno_pedido"></span></td>
                        <td><span id="codigo_proveedor_pedido"></span></td>
                        <td><span id="proveedor_pedido"></span></td>
                        <td><span id="cantidad_pedido"></span> </td>
                        <td><span id="usuario_pedido"></span> </td>
                        <td><span id="fecha_pedido"></span> </td>
                      </tr>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-4">
                        <select name="proveedores" id="proveedores_" class="form-select form-control">
                            <option value="">Seleccione proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="" id="cantidad" class="form-control" placeholder="Ingrese cantidad del pedido">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="cod_prov" id="cod_prov" class="form-control" placeholder="Ingrese codigo interno del proveedor">
                    </div>
                </div>
                
            </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success btn-sm" onclick="guardarDetalle()">Guardar</button>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="modalDetalleRepuesto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="resultado_">
                
            </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
</div>

<!-- Se guarda el id del repuesto en un campo hidden -->
<input type="hidden" id="id_repuesto">
@endsection