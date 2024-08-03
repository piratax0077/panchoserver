<template>
    <div class="container-fluid">
        <h3>MI COMPONENTE</h3>
        <p>Este es mi primer componente</p>
        <div class="row">
            <div class="col-3" style="background:#ffeecc">
                col-3: más corto al lado izquierdo. Como se indica, solo tiene 3 columnas de las 12 permitidas como máximo.
            </div>
            <div class="col-9" style="background:#fb3ffe">
                col-9: al lado derecho que es más amplio. col-9: al lado derecho que es más amplio. col-9: al lado derecho que es más amplio. col-9: al lado derecho que es más amplio. col-9: al lado derecho que es más amplio. col-xs-9: al lado derecho que es más amplio.
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center">
                <button class="btn btn-danger" @click="saludo">XHUTA</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('mi_componente listo.')
        },
        methods:{
            saludo:function(){
                console.log("saludo");
                Vue.swal({
                    title:'Uy curuju',
                    text:'Este es un lindo  botón',
                    allowOutsideClick:false
                });
            }
        },
    }
</script>
