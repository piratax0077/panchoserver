@extends('plantillas.app')

@section('titulo','Devolución de mercadería')
@section('javascript')
<script type="text/javascript">


function cargar_documento(tipo_busqueda){
    if(tipo_busqueda == 1){
      let numero_nc = $('#numero_nc').val();
      if(numero_nc.trim() == 0 || numero_nc == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar un número de nota de crédito',
        });
        return false;
      }

      let url = '/guiadespacho/detalle_nc_nueva/'+numero_nc;
      $.ajax({
        type:'get',
        url: url,
        beforeSend:function(){
          Vue.swal({
            icon:'info',
            text:'CARGANDO'
          });
        },
        success: function(resp){
          
          Vue.swal.close();
          if(resp.error){
            Vue.swal({
              icon:'error',
              text: resp.error
            });
            $('#contenido').empty();
            return false;
          }
          //Desactivar boton de imprimir
          $('#btn-imprimir').attr('disabled','true');
          $('#contenido').empty();
          $('#contenido').append(resp);
        },
        error: function(error){
          Vue.swal({
            icon:'error',
            text:error.responseText
          });
          return false;
        }
      });
    }else{
      
      let numero_nc = $('#numero_doc').val();
      let tipo_doc = $('input[name="exampleRadios"]:checked').val();
      if(numero_nc.trim() == 0 || numero_nc == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar un número de boleta o factura',
        });
        return false;
      }

      let url = '/guiadespacho/detalle_doc_nuevo/'+numero_nc+'/'+tipo_doc;
      $.ajax({
        type:'get',
        url: url,
        beforeSend:function(){
          Vue.swal({
            icon:'info',
            text:'CARGANDO'
          });
        },
        success: function(resp){
          
          Vue.swal.close();
          
          if(resp.error){
            Vue.swal({
              icon:'error',
              text: resp.error
            });
            $('#contenido').empty();
            return false;
          }
          //Desactivar boton de imprimir
          $('#btn-imprimir').attr('disabled','true');
          $('#contenido').empty();
          $('#contenido').append(resp);
        },
        error: function(error){
          Vue.swal({
            icon:'error',
            text:error.responseText
          });
          return false;
        }
      });
    }
    
}

function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
  }


function confirmar_devolucion(repuesto_id){
  // Vue.swal({
  //   icon:'info',
  //   text:'TRABAJANDO ...'
  // });
  // return false;
  Vue.swal({
        title:'¿Estás seguro?',
        text:'Se DEVOLVERÁ el repuesto a la UBICACIÓN ESPECIFICADA',
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Devolver!'
      }).then((result) => {
        
        if(result.isConfirmed){
          // Vue.swal({
          //   icon:'info',
          //   text:'TRABANDO EN ESO ...'
          // });
          // return false;
          let cantidad = $('#cantidad-'+repuesto_id).val();
          let local_id = $('#local_id-'+repuesto_id).val();
          let merma = $('#merma-'+repuesto_id).val();

          if(parseInt(cantidad) < 0){
            Vue.swal({
              icon:'error',
              text:'No puede solicitar un valor negativo.'
            });
            return false;
          }
            
          if(parseInt(cantidad) > parseInt(merma)){
            Vue.swal({
              icon:'error',
              text:'No puede solicitar más de lo que se vendió.'
            });
            return false;
          }
          
          let num_nc = $('#num_nc').val();
          let tipo_doc = $('input[name="exampleRadios"]:checked').val();
          if(num_nc !== ''){
            
              var data = {
                'repuesto_id': repuesto_id,
                'cantidad': cantidad,
                'local_id' : local_id,
                'num_nc': num_nc,
                'tipo_doc':'nc'
              };
          }else{
          
              var data = {
                'repuesto_id': repuesto_id,
                'cantidad': cantidad,
                'local_id' : local_id,
                'num_nc': $('#numero_doc').val(),
                'tipo_doc':tipo_doc
              };
          }


          let url = '/guiadespacho/devolucion';

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
              
              Vue.swal.close();
              if(resp == 'error'){
                Vue.swal({
                  icon:'error',
                  text:'Repuesta ya esta ingresado a la devolución'
                });
                return false;
              }else{
                $('#table_devolucion_').empty();
                $('#table_devolucion_').append(resp);
              }
              
            
            },
            error: function(error){
              console.log(error);
            }
          });
        }
      });
  
}

function cerrar_devolucion(num_nc){

  let url;
  let num_doc;
  let tipo_doc;
  //Si el numero de nota de crédito no viene vacio se consulta por el numero de la nota de crédito
  if(num_nc === undefined){
    //Si no tiene número de nota de crédito se deduce que es una devolución por vale de mercadería.
   
    num_doc = $('#numero_doc').val();
    tipo_doc = $('input[name="exampleRadios"]:checked').val();
    //url especial diseñada para devolución de mercadería por vale de mercadería
    url = '/guiadespacho/cerrar_devolucion_/'+num_doc+'/'+tipo_doc;
    
  }else{
    //url normal diseñada para devolución de mercadería por nota de crédito
    url = '/guiadespacho/cerrar_devolucion/'+num_nc;
  }

  $.ajax({
    type:'get',
    url: url,
    success: function(resp){
      console.log(resp);
      if(resp[0] == 'OK'){
        $('#exampleModal').modal('show');
        $('#listado_repuestos_devolucion').empty();
        $('#listado_repuestos_devolucion').append(resp[1]);
        
        // setTimeout(() => {
        //   // funciona como una redirección HTTP
        //   window.location.replace("/guiadespacho/devolucion_mercaderia");
        // }, 1000);
      }else{
        Vue.swal({
          icon:'error',
          text:'No existen repuestos ingresados'
        });
      }
    },
    error: function(error){
      Vue.swal({
          icon:'error',
          text:error.responseText
        });
     
    }
  })

}

function imprimir_devolucion(num_nc){
       

  let url = '/imprimir_devolucion';
  let parametros ={num_nc: num_nc};
          
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

          $.ajax({
              type:'post',
              data:parametros,
              url:url,
              beforeSend:function(){
                Vue.swal({
                  icon:'info',
                  text:'ESPERE ...'
                });
              },
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
              error: function(error){
                Vue.swal({
                  icon:'error',
                  text:error.responseText
                });
              }
          });
}
</script>
@endsection

@section('style')
<style>
  

.col-sm-3, .col-sm-9{
  padding-right: 5px;
  padding-left: 5px;
}

.modal-body-alto {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 40px);
}

#info_devolucion{
  width: 400px;
  background: #eee;
}
</style>
@endsection

@section('contenido_titulo_pagina')

  <h4 class="titulazo">Devoluciones</h4>

@endsection
@section('mensajes')
  @include('fragm.mensajes')
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-6">
      <h5 class="text-center">Devolución por Nota de Crédito</h5>
      <div class="row p-3" style="background: rgb(242, 244, 169) none repeat scroll 0% 0%; border: 1px solid black; border-radius: 10px;">
        <div class="col-md-2"><img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_" style="width: 100%"></div>
        <div class="col-md-2 pt-2">
          <label for="" style="font-weight: bold;">N° de Nota de Crédito</label>
        </div>
        <div class="col-md-4" style="padding-top: 25px;">
          <input type="text" name="numero_nc" id="numero_nc" class="form-control" onKeyPress="return soloNumeros(event)">
        </div>
        <div class="col-md-2" style="padding-top: 25px;">
          <button class="btn btn-success btn-sm" onclick="cargar_documento(1)">Buscar</button>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <h5 class="text-center">Devolución por vale de mercadería</h5>
      <div class="row p-3" style="background: rgb(242, 244, 169) none repeat scroll 0% 0%; border: 1px solid black; border-radius: 10px;">
        <div class="col-md-2"><img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_" style="width: 100%"></div>
        <div class="col-md-4 d-flex mt-3">
          <div class="form-check">
              <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="bo" checked>
              <label class="form-check-label" for="exampleRadios1">
                Boleta
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="fa">
              <label class="form-check-label" for="exampleRadios2">
                Factura
              </label>
            </div>
      </div>
        <div class="col-md-4" style="padding-top: 25px;">
          
          <input type="text" name="numero_doc" id="numero_doc" class="form-control" onKeyPress="return soloNumeros(event)">
        </div>
        <div class="col-md-2" style="padding-top: 25px;">
          <button class="btn btn-success btn-sm" onclick="cargar_documento(2)">Buscar</button>
        </div>
      </div>
    </div>
  </div>
  

  

  <div id="contenido"  style="background: rgb(207, 255, 255) none repeat scroll 0% 0%; border-radius:10px;" >

  </div>
  
</div>
  
@endsection