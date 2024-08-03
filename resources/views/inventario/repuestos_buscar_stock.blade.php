@extends('plantillas.app')
@section('titulo','Stock de repuestos')

@section('contenido_titulo_pagina')

    <h4 class="titulazo">Stock de repuestos</h4>

  
    
  
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

      function buscar_stock_repuesto(){

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
                $('#mensajes').append('Buscando ...');
                espere('Buscando ...');
              },
              success: function(resp){
                
                Vue.swal.close();
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
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                }else{
                  
                 resp[0].forEach(element => {
                  if(element.local_id == 1 && element.local_id_dos == 3){
                    local = 'Bodega';
                    local_dos = 'Tienda';
                  }else if(element.local_id_dos == 3 && element.local_id == 1){
                    local = 'Tienda';
                    local_dos = 'Bodega';
                  }else if(element.local_id == 3){
                    local = 'Tienda';
                    local_dos = 'Bodega';
                  }else if(element.local_id_dos == null){
                    local = 'Bodega';
                    local_dos = 'null';
                  }

                  
                     if(element.stock_actual < 10){
                      
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                          <td><img src="/storage/`+resp[1].urlfoto+`" alt="foto repuesto" class="imagen_pequeña"></td>
                            <td ><a href="`+url+`" target="_blank" style="color:black">`+element.codigo_interno+`</a></td>
                            
                            <td scope="row" style="width: 20%" >`+element.descripcion+`</td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable+`")' data-target="#modal_modificar_stock">M</button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td><span id="stock_dos-`+element.id+`">`+element.stock_actual_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_dos+`")' data-target="#modal_modificar_stock_dos">M </button></td>
                            <td><span id="ubicacion_dos-`+element.id+`">`+element.ubicacion_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M</button></td>
                            <td><span id="stock_tres-`+element.id+`">`+element.stock_actual_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_tres+`")' data-target="#modal_modificar_stock_tres">M </button></td>
                            <td><span id="ubicacion_tres-`+element.id+`">`+element.ubicacion_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_tres+`")' data-target="#modal_modificar_ubicacion_tres">M</button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            
                        </tr>
                 `); 
                     }else{
                      console.log('stock 2 menore qu 10');
                       if(element.stock_actual_dos < 10){
                         let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                          <td><img src="/storage/`+resp[1].urlfoto+`" alt="foto repuesto" class="imagen_pequeña"></td>
                            <td><a href="`+url+`" target="_blank" style="color:black">`+element.codigo_interno+`</a></td>
                            
                            <td style="width: 20%">`+element.descripcion+`</td>
                            <td><span id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable+`")' data-target="#modal_modificar_stock">M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td><span class="stock_bajo" id="stock_dos-`+element.id+`">`+element.stock_actual_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_dos+`")' data-target="#modal_modificar_stock_dos">M </button></td>
                            <td><span id="ubicacion_dos-`+element.id+`">`+element.ubicacion_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td><span id="stock_tres-`+element.id+`">`+element.stock_actual_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_tres+`")' data-target="#modal_modificar_stock_tres">M </button></td>
                            <td><span id="ubicacion_tres-`+element.id+`">`+element.ubicacion_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_tres+`")' data-target="#modal_modificar_ubicacion_tres">M</button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            
                        </tr>
                 `); 
                       }else{
                        console.log('sin stock en las dos primeras ubicaciones');
                        let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                          <td><img src="/storage/`+resp[1].urlfoto+`" alt="foto repuesto" class="imagen_pequeña"></td>
                            <td><a href="`+url+`" target="_blank" style="color:black">`+element.codigo_interno+`</a></td>
                            
                            <td style="width: 20%">`+element.descripcion+`</td>
                            <td><span id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" >M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td><span id="stock_dos-`+element.id+`">`+element.stock_actual_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_dos+`")' data-target="#modal_modificar_stock_dos">M </button></td>
                            <td><span id="ubicacion_dos-`+element.id+`">`+element.ubicacion_dos+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td><span id="stock_tres-`+element.id+`">`+element.stock_actual_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`","`+element.nombre_responsable_tres+`")' data-target="#modal_modificar_stock_tres">M </button></td>
                            <td><span id="ubicacion_tres-`+element.id+`">`+element.ubicacion_tres+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_tres(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_tres+`")' data-target="#modal_modificar_ubicacion_tres">M</button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            
                        </tr>
                 `); 
                       }
                        
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
          });
      }

      function abrir_campos(id_repuesto,nombre_repuesto,marca,pais,responsable){
        
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
                    <input type="number" class="form-control" value="" id="nuevo_stock" min="0">
                    <input type="hidden" id="id_repuesto_stock" value="`+id_repuesto+`">
                </div>
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
          
      }

      function abrir_campos_dos(id_repuesto,nombre_repuesto,marca,pais,responsable){
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
                    <input type="number" class="form-control" value="" id="nuevo_stock_dos" min="0">
                    <input type="hidden" id="id_repuesto_stock_dos" value="`+id_repuesto+`">
                </div>
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function abrir_campos_tres(id_repuesto,nombre_repuesto,marca,pais,responsable){
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
                    <input type="number" class="form-control" value="" id="nuevo_stock_tres" min="0">
                    <input type="hidden" id="id_repuesto_stock_tres" value="`+id_repuesto+`">
                </div>
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function guardar_stock(){
          let id_repuesto = document.getElementById('id_repuesto_stock').value;
          let stock = document.getElementById('nuevo_stock').value;
          let idresponsable = document.getElementById('select_stock').value;
          let data = {stock: stock, id_repuesto: id_repuesto, idresponsable: idresponsable};
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
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
          
      }

      function guardar_stock_dos(){
        let id_repuesto = document.getElementById('id_repuesto_stock_dos').value;
          let stock = document.getElementById('nuevo_stock_dos').value;
          let idresponsable = document.getElementById('select_stock_dos').value;
          let opt = 'stock2';
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable};
          
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
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }
                    
                    
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
      }

      function guardar_stock_tres(){
        let id_repuesto = document.getElementById('id_repuesto_stock_tres').value;
          let stock = document.getElementById('nuevo_stock_tres').value;
          let idresponsable = document.getElementById('select_stock_tres').value;
          let opt = 'stock3';
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable};
          
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
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }
                    
                    
                },
                error: function(error){
                    console.log(error.responseText);
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
        .busqueda_principal{
            margin-top: 30px;
            border: 1px solid black;
            border-radius: 10px;
            background: #f2f4a9;
            padding: 20px;

        }

        .busqueda_resultado{
            border: 1px solid black;
            border-radius: 10px;
            margin-top: 30px;
            min-height: 200px;
            padding: 20px;
           
        }

        .campo_modificar{
            margin-top:30px;
            padding: 30px;
            border: 1px solid black;
            border-radius: 10px;
        }

        .logo{
            width: 100px;
            border-radius: 10px;
        }

        .alert-danger{
            padding: 5px;
            border: 1px solid dotted;
        }

        #mensajes{
          
            background: #eee;
            padding: 10px;
        }

        .select-ubicacion{
            width: 100%;
        }

        .select-ubicacion select{
            width: 100%;
            margin-bottom: 2px;
        }

        .stock_bajo{
            background: red;
            color: white;
            padding: 4px;
            border-radius: 10px;
        }
        #tbody_resultados {
          font-size: 13px; 
        }

        

        .imagen_pequeña{
              width: 120px;
              
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
<div class="row" style="width: 100%">
  <div class="col-md-4" style="    background: rgb(207, 255, 255);">
    <h4>Últimos repuestos vendidos</h4>
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
  </div>
  <div class="col-md-4" style="    background: rgb(207, 255, 255);">
    <h4>Ultimas ventas</h4>
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
  </div>
  <div class="col-md-4" style="    background: rgb(207, 255, 255);">
    <h4>Ultimos repuestos ingresados</h4>
    <table class="table" style="font-size: 13px">
      <thead class="thead-dark">
        <tr>
          <th scope="col">Codigo interno</th>
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
  </div>
</div>

<button class="btn btn-success mt-2" type="button" id="btnNuevo" data-toggle="modal" data-target="#modalArticulos" data-keyboard="false" data-backdrop="static"><i class="fa fa-plus"></i> Nuevo Bulto</button>
@if(Auth::user()->rol->nombrerol == "Administrador") <a href="/ventas/detalle_ventas" class="btn btn-warning mt-2">Detalle </a> @endif
@if(Auth::user()->rol->nombrerol == "Administrador") <a href="/repuesto/actualizados" class="btn btn-secondary mt-2">Repuestos actualizados </a> @endif
<div class="busqueda_principal">
  
    <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" class="logo">
    <div class="row" style="width: 100%;">
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked>
                <label class="form-check-label" for="flexRadioDefault1">
                  Codigo interno
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
                  Codigo de proveedor
                </label>
              </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label for="codigo_repuesto">Ingrese el codigo del repuesto</label>
                <input type="text" class="form-control" name="codigo_repuesto" id="codigo_repuesto" placeholder="Ingrese codigo del repuesto" onkeyup="enter_press(event)">
            </div>
            <div class="form-group">
                <input type="submit" value="Buscar" class="btn btn-success btn-sm" onclick="buscar_stock_repuesto()">
            </div>
        </div>
    </div>
    
</div>

<div class="busqueda_resultado">
    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo">
    <table class="table" id="tabla_resultados">
        <thead class="thead-dark">
          <tr>
            <th scope="col">Imagen</th>
            <th scope="col" class="letra_pequeña">Código interno</th>
            
            <th scope="col" class="letra_pequeña">Descripción</th>
            <th scope="col" class="letra_pequeña">Stock</th>
            
            <th scope="col" class="letra_pequeña">Ubicación</th>
            <th scope="col" class="letra_pequeña">Stock 2</th>
            
            <th scope="col" class="letra_pequeña">Ubicación 2</th>
            <th scope="col" class="letra_pequeña">Stock 3</th>
            
            <th scope="col" class="letra_pequeña">Ubicación 3</th>
            <th scope="col" class="letra_pequeña" style="width: 100px;">Codigo de proveedor</th>
            <th scope="col" class="letra_pequeña">Marca</th>
            <th scope="col" class="letra_pequeña">Pais</th>
            
          </tr>
        </thead>
        <tbody id="tbody_resultados">
          
        </tbody>
      </table>
      
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