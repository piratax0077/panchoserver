<template>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-2 col-sm-2 col-md-2 col-lg-2">
                <label>Marca del Repuesto:<input type="text" size="20" maxlength="20" v-model="marcarepuesto" class="form-control" style="width:100%"></label>
              </div>
              <div class="col-2 col-sm-2 col-md-2 col-lg-2">
                <button  @click="guardar_mr" id="btnGuardarMarcaRepuesto"  class="btn btn-primary btn-sm" style="margin-top:30px">Guardar</button>
                <button  @click="aumentarcito"  class="btn btn-success btn-sm" style="margin-top:30px">+</button><br>
            <p>{{contador}}</p>
            <p v-if="guardó_marca_repuesto==true">Marca Guardada</p>
            <p v-else>No se Guardó la Marca...</p>
              </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';
    export default {
        mounted() {
            console.log('MarcaRepuestoAgregarComponente Listo.');
        },
        data(){
            return {
              marcarepuesto: '',
            }
        },
        computed:{
            ...mapGetters(
                ['contador','guardó_marca_repuesto']
            )
        },
        watch:{
            guardó_marca_repuesto(n_val,o_val){
                console.log("nuevo: "+n_val+" anterior: "+o_val);
                if(n_val==true)
                {
                    Vue.swal({
                    text: 'Se guardó correctamente',
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
                    this.marcarepuesto='';
                    this.$store.state.guardó_marca_repuesto=!this.$store.state.guardó_marca_repuesto;
                }
            }
        },

        methods:{
            ...mapActions(['aumentar','guardar_marca_repuesto']),
            aumentarcito()
            {
                this.aumentar();
            },
            guardar_mr()
            {
                if(this.marcarepuesto=='')
                {
                    Vue.swal({
                                icon:'error',
                                title:'ERROR!!!',
                                text:'NO puede dejar la marca vacía'
                            });
                    return
                }
                this.guardar_marca_repuesto(this.marcarepuesto);
                console.log('Guardó marca: '+this.guardó_marca_repuesto);
            },
        },
    }
</script>
