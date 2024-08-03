@extends('plantillas.app')

@section('titulo','Traspaso de mercadería')

@section('javascript')
  <script>
    function traspasar(){
      
      let codigo_interno = document.getElementById('cod_int').value;
      let cantidad = document.getElementById('cantidad').value;
      let opcion = document.getElementById('opciones').value;

      if(codigo_interno.trim() == 0){
        Vue.swal({
          icon:'info',
          position: 'top-end',
          text:'Debe ingresar un codigo interno',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
        return false;
      }

      if(cantidad.trim() == 0){
        Vue.swal({
          icon:'info',
          position: 'top-end',
          text:'Debe ingresar una cantidad',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
        return false;
      }

      if(cantidad.trim() <= 0 || cantidad < 0){
        Vue.swal({
          icon:'error',
          position: 'top-end',
          text:'La cantidad no puede ser negativa',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
        return false;
      }
      let url = '/guiadespacho/traspasar_mercaderia';
      let params = {
        codigo_interno: codigo_interno,
        cantidad: cantidad,
        opcion: opcion
      }
      var texto;
      if(opcion == 1){
        texto = "Se descontarán del stock de Bodega para ser llevados a Tienda";
      }
      if(opcion == 2){
        texto = "Se descontarán del stock de Bodega para ser llevados a Casa Matríz";
      }
      if(opcion == 3){
        texto ="Se descontarán del stock de Casa Matríz para ser llevados a Tienda";
      }


      Vue.swal({
        title:'¿Estás seguro?',
        text:texto,
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Agregar!'
      }).then((result) => {
        if(result.isConfirmed){
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
       console.log(resp);
         $('#num_solicitud_ingreso').val(resp[3]);
          if(resp == "error"){
            Vue.swal({
              icon:'error',
              text:'Repuesto ya solicitado por este usuario'
            });
            return false;
          }
          
          if(resp[0] === 'OK'){
            $('#tbody_detalle_repuestos').empty();
            resp[2].forEach(e => {
              $('#tbody_detalle_repuestos').append(`
                <tr> 
                  <td class='letra_pequeña'>`+e.codigo_interno+` </td>
                  <td class='letra_pequeña'>`+e.descripcion+` </td>
                  <td class='letra_pequeña'>`+e.cantidad+` </td>
                </tr>
              `);
          });
            Vue.swal({
              icon:'success',
              title:'INFORMACIÓN',
              text: 'Repuesto agregado correctamente'
            })
          }else{
            Vue.swal({
            icon:'error',
            title: 'INFORMACION',
            text:resp
          })
          }
         
          
        },
        error: function(error){
          console.log(error);
        }
      });
        }
      });

      
    }

    function cerrarSolicitud(){
      let num_solicitud_ingreso = $('#num_solicitud_ingreso').val();
      let num_solicitud = $('#num_solicitud').val();
        if(num_solicitud == '' || num_solicitud == undefined || num_solicitud.trim() == 0  ){
          var value = num_solicitud_ingreso;
        }else{
          var value = num_solicitud;
        }

        
        if(value == ''){
          Vue.swal({
            icon:'error',
            showConfirmButton: false,
            toast: true,
            timer:3000,
            text:'No tiene repuesto/s agregados',
            position: 'top-end'
          });
          return false;
        }

        let url = '/repuesto/avanzarNumTraspaso';

        $.ajax({
          type:'get',
          url: url,
          beforeSend: function(){
            Vue.swal({
              icon:'info',
              title:'Cargando ...'
            });
          },
          success: function(resp){
            Vue.swal.close();
            
            if(resp == 'OK'){
              Vue.swal({
                icon:'success',
                title:'Exito',
                text:''
              });
              
            }
            var myTimeout = setTimeout(redireccionar, 1000);

            function redireccionar() {
              window.location.href = "http://panchoserver.ddns.net/guiadespacho/traspaso_mercaderia";
            }
           
          },
          error: function(error){
            Vue.swal({
              icon:'error',
              title: error.responseText
            });
          }
          
        });
    }

    function buscar_repuesto(){

      let quien = 1;
      let codigo_interno = document.getElementById('cod_int').value;

      if(codigo_interno.trim() == 0 || codigo_interno == ''){
        Vue.swal({
          icon:'info',
          text:'Debe ingresar un codigo interno',
          toast: true,
          showConfirmButton:false,
          timer:3000,
          position:'top-end'
        });
        return false;
      }
      let url = '{{url("repuesto/buscarcodigo")}}'+'/'+quien+codigo_interno;

      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          
          if(resp == -1){
            Vue.swal({
              icon:'error',
              text:'No se encontró el repuesto',
            });
            return false;
          }
          let r = JSON.parse(resp[0]);
          $('#tbody_repuestos').empty();
          r.forEach(e => {
            if(e.local_id == 1 && e.local_id_dos == 3){
              local = 'Bodega';
              local_dos = 'Tienda';
            }else if(e.local_id_dos == 3 && e.local_id == 1){
              local = 'Tienda';
              local_dos = 'Bodega';
            }else if(e.local_id == 3){
              local = 'Tienda';
              local_dos = 'Bodega';
            }else if(e.local_id_dos == null){
              local = 'Bodega';
              local_dos = 'null';
            }else if(e.local_id === 1 && e.local_id_dos === 1){
              local = 'Bodega';
              local_dos = 'Bodega';
            }

            local_tres = 's/n';
              $('#tbody_repuestos').append(`
                <tr> 
                  <td class='letra_pequeña'>`+e.codigo_interno+` </td>
                  <td class='letra_pequeña'>`+e.descripcion+` </td>
                  <td class='letra_pequeña'>`+e.ubicacion+` (`+e.stock_actual+`)</td>
                  <td class='letra_pequeña'>`+e.ubicacion_dos+` (`+e.stock_actual_dos+`) </td>
                  <td class='letra_pequeña'>`+e.ubicacion_tres+` (`+e.stock_actual_tres+`) </td>
                </tr>
              `);
          });
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
    }

    function buscar_repuestos_sin_ubicacion(){
      
      let url = '/repuesto/repuestos_sin_ubicacion';
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
            Vue.swal({
              icon:'info',
              title:'Cargando ...',
              showConfirmButton:false,

            });
        },
        success: function(repuestos){
         var local;
         var local_dos;
          Vue.swal.close();
          
          $('#tbody_repuestos').empty();
          repuestos.forEach(e => {
            if(e.local_id == 1 && e.local_id_dos == 3){
              local = 'Bodega';
              local_dos = 'Tienda';
            }else if(e.local_id_dos == 3 && e.local_id == 1){
              local = 'Tienda';
              local_dos = 'Bodega';
            }else if(e.local_id == 3){
              local = 'Tienda';
              local_dos = 'Bodega';
            }else if(e.local_id_dos == null){
              local = 'Bodega';
              local_dos = 'null';
            }
              $('#tbody_repuestos').append(`
                <tr> 
                  <td class='letra_pequeña'><a href="/repuesto/modificar/`+e.id+`" target="_onblank"> `+e.codigo_interno+` </a> </td>
                  <td class='letra_pequeña'>`+e.descripcion+` </td>
                  
                  <td class='letra_pequeña' id="ubicacion-`+e.id+`"><a class='letra_pequeña' href='javascript:void(0)'data-toggle="modal" data-target="#modal_modificar_ubicacion" onclick="cargar_info('`+e.id+`','`+e.marcarepuesto+`','`+e.descripcion+`','`+e.ubicacion+`')"> `+e.ubicacion+` (`+e.stock_actual+`)</a> </td>
                  
                  <td class='letra_pequeña'>`+e.ubicacion_dos+` (`+e.stock_actual_dos+`) </td>
                  <td class='letra_pequeña'>`+e.ubicacion_tres+` (`+e.stock_actual_tres+`) </td>
                </tr>
              `);
          });
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
    }

    function cargar_info(id,marca, descripcion, ubicacion){
      
      $('#id_repuesto_ubicacion').val(id);
      $('#marca').empty();
      $('#modal_modificar_ubicacion_Label').empty();
      $('#ubi_uno').empty();

      $('#marca').append('<p>Marca: '+marca+'</p>');
      $('#modal_modificar_ubicacion_Label').append(descripcion);
      $('#ubi_uno').append('<p>Ubicación: '+ubicacion+'</p>');
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
                       
                    }else{
                      Vue.swal({
                          text: "No se pueden guardar las dos ubicaciones en Tienda",
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

      function notificar(){
        let num_solicitud_ingreso = $('#num_solicitud_ingreso').val();
        let num_solicitud = $('#num_solicitud').val();
        if(num_solicitud == '' || num_solicitud == undefined || num_solicitud.trim() == 0  ){
          var value = num_solicitud_ingreso;
        }else{
          var value = num_solicitud;
        }

        if(value == ''){
          Vue.swal({
            icon:'error',
            showConfirmButton: false,
            toast: true,
            timer:3000,
            text:'Seleccione un repuesto',
            position: 'top-end'
          });
          return false;
        }

        let url = '/solicitud_traspaso';

        $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        
            $.ajax({
              type:'post',
              url: url,
              data: {num_solicitud: value},
              beforeSend: function(){
                Vue.swal({
                  icon:'info',
                  title:'Cargando ...',
                  showConfirmButton: false
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
                  title:'error',
                  text: error.responseText
                });
              }
            })

      }

      

  </script>
@endsection

@section('style')
<style>
  .letra_pequeña{
    font-size: 12px;
  }
</style>
@endsection

@section('contenido_titulo_pagina')

  <h4 class="titulazo">Traspaso de mercadería</h4>

@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
  <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
  <div class="row my-4 p-3" style="width: 100%; background: #f2f4a9; border: 1px solid black; border-radius:10px;">
    <div class="col-md-2">
      <div class="form-group">
        <label for="">Opciones</label>
        <select name="opciones" id="opciones" class="form-control">
          <option value="1">Bodega a Tienda</option>
          <option value="2">Bodega a Casa matríz</option>
          <option value="3">Casa Matríz a Tienda</option>
        </select>
      </div>
    </div>
    <div class="col-md-2">
      <div class="form-group">
        <label for="">Codigo interno</label>
        <input type="text" name="" id="cod_int" class="form-control">
        <input type="button" value="Buscar" class="btn btn-warning btn-sm mt-2" onclick="buscar_repuesto()">
        
        @if(Auth::user()->name == "Mauricio Eguren" || Auth::user()->name == "Francisco Rojo" || Auth::user()->name == "José Troncoso") <input type="button" value="Repuestos sin información" class="btn btn-danger btn-sm mt-2" onclick="buscar_repuestos_sin_ubicacion()"> @endif
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-group">
        <label for="">Cantidad</label>
        <input type="number" name="" id="cantidad" class="form-control">
      </div>
      
    </div>
    <div class="col-md-5">
      <p>Sección dedidaca al traspaso de mercadería entre bodega a tienda</p> 
      <button class="btn btn-success btn-sm" onclick="traspasar()">Confirmar</button>
    </div>
    </div>
    <div class="row" style="width: 100%">
    <div class="col-md-8">
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col" class="letra_pequeña">Cod Int</th>
            <th scope="col" class="letra_pequeña" style="width: 40%;">Descripcion</th>
            
            <th scope="col" class="letra_pequeña">Ubicación</th>
            
            <th scope="col" class="letra_pequeña">Ubicación</th>
            <th scope="col" class="letra_pequeña">Ubicación</th>
          </tr>
        </thead>
        <tbody id="tbody_repuestos" style="background: rgb(207, 255, 255);">
          
        </tbody>
      </table>
      
    </div>
    
    <div class="col-md-4">
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col" class="letra_pequeña">Código interno</th>
            <th scope="col" class="letra_pequeña">Descripción</th>
            <th scope="col" class="letra_pequeña">Cantidad</th>
          </tr>
        </thead>
        <tbody id="tbody_detalle_repuestos">
          @if(isset($detalle))
          @foreach($detalle as $d)
            <tr>
              <td class="letra_pequeña">{{$d->codigo_interno}}</td>
              <td class="letra_pequeña">{{$d->descripcion}}</td>
              <td class="letra_pequeña">{{$d->cantidad}}</td>
            </tr>
          @endforeach
          @endif
        </tbody>
      </table>
      <button class="btn btn-warning btn-sm" onclick="notificar()">Generar solicitud</button>
      <button class="btn btn-danger btn-sm" onclick="cerrarSolicitud()">Terminar solicitud</button>
      <h2 class="mt-4">Historial</h2>
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col" class="letra_pequeña">Código interno</th>
            <th scope="col" class="letra_pequeña">Descripción</th>
            <th scope="col" class="letra_pequeña">Cantidad</th>
          </tr>
        </thead>
        <tbody id="tbody_detalle_repuestos" style="background: rgb(207, 255, 255);">
          @if(isset($historial))
          @foreach($historial as $h)
            <tr>
              <td class="letra_pequeña">{{$h->codigo_interno}}</td>
              <td class="letra_pequeña">{{$h->descripcion}}</td>
              <td class="letra_pequeña">{{$h->cantidad}}</td>
            </tr>
          @endforeach
          @endif
        </tbody>
      </table>
    </div>
    
    </div>
    
</div>
  <!-- Modal -->
<div class="modal fade" id="modal_modificar_ubicacion" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logo_">
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
                  @foreach($locales as $b)
                      <option value="{{$b->id}}">{{$b->local_nombre}}</option>
                  @endforeach
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
<!-- DATOS DE VITAL IMPORTANCIA -->
@if(isset($num_solicitud))
<input type="hidden" name="num_solicitud" id="num_solicitud" value="{{$num_solicitud}}">
@endif

<input type="hidden" name="num_solicitud_ingreso" id="num_solicitud_ingreso">
@endsection