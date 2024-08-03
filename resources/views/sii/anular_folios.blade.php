@extends('plantillas.app')
@section('titulo','ANULAR FOLIOS')
@section('javascript')
<script type="text/javascript">

    function buscar_doc(){
        alert("EN CONSTRUCCION");
        return false;
    }

    function revisar(){
        let mes=document.getElementById('periodo_mes').value;
        let año=document.getElementById('periodo_año').value;
        let tipo_dte=$('input[name="tipo_dte"]:checked').val().trim();
        let url='{{url("/sii/revisarfolios")}}'+'/'+mes+"&"+año+"&"+tipo_dte;
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
    #periodo{
        grid-column: 1/2;
        align-self: center;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;

    }

    #btn_revisar{
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

    #btn_buscar_documento{
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
        grid-template-columns: 65% 35%;
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
        @php
            $año_actual=date("Y");
        @endphp
        <div id="titulo">
            <center><h4>ANULAR FOLIOS</h4></center>
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
                    <option value="9">Setiembre</option>
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
            <div id="btn_revisar"><button class="btn btn-sm btn-success" onclick="revisar()">REVISAR</button></div>
            <div id="tipo_dte">
                <div style="padding-left:2px"><strong>Documento:</strong></div>

                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_boleta" value="39" checked><label for="radio_boleta">Boleta</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_factura" value="33" disabled><label for="radio_factura">Factura</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notacredito" value="61" disabled><label for="radio_notacredito">Nota Cred.</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notadebito" value="56" disabled><label for="radio_notadebito">Nota Deb.</label></div>
                <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_guiadespacho" value="52" disabled><label for="radio_guiadespacho">Guía Desp.</label></div>
            </div>
            <div id="btn_buscar_documento">
                <div><input type="text" id="num_docu" size="10px" placeholder="Núm Doc."></div>
                <div><button class="btn btn-sm btn-primary" onclick="buscar_doc()">Buscar</button></div>
            </div>
        </div>
        <div id="cuerpo">
            <div id="totales">folios sin usar</div>
            <div id="detalle">detalle</div>
        </div>

    </div>
@endsection
