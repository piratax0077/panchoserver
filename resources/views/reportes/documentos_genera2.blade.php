@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')
<script type="text/javascript">
    var id_pago_actualizar=0;
    function enter_buscar(e){
        let keycode = e.keyCode;
        if(keycode=='13'){
            buscar_documentos();
        }
    }

    function buscar_documentos(){
        id_pago_actualizar=0;
        let buscado=document.getElementById("buscar_valor").value.trim();
        if(buscado.length==0){
            Vue.swal({
                title: 'ATENCIÓN!',
                text: 'Valor buscado vacio',
                icon: 'error',
            });
            return false;
        }

        //normalizar los signos "/" y "&" para poderlos pasar por la ruta
        if(buscado.indexOf("/")){
            buscado=buscado.replace(/\//g,"_slash_");
        }
        if(buscado.indexOf("&")){
            buscado=buscado.replace(/\&/g,"_ampersand_");
        }
        let opcion= $('input[name="buscar_por"]:checked').val().trim();

        /*
        switch (opcion){
            case "documento":

            break;
            case "fecha":

            break;
            case "operacion":

            break;
            case "monto":

            break;
        }
        */

        let url='{{url("/reportes/buscar_documentos")}}'+'/'+opcion+"&"+buscado;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend:function(){
                $('#respuesta').html("<h3>Procesando...</h3>");
            },
            success:function(resp){
                $('#respuesta').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });

    }

    function cargar_pago(id_pago){
        id_pago_actualizar=id_pago;
        let url='{{url("/ventas/cargar_pago")}}'+'/'+id_pago;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend:function(){

            },
            success:function(pago){
                let p=JSON.parse(pago);
                document.getElementById("forma_pago_select").value=p.id_forma_pago;
                document.getElementById("fecha_pago").value=p.fecha_pago;
                document.getElementById("referencia_pago").value=p.referencia;
                document.getElementById("activo_pago").checked=p.activo;
                $("#modificar-pago-modal").modal("show");
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

    function actualizar_pago(){
        let id_forma_pago=document.getElementById("forma_pago_select").value;
        let fecha_pago=document.getElementById("fecha_pago").value;
        let referencia_pago=document.getElementById("referencia_pago").value.trim();
        let activo_pago=0;
        if(document.getElementById("activo_pago").checked) activo_pago=1;

        //validaciones
        if(fecha_pago.length==0){
            Vue.swal({
                title: 'ATENCIÓN!',
                text: 'Fecha vacía o no válida',
                icon: 'error',
            });
            return false;
        }

        if(referencia_pago.length==0){
            Vue.swal({
                title: 'ATENCIÓN!',
                text: 'Referencia vacía',
                icon: 'error',
            });
            return false;
        }

        var url="{{url('ventas/actualizar_pago')}}";
        var parametros={id_pago_actualizar,id_forma_pago,fecha_pago,referencia_pago,activo_pago};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            success:function(rpta){
                
                r=JSON.parse(rpta);
                $("#modificar-pago-modal").modal("hide");
                if(r.estado=='OK'){
                    id_pago_actualizar=0;
                    buscar_documentos();
                    Vue.swal({
                        text: 'Pago actualizado',
                        position: 'center',
                        icon: 'info',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }
                if(r.estado=='ERROR'){
                    Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(r.mensaje),
                        icon: 'error',
                    });
                }

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


    function mostrar(op){
        let valor1="";
        let valor2="";
        if(op==0){
            valor1=document.getElementById("fecha").value;
            valor2 = $('input[name="tipo_dte"]:checked').val().trim();
        }

        if(op==1){
            valor1 = "x";
            valor2 = document.getElementById("num_operacion").value;
            if(valor2.trim().length==0 || isNaN(valor2)){
                Vue.swal({
                    title: 'ERROR',
                    text: "Ingrese número de operación o autorización",
                    icon: 'error',
                });
            }
        }


        let url='{{url("/reportes/detalle_documentosgenerados")}}'+'/'+valor1+"&"+valor2;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#totales').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });
    }

    function imprimir(){
        Vue.swal({
                title: 'P R O N T O',
                icon: 'warning',
            });
    }

    function imprimir_xml(xml){
        let url='{{url("imprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
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
                    title: 'ERROR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });

            }

        });

    }

    function formatear_error(error){
        let max=300;
        let rpta=error.substring(0,max);
        return rpta;
    }

    function dame_formas_pago()
    {
        var url='{{url("ventas/dame_forma_pago_modificar_pagos")}}';
        $.ajax({
            type:'GET',
            beforeSend: function () {
            },
            url:url,
            success:function(formas){
                let formapago=JSON.parse(formas);
                $('#forma_pago_select option').remove();
                formapago.forEach(function(fp){
                    $('#forma_pago_select').append('<option value="'+fp.id+'">'+fp.formapago.toUpperCase()+'</option>');
                });
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

    window.onload = function(e){
        dame_formas_pago();
    }
</script>
@endsection
@section('style')
<style>
    .table-wrapper{
        overflow-y:scroll;
        height: 100px;
    }
    .table-wrapper th{
        position:sticky;
        top: 25px;
    }
    .contenedor{
        margin: 0px;
        display:grid;
        grid-template-columns: repeat(6,1fr);
        grid-template-rows: 30px 40px auto;
        grid-gap: 5px;
    }
    #titulo{
        grid-column: 1/7;
        background-color: cornflowerblue;
    }
    #botones{
        grid-column: 1/7;
        grid-row: 2/3;
        background-color:gainsboro;
        display:grid;
        grid-template-columns: repeat(6,1fr);
        grid-auto-flow: column;
    }

    #buscar_por{
        background-color: khaki;
        grid-column: 1/5;
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-auto-flow: column;
    }

    #btn_buscar_por{
        align-self: left;
        justify-self: center;
        display:grid;
        grid-column: 5/7;
        grid-template-columns: 1fr 1fr;
        grid-auto-flow: column;
    }
    #cuerpo{
        grid-column:1/7;
        grid-row:3/4;
        display:grid;
        grid-template-columns: 65% 35%;
        grid-auto-flow: column;
    }
    #respuesta{
        grid-column:1/2;
    }
    #detalle{
        grid-column:2/3;
    }
    .enlinea{
        display:grid;
        grid-auto-flow: column;
    }
</style>
@endsection
@section('contenido_ingresa_datos')
    <div class="contenedor">
        <div class="titulazo">
            <center><h4>Buscar Documentos</h4></center>
        </div>
        <div id="botones">
            <div id="buscar_por">
                <div style="padding-left:2px"><strong>Buscar por:</strong></div>

                <div style="padding-left:2px"><input type="radio" name="buscar_por" id="radio_documento" value="documento" checked><label for="radio_documento">Documento</label></div>
                <div style="padding-left:2px"><input type="radio" name="buscar_por" id="radio_fecha" value="fecha"><label for="radio_fecha">Fecha</label></div>
                <div style="padding-left:2px"><input type="radio" name="buscar_por" id="radio_operacion" value="operacion"><label for="radio_operacion">Operación</label></div>
                <div style="padding-left:2px"><input type="radio" name="buscar_por" id="radio_monto" value="monto"><label for="radio_monto">Pago</label></div>
            </div>
            <div id="btn_buscar_por">
                <div><input class="form-control form-control-sm text-right" type="text" id="buscar_valor" onkeyup="enter_buscar(event)" size="20px" placeholder="Escriba"></div>
                <div><button class="btn btn-sm btn-primary"  onclick="buscar_documentos()">Buscar</button></div>
            </div>
        </div>
        <div id="cuerpo">
            <div id="respuesta">construyendo</div>
            <div id="detalle"></div>
        </div>

    </div>

<!-- Modificar Pago -->
<div class="modal fade" tabindex="-1" role="dialog" id="modificar-pago-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">MODIFICAR PAGO</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group-sm">
                <div class="col-8 enlinea">
                    <label for="forma_pago_select">Forma de Pago:</label>
                    <select class="form-control form-control-sm" name="forma_pago_select" id="forma_pago_select">
                        <option value="0">Elegir</option>
                    </select>
                </div>
                <div class="col-8 mt-2 enlinea">
                    <label for="fecha_pago">Fecha:</label>
                    <input type="date" name="fecha_pago" id="fecha_pago" class="form-control form-control-sm">
                </div>
                <div class="col-8 mt-2 enlinea">
                    <label for="referencia_pago">Referencia:</label>
                    <input class="form-control form-control-sm" type="text" id="referencia_pago">
                </div>
                <div class="col-4 mt-2 enlinea" >
                    <label for="activo_pago">Pago Activo: </label>
                    <input type="checkbox" id="activo_pago">
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="actualizar_pago()">ACTUALIZAR</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
        </div>
      </div>
    </div>
  </div> <!-- FIN Modificar Pago -->
@endsection
