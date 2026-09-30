@extends('plantillas.app')
@section('titulo','BAJO STOCK')
@section('javascript')
<script>

    // Funcionalidad de búsqueda avanzada para stock mínimo preparada, pero no visible en la vista.
    // Para activarla, mostrar los bloques HTML marcados como BUSCADOR_STOCK_MINIMO_OCULTO.

    window.onload = function(){
        // el select con id periodo_mes se cargue con el mes actual
        let mes_actual = new Date().getMonth() + 1;
        $('#periodo_mes').val(mes_actual);
        dameRepuestosPorEstado();
    }
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
        if(estado == 999){
            estado = 'todos';
        }
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
                        repuestos_stock_minimo();
                        repuestos_sin_stock_proveedor();
                    }else if(idFamilia !== '0'){
                        console.log("entro a familia")
                        dameRepuestosPorFamilia();
                        repuestos_stock_minimo();
                        repuestos_sin_stock_proveedor();
                    }else if(estado_pedido !== "0"){
                        console.log("entro a estado");
                        dameRepuestosPorEstado();
                        repuestos_stock_minimo();
                        repuestos_sin_stock_proveedor();
                    }else{
                        console.log("entro a else")
                        // dameRepuestosPorEvento();
                        dameRepuestosPorEstado();
                        // funcion que viene de plantillas.app para actualizar el stock mínimo
                        repuestos_stock_minimo();
                        repuestos_sin_stock_proveedor();
                    }
                }
                
            },
            error: function(error){
                console.log(error);
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

    function abrirModalExcel(){
        $('#modalExcel').modal('show');
    }

    function importarModalExcel(){
        $('#modalImportarExcel').modal('show');
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
        if(cod_rep_prov == ""){
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
                    var idProveedorFiltro = document.getElementById("proveedores").value;
                    var idFamiliaFiltro = document.getElementById("familias").value;
                    var estadoPedidoFiltro = document.getElementById("estados_pedidos").value;

                    if(idProveedorFiltro !== "0"){
                        dameRepuestosPorProveedor();
                    }else if(idFamiliaFiltro !== '0'){
                        dameRepuestosPorFamilia();
                    }else if(estadoPedidoFiltro !== "0"){
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



    function normalizarTextoBusquedaStockMinimo(texto){
        texto = (texto || '').trim();
        texto = texto.replace(/\//g, '_&_');
        return encodeURIComponent(texto);
    }

    function enter_buscar_stock_minimo(e){
        if(e.key === 'Enter'){
            buscarRepuestoStockMinimo();
        }
    }

    function limpiarBusquedaStockMinimo(){
        $('#buscar_repuesto_stock_minimo').val('');
        $('#resultado_busqueda_stock_minimo').html(`
            <div class="alert alert-info mb-0">
                Ingrese un código interno, código proveedor, OEM, fabricante o descripción para buscar productos.
            </div>
        `);
        $('#proveedores').val('0');
        $('#familias').val('0');
    }

    function buscarRepuestoStockMinimo(){
        let texto = $('#buscar_repuesto_stock_minimo').val();

        if(!texto || texto.trim().length < 2){
            Vue.swal({
                title: 'Búsqueda requerida',
                text: 'Ingrese al menos 2 caracteres para buscar un producto',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        let dato = normalizarTextoBusquedaStockMinimo(texto);
        let url = '{{url("/repuesto/buscar-stock-minimo")}}' + '/' + dato;

        $.ajax({
            type: 'GET',
            url: url,
            beforeSend: function(){
                espere();
                $('#resultado_busqueda_stock_minimo').html(`
                    <div class="alert alert-info mb-0">
                        Buscando productos...
                    </div>
                `);
            },
            success: function(resp){
                Vue.swal.close();

                // Si el backend devuelve HTML parcial, lo mostramos directo.
                if(typeof resp === 'string'){
                    $('#resultado_busqueda_stock_minimo').html(resp);
                    return;
                }

                // Si el backend devuelve JSON, armamos la tabla desde esta vista.
                if(resp && resp.estado === 'OK'){
                    renderTablaBusquedaStockMinimo(resp.repuestos || []);
                    return;
                }

                $('#resultado_busqueda_stock_minimo').html(`
                    <div class="alert alert-warning mb-0">
                        No se encontraron productos para la búsqueda ingresada.
                    </div>
                `);
            },
            error: function(error){
                Vue.swal.close();
                console.log(error.responseText);
                $('#resultado_busqueda_stock_minimo').html(`
                    <div class="alert alert-danger mb-0">
                        Ocurrió un error al buscar los productos.
                    </div>
                `);
            }
        });
    }

    function renderTablaBusquedaStockMinimo(repuestos){
        if(!repuestos || repuestos.length === 0){
            $('#resultado_busqueda_stock_minimo').html(`
                <div class="alert alert-warning mb-0">
                    No se encontraron productos para la búsqueda ingresada.
                </div>
            `);
            return;
        }

        let estados = @json($estados ?? []);
        let filas = '';

        repuestos.forEach(function(r){
            let stockUno = Number(r.stock_actual || 0);
            let stockDos = Number(r.stock_actual_dos || 0);
            let stockTres = Number(r.stock_actual_tres || 0);
            let stockTotal = stockUno + stockDos + stockTres;
            let estadoActual = r.estado || r.estado_stock_minimo || 'Sin estado';

            let options = `<option value="0">Sin estado</option>`;
            estados.forEach(function(estado){
                let selected = estado === estadoActual ? 'selected' : '';
                options += `<option value="${estado}" ${selected}>${estado}</option>`;
            });

            filas += `
                <tr id="fila_busqueda_stock_${r.id}">
                    <td>
                        <a href="javascript:void(0)" data-target="#modalDetalleRepuesto" data-toggle="modal" onclick="detalle_repuesto(${r.id})">
                            ${r.codigo_interno || ''}
                        </a>
                    </td>
                    <td>${r.descripcion || ''}</td>
                    <td>${r.empresa_nombre_corto || 'N/A'}</td>
                    <td class="text-center">${stockTotal}</td>
                    <td class="text-center">${r.stock_minimo || 0}</td>
                    <td>
                        <select class="form-control form-control-sm" onchange="cambiarEstadoStockMinimo(${r.id}, this.value)">
                            ${options}
                        </select>
                    </td>
                    <td class="text-center">
                        <a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto(${r.id})" data-target="#modalNuevoStockMinimo" data-toggle="modal">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </a>
                    </td>
                </tr>
            `;
        });

        let tabla = `
            <div class="table-responsive tabla-scroll-y-300">
                <table class="table table-sm table-bordered table-hover mb-0" style="font-size:13px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Cod Int</th>
                            <th>Descripción</th>
                            <th>Proveedor</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Stock mín.</th>
                            <th>Estado</th>
                            <th class="text-center">Stock mín.</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
            </div>
        `;

        $('#resultado_busqueda_stock_minimo').html(tabla);
    }

    function cambiarEstadoStockMinimo(idRepuesto, estado){
        if(!idRepuesto){
            return false;
        }

        let estadoFinal = estado || '0';
        let url = '{{url("/repuesto/cambiar-estado-stock-minimo")}}' + '/' + idRepuesto + '/' + encodeURIComponent(estadoFinal);

        $.ajax({
            type: 'GET',
            url: url,
            beforeSend: function(){
                $('#fila_busqueda_stock_' + idRepuesto).addClass('table-warning');
            },
            success: function(resp){
                $('#fila_busqueda_stock_' + idRepuesto).removeClass('table-warning').addClass('table-success');

                Vue.swal({
                    text: 'Estado actualizado correctamente',
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 2500,
                });

                dameRepuestosPorEstado();
                repuestos_stock_minimo();
                repuestos_sin_stock_proveedor();
            },
            error: function(error){
                $('#fila_busqueda_stock_' + idRepuesto).removeClass('table-warning');
                console.log(error.responseText);

                Vue.swal({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado del producto',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    function importar_excel_stock_minimo(){
        var archivo_excel = document.getElementById("archivo_excel").files[0];
        var id_proveedor = document.getElementById("proveedor_importar").value;
        if(!archivo_excel){
            Vue.swal({
                title: 'Error',
                text: 'Debe seleccionar un archivo Excel',
                icon: 'error',
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
            });
            return false;
        }
        if(id_proveedor == 0){
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
        var formData = new FormData();
        formData.append('archivo_excel', archivo_excel);
        formData.append('proveedor_id', id_proveedor);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $.ajax({
            url: '{{url("/factuprodu/importar_excel_stock_minimo")}}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(){
                espere();
            },
            success: function(resp){
                Vue.swal.close();
                console.log(resp);
                if(resp.estado == 'OK'){
                    Vue.swal({
                        title: 'Importación exitosa',
                        text: 'El archivo Excel se importó correctamente',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    });
                    $('#modalImportarExcel').modal('hide');
                    // dameRepuestosPorEvento();
                    repuestos_stock_minimo();
                        repuestos_sin_stock_proveedor();
                }else{
                    Vue.swal({
                        title: 'Error en la importación',
                        text: resp.mensaje,
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonText: 'Aceptar',
                        allowOutsideClick: false,
                    });
                }
                
            },
            error: function(error){
                console.log(error.responseText);
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
    <!-- seccion de mensajes desde el backend  -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('errores_excel'))
        <div class="alert alert-danger">
            <strong>Errores encontrados:</strong>
            <ul>
                @foreach(session('errores_excel') as $error)
                    <li>
                        Fila {{ $error['fila'] }} - 
                        Campo {{ $error['columna'] }}: 
                        {{ implode(', ', $error['errores']) }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
    <div class="row">
        <div class="col-md-4">
            <h4>Filtros</h4>

            {{-- BUSCADOR_STOCK_MINIMO_OCULTO
            <hr>
            <div class="card mb-3">
                <div class="card-header py-2">
                    <strong>Buscar producto</strong>
                </div>
                <div class="card-body p-2">
                    <label for="buscar_repuesto_stock_minimo" class="form-label mb-1">Producto</label>
                    <div class="input-group input-group-sm">
                        <input type="text"
                               id="buscar_repuesto_stock_minimo"
                               class="form-control"
                               placeholder="Código, OEM, proveedor o descripción"
                               onkeypress="enter_buscar_stock_minimo(event)">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" onclick="buscarRepuestoStockMinimo()">
                                Buscar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarBusquedaStockMinimo()">
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Busca productos del inventario para agregarlos o cambiar su estado en stock mínimo.
                    </small>
                </div>
            </div>
            --}}
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
                <button type="button" class="btn btn-success btn-sm w-100" onclick="abrirModalExcel()">Excel</button>
                <button type="button" class="btn btn-warning btn-sm w-100 my-2" onclick="importarModalExcel()">Importar Excel</button>
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
            
            {{-- <button class="btn btn-success btn-sm w-100" onclick="buscar_mes()">Buscar</button> --}}
            <hr>
            <div id="tabla_todos_repuestos" class="tabla-scroll-y-300" style="font-size: 13px;">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {{-- @foreach($repuestos_todos as $r)
                      @php
                            $r->estado == 'Pedido' ? $clase = 'bg-success text-white' : $clase = 'bg-light';
                            $r->id_modifica_estado == 5 ? $clase = 'bg-primary text-white' :  '';
                      @endphp
                        <tr class="{{$clase}}">
                            <td><a href="javascript:void(0)" style="color: black" data-toggle="modal" data-target="#modalDetalleRepuesto" onclick="detalle_repuesto({{$r->id}})">{{$r->codigo_interno}}</a> </td>
                            <td>{{$r->fecha_emision}}</td>
                            <td>{{$r->estado}}</td>
                            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id_repuesto}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
                            @if($r->estado == 'Pedido')<td><a href="javascript:void(0)" onclick="detalle_pedido({{$r->id_repuesto}})" class="btn btn-warning btn-sm">D</a></td>@endif
                            <td></td>
                        </tr>
                      @endforeach --}}
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
        <div class="col-md-8">
            {{-- BUSCADOR_STOCK_MINIMO_OCULTO_RESULTADOS
            <div class="card mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <strong>Resultado búsqueda de productos</strong>
                    <small class="text-muted">Cambio de estado rápido</small>
                </div>
                <div class="card-body p-2" id="resultado_busqueda_stock_minimo">
                    <div class="alert alert-info mb-0">
                        Ingrese un código interno, código proveedor, OEM, fabricante o descripción para buscar productos.
                    </div>
                </div>
            </div>
            --}}
            <div id="resultado">
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
                      @foreach($repuestos_ as $r)
                        @php
                            $r->estado == 'Pedido' ? $clase = 'bg-success text-white' : $clase = 'bg-light';
                            $r->id_modifica_estado == 5 ? $clase = 'bg-primary text-white' :  '';
                        @endphp
                        <tr class="{{$clase}}">
                            <td><a href="javascript:void(0)" style="color: black;" data-target="#modalDetalleRepuesto" data-toggle="modal" onclick="detalle_repuesto({{$r->id}})">{{$r->codigo_interno}}</a> </td>
                            <td>{{$r->ultima_fecha_emision}}</td>
                            <td>{{$r->estado}}</td>
                            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
                            @if($r->estado == 'Pedido')<td><a href="javascript:void(0)" onclick="detalle_pedido({{$r->id}})" class="btn btn-warning btn-sm">D</a></td>@endif
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
<!-- MODAL EXCEL -->
<div class="modal fade" id="modalExcel" tabindex="-1" role="dialog" aria-labelledby="modalExcelLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalExcelLabel">Exportar a Excel</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ url('/repuesto/descargar-repuestos-stock-minimo') }}" method="GET">
            <div class="modal-body">
                <div class="form-group">
                    <label for="fecha_inicial_excel">Fecha Inicial</label>
                    <input type="date" name="fecha_inicial_excel" id="fecha_inicial_excel" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="fecha_final_excel">Fecha Final</label>
                    <input type="date" name="fecha_final_excel" id="fecha_final_excel" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="id_proveedor_excel">Proveedor</label>
                    <select name="id_proveedor_excel" id="id_proveedor_excel" class="form-control">
                        <option value="0">Todos</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">{{ $proveedor->empresa_nombre_corto }}</option>
                        @endforeach
                    </select>
                </div>
                <p>¿Desea exportar el reporte de bajo stock mínimo a Excel?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success btn-sm">Exportar</button>
            </div>
        </form>
        
      </div>
    </div>
</div>
<!-- CERRAR MODAL EXCEL -->
<!-- MODAL IMPORTAR EXCEL -->
<div class="modal fade" id="modalImportarExcel" tabindex="-1" role="dialog" aria-labelledby="modalImportarExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalImportarExcelLabel">Importar Excel</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="proveedor_importar">Proveedor</label>
                        <select name="proveedor_importar" id="proveedor_importar" class="form-control">
                            <option value="0">Seleccione</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->empresa_nombre_corto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"> 
                        <label for="archivo_excel">Seleccione el archivo Excel</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" class="form-control">
                    </div>
                    <p>El archivo Excel debe tener el siguiente formato:</p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>proveedor</th>
                                <th>codigo_interno</th>
                                <th>respuesta_proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Proveedor A</td>
                                <td>REP001 (CODIGO PROVEEDOR)</td>
                                <td>OK</td>
                            </tr>
                            <tr>
                                <td>Proveedor A</td>
                                <td>REP002 (CODIGO PROVEEDOR)</td>
                                <td>OK</td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm w-100 my-2" onclick="importar_excel_stock_minimo()">Importar</button>
                </div>
                <div class="col-md-4">
                    <!-- MOSTRAMOS LAS IMPORTACIONES EXCEL -->
                    <h5>Últimas importaciones</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($importaciones_excel as $importacion)
                                <tr>
                                    <td>{{ $importacion->proveedor ? $importacion->proveedor->empresa_nombre_corto : 'N/A' }}</td>
                                    <td>{{ $importacion->created_at }}</td>
                                    <td>{{ $importacion->usuario ? $importacion->usuario->name : 'N/A' }}</td>
                                    <td>{{ $importacion->estado }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        
      </div>
    </div>
</div>
<!-- fin modal importar excel -->
<!-- Se guarda el id del repuesto en un campo hidden -->
<input type="hidden" id="id_repuesto">
@endsection