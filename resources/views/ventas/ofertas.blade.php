@extends('plantillas.app')

@section('titulo','Ofertas Pagina web')

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
              url:'/ventas/damerepuesto_oferta',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append('Buscando ...');
                
                espere('Buscando ...');
              },
              success: function(resp){
                
                Vue.swal.close();
                
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                    $('#busqueda_resultado').removeClass('d-block');
                    $('#busqueda_resultado').addClass('d-none');
                    $('#detalle_descuento').removeClass('d-block');
                    $('#detalle_descuento').addClass('d-none');
                    $('#detalle_descuentos_familia').removeClass('d-block');
                    $('#detalle_descuentos_familia').addClass('d-none');
                }else{
                  console.log(resp);
                $('#busqueda_resultado').removeClass('d-none');
                $('#busqueda_resultado').addClass('d-block');
                $('#detalle_descuento').removeClass('d-none');
                $('#detalle_descuento').addClass('d-block');
                $('#detalle_descuentos_familia').removeClass('d-block');
                $('#detalle_descuentos_familia').addClass('d-none');
                $('#mensajes').empty();
                $('#mensajes').append('Listo');
                $('#tbody_resultados').empty();
                 resp[0].forEach(element => {
                    $('#precio_repuesto').val(element.precio_venta);
                      $('#id_repuesto').val(element.id);
                      let url = "/repuesto/modificar/"+element.id;
                        $('#tbody_resultados').append(`
                        <tr>
                          <td width="20%"><img src="/storage/`+resp[1].urlfoto+`" alt="foto repuesto" class="imagen_pequeña"></td>                            
                            <td scope="row" style="width: 20%" >`+element.descripcion+`</td>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+element.stock_actual+`</span></td>
                            <td><span id="ubicacion-`+element.id+`">$ `+Number(element.precio_venta).toFixed(0)+`</span></td>
                            
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td> </td>
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

    function editar_oferta(id_oferta){
      $('#id_oferta').val(id_oferta);
      let url = '/ventas/dameoferta/'+id_oferta;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          $('#tbody_info_repuesto').empty();
          $('#tbody_info_repuesto').append('CARGANDO ...');
        },
        success: function(resp){
          console.log(resp);
          let precio_format = Number(resp.precio_venta).toFixed(0);
          $('#tbody_info_repuesto').empty();

         $('#fecha_inicial_oferta').empty();
         $('#fecha_final_oferta').empty();
         $('#tbody_info_repuesto').append(`
         <tr>
          <td>`+resp.codigo_interno+` </td>
          <td>`+resp.descripcion+` </td>
          <td>$ `+commaSeparateNumber(precio_format)+` </td>
          <td>$ `+commaSeparateNumber(resp.precio_actualizado)+` </td>
          </tr>
         `);
         $('#fecha_inicial_oferta').val(resp.desde);
         $('#fecha_final_oferta').val(resp.hasta);
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
    }

    function aplicar_descuento(){
        let descuento = $('#descuento').val();
        if(descuento === '' || descuento.trim() == 0){
            Vue.swal({
                icon:'error',
                text:'Debe ingresar un valor'
            });
            return false;
        }
        let precio_repuesto = parseInt($('#precio_repuesto').val());
       if(parseInt(descuento) > parseInt(precio_repuesto)){
        Vue.swal({
          icon:'error',
          text:'El valor a descontar no puede ser mayor al precio del repuesto'
        });
        return false;
       }
      
        let precio_final = precio_repuesto - descuento;
        $('#precio_actualizado').val(precio_final);
        $('#btn_ofertar').removeAttr('disabled');
        
    }

    function ofertar_repuesto(idrepuesto){
        let precio_antiguo = parseInt($('#precio_repuesto').val());
        let precio_actualizado = parseInt($('#precio_actualizado').val());
        let desde = $('#desde').val();
        let hasta = $('#hasta').val();
        let id_repuesto = $('#id_repuesto').val();
        let descuento = parseInt($('#descuento').val());
        if(descuento === '' || descuento == 0){
            Vue.swal({
                icon:'error',
                text:'Debe ingresar un valor a descontar'
            });
            return false;
        }

        if(desde === '' ){
            Vue.swal({
                icon:'error',
                text:'Debe ingresar una fecha inicial'
            });
            return false;
        }

        if(hasta === '' ){
            Vue.swal({
                icon:'error',
                text:'Debe ingresar una fecha final'
            });
            return false;
        }

        let descuento_formateado = descuento / 100;
        let data = {
            precio_antiguo: precio_antiguo,
            precio_actualizado: precio_actualizado,
            id_repuesto: id_repuesto,
            descuento: descuento,
            desde: desde,
            hasta: hasta
        }
   
        $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
          $.ajax({
              type:'POST',
              url:'/ventas/aplicar_descuento',
              data: data,
              beforeSend: function(){
                $('#mensajes').empty();
                $('#mensajes').append('Buscando ...');
                espere('Buscando ...');
              },
              success: function(resp){
                
                Vue.swal.close();
                console.log(resp);
                let ofertas = resp[1];
                if(resp[0] == 'OK'){
                  Vue.swal({
                    icon:'success',
                    text:'Oferta aplicada correctamente'
                  });
                   setTimeout(() => {
                    // Simulate a mouse click:
                    window.location.href = "/ventas/ofertas";
                   },1000);
                }
               
                
              },
              error: function(err){
                  Vue.swal({
                      title:'Error!',
                      text:err.responseText,
                      icon:'error'
                    });
              }
          });
    }

    function eliminar_oferta(id_oferta){
        let url = '/ventas/eliminar_oferta/'+id_oferta;
        $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              Vue.swal({
                icon:'success',
                text:'Oferta eliminada correctamente'
              });
              setTimeout(() => {
                  window.location.href = "/ventas/ofertas";
              }, 1000);
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function descuento_familia(){
      let url = '/familiascondescuento';
      $.ajax({
        type:'get',
        url:url,
        beforeSend: function(){
          Vue.swal({
            title:'Cargando',
            text:'Espere un momento ...',
            icon:'info',
            allowOutsideClick:false
          });
        },
        success: function(resp){
          Vue.swal.close();
          ocultar_resultado();
          $('#detalle_descuentos_familia').removeClass('d-none');
          $('#detalle_descuentos_familia').addClass('d-block');
          let familias = JSON.parse(resp[0]);
          let descuentos = resp[1];
          
          $('#familias').empty();
        
          $('#tbody_descuentos').empty();

          descuentos.forEach(d=> {
            console.log(d);
            var estado = d.activo == 1 ? 'Vigente' : 'Caducado';
            if(d.id_local == 1) var res = 'Local';
            if(d.id_local == 2) var res = 'WEB';
            if(d.id_local == 3) var res = 'Local y Web';
            $('#tbody_descuentos').append(`
            <tr>
              <td><img src="/storage/imagenes/familias/`+d.image_path+`" alt="imagen" class="logoHeader" /></td>
              <td><a href="javascript:void(0)" onclick="editar_descuento_familia(`+d.id+`)">`+d.nombrefamilia+`</a> </td>
              <td>`+d.porcentaje+` % </td>
              <td>`+d.desde+` </td>
              <td>`+d.hasta+` </td>
              <td>`+estado+`</td>
              <td>`+res+`</td>
              <td><button class="btn btn-danger btn-sm" onclick="eliminar_descuento(`+d.id+`)">Eliminar </button> </td>
            </tr> 
            `);
          });

          familias.forEach( f => {
            
            $('#familias').append(`
                <option value=`+f.id+`>`+f.nombrefamilia+` </option>
            `);
          });
          
          
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function soloNumeros(e)
    {
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
    }

    function confirmar_edicion_oferta(){
      let id_oferta = $('#id_oferta').val();
      let desde = $('#fecha_inicial_oferta').val();
      let hasta = $('#fecha_final_oferta').val();
      let url = '/ventas/confirmar_edicion_oferta';
      let params = {
        'desde': desde,
        'hasta': hasta,
        'id_oferta': id_oferta
      }

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
         
          if(resp == 'OK'){
            Vue.swal({
              icon:'success',
              title:'Oferta modificada'
            });
            setTimeout(() => {
              // Simulate a mouse click:
            window.location.href = "/ventas/ofertas";
            }, 1000);
            
          }else{
            console.log('HA OCURRIDO UN ERROR');
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
      });

    }

    function editar_descuento_familia(idfamilia){
      let url = '/ventas/editar_descuento_familia/'+idfamilia;
      $.ajax({
        type:'get',
        url:url,
        success: function(resp){
          
          $('#modal_editar_descuento_familia').modal('show');
         
          $('#modal_body_editar_descuento').empty();
          $('#modal_body_editar_descuento').append(resp);
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
      
    }

    function guardar_descuento(){
      let f = $('#familias').val();
      let porcentaje = $('#porcentaje').val();
      let fecha_inicio = $('#fecha_inicio').val();
      let fecha_fin = $('#fecha_fin').val();
      let local_id = $('#local').val();
      if(porcentaje == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar un porcentaje'
        });
      }

      if(fecha_inicio == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha inicial'
        });
      }

      if(fecha_fin == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha final'
        });
      }

      let data = {
        'id_familia': f,
        'porcentaje': porcentaje,
        'desde': fecha_inicio,
        'hasta': fecha_fin,
        'local_id': local_id
      }

     
      let url = "/ventas/guardar_dcto_familia";

      $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
      });

      $.ajax({
        type:'post',
        data: data,
        url: url,
        success: function(descuentos){
          console.log(descuentos);
          if(descuentos == 'error'){
            return Vue.swal({
                    icon:'error',
                    text:'Ya existe un descuento para esa familia'
                  });
          }
          $('#modalNuevoDescuentoFamilia').modal('hide');
          $('#tbody_descuentos').empty();

          descuentos.forEach(d=> {
            var estado = d.activo == 1 ? 'Vigente' : 'Caducado';
            if(d.id_local == 1) var res = 'Local';
            if(d.id_local == 2) var res = 'WEB';
            if(d.id_local == 3) var res = 'Local y Web';

            $('#tbody_descuentos').append(`
            <tr>
              <td><a href="javascript:void(0)" onclick="editar_descuento_familia(`+d.id+`)">`+d.nombrefamilia+`</a> </td>
              <td>`+d.porcentaje+` % </td>
              <td>`+d.desde+` </td>
              <td>`+d.hasta+` </td>
              <td>`+estado+` </td>
              <td>`+res+`</td>
              <td><button class="btn btn-danger btn-sm" onclick="eliminar_descuento(`+d.id+`)">Eliminar </button> </td>
            </tr> 
            `);
          });
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function eliminar_descuento(id_familia){
           
      let url = "/ventas/eliminar_descuento_familia/"+id_familia;

      $.ajax({
        type:'get',
        url: url,
        success: function(descuentos){
          console.log(descuentos);
          $('#tbody_descuentos').empty();

          descuentos.forEach(d=> {
            var estado = d.activo == 1 ? 'Vigente' : 'Caducado';
            if(d.id_local == 1) var res = 'Local';
            if(d.id_local == 2) var res = 'WEB';
            if(d.id_local == 3) var res = 'Local y Web';
            $('#tbody_descuentos').append(`
            <tr>
              <td><a href="javascript:void(0)" onclick="editar_descuento_familia(`+d.id+`)">`+d.nombrefamilia+`</a> </td>
              <td>`+d.porcentaje+` % </td>
              <td>`+d.desde+` </td>
              <td>`+d.hasta+` </td>
              <td>`+estado+`</td>
              <td>`+res+`</td>
              <td><button class="btn btn-danger btn-sm" onclick="eliminar_descuento(`+d.id+`,`+d.idfamilia+`)">Eliminar </button> </td>
            </tr> 
            `);
          });
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function editar_nueva_familia(){
      
      let porcentaje_nuevo = $('#porcentaje').val();
      let idfamilia = $('#idfamilia_editar').val();
      let fecha_inicio = $('#fecha_inicio').val();
      let fecha_fin = $('#fecha_fin').val();
      // obtener el valor del input de tipo file con id imagenReferencia
      let imagen = $('#referenciaImagen')[0].files[0];


      if(porcentaje_nuevo == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar un porcentaje',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(fecha_inicio == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha inicial',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(fecha_fin == ''){
        return Vue.swal({
          icon:'error',
          text:'Debe ingresar una fecha final',
          timer:1000,
          showConfirmButton:false,
          position:'top-right',
          toast:true
        });
      }

      if(imagen == undefined){
        // return Vue.swal({
        //   icon:'error',
        //   text:'Debe ingresar una imagen',
        //   timer:1000,
        //   showConfirmButton:false,
        //   position:'top-right',
        //   toast:true
        // });
      }
     
      let url = '/ventas/editar_nueva_familia';

      // preparar el objeto data para enviarle un archivo adjunto
      var data = new FormData();
      data.append('porcentaje_nuevo', porcentaje_nuevo);
      data.append('idfamilia', idfamilia);
      data.append('fecha_inicio', fecha_inicio);
      data.append('fecha_fin', fecha_fin);
      data.append('imagen', imagen);


      // enviar el objeto data el cual contiene una imagen mediante ajax
      $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
      });

      $.ajax({
        type:'post',
        url:url,
        data: data,
        contentType: false,
        processData: false,
        success: function(resp){
         console.log(resp);
          if(resp == 'ok'){
            $('#modal_editar_descuento_familia').modal('hide');
            Vue.swal({
              icon:'success',
              text:'Se ha modificado el descuento'
            });
            setTimeout(function(){
              descuento_familia();
            }, 1000);
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
      });

      
      
    }

    function ocultar_resultado(){
      $('#busqueda_resultado').removeClass('d-block');
      $('#busqueda_resultado').addClass('d-none');
      $('#detalle_descuento').removeClass('d-block');
      $('#detalle_descuento').addClass('d-none');
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
            margin-top: 30px;
            min-height: 200px;
            padding: 20px;
            border-radius: 10px;

            background: #f2f4a9;
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

        .imagen_pequeña{
            width: 120px;      
        }

        .modal-open {
            padding-right: 0px !important;
        }

        tr:hover{
          background: yellow;
        }

</style>
@endsection

@section('contenido')
<h3 class="titulazo">Ofertas</h3>
<article>Sección dedicada a ofertar productos para ser visualizados en el sitio web <a href="https://panchorepuestos.cl" target=”_blank”> panchorepuestos.cl</a>.</article>
<div class="container-fluid">
  <div class="row w-100">
    <div class="col-md-6">
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
                        <input type="submit" value="Buscar" class="btn btn-success btn-sm" onclick="buscar_repuesto()">
                        <input type="submit" value="Descuento a familia" class="btn btn-primary btn-sm" onclick="descuento_familia()">
                    </div>

                </div>
            </div>
            
        </div>
        @if(count($ofertas) > 0)
        
        <table class="table mt-3">
            <thead>
              <tr>
                <th scope="col">Cod Int</th>
                
                <th scope="col">Desde</th>
                <th scope="col">Hasta</th>
               
                <th scope="col">Descuento</th>
                <th scope="col">Precio Actualizado</th>
                <th scope="col">Estado</th>
                <th scope="col">Usuario</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody id="tbody_nuevas_ofertas">
                @foreach($ofertas as $oferta)
                @php
                  $desde = explode(" ", $oferta->desde);
                  $hasta = explode(" ", $oferta->hasta);

                  if($oferta->activo == 1){
                    $classname = 'bg-success text-white';
                  }else{
                    $classname = 'bg-danger text-white';
                  }
                @endphp
                  <tr class={{$classname}}>
                    <td>{{$oferta->codigo_interno}}</td>
                    
                    <td>{{ $oferta->desde}}</td>
                    <td>{{$oferta->hasta}}</td>
                    
                    <td>$ {{(number_format($oferta->descuento))}}</td>
                    <td>$ {{number_format($oferta->precio_actualizado)}}</td>
                    @if($oferta->activo == 1)
                    <td>Vigente</td>
                    @else
                    <td class={{$classname}}>Caducada</td>
                    @endif
                    <td>{{$oferta->name}}</td>
                    <td class="d-flex"><button class="btn btn-warning btn-sm" onclick="editar_oferta({{$oferta->id}})" data-toggle="modal" data-target="#exampleModal"><i class="fa-solid fa-pen-to-square"></i></button> <button class="btn btn-danger btn-sm" onclick="eliminar_oferta({{$oferta->id}})">X</button></td>
                    
                  </tr>
                @endforeach
              
            </tbody>
          </table>
        
        @else
        <p>No hay ofertas</p>
        @endif
    </div>
    <div class="col-md-6">
        <div class="busqueda_resultado d-none" id="busqueda_resultado">
           
            <table class="table" id="tabla_resultados">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Imagen</th>
                    <th scope="col" class="letra_pequeña">Descripción</th>
                    <th scope="col" class="letra_pequeña">Stock</th>
                    <th scope="col" class="letra_pequeña">Precio venta</th>
                    <th scope="col" class="letra_pequeña" style="width: 100px;">Codigo de proveedor</th>
                    <th scope="col" class="letra_pequeña">Marca</th>
                    <th scope="col" class="letra_pequeña">Pais</th>
                    <th scope="col" class="letra_pequeña"></th>
                  </tr>
                </thead>
                <tbody id="tbody_resultados">
                  
                </tbody>
              </table>
              
        </div>
        <hr>
        <div id="detalle_descuento" style="border: 1px solid black; border-radius: 10px;" class="d-none p-3">
            <h4>Detalle</h4>
            <div class="form-group">
                <label for="descuento">Valor a descontar</label>
                <input type="text" class="form-group" name="descuento" id="descuento" onkeypress="return soloNumeros(event)" onkeyup="soloNumeros(event)" >
                <button class="btn btn-success btn-sm" onclick="aplicar_descuento()">Aplicar</button>
            </div>
            <div class="form-group">
                <label for="precio_actualizado">Precio actualizado</label>
                <input type="text" class="form-group" name="precio_actualizado" id="precio_actualizado" readonly>
            </div>
            <div class="row form-group">
              <div class="col-md-6">
                <label for="desc">Desde:</label>
                <input type="date" name="desde" id="desde" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="desc">Hasta_</label>
                <input type="date" name="hasta" id="hasta" class="form-control">
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <button class="btn btn-danger btn-sm" id="btn_ofertar" onclick="ofertar_repuesto()" disabled>Ofertar </button>
              <button class="btn btn-sm btn-danger" onclick="ocultar_resultado()">Ocultar</button>
            </div>
            
        </div>
        <div id="detalle_descuentos_familia" class="d-none">
          <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNuevoDescuentoFamilia">Nuevo descuento por familia</button>
          <table class="table table-striped" style="font-size: 12px;">
            <thead>
              <tr>
                <th scope="col">Imagen Referencia</th>
                <th scope="col" style="width: 30px;">Nombre familia</th>
                <th scope="col">%</th>
                <th scope="col">Fecha inicio</th>
                <th scope="col">Fecha fín</th>
                <th scope="col">Estado</th>
                <th scope="col">Orientación</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody id="tbody_descuentos">
              
            </tbody>
          </table>
        </div>
    </div>
    
</div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modificar Oferta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Codigo interno</th>
              <th scope="col">Descripción</th>
              <th scope="col">Precio antiguo</th>
              <th scope="col">Precio actualizado</th>
            </tr>
          </thead>
          <tbody id="tbody_info_repuesto">
            
          </tbody>
        </table>
        <h3 class="text-center my-3">Modificar fechas</h3>
        <div class="row">
          
          <div class="col-md-6">
            <p class="text-center">Desde</p>
            <div class="form-group">
              <input type="date" class="form-control" id="fecha_inicial_oferta" />
              </div>
            </div>
            
            <div class="col-md-6">
              <p class="text-center">Hasta</p>
              <div class="form-group">
                <input type="date" class="form-control" id="fecha_final_oferta" />
                </div>
             </div> 
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal" onclick="confirmar_edicion_oferta()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal_editar_descuento_familia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar el descuento a familia</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_editar_descuento">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" onclick="editar_nueva_familia()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modalNuevoDescuentoFamilia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" id="modal_editar_descuento_repuesto_tamanio" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_editar_descuento_repuesto_titulo">Nuevo descuento a familia</h5>
        <button type="button" class="close" onclick="cerrar_modal_editar_descuento_repuesto()" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_editar_descuento_repuesto">
        
        <table class="table">
          
          <tbody>
            <tr>
              <td>Familia</td>
              <td><select name="familias" id="familias" class="form-control"></select></td>
            </tr>
            <tr>
              <td>%</td>
              <td><input type="number" min="1" max="100" name="porcentaje" id="porcentaje" maxlength="2" class="form-control"></td>
            </tr>
            <tr>
              <td>Fecha Inicio</td>
              <td><input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"></td>
            </tr>
            <tr>
              <td>Fecha fín</td>
              <td><input type="date" name="fecha_fin" id="fecha_fin" class="form-control"></td>
            </tr>
            <tr>
              <td>Local</td>
              <td><select name="local" id="local" class="form-control">
                <option value="1">Solo Local</option>
                <option value="2">Solo Web</option>
                <option value="3">Local + Web</option>
              </select>
            </td>
            </tr>
          </tbody>
        </table>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" onclick="guardar_descuento()">Guardar</button>
      </div>
    </div>
  </div>
</div>

  <input type="hidden" name="precio_repuesto" id="precio_repuesto" value="">
  <input type="hidden" name="id_repuesto" id="id_repuesto" value="">
  <input type="hidden" name="id_oferta" id="id_oferta" value="">
  <input type="hidden" name="id_familia" value="">
@endsection