@extends('plantillas.app')
  @section('titulo','Ingreso por Compras')
  @section('javascript')

    <script type="text/javascript">
    window.onload=function(e){
        document.getElementById("fs-detalle").disabled=true;
        document.getElementById("fs-div-item-grupo").disabled=true;

        //definir funciones algunas;
        document.getElementById("input-valor-medida").onblur=function(){
            //si hay coma lo reemplazamos por punto.
            this.value.replace(",",".");
            calcular_precio_x_medida();
        };

        document.getElementById("input-total-neto").onblur=function(){
            this.value.replace(",",".");
            calcular_precio_x_medida();
        };

        cargar_transportistas();
        cargar_proveedores();
        limpiar_storage();


    }

    function calcular_precio_x_medida(){
        let medida=document.getElementById("input-valor-medida").value;
        let total=document.getElementById("input-total-neto").value;
        if(medida.trim().length>0 && total.trim().length>0){
            let precio=(parseFloat(total)/parseFloat(medida))*1.00;
            document.getElementById("input-precio-por-medida").value=parseFloat(precio);
        }



    }

    function nueva_ot(){
        //sessionStorage.clear(); //Limpiar todas las variables de sessionStorage.
        limpiar_storage();
        location.href="/ot";
    }

    function limpiar_storage(){
        sessionStorage.removeItem('id_ot_cab');
        sessionStorage.removeItem('id_ot_det');
    }

    function agregar_detalle(){
        //validar datos
        let id_transportista=document.getElementById("cbo_transportista").value;
        if(id_transportista==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Debe elegir un transportista',
                icon: 'error',
            });
            return false;
        }


        let numero_ot=document.getElementById("input-num-ot").value.trim();
        if(numero_ot.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Número de OT/Envio/Fact',
                icon: 'error',
            });
            return false;
        }


        let fecha_ot=document.getElementById("input-fecha-ot").value;
        if(fecha_ot.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Fecha de OT/Envio/Fact',
                icon: 'error',
            });
            return false;
        }

        let fecha_recepcion=document.getElementById("input-fecha-recepcion").value;
        if(fecha_recepcion.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Fecha de Recepción',
                icon: 'error',
            });
            return false;
        }


        let receptor_ot=document.getElementById("input-receptor-ot").value.trim();
        if(receptor_ot.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Receptor de OT',
                icon: 'error',
            });
            return false;
        }


        let origen_ot=document.getElementById("input-origen-ot").value.trim();
        if(origen_ot.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Origen',
                icon: 'error',
            });
            return false;
        }

        let observaciones_ot=document.getElementById("textarea-observaciones-ot").value.trim();

        let datos={'id_transportista':id_transportista,
                    'numero_ot':numero_ot,
                    'fecha_ot':fecha_ot,
                    'fecha_recepcion':fecha_recepcion,
                    'receptor_ot':receptor_ot,
                    'origen_ot':origen_ot,
                    'observaciones_ot':observaciones_ot};

        guardar_cabecera_ot(datos);


    }

    function verificar_ot(){
        let id_transportista=document.getElementById("cbo_transportista").value;
        if(id_transportista==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Debe elegir un transportista',
                icon: 'error',
            });
            return false;
        }


        let numero_ot=document.getElementById("input-num-ot").value.trim();
        if(numero_ot.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'Falta Número de OT/Envio/Fact',
                icon: 'error',
            });
            return false;
        }
        let dato=id_transportista+"&"+numero_ot;
        let url = "/ot/verificar_ot/"+dato;
        fetch(url, {
            method: 'GET',
        }).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(data) {
            if(data.estado=="EXISTE"){
                //avisar y rellenar los campos
                Vue.swal({
                    title: 'OT EXISTENTE',
                    text: "Desea agregar más items?",
                    icon: 'warning',
                    showCancelButton: true,
                    allowOutsideClick:false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText:'NO',
                    confirmButtonText: 'SI'
                    }).then((result) => {
                        if (result.value) {
                            document.getElementById('cbo_transportista').value=data.cab['id_transportista'];
                            document.getElementById('input-num-ot').value=data.cab['numero_ot'];
                            document.getElementById('input-fecha-ot').value=data.cab['fecha_ot'];
                            document.getElementById('input-fecha-recepcion').value=data.cab['fecha_recepcion'];
                            document.getElementById('input-receptor-ot').value=data.cab['receptor_ot'];
                            document.getElementById('input-origen-ot').value=data.cab['origen_ot'];
                            $('#total_ot').html('<strong>'+new Intl.NumberFormat('es-CL').format(data.cab['total_neto'])+'</strong>');
                            document.getElementById('textarea-observaciones-ot').value=data.cab['observaciones_ot'];

                            document.getElementById("fs-div-cabecera").disabled=true;
                            document.getElementById("fs-detalle").disabled=false;
                            document.getElementById("fs-div-item-grupo").disabled=true;
                            sessionStorage.setItem('id_ot_cab',data.cab['id']);
                        }else{
                            document.getElementById('cbo_transportista').value=0;
                            document.getElementById('input-num-ot').value="";
                            document.getElementById('input-fecha-ot').value="";
                            document.getElementById('input-fecha-recepcion').value="";
                            document.getElementById('input-receptor-ot').value="";
                            document.getElementById('input-origen-ot').value="";
                            document.getElementById('textarea-observaciones-ot').value="";
                        }
                    });
            }else{
                Vue.swal({
                title: 'NO EXISTE OT',
                icon: 'info',
            });
            }
        }).catch(function(error) {
            Vue.swal({
                title: 'ERROR',
                text: error,
                icon: 'error',
            });
        });

    }

    function verificar_detalle_grupo(){
        let id_grupos=sessionStorage.getItem('id_ot_cab');
        console.log(id_grupos); //kaka
        let url = "/ot/verificar_grupos/"+id_grupos;
        fetch(url, {
            method: 'GET',
        }).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(data) {
            if(data.estado=="EXISTE"){
                console.log(data.grupos);
            }else{
                console.log("NO HAY GRUPOS");
            }


        }).catch(function(error) {
            Vue.swal({
                title: 'ERROR',
                text: error,
                icon: 'error',
            });
        });
    }

    function dame_id_proveedor_varios(){
        let cp=document.getElementById("cbo_proveedor");
        for(var i=1;i<cp.length;i++)
        {
            if(cp.options[i].text=="VARIOS")
            {
                cp.selectedIndex=i;
            }
        }
        return cp.value;
    }

    function item_es_grupo(){
        let g=document.getElementById("chk-es-grupo").checked;
        if(g==true){
            verificar_detalle_grupo();
            document.getElementById("input-num-documento").value="GRUPO";
            document.getElementById("input-num-documento").disabled=true;
            document.getElementById("input-num-items-documento").value="0";
            document.getElementById("input-num-items-documento").disabled=true;
            document.getElementById("cbo_tipo_documento").value="GRUPO";
            document.getElementById("cbo_tipo_documento").disabled=true;
            document.getElementById("cbo_tipo_paquete").value="VARIOS";
            document.getElementById("cbo_tipo_paquete").disabled=true;
            document.getElementById("input-cant-paquetes").value=0;
            document.getElementById("input-cant-paquetes").disabled=true;
            document.getElementById("cbo_proveedor").value="0"; //dame_id_proveedor_varios(); // por programación elegir VARIOS
            //document.getElementById("cbo_proveedor").disabled=true;
        }else{
            document.getElementById("input-num-documento").value="";
            document.getElementById("input-num-documento").disabled=false;
            document.getElementById("input-num-items-documento").value="";
            document.getElementById("input-num-items-documento").disabled=false;
            document.getElementById("cbo_tipo_documento").value="0";
            document.getElementById("cbo_tipo_documento").disabled=false;
            document.getElementById("cbo_tipo_paquete").value="0";
            document.getElementById("cbo_tipo_paquete").disabled=false;
            document.getElementById("input-cant-paquetes").value="";
            document.getElementById("input-cant-paquetes").disabled=false;
            document.getElementById("cbo_proveedor").value="0";
            document.getElementById("cbo_proveedor").disabled=false;
        }


        /*
        document.getElementById("cbo_tipo_documento").value="GRUPO";
        document.getElementById("cbo_tipo_documento").disabled=true;
        document.getElementById("cbo_tipo_paquete").value="VARIOS";
        document.getElementById("cbo_tipo_paquete").disabled=true;
        */
    }



    function guardar_item_detalle(){
        //validar datos antes de guardar

        let td=document.getElementById("cbo_tipo_documento").value;
        if(td==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Elegir Tipo Documento',
                icon: 'error',
            });
            return false;
        }

        let es_grupo=document.getElementById("chk-es-grupo").checked;
        if(td=="GRUPO" && !es_grupo){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Debe marcar la casilla Es grupo',
                icon: 'error',
            });
            return false;
        }

        let num_doc_detalle=document.getElementById("input-num-documento").value.trim();
        if(num_doc_detalle.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Falta Número de Documento',
                icon: 'error',
            });
            return false;
        }

        let num_items_doc_detalle=document.getElementById("input-num-items-documento").value.trim();
        if(num_items_doc_detalle.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Falta Items Documento',
                icon: 'error',
            });
            return false;
        }

        let cant_paq_detalle=document.getElementById("input-cant-paquetes").value.trim();
        if(cant_paq_detalle.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Falta Cantidad de Paquetes',
                icon: 'error',
            });
            return false;
        }

        let tp=document.getElementById("cbo_tipo_paquete").value;
        if(tp==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Elegir Tipo Paquete',
                icon: 'error',
            });
            return false;
        }

        let prov=document.getElementById("cbo_proveedor").value;
        if(prov==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Elegir Proveedor',
                icon: 'error',
            });
            return false;
        }

        let tm=document.getElementById("cbo_tipo_medida").value;
        if(tm==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Elegir Tipo Medida',
                icon: 'error',
            });
            return false;
        }

        let val_med=document.getElementById("input-valor-medida").value.trim();
        if(val_med.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Falta Valor Medida',
                icon: 'error',
            });
            return false;
        }

        let total_neto=document.getElementById("input-total-neto").value.trim();
        if(total_neto.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'DETALLE: Falta Total Neto',
                icon: 'error',
            });
            return false;
        }

        let observaciones_detalle=document.getElementById("textarea-observaciones-detalle").value.trim();

        //Si es grupo o no, en ambos casos guardar el item-detalle. Enviarlo por POST
        let id_ot_cab=sessionStorage.getItem('id_ot_cab');
        let datos={'id_ot_cab':id_ot_cab,
                    'tipo_documento':td,
                    'numero_doc_detalle':num_doc_detalle,
                    'num_item_documento':num_items_doc_detalle,
                    'cant_paq_detalle':cant_paq_detalle,
                    'tipo_paquete':tp,
                    'id_proveedor':prov,
                    'tipo_medida':tm,
                    'valor_medida':val_med,
                    'total_neto':total_neto, //precio x medida lo calculamos bien en el backend.
                    'observaciones_detalle':observaciones_detalle};
        guardar_detalle_ot(datos,es_grupo);
    }

    function nuevo_item_detalle(){
        document.getElementById("fs-detalle").disabled=false;
        document.getElementById("fs-div-item-grupo").disabled=true;

        limpiar_detalle();
        limpiar_item_grupo();
    }

    function limpiar_detalle(){
        document.getElementById("chk-es-grupo").checked=false;
        document.getElementById("cbo_tipo_documento").value=0;
        document.getElementById("cbo_tipo_documento").disabled=false;
        document.getElementById("input-num-documento").value="";
        document.getElementById("input-num-documento").disabled=false;
        document.getElementById("input-num-items-documento").value="";
        document.getElementById("input-num-items-documento").disabled=false;
        document.getElementById("input-cant-paquetes").value="";
        document.getElementById("input-cant-paquetes").disabled=false;
        document.getElementById("cbo_tipo_paquete").value=0;
        document.getElementById("cbo_tipo_paquete").disabled=false;
        document.getElementById("cbo_proveedor").value=0;
        document.getElementById("cbo_tipo_medida").value=0;
        document.getElementById("input-total-neto").value="";
        document.getElementById("input-valor-medida").value="";
        document.getElementById("input-precio-por-medida").value="0";
        document.getElementById("textarea-observaciones-detalle").value="";
        sessionStorage.removeItem('id_ot_det');
    }

    function limpiar_item_grupo(){
        document.getElementById("cbo_tipo_documento_grupo").value=0;
        document.getElementById("input-num-documento-grupo").value="";
        document.getElementById("input-num-items-documento-grupo").value="";
        document.getElementById("input-cant-paquetes-grupo").value="";
        document.getElementById("cbo_tipo_paquete_grupo").value=0;
        //document.getElementById("cbo_proveedor_grupo").value=0;
        document.getElementById("textarea-observaciones-item-grupo").value="";
    }

    function cargar_transportistas(){
        let url = "/proveedor/dametransportistas";
        fetch(url, {
            method: 'GET',
        }).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(rpta) { //aquí viene de regreso la información
            if(rpta.estado=="OK"){
                let transportistas=JSON.parse(rpta.transportistas);
                //$('#cbo_transportista option').remove();
                //$('#cbo_transportista').append('<option value="0">Elija Transportista</option>');
                transportistas.forEach(function(t){
                    $('#cbo_transportista').append('<option value="'+t.id+'">'+t.empresa_nombre_corto.toUpperCase()+'</option>');
                });
            }
        }).catch(function(error) {
            console.log(error);
        });
    }

    function cargar_proveedores(){
        let url = "/proveedor/dameproveedores";
        fetch(url, {
            method: 'GET',
        }).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(rpta) { //aquí viene de regreso la información
            if(rpta.estado=="OK"){
                let proveedores=JSON.parse(rpta.proveedores);
                //$('#cbo_transportista option').remove();
                //$('#cbo_transportista').append('<option value="0">Elija Transportista</option>');
                proveedores.forEach(function(p){
                    $('#cbo_proveedor').append('<option value="'+p.id+'">'+p.empresa_nombre_corto.toUpperCase()+'</option>');
                    //$('#cbo_proveedor_grupo').append('<option value="'+p.id+'">'+p.empresa_nombre_corto.toUpperCase()+'</option>');
                });
            }
        }).catch(function(error) {
            console.log(error);
        });

    }

    function guardar_cabecera_ot(datos){
        sessionStorage.removeItem('id_ot_det');
        let url="/ot/guardarcabecera";
        const param = new FormData();
        param.append('datos', JSON.stringify(datos));
        fetch(url, {
            method: 'POST',
            body: param,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(response) { //información del response
            if (response.ok) {
                return response.json();
            } else {
                console.log(response.text());
            }
        }).then(function(data) { //aquí viene de regreso la información
            //ANTES DE GUARDAR COMPROBAR SU EXISTENCIA.
            //DE EXISTIR, DEBE AVISAR QUE YA EXISTE, Y PREGUNTAR SI CONTINUA EL INGRESO DEL DETALLE
            if (data.estado == "OK") {
                //desactivar areas
                document.getElementById("fs-div-cabecera").disabled=true;
                document.getElementById("fs-detalle").disabled=false;
                document.getElementById("fs-div-item-grupo").disabled=true;
                sessionStorage.setItem('id_ot_cab',data.id_ot_cab); //para la grabación de los items del detalle

                // también se puede usar asi: sessionStorage.id_ot_cab=data.id_ot_cab;
                // obtener el valor guardado: let x=sessionStorage.getItem('id_ot_cab');
                // eliminar el valor guardado: sessionStorage.removeItem('id_ot_cab');
                // eliminar todos los valores existentes: sessionStorage.clear();
                // Depurar sus valores SHIFT+CTRL+I, pestaña APPLICATION, desplegar la sección Session Storage y seleccionar Localhost...


                let elvalor=sessionStorage.getItem('id_ot_cab');
                console.log(`Valor de id_ot_cab: ${elvalor}`); //tilde invertida

            }

            if (data.estado == "EXISTE") {
                //avisar y rellenar los campos
                Vue.swal({
                    title: 'OT EXISTENTE',
                    text: "Desea agregar más items?",
                    icon: 'warning',
                    showCancelButton: true,
                    allowOutsideClick:false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText:'NO',
                    confirmButtonText: 'SI'
                    }).then((result) => {
                        if (result.value) {
                            document.getElementById('cbo_transportista').value=data.cab['id_transportista'];
                            document.getElementById('input-num-ot').value=data.cab['numero_ot'];
                            document.getElementById('input-fecha-ot').value=data.cab['fecha_ot'];
                            document.getElementById('input-fecha-recepcion').value=data.cab['fecha_recepcion'];
                            document.getElementById('input-receptor-ot').value=data.cab['receptor_ot'];
                            document.getElementById('input-origen-ot').value=data.cab['origen_ot'];
                            document.getElementById('textarea-observaciones-ot').value=data.cab['observaciones_ot'];

                            document.getElementById("fs-div-cabecera").disabled=true;
                            document.getElementById("fs-detalle").disabled=false;
                            document.getElementById("fs-div-item-grupo").disabled=true;
                            sessionStorage.setItem('id_ot_cab',data.cab['id']);
                        }else{
                            document.getElementById('cbo_transportista').value=0;
                            document.getElementById('input-num-ot').value="";
                            document.getElementById('input-fecha-ot').value="";
                            document.getElementById('input-fecha-recepcion').value="";
                            document.getElementById('input-receptor-ot').value="";
                            document.getElementById('input-origen-ot').value="";
                            document.getElementById('textarea-observaciones-ot').value="";
                        }
                    });
            }

        }).catch(function(error) {
            console.log(error);
        });
    }

    function guardar_detalle_ot(datos,es_grupo){
        let url="/ot/guardardetalle";
        const param = new FormData();
        param.append('datos', JSON.stringify(datos));
        fetch(url, {
            method: 'POST',
            body: param,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(response) { //información del response
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(data) { //aquí viene de regreso la información
            if (data.estado == "OK") {
                $('#total_ot').html('<strong>'+new Intl.NumberFormat('es-CL').format(data.total_neto)+'</strong>');
                //desactivar areas
                if(es_grupo==true){
                    document.getElementById("fs-detalle").disabled=true; //fs > es un fieldset
                    document.getElementById("fs-div-item-grupo").disabled=false;
                    sessionStorage.setItem('id_ot_det',data.id_ot_det);
                    limpiar_item_grupo();
                }else{
                    limpiar_detalle();
                }
            }

            if(data.estado=='EXISTE'){
                //llegan dos arrays, det con los datos de detalle y grupo con los datos del grupo
                //llega también id_ot_det
                if(es_grupo==true){
                    document.getElementById("fs-detalle").disabled=true; //fs > es un fieldset
                    document.getElementById("fs-div-item-grupo").disabled=false;
                    sessionStorage.setItem('id_ot_det',data.id_ot_det);
                    limpiar_item_grupo();
                }else{
                    Vue.swal({
                        title: 'ATENCION!!!',
                        text: 'DETALLE: Ya existe item',
                        icon: 'info',
                    });
                    return false;
                }
            }

            if(data.estado=='EXISTE_GRUPO'){
                Vue.swal({
                        title: 'ATENCION!!!',
                        text: 'DETALLE: Ya existe documento en un grupo',
                        icon: 'info',
                    });
            }
        }).catch(function(error) {
            console.log(error);
        });
    }

    function guardar_detalle_grupo(){


        let tdg=document.getElementById("cbo_tipo_documento_grupo").value;
        if(tdg==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Elegir Tipo Documento',
                icon: 'error',
            });
            return false;
        }

        let num_doc_grupo=document.getElementById("input-num-documento-grupo").value.trim();
        if(num_doc_grupo.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Falta Número de Documento',
                icon: 'error',
            });
            return false;
        }

        let num_item_doc_grupo=document.getElementById("input-num-items-documento-grupo").value.trim();
        if(num_item_doc_grupo.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Falta Items Documento',
                icon: 'error',
            });
            return false;
        }

        let cant_paq_grupo=document.getElementById("input-cant-paquetes-grupo").value.trim();
        if(cant_paq_grupo.length==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Falta Cantidad de Paquetes',
                icon: 'error',
            });
            return false;
        }

        let tpg=document.getElementById("cbo_tipo_paquete_grupo").value;
        if(tpg==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Elegir Tipo Paquete',
                icon: 'error',
            });
            return false;
        }

        let provg=document.getElementById("cbo_proveedor").value;
/*
        if(provg==0){
            Vue.swal({
                title: 'ERROR',
                text: 'ITEM GRUPO: Elegir Proveedor',
                icon: 'error',
            });
            return false;
        }
*/
        let observaciones_detalle_grupo=document.getElementById("textarea-observaciones-item-grupo").value.trim();
        let id_ot_det=sessionStorage.getItem('id_ot_det');
        let datos={'id_ot_det':id_ot_det,
                    'tipo_documento_grupo':tdg,
                    'numero_doc_detalle_grupo':num_doc_grupo,
                    'numero_item_documento_grupo': num_item_doc_grupo,
                    'cant_paq_detalle_grupo':cant_paq_grupo,
                    'tipo_paquete_grupo':tpg,
                    'id_proveedor_grupo':provg,
                    'observaciones_detalle_grupo':observaciones_detalle_grupo};


        let url="/ot/guardardetallegrupo";
        const param = new FormData();
        param.append('datos', JSON.stringify(datos));
        fetch(url, {
            method: 'POST',
            body: param,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(response) { //información del response
            if (response.ok) {
                return response.json();
            } else {
                throw "Error en la llamada Ajax";
            }
        }).then(function(data) { //aquí viene de regreso la información
            if (data.estado == "OK") {
                document.getElementById("input-cant-paquetes").value=data.total_paquetes_grupo;
                document.getElementById("input-num-items-documento").value=data.total_item_documento_grupo;
                limpiar_item_grupo();
            }

            if(data.estado=='EXISTE'){
                Vue.swal({
                    title: 'ATENCIÓN!!!',
                    text: 'ITEM GRUPO: Ya existe documento en ITEM GRUPO',
                    icon: 'warning',
                });
            }

            if(data.estado=='EXISTE_DETALLE'){
                Vue.swal({
                    title: 'ATENCIÓN!!!',
                    text: 'ITEM GRUPO: Ya existe documento en DETALLE. PROV: '+data.proveedor,
                    icon: 'warning',
                });

            }

        }).catch(function(error) {
            console.log(error);
        });
    }


    function soloNumeros(e)
    {
        var key = window.Event ? e.which : e.keyCode;
        return ((key >= 48 && key <= 57) || (key==8))
    }

    function soloDecimales(e,quien){
        //https://keycode.info/
        var key = window.Event ? e.which : e.keyCode;
        //console.log(key);
        let valor=quien.value;
        let haycoma_o_punto=(valor.indexOf(',')>0 || valor.indexOf('.')>0) ? true:false; //para que no se repita la coma
        return ((key >= 48 && key <= 57) || (key==8) || (key==44 && !haycoma_o_punto) || (key==46 && !haycoma_o_punto)); // 44 es la coma
    }

    </script>

  @endsection
  @section('style')
    <style>
        .orden_transporte_principal{
            min-height: 100vh;
            max-height: 100vh;
            display:grid;
            grid-template-rows:40px 160px 210px 200px;
            grid-gap:5px;
        }
        #div-titulo{
            grid-row:1/2;
            display:grid;
            grid-template-columns: repeat(6,1fr);
        }
        #div-titulo-texto{
            grid-column:2/6;
        }
        #div-titulo-boton{
            grid-column:6/7;
        }
        #div-cabecera{
            background-color: cornsilk;
            grid-row:2/3;
            display:grid;
            grid-template-columns: repeat(7,1fr);
            grid-template-rows: 80px 80px;
            grid-gap:3px;
        }
        #boton-agregar-detalle, #boton-guardar-item, #boton-guardar-item-grupo{
            display:grid;
            align-items:center;
        }
        #observaciones-ot{
            grid-column:1/4;
            grid-row:2/3;
        }
        #detalle{
            grid-row:3/4;
            display:grid;
            grid-template-columns:1fr;
            grid-template-rows: 40px 1fr 1fr;
        }
        #detalle-titulo{
            grid-column:1/6;
            grid-row:1/2;
            display:grid;
            grid-template-columns: repeat(7,1fr);
        }
        #detalle-titulo-texto{
            grid-column:1/6;

        }
        #detalle-titulo-boton-nuevo-item{
            grid-column:6/7;
        }
        #detalle-1{
            grid-row:2/3;
            display:grid;
            grid-template-columns: repeat(7,1fr);
            grid-gap:3px;
        }
        #check-es-grupo{
            display:grid;
            justify-content: center;
            align-items: center;
        }
        #detalle-2{
            grid-row:3/4;
            display:grid;
            grid-template-columns: repeat(6,1fr);
            grid-gap:3px;
        }
        #div-item-grupo{
            background-color: cornsilk;
            grid-row:4/5;
            display:grid;
            grid-template-columns:1fr;
            grid-template-rows: 40px 1fr 1fr;
        }
        #item-grupo-titulo{
            grid-column:1/6;
            grid-row:1/2;
            display:grid;
            grid-template-columns: repeat(7,1fr);
        }
        #item-grupo-titulo-texto{
            grid-column:1/6;
        }
        #item-grupo-titulo-boton-nuevo-item{
            grid-column:6/7;
        }
        #item-grupo-1{
            grid-row:2/3;
            display:grid;
            grid-template-columns: repeat(6,1fr);
            grid-gap:3px;
        }
        #item-grupo-2{
            grid-row:3/4;
            display:grid;
            grid-template-columns: repeat(6,1fr);
        }
    </style>
  @endsection
@section('contenido_ingresa_datos')
<div class="orden_transporte_principal">
    <div id="div-titulo">
        <div id="div-titulo-texto">
            <center><h2>ORDEN TRANSPORTE</h2></center>
        </div>
        <div id="div-titulo-boton">
            <button class="form-control btn btn-primary btn-md" onclick="nueva_ot()">NUEVA OT</button>
        </div>

    </div>
    <fieldset id="fs-div-cabecera">
    <div id="div-cabecera">
        <div class="form-group" id="div-transportista">
            <label for="cbo_transportista">Transportista:</label>
            <select class="form-control form-control-sm" id="cbo_transportista">
                <option value="0">Elegir Transportista</option>
            </select>
        </div>
        <div class="form-group" id="numero-ot">
            <label for="input-num-ot">Número OT/Envio/Fact:</label>
            <input class="form-control form-control-sm" type="text" id="input-num-ot">
            <button class="btn btn-success btn-sm" onclick="verificar_ot()">VERIFICAR NUMERO</button>
        </div>
        <div class="form-group" id="fecha-ot">
            <label for="input-fecha-ot">Fecha OT/Envio/Fact:</label>
            <input class="form-control form-control-sm" type="date" id="input-fecha-ot">
        </div>
        <div class="form-group" id="fecha-recepcion">
            <label for="input-fecha-recepcion">Fecha Recepción:</label>
            <input class="form-control form-control-sm" type="date" id="input-fecha-recepcion">
        </div>
        <div class="form-group" id="receptor-ot">
            <label for="input-receptor-ot">Recepcionó:</label>
            <input class="form-control form-control-sm" type="text" id="input-receptor-ot" placeholder="nombre persona">
        </div>
        <div class="form-group" id="origen-ot">
            <label for="input-origen-ot">Origen OT:</label>
            <input class="form-control form-control-sm" type="text" id="input-origen-ot" placeholder="de donde llega">
        </div>
        <div id="div_total_ot">
            <strong>TOTAL OT:</strong>
            <div id="total_ot"><strong>0.00</strong></div>
        </div>
        <div class="form-group" id="observaciones-ot">
            <label for="input-observaciones-ot">Observaciones OT:</label>
            <textarea class="form-control form-control-sm" id="textarea-observaciones-ot" rows="2"></textarea>
        </div>
        <div id="boton-agregar-detalle">
            <button class="form-control btn btn-primary btn-md" onclick="agregar_detalle()">AGREGAR DETALLE</button>
        </div>
    </div>
    </fieldset>
    <fieldset id="fs-detalle">
    <div id="detalle">
        <div id="detalle-titulo">
            <div id="detalle-titulo-texto">
                <strong>DETALLE:</strong>
            </div>
            <div id="detalle-titulo-boton-nuevo-item">
                <button class="btn btn-primary btn-sm">NUEVO ITEM</button>
            </div>
        </div>
        <div id="detalle-1">
            <div class="form-group" id="check-es-grupo">
                <label for="chk-es-grupo">Es grupo:</label>
                <input class="form-control form-control-sm" type="checkbox" id="chk-es-grupo" onclick="item_es_grupo()">
            </div>
            <div class="form-group" >
                <label for="cbo_tipo_documento">Tipo Documento:</label>
                <select class="form-control form-control-sm" name="" id="cbo_tipo_documento">
                    <option value="0">Elegir Documento</option>
                    <option value="FACTURA">Factura</option>
                    <option value="GUIA">Guia</option>
                    <option value="OT">OT</option>
                    <option value="GRUPO">Grupo</option>
                </select>
            </div>
            <div class="form-group" >
                <label for="input-num-documento">Núm Documento:</label>
                <input class="form-control form-control-sm" type="text" id="input-num-documento">
            </div>
            <div class="form-group" >
                <label for="input-num-items-documento">Items Documento:</label>
                <input class="form-control form-control-sm" type="text" id="input-num-items-documento" placeholder="Obtenerlo del docu">
            </div>
            <div class="form-group" >
                <label for="input-cant-paquetes">Cant Paquetes:</label>
                <input class="form-control form-control-sm" type="text" id="input-cant-paquetes" onKeyPress="return soloNumeros(event)">
            </div >
            <div class="form-group" >
                <label for="cbo_tipo_paquete">Tipo Paquete:</label>
                <select class="form-control form-control-sm" name="" id="cbo_tipo_paquete">
                    <option value="0">Elegir Paquete</option>
                    <option value="UNIDAD">Unidad</option>
                    <option value="CAJA">Caja</option>
                    <option value="BULTO">Bulto</option>
                    <option value="PALLET">Pallet</option>
                    <option value="VARIOS">Varios</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>
            <div class="form-group" >
                <label for="cbo_proveedor">Proveedor:</label>
                <select class="form-control form-control-sm" name="" id="cbo_proveedor">
                    <option value="0">Elegir Proveedor</option>
                </select>
            </div>
        </div>
        <div id="detalle-2">
            <div class="form-group">
                <label for="cbo_tipo_medida">Tipo Medida:</label>
                <select class="form-control form-control-sm" name="" id="cbo_tipo_medida">
                    <option value="0">Elegir Medida</option>
                    <option value="KILO">Kilo</option>
                    <option value="MT3">Metro Cúbico</option>
                    <option value="UNIDAD">Unidad</option>
                </select>
            </div>
            <div class="form-group">
                <label for="input-valor-medida">Valor Medida:</label>
                <input class="form-control form-control-sm" type="text" id="input-valor-medida" onkeypress="return soloDecimales(event,this)">
            </div>
            <div class="form-group">
                <label for="input-total-neto">Total Neto:</label>
                <input class="form-control form-control-sm" type="text" id="input-total-neto" onkeypress="return soloDecimales(event,this)">
            </div>
            <div class="form-group">
                <label for="input-precio-por-medida">Precio Referencial:</label>
                <input class="form-control form-control-sm" type="text" id="input-precio-por-medida" disabled>
            </div>
            <div class="form-group">
                <label for="input-observaciones-detalle">Observaciones:</label>
                <textarea class="form-control form-control-sm" id="textarea-observaciones-detalle" rows="2"></textarea>
            </div>
            <div class="form-group" id="boton-guardar-item">
                <button class="btn btn-primary btn-sm" onclick="guardar_item_detalle()">GUARDAR ITEM</button>
            </div>
        </div>
    </div>
    </fieldset>
    <fieldset id="fs-div-item-grupo">
    <div id="div-item-grupo">
        <div id="item-grupo-titulo">
            <div id="item-grupo-titulo-texto">
                <strong>ITEM GRUPO:</strong>
            </div>
            <div id="item-grupo-titulo-boton-nuevo-item">
                <button class="btn btn-primary btn-sm" onclick="nuevo_item_detalle()">NUEVO ITEM DETALLE</button>
            </div>
        </div>
        <div id="item-grupo-1">
            <div class="form-group">
                <label for="cbo_tipo_documento_grupo">Tipo Documento:</label>
                <select class="form-control form-control-sm" name="" id="cbo_tipo_documento_grupo">
                    <option value="0">Elegir Documento</option>
                    <option value="FACTURA">Factura</option>
                    <option value="GUIA">Guia</option>
                    <option value="OT">OT</option>
                </select>
            </div>
            <div class="form-group">
                <label for="input-num-documento-grupo">Núm Documento:</label>
                <input class="form-control form-control-sm" type="text" id="input-num-documento-grupo">
            </div>
            <div class="form-group" >
                <label for="input-num-items-documento-grupo">Items Documento:</label>
                <input class="form-control form-control-sm" type="text" id="input-num-items-documento-grupo" placeholder="Obtenerlo del docu">
            </div>
            <div class="form-group">
                <label for="input-cant-paquetes-grupo">Cant Paquetes:</label>
                <input class="form-control form-control-sm" type="text" id="input-cant-paquetes-grupo">
            </div>
            <div class="form-group">
                <label for="cbo_tipo_paquete_grupo">Tipo Paquete:</label>
                <select class="form-control form-control-sm" name="" id="cbo_tipo_paquete_grupo">
                    <option value="0">Elegir Paquete</option>
                    <option value="UNIDAD">Unidad</option>
                    <option value="CAJA">Caja</option>
                    <option value="BULTO">Bulto</option>
                    <option value="PALLET">Pallet</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <!--
                <label for="cbo_proveedor_grupo">Proveedor:</label>
                <select class="form-control form-control-sm" name="" id="cbo_proveedor_grupo">
                    <option value="0">Elegir Proveedor</option>
                </select>
            -->
            </div>
        </div>
        <div id="item-grupo-2">
            <div class="form-group">
                <label for="textarea-observaciones-item-grupo">Observaciones:</label>
                <textarea class="form-control form-control-sm" id="textarea-observaciones-item-grupo" rows="2"></textarea>
            </div>
            <div id="boton-guardar-item-grupo">
                <button class="btn btn-primary btn-sm" onclick="guardar_detalle_grupo()">GUARDAR ITEM</button>
            </div>
        </div>
    </div>
    </fieldset>
</div>
@endsection

@section('contenido_ver_datos')

@endsection
