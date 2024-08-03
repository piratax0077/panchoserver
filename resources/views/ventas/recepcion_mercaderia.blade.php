@extends('plantillas.app')

@section('titulo','Recepción de mercadería')

@section('javascript')
<script>
    function buscar_solicitud(){
        let num_solicitud = $('#num_solicitud').val();
        if(num_solicitud.trim() == 0 || num_solicitud == ''){
            Vue.swal({
                icon:'error',
                title:'Error',
                text:'Debe ingresar un número de solicitud'
            });
        return false;
        }

        let url = '/guiadespacho/buscarsolicitud/'+num_solicitud;

        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'Cargando ...'
                });
            },
            success: function(html){
                Vue.swal.close();
               
                if(html == 'error'){
                    Vue.swal({
                        icon:'error',
                        title:'Posibles causas:',
                        html:'<li>No existen solicitudes con ese número</li> <li>Ya fué procesada</li> <li>Aún no termina la solicitud</li>'
                    });
                return false;
                }
                
              
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:error.responseText
                });
            }
        })

    }

    function aceptar_traspaso_repuesto(repuesto_id, solicitud_id,cantidad,locaciones){
        let params = {repuesto_id: repuesto_id, solicitud_id: solicitud_id,cantidad: cantidad,locaciones: locaciones};
        let url = '/guiadespacho/aceptar_traspaso_repuesto';
     
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(html){
                
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    text: error.responseText
                });
            }
        })
    }

    function rechazar_traspaso_repuesto(repuesto_id, solicitud_id){
        let params = {repuesto_id: repuesto_id, solicitud_id: solicitud_id};
        let url = '/guiadespacho/rechazar_traspaso_repuesto';
     
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(html){
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    text: error.responseText
                });
            }
        })
    }

    function resumen(){
        $.ajax({
            type:'get',
            url:'/guiadespacho/resumen',
            success: function(html){
                console.log(html);
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html[0]);
                $('#cantidad_traspasos_total').empty();
                $('#cantidad_traspasos_total').append('Cantidad: '+html[1]);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    title: error.responseText
                });
                return false;
            }
        })
      }

      function buscar_solicitud_fecha(){
        let fecha=document.getElementById("fecha").value;
        if(fecha.trim() == 0 || fecha == ''){
            Vue.swal({
                icon:'error',
                text:'Debe seleccionar una fecha',
                title: 'Error'
            });
        return false;
        }
        let url='{{url("/guiadespacho/detalle")}}'+'/'+fecha;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'Cargando ...'
                });
            },
            success:function(resp){
                Vue.swal.close();
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').html(resp[0]);
                $('#cantidad_traspasos').empty();
                $('#cantidad_traspasos').append('Cantidad: '+resp[1]);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });
      }

      function buscar__numero_solicitud(){
          let num_solicitud = $('#num_solicitud_busqueda').val();
          if(num_solicitud.trim() == 0 || num_solicitud == ''){
            Vue.swal({
                icon:'error',
                text:'Debe ingresar un numero de solicitud',
                title: 'Error'
                });
            return false;
        }
        let url='{{url("/guiadespacho/detalle_solicitud")}}'+'/'+num_solicitud;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'Cargando ...'
                });
            },
            success:function(resp){
                Vue.swal.close();
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });
      }

      function pendientes(){
          let url = '/guiadespacho/pendientes';
          $.ajax({
              type:'get',
              url: url,
              beforeSend(){

              },
              success: function(resp){
                Vue.swal.close();
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').html(resp);
              },
              error: function(error){
                console.log(error.responseText);
              }
          })
      }
</script>
@endsection

@section('style')
<style>
    .panel_busqueda{
            display: block;
            width: 100%;
            margin: 20px 0px 20px 0px;
            border: 1px solid black;
            padding: 20px;
            border-radius: 10px;
        }
    .cantidad_traspasos{
        float: right;
        font-style: italic;
        font-weight: bold;
    }
</style>
@endsection

@section('contenido_titulo_pagina')

  <h4 class="titulazo">Recepción de mercadería</h4>

@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
    <div class="row" style="width: 100%; ">
        <div class="col-md-4">
            <div class="panel_busqueda" style="background: #f2f4a9;">
                <div class="form-group">
                  <label for="num_solicitud">N° de solicitud</label>
                  <input type="text" class="form-control" placeholder="Ingrese N° de solicitud" id="num_solicitud">
                </div>
                <input type="button" value="Buscar" onclick="buscar_solicitud()" class="btn btn-success btn-sm">
                <input type="button" value="Resumen de solicitudes" class="btn btn-primary btn-sm" onclick="resumen()">
                <input type="button" value="Pendientes" class="btn btn-warning btn-sm" onclick="pendientes()">
                <p class="cantidad_traspasos" id="cantidad_traspasos_total"></p>
              </div>
              <div class="panel_busqueda" style="background: rgb(207, 255, 255);">
                <div class="form-group">
                    <label for="">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control">
                </div>
                <input type="button" value="Buscar" onclick="buscar_solicitud_fecha()" class="btn btn-success btn-sm">
                <p class="cantidad_traspasos" id="cantidad_traspasos"></p>
              </div>
              <div class="panel_busqueda" style="background: rgb(207, 255, 255);">
                <div class="form-group">
                    <label for="">N° de Solicitud</label>
                    <input type="text" name="num_solicitud_busqueda" id="num_solicitud_busqueda" class="form-control">
                </div>
                <input type="button" value="Buscar" onclick="buscar__numero_solicitud()" class="btn btn-success btn-sm">
              </div>
        </div>
        <div class="col-md-8">
            <div id="panel_resultado_solicitudes">
    
            </div>
        </div>
    </div>
</div>

@endsection

