@extends('plantillas.app')
  @section('titulo','Busqueda por medidas')
  
  @section('javascript')
    <script type="text/javascript">

function formatear_error(error){

let max=300;

let rpta=error.substring(0,max);

return rpta;

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

    }

    function buscar_medida(idfamilia){
      let id_familia_input = $('#id_familia_input').val(idfamilia);
  
      if(idfamilia == 0){
        Vue.swal({
          icon:'error',
          title:'Error',
          text:'Debe seleccionar alguna familia'
        });
        $('#resultado').empty();
        return false;
      }
      let url = '{{url("repuesto/repuestos_por_familia")}}'+'/'+idfamilia;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          Vue.swal({
            icon:'info',
            title:'Espere',
          });
        },
        success: function(html){
          Vue.swal.close();
          $('#resultado').empty();
          $('#resultado').append(html);
        },
        error: function(error){
          console.log(error);
        }
      })
    }

    function detalle_repuesto(codigo_interno){
      $('#detalle_repuesto').empty();
      $('#detalle_repuesto').append('<p>Detalle de '+codigo_interno+'  </p>');
    }

    function mas_detalle(id_repuesto){
      // $(window).scrollTop(0);
      // $("html, body").animate({ scrollTop: 0 }, "slow");
      //       return false;
      // });
      // go_to_top();
      edicion_repuesto(id_repuesto);
      dame_similares(id_repuesto);
      dame_oems(id_repuesto);
      dame_fabricantes(id_repuesto);
      dame_regulador_voltaje(id_repuesto);
    }

    function go_to_top(){
      $("html, body").animate({ scrollTop: 200 }, "slow");
    }

    function dame_similares(id_repuesto)
  {
      var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damesimilares';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        console.log('buscando ...');
          },
       url:url,
       success:function(similares){
        
          $("#zona_similares_r").html(similares);
          $('#zonaInfoSimilares_modal').html(similares);
       },
        error: function(error){
          $('#zona_similares').html(formatear_error(error.responseText));
        }
      });
  }

  function dame_regulador_voltaje(id_repuesto){
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damereguladorvoltaje';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        console.log('buscando ...');
          },
       url:url,
       success:function(rv){
        console.log(rv);
          $("#zona_regulador_voltaje").html(rv);
          $('#zonaInfoReguladorVoltaje_modal').html(rv);
       },
        error: function(error){
          $('#zona_regulador_voltaje').html(formatear_error(error.responseText));
          $('#zonaInfoReguladorVoltaje_modal').html(formatear_error(error.responseText));
        }
      });
  }

  function dame_oems(id_repuesto)
  {
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/dameoems';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        console.log('buscando ...');
      },
       url:url,
       success:function(oems){
          $("#zona_oem").html(oems);
          $('#zonaInfoOems_modal').html(oems);
       },
        error: function(error){
          $('#zona_oem').html(formatear_error(error.responseText));
          $('#zonaInfoOems_modal').html(formatear_error(error.responseText));
        }
      });
  }

    function agregar_carrito(id_rep){
      var cant = document.getElementById('cantidad').value;
      var idlocal = document.getElementById('idlocal').value;
      var stock_bodega = $('#stock-bodega').val();
      var stock_tienda = $('#stock-tienda').val();
      var stock_cm = $('#stock-cm').val();
      var idcliente=4;
      if(cant <= 0){
        Vue.swal({
            icon:'error',
            text:'Debe ingresar cantidad valida',
            position:'top-end',
            timer:3000,
            showConfirmButton: false,
            toast:true
          });
          return false;
      }
      
      if(idlocal == 1){
        console.log('bodega');
        if(cant > parseInt(stock_bodega)){
          Vue.swal({
            icon:'error',
            text:'Cantidad '+cant+' sobrepasa el stock',
            position:'top-end',
            timer:3000,
            showConfirmButton: false,
            toast:true
          });
          return false;
        }
      }else if(idlocal == 3){
        console.log('tienda');
        if(cant > parseInt(stock_tienda)){
          Vue.swal({
            icon:'error',
            text:'Cantidad '+cant+' sobrepasa el stock',
            position:'top-end',
            timer:3000,
            showConfirmButton: false,
            toast:true
          });
          return false;
        }
      }else{
        console.log('CASA MATRIZ');
        if(cant > parseInt(stock_cm)){
          Vue.swal({
            icon:'error',
            text:'Cantidad '+cant+' sobrepasa el stock',
            position:'top-end',
            timer:3000,
            showConfirmButton: false,
            toast:true
          });
          return false;
        }
      }
      if(cant.trim() == 0 || cant == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar una cantidad'
        });
        return false;
      }
      var parametros={idcliente:idcliente,idrep:id_rep,idlocal:idlocal,cantidad:cant, stock_bodega: stock_bodega, stock_tienda: stock_tienda};
      
      var url="{{url('ventas/agregar_carrito')}}";
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });
        $.ajax({
        type:'POST',
        beforeSend: function () {
          $("#mensajes-modal").html("Agregando al carro de compras...");
        },
        url:url,
        data:parametros,
        success:function(resp){
          $('#masDetalleModal').modal('hide');
          if(resp == 'viejo'){
          $("#mensajes-modal").html("Upss!!!");
            Vue.swal({
                    text: 'Repuesto no actualizado',
                    position: 'center',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          return false;
         }
          if(resp=="existe")
          {
            $("#mensajes-modal").html("Upss!!!");
            Vue.swal({
                    text: 'Repuesto ya esta en el carrito',
                    position: 'center',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
            let t=new Intl.NumberFormat('es-CL').format(resp);
            $("#mensajes-modal").html("Total: "+t);
            $('#edicion_repuesto').html("Total: "+t);
            // dame_carrito();
            // clic_en(1);
           
            Vue.swal({
                text: 'Agregado',
                position: 'center',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
           
          }
            },
        error: function(error){
            Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error'
            });
            }
          });
    }

    function busqueda_rapida(){
      let medida = $('#medida_busqueda').val();
      let idfamilia = $('#id_familia_input').val();
      if(medida.trim() == 0 || medida == ''){
        Vue.swal({
          icon:'info',
          text:'Debe ingresar un codigo interno',
          position:'top-end',
          timer: 3000,
          showConfirmButton: false,
          toast: true
        });
        return false;
      }
      let url = '{{url("/repuesto/busqueda_rapida")}}'+'/'+medida+'/'+idfamilia;
      
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          Vue.swal({
            icon:'info',
            title:'Buscando'
          });
        },
        success: function(html){
          Vue.swal.close();
          $('#resultado').empty();
          $('#resultado').append(html);
        },
        error: function(error){
          Vue.swal({
            icon:'error',
            title:'Error',
            text: error.responseText
          })
        }
      })
    }

    function volver(){
      let idfamilia = $('#id_familia_input').val();
      buscar_medida(idfamilia);
      
    }

    function edicion_repuesto(id_rep){
     
      let url = '{{url("/repuesto/buscaridrep_html")}}'+'/'+id_rep;
      $.ajax({
        type:'get',
        url: url,
        success: function(html){
          console.log(html);
          // $('#edicion_repuesto').html(html);
          $('#zonaInfoRepuesto_modal').html(html);
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
      
    }

        function busqueda_repuestos(){
          let medida = document.getElementById('medida').value;
          if(!medida){
            espere('Debe ingresar una medida');
          }else{
              let url = '/repuesto/buscar-medida/'+medida;
              $.ajax({
                  type:'get',
                  url: url,
                  success: function(resp){
                    Vue.swal.close();
                    $('#tbl').empty();
                    $('#tbl').append(resp);
                  },
                  error: function(error){
                    console.log(error);
                  }
              })
            espere('buscando ...');
          }
        }
        
        function enter_press(e)

    {

      var keycode = e.keyCode;

      if(keycode=='13')

      {

        busqueda_repuestos();

      }

    }


  function dame_fabricantes(id_repuesto)
{
  var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefabricantes';
    $.ajax({
     type:'GET',
     beforeSend: function () {
      
        },
     url:url,
     success:function(fabs){
      
      $("#zona_fab_r").html(fabs);
      $('#zonaInfoFab_modal').html(fabs);
     },
      error: function(error){
        $('#zona_fab').html(formatear_error(error.responseText));
        $('#zonaInfoFab_modal').html(formatear_error(error.responseText));
      }



    }); //Fin petición

}
    </script>

  @endsection
  @section('style')
    <style>
      #detalle_repuesto, #resultado{
          /* font-family: 'Arial Narrow'; */
          font-size: 15px;
      }

      #logo_central{
        text-align: center;
      }
      #logo_central img{
        width: 250px;
        border-radius: 10px;
      }
    </style>
  @endsection
  @section('contenido_ver_datos')
  <h4 class="titulazo">Busqueda por medidas</h4>
 <div class="container-fluid">
   
   <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_ mb-4 ml-3">
   <div id="mensajes-modal"></div>
  <div id="busqueda_medidas" style="margin: 30px;">
    
    <div class="row">
                <div class="col-md-2 p-3" style="background: #f2f4a9;">
                    {{-- <input type="text" name="medida" id="medida" onkeyup="enter_press(event,'d')" placeholder="Ingrese medida" class="form-control"> --}}
                    <label for="familia">Familias:</label>
                    <select name="familia" id="familia" class="form-control" onchange="buscar_medida(this.value)">
                      <option value="0">Seleccione familia</option>
                      @foreach($familias as $familia)
                        <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                      @endforeach
                    </select>
                    <div id="edicion_repuesto">

                    </div>
                  </div>
                <div class="col-md-6 p-3" style="background: #cfffff;">
                    <div id="resultado">

                    </div>
                </div>
                <div class="col-md-4 p-3" style="background: #f2f4a9;">
                  
                  <div id="detalle_repuesto">
                    <div id="zona_similares_r">

                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div id="zona_oem" >

                        </div>
                      </div>
                      <div class="col-md-8">
                        <div id="zona_fab_r">

                        </div>
                      </div>
                    </div>
                    <div id="zona_regulador_voltaje">

                    </div>
                    
                  </div>
                </div>
    </div>
    
</div>

 </div>
    <!-- Datos de vital importancia -->
    <input type="hidden" name="" id="id_familia_input" value="">
  @endsection