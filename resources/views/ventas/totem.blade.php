@extends('plantillas.app')
@section('javascript')
    <script type="text/javascript">

      let valorAnterior = 0; // Variable para almacenar el valor anterior
      let sonido = new Audio('/notificaciones/campana.mp3'); // Ruta al sonido en public/
      let sonando = false;
      

      setInterval(() => {
          let url = '/revisar_totem';
          
          $.ajax({
              type: 'get',
              url: url,
              beforeSend: function () {
                  console.log('Consultando...');
              },
              success: function (resp) {
                console.log(resp);
                  if (resp > valorAnterior) {
                      if (!sonando) {
                          sonando = true;
                          // sonido.loop = true; // para que siga sonando
                          sonido.play().catch(error => console.log('Error al reproducir sonido:', error));
                      }
                  } else {
                      if (sonando) {
                          sonido.pause();
                          sonido.currentTime = 0;
                          sonando = false;
                      }
                  }
                  
                  valorAnterior = resp; // Actualiza el valor anterior con el nuevo

                  let contenido = `
                      <a href='javascript:dame_solicitudes()'>
                          <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
                          <span class="badge badge-danger">${resp}</span>
                      </a>
                  `;

                  $('#campana').empty().append(contenido);
                  dame_solicitudes();
              },
              error: function (err) {
                  console.log(err.responseText);
              }
          });
      }, 5000);


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

        function dame_solicitudes(){
         
          let url = '/dame_ventas_totem';
          $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              $('#panel_informativo').html(resp);
            },
            error: function(err){
              console.log(err);
            }
          })
        }

        function cargar_venta(id_carrito){
          let url ='/dame_venta_totem/'+id_carrito;
          $.ajax({
            type:'get',
            url: url,
            success: function(resp){
                console.log(resp);
                $('#resultado_repuestos_devolucion').empty();
                $('#resultado_repuestos_devolucion').append(resp);
                dame_solicitudes();
            },
            error: function(error){
              console.log(error.responseText);
            }
          })
        }

        function procesar_traspaso_repuesto(repuesto,solicitud){
      
          Vue.swal({ 
            title: '¿Estás seguro?',
            text: repuesto.descripcion+' se descontarán '+solicitud.cantidad+' del stock de bodega',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, transferir!'
          }).then((result) => {
            if (result.isConfirmed) {
              let url = '/procesar_solicitud';
              let data = {'id_repuesto': repuesto.id,'cantidad': solicitud.cantidad,'id_solicitud': solicitud.id};
              $.ajaxSetup({
                  headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });
              $.ajax({
                type:'post',
                data: data,
                url: url,
                success: function(resp){
                  console.log(resp);
                  
                  Vue.swal.fire(
                    'Transferido!',
                    resp.descripcion+' ha sido transferido a tienda.',
                    'success'
                  )
                  dame_solicitudes();
                },
                error: function(err){

                }
              })
              
            }
          });
        }
        

        function eliminar_traspaso_repuesto(id_solicitud){
          Vue.swal({ 
            title: '¿Estás seguro?',
            text: "No se podrá procesar la solicitud",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
          }).then((result) => {
            if (result.isConfirmed) {
              let url = '/eliminar_solicitud/'+id_solicitud;
              $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                  $('#panel_informativo').html(resp);
                  Vue.swal.fire(
                    'Eliminado!',
                    'La solicitud ha sido eliminada',
                    'success'
                  )
                },
                error: function(err){
                  console.log(err.responseText);
                }
              })
              
            }else{
              espere("La solicitud no se ha eliminado");
            }
          });
        }

        function cargar_devolucion(num_nc){
          let url ='/guiadespacho/dame_devoluciones/'+num_nc;
          $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              $('#resultado_repuestos_devolucion').empty();
              $('#resultado_repuestos_devolucion').append(resp);
            },
            error: function(error){
              console.log(error.responseText);
            }
          })
        }

        function opciones_devolucion(repuesto_id, cantidad, local_id,opcion){

          let num_nc = $('#num_nc').val();
          
          let data = {
            repuesto_id: repuesto_id,
            cantidad: cantidad,
            local_id: local_id,
            num_nc: num_nc,
            opcion: opcion
          };

          let url = '/guiadespacho/opciones_devolucion';
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
              console.log(resp);
              $('#resultado_repuestos_devolucion').empty();
              $('#resultado_repuestos_devolucion').append(resp);
            },
            error: function(error){
              console.log(error.responseText);
            }
          })

        }

        function buscar_devolucion(){
         let num_nc = $('#num_devolucion').val();
         if(num_nc.trim() == 0 || num_nc == ''){
          Vue.swal({
            icon:'error',
            text:'Debe ingresar un numero de nota de crédito'
          });
          return false;
         }
         let url = '/guiadespacho/historial_devolucion/'+num_nc;

         $.ajax({
          type:'get',
          url: url,
          beforeSend: function(){
            Vue.swal({
              icon:'info',
              text:'ESPERE ...'
            });
          },
          success: function(resp){
            Vue.swal.close();
            $('#panel_resultado_solicitudes').empty();
            $('#panel_resultado_solicitudes').append(resp);
          },
          error: function(error){
            console.log(error.responseText);
          }
         });
        }

        function borrar_carrito_transferido(cliente_id){
          Vue.swal({
            title: '¿Está seguro de eliminar el carrito?',
            text: "Esta acción elimina el carrito a la cajera.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI, ELIMINAR',
            cancelButtonText: 'NO, CANCELAR'
          }).then((result) => {
            if(result.isConfirmed){
              borrar_carrito_transferido_confirmar(cliente_id);
            }
          });
        }

        function borrar_carrito_transferido_confirmar(cliente_id){
          let url = '/ventas/borrar_carrito_cliente/'+cliente_id;
          $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
              console.log();
            },
            success: function(resp){
              console.log(resp);
              if(resp === 'OK'){
                dame_solicitudes();
              }
            }, error: function(err){
              Vue.swal({
                  icon:'error',
                  title:'Error',
                  text:err.responseText
                });
            }
          })
        }
    </script>
@endsection
@section('style')
<style>
      
      #panel_informativo
      {
        padding: 20px;
      }
      .avatar_cajero{
          width: 130px;
          height: 130px;
          border-radius: 100px;
          margin: 5px;
      }
      .info_bodeguero{
          background: #f2f4a9;
          border-radius: 10px;
          height: 150px;
          line-height: 100px;
          border: 1px solid black;
        }
      #campana img{
        border-radius: 20px;
      }

      .letra_pequeña{
        font-size: 13px;
      }

      #busqueda_devolucion{
        border: 1px solid black;
        min-height: 200px;
        display: block;
        border-radius: 10px;
      }
      
</style>
@endsection
@section('contenido_titulo_pagina')
  <div class="row titulazo" style="width: 100%;">
    <div class="col-sm-11" style="width:95%"><center><h4>Revisión Carritos transferidos</h4></center></div>
    <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

@section('contenido_ingresa_datos')
    
    <div class="container-fluid">
      <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_ mb-4 ml-3">
      <button type="button" class="btn btn-success btn-sm" id="activarSonido">Activar Sonido</button>
      <div id="info_bodeguero" class="info_bodeguero mt-3 letra_pequeña">
        <div class="row" style="width: 100%">
          <div class="col-md-3">
            <img src="{{url('usuarios/avatar/'.Auth::user()->image_path)}}" alt="" class="avatar_cajero" >
          </div>
          <div class="col-md-3">
            <span id="cajero_data">{{Auth::user()->name}}</span>
          </div>
          <div class="col-md-2">
            <span id="rol_data">{{Auth::user()->rol->nombrerol}}</span>
          </div>
          <div class="col-md-2">
            <span id="telefono_data">{{Auth::user()->telefono}}</span>
          </div>
          <div class="col-md-2">
            
            <span id="email_data">{{Auth::user()->email}}</span>
          </div>
        </div>
      </div>
      <div class="row " style="background: rgb(207, 255, 255) none repeat scroll 0% 0%;">
        
        <div class="col-md-6 my-5">
          <div id="panel_informativo">
            <div class="row">
              @foreach($carrito as $c)
                <div class="col-md-4 mb-2">
                  <div class="card">
                    <div class="card-header">
                      {{$c->id}} - {{$c->titulo}}
                    </div>
                    <div class="card-body">
                     
                      <p>{{$c->usuario_nombre}}</p>
                      <p>{{$c->fecha}} <br>{{$c->hora}}</p>
                      <p>{{$c->bodeguero_nombre}}</p>
                    </div>
                    <div class="card-footer">
                      @if($c->id_bodeguero == null || $c->id_bodeguero == Auth::user()->id)
                        <button class="btn btn-outline-success btn-sm" onclick="cargar_venta({{$c->cliente_id}})">Abrir</button>
                        <a href="javascript:borrar_carrito_transferido({{$c->cliente_id}})" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></a>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach 
            </div>
            
          </div>
        </div>
        <div class="col-md-6" id="resultado_repuestos_devolucion">

        </div>
      </div>
                
    </div>
    <script>
      // Permitir reproducción del sonido tras la interacción del usuario
      document.getElementById('activarSonido').addEventListener('click', function() {
          sonido.play().then(() => {
              sonido.pause(); // Se detiene de inmediato, solo desbloquea la reproducción futura
              sonido.currentTime = 0;
              console.log('Sonido activado');
          }).catch(error => console.log('Error activando sonido:', error));
      });
    </script>
@endsection