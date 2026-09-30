@extends('plantillas.app')
@section('titulo','VENTAS DIARIAS')
@section('javascript')
<script type="text/javascript">


    let resumen_diario_desbloqueado = false;

    function solicitar_clave_resumen_diario(){
        Vue.swal({
            title: 'Contenido protegido',
            text: 'Ingrese su contraseña para visualizar los indicadores de rentabilidad.',
            input: 'password',
            inputPlaceholder: 'Contraseña',
            confirmButtonText: '<i class="fa fa-unlock"></i> Desbloquear',
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'current-password'
            },
            inputValidator: function(valor){
                if(!valor || !valor.trim()){
                    return 'Debe ingresar su contraseña.';
                }
            },
            preConfirm: function(clave){
                return $.ajax({
                    type: 'POST',
                    url: '{{ url("/clave") }}',
                    data: {
                        clave: clave
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                }).then(function(resp){
                    if(resp !== 'OK'){
                        throw new Error('La contraseña ingresada no es válida.');
                    }

                    return resp;
                }).catch(function(error){
                    let mensaje = 'No fue posible validar la contraseña.';

                    if(error && error.message){
                        mensaje = error.message;
                    }

                    Vue.swal.showValidationMessage(mensaje);
                });
            }
        }).then(function(resultado){
            if(!resultado.isConfirmed){
                return;
            }

            resumen_diario_desbloqueado = true;

            $('#bloque_resumen_diario_protegido')
                .stop(true, true)
                .slideDown(200);

            $('#btn_ver_resumen')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fa fa-lock"></i> Ocultar')
                .attr('onclick', 'bloquear_resumen_diario()');

            Vue.swal({
                icon: 'success',
                title: 'Indicadores desbloqueados',
                timer: 1200,
                showConfirmButton: false
            });
        });
    }

    function bloquear_resumen_diario(){
        resumen_diario_desbloqueado = false;

        $('#bloque_resumen_diario_protegido').hide();

        $('#btn_ver_resumen')
            .removeClass('btn-outline-danger')
            .addClass('btn-outline-secondary')
            .html('<i class="fa fa-key"></i> Ver indicadores')
            .attr('onclick', 'solicitar_clave_resumen_diario()');
    }

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
.ventas-diarias-header{background:#6495ed;color:#fff;padding:15px 20px;border-radius:4px 4px 0 0}
.ventas-diarias-filtros{background:#fff;border:1px solid #dfe3e8;border-top:0;padding:18px 20px;margin-bottom:18px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.ventas-diarias-contenido{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,34%);gap:16px;align-items:start}
#totales,#detalle{min-width:0}.estado-carga-diaria{display:none;padding:30px;text-align:center;color:#6c757d;font-weight:600}
#fecha_pago_div{width:100%;margin-bottom:15px}
@media(max-width:1100px){.ventas-diarias-contenido{grid-template-columns:1fr}}
</style>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
    <div class="ventas-diarias-header">
        <h4 class="mb-1"><i class="fa fa-line-chart"></i> Ventas diarias</h4>
        <small>Ventas, costos, utilidad, medios de pago y documentos del día.</small>
    </div>
    <div class="ventas-diarias-filtros">
        <div class="row align-items-end">
            <div class="col-md-4 col-sm-6">
                <label for="fecha"><strong>Fecha del informe</strong></label>
                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" id="fecha" class="form-control">
            </div>
            <div class="col-md-4 col-sm-6 mt-3 mt-sm-0">
                <button type="button" class="btn btn-success" onclick="mostrar()">
                    <i class="fa fa-search"></i> Consultar
                </button>
                <button type="button" class="btn btn-primary ml-2" onclick="imprimir()">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
    <div id="estado_carga_diaria" class="estado-carga-diaria">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
        <div class="mt-2">Calculando el resumen diario...</div>
    </div>
    <div class="ventas-diarias-contenido">
        <div id="totales"></div>
        <div id="detalle"></div>
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
