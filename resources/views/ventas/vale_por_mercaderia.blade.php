@extends('plantillas.app')

@section('titulo','Vale por mercadería')
@section('style')
    <style>
        .vm-page{
            background:#f4f7fb;
            padding:18px;
            border-radius:14px;
        }

        .vm-card{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:16px;
            box-shadow:0 8px 22px rgba(15,23,42,.08);
            overflow:hidden;
            margin-bottom:18px;
        }

        .vm-card-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:16px 18px;
            border-bottom:1px solid #eef2f7;
            background:linear-gradient(135deg,#0f172a,#1e3a8a);
            color:#fff;
        }

        .vm-card-header.secondary{
            background:linear-gradient(135deg,#064e3b,#0f766e);
        }

        .vm-card-title{
            margin:0;
            font-size:1rem;
            font-weight:700;
            letter-spacing:.2px;
        }

        .vm-card-subtitle{
            margin:2px 0 0;
            font-size:.78rem;
            opacity:.85;
        }

        .vm-card-body{
            padding:18px;
        }

        .vm-logo{
            width:76px;
            height:auto;
            background:#fff;
            border-radius:12px;
            padding:6px;
            box-shadow:0 3px 10px rgba(0,0,0,.12);
        }

        .vm-label{
            font-weight:700;
            color:#334155;
            font-size:.82rem;
            margin-bottom:5px;
        }

        .vm-help{
            color:#64748b;
            font-size:.78rem;
            margin-top:4px;
        }

        .vm-actions{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            padding-top:8px;
        }

        .vm-search-box{
            background:#f8fafc;
            border:1px dashed #cbd5e1;
            border-radius:14px;
            padding:14px;
        }

        .vm-result-box{
            margin-top:14px;
        }

        .vm-radio-panel{
            background:#f8fafc;
            border:1px solid #e2e8f0;
            border-radius:12px;
            padding:12px;
        }

        .radio_button_options{
            display:flex;
            justify-content:center;
            flex-wrap:wrap;
            gap:14px;
            padding:12px;
            background:#f8fafc;
            border:1px solid #e5e7eb;
            border-radius:12px;
            margin-bottom:14px;
        }

        .vm-table-wrap{
            border:1px solid #e5e7eb;
            border-radius:12px;
            overflow:hidden;
        }

        .vm-table-wrap table{
            margin-bottom:0;
        }

        .vm-badge-soft{
            background:#e0f2fe;
            color:#075985;
            border-radius:999px;
            padding:6px 10px;
            font-size:.78rem;
            font-weight:700;
            white-space:nowrap;
        }


        .vm-product-result{
            background:#fff;
            border:1px solid #e2e8f0;
            border-radius:12px;
            box-shadow:0 4px 14px rgba(15,23,42,.06);
            padding:14px;
        }

        .vm-product-name{
            font-weight:700;
            color:#1e293b;
            line-height:1.35;
        }

        .vm-stock-chip{
            display:inline-flex;
            align-items:center;
            gap:5px;
            padding:6px 9px;
            border-radius:8px;
            background:#f8fafc;
            border:1px solid #e2e8f0;
            font-size:.78rem;
            margin:2px 3px 2px 0;
        }

        .vm-price-box{
            background:#ecfdf5;
            border:1px solid #a7f3d0;
            border-radius:10px;
            padding:10px 12px;
            text-align:right;
        }

        .vm-price-box small{
            display:block;
            color:#64748b;
        }

        .vm-price-box strong{
            color:#047857;
            font-size:1.1rem;
        }

        .vm-result-control label{
            font-size:.75rem;
            font-weight:700;
            color:#475569;
            margin-bottom:4px;
        }

        .vm-modal-status{
            border-radius:10px;
        }

        @media (max-width: 768px){
            .vm-card-header{
                align-items:flex-start;
                flex-direction:column;
            }
            .vm-logo{
                width:64px;
            }
        }


        /* Modal Repuesto Express */
        #modal-repuesto-xpress-vale .modal-content{
            border-radius:18px;
            overflow:hidden;
        }

        #modal-repuesto-xpress-vale .xpress-header{
            background:linear-gradient(135deg,#f59e0b,#facc15);
            color:#1f2937;
        }

        #modal-repuesto-xpress-vale .xpress-icon{
            width:48px;
            height:48px;
            border-radius:14px;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 6px 16px rgba(15,23,42,.12);
        }

        #modal-repuesto-xpress-vale .xpress-card{
            border:1px solid #e5e7eb;
            border-radius:14px;
            box-shadow:0 5px 16px rgba(15,23,42,.06);
            height:100%;
        }

        #modal-repuesto-xpress-vale .documento-card{
            background:#fff;
            color:#64748b;
            border:2px solid #e2e8f0 !important;
            border-radius:12px;
            cursor:pointer;
            transition:all .2s ease;
        }

        #modal-repuesto-xpress-vale .documento-card:hover{
            border-color:#3b82f6 !important;
            transform:translateY(-2px);
            box-shadow:0 6px 16px rgba(15,23,42,.08);
        }

        #modal-repuesto-xpress-vale
        input[name="vale_xpress_tipo_documento"]:checked + .documento-card{
            color:#1d4ed8;
            background:#eff6ff;
            border-color:#3b82f6 !important;
            box-shadow:0 0 0 3px rgba(59,130,246,.12);
        }

        #modal-repuesto-xpress-vale .formas-pago-box{
            min-height:92px;
            padding:14px;
            border:1px solid #e2e8f0;
            border-radius:12px;
            background:#f8fafc;
        }

        #modal-repuesto-xpress-vale .formas-pago-box .form-check,
        #modal-repuesto-xpress-vale .formas-pago-box .custom-control{
            margin-bottom:8px;
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
                        console.log(resp);
                        Vue.swal.close();

                        if(!resp || String(resp).trim() === ''){
                            Vue.swal({
                                icon:'warning',
                                text:'No se encontró información para el vale ingresado.'
                            });
                            $('#total_devolucion').empty();
                            return;
                        }

                        $('#total_devolucion').html(resp);
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
      const codigoInterno = String($('#repuesto-xpress-codigo').val() || '').trim();
      const $botonBuscar = $('#btn_buscar_repuesto_vale');
      const $botonAgregar = $('#btn_agregar_repuesto_buscado');

      if(codigoInterno === ''){
        Vue.swal({
          icon:'warning',
          text:'Debe ingresar un código interno',
          toast:true,
          position:'top-end',
          timer:3000,
          showConfirmButton:false
        });

        $('#repuesto-xpress-codigo').focus();
        return false;
      }

      const url = '/repuesto/buscarcodigo/1' + encodeURIComponent(codigoInterno);

      $.ajax({
        type:'get',
        url:url,

        beforeSend:function(){
          $botonBuscar
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1"></span> Buscando');

          $botonAgregar
            .prop('disabled', true)
            .removeAttr('onclick');

          $('#informacion_repuesto').html(`
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <span class="spinner-border spinner-border-sm mr-2"></span>
                Buscando repuesto...
              </td>
            </tr>
          `);

          $('#mensaje_modal')
            .removeClass('alert-success alert-danger')
            .addClass('alert-light')
            .html('<i class="fas fa-search mr-1"></i> Consultando código ' + codigoInterno + '...');
        },

        success:function(resp){
          if(resp == -1 || !resp || !resp[0]){
            $('#informacion_repuesto').html(`
              <tr>
                <td colspan="7" class="text-center py-5">
                  <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                  <strong>No se encontró el repuesto</strong>
                  <div class="small text-muted mt-1">
                    Revise el código e intente nuevamente.
                  </div>
                </td>
              </tr>
            `);

            $('#mensaje_modal')
              .removeClass('alert-light alert-success')
              .addClass('alert-danger')
              .html('<i class="fas fa-exclamation-circle mr-1"></i> Sin resultados.');

            return false;
          }

          let repuestos = [];
          let locales = Array.isArray(resp[2]) ? resp[2] : [];

          try{
            repuestos = typeof resp[0] === 'string'
              ? JSON.parse(resp[0])
              : resp[0];
          }catch(error){
            repuestos = [];
          }

          if(!Array.isArray(repuestos) || repuestos.length === 0){
            $('#informacion_repuesto').html(`
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  No se encontraron datos válidos para el producto.
                </td>
              </tr>
            `);

            return false;
          }

          $('#informacion_repuesto').empty();

          repuestos.forEach(function(e){
            const precio = new Intl.NumberFormat('es-CL').format(
              Math.round(Number(e.precio_venta) || 0)
            );

            const ubicaciones = [
              {
                nombre:e.ubicacion || 'Ubicación 1',
                stock:Number(e.stock_actual) || 0
              },
              {
                nombre:e.ubicacion_dos || 'Ubicación 2',
                stock:Number(e.stock_actual_dos) || 0
              },
              {
                nombre:e.ubicacion_tres || 'Ubicación 3',
                stock:Number(e.stock_actual_tres) || 0
              }
            ];

            const chips = ubicaciones.map(function(ubicacion){
              return `
                <span class="vm-stock-chip">
                  <i class="fas fa-warehouse ${ubicacion.stock > 0 ? 'text-success' : 'text-muted'}"></i>
                  ${ubicacion.nombre}:
                  <strong>${ubicacion.stock}</strong>
                </span>
              `;
            }).join('');

            $('#informacion_repuesto').append(`
              <tr>
                <td colspan="7" class="p-3">
                  <div class="vm-product-result">
                    <div class="row align-items-center">

                      <div class="col-lg-5 mb-3 mb-lg-0">
                        <div class="small text-muted mb-1">
                          Producto encontrado
                        </div>

                        <div class="vm-product-name">
                          ${e.descripcion || 'Sin descripción'}
                        </div>

                        <div class="mt-2">
                          ${chips}
                        </div>
                      </div>

                      <div class="col-lg-2 col-md-4 vm-result-control mb-3 mb-lg-0">
                        <label for="cantidad_repuesto_vale">Cantidad</label>

                        <input type="number"
                               min="1"
                               step="1"
                               id="cantidad_repuesto_vale"
                               name="cantidad_repuesto_vale"
                               class="form-control"
                               placeholder="1"
                               inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>

                      <div class="col-lg-2 col-md-4 vm-result-control mb-3 mb-lg-0">
                        <label for="origen_repuesto_vale">Origen de stock</label>

                        <select id="origen_repuesto_vale"
                                name="origen_repuesto_vale"
                                class="form-control">
                        </select>
                      </div>

                      <div class="col-lg-3 col-md-4">
                        <div class="vm-price-box">
                          <small>Precio de venta</small>
                          <strong>$ ${precio}</strong>
                        </div>
                      </div>

                    </div>
                  </div>
                </td>
              </tr>
            `);

            const $select = $('#origen_repuesto_vale');

            $select
              .empty()
              .append('<option value="">Seleccione origen</option>');

            locales.forEach(function(local){
              $select.append(
                $('<option>', {
                  value:local.id,
                  text:local.local_nombre
                })
              );
            });

            $botonAgregar
              .attr('onclick', 'agregar_repuesto_buscado(' + e.id + ')')
              .prop('disabled', false);
          });

          $('#mensaje_modal')
            .removeClass('alert-light alert-danger')
            .addClass('alert-success')
            .html(
              '<i class="fas fa-check-circle mr-1"></i>' +
              ' Repuesto encontrado. Complete cantidad y origen.'
            );

          setTimeout(function(){
            $('#exampleModal')
              .find('#cantidad_repuesto_vale')
              .first()
              .focus();
          }, 100);
        },

        error:function(error){
          $('#informacion_repuesto').html(`
            <tr>
              <td colspan="7" class="text-center py-5 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                No fue posible realizar la búsqueda.
              </td>
            </tr>
          `);

          $('#mensaje_modal')
            .removeClass('alert-light alert-success')
            .addClass('alert-danger')
            .html(
              '<i class="fas fa-exclamation-circle mr-1"></i>' +
              (error.responseText || 'Error de búsqueda.')
            );
        },

        complete:function(){
          $botonBuscar
            .prop('disabled', false)
            .html('<i class="fas fa-search mr-1"></i> Buscar');
        }
      });
    }

    function agregar_repuesto_buscado(idrep){
        const $cantidadInput = $('#exampleModal').find('#cantidad_repuesto_vale').first();
        const $origenSelect = $('#exampleModal').find('#origen_repuesto_vale').first();

        const cantidadTexto = String($cantidadInput.val() || '').trim();
        const cantidad = parseInt(cantidadTexto, 10);
        const origen = $origenSelect.val();
        const numeroVale = String($('#numero_vale').val() || '').trim();

        if(
            cantidadTexto === '' ||
            !/^\d+$/.test(cantidadTexto) ||
            !Number.isInteger(cantidad) ||
            cantidad <= 0
        ){
            Vue.swal({
                text:'Ingrese una cantidad válida mayor a cero',
                position:'top-end',
                icon:'warning',
                toast:true,
                showConfirmButton:false,
                timer:3000
            });
            $cantidadInput.focus();
            return false;
        }

        if(!origen){
            Vue.swal({
                text:'Debe seleccionar el origen del stock',
                position:'top-end',
                icon:'warning',
                toast:true,
                showConfirmButton:false,
                timer:3000
            });
            $origenSelect.focus();
            return false;
        }

        if(numeroVale === ''){
            Vue.swal({
                icon:'error',
                text:'No se encontró el número del vale cargado.'
            });
            return false;
        }

        agregar_mercaderia(idrep, numeroVale);
    }

  function agregar_mercaderia(idrep, num_boucher){
    const origen = $('#exampleModal').find('#origen_repuesto_vale').first().val();
    const cantidad = $('#exampleModal').find('#cantidad_repuesto_vale').first().val();
    const $botonAgregar = $('#btn_agregar_repuesto_buscado');

    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type:'post',
        url:'/ventas/agregar_repuesto_valemercaderia',
        data:{
            idrep:idrep,
            num_boucher:num_boucher,
            cantidad:cantidad,
            origen:origen
        },
        beforeSend:function(){
            $botonAgregar.prop('disabled', true)
              .html('<span class="spinner-border spinner-border-sm mr-1"></span> Agregando');

            $('#mensaje_modal')
              .removeClass('alert-success alert-danger')
              .addClass('alert-light')
              .html('<i class="fas fa-cart-plus mr-1"></i> Agregando producto al vale...');
        },
        success:function(resp){
            if(resp === 'error'){
                $('#mensaje_modal')
                  .removeClass('alert-light alert-success')
                  .addClass('alert-danger')
                  .html(
                    '<i class="fas fa-exclamation-circle mr-1"></i>' +
                    ' No hay stock en el local seleccionado.'
                  );

                Vue.swal({
                    text:'No hay stock suficiente en el local seleccionado',
                    icon:'error'
                });

                return false;
            }

            if(resp === 'viejo'){
                $('#mensaje_modal')
                  .removeClass('alert-light alert-success')
                  .addClass('alert-danger')
                  .html(
                    '<i class="fas fa-clock mr-1"></i>' +
                    ' El precio del repuesto requiere actualización.'
                  );

                Vue.swal({
                    text:'El repuesto no tiene su precio actualizado',
                    icon:'info'
                });

                return false;
            }

            const nuevaVistaVale = resp;
            const $modal = $('#exampleModal');

            $modal.one('hidden.bs.modal', function(){
                $('#vale_mercaderia_detalle').html(nuevaVistaVale);

                $('.modal-backdrop').remove();

                $('body')
                  .removeClass('modal-open')
                  .css('padding-right', '');

                Vue.swal({
                    icon:'success',
                    title:'Producto agregado',
                    text:'El repuesto fue incorporado correctamente al vale.',
                    toast:true,
                    position:'top-end',
                    timer:3000,
                    showConfirmButton:false
                });
            });
            buscar_devolucion();
            $modal.modal('hide');
        },

        error:function(error){
            $('#mensaje_modal')
              .removeClass('alert-light alert-success')
              .addClass('alert-danger')
              .html('<i class="fas fa-exclamation-circle mr-1"></i> No se pudo agregar el producto.');

            Vue.swal({
                icon:'error',
                text:error.responseText || 'No se pudo agregar el producto.'
            });
        },
        complete:function(){
            const $botonActual = $('#btn_agregar_repuesto_buscado');

            if($botonActual.length){
                $botonActual
                  .prop('disabled', false)
                  .html('<i class="fas fa-plus mr-1"></i> Agregar repuesto');
            }
        }
    });
  }

  let datosValeXpress = {
    numeroVale: null,
    saldoFaltante: 0
};

function formato_peso_xpress(valor) {
    return new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 0
    }).format(Math.round(Number(valor) || 0));
}

function abrir_modal_repuesto_xpress_vale(numeroVale, saldoFaltante) {
    saldoFaltante = Math.max(0, Math.round(Number(saldoFaltante) || 0));

    if (!numeroVale) {
        Vue.swal({
            icon: 'error',
            text: 'No se encontró el número del vale.'
        });
        return;
    }

    if (saldoFaltante <= 0) {
        Vue.swal({
            icon: 'info',
            text: 'El vale ya se encuentra completado.'
        });
        return;
    }

    datosValeXpress.numeroVale = numeroVale;
    datosValeXpress.saldoFaltante = saldoFaltante;

    $('#vale-xpress-codigo').val('VALE-' + numeroVale);
    $('#vale-xpress-descripcion').val(
        'COMPLEMENTO VALE DE MERCADERÍA N° ' + numeroVale
    );
    $('#vale-xpress-cantidad').val(1);
    $('#vale-xpress-precio')
        .val(formato_peso_xpress(saldoFaltante))
        .data('valor', saldoFaltante);

    $('#resumen-total-xpress').text(
        '$ ' + formato_peso_xpress(saldoFaltante)
    );

    $('#vale-xpress-tipo-boleta').prop('checked', true);
    $('#resumen-tipo-documento').text('Boleta');

    $('#formas_pago').html(
        '<div class="text-center text-muted py-3">' +
            '<span class="spinner-border spinner-border-sm mr-2"></span>' +
            'Cargando formas de pago...' +
        '</div>'
    );

    $('#error-formas-pago').hide();
    $('#btn-guardar-xpress-vale').prop('disabled', true);

    $('#modal-repuesto-xpress-vale').modal('show');
    dame_formas_pago();
}

$(document).on(
    'change',
    'input[name="vale_xpress_tipo_documento"]',
    function () {
        $('#resumen-tipo-documento').text(
            $(this).val() === 'fa' ? 'Factura' : 'Boleta'
        );
    }
);

function obtener_formas_pago_xpress() {
    const datos = {};

    $('#formas_pago')
        .find('input, select, textarea')
        .each(function () {
            const $campo = $(this);
            const nombre = $campo.attr('name');

            if (!nombre || $campo.is(':disabled')) {
                return;
            }

            if (($campo.is(':checkbox') || $campo.is(':radio')) &&
                !$campo.is(':checked')) {
                return;
            }

            datos[nombre] = $campo.val();
        });

    return datos;
}

function hay_forma_pago_xpress_seleccionada() {
    const $camposMarcables = $('#formas_pago').find(
        'input[type="checkbox"], input[type="radio"]'
    );

    if ($camposMarcables.length > 0) {
        return $camposMarcables.filter(':checked').length > 0;
    }

    const $selects = $('#formas_pago').find('select');

    if ($selects.length > 0) {
        let seleccionada = false;
        $selects.each(function () {
            if ($(this).val()) {
                seleccionada = true;
            }
        });
        return seleccionada;
    }

    return false;
}

function guardar_repuesto_xpress_vale() {
    return Vue.swal({
        title:'EN CONSTRUCCION',
        icon:'info',
        text:'En construcción'
    });
    const codigo = String($('#vale-xpress-codigo').val() || '').trim();
    const descripcion = String(
        $('#vale-xpress-descripcion').val() || ''
    ).trim();

    const cantidad = parseInt($('#vale-xpress-cantidad').val(), 10);
    const precio = Math.round(
        Number($('#vale-xpress-precio').data('valor')) || 0
    );

    const tipoDocumento = $(
        'input[name="vale_xpress_tipo_documento"]:checked'
    ).val();

    if (!descripcion) {
        Vue.swal({
            icon: 'warning',
            text: 'Debe ingresar una descripción.'
        });
        $('#vale-xpress-descripcion').focus();
        return;
    }

    if (!tipoDocumento) {
        Vue.swal({
            icon: 'warning',
            text: 'Debe seleccionar Boleta o Factura.'
        });
        return;
    }

    if (!hay_forma_pago_xpress_seleccionada()) {
        Vue.swal({
            icon: 'warning',
            text: 'Debe seleccionar al menos una forma de pago.'
        });
        return;
    }

    if (!Number.isInteger(cantidad) || cantidad <= 0 || precio <= 0) {
        Vue.swal({
            icon: 'warning',
            text: 'El monto del complemento no es válido.'
        });
        return;
    }

    if (!datosValeXpress.numeroVale) {
        Vue.swal({
            icon: 'error',
            text: 'No se encontró el vale cargado.'
        });
        return;
    }

    const formasPago = obtener_formas_pago_xpress();

    Vue.swal({
        title: '¿Crear Repuesto Express?',
        html:
            '<div class="text-left">' +
                '<div class="d-flex justify-content-between mb-2">' +
                    '<span>Documento:</span>' +
                    '<strong>' +
                        (tipoDocumento === 'fa' ? 'Factura' : 'Boleta') +
                    '</strong>' +
                '</div>' +
                '<div class="d-flex justify-content-between">' +
                    '<span>Total:</span>' +
                    '<strong>$ ' + formato_peso_xpress(precio) + '</strong>' +
                '</div>' +
            '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, crear y agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#16a34a'
    }).then(function (resultado) {
        if (!resultado.isConfirmed) {
            return;
        }

        const boton = $('#btn-guardar-xpress-vale');
        const parametros = Object.assign({}, formasPago, {
            codigo: codigo,
            descripcion: descripcion,
            precio: precio,
            tipo_documento: tipoDocumento,
            num_boucher: datosValeXpress.numeroVale
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ url('repuesto/guardar_xpress') }}",
            data: parametros,

            beforeSend: function () {
                boton
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm mr-1"></span>' +
                        'Creando...'
                    );
            },

            success: function (respuesta) {
                const idRepuestoXpress = parseInt(
                    respuesta.id || respuesta.id_repuesto || respuesta,
                    10
                );

                if (!Number.isInteger(idRepuestoXpress) ||
                    idRepuestoXpress <= 0) {
                    Vue.swal({
                        icon: 'error',
                        text: respuesta.mensaje ||
                            'No fue posible crear el Repuesto Express.'
                    });
                    return;
                }

                agregar_xpress_al_detalle_vale(
                    idRepuestoXpress,
                    datosValeXpress.numeroVale,
                    cantidad
                );
            },

            error: function (error) {
                Vue.swal({
                    icon: 'error',
                    text:
                        error.responseJSON?.mensaje ||
                        error.responseText ||
                        'No fue posible crear el Repuesto Express.'
                });
            },

            complete: function () {
                boton
                    .prop('disabled', false)
                    .html(
                        '<i class="fas fa-check-circle mr-1"></i>' +
                        'Cerrar el vale con Repuesto Express'
                    );
            }
        });
    });
}

function generar_xml()
{
  /*
   * El indicador #es_oferta viene dentro del HTML dinámico del carrito.
   * Se normaliza porque puede recibirse como 1, "1", true o "SI".
   */
  const elementoOferta = $('#carrito #es_oferta').first().length
      ? $('#carrito #es_oferta').first()
      : $('#es_oferta').first();

  const valorOferta = elementoOferta.length
      ? String(elementoOferta.val() ?? elementoOferta.text() ?? '').trim().toLowerCase()
      : '';

  const es_oferta = ['1', 'true', 'si', 'sí', 'oferta'].includes(valorOferta);
  const id_cliente = Number($('#id_cliente').val() || 0);

  console.log('Validación de oferta antes de procesar', {
      existeIndicador: elementoOferta.length > 0,
      valorOferta: valorOferta,
      esOferta: es_oferta,
      ofertaConfirmada: oferta_confirmada_en_proceso
  });

  // Solo se pregunta una vez por cada intento de procesamiento.
  if(es_oferta && !oferta_confirmada_en_proceso){
          Vue.swal({
                  title: '¿Desea mantener el precio de oferta?',
                  html: `
                    <div style="text-align:left;">
                      <p style="margin-bottom:10px;">
                        El carrito contiene uno o más productos con precio de oferta.
                      </p>
                      <div style="padding:10px 12px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb;color:#92400e;">
                        <strong>Importante:</strong> el precio de oferta solo se respeta pagando con
                        <strong>efectivo</strong> o <strong>transferencia bancaria</strong>.
                      </div>
                    </div>
                  `,
                  icon: 'question',
                  allowOutsideClick: false,
                  allowEscapeKey: false,
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: '<i class="fa-solid fa-tag mr-1"></i> MANTENER OFERTA',
                  cancelButtonText: '<i class="fa-solid fa-dollar-sign mr-1"></i> USAR PRECIO NORMAL'
                }).then((result) => {
                  if (result.isConfirmed) {
                    const pagosNoPermitidos = obtener_pagos_no_permitidos_oferta();

                    if(pagosNoPermitidos.length > 0){
                      const nombresPagos = pagosNoPermitidos
                        .map(function(forma){ return forma.nombre; })
                        .join(', ');

                      Vue.swal({
                        icon: 'warning',
                        title: 'Forma de pago no válida para la oferta',
                        html: `
                          <div style="text-align:left;">
                            <p>Actualmente tiene seleccionado o con monto ingresado:</p>
                            <p style="font-weight:700;color:#b91c1c;">${nombresPagos}</p>
                            <p>
                              Para mantener el precio de oferta debe pagar únicamente con
                              <strong>efectivo</strong> y/o <strong>transferencia bancaria</strong>.
                            </p>
                          </div>
                        `,
                        showCancelButton: true,
                        showDenyButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonColor: '#15803d',
                        denyButtonColor: '#2563eb',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa-solid fa-money-bill-wave mr-1"></i> CAMBIAR A EFECTIVO',
                        denyButtonText: '<i class="fa-solid fa-dollar-sign mr-1"></i> USAR PRECIO NORMAL',
                        cancelButtonText: '<i class="fa-solid fa-arrow-left mr-1"></i> VOLVER'
                      }).then(function(decisionPago){
                        if(decisionPago.isConfirmed){
                          mantener_oferta_y_continuar(elementoOferta, true);
                          return;
                        }

                        if(decisionPago.isDenied){
                          aplicar_precio_normal_y_continuar(elementoOferta);
                          return;
                        }

                        habilitar_boton_procesar_dte();
                      });

                      return;
                    }

                    const validacionPago = pago_oferta_valido();

                    if(!validacionPago.tienePagoPermitido){
                      Vue.swal({
                        icon: 'warning',
                        title: 'Seleccione una forma de pago válida',
                        text: 'Para mantener la oferta debe ingresar un monto en efectivo o transferencia bancaria.',
                        confirmButtonText: 'ENTENDIDO'
                      });
                      habilitar_boton_procesar_dte();
                      return;
                    }

                    mantener_oferta_y_continuar(elementoOferta);
                  }else{
                    aplicar_precio_normal_y_continuar(elementoOferta);
                  }

                });
          return false;
  }else{
    console.log('no tiene ofertas');
    
    //Verificar si seleccionó documento de venta
    let bb=document.getElementById("radio_boleta");
    let ff=document.getElementById("radio_factura");
  
    let nombre_cotizacion="";

    if(!bb.checked && !ff.checked){
        Vue.swal({
            text: 'Seleccione un documento de Venta',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
      habilitar_boton_procesar_dte();
      return false;
    }

    //Verificar si hay algo en el carrito    (campo oculto en la vista ventas_carrito)
    var items_en_carrito=document.getElementById("items_carrito").value;

    if(items_en_carrito==0)
    {
      Vue.swal({
        text: 'Carrito de Compras Vacio...',
        position: 'top-end',
        icon: 'error',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      habilitar_boton_procesar_dte();
      return false;
    }



    //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};
    let docu = $('input[name="tipo_documento"]:checked').val().trim();
    let idc=document.getElementById("id_cliente").value;


    if(idc==0 && docu=='factura') // Solo exige cliente si es factura
    {
      Vue.swal({
        text: 'No ha seleccionado cliente...',
        position: 'top-end',
        icon: 'error',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      habilitar_boton_procesar_dte();
      return false;
    }

    var venta="nada";

    if(docu!="cotizacion")
    {
      venta=$('input[name="tipo_venta"]:checked').val().trim();
    }

    if(venta=="contado")
    {
        //Forma de pago, monto y referencia
        //formita-1, monto-1, referencia-1

        let paguitos=$('input[name="forma_pago"]:checked');
        //Si no selecciona ningún check??
        let seleccionados=paguitos.length;
        if(seleccionados==0)
        {
            Vue.swal({
                text: 'Debe seleccionar al menos una forma de pago',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
            habilitar_boton_procesar_dte();
      return false;
        }

        //inicio paguitos
        
        var id_forma_pago=[];
        var monto_forma_pago=[];
        var referencia_forma_pago=[];
        var m="";
        var r="";
        var monto="";
        var referencia_pago="";
        let error=false;
        paguitos.each(function() {
            //Si los checks seleccionados no tienen monto ni referencia??
            var texto_seleccionado=$(this)[0].nextSibling.nodeValue.toUpperCase().trim();
            m="monto-"+$(this).val();
            monto=document.getElementById(m).value;
            //console.log("Checkbox " + $(this).prop("id") +  " (" + $(this).val() + ") Seleccionado MONTO-m: "+m+" TEXTO: "+texto_seleccionado+" monto-valor: "+Number(monto));
            if(isNaN(monto) || Number(monto)<=0)
            {
                Vue.swal({
                    text: texto_seleccionado+": Monto vacio, cero o no es un número válido.",
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    });
                error=true;
                habilitar_boton_procesar_dte();
      return false; //sale del bucle jquery paguitos.each
            }

            r="referencia-"+$(this).val();
            referencia_pago=document.getElementById(r).value.toUpperCase().trim();
            if(referencia_pago.length==0)
            {
                Vue.swal({
                    text: texto_seleccionado+": Referencia vacia, cero o no es un número válido.",
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    });
                error=true;
                habilitar_boton_procesar_dte();
      return false; //sale del bucle jquery paguitos.each
            }

            id_forma_pago.push($(this).val());
            monto_forma_pago.push(monto);
            referencia_forma_pago.push(referencia_pago);
        }); // fin de paguitos

        if(error){
            return false; //sale de la funcion...
        }

        // Validar que el total pagado coincida con el total del carrito
        // Esto aplica para todas las ventas, incluyendo las que usan giftcard
        var error_pago=total_pagado();

        if(error_pago=="SI")
        {
            mensaje_error="Total a Pagar no coincide con Total Venta";
            Vue.swal({
                title:"ATENCIÓN!!!",
                text: mensaje_error,
                position: 'center',
                icon: 'error',
                showConfirmButton: false,
                timer: 5000,
                });
            habilitar_boton_procesar_dte();
      return false;
        }

    //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};

    }else{ //Venta al crédito o cotización, no hay formas de pago.
        if(venta=="credito" || venta=="delivery")
        {
            //var parametros={idcliente:idc,docu:docu,venta:venta};
        }else{ //Si no es crédito ni contado, entonces es cotización porque así lo programé... ja ja ja
            var d_ex=document.getElementById("dias_expira").value;
            //var parametros={idcliente:idc,docu:docu,dias_expira:d_ex};
        }
    }

    let descuento = $('#formita-descuento').val();
    //Verificamos si esta en oferta
    let o = $('#es_oferta').val();
    let confirmado = $('#confirmado').val();
    let numero_giftcard = $('#numero_giftcard_cargado').val();

    /*
     * Última barrera antes de enviar la venta. Aunque algún evento reactive
     * una tarjeta u otro medio, una oferta nunca se procesa con ellos.
     */
    if(confirmado === 'ofertado'){
      const validacionFinalOferta = pago_oferta_valido();

      if(!validacionFinalOferta.valido){
        const nombresNoPermitidos = validacionFinalOferta.noPermitidos
          .map(function(forma){ return forma.nombre; })
          .join(', ');

        Vue.swal({
          icon: 'error',
          title: 'No se puede procesar la oferta',
          html: nombresNoPermitidos
            ? 'Elimine estas formas de pago: <strong>' + nombresNoPermitidos + '</strong>.<br>Solo se permite efectivo y/o transferencia.'
            : 'Debe ingresar el pago en <strong>efectivo</strong> y/o <strong>transferencia bancaria</strong>.',
          confirmButtonText: 'REVISAR PAGO'
        });

        habilitar_boton_procesar_dte();
        return false;
      }
    }

    let url="{{url('ventas/generarxml')}}";
    if((document.getElementById('vendedor_id')) !== null){
      var parametros={docu:docu,
                    idcliente:idc,
                    fmapago:venta,
                    cliente_id: document.getElementById('cliente_id').value,
                    vendedor_id: document.getElementById('vendedor_id').value,
                    ref1:JSON.stringify(ref1),
                    ref2:JSON.stringify(ref2),
                    ref3:JSON.stringify(ref3),
                    dcto: descuento,
                    oferta:o,
                    confirmado: confirmado,
                    numero_giftcard: numero_giftcard
                };
    }else{
      var parametros={docu:docu,
                    idcliente:idc,
                    fmapago:venta,
                    ref1:JSON.stringify(ref1),
                    ref2:JSON.stringify(ref2),
                    ref3:JSON.stringify(ref3),
                    dcto: descuento,
                    oferta:o,
                    confirmado: confirmado,
                    numero_giftcard: numero_giftcard
                };
    }

   
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
        type: 'POST',
        url: url,
        data: parametros,
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        beforeSend: function(){
          bloquear_boton_procesar('GENERANDO XML...');
          $("#mensajes").html("<b>Generando XML...</b>");
          $("#envio-txt").html(
            '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generando XML...'
          );
        },
        success: function(rs){
          const rpta = normalizar_respuesta_json(rs);

          console.log('Respuesta generar_xml:', rpta);

          if(rpta.estado === 'SESION_EXPIRADA'){
            manejar_error_sesion_dte(null, rpta);
            return;
          }

          if(rpta.estado === 'GENERADO'){
            const proceso = rpta.proceso_dte || {
              xml: rpta.xml || null,
              tipo_dte: rpta.tipo_dte || null,
              tipo_dte_nombre: rpta.tipo_dte_nombre || docu,
              folio_dte: rpta.folio_dte || null,
              idcliente: rpta.idcliente || idc
            };

            guardar_proceso_dte(proceso);

            $("#mensajes").html("Generando XML... <b>Listo</b>");
            $("#envio-txt").html(
              '<i class="fa-solid fa-circle-check text-success mr-1"></i>' +
              'XML generado correctamente.'
            );

            documento_procesado =
              rpta.mensaje ||
              ((String(docu).toUpperCase()) +
              (rpta.folio_dte ? ' N° ' + rpta.folio_dte : ''));

            document.getElementById("dcmto_en_envio").value = documento_procesado;

            abrir_procesar_envio();
            $('#btn-enviarsii').prop('disabled', false);

            /*
             * El carrito ya generó un DTE. Se mantiene protegido contra una
             * segunda generación, pero se informa claramente el motivo.
             */
            marcar_dte_pendiente_envio();
            return;
          }

          if(rpta.estado === 'GIFTCARD_OK'){
            limpiar_proceso_dte();

            $("#mensajes").html("<b>Pago con giftcard procesado.</b>");
            $("#envio-txt").html("<small>"+(rpta.mensaje || '')+"</small>");

            const carrito_id = $('#carrito_id').val();
            const cliente_id = $('#cliente_id').val();

            Vue.swal({
              icon: 'success',
              title: 'Giftcard procesada',
              html:
                (rpta.mensaje || 'Pago procesado correctamente.') +
                '<br><br><b>No se generó XML y no se enviará al SII.</b>',
              allowOutsideClick: false,
              allowEscapeKey: false
            });

            limpiar_cliente();

            if(carrito_id && cliente_id){
              borrar_carrito_transferido(cliente_id);
            }

            borrar_carrito('actual');

            if(document.getElementById("monto_efectivo")){
              document.getElementById("monto_efectivo").value = '';
            }

            reiniciar_descuento();
            limpiar_sesion();

            documento_procesado = "Giftcard";
            document.getElementById("dcmto_en_envio").value =
              "Giftcard sin XML/SII";

            habilitar_boton_procesar_dte();
            return;
          }

          habilitar_boton_procesar_dte();
          mostrar_error_dte(rpta, rpta.estado || 'Error al generar XML');
        },
        error: function(xhr){
          console.error('Error generar_xml:', xhr);

          if(manejar_error_sesion_dte(xhr)){
            return;
          }

          habilitar_boton_procesar_dte();

          const rpta = normalizar_respuesta_json(
            xhr.responseJSON || xhr.responseText
          );

          $("#envio-txt").html(
            '<span class="text-danger">' +
            (rpta.mensaje || 'No fue posible generar el XML.') +
            '</span>'
          );

          mostrar_error_dte(rpta, 'Error al generar XML');
        }
    });
  }
}

function agregar_xpress_al_detalle_vale(
    idRepuesto,
    numeroVale,
    cantidad
) {
    $.ajax({
        type: 'POST',
        url: '/ventas/agregar_repuesto_valemercaderia',

        data: {
            idrep: idRepuesto,
            num_boucher: numeroVale,
            cantidad: cantidad,

            /*
             * Se utiliza local 1 para mantener compatibilidad con la
             * ruta actual. El backend debe reconocer que es Express
             * y no exigir ni descontar stock físico.
             */
            origen: 1,
            es_xpress: 1
        },

        beforeSend: function () {
            Vue.swal({
                title: 'Agregando al vale',
                text: 'Espere mientras se actualiza el detalle.',
                allowOutsideClick: false,
                didOpen: function () {
                    Vue.swal.showLoading();
                }
            });
        },

        success: function (respuesta) {
            Vue.swal.close();

            if (respuesta === 'error') {
                Vue.swal({
                    icon: 'error',
                    title: 'No se pudo agregar',
                    text:
                        'El backend intentó validar stock para el ' +
                        'Repuesto Express.'
                });

                return;
            }

            if (respuesta === 'viejo') {
                Vue.swal({
                    icon: 'error',
                    text:
                        'El backend rechazó el precio del ' +
                        'Repuesto Express.'
                });

                return;
            }

            $('#modal-repuesto-xpress-vale').modal('hide');

            /*
             * La ruta actual ya devuelve nuevamente la vista del
             * detalle del vale.
             */
            $('#vale_mercaderia_detalle').html(respuesta);

            $('.modal-backdrop').remove();

            $('body')
                .removeClass('modal-open')
                .css('padding-right', '');

            limpiar_modal_xpress_vale();

            Vue.swal({
                icon: 'success',
                title: 'Repuesto Express agregado',
                text:
                    'El complemento fue incorporado al detalle del vale.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
            buscar_devolucion();
        },

        error: function (error) {
            Vue.swal.close();

            Vue.swal({
                icon: 'error',
                text:
                    error.responseJSON?.mensaje ||
                    error.responseText ||
                    'No fue posible agregar el Repuesto Express al vale.'
            });
        }
    });
}

function limpiar_modal_xpress_vale() {
    datosValeXpress.numeroVale = null;
    datosValeXpress.saldoFaltante = 0;

    $('#vale-xpress-codigo').val('');
    $('#vale-xpress-descripcion').val('');
    $('#vale-xpress-cantidad').val(1);
    $('#vale-xpress-precio').val('').removeData('valor');
    $('#vale-xpress-tipo-boleta').prop('checked', true);
    $('#resumen-tipo-documento').text('Boleta');
    $('#resumen-total-xpress').text('$ 0');
    $('#formas_pago').empty();
    $('#error-formas-pago').hide();
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
  

  function imprimir_resultado(numeroVale, valorVale, totalProductos) {
    const valor = Number(valorVale) || 0;
    const total = Number(totalProductos) || 0;
    const exceso = Math.max(0, total - valor);
    const tieneExceso = exceso > 0;

    const formatoPesos = new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 0
    });

    Vue.swal({
        title: tieneExceso
            ? 'El total supera el valor del vale'
            : '¿Procesar entrega?',
        html: tieneExceso
            ? `
                <div class="text-left">
                    <p>Los productos agregados superan el valor disponible del vale.</p>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Valor del vale:</span>
                            <strong>$ ${formatoPesos.format(valor)}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total productos:</span>
                            <strong>$ ${formatoPesos.format(total)}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Exceso:</span>
                            <strong>$ ${formatoPesos.format(exceso)}</strong>
                        </div>
                    </div>
                    <p class="mt-3 mb-0">
                        Si continúa, se descontará el stock, el vale quedará procesado
                        y su saldo se registrará en $0.
                    </p>
                </div>
            `
            : 'Se confirmará la entrega de los productos asociados al vale.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: tieneExceso ? '#dc3545' : '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: tieneExceso
            ? 'Procesar igualmente'
            : 'Procesar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((resultado) => {

        if (!resultado.isConfirmed) {
            return;
        }

        $.ajax({
            type: 'post',
            url: '/ventas/imprimir_vale_resultado',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                numero_vale: numeroVale
            },
            beforeSend: function () {
                Vue.swal({
                    title: 'Procesando vale',
                    text: 'Espere mientras se genera el comprobante...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        Vue.swal.showLoading();
                    }
                });
            },
            success: function (resp) {
                Vue.swal.close();

                let respuesta = resp;

                if (typeof resp === 'string') {
                    try {
                        respuesta = JSON.parse(resp);
                    } catch (error) {
                        Vue.swal({
                            icon: 'error',
                            title: 'Respuesta inválida',
                            text: resp
                        });
                        return;
                    }
                }

                if (respuesta.estado === 'OK') {
                    const excesoProcesado = Number(respuesta.exceso || 0);

                    Vue.swal({
                        icon: excesoProcesado > 0 ? 'warning' : 'success',
                        title: excesoProcesado > 0
                            ? 'Vale procesado con excedente'
                            : 'Vale procesado',
                        html: excesoProcesado > 0
                            ? 'La entrega fue procesada y el stock fue descontado.<br><br>' +
                              'Se preparará un <strong>Repuesto Express</strong> por <strong>$' +
                              formatoPesos.format(excesoProcesado) +
                              '</strong> para emitir la boleta o factura desde el flujo normal de venta.'
                            : 'La entrega fue procesada correctamente y el stock fue descontado.',
                        confirmButtonText: excesoProcesado > 0
                            ? 'PREPARAR COBRO'
                            : 'ACEPTAR',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(function () {
                        const config = 'location=yes,height=570,width=720,scrollbars=yes,status=yes';
                        const ventana = window.open(respuesta.mensaje, '_blank', config);

                        if (ventana) {
                            ventana.focus();
                        } else {
                            Vue.swal({
                                icon: 'info',
                                title: 'Ventana bloqueada',
                                text: 'El vale fue procesado, pero el navegador bloqueó la apertura del comprobante.'
                            });
                        }

                        buscar_devolucion();

                        if (excesoProcesado > 0 && respuesta.exceso_xpress) {
                            window.dispatchEvent(new CustomEvent('vale:exceso', {
                                detail: respuesta.exceso_xpress
                            }));
                        }
                    });
                } else {
                    Vue.swal({
                        icon: 'error',
                        title: respuesta.estado || 'Error',
                        text: respuesta.mensaje || 'No fue posible procesar el vale.'
                    });
                }
            },
            error: function (error) {
                Vue.swal.close();

                Vue.swal({
                    icon: 'error',
                    title: 'Error',
                    text: error.responseJSON?.mensaje || error.responseText ||
                        'No fue posible procesar el vale.'
                });
            }
        });
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
    function dame_formas_pago()
    {
        const url = '{{url("ventas/dame_forma_pago")}}';

        $('#error-formas-pago').hide();
        $('#btn-guardar-xpress-vale').prop('disabled', true);

        $.ajax({
            type: 'GET',
            url: url,

            beforeSend: function () {
                $('#formas_pago').html(
                    '<div class="text-center text-muted py-3">' +
                        '<span class="spinner-border spinner-border-sm mr-2"></span>' +
                        'Cargando formas de pago...' +
                    '</div>'
                );
            },

            success: function (formas) {
                if (!formas || String(formas).trim() === '') {
                    $('#formas_pago').empty();
                    $('#error-formas-pago')
                        .text('No existen formas de pago disponibles.')
                        .show();
                    return;
                }

                $('#formas_pago').html(formas);
                $('#error-formas-pago').hide();
                $('#btn-guardar-xpress-vale').prop('disabled', false);
            },

            error: function (error) {
                console.error(error.responseText);

                $('#formas_pago').empty();
                $('#error-formas-pago')
                    .text('No fue posible cargar las formas de pago.')
                    .show();

                $('#btn-guardar-xpress-vale').prop('disabled', true);
            }
        });
    }

    function activar_forma_pago(id){
        let marca=document.getElementById("formita-"+id).checked;
        document.getElementById("monto-"+id).disabled=!marca;
        document.getElementById("referencia-"+id).disabled=!marca;
      }
</script>
@endsection
@section('contenido_titulo_pagina')
    <h4 class="titulazo">Vale por mercadería</h4>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid vm-page">
    <div class="row">
        <div class="col-lg-6">
            <div class="vm-card">
                <div class="vm-card-header">
                    <div>
                        <h5 class="vm-card-title"><i class="fas fa-gift mr-1"></i> Crear vale por mercadería</h5>
                        <p class="vm-card-subtitle">Registra un vale/giftcard asociado a una boleta o factura.</p>
                    </div>
                    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="Logo" class="vm-logo">
                </div>

                <div class="vm-card-body">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label class="vm-label" for="rut_cliente">RUT cliente</label>
                            <input type="text" class="form-control" id="rut_cliente" name="rut_cliente" placeholder="Ej: 12345678-9">
                        </div>
                        <div class="form-group col-md-7">
                            <label class="vm-label" for="nombre_cliente">Nombre cliente</label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" placeholder="Nombre o razón social">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label class="vm-label" for="telefono_cliente">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" placeholder="Opcional">
                        </div>
                        <div class="form-group col-md-7">
                            <label class="vm-label" for="descripcion">Descripción del repuesto / motivo</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Detalle del vale">
                        </div>
                    </div>

                    <div class="vm-radio-panel mb-3">
                        <label class="vm-label d-block">Documento asociado</label>
                        <div class="form-row align-items-end">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" name="exampleRadios" id="exampleRadios1" value="bo" checked>
                                    <label class="custom-control-label" for="exampleRadios1">Boleta</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" name="exampleRadios" id="exampleRadios2" value="fa">
                                    <label class="custom-control-label" for="exampleRadios2">Factura</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="vm-label" for="numero_documento">N° documento</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" placeholder="N° de boleta o factura" onKeyPress="return soloNumeros(event)">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="vm-label" for="valor">Valor del vale</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="text" class="form-control" id="valor" name="valor" placeholder="0" onKeyPress="return soloNumeros(event)">
                        </div>
                        <div class="vm-help">Este monto será el saldo inicial disponible del vale.</div>
                    </div>

                    <div class="vm-actions">
                        <button class="btn btn-warning btn-sm" onclick="imprimir_boucher()">
                            <i class="fas fa-print mr-1"></i> Guardar e imprimir
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="ver_historial_vales()" data-toggle="modal" data-target="#exampleModal_">
                            <i class="fas fa-history mr-1"></i> Historial
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="vm-card">
                <div class="vm-card-header secondary">
                    <div>
                        <h5 class="vm-card-title"><i class="fas fa-search mr-1"></i> Cargar vale existente</h5>
                        <p class="vm-card-subtitle">Busca el vale y agrega productos para entregar mercadería.</p>
                    </div>
                    <span class="vm-badge-soft">Giftcard / Vale</span>
                </div>

                <div class="vm-card-body">
                    <div id="busqueda_devolucion" class="vm-search-box">
                        <label class="vm-label" for="numero_vale">N° vale de mercadería</label>
                        <div class="input-group">
                            <input type="text" name="numero_vale" id="numero_vale" class="form-control" placeholder="Ingrese número de vale" onKeyPress="return soloNumeros(event)">
                            <div class="input-group-append">
                                <button class="btn btn-success" onclick="buscar_devolucion()">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="vm-help">Al cargar el vale podrás seleccionar repuestos y origen de stock.</div>
                    </div>

                    <div id="total_devolucion" class="vm-result-box"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal historial -->
<div class="modal fade bd-example-modal-xl" id="exampleModal_" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title mb-0" id="exampleModalLabel"><i class="fas fa-list mr-1"></i> Historial de vales de mercadería</h5>
                    <small class="text-white-50">Consulta, imprime o elimina vales registrados.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div class="radio_button_options">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="custom-control-input" onclick="ver_historial_vales()" type="radio" name="flexRadioDefault" id="vales_historial" value="" checked>
                        <label class="custom-control-label" for="vales_historial">Todos</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="custom-control-input" onchange="filtrar(this)" type="radio" name="flexRadioDefault" id="pendientes" value="0">
                        <label class="custom-control-label" for="pendientes">Procesados</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="custom-control-input" onchange="filtrar(this)" type="radio" name="flexRadioDefault" id="procesados" value="1">
                        <label class="custom-control-label" for="procesados">Pendientes</label>
                    </div>
                </div>

                <div class="vm-table-wrap table-responsive bg-white">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">N° Boucher</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Tipo Doc</th>
                                <th scope="col">N° Documento</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="text-center">Imprimir</th>
                                <th scope="col" class="text-center">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_vales"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade"
     id="modal-repuesto-xpress-vale"
     tabindex="-1"
     role="dialog"
     aria-labelledby="modalRepuestoXpressValeLabel"
     aria-hidden="true"
     data-backdrop="static"
     data-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-lg"
         role="document">

        <div class="modal-content border-0 shadow-lg"
             style="border-radius: 16px; overflow: hidden;">

            {{-- Encabezado --}}
            <div class="modal-header border-0 px-4 py-3"
                 style="background: linear-gradient(135deg, #ffc107, #ffda6a);">

                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center bg-white shadow-sm mr-3"
                         style="width: 48px; height: 48px; border-radius: 14px;">
                        <i class="fas fa-bolt text-warning fa-lg"></i>
                    </div>

                    <div>
                        <h5 class="modal-title font-weight-bold mb-1"
                            id="modalRepuestoXpressValeLabel">
                            Agregar repuesto Express
                        </h5>

                        <small class="text-dark">
                            Complete el saldo pendiente y seleccione el documento de venta.
                        </small>
                    </div>
                </div>

                <button type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Cuerpo --}}
            <div class="modal-body p-4"
                 style="background: #f8fafc;">

                <div class="alert alert-info border-0 shadow-sm mb-4"
                     style="border-radius: 12px;">
                    <div class="d-flex">
                        <i class="fas fa-info-circle mr-3 mt-1"></i>

                        <div>
                            <strong>Registro Express</strong>

                            <div class="small mt-1">
                                Este registro permitirá completar el saldo restante
                                del vale. No descuenta stock ni corresponde a un
                                producto físico del inventario.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    {{-- Información del registro --}}
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="card border-0 shadow-sm h-100"
                             style="border-radius: 14px;">

                            <div class="card-body p-4">

                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-box-open text-warning mr-2"></i>
                                    Información del registro
                                </h6>

                                <div class="form-group">
                                    <label for="vale-xpress-codigo"
                                           class="font-weight-bold">
                                        Código
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-barcode text-muted"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                               id="vale-xpress-codigo"
                                               class="form-control bg-light"
                                               value="VALE-EXPRESS"
                                               readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="vale-xpress-descripcion"
                                           class="font-weight-bold">
                                        Descripción
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           id="vale-xpress-descripcion"
                                           class="form-control"
                                           maxlength="255"
                                           placeholder="Ej.: Diferencia vale de mercadería">

                                    <small class="form-text text-muted">
                                        Esta descripción aparecerá en el documento.
                                    </small>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="vale-xpress-cantidad"
                                               class="font-weight-bold">
                                            Cantidad
                                        </label>

                                        <input type="number"
                                               id="vale-xpress-cantidad"
                                               class="form-control bg-light text-center"
                                               value="1"
                                               min="1"
                                               readonly>
                                    </div>

                                    <div class="form-group col-md-8">
                                        <label for="vale-xpress-precio"
                                               class="font-weight-bold">
                                            Saldo por completar
                                        </label>

                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white">
                                                    $
                                                </span>
                                            </div>

                                            <input type="text"
                                                   id="vale-xpress-precio"
                                                   class="form-control bg-white font-weight-bold text-success"
                                                   readonly>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Documento y pago --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100"
                             style="border-radius: 14px;">

                            <div class="card-body p-4">

                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
                                    Documento y forma de pago
                                </h6>

                                {{-- Tipo de documento --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-block">
                                        Tipo de documento
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="row">

                                        <div class="col-6">
                                            <label class="documento-option w-100 mb-0"
                                                   for="vale-xpress-tipo-boleta">

                                                <input type="radio"
                                                       name="vale_xpress_tipo_documento"
                                                       id="vale-xpress-tipo-boleta"
                                                       value="bo"
                                                       checked
                                                       class="d-none">

                                                <div class="documento-card text-center border p-3"
                                                     style="border-radius: 12px; cursor: pointer;">
                                                    <i class="fas fa-receipt fa-2x mb-2"></i>

                                                    <div class="font-weight-bold">
                                                        Boleta
                                                    </div>

                                                    <small class="text-muted">
                                                        Consumidor final
                                                    </small>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="col-6">
                                            <label class="documento-option w-100 mb-0"
                                                   for="vale-xpress-tipo-factura">

                                                <input type="radio"
                                                       name="vale_xpress_tipo_documento"
                                                       id="vale-xpress-tipo-factura"
                                                       value="fa"
                                                       class="d-none">

                                                <div class="documento-card text-center border p-3"
                                                     style="border-radius: 12px; cursor: pointer;">
                                                    <i class="fas fa-file-invoice fa-2x mb-2"></i>

                                                    <div class="font-weight-bold">
                                                        Factura
                                                    </div>

                                                    <small class="text-muted">
                                                        Cliente empresa
                                                    </small>
                                                </div>
                                            </label>
                                        </div>

                                    </div>
                                </div>
                                {{-- Forma de pago --}}
                                <div class="form-group mt-4 mb-0">
                                    <label class="font-weight-bold d-block">
                                        Forma de pago
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div id="formas_pago"
                                         class="formas-pago-box">
                                        <div class="text-center text-muted py-3">
                                            Seleccione el documento para cargar las formas de pago.
                                        </div>
                                    </div>

                                    <div id="error-formas-pago"
                                         class="alert alert-danger py-2 mt-2 mb-0"
                                         style="display:none;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        No fue posible cargar las formas de pago.
                                    </div>
                                </div>

                                {{-- Resumen --}}
                                <div class="border rounded p-3 mt-4 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">
                                            Documento
                                        </span>

                                        <strong id="resumen-tipo-documento">
                                            Boleta
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">
                                            Total a registrar
                                        </span>

                                        <strong class="text-success"
                                                id="resumen-total-xpress">
                                            $ 0
                                        </strong>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Pie --}}
            <div class="modal-footer bg-white border-0 px-4 py-3">

                <button type="button"
                        class="btn btn-outline-secondary px-4"
                        data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-success px-4"
                        id="btn-guardar-xpress-vale"
                        onclick="guardar_repuesto_xpress_vale()">
                    <i class="fas fa-check-circle mr-1"></i>
                    Crear y agregar al vale
                </button>

            </div>

        </div>
    </div>
</div>
@endsection