@extends('plantillas.app')
@section('javascript')
    <script type="text/javascript">

        setInterval(() => {
            
            let url = '/revisar_solicitud';
            $.ajax({
              type:'get',
              url: url,
              beforeSend: function(){
                console.log('consultando');
              },
              success: function(resp){
                
                if(resp > 0){
                  $('#campana').empty();
                  $('#campana').append(`
                  <a href='javascript:dame_solicitudes()'>
                    <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
                    <span class="badge badge-danger">`+resp+` </span> 
                  </a>
                  `);
                }else{
                  $('#campana').empty();
                  $('#campana').append(`
                    <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
                    <span class='badge badge-danger'>`+resp+` </span> 
                  `);
                }
              },
              error: function(err){
                console.log(err.responseText);
              }
            })
        }, 15000);

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
         
          let url = '/dame_solicitudes';
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
    <div class="col-sm-11" style="width:95%"><center><h4>Solicitudes de Devolución de Mercadería</h4></center></div>
    <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

@section('contenido_ingresa_datos')
    
    <div class="container-fluid">
      <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_ mb-4 ml-3">
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
            @if($solicitudes->count() > 0)<h3>Documentos</h3>@endif
            <div class="row" style="width: 100%;">
              @if(count($solicitudes) > 0)
              @foreach($solicitudes as $solicitud)
              <div class="col-md-3">
                <div class="card mb-3">
                  <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="Card image cap" srcset="" class="card-img-top">
                  
                  <div class="card-body">
                    <h5 class="card-title"><a href="javascript:void(0)" onclick="cargar_devolucion({{$solicitud->num_nc}})">{{$solicitud->num_nc}}</a> </h5>
                    <div class="card-text">
                        
                      </div>
                  </div>
                </div>
              </div>
              
              @endforeach
              @else 
                <h3 class="alert-danger">No hay devoluciones de mercaderías</h3>
              @endif
            </div>
          </div>
        </div>
        <div class="col-md-6" id="resultado_repuestos_devolucion">

        </div>
      </div>
        
        <div id="busqueda_devolucion">
          <div class="row" style="width: 100%; ">
            
            <div class="col-md-5">
                <div class="panel_busqueda p-5" style="background: #f2f4a9; height: 100%;">
                    <div class="form-group">
                      <label for="num_solicitud">N° de Nota de Crédito</label>
                      <input type="text" class="form-control" placeholder="Ingrese N° de Nota de Crédito" id="num_devolucion">
                    </div>
                    <input type="button" value="Buscar" onclick="buscar_devolucion()" class="btn btn-success btn-sm">
                    
                  </div>
                  
            </div>
            <div class="col-md-7">
                <div id="panel_resultado_solicitudes">
        
                </div>
            </div>
        </div>
        </div>
        
    </div>
    
@endsection