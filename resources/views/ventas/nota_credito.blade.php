@extends('plantillas.app')
@section('titulo','NOTA DE CRÉDITO')
@section('javascript')

<script type="text/javascript">
 var nc_item_num_item=[];
 var nc_item_id=[];
 var nc_item_idrep=[];
 var nc_item_descripcion=[];
 var nc_item_precio=[];
 var nc_item_dev=[];
 var nc_item_subtotal=[];
 var TrackID=0;

  function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
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
    let p=document.getElementsByClassName("nc_item_precio");
    let d=document.getElementsByClassName("nc_item_dev");
    let s=document.getElementsByClassName("nc_item_subtotal");
    let dd=document.getElementsByClassName("nc_item_descuento");

    nc_item_num_item=[];
    nc_item_id=[];
    nc_item_idrep=[];
    nc_item_descripcion=[];
    nc_item_precio=[];
    nc_item_dev=[];
    nc_item_subtotal=[];
    nc_item_descuento=[];

    let total_nc=0;
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
        nc_item_precio[cc]=p[i].value;
        nc_item_dev[cc]=dev;
        nc_item_subtotal[cc]=s[i].value;
        nc_item_descuento[cc]=dd[i].value;
        total_nc=total_nc+parseInt(s[i].value)-nc_item_descuento[cc];
        cc++;
      }

    }
    //$("#totalzito").html(total_nc);
  }

  function cargar_documento()
  {
    let td=$('input[name="opt_documento"]:checked').val().trim();
    let mot=$('input[name="opt_motivo"]:checked').val();

    let num=document.getElementById("num_documento").value.trim();
    document.getElementById("num_documento_h").value=num;
    let doc="";
    if(td=='factura')
    {
      doc="fa-"+num;
    }
    if(td=='boleta')
    {
      doc="bo-"+num;
    }


    let url='{{url("notacredito")}}'+'/cargar_documento/'+doc+"-"+mot;

    $.ajax({
      type:'GET',
      beforeSend: function () {
          $("#mensajes").html("Buscando...");
      },
      url:url,
      success:function(resp){
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
      }
    });

  }

  function agregar_cliente_boleta(){
    let el_rut = document.getElementById("rut_xpress").value.toString().trim().toUpperCase();
    rut=el_rut.replace("-","");
    rut=rut.replaceAll(".","");
    let nombre = $('#nombre_xpress').val();
    let apellidos = $('#apellido_xpress').val();
    let direccion = $('#direccion_xpress').val();
    let email = $('#email_xpress').val();
    let telefono = $('#telefono_xpress').val();
    let reembolso_referencia = $('#reembolso_referencia').val();

    console.log(rut);

    if(rut == '' || (rut.length > 10 && rut.length <5)){
      Vue.swal({
            text: 'Ingrese rut valido del cliente',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#rut_xpress').focus();
      return false;
                
    }

    if(nombre == ''){
      Vue.swal({
            text: 'Ingrese nombre del cliente',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#nombre_xpress').focus();
      return false;
                
    }

    if(apellidos == ''){
      Vue.swal({
            text: 'Ingrese apellidos del cliente',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#apellido_xpress').focus();
      return false;  
    }
    
    if(direccion == ''){
      Vue.swal({
            text: 'Debe ingresar una dirección',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#direccion_xpress').focus();
      return false;     
    }
    
    if(email == ''){
      Vue.swal({
            text: 'Debe ingresar una email autorizado',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#email_xpress').focus();
      return false;
    }

    if(telefono == ''){
      Vue.swal({
            text: 'Debe ingresar un telefono de contacto',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
      });
      $('#telefono_xpress').focus();
      return false;
    }

    let parametros = {rut:rut,nombre:nombre,apellidos:apellidos,direccion:direccion,email:email, telefono: telefono};

    let url = '/clientes/guardar_cliente_xpress_nc';

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
      type:'post',
      dataType:'json',
      data:parametros,
      url:url,
      success: function(resp){
        console.log(resp);
        
        Vue.swal({
          icon:'success',
          title:'Exito',
          text:'Cliente actualizado'
        });
        $('#exampleModal').modal('hide');
        $('#rut_razon_cliente').empty();
        $('#rut_razon_cliente').append('Cliente: <b>'+resp.rut+' '+resp.nombres+' '+resp.apellidos+'</b>');

        $('#giro_cliente').empty();
        $('#giro_cliente').append('Giro: Sin giro');

        $('#direccion_cliente').empty();
        $('#direccion_cliente').append('Dirección: '+resp.direccion);

        $('#id_cliente_h').val(resp.id);
      },
      error: function(error){
        console.log(error.responseText);
      }
    })
    
  }

  function generar_xml()
  {

    let doc_ref="fa*"+(document.getElementById("num_documento_h").value).trim()+"*"+document.getElementById("fec_documento_h").value;

    if(document.getElementById("opt_boleta").checked)
    {
      //doc_ref="Boleta-"+document.getElementById("num_documento_h").value+'/bo*'+document.getElementById("id_documento_h").value;
      doc_ref="bo*"+(document.getElementById("num_documento_h").value).trim()+"*"+document.getElementById("fec_documento_h").value;
    }
    let mot_correccion=document.getElementById("motivo").value;
    let cliente_id=document.getElementById("id_cliente_h").value;
    let tnc=document.getElementById("total_nc").value;

   
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

    var url='{{url("notacredito/generarxml")}}';

    
    var parametros={docum_referencia:doc_ref,motivo_correccion:mot_correccion,motivo_codigo:mot_codigo,id_cliente:cliente_id,total:tnc,texto_modificado:texto_modificado,
          items_num:JSON.stringify(nc_item_num_item),
          items_id:JSON.stringify(nc_item_id),
          items_idrep:JSON.stringify(nc_item_idrep),
          items_descripcion:JSON.stringify(nc_item_descripcion),
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
        },
        url:url,
        data:parametros,
        success:function(nc){
           
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

function enviar_sii()
{
    let cliente_id=document.getElementById("id_cliente_h").value;
    let url='{{url("notacredito/enviarsii")}}';
  
    var parametros={id_cliente:cliente_id,
        items_num:JSON.stringify(nc_item_num_item),
        items_id:JSON.stringify(nc_item_id),
        items_idrep:JSON.stringify(nc_item_idrep),
        items_descripcion:JSON.stringify(nc_item_descripcion),
        items_precio:JSON.stringify(nc_item_precio),
        items_cantidad:JSON.stringify(nc_item_dev),
        items_subtotal:JSON.stringify(nc_item_subtotal),
    };
  
    $.ajaxSetup({
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
    });

    $.ajax({
        type:'POST',
        beforeSend: function () {
            $("#mensajes").html("<b>Enviando al SII...</b>");
            $("#envio-txt").html("Enviando al SII...");
        },
        url:url,
        data:parametros,
        success:function(rs){
            let rpta=JSON.parse(rs);
            if(rpta.estado=='OK')
            {
                $("#mensajes").html("Enviando al SII...<b>Recibido !!!</b>");
                $("#envio-txt").html(rpta.mensaje+" TrackID: "+rpta.trackid);
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=false;
                document.getElementById("btn-imprimir").disabled=true;
                TrackID=rpta.trackid;
            }else{
                $("#mensajes").html("Enviando al SII...<b>Ups !!!</b>");
                $("#envio-txt").html(rpta.estado+". "+rpta.mensaje);
                TrackID=0;
            }
        },
        error: function(error){
            let e=String(error.responseText);
            $('#envio-txt').html(e.substring(0,300));
        }
    }); //Fin petición POST
}

function ver_estadoUP()
{
    //let TrackID=document.getElementById("trackID").value;
    var url='{{url("sii/verestado")}}'+"/61&"+TrackID; //Controlador servicios_sii\sii_controlador
    $.ajax({
        type:'GET',
        beforeSend: function () {
        $("#mensajes").html("<b>Revisando Estado...</b>");
        $("#envio-txt").html("Revisando estado...");
        },
        url:url,
        success:function(rs){
            rs=JSON.parse(rs);
            if(rs.estado=='ACEPTADO'){
                $("#envio-txt").html("Envío ACEPTADO... puede imprimir");
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=true;
                document.getElementById("btn-imprimir").disabled=false;
                let doc=""
            }else{
                $("#envio-txt").html(rs.estado+": "+rs.mensaje+". Revise e-mail.");
            }


        },
        error: function(error){
            $('#mensajes').html(error.responseText);
        }
    });

}

function imprimir()
  {
    var url='{{url("imprimir/0")}}';
      $.ajax({
          type:'GET',
          beforeSend: function () {
              $("#mensajes").html("<b>Imprimiendo...</b>");
          },
          url:url,
          success:function(resp){
            let r=JSON.parse(resp);
            if(r.estado=='OK'){
                $("#mensajes").html("Imprimiendo... <b>Listo...</b>");
                $('#envio-txt').html("PDF Generado...");
                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w=window.open(r.mensaje,'_blank',config);
                w.focus();
                location.href="{{url('notacredito')}}";
            }else{
                $('#envio-txt').html(r.estado+": "+r.mensaje);
            }

          },
          error: function(error){
            let e=String(error.responseText);
            $('#envio-txt').html(e.substring(0,300));
            /*
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
                });
                */
            }

      });



    //location.href="{{url('ventas')}}"; //
  }

  function limpiar_todo(){

  }

  function guardar_nota_credito() //backup por si acaso
  {
    var url='{{url("notacredito")}}/guardar_nota';

    //datos de cabecera
    /*
      id_cliente
                                     docu-num/doc*id
      docum_referencia (Boleta-125/bo*231)
      motivo_correccion
      neto
      exento
      iva
      total
    */
    //let doc_ref="Factura-"+document.getElementById("num_documento_h").value+"/fa*"+document.getElementById("id_documento_h").value;

    let doc_ref="fa*"+(document.getElementById("num_documento_h").value).trim()+"*"+document.getElementById("fec_documento_h").value;

    if(document.getElementById("opt_boleta").checked)
    {
      //doc_ref="Boleta-"+document.getElementById("num_documento_h").value+'/bo*'+document.getElementById("id_documento_h").value;
      doc_ref="bo*"+(document.getElementById("num_documento_h").value).trim()+"*"+document.getElementById("fec_documento_h").value;
    }
    let mot_correccion=document.getElementById("motivo").value;
    let cliente_id=document.getElementById("id_cliente_h").value;
    let tnc=document.getElementById("total_nc").value;

    //let mot_codigo= $('input[name="opt_motivo"]:checked').val(); //JQuery
    //let mot_codigo=document.querySelectorAll('input[name="opt_motivo"]:checked')[0].value; //JS1
    let mot_codigo=document.querySelector('input[name="opt_motivo"]:checked').value; //JS2

    if(mot_codigo==1) devolver_todo(); //anula documento

    //modificación de textos
    var texto_modificado="";
    if(mot_codigo==2){
        texto_modificado="["+document.getElementById("cliente_razon_social").value.trim()+"*"+
                                            document.getElementById("cliente_giro").value.trim()+"*"+
                                            document.getElementById("cliente_direccion").value.trim()+"*"+
                                            document.getElementById("cliente_comuna").value.trim()+"*"+
                                            document.getElementById("cliente_ciudad").value.trim()+"]";
    }

    //datos de detalle estan en los array nc_item_*

    var parametros={docum_referencia:doc_ref,motivo_correccion:mot_correccion,motivo_codigo:mot_codigo,id_cliente:cliente_id,total:tnc,texto_modificado:texto_modificado,
          items_id:JSON.stringify(nc_item_id),
          items_idrep:JSON.stringify(nc_item_idrep),
          items_precio:JSON.stringify(nc_item_precio),
          items_cantidad:JSON.stringify(nc_item_dev),
          items_subtotal:JSON.stringify(nc_item_subtotal),
          };

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      $("#mensajes").html("<b>Guardando Nota Crédito...</b>");
    },
    url:url,
    data:parametros,
    success:function(nc){
      $("#mensajes").html("Guardando Nota Crédito... <b>Guardé Nota Crédito...</b>"+nc);
      if(nc.substr(0,1)=="y") //No hay correlativo autorizado
      {
        $('#mensajes').html("<strong>No hay correlativo autorizado(SII) disponible</strong>");
        Vue.swal({
            title: 'ERROR',
            text: 'No hay correlativo autorizado(SII) disponible',
            icon: 'error',
            });
        return;
      }

      if(nc.substr(0,1)=='z')
      {
        $('#mensajes').html(nc.substr(1));
        Vue.swal({
                    title: 'ERROR',
                    text: doc.substr(1),
                    //position: 'top-end',
                    icon: 'error',
                    //toast: true,
                    //showConfirmButton: false,
                    //timer: 3000,
                    });
        return;
      }

      if(nc.substr(0,1)=='s') //Error al preparar  y/o enviar documento al SII
      {
        $("#mensajes").html("Enviado a SII... <b>Revisar email para detalles</b>");
        Vue.swal({
                    title: 'ERROR',
                    text: nc.substr(1),
                    //position: 'top-end',
                    icon: 'error',
                    //toast: true,
                    //showConfirmButton: false,
                    //timer: 3000,
                    });
        return;
      }

     imprimir(nc);

    },
    error: function(error){
      $('#mensajes').html(error.responseText);
    }
    });

  } // fin guardar nc


  window.onload = function() {
    Vue.swal({
        title: 'Ingrese Contraseña',
        input: 'password',
        confirmButtonText: 'Verificar',
        showConfirmButton: true,
        showCancelButton: true,
    }).then((result) => {
        if(result.isConfirmed){
          let clave=result.value;
            let url='{{url("/clave_descuento")}}';
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
              console.log(resp);
                Vue.swal.close();
                if(resp=="OK"){
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
                          let nd=document.getElementById("num_documento").value;
                          if(nd>0) cargar_documento();
                          if(this.value==2) //Corregir texto
                          {
                              nc_item_num_item=[];
                              nc_item_id=[];
                              nc_item_idrep=[];
                              nc_item_descripcion=[];
                              nc_item_precio=[];
                              nc_item_dev=[];
                              nc_item_subtotal=[];
                              document.getElementById("total_nc").value=0;
                          }
                      }
                  });      
                }else{
                  Vue.swal({
                        title: 'ERROR',
                        text: 'Contraseña incorrecta',
                        icon: 'error',
                    });
                    setTimeout(() => {
                      window.location.href = "/home";
                    }, 2000);
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
          
        }else{
          console.log('cancelando');
          // Simulate a mouse click:
          window.location.href = "/home";
        }


    });
    
};



</script>
@endsection
@section('style')
<style>
.container-fluid {
    padding-right: 1px;
    padding-left: 1px;
    margin-right: 1px;
    margin-left: 1px;
}

.col-sm-3, .col-sm-9{
  padding-right: 5px;
  padding-left: 5px;
}

.modal-body-alto {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 40px);
}


</style>
@endsection
@section('contenido_titulo_pagina')
<center><h4 class="titulazo">NOTA DE CRÉDITO</h4></center>
@endsection

@section('mensajes')
  @include('fragm.mensajes')
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
  <!-- fila 1 -->
  <div class="row ml-1 w-100">
    <div class="col-sm-2 col-md-2 col-lg-2" style="border: 1px solid black; padding: 10px; border-radius: 10px;" >
        <strong>Tipo de Documento</strong><br>&nbsp;
        <label>
            <input type="radio" name="opt_documento" value="boleta" id="opt_boleta" checked="true">Boleta
        </label>
        <label>
            <input type="radio" name="opt_documento" value="factura" id="opt_factura">Factura
        </label>
    </div>
    <div class="col-sm-6 col-md-6 col-lg-6" style="background-color: #66ffff;height:60%; border: 1px solid black; padding: 10px; border-radius: 10px;">
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
    <div class="col-sm-1 col-md-1 col-lg-1" style="border: 1px solid black;padding: 10px;border-radius: 10px;margin-left: 2px;">
        <input type="text" id="num_documento" placeholder="Ingrese Número" maxlength="15" size="13" style="margin-bottom:4px;" onKeyPress="return soloNumeros(event)">
        <input type="hidden" id="num_documento_h" value="0">
        <input type="hidden" id="total_nc" value="0">
    </div>
    <div class="col-sm-1 col-md-1 col-lg-1">
        <button class="btn btn-primary btn-block" onclick="cargar_documento()">Buscar</button>
    </div>
  </div>  <!-- FIN fila 1 -->

  <!-- COLUMNA 2 -->
  <div class="col-sm-9 col-md-9 col-lg-9" style="background-color: rgb(255, 255, 128);
  margin-top: 10px;
  border: 1px solid black;
  border-radius: 10px;
  padding: 10px;">
    <div id="mostrar_documento">
      cargar factura o boleta según elección
    </div>

  </div> <!-- FIN COLUMNA 2 -->
</div> <!-- fin container-fluid principal -->

@endsection
