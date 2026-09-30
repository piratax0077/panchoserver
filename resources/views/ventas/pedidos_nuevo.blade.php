@extends('plantillas.app')

@section('titulo','Generar pedido')

@section('javascript')
    <script>
        var items = [];
        window.onload= function(){
           
            document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
            document.querySelectorAll("#tablero_select *").forEach(el => el.setAttribute("disabled", "true"));
            revisar_pedidos();
        }

        function revisar_pedidos(){
            console.log('preguntando ...');
            let url = "/ventas/revisar_pedidos";
            $.ajax({
                type:'get',
                url: url,
                beforeSend: function(){
                    
                },
                success: function(resp){
                    console.log(resp);
                    // validar 
                    if(resp.length > 0){
                        let pedidos_terrestre = resp[0];
                        let pedidos_aereos = resp[1];
                        $('#tbody_revision_pedidos_terrestres').empty();
                        $('#tbody_revision_pedidos_aereos').empty();
                        pedidos_terrestre.forEach( pedido => {
                            $('#tbody_revision_pedidos_terrestres').append(`
                            <tr> 
                                <td><a href="javascript:void(0)" onclick="cargar_pedido_modal(`+pedido.id+`)">`+pedido.num_abono+` </a></td>
                                <td>`+pedido.fecha_emision+` </td>
                                <td>`+pedido.nombre_cliente+` </td>
                                <td>`+pedido.responsable+` </td>
                                <td>`+pedido.por_encargo+` </td>
                                <td>`+pedido.por_cobrar+`</td>
                                <td><button class='btn btn-success btn-sm' onclick='imprimir_pedido_antiguo(`+pedido.id+`)'><i class="fa-solid fa-print"></i> </button>  </td>
                                <td><button class='btn btn-warning btn-sm' onclick='imprimir_pedido_admin(`+pedido.id+`)'><i class="fa-solid fa-print"></i></button> </td>
                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_cliente_modal(`+pedido.id+`)'>X</button> </td>
                            </tr>
                            `);
                        });

                        pedidos_aereos.forEach( pedido => {
                            $('#tbody_revision_pedidos_aereos').append(`
                            <tr> 
                                <td><a href="javascript:void(0)" onclick="cargar_pedido_modal(`+pedido.id+`)">`+pedido.num_abono+`</a> </td>
                                <td>`+pedido.fecha_emision+` </td>
                                <td>`+pedido.nombre_cliente+` </td>
                                <td>`+pedido.responsable+` </td>
                                <td>`+pedido.por_encargo+` </td>
                                <td>`+pedido.por_cobrar+`</td>
                                <td><button class='btn btn-success btn-sm' onclick='imprimir_pedido_antiguo(`+pedido.id+`)'><i class="fa-solid fa-print"></i> </button> </td>
                                <td><button class='btn btn-warning btn-sm' onclick='imprimir_pedido_admin(`+pedido.id+`)'><i class="fa-solid fa-print"></i></button> </td>
                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_cliente_modal(`+pedido.id+`)'>X</button> </td>
                            </tr>
                            `);
                        });
                        $('#modalRevisionPedidos').modal('show');
                    }
                    
                },
                error: function(err){
                    console.log(err);
                }
            });
        }

        function registrar_cliente(){
            let nombre = $('#nombre').val();
            let telefono = $('#telefono').val();
            let email = $('#email').val();
            let patente = $('#patente').val();
            let responsable = $('#responsable').val();

            if(nombre === ''){
                Vue.swal({
                    text: 'Ingrese nombre correcto',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#nombre').focus();
                return false;
            }

            if(telefono === ''){
                Vue.swal({
                    text: 'Ingrese telefono correcto',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#telefono').focus();
                return false;
            }

            if(email === ''){
                Vue.swal({
                    text: 'Ingrese email correcto',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#email').focus();
                return false;
            }

            if(responsable == 0){
                Vue.swal({
                    text: 'Seleccione un responsable',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#responsable').focus();
                return false;
            }

            let parametros = {
                nombre: nombre,
                telefono: telefono,
                email: email,
                patente: patente,
                responsable: responsable
            };
            let url = '/ventas/nuevo_abono';

            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            success:function(resp){
                if (!resp || !Number.isInteger(Number(resp.id)) || Number(resp.id) <= 0) {
                    Vue.swal({title: 'ERROR', text: 'No se pudo crear el pedido.', icon: 'error'});
                    return;
                }
                if(resp == 'no'){
                    Vue.swal({
                        text: 'Cliente ya existe ... ingrese uno nuevo',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    return false;
                }else{
                //Recibo el id del nuevo abono y lo asigno a un input para poder registrar los nuevos productos
                $('#id_abono').val(resp.id);
                cargar_pedido(resp.id);
                //Se remueven los atributos disabled para poder registrar nuevos productos
                document.querySelectorAll("#tablero *").forEach(el => el.removeAttribute("disabled"));
                //Se bloquean los input para no modificar los datos del cliente
                document.querySelectorAll("#info_cliente *").forEach(el => el.setAttribute("disabled", "true"));

                document.querySelectorAll("#tablero_select *").forEach(el => el.removeAttribute("disabled"));
                
                Vue.swal({
                    icon:'success',
                    title:'Felicidades',
                    text:'Cliente ingresado'
                });
                }
                
            },
            error: function(err){
                console.log(err);
                Vue.swal({title: 'ERROR', text: 'No se pudo crear el pedido.', icon: 'error'});
            }
        });
        }

        function nuevo_cliente(){
            document.getElementById('btn_registrar_cliente').removeAttribute('disabled');
            
            $('#btn_pago').attr('disabled','true');
            document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
            limpiar_todo();
        }
        
        function agregar_repuesto(){
            if (!Number.isInteger(Number($('#id_abono').val())) || Number($('#id_abono').val()) <= 0) {
                Vue.swal({title: 'ERROR', text: 'Primero registra o selecciona un pedido.', icon: 'error'});
                return;
            }
            let cantidad = $('#cantidad').val();
            let codigo_proveedor = $('#codigo_proveedor').val();
            let descripcion = $('#descripcion').val();
            let precio_unitario = $('#precio_unitario').val();
            let proveedor = $('#proveedor').val();
            let total = cantidad * precio_unitario;

            let id_abono = $('#id_abono').val();
            $('#total').val(total);

            if(cantidad === '' || cantidad > 10 || cantidad < 0){
            Vue.swal({
                    text: 'Ingrese una cantidad correcta',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#cantidad').focus();
                return false;
            }

            if(codigo_proveedor === ''){
                Vue.swal({
                        text: 'Ingrese un código de proveedor correcto',
                        position: 'top-end',
                        icon: 'warning',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    $('#codigo_proveedor').focus();
                    return false;
                }

            if(descripcion === ''){
                Vue.swal({
                        text: 'Ingrese una descripción correcta',
                        position: 'top-end',
                        icon: 'warning',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    $('#descripcion').focus();
                    return false;
                }

            if(precio_unitario === '' || precio_unitario < 0){
                Vue.swal({
                        text: 'Ingrese un valor unitario correcto',
                        position: 'top-end',
                        icon: 'warning',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                $('#precio_unitario').focus();
                return false;
            }

            if(proveedor === ''){
                Vue.swal({
                        text: 'Seleccione un proveedor',
                        position: 'top-end',
                        icon: 'warning',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                $('#proveedor').focus();
                return false;
            }
            let parametros = { 
                id_abono: id_abono,
                cantidad: cantidad, 
                codigo_proveedor: codigo_proveedor,
                descripcion: descripcion, 
                precio: precio_unitario,
                total: total,
                id_proveedor: proveedor
            };

            let url = '/clientes/abonar';
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            beforeSend: function(){
                $('#tbody_historial').html('CARGANDO ...');
            },
            success:function(resp){
                console.log(resp);
                $('#tbody_historial').empty();
                $('#precio_lista').empty();
                if(resp[2].length > 0){
                    var total_precio_lista = 0;
                    Vue.swal.close();
                    if(resp[0]=="OK"){
                        Vue.swal({
                            text: 'Listo...',
                            position: 'top-end',
                            icon: 'info',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                        $('#total').val(total);
                        $('#id_abono').val(resp[1]);
                        
                        resp[2].forEach(item => {
                            
                            total_precio_lista += item.total;
                            $('#tbody_historial').append(`
                                <tr>
                                    <td>`+item.cantidad+` </td>
                                    <td>`+item.descripcion+` </td>
                                    <td>$`+new Intl.NumberFormat().format(item.precio_unitario)+` </td>
                                    <td>$`+new Intl.NumberFormat().format(item.total)+` </td>
                                    <td>`+item.empresa_nombre_corto+`</td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.idrep+`)">X</button> </td>
                                </tr>
                                `);
                        });
                        $('#precio_lista').val(total_precio_lista);
                        let abono = $('#abono').val();
                        $('#saldo_pendiente').val(total_precio_lista - abono);
                        // $('.form-control').removeAttr('disabled');
                        // limpiar_controles();
                        document.querySelectorAll("#tablero_select *").forEach(el => el.removeAttribute("disabled"));
                    }
                }else{
                    $('#tbody_historial').append('No existen pedidos');
                }
            },
            error: function(error){
                Vue.swal.close();
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
            }
        });
    }

        function soloNumeros(e)
        {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
        }

        function limpiar_controles(){
            // document.getElementById('buscado').value = "";
            document.getElementById("descripcion").value="";
            document.getElementById("cantidad").value="";
            document.getElementById("precio_unitario").value="";
            document.getElementById("total").value="";
            // $('#nombre').val('');
            // $('#telefono').val('');
            // $('#abono').val('');
            // $('#saldo_pendiente').val('');
            // $('#precio_lista').val('');
            
        }

        function limpiar_todo(){
            $('#id_abono').val('');
            document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
            document.querySelectorAll("#tablero_select *").forEach(el => el.setAttribute("disabled", "true"));
            document.querySelectorAll("#info_cliente *").forEach(el => el.removeAttribute("disabled"));
            // document.getElementById('buscado').value = "";
            document.getElementById("descripcion").value="";
            document.getElementById("cantidad").value="";
            document.getElementById("precio_unitario").value="";
            document.getElementById("total").value="";
            $('#tbody_historial').empty();
            $('#nombre').val('');
            $('#telefono').val('');
            $('#email').val('');
            $('#patente').val('');
            $('#responsable').val('');
            $('#abono').val('');
            $('#saldo_pendiente').val('');
            $('#precio_lista').val('');
        }

        function registrar(){
            let por_encargo = $('input:radio[name=tipo_envio]:checked').val();
                let por_cobrar = $('input:radio[name=por_cobrar]:checked').val();
                let id_abono = $('#id_abono').val();
                let abono = $('#abono').val();
                let saldo_pendiente = $('#saldo_pendiente').val();
                let precio_lista = $('#precio_lista').val();
                let forma_pago = $('#forma_pago').val();

            if(id_abono == ''){
                Vue.swal({
                    icon:'error',
                    title:'error',
                    text:'Debe registrar un nuevo producto',
                });

                return false;
            }

            if(abono === '' || abono < 0){
                Vue.swal({
                    text: 'Ingrese una cantidad de abono',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#abono').focus();
                return false;
            }

            if(saldo_pendiente === '' || saldo_pendiente < 0){
                Vue.swal({
                    text: 'Ingrese saldo pendiente',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#saldo_pendiente').focus();
                return false;
            }

            if(precio_lista === '' || precio_unitario < 0){
                Vue.swal({
                        title: 'ERROR',
                        text: 'El valor PRECIO_LISTA no puede ser vacio o negativo',
                        icon: 'error',
                    });
                return false;
            }

            if(parseInt(abono) > parseInt(precio_lista)){
                Vue.swal({
                    text: 'El ABONO no puede ser mayor que el PRECIO_LISTA',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#abono').focus();
                return false;
            }

            if(forma_pago == 0){
                Vue.swal({
                    text: 'Seleccione una forma de pago',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                $('#forma_pago').focus();
                return false;
            }

            let parametros = {
                por_encargo: por_encargo, 
                por_cobrar: por_cobrar, 
                id_abono: id_abono, 
                abono: abono, 
                saldo_pendiente: saldo_pendiente, 
                precio_lista: precio_lista, 
                forma_pago: forma_pago
                
            };

            let url = '/clientes/abonar_detalle';
                Vue.swal({ 
                    title: '¿Estás seguro?',
                    text: "Es necesario fijarse en el saldo pendiente",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
                }).then((result)=>{
                    if(result.isConfirmed){
                

                        $.ajaxSetup({
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $.ajax({
                            type:'POST',
                            url:url,
                            data:parametros,
                            success:function(resp){
                                
                                $('btn-imprimir').removeAttr('disabled');
                                Vue.swal.close();
                                if(resp =="OK"){
                                    Vue.swal({
                                    title: 'Pedido registrado',
                                    html:'<h3 id="envio-txt">Su pedido ha sido registrado. Puede imprimir</h3>'+
                                        '<button  class="btn btn-success form-control-sm" onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                                    allowOutsideClick:false,
                                    allowEscapeKey:false,
                                    showConfirmButton:false,
                                    showCancelButton:true
                                    //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estadoUP()
                                    });
                                    
                                    $('#tbody_historial').empty();
                                    document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
                                    document.querySelectorAll("#tablero_select *").forEach(el => el.setAttribute("disabled", "true"));
                                    
                                    document.querySelectorAll("#info_cliente *").forEach(el => el.removeAttribute("disabled"));
                                    
                                }
                            },
                            error: function(error){
                                Vue.swal.close();
                                    Vue.swal({
                                        title: 'ERROR',
                                        text: error.responseText,
                                        icon: 'error',
                                    });
                            }
                        });
              }
          })
        
    }
        function ver_historial(){
            let url = "/clientes/dame_abonos";
            $.ajax({
                type:'get',
                url: url,
                beforeSend: function(){
                    $('#tbody_clientes').html('CARGANDO ...');
                },
                success: function(resp){
                    
                    $('#tbody_clientes').empty();
                    if(resp.length === 0){
                        $('#tbody_clientes').append('No existen pedidos registrados');
                    }else{
                        resp.forEach(pedido => {
                            console.log(pedido);
                    
                            $('#tbody_clientes').append(`
                                <tr>
                                    <td>`+pedido.num_abono+` </td>
                                    <td>`+pedido.fecha_emision+` </td>
                                    <td> <a href='javascript:cargar_pedido(`+pedido.id+`)'> `+pedido.nombre_cliente+`</a> </td>
                                    <td>`+pedido.por_encargo+` </td>
                                    <td>`+pedido.por_cobrar+`</td>
                                    <td>`+pedido.responsable+`</td>
                                    <td><button class='btn btn-success btn-sm' onclick='imprimir_pedido_antiguo(`+pedido.id+`)'><i class="fa-solid fa-print"></i> </button> </td>
                                    <td><button class='btn btn-danger btn-sm' onclick='eliminar_cliente(`+pedido.id+`)'>X</button> </td>
                                </tr>
                                
                            `);
                        });
                    }
                    
                },
                error: function(err){
                    console.log(err);
                }
            });
        }

        function cargar_pedido(pedido_id){
            
            let url = '/ventas/cargar_pedido/'+pedido_id;
            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                    console.log(resp);
                    let cliente = resp[0];
                    let repuestos_pedidos = resp[1];

                    $('#contenedor_repuestos_pedidos_historial').empty();
                    $('#id_abono').val(cliente.id);

                    repuestos_pedidos.forEach(repuesto => {

                        $('#contenedor_repuestos_pedidos_historial').append(`
                            <tr>
                                <td>${repuesto.cantidad}</td>

                                <td>
                                    ${repuesto.descripcion}
                                    <br>
                                    <small class="text-muted">
                                        ${repuesto.empresa_nombre_corto}
                                    </small>
                                </td>

                                <td>
                                    $${new Intl.NumberFormat().format(repuesto.total)}
                                </td>
                            </tr>
                        `);

                    });

                    $('#tbody_historial').empty();
                    repuestos_pedidos.forEach(item => {
                        $('#tbody_historial').append(`
                            <tr>
                                <td>`+item.cantidad+` </td>
                                <td>`+item.descripcion+` </td>
                                <td>$`+new Intl.NumberFormat().format(item.precio_unitario)+` </td>
                                <td>$`+new Intl.NumberFormat().format(item.total)+` </td>
                                <td>`+item.empresa_nombre_corto+`</td>
                                <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.idrep+`)">X</button> </td>
                            </tr>
                            `);
                    });

                    document.querySelectorAll("#tablero *").forEach(el => el.removeAttribute("disabled"));
                    // document.querySelectorAll("#info_cliente *").forEach(el => el.setAttribute("disabled", "true"));
                    document.getElementById('btn_registrar_cliente').setAttribute('disabled','true');
                    $('#precio_lista').val(cliente.precio_lista);
                    $('#abono').val(cliente.abono);
                    $('#saldo_pendiente').val(cliente.saldo_pendiente);
                    $('#forma_pago').val(cliente.forma_pago);
                },
                error: function(err){   
                    console.log(err);
                }
            })
        }

        function cargar_pedido_modal(pedido_id){

            let url = '/ventas/cargar_pedido/'+pedido_id;

            $.ajax({
                type:'get',
                url: url,

                success: function(resp){

                    let cliente = resp[0];
                    let repuestos_pedidos = resp[1];

                    $('#contenedor_repuestos_pedidos').empty();

                    repuestos_pedidos.forEach(repuesto => {

                        $('#contenedor_repuestos_pedidos').append(`
                            <tr>
                                <td>${repuesto.cantidad}</td>

                                <td>
                                    ${repuesto.descripcion}
                                    <br>
                                    <small class="text-muted">
                                        ${repuesto.empresa_nombre_corto}
                                    </small>
                                </td>

                                <td>
                                    $${new Intl.NumberFormat().format(repuesto.total)}
                                </td>
                            </tr>
                        `);

                    });

                },

                error: function(err){
                    console.log(err);
                }

            });

        }

        function eliminar_pedido(idrep){
            Vue.swal({ 
            title: '¿Estás seguro?',
            text: "Está eliminando el pedido",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
          }).then((result) => {
            if (result.isConfirmed) {
                let id_abono = document.getElementById('id_abono').value;
                let url = '/ventas/eliminar_pedido/'+idrep+'/'+id_abono;
                
                $.ajax({
                    type:'get',
                    url: url,
                    success: function(resp){
                        if (!Array.isArray(resp)) {
                            Vue.swal({title: 'ERROR', text: 'No se pudo actualizar el pedido. Recarga el pedido para verificar su estado.', icon: 'error'});
                            return;
                        }
                        $('#tbody_historial').empty();
                        var total_precio_lista = 0;
                        if(resp.length > 0){
                            

                            resp.forEach(item => {
                                total_precio_lista += Number(item.total) || 0;
                                $('#tbody_historial').append(`
                                            <tr>
                                                <td>`+item.cantidad+` </td>
                                                <td>`+item.descripcion+` </td>
                                                <td>$`+new Intl.NumberFormat().format(item.precio_unitario)+` </td>
                                                <td>$`+new Intl.NumberFormat().format(item.total)+` </td>
                                                <td>`+item.empresa_nombre_corto+`</td>
                                                <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.idrep+`)">X</button> </td>
                                            </tr>
                                            `);
                            });


                            document.querySelectorAll("#tablero_select *").forEach(el => el.removeAttribute("disabled"));
                        }else{
                            $('#tbody_historial').append(`
                            <tr>
                                <td colspan="6">No hay pedidos</td>
                            </tr>
                            `);
                        }
            
                        $('#precio_lista').val(total_precio_lista);
                        $('#saldo_pendiente').val(total_precio_lista - (Number($('#abono').val()) || 0));
                        },
                        error: function(err){
                            console.log(err);
                            Vue.swal({title: 'ERROR', text: 'No se pudo completar la solicitud. Recarga el pedido para verificar su estado.', icon: 'error'});
                        }
                    })
              
            }else{
              console.log("La solicitud no se ha eliminado");
            }
          });
  }

  function eliminar_cliente(id_abono){
    let url = '/ventas/eliminar_abono/'+id_abono;
        //   var url='{{url("ventas/imprimir_pedido")}}';
        //   let parametros = {id_abono: id_abono};

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

          $.ajax({
              type:'get',
              url: url,
              success: function(resp){
                
                $('#tbody_clientes').empty();
                    if(resp.length === 0){
                        $('#tbody_clientes').append('No existen pedidos registrados');
                    }else{
                        Vue.swal({
                            icon:'success',
                            text:'Abono eliminado con éxito'
                        });
                        ver_historial();
                    }
            },
              
              error: function(err){
                console.log(err.responseText);
              }
          })
  }

  function eliminar_cliente_modal(id_abono){
    Vue.swal({ 
                    title: '¿Estás seguro?',
                    text: "Se eliminará el pedido permantemente",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
                }).then((result) => {
                    if(result.isConfirmed){
                        let url = '/ventas/eliminar_abono_modal/'+id_abono;
                        
                        //   var url='{{url("ventas/imprimir_pedido")}}';
                        //   let parametros = {id_abono: id_abono};

                        $.ajaxSetup({
                                headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                        $.ajax({
                            type:'get',
                            url: url,
                            success: function(resp){
                                
                                if(resp.length > 0){
                                        let pedidos_terrestre = resp[0];
                                        let pedidos_aereos = resp[1];
                                        $('#tbody_revision_pedidos_terrestres').empty();
                                        $('#tbody_revision_pedidos_aereos').empty();
                                        pedidos_terrestre.forEach( pedido => {
                                            $('#tbody_revision_pedidos_terrestres').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido_modal(`+pedido.id+`)">`+pedido.num_abono+`</a> </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.por_encargo+` </td>
                                                <td>`+pedido.por_cobrar+`</td>
                                                <td><button class='btn btn-success btn-sm' onclick='imprimir_pedido_antiguo(`+pedido.id+`)'><i class="fa-solid fa-print"></i> </button>  </td>
                                                <td><button class='btn btn-warning btn-sm' onclick='imprimir_pedido_admin(`+pedido.id+`)'><i class="fa-solid fa-print"></i></button> </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_cliente_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });

                                        pedidos_aereos.forEach( pedido => {
                                            $('#tbody_revision_pedidos_aereos').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido_modal(`+pedido.id+`)">`+pedido.num_abono+` </a> </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.por_encargo+` </td>
                                                <td>`+pedido.por_cobrar+`</td>
                                                <td><button class='btn btn-success btn-sm' onclick='imprimir_pedido_antiguo(`+pedido.id+`)'><i class="fa-solid fa-print"></i> </button> </td>
                                                <td><button class='btn btn-warning btn-sm' onclick='imprimir_pedido_admin(`+pedido.id+`)'><i class="fa-solid fa-print"></i></button> </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_cliente_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });
                                        $('#modalRevisionPedidos').modal('show');
                                    }
                            },
                            
                            error: function(err){
                                console.log(err.responseText);
                            }
                        });
                    }
                })
        
  }

  function calcular_saldo_pendiente(){
          let saldo_pendiente = $('#precio_lista').val() - $('#abono').val();
          console.log(saldo_pendiente);
          $('#saldo_pendiente').val(saldo_pendiente);
      }

      function imprimir(){
          let id_abono = $('#id_abono').val();
        
          let url = '/ventas/imprimir_pedido/'+id_abono;
        //   var url='{{url("ventas/imprimir_pedido")}}';
        //   let parametros = {id_abono: id_abono};

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

          $.ajax({
              type:'get',
              url: url,
              success: function(resp){
               
                Vue.swal.close();

                        let r=JSON.parse(resp);
                            if(r.estado=='OK'){
                                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                                var w=window.open(r.mensaje,'_blank',config);
                                // w.focus();
                            }else{
                                Vue.swal({
                                    title: r.estado,
                                    text: r.mensaje,
                                    icon: 'error',
                                });
                            }
                    limpiar_todo();
                    },
              
              error: function(err){
                console.log(err.responseText);
              }
          })
          
      }

      function imprimir_pedido_antiguo(id_abono){
        let url = '/ventas/imprimir_pedido/'+id_abono;

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

          $.ajax({
              type:'get',
              url: url,
              success: function(resp){
               
                Vue.swal.close();

                let r=JSON.parse(resp);
                    if(r.estado=='OK'){
                        var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                        var w=window.open(r.mensaje,'_blank',config);
                        // w.focus();
                    }else{
                        Vue.swal({
                            title: r.estado,
                            text: r.mensaje,
                            icon: 'error',
                        });
                    }
                limpiar_todo();
                },
              
              error: function(err){
                console.log(err.responseText);
              }
          })
      }

      function pagar_pendiente(){
          let pendiente = $('#saldo_pendiente').val();
          let total = $('#precio_lista').val();
          let id_abono = $('#id_abono').val();
          
          let parametros ={pendiente:pendiente,total:total,id_abono:id_abono};
          let url = '/giftcard';
       
          
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

          $.ajax({
              type:'post',
              data:parametros,
              url:url,
              beforeSend:function(){

              },
              success: function(resp){
                
                let r=JSON.parse(resp);
                            if(r.estado=='OK'){
                                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                                var w=window.open(r.mensaje,'_blank',config);
                                w.focus();
                            }else{
                                Vue.swal({
                                    title: r.estado,
                                    text: r.mensaje,
                                    icon: 'error',
                                });
                            }
              },
              error: function(error){
                console.log(error.responseText);
              }
          });
      }

      function calcular_total(){
        let cantidad = document.querySelector('#cantidad');
        let precio_unitario = document.querySelector('#precio_unitario');
        let total = document.querySelector('#total');
        precio_unitario.addEventListener('input', function () {
            console.log(cantidad.value);
  
            let resultado = parseInt(cantidad.value) * this.value;
            total.value = resultado;
        });
      }

      function imprimir_pedido_admin(id_abono){
        let url = '/ventas/imprimir_pedido_admin/'+id_abono;
        //   var url='{{url("ventas/imprimir_pedido")}}';
        //   let parametros = {id_abono: id_abono};

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

          $.ajax({
              type:'get',
              url: url,
              success: function(url){
               
                Vue.swal.close();
                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w=window.open(url,'_blank',config);
                w.focus();

                limpiar_todo();
            },
              
              error: function(err){
                console.log(err.responseText);
              }
          })
      }
      
    </script>
@endsection

@section('style')
    <style>
        
        .logo_{
            width: 150px;
            margin-bottom: 30px;
        }

        .div_select{
            border: 1px solid black;
            padding: 20px;
            margin-bottom: 10px;
            background: #f2f4a9;
            border-radius: 10px;
        }
    </style>
@endsection

@section('contenido_ingresa_datos')
    <h4 class="titulazo">Generar pedidos</h4>
    <div class="container-fluid" style="width: 97%; margin: 0px auto;">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
        <div class="row mb-4" id="info_cliente">

            <div class="col-md-12">
        
                <div class="card shadow-sm">
        
                    <div class="card-header bg-light">
                        Datos del cliente
                    </div>
        
                    <div class="card-body">
        
                        <div class="row">
        
                            <div class="col-md-3">
                                <label>Nombre del cliente</label>
                                <input type="text"
                                       class="form-control"
                                       name="nombre"
                                       id="nombre"
                                       placeholder="Nombre del cliente">
                            </div>

                            <div class="col-md-2">
                                <label>Teléfono</label>
                                <input type="text"
                                       name="telefono"
                                       id="telefono"
                                       class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Email</label>
                                <input type="text"
                                       name="email"
                                       id="email"
                                       class="form-control">
                            </div>

                            <div class="col-md-2">
                                <label>Patente <small class="text-muted">(opcional)</small></label>
                                <input type="text"
                                       name="patente"
                                       id="patente"
                                       class="form-control text-uppercase"
                                       maxlength="10"
                                       placeholder="Ej: AB1234"
                                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                            </div>

                            <div class="col-md-2">
                                <label>Responsable</label>
        
                                <select name="responsable"
                                        id="responsable"
                                        class="form-control">
        
                                    <option value="">
                                        Seleccione
                                    </option>
        
                                    @foreach($usuarios as $usuario)
                                        <option value="{{$usuario->id}}">
                                            {{$usuario->name}}
                                        </option>
                                    @endforeach
        
                                </select>
        
                            </div>
        
                        </div>
        
        
                        <!-- BOTONES -->
        
                        <div class="mt-3 text-right">
        
                            <button class="btn btn-success btn-sm"
                                    onclick="registrar_cliente()"
                                    id="btn_registrar_cliente">
        
                                Registrar cliente
        
                            </button>
        
                            <button class="btn btn-secondary btn-sm" onclick="revisar_pedidos()">Pedidos urgentes</button>

                            <button class="btn btn-warning btn-sm"
                                    onclick="ver_historial()"
                                    data-toggle="modal"
                                    data-target="#exampleModal">
        
                                Historial de pedidos
        
                            </button>
        
        
                            <button class="btn btn-primary btn-sm"
                                    onclick="nuevo_cliente()">
        
                                Nuevo cliente
        
                            </button>
        
                        </div>
        
                    </div>
        
                </div>
        
            </div>
        
        </div>
        
        <div id="tablero" class="mb-4">

            <div class="row">
        
                <!-- PANEL INGRESO -->
                <div class="col-md-8">
        
                    <div class="card shadow-sm">
        
                        <div class="card-header bg-light">
                            Agregar repuesto
                        </div>
        
                        <div class="card-body">

                            <!-- FILA 1 -->
                        
                            <div class="row">
                        
                                <div class="col-md-2">
                                    <label>Cantidad</label>
                                    <input type="text"
                                           id="cantidad"
                                           class="form-control"
                                           onkeypress="return soloNumeros(event)">
                                </div>
                        
                                <div class="col-md-3">
                                    <label>Cód. proveedor</label>
                                    <input type="text"
                                           id="codigo_proveedor"
                                           class="form-control"
                                           placeholder="Código proveedor">
                                </div>
                        
                                <div class="col-md-4">
                                    <label>Descripción</label>
                                    <input type="text"
                                           id="descripcion"
                                           class="form-control"
                                           placeholder="Descripción o código interno">
                                </div>
                        
                                <div class="col-md-3">
                                    <label>Proveedor</label>
                                    <select id="proveedor"
                                            class="form-control">
                        
                                        <option value="">
                                            Seleccione
                                        </option>
                        
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{$proveedor->id}}">
                                                {{$proveedor->empresa_nombre_corto}}
                                            </option>
                                        @endforeach
                        
                                    </select>
                                </div>
                        
                            </div>
                        
                        
                            <!-- FILA 2 -->
                        
                            <div class="row mt-3">
                        
                                <div class="col-md-3">
                                    <label>Precio unitario</label>
                                    <input type="number"
                                           id="precio_unitario"
                                           class="form-control"
                                           onkeyup="calcular_total()">
                                </div>
                        
                                <div class="col-md-3">
                                    <label>Total</label>
                                    <input type="number"
                                           id="total"
                                           class="form-control"
                                           disabled>
                                </div>
                        
                                <div class="col-md-6 d-flex align-items-end">
                        
                                    <button class="btn btn-success mr-2"
                                            onclick="agregar_repuesto()">
                        
                                        Agregar repuesto
                        
                                    </button>
                        
                                    <button class="btn btn-secondary"
                                            onclick="limpiar_controles()">
                        
                                        Limpiar
                        
                                    </button>
                        
                                </div>
                        
                            </div>
                        
                        </div>
        
                    </div>
        
                </div>
        
        
                <!-- PANEL HISTORIAL -->
                <div class="col-md-4">
        
                    <div class="card shadow-sm">
        
                        <div class="card-header bg-light">
                            Repuestos agregados
                        </div>
        
                        <div class="card-body p-0">
        
                            <div class="mt-4" style="max-height:350px; overflow:auto;">
        
                                <table class="table table-sm table-striped mb-0">
        
                                    <thead class="thead-light">
        
                                        <tr>
                                            <th>Cant</th>
                                            <th>Desc</th>
                                            <th>P.U</th>
                                            <th>Total</th>
                                            <th>Prov</th>
                                            <th></th>
                                        </tr>
        
                                    </thead>
        
                                    <tbody id="tbody_historial"
                                           style="font-size:12px;">
        
                                    </tbody>
        
                                </table>
        
                            </div>
        
                        </div>
        
                        <div class="card-footer text-right">
        
                            <button class="btn btn-warning btn-sm"
                                    onclick="pagar_pendiente()"
                                    id="btn_pago">
        
                                Pagar saldo pendiente
        
                            </button>
        
                        </div>
        
                    </div>
        
                </div>
        
            </div>
        
        </div>
     
        <div id="tablero_select">
            <div class="row">
                <div class="col-md-8">

                    <div class="card shadow-sm">
                
                        <div class="card-header bg-light">
                            Datos de pago
                        </div>
                
                        <div class="card-body">
                
                            <div class="row">
                
                                <div class="col-md-3">
                                    <label>Precio lista</label>
                                    <input type="text"
                                            id="precio_lista"
                                            name="precio_lista"
                                            class="form-control"
                                            disabled>
                                </div>
                
                                <div class="col-md-3">
                                    <label>Abono</label>
                                    <input type="text"
                                            id="abono"
                                            name="abono"
                                            class="form-control"
                                            onkeypress="return soloNumeros(event)">
                                </div>
                
                                <div class="col-md-3">
                                    <label>Saldo pendiente</label>
                                    <input type="text"
                                            id="saldo_pendiente"
                                            name="saldo_pendiente"
                                            class="form-control"
                                            disabled>
                                </div>
                
                                <div class="col-md-3">
                                    <label>Forma de pago</label>
                
                                    <select class="form-control"
                                            name="forma_pago"
                                            id="forma_pago">
                
                                        <option value="">Seleccione</option>
                
                                        @foreach($formas_pago as $fp)
                                            <option value="{{ $fp->id }}">
                                                {{ $fp->formapago }}
                                            </option>
                                        @endforeach
                
                                    </select>
                
                                </div>
                
                            </div>
                
                
                            <div class="row mt-3">
                
                                <div class="col-md-12 text-right">
                
                                    <button class="btn btn-success"
                                            onclick="calcular_saldo_pendiente()">
                
                                        Calcular saldo
                
                                    </button>
                
                                </div>
                
                            </div>
                
                        </div>
                
                    </div>
                
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">

                    <div class="card shadow-sm mt-3">
                
                        <div class="card-header bg-light">
                            Opciones del pedido
                        </div>
                
                        <div class="card-body">
                
                            <!-- SERVICIO POR ENCARGO -->
                
                            <div class="row mb-3">
                
                                <div class="col-md-4">
                                    <label class="font-weight-bold">
                                        Servicio por encargo
                                    </label>
                                </div>
                
                                <div class="col-md-8">
                
                                    <div class="form-check form-check-inline">
                
                                        <input class="form-check-input"
                                                type="radio"
                                                name="tipo_envio"
                                                id="flexRadioAereo"
                                                value="aereo">
                
                                        <label class="form-check-label"
                                                for="flexRadioAereo">
                
                                            Aéreo
                
                                        </label>
                
                                    </div>
                
                
                                    <div class="form-check form-check-inline">
                
                                        <input class="form-check-input"
                                                type="radio"
                                                name="tipo_envio"
                                                id="flexRadioTerrestre"
                                                value="terrestre"
                                                checked>
                
                                        <label class="form-check-label"
                                                for="flexRadioTerrestre">
                
                                            Terrestre
                
                                        </label>
                
                                    </div>
                
                                </div>
                
                            </div>
                
                
                            <!-- POR COBRAR -->
                
                            <div class="row mb-3">
                
                                <div class="col-md-4">
                                    <label class="font-weight-bold">
                                        Por cobrar
                                    </label>
                                </div>
                
                                <div class="col-md-8">
                
                                    <div class="form-check form-check-inline">
                
                                        <input class="form-check-input"
                                                type="radio"
                                                name="por_cobrar"
                                                id="flexRadioCobrarSi"
                                                value="si">
                
                                        <label class="form-check-label"
                                                for="flexRadioCobrarSi">
                
                                            Sí
                
                                        </label>
                
                                    </div>
                
                
                                    <div class="form-check form-check-inline">
                
                                        <input class="form-check-input"
                                                type="radio"
                                                name="por_cobrar"
                                                id="flexRadioCobrarNo"
                                                value="no"
                                                checked>
                
                                        <label class="form-check-label"
                                                for="flexRadioCobrarNo">
                
                                            No
                
                                        </label>
                
                                    </div>
                
                                </div>
                
                            </div>
                
                        </div>
                
                    </div>
                
                </div>
            </div>
            
            <div class="my-3">
                <button class='btn btn-success btn-sm mb-5' onclick='registrar()'>Registrar </button>
                {{-- <button class='btn btn-warning btn-sm mb-5' onclick='pagar_pendiente()'>Pagar saldo pendiente </button> --}}
            </div>
        </div>
        
        <!-- Modal -->
        <div class="modal fade bd-example-modal-xl" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Listado de clientes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="tabla-scroll-y-500">
                                <table class="table table-striped">
                                    <thead>
                                      <tr>
                                        <th scope="col">N° Pedido</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Tipo</th>
                                        <th scope="col">Por pagar</th>
                                        <th scope="col">Responsable</th>
                                        <th scope="col">Imprimir</th>
                                        <th scope="col">Eliminar</th>
                                      </tr>
                                    </thead>
                                    <tbody id="tbody_clientes">
                                      
                                    </tbody>
                                  </table>
                            </div>
                            
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light p-2">
                                    Repuestos del pedido
                                </div>
                            
                                <div class="card-body p-0">
                                    
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Cant</th>
                                                <th>Detalle</th>
                                                <th>$</th>
                                            </tr>
                                        </thead>
                            
                                        <tbody id="contenedor_repuestos_pedidos_historial">
                            
                                        </tbody>
                            
                                    </table>
                            
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
                </div>
            </div>
            </div>
        </div>
        <!-- Modal revisión de pedidos -->
        <div class="modal fade bd-example-modal-xl" id="modalRevisionPedidos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Listado de Pedidos Urgentes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-9">
                            <p>Listado de pedidos terrestres desde hace 21 días.</p>
                            <table class="table table-striped">
                                <thead>
                                  <tr>
                                    <th scope="col">N° Pedido</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Responsable</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Por cobrar</th>
                                    <th scope="col">Imprimir</th>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                  </tr>
                                </thead>
                                <tbody id="tbody_revision_pedidos_terrestres">
                                  
                                </tbody>
                            </table>
                                <hr>
                                <p>Listado de pedidos aéreos desde hace 14 días.</p>
                              <table class="table table-striped">
                                <thead>
                                  <tr>
                                    <th scope="col">N° Pedido</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Responsable</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Por pagar</th>
                                    <th scope="col">Imprimir</th>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                  </tr>
                                </thead>
                                <tbody id="tbody_revision_pedidos_aereos">
                                  
                                </tbody>
                              </table>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light p-2">
                                    Repuestos del pedido
                                </div>
                            
                                <div class="card-body p-0">
                            
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Cant</th>
                                                <th>Detalle</th>
                                                <th>$</th>
                                            </tr>
                                        </thead>
                            
                                        <tbody id="contenedor_repuestos_pedidos">
                            
                                        </tbody>
                            
                                    </table>
                            
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
                </div>
            </div>
            </div>
        </div>
        <!--Datos de vital importancia -->
    
        <input type="hidden" name="" id="id_abono">
        
    </div>
    
@endsection