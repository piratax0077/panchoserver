<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.css')}}">
        <link rel="icon" href="{{asset('storage/imagenes/favicon1.png')}}" type="image/png" />
        <title>Login</title>

        <!-- Script -->
        <script>
          window.onload = function(e){
            document.getElementById("usuario").focus();
          };
        </script>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .pie{
              position: absolute;
              bottom: 0;
              width: 98%;
            }

        </style>
    </head>
<body>
<div class="container-fluid">
    <div class="modal-header text-center">
      <h3>PANCHO REPUESTOS</h3>
    </div><br>
    @include('fragm.mensajes')
      <form method="post" action="{{ url('login') }}" name="login_form">
        {{ csrf_field() }}
      <div class="row">
        <div class="col-sm-4 col-sm-offset-4">
          <center><input type="text" class="form-control" name="nombreUsuario" id="usuario" placeholder="Usuario" style="width:70%;"></center>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-sm-4 col-sm-offset-4">
        <center><input type="password" class="form-control" name="claveUsuario" placeholder="Clave" style="width:70%;"></center>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-sm-4 col-sm-offset-4">
          <center><input type="submit" name="btnLogin" id="btnIngresar" value="Ingresar" class="btn btn-info btn-md btn-block" style="width:70%;"/></center>
        </div>
      </div>
        <!--
        <p><button type="submit" class="btn btn-primary">Ingresar</button></p>
        -->
      </form>
    <br>
    <div class="row">
      <div class="col-md-10">
        <p>DEBE CONFIGURARSE EL LOCAL DONDE FUNCIONARÁ CADA TERMINAL</p>
        <p>ESTE TRABAJA CON EL LOCAL id_local = 1</p>
      </div>
    </div>
    <br>
    <div class="modal-footer pie">
      Arica-Chile
    </div>

</div>
<script src="{{ asset('bootstrap/js/bootstrap.js')}}"></script>
</body>

</html>
