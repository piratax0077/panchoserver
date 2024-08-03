@extends('plantillas.usuarios')
@section('titulo','Usuarios')
@section('contenido')
    <script>
       
        function verUsuario(user_id){
            
                let id = user_id;
                let url = '/usuarios/user/'+id;
                $('#usuario_id').val(id);
                $.ajax({
                    type:'get',
                    url: url,
                    beforeSend: function(){
                        $('#rolesUser').empty();
                        $('#rolesUser').append('Buscando ...');
                        
                    },
                    success: function(data){
                        console.log(data);
                        $('#listado-permisos-detalle').css('display','none');
                        Vue.swal.close();
                        let urlImage = "usuarios/avatar/"+data[0].image_path;
                        let permisos = data[2];
                        let u_h_p = data[3];
                        var user = data[0];
                        
                        $('#rolesUser').empty();
                        $('#rolesUser').append("<img src='"+urlImage+"' alt='' class='imgUserRol'>");
                        $('#rolesUser').append("<label class='lbl-info-user' for='nameUser'>Nombre de usuario: </label>");
                        $('#rolesUser').append('<p>'+user.name+' </p>');
                        $('#rolesUser').append("<label class='lbl-info-user' for='cargoUser'>Cargo: </label>");
                        $('#rolesUser').append("<p id='roleName'>"+data[1]+" </p>");
                        $('#rolesUser').append("<label class='lbl-info-user' for='emailUser'>Email: </label>");
                        $('#rolesUser').append("<p id='roleEmail'>"+user.email+" </p>");
                        $('#rolesUser').append("<label class='lbl-info-user' for='rutUser'>Rut: </label>");
                        $('#rolesUser').append("<p id='roleRut'>"+user.rut+" </p>");
                        $('#rolesUser').append("<label class='lbl-info-user' for='telefonoUser'>Telefono: </label>");
                        $('#rolesUser').append("<p id='roleTelefono'>"+user.telefono+" </p>");
                        $('#rolesUser').append("<label class='lbl-info-user' for='estadoUser'>Estado: </label>");

                        if(user.activo === 1){
                            $('#rolesUser').append("<p id='roleEstado'>Activo </p>");
                        }else{
                            $('#rolesUser').append("<p id='roleEstado'>Inactivo </p>");
                        }
                        $('#rolesUser').append("<button class='btn btn-warning' data-toggle='modal' data-target='#exampleModal"+user.id+"'>Cambiar rol </button>");

                        

                        $('#btnActivar').attr({'href':'usuarios/up/'+user.id});
                        $('#btnActivar').removeClass('disabled');
                        $('#btnDesactivar').attr('href','usuarios/down/'+user.id);
                        $('#btnDesactivar').removeClass('disabled');
                        $('#btnEliminar').attr('href','usuarios/delete/'+user.id);
                        $('#btnEliminar').removeClass('disabled');

                        // $('#listado-permisos').empty();
                        $('#tbody_permisos').empty();
                        permisos.forEach(permiso => {
                            u_h_p.forEach(data => {
                                if(permiso.id === data.permission_id){
                                    $('#tbody_permisos').append(`
                                    <tr> 
                                        <td><strong>`+permiso.name+`</strong> - `+data.descripcion+` </td>
                                        <td class='text-center'><button class='btn btn-danger btn-xs' onclick='eliminarPermiso(`+data.id+`,`+user.id+`)' style='border-radius: 100px'> x </button> </td>
                                    </tr>
                                    `);
                                    
                                }
                            });
                        });

                        $('#tbody_permisos').append("<button class='btn btn-success btn-xs' onclick='abrirModalPermiso()'>Agregar permisos </button>");
                    },

                    error: function(err){
                        console.log(err);
                    },
                    complete: function(){
                        console.log('Completada');
                    }

                });

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



        function cambiarRolUser(user){

            let idNuevoRol = $('input[name="flexRadioDefault"]:checked').val();
            
            if(idNuevoRol === null || typeof idNuevoRol === "undefined"){
                return Vue.swal({
                    icon:'error',
                    text:'Debe seleccionar un rol'
                });
            }
            let data = {'user_id': user.id, 'role_id': idNuevoRol};
            let url = '/usuarios/cambiar-rol';

            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });

            $.ajax({
                type:'POST',
                url:url,
                data: data,
                success: function(data){
                    
                    let datos = data[0];
                    let permisos = data[1][2];
                    let u_h_p = data[1][3];
                    console.log(u_h_p);
                    $('#roleName').empty();
                    $('#roleName').append(datos.nombrerol);
                     // $('#listado-permisos').empty();
                     $('#tbody_permisos').empty();
                        permisos.forEach(permiso => {
                            u_h_p.forEach(data => {
                                if(permiso.id === data.permission_id){
                                    $('#tbody_permisos').append(`
                                    <tr> 
                                        <td><strong>`+permiso.name+`</strong> - `+data.descripcion+` </td>
                                        <td class='text-center'><button class='btn btn-danger btn-xs' onclick='eliminarPermiso(`+data.id+`,`+user.id+`)' style='border-radius: 100px'> x </button> </td>
                                    </tr>
                                    `);
                                    
                                }
                            });
                        });

                        $('#tbody_permisos').append("<button class='btn btn-success btn-xs' onclick='abrirModalPermiso()'>Agregar permisos </button>");
                    Vue.swal({
                        title: 'Felicidades',
                        text: "Rol actualizado",
                        icon: 'success',
                    });
                },
                error: function(err){
                            Vue.swal({
                                title:'Error!',
                                text:err.responseText,
                                icon:'error'
                            });
                }

            })

            

        }



        function activarUsuario(event){

            event.preventDefault();

            let url = $('#btnActivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = url.substring(12,14);

            $.ajax({

                type:'get',

                url: url,

                data: id,

                success: function(data){

                    $('#userDesactive').empty();

                    $('#userDesactive').removeClass('info-estado-usuario-inactivo');

                    $('#userDesactive').append('<span id="userActive">Activo</span>');

                    $('#userDesactive').addClass('info-estado-usuario-activo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Activo </h3>");

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario "+data+" activado con éxito",

                        icon: 'success',

                    });

                }

            });

        }

        function handleRoles(idrol){
            console.log(idrol);
        }

        function desactivarUsuario(event){

            event.preventDefault();

            let url = $('#btnDesactivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = url.substring(12,14);

            $.ajax({

                type:'get',

                url: url,

                data: id,

                success: function(data){

                    $('#userActive').empty();

                    $('#userActive').removeClass('info-estado-usuario-activo');

                    $('#userActive').append('<span id="userDesactive">Inactivo</span>');

                    $('#userActive').addClass('info-estado-usuario-inactivo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Inactivo </h3>");

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario "+data+" desactivado con éxito",

                        icon: 'success',

                    });

                }

            });

        }



        function eliminarUsuario(event){

            event.preventDefault();

            Vue.swal({

                title: '¿Esta seguro de eliminar al usuario?',

                text: "¡No podrá revertir esta acción!",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#3085d6',

                cancelButtonColor: '#d33',

                confirmButtonText: 'Si, eliminalo!'

                }).then((result) => {

                if (result.isConfirmed) {

                    

                    let url = $('#btnEliminar').attr('href');

                    let id = url.substring(16,19);

                    $.ajax({

                        type:'get',

                        url: url,

                        data: id,

                        success: function(data){

                            console.log(data);

                            Vue.swal({

                                title:'Eliminado!',

                                text:'El usuario'+data.name+ 'ha sido eliminado',

                                icon:'success'

                            });

                        },

                        error: function(e){

                            Vue.swal({

                                title:'Error!',

                                text:e.responseText,

                                icon:'error'

                            })

                        }

                    });

                    setTimeout(function(){ window.location.href = '/usuarios'; },3000);

                }else{

                    console.log('No eliminado');

                }

                });

            

        }



        function editarUsuario(id){

            alert(id);

        }

        function abrirModalPermiso(){
            $('#listado-permisos-detalle').removeClass('d-none');
            $('#listado-permisos-detalle').addClass('d-block');
        }

        function esconderModal(){
            $('#listado-permisos-detalle').removeClass('d-block');
            $('#listado-permisos-detalle').addClass('d-none');
        }

        function cambiarDivPermisos(){
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let url = '/usuarios/cambiar_div/'+permisoId;
            
            $.ajax({
                type:'get',
                url:url,
                beforeSend: function(){
                    $('#panelPermisos').empty();
                    $('#panelPermisos').append('Cargando ...');
                },
                success: function(resp){
                    
                    $('#panelPermisos').empty();
                    $('#panelPermisos').prepend(resp);
                },
                error: function(error){
                    console.log(error);
                }
            })
        }

        function guardarPermisos(){
            var choices = [];
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let usuario_id = $('#usuario_id').val();
            let permisos = document.getElementsByName('permisos');
            for (var i=0;i<permisos.length;i++){
                if ( permisos[i].checked ) {
                    choices.push(permisos[i].value);
                }
            }

            if(choices.length == 0){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:'Debe escoger permisos'
                });
                return false;
            }

            let parametros = {
                permisoId: permisoId,
                detalles: choices,
                usuario_id: parseInt(usuario_id)
            }
            let url = '/usuarios/guardarPermisosDetalles';
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: parametros,
                url:url,
                success: function(data){
                    if(data == 'error'){
                        return Vue.swal({
                            icon:'error',
                            text:'Ya existe permiso'
                        });
                    }
                    let permisos = data[2];
                    let u_h_p = data[3];
                    var user = data[0];

                            // $('#listado-permisos').empty();
                        $('#tbody_permisos').empty();
                        permisos.forEach(permiso => {
                            u_h_p.forEach(data => {
                                if(permiso.id === data.permission_id){
                                    $('#tbody_permisos').append(`
                                    <tr> 
                                        <td><strong>`+permiso.name+`</strong> - `+data.descripcion+` </td>
                                        <td class='text-center'><button class='btn btn-danger btn-xs' onclick='eliminarPermiso(`+data.id+`,`+user.id+`)' style='border-radius: 100px'> x </button> </td>
                                    </tr>
                                    `);
                                    
                                }
                            });
                        });

                        $('#tbody_permisos').append("<button class='btn btn-success btn-xs' onclick='abrirModalPermiso()'>Agregar permisos </button>");
              
                        Vue.swal({
                            icon:'success',
                            title:'felicidades',
                            text:'Permiso concedido'
                        });
                    
                   
                },
                error: function(error){
                    
                    Vue.swal({
                            icon:'success',
                            title:'felicidades',
                            text:error.responseText
                        });
                }
            })
           
        }

        function eliminarPermiso(id,user_id){
            Vue.swal({
                title: '¿Esta seguro de eliminar el permiso del usuario?',
                text: "¡Precaución!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, eliminalo!'

                }).then((result) => {

                if (result.isConfirmed) {
                    let data = {'id':id,'user_id': user_id};
                    let url = "/usuarios/permisos/delete";
                    $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });

                    $.ajax({
                        type:'post',
                        data: data,
                        url: url,
                        success: function(data){
                            let permisos = data[2];
                            let u_h_p = data[3];
                            var user = data[0];

                            // $('#listado-permisos').empty();
                        $('#tbody_permisos').empty();
                        permisos.forEach(permiso => {
                            u_h_p.forEach(data => {
                                if(permiso.id === data.permission_id){
                                    $('#tbody_permisos').append(`
                                    <tr> 
                                        <td><strong>`+permiso.name+`</strong> - `+data.descripcion+` </td>
                                        <td class='text-center'><button class='btn btn-danger btn-xs' onclick='eliminarPermiso(`+data.id+`,`+user.id+`)' style='border-radius: 100px'> x </button> </td>
                                    </tr>
                                    `);
                                    
                                }
                            });
                        });

                        $('#tbody_permisos').append("<button class='btn btn-success btn-xs' onclick='abrirModalPermiso()'>Agregar permisos </button>");
                            Vue.swal({

                                title:'Eliminado!',

                                text:'El permiso ha sido eliminado',

                                icon:'success'

                            });

                            //setTimeout(function(){ window.location.href = '/usuarios'; },3000);

                        },

                        error: function(err){

                            console.log(err);

                        }

                    })

                        }else{

                            console.log('No eliminado');

                        }

                        });

            

           

        }

        function seleccionarCajero(e){
            e.preventDefault();
            $.ajax({
                type:'get',
                url:'ventas/damecajeros',
                success: function(cajeros){
                console.log(cajeros);
                if(cajeros.length > 0){
                    $("#transferir-carrito-modal").modal("show");
                    $('#nombres-cajeros').empty();
                    $('#nombres-cajeros').append('<p>Cajeros disponibles </p>');
                    cajeros.forEach((cajero) => {
                    $('#nombres-cajeros').append(`
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="CheckCajero" id="CheckCajero`+cajero.id+`" value="`+cajero.id+`">
                    <label class="form-check-label" for="CheckCajero`+cajero.id+`">
                    `+cajero.name+`
                    </label>
                    </div>
                    
                    `);
                    });
                }else{
                    Vue.swal({
                        title: 'ERROR',
                        text: 'No hay cajeros disponibles',
                        icon: 'error',
                        });
                }
                
                },
                error: function(e){
                Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(e.responseText),
                        icon: 'error',
                        });
                }
            });
           
        }

        function guardarCajeros(){
            console.log('guardando');
            var choices = [];
            let usuario_id = $('#usuario_id').val();
            let cajeros = document.getElementsByName('CheckCajero');
            for (var i=0;i<cajeros.length;i++){
                if ( cajeros[i].checked ) {
                    choices.push(cajeros[i].value);
                }
            }

            if(choices.length == 0){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:'Debe escoger permisos'
                });
                return false;
            }

            let parametros = {
                detalles: choices
            }
            console.log(parametros);
            let url = '/usuarios/guardarCajerosDisponibles';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: parametros,
                url:url,
                success: function(resp){
                    
                    if(resp[0] == 'OK'){
                        $('#listado_cajeros_disponibles').empty();    
                        let cajeros = resp[1];
                        
                            cajeros.forEach(c => {
                                let nombre = c['name'];
                                let usuario_id = c['id_usuario'];
                                console.log(nombre);
                                $('#listado_cajeros_disponibles').append(`
                                <tr> 
                                    <td>`+nombre+` </td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(`+usuario_id+`)">X</button> </td>    
                                </tr>
                                `);
                        });
                    }else{
                        Vue.swal({
                            icon:'error',
                            text:resp
                        });
                        return false;
                    }
                    
                   
                },
                error: function(error){
                    
                    Vue.swal({
                            icon:'error',
                            title:'Error',
                            text:error.responseText
                        });
                }
            })
        }

        function eliminarCajeroDisponible(id_usuario){
            let url = '/usuarios/eliminar_cajero_disponible/'+id_usuario;
            $.ajax({
                type:'get',
                url: url,
                success: function(cajeros){
                    console.log(cajeros);
                    $('#listado_cajeros_disponibles').empty();
                    if(cajeros.length > 0){
                        cajeros.forEach(c => {
                        $('#listado_cajeros_disponibles').append(`
                        <tr> 
                            <td>`+c.name+` </td>
                            <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(`+c.id_usuario+`)">X</button> </td>    
                        </tr>
                        `);
                    });
                    }else{
                        $('#listado_cajeros_disponibles').append(`<p class="alert-danger">No hay cajeros seleccionados. </p>`);
                    }
                    
                    
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }
    </script>
    <h4 class="titulazo">Gestión de usuarios</h4>
    <div class="row" style="width: 100%;">
        <div class="col-md-4">
            <h3>Listado de usuarios</h3>
            <div class="button_group">
                <a href="/usuarios/crear" class="btn btn-primary btn-sm">Registrar</a>
                <a class="btn btn-success btn-sm disabled" id="btnActivar" href="" onclick="activarUsuario(event)"  >Activar</a>
                <a class="btn btn-warning btn-sm disabled" id="btnDesactivar" href="" onclick="desactivarUsuario(event)" >Desactivar</a>
                <a class="btn btn-danger btn-sm disabled" id="btnEliminar" href="" onclick="eliminarUsuario(event)" >Borrar</a>
                <a class="btn btn-secondary btn-sm" href="" onclick="seleccionarCajero(event)" data-toggle="modal" data-target="#modalCajeros">Seleccionar Cajero</a>
            </div>
            <ul>
                @foreach($users as $user)
                @if ($user->id !== Auth::user()->id ) 
                <li class="mb-3 mt-3" ><img src="{{url('usuarios/avatar/'.$user->image_path)}}" alt="" class="logoInicio"> 
                    <a href="javascript:verUsuario({{$user->id}})" class="ml-4" style="text-decoration: none !important;" id="{{$user->id}}">
                        <span style="font-size: 16px; ">{{$user->name}}</span>
                    </a>
                    <a class="btn btn-xs btn-warning" style="float: right; margin-top: 25px;" href="{{url('usuarios/edit/'.$user->id)}}"><i class="fas fa-edit"></i></a>
                </li> 
                @endif
                @endforeach
            </ul>
        </div>

        <div class="col-md-4">

            <h3>Roles de usuario</h3>

            <div id="rolesUser">

                <table class="table">

                    <thead class="thead-dark">

                        <tr>

                            <th>Cod.</th>

                            <th>Rol</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($roles as $rol)

                        <tr>

                            <th>{{$rol->id}}</th>

                            <th>{{$rol->nombrerol}}</th>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

        <div class="col-md-4">

            <h3>Listado de Permisos</h3>

            <div id="listado-permisos">

                <table class="table">

                    <thead class="thead-dark">

                        <tr>

                            <th>Cod.</th>

                            <th>Permiso</th>

                        </tr>

                    </thead>

                    <tbody id="tbody_permisos">

                        @foreach($permisos as $permiso)

                        <tr>

                            <th>{{$permiso->id}}</th>

                            <th>{{$permiso->name}}</th>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div id="listado-permisos-detalle" class="d-none" style="padding: 5px; color: black; background: #f2f4a9; margin-bottom: 60px;">
                <div class="row">
                    <div class="col-md-4">
                        @foreach($permisos as $permiso)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="flexCheckPermisos" value="{{$permiso->id}}" id="flexRadioDefault1" onclick="cambiarDivPermisos()">
                            <label class="form-check-label" for="flexRadioDefault1">
                              {{$permiso->name}}
                            </label>
                          </div>
                          @endforeach
                          
                    </div>
                    <div class="col-md-8 d-flex justify-content-between align-items-start">
                        <div id="panelPermisos">
                            
                        </div>
                        <button class="btn btn-success btn-sm float-rigth" onclick="guardarPermisos()">Guardar</button>
                        <button class="btn btn-danger btn-sm" onclick="esconderModal()"><i class="fa-solid fa-circle-xmark"></i></button>
                    </div>
                </div>
                
            
            </div>

        </div>

    </div>

 

    <!-- Modal -->

    @foreach ($users as $user )

    <div class="modal fade" id="exampleModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">

          <div class="modal-content">

            <div class="modal-header">

              <h5 class="modal-title" id="exampleModalLabel">{{$user->name}}</h5>

              <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                <span aria-hidden="true">&times;</span>

              </button>

            </div>

            <form action="" method="post">

            <div class="modal-body">

              @foreach ($roles as $rol )

              <div class="form-check">

                <input class="form-check-input" type="radio" onchange="handleRoles({{$rol->id}})" name="flexRadioDefault" id="flexRadioDefault{{$rol->id}}" value="{{$rol->id}}">

                <label class="form-check-label" for="flexRadioDefault">

                    {{$rol->nombrerol}}

                </label>

              </div>

                  

              @endforeach

             

            </div>

            <div class="modal-footer">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

              <button type="button" class="btn btn-primary" onclick="cambiarRolUser({{$user}})" data-dismiss="modal">Guardar cambios</button>

            </div>

        </form>

          </div>

        </div>

      </div>


    @endforeach

    <!-- Modal -->
<div class="modal fade" id="modalCajeros" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
          <div class="row">
            <div class="col-md-6" id="nombres-cajeros">

            </div>
            <div class="col-md-6">
                <p>Listado de cajeros habilitados</p>
                <div >
                    @if($cajeros->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                              <th scope="col">Nombre</th>
                              <th scope="col"></th>
                            </tr>
                          </thead>
                          <tbody id="listado_cajeros_disponibles">
                            
                                @foreach($cajeros as $c)
                                <tr>
                                    <td>{{$c->name}}</td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible({{$c->id_usuario}})">X</button></td>
                                </tr>
                                @endforeach
                            
                            
                          </tbody>
                    </table>
                    @else
                    <table class="table">
                        <thead>
                            <tr>
                              <th scope="col">Nombre</th>
                              <th scope="col"></th>
                            </tr>
                          </thead>
                          <tbody id="listado_cajeros_disponibles">
                            <tr>
                                <td class="alert-danger">No hay cajeros</td>
                            </tr>
                            
                            
                          </tbody>
                    </table>
                    @endif
                    
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="guardarCajeros()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

    <!--Dato de vital importancia -->
    <input type="hidden" name="usuario_id" id="usuario_id">

@endsection