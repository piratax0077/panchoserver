export const DAME_SESION = (state, ses) => {
    state.sesion = ses;
}

export const CARGAR_MARCAS_REPUESTOS = (state, marcas_repuestos) => {
    state.marcas_repuestos = marcas_repuestos;
}

export const GUARDÓ_MARCA_REPUESTO = (state, rpta) => {
    console.log('mutations, guardó_marca_repuesto: ' + rpta);
    state.guardó_marca_repuesto = rpta;
    console.log('mutations, guardó_marca_repuesto state: ' + state.guardó_marca_repuesto);
}

export const AUMENTAR_CONTADOR = (state) => {
    state.contador++;
    console.log('mutations')
}