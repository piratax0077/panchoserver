@extends('plantillas.app')
@section('titulo','Clonaciones')
@section('javascript')
<style>
.clonaciones-page { padding-bottom: 24px; }
.clonaciones-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.clonaciones-grid { row-gap: 18px; }
.clon-card {
    overflow: hidden;
    border: 1px solid #dbe4ee;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .07);
}
.clon-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.clon-card__header h5 { margin: 0; color: #172033; font-weight: 700; }
.clon-card__count {
    margin: 0;
    padding: 4px 9px;
    border-radius: 999px;
    color: #1d4ed8 !important;
    background: #dbeafe;
    font-size: 12px;
    font-weight: 700;
}
.clon-history { max-height: 410px; overflow-y: auto; }
.clon-table { margin: 0; }
.clon-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    border-top: 0;
    color: #475569;
    background: #eef5f8;
    font-size: 12px;
    text-transform: uppercase;
}
.clon-table td { vertical-align: middle; }
.clon-table th:last-child,
.clon-table td:last-child { width: 64px; text-align: right; }
.clon-delete { width: 34px; height: 34px; padding: 0 !important; }
.clon-form {
    display: grid !important;
    grid-template-columns: minmax(0,1fr) 40px minmax(0,1fr) 40px;
    gap: 7px;
    padding: 14px 16px 8px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.clon-form .form-control { min-width: 0; }
.clon-submit { margin: 0 16px 14px !important; min-width: 120px; }
.clon-preview { padding: 0 16px 12px; }
.clon-empty td {
    padding: 30px 10px !important;
    color: #94a3b8;
    text-align: center !important;
}
.clon-empty i, .clon-empty span { display: block; }
.clon-empty i { margin-bottom: 7px; font-size: 24px; }
.clon-confirm {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 12px 0;
    padding: 12px;
    border-radius: 8px;
    background: #f1f5f9;
}
@media (max-width: 767.98px) {
    .clon-form { grid-template-columns: minmax(0,1fr) 40px; }
}
</style>
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
        // agregamos un mensaje de Ã©xito
        $("#mensajes").html("Aplicaciones cargadas con Ã©xito");
        },
          error: function(error){
            $('#mensajes').html(error.responseText);
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }

        }); //Fin peticiÃ³n
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
                // agregamos un mensaje de Ã©xito
                $("#mensajes").html("Fabricantes cargados con Ã©xito");
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
                        text: 'No se encontrÃ³ el repuesto',
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
        let opt = confirm('Â¿EstÃ¡ seguro de clonar las aplicaciones del repuesto?');
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
                        title: 'Ã‰xito',
                        text: 'ClonaciÃ³n exitosa',
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

    function escapar_html(valor){
        return $('<div>').text(valor == null ? '' : valor).html();
    }

    function renderizar_clonaciones(tipo, clonaciones){
        const esAplicacion = tipo === 'aplicaciones';
        const tbody = esAplicacion ? '#tbody_clonaciones_similares' : '#tbody_clonaciones_fabs';
        const info = esAplicacion ? '#info_similares' : '#info_fabricantes';
        const eliminar = esAplicacion ? 'eliminar_similar' : 'eliminar_fab';
        const etiqueta = esAplicacion ? 'aplicaciones' : 'fabricantes';

        $(info).text('Clonaciones de '+etiqueta+': '+clonaciones.length);
        $(tbody).empty();

        if(!clonaciones.length){
            $(tbody).append('<tr class="clon-empty"><td colspan="3"><i class="fas fa-inbox"></i><span>No hay clonaciones registradas</span></td></tr>');
            return;
        }

        clonaciones.forEach(function(c){
            $(tbody).append(
                '<tr>'+
                    '<td><strong>'+escapar_html(c.codigo_origen)+'</strong></td>'+
                    '<td><strong>'+escapar_html(c.codigo_destino)+'</strong></td>'+
                    '<td class="text-right"><button type="button" class="btn btn-outline-danger btn-sm clon-delete" onclick="'+eliminar+'('+c.id+',this)" title="Eliminar clonaciÃ³n"><i class="fas fa-trash"></i></button></td>'+
                '</tr>'
            );
        });
    }

    function mensaje_error_ajax(error, reemplazo){
        if(error.responseJSON && error.responseJSON.detalle) return error.responseJSON.detalle;
        if(error.responseJSON && error.responseJSON.message) return error.responseJSON.message;
        return reemplazo;
    }

    function eliminar_clonacion(tipo, id, boton){
        boton = boton || (typeof event !== 'undefined' ? event.currentTarget : null);
        const fila = $(boton).closest('tr');
        const origen = fila.find('td').eq(0).text().trim();
        const destino = fila.find('td').eq(1).text().trim();
        const url = '/repuesto/clonaciones/'+tipo+'/'+id;

        Vue.swal({
            title: 'Eliminar clonaciÃ³n',
            html: '<p>Se quitarÃ¡ del historial la relaciÃ³n:</p><div class="clon-confirm"><strong>'+escapar_html(origen)+'</strong><i class="fas fa-arrow-right"></i><strong>'+escapar_html(destino)+'</strong></div><small>Los datos existentes del repuesto destino no serÃ¡n eliminados.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: function(){ return !Vue.swal.isLoading(); },
            preConfirm: function(){
                return $.ajax({
                    type: 'DELETE',
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
                }).catch(function(error){
                    Vue.swal.showValidationMessage(mensaje_error_ajax(error, 'No fue posible eliminar la clonaciÃ³n.'));
                });
            }
        }).then(function(resultado){
            if(!resultado.value) return;
            const respuesta = resultado.value;
            renderizar_clonaciones(tipo, respuesta.clonaciones || []);
            Vue.swal({
                title: 'ClonaciÃ³n eliminada',
                text: respuesta.detalle || 'El registro fue eliminado del historial.',
                icon: 'success',
                timer: 2200,
                showConfirmButton: false
            });
        });
    }

    function eliminar_similar(id, boton){
        eliminar_clonacion('aplicaciones', id, boton);
    }

    function eliminar_fab(id, boton){
        eliminar_clonacion('fabricantes', id, boton);
    }
</script>
@endsection
@section('contenido_ingresa_datos')
<h4 class="titulazo">Clonaciones</h4>
<div class="container-fluid clonaciones-page">
    <a class="btn btn-danger btn-sm my-2" href="/repuesto/listar-oems">Clonaciones OEMS</a>
    <div class="row clonaciones-grid">
        <section class="col-lg-6 clon-card">
            <div class="clon-card__header"><h5><i class="fas fa-car mr-2"></i>Aplicaciones</h5><p class="clon-card__count" id="info_similares">{{$similares->count()}} clonaciones</p></div>
            <div class="clon-history"><table class="table table-hover clon-table">
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
                        <td class="text-right"><button type="button" class="btn btn-outline-danger btn-sm clon-delete" onclick="eliminar_similar({{$s->id}},this)" title="Eliminar clonación"><i class="fas fa-trash"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div><div class="d-flex clon-form" id="seccion_destino">
                <input type="text" class="form-control" id="codigo_interno_origen" onkeyup="enter_buscar_repuesto(1)" placeholder="Ingrese codigo interno origen">
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(1)"><i class="fas fa-search" ></i></button>
                
                <input type="text" class="form-control" id="codigo_interno_destino" onkeyup="enter_buscar_repuesto(2)" placeholder="Ingrese codigo interno destino" disabled>
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(2)" disabled><i class="fas fa-search" ></i></button>
                
            </div>
            <button class="btn btn-primary btn-sm my-2 clon-submit" id="btn_clonar" onclick="clonar(1)" disabled><i class="fas fa-copy mr-1"></i>Clonar</button>
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
        </section>
        <section class="col-lg-6 clon-card">
            <div class="clon-card__header"><h5><i class="fas fa-industry mr-2"></i>Fabricantes</h5><p class="clon-card__count" id="info_fabricantes">{{$fabs->count()}} clonaciones</p></div>
            <div class="clon-history"><table class="table table-hover clon-table">
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
                        <td class="text-right"><button type="button" class="btn btn-outline-danger btn-sm clon-delete" onclick="eliminar_fab({{$f->id}},this)" title="Eliminar clonación"><i class="fas fa-trash"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div><div class="d-flex clon-form" id="seccion_destino_">
                <input type="text" class="form-control" id="codigo_interno_origen_f" onkeyup="enter_buscar_repuesto(3)" placeholder="Ingrese codigo interno origen">
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(3)"><i class="fas fa-search" ></i></button>
                
                <input type="text" class="form-control" id="codigo_interno_destino_f" onkeyup="enter_buscar_repuesto(4)" placeholder="Ingrese codigo interno destino" disabled>
                <button class="btn btn-success btn-sm" onclick="buscar_repuesto(4)" disabled><i class="fas fa-search" ></i></button>
            
                
            </div>
            <button class="btn btn-primary btn-sm my-2 clon-submit" id="btn_clonar_fab" onclick="clonar(2)" disabled><i class="fas fa-copy mr-1"></i>Clonar</button>
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
        </section>
    </div>
</div>
@endsection
