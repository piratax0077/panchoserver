<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <title>Inicio</title>
    <style>
        body{
            background: #000; 
            background-image: url("{{asset('storage/imagenes/fondoLogin.jpg')}}");
            background-repeat: no-repeat;
            background-position: top;
        }
        form{
            width: 30%;
            margin: 0px auto;
            border: 1px solid black;
            padding: 20px;
            color: white;
        }
        input[type=button]{
            margin-top: 200px;
            border: 1px solid white;
        }

        @media (max-width: 852px) {
        /* acá van las reglas CSS que aplican para este media query */
            form{
                width: 70%;
            }
        }

       
    </style>

    <script>
        
        //Se limpia el token para poder a ingresar
        window.onload = function(){
        if (window.localStorage.getItem('token') !== undefined && window.localStorage.getItem('token')){
           
            localStorage.removeItem('token');
            localStorage.removeItem('opt');
            localStorage.removeItem('user');
        }
    }
        //Funciones para generar el token para ingresar al servidor web
        
        function random() {
            return Math.random().toString(36).substr(2); // Eliminar `0.`
        };
        
        function token() {
            return random() + random(); // Para hacer el token más largo
        };

        function user(){
            var user = document.getElementById('user').value;
            return user;
        }
        function validar_usuario(){
            window.localStorage.setItem(
                    "token",token()
                );

            window.localStorage.setItem(
                "user",user()
            )
            
        }
    </script>
</head>
<body>
    <main class="text-center">
        @if (session('status'))
            <div class="alert alert-danger">
                {{ session('status') }}
            </div>
        @endif
        <div class="row w-100">
            <div class="col-md-12 col-sm-12">
                <form action="{{ route('loginserver') }}" method="post">
                    @csrf
                    <div class="form-group w-100">
                        <label for="user">Nombre de usuario</label>
                        <input id="user" name="user" id="user" type="text" class="form-control">
                    </div>
                    <div class="form-group w-100">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" class="form-control">
                    </div>
                    <input type="submit" value="Ingresar" onclick="validar_usuario()" onsubmit="validar_usuario()" class="btn btn-success btn-xs" >
                </form>
            </div>
        </div>
        
    </main>
    
</body>
</html>
