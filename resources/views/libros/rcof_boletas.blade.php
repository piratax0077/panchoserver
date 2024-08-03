@extends('plantillas.app')
@section('titulo','RCOF Boletas')
@section('style')
<style>
    .contenedor{
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-template-rows: 30px 80px auto;
        grid-gap: 5px;
    }
    .titulo{
        grid-column: 1/5;
        grid-row:1/2;
    }

    p{
        margin-bottom: 0px;
    }
    .periodo{
        background-color: aquamarine;
        grid-column: 1/5;
        grid-row:2/3;
        display:grid;
        grid-template-columns: 10% 10% auto;
        grid-template-rows: 50px;
        grid-auto-flow: column;
        grid-gap: 10px;

    }
    #mes{
        align-self: center;
        justify-self: center;
        grid-column: 1/2;
        display:grid;
        grid-auto-flow: column;
    }
    #año{
        align-self: center;
        justify-self: center;
        grid-column: 2/3;
        display:grid;
        grid-auto-flow: column;
    }
    #botonera{
        align-self: center;
        justify-self: left;
        grid-column: 3/-1;
        display:grid;
        grid-auto-flow: column;
        grid-gap:5px;
    }
    #boton_crear{
        align-self: center;
        justify-self: center;
        /* visibility: hidden; */
    }
    #boton_buscar{
        align-self: center;
        justify-self: center;
        grid-column: 2/3;
        grid-row: 2/3;
    }
    #boton_procesar{
        align-self: center;
        justify-self: center;
        grid-column: 3/4;
        grid-row: 2/3;
    }
    #boton_enviar{
        align-self: center;
        justify-self: center;
        grid-column: 4/5;
        grid-row: 2/3;
    }
    #boton_estado{
        align-self: center;
        justify-self: center;
        grid-column: 5/6;
        grid-row: 2/3;
    }
    #boton_detalle{
        align-self: center;
        justify-self: center;
        grid-column: 6/7;
        grid-row: 2/3;
    }
    #boton_aumentar_secuencia{
        align-self: center;
        justify-self: center;
        grid-column: 7/8;
        grid-row: 2/3;
    }
    #listado_rcof{
        grid-column: 1/4;
        grid-row: 3/4;
        background-color:floralwhite;
    }
    #detalle_rcof{
        grid-column: 4/5;
        grid-row: 3/4;
        background-color:rgb(247, 225, 181);
    }

</style>
@endsection
@section('javascript')
<script type="text/javascript">
    var fecha_elegida='';
    window.onload = function(e){
        //Seleccionar el mes actual
        let cmes = document.getElementById("combo_mes");
        cmes.selectedIndex=new Date().getMonth();

    }

    function formatear_error(error){
            let max=300;
            let rpta=error.substring(0,max);
            return rpta;
        }

    function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });
    }

    function crear_rcof(){
        let mes = document.getElementById("combo_mes").value;
        let año = document.getElementById("combo_año").value;
        let url='{{url("/rcof/crear_rcof")}}'+'/'+mes+"&"+año;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                $('#listado_rcof').html('<br><br><center><h3 style="color:red">CREANDO...</h3></center>');
            },
            success:function(resp){
                $('#listado_rcof').html(resp);
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

    function listar_rcof(){
        let mes = document.getElementById("combo_mes").value;
        let año = document.getElementById("combo_año").value;
        let url='{{url("/rcof/listar_rcof")}}'+'/'+mes+"&"+año;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#listado_rcof').html(resp);
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

    function seleccionado(fecha){
        fecha_elegida=fecha;
    }

    function procesar(){

        if(fecha_elegida===undefined || fecha_elegida==''){
            Vue.swal({
                text: 'Busque y elija una fecha en el listado',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        let url='{{url("/rcof/procesar")}}'+'/'+fecha_elegida;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log(resp);
               
                let rs=JSON.parse(resp);
                
                if(rs.estado=='OK'){
                    fecha_elegida='';
                    listar_rcof();
                    Vue.swal({
                        text: 'PROCESADO',
                        position: 'top-end',
                        icon: 'info',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }else{
                    Vue.swal({
                        title:rs.estado,
                        text: rs.mensaje,
                        icon: 'error',
                    });
                }
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

    function enviar_sii(){
        if(fecha_elegida===undefined || fecha_elegida==''){
            Vue.swal({
                text: 'Busque y elija una fecha en el listado',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        let url='{{url("/rcof/enviar_sii")}}'+'/'+fecha_elegida;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function(){ //kaka
                espere("Enviando...");
            },
            success:function(resp){
                let rs=JSON.parse(resp);
                if(rs.estado=='OK'){
                    fecha_elegida='';
                    listar_rcof();
                    Vue.swal({
                        text: 'ENVIADO',
                        position: 'top-end',
                        icon: 'info',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }else{
                    Vue.swal({
                        title: rs.estado,
                        text: rs.mensaje,
                        icon: 'error',
                    });
                }
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

    function ver_estado(){
        if(fecha_elegida===undefined || fecha_elegida==''){
            Vue.swal({
                text: 'Busque y elija una fecha en el listado',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        let TrackID=document.getElementById("trackid-"+fecha_elegida).value;
        
        
        var url='{{url("sii/verestadotrack")}}'+"/"+TrackID;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                espere('Revisando TrackID '+TrackID);

            },
            success:function(rs){
                console.log(rs);
               
                Vue.swal.close();
             
                rs=JSON.parse(rs);
                actualizar_estado_BD(rs.estado,rs.mensaje,TrackID);
            },
            error: function(error){

                $('#listado_dte').html(formatear_error(error.responseText));
            }
        });


    }

    function actualizar_estado_BD(estado,mensaje,TrackID){
        let info=estado+"-"+mensaje+"-"+TrackID;
        let url='{{url("/rcof/actualizar_estado")}}'+"/"+info;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {

            },
            success:function(rs){
                Vue.swal.close();
                rs=JSON.parse(rs);
                Vue.swal({
                    title: rs.estado,
                    text: rs.mensaje,
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                if(rs.estado=='OK')
                    listar_rcof();
            },
            error: function(error){

                $('#listado_dte').html(formatear_error(error.responseText));
            }
        });
    }

    function detalle(){
        if(fecha_elegida===undefined || fecha_elegida==''){
            Vue.swal({
                text: 'Busque y elija una fecha en el listado',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        let url='{{url("/rcof/ver_detalle")}}'+"/"+fecha_elegida;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {

            },
            success:function(detalle){
                Vue.swal.close();
                $('#detalle_rcof').html(detalle);
            },
            error: function(error){
                $('#detalle_rcof').html(formatear_error(error.responseText));
            }
        });
    }

    function aumentar_secuencia(){
        Vue.swal({
            title: 'P R O N T O',
            icon: 'info',
        });
    }

</script>
@endsection
@section('contenido')
@php
    $año_actual=date("Y");
@endphp
<div class="contenedor">
    <div class="titulo">
        <center><h3>REPORTE CONSUMO DE FOLIOS RCOF</h3></center>
    </div>
    <div class="periodo">
        <div id="mes">
            <label for="mes">MES:&nbsp;</label>
            <select name="mes" id="combo_mes" class="form-control form-control-sm">
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
        </div>
        <div id="año">
            <label for="mes">AÑO:&nbsp;</label>
            <select name="mes" id="combo_año" class="form-control form-control-sm">
                @for($an=2020;$an<=$año_actual;$an++)
                    @if($an==$año_actual){
                        <option value="{{$an}}" selected>{{$an}}</option>
                    @else
                        <option value="{{$an}}">{{$an}}</option>
                    @endif
                @endfor
            </select>
        </div>
        <div id="botonera">
            <div id="boton_buscar">
                <button class="btn btn-success btn-sm" onclick="listar_rcof()">Buscar</button>
            </div>
            <div id="boton_procesar">
                <button class="btn btn-info btn-sm" onclick="procesar()">Procesar</button>
            </div>
            <div id="boton_enviar">
                <button class="btn btn-warning btn-sm" onclick="enviar_sii()">Enviar</button>
            </div>
            <div id="boton_estado">
                <button class="btn btn-primary btn-sm" onclick="ver_estado()">Ver Estado</button>
            </div>
            <div id="boton_detalle">
                <button class="btn btn-secondary btn-sm" onclick="detalle()">Detalle</button>
            </div>
            <div id="boton_aumentar_secuencia">
                <button class="btn btn-success btn-sm" onclick="aumentar_secuencia()">Secuencia ++</button>
            </div>
        </div>
    </div>
    <div id="listado_rcof">

    </div>
    <div id="detalle_rcof">
        Detalle de rangos y números...
    </div>

</div>



@endsection
