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
                  // Pasar las familias de PHP a JS
                  var familias = @json($familias);
                  let html_familias = '<select id="familia_id">';
                  familias.forEach(fam => {
                    html_familias += '<option value="'+fam.id+'">'+fam.nombrefamilia+'</option>';
                  });
                  html_familias += '</select>';

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
    <div class="kit-result-card">
        <dl class="kit-result-fields">
            <div><dt>Descripción</dt><dd>`+element.descripcion+`</dd></div>
            <div><dt>Stock total</dt><dd><span class="stock_bajo" id="stock-`+element.id+`">`+stock_total+`</span></dd></div>
            <div><dt>Precio de venta</dt><dd><span id="ubicacion-`+element.id+`">$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</span></dd></div>
            <div><dt>Código proveedor</dt><dd>`+element.cod_repuesto_proveedor+`</dd></div>
            <div><dt>País</dt><dd>`+element.nombre_pais+`</dd></div>
        </dl>
        <div class="kit-result-control">
            <label for="familia_id">Familia</label>
            <div class="kit-family-control">
                `+html_familias+`
                <button type="button" class="btn btn-primary btn-sm" onclick="guardar_rep_familia(`+element.id+`, this)">Guardar familia</button>
            </div>
        </div>
        <div class="kit-result-control">
            <label for="locales">Local del kit</label>
            `+html_locales+`
        </div>
        <div class="kit-result-actions">
            <button type="button" class="btn btn-success" onclick="seleccionar_kit(`+element.id+`)">Seleccionar kit</button>
        </div>
    </div>
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

                      <td><button class='btn btn-danger btn-sm' onclick="eliminar_repuesto_kit(`+d.id_repuesto+`)">Eliminar</button> </td>
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
                  html_familias = '<select id="familia_id">';
                  var familias = @json($familias);
                  familias.forEach(familia => {
                    html_familias += '<option value='+familia.id+'>'+familia.nombrefamilia+' </option>';
                  });
                  html_familias += '</select>';


                    $('#busqueda_resultado').removeClass('d-none');
                    $('#busqueda_resultado').addClass('d-block');
                    $('#tbody_resultados').empty();
                    resp[0].forEach(element => {
                  let stock_total = parseInt(element.stock_actual)+parseInt(element.stock_actual_dos)+parseInt(element.stock_actual_tres);
                    $('#precio_repuesto').val(element.precio_venta);
                      $('#id_repuesto').val(element.id);
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
    <div class="kit-result-card">
        <dl class="kit-result-fields">
            <div><dt>Descripción</dt><dd>`+element.descripcion+`</dd></div>
            <div><dt>Stock total</dt><dd><span class="stock_bajo" id="stock-`+element.id+`">`+stock_total+`</span></dd></div>
            <div><dt>Precio de venta</dt><dd><span id="ubicacion-`+element.id+`">$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</span></dd></div>
            <div><dt>Código proveedor</dt><dd>`+element.cod_repuesto_proveedor+`</dd></div>
            <div><dt>País</dt><dd>`+element.nombre_pais+`</dd></div>
        </dl>
        <div class="kit-result-control">
            <label for="familia_id">Familia</label>
            <div class="kit-family-control">
                `+html_familias+`
                <button type="button" class="btn btn-primary btn-sm" onclick="guardar_rep_familia(`+element.id+`, this)">Guardar familia</button>
            </div>
        </div>
        <div class="kit-result-control">
            <label for="locales">Local del kit</label>
            `+html_locales+`
        </div>
        <div class="kit-result-actions">
            <button type="button" class="btn btn-success" onclick="seleccionar_kit(`+element.id+`)">Seleccionar kit</button>
        </div>
    </div>
`);

                 });
                    $('#detalle_kit').html('<tr><td colspan="5" class="kit-empty">Este kit todavía no tiene repuestos. Selecciónalo para comenzar a agregar.</td></tr>');

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
    <div class="kit-result-card">
        <dl class="kit-result-fields">
            <div><dt>Descripción</dt><dd>`+element.descripcion+`</dd></div>
            <div><dt>Stock bodega</dt><dd>`+parseInt(element.stock_actual)+`</dd></div>
            <div><dt>Stock tienda</dt><dd>`+parseInt(element.stock_actual_dos)+`</dd></div>
            <div><dt>Stock casa matriz</dt><dd>`+parseInt(element.stock_actual_tres)+`</dd></div>
            <div><dt>Precio de venta</dt><dd>$ `+commaSeparateNumber(parseInt(element.precio_venta).toFixed(0))+`</dd></div>
            <div><dt>Código proveedor</dt><dd>`+element.cod_repuesto_proveedor+`</dd></div>
            <div><dt>Marca</dt><dd>`+element.marcarepuesto+`</dd></div>
            <div><dt>País</dt><dd>`+element.nombre_pais+`</dd></div>
        </dl>
        <div class="kit-result-control">
            <label for="locales_">Local de origen</label>
            `+html_locales+`
        </div>
        <div class="kit-result-actions">
            <button type="button" class="btn btn-success" onclick="agregar_repuesto(`+element.id+`)">Agregar</button>
        </div>
    </div>
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
            const pasoDos = document.getElementById('detalle_descuento');
            const cabecera = document.getElementById('navHeader');
            let margen = 20;
            if (cabecera) {
              const posicion = window.getComputedStyle(cabecera).position;
              if (posicion === 'fixed' || posicion === 'sticky') {
                margen += cabecera.getBoundingClientRect().height;
              }
            }
            document.getElementById('codigo_repuesto_').focus({ preventScroll: true });
            window.scrollTo({
              top: Math.max(0, pasoDos.getBoundingClientRect().top + window.scrollY - margen),
              behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
            });
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
                  $('#info_kit').append('<tr><td>'+d.codigo_interno+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">Eliminar</button> </td></tr>');
                });

                $('#detalle_kit').empty();
                detalle.forEach(d => {
                  $('#detalle_kit').append('<tr><td>'+d.codigo_interno+'</td><td>'+d.descripcion+' </td><td>'+d.local_nombre+' </td><td>$ '+commaSeparateNumber(parseInt(d.precio_venta).toFixed(0))+' </td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">Eliminar</button> </td></tr>');
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
                $('#info_kit').append('<tr><td>'+d.codigo_interno+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">Eliminar</button> </td></tr>');
              });

              $('#detalle_kit').empty();
              detalle.forEach(d => {
                $('#detalle_kit').append('<tr><td>'+d.codigo_interno+'</td><td>'+d.descripcion+' </td><td>'+d.local_nombre+' </td><td>$ '+commaSeparateNumber(parseInt(d.precio_venta).toFixed(0))+' </td><td><button class="btn btn-danger btn-sm" onclick="eliminar_repuesto_kit('+d.idrep+')">Eliminar</button> </td></tr>');
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
    function guardar_rep_familia(id_repuesto, btn) {
        // Obtener el select de familia que está inmediatamente antes del botón
        var select = btn.previousElementSibling;
        var id_familia = select.value;

        // Validar que se seleccione una familia
        if(!id_familia || id_familia === ''){
            Vue.swal({
                icon: 'warning',
                text: 'Seleccione una familia válida',
                timer: 2000,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
            return false;
        }

        // Realizar la petición AJAX para guardar la familia
        $.ajax({
            url: '{{ route("guardar_familia_kit") }}',
            method: 'POST',
            data: {
                id_repuesto: id_repuesto,
                id_familia: id_familia,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function(){
                // Opcional: mostrar estado de carga
                btn.disabled = true;
            },
            success: function(resp) {
                btn.disabled = false;
                console.log('Respuesta del servidor:', resp);

                // Validar la respuesta
                if(resp.estado === 'OK'){
                    Vue.swal({
                        icon: 'success',
                        text: 'Familia actualizada correctamente',
                        timer: 2000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                } else {
                    Vue.swal({
                        icon: 'warning',
                        text: resp.mensaje || 'Error al actualizar la familia',
                        timer: 3000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            },
            error: function(err) {
                btn.disabled = false;
                console.error('Error en la solicitud:', err);

                // Intentar obtener el mensaje de error del servidor
                let mensaje = 'Error al guardar la familia';
                try {
                    let response = JSON.parse(err.responseText);
                    mensaje = response.mensaje || mensaje;
                } catch(e) {
                    mensaje = err.statusText || mensaje;
                }

                Vue.swal({
                    icon: 'error',
                    text: mensaje,
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
        });
    }
</script>
@endsection

@section('contenido_titulo_pagina')
<h4 class="text-center py-2 mb-0">Armado de kits de repuestos</h4>
@endsection

@section('style')
<style>
    .kit-page { max-width: 1680px; margin: 24px auto 40px; color: #334155; }
    .kit-page .busqueda_principal, .kit-page .busqueda_resultado, .kit-guide {
        background: #fff; border: 1px solid #dce2e8; border-radius: 10px; padding: 24px; margin-bottom: 20px;
    }
    .kit-page h2 { font-size: 22px; font-weight: 600; color: #243449; margin-bottom: 8px; }
    .kit-page h3 { font-size: 18px; font-weight: 600; color: #243449; margin-bottom: 8px; }
    .kit-page .kit-muted { color: #64748b; font-size: 14px; margin-bottom: 20px; }
    .kit-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 0; margin: 24px 0; list-style: none; }
    .kit-steps li { display: flex; gap: 12px; font-size: 14px; line-height: 1.6; }
    .kit-step { flex: 0 0 30px; height: 30px; border-radius: 50%; background: #edf2f7; color: #36516f; text-align: center; line-height: 30px; font-weight: 600; }
    .kit-steps strong { display: block; color: #334155; margin-bottom: 3px; }
    .kit-notice { padding: 12px 16px; border-radius: 6px; background: #f1f5f9; font-size: 14px; line-height: 1.6; margin-top: 10px; border-left: 3px solid #94a3b8; }
    .kit-notice-stock { background: #faf6ed; border-color: #c49a54; color: #745b32; }
    .kit-page label { font-size: 14px; font-weight: 600; }
    .kit-search { display: flex; gap: 12px; align-items: flex-end; }
    .kit-search .form-group { flex: 1; margin-bottom: 0; }
    .kit-page .form-control, .kit-page select { border: 1px solid #cbd5e1; border-radius: 6px; color: #334155; background: #fff; min-height: 40px; }
    .kit-page select { padding: 5px 8px; max-width: 220px; }
    .kit-page .btn { border-radius: 6px; font-size: 14px; }
    .kit-page .kit-search .btn { min-height: 40px; padding: 8px 20px; }
    .kit-page .btn-success, .kit-page .btn-primary { background: #36516f; border-color: #36516f; color: #fff; }
    .kit-page .btn-success:hover, .kit-page .btn-primary:hover { background: #263e58; border-color: #263e58; }
    .kit-page .btn-danger { background: #fff; color: #a34545; border-color: #e1bcbc; }
    .kit-page .btn-danger:hover { background: #fbefef; }
    .kit-page input:focus, .kit-page select:focus, .kit-page button:focus { outline: 2px solid #6286ab; outline-offset: 2px; }
    .kit-page .table-responsive { margin-top: 16px; }
    .kit-page .table { margin-bottom: 0; font-size: 14px; }
    .kit-page .table th, .kit-page .table .thead-dark th { background: #f1f5f9; color: #475569; border-color: #e2e8f0; font-size: 12px; font-weight: 600; vertical-align: middle; }
    .kit-page .table td { vertical-align: middle; border-color: #e8edf2; padding: 12px 10px; }
    .kit-page .table tbody tr:hover { background: #f8fafc; }
    .kit-page .table .btn { white-space: nowrap; }
    .kit-result-card { margin-top: 20px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .kit-result-fields { margin: 0; }
    .kit-result-fields > div { padding: 14px 18px; border-bottom: 1px solid #e8edf2; }
    .kit-result-fields dt, .kit-result-control > label { display: block; color: #64748b; font-size: 13px; font-weight: 600; margin: 0 0 6px; }
    .kit-result-fields dd { margin: 0; color: #334155; font-size: 15px; line-height: 1.6; overflow-wrap: anywhere; }
    .kit-result-control { padding: 14px 18px; border-bottom: 1px solid #e8edf2; }
    .kit-family-control { display: flex; flex-wrap: wrap; gap: 10px; }
    .kit-page .kit-result-control select { width: 100%; max-width: 100%; min-width: 0; }
    .kit-family-control select { flex: 1 1 200px; }
    .kit-result-actions { padding: 16px 18px; background: #f8fafc; }
    .kit-result-actions .btn { padding: 9px 20px; }
    .kit-page .kit-table-title { border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 24px; }
    .kit-page .kit-empty { padding: 28px 16px; text-align: center; color: #64748b; font-size: 14px; }
    .kit-page .imagen_pequeña { width: 120px; }
    @media (max-width: 767px) {
        .kit-page { margin-top: 16px; }
        .kit-page .busqueda_principal, .kit-page .busqueda_resultado, .kit-guide { padding: 18px; }
        .kit-steps { grid-template-columns: 1fr; gap: 16px; }
        .kit-search { flex-direction: column; align-items: stretch; }
    }
</style>
@endsection

@section('mensajes')
  @include('fragm.mensajes')
@endsection



@section('contenido_ingresa_datos')
<div class="container-fluid kit-page">
  <section class="kit-guide" aria-labelledby="kit-guide-title">
    <h2 id="kit-guide-title">Configura los repuestos de tu kit</h2>
    <p class="kit-muted">Busca un kit existente, selecciona su local y administra los repuestos que lo componen.</p>
    <ol class="kit-steps">
      <li><span class="kit-step">1</span><div><strong>Busca el kit</strong>Usa el código interno exclusivo del kit. No ingreses el código de un repuesto individual.</div></li>
      <li><span class="kit-step">2</span><div><strong>Selecciona el kit y el local</strong>Revisa el resultado, elige el local y presiona “Seleccionar kit” para habilitar la edición.</div></li>
      <li><span class="kit-step">3</span><div><strong>Agrega o elimina repuestos</strong>Busca cada repuesto por su código interno y presiona “Agregar”. Revisa la composición del kit en la tabla.</div></li>
    </ol>
    <div class="kit-notice"><strong>Precio de venta:</strong> se actualiza automáticamente al agregar o eliminar repuestos.</div>
    <div class="kit-notice kit-notice-stock"><strong>Al eliminar un repuesto:</strong> debes devolver su stock manualmente, según el stock del kit.</div>
  </section>
    <div class="row">

        <div class="col-xl-6">
            <div class="busqueda_principal" id="busqueda_principal">
                <h3>1. Buscar kit</h3>
                <p class="kit-muted">Ingresa su código interno para consultar el precio, stock y composición.</p>
                <input type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked hidden>
                <div class="kit-search">
                    <div class="form-group">
                        <label for="codigo_repuesto">Código interno del kit</label>
                        <input type="text" class="form-control" name="codigo_repuesto" id="codigo_repuesto" placeholder="Ej.: código de tu kit" onkeydown="if(event.key === 'Enter'){event.preventDefault();buscar_repuesto();}">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="buscar_repuesto()">Buscar kit</button>
                </div>

            </div>
            <div class="row">
              <div class="col-12">
                <div class="busqueda_resultado d-none" id="busqueda_resultado">

                  <h3>Resultado de búsqueda del kit</h3><p class="kit-muted">Elige el local del kit antes de seleccionarlo.</p><div id="tabla_resultados"><div id="tbody_resultados"></div></div>
                    <h3 class="kit-table-title">Composición del kit</h3><div class="table-responsive"><table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Código interno</th>
                          <th scope="col">Descripción</th>

                          <th scope="col">Ubicación</th>
                          <th scope="col">Precio unitario</th>

                          <th scope="col">Acción</th>
                        </tr>
                      </thead>
                      <tbody id="detalle_kit">

                      </tbody>
                    </table></div>


              </div>
              </div>

            </div>
        </div>
        <div class="col-xl-6">
          <div id="detalle_descuento" class="busqueda_principal d-none">
            <h3>2. Agregar repuestos</h3>
            <p class="kit-muted">Busca el repuesto, elige su local de origen y presiona “Agregar”.</p>
            <input type="radio" name="flexRadioDefault_" id="kit-part-code-option" value="cod_int" checked hidden>
            <div class="kit-search">
                <div class="form-group">
                    <label for="codigo_repuesto_">Código interno del repuesto</label>
                    <input type="text" class="form-control" name="codigo_repuesto_" id="codigo_repuesto_" placeholder="Código del repuesto a agregar" onkeydown="if(event.key === 'Enter'){event.preventDefault();buscar_repuesto_para_agregar();}">
                </div>
                <button type="button" class="btn btn-primary" onclick="buscar_repuesto_para_agregar()">Buscar repuesto</button>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="busqueda_resultado d-none" id="busqueda_resultado_">

                <h3>Repuestos disponibles para agregar</h3><div id="tabla_resultados_"><div id="tbody_resultados_"></div></div>

            </div>
            </div>
          </div>
        </div>

    </div>
</div>
<input type="hidden" name="id_kit" id="id_kit" value="">
@endsection