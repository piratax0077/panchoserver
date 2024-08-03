@extends('plantillas.app')

@section('titulo','Vale por mercadería')
@section('style')
    <style>
            .formulario_ingreso{
                width: 100%;
                margin: 0px auto;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 10px;
                background: rgb(207, 255, 255);
                border: 1px solid gray;
            }

            .logo_{
                width: 150px;
                margin-bottom: 30px;
            }

            .radio_button_options{
                display: flex;
                justify-content: space-around;
                padding: 25px;
            }
        </style>
@endsection
@section('javascript')
        <script>

            function espere(msg){
                Vue.swal({
                    icon:'info',
                    text: msg
                });
            }

            function soloNumeros(e)
            {
                var key = window.Event ? e.which : e.keyCode
                return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
            }
            function imprimir_boucher(){
                let nombre_cliente = $('#nombre_cliente').val();
                let descripcion = $('#descripcion').val();
                let numero_documento = $('#numero_documento').val();
                let tipo_doc = $('input[name="exampleRadios"]:checked').val();
                
                let valor = $('#valor').val();
                let rut = $('#rut_cliente').val();
                let telefono = $('#telefono_cliente').val();

                if(rut === ''){
                    Vue.swal({
                            text: 'Ingrese rut del cliente',
                            position: 'top-end',
                            icon: 'warning',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    $('#rut_cliente').focus();
                    return false;
                }
               
                if(nombre_cliente === ''){
                    Vue.swal({
                            text: 'Ingrese nombre del cliente',
                            position: 'top-end',
                            icon: 'warning',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    $('#nombre_cliente').focus();
                    return false;
                }

                if(descripcion === ''){
                    Vue.swal({
                            text: 'Ingrese una descripcion correcta',
                            position: 'top-end',
                            icon: 'warning',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    $('#descripcion').focus();
                    return false;
                }

                if(numero_documento < 0 || isNaN(numero_documento) || numero_documento === ''){
                    Vue.swal({
                            text: 'Ingrese un numero de boleta correcto',
                            position: 'top-end',
                            icon: 'warning',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    $('#numero_documento').focus();
                    return false;
                }

                if(valor === '' || valor < 0){
                    Vue.swal({
                            text: 'Ingrese valor correcto',
                            position: 'top-end',
                            icon: 'warning',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    $('#valor').focus();
                    return false;
                }
                
                let parametros = {
                    descripcion: descripcion,
                    numero_documento: numero_documento,
                    nombre_cliente: nombre_cliente,
                    valor: valor,
                    rut: rut,
                    telefono: telefono,
                    tipo_doc: tipo_doc
                }


                let url = '/ventas/guardar_vale_mercaderia';
                $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type:'post',
                    data: parametros,
                    url: url,
                    success: function(resp){
                        
                        if(resp[0] === "OK"){
                            Vue.swal({
                                title: 'INFO',
                                text: "Vale por mercadería registrado con exito",
                                icon: 'success',
                            });
                        imprimir(resp[1]);
                        limpiar_controles();
                        }else{
                            Vue.swal({
                            title: 'INFO',
                            text: "Algo ha ocurrido, no se guardo el vale por mercadería",
                            icon: 'info',
                        });
                        }
                    },
                    error: function(err){
                        Vue.swal({
                            title: 'ERROR',
                            text: err.responseText,
                            icon: 'error',
                        });
                    }
                });
            }

            function imprimir(num_boucher){
                let nombre_cliente = $('#nombre_cliente').val();
                let descripcion = $('#descripcion').val();
                let numero_documento = $('#numero_documento').val();
                let numero_boucher = num_boucher;
                let valor = $('#valor').val();
                let rut = $('#rut_cliente').val();
                let telefono = $('#telefono_cliente').val();
                let tipo_doc = $('input[name="exampleRadios"]:checked').val();

                let parametros = {
                    descripcion: descripcion,
                    numero_documento: numero_documento,
                    nombre_cliente: nombre_cliente,
                    numero_boucher: num_boucher,
                    valor: valor,
                    rut: rut,
                    telefono: telefono,
                    tipo_doc: tipo_doc
                }

                let url = '/ventas/imprimir_vale';
                $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type:'post',
                    data: parametros,
                    url: url,
                    success: function(resp){
                        Vue.swal.close();

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
                    error: function(err){
                        console.log(err);
                    }
                });
            }

            function limpiar_controles(){
                $('#descripcion').val('');
                $('#numero_documento').val('');
                $('#nombre_cliente').val('');
                $('#rut_cliente').val('');
                $('#telefono_cliente').val('');
                $('#valor').val('');
                $('#descripcion').focus();
                $('#numero_vale').val('');
            }

            function buscar_devolucion(){
                let numero_vale = $('#numero_vale').val();
                if(numero_vale.trim() == 0 || numero_vale == ''){
                    Vue.swal({
                        icon:'error',
                        text:'Debe ingresar un numero de devolución'
                    });
                    return false;
                }

                let url = '/ventas/vale_por_mercaderia/'+numero_vale;
                $.ajax({
                    type:'get',
                    url: url,
                    beforeSend: function(){
                        espere('Cargando ...');
                    },
                    success: function(resp){
                        Vue.swal.close();
                        $('#total_devolucion').empty();
                        $('#total_devolucion').append(resp);
                    },
                    error: function(error){
                        Vue.swal.close();
                        Vue.swal({
                            icon:'error',
                            text: error.responseText
                        });
                    }
                })
            }

    function buscar_repuesto_xpress(){
      let quien = 1;
      let codigo_interno = document.getElementById('repuesto-xpress-codigo').value;
      if(codigo_interno.trim() == 0 || codigo_interno.value == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar un codigo interno'
        });
        return false;
      }

      let url = '/repuesto/buscarcodigo/'+quien+codigo_interno;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          espere('Buscando ...');
        },
        success: function(resp){
          Vue.swal.close();
          var ubi_uno;
          var ubi_dos;
          var ubi_tres;
          if(resp == -1){
            Vue.swal({
              icon:'error',
              text:'No se encontró el repuesto',
            });
            return false;
          }
          let r = JSON.parse(resp[0]);
          console.log(r);
          let locales = resp[2];
          $('#informacion_repuesto').empty();
          
          r.forEach(e=>{
            if(e.local_id == 1){
              ubi_uno = "Bodega";
            }else if(e.local_id == 3){
              ubi_uno = "Tienda";
            }else{
              ubi_uno = "Casa Matriz";
            }

            if(e.local_id_dos == 1){
              ubi_dos = "Bodega";
            }else if(e.local_id_dos == 3){
              ubi_dos = "Tienda";
            }else{
              ubi_dos = "Casa Matriz";
            }

            if(e.local_id_tres == 1){
              ubi_tres = "Bodega";
            }else if(e.local_id_tres == 3){
              ubi_tres = "Tienda";
            }else{
              ubi_tres = "Casa Matriz";
            }
            $('#informacion_repuesto').append(`
            <tr> 
                  <td class='letra_pequeña'>`+e.descripcion+` </td>
                  
                  <td class='letra_pequeña'>`+e.ubicacion+`(`+e.stock_actual+`) </td>
                  
                  <td class='letra_pequeña'>`+e.ubicacion_dos+`(`+e.stock_actual_dos+`) </td>
                 
                  <td class='letra_pequeña'>`+e.ubicacion_tres+`(`+e.stock_actual_tres+`) </td>
                  <td class='letra_pequeña'><input type='text' placeholder='Cantidad' id='input_cantidad' onkeypress="return soloNumeros(event)" onkeyup="soloNumeros(event)"  /></td>
                  <td><select id='select_locales'>
                    
                  </select></td>
                  <td class='letra_pequeña'>$ `+e.precio_venta+`</td>
                </tr>
            `);

            $('#btn_agregar_repuesto_buscado').attr('onclick','agregar_repuesto_buscado('+e.id+')');
            $('#select_locales').empty();
            locales.forEach(local=> {
              $('#select_locales').append(`
              <option value='`+local.id+`'>`+local.local_nombre+` </option>
              `);
            })
            
          });
          
        },
        error: function(error){
          Vue.swal({
            icon:'error',
            text:error.responseText
          });
        }
      })
    }

    function agregar_repuesto_buscado(idrep){
    
    cant=document.getElementById("input_cantidad").value.trim();
    let numero_vale = $('#numero_vale').val();
    if(cant.trim() == 0 || cant == ''){
          Vue.swal({
                text: 'Debe ingresar una cantidad',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
    }else{
          agregar_mercaderia(idrep,numero_vale);
          $('#informacion_repuesto').empty();
          $('#repuesto-xpress-codigo').val('');
          $('#repuesto-xpress-codigo').focus();
          $('#exampleModal').modal('hide');
    }
    
  }

  function agregar_mercaderia(idrep, num_boucher){
    let origen = $('#select_locales').val();
    let cantidad = $('#input_cantidad').val();
    let url = "/ventas/agregar_repuesto_valemercaderia";
    let data = {
        idrep: idrep, num_boucher: num_boucher, cantidad: cantidad, origen: origen
    }


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type:'post',
        url: url,
        data: data,
        success: function(resp){
            if(resp == 'error'){
                Vue.swal({
                    text:'No hay stock en local seleccionado',
                    icon:'error'
                });
                return false;
            }

            if(resp == 'viejo'){
                Vue.swal({
                    text:'Repuesto no actualizado',
                    icon:'info',
                    position:'center',
                    timer: 3000,
                    toast: true,
                    showConfirmButton: false
                });
                return false;
            }
            $('#vale_mercaderia_detalle').empty();
            $('#vale_mercaderia_detalle').append(resp);
        },
        error: function(error){
            Vue.swal({
                icon:'error',
                text: error.responseText
            });
            console.log(error.responseText);
        }
    });

  }

  function eliminar_vale_mercaderia(id){
    Vue.swal({
        title:'¿Estás seguro?',
        text:'Se ELIMINARÁ el vale de mercadería de forma permanente',
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar'
    }).then(resp => {
        if(resp.isConfirmed){
            let url = '/ventas/eliminar_vale_mercaderia/'+id;
            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                    if(resp == 'OK'){
                        ver_historial_vales();
                    }
                    
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }
    });
    
  }

  function eliminar_repuesto_devolucion(devolucion_id){
    let numero_vm = $('#numero_vale_mercaderia').val();
    let url = '/ventas/eliminar_repuesto_valemercaderia/'+devolucion_id+'/'+numero_vm;
   
    $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
            espere('CARGANDO ...');
        },
        success: function(resp){
            console.log(resp);
            Vue.swal.close();
            $('#vale_mercaderia_detalle').empty();
            $('#vale_mercaderia_detalle').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }
  

  function imprimir_resultado(vale, detalles){
    Vue.swal({
        title:'¿Estás seguro?',
        text:'Se descontarán stock de los origenes seleccionados para ser entregados',
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar'
    }).then((resp) => {
        if(resp.isConfirmed){
            let url = '/ventas/imprimir_vale_resultado';
                let parametros = {
                    vale: vale,
                    detalles: detalles
                };
                
                $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type:'post',
                    data: parametros,
                    url: url,
                    success: function(resp){
                        
                        Vue.swal.close();

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
                    error: function(err){
                        console.log(err);
                    }
                });
        }
    });
                
  }

  function ver_historial_vales(){
    let url = "/clientes/dame_vales_mercaderia";
    $.ajax({
        type:'get',
        url: url,
        success: function(vales){
            
            $('#tbody_vales').empty();
            vales.forEach(v => {
                let state;
                let clase;
                if(v.activo == 0){
                    state = "PROCESADO";
                    clase = "bg-success text-white"
                }else{
                    state = "PENDIENTE";
                    clase = "bg-warning";
                }
                $('#tbody_vales').append(`
                                <tr class= "`+clase+`">
                                    <td><a href="javascript:void(0)" onclick="cargar_vale_mercaderia(`+v.numero_boucher+`)"> `+v.numero_boucher+` </a></td>
                                    <td>`+v.fecha+` </td>
                                    <td>`+v.descripcion+` </td>
                                    <td>`+v.tipo_doc+` </td>
                                    <td> `+v.numero_documento+`</td>
                                    <td>`+state+` </td>
                                    <td>`+v.nombre_cliente+` </td>
                                    <td><button class='btn btn-success btn-sm' onclick='imprimir_vale_mercaderia_historial("`+v.url_pdf+`")'><i class="fa-solid fa-print"></i> </button> </td>
                                    <td><button class='btn btn-danger btn-sm' onclick='eliminar_vale_mercaderia(`+v.id+`)'>X</button> </td>
                                </tr>
                                
            `);
            });
            
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function cargar_vale_mercaderia(numero_vale){
    console.log(numero_vale);
    $('#exampleModal_').modal('hide');
    $('#numero_vale').val(numero_vale);
    let url = "/ventas/detalle_vale_mercaderia/"+numero_vale;

    $.ajax({
        type:'get',
        url: url,
        success: function(resp){
            
                        $('#total_devolucion').empty();
                        $('#total_devolucion').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function imprimir_vale_mercaderia_historial(pdf){
    let url = '{{url("imprimir_pdf_vale_historial")}}'+'/'+pdf;
        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            success: function(resp){
                Vue.swal.close();
                console.log(resp);
                
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
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                    });
            }
        })
  }


    function filtrar(radio) {
        let url = '{{url("ventas/filtrar_vales")}}'+'/'+radio.value;
        $.ajax({
            type:'get',
            url: url,
            success: function(vales){
                console.log(vales);
                $('#tbody_vales').empty();
            vales.forEach(v => {
                let state;
                let clase;
                if(v.activo == 0){
                    state = "PROCESADO";
                    clase = "bg-success text-white"
                }else{
                    state = "PENDIENTE";
                    clase = "bg-warning";
                }
                $('#tbody_vales').append(`
                                <tr class= "`+clase+`">
                                    <td><a href="javascript:void(0)" onclick="cargar_vale_mercaderia(`+v.numero_boucher+`)"> `+v.numero_boucher+` </a></td>
                                    <td>`+v.fecha+` </td>
                                    <td>`+v.descripcion+` </td>
                                    <td>`+v.tipo_doc+` </td>
                                    <td> `+v.numero_documento+`</td>
                                    <td>`+state+` </td>
                                    <td>`+v.nombre_cliente+` </td>
                                    <td><button class='btn btn-success btn-sm' onclick='imprimir_vale_mercaderia_historial("`+v.url_pdf+`")'><i class="fa-solid fa-print"></i> </button> </td>
                                    <td><button class='btn btn-danger btn-sm' onclick='eliminar_vale_mercaderia(`+v.id+`)'>X</button> </td>
                                </tr>
                                
            `);
            });
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

</script>
@endsection
@section('contenido_titulo_pagina')
    <h4 class="titulazo">Vale por mercadería</h4>
@endsection

@section('contenido_ingresa_datos')
        <div class="container-fluid">
            <div class="row w-100">
                <div class="col-md-6" >
                    
                    <div class="formulario_ingreso">
                        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
                        
                        <div class="form-group">
                            <label for="rut_Cliente">Rut </label>
                            <input type="text" class="form-control" id="rut_cliente" name="rut_cliente">
                        </div>
                        <div class="form-group">
                            <label for="nombre_Cliente">Nombre </label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente">
                        </div>
                        <div class="form-group">
                            <label for="telefono_Cliente">Telefono </label>
                            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción del repuesto</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion">
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4 d-flex mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="bo" checked>
                                        <label class="form-check-label" for="exampleRadios1">
                                          Boleta
                                        </label>
                                      </div>
                                      <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="fa">
                                        <label class="form-check-label" for="exampleRadios2">
                                          Factura
                                        </label>
                                      </div>
                                </div>
                                <div class="col-md-8">
                                    <label for="numero_documento">N° de Boleta / N° de Factura</label>
                                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" onKeyPress="return soloNumeros(event)">
                                </div>
                            </div>
                            
                            
                        </div>
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="text" class="form-control" id="valor" name="valor" onKeyPress="return soloNumeros(event)">
                        </div>
        
                        <button class="btn btn-warning btn-sm" onclick="imprimir_boucher()">Imprimir</button>
                        <button class="btn btn-success btn-sm" onclick="ver_historial_vales()" data-toggle="modal" data-target="#exampleModal_">Historial</button>
                        
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="busqueda_devolucion" >
                        <div class="formulario_ingreso">
                            <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
                            <div class="form-group" style="display: flex">
                                <label for="numero_vale">N° Vale de Mercadería</label>
                                <input type="text" name="numero_vale" id="numero_vale" class="form-control" placeholder="N° de mercadería" onKeyPress="return soloNumeros(event)">
                                <button class="btn btn-success btn-sm" onclick="buscar_devolucion()">Buscar</button>
                            </div>
                        </div>
                        
                    </div>
                    <div id="total_devolucion"></div>
                </div>
            </div>
            
            
        </div>

        <!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="exampleModal_" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Listado Historial de Vales de Mercadería.</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="radio_button_options d-flex text-center">
                <div class="form-check">
                    <input class="form-check-input" onclick="ver_historial_vales()" type="radio" name="flexRadioDefault" id="vales_historial" value="" checked>
                    <label class="form-check-label" for="flexRadioDefault1">
                      Todos
                    </label>
                  </div>
                <div class="form-check">
                    <input class="form-check-input" onchange="filtrar(this)" type="radio" name="flexRadioDefault" id="pendientes" value="0">
                    <label class="form-check-label" for="flexRadioDefault1">
                      Procesados
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" onchange="filtrar(this)" type="radio" name="flexRadioDefault" id="procesados" value="1">
                    <label class="form-check-label" for="flexRadioDefault2">
                      Pendientes
                    </label>
                  </div>
            </div>
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">N° Boucher</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Tipo Doc</th>
                    <th scope="col">N° Documento</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Imprimir</th>
                    <th scope="col">Eliminar</th>
                  </tr>
                </thead>
                <tbody id="tbody_vales">
                  
                </tbody>
              </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
@endsection