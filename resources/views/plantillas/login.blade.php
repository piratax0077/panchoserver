<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="icon" href="{{asset('storage/imagenes/favicon1.png')}}" type="image/png" />
    <!-- Styles  -->
    @php
        $entorno = App::environment();
        /*
            08dic2020
            Modifiqué el archivo Mix.php linea 46 para que pueda encontrar el manifiesto del cambio de versionado para app.js y app.css
            en /repuestos/vender/laravel/framework/src/illuminate/Foundation/Mix.php
        */
        if($entorno=='local'){
            echo "<link href=\"".asset('css/app.css')."\" rel='stylesheet'>";
        }elseif($entorno=='production'){
            echo "<link href=\"".mix('app.css','dist')."\" rel='stylesheet'>";
        }
    @endphp

    @yield('javascript')
</head>
<body>
    <div id="app">
        <main class="py-4">
            @yield('contenido')
        </main>
    </div>
     <!-- Scripts  -->
     @php
        if($entorno=='local'){
            echo "<script src=\"".asset('js/app.js')."\" defer></script>";
        }elseif($entorno=='production'){
            echo "<script src=\"".mix('app.js','dist')."\" defer></script>";
        }
    @endphp
</body>
</html>
