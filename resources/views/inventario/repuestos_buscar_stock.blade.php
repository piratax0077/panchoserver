@extends('plantillas.app')
@section('titulo','Stock de repuestos')

@section('contenido_titulo_pagina')

    <div class="stock-page-heading">
        <div>
            <span class="stock-page-eyebrow">Inventario</span>
            <h4 class="titulazo mb-1">Stock de repuestos</h4>
            <p class="stock-page-subtitle mb-0">Consulta, controla y actualiza el stock disponible en cada ubicación.</p>
        </div>
        <div class="stock-page-icon"><i class="fas fa-boxes"></i></div>
    </div>

  
    
  
 @endsection

 @section('javascript')
  <script>

      window.onload = function(){
          
          $('#campo_modificar').css('display','none');
          $('#campo_ubicacion').css('display','none');
          $('#mensaje_stock').css('display','none');
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

            document.getElementById('codigo_repuesto').focus();
    }

      function buscar_stock_repuesto(silencioso = false){

          let codigo_repuesto = document.getElementById('codigo_repuesto').value;
          let opt = $('input:radio[name=flexRadioDefault]:checked').val();
          let data = {codigo_repuesto: codigo_repuesto, option: opt};

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
              url:'/repuesto/damestockrepuesto',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append(silencioso ? 'Actualizando ...' : 'Buscando ...');
                if(!silencioso){
                  espere('Buscando ...');
                }
              },
              success: function(resp){
                
                if(!silencioso){ Vue.swal.close(); }
                if(resp[0] === "error"){
                    Vue.swal({
                      title:'Error!',
                      text:resp[1],
                      icon:'error'
                    });
                }
                
                else{
                  console.log(resp);
                let responsables = resp[2];
                $('#select_stock').empty();
                $('#select_stock').append(`<option value="0">Seleccione</option>`);
                responsables.forEach(element => {
                  $('#select_stock').append(`<option value="`+element.id+`">`+element.name+`</option>`);
                });

                $('#select_stock_dos').empty();
                $('#select_stock_dos').append(`<option value="0">Seleccione</option>`);
                responsables.forEach(element => {
                  $('#select_stock_dos').append(`<option value="`+element.id+`">`+element.name+`</option>`);
                });

                $('#select_stock_tres').empty();
                $('#select_stock_tres').append(`<option value="0">Seleccione</option>`);
                responsables.forEach(element => {
                  $('#select_stock_tres').append(`<option value="`+element.id+`">`+element.name+`</option>`);
                });

                
                $('#mensajes').empty();
                $('#mensajes').append('Listo');
                $('#tbody_resultados').empty();

                resp[0].forEach(element => {
                  renderizar_resultado_stock(element, resp[1]);
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


      function refrescar_repuesto_actual(){
        const codigo = $('#codigo_repuesto').val();
        if(codigo && String(codigo).trim() !== ''){
          buscar_stock_repuesto(true);
        }
      }

      function valor_seguro(valor, reemplazo = '--'){
        return (valor === null || valor === undefined || valor === '') ? reemplazo : valor;
      }

      function escapar_atributo(valor){
        return String(valor_seguro(valor, ''))
          .replace(/\\/g, '\\\\')
          .replace(/'/g, "\\'")
          .replace(/\"/g, '&quot;')
          .replace(/\r?\n/g, ' ');
      }

      function clase_stock(stock){
        return Number(stock || 0) < 10 ? 'stock-value stock-value-low' : 'stock-value';
      }

      function nombre_local(indice, element){
        if(indice === 1){
          if(Number(element.local_id) === 3) return 'Tienda';
          return 'Bodega';
        }

        if(indice === 2){
          if(Number(element.local_id_dos) === 3) return 'Tienda';
          if(Number(element.local_id_dos) === 1) return 'Bodega';
          return 'Tienda';
        }

        return 'Casa Matriz';
      }

      function tarjeta_ubicacion(element, indice){
        let sufijo = indice === 1 ? '' : (indice === 2 ? '_dos' : '_tres');
        let stockCampo = indice === 1 ? 'stock_actual' : (indice === 2 ? 'stock_actual_dos' : 'stock_actual_tres');
        let ubicacionCampo = indice === 1 ? 'ubicacion' : (indice === 2 ? 'ubicacion_dos' : 'ubicacion_tres');
        let responsableCampo = indice === 1 ? 'nombre_responsable' : (indice === 2 ? 'nombre_responsable_dos' : 'nombre_responsable_tres');
        let stockId = indice === 1 ? 'stock-' : (indice === 2 ? 'stock_dos-' : 'stock_tres-');
        let ubicacionId = indice === 1 ? 'ubicacion-' : (indice === 2 ? 'ubicacion_dos-' : 'ubicacion_tres-');
        let funcionStock = indice === 1 ? 'abrir_campos' : (indice === 2 ? 'abrir_campos_dos' : 'abrir_campos_tres');
        let modalStock = indice === 1 ? '#modal_modificar_stock' : (indice === 2 ? '#modal_modificar_stock_dos' : '#modal_modificar_stock_tres');
        let funcionUbicacion = indice === 1 ? 'modificar_ubicacion' : (indice === 2 ? 'modificar_ubicacion_dos' : 'modificar_ubicacion_tres');
        let modalUbicacion = indice === 1 ? '#modal_modificar_ubicacion' : (indice === 2 ? '#modal_modificar_ubicacion_dos' : '#modal_modificar_ubicacion_tres');
        let tema = indice === 1 ? 'blue' : (indice === 2 ? 'green' : 'purple');
        let icono = indice === 1 ? 'fa-warehouse' : (indice === 2 ? 'fa-store' : 'fa-building');
        let stock = Number(element[stockCampo] || 0);
        let ubicacion = valor_seguro(element[ubicacionCampo]);
        let responsable = valor_seguro(element[responsableCampo]);
        let bajo = stock < 10;

        return `
          <article class="stock-location-card stock-location-${tema}">
            <div class="stock-location-header">
              <div class="stock-location-title">
                <span class="stock-location-icon"><i class="fas ${icono}"></i></span>
                <div>
                  <h6>${nombre_local(indice, element)}</h6>
                  <small>Ubicación ${indice}</small>
                </div>
              </div>
              <i class="fas fa-cube stock-location-cube"></i>
            </div>

            <div class="stock-location-body">
              <div class="stock-location-field">
                <div>
                  <span class="stock-field-label">Stock actual</span>
                  <div class="stock-number-row">
                    <span class="${clase_stock(stock)}" id="${stockId}${element.id}">${stock}</span>
                    <small>unidades</small>
                    ${bajo ? '<span class="stock-low-badge">Stock bajo</span>' : ''}
                  </div>
                </div>
                <button type="button" class="stock-edit-btn" title="Editar stock" data-toggle="modal"
                  onclick="${funcionStock}(${element.id},'${escapar_atributo(element.descripcion)}','${escapar_atributo(element.marcarepuesto)}','${escapar_atributo(element.nombre_pais)}','${escapar_atributo(element[responsableCampo])}',${stock})"
                  data-target="${modalStock}"><i class="fas fa-pencil-alt"></i></button>
              </div>

              <div class="stock-location-field">
                <div class="stock-field-content">
                  <span class="stock-field-label">Ubicación</span>
                  <strong id="${ubicacionId}${element.id}">${ubicacion}</strong>
                </div>
                <button type="button" class="stock-edit-btn" title="Editar ubicación" data-toggle="modal"
                  onclick="${funcionUbicacion}(${element.id},'${escapar_atributo(element.descripcion)}','${escapar_atributo(element.marcarepuesto)}','${escapar_atributo(element[ubicacionCampo])}')"
                  data-target="${modalUbicacion}"><i class="fas fa-pencil-alt"></i></button>
              </div>

              <div class="stock-responsible-box">
                <div>
                  <span class="stock-field-label">Responsable</span>
                  <strong>${responsable}</strong>
                </div>
                <i class="fas fa-user-check"></i>
              </div>
            </div>

            <div class="stock-location-footer">
              <span class="stock-progress"><span style="width:${Math.min(Math.max(stock * 4, 4), 100)}%"></span></span>
              <strong>${stock} uds.</strong>
            </div>
            <button type="button" class="stock-history-btn" onclick="ver_historial_stock(${element.id},${indice},'${escapar_atributo(nombre_local(indice, element))}')">
              <i class="fas fa-history"></i> Ver historial
            </button>
          </article>`;
      }

      function renderizar_resultado_stock(element, imagen){
        let url = '/repuesto/modificar/' + element.id;
        let totalStock = Number(element.stock_actual || 0) + Number(element.stock_actual_dos || 0) + Number(element.stock_actual_tres || 0);
        let cantidadBajos = [element.stock_actual, element.stock_actual_dos, element.stock_actual_tres]
          .filter(stock => Number(stock || 0) < 10).length;
        let urlFoto = '{{ route('storage.public', [], false) }}';
        if (imagen && imagen.urlfoto) {
          urlFoto += '?path=' + encodeURIComponent(imagen.urlfoto);
        }

        $('#tbody_resultados').append(`
          <section class="stock-result-layout">
            <aside class="stock-product-card">
              <div class="stock-product-status"><i class="fas fa-check-circle"></i> Activo</div>
              <div class="stock-product-image-wrap">
                <img src="${urlFoto}" alt="${valor_seguro(element.descripcion, 'Repuesto')}" class="stock-product-image">
              </div>

              <div class="stock-product-data">
                <div class="stock-product-item">
                  <span class="stock-product-item-icon"><i class="fas fa-barcode"></i></span>
                  <div><small>Código interno</small><a href="${url}" target="_blank">${valor_seguro(element.codigo_interno)}</a></div>
                </div>
                <div class="stock-product-item">
                  <span class="stock-product-item-icon"><i class="far fa-file-alt"></i></span>
                  <div><small>Descripción</small><strong>${valor_seguro(element.descripcion)}</strong></div>
                </div>
                <div class="stock-product-item">
                  <span class="stock-product-item-icon purple"><i class="fas fa-truck"></i></span>
                  <div><small>Código de proveedor</small><strong>${valor_seguro(element.cod_repuesto_proveedor)}</strong></div>
                </div>
                <div class="stock-product-item">
                  <span class="stock-product-item-icon"><i class="fas fa-tag"></i></span>
                  <div><small>Marca</small><strong>${valor_seguro(element.marcarepuesto)}</strong></div>
                </div>
                <div class="stock-product-item">
                  <span class="stock-product-item-icon"><i class="fas fa-globe-americas"></i></span>
                  <div><small>País</small><strong>${valor_seguro(element.nombre_pais)}</strong></div>
                </div>
              </div>

              <div class="stock-product-summary-title">Resumen total <i class="fas fa-info-circle"></i></div>
              <div class="stock-product-summary">
                <div><small>Total stock</small><strong>${totalStock}</strong><span>unidades</span></div>
                <div class="success"><small>Ubicaciones</small><strong>3</strong><span>activas</span></div>
                <div class="danger"><small>Stock bajo</small><strong>${cantidadBajos}</strong><span>ubicaciones</span></div>
              </div>
              <div class="stock-warning-note"><i class="fas fa-exclamation-triangle"></i> Stock bajo: menos de 10 unidades</div>
            </aside>

            <div class="stock-locations-panel">
              <div class="stock-locations-heading">
                <div><h5>Resultado de búsqueda</h5><p>Revisa y edita stock o ubicación desde los íconos de lápiz.</p></div>
                <span><i class="fas fa-check-circle"></i> Búsqueda completada</span>
              </div>
              <div class="stock-location-grid">
                ${tarjeta_ubicacion(element, 1)}
                ${tarjeta_ubicacion(element, 2)}
                ${tarjeta_ubicacion(element, 3)}
              </div>
              <div class="stock-result-help">
                <span><i class="fas fa-info-circle"></i> Edita el stock o la ubicación directamente en cada sección.</span>
                <span>Información actual del inventario</span>
              </div>
            </div>
          </section>`);
      }

      function campos_auditoria_stock(stockActual, sufijo){
        return `
          <div class="stock-audit-current">
            <span>Stock registrado</span>
            <strong>${Number(stockActual || 0)} unidades</strong>
          </div>
          <input type="hidden" id="stock_anterior${sufijo}" value="${Number(stockActual || 0)}">
          <div class="form-group">
            <label for="motivo_stock${sufijo}">Motivo del ajuste <span class="text-danger">*</span></label>
            <textarea class="form-control" id="motivo_stock${sufijo}" rows="3" maxlength="500" placeholder="Ej.: diferencia detectada durante conteo físico"></textarea>
            <small class="form-text text-muted">El motivo quedará registrado en la bitácora y no podrá eliminarse.</small>
          </div>`;
      }

      function abrir_campos(id_repuesto,nombre_repuesto,marca,pais,responsable,stockActual){
        
          if(id_repuesto !== ""){
            $('#modal_modificar_stock_Label').empty();
            $('#modal_modificar_stock_Label').append(nombre_repuesto);
            $('#modal_body').empty();
            $('#modal_body').append(`
            <p>Marca: `+marca+` </p>
            <p>País: `+pais+` </p>
            <p>Responsable: `+responsable+` </p>
            <span>Ingrese nuevo stock</span>
                <div class="form-group">
                    <input type="number" class="form-control" value="${Number(stockActual || 0)}" id="nuevo_stock" min="0" max="65535">
                    <input type="hidden" id="id_repuesto_stock" value="`+id_repuesto+`">
                </div>
                ${campos_auditoria_stock(stockActual, '')}
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
          
      }

      function abrir_campos_dos(id_repuesto,nombre_repuesto,marca,pais,responsable,stockActual){
        if(id_repuesto !== ""){
            $('#modal_modificar_stock_dos_Label').empty();
            $('#modal_modificar_stock_dos_Label').append(nombre_repuesto);
            $('#modal_body_dos').empty();
            $('#modal_body_dos').append(`
            <p>Marca: `+marca+` </p>
            <p>País: `+pais+` </p>
            <p>Responsable: `+responsable+` </p>
            <span>Ingrese nuevo stock</span>
                <div class="form-group">
                    <input type="number" class="form-control" value="${Number(stockActual || 0)}" id="nuevo_stock_dos" min="0" max="65535">
                    <input type="hidden" id="id_repuesto_stock_dos" value="`+id_repuesto+`">
                </div>
                ${campos_auditoria_stock(stockActual, '_dos')}
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function abrir_campos_tres(id_repuesto,nombre_repuesto,marca,pais,responsable,stockActual){
        if(id_repuesto !== ""){
            $('#modal_modificar_stock_tres_Label').empty();
            $('#modal_modificar_stock_tres_Label').append(nombre_repuesto);
            $('#modal_body_tres').empty();
            $('#modal_body_tres').append(`
            <p>Marca: `+marca+` </p>
            <p>País: `+pais+` </p>
            <p>Responsable: `+responsable+` </p>
            <span>Ingrese nuevo stock</span>
                <div class="form-group">
                    <input type="number" class="form-control" value="${Number(stockActual || 0)}" id="nuevo_stock_tres" min="0" max="65535">
                    <input type="hidden" id="id_repuesto_stock_tres" value="`+id_repuesto+`">
                </div>
                ${campos_auditoria_stock(stockActual, '_tres')}
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function solicitar_clave_disminuir_stock(continuar){
        // SweetAlert se muestra sobre un modal Bootstrap abierto. El focus trap
        // de Bootstrap intenta devolver el foco a su modal e impide escribir
        // en el input de la alerta, por eso se desactiva sólo mientras dure.
        const modalActivo = $('.modal.show').first();
        const restaurarFocoModal = function(){
          if(modalActivo.length && modalActivo.hasClass('show')){
            const instanciaModal = modalActivo.data('bs.modal');
            if(instanciaModal && typeof instanciaModal._enforceFocus === 'function'){
              instanciaModal._enforceFocus();
            }
          }
        };

        $(document).off('focusin.bs.modal');

        Vue.swal({
          title: 'Autorización requerida',
          text: 'Ingrese la clave para autorizar la disminución de stock.',
          input: 'password',
          inputPlaceholder: 'Clave de autorización',
          confirmButtonText: '<i class="fas fa-unlock"></i> Autorizar',
          cancelButtonText: 'Cancelar',
          showCancelButton: true,
          showLoaderOnConfirm: true,
          allowOutsideClick: false,
          inputAttributes: {
            autocapitalize: 'off',
            autocomplete: 'current-password'
          },
          onOpen: function(){
            const inputClave = Vue.swal.getInput();
            if(inputClave) inputClave.focus();
          },
          inputValidator: function(valor){
            if(!valor || !valor.trim()){
              return 'Debe ingresar la clave de autorización.';
            }
          },
          preConfirm: function(clave){
            return $.ajax({
              type: 'POST',
              url: '{{ url("/clave") }}',
              data: {
                clave: clave,
                accion: 'disminuir_stock'
              },
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            }).then(function(respuesta){
              if(respuesta !== 'OK'){
                throw new Error('La clave ingresada no es válida.');
              }

              return respuesta;
            }).catch(function(error){
              Vue.swal.showValidationMessage(
                error && error.message
                  ? error.message
                  : 'No fue posible validar la clave.'
              );
            });
          }
        }).then(function(resultado){
          restaurarFocoModal();

          if(resultado.isConfirmed || resultado.value){
            continuar();
          }
        });
      }

      function confirmar_ajuste_stock(stockAnterior, stockNuevo, motivo, continuar, autorizado = false){
          stockAnterior = Number(stockAnterior);
          stockNuevo = Number(stockNuevo);
          motivo = String(motivo || '').trim();

          if(!Number.isInteger(stockNuevo) || stockNuevo < 0){
            Vue.swal({title:'Dato inválido', text:'Ingrese un stock válido, sin decimales.', icon:'warning'});
            return;
          }
          // if(stockAnterior === stockNuevo){
          //   Vue.swal({title:'Sin cambios', text:'El stock nuevo es igual al stock actual.', icon:'info'});
          //   return;
          // }
          if(motivo.length < 5){
            Vue.swal({title:'Motivo requerido', text:'Explique el ajuste con al menos 5 caracteres.', icon:'warning'});
            return;
          }

          if(stockNuevo < stockAnterior && !autorizado){
            solicitar_clave_disminuir_stock(function(){
              confirmar_ajuste_stock(stockAnterior, stockNuevo, motivo, continuar, true);
            });
            return;
          }

          let diferencia = stockNuevo - stockAnterior;
          let signo = diferencia > 0 ? '+' : '';
          Vue.swal({
            title: 'Confirmar ajuste de stock',
            html: '<div class="stock-confirm-change"><span>'+stockAnterior+'</span><i class="fas fa-arrow-right"></i><strong>'+stockNuevo+'</strong></div><p>Diferencia: <b>'+signo+diferencia+'</b></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar ajuste',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb'
          }).then(function(resultado){
            if(resultado.value) continuar();
          });
      }

      function mostrar_error_ajuste(error){
        let mensaje = 'No fue posible registrar el ajuste de stock.';
        if(error.responseJSON){
          if(error.responseJSON.message){
            mensaje = error.responseJSON.message;
          }else if(error.responseJSON.errors){
            let primerError = Object.values(error.responseJSON.errors)[0];
            mensaje = Array.isArray(primerError) ? primerError[0] : primerError;
          }
        }
        Vue.swal({title:'Ajuste no registrado', text:mensaje, icon:'error'});
      }

      function guardar_stock(){
          confirmar_ajuste_stock(
            document.getElementById('stock_anterior').value,
            document.getElementById('nuevo_stock').valueAsNumber,
            document.getElementById('motivo_stock').value,
            ejecutar_guardar_stock
          );
      }

      function ejecutar_guardar_stock(){
          let id_repuesto = document.getElementById('id_repuesto_stock').value;
          let stock = document.getElementById('nuevo_stock').value;
          let idresponsable = document.getElementById('select_stock').value;
          let motivo = document.getElementById('motivo_stock').value.trim();
          let data = {stock: stock, id_repuesto: id_repuesto, idresponsable: idresponsable, motivo: motivo};
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type:'post',
                data: data,
                url:'/repuesto/guardarstock',
                success: function(resp){
                  
                  if(resp == 'nopermiso'){
                    alert('NO PUEDE HACER CAMBIOS 2 VECES EN EL DÍA');
                    return false;
                  }
                    let id_repuesto = resp[2];
                    if(resp[0] === 'OK'){
                        console.log('stock actualizado');
                        if(resp[1] < 10){
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock').modal('hide');
                          refrescar_repuesto_actual();
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    
                },
                error: function(error){
                    mostrar_error_ajuste(error);
                }
            });
          
      }

      function guardar_stock_dos(){
        confirmar_ajuste_stock(
          document.getElementById('stock_anterior_dos').value,
          document.getElementById('nuevo_stock_dos').valueAsNumber,
          document.getElementById('motivo_stock_dos').value,
          ejecutar_guardar_stock_dos
        );
      }

      function ejecutar_guardar_stock_dos(){
        let id_repuesto = document.getElementById('id_repuesto_stock_dos').value;
          let stock = document.getElementById('nuevo_stock_dos').value;
          let idresponsable = document.getElementById('select_stock_dos').value;
          let opt = 'stock2';
          let motivo = document.getElementById('motivo_stock_dos').value.trim();
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable, motivo: motivo};
          
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type:'post',
                data: data,
                url:'/repuesto/guardarstock',
                success: function(resp){
                  if(resp == 'nopermiso'){
                    alert('NO PUEDE HACER CAMBIOS 2 VECES EN EL DÍA');
                    return false;
                  }
                    let id_repuesto = resp[2];
                    if(resp[3] === 'stock2'){
                      console.log('stock 2 actualizado');
                      if(resp[1] < 10){
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_dos').modal('hide');
                          refrescar_repuesto_actual();
                    }else{
                      if(resp[0] === 'OK'){
                        console.log('stock actualizado');
                        if(resp[1] < 10){
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                            $('#modal_modificar_stock_dos').modal('hide');
                            refrescar_repuesto_actual();
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }
                    
                    
                },
                error: function(error){
                    mostrar_error_ajuste(error);
                }
            });
      }

      function guardar_stock_tres(){
        confirmar_ajuste_stock(
          document.getElementById('stock_anterior_tres').value,
          document.getElementById('nuevo_stock_tres').valueAsNumber,
          document.getElementById('motivo_stock_tres').value,
          ejecutar_guardar_stock_tres
        );
      }

      function ejecutar_guardar_stock_tres(){
        let id_repuesto = document.getElementById('id_repuesto_stock_tres').value;
          let stock = document.getElementById('nuevo_stock_tres').value;
          let idresponsable = document.getElementById('select_stock_tres').value;
          let opt = 'stock3';
          let motivo = document.getElementById('motivo_stock_tres').value.trim();
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable, motivo: motivo};
          
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type:'post',
                data: data,
                url:'/repuesto/guardarstock',
                success: function(resp){
                  if(resp == 'nopermiso'){
                    alert('NO PUEDE HACER CAMBIOS 2 VECES EN EL DÍA');
                    return false;
                  }
                    let id_repuesto = resp[2];
                    if(resp[3] === 'stock2'){
                      console.log('stock 2 actualizado');
                      if(resp[1] < 10){
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_tres').modal('hide');
                          refrescar_repuesto_actual();
                    }else if(resp[3] === 'stock3'){
                      console.log('stock 3 actualizado');
                      if(resp[1] < 10){
                            $('#stock_tres-'+id_repuesto).empty();
                            $('#stock_tres-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_tres-'+id_repuesto).empty();
                            $('#stock_tres-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_tres').modal('hide');
                          refrescar_repuesto_actual();
                    }else{
                      if(resp[0] === 'OK'){
                        console.log('stock actualizado');
                        if(resp[1] < 10){
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                            $('#modal_modificar_stock_tres').modal('hide');
                            refrescar_repuesto_actual();
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }
                    
                    
                },
                error: function(error){
                    mostrar_error_ajuste(error);
                }
            });
      }

      function escapar_html(valor){
        return $('<div>').text(valor === null || valor === undefined ? '' : valor).html();
      }

      function ver_historial_stock(idRepuesto, ubicacion, nombreUbicacion){
        $('#historial_stock_titulo').text('Historial · ' + nombreUbicacion);
        $('#historial_stock_contenido').html('<div class="stock-history-loading"><i class="fas fa-spinner fa-spin"></i><span>Cargando movimientos...</span></div>');
        $('#modal_historial_stock').modal('show');

        $.ajax({
          type: 'get',
          url: '/repuesto/' + idRepuesto + '/historial-stock',
          data: {ubicacion: ubicacion},
          success: function(movimientos){
            if(!movimientos.length){
              $('#historial_stock_contenido').html('<div class="stock-history-empty"><i class="fas fa-clipboard-list"></i><strong>Sin movimientos registrados</strong><span>Los próximos ajustes aparecerán aquí.</span></div>');
              return;
            }

            let html = '<div class="stock-history-list">';
            movimientos.forEach(function(movimiento){
              let diferencia = Number(movimiento.cantidad_movimiento);
              let clase = diferencia > 0 ? 'positive' : 'negative';
              let signo = diferencia > 0 ? '+' : '';
              html += '<article class="stock-history-item">' +
                '<div class="stock-history-icon '+clase+'"><i class="fas '+(diferencia > 0 ? 'fa-arrow-up' : 'fa-arrow-down')+'"></i></div>' +
                '<div class="stock-history-main">' +
                  '<div class="stock-history-change"><em>'+escapar_html(String(movimiento.tipo || '').replace(/_/g,' '))+'</em><span>'+movimiento.stock_anterior+'</span><i class="fas fa-arrow-right"></i><strong>'+movimiento.stock_nuevo+'</strong><b class="'+clase+'">'+signo+diferencia+'</b></div>' +
                  '<p>'+escapar_html(movimiento.motivo)+'</p>' +
                  '<div class="stock-history-meta"><span><i class="fas fa-user"></i>'+escapar_html(movimiento.usuario)+'</span>' +
                  (movimiento.responsable ? '<span><i class="fas fa-user-check"></i>Responsable: '+escapar_html(movimiento.responsable)+'</span>' : '') +
                  '<span><i class="far fa-clock"></i>'+escapar_html(movimiento.fecha)+'</span></div>' +
                '</div>' +
              '</article>';
            });
            html += '</div>';
            $('#historial_stock_contenido').html(html);
          },
          error: function(error){
            let mensaje = error.responseJSON && error.responseJSON.message
              ? error.responseJSON.message
              : 'No fue posible consultar el historial.';
            $('#historial_stock_contenido').html('<div class="stock-history-empty text-danger"><i class="fas fa-exclamation-circle"></i><strong>'+escapar_html(mensaje)+'</strong></div>');
          }
        });
      }

      function guardar_ubicacion(){
          let ubicacion = document.getElementById('ubicacion').value;
          let piso = document.getElementById('piso').value;
          let estanteria = document.getElementById('estanteria').value;
          let bandeja = document.getElementById('bandeja').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;
        
          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              pasillo: pasillo,
              id_repuesto: id_repuesto
          }

          let url = '/repuesto/modificarubicacion';
          
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){
                  console.log(data);
                
                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        // $('#ubi_uno').empty();
                        // $('$ubi_uno').append('<p>Ubicacion: '+data[1]+'  </p>');
                        $('#ubicacion-'+id_repuesto).empty();
                        $('#ubicacion-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación guardada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion").modal('hide');
                        actualizar_stock(id_repuesto);
                    }else{
                      Vue.swal({
                          text: "No se puede guardar la misma ubicacion",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function guardar_ubicacion_dos(){
          console.log('GUARDANDO');
          let ubicacion = document.getElementById('bodega_ubicacion_dos').value;
          let piso = document.getElementById('piso_ubicacion_dos').value;
          let estanteria = document.getElementById('estanteria_ubicacion_dos').value;
          let bandeja = document.getElementById('bandeja_ubicacion_dos').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;
          let opt = 'ubicacion2';

          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              id_repuesto: id_repuesto,
              pasillo: pasillo,
              opt: opt
          }

          let url = '/repuesto/modificarubicacion';

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){
                   
                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        $('#ubicacion_dos-'+id_repuesto).empty();
                        $('#ubicacion_dos-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación actualizada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                        actualizar_stock(id_repuesto);
                        $("#modal_modificar_ubicacion_dos").modal('hide');
                    }else{
                      Vue.swal({
                          text: "No se pueden guardar 2 ubicaciones iguales",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion_dos").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function guardar_ubicacion_tres(){
          console.log('GUARDANDO 3');
          let ubicacion = document.getElementById('bodega_ubicacion_tres').value;
          let piso = document.getElementById('piso_ubicacion_tres').value;
          let estanteria = document.getElementById('estanteria_ubicacion_tres').value;
          let bandeja = document.getElementById('bandeja_ubicacion_tres').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;
          let opt = 'ubicacion3';

          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              id_repuesto: id_repuesto,
              pasillo: pasillo,
              opt: opt
          }

          let url = '/repuesto/modificarubicacion';

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){
                   
                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        $('#ubicacion_tres-'+id_repuesto).empty();
                        $('#ubicacion_tres-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación actualizada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                        actualizar_stock(id_repuesto);
                        $("#modal_modificar_ubicacion_tres").modal('hide');
                    }else{
                      Vue.swal({
                          text: "No se pueden guardar 2 ubicaciones iguales",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion_tres").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function actualizar_stock(id_repuesto){
        
        let url = '/repuesto/actualizarstock/'+id_repuesto;
        $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              
                if(resp[0].stock_actual < 10){
                    
                    $('#mensaje_stock').empty();
                    $('#mensaje_stock').css('display','block');
                    $('#mensaje_stock').append('Stock acabandose')
                }else{
                    $('#mensaje_stock').css('display','none');
                    $('#mensaje_stock').empty();
                }
                
            },
            error: function(err){

            }
        })
      }

      function cerrar_div(){
        $('#campo_modificar').css('transition','all 300ms');
        $('#campo_modificar').css('display','none');
      }

      function cerrar_div_ubicacion(){
        $('#campo_ubicacion').css('display','none');
      }

      function enter_press(e)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            buscar_stock_repuesto();
        }
    }

    function modificar_ubicacion(id_repuesto,nombre_repuesto,marca,ubicacion){
        
        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_Label').empty();
            $('#modal_modificar_ubicacion_Label').append('<p class="title">'+nombre_repuesto+'</p>');
            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca').empty();
            $('#marca').append('<p>Marca: '+marca+' </p>');
            $('#ubi_uno').empty();
            $('#ubi_uno').append('<p>Ubicacion: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }

    function modificar_ubicacion_dos(id_repuesto,nombre_repuesto,marca,ubicacion){
        
        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_dos_Label').empty();
            $('#modal_modificar_ubicacion_dos_Label').append('<p class="title">'+nombre_repuesto+'</p>');

            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca_dos').empty();
            $('#marca_dos').append('<p>Marca: '+marca+' </p>');

            $('#ubi_dos').empty();
            $('#ubi_dos').append('<p>Ubicación: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }

    function modificar_ubicacion_tres(id_repuesto,nombre_repuesto,marca,ubicacion){
        
        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_tres_Label').empty();
            $('#modal_modificar_ubicacion_tres_Label').append('<p class="title">'+nombre_repuesto+'</p>');

            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca_tres').empty();
            $('#marca_tres').append('<p>Marca: '+marca+' </p>');

            $('#ubi_tres').empty();
            $('#ubi_tres').append('<p>Ubicación: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }
    
  </script>
 @endsection

 @section('style')
  <style>
    :root {
      --stock-primary: #2563eb;
      --stock-primary-dark: #1d4ed8;
      --stock-soft: #eff6ff;
      --stock-bg: #f4f7fb;
      --stock-card: #ffffff;
      --stock-text: #1f2937;
      --stock-muted: #6b7280;
      --stock-border: #e5e7eb;
      --stock-success: #15803d;
      --stock-warning: #b45309;
      --stock-danger: #dc2626;
    }

    .stock-page-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:22px 26px; margin:14px 0 20px; background:linear-gradient(135deg,#fff 0%,#eff6ff 100%); border:1px solid #dbeafe; border-radius:18px; box-shadow:0 8px 24px rgba(15,23,42,.06); }
    .stock-page-eyebrow { display:block; color:var(--stock-primary); font-size:.75rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .stock-page-subtitle { color:var(--stock-muted); font-size:.94rem; }
    .stock-page-icon { width:54px; height:54px; display:flex; align-items:center; justify-content:center; border-radius:16px; background:var(--stock-primary); color:#fff; font-size:1.45rem; box-shadow:0 8px 18px rgba(37,99,235,.25); }

    .stock-dashboard-row { margin:0 -8px 20px; }
    .stock-dashboard-row > [class*=col-] { padding:8px; }
    .stock-summary-card { height:100%; background:#fff; border:1px solid var(--stock-border); border-radius:16px; overflow:hidden; box-shadow:0 6px 18px rgba(15,23,42,.05); }
    .stock-summary-header { display:flex; align-items:center; gap:10px; padding:14px 16px; background:#f8fafc; border-bottom:1px solid var(--stock-border); }
    .stock-summary-header i { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:var(--stock-soft); color:var(--stock-primary); }
    .stock-summary-header h4 { margin:0; font-size:1rem; color:var(--stock-text); font-weight:700; }
    .stock-summary-card .table { margin:0; font-size:12.5px !important; }
    .stock-summary-card .table thead th { background:#fff; color:#64748b; border-top:0; border-bottom:1px solid var(--stock-border); font-size:11px; text-transform:uppercase; letter-spacing:.03em; padding:10px 12px; }
    .stock-summary-card .table tbody td { padding:9px 12px; border-top:1px solid #f1f5f9; vertical-align:middle; color:#374151; }
    .stock-summary-card .table tbody tr:hover { background:#f8fbff; }

    .stock-actions { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 18px; }
    .stock-actions .btn { border-radius:9px; font-weight:600; box-shadow:none; padding:8px 13px; }

    .busqueda_principal, .busqueda_resultado { background:var(--stock-card); border:1px solid var(--stock-border); border-radius:18px; box-shadow:0 8px 24px rgba(15,23,42,.06); }
    .busqueda_principal { margin-top:0; padding:22px; }
    .busqueda_resultado { margin-top:20px; min-height:220px; padding:0; overflow:hidden; }
    .stock-card-header { display:flex; align-items:center; justify-content:space-between; gap:15px; padding:18px 20px; border-bottom:1px solid var(--stock-border); background:#fbfdff; }
    .stock-card-title { display:flex; align-items:center; gap:12px; }
    .stock-card-title .icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:var(--stock-soft); color:var(--stock-primary); font-size:1.05rem; }
    .stock-card-title h5 { margin:0; color:var(--stock-text); font-weight:700; }
    .stock-card-title p { margin:2px 0 0; color:var(--stock-muted); font-size:.85rem; }
    .stock-search-body { padding:22px; }
    .search-type-selector { display:flex; flex-wrap:wrap; gap:9px; }
    .search-type-selector .form-check { margin:0; padding:0; }
    .search-type-selector input { position:absolute; opacity:0; }
    .search-type-selector label { cursor:pointer; height:auto; line-height:1.2; margin:0; padding:10px 14px; border:1px solid var(--stock-border); border-radius:10px; background:#fff; color:#475569; font-weight:600; transition:.2s ease; }
    .search-type-selector input:checked + label { background:var(--stock-soft); color:var(--stock-primary-dark); border-color:#93c5fd; box-shadow:0 0 0 3px rgba(37,99,235,.08); }
    .stock-search-input { height:46px; border-radius:10px 0 0 10px; border-color:#cbd5e1; font-size:15px; }
    .stock-search-input:focus { border-color:#60a5fa; box-shadow:0 0 0 .2rem rgba(37,99,235,.12); }
    .stock-search-btn { min-width:120px; border-radius:0 10px 10px 0; background:var(--stock-primary); border-color:var(--stock-primary); font-weight:700; }
    .stock-search-btn:hover { background:var(--stock-primary-dark); border-color:var(--stock-primary-dark); }
    #mensajes { background:transparent; padding:0; color:var(--stock-muted); font-size:.86rem; }

    .stock-table-wrapper { overflow-x:auto; }
    #tabla_resultados { min-width:1280px; margin:0; }
    #tabla_resultados thead th { position:sticky; top:0; z-index:1; padding:12px 9px; border:0; background:#1e293b; color:#fff; font-size:10.5px; text-transform:uppercase; letter-spacing:.035em; vertical-align:middle; white-space:nowrap; }
    #tabla_resultados tbody td { padding:10px 8px; vertical-align:middle; border-color:#eef2f7; color:#334155; }
    #tabla_resultados tbody tr:nth-child(even) { background:#fafcff; }
    #tabla_resultados tbody tr:hover { background:#eff6ff; }
    #tabla_resultados a { color:var(--stock-primary-dark) !important; font-weight:700; }
    #tabla_resultados .btn-warning { width:28px; height:28px; padding:0; margin-left:6px; border:0; border-radius:8px; background:#fff7ed; color:var(--stock-warning); font-size:0; box-shadow:none; vertical-align:middle; }
    #tabla_resultados .btn-warning:after { content:'\f044'; font-family:'Font Awesome 6 Free'; font-weight:900; font-size:11px; }
    #tabla_resultados .btn-warning:hover { background:#ffedd5; color:#9a3412; }
    .stock_bajo { display:inline-flex; align-items:center; justify-content:center; min-width:34px; background:#fef2f2; color:var(--stock-danger); border:1px solid #fecaca; font-weight:800; padding:4px 8px; border-radius:999px; }
    .imagen_pequeña { width:72px; height:58px; object-fit:contain; background:#fff; border:1px solid var(--stock-border); border-radius:10px; padding:4px; }

    .modal-content { border:0; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(15,23,42,.2); }
    .modal-header { align-items:center; background:#f8fafc; border-bottom:1px solid var(--stock-border); }
    .modal-header .logo { width:55px; height:42px; object-fit:contain; margin-right:12px; }
    .modal-title { color:var(--stock-text); font-weight:700; }
    .modal-footer { border-top:1px solid var(--stock-border); background:#fbfdff; }
    .modal-footer .btn { border-radius:9px; font-weight:600; }
    .select-ubicacion { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; margin-top:10px; }
    .select-ubicacion select { width:100%; margin:0; border-radius:9px; }
    .campo_modificar { margin-top:30px; padding:30px; border:1px solid var(--stock-border); border-radius:14px; }
    .logo { width:70px; border-radius:10px; }
    .alert-danger { padding:5px; border:1px dotted; }
    #tbody_resultados { font-size:13px; }


    .stock-results-container { padding:18px; background:#f7f9fc; }
    .stock-empty-state { min-height:250px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; color:var(--stock-muted); }
    .stock-empty-state > span { width:68px; height:68px; display:flex; align-items:center; justify-content:center; border-radius:20px; background:var(--stock-soft); color:var(--stock-primary); font-size:1.6rem; margin-bottom:14px; }
    .stock-empty-state h5 { color:var(--stock-text); font-weight:700; }
    .stock-empty-state p { max-width:460px; }
    .stock-result-layout { display:grid; grid-template-columns:330px minmax(0,1fr); gap:18px; }
    .stock-product-card, .stock-locations-panel { background:#fff; border:1px solid var(--stock-border); border-radius:16px; box-shadow:0 7px 20px rgba(15,23,42,.05); }
    .stock-product-card { position:relative; padding:18px; }
    .stock-product-status { position:absolute; top:14px; right:14px; z-index:2; padding:6px 10px; border-radius:999px; background:#eaf8ef; color:#15803d; font-size:.75rem; font-weight:700; }
    .stock-product-image-wrap { height:170px; display:flex; align-items:center; justify-content:center; margin-bottom:18px; border-radius:13px; background:#f8fafc; border:1px solid #eef2f7; }
    .stock-product-image { max-width:88%; max-height:145px; object-fit:contain; }
    .stock-product-placeholder { font-size:3rem; color:#94a3b8; }
    .stock-product-data { border-top:1px solid var(--stock-border); border-bottom:1px solid var(--stock-border); padding:12px 0; }
    .stock-product-item { display:grid; grid-template-columns:38px 1fr; gap:10px; align-items:center; padding:8px 0; }
    .stock-product-item-icon { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:9px; background:#eff6ff; color:#2563eb; }
    .stock-product-item-icon.purple { background:#f3e8ff; color:#7e22ce; }
    .stock-product-item small, .stock-field-label { display:block; color:#64748b; text-transform:uppercase; font-size:.66rem; font-weight:700; letter-spacing:.035em; }
    .stock-product-item strong, .stock-product-item a { display:block; margin-top:2px; color:#172554; font-weight:700; line-height:1.35; }
    .stock-product-item a { color:#2563eb; font-size:1rem; }
    .stock-product-summary-title { margin:15px 0 9px; color:#1e293b; font-size:.77rem; font-weight:800; text-transform:uppercase; }
    .stock-product-summary-title i { color:#2563eb; margin-left:4px; }
    .stock-product-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; }
    .stock-product-summary > div { min-width:0; padding:10px 5px; text-align:center; border:1px solid #bfdbfe; border-radius:10px; background:#f8fbff; color:#1d4ed8; }
    .stock-product-summary > div.success { color:#15803d; border-color:#bbf7d0; background:#f0fdf4; }
    .stock-product-summary > div.danger { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
    .stock-product-summary small, .stock-product-summary span { display:block; font-size:.61rem; text-transform:uppercase; }
    .stock-product-summary strong { display:block; font-size:1.5rem; line-height:1.15; }
    .stock-warning-note { margin-top:14px; padding:10px 11px; border-radius:9px; border:1px solid #fde68a; background:#fffbeb; color:#92400e; font-size:.76rem; }
    .stock-warning-note i { color:#f59e0b; margin-right:6px; }
    .stock-locations-panel { padding:16px; }
    .stock-locations-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:4px 4px 15px; }
    .stock-locations-heading h5 { margin:0; color:#172554; font-weight:800; }
    .stock-locations-heading p { margin:2px 0 0; color:#64748b; font-size:.82rem; }
    .stock-locations-heading > span { white-space:nowrap; padding:8px 11px; border-radius:9px; background:#ecfdf3; color:#15803d; font-size:.75rem; font-weight:700; }
    .stock-location-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:13px; }
    .stock-location-card { --location-color:#2563eb; --location-soft:#eff6ff; display:flex; flex-direction:column; min-width:0; overflow:hidden; border:1px solid var(--stock-border); border-radius:14px; background:#fff; box-shadow:0 5px 15px rgba(15,23,42,.04); }
    .stock-location-green { --location-color:#16a34a; --location-soft:#ecfdf3; }
    .stock-location-purple { --location-color:#7c3aed; --location-soft:#f5f3ff; }
    .stock-location-header { display:flex; align-items:center; justify-content:space-between; padding:14px 15px; border-bottom:1px solid var(--stock-border); }
    .stock-location-title { display:flex; align-items:center; gap:10px; min-width:0; }
    .stock-location-icon { width:39px; height:39px; flex:none; display:flex; align-items:center; justify-content:center; border-radius:11px; background:var(--location-soft); color:var(--location-color); }
    .stock-location-title h6 { margin:0; color:var(--location-color); font-size:1rem; font-weight:800; }
    .stock-location-title small { color:#64748b; }
    .stock-location-cube { color:var(--location-color); }
    .stock-location-body { padding:5px 15px 10px; }
    .stock-location-field { display:flex; align-items:center; justify-content:space-between; gap:9px; min-height:83px; padding:12px 0; border-bottom:1px solid #eef2f7; }
    .stock-field-content { min-width:0; }
    .stock-field-content strong { display:block; margin-top:7px; color:#172554; overflow-wrap:anywhere; }
    .stock-number-row { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-top:4px; }
    .stock-number-row small { color:#475569; }
    .stock-value { color:var(--location-color); font-size:1.9rem; line-height:1; font-weight:800; }
    .stock-value-low { color:#dc2626; }
    .stock-low-badge { padding:4px 7px; border-radius:999px; background:#fef2f2; color:#dc2626; font-size:.66rem; font-weight:700; }
    .stock-edit-btn { width:36px; height:36px; flex:none; display:flex; align-items:center; justify-content:center; border:1px solid #dbe3ef; border-radius:9px; background:#fff; color:#172554; cursor:pointer; transition:.2s ease; }
    .stock-edit-btn:hover { border-color:var(--location-color); color:var(--location-color); background:var(--location-soft); }
    .stock-responsible-box { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:11px; padding:11px; border-radius:9px; background:#f8fafc; }
    .stock-responsible-box strong { display:block; margin-top:3px; color:#334155; font-size:.82rem; overflow-wrap:anywhere; }
    .stock-responsible-box i { color:var(--location-color); }
    .stock-location-footer { display:flex; align-items:center; gap:9px; margin-top:auto; padding:0 15px 15px; color:var(--location-color); }
    .stock-progress { height:4px; flex:1; overflow:hidden; border-radius:99px; background:#e2e8f0; }
    .stock-progress span { display:block; height:100%; border-radius:inherit; background:var(--location-color); }
    .stock-location-footer strong { white-space:nowrap; font-size:.76rem; }
    .stock-history-btn { display:flex; align-items:center; justify-content:center; gap:7px; margin:0 15px 14px; padding:8px 10px; border:1px solid var(--stock-border); border-radius:9px; background:#fff; color:var(--location-color); font-size:.73rem; font-weight:700; cursor:pointer; }
    .stock-history-btn:hover { border-color:var(--location-color); background:var(--location-soft); }
    .stock-audit-current { display:flex; align-items:center; justify-content:space-between; margin:12px 0; padding:11px 13px; border:1px solid #bfdbfe; border-radius:10px; background:#eff6ff; color:#1e40af; }
    .stock-audit-current span { font-size:.75rem; font-weight:700; text-transform:uppercase; }
    .stock-audit-current strong { font-size:1rem; }
    .stock-confirm-change { display:flex; align-items:center; justify-content:center; gap:14px; margin:12px 0; font-size:1.35rem; }
    .stock-confirm-change span { color:#64748b; }
    .stock-confirm-change strong { color:#2563eb; }
    #modal_historial_stock .modal-dialog { max-width:760px; }
    #historial_stock_contenido { min-height:180px; padding:5px; background:#f8fafc; }
    .stock-history-loading, .stock-history-empty { display:flex; min-height:180px; flex-direction:column; align-items:center; justify-content:center; gap:9px; color:#64748b; text-align:center; }
    .stock-history-loading i, .stock-history-empty > i { font-size:1.8rem; color:#2563eb; }
    .stock-history-list { display:flex; flex-direction:column; gap:10px; }
    .stock-history-item { display:grid; grid-template-columns:42px minmax(0,1fr); gap:12px; padding:13px; border:1px solid var(--stock-border); border-radius:11px; background:#fff; }
    .stock-history-icon { display:flex; width:38px; height:38px; align-items:center; justify-content:center; border-radius:10px; }
    .stock-history-icon.positive { color:#15803d; background:#dcfce7; }
    .stock-history-icon.negative { color:#dc2626; background:#fee2e2; }
    .stock-history-change { display:flex; align-items:center; flex-wrap:wrap; gap:8px; color:#64748b; font-size:.9rem; }
    .stock-history-change em { padding:3px 7px; border-radius:999px; color:#1d4ed8; background:#dbeafe; font-size:.64rem; font-style:normal; font-weight:800; }
    .stock-history-change strong { color:#172554; font-size:1.05rem; }
    .stock-history-change b { margin-left:auto; padding:3px 8px; border-radius:999px; font-size:.75rem; }
    .stock-history-change b.positive { color:#15803d; background:#dcfce7; }
    .stock-history-change b.negative { color:#dc2626; background:#fee2e2; }
    .stock-history-main p { margin:7px 0; color:#334155; font-size:.82rem; }
    .stock-history-meta { display:flex; flex-wrap:wrap; gap:7px 14px; color:#64748b; font-size:.7rem; }
    .stock-history-meta i { margin-right:5px; color:#2563eb; }
    .stock-result-help { display:flex; justify-content:space-between; gap:12px; margin-top:15px; padding:11px 13px; border:1px solid var(--stock-border); border-radius:10px; color:#475569; font-size:.75rem; }
    .stock-result-help i { color:#2563eb; margin-right:5px; }

    @media (max-width: 767px) {
      .stock-page-heading { padding:18px; }
      .stock-page-icon { display:none; }
      .stock-card-header { align-items:flex-start; }
      .stock-search-body { padding:16px; }
      .search-type-selector { margin-bottom:14px; }
      .stock-search-btn { min-width:92px; }
      .select-ubicacion { grid-template-columns:1fr; }
      .stock-result-layout { grid-template-columns:1fr; }
      .stock-location-grid { grid-template-columns:1fr; }
      .stock-locations-heading, .stock-result-help { align-items:flex-start; flex-direction:column; }
    }
  </style>
 @endsection

 @section('contenido_ingresa_datos')
 <div class="container-fluid">
  <script type="text/javascript">
    $('#modalArticulos').on('shown.bs.modal', function () {
        $('#codigoEscaneado').focus();
    });

    function buscarArticulo(){
        let codigo_escaneado = $('#codigoEscaneado').val();
        let url = '{{url("/buscar-barcode")}}';

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });
        $.ajax({
            type:'POST',
            url: url,
            data: {'codigo_escaneado': codigo_escaneado},
            beforeSend: function(){
                console.log('Enviando');
            },
            success: function(resp){
                
                $('#tbody_repuesto_escaneado').html(resp);
            },
            error: function(err){
                console.log(err.statusText);
            }
        })
    }
    
    function agregar(){
        console.log('Agregando');
        
        let codigo_repuesto = document.getElementById('codigoEscaneado').value;
          let opt = 'cod_int';
          let data = {codigo_repuesto: codigo_repuesto, option: opt};
          
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
          $.ajax({
              type:'POST',
              url:'/repuesto/damestockrepuesto',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append('Buscando ...');
                espere('Buscando ...');
              },
              success: function(resp){
                
                Vue.swal.close();
                $('#mensajes').empty();
                $('#mensajes').append('Listo');
                $('#tbody_resultados').empty();
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                }else{
                 resp.forEach(element => {
                     
                     if(element.stock_actual < 10){
                        $('#tbody_resultados').append(`
                        <tr>
                            <th scope="row" style="width: 30%">`+element.descripcion+`</th>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`")' data-target="#modal_modificar_stock">M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td>`+element.ubicacion_dos+`<button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td></td>
                        </tr>
                 `); 
                     }else{
                        $('#tbody_resultados').append(`
                        <tr>
                            <th scope="row" style="width: 30%">`+element.descripcion+`</th>
                            <td><span id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`")' data-target="#modal_modificar_stock">M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td>`+element.ubicacion_dos+`<button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td></td>
                        </tr>
                 `); 
                     }
                    
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
          })
    }

    function resetear_contador_dias_precio_reps(){
      Vue.swal({
        title: "¿Está seguro de resetear los días de precio de los repuestos?",
        text: "Una vez reseteados los días de precio de los repuestos no se podrá volver atrás",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
      })
      .then((result) => {
        if (result.isConfirmed) {
          confirmar_resetear_dias();
        } else {
          Vue.swal("Operación cancelada");
        }
      });
    }

    function confirmar_resetear_dias(){
      let url = '{{url("/resetear-dias-precio-repuestos")}}';
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          console.log('Enviando');
          Vue.swal({
            title: "Reseteando días de precio de repuestos",
            text: "Por favor espere...",
            allowOutsideClick: false,
            didOpen: () => {
              Vue.swal.showLoading()
            }
          });
        },
        success: function(resp){
          console.log(resp);
          if(resp === 'ok'){
            Vue.swal({
              title: "Días de precio de repuestos reseteados",
              icon: "success",
              timer: 3000
            });
          }else{
            Vue.swal({
              title: "Error al resetear los días de precio de los repuestos",
              icon: "error",
              timer: 3000
            });
          }
        },
        error: function(err){
          console.log(err);
        }
      });
    }
</script>

<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="modalArticulos" tabindex="-1" role="dialog" aria-labelledby="modalArticulosLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
                <h4 class="modal-title" id="modalArticulosLabel">Ingreso de Artículos</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Escanear Código de Barras</label>
					<div class="input-group">
						<div class="input-group-addon icon_barcode"><i class="fa fa-barcode"></i></div>
						<input type="text" class="form-control producto" name="codigoEscaneado" id="codigoEscaneado" autocomplete="off" onchange="buscarArticulo();">
					</div>
				</div>
				<div>
					<table class="table table-striped" id="tablaAgregarArticulos">
						<thead>	
							<tr>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Familia</th>
                                <th>Observaciones</th>
                                <th>Pais</th>
                                <th>Empresa</th>
                                <th>Precio Venta</th>
							</tr>
						</thead>
						<tbody id="tbody_repuesto_escaneado">
						
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" id="btnCerrarModal">Cerrar</button>
				<button type="button" class="btn btn-primary" id="btnAgregar" onclick="agregar();">Agregar</button>
			</div>
		</div>
	</div>
</div>
<div class="row stock-dashboard-row">
  <div class="col-md-4"><div class="stock-summary-card"><div class="stock-summary-header"><i class="fas fa-shopping-cart"></i><h4>Últimos repuestos vendidos</h4></div>
    <table class="table" style="font-size: 13px">
      <thead class="thead-dark">
        <tr>
          <th scope="col" style="width: 100px">Código interno</th>
          <th scope="col">Desc.</th>
          <th scope="col">Cantidad</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ultimos_repuestos_vendidos as $repuesto)
        <tr>
          <td>{{$repuesto->codigo_interno}}</td>
          <td>{{$repuesto->descripcion}}</td>
          <td>{{$repuesto->cantidad}}</td>
          
        </tr>
        @endforeach
      </tbody>
    </table>
  </div></div>
  <div class="col-md-4"><div class="stock-summary-card"><div class="stock-summary-header"><i class="fas fa-receipt"></i><h4>Últimas ventas</h4></div>
    <table class="table" style="font-size: 13px">
      <thead class="thead-dark">
        <tr>
          
          <th scope="col">Fecha</th>
          <th scope="col">Tipo Doc</th>
          <th scope="col">Hora</th>
          <th scope="col">Venta total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ultimas_ventas as $repuesto)
        <tr>
          
          <td>{{$repuesto->fecha_formateada}}</td>
          <td>{{$repuesto->tipo_doc}}</td>
          <td>{{$repuesto->hora}}</td>
          <td>${{number_format($repuesto->total)}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div></div>
  <div class="col-md-4"><div class="stock-summary-card"><div class="stock-summary-header"><i class="fas fa-box-open"></i><h4>Últimos repuestos ingresados</h4></div>
    <table class="table" style="font-size: 13px">
      <thead class="thead-dark">
        <tr>
          <th scope="col">Código interno</th>
          <th scope="col">Desc.</th>
          <th scope="col">Valor</th>
          
        </tr>
      </thead>
      <tbody>
        @foreach($ultimos_repuestos as $repuesto)
        <tr>
          <td>{{$repuesto->codigo_interno}}</td>
          <td>{{$repuesto->descripcion}}</td>
          <td>${{number_format($repuesto->precio_venta)}}</td>
          <td></td>
        </tr>
        @endforeach
        
      </tbody>
    </table>
  </div></div>
</div>

<div class="stock-actions"><button class="btn btn-success" type="button" id="btnNuevo" data-toggle="modal" data-target="#modalArticulos" data-keyboard="false" data-backdrop="static"><i class="fa fa-plus"></i> Nuevo Bulto</button>
@if(Auth::user()->rol->nombrerol == "Administrador") 
<a href="/ventas/detalle_ventas" class="btn btn-warning mt-2">Detalle </a> 
<a href="/repuesto/actualizados" class="btn btn-secondary mt-2">Repuestos actualizados </a>
<button class="btn btn-dark mt-2" onclick="resetear_contador_dias_precio_reps()">Reiniciar contador de diás de cambio de precio de reps</button>
 @endif
</div>
<div class="busqueda_principal">
  
    <div class="stock-card-header">
      <div class="stock-card-title">
        <div class="icon"><i class="fas fa-search"></i></div>
        <div><h5>Buscar repuesto</h5><p>Selecciona el tipo de código e ingresa el valor a consultar.</p></div>
      </div>
      <div id="mensajes"></div>
    </div>
    <div class="stock-search-body"><div class="row align-items-end">
        <div class="col-md-5"><label class="font-weight-bold mb-2">Buscar por</label><div class="search-type-selector"><div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked>
                <label class="form-check-label" for="flexRadioDefault1">
                  Código interno
                </label>
              </div>
              
              <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="oem" >
                <label class="form-check-label" for="flexRadioDefault2">
                  OEM
                </label>
              </div>
              
              <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4" value="cod_prov" >
                <label class="form-check-label" for="flexRadioDefault4">
                  Código de proveedor
                </label>
              </div></div>
        </div>
        <div class="col-md-7">
            <div class="form-group">
                <label for="codigo_repuesto" class="font-weight-bold">Código del repuesto</label>
                <div class="input-group">
                  <input type="text" class="form-control stock-search-input" name="codigo_repuesto" id="codigo_repuesto" placeholder="Ej: código interno, OEM o proveedor" onkeyup="enter_press(event)" autocomplete="off">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-primary stock-search-btn" onclick="buscar_stock_repuesto()"><i class="fas fa-search mr-1"></i> Buscar</button>
                  </div>
                </div>
                <small class="form-text text-muted">También puedes presionar Enter para realizar la búsqueda.</small>
            </div>
        </div>
    </div></div>
</div>

<div class="busqueda_resultado">
    <div id="tbody_resultados" class="stock-results-container">
      <div class="stock-empty-state">
        <span><i class="fas fa-boxes"></i></span>
        <h5>Busca un repuesto para consultar su stock</h5>
        <p>La información del producto y sus ubicaciones aparecerán ordenadas en este espacio.</p>
      </div>
    </div>
</div>
</div>
<!-- Modal -->
<div class="modal fade" id="modal_historial_stock" tabindex="-1" role="dialog" aria-labelledby="historial_stock_titulo" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="modal-title" id="historial_stock_titulo">Historial de stock</div>
          <small class="text-muted">Registro inmutable de ajustes manuales</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="historial_stock_contenido"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_modificar_stock" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_stock_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body">
          
        </div>
        <div class="modal-footer">
            <select name="select_stock" id="select_stock" class="form-control"></select>
          
          
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_stock()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal modificar stock 2 -->
<div class="modal fade" id="modal_modificar_stock_dos" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_dos_Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
        <div class="modal-title" id="modal_modificar_stock_dos_Label">Modal title</div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_dos">
        
      </div>
      <div class="modal-footer">
        <select name="select_stock_dos" id="select_stock_dos" class="form-control"></select>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="guardar_stock_dos()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal modificar stock 3 -->
<div class="modal fade" id="modal_modificar_stock_tres" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_tres_Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
        <div class="modal-title" id="modal_modificar_stock_tres_Label">Modal title</div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_tres">
        
      </div>
      <div class="modal-footer">
        <select name="select_stock_tres" id="select_stock_tres" class="form-control"></select>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="guardar_stock_tres()">Guardar</button>
      </div>
    </div>
  </div>
</div>
  <!-- Modal -->
<div class="modal fade" id="modal_modificar_ubicacion" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca">Marca:</p>
            <p id="ubi_uno">Ubicación: </p>
            <span>Ingrese nueva ubicación</span>
               
                <div class="select-ubicacion">
                  <select name="ubicacion" id="ubicacion" class="form-control">
                    <option value="1">Bodega</option>
                    </select>
                    <select name="piso" id="piso" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja" class="form-control">
                        <option value="b0">Bandeja</option>
                        <option value="bA">Bandeja A</option>
                        <option value="bB">Bandeja B</option>
                        <option value="bC">Bandeja C</option>
                        <option value="bD">Bandeja D</option>
                        <option value="bE">Bandeja E</option>
                        <option value="bF">Bandeja F</option>
                        <option value="bG">Bandeja G</option>
                        <option value="bH">Bandeja H</option>
                        <option value="bI">Bandeja I</option>
                        <option value="bJ">Bandeja J</option>
                        <option value="bK">Bandeja K</option>
                        <option value="bL">Bandeja L</option>
                        <option value="bM">Bandeja M</option>
                        <option value="bN">Bandeja N</option>
                        <option value="bÑ">Bandeja Ñ</option>
                    </select>
                    <select name="pasillo" id="pasillo" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  <!--Modal ubicacion 2 -->
  <div class="modal fade" id="modal_modificar_ubicacion_dos" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_dos_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_dos_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion_dos">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca_dos">Marca: </p>
            <p id="ubi_dos">Ubicación:</p>
            <span>Ingrese nueva ubicación</span>
                <div class="select-ubicacion">
                  <select name="bodega_ubicacion_dos" id="bodega_ubicacion_dos" class="form-control">
                    <option value="3">Tienda</option>
                    </select>
                    <select name="piso" id="piso_ubicacion_dos" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria_ubicacion_dos" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja_ubicacion_dos" class="form-control">
                      <option value="b0">Bandeja</option>
                      <option value="bA">Bandeja A</option>
                      <option value="bB">Bandeja B</option>
                      <option value="bC">Bandeja C</option>
                      <option value="bD">Bandeja D</option>
                      <option value="bE">Bandeja E</option>
                      <option value="bF">Bandeja F</option>
                      <option value="bG">Bandeja G</option>
                      <option value="bH">Bandeja H</option>
                      <option value="bI">Bandeja I</option>
                      <option value="bJ">Bandeja J</option>
                      <option value="bK">Bandeja K</option>
                      <option value="bL">Bandeja L</option>
                      <option value="bM">Bandeja M</option>
                      <option value="bN">Bandeja N</option>
                      <option value="bÑ">Bandeja Ñ</option>
                  </select>
                    <select name="pasillo" id="pasillo_ubicacion_dos" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion_dos()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  
  <!--Modal ubicacion 3 -->
  <div class="modal fade" id="modal_modificar_ubicacion_tres" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_tres_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_tres_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion_tres">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca_tres">Marca: </p>
            <p id="ubi_tres">Ubicación:</p>
            <span>Ingrese nueva ubicación</span>
                <div class="select-ubicacion">
                  <select name="bodega_ubicacion_tres" id="bodega_ubicacion_tres" class="form-control">
                    <option value="4">Casa Matríz</option>
                    </select>
                    <select name="piso" id="piso_ubicacion_tres" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria_ubicacion_tres" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja_ubicacion_tres" class="form-control">
                      <option value="b0">Bandeja</option>
                      <option value="bA">Bandeja A</option>
                      <option value="bB">Bandeja B</option>
                      <option value="bC">Bandeja C</option>
                      <option value="bD">Bandeja D</option>
                      <option value="bE">Bandeja E</option>
                      <option value="bF">Bandeja F</option>
                      <option value="bG">Bandeja G</option>
                      <option value="bH">Bandeja H</option>
                      <option value="bI">Bandeja I</option>
                      <option value="bJ">Bandeja J</option>
                      <option value="bK">Bandeja K</option>
                      <option value="bL">Bandeja L</option>
                      <option value="bM">Bandeja M</option>
                      <option value="bN">Bandeja N</option>
                      <option value="bÑ">Bandeja Ñ</option>
                  </select>
                    <select name="pasillo" id="pasillo_ubicacion_tres" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion_tres()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
 @endsection
