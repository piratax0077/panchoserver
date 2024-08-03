<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Maestro | @yield('titulo','Default')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0" maximum-scale=1.0, user-scalable=no>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{asset('DataTables-1.10.18/css/jquery.dataTables.min.css')}}" >
    <script type='text/javascript' src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <link rel="stylesheet" href="{{asset('msdropdown/css/dd.css')}}">
    <script type='text/javascript' src="{{asset('msdropdown/js/jquery.dd.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('css/mios.css')}}">
    <link rel="icon" href="{{asset('storage/imagenes/favicon1.png')}}" type="image/png" />
    <section>
        @yield('javascript')
    </section>
    <section>
        @yield('style')
    </section>
</head>
<body>
    <menucomponente></menucomponente>
    @include('fragm.menu')
    <section>
        @yield('contenido_titulo_pagina')
        @yield('mensajes')
        @yield('contenido_ingresa_datos')
        @yield('contenido_ver_datos')
    </section>
    @include('fragm.pie')
<!--
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
-->

    <script src="{{ asset('jquery/jquery-3.2.1.js')}}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.js')}}"></script>
    <script src="{{ asset('DataTables-1.10.18/js/jquery.dataTables.min.js')}}"></script>

</body>
</html>
