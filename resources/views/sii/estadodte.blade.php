@extends('plantillas.app')
@section('titulo','Estado DTE')
@section('style')
<style>
    .contenedor{
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-template-rows: 30px 80px auto;
        grid-gap: 4px;
    }
    .titulo{
        grid-column: 1/5;
    }
    .periodo{
        background-color: aquamarine;
        grid-column: 1/4;
        grid-row:2/3;
        display:grid;
        grid-template-columns: 2fr 2fr 1fr;
        grid-template-rows: 25px 50px;
        grid-auto-flow: column;
        border: 1px solid black;
        border-radius: 10px;
        padding: 3px;
    }

    #tipo_dte{
        background-color: khaki;
        grid-column: 1/4;
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-auto-flow: column;
    }
    #fechainicial{
        align-self: center;
        justify-self: center;
        grid-column: 1/2;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: column;
    }
    #fechafinal{
        align-self: center;
        justify-self: center;
        grid-column: 2/3;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: column;
    }
    #boton_buscar{
        align-self: center;
        justify-self: center;
        grid-column: 3/4;
        grid-row: 2/3;
    }
    #listado_dte{
        grid-column: 1/5;
        grid-row: 3/4;
        background-color:floralwhite;
    }


    p{
        margin-bottom: 0px;
    }

</style>
@endsection
@section('javascript')
<script type="text/javascript">
    function enviar_correo(datos){
        let tipo_doc = $('input[name="tipo_dte"]:checked').val().trim();
        let dato=datos.split("&");
        let num_doc=dato[0];
        let correo=dato[1];
        let docu="";
        if(tipo_doc==33) docu="Enviar Factura N° ";
        if(tipo_doc==39) docu="Enviar Boleta N° ";
        if(tipo_doc==61) docu="Enviar Nota Crédito N° ";
        if(tipo_doc==56) docu="Enviar Nota Débito N° ";
        if(tipo_doc==52) docu="Enviar Guía Despacho N° ";

        console.log(tipo_doc);
        Vue.swal({
            html: docu+num_doc+" a <input type='text' id='que_correo' value='"+correo+"'>",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'CONTINUAR',
            cancelButtonText: 'CANCELAR'
        }).then((result) => {
            if (result.isConfirmed) {
                correo=document.getElementById("que_correo").value.trim();
                var url="{{url('/enviarcorreo')}}";
                var parametros={correo_destino:correo,tipo_doc:tipo_doc,num_doc:num_doc};
                
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type:'POST',
                    beforeSend: function () {
                        Vue.swal({
                            text: 'Enviando correo...',
                            position: 'center',
                            icon: 'info'
                        });
                    },
                    url:url,
                    data:parametros,
                    success:function(resp){
                        console.log(resp);
                        
                        Vue.swal({
                            text: resp,
                            position: 'center',
                            icon: 'info'
                        });
                    },
                    error: function(error){
                        console.log(error.responseText);
                    }
                });
            }
        })
        /*
        console.log("correo: "+correo);
        console.log("tipo_doc: "+tipo_doc);
        console.log("num_doc: "+num_doc);

        return false;

        */

    }


    function listar_dtes(){
        //documento seleccionado
        let tipo_dte=$('input[name="tipo_dte"]:checked').val().trim();
        let fechainicial = document.getElementById("ifechainicial").value;
        let fechafinal = document.getElementById("ifechafinal").value;
        if(fechainicial.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Inicial',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
        if(fechafinal.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
        if(fechainicial>fechafinal){
            Vue.swal({
                text: 'Fecha Inicial debe ser menor o igual a Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
        let url='{{url("/ventas/damedteporfechas")}}'+'/'+tipo_dte+"/"+fechainicial+'/'+fechafinal;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                
                $('#listado_dte').html(resp);
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

    function imprimir(xml){
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
                console.log(resp);
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

    function ver_estado(TrackID){
       
        if(parseInt(TrackID)==0 || isNaN(parseInt(TrackID))){
            Vue.swal({
                title: 'ERROR',
                text: "No se puede revisar ESTADO, No hay TrackID Válido.",
                icon: 'error',
            });
            return false;
        }

        let tipoDTE = $('input[name="tipo_dte"]:checked').val().trim();
        var url='{{url("sii/verestado")}}'+"/"+tipoDTE+"&"+TrackID; //Controlador servicios_sii\sii_controlador ver_estadoUP
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                Vue.swal({
                    title: 'REVISANDO',
                    icon: 'info',
                });
            },
            success:function(rs){
                console.log(rs);
                // return false;
                Vue.swal.close();
                //rs=JSON.parse(rs);
                listar_dtes();
            },
            error: function(error){

                $('#listado_dte').html(formatear_error(error.responseText));
            }
        });
    }

    function ver_estadotrack(){

        let TrackID=document.getElementById('trackid').value;
        var url='{{url("sii/verestadotrack")}}'+"/"+TrackID;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                Vue.swal({
                    title: 'REVISANDO',
                    icon: 'info',
                });
            },
            success:function(rs){
                console.log(rs);
                Vue.swal.close();
                rs=JSON.parse(rs);
                Vue.swal({
                    title: 'RESULTADO. '+rs.estado,
                    icon: 'info',
                    text: rs.mensaje
                });
            },
            error: function(error){

                $('#listado_dte').html(formatear_error(error.responseText));
            }
        });
    }


    function formatear_error(error){
        let max=300;
        let rpta=error.substring(0,max);
        return rpta;
    }

    function limpiar_sesion(){
        let url='{{url("ventas/limpiarsesion")}}';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log("OK");
            },
            error: function(error){
                console.log("ERROR");
            }

        });
    }
    
    function set_xml_imprimir(xml){
        let url='{{url("ventas/setxmlimprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log("OK");
            },
            error: function(error){
                console.log("ERROR");
            }

        });
    }
</script>
@endsection
@section('contenido')

<div class="contenedor">
    <div class="titulo">
        <center><h4 class="titulazo">ESTADO DTE</h4></center>
    </div>
    <div class="periodo">
        <div id="tipo_dte">
            <div style="padding-left:2px"><strong>Documento:</strong></div>

            <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_boleta" value="39" checked><label for="radio_boleta">Boleta</label></div>
            <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_factura" value="33"><label for="radio_factura">Factura</label></div>
            <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notacredito" value="61"><label for="radio_notacredito">Nota Cred.</label></div>
            <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notadebito" value="56" disabled><label for="radio_notadebito">Nota Deb.</label></div>
            <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_guiadespacho" value="52"><label for="radio_guiadespacho">Guía Desp.</label></div>
        </div>
        <div id="fechainicial">
            <label for="fechainicial">Fecha Inicial:</label>
            <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control form-control-sm">
        </div>
        <div id="fechafinal">
            <label for="fechafinal">Fecha Final:</label>
            <input type="date" name="ifechafinal" value='@php echo date("Y-m-d"); @endphp'  id="ifechafinal" class="form-control form-control-sm">
        </div>
        <div id="boton_buscar">
            <button class="btn btn-success btn-lg" onclick="listar_dtes()">Buscar</button>
        </div>
        <div id="ver_trackid">
            <input type="text" name="trackid" id="trackid" placeholder="TrackID">
            <button class="btn btn-sm btn-info" onclick="ver_estadotrack()">ver</button>
        </div>
    </div>
    <div id="listado_dte">

    </div>

</div>



@endsection
