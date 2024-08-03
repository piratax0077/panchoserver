@extends('plantillas.app')
@section('contenido_titulo_pagina')
<div class="titulazo">
    <h4>Ingresados vs Vendidos</h4>
  </div>
 @endsection
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
    }
    function enter_press(e)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            dame_info();
        }
    }
    function dame_info(){
        
        let codigo_repuesto = document.getElementById('codigo_repuesto').value;
          let opt = $('input:radio[name=flexRadioDefault]:checked').val();
          let desde = 'ingresados_vendidos';
          let data = {codigo_repuesto: codigo_repuesto, option: opt,desde: desde};
          
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
                let total_vendido = 0;
                let traspasos = resp[3];
                
                let total_stock = resp[2].stock_actual + resp[2].stock_actual_dos + resp[2].stock_actual_tres;
                if(resp[0] === "error"){
                    
                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                    return false;
                }
                
                if(resp[0].length == 0){
                    $('#ingresados').empty();
                    $('#ingresados').append(`
                        <p>No se ha agregado por factura </p>
                    `);
                }else{
                    let cantidad_ingresada = 0;
                    $('#ingresados').empty();
                    resp[0].forEach(item => {
                        let fecha = item.created_at;
                        
                        cantidad_ingresada += item.cantidad;
                        $('#ingresados').append(`
                        <tr>
                            <td scope="row">`+item.fecha+`</td>
                            <td>`+item.cantidad+`</td>
                        </tr>
                  `);
                    });
                    $('#ingresados').append(`
                    <tr>
                            <td scope="row"><strong>Total</strong></td>
                            <td><strong>`+cantidad_ingresada+`</strong></td>
                        </tr>
                    `);
                }
                if(resp[1].length == 0){
                    $('#vendidos').empty();
                    $('#vendidos').append(`
                        <p>No se han vendido productos </p>
                    `);
                }else{
                    $('#vendidos').empty();
                    resp[1].forEach(element => {
                        total_vendido += element.cantidad;
                        $('#vendidos').append(`
                        <tr>
                            <td class='text-center'>`+element.tipo_doc+`</td>
                            <td class='text-center'>`+element.num_doc+`</td>
                            <td class='text-center'>`+element.cantidad+`</td>
                            <td class='text-center'>`+element.fecha_emision+`</td>
                            <td class='text-center'>`+element.name+`</td>
                            <td class='text-center'> `+element.local_nombre+` </td>
                        </tr>
                        `);
                    });
                    
                    
                }
                if(traspasos.length == 0){
                    $('#traspasos').empty();
                    $('#traspasos').append(`
                        <p>No se han traspasado productos </p>
                    `);
                }else{
                    $('#traspasos').empty();
                    traspasos.forEach(element => {
                        let locaciones;
                        let estado;
                        if(element.locaciones == 1){
                            locaciones = "Bodega a Tienda";
                        }else if(element.locaciones == 2){
                            locaciones = "Bodega a Casa Matríz";
                        }else{
                            locaciones = "Casa Matríz a Tienda";
                        }

                        if(element.estado == 1){
                            estado = 'ACEPTADO';
                        }else if(element.estado == 0){
                            estado = 'RECHAZADO';
                        }else{
                            estado = 'ESPERANDO';
                        }
                        $('#traspasos').append(`
                        <tr>
                            <td>`+element.cantidad+`</td>
                            <td>`+element.fecha_emision+`</td>
                            <td> `+locaciones+` </td>
                            <td> `+estado+` </td>
                            <td>`+element.name+` </td>
                        </tr>
                        `);
                    });
                    
                    
                }
                $('#info_repuesto').empty();
                $('#info_repuesto').css('padding-top','10px');
                // dar formato de numero al precio de venta
                let precio_venta = new Intl.NumberFormat("de-DE").format(resp[2].precio_venta);
                // dar formato de numero al precio antiguo
                let precio_antiguo = new Intl.NumberFormat("de-DE").format(resp[2].precio_antiguo);
                $('#info_repuesto').append(`
                    <tr>
                            <td>`+resp[2].descripcion+`</td>
                            
                            <td> `+total_stock+` </td>
                            <td> `+total_vendido+` </td>
                            <td>`+resp[2].fecha_ultima+` </td>
                            <td>$`+precio_venta+`</td>
                            <td>`+precio_antiguo+`</td>
                            <td>`+resp[2].name+`</td>
                        </tr>
                `);
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
                        text:'No existen solicitudes o ya fue procesada'
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

    function aceptar_traspaso_repuesto(repuesto_id, solicitud_id,cantidad){
        let params = {repuesto_id: repuesto_id, solicitud_id: solicitud_id,cantidad: cantidad};
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
</script>
@endsection
@section('style')
<style>
.busqueda_principal{
            margin-top: 30px;
            border: 1px solid black;
            background: #f2f4a9;
            padding: 20px;
            border-radius: 10px;
        }
        .busqueda_resultado{
            border: 1px solid black;
            margin-top: 30px;
            min-height: 200px;
            padding: 20px;
            border-radius: 10px;
        }

        .logo{
            width: 100px;
            border-radius: 10px;
        }

        #panel_busqueda{
            display: block;
            width: 100%;
            margin: 20px 0px 20px 0px;
            border: 1px solid black;
            padding: 20px;
            border-radius: 10px;
        }
</style>

@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
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
                    <input type="submit" value="Buscar" class="btn btn-success btn-sm" onclick="dame_info()">
                </div>
            </div>
        </div>
        
    </div>
    
    
    <div class="busqueda_resultado">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo">
        <table class="table">
            <thead>
              <tr>
                <th scope="col">Descripción</th>
                
                <th scope="col">Stock Total</th>
                <th scope="col">Total vendido</th>
                <th scope="col">Fecha</th>
                <th scope="col">Precio venta</th>
                <th scope="col">Ex precio venta</th>  
                <th scope="col">Stock Modificado por</th>
              </tr>
            </thead>
            <tbody id="info_repuesto">
              
              
            </tbody>
          </table>
          <hr>
        <div class="row" style="width: 100%;">
        <div class="col-md-3">
            <p class="titulazo">Ingresados</p>
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">Cantidad</th>
                  </tr>
                </thead>
                <tbody id="ingresados">
                  
                  
                </tbody>
              </table>
            
        </div>
        <div class="col-md-4">
            <p class="titulazo">Vendidos</p>
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Doc</th>
                    <th scope="col">N° Documento</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Fecha de emisión</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Origen</th>
                  </tr>
                </thead>
                <tbody id="vendidos">
                  
                </tbody>
              </table>
            
        </div>
        <div class="col-md-5">
            <p class="titulazo">Traspasos</p>
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Fecha de emisión</th>
                    <th scope="col">Locaciones</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Usuario</th>
                  </tr>
                </thead>
                <tbody id="traspasos">
                  
                </tbody>
              </table>
        </div>
        </div>
          
    </div>
</div>


@endsection