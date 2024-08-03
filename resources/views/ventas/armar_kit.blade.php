@extends('plantillas.app')

@section('titulo','Armado de kit')

@section('javascript')
<script>

function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });

            document.getElementById('codigo_repuesto').focus();
    }
    function enter_press(e)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            buscar_repuesto();
        }
    }
    function buscar_repuesto(){
          let codigo_repuesto = document.getElementById('codigo_repuesto').value;
          let opt = $('input:radio[name=flexRadioDefault]:checked').val();
          let data = {codigo_repuesto: codigo_repuesto, option: opt, value: 1};

          if(codigo_repuesto.trim() == 0 || codigo_repuesto == ''){
            Vue.swal({
              icon:'error',
              position:'top-end',
              text:'Debe ingresar un codigo',
              timer: 3000,
              toast:true,
              showConfirmButton: false
            });
            return false;
          }

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
          $.ajax({
              type:'POST',
              url:'/ventas/damerepuesto_kit',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append('Buscando ...');
                
                espere('Buscando ...');
              },
              success: function(resp){
      
              
                Vue.swal.close();
                $('#id_kit').val(resp[0][0].id);
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                    $('#busqueda_resultado').removeClass('d-block');
                    $('#busqueda_resultado').addClass('d-none');
                    $('#busqueda_resultado_').removeClass('d-block');
                    $('#busqueda_resultado_').addClass('d-none');
                    $('#detalle_descuento').removeClass('d-block');
                    $('#detalle_descuento').addClass('d-none');
                    $('#informacion_kit').removeClass('d-block');
                    $('#informacion_kit').addClass('d-none');
                }else{
                  if(resp[2].length > 0){
                    let detalle_kit = resp[2];
                    let locales = resp[3];
                    
                    let html_locales = '<select id="locales">';
                    locales.forEach(local => {
                      if(local.activo == 1){
                        html_locales += '<option value='+local.id+'>'+local.local_nombre+' </option>';
                      }
                      
                    });

                  html_locales += '</select>';
                    $('#id_kit').val(detalle_kit[0].id_kit);
                    console.log(detalle_kit);
                    $('#busqueda_resultado').removeClass('d-none');
                    $('#busqueda_resultado').addClass('d-block');
                  
                    $('#mensajes').empty();
                    $('#mensajes').append('Listo');
                    $('#tbody_resultados').empty();
                 resp[0].forEach(element => {
                  let stock_total = parseInt(element.stock_actual)+parseInt(element.stock_actual_dos)+parseInt(element.stock_actual_tres);
                    $('#precio_repuesto').val(element.precio_venta);
                      $('#id_repuesto').val(element.id);
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                                                    
                            <td scope="row" style="width: 20%" >`+element.descripcion+`</td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+stock_total+`</span></td>
                           
                            <td><span id="ubicacion-`+element.id+`">$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</span></td>
                            
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            
                            <td>`+element.nombre_pais+`</td>
                            <td>`+html_locales+`</td>
                            <td><button class="btn btn-success btn-sm" onclick="seleccionar_kit(`+element.id+`)">Seleccionar </button> </td>
                        </tr>
                 `);
                    
                 });
                   $('#detalle_kit').empty();
                   detalle_kit.forEach(d => {
                    let ubicacion;
                    if(d.local_id == 1){
                        ubicacion="Bodega";
                    }else if(d.local_id == 3){
                      ubicacion = "Tienda";
                    }else{
                      ubicacion = "Casa matríz";
                    }
                    $('#detalle_kit').append(`<tr>
                      <td>`+d.codigo_interno+` </td>
                      <td>`+d.descripcion+` </td>
                      
                      <td>`+ubicacion+` </td>
                      <td>$ `+commaSeparateNumber(parseInt(d.precio_venta).toFixed(0))+` </td>
                      
                      <td><button class='btn btn-danger btn-sm' onclick="eliminar_repuesto_kit(`+d.id_repuesto+`)">X</button> </td>
                      </tr>`);
                   });
                  }else{
                    let detalle_kit = resp[2];
                    let locales = resp[3];
                    console.log(locales);
                    let html_locales = '<select id="locales">';
                    locales.forEach(local => {
                      if(local.activo == 1){
                        html_locales += '<option value='+local.id+'>'+local.local_nombre+' </option>';
                      }
                      
                    });

                  html_locales += '</select>';
                  
                   
                    $('#busqueda_resultado').removeClass('d-none');
                    $('#busqueda_resultado').addClass('d-block');
                    $('#tbody_resultados').empty();
                    resp[0].forEach(element => {
                  let stock_total = parseInt(element.stock_actual)+parseInt(element.stock_actual_dos)+parseInt(element.stock_actual_tres);
                    $('#precio_repuesto').val(element.precio_venta);
                      $('#id_repuesto').val(element.id);
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                                                  
                            <td scope="row" style="width: 20%" >`+element.descripcion+`</td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+stock_total+`</span></td>
                           
                            <td><span id="ubicacion-`+element.id+`">$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</span></td>
                            
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td>`+html_locales+` </td>
                            <td><button class="btn btn-success btn-sm" onclick="seleccionar_kit(`+element.id+`)">Seleccionar </button> </td>
                        </tr>
                 `);
                    
                 });
                    //$('#detalle_kit').empty();
                    //$('#detalle_kit').append('<tr><td><button class="btn btn-success btn-sm" onclick="seleccionar_kit()">Crear </button> </td> </tr>');
                  }
                  
                   
                }
                
              },
              error: function(err){
                  console.log(err.responseText);
                  Vue.swal({

                    title:'Error!',

                    text:err.responseText,

                    icon:'error'

                    });
              }
          });
    }

    function buscar_repuesto_para_agregar(){
          let codigo_repuesto = document.getElementById('codigo_repuesto_').value;
          let opt = $('input:radio[name=flexRadioDefault_]:checked').val();
          let data = {codigo_repuesto: codigo_repuesto, option: opt, value: 2};

          if(codigo_repuesto.trim() == 0 || codigo_repuesto == ''){
            Vue.swal({
              icon:'error',
              position:'top-end',
              text:'Debe ingresar un codigo',
              timer: 3000,
              toast:true,
              showConfirmButton: false
            });
            return false;
          }

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
          $.ajax({
              type:'POST',
              url:'/ventas/damerepuesto_kit',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append('Buscando ...');
                
                espere('Buscando ...');
              },
              success: function(resp){
                Vue.swal.close();
                console.log(resp);
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                    
                }else{
                  
                  let locales = resp[3];
                  console.log(locales);
                  let html_locales = '<select id="locales_">';
                  locales.forEach(local => {
                    if(local.activo == 1){
                      html_locales += '<option value='+local.id+'>'+local.local_nombre+' </option>';
                    }
                    
                  });

                  html_locales += '</select>';
                 $('#busqueda_resultado_').removeClass('d-none');
                 $('#busqueda_resultado_').addClass('d-block'); 
                $('#mensajes').empty();
                $('#mensajes').append('Listo');
                $('#tbody_resultados_').empty();
                 resp[0].forEach(element => {
                  let stock_total = parseInt(element.stock_actual)+parseInt(element.stock_actual_dos)+parseInt(element.stock_actual_tres);
                    $('#precio_repuesto').val(element.precio_venta);
                      $('#id_repuesto').val(element.id);
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados_').append(`
                        <tr>
                                                 
                            <td scope="row" >`+element.descripcion+`</td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+parseInt(element.stock_actual)+`</span></td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+parseInt(element.stock_actual_dos)+`</span></td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+parseInt(element.stock_actual_tres)+`</span></td>
                            <td><span id="ubicacion-`+element.id+`">$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</span></td>
                            
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td>`+html_locales+` </td>
                            <td  class="d-flex"><button class="btn btn-success btn-sm" onclick="agregar_repuesto(`+element.id+`)">+</button>  </td>
                           
                        </tr>
                 `);
                    
                 });
                   
                }
              },
              error: function(err){
                  console.log(err.responseText);
                  Vue.swal({

                    title:'Error!',

                    text:err.responseText,

                    icon:'error'

                    });
              }
          });
    }

    function seleccionar_kit(idrep){
      let local_id = $( "#locales" ).val();
      let url = "/ventas/seleccionar_kit/"+idrep+"/"+local_id;
    
      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          console.log(resp);
          if(resp == 'OK'){
            $('#detalle_descuento').removeClass('d-none');
            $('#detalle_descuento').addClass('d-block');
          }else{
            Vue.swal({
              icon:'error',
              text: resp[1]
            });
            $('#detalle_descuento').removeClass('d-block');
            $('#detalle_descuento').addClass('d-none');
            $('#busqueda_resultado_').removeClass('d-block');
            $('#busqueda_resultado_').addClass('d-none');
          }
          
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
      
      
    }

    function agregar_repuesto(id_repuesto){
      Vue.swal({
        text: "Se descontará el stock, ¿Desea continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CONTINUAR',
        cancelButtonText: 'CANCELAR'
        }).then((result) => {
        if (result.isConfirmed) {
          let id_kit = $('#id_kit').val();
          let local_id = $( "#locales_" ).val();
          let local_id_kit = $('#locales').val();
          let cantidad = 1;
          if(cantidad < 1 || cantidad == ''){
            Vue.swal({
              icon:'error',
              text:'Ingrese una cantidad valida'
            });
            return false;
          }
          let params = {
            id_kit: id_kit,
            id_repuesto: id_repuesto,
            cantidad:cantidad,
            local_id: local_id,
            local_id_kit: local_id_kit
          }

          console.log(params);
          
          let url = '/ventas/agregar_repuesto_kit';

          $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

          $.ajax({
            type:'post',
            data: params,
            url: url,
            success: function(resp){
              console.log(resp);
              
              if(resp[0] == 'error'){
                Vue.swal({

                title:'Error!',

                text:resp[1],

                icon:'error'

                });
              }else{
                Vue.swal({
                  icon:'success',
                  text:'Repuesto agregado',
                  position:'center',
                  toast: true,
                  showConfirmButton: false,
                  timer: 3000
                });
                $('#informacion_kit').removeClass('d-none');
                $('#informacion_kit').addClass('d-block');
                let detalle = resp[0];
                let repuesto = resp[1];
                $('#info_kit').empty();
                detalle.forEach(d => {
                  $('#info_kit').append('<tr><td>'+d.codigo_interno+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">X </button> </td></tr>');
                });

                $('#detalle_kit').empty();
                detalle.forEach(d => {
                  $('#detalle_kit').append('<tr><td>'+d.codigo_interno+'</td><td>'+d.descripcion+' </td><td>'+d.local_nombre+' </td><td>$ '+commaSeparateNumber(parseInt(d.precio_venta).toFixed(0))+' </td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">X </button> </td></tr>');
                });
              }
              
              
            },
            error: function(error){
              console.log(error.responseText);

            }
          });
          }else{
              /*
              let bb=document.getElementById("radio_boleta");
              let ff=document.getElementById("radio_factura");
              bb.checked=true;
              ff.checked=false;
              */
          }
        });
      
    }

    function eliminar_repuesto_kit(id_repuesto){
      let idkit = $('#id_kit').val();
      let url = '/ventas/eliminar_repuesto_kit/'+id_repuesto+'/'+idkit;

      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
         
          let detalle = resp[1];
          let nuevo_precio = resp[2];
          if(resp[0] == 'OK'){
            Vue.swal({
              icon:'success',
              text:'repuesto eliminado del kit',

            });

            $('#info_kit').empty();
              detalle.forEach(d => {
                $('#info_kit').append('<tr><td>'+d.codigo_interno+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">X </button> </td></tr>');
              });

              $('#detalle_kit').empty();
              detalle.forEach(d => {
                $('#detalle_kit').append('<tr><td>'+d.codigo_interno+'</td><td>'+d.descripcion+' </td><td>'+d.local_nombre+' </td><td>$ '+commaSeparateNumber(parseInt(d.precio_venta).toFixed(0))+' </td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">X </button> </td></tr>');
              });
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
      });

    }

    function guardar(){
      let nombre_kit = $('#nombre_kit').val();
      if(nombre_kit == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar un nombre para el kit',
          showConfirmButton: false,
          position:'top-end',
          timer: 3000,
          toast: true
        });
        return false;
      }

      let url = '/ventas/crear_kit/'+nombre_kit;
   
      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          console.log(resp);
          if(resp[0] == 'OK'){
            Vue.swal({
              icon:'success',
              position:'center',
              timer: 3000,
              showConfirmButton: false,
              text:'Nuevo kit creado',
              toast:true
            });
            $('#busqueda_principal').removeClass('d-none');
            $('#busqueda_principal').addClass('d-block');
            $('#nombre_kit').attr('disabled','disabled');
            $('#nombre_kit_').empty();
            $('#nombre_kit_').append('<p>'+resp[1].nombre_kit+'</p>');
            $('#id_kit').val(resp[1].id);
          }else{
            Vue.swal({
              icon:'error',
              position:'center',
              timer: 3000,
              showConfirmButton: false,
              text:resp[1],
              toast:true
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

@section('contenido_titulo_pagina')
<center><h4 class="titulazo">ARMADO DE KIT</h4></center>
@endsection

@section('style')
<style>
    .busqueda_principal{
            margin-top: 30px;
            border: 1px solid black;
            background: #eee;
            padding: 20px;
        border-radius: 10px;
        }

        .busqueda_resultado, .resultado_armado{
            border: 1px solid black;
            margin-top: 30px;
            min-height: 200px;
            padding: 20px;
            border-radius: 10px;
        }

        .logo{
            width: 100px;
            border-radius: 10px;
        }
        .imagen_pequeña{
            width: 120px;      
        }
</style>
@endsection

@section('mensajes')
  @include('fragm.mensajes')
@endsection



@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div class="busqueda_principal">
    <h3>Instrucciones</h3>
    <ol>
      <li>Buscar el kit por codigo interno (Debe ser un codigo interno exclusivo del kit, no debe ser un repuesto normal)</li>
      <li>Seleccionar el kit (Le aparecerá el detalle del kit, los repuestos de los que esta compuesto)</li>
      <li>Se abrirá un nuevo formulario, donde debe ingresar el codigo interno del repuesto que quiera agregar al kit. (Puede agregar los necesarios)</li>
      <li>Una vez ingresado el codigo interno del repuesto, se debe agregar presionando el boton +</li>
      <li>Podrá ver agregado su repuesto y al costado podrá ver el detalle del kit y poder eliminar repuestos, en caso de que sea necesario.</li>
     
    </ol>
    
   <span style="font-size: 18px;" class="badge badge-warning">Al agregar o eliminar algun repuesto del kit el precio venta se irá actualizando.</span> <br>
   <hr>
   <span style="font-size: 18px;" class="badge badge-danger">Al eliminar algun repuesto del kit, se debe devolver el stock al repuesto de forma manual dependiendo del stock del kit.</span>
  </div>
    <div class="row">
      
        <div class="col-md-6">
            <div class="busqueda_principal" id="busqueda_principal">
                <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" class="logo">
            <div class="row" style="width: 100%;">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked>
                        <label class="form-check-label" for="flexRadioDefault1">
                          Codigo interno
                        </label>
                      </div>
                      
                      <div class="form-check DISABLE">
                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="oem" disabled>
                        <label class="form-check-label" for="flexRadioDefault2">
                          OEM
                        </label>
                      </div>
                      
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4" value="cod_prov" disabled>
                        <label class="form-check-label" for="flexRadioDefault4">
                          Codigo de proveedor
                        </label>
                      </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="codigo_repuesto">Ingrese el codigo interno del KIT</label>
                        <input type="text" class="form-control" name="codigo_repuesto" id="codigo_repuesto" placeholder="Ingrese codigo del KIT" >
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Buscar" class="btn btn-success btn-sm" onclick="buscar_repuesto()">
                    </div>
                </div>
            </div>
            
            </div>
            <div class="row">
              <div class="col-12">
                <div class="busqueda_resultado d-none" id="busqueda_resultado">
                  <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo">
                  <table class="table" id="tabla_resultados">
                      <thead class="thead-dark">
                        <tr>
                        
                          <th scope="col" class="letra_pequeña">Descripción</th>
                          <th scope="col" class="letra_pequeña">Stock Total</th>
                         
                          <th scope="col" class="letra_pequeña">Precio venta</th>
                          <th scope="col" class="letra_pequeña" style="width: 100px;">Codigo de proveedor</th>
                          <th scope="col" class="letra_pequeña">Pais</th>
                          <th scope="col" class="letra_pequeña">Locales</th>
                          <th scope="col" class="letra_pequeña"></th>
                        </tr>
                      </thead>
                      <tbody id="tbody_resultados">
                        
                      </tbody>
                    </table>
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Codigo interno</th>
                          <th scope="col">Descripcion</th>
                     
                          <th scope="col">Ubicación</th>
                          <th scope="col">P.U.</th>
                          
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody id="detalle_kit">
                        
                      </tbody>
                    </table>
                    
                   
              </div>
              </div>
              
            </div>
        </div>
        <div class="col-md-6">
          <div id="detalle_descuento" class="busqueda_principal d-none">
            <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" class="logo">
            <div class="row" style="width: 100%;">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault_" id="flexRadioDefault1" value="cod_int" checked>
                        <label class="form-check-label" for="flexRadioDefault1">
                          Codigo interno
                        </label>
                      </div>
                      
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault_" id="flexRadioDefault2" value="oem" disabled>
                        <label class="form-check-label" for="flexRadioDefault2">
                          OEM
                        </label>
                      </div>
                      
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault_" id="flexRadioDefault4" value="cod_prov" disabled>
                        <label class="form-check-label" for="flexRadioDefault4">
                          Codigo de proveedor
                        </label>
                      </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="codigo_repuesto">Ingrese el codigo del repuesto</label>
                        <input type="text" class="form-control" name="codigo_repuesto" id="codigo_repuesto_" placeholder="Ingrese codigo del repuesto">
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Buscar" class="btn btn-success btn-sm" onclick="buscar_repuesto_para_agregar()">
                    </div>
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="busqueda_resultado d-none" id="busqueda_resultado_">
                <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo">
                <table class="table" id="tabla_resultados_">
                    <thead class="thead-dark">
                      <tr>
                        
                        <th scope="col" class="letra_pequeña">Descripción</th>
                        <th scope="col" class="letra_pequeña">Stk. Bodega</th>
                        <th scope="col" class="letra_pequeña">Stk. Tienda</th>
                        <th scope="col" class="letra_pequeña">Stk. CM</th>
                        <th scope="col" class="letra_pequeña">Precio venta</th>
                        <th scope="col" class="letra_pequeña" style="width: 100px;">Codigo de proveedor</th>
                        <th scope="col" class="letra_pequeña">Marca</th>
                        <th scope="col" class="letra_pequeña">Pais</th>
                        <th scope="col" class="letra_pequeña"></th>
                        <th scope="col" class="letra_pequeña"></th>
                      </tr>
                    </thead>
                    <tbody id="tbody_resultados_">
                      
                    </tbody>
                  </table>
                  
            </div>
            </div>
          </div>
        </div>
        
    </div>
</div>
<input type="hidden" name="id_kit" id="id_kit" value="">
@endsection