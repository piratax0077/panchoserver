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
            let url='{{url("/reportes/transbank_mes")}}'+'/'+mes+"&"+año;
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                    $('#resumen').html(resp);
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
        function dame_detalle(fecha){
            let url='{{url("/reportes/transbank_dia")}}'+'/'+fecha;
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

        function imprimir_detalle(fecha){
            let url = '{{url("/reportes/imprimir_detalle_dia")}}'+'/'+fecha;
            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                    console.log(resp);
                },
                error: function(error){
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                }
            })
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
            <center><h4>Reporte Banco Estado</h4></center>
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
            <div id="resumen">RESUMEN MENSUAL</div>
            <div id="detalle">RESUMEN POR DIA</div>
        </div>

    </div>
@endsection
