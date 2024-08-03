@extends('plantillas.app')
@section('titulo','Clonaciones')
@section('javascript')
<script>

    function enter_buscar_repuesto(opt){
        if(event.key === 'Enter'){
            if(opt == 1)
                buscar_repuesto(1);
            else if(opt == 2)
                buscar_repuesto(2);
            else if(opt == 3)
                buscar_repuesto(3);
            else if(opt == 4)
                buscar_repuesto(4);

        }
    }

    function buscar_repuesto(op){
        if(op==1){
            var cod_int = document.getElementById('codigo_interno_origen').value;
        }else if(op==2){
            var cod_int = document.getElementById('codigo_interno_destino').value;
        }else if(op==3){
            var cod_int = document.getElementById('codigo_interno_origen_f').value;
        }else if(op==4){
            var cod_int = document.getElementById('codigo_interno_destino_f').value;
        }

        dame_repuesto(cod_int,op);

        if(op == 1 || op == 2){
            dame_aplicaciones(cod_int,op);
        }

        if(op == 3 || op == 4){
            dame_fabricantes(cod_int,op);
        }
        
        
    }

    function dame_aplicaciones(codigo_interno,op)
    {
      var url='{{url("repuesto")}}'+'/'+codigo_interno+'/dame_aplicaciones_clonar';
        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Cargando...");
          Vue.swal({
                title: 'Cargando',
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
        success:function(aplicaciones){
            console.log(aplicaciones);
            Vue.swal.close();
            if(op==1)
                $("#aplicaciones_rep").html(aplicaciones);
            else if(op==2)
                $("#aplicaciones_rep_dest").html(aplicaciones);
            else if(op==3)
                $("#fabricantes_rep").html(aplicaciones);
            else if(op==4)
                $("#fabricantes_rep_dest").html(aplicaciones);

          // le quitamos los disabled a todos los elementos del div con id seccion_destino
        $("#seccion_destino").find('*').prop('disabled', false);
        // agregamos un mensaje de éxito
        $("#mensajes").html("Aplicaciones cargadas con éxito");
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

    function dame_fabricantes(codigo_interno,op){
        console.log('entro a dame_fabricantes');
        var url = '/repuesto/'+codigo_interno+'/dame_fabricantes_clonar';
        $.ajax({
            type:'GET',
            url:url,
            success:function(fabricantes){
                console.log(fabricantes);
                if(op==3)
                    $("#fabricantes_rep").html(fabricantes);
                else if(op==4)
                    $("#fabricantes_rep_dest").html(fabricantes);
                // le quitamos los disabled a todos los elementos del div con id seccion_destino
                $("#seccion_destino_").find('*').prop('disabled', false);
                // agregamos un mensaje de éxito
                $("#mensajes").html("Fabricantes cargados con éxito");
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
        // le quitamos los disabled a todos los elementos del div con id seccion_destino
        $("#seccion_destino_").find('*').prop('disabled', false);
    }

    function dame_repuesto(codigo_interno,op){
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
                }else{
                    if(op==1){
                        $('#repuesto_origen').html(html);
                        $('#codigo_interno_origen').val(codigo_interno);

                    }else if(op==2){
                        $('#repuesto_destino').html(html);
                        $('#codigo_interno_destino').val(codigo_interno);
                        // le quitamos el atributo disabled al boton clonar
                        $("#btn_clonar").prop('disabled', false);
                    }else if(op==3){
                        $('#repuesto_origen_f').html(html);
                        $('#codigo_interno_origen_f').val(codigo_interno);
                    }else if(op==4){
                        $('#repuesto_destino_f').html(html);
                        $('#codigo_interno_destino_f').val(codigo_interno);
                        // le quitamos el atributo disabled al boton clonar
                        $("#btn_clonar_fab").prop('disabled', false);
                    }
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

    function clonar(op){
        let opt = confirm('¿Está seguro de clonar las aplicaciones del repuesto?');
        if(!opt){
            return;
        }
        if(op==1){
            var cod_int_origen = document.getElementById('codigo_interno_origen').value;
            var cod_int_destino = document.getElementById('codigo_interno_destino').value;
            var url = '/repuesto/clonar_aplicaciones/'+cod_int_origen+'/'+cod_int_destino;
        }else if(op==2){
            var cod_int_origen = document.getElementById('codigo_interno_origen_f').value;
            var cod_int_destino = document.getElementById('codigo_interno_destino_f').value;
            var url = '/repuesto/clonar_fabricantes/'+cod_int_origen+'/'+cod_int_destino;
        }

        console.log(url);
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                console.log(html);
                if(html.mensaje == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se pudo clonar',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else{
                    
                    let clonaciones = html.clonaciones;
                    if(op==1){
                        let aplicaciones = html.aplicaciones;
                        $('#aplicaciones_rep_dest').html(aplicaciones);
                        $('#info_similares').html('Clonaciones similares: '+clonaciones.length);
                        $('#tbody_clonaciones_similares').empty();
                        clonaciones.forEach(c => {
                            $('#tbody_clonaciones_similares').append('<tr><td>'+c.codigo_origen+'</td><td>'+c.codigo_destino+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_similar('+c.id+')"><i class="fas fa-trash" ></i></button></td></tr>');
                        });
                    }
                        
                    else if(op==2){
                        let fabricantes = html.fabricantes;
                        $('#info_fabricantes').html('Clonaciones fabricantes: '+clonaciones.length);
                        $('#fabricantes_rep_dest').html(fabricantes);
                        $('#tbody_clonaciones_fabs').empty();
                        clonaciones.forEach(c => {
                            $('#tbody_clonaciones_fabs').append('<tr><td>'+c.codigo_origen+'</td><td>'+c.codigo_destino+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_fab('+c.id+')"><i class="fas fa-trash" ></i></button></td></tr>');
                        });
                    }
                        
                    

                    return Vue.swal({
                        title: 'Éxito',
                        text: 'Clonación exitosa',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
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

    function eliminar_similar(id){
        console.log('entro a eliminar_similar'+id);
        let opt = confirm('¿Está seguro de eliminar el registro?');
        if(!opt){
            return;
        }
        let url = '/repuesto/deshacer_similares/'+id;
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                console.log(html);
                if(html.mensaje == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se pudo eliminar',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else{
                    let aplicaciones = html.aplicaciones;
                    //$('#aplicaciones_rep_dest').html(aplicaciones);
                    let clonaciones = html.clonaciones;
                    $('#info_similares').html('Clonaciones similares: '+clonaciones.length);
                    $('#tbody_clonaciones_similares').empty();
                    clonaciones.forEach(c => {
                        $('#tbody_clonaciones_similares').append('<tr><td>'+c.codigo_origen+'</td><td>'+c.codigo_destino+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_similar('+c.id+')"><i class="fas fa-trash" ></i></button></td></tr>');
                    });
                    return Vue.swal({
                        title: 'Éxito',
                        text: 'Eliminación exitosa',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
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

    function eliminar_fab(id){
        console.log('entro a eliminar_fab'+id);
        let opt = confirm('¿Está seguro de eliminar el registro?');
        if(!opt){
            return;
        }
        let url = '/repuesto/deshacer_fabricantes/'+id;
        $.ajax({
            type:'GET',
            url:url,
            success:function(html){
                console.log(html);
                if(html.mensaje == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se pudo eliminar',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else{
                    let fabricantes = html.fabricantes;
                    //$('#fabricantes_rep_dest').html(fabricantes);
                    let clonaciones = html.clonaciones;
                    $('#tbody_clonaciones_fabs').empty();
                    $('#info_fabricantes').html('Clonaciones fabricantes: '+clonaciones.length);
                    clonaciones.forEach(c => {
                        $('#tbody_clonaciones_fabs').append('<tr><td>'+c.codigo_origen+'</td><td>'+c.codigo_destino+'</td><td><button class="btn btn-danger btn-sm" onclick="eliminar_fab('+c.id+')"><i class="fas fa-trash" ></i></button></td></tr>');
                    });
                    return Vue.swal({
                        title: 'Éxito',
                        text: 'Eliminación exitosa',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
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
<h4 class="titulazo">Clonaciones</h4>
<div class="container-fluid">
    <a class="btn btn-danger btn-sm my-2" href="/repuesto/listar-oems">Clonaciones OEMS</a>
    <div class="row">
        <div class="col-md-6 border">
            <p class="titulazo">Aplicaciones</p>
            <p class="text-secondary" id="info_similares">Clonaciones similares: {{$similares->count()}}</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody_clonaciones_similares">
                    @foreach($similares as $s)
                    <tr>
                        <td>{{$s->codigo_interno_origen}}</td>
                        <td>{{$s->codigo_interno_destino}}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="eliminar_similar({{$s->id}})"><i class="fas fa-trash" ></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex" id="seccion_destino">
                <input type="text" class="form-control" id="codigo_interno_origen" onkeyup="enter_buscar_repuesto(1)" placeholder="Ingrese codigo interno origen">
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(1)"><i class="fas fa-search" ></i></button>
                
                <input type="text" class="form-control" id="codigo_interno_destino" onkeyup="enter_buscar_repuesto(2)" placeholder="Ingrese codigo interno destino" disabled>
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(2)" disabled><i class="fas fa-search" ></i></button>
                
            </div>
            <button class="btn btn-warning btn-sm my-2" id="btn_clonar" onclick="clonar(1)" disabled>Clonar</button>
            <div class="row">
                <div class="col-md-6">
                    <div id="repuesto_origen">

                    </div>
                </div>
                <div class="col-md-6">
                    <div id="repuesto_destino">

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div id="aplicaciones_rep">

                    </div>
                </div>
                <div class="col-md-6">
                    <div id="aplicaciones_rep_dest">

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 border">
            <p class="titulazo">Fabricantes</p>
            <p class="text-secondary" id="info_fabricantes">Clonaciones fabricantes: {{$fabs->count()}}</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody_clonaciones_fabs">
                    @foreach($fabs as $f)
                    <tr>
                        <td>{{$f->codigo_interno_origen}}</td>
                        <td>{{$f->codigo_interno_destino}}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="eliminar_fab({{$f->id}})"><i class="fas fa-trash" ></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex" id="seccion_destino_">
                <input type="text" class="form-control" id="codigo_interno_origen_f" onkeyup="enter_buscar_repuesto(3)" placeholder="Ingrese codigo interno origen">
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(3)"><i class="fas fa-search" ></i></button>
                
                <input type="text" class="form-control" id="codigo_interno_destino_f" onkeyup="enter_buscar_repuesto(4)" placeholder="Ingrese codigo interno destino" disabled>
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(4)" disabled><i class="fas fa-search" ></i></button>
            
                
            </div>
            <button class="btn btn-warning btn-sm my-2" id="btn_clonar_fab" onclick="clonar(2)" disabled>Clonar</button>
            <div class="row">
                <div class="col-md-6">
                    <div id="repuesto_origen_f">

                    </div>
                </div>
                <div class="col-md-6">
                    <div id="repuesto_destino_f">

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div id="fabricantes_rep">

                    </div>
                </div>
                <div class="col-md-6">
                    <div id="fabricantes_rep_dest">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection