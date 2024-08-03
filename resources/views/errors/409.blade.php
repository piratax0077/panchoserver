<!DOCTYPE html>
<html>
    <head>
        <title>Atención!!!</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato', sans-serif;
            }

            .container {
                text-align: center;
                vertical-align: middle;
                display: table-cell;

            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                color:red;
                font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body>

        <div class="container">

            <div class="content">
                    @include('fragm.cabecera_sistema')
                <div class="title">SU SESIÓN A EXPIRADO... (419)</div>
                <div class="alert alert-info" role="alert">
                        <p>Debe loguearse de nuevo...</p>
                        <a href="{{url('/login')}}"><h3>Login</h3></a>
                </div>
                @include('fragm.pie')
            </div> <!-- fin content -->

        </div> <!-- fin container -->
    </body>
</html>
