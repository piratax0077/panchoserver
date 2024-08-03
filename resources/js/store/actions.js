export const dame_sesion = async(context) => {
    axios.get('/damesesion')
        .then(response => {
            context.commit('DAME_SESION', response.data);
        })
        .catch(error => {
            console.log(error);
        });


}

export const guardar_marca_repuesto = (context, marcarepuesto) => {
        axios
            .post('marcarepuesto', { marcarepuesto: marcarepuesto, donde: 'factuprodu', btnGuardarMarcaRepuesto: 'ajo' })
            .then(response => {
                if (response.data == 'OK') {
                    context.dispatch('cargar_marcas_repuestos') //llama a la otra acción
                    context.commit('GUARDÓ_MARCA_REPUESTO', true)
                } else {
                    context.commit('GUARDÓ_MARCA_REPUESTO', false)
                }

            })
            .catch(error => {
                console.log('ERROR NO GUARDÓ')
                    /*
                    Vue.swal({
                        icon: 'error',
                        title: 'ERROR!!!',
                        text: 'NO guardó Marca de Repuesto'
                    });
                    */
            })


    } //fin

export const cargar_marcas_repuestos = async(context) => {
    let rpta = await axios.get('marcarepuestoJSON');
    context.commit('CARGAR_MARCAS_REPUESTOS', rpta.data);
}


export const borrar_marca_repuesto = (context) => {
        /*
                axios
                    .get('marcarepuesto/' + id + '/destruir')
                    .then(response => {
                        if (response.data == 'OK') {
                            Vue.swal({
                                text: 'Se borró correctamente',
                                position: 'top-end',
                                icon: 'success',
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                            });
                        } else {
                            Vue.swal({
                                icon: 'error',
                                title: 'ERROR!!!',
                                text: response.data
                            });
                        }
                    })

        */



    } // fin

export const aumentar = (context) => {
    console.log('actions');
    context.commit('AUMENTAR_CONTADOR');
}