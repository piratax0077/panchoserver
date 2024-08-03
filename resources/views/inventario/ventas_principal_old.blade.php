@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')

<script type="text/javascript">
  //Variables globales
  var cliente=[];
  var modal_relacionado=false;
  var mostrar_panel_buscar=false;
  var modifikar_precio_panel=false;
  var precio_a_modifikar=0;
  var id_precio_a_modifikar=0;
  var TrackID=0;
  var referencia1=true;
  var referencia2=true;
  var referencia3=true;
  var ref1=[];
  var ref2=[];
  var ref3=[];
  var documento_procesado="Ninguno";
  var div_cliente_xpress_mostrar=false;

  //Para resaltar la fila seleccionada
  var ultimaFila=null;
  var colorOriginal;
  var editando_anio_similares=false;
  var antiguo_valor_anio_similar="";


  function formatear_error(error){
    let max=300;
    let rpta=error.substring(0,max);
    return rpta;
  }

  function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
  }

  function RUN_correcto(run)
  {
    console.log(run);
    var multiplicador=[3,2,7,6,5,4,3,2];
    var x=[0,0,0,0,0,0,0];
    if(run.length==8)
    {
      x[0]=0;
      x[1]=parseInt(run.substring(0,1));
      x[2]=parseInt(run.substring(1,2));
      x[3]=parseInt(run.substring(2,3));
      x[4]=parseInt(run.substring(3,4));
      x[5]=parseInt(run.substring(4,5));
      x[6]=parseInt(run.substring(5,6));
      x[7]=parseInt(run.substring(6,7));
      x[8]=run.substring(7,8); // porque puede ser la letra K
    }
    if(run.length==9)
    {
      x[0]=parseInt(run.substring(0,1));
      x[1]=parseInt(run.substring(1,2));
      x[2]=parseInt(run.substring(2,3));
      x[3]=parseInt(run.substring(3,4));
      x[4]=parseInt(run.substring(4,5));
      x[5]=parseInt(run.substring(5,6));
      x[6]=parseInt(run.substring(6,7));
      x[7]=parseInt(run.substring(7,8));
      x[8]=run.substring(8,9);
    }

    var suma=0;
    for(var i=0;i<8;i++)
    {
      suma=suma+x[i]*multiplicador[i];
    }
    var residuo=suma%11;
    var digito=11-residuo;
    if(x[8]=="K")
    {
      if(digito==10){
        return true;
      }else{
        return false;
      }
    }else{
        if(x[8]==digito){
        return true;
      }else{
        if(x[8]==0 && digito==11){
            return true;
        }else{
            return false;
        }
      }
    }

  }

  function milis()
  {
    var d=new Date();
    var t=d.getTime();
    return t;

  }

  function enter_buscar(e){
    let keycode = e.keyCode;
    if(keycode=='13'){
        buscar_clientes();
    }
  }

  function formatear_rut(rut)
  {
    var rf="";
    var r=rut.toString();
    if(r.length==8) rf=r.substr(0,1)+"."+r.substr(1,3)+"."+r.substr(4,3)+"-"+r.substr(7,1);
    if(r.length==9) rf=r.substr(0,2)+"."+r.substr(2,3)+"."+r.substr(5,3)+"-"+r.substr(8,1);
    if(r.length==1) rf=0;
    $("#rut"+rut).html(rf);
  }

  function limpiar_todo(){
    borrar_carrito('actual');
    cliente=[];
    modal_relacionado=false;
    mostrar_panel_buscar=false;
    modifikar_precio_panel=false;
    precio_a_modifikar=0;
    id_precio_a_modifikar=0;
    TrackID=0;
    dame_formas_pago();
    let bb=document.getElementById("radio_boleta");
    bb.checked=true;
    document.getElementById("id_cliente").value=0;
    $("#cliente_rut").html("RUT: ");
    $("#cliente_nombres").html("Cliente:");
    $("#total_forma_pago").html("0");
    contado_credito(1); //para que reactive las formas de pago
    document.getElementById("credito").checked=false;
    document.getElementById("credito").disabled=true;
    document.getElementById("contado").checked=true;
    document.getElementById("nombre_cotizacion").value="";
    document.getElementById("nombre_cotizacion").disabled=true;
    documento_procesado="Ninguno";
    referencias_cancelar();
    limpiar_sesion();
  }

  function limpiar_sesion(){
    let url='{{url("/ventas/limpiarsesion")}}';
    $.ajax({
        type:'GET',
        url:url,
        success:function(resp){

        },
        error: function(error){
            console.log(error.responseText);
        }

    });
  }

  function nueva_venta(){
    borrar_carrito('actual');
    location.href="{{url('ventas')}}";
  }

  function buscar()
  {
    if(!modifikar_precio_panel)
    {
        if(mostrar_panel_buscar==false)
        {
            $("#panel-buscar").fadeOut(300,function(){
                document.getElementById("grilla").className = "col-sm-12";
            });
            $("#txtBotonPanel").html("Mostrar Marcas y Modelos");
        }else{
            document.getElementById("grilla").className = "col-sm-9";
            $("#panel-buscar").fadeIn("slow");
            $("#txtBotonPanel").html("<p>Ocultar Marcas y Modelos</p>");
            clic_en(5);
        }
        mostrar_panel_buscar=!mostrar_panel_buscar;
    }

  }


  function clic_en(valor)
  {
    $("#zona_familia").html("");
    $("#zona_grilla").html("");
    $("#zona_fotos").html("");
    $("#zona_similares").html("");
    $("#zona_oem").html("");
    $("#zona_fab").html("");
    $("#mensajes-modal").html("");
    $("#mod_titulo_header").html("BUSCAR REPUESTO");
    switch (valor){
      /*
      case 1: //buscar por descripcion
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codint").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
        break;
      case 2: //buscar por codigo proveedor
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codint").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
        break;
      case 3: //buscar por oem
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codint").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
        break;
      case 4: //buscar por medidas
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_codint").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
        break;
        */
      case 5: //buscar por marcas y modelos
        /*
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codint").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
        */
        buscar_por_marca_modelo();
        break;
        /*
      case 6: //buscar por codigo interno pancho repuestos
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codigo_fabricante").value="";
      case 7: //Buscar por código de fabricante
        document.getElementById("buscar_por_descripcion").value="";
        document.getElementById("buscar_por_oem").value="";
        document.getElementById("buscar_por_codigo_proveedor").value="";
        document.getElementById("buscar_por_medidas").value="";
        document.getElementById("buscar_por_codint").value="";
        */
    }
  }

    function enter_press(e,q)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
        if(q=='d') buscar_por_descripcion();
        if(q=='p') buscar_por_proveedor();
        if(q=='o') buscar_por_oem();
        if(q=='m') buscar_por_medidas();
        if(q=='c') buscar_por_codigo_interno();
        if(q=='f') buscar_por_codigo_fabricante();
        }
    }

    function enter_anio_similar(e,id_simi){
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            guardar_anio_similares(id_simi);
        }
    }


    function buscar_por_descripcion()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      var checkDescripcion=document.getElementById("chkDescripcion");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      if(checkDescripcion.checked) valor=valor+4;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var _descripcion=document.getElementById("buscar_por_descripcion");
      var _donde=_descripcion.placeholder;
      var descripcion=valor+_descripcion.value.trim();
      if(descripcion.indexOf("/")){
        descripcion=descripcion.replace(/\//g,"_&_");
      }

      $("#mod_titulo_header").html("<p class='d-flex justify-content-center'>BUSCANDO <strong>"+_descripcion.value.toUpperCase()+" EN "+_donde.toUpperCase()+"</strong></p>");
      var url_buscar='{{url("ventas/buscardescripcion")}}'+'/'+descripcion;


      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR '+error.status,
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_proveedor()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var _codprov=document.getElementById("buscar_por_codigo_proveedor");
      var _donde=_codprov.placeholder;
      var codprov=valor+_codprov.value.trim();
      $("#mod_titulo_header").html("<p class='d-flex justify-content-center'>BUSCANDO <strong>"+_codprov.value.toUpperCase()+" EN "+_donde.toUpperCase()+"</strong></p>");
      var url_buscar='{{url("ventas/buscarcodproveedor")}}'+'/'+codprov;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_oem()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var _oem=document.getElementById("buscar_por_oem");
      var oem=valor+_oem.value.trim();
      var _donde=_oem.placeholder;
      $("#mod_titulo_header").html("<p class='d-flex justify-content-center'>BUSCANDO <strong>"+_oem.value.toUpperCase()+" EN "+_donde.toUpperCase()+"</strong></p>");
      var url_buscar='{{url("ventas/buscaroem")}}'+'/'+oem;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_codigo_fabricante()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var _fab=document.getElementById("buscar_por_codigo_fabricante");
      var fab=valor+_fab.value.trim();
      var _donde=_fab.placeholder;
      $("#mod_titulo_header").html("<p class='d-flex justify-content-center'>BUSCANDO <strong>"+_fab.value.toUpperCase()+" EN "+_donde.toUpperCase()+"</strong></p>");
      var url_buscar='{{url("ventas/buscarcodfabricante")}}'+'/'+fab;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_medidas()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var _medidas=document.getElementById("buscar_por_medidas");
      var _donde=_medidas.placeholder;
      var medidas=valor+_medidas.value.trim();
      $("#mod_titulo_header").html("<p class='d-flex justify-content-center'>BUSCANDO <strong>"+_medidas.value.toUpperCase()+" EN "+_donde.toUpperCase()+"</strong></p>");
      var url_buscar='{{url("ventas/buscarmedidas")}}'+'/'+medidas;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_codigo_interno()
    {
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var codint=valor+document.getElementById("buscar_por_codint").value.trim();
      var url_buscar='{{url("ventas/buscarcodint")}}'+'/'+codint;


      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function buscar_por_marca_modelo()
    {
      cargar_Marcas();
      $("#buscar-marcamodelo-modal").modal("show");
    }

    function buscar_por_modelo(idmodelo)
    {
      $("#zona_familia").html("<small>Buscando Familias</small>");
      $("#buscar-marcamodelo-modal").modal("hide");
      var valor=0;
      var checkMedidas=document.getElementById("chkMedidas");
      var checkStock=document.getElementById("chkStock");
      if(checkMedidas.checked) valor=valor+1;
      if(checkStock.checked) valor=valor+2;
      //Si ninguno esta marcado, valor=0
      //Si solo medidas esta marcado, valor=1
      //Si solo stock esta marcado, valor=2
      //Si medidas y stock estan marcados, valor=3
      var modelo=valor+idmodelo.toString();
      var url_buscar='{{url("ventas/buscarmodelo")}}'+'/'+modelo;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html("");
          $("#zona_similares_r").html("");
          $("#zona_oem_r").html("");
        }else{
          $("#zona_fotos").html("");
          $("#zona_similares").html("");
          $("#zona_oem").html("");
          $("#zona_fab").html("");
        }
        $("#zona_grilla").html("<h3>BUSCANDO...</h3>");
        $("#mensajes-modal").html("Buscando...");
      },
      url:url_buscar,
      success:function(resp){
        $("#mensajes-modal").html("Listo...");
        $("#zona_grilla").html(resp);
        damefamilias(modelo);
      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });

    }

    function damefamilias(id_modelo)
    {

        $("#buscar-marcamodelo-modal").modal("hide");
        var url='{{url("ventas/damefamilias")}}'+'/'+id_modelo;
        $.ajax({
        type:'GET',
        beforeSend: function () {
            $("#mensajes-modal").html("Buscando...");
            },
        url:url,
        success:function(familias){
            $("#mensajes-modal").html("Listo...");
            $("#zona_familia").html(familias);
        },
            error: function(error){
            $('#zona_grilla').html(formatear_error(error.responseText));
            }

        }); //Fin petición

    }

//Devuelve repuestos según modelo y familia
function damerepuestos(id_familia,dato)
  {
      if(id_familia==0)
      {
        id_modelo=dato.substr(1);
        buscar_por_modelo(id_modelo);
      }else{

        var url='{{url("ventas")}}'+'/'+id_familia+'/'+dato+'/damerepuestos';
        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes-modal").html("Buscando Repuestos de la Familia...");
            },
        url:url,
        success:function(repuestos){
          $("#mensajes-modal").html("Listo...");
          $("#zona_grilla").html(repuestos);
        },
          error: function(error){
              $('#zona_grilla').html(formatear_error(error.responseText));
          }

        }); //Fin petición
      }
  }




    function cargar_Marcas()
    {
      var url_buscar='{{url("ventas")}}'+'/damemarcas';

      $.ajax({
       type:'GET',
       beforeSend: function () {

      },
      url:url_buscar,
      success:function(resp){
        $('#divmarcas').html(resp);
        /*
        $('#marcas option').remove();
        var marcas=JSON.parse(respJSON);
        $('#marcas').append('<option value="0">Elija una Marca</option>');
          marcas.forEach(function(marca){
          $('#marcas').append('<option value="'+marca.idmarcavehiculo+'">'+marca.marcanombre.toUpperCase()+'</option>');
          });

          document.getElementById("marcas").selectedIndex=0;
          */
      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

    function cargar_Modelos(idmarca)
    {
      if(idmarca==0)
      {
        Vue.swal({
            text: 'Elija una marca',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }
      var url_buscar='{{url("ventas")}}'+'/damemodelos/'+idmarca;

      $.ajax({
       type:'GET',
       beforeSend: function () {

      },
      url:url_buscar,
      success:function(resp){
        $('#divmodelos').html(resp);
        /*
        var filas="";
        $('#modelos option').remove();
        var modelos=JSON.parse(respJSON);
        $('#modelos').append('<option value="">Elija un Modelo</option>');
          modelos.forEach(function(modelo){
          $('#modelos').append('<option value="'+modelo.id+'">'+modelo.modelonombre.toUpperCase()+'</option>');
          });
*/

      },
      error: function(error){
        $("#busca-repuesto-modal").modal("hide");
        Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
      }

      });
    }

  function buscar_repuesto()
  {
    modal_relacionado=false;
    $('#buscar-repuesto-modal').on('shown.bs.modal', function () {
        $("#buscar_por_descripcion").focus();
      });
    $("#buscar-repuesto-modal").modal("show");
  }

function agregar_repuesto_xpress(){
    let codi=document.getElementById("xpress-codigo").value.trim();
    let desc=document.getElementById("xpress-descripcion").value.trim();
    let cant=document.getElementById("xpress-cantidad").value.trim();
    let prec=document.getElementById("xpress-precio").value.trim();

    if(desc.length==0)
    {
        Vue.swal({
            text: 'Ingrese Descripción',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    if(cant<=0 || isNaN(cant) || cant.length==0) //is Not A Number (isNaN) comprueba si no es un número
    {
        Vue.swal({
            text: 'Cantidad vacia, cero o no es un número válido...',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    if(prec<=0 || isNaN(prec) || prec.length==0)
    {
        Vue.swal({
            text: 'Precio Total, cero o no es un número válido...',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    if(cant<=0 || isNaN(cant) || cant.length==0) //is Not A Number (isNaN) comprueba si no es un número
    {
        Vue.swal({
            text: 'Cantidad vacia, cero o no es un número válido...',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

      var url="{{url('repuesto/guardar_xpress')}}";
      var parametros={codigo:codi,descripcion:desc,precio:prec};

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });

        $.ajax({
        type:'POST',
        url:url,
        data:parametros,
        success:function(idxpress){
            if(idxpress>0){
                agregar_carrito(idxpress,2);
                $("#repuesto-xpress-modal").modal("hide");
                document.getElementById("xpress-codigo").value="";
                document.getElementById("xpress-descripcion").value="";
                document.getElementById("xpress-cantidad").value="";
                document.getElementById("xpress-precio").value="";
            }else{
                Vue.swal({
                    text: 'No se pudo crear Repuesto Xpress',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
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

  function agregar_carrito(id_rep,cual=1)
  {
    let idcliente=cliente['id'];
    let cant=0;
    let idlocal=0;
    if(cual==1){ //desde búsqueda repuestos normal
        var idc="cant-"+id_rep;
        var idl="local-"+id_rep;
        var local=document.getElementById(idl);
        var texto_local=local.options[local.selectedIndex].text;
        var ids="stock-"+id_rep;
        cant=document.getElementById(idc).value;
        idlocal=local.value;
        var st=document.getElementById(ids).value;
        var ini=texto_local.indexOf("(");
        var fin=texto_local.indexOf(")");
        var saldo_local=texto_local.substr(ini+1,(fin-ini-1));
        if(Number(cant)>Number(saldo_local))
        {
            Vue.swal({
                text: 'Excede el Stock Actual ('+saldo_local+") del local",
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
    }

    if(cual==2){ //repuesto xpress
        cant=document.getElementById("xpress-cantidad").value;
    }

    if(cant<=0 || isNaN(cant)) //is Not A Number (isNaN) comprueba si no es un número
    {
        Vue.swal({
            text: 'Cantidad vacia, cero o no es un número válido...',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
    }else{
      // Petición
      var url="{{url('ventas/agregar_carrito')}}";
      var parametros={idcliente:idcliente,idrep:id_rep,idlocal:idlocal,cantidad:cant};

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });

        $.ajax({
        type:'POST',
        beforeSend: function () {
          $("#mensajes-modal").html("Agregando al carro de compras...");
        },
        url:url,
        data:parametros,
        success:function(resp){
          if(resp=="existe")
          {
            $("#mensajes-modal").html("Upss!!!");
            Vue.swal({
                    text: 'Repuesto ya esta en el carrito',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
            let t=new Intl.NumberFormat('es-CL').format(resp);
            $("#mensajes-modal").html("Total: "+t);
            document.getElementsByName("forma_pago_monto")[0].value=t.replace(/\./g,"");
            dame_carrito();
            Vue.swal({
                    text: 'Agregado',
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
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
    } //fin del if si cant es 0 o vacio

    // $("#buscar-repuesto-modal").modal("hide");
  }

function ver_relacionados(idrep)
{
  modal_relacionado=true;
  //Cargar los repuestos relacionados de idrep y mostrarlos en el modal para poder agregarlos al carrito
  dame_relacionados(idrep);
  $("#agregar-relacionado-modal").modal("show");
}

  function dame_carrito()
  {

    // Petición
    var url="{{url('ventas/dame_carrito')}}"; //fragm.ventas_carrito
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#carrito").html("Buscando Carrito...");
          },
       url:url,
       success:function(carrito){
        $("#carrito").html(carrito);
       },
        error: function(error){
          $('#carrito').html(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  function poner_nombre_carrito()
  {
    //Verificar que carrito actual (activo) no esté vacio
    var num_items_carrito=document.getElementById("items_carrito").value // esta en fragm.ventas_carrito.blade.php
    if(num_items_carrito==0)
    {
      Vue.swal({
                    text: 'Carrito vacío. ¿Qué vas a guardar?',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
      return false;
    }
    var c=document.getElementById("confirmar");
        c.style.display="none";
        document.getElementById("nombre-carrito").value="";
        document.getElementById("nombre-carrito").placeholder="Escriba el nombre...";
    //abrir modal que muestre un text para ingresar el nombre del carrito
    //con dos botones, uno guardar y otro cancelar.

    $('#poner-nombre-carrito-modal').on('shown.bs.modal', function () {
        $("#nombre-carrito").focus();
      });
    $("#poner-nombre-carrito-modal").modal("show");

  }

  function verificar_nombre_carrito()
  {
    var nombre=document.getElementById("nombre-carrito");
    if(nombre.value.trim().length==0) //No escribió Nombre
    {
      Vue.swal({
        text: 'Escriba el nombre del carrito',
        position: 'top-end',
        icon: 'error',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      $("#nombre-carrito").focus();
    }else{
      var url='{{url("ventas")}}'+'/verificarnombrecarrito/'+nombre.value.trim();
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#mensajes").html("Verificando Nombre Carrito...");
          },
       url:url,
       success:function(rpta){
        $("#mensajes").html("");
        if(rpta=="existe")
        {
          var c=document.getElementById("confirmar");
          c.style.display="block";
        }else{
          $("#poner-nombre-carrito-modal").modal("hide");
          if(rpta==nombre.value.trim()) //guardó bien
          {
              Vue.swal({
                    text: 'Carrito '+rpta+" Guardado...",
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
            Vue.swal({
                    text: 'VERIFICADO: '+rpta,
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }
        }
       },
        error: function(error){
          $("#poner-nombre-carrito-modal").modal("hide");
          Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
        }

      }); //Fin petición
    }
  }

  function guardar_carrito_completo()
  {
      var nombre=document.getElementById("nombre-carrito");
      nombre.placeholder="Escriba el nombre...";
      $existe="SI";
      var url="{{url('ventas/guardarcarritocompleto')}}"+"/"+nombre.value.trim()+"/"+$existe;

        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Guardando carrito de compras...");
        },
        url:url,
        success:function(resp){
          $("#poner-nombre-carrito-modal").modal("hide");
          $('#mensajes').html("");
          if(resp==nombre.value.trim()) //guardó bien
          {
              Vue.swal({
                    text: 'Carrito '+resp+' Guardado...',
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
              Vue.swal({
                    text: 'GUARDANDO: '+resp,
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }
        },
        error: function(error){
            $("#poner-nombre-carrito-modal").modal("hide");
            Vue.swal({
                title: 'ERROR',
                text: formatear_error(error.responseText),
                icon: 'error',
                });
            }
          });


  }

  function mostrar_nombres_carrito()
  {
      var url="{{url('ventas/damecarritosguardados')}}";
      $.ajax({
       type:'GET',
       url:url,
       success:function(carritos){
        var c=JSON.parse(carritos);
        var nombres="";
        if(c.length>0)
        {
          var n=0;
          c.forEach(function(nombre){
            n++;
            nombres=nombres+n+".- <a href='javascript:void(0);' onclick='cargar_carrito_completo(\""+nombre.nombre_carrito+"\")'>"+nombre.nombre_carrito+"</a><br>";
          });
          $("#titulo_nombre_carrito").html("ELEGIR CARRITO GUARDADO");
        }else{ //no hay carritos guardados
          $("#titulo_nombre_carrito").html("ATENCIÓN !!!");
          nombres="<strong><p style='color:red;text-align:center'>No hay carritos guardados</p></strong>";
        }
        $("#nombres-carrito").html(nombres);
        $("#elegir-carrito-modal").modal("show");
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

  function cargar_carrito_completo(nombre)
  {
    //keke
    Vue.swal({
        title: '¿Está seguro?',
        text: "Si ACEPTA se borrará el carrito actual!",
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick:false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText:'CANCELAR',
        confirmButtonText: 'ACEPTAR'
        }).then((result) => {
            if (result.value) {
                var url="{{url('ventas/cargarcarritocompleto')}}"+"/"+nombre;
                $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                    $("#elegir-carrito-modal").modal("hide");
                    if(resp=='OK')
                    {
                    dame_carrito();
                    }else{
                        Vue.swal({
                            title: 'ERROR',
                            text: 'No pude recuperar el carrito...',
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
            }else{
                $("#elegir-carrito-modal").modal("hide");
            }
        });

  }

  function borrar_carritos_guardados()
  {
    borrar_carrito('guardados');
  }

  function borrar_carrito(cual) // Borra el carrito activo o guardados
  {
    var url="{{url('ventas/borrar_carrito')}}"+"/"+cual;
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#carrito").html("Buscando Carrito...");
          },
       url:url,
       success:function(resp){
        if(resp=='actual')
            $('#carrito').html("<h4 class='alert alert-info d-flex justify-content-center'>Carrito Vacio</h4><input type='hidden' id='items_carrito' value='0'>");
            $('#total_forma_pago').html("0");
            dame_formas_pago();
        if(resp=='guardados')
             Vue.swal({
                    text: 'Carritos guardados borrados...',
                    position: 'top-end',
                    icon: 'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
       },
        error: function(error){
        $('#carrito').html(formatear_error(error.responseText));
      }

      });
  }

  function borrar_item_carrito(item_id)
  {
      var url='{{url("ventas")}}'+'/'+item_id+'/borrar_item_carrito'; //petición
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#carrito").html("Borrando Item...");
          },
       url:url,
       success:function(resp){
        let t=new Intl.NumberFormat('es-CL').format(resp);
        document.getElementsByName("forma_pago_monto")[0].value=t.replace(/\./g,"");
        dame_carrito();
        //calcular_sumatoria();
       },
        error: function(error){
        $('#carrito').html(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

function transferir_carrito(){
    Vue.swal({
        title: 'P R O N T O',
        icon: 'info',
    });
}

function modifikar_precio(idrep)
{
    if(!modifikar_precio_panel )
    {
        //Pedir las compras del repuesto
        var url='{{url("factuprodu/damecompras")}}'+'/'+idrep;
        id_precio_a_modifikar=0;
        $.ajax({
        type:'GET',
        beforeSend: function () {
        //$("#mensajes").html("Descuentos Clientes...");
            },
        url:url,
        success:function(compras){
            $("#compras_repuesto").html(compras);//datos en la vista
            //muestra los datos
            mostrar_panel_buscar=false;
            buscar();
            precio_a_modifikar=parseInt(document.getElementById("pv-"+idrep).value.replace(/\./g,""));
            id_precio_a_modifikar=idrep;
            document.getElementById("precio_a_modificar").value=precio_a_modifikar;
            document.getElementById("rezultadoz").className="col-sm-9 tabla-scroll-y-300";
            document.getElementById("rezultadoz_fieldset").disabled=true;
            document.getElementById("zona_detalle_fieldset").disabled=true;
            document.getElementById("modifikar_precio").className="col-sm-3";
            document.getElementById("modifikar_precio").style="display:visible";
            document.getElementById("modifikar_precio").style="padding-right:1px";
            modifikar_precio_panel=true;
        },
        error: function(error){
            Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
        }

        }); //Fin petición




    }

}

function cerrar_panel_modifikar()
{
    if(modifikar_precio_panel)
    {
        $("#compras_repuesto").html("");
        precio_a_modifikar=0;
        document.getElementById("rezultadoz").className="col-sm-12 tabla-scroll-y-300";
        document.getElementById("rezultadoz_fieldset").disabled=false;
        document.getElementById("zona_detalle_fieldset").disabled=false;
        document.getElementById("modifikar_precio").className="col-sm-0";
        document.getElementById("modifikar_precio").style="display:none";
        modifikar_precio_panel=false;
        //buscar();

    }
}

function guardar_nuevo_precio()
{

    //Sólo realizar la modificación del precio si son diferentes
    //quitarle el punto de miles
    let old_val=precio_a_modifikar;
    let new_val=parseInt(document.getElementById("precio_a_modificar").value.replace(/\./g,""));
    let guardó_ok=false;
    if(new_val<old_val)
    {
        let r=confirm("valor anterior: "+old_val+" nuevo valor: "+new_val+"\nNUEVO VALOR MENOR QUE VALOR ANTERIOR\nDesea guardarlo?");
        if(r)
        {
            guardar_precio_venta(new_val);
        }
    }
    if(new_val>old_val)
    {
        //console.log("nuevo valor > antiguo valor");
        guardar_precio_venta(new_val);
    }
}

function guardar_precio_venta(nuevo_precio)
{
    let guardó_ok=false;
    let dato=id_precio_a_modifikar+"&"+nuevo_precio;
    var url='{{url("repuesto/guardar_precio_venta")}}'+'/'+dato;
    $.ajax({
        type:'GET',
        beforeSend: function () {
            $("#mensajes-modal").html("Guardando Nuevo Precio...");
        },
        url:url,
        success:function(rpta){
            if(rpta!="XUXA")
            {
                $("#ppv-"+id_precio_a_modifikar).html("<b>"+rpta+"</b>");
                $("#pv-"+id_precio_a_modifikar).val(rpta);
                $("#mensajes-modal").html("Nuevo Precio Guardado...");
                //actualizar el precio de venta del carrito
                //Mejor avisar en la modificación de precios
                //dame_carrito();
                id_precio_a_modifikar=0;
                cerrar_panel_modifikar();
            }else{
                $("#mensajes-modal").html("XUXA...NO GUARDÓ");
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

function dame_relacionados(idrep)
{
  var url='{{url("ventas/relacionados")}}'+'/'+idrep;
  $.ajax({
    type:'GET',
    beforeSend: function () {
    $("#mensajes").html("Relacionados...");
      },
    url:url,
    success:function(relacionados){
      $("#zona_repuestos_relacionados").html(relacionados);
    },
    error: function(error){
    $("#zona_repuestos_relacionados").html(formatear_error(error.responseText));
    }
  });
}

  function mas_detalle(id_repuesto)
  {
    dame_fotos(id_repuesto);
    dame_similares(id_repuesto);
    dame_oems(id_repuesto);
    dame_fabricantes(id_repuesto);
  }

  function dame_fotos(id_repuesto)
  {
      var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefotos';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(fotos){
        $("#mensajes-modal").html("Listo...");
        if(modal_relacionado)
        {
          $("#zona_fotos_r").html(fotos);
        }else{
          $("#zona_fotos").html(fotos);
        }
       },
        error: function(error){
          $('#zona_fotos').html(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  function abrir_foto_modal(link)
  {
    var base='{{asset("storage/")}}';
    var enlace=base+'/'+link;
    $("#aqui_foto").html("<img src='"+enlace+"' width='100%' onmouseout='cerrar_foto_modal();'>");
    $('#foto-modal').modal({backdrop: 'static', keyboard: false})
    $("#foto-modal").modal("show");
  }

  function cerrar_foto_modal()
  {
    $("#foto-modal").modal("hide");
  }

  function dame_similares(id_repuesto)
  {
      var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damesimilares';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(similares){
        $("#mensajes-modal").html("Listo...");
        if(modal_relacionado)
        {
          $("#zona_similares_r").html(similares);
        }else{
          $("#zona_similares").html(similares);
        }
       },
        error: function(error){
          $('#zona_similares').html(formatear_error(error.responseText));
        }

      });
  }

  function editar_anio_similares(id_simi){
    editando_anio_similares=true;
    document.getElementById("anio_simi_"+id_simi).readOnly=false;
    antiguo_valor_anio_similar=document.getElementById("anio_simi_"+id_simi).value;
  }

  function validar_nuevo_anio_similares(anios){
      //Validar que anios tenga el formato 9999-9999
      var mensa="Años de la aplicación debe ser en formato 9999-9999 y el año inicial menor igual al año final";
      var n="";
      var nn=0;
      var anios_ok=true;
      if(anios.length!=9)
      {
        Vue.swal({
                text: mensa,
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }

      for (var i=0;i<anios.length;i++)
      {
        n=anios.substring(i,i+1);
        if(i==4)
        {
          if(n!="-")
          {
            anios_ok=false;
            break;
          }
        }else{
          nn=n*1;
          if(isNaN(nn) || !Number.isInteger(nn))
          {
            anios_ok=false;
            break;
          }
        }
      }
      if(anios_ok==false)
      {
        Vue.swal({
                text: mensa,
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }

      let año_actual=(new Date).getFullYear();
      let año_inicial=anios.substring(0,4)*1;
      let año_final=anios.substring(5)*1;
      if(año_inicial>año_actual || año_final>año_actual)
      {
        Vue.swal({
                text: mensa,
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        return false;
      }
      if(año_inicial>año_final)
      {
        Vue.swal({
            text: mensa,
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
      }

      return true;
  }

  function guardar_anio_similares(id_simi){
    if(editando_anio_similares){

        let nuevo_valor_anio_similar=document.getElementById("anio_simi_"+id_simi).value;
        if(antiguo_valor_anio_similar!=nuevo_valor_anio_similar){
            if(validar_nuevo_anio_similares(nuevo_valor_anio_similar)){
                editando_anio_similares=false;
                document.getElementById("anio_simi_"+id_simi).readOnly=true;
                let dato=id_simi+"_"+nuevo_valor_anio_similar;
                var url='{{url("repuesto")}}'+'/actualizar_anio_similar/'+dato;
                $.ajax({
                    type:'GET',
                    beforeSend: function () {

                    },
                    url:url,
                    success:function(rpta){
                        if(rpta=="OK"){
                            Vue.swal({
                                text: "Año Modificado...",
                                position: 'center',
                                showConfirmButton: false,
                                toast: true,
                                timer: 2000,
                            });
                        }else{
                            Vue.swal({
                                text: "No se pudo guardar...",
                                position: 'center',
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
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
        }

    }
  }

  function dame_oems(id_repuesto)
  {
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/dameoems';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(oems){
        $("#mensajes-modal").html("Listo...");
        if(modal_relacionado)
        {
          $("#zona_oem_r").html(oems);
        }else{
          $("#zona_oem").html(oems);
        }
       },
        error: function(error){
          $('#zona_oem').html(formatear_error(error.responseText));
        }

      });
  }

  function dame_fabricantes(id_repuesto)
  {
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefabricantes';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(fabs){
        $("#mensajes-modal").html("Listo...");
        if(modal_relacionado)
        {
          $("#zona_fab_r").html(fabs);
        }else{
          $("#zona_fab").html(fabs);
        }
       },
        error: function(error){
          $('#zona_fab').html(formatear_error(error.responseText));
        }

      }); //Fin petición
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
    $("#total_forma_pago").html("<b><p style='color:blue'>"+t+"</p></b>");
    return total_pago;
  }

  function total_pagado()
  {
    var total_pago=calcular_sumatoria();

    //Comparar con el total del carrito
    let total_carrito=Number(document.getElementById("total_carrito").value);
    let total_pagado=Number(total_pago);
    let diferencia=Math.abs(total_pagado-total_carrito);

    if(diferencia>2)
    {
      $("#aqui_mensaje").html("Total a Pagar es menor que Total Venta");
      return "SI";
    }else{
      return "NO"
    }

  }

  function dame_formas_pago()
  {
    var url='{{url("ventas/dame_forma_pago")}}';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $('#mensajes').html("Cargando Formas de Pago...");
          },
       url:url,
       success:function(formas){
        $('#mensajes').html("&nbsp;");
        $("#formas_pago").html(formas);
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

  function activar_forma_pago(id){
    let marca=document.getElementById("formita-"+id).checked;
    document.getElementById("monto-"+id).disabled=!marca;
    document.getElementById("referencia-"+id).disabled=!marca;
  }

  function buscar_cliente()
  {
    $("#listar_clientes").html("");
    document.getElementById("buscado").value="";
    $('#buscar-cliente-modal').on('shown.bs.modal', function () {
      $("#buscado").focus();
    });
    $("#buscar-cliente-modal").modal("show");
  }

  function buscar_clientes()
  {
    //medir var t_ini=milis();
    var url="{{url('clientes/buscar/')}}";
    var quien="ventas";
    var ax=document.getElementById("buscaxnombres").checked;
    if(ax)
    {
      var nombres=document.getElementById("buscado").value.trim();
      if(nombres.length==0)
      {
        Vue.swal({
                    text: 'Criterio Búsqueda Vacío. Ingrese NOMBRES',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        $("#buscado").focus();
        return false;
      }
      var parametros={buscax:"nombres",buscado:nombres,quien:quien};
    }


    var bx=document.getElementById("buscaxrut").checked;
    if(bx) //Elegido el rut
    {
      //DEJAMOS SOLO NÚMEROS
      var ruut=document.getElementById("buscado");
      var rut=ruut.value.replace(/[^\d]/g, '');
      ruut.value=rut;
      if(rut.length==0)
      {
        Vue.swal({
                    text: 'Criterio Búsqueda Vacío. Ingrese RUT',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        $("#buscado").focus();
        return false;
      }
      parametros=parametros={buscax:"rut",buscado:rut,quien:quien};
    }

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      $("#listar_clientes").html("<h5><p class='text-center'>Buscando Clientes...</p></h5>");
    },
    url:url,
    data:parametros,
    success:function(resp){
                 $("#listar_clientes").html(resp);
                 $("#rut_cliente_rapido").focus(); // Por si no encuentra clientes, se ubica allí.
      //medir var t_fin=milis();
      //medir $("#buscar-cliente-modal-titulo").html("tiempo: "+(t_fin-t_ini));

    },
    error: function(error){
      $("#buscar-cliente-modal").modal("hide");
      Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
    }
  });

  }

  function estadistica_buscar_cliente(idc)
  {
    var url='{{url("clientes_buscar")}}'+'/'+idc;
      $.ajax({
       type:'GET',
       beforeSend: function () {
          },
       url:url,
       success:function(){

       },
        error: function(error){
        $("#listar_clientes").html(formatear_error(error.responseText));
        }

      });
  }

  function cargar_cliente(cliente_array)
  {
    let bb=document.getElementById("radio_boleta");
    //cliente_array es un array asociativo que llega desde fragm.clientes_ventas.blade
    cliente=cliente_array;
    estadistica_buscar_cliente(cliente["id"]);
    document.getElementById("id_cliente").value=cliente["id"];
    $("#cliente_rut").html("RUT: "+cliente['rut']);
    if(cliente['tipo_cliente']==0){
        $("#cliente_nombres").html("Cliente: "+cliente['nombres']+" "+cliente['apellidos']);
    }
    if(cliente['tipo_cliente']==1){
        $("#cliente_nombres").html("Razón Social: <br>"+cliente['empresa']);
    }

    $("#buscar-cliente-modal").modal("hide");
    aplicar_descuentos_cliente(cliente['id']);

    var td=$('input[name="tipo_documento"]:checked').val().trim();
    if(td!="cotizacion")
    {
        document.getElementById("radio_factura").checked=true;
      if(cliente["credito"]==1)
      {
        document.getElementById("credito").disabled=false;
      }else{ //credito=0, no se le permite créditos al cliente
        document.getElementById("credito").disabled=true;
        document.getElementById("credito").checked=false;
        document.getElementById("contado").checked=true;
      }
    }

  }

  function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
  }


  function guardar_cliente_xpress() // En manten.clientes.blade.php también hay... tal vez se debe modificar
  {


    $("#cliente_xpress_mensaje").html("");
    var documento_envio=document.getElementById("dcmto_en_envio").value.toString().trim();
    if(documento_envio.length==0){
        Vue.swal({
                    title: 'CUIDADO',
                    text: 'Documento envio sin número, comuníquese con el ADMIN',
                    position: 'center',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
        return false;
    }

    var cantidad_datos_cliente_xpress=0;

    //REVISAR RUT
    //le ponemos uppercase por si el último dígito es "k"
    var el_rut=document.getElementById("cliente_xpress_rut_en_envio").value.toString().trim().toUpperCase();
    el_rut=el_rut.replace(/\./g,"");
    el_rut=el_rut.replace("-","");

    if(el_rut.length==8 || el_rut.length==9)
    {
        if(!RUN_correcto(el_rut))
        {
            Vue.swal({
                        title: 'RUT INVÁLIDO',
                        text: 'Dígito verificador inválido',
                        position: 'center',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
            return false;
        }else{
            cantidad_datos_cliente_xpress++;
        }
    }else{
        if(el_rut.length==0){
            el_rut="999999999";
        }else{
            Vue.swal({
                        title: 'RUT INVÁLIDO',
                        text: 'pocos o demasiados dígitos',
                        position: 'center',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
            return false;
        }

    }

    var nombres=document.getElementById("cliente_xpress_nombres_en_envio").value.toString().trim();
    if(nombres.length>2)
    {
        cantidad_datos_cliente_xpress++;
    }else{
        nombres="---";
    }

    var apellidos=document.getElementById("cliente_xpress_apellidos_en_envio").value.toString().trim();
    if(apellidos.length>2)
    {
        cantidad_datos_cliente_xpress++;
    }else{
        apellidos="---";
    }

    var celular=document.getElementById("cliente_xpress_celular_en_envio").value.toString().trim();
    if(celular.length>6)
    {
        cantidad_datos_cliente_xpress++;
    }else{
        celular="---";
    }

    var correo=document.getElementById("cliente_xpress_correo_en_envio").value.toString().trim();
    if(correo.length>6)
    {
        cantidad_datos_cliente_xpress++;
    }else{
        correo="---";
    }

    if(cantidad_datos_cliente_xpress==0){
        Vue.swal({
                    title: 'NO GUARDÓ',
                    text: 'Ingrese al menos un dato del Cliente',
                    position: 'center',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
        return false;
    }

    //determinar si guarda persona o empresa
    var empresa="---";
    var tipo_cliente=0;
    if(nombres.length>5 && apellidos.length==0){
        empresa=nombres;
        nombres="---";
        apellidos="---";
    }

    if(nombres.length>5 && apellidos.length>5){
        empresa="---";
    }

    if(nombres.length==0 && apellidos.length==0){
        empresa="---";
        nombres="---";
        apellidos="---";
    }

    var url="{{url('clientes/guardar_cliente_xpress')}}";
    //console.log("rut: "+el_rut+"\n\rnombres: "+nombres+"\n\rapellidos: "+apellidos+"\n\rempresa: "+empresa+"\n\rcelular: "+celular+"\n\rcorreo: "+correo+"\n\rdocum: "+documento_envio);
    var parametros={
        rut_xpress:el_rut,
        nombres_xpress:nombres,
        apellidos_xpress:apellidos,
        empresa_xpress:empresa,
        telf1_xpress:celular,
        email_xpress:correo,
        documento_xpress:documento_envio
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
    beforeSend:function(){

    },
    success:function(resp){
      //Si el primer carácter no se puede convertir en número, parseInt devuelve NaN.
      var x=parseInt(resp);
      if(isNaN(x))
      {
        $("#cliente_xpress_mensaje").html(resp);
      }else{
        //devuelve el ID de la tabla clientes_xpress
        $("#cliente_xpress_mensaje").html("<h4>Cliente Xpress Guardado</h4>");

        //limpiar controles
        document.getElementById("dcmto_en_envio").value="";
        document.getElementById("cliente_xpress_rut_en_envio").value="";
        document.getElementById("cliente_xpress_nombres_en_envio").value="";
        document.getElementById("cliente_xpress_apellidos_en_envio").value="";
        document.getElementById("cliente_xpress_celular_en_envio").value="";
        document.getElementById("cliente_xpress_correo_en_envio").value="";
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

    function abrir_referencias(){
        $('#referencias-modal').on('shown.bs.modal', function () {

        });
        $("#referencias-modal").modal("show");
    }

    function abrir_cliente_xpress(){

        if(!div_cliente_xpress_mostrar){
            $("#boton_cliente_xpress_en_envio").html("Cerrar Cliente Xpress");
            $("#cliente_xpress_mensaje").html("");
            document.getElementById( 'cliente_xpress_div' ).style="display:visible";
        }else{
            $("#boton_cliente_xpress_en_envio").html("Abrir Cliente Xpress");
            document.getElementById( 'cliente_xpress_div' ).style="display:none";
        }
        div_cliente_xpress_mostrar=!div_cliente_xpress_mostrar;
        /*
        $('#cliente-xpress-modal').on('shown.bs.modal', function () {

        });
        $("#cliente-xpress-modal").modal("show");
        */
    }


    function abrir_procesar_envio(){
        $("#titulo_procesar_envio_modal").html("PROCESANDO "+documento_procesado.toUpperCase());
        $('#procesar-envio-modal').on('shown.bs.modal', function () {

        });
        $("#procesar-envio-modal").modal("show");
        enviar_sii();
    }

    function cerrar_procesar_envio(){
        cliente_xpress_cancelar();
        $("#procesar-envio-modal").modal("hide");
        abrir_cliente_xpress();
    }

    function referencias_aceptar(){
        let i=0;
        let ref1_docu=document.getElementById("ref1_docu").value;
        let ref1_folio=document.getElementById("ref1_folio").value;
        let ref1_fecha=document.getElementById("ref1_fecha").value;
        let ref1_razon=document.getElementById("ref1_razon").value;
        let ref1_check=document.getElementById("ref1_check").checked;

        let ref2_docu=document.getElementById("ref2_docu").value;
        let ref2_folio=document.getElementById("ref2_folio").value;
        let ref2_fecha=document.getElementById("ref2_fecha").value;
        let ref2_razon=document.getElementById("ref2_razon").value;
        let ref2_check=document.getElementById("ref2_check").checked;

        let ref3_docu=document.getElementById("ref3_docu").value;
        let ref3_folio=document.getElementById("ref3_folio").value;
        let ref3_fecha=document.getElementById("ref3_fecha").value;
        let ref3_razon=document.getElementById("ref3_razon").value;
        let ref3_check=document.getElementById("ref3_check").checked;

        let mensaje_error_ref="";

        if(ref1_check===true){
            if(ref1_docu==0){
                mensaje_error_ref="Elija un documento para Referencia 1";
            }else if(ref1_folio.length==0 || isNaN(parseInt(ref1_folio))){
                mensaje_error_ref="Escriba Número de Folio para Referencia 1";
            }else if(ref1_fecha.length==0){
                mensaje_error_ref="Elija Fecha para Referencia 1";
            }else if(ref1_razon.length==0){
                mensaje_error_ref="Escriba Razón para Referencia 1";
            }
            if(mensaje_error_ref!=""){
                Vue.swal({
                    icon: 'error',
                    text:mensaje_error_ref,
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 4000,
                });
                return false;
            }
            ref1.push({
                docu:ref1_docu,
                folio:ref1_folio,
                fecha:ref1_fecha,
                razon:ref1_razon
            });
            i++;
        }else{
            ref1=[];
        }

        mensaje_error_ref="";
        if(ref2_check===true){
            if(ref2_docu==0){
                mensaje_error_ref="Elija un documento para Referencia 2";
            }else if(ref2_folio.length==0 || isNaN(parseInt(ref2_folio))){
                mensaje_error_ref="Escriba Número de Folio para Referencia 2";
            }else if(ref2_fecha.length==0){
                mensaje_error_ref="Elija Fecha para Referencia 2";
            }else if(ref2_razon.length==0){
                mensaje_error_ref="Escriba Razón para Referencia 2";
            }
            if(mensaje_error_ref!=""){
                Vue.swal({
                    icon: 'error',
                    text:mensaje_error_ref,
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 4000,
                });
                return false;
            }
            ref2.push({
                docu:ref2_docu,
                folio:ref2_folio,
                fecha:ref2_fecha,
                razon:ref2_razon
            });
            i++;
        }else{
            ref2=[];
        }

        mensaje_error_ref="";
        if(ref3_check===true){
            if(ref3_docu==0){
                mensaje_error_ref="Elija un documento para Referencia 3";
            }else if(ref3_folio.length==0 || isNaN(parseInt(ref3_folio))){
                mensaje_error_ref="Escriba Número de Folio para Referencia 3";
            }else if(ref3_fecha.length==0){
                mensaje_error_ref="Elija Fecha para Referencia 3";
            }else if(ref3_razon.length==0){
                mensaje_error_ref="Escriba Razón para Referencia 3";
            }
            if(mensaje_error_ref!=""){
                Vue.swal({
                    icon: 'error',
                    text:mensaje_error_ref,
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 4000,
                });
                return false;
            }
            ref3.push({
                docu:ref3_docu,
                folio:ref3_folio,
                fecha:ref3_fecha,
                razon:ref3_razon
            });
            i++;
        }else{
            ref3=[];
        }

        if(i>0){
            document.getElementById("referencias").innerHTML="<b>REFERENCIAS:</b> "+i;
        }else{
            document.getElementById("referencias").innerHTML="<b>REFERENCIAS:</b> Ninguna";
        }
        $("#referencias-modal").modal("hide");
    }

    function referencias_cancelar(){
        document.getElementById("referencias").innerHTML="<b>REFERENCIAS:</b> Ninguna";

        referencia1=true;
        document.getElementById("referencia1").disabled=referencia1;
        document.getElementById("ref1_docu").value=0;
        document.getElementById("ref1_folio").value="";
        document.getElementById("ref1_fecha").value="";
        document.getElementById("ref1_razon").value="";
        document.getElementById("ref1_check").checked=false;
        ref1=[];

        referencia2=true;
        document.getElementById("referencia2").disabled=referencia2;
        document.getElementById("ref2_docu").value=0;
        document.getElementById("ref2_folio").value="";
        document.getElementById("ref2_fecha").value="";
        document.getElementById("ref2_razon").value="";
        document.getElementById("ref2_check").checked=false;
        ref2=[];

        referencia3=true;
        document.getElementById("referencia3").disabled=referencia3;
        document.getElementById("ref3_docu").value=0;
        document.getElementById("ref3_folio").value="";
        document.getElementById("ref3_fecha").value="";
        document.getElementById("ref3_razon").value="";
        document.getElementById("ref3_check").checked=false;
        ref3=[];
    }

    function cliente_xpress_guardar(){
        //console.log("cliente_xpress_guardar");
        guardar_cliente_xpress();
    }

    function cliente_xpress_cancelar(){
        //console.log("cliente_xpress_cancelar");
        document.getElementById("dcmto_en_envio").value="";
        document.getElementById("cliente_xpress_rut_en_envio").value="";
        document.getElementById("cliente_xpress_nombres_en_envio").value="";
        document.getElementById("cliente_xpress_apellidos_en_envio").value="";
        document.getElementById("cliente_xpress_celular_en_envio").value="";
        document.getElementById("cliente_xpress_correo_en_envio").value="";
        $("#cliente_xpress_mensaje").html("");
    }

    function cargar_documentos_referencia(){
        var url="{{url('/dame_tipo_documentos')}}";
        $.ajax({
        type:'GET',
        url:url,
        success:function(resp){
            resp.forEach(function(r){
                $("#ref1_docu").append('<option value="'+r.codigo_documento+'">'+r.nombre_documento+'</option>');
                $("#ref2_docu").append('<option value="'+r.codigo_documento+'">'+r.nombre_documento+'</option>');
                $("#ref3_docu").append('<option value="'+r.codigo_documento+'">'+r.nombre_documento+'</option>');
            });
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

    function activar_referencia(cual){
        if(cual==1){
            referencia1=!referencia1;
            document.getElementById("referencia1").disabled=referencia1;
        }
        if(cual==2){
            referencia2=!referencia2;
            document.getElementById("referencia2").disabled=referencia2;
        }
        if(cual==3){
            referencia3=!referencia3;
            document.getElementById("referencia3").disabled=referencia3;
        }
    }

    function cotizar(){
        let idc=document.getElementById("id_cliente").value;
/*
        if(idc==0){
            Vue.swal({
                text: 'Elija un Cliente',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
*/
        nombre_cotizacion=document.getElementById("nombre_cotizacion").value;
        if(nombre_cotizacion.length==0)
        {
            Vue.swal({
                text: 'Ingrese nombre de cotización',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        let num_items_carrito=document.getElementById("items_carrito").value // esta en fragm.ventas_carrito.blade.php
        if(num_items_carrito==0)
        {
        Vue.swal({
                text: 'Carrito vacío. ¿Qué vas a cotizar?',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
            return false;
        }
        let docu = $('input[name="tipo_documento"]:checked').val().trim();
        let venta="nada";
        let url="{{url('/ventas/cotizar')}}";
        let parametros={docu:docu,idcliente:idc,nombre_cotizacion};
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            beforeSend: function () {

            },
            url:url,
            data:parametros,
            success:function(resp){
                let r=JSON.parse(resp);
                if(r.estado='OK'){
                    imprimir_cotizacion(r.cotizacion);
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error'
                    });
                }
            },
            error: function(error){
                Vue.swal({
                    title: 'ERRORRR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });
            }
        });
    }

    function mostrar_cotizaciones(){
        let html='<table> \
        <thead> \
            <th></th> \
            <th></th> \
        <thead> \
        <tbody> \
            <tr> \
                <td><button  class="btn btn-info form-control-sm" onclick="mostrar_cotizaciones_mes()">BUSCAR</button></td> \
                <td><input type="text" style="width:80%" id="nombre_cotizacion_buscar" placeholder="nombre a buscar"></td> \
            </tr> \
            <tr> \
                <td><button  class="btn btn-warning form-control-sm" onclick="mostrar_cotizaciones_cliente()">CLIENTE</button></td> \
                <td>Buscar por cliente</td> \
            </tr> \
            <tr> \
                <td><button  class="btn btn-success form-control-sm" onclick="mostrar_cotizaciones_numero()">BUSCAR</button></td> \
                <td><input type="text" style="width:80%" id="numero_cotizacion_buscar" placeholder="número a buscar"></td> \
            </tr> \
        </tbody> \
    </table>';

        Vue.swal({
            title: 'COTIZACIONES',
            html:html,
            showConfirmButton:false,
            showCancelButton:true,
            cancelButtonText:"CERRAR"
        });
    }

  function mostrar_cotizaciones_cliente()
  {
    Vue.swal.close();
    var id_cliente=document.getElementById("id_cliente").value;
    if(id_cliente==0){
        Vue.swal({
            text: 'Elija el Cliente para mostrar sus cotizaciones vigentes',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    var url='{{url("ventas/damecotizaciones")}}'+'/'+id_cliente;
      $.ajax({
       type:'GET',
       url:url,
       success:function(cotizaciones){
        var dato=cotizaciones.substr(0,2);
        if(dato=="-1") //error
        {
          Vue.swal({
            text: 'En la tabla clientes no se ha definido el cliente 000000000',
            icon: 'error',
            allowOutsideClick:false
            });
          return false;
        }
        //REVISAR: Mejorar esto para que no tenga conflictos en las llaves
        if(dato=="[{") //viene un json
        {
          var c=JSON.parse(cotizaciones);
          if(c.length>0)
          {
            //abrir modal mostrando los números de cotización y clientes para luego elegir y cargarla al carrito.
            var cotiz="<strong>N°     Fecha</strong><br>";
            let nomcli="";
            if(id_cliente>0)
            {
                if(cliente['tipo_cliente']==0){
                    nomcli=cliente['nombres'].toUpperCase()+" "+cliente['apellidos'].toUpperCase()
                }
                if(cliente['tipo_cliente']==1){
                    nomcli=cliente['empresa'].toUpperCase();
                }
              $("#subtitulo-listado-cotizaciones").html(nomcli);
            }else{ //No se eligió cliente,
              $("#subtitulo-listado-cotizaciones").html("(SIN CLIENTES)");
            }
            let d=new Date();
            let hoy="'"+d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate()+"'";
            c.forEach(function(coti)
            {
                //la fecha la ponemos en formato YYYY-MM-DD
                let s=coti.fecha.split("-");
                let fec="'"+s[2]+"-"+s[1]+"-"+s[0]+"'";
                let dd=diferencia_dias(hoy,fec);
                if(dd>7){
                    cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:red\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+"</a><br>";
                }else{
                    cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:blue\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+"</a><br>";
                }
            });
            $("#listado_cotizaciones").html(cotiz);
            $("#mostrar-cotizaciones-modal").modal("show");
          }else{
            Vue.swal({
                text: 'No hay Cotizaciones Vigentes...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
          }
        }
        if(dato=="[]")
        {
          Vue.swal({
                text: 'No hay Cotizaciones Vigentes...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
        }


       },
        error: function(error){
          $("#mostrar-cotizaciones-modal").modal("hide");
          Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
        }

      }); //Fin petición
  }

  function diferencia_dias(fecha_mayor,fecha_menor){
    var date_1 = new Date(fecha_menor);
    var date_2 = new Date(fecha_mayor);

    var day_as_milliseconds = 86400000;
    var diff_in_millisenconds = date_2 - date_1;
    var diff_in_days = diff_in_millisenconds / day_as_milliseconds;

    return diff_in_days;
  }

  function mostrar_cotizaciones_mes()
  {
    Vue.swal.close();
    let id_cliente=document.getElementById("id_cliente").value;
    nombre_cotizacion_buscar=document.getElementById("nombre_cotizacion_buscar").value;
    if(nombre_cotizacion_buscar.length==0)
    {
        Vue.swal({
            text: 'Ingrese nombre de cotización a buscar',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let url='{{url("/ventas/damecotizacionesmes")}}'+'/'+nombre_cotizacion_buscar+"&"+id_cliente;

      $.ajax({
       type:'GET',
       url:url,
       success:function(cotizaciones){
        var dato=cotizaciones.substr(0,2);
        //REVISAR: Mejorar esto para que no tenga conflictos en las llaves
        if(dato=="[{") //viene un json
        {
          var c=JSON.parse(cotizaciones);
          if(c.length>0)
          {
            //abrir modal mostrando los números de cotización y clientes para luego elegir y cargarla al carrito.
            var cotiz="<strong>Fecha y Nombre</strong><br>";

            $("#subtitulo-listado-cotizaciones").html("(MES)");
            let d=new Date();
            let hoy="'"+d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate()+"'";
            c.forEach(function(coti)
            {
                //la fecha la ponemos en formato YYYY-MM-DD
                let s=coti.fecha.split("-");
                let fec="'"+s[2]+"-"+s[1]+"-"+s[0]+"'";
                let dd=diferencia_dias(hoy,fec);
                if(dd>7){
                    cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:red\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+" cli:"+(coti.elcliente==undefined ? " Ninguno": coti.elcliente)+"</a><br>";
                }else{
                    cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:blue\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+" cli:"+(coti.elcliente==undefined ? " Ninguno": coti.elcliente)+"</a><br>";
                }

            });
            $("#listado_cotizaciones").html(cotiz);
            $("#mostrar-cotizaciones-modal").modal("show");
          }else{
            Vue.swal({
                text: 'No hay Cotizaciones Vigentes...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          }
        }
        if(dato=="[]")
        {
          Vue.swal({
                text: 'No hay Cotizaciones Vigentes...',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
        }


       },
        error: function(error){
          $("#mostrar-cotizaciones-modal").modal("hide");
          Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
        }

      }); //Fin petición
  }

  function mostrar_cotizaciones_numero()
  {
    Vue.swal.close(); //nombre_cotizacion_buscar
    numero_cotizacion_buscar=document.getElementById("numero_cotizacion_buscar").value;
    if(numero_cotizacion_buscar.length==0)
    {
        Vue.swal({
            text: 'Ingrese número de cotización a buscar',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    cargar_cotizacion(numero_cotizacion_buscar);
  }

  function cargar_cotizacion(num_cotizacion)
  {
    var k=confirm("Si ACEPTA, se borrará el carrito actual");
    if(k)
    {
      var url="{{url('ventas/cargarcotizacion')}}"+"/"+num_cotizacion;
      $.ajax({
       type:'GET',
       url:url,
       success:function(resp){
        $("#mostrar-cotizaciones-modal").modal("hide");
        if(resp=='OK')
        {
          dame_carrito();
        }else{
            Vue.swal({
                title: 'ERROR',
                text: resp,
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

  }

  function imprimir_cotizacion(num_cotizacion){
    var url="{{url('ventas/imprimir_cotizacion')}}"+"/"+num_cotizacion;
      $.ajax({
       type:'GET',
       url:url,
       success:function(resp){
            let r=JSON.parse(resp);
            if(r.estado=='OK'){
                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w=window.open(r.mensaje,'_blank',config);
                w.focus();
                limpiar_todo();
            }else{
                Vue.swal({
                    title: r.estado,
                    text: formatear_error(r.mensaje),
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


  function contado_credito(op)
  {
    if(op==1) //click en contado
    {
      //dame_formas_pago();
      document.getElementById("zona_formas_pago").disabled=false;
    }else{ // click en crédito
      //$("#formas_pago").html("<br>");
      document.getElementById("zona_formas_pago").disabled=true;
    }
  }

  function aplicar_descuentos_cliente(id_cliente)
  {

    //Debe afectar al carrito de compras
    var url='{{url("ventas/descuento_carrito")}}'+'/'+id_cliente;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#mensajes").html("Descuentos Clientes...");
          },
       url:url,
       success:function(resp){
        //$("#mensajes").html("Cliente ID: "+resp);
        dame_carrito();
       },
        error: function(error){
          $('#mensaje-cliente').html(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

    window.onload = function(e){

        //Para que el panel de busqueda lateral inicie oculto
        mostrar_panel_buscar=false;
        buscar();

        //Cambia color de la fila en la tabla al hacerle click
        $(document).on('click','tr', function(){
            if (ultimaFila != null)
            {
                //ultimaFila.css('background-color', colorOriginal)
                ultimaFila.css('background-color', 'white')
            }
            colorOriginal = $(this).css('background-color');
            $(this).css('background-color', 'aqua');
            ultimaFila = $(this);
            //Después de hacerle click, no funciona el hover sobre la fila
        });

      //desactiva click derecho
      //document.oncontextmenu = function(){return false}

      //Cuando ingrese, asegurar que el carrito esta vacio y no haya cliente seleccionado
      //borrar_carrito();
      cliente['id']=0;
      dame_formas_pago();
      dame_carrito();
      aplicar_descuentos_cliente(0);
    //Al cerrar la ventana modal de buscar repuestos
    $("#buscar-repuesto-modal").on('hidden.bs.modal', function () {
      dame_carrito();
    });

    cargar_documentos_referencia();

    $(window).on("beforeunload", function(e) {
      document.getElementById("id_cliente").value=0;
    });

    let bb=document.getElementById("radio_boleta");
    let ff=document.getElementById("radio_factura");
    bb.checked=true;
    ff.checked=false;

}

function confirmar_documento(){
    let docu = $('input[name="tipo_documento"]:checked').val().trim();
    docu=docu.toUpperCase();
    Vue.swal({
        text: "Desea continuar con "+docu+ ' ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CONTINUAR',
        cancelButtonText: 'CANCELAR'
        }).then((result) => {
        if (result.isConfirmed) {
            if(docu=='COTIZACION'){
                cotizar();
            }
            if(docu=='BOLETA' || docu=='FACTURA'){
                generar_xml();
            }
        }else{
            /*
            let bb=document.getElementById("radio_boleta");
            let ff=document.getElementById("radio_factura");
            bb.checked=true;
            ff.checked=false;
            */
        }
        })
}

function que_documento(tipo_doc)
{
    let bb=document.getElementById("radio_boleta");
    let ff=document.getElementById("radio_factura");

    let idc=document.getElementById("id_cliente").value;


    if(idc==0 && tipo_doc=='factura'){
        bb.checked=true;
        ff.checked=false;
        Vue.swal({
            text: 'Elija un Cliente para emitirle Factura',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

  if(cliente["credito"]==undefined);
  {
    if(tipo_doc=="boleta" || tipo_doc=="factura")
    {
      document.getElementById("contado").disabled=false;
      document.getElementById("contado").checked=true;
      document.getElementById("credito").disabled=true;
      document.getElementById("dias_expira").disabled=true;
      document.getElementById("nombre_cotizacion").disabled=true;
      document.getElementById("nombre_cotizacion").value="";
      //dame_formas_pago();
      document.getElementById("zona_formas_pago").disabled=false;

    }

    if(tipo_doc=="cotizacion")
    {
      document.getElementById("nombre_cotizacion").disabled=false;
      document.getElementById("contado").disabled=true;
      document.getElementById("credito").disabled=true;
      document.getElementById("contado").checked=false;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=false;
      document.getElementById("nombre_cotizacion").focus();
      //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
      document.getElementById("zona_formas_pago").disabled=true;

    }
  }

  if(cliente["credito"]==1) //Cliente tiene crédito
  {
    if(tipo_doc=="boleta" || tipo_doc=="factura")
    {
      document.getElementById("contado").disabled=false;
      document.getElementById("credito").disabled=false;
      document.getElementById("contado").checked=true;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=true;
      if(document.getElementById("contado").checked)
      {
        //dame_formas_pago();
        document.getElementById("zona_formas_pago").disabled=false;
      }else{
        //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
        document.getElementById("zona_formas_pago").disabled=true;
      }
    }

    if(tipo_doc=="cotizacion")
    {
      document.getElementById("contado").disabled=true;
      document.getElementById("contado").checked=false;
      document.getElementById("credito").disabled=true;
      document.getElementById("dias_expira").disabled=false;
      document.getElementById("dias_expira").focus();
      //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
      document.getElementById("zona_formas_pago").disabled=true;
    }

  }

  if(cliente["credito"]==0) // Cliente no tiene crédito
  {
    if(tipo_doc=="boleta" || tipo_doc=="factura")
    {
      document.getElementById("contado").disabled=false;
      document.getElementById("contado").checked=true;
      document.getElementById("credito").disabled=true;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=true;
      //dame_formas_pago();
      document.getElementById("zona_formas_pago").disabled=false;
    }

    if(tipo_doc=="cotizacion")
    {
      document.getElementById("contado").disabled=true;
      document.getElementById("contado").checked=false;
      document.getElementById("credito").disabled=true;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=false;
      document.getElementById("dias_expira").focus();
      //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
      document.getElementById("zona_formas_pago").disabled=true;
    }
  }

}

function generar_xml()
{
    //Verificar si seleccionó documento de venta
    let bb=document.getElementById("radio_boleta");
    let ff=document.getElementById("radio_factura");
    let nombre_cotizacion="";

    if(!bb.checked && !ff.checked){
        Vue.swal({
            text: 'Seleccione un documento de Venta',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
      return false;
    }

    //Verificar si hay algo en el carrito    (campo oculto en la vista ventas_carrito)
    var items_en_carrito=document.getElementById("items_carrito").value;
    if(items_en_carrito==0)
    {
      Vue.swal({
        text: 'Carrito de Compras Vacio...',
        position: 'top-end',
        icon: 'error',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }



    //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};
    let docu = $('input[name="tipo_documento"]:checked').val().trim();
    let idc=document.getElementById("id_cliente").value;

    if(idc==0 && docu=='factura') // Solo exige cliente si es factura
    {
      Vue.swal({
        text: 'No ha seleccionado cliente...',
        position: 'top-end',
        icon: 'error',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    var venta="nada";

    if(docu!="cotizacion")
    {
      venta=$('input[name="tipo_venta"]:checked').val().trim();
    }

    if(venta=="contado")
    {
        //Forma de pago, monto y referencia
        //formita-1, monto-1, referencia-1

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
            mensaje_error="Total a Pagar no coincide con Total Venta";
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

    //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};

    }else{ //Venta al crédito o cotización, no hay formas de pago.
        if(venta=="credito" || venta=="delivery")
        {
            //var parametros={idcliente:idc,docu:docu,venta:venta};
        }else{ //Si no es crédito ni contado, entonces es cotización porque así lo programé... ja ja ja
            var d_ex=document.getElementById("dias_expira").value;
            //var parametros={idcliente:idc,docu:docu,dias_expira:d_ex};
        }
    }

    let url="{{url('ventas/generarxml')}}";
    let parametros={docu:docu,
                    idcliente:idc,
                    fmapago:venta,
                    ref1:JSON.stringify(ref1),
                    ref2:JSON.stringify(ref2),
                    ref3:JSON.stringify(ref3),
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
        success:function(rs){ //llega un JSON
            let rpta=JSON.parse(rs);

            if(rpta.estado=='GENERADO')
            {
                $("#mensajes").html("Generando XML...<b> Listo!!!</b>");
                documento_procesado=rpta.mensaje; //tiene el tipo y número de documento que se está procesando.
                document.getElementById("dcmto_en_envio").value=documento_procesado;
                abrir_procesar_envio();
                /*
                Vue.swal({
                    title: '<h3><i><strong>PROCESANDO '+documento_procesado.toUpperCase()+'</strong></i></h3>',
                    html:'<h3 id="envio-txt">Generando XML...</h3>'+
                            '<button  class="btn btn-info form-control-sm" onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>'+
                            '<button  class="btn btn-warning form-control-sm" disabled onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>'+
                            '<button  class="btn btn-success form-control-sm" disabled onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    showConfirmButton:false,
                    showCancelButton:true,
                    cancelButtonText:"CERRAR" //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estado()
                    });


                    */
            }else{
                Vue.swal({
                    title: rpta.estado,
                    text: rpta.mensaje,
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

function enviar_sii(){
    document.getElementById("btn-enviarsii").disabled=true;

    let docu = $('input[name="tipo_documento"]:checked').val().trim();
    if(docu!="cotizacion")
    {
      venta=$('input[name="tipo_venta"]:checked').val().trim();
    }

    var id_forma_pago=[];
    var monto_forma_pago=[];
    var referencia_forma_pago=[];
    var m="";
    var r="";
    var monto="";
    var referencia_pago="";
    let error=false;
    let parametros={};
    if(venta=='contado'){
      var paguitos=$('input[name="forma_pago"]:checked');
      paguitos.each(function() {
          //Si los checks seleccionados no tienen monto ni referencia??
          var texto_seleccionado=$(this)[0].nextSibling.nodeValue.toUpperCase().trim();
          m="monto-"+$(this).val();
          monto=document.getElementById(m).value;
          //console.log("monto: "+monto);
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
        parametros={idcliente:cliente['id'],docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};
    }else{ //es crédito o delivery
        parametros={idcliente:cliente['id'],docu:docu,venta:venta};
    }


    let url='{{url("ventas/enviarsii")}}';
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
        type:'POST',
        beforeSend: function () {
            $("#mensajes").html("<b>Enviando al SII...</b>");
            $("#envio-txt").html("<small>Enviando al SII...</small>");

        },
        url:url,
        data:parametros,
        success:function(rs){
            let rpta=JSON.parse(rs);
            if(rpta.estado=='OK')
            {
                $("#mensajes").html("Enviando al SII...<b>Recibido !!!</b>");
                $("#envio-txt").html("<small>"+rpta.mensaje+" TrackID: "+rpta.trackid+"</small>");
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=false;
                document.getElementById("btn-imprimir").disabled=false;
                TrackID=rpta.trackid;
            }else{
                document.getElementById("btn-enviarsii").disabled=false;
                $("#mensajes").html("Enviando al SII...<b>No recibido !!! Reintente el envío</b>");
                $("#envio-txt").html("<small>"+rpta.estado+". "+rpta.mensaje+"</small>");
                TrackID=0;
            }
        },
        error: function(error){
            console.log(error.responseText);
            $('#envio-txt').html(error.responseText);
        }
    });


}

function enviar_sii_original()
{

   let url='{{url("ventas/enviarsii")}}'+'/'+cliente['id'];

    $.ajax({
        type:'GET',
        beforeSend: function () {
        $("#mensajes").html("<b>Enviando al SII...</b>");
        $("#envio-txt").html("Enviando al SII...");
            },
        url:url,
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
            $('#envio-txt').html(formatear_error(error.responseText));
        }
    }); //Fin petición GET
}

function ver_estado(){
    //se necesita trackid (TrackID), tipo dte y numdoc
    let url='{{url("ventas/verestado")}}'+'/'+TrackID; //variable global en enviar_sii()

    $.ajax({
        type:'GET',
        beforeSend: function () {
        $("#mensajes").html("<b>Revisando estado...</b>");
        $("#envio-txt").html("<small>Revisando estado...</small>");
            },
        url:url,
        success:function(rs){
            rs=JSON.parse(rs);
            if(rs.estado=='ACEPTADO'){
                $("#envio-txt").html("<small>Envío ACEPTADO... puede imprimir</small>");
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=true;
                document.getElementById("btn-imprimir").disabled=false;
                let doc=""
            }else{
                $("#envio-txt").html("<small>"+rs.mensaje+"</small>");
            }
        },
        error: function(error){
            $('#envio-txt').html(formatear_error(error.responseText));
        }
    }); //Fin petición GET
}

function ver_estadoUP()
{
    //let TrackID=document.getElementById("trackID").value;
    document.getElementById("btn-verestado").disabled=true;
    let docu = $('input[name="tipo_documento"]:checked').val().trim();
    let tipoDTE="??";
    if(docu=="boleta"){
        tipoDTE='39';
    }
    if(docu=="factura"){
        tipoDTE='33';
    }

    var url='{{url("sii/verestado")}}'+"/"+tipoDTE+"&"+TrackID; //Controlador servicios_sii\sii_controlador
    $.ajax({
        type:'GET',
        beforeSend: function () {

            $("#mensajes").html("<b>Revisando Estado...</b>");
            $("#envio-txt").html("<small>Revisando estado...</small>");
            },
        url:url,
        success:function(rs){
            rs=JSON.parse(rs);
            if(rs.estado=='ACEPTADO'){
                $("#envio-txt").html("<small>Envío ACEPTADO... puede imprimir</small>");
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=true;
                document.getElementById("btn-imprimir").disabled=false;
                let doc="";
            }else{
                $("#envio-txt").html("<small>"+rs.estado+": "+rs.mensaje+"</small>");
                document.getElementById("btn-verestado").disabled=false;
            }


        },
        error: function(error){
            $('#mensajes').html(formatear_error(error.responseText));
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
              //console.log(resp);
            let r=JSON.parse(resp);
            if(r.estado=='OK'){
                $("#mensajes").html("Imprimiendo... <b>Listo...</b>");
                $('#envio-txt').html("PDF Generado...");
                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w=window.open(r.mensaje,'_blank',config);
                w.focus();
                //limpiar toda la pantalla y variables para una nueva venta...
                limpiar_todo();
                Vue.swal.close();
            }else{
                $('#envio-txt').html(r.estado+": "+r.mensaje);
            }

          },
          error: function(error){
            $('#envio-txt').html(formatear_error(error.responseText));
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


function guardar_venta()
{
    var docu = $('input[name="tipo_documento"]:checked').val().trim();
    if(docu=="cotizacion")
    {
      venta="nada";
    }else{
      var venta=$('input[name="tipo_venta"]:checked').val().trim();
    }

    var idc=document.getElementById("id_cliente").value;

  var url="{{url('ventas/guardarventa')}}";

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      $("#mensajes").html("<b>Enviando a SII...</b>");
    },
    url:url,
    data:parametros,
    success:function(doc){

      if(doc.substr(0,1)=="e") //Error
      {
        $('#mensajes').html(doc.substr(1));
        Vue.swal({
            title: 'ERROR',
            text: doc.substr(1),
            icon: 'error',
            });
        return;
      }



    },
    error: function(error){
        Vue.swal({
            title: 'ERRORRR',
            text: formatear_error(error.responseText),
            icon: 'error',
            });
    }
    });

  }





  //Mostrar Ocultar Filtros
  function filtros()
  {
    var f=document.getElementById("filtros");
    if (f.style.display === "none")
    {
      f.style.display = "block";
      $("#pFiltros").html('Ocultar Filtros');
    } else {
      f.style.display = "none";
      $("#pFiltros").html('Mostrar Filtros');
    }
  }

</script>
@endsection
@section('style')
<style>
*{
    margin: 0px;
    padding:0px;
}

/*
a:link {
  color: hotpink;
}

a:visited {
  color: yellow;
  background-color:red;
}
*/
input[type="checkbox"],
label {
    float: left;
    line-height: 1.6em;
    height: 1.6em;
    margin: 0px 2px;
    padding: 0px;
    font-size: inherit;
}

.table>thead>tr>th {
  padding-top:2px;
  padding-bottom:2px;
  padding-left:2px;
  padding-right:2px;
}

.table>tbody>tr>td {
  padding:0.5px;
}

.modal-ventas {
    width: 100%;
}

.modal-xl{
    max-width: 95%;
}

.modal-header-40 {
    background-color: #5b60e9;
    color: white;
    height: 40px;
    padding-top:8px;
    padding-bottom:4px;
    padding-left:2px;
    padding-right: 2px;
}

.modal-header-80 {
    display:flex;
    flex-direction: column;
    background-color: #4146D8;
    /* color: white; */
    height: 80px;
    width: 100%;
    padding-top:10px;
    padding-bottom:6px;
    margin-right: 1px;
}

.modal-body-alto {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 40px);
    overflow-y: auto;
}


.modal-buscar {
    width: 90%;

}

.modal-body-buscar {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 40px);
    /* height: 900px; /* altura del modal */
}


.gris{
    color:cornsilk;
}

.row-cero-margen{
  margin-left:1px;
  margin-right:2px;
}

.alin-der{
  text-align: right;
}
.alin-izq{
  text-align: left;
}
.alin-cen{
    text-align: center;
}

.centrar-div{
    display: flex;
    justify-content: center;
}
.btn{
    margin-left:4px;
    margin-right:4px;
}

.btn_procesar{
  display:flex;
  justify-content: center;
}

.botonera{
  float:left;
  position:relative;
  box-sizing: border-box;
  display:block;
  width:8.333333%;
  padding-left:2px;
  padding-right: 2px;
}

.pading_cero{
    padding-left:0px;
    padding-right:0px;
}

.ref_check{
    width:30px;
    height:20px;
}


</style>
@endsection

@section('contenido_titulo_pagina')
  <div class="row">
  <div class="col-sm-11" style="width:95%"><center><h2>VENTAS</h2></center></div>
  <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none"><img src="{{asset('storage/imagenes/foco-idea-web.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

  @section('mensajes')
    @include('fragm.mensajes')
  @endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
    <div class="btn-toolbar" role="toolbar" style="margin-bottom:3px;background-color:peachpuff">
        <div class="btn-group btn-group-sm mr-10" role="group">
            <button class="btn btn-danger" onclick="nueva_venta()">Nueva Venta</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="buscar_cliente()">Buscar Cliente</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="mostrar_cotizaciones()" ><small>Mostrar Cotizaciones</small></button>
            <button type="button" class="btn btn-success btn-sm" onclick="buscar_repuesto()" >Buscar Repuesto</button>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#repuesto-xpress-modal">Repuesto Xpress</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="abrir_referencias()">Referencias</button>
        </div>
        <div class="btn-group btn-group-sm ml-auto" role="group">
            <button type="button" class="btn btn-success" onclick="transferir_carrito()"><small>Transferir Carrito</small></button>
            <button type="button" class="btn btn-danger"  onclick="borrar_carritos_guardados()"><small>Borrar Carritos Guardados</small></button>
            <button type="button" class="btn btn-info" onclick="mostrar_nombres_carrito()" >Recuperar Carrito</button>
            <button type="button" class="btn btn-warning" onclick="poner_nombre_carrito()" >Guardar Carrito</button>
            <button type="button" class="btn btn-danger" onclick="borrar_carrito('actual')"><small>Borrar Carrito Actual</small></button>
        </div>
    </div>

<div class="row">
    <div class="col-sm-3" style="background-color: #cfcaff;height:60%">
        <div class="row row-15" id="el_cliente">
            <div id="mensaje-cliente"></div>
                <div class="col-sm-12">
                <input type="hidden" id="id_cliente" value="0">
                <p id="cliente_rut" style="margin-bottom:1px">RUT: </p>
                <p id="cliente_nombres" style="margin-bottom:1px">Cliente:</p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4" style="padding-left:2px"><strong>Documento:</strong></div>
            <div class="col-sm-4" class="form-check form-check-inline" style="padding-left:2px">
                <input type="radio" class="form-check-input" name="tipo_documento" id="radio_boleta" value="boleta" onclick="que_documento('boleta')" checked>
                <label for="radio_boleta" class="form-check-label">Boleta</label>
            </div>
            <div class="col-sm-4" class="form-check form-check-inline" style="padding-left:2px">
                <input type="radio" class="form-check-input" name="tipo_documento" id="radio_factura" value="factura" onclick="que_documento('factura')">
                <label for="radio_factura" class="form-check-label">Factura</label>
            </div>
        </div>
        <div class="row row-cero-margen">
            <div class="col-sm-4" class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="tipo_documento" id="radio_cotizacion" value="cotizacion" onclick="que_documento('cotizacion')">
                <label for="radio_cotizacion" class="form-check-label">Cotización</label>
            </div>
            <div class="col-sm-8" style="padding-left:2px">
            <input type="text" name="nombre_cotizacion" value="" id="nombre_cotizacion" maxlength="100" class="form-control-sm" style="width:90%" placeholder="Nombre Cotización" disabled>
            <input type="text" name="dias_expira" value="7" id="dias_expira" maxlength="2" class="form-control-sm" style="width:15%;display:none;" disabled>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><strong>Venta:</strong></div>
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="contado" id="contado" onclick="contado_credito(1)" checked><small>Contado</small></div>
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="credito" id="credito" onclick="contado_credito(2)" disabled><small>Crédito</small></div>
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="delivery" id="delivery" onclick="contado_credito(3)"><small>Delivery</small></div>
        </div>
        <fieldset id="zona_formas_pago">
            <div class="row row-15" id="formas_pago"> </div>
        </fieldset>

        <div class="row">
            <div class="col-sm-2"><button class="btn btn-success btn-sm form-control-sm" onclick="calcular_sumatoria()">Sumar</button></div>
            <div class="col-sm-8">
                <table border="0" class="table table-sm letra-chica">
                <tr><td width="50%" style="text-align:right;color:blue"><b>Total a Pagar:</b></td><td id="total_forma_pago">0</td></tr>
                </table>
            </div>
        </div>

        <div class="row" id="referencias">
            <b>REFERENCIAS:</b> Ninguna
        </div>
        <div class="btn_procesar">
            <button  class="btn btn-warning form-control-sm" onclick="confirmar_documento()">PROCESAR</button>
        </div>


    </div>

    <div class="col-sm-9">
    <div  id="carrito" style="background-color: #fffded">
        <p class="d-flex justify-content-center">Obteniendo carritoooo...</p>
        <hr>
    </div>
    </div>

</div>

<!-- Ventana modal BUSCAR REPUESTO -->
<div role="dialog" tabindex="-1" class="modal fade" id="buscar-repuesto-modal">
   <div class="modal-dialog modal-xl modal-ventas" role="document" >
     <div class="modal-content">

       <div class="modal-header modal-header-80"> <!-- CABECERA -->
        <div class="row" style="width:100%">
            <div class="col-2" style="padding-left:2px">
                <button class="btn btn-warning btn-sm" onclick="buscar()" style="height: 20px"><p id="txtBotonPanel">Ocultar Marcas y Modelos</p></button>
            </div>
            <div class="col-9"><p class="d-flex justify-content-center" id="buscar-repuesto-modal-titulo" style="color:white;font-weight:bold">BUSCAR REPUESTO</p></div>
            <div class="col-1" style="padding-right: 1px"><button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding-right: 1px"><span aria-hidden="true">×</span></button></div>
        </div>
        <div class="row" style="width:90%">
            <div class="col-2 alin-der" style="padding-right:2px;color:white">Buscar:</div>
            <div class="col-10" style="margin-bottom:5px;padding-left:2px">
                <input type="search" class="form-control" style="height:25px;" name="txtDescripcion" id="buscar_por_descripcion" placeholder="¿Qué desea encontrar?" onkeyup="enter_press(event,'d')" onclick="clic_en(1)">
            </div>
        </div>
       </div> <!-- FIN CABECERA -->

       <div class="modal-body modal-body-alto" style="padding-top: 0px;"> <!-- CONTENIDO -->
        <div class="row">
        <!-- Columna 1 : elegir -->
                <div class="col-sm-3" id="panel-buscar" style="background-color: #F2F5A9;">
                <div class="row row-25 " id="zona_elegir">
                    <!--
                    <strong>BUSCAR POR:</strong><br><button class="btn btn-success btn-sm" onclick="filtros()" style="height: 20px;padding-top:0px"><p id="pFiltros">Mostrar Filtros</p></button><br>
                -->
                    <div id="filtros" style="display:none"> <!-- FILTROS Inicia oculto -->
                        <div class="col-12 col-sm-12 col-md-12">
                        <input class="form-control-sm" type="checkbox" name="chkMedidas" id="chkMedidas"><small>Solo con Medidas</small>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12">
                        <input class="form-control-sm" type="checkbox" name="chkStock" id="chkStock"><small>Solo con Stock</small>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12" style="display:none">
                        <input class="form-control-sm" type="checkbox" name="chkStock" id="chkDescripcion"><small>No buscar en Descripción</small>
                        </div>
                    </div> <!-- fin div FILTROS -->

                <!--
                    <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom:5px;">
                        <input type="search" class="form-control" style="height:25px;" name="txtCodigoProveedor" id="buscar_por_codigo_proveedor" placeholder="Cod. Proveedor" onkeyup="enter_press(event,'p')" onclick="clic_en(2)">
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom:5px;">
                        <input type="search" class="form-control" style="height:25px;" name="txtOem" id="buscar_por_oem" placeholder="Cod. OEM" onkeyup="enter_press(event,'o')" onclick="clic_en(3)">
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom:5px;">
                        <input type="search" class="form-control" style="height:25px;" name="txtCodigoFabricante" id="buscar_por_codigo_fabricante" placeholder="Cod. Fabricante" onkeyup="enter_press(event,'f')" onclick="clic_en(7)">
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom:5px;">
                        <input type="search" class="form-control" style="height:25px;" name="txtMedidas" id="buscar_por_medidas" placeholder="Medidas" onkeyup="enter_press(event,'m')" onclick="clic_en(4)">
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom:5px;display:none">
                         <input type="search" class="form-control" style="height:25px;" name="txtDescripcion" id="buscar_por_codint" placeholder="Código Interno Pancho" onkeyup="enter_press(event,'c')" onclick="clic_en(6)">
                    </div>
                -->
                    <div class="col-12 col-sm-12 col-md-12" style="margin-bottom:5px;">
                        <button class="btn btn-info btn-md btn-block" style="height:25px; padding-top:2px" onclick="clic_en(5)">Volver a Abrir</button>
                    </div>
                </div> <!-- FIN zona_elegir -->
                <div class="row" >
                    <div class="col-sm-12" id="zona_familia"></div>
                </div>
                </div> <!-- FIN Columna 1 : elegir -->

        <!-- Columna 2 : grilla y detalles -->
                <div class="col-sm-9" id="grilla" style="background-color: #81BEF7;padding-left:0px;padding-right:0px">
                <div id="zona_grilla" style="height:300px"></div> <!-- fragm.ventas_repuestos.blade -->
                <div class="row row-cero-margen" id="zona_detalle">
                    <div class="row row-cero-margen" id="zona_detalle_fieldset" style="width:100%">
                        <div class="col-3" id="zona_fotos" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px"></div>
                        <div class="col-5" id="zona_similares" style="background-color: #b3e6ff;padding-left: 1px;padding-right: 1px"></div>
                        <div class="col-2" id="zona_oem" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px"></div>
                        <div class="col-2" id="zona_fab" style="background-color: #96dafe; padding-left: 1px;padding-right: 1px"></div>
                    </div>
                </div>

                </div><!-- FIN Columna 2 : grilla y detalles  -->

         </div> <!-- FIN de row principal -->
      </div> <!-- FIN DE modal-body CONTENIDO -->

       <div class="modal-footer" style="flex-direction:column; align-content: start"> <!-- PIE -->
            <div class="row" style="width:90%">
                <div class="col-sm-8 col-offset-4" id="mensajes-modal"></div>
            </div>
       </div> <!-- FIN MODAL-FOOTER -->
        </div> <!-- modal-content -->


   </div> <!-- modal-dialog -->
</div> <!-- Fin ventana modal -->

<!-- VENTANA MODAL BUSCAR CLIENTE"-->
<div role="dialog" tabindex="-1" class="modal fade" id="buscar-cliente-modal">
   <div class="modal-dialog" role="document" >
     <div class="modal-content">

        <div class="modal-header" style="height: 30px;padding-top:2px;padding-bottom:2px"> <!-- CABECERA -->
            <h5 class="modal-title" id="buscar-cliente-modal-titulo">ELEGIR CLIENTE</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div> <!-- FIN CABECERA -->

       <div class="modal-body" style="padding:2px"> <!-- CONTENIDO -->
            <div class="row" style="padding:0px">
                <div class="col-sm-8 form-check">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="buscapor" id="buscaxrut" checked="true">
                        <label class="form-check-label" for="buscaxrut">RUT</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="buscapor" id="buscaxnombres">
                        <label class="form-check-label" for="buscaxnombres">Nombres</label>
                    </div>
                </div>
            </div>
            <div class="row"  style="margin-left:5px">
              <input type="text" style="width:150px" onkeyup="enter_buscar(event)" placeholder="Ingrese búsqueda" id="buscado">
              <span><button onclick="buscar_clientes()" class="btn btn-info btn-sm">Buscar</button></span>
            </div>
            <div class="row mt-2" id="listar_clientes" style="margin: 0px"></div>
        </div>


      </div> <!-- FIN DE modal-body -->
     </div> <!-- modal-content -->
   </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL BUSCAR CLIENTE"-->


<!-- VENTANA MODAL VISTA-PDF"-->
<div role="dialog" tabindex="-1" class="modal fade" id="vista-pdf-modal">
    <div class="modal-dialog modal-lg" role="document" >
      <div class="modal-content">
          <div class="modal-header" style="height: 30px"> <!-- CABECERA -->
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <p class="d-flex justify-content-center modal-title" id="mod_titulo_header_pdf">PDF</p>
           </div> <!-- FIN CABECERA -->
        <div class="modal-body" style="height: 500px"> <!-- CONTENIDO -->
         <div id="aqui_pdf">
           <p>VISTA PDF MODAL</p>

         </div>
       </div> <!-- FIN DE modal-body -->
       <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div> <!-- modal-content -->
    </div> <!-- modal-dialog -->
 </div> <!-- FIN VENTANA MODAL VISTA-PDF"-->

<!-- Ventana modal ELEGIR RELACIONADO -->
<div role="dialog" tabindex="-1" class="modal fade" id="agregar-relacionado-modal">
  <div class="modal-dialog modal-lg" role="document" >
    <div class="modal-content">
      <div class="modal-header modal-header-40"> <!-- CABECERA -->
       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">X</span></button>
       <p class="d-flex justify-content-center" id="mod_titulo_header">AGREGAR REPUESTO RELACIONADO</p>
      </div> <!-- FIN CABECERA -->
      <div class="modal-body modal-body-alto"> <!-- CONTENIDO -->
       <div class="row">
        <div class="col-sm-12" id="zona_repuestos_relacionados"></div>
       </div>
       <div class="row row-40" id="zona_detalle">
        <div class="col-sm-4" id="zona_fotos_r" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px">
          fotos
        </div>
        <div class="col-sm-5" id="zona_similares_r" style="background-color: #b3e6ff;padding-left: 1px;padding-right: 1px">
          similares
        </div>
        <div class="col-sm-3" id="zona_oem_r" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px">
          oem
        </div>

      </div>
     </div> <!-- FIN DE modal-body CONTENIDO-->

      <div class="modal-footer"> <!-- PIE -->
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- Fin ventana modal relacionados-->

<!-- VENTANA MODAL BUSCAR POR MARCA Y MODELO"-->
<div role="dialog" tabindex="-1" class="modal fade" id="buscar-marcamodelo-modal">
  <div class="modal-dialog modal-xl" role="document" >
    <div class="modal-content">
        <div class="modal-header modal-header-40">
            <div class="row" style="width:100%">
                <div class="col-2"></div>
                <div class="col-9">
                    <p class="d-flex justify-content-center" id="mod_titulo_header">BUSCAR POR MARCA Y MODELO</p>
                </div>
                <div class="col-1" style="padding-right: 1px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding-right: 1px"><span aria-hidden="true">x</span></button>
                </div>
            </div>

        </div>
      <div class="modal-body modal-body-buscar"> <!-- CONTENIDO -->
        <div class="row">
            <div id="divmarcas" class="col-4 col-sm-4 col-md-4 col-lg-4">
            </div>
          <div id="divmodelos" class="col-8 col-sm-8 col-md-8 col-lg-8">
          </div>
      </div>
     </div> <!-- FIN DE modal-body -->
     <div class="modal-footer"> <!-- PIE -->
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    </div>
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL BUSCAR POR MARCA Y MODELO"-->

<!-- VENTANA MODAL FOTO GRANDE"-->
<div role="dialog" tabindex="-1" class="modal fade" id="foto-modal">
  <div class="modal-dialog" role="document" >
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="aqui_foto"></div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL FOTO GRANDE"-->

<!-- VENTANA MODAL PONER NOMBRE CARRITO"-->
<div role="dialog" tabindex="-1" class="modal fade" id="poner-nombre-carrito-modal">
  <div class="modal-dialog modal-sm" role="document" >
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="poner_nombre_carrito">
        <div class="row centrar-div">
             <h5 ><strong>GUARDAR CARRITO COMPRAS</strong></h5>
            <input type="text" id="nombre-carrito" size="20" maxlength="10" placeholder="Escriba el nombre">
        </div>
        <div class="row mt-2">
         <div class="col-6"><button class="btn btn-primary btn-sm float-right" onclick="verificar_nombre_carrito()">Guardar</button></div>
         <div class="col-6"><button class="btn btn-danger btn-sm float-left" data-dismiss="modal" aria-label="Close">Cancelar</button></div>
        </div>
       </div>
       <div id="confirmar" style="display:none">
        <h4 style="text-align: center;margin-bottom:1px;"><strong>Nombre de Carrito Existe.</strong></h4>
        <h4 style="text-align: center;margin-bottom:1px;margin-top:1px;"><strong>¿Desea Reemplazar Carrito?</strong></h4>
        <center><small><p style="color:red;font-style: italic">(No se puede deshacer...)</p></small></center>
         <div class="col-6"><button class="btn btn-primary btn-sm float-right" onclick="guardar_carrito_completo()">SI</button></div>
         <div class="col-6"><button class="btn btn-danger btn-sm float-left" data-dismiss="modal" aria-label="Close">NO</button></div>

         <br>
       </div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL PONER NOMBRE CARRITO"-->

<!-- VENTANA MODAL ELEGIR CARRITO"-->
<div role="dialog" tabindex="-1" class="modal fade" id="elegir-carrito-modal">
  <div class="modal-dialog modal-sm" role="document" >
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="elegir_carrito">
          <h5 style="text-align: center"><strong><p id="titulo_nombre_carrito">ELEGIR CARRITO GUARDADO</p></strong></h5>
          <div id="nombres-carrito"></div>
       </div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL ELEGIR CARRITO"-->

<!-- VENTANA MODAL MOSTRAR COTIZACIONES"-->
<div role="dialog" tabindex="-1" class="modal fade" id="mostrar-cotizaciones-modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="mostrar_cotizaciones">
          <h4 style="text-align: center; color:blue"><strong><p id="titulo-listado-cotizaciones">COTIZACIONES VIGENTES</p></strong></h4>
          <h5 style="text-align: center; color:slategrey "><strong><p id="subtitulo-listado-cotizaciones"></p></strong></h5>
          <div id="listado_cotizaciones"></div>
       </div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL MOSTRAR COTIZACIONES"-->

<!-- Repuesto Xpress MOdal -->
<div class="modal fade" tabindex="-1" role="dialog" id="repuesto-xpress-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Repuesto Xpress</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group-sm">
                <div class="col-6">
                    <input type="text" name="" id="xpress-codigo" placeholder="Cód. Ref." style="width:50%">&nbsp;<p style="display:inline;color:blue;font-style:italic;font-size:12px">opcional</p>
                </div>
                <div class="col-12">
                    <input type="text" name="" id="xpress-descripcion" placeholder="Descripción" style="width:100%">
                </div>
                <div class="col-3">
                    <input type="text" name="" id="xpress-cantidad" placeholder="Cantidad" style="width:100%">
                </div>
                <div class="col-6">
                    <input type="text" name="" id="xpress-precio" placeholder="Precio Unitario" style="width:50%" data-toggle="tooltip" data-placement="left" title="Sin puntos ni comas, sólo números">&nbsp;<p style="display:inline;color:blue;font-style:italic;font-size:12px">incluye IVA</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="agregar_repuesto_xpress()">Agregar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div> <!-- FIN Repuesto Xpress MOdal -->

<!-- MODAL CLIENTE XPRESS -->
<div class="modal" tabindex="-1" id="cliente-xpress-modal" data-backdrop="static" data-keyboard="false" style="position:relative">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cliente Xpress</h5>
        </div>
        <div class="modal-body">
            <!-- ESTE MISMO CODIGO ESTA EN EL MODAL DE PROCESAR ENVIO MAS ABAJO -->
          <div class="form-group">
            <label for="dcmto">Documento:</label>
            <input class="form-control form-control-sm" type="text" id="dcmto" style="width:150px" maxlength="20">
          </div>
          <fieldset class="form-group referencia1" id="cliente_xpress" >
              <input class="form-control form-control-sm ref_razon" style="width:100px" type="text" id="cliente_xpress_rut" placeholder="rut">
              <input class="form-control form-control-sm ref_razon" type="text" id="cliente_xpress_nomape" placeholder="nombres y apellidos">
              <input class="form-control form-control-sm ref_razon" style="width:100px" type="text" id="cliente_xpress_celular" placeholder="Celular">
              <input class="form-control form-control-sm ref_razon" type="email" id="cliente_xpress_correo" placeholder="Correo Electrónico" title="Holitaaas...">
          </fieldset>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="cliente_xpress_cancelar()">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="cliente_xpress_guardar()">Guardar</button>
        </div>
      </div>
    </div>
  </div> <!-- FIN MODAL CLIENTE XPRESS -->

  <!-- MODAL PROCESAR ENVIO -->
<div class="modal" tabindex="-1" id="procesar-envio-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="titulo_procesar_envio_modal"></h5>
        </div>
        <div class="modal-body">
            <h3 id="envio-txt">Generando XML...</h3>
            <button  class="btn btn-info form-control-sm" onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>
            <button  class="btn btn-warning form-control-sm" disabled onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>
            <button  class="btn btn-success form-control-sm" disabled onclick="imprimir()" id="btn-imprimir">Imprimir</button>
            <div id="cliente_xpress_div" style="display:none">
                <!-- ESTE MISMO CODIGO ESTA EN EL MODAL DE CLIENTE XPRESS MAS ARRIBA -->
                <br>
                <hr>
                <h4>CLIENTE XPRESS</h4>
                <div class="form-group referencia1">
                    <label for="dcmto">Documento:</label>
                    <input class="form-control form-control-sm" type="text" id="dcmto_en_envio" style="width:150px" maxlength="20" disabled>
                    <button type="button" class="btn btn-primary form-control-sm" onclick="cliente_xpress_guardar()">Guardar Cliente Xpress</button>
                </div>
                <fieldset class="form-group referencia1" id="cliente_xpress" >
                    <input class="form-control form-control-sm ref_razon" style="width:120px" type="text" id="cliente_xpress_rut_en_envio" placeholder="RUT solo números" onKeyPress="return soloNumeros(event)">
                    <input class="form-control form-control-sm ref_razon" style="width:100px" type="text" id="cliente_xpress_nombres_en_envio" placeholder="nombres">
                    <input class="form-control form-control-sm ref_razon" style="width:100px" type="text" id="cliente_xpress_apellidos_en_envio" placeholder="apellidos">
                    <input class="form-control form-control-sm ref_razon" style="width:100px" type="text" id="cliente_xpress_celular_en_envio" placeholder="Celular">
                    <input class="form-control form-control-sm ref_razon" style="width:100px" type="email" id="cliente_xpress_correo_en_envio" placeholder="Correo Electrónico" title="Holitaaas...">
                </fieldset>
                <div id="cliente_xpress_mensaje"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button  class="btn btn-success form-control-sm" onclick="abrir_cliente_xpress()" id="boton_cliente_xpress_en_envio">Abrir Cliente Xpress</button>
            <button type="button" class="btn btn-danger" onclick="cerrar_procesar_envio()">Cerrar Envio</button>
        </div>
      </div>
    </div>
  </div> <!-- FIN MODAL PROCESAR ENVIO -->


<!-- MODAL REFERENCIAS -->
<div class="modal" tabindex="-1" id="referencias-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">REFERENCIAS</h5>
        </div>
        <div class="modal-body">
            <!-- el estilo de las clases referencia1,2 y 3 estan definidas en GuiaDespachoComponente por que lo compila hacia app.css laravel-mix -->
            <input type="checkbox" class="form-control form-control-sm ref_check" id="ref1_check" onclick="activar_referencia(1)">
            <fieldset class="form-group referencia1" id="referencia1" disabled>
                <select class="form-control form-control-sm ref_docu" id="ref1_docu">
                    <option value="0">Seleccione Documento</option>
                </select>
                <input class="form-control form-control-sm ref_folio" type="text" id="ref1_folio" placeholder="Folio">
                <input class="form-control form-control-sm ref_fecha" type="date" id="ref1_fecha">
                <input class="form-control form-control-sm ref_razon" type="text" id="ref1_razon" placeholder="Razón">
            </fieldset>
            <input type="checkbox" class="form-control form-control-sm ref_check" id="ref2_check" onclick="activar_referencia(2)">
            <fieldset class="form-group referencia2" id="referencia2" disabled>
                <select class="form-control form-control-sm ref_docu" id="ref2_docu">
                    <option value="0">Seleccione Documento</option>
                </select>
                <input class="form-control form-control-sm ref_folio" type="text" id="ref2_folio" placeholder="Folio">
                <input class="form-control form-control-sm ref_fecha" type="date" id="ref2_fecha">
                <input class="form-control form-control-sm ref_razon" type="text" id="ref2_razon" placeholder="Razón">
            </fieldset>
            <input type="checkbox" class="form-control form-control-sm ref_check" id="ref3_check" onclick="activar_referencia(3)">
            <fieldset class="form-group referencia3" id="referencia3" disabled>
                <select class="form-control form-control-sm ref_docu" id="ref3_docu">
                    <option value="0">Seleccione Documento</option>
                </select>
                <input class="form-control form-control-sm ref_folio" type="text" id="ref3_folio" placeholder="Folio">
                <input class="form-control form-control-sm ref_fecha" type="date" id="ref3_fecha">
                <input class="form-control form-control-sm ref_razon" type="text" id="ref3_razon" placeholder="Razón">
            </fieldset>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="referencias_cancelar()">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="referencias_aceptar()">Aceptar</button>
        </div>
      </div>
    </div>
  </div> <!-- FIN MODAL REFERENCIAS -->


</div> <!-- fin container-fluid -->
@endsection
