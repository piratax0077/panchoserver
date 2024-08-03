<template>
    <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-md-4 tabla-scroll-y-300">
              <p>{{ contador }}</p>
              <table class="table table-hover table-sm" height="172">
                <thead>
                <tr>
                    <th width="56" scope="col">ID</th>
                    <th width="32" scope="col">Marca</th>
                    <th width="59" scope="col"></th> <!-- ELIMINAR -->
                </tr>
                </thead>
                <tbody>
                    <tr v-for="marca in marcas_repuestos" :key="marca.id">
                    <td>{{marca.id}}</td>
                    <td>{{marca.marcarepuesto}}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" @click="borrar(marca.id)">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
              </table>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState,mapActions} from 'vuex';
    export default {
        mounted() {
            console.log('MarcaRepuestoMostrarComponente Listo.')
        },
        created(){
           this.cargar_marcas_repuestos();
        },
        data(){
            return {
              marca: '',
            }
        },
        computed:{
            ...mapState(['contador','marcas_repuestos']),
        },
        methods:{
           ...mapActions(['cargar_marcas_repuestos']),
        },
    }
</script>
