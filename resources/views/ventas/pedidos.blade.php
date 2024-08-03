@extends('plantillas.app')

@section('titulo','Generar pedido')

@section('javascript')
<script>
    var repuestos = [];
    var por_encargo = false;
    var por_cobrar = false;

    function enter_buscar(e){
        let keycode = e.keyCode;
        if(keycode=='13'){
            buscar_clientes();
        }
    }

    function soloNumeros(e)
    {
        var key = window.Event ? e.which : e.keyCode
        return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
    }
    
    function buscar_clientes(){
        var url="{{url('clientes/buscar/')}}";
        var quien="pedidos";
        var parametros={buscax:"nombres",buscado:document.getElementById("buscado").value,quien:quien};
        var bx=document.getElementById("buscaxrut").checked;

        if(bx) parametros=parametros={buscax:"rut",buscado:document.getElementById("buscado").value,quien:quien};

        $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });

        $.ajax({
            type:'POST',
            beforeSend: function () {
            //$("#mensajes").html("Buscando...");
            espere("Buscando...");
            },
            url:url,
            data:parametros,
            success:function(resp){
                Vue.swal.close();
            //$("#mensajes").html("Listado de Clientes...");
            $('#tabla_ingreso').css('display','none');
            $('#eliminarpedido').css('display','none');
            $('#listar_clientes').css('display','block');
            $("#listar_clientes").html(resp);
            // limpiar_controles();
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

    function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });
    }

    function limpiar_controles()
  {
    // document.getElementById('buscado').value = "";
    document.getElementById("descripcion").value="";
    document.getElementById("cantidad").value="";
    document.getElementById("precio_unitario").value="";
    document.getElementById("total").value="";
    // document.getElementById("abono").value="";
    // document.getElementById("saldo_pendiente").value="";
    // document.getElementById("precio_lista").value="";

    borrar_descuentos();
  }

  window.onload= function(){


    // document.querySelectorAll("#segunda_base *").forEach(el => el.setAttribute("disabled", "true"));
    $('#tabla_ingreso').css('display','none');
    $('#eliminarpedido').css('display','none');
    
    // Vue.swal({
    //             title: 'Info',
    //             text: 'En construccion',
    //             icon: 'info',
    //         });
  }

  function borrar_descuentos()
{
//Borrar la tabla temporal de descuentos.
    var url='{{url("clientes/borrarfamtodo")}}'; //petición
    //modifica=false;

    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Borrar temporal descuentos...");
        //espere("Borrando descuento...");
      },
      url:url,
      success:function(resp){
        //Vue.swal.close();
        //$('#mensajes').html("Borrar temporal descuentos...");
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

function cargar_cliente(idc)
  {
    $('#id_cliente_nuevo_pedido').val(idc);
    modifica=true;
    var contador_repuesto = 0;
    var url='{{url("clientes")}}'+'/'+idc+'/cargar';
    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Cargar Cliente...");
        espere("Cargando Cliente");
      },
      url:url,
      success:function(resp){
          
        Vue.swal.close();
        
        $('#notificaciones').empty();
        //Si el controlador detiene el proceso en caso de que la respuesta venga con
          if(resp === 'error'){
            $('#notificaciones').append(`
                <div class="container-fluid" style="width: 60%; margin: 0px auto;">
                <div class="alert-danger" style="padding: 10px;">
                    No existen pedidos registrados para este cliente 
                </div>
                <button class="btn btn-success btn-sm m-2" onclick="agregar_pedido()">Agregar pedido</button>
                </div>
                `);
                return false;
            }
            //Si el id del abono viene vacio
            if(resp[5].length !== 0){
                $('#id_abono').val(resp[5]);
            }else{
                $('#notificaciones').append(`
                <div class="container-fluid" style="width: 60%; margin: 0px auto;">
                <div class="alert-danger" style="padding: 10px;">
                    No existen pedidos registrados para este cliente 
                </div>
                <button class="btn btn-success btn-sm m-2" onclick="agregar_pedido()">Agregar pedido</button>
                </div>
                `);
            }
          //Si no existen pedidos
        if(resp[1].length === 0){
            $('#notificaciones').append(`
            <div class="container-fluid" style="width: 60%; margin: 0px auto;">
            <div class="alert-danger" style="padding: 10px;">
                No existen pedidos registrados para este cliente 
            </div>
            <button class="btn btn-success btn-sm m-2" onclick="agregar_pedido()">Agregar pedido</button>
            </div>
            `);
        }else{
          
          var precio_lista = 0;
        //Llenar los datos del cliente en los controles.
        //viene en JSON desde el controlador
        $('#notificaciones').append();
        $('#tabla_ingreso').css('display','block');
        var ccc=JSON.parse(resp[0]);
        $('#id_cliente').val(ccc.id);
        $('#listar_clientes').empty();
        $('#tbody_historial').empty();
        if(resp[4].length > 0){
            resp[4].forEach(item => {
            precio_lista += item.total;
            $('#tbody_historial').append(`
            <tr>
                <td>`+item.cantidad+` </td>
                <td>`+item.descripcion+` </td>
                <td>$`+new Intl.NumberFormat().format(item.precio_unitario)+` </td>
                <td>$`+new Intl.NumberFormat().format(item.total)+` </td>
                <td class='text-uppercase'>`+item.descripcion_estado+`</td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.id+`)">X</button> </td>
            </tr>
            `)
        });
        }else{
            $('#tbody_historial').append(`
            <tr>
                <td>No hay pedidos</td>
            </tr>
            `);
        }
        
        $('#eliminarpedido').css('display','block');
        $('#eliminarpedido').empty();
        $('#botonera').empty();
        $('#botonera').append(`
            
        `);
        $('#listar_clientes').append(`
        <button class="btn btn-warning btn-sm">Imprimir </button>
        <div id="info_cliente">
        <h4>Nombre: `+ccc.nombres+` `+ccc.apellidos+` </h4>
        <h6>Telefono: `+ccc.telf1+` </h6>
        <h6>Razón social: `+ccc.razon_social+` </h6>
        <h6>Dirección: `+ccc.direccion+` </h6>
        </div>
        <div class='row mt-3'>
            <div class='col-md-2'>
                <p class='thead'>Cantidad</p>
            </div>
            <div class='col-md-4'>
                <p class='thead'>Descripcion</p>
            </div>
            <div class='col-md-3'>
                <p class='thead'>Precio unitario</p>
            </div>
            <div class='col-md-3'>
                <p class='thead'>Total</p>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-2'> 
                <input type='text' id='cantidad' name='cantidad' class='form-control' placeholder="Ingrese cantidad" onKeyPress="return soloNumeros(event)"  />
            </div>
            <div class='col-md-4'>
                <input type='text' id='descripcion' name='descripcion' placeholder="Ingrese descripcion o código interno del repuesto" style='width: 100%' class='form-control' />
            </div>
            <div class='col-md-3'>
                <input type='number' id='precio_unitario' name='precio_unitario' placeholder="Ingrese valor unitario" class='form-control' />
            </div>
            <div class='col-md-3'>
                <input type='number' id='total' name='total' class='form-control' disabled />
            </div>
        </div>
        
        <button class='btn btn-success btn-sm mt-4' onclick='agregar_repuesto()'>Agregar repuesto </button>
        <button class='btn btn-warning btn-sm mt-4' onclick='limpiar_controles()'>Limpiar </button>
        <div id='resumen'>

        </div>
        <div class='row mt-5'>
            <div class='col-md-4'>
                <p class='thead'>Abono</p>
            </div>
            <div class='col-md-4'>
                <p class='thead'>Saldo pendiente</p>
            </div>
            <div class='col-md-4'>
                <p class='thead'>Precio lista</p>
            </div>
        </div>
        <div id='segunda_base'>
        <div class='row'>
            <div class='col-md-4'>
                <input type='text' name='abono' id='abono' class='form-control' onKeyPress="return soloNumeros(event)"/>
            </div>
            <div class='col-md-4'>
                <input type='text' name='saldo_pendiente' id='saldo_pendiente' value=$`+new Intl.NumberFormat().format(resp[2]) +` class='form-control' disabled />
            </div>
            <div class='col-md-4'>
                <input type='text' name='precio_lista' id='precio_lista' value=$`+new Intl.NumberFormat().format(precio_lista)+` class='form-control' disabled />
            </div>
        </div>
        </div>
        <div class='row mt-5 div_select'>
            <div class='col-md-6'>
                <label>Servicio por encargo </label>
            </div>
            <div class='col-md-6'>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault1" id="flexRadioAereo" value='aereo'>
                    <label class="form-check-label" for="flexRadioDefault1">
                        Aereo
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault1" id="flexRadioterrestre" value='terrestre' checked>
                    <label class="form-check-label" for="flexRadioDefault2">
                        Terrestre
                    </label>
                </div>
            </div>
        </div>
        <div class='row mt-5 div_select'>
            <div class='col-md-6'>
                <label>Por cobrar </label>
            </div>
            <div class='col-md-6'>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault2" id="flexRadioCobrarSi" value='si'>
                    <label class="form-check-label" for="flexRadioDefault2">
                        Sí
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault2" id="flexRadioCobrarNo" value='no' checked>
                    <label class="form-check-label" for="flexRadioDefault2">
                        No
                    </label>
                </div>
            </div>
        </div>
        <div>
            <button class='btn btn-success btn-sm' onclick='registrar()'>Registrar </button>
        </div>
        `);
        $('#modal_estados_abono').empty();
        let html = `
        <select class='form-select' aria-label="Default select example">
        `;
        resp[3].forEach(estado => {
            html+=`<option value='`+estado.id+`'>`+estado.descripcion_estado+` </option>`;
        });

        html += `</select>`;
        $('#modal_estados_abono').append(html);
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
    //console.log("cargar cliente, modifica: "+modifica)
  }

  function agregar_pedido(){
        let id_cliente = $('#id_cliente_nuevo_pedido').val();
        let url = "/ventas/nuevo_pedido/"+id_cliente;
        console.log(url);
        $.ajax({
            type:'get',
            url: url,
            success: function(id_abono){
                $('#id_abono').val(id_abono);
                cargar_cliente(id_cliente);
            },
            error: function(err){
                console.log(err);
            }
        })
  }

  function agregar_repuesto(){
     let id_abono = document.getElementById('id_abono').value;
     let cantidad = $('#cantidad').val();
     let descripcion = $('#descripcion').val();
     let precio_unitario = $('#precio_unitario').val();
        
        // let id_abono = $('#id_abono').val();
      total = cantidad * precio_unitario;

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

      let parametros = {
          id_abono: id_abono, 
          cantidad: cantidad, 
          descripcion: descripcion, 
          precio_unitario: precio_unitario,
          total: total
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
            success:function(resp){
               
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
                                    <td class='text-uppercase'>`+item.descripcion_estado+`</td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.id+`)">X</button> </td>
                                </tr>
                                `);
                        });
                        $('#precio_lista').val(total_precio_lista);
                        // $('.form-control').removeAttr('disabled');
                        // limpiar_controles();
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

  
  function eliminar_pedido(id_pedido){
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
                let url = '/ventas/eliminar_pedido/'+id_pedido+'/'+id_abono;
      
                $.ajax({
                    type:'get',
                    url: url,
                    success: function(resp){
                        
                        $('#tbody_historial').empty();
                        $('#precio_lista').empty();
                        if(resp.length > 0){
                            
                            var total_precio_lista = 0;
                            resp.forEach(item => {
                                total_precio_lista += item.total;
                                $('#tbody_historial').append(`
                                            <tr>
                                                <td>`+item.cantidad+` </td>
                                                <td>`+item.descripcion+` </td>
                                                <td>$`+new Intl.NumberFormat().format(item.precio_unitario)+` </td>
                                                <td>$`+new Intl.NumberFormat().format(item.total)+` </td>
                                                <td class='text-uppercase'>`+item.descripcion_estado+`</td>
                                                <td><button class="btn btn-danger btn-sm" onclick="eliminar_pedido(`+item.id+`)">X</button> </td>
                                            </tr>
                                            `);
                            });
                            $('#precio_lista').val(total_precio_lista);
                        }else{
                            $('#tbody_historial').append(`
                            <tr>
                                <td>No hay pedidos </td>
                            </tr>
                            `);
                        }
            
                        },
                        error: function(err){
                            console.log(err);
                        }
                    })
              
            }else{
              console.log("La solicitud no se ha eliminado");
            }
          });
      
      
  }

function registrar(){
    let por_encargo = $('input:radio[name=flexRadioDefault1]:checked').val();
    let por_cobrar = $('input:radio[name=flexRadioDefault2]:checked').val();
    let id_abono = $('#id_abono').val();
    let abono = $('#abono').val();
    let saldo_pendiente = $('#saldo_pendiente').val();
    let precio_lista = $('#precio_lista').val();
    let id_cliente = document.getElementById('id_cliente').value;


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

      let saldo_pendiente_formateado = saldo_pendiente.slice(1);
      let precio_lista_formateado = precio_lista.slice(1);
    let parametros = {
        por_encargo: por_encargo, 
        por_cobrar: por_cobrar, 
        id_abono: id_abono, 
        abono: abono, 
        saldo_pendiente: saldo_pendiente_formateado, 
        precio_lista: precio_lista_formateado, 
        id_cliente: id_cliente
    };

    let url = '/clientes/abonar_detalle';

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
       console.log(resp);
        Vue.swal.close();
        if(resp =="OK"){
            Vue.swal({
                title: 'Felicidades',
                text: 'Pedido registrado con éxito',
                icon: 'success',
            });
            $('#listar_clientes').css('display','none');
            $('#tabla_ingreso').css('display','none');
            $('#eliminarpedido').css('display','none');
            limpiar_controles();
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

function borrar_item(id){
    let opt = confirm('¿Esta seguro que desea eliminar '+id+' ?');
    let idc = $('#id_cliente').val();

    let parametros = {id_abono: id, idc: idc};
    if(opt){
        console.log('eliminando ...');
        let url = '/ventas/eliminaritem';

        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            data: parametros,
            url:url,
            beforeSend: function(){
                console.log('enviando ...');
            },
            success: function(resp){
                console.log(resp);
                $('#tbody_historial').empty();
                resp.forEach(item => {
                $('#tbody_historial').append(`
                <tr>
                    <td>`+item.cantidad+` </td>
                    <td>`+item.descripcion+` </td>
                    <td>`+item.precio_unitario+` </td>
                    <td>`+item.total+` </td>
                    
                </tr>
                `)
            });
            $('#eliminarpedido').css('display','none');
            },
            error: function(error){
                console.log(error);
            }
        })
    }else{
        return false;
    }
}

function eliminarpedido(idc){
    let url = '/ventas/eliminarpedido/'+idc;

    $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
            console.log('eliminando pedido ...');
        },
        success: function(resp){
            if(resp === 'OK'){
                Vue.swal({
                        title: 'INFO',
                        text: 'Pedido eliminado con éxito',
                        icon: 'success',
                    });
                $('#tbody_historial').empty();
                $('#tbody_historial').append(`No existen pedidos`);
            }
        },
        error: function(error){
            Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
        }
    })
}
</script>
@endsection

@section('style')
    <style>
        .thead{
            text-align: center;
            background: black;
            color: wheat;
        }

        .div_select{
            border: 1px solid black;
            padding: 20px;
            margin-bottom: 10px;
            background: #eee;
            border-radius: 10px;
        }

        .logo_{
            width: 130px;
        }

        #info_cliente{
            border: 1px solid black;
            background: #eee;
            padding: 5px;
            border-radius: 10px;
        }
    </style>
@endsection

@section('contenido_ingresa_datos')

    <h4 class="titulazo">Generar pedidos</h4>
    <div class="container-fluid">
    <div class="row" style="width: 98%; margin: 0px auto; border: 1px solid black; padding: 10px; border-radius: 10px; margin-bottom: 20px;">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
        <legend>Buscar Cliente</legend>
        <div class="col-sm-3">
          <input type="radio" name="buscapor" id="buscaxrut" checked="true">
          <label for="buscaxrut">RUT</label>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="buscapor" id="buscaxnombres" >
          <label for="buscaxnombres">Nombres</label>
        </div>
        <div class="col-sm-3" style="padding-left:5px;padding-right:5px">
          <input type="text" class="form-control" placeholder="Ingrese búsqueda" id="buscado" onkeyup="enter_buscar(event)" style="width:100%;padding-left:5px;padding-right:5px">
        </div>
        <div class="col-sm-4">
          <button onclick="buscar_clientes()" class="btn btn-success btn-sm">Buscar</button>
        </div>

    </div>
    <div id="notificaciones">

    </div>
    <div class="container-fluid">
        <div id="botonera">
            
        </div>
        <div class="row">
            <div class="col-md-7">
                <div id="listar_clientes">
            
                </div>
            </div>
            <div class="col-md-5">
                <table class="table" id="tabla_ingreso">
                    
                    <thead class="thead-dark">
                        <tr>
                          <th style="width: 16%" scope="col">Cantidad</th>
                          <th style="width: 12%" scope="col">Descripcion</th>
                          <th style="width: 16%" scope="col">Precio unitario</th>
                          <th style="width: 16%" scope="col">Total</th>
                          <th style="width: 20%" scope="col">Estado</th>
                          <th style="width: 10%" scope="col">Eliminar</th>
                        </tr>
                      </thead>
                      <tbody id="tbody_historial">
                        
                      </tbody>
                </table>
                <div id="eliminarpedido">
    
                </div>
                
            </div>
        </div>
    </div>
    
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_estados_abono">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>

<!--Datos de vital importancia -->
<input type="hidden" name="" id="id_cliente">
<input type="hidden" name="" id="id_abono">
<input type="hidden" name="" id="id_cliente_nuevo_pedido">
@endsection