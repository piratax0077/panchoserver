@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')
<script type="text/javascript">

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
            beforeSend: function () {
                $('#totales').html("PROCESANDO...");
            },
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
        alert("pronto");
        return false;
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
    #fecha_reporte{
        grid-column: 1/2;
        align-self: center;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;

    }

    #btn_mostrar{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 2/3;
    }

    #tipo_dte{
        background-color: khaki;
        grid-column: 3/6;
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-auto-flow: column;
    }

    #btn_buscar_operacion{
        align-self: left;
        justify-self: center;
        display:grid;
        grid-column: 6/7;
        grid-template-columns: 1fr 1fr;
        grid-auto-flow: column;
    }
    #cuerpo{
        grid-column:1/7;
        grid-row:3/4;
        display:grid;
        grid-template-columns: 95% 5%;
        grid-auto-flow: column;
    }
    #totales{
        grid-column:1/2;
    }
    #detalle{
        grid-column:2/3;
    }
</style>
@endsection
@section('contenido_ingresa_datos')
    <div class="contenedor">
        <div class="titulazo">
            <center><h4>Documentos Generados</h4></center>
        </div>
        <div id="botones">
            <div id="fecha_reporte">
                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" value='<?php echo date("Y-m-d"); ?>' id="fecha" class="form-control  form-control-sm">
            </div>
            <div id="btn_mostrar">
                <button class="btn btn-sm btn-success" onclick="mostrar(0)">MOSTRAR</button>
            </div>
            <div id="tipo_dte">
                <div style="padding-left:2px"><strong>Documento:</strong></div>

                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_boleta" value="39" checked><label for="radio_boleta">Boleta</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_factura" value="33"><label for="radio_factura">Factura</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notacredito" value="61" disabled><label for="radio_notacredito">Nota Cred.</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notadebito" value="56" disabled><label for="radio_notadebito">Nota Deb.</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_guiadespacho" value="52" disabled><label for="radio_guiadespacho">Guía Desp.</label></div>
            </div>
            <div id="btn_buscar_operacion" style="display: none">
                <div><input type="text" id="num_operacion" size="10px" placeholder="Núm Op."></div>
                <div><button class="btn btn-sm btn-primary" onclick="mostrar(1)">Buscar</button></div>
            </div>
        </div>
        <div id="cuerpo">
            <div id="totales">Elija una fecha y presione MOSTRAR</div>
            <div id="detalle"></div>
        </div>

    </div>
@endsection
