/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import DataTable from 'datatables.net';
import 'datatables.net-dt/css/jquery.dataTables.min.css';

//const Swal = require('sweetalert2');
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import Vuex from 'vuex';

window.Vue = require('vue');

Vue.use(Vuex);


Vue.use(VueSweetalert2);


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue'));
// Vue.component('mi-componente', require('./components/mi_componente.vue'));
// Vue.component('menucomponente', require('./components/menuComponente.vue'));
// Vue.component('marca-repuestos-agregar', require('./components/MarcaRepuestoAgregarComponente.vue'));
// Vue.component('marca-repuestos-mostrar', require('./components/MarcaRepuestoMostrarComponente.vue'));
// Vue.component('usuarios', require('./components/UsuariosComponente.vue'));
// Vue.component('notadebito', require('./components/NotaDebitoComponente.vue'));
// Vue.component('guiadespacho', require('./components/GuiaDespachoComponente.vue'));

//SII
//Vue.component('cargarfolios', require('./components/sii/CargarFolios.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


import storeData from "./store/index";

const store = new Vuex.Store(
    storeData
);

const app = new Vue({
    el: '#app',
    store
});