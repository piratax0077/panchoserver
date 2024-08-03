@extends('plantillas.app')
  @section('titulo','Ambiente Certificación')
  @section('javascript')
  <script type="text/javascript">
    function basico(){

      var url='{{url("/sii/basico")}}';

      $.ajax({
        type:'GET',
        beforeSend: function () {
          $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            console.log(resp);
            $('#mensajes').html(resp);
        },
        error: function(error){
          $('#mensajes').html(error.responseText);
        }

      });
    }

    function libroventas(){

        var url='{{url("/sii/libroventas")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function librocompras(){

        var url='{{url("/sii/librocompras")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function setguias(){

        var url='{{url("/sii/setguias")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function libroguias(){

        var url='{{url("/sii/libroguias")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function simulacion(){

        var url='{{url("/sii/simulacion")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function intercambio(){

        var url='{{url("/sii/intercambio")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }

    function basico_boletas(){
        var url='{{url("/sii/basico_boletas")}}';
        $.ajax({
            type:'GET',
            beforeSend: function () {
                $('#mensajes').html("Procesando...");
            },
            url:url,
            success:function(resp){
                $('#mensajes').html(resp);
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }

        });
    }

    function rcof_boletas(){
        var url='{{url("/sii/rcof_boletas")}}';
        $.ajax({
            type:'GET',
            beforeSend: function () {
                $('#mensajes').html("Procesando...");
            },
            url:url,
            success:function(resp){
                $('#mensajes').html(resp);
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
            }

        });
    }

    function generarPDF(){

        var url='{{url("/sii/generarPDF")}}';

        $.ajax({
        type:'GET',
        beforeSend: function () {
            $('#mensajes').html("Procesando...");
        },
        url:url,
        success:function(resp){
            $('#mensajes').html(resp);
        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }

        });
    }
</script>

@endsection
@section('contenido_titulo_pagina')
    <center>
        <h3>AMBIENTE CERTIFICACIÓN</h3>
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" width="100px"><br>
    </center><br>

@endsection
@section('contenido_ingresa_datos')
    @include('fragm.mensajes')<br>
    <button class="btn btn-warning" onclick="basico()">Set Pruebas Básico Facturas</button>|
    <button class="btn btn-success" onclick="libroventas()">Libro Ventas</button>|
    <button class="btn btn-info" onclick="librocompras()">Libro Compras</button>|
    <button class="btn btn-default" onclick="setguias()">Set Guías Despacho</button>|
    <button class="btn btn-primary" onclick="libroguias()">Libro Guías Despacho</button><br><br>
    <hr>
    <button class="btn btn-danger" onclick="simulacion()">Simulación</button>|
    <button class="btn btn-success" onclick="intercambio()">Intercambio</button>|
    <button class="btn btn-info" onclick="generarPDF()">Generar PDF's</button><br><br>
    <hr>

    <button class="btn btn-warning" onclick="basico_boletas()">Set Pruebas Básico Boletas</button>
    <button class="btn btn-success" onclick="rcof_boletas()">RCOF Boletas</button><br><br>

@endsection
