@extends('plantillas.app')
@section('titulo','Guía Despacho')


@section('style')
    <style>
        #opciones{
            display: flex;
            background: #deb887;
            width: 100%;
        }
        .formulario{
            width: 80%; 
            background: #fefaef;
        }
    </style>
@endsection

@section('javascript')
    <script>

        var referencia1 = true;
        var referencia2 = true;
        var referencia3 = true;
        var ref1 = [];
        var ref2 = [];
        var ref3 = [];
        var TrackID = 0;
        var arrayParametros = [];
        var id = 0;

        window.onload = function(){
            cargar_documentos_referencia();
        }

        function activar_referencia(cual) {
            if (cual == 1) {
                referencia1 = !referencia1;
                document.getElementById("referencia1").disabled = referencia1;
            }
            if (cual == 2) {
                referencia2 = !referencia2;
                document.getElementById("referencia2").disabled = referencia2;
            }
            if (cual == 3) {
                referencia3 = !referencia3;
                document.getElementById("referencia3").disabled = referencia3;
            }
        }

        function referencias_aceptar() {
            let i = 0;
            let ref1_docu = document.getElementById("ref1_docu").value;
            let ref1_folio = document.getElementById("ref1_folio").value;
            let ref1_fecha = document.getElementById("ref1_fecha").value;
            let ref1_razon = document.getElementById("ref1_razon").value;
            let ref1_check = document.getElementById("ref1_check").checked;

            let ref2_docu = document.getElementById("ref2_docu").value;
            let ref2_folio = document.getElementById("ref2_folio").value;
            let ref2_fecha = document.getElementById("ref2_fecha").value;
            let ref2_razon = document.getElementById("ref2_razon").value;
            let ref2_check = document.getElementById("ref2_check").checked;

            let ref3_docu = document.getElementById("ref3_docu").value;
            let ref3_folio = document.getElementById("ref3_folio").value;
            let ref3_fecha = document.getElementById("ref3_fecha").value;
            let ref3_razon = document.getElementById("ref3_razon").value;
            let ref3_check = document.getElementById("ref3_check").checked;

            let mensaje_error_ref = "";

            if (ref1_check === true) {
                if (ref1_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 1";
                } else if (ref1_folio.length == 0 || isNaN(parseInt(ref1_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 1";
                } else if (ref1_fecha.length == 0) {
                    mensaje_error_ref = "Elija Fecha para Referencia 1";
                } else if (ref1_razon.length == 0) {
                    mensaje_error_ref = "Escriba Razón para Referencia 1";
                }

                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref1.push({
                    docu: ref1_docu,
                    folio: ref1_folio,
                    fecha: ref1_fecha,
                    razon: ref1_razon
                });
                i++;
            } else {
                ref1 = [];
            }

            mensaje_error_ref = "";
            if (ref2_check === true) {
                if (ref2_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 2";
                } else if (ref2_folio.length == 0 || isNaN(parseInt(ref2_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 2";
                } else if (ref2_fecha.length == 0) {
                    mensaje_error_ref = "Elija Fecha para Referencia 2";
                } else if (ref2_razon.length == 0) {
                    mensaje_error_ref = "Escriba Razón para Referencia 2";
                }
                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref2.push({
                    docu: ref2_docu,
                    folio: ref2_folio,
                    fecha: ref2_fecha,
                    razon: ref2_razon
                });
                i++;
            } else {
                ref2 = [];
            }

            mensaje_error_ref = "";
            if (ref3_check === true) {
                if (ref3_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 3";
                } else if (ref3_folio.length == 0 || isNaN(parseInt(ref3_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 3";
                } else if (ref3_fecha.length == 0) {
                    mensaje_error_ref = "Elija Fecha para Referencia 3";
                } else if (ref3_razon.length == 0) {
                    mensaje_error_ref = "Escriba Razón para Referencia 3";
                }
                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref3.push({
                    docu: ref3_docu,
                    folio: ref3_folio,
                    fecha: ref3_fecha,
                    razon: ref3_razon
                });
                i++;
            } else {
                ref3 = [];
            }

            // if (i > 0) {
            //     document.getElementById("referencias").innerHTML = "<b>REFERENCIAS:</b> " + i;
            // } else {
            //     document.getElementById("referencias").innerHTML = "<b>REFERENCIAS:</b> Ninguna";
            // }
            $("#referencias-modal").modal("hide");
        }

        function referencias_cancelar() {

            referencia1 = true;
            document.getElementById("referencia1").disabled = referencia1;
            document.getElementById("ref1_docu").value = 0;
            document.getElementById("ref1_folio").value = "";
            document.getElementById("ref1_fecha").value = "";
            document.getElementById("ref1_razon").value = "";
            document.getElementById("ref1_check").checked = false;
            ref1 = [];

            referencia2 = true;
            document.getElementById("referencia2").disabled = referencia2;
            document.getElementById("ref2_docu").value = 0;
            document.getElementById("ref2_folio").value = "";
            document.getElementById("ref2_fecha").value = "";
            document.getElementById("ref2_razon").value = "";
            document.getElementById("ref2_check").checked = false;
            ref2 = [];

            referencia3 = true;
            document.getElementById("referencia3").disabled = referencia3;
            document.getElementById("ref3_docu").value = 0;
            document.getElementById("ref3_folio").value = "";
            document.getElementById("ref3_fecha").value = "";
            document.getElementById("ref3_razon").value = "";
            document.getElementById("ref3_check").checked = false;
            ref3 = [];
        }

        function procesar(){
            Vue.swal({
                title: 'Confirmar',
                text: '¿Desea procesar la guía de despacho?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    procesar_confirmado();
                }
            
            })
            
        }

        function procesar_confirmado(){
            let rut= $('#rut').val();
            let direccion = $('#direccion').val();
            let comuna = $('#comuna').val();
            let ciudad = $('#ciudad').val();
            let rut_transportista = $('#rut_transportista').val();
            let rut_chofer = $('#rut_chofer').val();
            let nombre_chofer = $('#nombre_chofer').val();
            let patente = $('#patente').val();

            let tipo_despacho = $('#tipo_despacho').val();
            let tipo_traslado = $('#tipo_traslado').val();

            let url = '{{url("/guiadespacho/generarxml")}}';

            if(arrayParametros.length == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'No hay productos en la lista',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(rut.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'RUT cliente vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(direccion.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'DIRECCIÓN cliente vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(comuna.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'COMUNA cliente vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(ciudad.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'CIUDAD cliente vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(rut_transportista.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'RUT transportista vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(rut_chofer.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'RUT chofer vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(nombre_chofer.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'NOMBRE chofer vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }

            if(patente.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'PATENTE vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }
            
            let parametros = {
                ref1: JSON.stringify(ref1),
                ref2: JSON.stringify(ref2),
                ref3: JSON.stringify(ref3),
                rut: rut,
                cliente_direccion: direccion,
                tipo_despacho: tipo_despacho,
                tipo_traslado: tipo_traslado,
                comuna: comuna,
                ciudad: ciudad,
                rut_transportista: rut_transportista,
                rut_chofer: rut_chofer,
                nombre_chofer: nombre_chofer,
                patente: patente,
                id_cliente: $('#id_cliente').val(),
                datos:JSON.stringify(arrayParametros),
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: parametros,
                success: function(rs){
                    console.log(rs);
                        let rpta = JSON.parse(rs);

                        if (rpta.estado == 'GENERADO') {
                            $("#mensajes").html("Generando XML...<b> Listo!!!</b>");
                            $("#envio-txt").html("<b>Generando XML...</b> Listo!!!");
                            documento_procesado = rpta
                                .mensaje; //tiene el tipo y número de documento que se está procesando.
                            //document.getElementById("dcmto_en_envio").value = documento_procesado;

                            abrir_procesar_envio();
                        } else {
                            Vue.swal({
                                title: rpta.estado,
                                text: rpta.mensaje,
                                icon: 'error',
                            });
                        }
                },
                error: function(error){
                    console.log(error);
                }
            });
        }

        function abrir_procesar_envio() {
            $("#titulo_procesar_envio_modal").html("PROCESANDO " + documento_procesado.toUpperCase());
            $('#procesar-envio-modal').on('shown.bs.modal', function() {

            });
            $("#procesar-envio-modal").modal("show");
            //enviar_sii();
        }

        function enviar_sii(){
            let url = '{{url("/guiadespacho/enviarsii")}}';
            // let documento = document.getElementById("dcmto_en_envio").value;
            // let tipoDTE = documento.substring(0, 2);
            // let folio = documento.substring(2);
            let parametros = {
                id_cliente: $('#id_cliente').val(),
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: parametros,
                success: function(rs){
                    console.log(rs);
                    let rpta = JSON.parse(rs);
                    if(rpta.estado == 'OK'){
                        TrackID = rpta.trackID;
                    }
                    if (rpta.estado == 'ACEPTADO') {
                        $("#envio-txt").html("<small>Envío ACEPTADO... puede imprimir</small>");
                        document.getElementById("btn-enviarsii").disabled = true;
                        document.getElementById("btn-verestado").disabled = true;
                        document.getElementById("btn-imprimir").disabled = false;
                        TrackID = rpta.trackID;
                    } else {
                        $("#envio-txt").html("<small>" + rpta.estado + ": " + rpta.mensaje + "</small>");
                        document.getElementById("btn-verestado").disabled = false;
                    }
                },
                error: function(error){
                    console.log(error);
                }
            })
        }

        function enter_buscar_cliente(e){
            let rut = e.target.value;
            if(e.key=='Enter'){
                buscar_cliente(rut);
            }
        }

        function buscar_cliente(rut){
            console.log(rut);
            let url = "{{url('/guiadespacho/damecliente')}}"+"/"+rut;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response){
                    let cliente = response["cliente"];
                    if(cliente != null){
                        if(cliente.tipo_cliente == 0) var tipocliente = "CLIENTE";
                        if(cliente.tipo_cliente == 1) var tipocliente = "EMPRESA";
                        $('#cliente_empresa').val(tipocliente);
                        $('#rut').val(cliente.rut);
                        $('#direccion').val(cliente.direccion);
                        $('#comuna').val(cliente.direccion_comuna);
                        $('#ciudad').val(cliente.direccion_ciudad);
                        $('#id_cliente').val(cliente.id);
                    }else{
                        Vue.swal({
                            icon:'error',
                            toast: true,
                            text:'RUT no encontrado',
                            showConfirmButton: false,
                            position: 'top-end',
                            timer: 3000
                        });
                    }
                }
            })
        }

        function cargar_documento(){
            let numero_documento = $('#numero_documento').val();
            // saber el radio button seleccionado para saber si es factura, factura o cotizacion
            let tipo_documento = $('input[name=documento]:checked').val();
            let dato = tipo_documento+numero_documento;
            let url = "{{url('/guiadespacho/cargar_documento')}}"+"/"+dato;
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response){
                    console.log(response);
                    let documento = response.documento;
                    $('#id_cliente').val(documento.id_cliente);
                    if(documento != null){
                        $('#numero_documento').val(documento.numero_documento);
                        $('#fecha_documento').val(documento.fecha_documento);
                        $('#cliente_empresa').val(documento.cliente_empresa);
                        $('#rut').val(documento.rut);
                        $('#direccion').val(documento.direccion);
                        $('#comuna').val(documento.comuna);
                        $('#ciudad').val(documento.comuna);
                        $('#rut_transportista').val(documento.rut_transportista);
                        $('#rut_chofer').val(documento.rut_chofer);
                        $('#patente').val(documento.patente);
                        $('#observaciones').val(documento.observaciones);
                    }else{
                        Vue.swal({
                            icon:'error',
                            toast: true,
                            text:'Documento no encontrado',
                            showConfirmButton: false,
                            position: 'top-end',
                            timer: 3000
                        });
                    }
                }
            })

        }



        function cargar_documentos_referencia() {
            var url = "{{ url('/dame_tipo_documentos') }}";
            $.ajax({
                type: 'GET',
                url: url,
                success: function(resp) {
                    resp.forEach(function(r) {
                        $("#ref1_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                        $("#ref2_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                        $("#ref3_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                    });
                },
                error: function(error) {
                    console.log(formatear_error(error.responseText));
                    // Vue.swal({
                    //     title: 'ERROR',
                    //     text: formatear_error(error.responseText),
                    //     icon: 'error',
                    // });
                }

            });
        }

        

        function guardar_datos(){
            let descripcion = $('#descripcion').val();
            let cantidad = $('#cantidad').val();
            let precio = $('#precio_con_iva').val();
            // formato de numero a precio
            precio = parseFloat(precio).toFixed(0);
            // separador de miles
            precio = precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            let subtotal = $('#subtotal').val();
            // formato de numero a subtotal
            subtotal = parseFloat(subtotal).toFixed(0);
            // separador de miles
            subtotal = subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            //let url = "{{url('/guiadespacho/guardar_detalle')}}";
            let parametros = {
                descripcion: descripcion,
                cantidad: cantidad,
                precio: precio,
                subtotal: subtotal
            }
            if(descripcion.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'Descripción vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }
            if(cantidad.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'Cantidad vacia',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }
            if(precio.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'Precio vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }
            if(subtotal.trim() == 0){
                Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'Subtotal vacio',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
                return false;
            }
            parametros.id = id;
            // agregar hasta 20 productos
            if(arrayParametros.length > 20){
                return Vue.swal({
                    icon:'error',
                    toast: true,
                    text:'Máximo 20 productos',
                    showConfirmButton: false,
                    position: 'top-end',
                    timer: 3000
                });
            }
            // Agrega los parámetros al array
            arrayParametros.push(parametros);
            $('#tbody_lista_productos').append('<tr id="producto-'+id+'"><td>'+descripcion+'</td><td>'+cantidad+'</td><td>$'+precio+'</td><td>$'+subtotal+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="sacar_item(' + id + ')"><i class="fas fa-trash"></i>Eliminar </button></td></tr>');
            id++;
            $('#descripcion').val('');
            $('#cantidad').val('');
            $('#precio_con_iva').val('');
            $('#subtotal').val('');
            // $.ajax({
            //     url: url,
            //     type: 'POST',
            //     data: parametros,
            //     success: function(response){
            //         console.log(response);
            //     },
            //     error: function(error){
            //         console.log(error);
            //     }
            // })
        }

        function sacar_item(id) {
            // preguntar si desea eliminar el item
            Vue.swal({
                title: 'Eliminar',
                text: '¿Desea eliminar este item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminar_item(id);
                }
            });
            
        }

        function eliminar_item(id){
            $('#producto-'+id).remove();
            arrayParametros.splice(id, 1);
        }

        function ver_estadoUP() {
            //let TrackID=document.getElementById("trackID").value;
            document.getElementById("btn-verestado").disabled = true;
            //let docu = $('input[name="tipo_documento"]:checked').val().trim();
            let tipoDTE = "52";

            var url = '{{ url("sii/verestado") }}' + "/" + tipoDTE + "&" +
                TrackID; //Controlador sii_controlador@ver_estadoUP
            $.ajax({
                type: 'GET',
                beforeSend: function() {

                    $("#mensajes").html("<b>Revisando Estado...</b>");
                    $("#envio-txt").html("<small>Revisando estado...</small>");
                },
                url: url,
                success: function(rs) {
                    rs = JSON.parse(rs);
                    if (rs.estado == 'ACEPTADO') {
                        $("#envio-txt").html("<small>Envío ACEPTADO... puede imprimir</small>");
                        document.getElementById("btn-enviarsii").disabled = true;
                        document.getElementById("btn-verestado").disabled = true;
                        document.getElementById("btn-imprimir").disabled = false;
                        let doc = "";
                    } else {
                        $("#envio-txt").html("<small>" + rs.estado + ": " + rs.mensaje + "</small>");
                        document.getElementById("btn-verestado").disabled = false;
                    }


                },
                error: function(error) {
                    $('#mensajes').html(formatear_error(error.responseText));
                }
            });

        }
    </script>
@endsection

@section('contenido_ingresa_datos')
    
    <div class="formulario">
        <div class="form-group mt-3">
            <label for="tipo_despacho" class="float-left">Tipo despacho</label>
            <select class="form-control form-control-sm w-25" name="tipo_despacho" id="tipo_despacho">
                <option value="0">Ninguno</option>
                <option value="1">Despacho por cuenta del receptor</option>
                <option value="2">Despacho por cuenta del emisor</option>
                <option value="3">Despacho por cuenta del emisor a otras Instalaciones</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tipo_traslado" class="float-left">Tipo traslado</label>
            <select class="form-control form-control-sm w-25" name="tipo_traslado" id="tipo_traslado">
                <option value="1">Operación constituye venta</option>
                <option value="2">Ventas por efectuar</option>
                <option value="3">Consignaciones</option>
                <option value="4">Entrega gratuita</option>
                <option value="5">Traslados internos</option>
                <option value="6">Otros traslados No venta</option>
                <option value="7">Guía de devolución</option>
            </select>
        </div>
        <label for="">RUT Cliente/Empresa: </label>
        <input type="text" name="" id="rut" placeholder="RUT 12345678-9 y enter" onkeyup="enter_buscar_cliente(event)" style="width: 180px;">
        <input type="text" name="" id="cliente_empresa" placeholder="CLIENTE/EMPRESA" style="width: 190px;" disabled> <br>
        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion"  style="width: 200px;">
        <label for="comuna">Comuna</label>
        <input type="text" name="comuna" id="comuna"  style="width: 100px;">
        <label for="ciudad">Ciudad</label>
        <input type="text" name="ciudad" id="ciudad"  style="width: 100px;"> <br>
        <label for="rut_transportista">RUT Transp.:</label>
        <input type="text" name="rut_transportista" id="rut_transportista"  style="width: 100px;" placeholder="12345678-9"> <br>
        <label for="rut_chofer">RUT Chofer:</label>
        <input type="text" name="rut_chofer" id="rut_chofer"  style="width: 100px;" placeholder="12345678-9"> 
        <label for="nombre_chofer">Nombre Chofer:</label>
        <input type="text" name="nombre_chofer" id="nombre_chofer"  style="width: 152px;" >
        <label for="patente">Patente:</label>
        <input type="text" name="patente" id="patente"  style="width: 80px;" >
        <hr>
        <label for="">Elegir documento</label>
        
            <input type="radio" id="documentoCotizacion"
             name="documento" value="co" checked>
            <label for="documento">Cotización</label>
        
            <input type="radio" id="documentoBoleta"
             name="documento" value="bo" >
            <label for="documento">Boleta</label>
        
            <input type="radio" id="documento Factura"
             name="documento" value="fa" >
            <label for="documento">Factura</label>

            <input type="text" name="numero_documento" id="numero_documento" style="width: 50px;" placeholder="Núm">
            <button class="btn btn-warning btn-sm" onclick="cargar_documento()">Buscar</button>
            <button class="btn btn-success btn-sm float-right" onclick="procesar()">PROCESAR</button>
<br>
        <span class="w-100 mt-4">Detalle documento</span><br>
        <input type="text" name="descripcion" id="descripcion" style="width: 300px;" placeholder="Descripción">
        <input type="text" name="cantidad" id="cantidad" style="width: 60px;" placeholder="Cantidad">
        <input type="text" name="precio_con_iva" id="precio_con_iva" style="width: 80px;" placeholder="PrecioNeto">
        <input type="text" name="subtotal" id="subtotal" style="width: 90px;" placeholder="SubTotal">
        <button class="btn btn-sm btn-success" onclick="guardar_datos()">+ Guardar Item</button> <br>
        <table class="table w-50 mt-3">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>SubTotal</th>
                    <th></th>
                </tr>
                <tbody id="tbody_lista_productos">
                    
                </tbody>
            </thead>
        </table>
        <strong class="mt-2">REFERENCIAS: </strong> <br>
        <div class="form-check">
            <input class="form-check-input mb-2 float-left" type="checkbox" value="" id="ref1_check" style="width: 20px;" onclick="activar_referencia(1)">
            <fieldset id="referencia1" class="form-group referencia1" disabled>
                <select name="ref1_docu" id="ref1_docu" class="form-control form-control-sm float-left " style="width: 150px; margin-left:30px;" >
                    <option value="0">Seleccionar documento</option>
                </select>
                <input type="text" name="ref1_folio" id="ref1_folio" placeholder="Folio" class="form-control form-control-sm float-left" style="width: 50px;" >
                <input type="date" name="ref1_fecha" id="ref1_fecha" class="form-control form-control-sm float-left" style="width: 140px;" >
                <input type="text" name="ref1_razon" id="ref1_razon" placeholder="Razón" class="form-control form-control-sm" style="width: 260px;" >
            </fieldset>
            
        </div>
        <div class="form-check">
            <input class="form-check-input mb-2" type="checkbox" value="" id="ref2_check" style="width: 20px; "  onclick="activar_referencia(2)">
            <fieldset id="referencia2" class="form-group referencia2" disabled>
                <select name="ref2_docu" id="ref2_docu" class="form-control form-control-sm float-left " style="width: 150px; margin-left:30px;" >
                    <option value="0">Seleccionar documento</option>
                </select>
                <input type="text" name="ref2_folio" id="ref2_folio" placeholder="Folio" class="form-control form-control-sm float-left" style="width: 50px;" >
                <input type="date" name="ref2_fecha" id="ref2_fecha" class="form-control form-control-sm float-left" style="width: 140px;" >
                <input type="text" name="ref2_razon" id="ref2_razon" placeholder="Razón" class="form-control form-control-sm" style="width: 260px;" >
            </fieldset>
            
        </div>
        <div class="form-check">
            <input class="form-check-input mb-2" type="checkbox" value="" id="ref3_check" style="width: 20px; "onclick="activar_referencia(3)">
            <fieldset id="referencia3" class="form-group referencia3" disabled>
                <select name="ref3_docu" id="ref3_docu" class="form-control form-control-sm float-left " style="width: 150px; margin-left:30px;" >
                    <option value="0">Seleccionar documento</option>
                </select>
                <input type="text" name="ref3_folio" id="ref3_folio" placeholder="Folio" class="form-control form-control-sm float-left" style="width: 50px;" >
                <input type="date" name="ref3_fecha" id="ref3_fecha" class="form-control form-control-sm float-left" style="width: 140px;" >
                <input type="text" name="ref3_razon" id="ref3_razon" placeholder="Razón" class="form-control form-control-sm" style="width: 260px;" >
            </fieldset>
            
        </div>
        <button type="button" class="btn btn-outline-danger" onclick="referencias_cancelar()">Cancelar</button>
        <button type="button" class="btn btn-outline-primary" onclick="referencias_aceptar()">Aceptar</button>
    </div>
<!-- MODAL PROCESAR ENVIO -->
<div class="modal" tabindex="-1" id="procesar-envio-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo_procesar_envio_modal"></h5>
            </div>
            <div class="modal-body">
                <h3 id="envio-txt">Generando XML...</h3>
                <button class="btn btn-outline-info form-control-sm" onclick="enviar_sii()"
                    id="btn-enviarsii"><small>Enviar al SII</small></button>
                <button class="btn btn-outline-dark form-control-sm" disabled onclick="ver_estadoUP()"
                    id="btn-verestado"><small>Ver Estado</small></button>
                <button class="btn btn-outline-success form-control-sm" disabled onclick="imprimir()"
                    id="btn-imprimir">Imprimir</button>
                <div id="cliente_xpress_div" style="display:none">
                    <!-- ESTE MISMO CODIGO ESTA EN EL MODAL DE CLIENTE XPRESS MAS ARRIBA -->
                    <br>
                    <hr>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cerrar Envio</button>
            </div>
        </div>
    </div>
</div> <!-- FIN MODAL PROCESAR ENVIO -->
    <input type="hidden" id="id_cliente" value="">
@endsection
