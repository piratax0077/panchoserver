<!-- www.layoutit.com -> http://bit.ly/2Yp2BK1 -->
@extends('plantillas.app')
  @section('titulo','Listar Facturas (Compras)')
  @section('javascript')
    <script type="text/javascript">
      function dameFacturasDelProveedor()
      {
        var idProveedor=document.getElementById("proveedor").value;
        var url_proveedor='{{url("compras")}}'+'/'+idProveedor+'/proveedor'; //petición

          $.ajax({
            type:'GET',
            beforeSend: function () {
              $("#mensajes").html("Obteniendo Facturas...");
            },
            url:url_proveedor,
            success:function(facturas){
              $("#listar_facturas").html(facturas);
              $("#mensajes").html("Listo...");
            },
            error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText
              $('#mensajes').html(errorMessage);
            }

          }); //Fin ajax

      }

      function dameFacturasPorProveedor(){
        var idProveedor=document.getElementById("proveedores").value;
        var url_proveedor='{{url("compras")}}'+'/'+idProveedor+'/proveedorjson'; //petición

          $.ajax({
            type:'GET',
            beforeSend: function () {
              $("#mensajes").html("Obteniendo Facturas...");
            },
            url:url_proveedor,
            success:function(facturas){
              // agregar el listado de facturas al select con id facturas
              $('#facturas').empty();
              facturas.forEach(factura => {
                
                $("#facturas").append('<option value="'+factura.id+'">'+factura.factura_numero+'</option>');
              });
              
              // $("#listar_facturas").html(facturas);
              // $("#mensajes").html("Listo...");
            },
            error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText
              $('#mensajes').html(errorMessage);
            }

          }); //Fin ajax
      }

      function dameFactura(idFactura)
      {
        var url_factura='{{url("compras")}}'+'/'+idFactura+'/damefactura'; //petición
        //guardamos el id de la factura en un campo oculto
        document.getElementById("idfactura").value=idFactura;

          $.ajax({
            type:'GET',
            beforeSend: function () {
              $("#mensajes").html("Obteniendo Factura...");
            },
            url:url_factura,
            success:function(factura){
              $("#factura").html(factura);
              $("#mensajes").html("Se muestra factura...");
            },
            error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText
              $('#mensajes').html(errorMessage);
            }

          }); //Fin ajax

      }

      function dameFacturaCab(){
        
        var idFactura = document.getElementById("idfactura").value;
        var url_factura='{{url("compras")}}'+'/'+idFactura+'/damefacturacab'; //petición

          $.ajax({
            type:'GET',
            beforeSend: function () {
              $("#mensajes").html("Obteniendo Factura...");
            },
            url:url_factura,
            success:function(factura){
              console.log(factura);
              $("#tabla_factura").html(factura);
              $("#mensajes").html("Se muestra factura...");
            },
            error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText
              $('#mensajes').html(errorMessage);
            }

          }); //Fin ajax
      }


      function pagada(idFactura){
        let url = '{{url("compras")}}'+'/pagar_factura';
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
                return false; //sale del bucle jquery paguitos.each
            }

            id_forma_pago.push($(this).val());
            monto_forma_pago.push(monto);
            referencia_forma_pago.push(referencia_pago);
        }); // fin de paguitos

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
            return false;
        }

        let parametros={
          idfactura: idFactura,
          forma_pago:id_forma_pago,
          monto:monto_forma_pago,
          referencia:referencia_forma_pago,
          };
          console.log(error_pago);
          

          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });
        $.ajax({
            type:'POST',
            data: parametros,
            beforeSend: function () {
              $("#mensajes").html("Pagando Factura...");
            },
            url:url,
            success:function(resp){
              
              if(resp[0] == 'OK'){
                
                  Vue.swal({
                    icon:'success',
                    text:'Factura pagada',
                    position:'top-end',
                    timer: 3000,
                    toast: true,
                    showConfirmButton: false,
                  });
                  $('#idpagada').empty();
                  $('#idfactura_lista-'+resp[1]).empty();

                  $('#idpagada').append('<span class="badge badge-success">Pagada</span>');
                  $('#idfactura_lista-'+resp[1]).append('<span class="badge badge-success">Pagada</span>');
                  $("#formCabFac").modal('hide');
               $("#mensajes").html('PAGADA');
              }
              
            },
            error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText
              $('#mensajes').html(errorMessage);
            }

          }); //Fin ajax
      }

      function calcular_sumatoria()
      {
        var total_pago=0;
        var paguitos=$('input[name="forma_pago"]:checked');
        var monto=0;
        var m="";

        paguitos.each(function() {
          m="monto-"+$(this).val();
          monto=Number(document.getElementById(m).value.replace(/\./g,""));
          
          total_pago=total_pago+monto;
          
          
        });
        return total_pago;
      }

      function total_pagado()
        {
          var total_pago=calcular_sumatoria();
          var valor_total_factura = document.getElementById("valor_total_factura").value;
          
          let total_carrito=Number(document.getElementById("valor_total_factura").value);
          let total_pagado=Number(total_pago);
          
          let diferencia=Math.abs(total_pagado-total_carrito);
    
          if(diferencia>2)
          {
            return "SI";
          }else{
            return "NO"
          }

        }

        function dame_utilidad(){
          // campos id y porcentaje de la tabla familias
          var idFamilia=document.getElementById("familia").value;
          if(idFamilia=="0" || idFamilia.trim().length==0)
          {
              return false;
          }

          //Petición AJAX para obtener el porcentaje respectivo

          var url_familia='{{url("factuprodu")}}'+'/'+idFamilia+'/utilidad'; //petición

          $.ajax({
            type:'GET',
            beforeSend: function () {
              //$("#mensajes").html("Obteniendo Utilidad...");
            },
            url:url_familia,
            success:function(utilidad){
              console.log(utilidad);
              document.getElementById("utilidad").value=utilidad;
            },
            error: function(error){
              Vue.swal.close();
              $('#mensajes').html(error.responseText);
              Vue.swal({



                    title: 'ERROR',



                    text: error.responseText,



                    icon: 'error',



                });



            }







          }); //Fin ajax dameUtilidad


        }

        function calcular_precio_sugerido(){
          var fle=document.getElementById("flete").value;
          var flete=0;

          if(isNaN(fle) || Number(fle)<0 || fle.length==0 )
          {
              document.getElementById("flete").value=0;
          }else{
              flete=parseInt(fle);

          }

          var pu=parseInt(document.getElementById("nuevo_pu").value);
          var iva=0.19; // Leer de la tabla parámetros
          var utilidad=parseFloat(document.getElementById("utilidad").value)/100.0;
          var ps=document.getElementById("nuevo_ps");
          var elvalor=pu*(1+utilidad)*(1+iva)+flete;

          
          if(flete==0 && ps.value>0) //calcula el flete
          {
              flete=ps.value-elvalor;
              document.getElementById("flete").value=flete;
          }
          Vue.swal({
            icon:'success',
            text:'Precio sugerido actualizado correctamente',
            position:'top-end',
            timer: 3000,
            toast: true,
            showConfirmButton: false,
          });
        }

      function editar_item(id_repuesto, id_factura){
        let url = '{{url("compras/dame_item_factura")}}'+'/'+id_repuesto+'/'+id_factura;
        
        $.get({
          type:'get',
          url: url,
          beforeSend: function(){
            $('#modal_body_repuesto_factura').empty();
            $('#modal_body_repuesto_factura').append('CARGANDO ...');
          },
          success: function(resp){
            
            $('#modal_body_repuesto_factura').empty();
            $('#modal_body_repuesto_factura').append(resp);
          },
          error: function(error){
            console.log(error.responseText);
          }
        });
      }

      function activar_forma_pago(id){
        let marca=document.getElementById("formita-"+id).checked;
        document.getElementById("monto-"+id).disabled=!marca;
        document.getElementById("referencia-"+id).disabled=!marca;
      }

      function dameFormasPago(){
        let url = '{{url("ventas/dame_forma_pago")}}';
       
        $.get({
          type:'get',
          url: url,
          success: function(resp){
            $('#formasPago').empty();
            $('#formasPago').append(resp);
          },
          error: function(error){
            console.log(error.responseText);
          }
        });
      }

      function eliminar_factura(id_factura){
        
        Vue.swal({
        text: "¿ Desea ELIMINAR la factura ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CONTINUAR',
        cancelButtonText: 'CANCELAR'
        }).then((result) => {
        if (result.isConfirmed) {
          let url = '{{url("ventas/eliminar_factura")}}'+'/'+id_factura;
          $.get({
            type:'get',
            url: url,
            success: function(resp){
              
              if(resp == 'OK'){
                Vue.swal({
                  icon:'success',
                  text:'Factura eliminada correctamente',
                  toast: true,
                  showConfirmButton: false,
                  timer: 3000,
                  position:'top-end'
                });
                dameFacturasDelProveedor();
              }else{
                console.log(resp);
                return false;
              }
              
            },
            error: function(error){
              console.log(error.responseText);
            }
          });
        }else{
        }
        
      });
    }

        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido eliminado correctamente!!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }

        function actualizar_item_factura(){
          let factura_id = $('#factura_id').val();
          let repuesto_id = $('#repuesto_id').val();
          let nueva_cantidad = $('#nueva_cantidad_item').val();
          let nuevo_pu = $('#nuevo_pu').val();
          let nuevo_ps = $('#nuevo_ps').val();
          let flete = $('#flete').val();
          let nuevo_numero_factura = $('#nuevo_numero_factura').val();

          if(nuevo_numero_factura < 0 || isNaN(nuevo_numero_factura) || nuevo_numero_factura.trim() == 0){
            Vue.swal({
              icon:'info',
              text:'Escriba un número de factura correcto',
              
            });
            return false;
          }

          if(nueva_cantidad < 0 || isNaN(nueva_cantidad) || nueva_cantidad.trim() == 0){
            Vue.swal({
              icon:'info',
              text:'Escriba una cantidad correcta',
              
            });
            return false;
          }
          if(nuevo_pu < 0 || isNaN(nuevo_pu) || nuevo_pu.trim() == 0){
            Vue.swal({
              icon:'info',
              text:'Escriba una precio unitario correcto',
              
            });
            return false;
          }
          if(nuevo_ps < 0 || isNaN(nuevo_ps) || nuevo_ps.trim() == 0){
            Vue.swal({
              icon:'info',
              text:'Escriba un precio sugerido correcto',
              
            });
            return false;
          }
          var url="{{url('compras/actualizar_item_factura')}}";
          var parametros={
            factura_id:factura_id,
            repuesto_id:repuesto_id,
            nueva_cantidad:nueva_cantidad,
            nuevo_pu: nuevo_pu,
            nuevo_ps: nuevo_ps,
            flete: flete,
            nuevo_numero_factura: nuevo_numero_factura
          };
          console.log(parametros);
          
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
          });

        $.ajax({
        type:'POST',
        url:url,
        data:parametros,
        success:function(resp){
        console.log(resp);
          if(resp[0] == 'OK'){
            Vue.swal({
              icon:'success',
              text:'Item cambiado exitosamente',
              timer: 3000,
              toast: true,
              showConfirmButton: false
            });
            $("#modalEditarItem").modal('hide');
          }else{
            Vue.swal({
              icon:'error',
              text:'Ha ocurrido un error desconocido',
              position:'top-end',
              timer: 3000,
              toast: true,
              showConfirmButton: false,
            });
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
        });
          
        }

        function eliminar_repuesto(numero_factura,id_repuesto){
          Vue.swal({
            text: "¿ Desea ELIMINAR el repuesto de la factura "+numero_factura+" y de forma permanente ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'CONTINUAR',
            cancelButtonText: 'CANCELAR'
          }).then((result) => {
            if(result.isConfirmed){
              console.log(numero_factura, id_repuesto);
              let params = {numero_factura: numero_factura, id_repuesto: id_repuesto};
              let url = "{{url('compras/eliminar_repuesto_factura')}}";
              
              $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
              });

              $.ajax({
                type:'post',
                url: url,
                data: params,
                beforeSend:function(){
                  $("#mensajes").html("Obteniendo factura...");
                },
                success: function(factura){
                  
                  console.log(factura);
                  $("#factura").html(factura);
                  $("#mensajes").html("Se muestra factura...");
                },
                error: function(error){
                
                  $("#mensajes").html(error.responseText);
                }
              });
            }
          });
          
        }

        function moverProducto(idrep){
          console.log('moviendo producto '+idrep);
          $('#idrep').val(idrep);
          proveedor = $('#proveedor').val();
          dameFacturaCab();
        }

        function soloNumeros(event){
          var charCode = event.which ? event.which : event.keyCode;

          // Verificar si la tecla presionada es un número (códigos de tecla entre 48 y 57)
          if (charCode < 48 || charCode > 57) {
            return false;
          }
        }

        function confirmarMovimiento(){
          let idrep = $('#idrep').val();
          let proveedor = $('#proveedores').val();
          let factura_destino = $('#facturas').val();
          let factura_origen = $('#idfactura').val();

          // crear el objeto ajax
          let url = "{{url('compras/moveritem')}}";
          let params = {idrep: idrep, proveedor: proveedor, factura_destino: factura_destino, factura_origen: factura_origen};
          console.log(params);
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });
          
          $.ajax({
            type:'post',
            url: url,
            data: params,
            beforeSend:function(){
              $("#mensajes").html("Obteniendo factura...");
            },
            success: function(factura){
              $("#factura").html(factura);
              $("#mensajes").html("Se muestra factura...");
              Vue.swal({
                icon:'success',
                text:'Item movido exitosamente',
                timer: 3000,
                toast: true,
                showConfirmButton: false
              });
              // cerrar modal
              $('#modalMoverProducto').modal('hide');
            },
            error: function(error){
            
              $("#mensajes").html(error.responseText);
            }
          });
        }
    </script>

  @endsection
  @section('contenido_titulo_pagina')
<center><h2 class="titulazo">Listar Facturas (Compras)</h2></center><br>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
  
  <div class="row">
<!-- PANEL LATERAL IZQUIERDO -->
    <div class="col-4" style="background-color: #f2f4a9; border: 1px solid black; border-radius: 10px;">
      <div class="row">

        <div class="col-9">
        <label for="proveedor">Proveedor: </label>
          @if(!empty($proveedores) or $proveedores->count()>0)
          <select name="proveedor" class="form-control" id="proveedor">
            @foreach ($proveedores as $proveedor)
              <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
            @endforeach
          </select>
          @else
            <p>No hay proveedores registrados</p>
          @endif
        </div>
        <div class="col-3">
          <button class="btn btn-info" type="button" id="buscar_facturas" onclick="dameFacturasDelProveedor()" style="margin-top:25px">Buscar</button>
        </div>

      </div>
      <div class="row">
        <div class="col-11" id="listar_facturas"></div>
      </div>
    </div>

<!-- PANEL LATERAL DERECHO -->
    <div class="col-8" style="background-color: rgb(230, 242, 255);
    border: 1px solid black;
    border-radius: 10px;
    padding: 10px;">
      <div class="row" id="factura" style="margin:0px"></div>
    </div>
</div>
<div class="row" id="mensajes"></div>
</div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modalEditarItem" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logoHeader">
        <h5 class="modal-title" id="exampleModalLabel">Editar Item Factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_repuesto_factura">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" onclick="actualizar_item_factura()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMoverProducto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Mover producto entre facturas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="tabla_factura">

        </div>
        <div class="form-group">
          <label for="proveedores" class="form-label">Proveedores</label>
          <select name="proveedores" id="proveedores" class="form-control" onchange="dameFacturasPorProveedor()">
            @foreach ($proveedores as $proveedor)
              <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="facturas" class="form-label">Facturas</label>
          <select name="facturas" id="facturas" class="form-control">
            <option value="">Seleccione una factura</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="confirmarMovimiento()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!--DATOS DE VITAL IMPORTANCIA -->
<input type="hidden" name="idrep" id="idrep" value="">
<input type="hidden" name="idfactura" id="idfactura" value="">
@endsection

@section('contenido_ver_datos')

@endsection
