@extends('plantillas.app')
@section('titulo','Nota de Débito')
@section('javascript')
    <script>
        function confirmar_documento(){
            Vue.swal({
                title: '¿Está seguro de generar Nota de Débito?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Anular'
                }).then((result) => {
                if (result.isConfirmed) {
                    generar_xml();
                }
            })
        }


        function generar_xml()
        {

            let doc_ref="fa*"+(document.getElementById("num_nd").value).trim()+"*"+document.getElementById("fec_documento_h").value;

            if(document.getElementById("opt_boleta").checked)
            {
            //doc_ref="Boleta-"+document.getElementById("num_nd").value+'/bo*'+document.getElementById("id_documento_h").value;
            doc_ref="bo*"+(document.getElementById("num_nd").value).trim()+"*"+document.getElementById("fec_documento_h").value;
            }
            if(document.getElementById("opt_notacredito").checked)
            {
            doc_ref="nc*"+(document.getElementById("num_nd").value).trim()+"*"+document.getElementById("fec_documento_h").value;
            }

            let mot_correccion=document.getElementById("motivo").value;
            let cliente_id=document.getElementById("id_cliente_h").value;
            let tnc=document.getElementById("total_nd").value;


            //let mot_codigo= $('input[name="opt_motivo"]:checked').val(); //JQuery
            //let mot_codigo=document.querySelectorAll('input[name="opt_motivo"]:checked')[0].value; //JS1
            let mot_codigo=document.querySelector('input[name="opt_motivo"]:checked').value; //JS2

            if(mot_codigo==1) devolver_todo(); //anula documento

            //corregir textos
            let texto_modificado="";

            if(mot_codigo==2){
                let rz=document.getElementById("cliente_razon_social").value.trim().replaceAll("*"," ");
                let gi=document.getElementById("cliente_giro").value.trim().replaceAll("*"," ");
                let di=document.getElementById("cliente_direccion").value.trim().replaceAll("*"," ");
                let co=document.getElementById("cliente_comuna").value.trim().replaceAll("*"," ");
                let ci=document.getElementById("cliente_ciudad").value.trim().replaceAll("*"," ");
                texto_modificado="["+rz+"*"+gi+"*"+di+"*"+co+"*"+ci+"]";
            }
            //datos de detalle estan en los array nc_item_*

            var url='{{url("notadebito/generarxml")}}';


            var parametros={docum_referencia:doc_ref,motivo_correccion:mot_correccion,motivo_codigo:mot_codigo,id_cliente:cliente_id,total:tnc,texto_modificado:texto_modificado,
                items_num:JSON.stringify(nc_item_num_item),
                items_id:JSON.stringify(nc_item_id),
                items_idrep:JSON.stringify(nc_item_idrep),
                items_descripcion:JSON.stringify(nc_item_descripcion),
                items_codigos:JSON.stringify(nc_item_codigos),
                items_precio:JSON.stringify(nc_item_precio),
                items_cantidad:JSON.stringify(nc_item_dev),
                items_subtotal:JSON.stringify(nc_item_subtotal), //no tiene sentido enviarlo si se puede calcular en el controlador
                };

            $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });

            $.ajax({
            type:'POST',
                beforeSend: function () {
                    $("#mensajes").html("<b>Generando XML...</b>");
                    Vue.swal({
                        title: 'Generando XML',
                        text: 'Espere un momento...',
                        icon: 'info',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        showConfirmButton:false,
                    });
                },
                url:url,
                data:parametros,
                success:function(nc){
                    Vue.swal.close();
                    console.log(nc);
                    let rpta=JSON.parse(nc);

                    if(rpta.estado=='GENERADO')
                    {

                        $("#mensajes").html("Generando XML...<b> Listo!!!</b>");
                        //document.getElementById("btn-enviarsii").disabled=false;
                        console.log('generado');
                        Vue.swal({
                            title: 'ENVIAR DOCUMENTO AL SII',
                            html:'<h3 id="envio-txt">XML Generado. Envíe el documento</h3>'+
                                    '<button  class="btn btn-info form-control-sm" onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>'+
                                    '<button  class="btn btn-warning form-control-sm" disabled onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>'+
                                    '<button  class="btn btn-success form-control-sm" disabled onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                            allowOutsideClick:false,
                            allowEscapeKey:false,
                            showConfirmButton:false,
                            showCancelButton:true
                            //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estadoUP()
                            });

                    }else{
                    console.log(rpta);
                        $("#mensajes").html("Generando XML...<b> Plop !!!</b>");
                        Vue.swal({
                            title: rpta.estado,
                            text: rpta.mensaje,
                            icon: 'error',
                            });
                    }
                },
                error: function(error){
                        let e=String(error.responseText);
                        Vue.swal({
                            title: 'ERRORRR',
                            text: e.substring(0,300),
                            icon: 'error',
                            });
                }
            });

        } //fin generar_xml

        function enviar_sii(){
            let num_nc = $('#numero_nd').val();
            let id_cliente = $('#id_cliente_h').val();
            var url='{{url("notadebito/enviarsii")}}';

            var parametros={
                id_cliente:id_cliente,
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url:url,
                type:'POST',
                data:parametros,
                success:function(data){
                console.log(data);
                let rpta=JSON.parse(data);

                if(rpta.estado=='ENVIADO')
                {
                    $("#envio-txt").html("Documento Enviado al SII...<b> Listo!!!</b>");
                    document.getElementById("btn-verestado").disabled=false;
                    document.getElementById("btn-imprimir").disabled=false;
                    Vue.swal({
                        title: 'ENVIAR DOCUMENTO AL SII',
                        html:'<h3 id="envio-txt">Documento Enviado al SII...<b> Listo!!!</b></h3>'+
                                '<button  class="btn btn-info form-control-sm" disabled onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>'+
                                '<button  class="btn btn-warning form-control-sm" onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>'+
                                '<button  class="btn btn-success form-control-sm" onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        showConfirmButton:false,
                        showCancelButton:true
                        //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estadoUP()
                        });

                }else{
                    console.log(rpta);
                    $("#envio-txt").html("Documento Enviado al SII...<b> Plop !!!</b>");
                    Vue.swal({
                        title: rpta.estado,
                        text: rpta.mensaje,
                        icon: 'error',
                        });
                }
                },
                error:function(error){
                console.log(error);
                }
            })
        }

        function buscar(){
            let num_nc = $('#numero_nd').val();
            var url='{{url("notadebito/buscar")}}';

            var parametros={
                num_nc:num_nc
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url:url,
                type:'POST',
                data:parametros,
                success:function(data){
                    console.log(data);
                    let rpta=JSON.parse(data);
                    console.log(rpta);
                    if(rpta.estado=='OK')
                    {
                        let nc = rpta.nc;
                        let total_formateado = new Intl.NumberFormat("es-CL", {style: "currency", currency: "CLP"}).format(nc.total);
                        $('#id_cliente').val(nc.id_cliente);
                        $('#nc_number').html(nc.num_nota_credito);
                        $('#nc_fecha').html(nc.fecha_emision);
                        $('#nc_total').html(total_formateado);
                        $('#nc_correccion').html(nc.docum_referencia);
                        $('#tbody_nd').empty();
                        rpta.detalle.forEach(det => {
                            let precio_formateado = new Intl.NumberFormat("es-CL", {style: "currency", currency: "CLP"}).format(det.total);
                            $('#tbody_nd').append('<tr><td>'+det.codigo_interno+'</td><td>'+det.descripcion+'</td><td>'+det.cantidad+'</td><td>'+precio_formateado+'</td></tr>');
                        });
                        // total neto
                        let total_neto_formateado = new Intl.NumberFormat("es-CL", {style: "currency", currency: "CLP"}).format(nc.neto);
                        $('#tbody_nd').append('<tr><td></td><td></td><td><strong>Total Neto</strong></td><td><strong>'+total_neto_formateado+'</strong></td></tr>');
                        // IVA
                        // calcular iva
                        let iva_formateado = new Intl.NumberFormat("es-CL", {style: "currency", currency: "CLP"}).format(nc.iva);
                        $('#tbody_nd').append('<tr><td></td><td></td><td><strong>IVA</strong></td><td><strong>'+iva_formateado+'</strong></td></tr>');
                        $('#tbody_nd').append('<tr><td></td><td></td><td><strong>Total</strong></td><td><strong>'+total_formateado+'</strong></td></tr>');
                        $('#tbody_nd').append('<button class="btn btn-danger" onclick="confirmar_documento()">GENERAR NOTA DE DEBITO</button> <br>');
                    }else{
                        console.log(rpta);
                        Vue.swal({
                            title: rpta.estado,
                            text: rpta.mensaje,
                            icon: 'error',
                            });
                    }
                },
                error:function(error){
                    console.log(error);
                }
            });
        }
        function soloNumeros(e)
        {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
        }

        function cargar_documento()
        {
            let td=$('input[name="opt_documento"]:checked').val().trim();
            let mot=$('input[name="opt_motivo"]:checked').val();

            let num=document.getElementById("numero_nd").value.trim();
            document.getElementById("num_nd").value=num;
            let doc="";
            if(td=='factura')
            {
            doc="fa-"+num;
            }
            if(td=='boleta')
            {
            doc="bo-"+num;
            }
            if(td=='notadebito')
            {
            doc="nd-"+num;
            }
            if(td=='notacredito')
            {
            doc="nc-"+num;
            }

            let url='{{url("notadebito")}}'+'/cargar_documento/'+doc+"-"+mot;

            $.ajax({
            type:'GET',
            beforeSend: function () {
                $("#mensajes").html("Buscando...");
            },
            url:url,
            beforeSend: function(){
                $("#mensajes").html("Buscando...");
                Vue.swal({
                    title: 'Buscando...',
                    text: 'Espere un momento...',
                    icon: 'info',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    showConfirmButton:false,
                });
            },
            success:function(resp){
                console.log(resp);
                Vue.swal.close();
                $("#mensajes").html("Buscando...<b>Listo...</b>");
                if(resp.substr(0,1)=='r')
                {
                    Vue.swal({
                            title: 'ATENCION!!!',
                            text: resp.substr(1),
                            //position: 'top-end',
                            icon: 'warning',
                            //toast: true,
                            //showConfirmButton: false,
                            //timer: 3000,
                            });
                    return;
                }

                $("#mostrar_documento").html(resp);
                if(mot==1) devolver_todo();
            },
            error: function(error){
                $('#mensajes').html(error.responseText);
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                }
            });

        }


  function recalcular_valor(id,pv,cant) //kaka
  {
    let cantdev=document.getElementById("item-"+id).value; //cantidad a devolver
    let st=pv*cantdev;
    let ds=Math.round(document.getElementById("nc_item_descuento_"+id).value*1.19/cant,0)*cantdev;
    let tt=st-ds;
    $("#zubtotal-"+id).html(st);
    $("#descuento-"+id).html(ds);
    $("#total-"+id).html(tt);
    document.getElementById("nc_item_subtotal_"+id).value=st;
    calcular_total();
  }

        function devolver_todo()
        {
            let ii=document.getElementsByClassName("nc_item_id");
            let p=document.getElementsByClassName("nc_item_precio");
            let d=document.getElementsByClassName("nc_item_dev");
            let c=document.getElementsByClassName("nc_item_cantidad");

            for(i=0;i<c.length;i++)
            {
            d[i].value=c[i].value;
            $("#subtotal-"+ii[i].value).html(p[i].value*c[i].value);
            document.getElementById("nc_item_subtotal_"+ii[i].value).value=p[i].value*c[i].value;
            }
            calcular_total();
        }

        function calcular_total()
  {
    let ii=document.getElementsByClassName("nc_item_id");
    let r=document.getElementsByClassName("nc_item_idrep");
    let desc=document.getElementsByClassName("nc_item_descripcion");
    let cods = document.getElementsByClassName("nc_item_codigos");
    let p=document.getElementsByClassName("nc_item_precio");
    let d=document.getElementsByClassName("nc_item_dev");
    let s=document.getElementsByClassName("nc_item_subtotal");
    let dd=document.getElementsByClassName("nc_item_descuento");

    nc_item_num_item=[];
    nc_item_id=[];
    nc_item_idrep=[];
    nc_item_descripcion=[];
    nc_item_codigos=[];
    nc_item_precio=[];
    nc_item_dev=[];
    nc_item_subtotal=[];
    nc_item_descuento=[];

    let total_nd=0;
    let cc=0;
    let dev=0;
    for(i=0; i<d.length;i++)
    {
      dev=parseInt(d[i].value);
      if(dev>0){
        nc_item_num_item[cc]=cc+1;
        nc_item_id[cc]=ii[i].value;
        nc_item_idrep[cc]=r[i].value;
        nc_item_descripcion[cc]=desc[i].value;
        nc_item_codigos[cc]=cods[i].value;
        nc_item_precio[cc]=p[i].value;
        nc_item_dev[cc]=dev;
        nc_item_subtotal[cc]=s[i].value;
        nc_item_descuento[cc]=dd[i].value;
        total_nd=total_nd+parseInt(s[i].value)-nc_item_descuento[cc];
        cc++;
      }

    }
    //$("#totalzito").html(total_nd);
  }

  window.onload = function() {
    // Vue.swal({
    //     title: 'Ingrese Contraseña',
    //     input: 'password',
    //     confirmButtonText: 'Verificar',
    //     showConfirmButton: true,
    //     showCancelButton: true,
    // }).then((result) => {
    //     if(result.isConfirmed){
    //       let clave=result.value;
    //         let url='{{url("/clave_descuento")}}';
    //         let parametros={
    //             clave:clave,
    //         };
    //         $.ajaxSetup({
    //             headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             }
    //         });

    //         $.ajax({
    //         type:'POST',
    //         url:url,
    //         data:parametros,
    //         success:function(resp){
    //           console.log(resp);
    //             Vue.swal.close();

    //         },
    //         error: function(error){
    //             Vue.swal.close();
    //                 Vue.swal({
    //                     title: 'ERROR',
    //                     text: error.responseText,
    //                     icon: 'error',
    //                 });
    //         }
    //         });

    //     }else{
    //       console.log('cancelando');
    //       // Simulate a mouse click:
    //       window.location.href = "/home";
    //     }


    // });
                  $('input[type=radio]').click(function(){
                  if (this.name == "opt_documento")
                  {
                      document.getElementById("num_documento").value="";
                      $("#mostrar_documento").html("");
                      if(this.value=='boleta')
                      {
                          $("label[for='opt_1']").text('Anular Boleta');
                      }else{
                          $("label[for='opt_1']").text('Anular Factura');
                      }
                  }

                  if(this.name=="opt_motivo")
                  {
                          let nd=document.getElementById("numero_nd").value;
                          if(nd>0) cargar_documento();
                          if(this.value==2) //Corregir texto
                          {
                              nc_item_num_item=[];
                              nc_item_id=[];
                              nc_item_idrep=[];
                              nc_item_descripcion=[];
                              nc_item_codigos=[];
                              nc_item_precio=[];
                              nc_item_dev=[];
                              nc_item_subtotal=[];
                              document.getElementById("total_nd").value=0;
                          }
                      }
                  });
};
    </script>
@endsection
{{-- @section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => "Nota Débito", 'titulo_modulo' => "Ventas", 'ruta_modulo' => "/notadebito"])

@endsection --}}
@section('contenido_ingresa_datos')
 <!-- fila 1 -->
 <div class="row ml-1 w-100">
    <div class="col-sm-2 col-md-2 col-lg-2 border text-center" >
        <strong>Tipo de Documento</strong><br>&nbsp;
        <label>
            <input type="radio" name="opt_documento" value="boleta" id="opt_boleta"  disabled>Boleta
        </label>
        <label>
            <input type="radio" name="opt_documento" value="factura" id="opt_factura" disabled>Factura
        </label>
        <label>
            <input type="radio" name="opt_documento" value="notacredito" id="opt_notacredito" checked="true">Nota Crédito
        </label>
    </div>
    <div class="col-sm-6 col-md-6 col-lg-6 border">
            <div class="row" style="display:inline-block;width:100%;text-align: center;">
                <strong>MOTIVO</strong>
            </div>
            <div class="row">
                <div class="col-4">
                    <input type="radio" name="opt_motivo" value="1" id="opt_1" checked="true">
                    <label for="opt_1">Devolución Total <small><i>(ANULAR)</i></small></label>
                </div>
                <div class="col-4">
                    <input type="radio" name="opt_motivo" value="3" id="opt_3">
                    <label for="opt_3">Devolución Parcial</label>
                </div>
                <div class="col-4">
                    <input type="radio" name="opt_motivo" value="2" id="opt_2">
                    <label for="opt_2">Corregir Textos</label>
                </div>

            </div>
    </div>
    <div class="col-sm-4 border d-flex">
        <input class="form-control" type="text" id="numero_nd" placeholder="Ingrese Número" maxlength="15" size="13" onKeyPress="return soloNumeros(event)">
        <input type="hidden" id="num_nd" value="0">
        <input type="hidden" id="total_nd" value="0">
        <button class="btn btn-primary btn-block" onclick="cargar_documento()">Buscar</button>
    </div>

  </div>  <!-- FIN fila 1 -->
  <div class="row w-100">
      <!-- COLUMNA 2 -->
  <div class="col-sm-9 col-md-9 col-lg-9">
    <div id="mostrar_documento">
      cargar factura o boleta según elección
    </div>

  </div> <!-- FIN COLUMNA 2 -->
  </div>
    {{-- <div class="row w-100">
        <div class="col-md-3">
            <span>Ingrese Núm de Nota de Crédito: </span>
            <input type="number" id="numero_nd" class="form-control form-control-sm" name="numero_nd">
            <br>
            <button class="btn btn-success btn-sm mt-3" onclick="buscar()">Buscar</button> <br>

            <table class="table my-2">
                <tr>
                    <td>Núm Nota de Crédito:</td>
                    <td id="nc_number"></td>
                </tr>
                <tr>
                    <td>Fecha de Emisión:</td>
                    <td id="nc_fecha"></td>
                </tr>
                <tr>
                    <td>Total:</td>
                    <td id="nc_total"></td>
                </tr>
                <tr>
                    <td>Corrección:</td>
                    <td id="nc_correccion"></td>
                </tr>
            </table>
            <span>ANULACIÓN DE NOTA DE DÉBITO</span><br>
            <button class="btn btn-danger" onclick="confirmar_documento()">GENERAR NOTA DE DEBITO</button> <br>
        </div>
        <div class="col-md-9">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col" style="width: 16%">Código interno</th>
                    <th scope="col" style="width: 50%">Descripción</th>
                    <th scope="col" style="width: 16%">Cantidad</th>
                    <th scope="col" style="width: 16%">Total</th>
                  </tr>
                </thead>
                <tbody id="tbody_nd">

                </tbody>
            </table>
        </div>
    </div> --}}


    <input type="hidden" name="id_cliente" id="id_cliente">
    @endsection
