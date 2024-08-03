@extends('plantillas.app')
@section('titulo','Bienvenida')
@section('javascript')
<script type="text/javascript">
    var mostrar_panel_buscar=false;

    function sii()
    {
         var url='{{url("sii/entrada")}}'; //Controlador autentica.php
        $.ajax({
            type:'GET',
            beforeSend: function () {
            $("#mensajes").html("<b>Autenticando...</b>");
            $("#div_semilla").html("Autenticando");
            },
            url:url,
            success:function(rpta){
                $("#mensajes").html("Autenticando... <b>Ya regresé...</b> ver console.log");
                console.log(rpta);

                /*
                $("#div_semilla").html(rpta);

                if(rpta.substring(0,1)=="E")
                {
                    $("#div_semilla").html(rpta)
                }else{
                    document.getElementById("semilla").value=rpta;
                }
                */
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }
        });
    }

    function ver_estadoUP()
    {
        let TrackID=document.getElementById("trackID").value;
        var url='{{url("sii/verestado")}}'+"/"+TrackID; //Controlador servicios_sii\sii_controlador
        $.ajax({
            type:'GET',
            beforeSend: function () {
            $("#mensajes").html("<b>Revisando Estado...</b>");
            },
            url:url,
            success:function(rpta){

            if(rpta===null && typeof(rpta)!=object)
            {
                console.log("js: regresó NULO");
            }else{
                if(typeof(rpta)=="string"){
                   if(rpta=="nulo")
                   {
                        console.log("js: SII no responde... REINTENTAR?");
                   }else{
                    rpta=JSON.parse(rpta);
                    $("#mensajes").html("estado: "+rpta.estado+" > "+rpta.mensaje);
                   }
                }

                if(Array.isArray(rpta)){
                    console.log("js: llegó un array de: "+rpta.length+" elementos");
                }
            }



            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }
        });

    }

    function ver_estado()
    {

        var url='{{url("sii/verestado")}}';
        $.ajax({
            type:'GET',
            beforeSend: function () {
            $("#mensajes").html("<b>Revisando Estado...</b>");
            },
            url:url,
            success:function(rpta){

            rpta=JSON.parse(rpta);
            $("#mensajes").html("estado: "+rpta.estado+" > "+rpta.mensaje);
                if(Array.isArray(rpta)){
                    console.log("js: llegó un array de: "+rpta.length+" elementos");
                }
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }
        });

    }

    function ver_estadoAv()
    {
        let TrackID=document.getElementById("trackID").value;
        var url='{{url("sii/verestado")}}'+"/"+TrackID; //Controlador sii_controlador
        $.ajax({
            type:'GET',
            beforeSend: function () {
            $("#mensajes").html("<b>Revisando Estado...</b>");
            },
            url:url,
            success:function(rpta){

            if(rpta===null && typeof(rpta)!=object)
            {
                console.log("js: regresó NULO");
            }else{
                if(typeof(rpta)=="string"){
                   if(rpta=="nulo")
                   {
                        console.log("js: SII no responde... REINTENTAR?");
                   }else{
                    rpta=JSON.parse(rpta);
                    $("#mensajes").html("estado: "+rpta.estado+" > "+rpta.mensaje);
                   }
                }

                if(Array.isArray(rpta)){
                    console.log("js: llegó un array de: "+rpta.length+" elementos");
                }
            }



            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }
        });

    }

    function emails()
    {
        let TrackID=document.getElementById("trackID").value;
        var url='{{url("sii/emails")}}'+"/"+TrackID;
        $.ajax({
            type:'GET',
            beforeSend: function () {
            $("#mensajes").html("<b>Revisando Emails...</b>");
            },
            url:url,
            success:function(rpta){
                $("#mensajes").html(rpta);
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }
        });

    }

    function enviar_correo(){
        let correo=document.getElementById("correo").value;
        let tipo_doc=document.getElementById("tipo_doc").value;
        let num_doc=document.getElementById("num_doc").value;
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
                console.log("Enviando correo...");
            },
            url:url,
            data:parametros,
            success:function(resp){
                console.log(resp);
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

</script>
@endsection

@section('style')
    <style>

    </style>
@endsection
@section('contenido_titulo_pagina')

<center><h2>PRUEBAS SII</h2></center><br>
@endsection
@section('contenido_ver_datos')
    <div class="container-fluid">
        <input type="text" id="correo" placeholder="correo">
        <input type="text" id="tipo_doc" placeholder="tipo documento">
        <input type="text" id="num_doc" placeholder="número documento">
        <button class="btn btn-success" onclick="enviar_correo()">Enviar Correo</button>
        <hr>
        <p>Track ID:</p>
        <input type="text" name="trackID" id="trackID">
        <button class="btn btn-primary" onclick="ver_estadoUP()">Ver Estado</button>
        <hr>
        <input type="file" name="archivo" id="archivo">
        <button class="btn btn-primary" onclick="ver_estado()">Ver Estado</button>
        <button class="btn btn-success" onclick="ver_estadoAv()">Ver Estado</button>
        <button class="btn btn-warning" onclick="emails()">Emails</button>
        <hr>

        @include('fragm.mensajes')
    </div>
@endsection
