@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')
    <script type="text/javascript">
        var xml="";
        var TrackID="";

        function formatear_error(error){
            let max=300;
            let rpta=error.substring(0,max);
            return rpta;
        }

        function mostrar(){
            let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/libro/ventas_resumen")}}'+'/'+mes+"&"+año;
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                    $('#resumen').html(resp);
                    dame_rechazados_mes();
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

        function dame_rechazados_mes(){
            let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/reportes/rechazados_mes")}}'+'/'+mes+"&"+año;
            $.ajax({
                type:'GET',
                url:url,
                success:function(json){
                    let rechazados=JSON.parse(json);
                    let html="<h5>NO ACEPTADOS (RECHAZO, REPARO, ETC) DEL PERÍODO</h5><br>";
                    html+="<table class='table table-sm table-hover'>";
                    html+="<thead><th width='80px'>Fecha</th><th width='50px'>Documento</th><th width='150px'>Motivo</th><th width='50px'>Total</th></thead><tbody>"
                    let total_rechazados=0;
                    rechazados.forEach(function(r){
                        let docu="";
                        total_rechazados+=parseInt(r.total_doc);
                        if(r.xml.substring(0,2)=='39'){
                            docu="Boleta "+r.num_doc;
                        }else{
                            docu="Factura "+r.num_doc;
                        }
                        html+="<tr><td>"+r.fecha_doc+"</td><td>"+docu+"</td><td>"+r.estado_sii+": "+r.resultado_envio+"</td><td>"+parseInt(r.total_doc)+"</td></tr>";
                    });
                    html+="<tr><td></td><td></td><td class='text-right'><b>TOTAL:</b></td><td>"+parseInt(total_rechazados)+"</td></tr>";
                    html+="</tbody></table>";
                    $('#detalle').html(html);
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

        function dame_detalle($tipo_dte){
            let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/libro/ventas_detalle")}}'+'/'+mes+"&"+año+"&"+$tipo_dte;
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                    $('#detalle').html(resp);
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

        function procesar(){
            Vue.swal({
                title: 'YA NO ES NECESARIO',
                icon: 'info',
            });

            let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/libro/ventas_generar_xml")}}'+'/'+mes+"&"+año;
            $.ajax({
                type:'GET',
                beforeSend: function () {
                    Vue.swal({
                        title: 'PROCESANDO...',
                        icon: 'info',
                    });
                },
                url:url,
                success:function(resp){
                    Vue.swal.close();
                    //$('#resumen').html(resp);
                    //return false;
                    let r=JSON.parse(resp);
                    if(r.estado=='OK'){
                        xml=r.archivo;
                        Vue.swal({
                            title: r.estado,
                            text: 'Generado '+r.archivo,
                            icon: 'info',
                        });
                        $("#btn_procesar").html('<button class="btn btn-sm btn-primary" onclick="enviar_sii()">Enviar al SII</button>');
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

        function enviar_sii(){
            if(xml==""){
                Vue.swal({
                    title: 'ERROR',
                    text: 'XML no generado...',
                    icon: 'error',
                });
                return false;
            }

            let url='{{url("/libro/ventas_enviar_sii")}}'+'/'+xml;
            $.ajax({
                type:'GET',
                beforeSend: function () {
                    Vue.swal({
                        title: 'PROCESANDO...',
                        icon: 'info',
                    });
                },
                url:url,
                success:function(resp){
                    Vue.swal.close();
                    let r=JSON.parse(resp);
                    if(r.estado=='OK'){
                        TrackID=r.trackid;
                        Vue.swal({
                            title: r.estado,
                            text: r.mensaje+' TrackID: '+TrackID,
                            icon: 'info',
                        });
                        $("#btn_procesar").html('<button class="btn btn-sm btn-primary" onclick="ver_estado()">Ver Estado</button>');
                    }else{
                        Vue.swal({
                            title: r.estado,
                            text: r.mensaje,
                            icon: 'warning',
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

        function ver_estado(){
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

    </script>
@endsection
@section('style')
<style>
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
    #periodo{
        grid-column: 1/2;
        align-self: center;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;

    }
    #periodo_mes{

    }
    #btn_mostrar{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 2/3;
    }

    #btn_imprimir{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 6/7;
    }

    #cuerpo{
        grid-column:1/7;
        grid-row:3/4;
        display:grid;
        grid-template-columns: 50% 50%;
        grid-auto-flow: column;
    }
    #resumen{
        grid-column:1/2;
    }
    #detalle{
        grid-column:2/3;
    }
    .transbank{
        color:grey;
    }
</style>
@endsection
@section('contenido_ingresa_datos')
    <div class="contenedor">
        @php
            $año_actual=date("Y");
        @endphp
        <div class="titulazo">
            <center><h4>Libro Ventas</h4></center>
        </div>
        <div id="botones">
            <div id="periodo">
                <label for="periodo_mes">Periodo:</label>
                <select name="periodo_mes" id="periodo_mes" class="form-control form-control-sm">
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
                <select name="periodo_año" id="periodo_año" class="form-control form-control-sm">
                    @for($an=2020;$an<=$año_actual;$an++)
                    @if($an==$año_actual){
                        <option value="{{$an}}" selected>{{$an}}</option>
                    @else
                        <option value="{{$an}}">{{$an}}</option>
                    @endif
                @endfor
                </select>
            </div>
            <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
            <div id="btn_procesar">
                @if(Session::get('rol')=='S')
                    <button class="btn btn-sm btn-primary" onclick="procesar()" style="display:none">PROCESAR</button>
                @endif
            </div>
        </div>
        <div id="cuerpo">
            <div id="resumen">resumen</div>
            <div id="detalle">detalle...</div>
        </div>

    </div>
@endsection
