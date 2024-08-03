@extends('plantillas.app')
@section('titulo','Listar OEMs')
@section('javascript')
<script>
    function buscar_repuesto_origen(){
        let cod_int = document.getElementById('codigo_interno_origen').value;
        if(cod_int == ''){
            return Vue.swal({
                title: 'Error',
                text: 'Debe ingresar un código interno',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
        dame_oems(cod_int);
        dame_repuesto(cod_int);
    }

    function dame_oems(codigo_interno)
    {
      var url='{{url("repuesto")}}'+'/'+codigo_interno+'/dameoems_clonar/1';
        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Cargando OEMs...");
          Vue.swal({
                title: 'Cargando OEMs',
                text: 'Espere un momento por favor',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                onOpen: () => {
                    Vue.swal.showLoading()
                }
          });
        },
        url:url,
        success:function(oems){
            Vue.swal.close();
          $("#oems_rep").html(oems);
          // le quitamos los disabled a todos los elementos del div con id seccion_destino
        $("#seccion_destino").find('*').prop('disabled', false);
        // agregamos un mensaje de éxito
        $("#mensajes").html("OEMs cargados con éxito");
        },
          error: function(error){
            $('#mensajes').html(error.responseText);
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }

        }); //Fin petición
    }

    function dame_oems_destino(codigo_interno)
    {
      var url='{{url("repuesto")}}'+'/'+codigo_interno+'/dameoems_clonar/2';
        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Cargando OEMs...");
          Vue.swal({
                title: 'Cargando OEMs',
                text: 'Espere un momento por favor',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                onOpen: () => {
                    Vue.swal.showLoading()
                }
          });
        },
        url:url,
        success:function(oems){
            Vue.swal.close();
            if(oems == 'error'){
                return Vue.swal({
                    title: 'Error',
                    text: 'No se encontró el repuesto',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }else{
                $("#oems_rep_destino").html(oems);
                // le quitamos el disabled al boton clone_oems
                $("#btn_clonar").prop('disabled', false);
            }
          
          
        },
          error: function(error){
            $('#mensajes').html(error.responseText);
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }

        }); //Fin petición
    }

    function dame_repuesto(codigo_interno){
        let url = '/repuesto/buscarcodint_clonar_oems/'+codigo_interno;
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                if(html == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se encontró el repuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else $("#repuesto_origen").html(html);

                $('#codigo_interno_origen').val(codigo_interno);
                
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
    }

    function dame_repuesto_destino(codigo_interno){
        let url = '/repuesto/buscarcodint_clonar_oems/'+codigo_interno;
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                
                if(html == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se encontró el repuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else $("#repuesto_destino").html(html);

                $('#codigo_interno_destino').val(codigo_interno);
                
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
    }

    function buscar_repuesto_destino(){
        let cod_int = document.getElementById('codigo_interno_destino').value;
        if(cod_int == ''){
            return Vue.swal({
                title: 'Error',
                text: 'Debe ingresar un código interno',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
        dame_repuesto_destino(cod_int);
        dame_oems_destino(cod_int);
    }

    function clonar_oems(){
        let cod_int_origen = document.getElementById('codigo_interno_origen').value;
        let cod_int_destino = document.getElementById('codigo_interno_destino').value;
        // el codigo interno origen que sea en mayusculas
        cod_int_origen = cod_int_origen.toUpperCase();
        // el codigo interno destino que sea en mayusculas
        cod_int_destino = cod_int_destino.toUpperCase();
        return Vue.swal({
            icon:'info',
            text:'¿Está seguro que desea clonar los OEMs de '+cod_int_origen+' a '+cod_int_destino+'?',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if(result.isConfirmed){
                
                let url = '/repuesto/clonar_oems/'+cod_int_origen+'/'+cod_int_destino;
                
                $.ajax({
                    type:'GET',
                    url:url,
                    success:function(html){
                        console.log(html);
                        if(html == 'error'){
                            return Vue.swal({
                                title: 'Error',
                                text: 'Ya se realizó la clonación de OEMs para este repuesto',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }else{
                            Vue.swal({
                                title: 'Éxito',
                                text: 'OEMs clonados con éxito',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then((result) => {
                                if(result.isConfirmed){
                                    //window.location.href = '/repuesto';
                                    buscar_repuesto_destino();
                                    dame_historial_clonaciones_oems();
                                }
                            });
                        }
                    },
                    error: function(error){
                        $('#mensajes').html(error.responseText);
                        Vue.swal({
                            title: 'ERROR',
                            text: error.responseText,
                            icon: 'error',
                        });
                    }
                });
            }
        });
    }

    function dame_historial_clonaciones_oems(){
        let url = '/repuesto/historial_clonaciones_oems';
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                console.log(html);
                $("#historial_clonaciones_oems").html(html);
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
    }

    function borraroem_origen(idoem)
    {
        var codigo_interno=$('#codigo_interno_origen').val();
        var url='{{url("factuprodu")}}'+'/'+idoem+'/borraroem_x_codint/'+codigo_interno+'/1';
        $.ajax({
        type:'GET',
        beforeSend: function () {
            $("#mensajes").html("<b>OEMs:</b> Borrando OEM, espere por favor...");
            
            $("#oems_rep").html("");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
            $("#mensajes").html("<b>OEMs:</b> OEM Borrado...");
            Vue.swal({
                    text: 'OEM borrado',
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
            $("#oems_rep").html(resp);
        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
        }

        }); //Fin ajax
    }

    function borraroem_destino(idoem)
    {
      var codigo_interno=$('#codigo_interno_destino').val();
      var url='{{url("factuprodu")}}'+'/'+idoem+'/borraroem_x_codint/'+codigo_interno+'/2';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes").html("<b>OEMs:</b> Borrando OEM, espere por favor...");
        
        $("#oems_rep_destino").html("");
      },
      url:url,
      success:function(resp){
        Vue.swal.close();
          $("#mensajes").html("<b>OEMs:</b> OEM Borrado...");
          Vue.swal({
                text: 'OEM borrado',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          $("#oems_rep_destino").html(resp);
      },
      error: function(error){
        Vue.swal.close();
        Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

        }); //Fin ajax
    }

    function deshacer_clonacion(idclonacion){
        let url = '/repuesto/deshacer_clonacion_oems/'+idclonacion;
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                console.log(html);
                if(html == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'Ya se realizó la clonación de OEMs para este repuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else{
                    Vue.swal({
                        title: 'Éxito',
                        text: 'OEMs eliminados con éxito del repuesto destino',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            //window.location.href = '/repuesto';
                            dame_historial_clonaciones_oems();
                            buscar_repuesto_destino();
                        }
                    });
                }
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
    }
</script>
@endsection
@section('contenido_ingresa_datos')
<h4 class="titulazo">Clonar OEM</h4>
<div class="container-fluid">
    
    <div id="mensajes"></div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group d-flex">
                <input type="search" class="form-control" name="codigo_interno_origen" id="codigo_interno_origen" placeholder="Ingrese código interno origen">
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto_origen()">Buscar</button>
            </div>
            <div id="repuesto_origen">

            </div>
            <div id="oems_rep">

            </div>
            
        </div>
        <div class="col-md-4" id="seccion_destino">
            <div class="form-group d-flex">
                <input type="search" class="form-control" name="codigo_interno_destino" id="codigo_interno_destino" placeholder="Ingrese código interno destino" disabled>
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto_destino()" disabled>Buscar</button>
            </div>
            <div id="repuesto_destino">

            </div>
            <div id="oems_rep_destino">

            </div>
        </div>
        <div class="col-md-2">
            <a href="/repuesto/listar-oems" class="btn btn-warning btn-sm">Nueva Clonación</a>
            <button class="btn btn-success btn-sm" id="btn_clonar" onclick="clonar_oems()" disabled>Clonar</button>
        </div>
        <div class="col-md-2 tabla-scroll-y-500" id="historial_clonaciones_oems">
            <table class="table" >
                <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="historial">
                    @foreach($clonaciones as $h)
                    <tr>
                        <td>{{$h->codigo_interno_origen}}</td>
                        <td>{{$h->codigo_interno_destino}}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="deshacer_clonacion({{$h->id}})">X</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
    
</div>

<!-- DATOS DE VITAL IMPORTANCIA -->
<input type="hidden" name="codigo_interno_destino" id="codigo_interno_destino" value="">
<input type="hidden" name="codigo_interno_origen" id="codigo_interno_origen" value="">
@endsection