@extends('plantillas.app')
@section('titulo','Listar OEMs')
@section('javascript')
<style id="oem-clonaciones-styles">
.oem-page{padding:20px 18px 28px}.oem-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}.oem-header h4{margin:0;font-weight:700}.oem-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr) 360px;gap:16px;align-items:start}.oem-card{overflow:hidden;border:1px solid #dbe4ee;border-radius:12px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.07)}.oem-card__title{display:flex;align-items:center;gap:9px;margin:0;padding:13px 15px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-size:16px;font-weight:700}.oem-search{display:grid;grid-template-columns:minmax(0,1fr) 42px;gap:7px;padding:14px}.oem-search .btn{padding:0}.oem-content{padding:0 14px 14px}.oem-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:16px}.oem-history{max-height:620px;overflow:auto}.oem-history table{margin:0}.oem-history thead th{position:sticky;top:0;z-index:2;background:#e6f7f4;color:#334155}.oem-history td{vertical-align:middle}.oem-history th:last-child,.oem-history td:last-child{width:52px;text-align:right}.oem-delete{width:34px;height:34px;padding:0!important}.oem-empty{padding:28px!important;text-align:center;color:#94a3b8}.oem-flow{display:flex;align-items:center;justify-content:center;gap:12px;margin:12px 0;padding:12px;border-radius:8px;background:#f1f5f9}@media(max-width:1100px){.oem-layout{grid-template-columns:1fr 1fr}.oem-history-card{grid-column:1/-1}}@media(max-width:767.98px){.oem-layout{grid-template-columns:1fr}.oem-history-card{grid-column:auto}}
</style>
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
            success:function(resp){
                console.log(resp);
                let html = resp[0];
                let id_rep = resp[1];
                console.log(html);
                if(html == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se encontró el repuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else $("#repuesto_origen").html(html);

                $('#codigo_interno_origen').val(codigo_interno);
                dameInfoRepuestoModal(id_rep,'repuesto_origen');
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
            success:function(resp){
                console.log(resp);
                let html = resp[0];
                let id_rep = resp[1];
                console.log(html);
                if(html == 'error'){
                    return Vue.swal({
                        title: 'Error',
                        text: 'No se encontró el repuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }else $("#repuesto_destino").html(html);

                $('#codigo_interno_destino').val(codigo_interno);
                dameInfoRepuestoModal(id_rep,'repuesto_destino');
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

    function deshacer_clonacion(idclonacion, boton){
        const fila = $(boton).closest('tr');
        const origen = fila.find('td').eq(0).text().trim();
        const destino = fila.find('td').eq(1).text().trim();
        Vue.swal({
            title: 'Eliminar clonación OEM',
            html: '<p>Se quitará del historial la relación:</p><div class="oem-flow"><strong>'+origen+'</strong><i class="fas fa-arrow-right"></i><strong>'+destino+'</strong></div><small>Los OEM existentes en el repuesto destino se conservarán.</small>',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Eliminar', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545', reverseButtons: true, showLoaderOnConfirm: true,
            allowOutsideClick: function(){ return !Vue.swal.isLoading(); },
            preConfirm: function(){
                return $.ajax({type:'DELETE',url:'/repuesto/clonaciones/oems/'+idclonacion,headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}})
                    .catch(function(error){Vue.swal.showValidationMessage(error.responseJSON && error.responseJSON.detalle ? error.responseJSON.detalle : 'No fue posible eliminar la clonación OEM.');});
            }
        }).then(function(resultado){
            if(!resultado.value) return;
            fila.fadeOut(180,function(){$(this).remove();});
            Vue.swal({title:'Clonación eliminada',text:resultado.value.detalle,icon:'success',timer:2200,showConfirmButton:false});
        });
    }
    function dameInfoRepuestoModal(idRepuesto, div){
    
        var nuevaurl = "/repuesto/buscaridrep_html_carrito/"+idRepuesto;
        $.ajax({
            type:'get',
            url:nuevaurl,
            beforeSend: function(){
            //espere('CARGANDO ...');
            },
            success: function(html){
            console.log(html);
                Vue.swal.close();
                $('#'+div).empty();
                $('#'+div).append(html);
            },
            error: function(error){
                console.log(error);
            }
        });
    }
</script>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid oem-page">
    <div class="oem-header">
        <h4><i class="fas fa-clone mr-2"></i>Clonación de OEM</h4>
        <div style="display:flex; gap:8px; align-items:center;">
            <a href="/repuesto/listar-oems" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo mr-1"></i> Nueva clonación</a>
            <button type="button" class="btn btn-primary btn-sm" id="btn_clonar" onclick="clonar_oems()" disabled><i class="fas fa-copy mr-1"></i> Clonar OEM</button>
            <a href="/repuesto/clonaciones" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Otras clonaciones</a>
        </div>
    </div>
    <div id="mensajes"></div>
    <div class="oem-layout">
        <section class="oem-card">
            <h5 class="oem-card__title"><span class="badge badge-primary">1</span> Repuesto de origen</h5>
            <div class="oem-search">
                <input type="search" class="form-control" id="codigo_interno_origen" placeholder="Código interno de origen" onkeyup="if(event.key==='Enter') buscar_repuesto_origen()">
                <button type="button" class="btn btn-success" onclick="buscar_repuesto_origen()" title="Buscar origen"><i class="fas fa-search"></i></button>
            </div>
            <div class="oem-content"><div id="repuesto_origen"></div><div id="oems_rep"></div></div>
        </section>
        <section class="oem-card" id="seccion_destino">
            <h5 class="oem-card__title"><span class="badge badge-primary">2</span> Repuesto de destino</h5>
            <div class="oem-search">
                <input type="search" class="form-control" id="codigo_interno_destino" placeholder="Código interno de destino" onkeyup="if(event.key==='Enter') buscar_repuesto_destino()" disabled>
                <button type="button" class="btn btn-success" onclick="buscar_repuesto_destino()" title="Buscar destino" disabled><i class="fas fa-search"></i></button>
            </div>
            <div class="oem-content"><div id="repuesto_destino"></div><div id="oems_rep_destino"></div></div>
        </section>
        <aside class="oem-card oem-history-card">
            <h5 class="oem-card__title"><i class="fas fa-history"></i> Historial de clonaciones</h5>
            <div class="oem-history" id="historial_clonaciones_oems">
                <table class="table table-hover">
                    <thead><tr><th>Origen</th><th>Destino</th><th></th></tr></thead>
                    <tbody>
                        @forelse($clonaciones as $h)
                        <tr><td><strong>{{$h->codigo_interno_origen}}</strong></td><td><strong>{{$h->codigo_interno_destino}}</strong></td><td><button type="button" class="btn btn-outline-danger btn-sm oem-delete" onclick="deshacer_clonacion({{$h->id}},this)" title="Eliminar clonación"><i class="fas fa-trash"></i></button></td></tr>
                        @empty
                        <tr><td colspan="3" class="oem-empty"><i class="fas fa-inbox mr-1"></i> No hay clonaciones registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </aside>
    </div>
</div>
@endsection