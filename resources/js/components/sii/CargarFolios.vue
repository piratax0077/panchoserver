<template>
   <div class="container-fluid">
       <div class="form-group">
        <h3 class="text-center">Cargar Folios Autorizados SII</h3>
        <div v-if="hay_locales==true">
            <label for="locales">Elija un Local:</label>
            <select  id="locales" v-model="idlocal" @change="cargar_documentos(idlocal)">
                <option value="0">Locales:</option>
                <option v-for="l in locales" :key="l.id" :value="l.id" >{{l.local_nombre}} - {{l.local_direccion}}</option>
            </select>
            <div v-if="hay_documentos==true">
                <table class="table table-sm table-hover" >
                    <thead>
                        <th></th>
                        <th>Documento</th>
                        <th>Archivo CAF</th>
                        <th>Fecha Autoriza</th>
                        <th>Rango</th>
                        <th>Núm Actual</th>
                    </thead>
                    <tbody>
                        <tr v-for="d in documentos" :key="d.id">
                            <td><input type="radio" v-model="iddocumento" :value="d.id"></td>
                            <td>{{d.nombre}}</td>
                            <td>{{d.archivo}}</td>
                            <td>{{d.fecha}}</td>
                            <td>{{d.desde}} - {{d.hasta}}</td>
                            <td>{{d.actual}}</td>
                        </tr>
                    </tbody>
                </table>
                <label for="XML">Elegir archivo CAF</label>
                <input type="file" name="XML" value="XML" id="XML"><br>
                <button class="btn btn-sm btn-primary" @click="cargar_caf()">Cargar Archivo CAF</button>
            </div>
            <div v-else>No hay documentos definidos para ese local</div>
        </div>
        <div v-else>No hay locales definidos</div>

       </div>
   </div>
</template>
<script>
export default {
    mounted() {
            console.log('CargarFolios listo.');

    },
    created(){
        this.cargar_locales();
    },
    data(){
        return{
            documentos:null,
            iddocumento:0,
            locales:null,
            idlocal:0,
            hay_locales:false,
            hay_documentos:false,
        }
    },

    methods:{
        cargar_locales()
        {
            axios.get('damelocales')
            .then(response=>{
                if(response.data=="cero")
                {
                    this.hay_locales=false;
                }else{
                    if(response.data.length>0)
                    {
                        this.locales=response.data;
                        this.hay_locales=true;
                    }
                }
            }).catch(error => {
                console.log(error)

            })
        },
        cargar_documentos(idlocal)
        {
            axios.get('damedocumentos'+'/'+idlocal)
            .then(response=>{
                if(response.data=="cero")
                {
                    this.hay_documentos=false;
                }else{
                    if(response.data.length>0)
                    {
                        console.log(response.data)
                        this.documentos=response.data;
                        this.hay_documentos=true;
                    }
                }
            }).catch(error => {
                console.log(error)

            })
        },
        cargar_caf(){
            //validar datos
            var archivo=$('#XML')[0].files[0];
            if(archivo===null || archivo===undefined)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Seleccione un archivo CAF de SII',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }else if(archivo.name.substr(archivo.name.length-3).toLowerCase()!='xml'){
                Vue.swal({
                    icon: 'error',
                    text:'Seleccione un archivo en formato XML',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }else{

            }


            var idlocal=this.idlocal;
            if(idlocal===null || idlocal==0)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Debe seleccionar un local',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }

            var iddocu=this.iddocumento;
            if(iddocu===null || iddocu==0)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Debe seleccionar un documento',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }

            var datos=new FormData();
            datos.append('archivo',archivo);
            datos.append('idlocal',idlocal);
            datos.append('iddocu',iddocu);
/*
            const config = {
                headers: {
                    'content-type': 'multipart/form-data'
                }
            }
            */
           var url="guardarcaf";
           axios
           .post(url,datos)
           .then(response=>{
               if(response.data==this.idlocal)
               {
                this.cargar_documentos(response.data);
                Vue.swal({
                    icon: 'success',
                    text:'CAF registrado',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
               }
               if(response.data=='-1')
               {
                   Vue.swal({
                    icon: 'error',
                    text:'No coincide tipo DTE',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 4000,
                });
               }
               if(response.data=='-2')
               {
                   Vue.swal({
                    icon: 'error',
                    text:'Archivo CAF ya existe',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 4000,
                });
               }

           })
           .catch(error=>{
                console.log(error.get)
           });
        },



    }
}
</script>
