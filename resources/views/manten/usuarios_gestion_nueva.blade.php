@extends('plantillas.usuarios')
@section('titulo', 'Usuarios')

@section('style')
    <style>
        /* Módulo de gestión de usuarios: todos los estilos quedan acotados a esta vista. */
        #erp-users-page {
            --erp-primary: #2563eb;
            --erp-primary-dark: #1d4ed8;
            --erp-text: #1f2937;
            --erp-muted: #667085;
            --erp-border: #e5e7eb;
            --erp-surface: #fff;
            box-sizing: border-box;
            width: 100%;
            min-height: calc(100vh - 90px);
            padding: 24px 22px 40px;
            background: #f5f7fa;
        }
        #erp-users-page *, #erp-users-page *::before, #erp-users-page *::after { box-sizing: border-box; }
        .erp-users-shell { max-width: 1500px; margin: 0 auto; }
        .erp-page-header { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:22px; }
        .erp-page-title { margin:0; font-size:26px; font-weight:700; color:#1f2937; }
        .erp-page-subtitle { margin:5px 0 0; color:#6b7280; font-size:14px; }
        .erp-primary-btn { display:inline-flex; align-items:center; background:var(--erp-primary); color:#fff; border:0; border-radius:10px; padding:10px 16px; font-weight:600; box-shadow:0 4px 12px rgba(37,99,235,.18); }
        .erp-primary-btn:hover { background:var(--erp-primary-dark); color:#fff; text-decoration:none; }
        .erp-layout { display:grid; grid-template-columns:minmax(260px, 0.85fr) minmax(320px, 1fr) minmax(380px, 1.4fr); gap:18px; align-items:start; }
        .erp-card { min-width:0; background:var(--erp-surface); border:1px solid var(--erp-border); border-radius:16px; box-shadow:0 4px 18px rgba(15,23,42,.05); overflow:hidden; }
        .erp-card-header { padding:18px 20px; border-bottom:1px solid #edf0f3; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        .erp-card-title { margin:0; font-size:16px; color:#111827; font-weight:700; }
        .erp-card-description { margin:4px 0 0; font-size:12.5px; color:#8a94a3; line-height:1.45; }
        .erp-card-body { padding:18px 20px; }
        .erp-actions { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
        .erp-action-btn { border:1px solid #dfe3e8; background:#fff; color:#4b5563; border-radius:9px; padding:8px 11px; font-size:13px; font-weight:600; text-decoration:none !important; transition:.2s; }
        .erp-action-btn:hover { background:#f8fafc; color:#111827; border-color:#cfd5dc; }
        .erp-action-btn.disabled { opacity:.45; pointer-events:none; }
        .erp-action-btn.erp-danger { color:#b42318; }
        .erp-action-btn.erp-warning { color:#9a6700; }
        .erp-action-btn.erp-success { color:#18794e; }
        .erp-users-list { list-style:none; padding:0; margin:0; max-height:620px; overflow:auto; }
        .erp-user-item { display:flex; align-items:center; gap:12px; padding:11px 10px; border-radius:12px; border:1px solid transparent; margin-bottom:7px; transition:.18s ease; }
        .erp-user-item:hover { background:#f8fafc; border-color:#e6eaf0; transform:translateY(-1px); }
        #erp-users-page .erp-user-avatar { display:block; flex:0 0 48px; width:48px; height:48px; object-fit:cover; object-position:center; border-radius:50%; border:2px solid var(--erp-border); background:#eef2f7; box-shadow:none; margin:0; padding:0; position:static; }
        .erp-user-avatar-fallback { width:48px; height:48px; min-width:48px; border-radius:50%; background:#eef2f7; color:#64748b; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .erp-user-main { min-width:0; flex:1; }
        .erp-user-link { display:block; color:#1f2937 !important; text-decoration:none !important; font-size:14px; font-weight:650; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .erp-user-help { display:block; color:#9aa3af; font-size:11.5px; margin-top:2px; }
        .erp-edit-btn { width:34px; height:34px; min-width:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; color:#667085 !important; background:#f8fafc; border:1px solid #e5e7eb; text-decoration:none !important; }
        .erp-edit-btn:hover { background:#fff7ed; color:#b45309 !important; border-color:#fed7aa; }
        .erp-empty-state { text-align:center; padding:42px 20px; color:#8a94a3; }
        .erp-empty-icon { width:62px; height:62px; margin:0 auto 14px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#eff6ff; color:#2563eb; font-size:24px; }
        .erp-empty-state h4 { color:#374151; font-size:17px; margin-bottom:7px; }
        .erp-empty-state p { margin:0; font-size:13px; }
        .erp-role-table, .erp-permissions-table { margin:0; }
        .erp-role-table thead th, .erp-permissions-table thead th { background:#f8fafc; border-top:0; border-bottom:1px solid #e5e7eb; color:#667085; font-size:11px; text-transform:uppercase; letter-spacing:.05em; padding:11px 12px; }
        .erp-role-table tbody td, .erp-role-table tbody th, .erp-permissions-table tbody td, .erp-permissions-table tbody th { padding:12px; border-top:1px solid #eef1f4; vertical-align:middle; font-size:13px; color:#344054; }
        .erp-code-badge { display:inline-flex; align-items:center; justify-content:center; min-width:36px; padding:4px 7px; border-radius:7px; background:#f1f5f9; color:#475569; font-weight:700; font-size:11px; }
        .erp-selected-user { padding:4px; }
        .erp-selected-user__top { display:flex; align-items:center; gap:14px; padding-bottom:17px; border-bottom:1px solid #eef1f4; }
        #erp-users-page .erp-selected-avatar { display:block; flex:0 0 72px; width:72px; height:72px; border-radius:50%; object-fit:cover; object-position:center; border:3px solid #fff; box-shadow:0 0 0 2px var(--erp-border); margin:0; padding:0; position:static; }
        .erp-avatar-fallback { width:72px; height:72px; min-width:72px; border-radius:50%; align-items:center; justify-content:center; background:#eef2f7; color:#64748b; font-size:26px; }
        .erp-selected-user__identity h4 { margin:3px 0 7px; color:#111827; font-size:19px; font-weight:700; }
        .erp-eyebrow { font-size:11px; color:#98a2b3; text-transform:uppercase; letter-spacing:.07em; font-weight:700; }
        .erp-status { display:inline-flex; align-items:center; border-radius:999px; padding:4px 8px; font-size:11px; font-weight:700; }
        .erp-status-active { background:#ecfdf3; color:#027a48; }
        .erp-status-inactive { background:#fef3f2; color:#b42318; }
        .erp-user-data-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:16px 0; }
        .erp-data-item { min-width:0; padding:11px 12px; border:1px solid #edf0f3; background:#fbfcfd; border-radius:10px; }
        .erp-data-label { display:block; color:#98a2b3; font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; margin-bottom:4px; }
        .erp-data-item strong, .erp-data-item > span:last-child { display:block; color:#344054; font-size:13px; overflow-wrap:anywhere; }
        .erp-btn-role { background:#fff7ed; color:#b45309; border:1px solid #fed7aa; border-radius:9px; font-weight:600; font-size:13px; padding:8px 12px; }
        #listado-permisos-detalle.erp-permission-editor { display:none; margin:14px 0 0 !important; padding:15px !important; border:1px solid #dbe4ee !important; border-radius:12px !important; background:#f8fafc !important; color:#344054 !important; }
        .erp-permission-categories { max-height:290px; overflow:auto; border-right:1px solid #e5e7eb; padding-right:12px; }
        .erp-permission-categories .form-check { margin-bottom:8px; }
        .erp-permission-categories .form-check-label { font-size:12.5px; }
        .erp-permission-actions td { padding-top:16px !important; border-top:0 !important; }
        .erp-remove-permission { width:30px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; }
        .erp-editor-actions { display:flex; justify-content:flex-end; gap:8px; }
        .erp-column-code { width:80px; }
        .erp-column-action { width:54px; }
        .erp-modal .modal-content { border:0; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(15,23,42,.2); }
        .erp-modal .modal-header { background:#f8fafc; border-bottom:1px solid #e5e7eb; padding:16px 20px; }
        .erp-modal .modal-title { font-weight:700; color:#1f2937; }
        .erp-modal .modal-footer { border-top:1px solid #edf0f3; }
        .erp-role-option { display:flex; align-items:center; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:8px; }
        .erp-role-option:hover { background:#f8fafc; }

        /* Permisos: texto legible y acciones siempre accesibles. */
        .erp-permissions-toolbar { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
        .erp-permissions-search { margin-bottom:12px; }
        #listado-permisos { max-height:480px; max-height:clamp(240px, 52vh, 560px); overflow-y:auto; overscroll-behavior:contain; scrollbar-gutter:stable; border:1px solid #e5e7eb; border-radius:10px; }
        .erp-permissions-table { width:100%; table-layout:fixed; }
        .erp-permissions-table thead th { position:sticky; top:0; z-index:1; background:#f8fafc !important; }
        .erp-permissions-table th:last-child, .erp-permissions-table td:last-child { width:68px; }
        .erp-permissions-table tbody td { padding:10px 12px; overflow-wrap:anywhere; }
        .erp-permission-category { display:block; color:#667085; font-size:11px; margin-bottom:3px; }
        .erp-permission-name { display:block; font-weight:600; line-height:1.4; }
        #panelPermisos { max-height:300px; overflow-y:auto; overscroll-behavior:contain; padding:4px 8px 4px 2px; }
        #panelPermisos .erp-permission-option { padding:8px 8px 8px 26px; border-bottom:1px solid #e5e7eb; }
        #panelPermisos .form-check-label { display:block; cursor:pointer; line-height:1.4; overflow-wrap:anywhere; }
        @media (max-width: 1199.98px) { .erp-layout { grid-template-columns:1fr 1fr; } .erp-layout > .erp-card:last-child { grid-column:1 / -1; } }
        @media (max-width: 767.98px) { .erp-users-page { padding:16px 10px 30px; } .erp-page-header { align-items:flex-start; flex-direction:column; } .erp-layout { grid-template-columns:1fr; } .erp-layout > .erp-card:last-child { grid-column:auto; } .erp-user-data-grid { grid-template-columns:1fr; } }
    </style>
@endsection

@section('contenido')
    <script>
        function verUsuario(user_id) {

            let id = user_id;
            let url = '/usuarios/user/' + id;
            $('#usuario_id').val(id);
            $.ajax({
                type: 'get',
                url: url,
                beforeSend: function() {
                    $('#rolesUser').empty();
                    $('#rolesUser').append('Buscando ...');

                },
                success: function(data) {
                    console.log(data);
                    $('#listado-permisos-detalle').css('display', 'none');
                    Vue.swal.close();
                    let urlImage = "usuarios/avatar/" + data[0].image_path;
                    let permisos = data[2];
                    let u_h_p = data[3];
                    var user = data[0];

                    $('#rolesUser').empty();
                    let estadoClass = user.activo === 1 ? 'erp-status-active' : 'erp-status-inactive';
                    let estadoText = user.activo === 1 ? 'Activo' : 'Inactivo';
                    let email = user.email ? user.email : 'Sin correo registrado';
                    let rut = user.rut ? user.rut : 'Sin RUT registrado';
                    let telefono = user.telefono ? user.telefono : 'Sin teléfono registrado';

                    $('#rolesUser').append(`
                        <div class="erp-selected-user">
                            <div class="erp-selected-user__top">
                                <img src="${urlImage}" alt="${user.name}" class="erp-selected-avatar"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="erp-avatar-fallback" style="display:none;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="erp-selected-user__identity">
                                    <span class="erp-eyebrow">Usuario seleccionado</span>
                                    <h4>${user.name}</h4>
                                    <span id="roleEstado" class="erp-status ${estadoClass}">${estadoText}</span>
                                </div>
                            </div>
                            <div class="erp-user-data-grid">
                                <div class="erp-data-item">
                                    <span class="erp-data-label"><i class="fas fa-user-tag"></i> Rol</span>
                                    <strong id="roleName">${data[1]}</strong>
                                </div>
                                <div class="erp-data-item">
                                    <span class="erp-data-label"><i class="fas fa-envelope"></i> Email</span>
                                    <span id="roleEmail">${email}</span>
                                </div>
                                <div class="erp-data-item">
                                    <span class="erp-data-label"><i class="fas fa-id-card"></i> RUT</span>
                                    <span id="roleRut">${rut}</span>
                                </div>
                                <div class="erp-data-item">
                                    <span class="erp-data-label"><i class="fas fa-phone"></i> Teléfono</span>
                                    <span id="roleTelefono">${telefono}</span>
                                </div>
                            </div>
                            <button type="button" class="btn erp-btn-role" data-toggle="modal" data-target="#exampleModal${user.id}">
                                <i class="fas fa-user-cog mr-1"></i> Cambiar rol
                            </button>
                        </div>
                    `);



                    $('#btnActivar').attr({
                        'href': 'usuarios/up/' + user.id
                    });
                    $('#btnActivar').removeClass('disabled');
                    $('#btnDesactivar').attr('href', 'usuarios/down/' + user.id);
                    $('#btnDesactivar').removeClass('disabled');
                    $('#btnEliminar').attr('href', 'usuarios/delete/' + user.id);
                    $('#btnEliminar').removeClass('disabled');

                    renderizarPermisosUsuario(permisos, u_h_p, user);
                },

                error: function(err) {
                    console.log(err);
                },
                complete: function() {
                    console.log('Completada');
                }

            });

        }



        function espere(mensaje) {

            Vue.swal({

                title: mensaje,

                icon: 'info',

                showConfirmButton: true,

                showCancelButton: false,

                allowOutsideClick: false,

            });

        }



        function cambiarRolUser(user) {

            let idNuevoRol = $('input[name="flexRadioDefault"]:checked').val();

            if (idNuevoRol === null || typeof idNuevoRol === "undefined") {
                return Vue.swal({
                    icon: 'error',
                    text: 'Debe seleccionar un rol'
                });
            }
            let data = {
                'user_id': user.id,
                'role_id': idNuevoRol
            };
            let url = '/usuarios/cambiar-rol';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function(data) {

                    let datos = data[0];
                    let permisos = data[1][2];
                    let u_h_p = data[1][3];
                    console.log(u_h_p);
                    $('#roleName').empty();
                    $('#roleName').append(datos.nombrerol);
                    renderizarPermisosUsuario(permisos, u_h_p, user);
                    Vue.swal({
                        title: 'Felicidades',
                        text: "Rol actualizado",
                        icon: 'success',
                    });
                },
                error: function(err) {
                    Vue.swal({
                        title: 'Error!',
                        text: err.responseText,
                        icon: 'error'
                    });
                }

            })



        }



        function activarUsuario(event) {

            event.preventDefault();

            let url = $('#btnActivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = url.substring(12, 14);

            $.ajax({

                type: 'get',

                url: url,

                data: id,

                success: function(data) {

                    $('#userDesactive').empty();

                    $('#userDesactive').removeClass('info-estado-usuario-inactivo');

                    $('#userDesactive').append('<span id="userActive">Activo</span>');

                    $('#userDesactive').addClass('info-estado-usuario-activo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Activo </h3>");

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario " + data + " activado con éxito",

                        icon: 'success',

                    });

                }

            });

        }

        function handleRoles(idrol) {
            console.log(idrol);
        }

        function desactivarUsuario(event) {

            event.preventDefault();

            let url = $('#btnDesactivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = url.substring(12, 14);

            $.ajax({

                type: 'get',

                url: url,

                data: id,

                success: function(data) {

                    $('#userActive').empty();

                    $('#userActive').removeClass('info-estado-usuario-activo');

                    $('#userActive').append('<span id="userDesactive">Inactivo</span>');

                    $('#userActive').addClass('info-estado-usuario-inactivo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Inactivo </h3>");

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario " + data + " desactivado con éxito",

                        icon: 'success',

                    });

                }

            });

        }



        function eliminarUsuario(event) {

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

                    let id = url.substring(16, 19);

                    $.ajax({

                        type: 'get',

                        url: url,

                        data: id,

                        success: function(data) {

                            console.log(data);

                            Vue.swal({

                                title: 'Eliminado!',

                                text: 'El usuario' + data.name + 'ha sido eliminado',

                                icon: 'success'

                            });

                        },

                        error: function(e) {

                            Vue.swal({

                                title: 'Error!',

                                text: e.responseText,

                                icon: 'error'

                            })

                        }

                    });

                    setTimeout(function() {
                        window.location.href = '/usuarios';
                    }, 3000);

                } else {

                    console.log('No eliminado');

                }

            });



        }



        function editarUsuario(id) {

            alert(id);

        }


        function renderizarPermisosUsuario(permisos, detalles, usuario) {
            const categorias = new Map(permisos.map(p => [String(p.id), p.name]));
            const filas = detalles.filter(d => categorias.has(String(d.permission_id))).slice()
                .sort((a, b) => String(categorias.get(String(a.permission_id))).localeCompare(String(categorias.get(String(b.permission_id))), 'es')
                    || String(a.descripcion).localeCompare(String(b.descripcion), 'es'));
            const tbody = $('#tbody_permisos').empty();
            filas.forEach(detalle => {
                const categoria = categorias.get(String(detalle.permission_id));
                const fila = $('<tr>').attr('data-busqueda', (categoria + ' ' + detalle.descripcion).toLocaleLowerCase('es'));
                const texto = $('<td>').append($('<small>', {class: 'erp-permission-category', text: categoria}))
                    .append($('<span>', {class: 'erp-permission-name', text: detalle.descripcion}));
                const boton = $('<button>', {type: 'button', class: 'btn btn-outline-danger erp-remove-permission',
                    title: 'Quitar ' + detalle.descripcion, 'aria-label': 'Quitar ' + detalle.descripcion})
                    .append($('<i>', {class: 'fas fa-times', 'aria-hidden': 'true'}))
                    .on('click', () => eliminarPermiso(detalle.id, usuario.id));
                fila.append(texto).append($('<td>', {class:'text-center'}).append(boton));
                tbody.append(fila);
            });
            if (!filas.length) tbody.append('<tr><td colspan="2" class="text-muted text-center py-4">Este usuario no tiene permisos específicos.</td></tr>');
            $('#cantidad-permisos').text(filas.length + (filas.length === 1 ? ' permiso' : ' permisos'));
            $('#agregar-permisos-usuario').prop('disabled', false);
            $('#buscar-permiso-usuario').val('').prop('disabled', false);
            $('#sin-coincidencias-permisos').addClass('d-none');
            $('#listado-permisos').scrollTop(0);
        }

        $(document).on('input', '#buscar-permiso-usuario', function() {
            const filtro = this.value.trim().toLocaleLowerCase('es');
            let visibles = 0;
            $('#tbody_permisos tr[data-busqueda]').each(function() {
                const visible = $(this).attr('data-busqueda').includes(filtro);
                $(this).toggle(visible);
                if (visible) visibles++;
            });
            $('#sin-coincidencias-permisos').toggleClass('d-none', visibles > 0 || !filtro);
        });

        function abrirModalPermiso() {
            $('#listado-permisos-detalle').removeClass('d-none');
            $('#listado-permisos-detalle').addClass('d-block');
        }

        function esconderModal() {
            $('#listado-permisos-detalle').removeClass('d-block');
            $('#listado-permisos-detalle').addClass('d-none');
        }

        function cambiarDivPermisos() {
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let url = '/usuarios/cambiar_div/' + permisoId;

            $.ajax({
                type: 'get',
                url: url,
                beforeSend: function() {
                    $('#panelPermisos').empty();
                    $('#panelPermisos').append('Cargando ...');
                },
                success: function(resp) {

                    $('#panelPermisos').empty();
                    $('#panelPermisos').prepend(resp);
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function guardarPermisos() {
            var choices = [];
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let usuario_id = $('#usuario_id').val();
            let permisos = document.getElementsByName('permisos');
            for (var i = 0; i < permisos.length; i++) {
                if (permisos[i].checked) {
                    choices.push(permisos[i].value);
                }
            }

            if (choices.length == 0) {
                Vue.swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe escoger permisos'
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
                type: 'post',
                data: parametros,
                url: url,
                success: function(data) {
                    if (data == 'error') {
                        return Vue.swal({
                            icon: 'error',
                            text: 'Ya existe permiso'
                        });
                    }
                    let permisos = data[2];
                    let u_h_p = data[3];
                    var user = data[0];

                    renderizarPermisosUsuario(permisos, u_h_p, user);

                    Vue.swal({
                        icon: 'success',
                        title: 'felicidades',
                        text: 'Permiso concedido'
                    });


                },
                error: function(error) {

                    Vue.swal({
                        icon: 'success',
                        title: 'felicidades',
                        text: error.responseText
                    });
                }
            })

        }

        function eliminarPermiso(id, user_id) {
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
                    let data = {
                        'id': id,
                        'user_id': user_id
                    };
                    let url = "/usuarios/permisos/delete";
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'post',
                        data: data,
                        url: url,
                        success: function(data) {
                            let permisos = data[2];
                            let u_h_p = data[3];
                            var user = data[0];

                            renderizarPermisosUsuario(permisos, u_h_p, user);
                            Vue.swal({

                                title: 'Eliminado!',

                                text: 'El permiso ha sido eliminado',

                                icon: 'success'

                            });

                            //setTimeout(function(){ window.location.href = '/usuarios'; },3000);

                        },

                        error: function(err) {

                            console.log(err);

                        }

                    })

                } else {

                    console.log('No eliminado');

                }

            });





        }

        function seleccionarCajero(e) {
            e.preventDefault();
            $.ajax({
                type: 'get',
                url: 'ventas/damecajeros',
                success: function(cajeros) {
                    console.log(cajeros);
                    if (cajeros.length > 0) {
                        $("#transferir-carrito-modal").modal("show");
                        $('#nombres-cajeros').empty();
                        $('#nombres-cajeros').append('<p>Cajeros disponibles </p>');
                        cajeros.forEach((cajero) => {
                            $('#nombres-cajeros').append(`
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="CheckCajero" id="CheckCajero` + cajero.id +
                                `" value="` + cajero.id + `">
                    <label class="form-check-label" for="CheckCajero` + cajero.id + `">
                    ` + cajero.name + `
                    </label>
                    </div>
                    
                    `);
                        });
                    } else {
                        Vue.swal({
                            title: 'ERROR',
                            text: 'No hay cajeros disponibles',
                            icon: 'error',
                        });
                    }

                },
                error: function(e) {
                    Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(e.responseText),
                        icon: 'error',
                    });
                }
            });

        }

        function guardarCajeros() {
            console.log('guardando');
            var choices = [];
            let usuario_id = $('#usuario_id').val();
            let cajeros = document.getElementsByName('CheckCajero');
            for (var i = 0; i < cajeros.length; i++) {
                if (cajeros[i].checked) {
                    choices.push(cajeros[i].value);
                }
            }

            if (choices.length == 0) {
                Vue.swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe escoger permisos'
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
                type: 'post',
                data: parametros,
                url: url,
                success: function(resp) {

                    if (resp[0] == 'OK') {
                        $('#listado_cajeros_disponibles').empty();
                        let cajeros = resp[1];

                        cajeros.forEach(c => {
                            let nombre = c['name'];
                            let usuario_id = c['id_usuario'];
                            console.log(nombre);
                            $('#listado_cajeros_disponibles').append(`
                                <tr> 
                                    <td>` + nombre + ` </td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(` +
                                usuario_id + `)">X</button> </td>
                                </tr>
                                `);
                        });
                    } else {
                        Vue.swal({
                            icon: 'error',
                            text: resp
                        });
                        return false;
                    }


                },
                error: function(error) {

                    Vue.swal({
                        icon: 'error',
                        title: 'Error',
                        text: error.responseText
                    });
                }
            })
        }

        function eliminarCajeroDisponible(id_usuario) {
            let url = '/usuarios/eliminar_cajero_disponible/' + id_usuario;
            $.ajax({
                type: 'get',
                url: url,
                success: function(cajeros) {
                    console.log(cajeros);
                    $('#listado_cajeros_disponibles').empty();
                    if (cajeros.length > 0) {
                        cajeros.forEach(c => {
                            $('#listado_cajeros_disponibles').append(`
                        <tr> 
                            <td>` + c.name + ` </td>
                            <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(` + c
                                .id_usuario + `)">X</button> </td>
                        </tr>
                        `);
                        });
                    } else {
                        $('#listado_cajeros_disponibles').append(
                            `<p class="alert-danger">No hay cajeros seleccionados. </p>`);
                    }


                },
                error: function(error) {
                    console.log(error.responseText);
                }
            });
        }
    </script>


    <div class="erp-users-page" id="erp-users-page">
        <div class="erp-users-shell">
            <div class="erp-page-header">
                <div>
                    <h1 class="erp-page-title">Gestión de usuarios</h1>
                    <p class="erp-page-subtitle">Administra usuarios, roles, permisos y cajeros del sistema.</p>
                </div>
                <a href="/usuarios/crear" class="erp-primary-btn text-decoration-none">
                    <i class="fas fa-user-plus mr-1"></i> Registrar usuario
                </a>
            </div>

            <div class="erp-layout">
                <section class="erp-card">
                    <div class="erp-card-header">
                        <div>
                            <h2 class="erp-card-title"><i class="fas fa-users mr-2 text-primary"></i>Usuarios</h2>
                            <p class="erp-card-description">Selecciona un usuario para administrar su acceso.</p>
                        </div>
                    </div>
                    <div class="erp-card-body">
                        <div class="erp-actions">
                            <a class="erp-action-btn erp-success disabled" id="btnActivar" href="" onclick="activarUsuario(event)"><i class="fas fa-check-circle mr-1"></i> Activar</a>
                            <a class="erp-action-btn erp-warning disabled" id="btnDesactivar" href="" onclick="desactivarUsuario(event)"><i class="fas fa-ban mr-1"></i> Desactivar</a>
                            <a class="erp-action-btn erp-danger disabled" id="btnEliminar" href="" onclick="eliminarUsuario(event)"><i class="fas fa-trash-alt mr-1"></i> Borrar</a>
                            <a class="erp-action-btn" href="" onclick="seleccionarCajero(event)" data-toggle="modal" data-target="#modalCajeros"><i class="fas fa-cash-register mr-1"></i> Cajeros</a>
                        </div>

                        <ul class="erp-users-list">
                            @foreach ($users as $user)
                                @if ($user->id !== Auth::user()->id)
                                    <li class="erp-user-item">
                                        <img src="{{ url('usuarios/avatar/' . $user->image_path) }}"
                                             alt="Foto de {{ $user->name }}"
                                             class="erp-user-avatar"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="erp-user-avatar-fallback" style="display:none;"><i class="fas fa-user"></i></span>
                                        <div class="erp-user-main">
                                            <a href="javascript:verUsuario({{ $user->id }})" class="erp-user-link" id="{{ $user->id }}">{{ $user->name }}</a>
                                            <span class="erp-user-help">Seleccionar para administrar</span>
                                        </div>
                                        <a class="erp-edit-btn" href="{{ url('usuarios/edit/' . $user->id) }}" title="Editar usuario"><i class="fas fa-pen"></i></a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </section>

                <section class="erp-card">
                    <div class="erp-card-header">
                        <div>
                            <h2 class="erp-card-title"><i class="fas fa-user-shield mr-2 text-primary"></i>Perfil y rol</h2>
                            <p class="erp-card-description">Información del usuario seleccionado y rol asignado.</p>
                        </div>
                    </div>
                    <div class="erp-card-body" id="rolesUser">
                        <div class="erp-empty-state">
                            <div class="erp-empty-icon"><i class="fas fa-user-shield"></i></div>
                            <h4>Selecciona un usuario</h4>
                            <p>Aquí verás sus datos y podrás cambiar su rol.</p>
                        </div>
                        <div class="mt-4">
                            <p class="erp-card-description mb-2">Roles disponibles en el sistema</p>
                            <div class="table-responsive">
                                <table class="table erp-role-table">
                                    <thead><tr><th class="erp-column-code">Cod.</th><th>Rol</th></tr></thead>
                                    <tbody>
                                        @foreach ($roles as $rol)
                                            <tr><td><span class="erp-code-badge">#{{ $rol->id }}</span></td><td>{{ $rol->nombrerol }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="erp-card">
                    <div class="erp-card-header">
                        <div>
                            <h2 class="erp-card-title"><i class="fas fa-key mr-2 text-primary"></i>Permisos</h2>
                            <p class="erp-card-description">Revisa y administra permisos específicos del usuario.</p>
                        </div>
                    </div>
                    <div class="erp-card-body">
                        <div class="erp-permissions-toolbar">
                            <span id="cantidad-permisos" class="text-muted small">Selecciona un usuario</span>
                            <button id="agregar-permisos-usuario" type="button" class="btn btn-success btn-sm" onclick="abrirModalPermiso()" disabled><i class="fas fa-plus mr-1"></i>Agregar permisos</button>
                        </div>
                        <div class="erp-permissions-search">
                            <label class="sr-only" for="buscar-permiso-usuario">Buscar permiso o categoría</label>
                            <input id="buscar-permiso-usuario" type="search" class="form-control form-control-sm" placeholder="Buscar permiso o categoría…" disabled>
                        </div>
                        <div id="listado-permisos" class="table-responsive" tabindex="0" role="region" aria-label="Permisos asignados al usuario">
                            <table class="table erp-permissions-table">
                                <thead><tr><th>Permiso / categoría</th><th class="text-center">Quitar</th></tr></thead>
                                <tbody id="tbody_permisos">
                                    <tr><td colspan="2" class="text-muted text-center py-4">Selecciona un usuario para ver sus permisos.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p id="sin-coincidencias-permisos" class="small text-muted mt-2 d-none" role="status">No hay permisos que coincidan con la búsqueda.</p>

                        <div id="listado-permisos-detalle" class="d-none erp-permission-editor">
                            <div class="row">
                                <div class="col-md-4 erp-permission-categories">
                                    @foreach ($permisos as $permiso)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="flexCheckPermisos" value="{{ $permiso->id }}" id="permisoCategoria{{ $permiso->id }}" onclick="cambiarDivPermisos()">
                                            <label class="form-check-label" for="permisoCategoria{{ $permiso->id }}">{{ $permiso->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-8">
                                    <div id="panelPermisos" class="mb-3"></div>
                                    <div class="erp-editor-actions">
                                        <button type="button" class="btn btn-light btn-sm" onclick="esconderModal()">Cancelar</button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="guardarPermisos()"><i class="fas fa-save mr-1"></i> Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @foreach ($users as $user)
        <div class="modal fade erp-modal" id="exampleModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div><small class="text-muted">Cambiar rol de usuario</small><h5 class="modal-title">{{ $user->name }}</h5></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @foreach ($roles as $rol)
                            <label class="erp-role-option">
                                <input class="mr-2" type="radio" onchange="handleRoles({{ $rol->id }})" name="flexRadioDefault" value="{{ $rol->id }}">
                                <span>{{ $rol->nombrerol }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="cambiarRolUser({{ $user }})" data-dismiss="modal"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade erp-modal" id="modalCajeros" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div><small class="text-muted">Configuración operativa</small><h5 class="modal-title">Cajeros habilitados</h5></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><h6 class="font-weight-bold mb-3">Cajeros disponibles</h6><div id="nombres-cajeros"></div></div>
                        <div class="col-md-6"><h6 class="font-weight-bold mb-3">Actualmente habilitados</h6>
                            <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Nombre</th><th class="erp-column-action"><span class="sr-only">Acciones</span></th></tr></thead><tbody id="listado_cajeros_disponibles">
                                @if ($cajeros->count() > 0)
                                    @foreach ($cajeros as $c)
                                        <tr><td>{{ $c->name }}</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarCajeroDisponible({{ $c->id_usuario }})"><i class="fas fa-times"></i></button></td></tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="2" class="text-muted">No hay cajeros habilitados.</td></tr>
                                @endif
                            </tbody></table></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCajeros()"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="usuario_id" id="usuario_id">

@endsection
