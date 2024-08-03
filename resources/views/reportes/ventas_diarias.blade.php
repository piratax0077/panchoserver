@extends('plantillas.app')
@section('titulo','VENTAS DIARIAS')
@section('javascript')
<script type="text/javascript">

    function mostrar(){
        let fecha=document.getElementById("fecha").value;
        let url='{{url("/reportes/totales")}}'+'/'+fecha;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'CREANDO REPORTE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                
                Vue.swal.close();
                
                $('#totales').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });
    }

    function detalle(doc,usu,form){
        let fecha=document.getElementById("fecha").value;
        let info=fecha+"&"+doc+"&"+usu+"&"+form;
        let url='{{url("/reportes/detalle")}}'+'/'+info;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#detalle').html(resp);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }
         });
    }

    function imprimir(){
        Vue.swal({
                title: 'P R O N T O',
                icon: 'warning',
            });
    }

    function imprimir_xml(xml){
        let url='{{url("imprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                Vue.swal.close();
                let r=JSON.parse(resp);
                if(r.estado=='OK'){
                    var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                    var w=window.open(r.mensaje,'_blank',config);
                    w.focus();
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error',
                    });
                }
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });

            }

        });

    }

    function imprimir_pdf(pdf){
        let url = '{{url("imprimir_pdf")}}'+'/'+pdf;
        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            success: function(resp){
                Vue.swal.close();
                console.log(resp);
                
                let r=JSON.parse(resp);
                if(r.estado=='OK'){
                    var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                    var w=window.open(r.mensaje,'_blank',config);
                    w.focus();
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error',
                    });
                }
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                    });
            }
        })
    }

    function activar_forma_pago(id){
        let marca=document.getElementById("formita-"+id).checked;
        document.getElementById("monto-"+id).disabled=!marca;
        document.getElementById("referencia-"+id).disabled=!marca;
    }

    function pedir_clave(doc){
        Vue.swal({
            title: 'Ingrese Contraseña',
            input: 'password',
            confirmButtonText: 'Verificar',
            showConfirmButton: true,
            showCancelButton: true,
        }).then((result) => {
            if(result.isConfirmed){
                let clave=result.value;
                let url='{{url("/clave")}}';
                let parametros={
                    clave:clave,
                };
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                type:'POST',
                url:url,
                data:parametros,
                success:function(resp){
                    Vue.swal.close();
                    if(resp=="OK"){
                        abrir_pagar_delivery(doc)
                    }
                },
                error: function(error){
                    Vue.swal.close();
                        Vue.swal({
                            title: 'ERROR',
                            text: error.responseText,
                            icon: 'error',
                        });
                }
                });



            }


        });
    }

    function abrir_pagar_delivery(doc){
        let p=doc.split("_");
        let tipo_documento="bo";
        if(p[0]==33) tipo_documento="fa";
        let id_documento=p[1];
        let num_documento=p[2];
        let total_documento=parseInt(p[3]);
        let id_cliente=p[4];

        $('#pagar-delivery-modal').on('shown.bs.modal', function () {
            //$("#valor_xml").focus();
            $("#documento_pagar").html(tipo_documento=="bo" ? "Documento: Boleta N° "+num_documento+" por "+total_documento : "Documento: Factura N° "+num_documento+" por "+total_documento);
            document.getElementsByName("forma_pago_monto")[0].value=total_documento;
            document.getElementById("valor_total").value=total_documento;
            document.getElementById("dato_delivery").value=doc;
        });
        $("#pagar-delivery-modal").modal("show");
    }

    function calcular_sumatoria()
    {
        var total_pago=0;
        var paguitos=$('input[name="forma_pago"]:checked');
        var monto=0;
        var m="";

        paguitos.each(function() {
        m="monto-"+$(this).val();
        monto=Number(document.getElementById(m).value.replace(/\./g,""));
        total_pago=total_pago+monto;
        });
        var t=new Intl.NumberFormat('es-CL').format(total_pago);
        //$("#total_forma_pago").html("<b><p style='color:blue'>"+t+"</p></b>");
        return total_pago;
    }

    function verificar_pagos_delivery(){
        let paguitos=$('input[name="forma_pago"]:checked');
        //Si no selecciona ningún check??
        let seleccionados=paguitos.length;
        if(seleccionados==0)
        {
            Vue.swal({
                text: 'Debe seleccionar al menos una forma de pago',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
            return false;
        }

        //inicio paguitos
        var id_forma_pago=[];
        var monto_forma_pago=[];
        var referencia_forma_pago=[];
        var m="";
        var r="";
        var monto="";
        var referencia_pago="";
        let error=false;
        paguitos.each(function() {
            //Si los checks seleccionados no tienen monto ni referencia??
            var texto_seleccionado=$(this)[0].nextSibling.nodeValue.toUpperCase().trim();
            m="monto-"+$(this).val();
            monto=document.getElementById(m).value;
            //console.log("Checkbox " + $(this).prop("id") +  " (" + $(this).val() + ") Seleccionado MONTO-m: "+m+" TEXTO: "+texto_seleccionado+" monto-valor: "+Number(monto));
            if(isNaN(monto) || Number(monto)<=0)
            {
                Vue.swal({
                    text: texto_seleccionado+": Monto vacio, cero o no es un número válido.",
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    });
                error=true;
                return false; //sale del bucle jquery paguitos.each
            }

            r="referencia-"+$(this).val();
            referencia_pago=document.getElementById(r).value.toUpperCase().trim();
            if(referencia_pago.length==0)
            {
                Vue.swal({
                    text: texto_seleccionado+": Referencia vacia, cero o no es un número válido.",
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    });
                error=true;
                return false; //sale del bucle jquery paguitos.each
            }

            id_forma_pago.push($(this).val());
            monto_forma_pago.push(monto);
            referencia_forma_pago.push(referencia_pago);
        }); // fin de paguitos

        if(error){
            return false; //sale de la funcion...
        }

        var error_pago=total_pagado();
        if(error_pago=="SI")
        {
            mensaje_error="Total a Pagar no coincide con Total Documento";
            Vue.swal({
                title:"ATENCIÓN!!!",
                text: mensaje_error,
                position: 'center',
                icon: 'error',
                showConfirmButton: false,
                timer: 5000,
                });
            return false;
        }

        return true;
    }

    function total_pagado()
    {
        let total_pago=calcular_sumatoria();

        //Comparar con el total del documento
        let total_documento=Number(document.getElementById("valor_total").value);
        let total_pagado=Number(total_pago);
        let diferencia=Math.abs(total_pagado-total_documento);

        if(diferencia>2)
        {
            return "SI";
        }else{
            return "NO"
        }

    }

    function pagar_delivery(){
        //necesita tipo_doc, id_doc, id_cliente, fecha_pago, referencia, monto, usuarios_id

        let resp=verificar_pagos_delivery();
        if(resp===true){ //procesar los pagos

            //leer los pagos
            let id_forma_pago=[];
            let monto_forma_pago=[];
            let referencia_forma_pago=[];
            let m="";
            let r="";
            let paguitos=$('input[name="forma_pago"]:checked');
            paguitos.each(function() {
                m="monto-"+$(this).val();
                r="referencia-"+$(this).val();
                id_forma_pago.push($(this).val());
                monto_forma_pago.push(document.getElementById(m).value);
                referencia_forma_pago.push(document.getElementById(r).value.toUpperCase().trim());
            });

            let dato=document.getElementById("dato_delivery").value;
            let fecha_pago=document.getElementById("fecha").value;
            var url='{{url("ventas/agregar_pago")}}';

            let parametros={id_forma_pago:id_forma_pago,
                    monto_forma_pago:monto_forma_pago,
                    referencia_forma_pago:referencia_forma_pago,
                    fecha_pago:fecha_pago,
                    dato:dato
                };

            $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });

            $.ajax({
                type:'POST',
                beforeSend: function () {
                    //console.log("guardando pagos delivery");
                },
                url:url,
                data:parametros,
                success:function(rs){
                    if(rs=="OK"){
                        dame_formas_pago_delivery(); // para borrar los pagos jejeje... pendex...
                        $("#pagar-delivery-modal").modal("hide");
                        mostrar();
                        window.location.href='#fin_pagina'; //ir al final de la página para poder ver los deliverys pendientes
                    }
                },
                error: function(error){
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                }
            });
        }
    }

    function dame_formas_pago_delivery()
    {
        var url='{{url("ventas/dame_forma_pago_delivery")}}';
        $.ajax({
            type:'GET',
            beforeSend: function () {
                //$('#mensajes').html("Cargando Formas de Pago...");
                },
            url:url,
            success:function(formas){
                //$('#mensajes').html("&nbsp;");
                $("#formas_pago_delivery").html(formas);
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }

        });

    }

    window.onload = function(e){
        mostrar();
        dame_formas_pago_delivery();
    }
</script>
@endsection
@section('style')
<style>
    .contenedor{
        margin: 0px;
        display:grid;
        grid-template-columns: repeat(6,1fr);
        grid-template-rows: 30px 40px auto;
        grid-gap: 5px;
    }
    #titulo{
        grid-column: 1/7;
        background-color: cornflowerblue;
    }
    #botones{
        grid-column: 1/7;
        grid-row: 2/3;
        background-color:gainsboro;
        display:grid;
        grid-template-columns: repeat(6,1fr);
        grid-auto-flow: column;
    }
    #fecha_reporte{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;
        grid-column: 1/2;
    }
    #btn_mostrar{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 2/3;
    }
    #btn_imprimir{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 3/4;
    }
    #cuerpo{
        grid-column:1/7;
        grid-row:3/4;
        display:grid;
        grid-template-columns: 65% 35%;
        grid-auto-flow: column;
    }
    #totales{
        grid-column:1/2;
    }
    #detalle{
        grid-column:2/3;
    }
    #fecha_pago_div{
        align-self:flex-start;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;
        width: 65%;
    }
</style>
@endsection
@section('contenido_ingresa_datos')
    <div class="contenedor">
        <div class="titulazo">
            <center><h4>Reporte Ventas Diarias</h4></center>
        </div>
        <div id="botones">
            <div id="fecha_reporte">
                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" value='<?php echo date("Y-m-d"); ?>' id="fecha" class="form-control  form-control-sm">
            </div>
            <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
            <div id="btn_imprimir"><button class="btn btn-sm btn-primary" onclick="imprimir()">IMPRIMIR</button></div>
        </div>
        <div id="cuerpo">
            <div id="totales">totales</div>
            <div id="detalle">detalle</div>
        </div>
        <a name="fin_pagina"></a>
    </div>

    <!-- MODAR PAGAR-DELIVERY -->
    <div class="modal fade" id="pagar-delivery-modal" tabindex="-1" role="dialog" aria-labelledby="pagar-delivery-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="pagar-delivery-label">PAGAR DELIVERY</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="valor_total">
                <input type="hidden" id="dato_delivery">
                <div id="documento_pagar"></div>
                <div id="fecha_pago_div">
                    <label for="fecha_pago">Fecha Pago:</label>
                    <input type="date" value='<?php echo date("Y-m-d"); ?>' id="fecha_pago" class="form-control  form-control-sm">
                </div>
                <div id="formas_pago_delivery"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-success" onclick="pagar_delivery()">PAGAR</button>
            </div>
          </div>
        </div>
      </div>
@endsection
