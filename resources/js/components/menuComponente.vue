<template>
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="/home">{{ appname }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">

        <ul class="navbar-nav">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ventas</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="/ventas">Ventas (Fact-Bole)</a>
            <a class="dropdown-item" href="/notacredito">Nota de Crédito</a>
            <a class="dropdown-item" href="/notadebito">Nota de Débito</a>
            <a class="dropdown-item" href="/bienvenida">Bienvenida con rutas</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="/factuprodu/crear">Facturas de Compra</a>
            <a class="dropdown-item" href="/compras/listar">Listar Facturas (Compras)</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">SII</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="/sii/cargarfolios">Cargar Folios</a>
            <a class="dropdown-item" href="/sii">Estado de Envíos</a>
            <a class="dropdown-item" href="/sii/ambiente">Ambiente Certificación</a>
            <a class="dropdown-item" href="#">Otra opción</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" href="/marcavehiculo">Marca Vehículos</a>
                <a class="dropdown-item" href="/modelovehiculo">Modelo Vehículos</a>
                <a class="dropdown-item" href="/rol">Roles de Usuario</a>
                <a class="dropdown-item" href="/familia">Familia de Repuestos</a>
                <a class="dropdown-item" href="/marcarepuesto">Marca de Repuestos</a>
                <a class="dropdown-item" href="/repuesto/modificar">Modificar Repuestossss</a>
                <a class="dropdown-item" href="/repuesto">Catálogo de Repuestos</a>
                <a class="dropdown-item" href="/pais">Países</a>
                <a class="dropdown-item" href="/proveedor">Proveedores</a>
                <a class="dropdown-item" href="/formapago">Formas de Pago</a>
                <a class="dropdown-item" href="/limitecredito">Límites de Crédito</a>
                <a class="dropdown-item" href="/diascredito">Días de Crédito</a>
                <a class="dropdown-item" href="/clientes">Clientes</a>
                <a class="dropdown-item" href="/parametros">Parámetros</a>
                <a class="dropdown-item" href="/relacionados">Repuestos Relacionados</a>
                <div class="dropdown-divider"></div>
                <li class="dropdown-header">Sistema</li>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/usuarios">Usuarios</a>
                <a class="dropdown-item" href="javascript:void(0)">Una opción</a>
                <a class="dropdown-item disabled" href="javascript:void(0)">Nada</a>
                <a class="dropdown-item" href="javascript:void(0)">Algo mas</a>
            </div>
        </li>


        </ul>

<ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{sesion.usuario_nombre}}({{sesion.usuario_id}})<span class="caret"></span>
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="javascript:void(0)" @click="salir()">Salir</a>
                    <a class="dropdown-item" href="/cambiarclave">Cambiar Clave</a>
                </div>
            </li>
         </ul>
    </div>
    </nav>
</template>
<script>
import {mapGetters,mapActions} from 'vuex';

export default {
        mounted() {
            console.log('menuComponente listo.')
        },
        created(){
            this.dame_sesion();
        },
        data(){
            return{
                appname:'Pancho Repuestos', //config('app.name', 'Laravel'),
                username: 'el usuario', //Auth::user()->name
            }
        },
        methods:{
            ...mapActions(['dame_sesion']),
            salir(){
                console.log("saliendo...");
                axios
                .post('/logout')
                .then(response=>{
                    console.log(response.data);
                    if(response.data=='ok') window.location.href = "/home";
                })
                .catch(error=>{
                    console.log("errooor: "+error);
                })
            }
        },
        computed:{
            ...mapGetters(
                ['sesion']
            )
        },
    }
</script>
