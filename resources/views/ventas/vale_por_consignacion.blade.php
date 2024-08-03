@extends('plantillas.app')

@section('titulo','Vale por consignacion')

@section('javascript')
<script>

        window.onload= function(){
           
           document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
           //document.querySelectorAll("#tablero_select *").forEach(el => el.setAttribute("disabled", "true"));
       }

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
    function registrar_cliente(){
            let rut = document.getElementById("rut").value.toString().trim().toUpperCase();
            let nombre = $('#nombre').val();
            let telefono = $('#telefono').val();
            rut=rut.replace("-","");
            document.getElementById("rut").value=rut;
        
        if(rut.length<8)
            {
            Vue.swal({
                text: 'RUT debe tener mínimo 8 dígitos',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
                $('#rut').focus();
            return false;
            }

            if(!RUN_correcto(rut))
                {
                Vue.swal({
                    text: 'RUT INVÁLIDO... DIGITO VERIFICADOR INVÁLIDO...',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
                    $('#rut').focus();
                    return false;
                }

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

            if(nombre.length < 8 ){
                Vue.swal({
                    text: 'Mínimo 8 caracteres en nombre',
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
            let parametros = {nombre: nombre, telefono: telefono, rut: rut};
            let url = '/ventas/nueva_consignacion';
          
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
                $('#id_vale').val(resp.id);
                //Se remueven los atributos disabled para poder registrar nuevos productos
                document.querySelectorAll("#tablero *").forEach(el => el.removeAttribute("disabled"));
                //Se bloquean los input para no modificar los datos del cliente
                document.querySelectorAll("#info_cliente *").forEach(el => el.setAttribute("disabled", "true"));
                $('#tbody_cliente').empty();
                $('#tbody_cliente').append(`
                <tr>
                    <td>`+resp.rut_cliente+` </td>
                    <td>`+resp.nombre_cliente+` </td>
                    <td>`+resp.telefono_cliente+` </td>
                </tr>
                `);
                Vue.swal({
                    icon:'success',
                    title:'Felicidades',
                    text:'Cliente ingresado'
                });
                }
                
            },
            error: function(err){
                console.log(err);
            }
        });
        }

    function RUN_correcto(run)
    {
        var multiplicador=[3,2,7,6,5,4,3,2];
        var x=[0,0,0,0,0,0,0];
        if(run.length==8)
        {
        x[0]=0;
        x[1]=parseInt(run.substring(0,1));
        x[2]=parseInt(run.substring(1,2));
        x[3]=parseInt(run.substring(2,3));
        x[4]=parseInt(run.substring(3,4));
        x[5]=parseInt(run.substring(4,5));
        x[6]=parseInt(run.substring(5,6));
        x[7]=parseInt(run.substring(6,7));
        x[8]=run.substring(7,8); // porque puede ser la letra K
        }
        if(run.length==9)
        {
        x[0]=parseInt(run.substring(0,1));
        x[1]=parseInt(run.substring(1,2));
        x[2]=parseInt(run.substring(2,3));
        x[3]=parseInt(run.substring(3,4));
        x[4]=parseInt(run.substring(4,5));
        x[5]=parseInt(run.substring(5,6));
        x[6]=parseInt(run.substring(6,7));
        x[7]=parseInt(run.substring(7,8));
        x[8]=run.substring(8,9);
        }

        var suma=0;
        for(var i=0;i<8;i++)
        {
        suma=suma+x[i]*multiplicador[i];
        }
        var residuo=suma%11;
        var digito=11-residuo;
        console.log("X8: "+x[8]+" digito: "+digito);
        if(x[8]=="K")
        {
        if(digito==10){
            return true;
        }else{
            return false;
        }
        }else{
        if(x[8]==digito){
            return true;
        }else{
            if(x[8]==0 && digito==11){
                return true;
            }else{
                return false;
            }
        }
        }

    }

        function nuevo_cliente(){
            document.getElementById('btn_registrar_cliente').removeAttribute('disabled');
            
            $('#btn_pago').attr('disabled','true');
            document.querySelectorAll("#tablero *").forEach(el => el.setAttribute("disabled", "true"));
            limpiar_todo();
        }

        function limpiar_todo(){
            // document.getElementById('buscado').value = "";
            
            $('#nombre').val('');
            $('#telefono').val('');
            $('#rut').val('');
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
    let id_vale = $('#id_vale').val();
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
        
          agregar_mercaderia(idrep,id_vale);
          $('#informacion_repuesto').empty();
          $('#repuesto-xpress-codigo').val('');
          $('#repuesto-xpress-codigo').focus();
          $('#exampleModal').modal('hide');
    }
    
  }

  function agregar_mercaderia(idrep, id_vale){
    let origen = $('#select_locales').val();
    let cantidad = $('#input_cantidad').val();
    let url = "/ventas/agregar_repuesto_valeconsignacion";
    Vue.swal({
      title: '¿Estas seguro?',
      text: "Se va a descontar del stock!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí'
    }).then((result) => {
      if (result.isConfirmed) {
        
        let data = {
            idrep: idrep, id_vale: id_vale, cantidad: cantidad, origen: origen
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
            $('#vale_consignacion_detalle').empty();
            $('#vale_consignacion_detalle').append(resp);
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
    });
    

  }

  function eliminar_item_consignacion(id_repuesto, numero_boucher){
    
    let url = '/ventas/eliminar_repuesto_valeconsignacion/'+numero_boucher+'/'+id_repuesto;
    Vue.swal({
        title: '¿Estás seguro?',
        text: "La cantida de repuesto será devuelta a su stock original",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                espere('CARGANDO ...');
            },
            success: function(resp){
                console.log(resp);
                Vue.swal.close();
                $('#vale_consignacion_detalle').empty();
                $('#vale_consignacion_detalle').append(resp);
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
        }
      });
    
  }

  function devolver_item_consignacion(id_repuesto, numero_boucher){
    
    let url = '/ventas/devolver_repuesto_valeconsignacion/'+numero_boucher+'/'+id_repuesto;
   
    $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
            espere('CARGANDO ...');
        },
        success: function(resp){
            console.log(resp);
            Vue.swal.close();
            $('#detalle_consignacion').empty();
            $('#detalle_consignacion').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function cerrar_consignacion(){
    let id_vale = $('#id_vale').val();
    let url = '/ventas/cerrar_consignacion/'+id_vale;
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
        if(resp == 'OK'){
          Vue.swal({
            icon:'success',
            text:'Consignación exitosa',
            position:'top-end',
            timer: 3000,
            toast:true,
            showConfirmButton: false
          });
        }
        setTimeout(() => {
          //window.location.href = '/ventas/vale_consignacion';
          $('#opcionesModal').modal('show');
        }, 1500);
        // Simulate a mouse click:
        
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
  }

  function ver_historial(){
    let url = "/clientes/dame_vales_consignacion";
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
                    
                }else{
                    state = "PENDIENTE";
                }
                $('#tbody_vales').append(`
                                <tr>
                                    <td> <a href="javascript:void(0)" onclick="cargar_vale_consignacion(`+v.numero_boucher+`)"> `+v.numero_boucher+` </a></td>
                                    <td>`+v.fecha_emision+` </td>
                                    <td>`+state+` </td>
                                    <td>`+v.nombre_cliente+` </td>
                                    <td><button class='btn btn-success btn-sm' onclick='imprimir_vale_consignacion_historial("`+v.url_pdf+`")'><i class="fa-solid fa-print"></i> </button> </td>
                                    <td><button class='btn btn-danger btn-sm' onclick='eliminar_vale_consignacion(`+v.id+`)'>X</button> </td>
                                </tr>
                                
            `);
            });
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function cargar_vale_consignacion(numero_boucher){
    let url = '/ventas/dame_vale_consignacion/'+numero_boucher;
    $.ajax({
        type:'get',
        url: url,
        success: function(resp){
            console.log(resp);
            //$('#exampleModal_historial').modal('hide');
            $('#detalle_consignacion').empty();
            $('#detalle_consignacion').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function eliminar_vale_consignacion(id_vale){
    let url = '/ventas/eliminar_vale_consignacion/'+id_vale;
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
        console.log(resp);
        if(resp == 'OK'){
          ver_historial();
        }
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
  }

  function imprimir_consignacion(id_vale){

    let url = '/ventas/imprimir_consignacion/'+id_vale;
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
        Vue.swal({
          icon:'info',
          text:'CARGANDO'
        });
      },
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
      error: function(error){
        console.log(error.responseText);
      }
    });
  }
  function imprimir_consignacion_boucher(){
    let id_vale = $('#id_vale').val();
    let url = '/ventas/imprimir_consignacion/'+id_vale;
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
        Vue.swal({
          icon:'info',
          text:'CARGANDO'
        });
      },
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
      error: function(error){
        console.log(error.responseText);
      }
    });
  }

  function imprimir_vale_consignacion_historial(pdf){
    let url = '{{url("imprimir_pdf_vale_consignacion_historial")}}'+'/'+pdf;
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
</script>
@endsection

@section('contenido_ingresa_datos')
<h4 class="titulazo">Vale por consignación </h4>
<div class="container-fluid">
    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
        <div class="row mb-5">
            <div class="col-md-6" style="background: rgb(207, 255, 255);
            padding: 10px;
            border-radius: 10px;" id="info_cliente">
            <div class="form-group">
                <label for="rut">Rut del cliente</label>
                <input type="text" class="form-control" placeholder="Rut del cliente" name="rut" id="rut">
            </div>
                <div class="form-group">
                    <label for="nombre">Nombre del cliente</label>
                    <input type="text" class="form-control" placeholder="Nombre del cliente" name="nombre" id="nombre">
                </div>
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Telefono del cliente">
                </div>
                <button class='btn btn-success btn-sm ' onclick='registrar_cliente()' id="btn_registrar_cliente">Registrar Cliente </button>
                <button class="btn btn-warning btn-sm" onclick="ver_historial()" data-toggle="modal" data-target="#exampleModal_historial">Historial de pedidos</button>
                <button class="btn btn-primary btn-sm" onclick="nuevo_cliente()">Nuevo cliente</button>
            </div>
            
          <div class="col-md-6">
            <div id="tablero">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Rut</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Telefono</th>
                      </tr>
                    </thead>
                    <tbody id="tbody_cliente">
                      
                    </tbody>
                  </table>
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exampleModal">Agregar repuesto</button>
                
            </div>
            <div id="vale_consignacion_detalle" class="mt-5">

            </div>
          </div>
           
        </div>

        
</div>

<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="exampleModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: #000; color: white;">
          <h5 class="modal-title">Busqueda de Repuestos Xpress</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body" style="background: rgb(242, 244, 169);">
            <div class="row form-group-sm">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="" id="repuesto-xpress-codigo" placeholder="Cód. Ref." style="width:100%">&nbsp;
                </div>
                <div class="col-md-6">
                  <input type="button" value="Buscar" class="btn btn-warning btn-sm" onclick="buscar_repuesto_xpress()">
                </div>
            </div>
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Descripción</th>
                  <th scope="col">Ubicación</th>
                  <th scope="col">Ubicación</th>
                  <th scope="col">Ubicación</th>
                  <th scope="col">Cantidad</th>
                  <th scope="col">Procedencia</th>
                  <th scope="col">Precio Venta</th>
                </tr>
              </thead>
              <tbody id="informacion_repuesto">
                
              </tbody>
            </table>
            
        </div>
        <div class="modal-footer" style="background: #000; color: white;">
          
          <button type="button" id="btn_agregar_repuesto_buscado" class="btn btn-primary">Agregar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        </div>
        <hr>
        <p id="mensaje_modal" class="ml-3">Esperando ...</p>
      </div>
    </div>
  </div>

  <!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="exampleModal_historial" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Vales de consignacion</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">N° Boucher</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Imprimir</th>
                    <th scope="col">Eliminar</th>
                  </tr>
                </thead>
                <tbody id="tbody_vales">
                  
                </tbody>
              </table>
              <hr>
              <div id="detalle_consignacion">
                
              </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="opcionesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
            <button class="btn btn-success btn-sm" onclick="imprimir_consignacion_boucher()">Imprimir</button>
            <a href="/ventas/vale_consignacion" class="btn btn-primary btn-sm">Nueva consignación</a>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  
<!--DATOS DE VITAL IMPORTANCIA -->
<input type="hidden" name="" id="id_vale">
@endsection