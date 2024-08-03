@extends('plantillas.app')
@section('titulo','Cotizaciones')
@section('javascript')
<script>
    function cargar_cotizacion(num_cotizacion){
        var url="{{url('ventas/cargarcotizacion_bodega')}}"+"/"+num_cotizacion;
      $.ajax({
       type:'GET',
       url:url,
       success:function(resp){
        $('#resultado_repuestos_cotizacion').html(resp);
       },
        error: function(error){
            Vue.swal({
                title: 'ERROR',
                text: formatear_error(error.responseText),
                icon: 'error',
            });
      }

      });
    }

    function cambiar_estado(id, opcion){
      Vue.swal({
        title: '¿Estas seguro?',
        text: "No se podrá revertir esta acción",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
      }).then((result) => {
        if (result.isConfirmed) {
          var url="{{url('ventas/cambiar_estado_cotizacion')}}"+"/"+id+"/"+opcion;
        $.ajax({
          type:'GET',
          url:url,
          success:function(cotizaciones){
            
                $('#panel_informativo').empty();
                $('#panel_informativo').append(cotizaciones);
                $('#resultado_repuestos_cotizacion').empty();
          },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                });
          } 

        });
          Vue.swal({
            icon:'success',
            text:'Exito',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
          });
        }
      })

      return false;
        
    }
</script>
@endsection

@section('contenido_titulo_pagina')
  <div class="row titulazo" style="width: 100%;">
    <div class="col-sm-11" style="width:95%"><center><h4>Solicitudes de Cotizaciones</h4></center></div>
    <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-6 my-1">
            <div id="panel_informativo">

                <div class="row" style="width: 100%;">

                  @foreach($cotizaciones as $c)
                  <div class="col-md-3">
                    <div class="card mb-3">
                      <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="Card image cap" srcset="" class="card-img-top">
                      
                      <div class="card-body text-center">
                        <h5 class="card-title">{{$c->num_cotizacion}}</h5>
                        <div class="card-text letra_pequeña">
                            <p><a href="javascript:void(0)" onclick="cargar_cotizacion({{$c->num_cotizacion}})">{{$c->nombre_cotizacion}}</a> </p>
                            <p>{{$c->fecha}}</p>
                          </div>
                      </div>
                    </div>
                  </div>
                  
                  @endforeach

                </div>
              </div>
        </div>
        <div class="col-md-6" id="resultado_repuestos_cotizacion">

        </div>
      
    </div>
@endsection