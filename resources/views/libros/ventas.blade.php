@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')
    <script type="text/javascript">
        var xml="";
        var TrackID="";

        function formatearMontoCL(valor) {
            let numero = Number(valor || 0);
            return new Intl.NumberFormat('es-CL').format(numero);
        }

        function formatear_error(error){
            let max=300;
            let rpta=error.substring(0,max);
            return rpta;
        }

        function mostrar(){
            let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/libro/ventas_resumen")}}'+'/'+mes+"&"+año;

            $('#resumen').html('<div class="panel-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando resumen mensual...</div>');
            $('#detalle').html('<div class="panel-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando documentos no aceptados...</div>');

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
                    let html="<div class='panel-libro'>";
                    html+="<div class='panel-libro__header'><h5 class='panel-libro__titulo'>No aceptados del período</h5></div>";
                    html+="<div class='table-responsive'>";
                    html+="<table class='table table-sm table-hover tabla-libro tabla-libro--rechazados'>";
                    html+="<thead><tr><th>Fecha</th><th>Documento</th><th>Motivo</th><th class='text-right'>Total</th></tr></thead><tbody>";
                    let total_rechazados=0;

                    if (rechazados.length === 0) {
                        html += "<tr><td colspan='4' class='text-center text-muted py-3'>No hay documentos rechazados/reparo para este período.</td></tr>";
                    } else {
                        rechazados.forEach(function(r){
                            let docu="";
                            total_rechazados+=parseInt(r.total_doc);
                            if(r.xml.substring(0,2)=='39'){
                                docu="Boleta "+r.num_doc;
                            }else{
                                docu="Factura "+r.num_doc;
                            }
                            html+="<tr><td>"+r.fecha_doc+"</td><td>"+docu+"</td><td><span class='badge badge-warning mr-1'>"+r.estado_sii+"</span>"+r.resultado_envio+"</td><td class='text-right'>"+formatearMontoCL(parseInt(r.total_doc))+"</td></tr>";
                        });
                    }

                    html+="</tbody><tfoot><tr><td colspan='3' class='text-right'><strong>Total no aceptados:</strong></td><td class='text-right'><strong>"+formatearMontoCL(parseInt(total_rechazados))+"</strong></td></tr></tfoot></table>";
                    html+="</div></div>";
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
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap');

    :root {
        --lv-bg: linear-gradient(145deg, #f5f7fb 0%, #eef3f9 45%, #e7eef8 100%);
        --lv-ink: #0f172a;
        --lv-muted: #5e6a7d;
        --lv-primary: #0e7490;
        --lv-primary-soft: #d9f2f7;
        --lv-surface: #ffffff;
        --lv-border: #d8e1ee;
        --lv-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .contenedor {
        margin: 0;
        padding: 12px;
        background: var(--lv-bg);
        border: 1px solid var(--lv-border);
        border-radius: 14px;
        font-family: 'Manrope', sans-serif;
    }

    .lv-titulo {
        margin: 0 0 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: linear-gradient(100deg, #101f3f 0%, #0e7490 100%);
        color: #fff;
        font-weight: 800;
        font-size: 26px;
        letter-spacing: .3px;
        text-align: center;
        box-shadow: var(--lv-shadow);
    }

    #botones {
        background: var(--lv-surface);
        border: 1px solid var(--lv-border);
        border-radius: 12px;
        box-shadow: var(--lv-shadow);
        padding: 14px;
        margin-bottom: 14px;
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    #periodo {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-right: auto;
    }

    #periodo label {
        margin: 0;
        color: var(--lv-muted);
        font-weight: 700;
    }

    #periodo_mes,
    #periodo_año {
        min-width: 120px;
        border-radius: 8px;
        border: 1px solid #cfd9e6;
    }

    #btn_mostrar {
        display: inline-flex;
    }

    #btn_mostrar .btn {
        border-radius: 8px;
        font-weight: 700;
        padding: 7px 16px;
    }

    #btn_procesar .btn {
        border-radius: 8px;
        font-weight: 700;
        padding: 7px 16px;
    }

    #cuerpo {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 14px;
    }

    .panel-cargando {
        background: var(--lv-surface);
        border: 1px dashed #bfd0e4;
        border-radius: 10px;
        padding: 30px 18px;
        color: var(--lv-muted);
        text-align: center;
        font-weight: 700;
    }

    .panel-libro {
        background: var(--lv-surface);
        border: 1px solid var(--lv-border);
        border-radius: 12px;
        box-shadow: var(--lv-shadow);
        overflow: hidden;
    }

    .panel-libro__header {
        background: linear-gradient(90deg, #eff8ff 0%, #f8fbff 100%);
        border-bottom: 1px solid var(--lv-border);
        padding: 10px 12px;
    }

    .panel-libro__titulo {
        margin: 0;
        color: var(--lv-ink);
        font-size: 20px;
        font-weight: 800;
    }

    .tabla-libro {
        margin: 0;
    }

    .tabla-libro thead th {
        background: var(--lv-primary-soft);
        border-top: 0;
        color: #0b3c4a;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .tabla-libro td {
        color: #1f2937;
        font-size: 13px;
        vertical-align: middle;
    }

    .tabla-libro tfoot td {
        background: #f7fbff;
        border-top: 2px solid #c8deef;
    }

    .transbank {
        color: #64748b;
    }

    .badge-warning {
        background: #ffd77a;
        color: #583000;
    }

    @media (max-width: 992px) {
        #cuerpo {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .contenedor {
            padding: 8px;
        }

        .lv-titulo {
            font-size: 22px;
        }

        #periodo {
            width: 100%;
        }

        #periodo_mes,
        #periodo_año {
            width: calc(50% - 6px);
            min-width: 0;
        }
    }
</style>
@endsection
@section('contenido_ingresa_datos')
    <div class="contenedor">
        @php
            $año_actual=date("Y");
        @endphp
        <h4 class="lv-titulo">Libro Ventas</h4>
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
            <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()"><i class="fa fa-search"></i> Mostrar</button></div>
            <div id="btn_procesar">
                @if(Session::get('rol')=='S')
                    <button class="btn btn-sm btn-primary" onclick="procesar()" style="display:none">PROCESAR</button>
                @endif
            </div>
        </div>
        <div id="cuerpo">
            <div id="resumen"><div class="panel-cargando">Selecciona período y pulsa Mostrar para ver el resumen.</div></div>
            <div id="detalle"><div class="panel-cargando">Aquí verás el detalle de no aceptados del período.</div></div>
        </div>

    </div>
@endsection
