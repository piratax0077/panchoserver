@extends('plantillas.app')
@section('titulo','Usuarios')
@section('javascript')
    <script>

        window.onload = function(){
            mostrar();
        }

        function espere(mensaje){
            Vue.swal({
                icon:'info',
                text:mensaje,
                title:'Informacion'
            })
        }

        function cargar_usuario(id){
            let url = '/usuarios/usuario_servidor/'+id;
            $.ajax({
                type:'get',
                url:url,
                beforeSend: function(){
                    espere('cargando');
                },
                success:function(usuario){
                    console.log(usuario);
                    Vue.swal.close();
                    $('#user').val('');
                    $('#usuario').empty();
                    $('#estado').empty();
                    $('#confirmar_password').empty();
                    $('#hora').empty();
                    $('#created_at').empty();
                    $('#fecha_login').empty();
                    $('#direccion_ip').empty();
                    $('#usuario').append(usuario.user);
                    $('#hora').append(usuario.hora);
                    $('#created_at').append(usuario.fecha);
                    $('#fecha_login').append(usuario.fecha_ingreso);
                    $('#direccion_ip').append(usuario.direccion_ip);
                    if(usuario.activo == 1){
                        $('#estado').append('Activo');
                    }else{
                        $('#estado').append('Inactivo');
                    }
                    $('#opciones').empty();
                    $('#opciones').append(`
                    <button class="btn btn-warning btn-sm" data-toggle='modal' data-target="#modalUsuario" onclick="editar_usuario(`+usuario.id+`)" ><i class="fa-solid fa-pen-ruler"></i> </button>
                    <button class="btn btn-danger btn-sm" onclick="eliminar_usuario(`+usuario.id+`)"><i class="fa-solid fa-trash"></i> </button>
                    `);
                },
                error: function(error){
                    console.log(error);
                }
            })
        }

        function editar_usuario(usuario_id){
            let url = '/usuarios/editar_usuario_servidor/'+usuario_id;

            $.ajax({
                type:'get',
                url:url,
                beforeSend: function(){

                },
                success: function(usuario){
                    $('#user').val(usuario.user);
                    $('#passworduser').val('');
                    $('#confirmar_password').empty();
                    $('#confirmar_password').append(`
                    <div class="form-group">
                        <label for="password_confirm">Confirmar password</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Repita el password">
                        <button type="button" id="togglePasswordBtn" onclick="toggleConfirmPassword()">Mostrar contraseña </button>
                    </div>
                    `);
                    // $('#botones').empty();
                    // $('#botones').append(`
                    // <input type="hidden" name="usuario_servidor_id" id="usuario_servidor_id">
                    // <button type="button" class="btn btn-danger float-right" data-dismiss="modal">Salir</button>
                    // <button type="submit" class="btn btn-primary float-right"  >Editar</button>
                    // `);
                    $('#usuario_servidor_id').val(usuario.id);
                    $('form').attr('action','/usuarios/editar_usuario_servidor_post');
                    $('form').attr('method','post');

                    
                },
                error: function(error){
                    console.log(error.responseText);
                }
            })
        }

        // Las siguientes 2 Funciones son para mostrar la contraseña y el usuario pueda saber que esta ingresando
        function togglePassword() {
            const passwordInput = document.getElementById('passworduser');
            // Preguntamos su el input es de tipo password ?
            if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            document.getElementById('togglePasswordBtn_').textContent = 'Ocultar contraseña';
            // Si no es de tipo password lo pasamos a tipo password
            } else {
            passwordInput.type = 'password';
            document.getElementById('togglePasswordBtn_').textContent = 'Mostrar contraseña';
            }
        }

        function toggleConfirmPassword() {
            const passwordInput = document.getElementById('password_confirm');
            if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            document.getElementById('togglePasswordBtn').textContent = 'Ocultar contraseña';
            } else {
            passwordInput.type = 'password';
            document.getElementById('togglePasswordBtn').textContent = 'Mostrar contraseña';
            }
        }
        

        // Función para eliminar un usuario y que no pueda acceder al servidor web
        function eliminar_usuario(usuario_id){
            let url = '/usuarios/eliminar_usuario_servidor/'+usuario_id;
            Vue.swal({ 
                title: '¿Estás seguro?',
                text: "El usuario no podrá acceder al servidor web",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡Confirmar!'
            }).then(result => {
                if(result.isConfirmed){
                    $.ajax({
                        type:'get',
                        url: url,
                        success: function(resp){
                            
                            let usuarios = resp;
                            
                            $('#lista_usuarios').empty();
                            let html = '<ul>';
                            usuarios.forEach(usuario => {
                                if(usuario.dias > 1 ){
                                var d = 'Hace '+usuario.dias+' días';
                                }else if(usuario.dias == 0){
                                var d = 'Hoy';
                                }else{
                                var d = 'Hace '+usuario.dias+' día';
                                }
            
                                if(usuario.dias <= 10){
                                    var classname = 'badge badge-success';
                                }else if(usuario.dias > 10 && usuario.dias < 30){
                                    var classname = 'badge badge-warning';
                                }else{
                                    var classname = 'badge badge-danger';
                                }

                                html += '<li><a href="javascript:cargar_usuario('+usuario.id+')"> '+usuario.user+'</a><span class="'+classname+'" id="dias">'+d+'</span </li>';
                            });

                            html += '</ul>';
                            $('#lista_usuarios').append(html);
                            $('#user').val('');
                            $('#hora').empty();
                            $('#direccion_ip').empty();
                            $('#usuario').empty();
                            $('#created_at').empty();
                            $('#opciones').empty();
                            $('#fecha_login').empty();
                            $('#estado').empty();
                        },
                        error: function(error){
                            console.log(error.responseText);
                        }
                    })
                }
            });
            
            
        }

        function mostrar(){
        let fecha=document.getElementById("fecha").value;
        let url='{{url("/reportes/conexiones")}}'+'/'+fecha;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'CREANDO REPORTE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                
                Vue.swal.close();
                
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
    </script>
@endsection
@section('style')
    <style>
        .contenedor{
            margin: 0px;
            display:block;
            margin-top: 20px;
            border: 1px solid black;
            border-radius: 10px;
            padding: 3px;
        }
        #titulo{
            grid-column: 1/7;
            background-color: cornflowerblue;
        }
        .contenedor .titulazo{
            border-radius: 10px;
        }
        #botones{
            grid-column: 1/7;
            grid-row: 2/3;
            background-color:gainsboro;
            display:grid;
            grid-template-columns: repeat(6,1fr);
            grid-auto-flow: column;
        }
        #fecha_reporte{
            align-self: center;
            justify-self: center;
            display:grid;
            grid-auto-flow: column;
            grid-column: 1/2;
        }
        #btn_mostrar{
            align-self: center;
            justify-self: center;
            display:grid;
            grid-column: 2/3;
        }
    
        #cuerpo{
            display: block;
            width: 100%;
            margin-top: 30px;
        }
        #totales{
            grid-column:1/2;
        }
        #detalle{
            grid-column:2/3;
        }
        #fecha_pago_div{
            align-self:flex-start;
            justify-self: center;
            display:grid;
            grid-auto-flow: column;
            width: 65%;
        }
        .lista_usuarios{
            margin-top: 20px;
        
            width: 100%;
            
        }

        .lista_usuarios ul{
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-around;
            list-style: none;
            align-items: center;
        }

        .lista_usuarios ul li{
            margin: 20px;
            border: 1px solid #444;
            border-radius: 10px;
            padding: 10px;
            background: black;
            color: white;
            transition: all 300ms;
            text-transform: uppercase;
        }

        .lista_usuarios ul li:hover{
            background: #ded729;
            color: black;
            text-shadow: 1px 5px 2px black;
        }

        .lista_usuarios ul li a{
            color: white;
            text-decoration: none;
            text-shadow: 1px 1px 2px black;
        }
        a:hover{
            color: #444;
        }
        .informacion{
            width: 80%;
            margin: 0px auto;
            text-align: center;
        }

        .boton{
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
@endsection
@section('contenido')
    <h4 class="titulazo">Gestión de usuarios del servidor web</h4>
    <div class="container-fluid" >
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
        @if (session('msg'))
                <div class="alert alert-success fade show" role="alert">
                    {{ session('msg') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span>&times;</span> </button>
                </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span>&times;</span> </button>
        </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <div class="lista_usuarios" id="lista_usuarios">
                    <ul>
                        @foreach($usuarios as $usuario)
                        @php
                            if($usuario->dias > 1 ){
                                $d = 'Hace '.$usuario->dias.' días';
                            }elseif($usuario->dias == 0){
                                $d = 'Hoy';
                            }else{
                                $d = 'Hace '.$usuario->dias.' día';
                            }
        
                            if($usuario->dias <= 10){
                                $classname = 'badge badge-success';
                            }elseif($usuario->dias > 10 && $usuario->dias < 30){
                                $classname = 'badge badge-warning';
                            }else{
                                $classname = 'badge badge-danger';
                            }
                        @endphp
                            <li><a href="javascript:cargar_usuario({{$usuario->id}})">{{$usuario->user}}</a><span class="{{$classname}}" id="dias">{{$d}}</span></li>
                            
                        @endforeach
                    </ul>
                    
                </div>
                <div class="boton" style="">
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalUsuario"><i class="fa-solid fa-user-check"></i></button>
                </div>
                <div class="informacion" id="informacion">
                    <table class="table">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Usuario</th>
                                <th scope="col">Fecha Creación</th>
                                <th scope="col">Ultima Conexión</th>
                                <th scope="col">Hora</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Dirección IP</th>
                                <th scope="col">Opciones</th>
                                
                              </tr>
                        </thead>
                        <tbody id="tbody_informacion">
                            <tr>
                              <td style="font-weight: bold" id="usuario"></td>
                              <td id="created_at"></td>
                              <td id="fecha_login"></td>
                              <td id="hora"></td>
                              <td id="estado"></td>
                              <td id="direccion_ip"></td>
                              <td id="opciones"></td>
                              
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contenedor">
                    <div class="titulazo">
                        <center><h4>Reporte Conexiones</h4></center>
                    </div>
                    <div id="botones">
                        <div id="fecha_reporte">
                            <label for="fecha">Fecha:</label>
                            <input type="date" name="fecha" value='<?php echo date("Y-m-d"); ?>' id="fecha" class="form-control  form-control-sm">
                        </div>
                        <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
                    </div>
                    <div id="cuerpo">
                        <div id="totales">totales</div>
                        
                    </div>
                    <a name="fin_pagina"></a>
                </div>
            </div>
        </div>
        
        
        
        
        <!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Usuario servidor web</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="/usuarios/agregar_usuario_servidor" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" name="user" id="user" class="form-control" placeholder="Nuevo usuario">
                </div>
                <div class="form-group">
                    <label for="password">password</label>
                    <input type="password" name="passworduser" id="passworduser" class="form-control" placeholder="Nuevo password">
                    <button type="button" id="togglePasswordBtn_" onclick="togglePassword()">Mostrar contraseña </button>
                </div>
                <div id="confirmar_password">

                </div>
             <div id="botones_">
                 
                <button type="button" class="btn btn-danger float-right" data-dismiss="modal">Salir</button>
                <button type="submit" class="btn btn-primary float-right" >Agregar</button>
             </div>
                
            </form>
          
        </div>
        <div class="modal-footer">
          
          
        </div>
      </div>
    </div>
  </div>
    </div>
   
@endsection