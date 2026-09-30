@extends('plantillas.app')
@section('titulo','VENTAS')
@section('javascript')

<script type="text/javascript">
  //Variables globales
  var cliente=[];
  var modal_relacionado=false;
  var mostrar_panel_buscar=false;
  var modifikar_precio_panel=false;
  var id_precio_a_modifikar=0;
  var precio_a_modifikar=0;
  var TrackID=0;
  var referencia1=true;
  var referencia2=true;
  var referencia3=true;
  var ref1=[];
  var ref2=[];
  var ref3=[];
  var documento_procesado="Ninguno";
  var div_cliente_xpress_mostrar=false;
  var id_repuesto_codigo=0;
  var proceso_dte_actual = null;
  var alerta_sesion_dte_mostrada = false;
  var oferta_confirmada_en_proceso = false;

  //Para resaltar la fila seleccionada
  var ultimaFila=null;
  var colorOriginal;
  var editando_anio_similares=false;
  var antiguo_valor_anio_similar="";


  /*
    //inputmask no funciona, Inputmask is not defined en consola.
    var xpress_precio=document.getElementById("xpress-precio");
    var im=new Inputmask("999.999.999");
    im.mask(xpress_precio);
    */

    let valorAnterior = 0; // Variable para almacenar el valor anterior
      let sonido = new Audio('/notificaciones/campana.mp3'); // Ruta al sonido en public/

  setInterval(() => {
    console.log('revisando');
    let url = '/revisar_carrito';
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
        
      },
      success: function(resp){
        if(resp > 0){
          $('#campana').empty();
          $('#campana').append(`
          <a href='javascript:dame_carrito()'>
            <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
            <span class="badge badge-danger" style="font-size: 15px;">`+resp+` </span> 
           </a>
          `);
        }else{
          $('#campana').empty();
          $('#campana').append(`
            <img src="{{asset('storage/imagenes/foco-idea-web.png')}}" width="30px"/>
            <span class="badge badge-danger" style="font-size: 15px;">`+resp+` </span> 
          `);
        }
      },
      error: function(error){
        console.log(error);
      }
    })
  }, 15000);

  function espere(msg){
                Vue.swal({
                    icon:'info',
                    text: msg
                });
  }

  

  function formatear_error(error){
    let max=300;
    let rpta=error.substring(0,max);
    return rpta;
  }

  function limpiar_monto(valor){
    let str = String(valor ?? '').trim();

    if(str === ''){
      return 0;
    }

    // Limpieza básica: deja números, punto, coma y signo negativo.
    str = str.replace(/[^0-9.,-]/g, '');

    if(str.includes(',')){
      // Formato chileno/europeo: 1.234,56 -> 1234.56
      str = str.replace(/\./g, '').replace(',', '.');
    }else{
      let partes = str.split('.');

      if(partes.length > 2){
        // Múltiples puntos: 1.234.567 -> 1234567
        str = str.replace(/\./g, '');
      }else if(partes.length === 2){
        let decimales = partes[1];

        if(decimales.length <= 2){
          // Decimal: 76000.00 -> 76000
        }else{
          // Miles: 76.000 -> 76000
          str = str.replace(/\./g, '');
        }
      }
    }

    return Math.round(parseFloat(str) || 0);
  }

  function formatear_miles(){
    /*
    let prz=document.getElementById("xpress-precio");
    let prezio_original=prz.value.trim();
    let prezio_temp=prezio_original.replace(/\./g,"");

    console.log(prezio_temp);
    */
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
    if(e.key=='Enter'){
        buscar_clientes();
    }
  }

  function enter_buscar_repuesto_xpress(e){
    if(e.key=='Enter'){
        buscar_repuesto_xpress();
    }
  }

  function enter_agregar_carrito(e,idrep){
    if(e.key=='Enter'){
        agregar_carrito(idrep);
    }
  }

  function enter_codigo(e){
    if(e.key=='Enter' || e.key=='Tab'){ //enter o tab(no funciona)
        buscar_por_codigo_interno();
    }
  }

  function enter_cant(e){
    if(e.key=='Enter' || e.key=='Tab'){ //enter o tab(no funciona)
        agregar_carrito(id_repuesto_codigo,3);
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

  function actualizar_visibilidad_cliente(){
    const idCliente = Number($('#id_cliente').val() || 0);
    const tieneCliente = idCliente > 0;

    $('#cliente_sin_seleccionar').toggle(!tieneCliente);
    $('#cliente_seleccionado').toggle(tieneCliente);
  }

  $(function(){
    actualizar_visibilidad_cliente();
  });

  function limpiar_todo(){
    borrar_carrito('actual');
    id_repuesto_codigo=0;
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
    actualizar_visibilidad_cliente();
    $("#cliente_rut").html("RUT: ");
    $("#cliente_nombres").html("Cliente: ");
    $("#cliente_direccion").html("Direccion: ");
    $("#cliente_giro").html("Giro: ");
    $("#total_forma_pago").html("0");
    contado_credito(1); //para que reactive las formas de pago
    document.getElementById("credito").checked=false;
    document.getElementById("credito").disabled=true;
    document.getElementById("contado").checked=true;
    document.getElementById("nombre_cotizacion").value="";
    document.getElementById("nombre_cotizacion").disabled=true;
    documento_procesado="Ninguno";
    document.getElementById("input_codigo").value="";
    document.getElementById("input_cantidad_footer").value="";
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
      var texto=_descripcion.value.trim();
        if(texto.length==0){
            Vue.swal({
                title: 'SI SERÁS SI SERÁS...',
                text: "Escribe algo para buscar poooh!!",
                icon: 'error',
            });
            return false;
        }


      var _donde=_descripcion.placeholder;
      var descripcion=valor+texto;
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
        console.log(formatear_error(error.responseText));
        // Vue.swal({
        //     title: 'ERROR '+error.status,
        //     text: formatear_error(error.responseText),
        //     icon: 'error',
        // });
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
        console.log(formatear_error(error.responseText));
        // Vue.swal({
        //     title: 'ERROR',
        //     text: formatear_error(error.responseText),
        //     icon: 'error',
        //     });
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
      var codint=document.getElementById("input_codigo").value.trim();
      var url_buscar='{{url("ventas/buscarcodint")}}'+'/'+codint;


      $.ajax({
       type:'GET',
       beforeSend: function () {
        console.log("buscando "+codint);
      },
      url:url_buscar,
      success:function(resp){
        let r=JSON.parse(resp);
        $("#input_descripcion").html(r.descripcion);
        if(r.id_repuesto>0){
            $("#input_cantidad_footer").focus();
            id_repuesto_codigo=r.id_repuesto;
            //agregar_carrito(r.id_repuesto,3);
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

    function buscar_repuesto_xpress(){
      let quien = 1;
      let codigo_interno = document.getElementById('repuesto-xpress-codigo').value;
      if(codigo_interno.trim() == 0 || codigo_interno.value == ''){
        Vue.swal({
          icon:'error',
          text:'Debe ingresar un codigo interno'
        });
        return false;
      }

      let url = '/repuesto/buscarcodigo/'+quien+codigo_interno;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          $('#mensaje_modal').html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Buscando repuesto...');
          $('#btn_agregar_repuesto_buscado').prop('disabled', true);
        },
        success: function(resp){
          $('#mensaje_modal').html('<i class="fa-solid fa-circle-check text-success mr-1"></i> Repuesto encontrado');
          if(resp == -1){
            Vue.swal({
              icon:'error',
              text:'No se encontró el repuesto',
            });
            return false;
          }
          let r = JSON.parse(resp[0]);
          
          let locales = resp[2];
          $('#informacion_repuesto').empty();
          r.forEach(e=>{
            var fechaInicio = new Date(e.fecha_actualiza_precio).getTime();
            var Hoy=new Date();

            var diff = Hoy - fechaInicio;
            var dias = Math.round(diff/(1000*60*60*24));
            var clase;
            if(dias < 30){
              clase = 'bg-success';
            }else if(dias > 30 && dias < 60){
              clase = 'bg-warning';
            }else{
              clase = 'bg-danger text-white';
            }
            const construirUbicacion = (nombre, detalle, stock) => {
              const cantidadStock = Number(stock || 0);
              let claseStock = '';

              if(cantidadStock <= 0){
                claseStock = 'sin-stock';
              }else if(cantidadStock <= 3){
                claseStock = 'stock-bajo';
              }

              return `
                <div class="xpress-location-item">
                  <span class="xpress-location-name">${nombre}</span>
                  <span class="xpress-location-detail">${detalle || 'Sin ubicación registrada'}</span>
                  <span class="xpress-location-stock ${claseStock}">${cantidadStock}</span>
                </div>
              `;
            };

            const ubicacionesHtml = `
              <div class="xpress-location-list">
                ${construirUbicacion('Bodega', e.ubicacion, e.stock_actual)}
                ${construirUbicacion('Tienda', e.ubicacion_dos, e.stock_actual_dos)}
                ${construirUbicacion('CM', e.ubicacion_tres, e.stock_actual_tres)}
              </div>
            `;

            $('#informacion_repuesto').append(`
              <tr>
                <td class="buscar-xpress-description">${e.descripcion || 'Sin descripción'}</td>
                <td class="${clase} text-center font-weight-bold">${dias} días</td>
                <td>${ubicacionesHtml}</td>
                <td>
                  <input type="text"
                         placeholder="Cantidad"
                         id="input_cantidad"
                         inputmode="numeric"
                         autocomplete="off"
                         onkeypress="return soloNumeros(event)"
                         onkeyup="soloNumeros(event)">
                </td>
                <td><select id="select_locales"></select></td>
                <td class="buscar-xpress-price text-right">$ ${Number(e.precio_venta || 0).toLocaleString('es-CL')}</td>
              </tr>
            `);
            $('#btn_agregar_repuesto_buscado')
              .attr('onclick','agregar_repuesto_buscado('+e.id+')')
              .prop('disabled', false);
            $('#select_locales').empty();
            locales.forEach(local=> {
              $('#select_locales').append(`
              <option value='`+local.id+`'>`+local.local_nombre+` </option>
              `);
            })
            
          });
          
        },
        error: function(error){
          Vue.swal({
            icon:'error',
            text:error.responseText
          });
        }
      })
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
        console.log(formatear_error(error.responseText));
        // Vue.swal({
        //     title: 'ERROR',
        //     text: formatear_error(error.responseText),
        //     icon: 'error',
        //     });
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
        console.log(formatear_error(error.responseText));
        // Vue.swal({
        //     title: 'ERROR',
        //     text: formatear_error(error.responseText),
        //     icon: 'error',
        //     });
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
    let prec_e=document.getElementById("xpress-precio");
    let prec=0;
    if (prec_e.inputmask){
        prec=prec_e.inputmask.unmaskedvalue();
    }else{
        prec=prec_e.value.trim();
    }
    //let prec=prec_e.value.trim();

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
              let vc = verificar_que_carrito();
              if(vc){
                console.log('carrito_Transferido');
                agregar_carrito_transferido(idxpress,2);
              }else {
                console.log('carrito_Normal');
                agregar_carrito(idxpress,2);
              }
                
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
          console.log(formatear_error(error.responseText));
            // Vue.swal({
            // title: 'ERROR',
            // text: formatear_error(error.responseText),
            // icon: 'error',
            // });
            }
        });
}

  function agregar_carrito(id_rep,cual=1)
  {
    if(id_rep==0)
    {
        Vue.swal({
            text: 'No ingresó o no se encontró código de repuesto',
            position: 'center',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

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
        idlocal = 1;
    }

    if(cual==3){ // desde ingreso por código o búsqueda express
        const cantidadFooter = document.getElementById("input_cantidad_footer");
        const cantidadModal  = document.getElementById("input_cantidad");

        // En la vista principal existe input_cantidad_footer; en el modal express, input_cantidad.
        cant = cantidadFooter && !cantidadFooter.disabled
            ? cantidadFooter.value.trim()
            : (cantidadModal ? cantidadModal.value.trim() : '');

        idlocal = $('#select_locales').val() || 1;
    }

    if(cual == 4){
      var desde = 'saldopendiente';
      cant=document.getElementById("input_cantidad_pedido").value.trim();
      var saldo_pendiente = document.getElementById("input_saldopendiente_pedido").value.trim();
      var total_pedido = document.getElementById("input_total_pedido").value.trim();
      var rep = document.getElementById("input_repuesto_pedido").value.trim();
      idlocal = 1;
    }

    if(cual == 5){
      idlocal = document.getElementById("local_id_consignacion").value.trim();
      cant = document.getElementById("cantidad_consignacion").value.trim();
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
      
      var url="{{url('ventas/agregar_carrito')}}";
      if(cual == 4){
        var parametros={
          idcliente:idcliente,
          idrep:id_rep,
          idlocal:idlocal,
          cantidad:cant, 
          desde: desde, 
          saldo_pendiente: saldo_pendiente, 
          total: total_pedido, 
          repuesto: rep
        };
        
      }else{
        var parametros={idcliente:idcliente,idrep:id_rep,idlocal:idlocal,cantidad:cant};
      }
      
      console.log(parametros);
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
          console.log(resp);
          if(resp == 'OK'){
            Vue.swal({
              icon:'info',
              title:'En construccion'
            });
            return false;
          }

          if(resp == 'bujia_minimo'){
            Vue.swal({
              text:'La cantidad mínima para bujías es de 3 unidades',
              icon:'error'
            });
            return false;
          }

          if(resp == 'valvula_minimo'){
            Vue.swal({
              text:'La cantidad mínima para valvulas es de 4 unidades',
              icon:'error'
            });
            return false;
          }

          if(resp == 'error'){
            Vue.swal({
              text:'No hay stock en local seleccionado',
              icon:'error'
            });
            return false;
          }
          
          if(resp=="existe")
          {
            $("#mensajes-modal").html("Upss!!!");
            Vue.swal({
                    text: 'Repuesto ya esta en el carrito',
                    position: 'center',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
            let t=new Intl.NumberFormat('es-CL').format(resp);
            let total_carrito = Number(resp);
            
            $("#mensajes-modal").html("Total: "+t);
            
            // Verificar si hay un giftcard cargado
            let valor_giftcard = limpiar_monto($('#monto-6').val());
            
            // Si hay giftcard, calcular la diferencia
            if(valor_giftcard > 0){
              let diferencia = total_carrito - valor_giftcard;
              if(diferencia < 0) diferencia = 0; // No permitir negativo
              document.getElementById("monto-1").value = diferencia;
            } else {
              // Si no hay giftcard, asignar el total al efectivo
              document.getElementById("monto-1").value = total_carrito;
            }
            
            document.getElementById("buscar_por_descripcion").value="";
            dame_carrito();
            // Recalcular montos después de cargar el carrito
            setTimeout(function(){
              recalcular_montos_pago();
            }, 500);
            clic_en(1);
            $("#buscar-repuesto-modal").modal("hide");
            Vue.swal({
                text: 'Agregado',
                position: 'center',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("input_codigo").value="";
            document.getElementById("input_cantidad_footer").value="";
            // $("#input_descripcion").html("Descripción");
          }

            },
        error: function(error){
          console.log(formatear_error(error.responseText));
            // Vue.swal({
            // title: 'ERROR',
            // text: formatear_error(error.responseText),
            // icon: 'error',
            // });
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
    // hacer lo mismo pero con fetch
    fetch("{{url('ventas/dame_carrito')}}")
    .then(response => response.text())
    .then(data => {
      $('#carrito').html(data);
    })
    .catch(error => {
      $('#carrito').html(formatear_error(error.responseText));
    });
  }

  function dameInfoRepuestoModal(idRepuesto){
    
        var nuevaurl = "/repuesto/buscaridrep_html_carrito/"+idRepuesto;
        $.ajax({
            type:'get',
            url:nuevaurl,
            beforeSend: function(){
               espere('CARGANDO ...');
            },
            success: function(html){
              console.log(html);
                Vue.swal.close();
                $('#modalBodyInfoRepuesto').empty();
                $('#modalBodyInfoRepuesto').append(html);
            },
            error: function(error){
                console.log(error);
            }
        });
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
    var id_cliente = document.getElementById('id_cliente').value;
   
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
      var url='{{url("ventas")}}'+'/verificarnombrecarrito/'+nombre.value.trim()+'/'+id_cliente;
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
          console.log(formatear_error(error.responseText));
          // Vue.swal({
          //   title: 'ERROR',
          //   text: formatear_error(error.responseText),
          //   icon: 'error',
          //   });
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
            console.log(formatear_error(error.responseText));
            // Vue.swal({
            //     title: 'ERROR',
            //     text: formatear_error(error.responseText),
            //     icon: 'error',
            //     });
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
                    let cliente = resp[1];
                    $('#id_cliente').val(cliente.id);
                    actualizar_visibilidad_cliente();
                    $('#cliente_rut').empty();
                    $('#cliente_rut').append(cliente.rut);
                    $('#cliente_nombres').empty();
                    $('#cliente_nombres').append(cliente.nombres+" "+cliente.apellidos);
                    $('#cliente_direccion').empty();
                    $('#cliente_direccion').append(cliente.direccion);
                    $('#cliente_giro').empty();
                    $('#cliente_giro').append(cliente.giro);
                    $("#elegir-carrito-modal").modal("hide");
                    if(resp[0]=='OK')
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

  function borrar_carrito_transferido(cliente_id){
    let url = '/ventas/borrar_carrito_cliente/'+cliente_id;
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
        console.log();
      },
      success: function(resp){
        if(resp === 'OK'){
          
          dame_carrito();
          // $('#btn_procesar_').removeClass('d-block');
          // $('#btn_procesar_').addClass('d-none');
        }
      }, error: function(err){
        Vue.swal({
            icon:'error',
            title:'Error',
            text:err.responseText
          });
      }
    })
  }

  function mostrar_carrito_vacio_elegante(mensaje = 'Comience agregando productos para crear una nueva venta.')
  {
    $('#carrito').html(`
      <div class="venta-carrito-vacio">
        <div class="venta-carrito-vacio__icono">
          <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <h3 class="venta-carrito-vacio__titulo">Carrito vacío</h3>
        <p class="venta-carrito-vacio__texto">${mensaje}</p>
        <div class="venta-carrito-vacio__ayuda">
          <span><kbd>F3</kbd> Ingreso por código</span>
          <span><kbd>F4</kbd> Buscar repuesto</span>
        </div>
        <input type="hidden" id="items_carrito" value="0">
      </div>
    `);
  }

  function borrar_carritos_guardados()
  {
    borrar_carrito('guardados');
  }


  function borrar_carritos_transferidos(cliente_id)
  {
    borrar_carrito('transferidos');
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
        if(resp=='actual'){
            mostrar_carrito_vacio_elegante();
            $('#total_forma_pago').html("0");
            dame_formas_pago();
            habilitar_boton_procesar_dte();
        }
        if(resp=='guardados')
            Vue.swal({
                text: 'Carritos guardados borrados...',
                position: 'top-end',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        if(resp=='transferidos'){
            mostrar_carrito_vacio_elegante();
            $('#total_forma_pago').html("0");
            dame_formas_pago();
            habilitar_boton_procesar_dte();
        }
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
  let opt = $('#items_carrito').val();
  let id_cliente = 4;
 if(id_cliente == 0){
  Vue.swal({
            title: 'Info',
            text: 'Debe seleccionar un cliente',
            icon: 'info',
          });
  return false;
 }
  if(opt > 0){
    $.ajax({
    type:'get',
    url:'usuarios/dame_cajeros_disponibles',
    success: function(cajeros){
      console.log(cajeros);
      if(cajeros.length > 0){
        $("#transferir-carrito-modal").modal("show");
        $('#nombres-cajeros').empty();
       
        cajeros.forEach((cajero) => {
          $('#nombres-cajeros').append(`
          <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioCajero" id="flexRadioCajero`+cajero.id+`" value="`+cajero.id+`">
          <label class="form-check-label" for="flexRadioCajero">
           `+cajero.name+`
          </label>
          </div>
          
        `);
        });
        $('#nombres-cajeros').append(`
        <input type="text" class="form-control my-2" id="nombre_carrito_transferido" placeholder="Ingrese un nombre descriptivo para el carrito" />
        `);
      }else{
        Vue.swal({
            title: 'ERROR',
            text: 'No hay cajeros disponibles',
            icon: 'error',
            });
      }
      
    },
    error: function(e){
      Vue.swal({
            title: 'ERROR',
            text: formatear_error(e.responseText),
            icon: 'error',
            });
    }
  })
  }else{
    Vue.swal({
            title: 'ERROR',
            text: 'No hay carrito para transferir',
            icon: 'error',
            });
  }
  
 
}

function transferir_carrito_confirmar(){
  
  let cajero_id = $('input[name="flexRadioCajero"]:checked').val();
  //Se genera un cliente_id para manejar los carritos transferidos
  let cliente_id = Math.floor(Math.random() * 90000);
  let titulo = $('#nombre_carrito_transferido').val();
  if(titulo == '' ){
    titulo = 'sin nombre';
  }
  let url = '/ventas/transferir_carrito/'+cajero_id+'/'+cliente_id+'/'+titulo;
  
  if(cajero_id == '' || typeof cajero_id === 'undefined'){
    Vue.swal({
      icon:'error',
      title:'Debe seleccionar un cajero',
      position:'center',
      toast: true,
      timer: 3000,
      showConfirmButton: false
    });
    return false;
  }
  $.ajax({
    type:'get',
    url:url,
    beforeSend: function(){
      console.log('enviando ...');
    },
    success: function(value){
      console.log(value);
      if(value === 'OK'){
        Vue.swal({
            text: '¡Carrito transferido exitosamente!',
            position: 'top-end',
            icon: 'success',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        $("#transferir-carrito-modal").modal("hide");
        borrar_carrito('actual');
      }
    },
    error: function(e){
      console.log(e.responseText);
    }
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
            let precio_a_modificar=document.getElementById("precio_a_modificar");
            Inputmask({"mask":"$ 9.999.999",numericInput: true}).mask(precio_a_modificar);

            mostrar_panel_buscar=false;
            buscar();
            precio_a_modifikar=limpiar_monto(document.getElementById("pv-"+idrep).value);
            id_precio_a_modifikar=idrep;
            precio_a_modificar.value=precio_a_modifikar;
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
    let new_val_tmp=document.getElementById("precio_a_modificar");// .value.replace(/\./g,""));
    let new_val=0;
    if(new_val_tmp.inputmask){
        new_val=new_val_tmp.inputmask.unmaskedvalue();
    }else{
        new_val=limpiar_monto(new_val_tmp.value);
    }

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

  function verificar_que_carrito(){
    // preguntar si es carrito transferido
    let ct = $('#es_transferido').val();
    // si ct es distinto de undefined es porque es transferido
    if(ct != undefined){
      return true;
    } 
    return false;
  }

  function agregar_repuesto_buscado(idrep){
    
    
    cant=document.getElementById("input_cantidad").value.trim();
    if(cant.trim() == 0 || cant == ''){
          Vue.swal({
                text: 'Debe ingresar una cantidad',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
    }else{
          // preguntar si es carrito transferido
          let vc = verificar_que_carrito();
          if(vc){
            console.log('carrito_Transferido');
            agregar_carrito_transferido(idrep);
          }else {
            console.log('carrito_Normal');
            agregar_carrito(idrep,3);
          }
          
          $('#informacion_repuesto').empty();
          $('#repuesto-xpress-codigo').val('');
          $('#repuesto-xpress-codigo').focus();
          $('#buscar-xpress-modal').modal('hide');
    }
    
  }

  function recalcular_montos_pago(){
    // Obtener el total actual del carrito
    let total_input = document.getElementById("total_carrito");
    let total_carrito = 0;
    if(total_input){
      total_carrito = limpiar_monto(total_input.value);
    }
    
    // Verificar si hay un giftcard cargado
    let valor_giftcard = limpiar_monto($('#monto-6').val());
    console.log('Recalculando montos de pago... Total carrito: '+total_carrito+', Valor giftcard: '+valor_giftcard);
    // Si hay giftcard, calcular la diferencia para efectivo
    if(valor_giftcard > 0 && total_carrito > 0){
      console.log('Recalculando montos de pago... Total carrito: '+total_carrito+', Valor giftcard: '+valor_giftcard);
      let diferencia = total_carrito - valor_giftcard;
      if(diferencia < 0) diferencia = 0; // No permitir negativo
      document.getElementById("monto-1").value = diferencia;
    } else if(total_carrito > 0){
      // Si no hay giftcard, asignar el total al efectivo
      document.getElementById("monto-1").value = total_carrito;
    }
  }

  function recalcular_pagos_giftcard(){
    // Función auxiliar para limpiar valores formateados
    function limpiar_valor(valor){
      // Convierte a string y trimea
      let str = String(valor).trim();
      
      // Si tiene puntos Y comas, es formato europeo (1.234.567,89)
      // Si tiene solo puntos, puede ser decimal (76000.00) o miles (76.000)
      // Si tiene solo comas, es decimal (76000,00)
      
      // Detectar formato:
      // - Si tiene coma, la coma es el decimal y los puntos son miles
      // - Si solo tiene punto y hay solo 1-2 dígitos después, es decimal
      // - Si solo tiene punto y hay 3 dígitos después, es separador de miles
      
      if(str.includes(',')){
        // Formato con coma como decimal (1.234,56)
        str = str.replace(/\./g, '').replace(',', '.');
      } else {
        // Solo puntos o sin formato
        // Contar puntos
        let comaParts = str.split('.');
        if(comaParts.length > 2){
          // Múltiples puntos = separadores de miles (1.234.567)
          str = str.replace(/\./g, '');
        } else if(comaParts.length === 2){
          // Un punto: verificar si es decimal (<=2 dígitos después) o miles
          let afterDot = comaParts[1];
          if(afterDot.length <= 2){
            // Es decimal, mantener tal cual
            // str ya está bien
          } else {
            // Parece ser miles, eliminar el punto
            str = str.replace(/\./g, '');
          }
        }
      }
      
      let num = parseFloat(str) || 0;
      return num;
    }
    
    // Obtener el total del carrito
    let total_input = document.getElementById("total_carrito");
    let total_carrito = 0;
    
    if(total_input && total_input.value){
      total_carrito = limpiar_valor(total_input.value);
      console.log('Total carrito - Valor original:', total_input.value, '→ Valor limpio:', total_carrito);
    }
    
    // Obtener el valor del giftcard
    let monto6_input = document.getElementById("monto-6");
    let valor_giftcard = 0;
    if(monto6_input){
      valor_giftcard = limpiar_valor(monto6_input.value);
      console.log('Giftcard - Valor original:', monto6_input.value, '→ Valor limpio:', valor_giftcard);
    }
    
    console.log('VALORES FINALES - Total carrito: '+total_carrito+', Giftcard: '+valor_giftcard);
    
    if(valor_giftcard <= 0){
      Vue.swal({
        icon:'error',
        text:'No hay un giftcard cargado',
        toast: true,
        position: 'top-end',
        timer: 3000,
        showConfirmButton: false
      });
      return false;
    }
    
    if(total_carrito <= 0){
      Vue.swal({
        icon:'error',
        title:'Error',
        html:'El carrito está vacío o el total no se pudo obtener.<br>Total obtenido: '+total_carrito,
        toast: true,
        position: 'top-end',
        timer: 3000,
        showConfirmButton: false
      });
      return false;
    }
    
    // Calcular la diferencia para efectivo
    let diferencia = total_carrito - valor_giftcard;
    if(diferencia < 0) diferencia = 0;
    
    console.log('Diferencia calculada:', diferencia);
    
    // Actualizar monto-1 (efectivo) SIN formato
    let monto1_input = document.getElementById("monto-1");
    if(monto1_input){
      monto1_input.value = Math.round(diferencia);
      console.log('monto-1 actualizado a:', Math.round(diferencia));
    }
    $('#formita-1').prop('checked', diferencia > 0);
    
    // Formatear para mostrar
    let total_formateado = new Intl.NumberFormat('es-CL').format(Math.round(total_carrito));
    let giftcard_formateado = new Intl.NumberFormat('es-CL').format(Math.round(valor_giftcard));
    let diferencia_formateado = new Intl.NumberFormat('es-CL').format(Math.round(diferencia));
    
    // Validar que las formas de pago coincidan con el total
    let total_pagos = valor_giftcard + diferencia;
    if(Math.abs(total_pagos - total_carrito) <= 1){
      Vue.swal({
        icon:'success',
        title:'Éxito',
        html:'<b>Pagos recalculados correctamente:</b><br><br><b>Giftcard:</b> $'+giftcard_formateado+'<br><b>Efectivo:</b> $'+diferencia_formateado+'<br><hr><b>Total:</b> $'+total_formateado,
        toast: true,
        position: 'top-end',
        timer: 5000,
        showConfirmButton: false
      });
    } else {
      Vue.swal({
        icon:'warning',
        title:'Advertencia',
        html:'<b>Discrepancia detectada:</b><br><b>Total carrito:</b> $'+total_formateado+'<br><b>Suma de pagos:</b> $'+new Intl.NumberFormat('es-CL').format(Math.round(total_pagos)),
        toast: true,
        position: 'top-end',
        timer: 5000,
        showConfirmButton: false
      });
    }
  }

  function agregar_carrito_transferido(idrep, cual = 1){
    let url = '{{url("ventas/agregar_carrito_transferido")}}';
    if(cual == 1){
      var idlocal=$('#select_locales').val();
      var cant=document.getElementById("input_cantidad").value.trim();
    }else if(cual == 2){
      var idlocal = 1;
      var cant = document.getElementById("xpress-cantidad").value;
    }
    
    let cliente_id = $('#cliente_id').val();
    let carrito_id = $('#carrito_id').val();
    let data = {idrep: idrep, idlocal: idlocal, cantidad: cant, cliente_id: cliente_id, carrito_id: carrito_id};
    console.log(data);
    // headings para el ajax
    let headers = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};
    $.ajax({
      type:'POST',
      headers: headers,
      url:url,
      data:data,
      success:function(rpta){
        console.log(rpta);
        if(rpta == "error"){
          return Vue.swal({
                text: 'No hay stock en local seleccionado',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }
        let total = rpta;
        if(total != 0){
          abrir_carrito_transferido(cliente_id);
        }else{
          Vue.swal({
                text: 'No se pudo agregar el repuesto al carrito',
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
                text: 'No se pudo agregar el repuesto al carrito',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
      }
    });

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
    var dcto = 0;
    var m="";
    var d="formita-descuento";
    var dcto = limpiar_monto(document.getElementById(d).value);
    if(dcto >= 100){
      Vue.swal({
        icon:'error',
        text:'NO PUEDE INGRESAR UN PORCENTAJE MAYOR O IGUAL A 100',
        toast: true,
        timer: 3000,
        position: 'center',
        showConfirmButton:false
      });
      return false;
    }

    paguitos.each(function() {
      //Aqui se obtiene el valor del id de los pagos seleccionados
      m="monto-"+$(this).val();
      monto=limpiar_monto(document.getElementById(m).value);
      
      let dcto_x_item = Number((dcto / 100) * monto);
      console.log(monto - dcto_x_item);
      total_pago= total_pago + monto - dcto_x_item;
      //Le asignamos los nuevos valores a los pagos con el descuento para no descuadrar el reporte diario
      document.getElementById(m).value = monto - dcto_x_item;
    });
    var t=new Intl.NumberFormat('es-CL').format(total_pago);
    $("#total_forma_pago").html("<b><p style='color:blue'>"+t+"</p></b>");
    
    return total_pago;
  }

  

  function total_pagado()
  {
    var total_pago=calcular_sumatoria();

    //Comparar con el total del carrito
    let dcto = limpiar_monto(document.getElementById('formita-descuento').value); 
    let total_carrito=limpiar_monto(document.getElementById("total_carrito").value);
    // El descuento se divide por 100 para sacar el porcentaje y ese resultado se multiplica por el total del carrito.
    // Con esto calculamos el valor a restar con el descuento aplicado.
    // El resultado anterior se resta al total del carrito.
    let total_carrito_descuento = Number(total_carrito) - Number((dcto/100) * Number(total_carrito)); 
    let total_pagado=limpiar_monto(total_pago);
    let diferencia=Math.abs(total_pagado-total_carrito_descuento);

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
          console.log(formatear_error(error.responseText));
            // Vue.swal({
            //     title: 'ERROR',
            //     text: formatear_error(error.responseText),
            //     icon: 'error',
            // });
        }

      });

  }

  function activar_forma_pago(id){
    if((document.getElementById("formita-2").checked) && id== 2 ){
      //$('#modalNuevaFormaPagoCredito').modal('show'); // abrir
    }

    if((document.getElementById("formita-5").checked) && id==5 )
    {
      //$('#modalNuevaFormaPagoDebito').modal('show'); // abrir
    }
    
    // Manejar giftcard (id=6)
    if(id == 6){
      let giftcard_checked = document.getElementById("formita-6").checked;
      if(giftcard_checked){
        // Si activa el giftcard, habilitar el campo de monto y referencia.
        // El código puede venir desde el input o desde el hidden si ya fue cargado.
        let codigo_giftcard_activo = $('#numero_giftcard_cargado').val() || $('#input_codigo_giftcard').val() || '';
        $('#monto-6').prop('disabled', false);
        $('#referencia-6').prop('readonly', false);
        $('#referencia-6').prop('disabled', false);
        if($('#referencia-6').val().trim() === ''){
          $('#referencia-6').val(codigo_giftcard_activo ? 'Giftcard ' + codigo_giftcard_activo : 'Giftcard');
        }
        $('#referencia-6').focus();
        $('#input_codigo_giftcard').focus();
      } else {
        // Si desactiva el giftcard, limpiar y mostrar botón Sumar
        limpiar_giftcard();
        $('#btn_sumar').show();
      }
    }
    
    let dcto = document.getElementById('forma_descuento').checked;
    if((dcto)){
      if(id == 999){
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
                  document.getElementById("formita-descuento").disabled=!dcto;
                  document.getElementById("btn_aplicar_dcto").disabled=!dcto;
                }else{
                  Vue.swal({
                        title: 'ERROR',
                        text: 'Contraseña incorrecta',
                        icon: 'error',
                    });
                    $("#forma_descuento").prop("checked", !dcto);
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
          $("#forma_descuento").prop("checked", !dcto);
        }


    });
      }
    }else{
      let marca=document.getElementById("formita-"+id).checked;
      document.getElementById("monto-"+id).disabled=!marca;
      document.getElementById("referencia-"+id).disabled=!marca; 
    }
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

  function devolver_item_consignacion(id_repuesto, num_consignacion){
    
    let url = '/ventas/devolver_repuesto_valeconsignacion/'+num_consignacion+'/'+id_repuesto;
   
    $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
            espere('CARGANDO ...');
        },
        success: function(resp){
            console.log(resp);
            Vue.swal.close();
            $('#detalle_consignacion').empty();
            $('#detalle_consignacion').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
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
    actualizar_visibilidad_cliente();
    $("#cliente_rut").html(cliente['rut']);
    $('#cliente_direccion').html(cliente['direccion']);
    $('#cliente_giro').html(cliente['giro']);
    dame_giros(cliente.id);
    if(cliente['tipo_cliente']==0){
        $("#cliente_nombres").html(cliente['nombres']+" "+cliente['apellidos']);
    }
    if(cliente['tipo_cliente']==1){
        $("#cliente_nombres").html("Razón Social: <br>"+cliente['empresa']);
    }

    $("#buscar-cliente-modal").modal("hide");
    
    let transferido = $('#es_transferido').val();
      //Consultamos si el carrito viene de otro vendedor
      if(transferido == '1'){
          let numero_carro = $('#cliente_carrito_transferido').val();
          aplicar_descuentos_carrito_transferido(cliente['id'], numero_carro);
      }else{
          aplicar_descuentos_cliente(cliente['id']);
      }
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

  function dame_giros(id_cliente){
    $.ajax({
      type:'get',
      url:'/clientes/dame_giros/'+id_cliente,
      success: function(giros){
        console.log(giros);
        if(giros.length > 0){
          $('#cliente_giro').empty();
          let html_ = '<select id="select_giros" class="w-100">';
          giros.forEach(giro => {
            html_+='<option>'+giro.giro+' </option>';
          });
          html_+='</select>';
          $('#cliente_giro').append(html_);
        }
      },
      error: function(error){
        console.log(error.responseText);
      }
    })
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
        //enviar_sii();
    }

    function cerrar_procesar_envio(){
        cliente_xpress_cancelar();
        $("#procesar-envio-modal").modal("hide");
        //abrir_cliente_xpress();
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
            // Se reemplaza el contenido para evitar referencias duplicadas
            // cuando el usuario abre y acepta el modal más de una vez.
            ref1 = [{
                docu: ref1_docu,
                folio: ref1_folio.trim(),
                fecha: ref1_fecha,
                razon: ref1_razon.trim()
            }];
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
            // Se reemplaza el contenido para evitar referencias duplicadas.
            ref2 = [{
                docu: ref2_docu,
                folio: ref2_folio.trim(),
                fecha: ref2_fecha,
                razon: ref2_razon.trim()
            }];
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
            // Se reemplaza el contenido para evitar referencias duplicadas.
            ref3 = [{
                docu: ref3_docu,
                folio: ref3_folio.trim(),
                fecha: ref3_fecha,
                razon: ref3_razon.trim()
            }];
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
              console.log(formatear_error(error.responseText));
                // Vue.swal({
                //     title: 'ERROR',
                //     text: formatear_error(error.responseText),
                //     icon: 'error',
                // });
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

    function consignar(){
      let idc=document.getElementById("id_cliente").value;

        nombre_consignacion=document.getElementById("nombre_consignacion").value;
        if(nombre_consignacion.length==0)
        {
            Vue.swal({
                text: 'Ingrese nombre de consignación',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            habilitar_boton_procesar_dte();
            return false;
        }

        let num_items_carrito=document.getElementById("items_carrito").value // esta en fragm.ventas_carrito.blade.php
        if(num_items_carrito==0)
        {
        Vue.swal({
                text: 'Carrito vacío. ¿Qué vas a consignar?',
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                });
            habilitar_boton_procesar_dte();
            return false;
        }
        let docu = $('input[name="tipo_documento"]:checked').val().trim();
        let venta="nada";
        let url="{{url('/ventas/consignar')}}";
        let parametros={docu:docu,idcliente:idc,nombre_consignacion};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            beforeSend: function () {
                bloquear_boton_procesar('GUARDANDO...');
            },
            url:url,
            data:parametros,
            success:function(resp){
                console.log(resp);
               
                let r=JSON.parse(resp);
                if(r.estado === 'OK'){
                    imprimir_consignacion(r.consignacion);
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error'
                    });
                }
                // borrar carrito 
                borrar_carrito('actual');
            },
            error: function(error){
                Vue.swal({
                    title: 'Error al procesar',
                    text: formatear_error(error.responseText),
                    icon: 'error'
                });
            },
            complete: function(){
                habilitar_boton_procesar_dte();
            }
        });
    }
    
function imprimir_consignacion(num_consignacion){

    let url = '/ventas/imprimir_consignacion/'+num_consignacion;
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
        Vue.swal({
          icon:'info',
          text:'CARGANDO'
        });
      },
      success: function(resp){
        console.log(resp);
        
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
        console.log(error.responseText);
      }
    });
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
            habilitar_boton_procesar_dte();
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
            habilitar_boton_procesar_dte();
            return false;
        }
        let docu = $('input[name="tipo_documento"]:checked').val().trim();
        let venta="nada";

        let si_carrito_transferido = $('#es_transferido').val();
        if(si_carrito_transferido == undefined){
          var es_transferido = false;
          var nombre_carrito_transferido = "";
        }else{
          var es_transferido = true;
          var nombre_carrito_transferido = document.getElementById('cliente_id').value;
        }
        
        let url="{{url('/ventas/cotizar')}}";
        
        let parametros={docu:docu,idcliente:idc,nombre_cotizacion, es_transferido, nombre_carrito_transferido};
        //console.log(parametros);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            beforeSend: function () {
                bloquear_boton_procesar('GUARDANDO...');
            },
            url:url,
            data:parametros,
            success:function(resp){
                console.log(resp);
                let r=JSON.parse(resp);
                if(r.estado === 'OK'){
                    imprimir_cotizacion(r.cotizacion);
                    borrar_carrito('actual');
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
                    title: 'Error al procesar',
                    text: formatear_error(error.responseText),
                    icon: 'error'
                });
            },
            complete: function(){
                habilitar_boton_procesar_dte();
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
                <td><button onclick="historial_cotizaciones()"   class="btn btn-secondary form-control-sm">HISTORIAL</button></td> \
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


    function normalizar_texto_historial(valor){
      return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
    }

    function formatear_fecha_historial(fecha){
      const texto = String(fecha ?? '').trim();

      if(!texto){
        return 'SIN FECHA';
      }

      const soloFecha = texto.substring(0, 10);
      const partes = soloFecha.split('-');

      if(partes.length === 3 && partes[0].length === 4){
        return partes[2] + '-' + partes[1] + '-' + partes[0];
      }

      return soloFecha;
    }

    function filtrar_historial(tipo){
      const esCotizacion = tipo === 'cotizaciones';
      const inputId = esCotizacion
        ? '#buscar_historial_cotizaciones'
        : '#buscar_historial_consignaciones';
      const tablaId = esCotizacion
        ? '#listado_cotizaciones_historial'
        : '#tbody_vales';
      const contadorId = esCotizacion
        ? '#contador_cotizaciones_visible'
        : '#contador_consignaciones_visible';

      const filtro = normalizar_texto_historial($(inputId).val());
      let visibles = 0;

      $(`${tablaId} tr[data-search]`).each(function(){
        const contenido = normalizar_texto_historial($(this).attr('data-search'));
        const mostrar = !filtro || contenido.includes(filtro);

        $(this).toggle(mostrar);

        if(mostrar){
          visibles++;
        }
      });

      $(contadorId).text(visibles);

      const emptyId = esCotizacion
        ? 'fila_sin_resultados_cotizaciones'
        : 'fila_sin_resultados_consignaciones';

      $('#' + emptyId).remove();

      if(visibles === 0 && $(`${tablaId} tr[data-search]`).length > 0){
        const colspan = esCotizacion ? 6 : 5;

        $(tablaId).append(`
          <tr id="${emptyId}">
            <td colspan="${colspan}" class="historial-empty">
              <i class="fa-solid fa-magnifying-glass"></i>
              No se encontraron coincidencias para la búsqueda.
            </td>
          </tr>
        `);
      }
    }

    function limpiar_filtro_historial(tipo){
      const inputId = tipo === 'cotizaciones'
        ? '#buscar_historial_cotizaciones'
        : '#buscar_historial_consignaciones';

      $(inputId).val('');
      filtrar_historial(tipo);
      $(inputId).focus();
    }

    function historial_consignaciones(){
      Vue.swal.close();

      $('#buscar_historial_consignaciones').val('');
      $('#contador_consignaciones_total').text('0');
      $('#contador_consignaciones_visible').text('0');

      $('#tbody_vales').html(`
        <tr>
          <td colspan="5" class="historial-empty">
            <i class="fa-solid fa-spinner fa-spin"></i>
            Cargando vales de consignación...
          </td>
        </tr>
      `);

      $('#modalHistorialConsignaciones').modal("show");

      $.ajax({
        type: 'GET',
        url: '/clientes/dame_vales_consignacion',
        cache: false,
        success: function(resp){
          let vales = resp;

          if(typeof resp === 'string'){
            try{
              vales = JSON.parse(resp);
            }catch(error){
              console.error('Respuesta inválida de consignaciones:', resp);
              vales = [];
            }
          }

          if(vales && !Array.isArray(vales) && Array.isArray(vales.data)){
            vales = vales.data;
          }

          if(!Array.isArray(vales) || vales.length === 0){
            $('#tbody_vales').html(`
              <tr>
                <td colspan="5" class="historial-empty">
                  <i class="fa-solid fa-box-open"></i>
                  No hay vales de consignación registrados.
                </td>
              </tr>
            `);

            $('#contador_consignaciones_total').text('0');
            $('#contador_consignaciones_visible').text('0');
            return;
          }

          let filas = '';

          vales.forEach(function(v){
            const numero = Number(v.num_consignacion || 0);
            const id = Number(v.id || 0);
            const fechaOriginal = v.fecha_emision || '';
            const fecha = formatear_fecha_historial(fechaOriginal);
            const nombre = String(v.nombre_consignacion || 'SIN NOMBRE');
            const pdf = String(v.url_pdf || '').replace(/"/g, '&quot;');

            const estado = Number(v.activo) === 0 ? 'PROCESADO' : 'PENDIENTE';
            const claseEstado = Number(v.activo) === 0 ? 'procesado' : 'pendiente';

            const textoBusqueda = [
              numero,
              fecha,
              nombre,
              estado
            ].join(' ');

            filas += `
              <tr data-search="${textoBusqueda.replace(/"/g, '&quot;')}">
                <td>
                  <a href="javascript:void(0)"
                     class="historial-number-link"
                     onclick="cargar_consignacion(${numero})"
                     title="Cargar consignación N° ${numero}">
                    <i class="fa-solid fa-hashtag"></i>${numero}
                  </a>
                </td>

                <td>
                  <span class="historial-date">
                    <i class="fa-regular fa-calendar"></i>
                    ${fecha}
                  </span>
                </td>

                <td>
                  <span class="historial-name" title="${nombre.replace(/"/g, '&quot;')}">
                    ${nombre}
                  </span>
                  <span class="historial-status ${claseEstado} mt-1">${estado}</span>
                </td>

                <td colspan="2">
                  <div class="historial-actions">
                    <button
                      type="button"
                      class="historial-action-btn historial-action-load"
                      onclick="cargar_consignacion(${numero})"
                      title="Cargar consignación">
                      <i class="fa-solid fa-cart-shopping"></i>
                    </button>

                    <button
                      type="button"
                      class="historial-action-btn historial-action-print"
                      onclick="imprimir_vale_consignacion_historial(&quot;${pdf}&quot;)"
                      title="Imprimir vale">
                      <i class="fa-solid fa-print"></i>
                    </button>

                    <button
                      type="button"
                      class="historial-action-btn historial-action-delete"
                      onclick="eliminar_consignacion(${id})"
                      title="Eliminar consignación">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `;
          });

          $('#tbody_vales').html(filas);
          $('#contador_consignaciones_total').text(vales.length);
          $('#contador_consignaciones_visible').text(vales.length);
        },
        error: function(error){
          console.error(error.responseText || error);

          $('#tbody_vales').html(`
            <tr>
              <td colspan="5" class="historial-empty text-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                No fue posible cargar los vales de consignación.
              </td>
            </tr>
          `);

          Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText || 'No fue posible consultar las consignaciones.'),
            icon: 'error'
          });
        }
      });
    }

  function eliminar_consignacion(num_consignacion){
    // Como puedo mostrar un mensaje de confirmación antes de eliminar?
    // Si el usuario dice que si, entonces se elimina, si dice que no, no se elimina.
    // Si se elimina, se debe actualizar la tabla de consignaciones.
    // Si no se elimina, no se hace nada.
    Vue.swal({
        title: '¿Está seguro de eliminar la consignación?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI, ELIMINAR',
        cancelButtonText: 'NO, CANCELAR'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminar_consignacion_si(num_consignacion);
        }
    }, (dismiss) => {
        if (dismiss === 'cancel') {
            Vue.swal({
                title: 'CANCELADO',
                text: 'No se ha eliminado la consignación.',
                icon: 'info',
            });
        }
    });
    console.log(num_consignacion);
    
  }

  function eliminar_consignacion_si(num_consignacion){
    let url = "/ventas/eliminar_consignacion/"+num_consignacion;
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
        console.log(resp);
            Vue.swal.close();
            //let r=JSON.parse(resp);
            if(resp=='OK'){
                Vue.swal({
                    title: 'EXITO',
                    text: 'Se ha eliminado con éxito la consignación.',
                    icon: 'success',
                });
                historial_consignaciones();
            }else{
                Vue.swal({
                    title: r.estado,
                    text: r.mensaje,
                    icon: 'error',
                });
            }
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function eliminar_consignacion_numero($numero){
    let url = "/ventas/eliminar_consignacion_numero/"+$numero;
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
        console.log(resp);
            Vue.swal.close();
            //let r=JSON.parse(resp);
            if(resp=='OK'){
                Vue.swal({
                    title: 'EXITO',
                    text: 'Se ha eliminado con éxito la consignación.',
                    icon: 'success',
                });
                historial_consignaciones();
            }else{
                Vue.swal({
                    title: r.estado,
                    text: r.mensaje,
                    icon: 'error',
                });
            }
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
  }

  function imprimir_vale_consignacion_historial(pdf){
    let url = '{{url("imprimir_pdf_vale_consignacion_historial")}}'+'/'+pdf;
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

function historial_cotizaciones(){
      Vue.swal.close();

      const url = '{{url("/ventas/damehistorialcotizaciones")}}/0';

      $('#buscar_historial_cotizaciones').val('');
      $('#contador_cotizaciones_total').text('0');
      $('#contador_cotizaciones_visible').text('0');

      $("#listado_cotizaciones_historial").html(`
        <tr>
          <td colspan="6" class="historial-empty">
            <i class="fa-solid fa-spinner fa-spin"></i>
            Cargando cotizaciones...
          </td>
        </tr>
      `);

      $("#mostrar-cotizaciones-modal_historial").modal("show");

      $.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(resp){
          console.log('Respuesta de cotizaciones:', resp);
          let user = {};
          let cotizaciones = [];

          try{
            if(Array.isArray(resp)){
              user = resp[1] || {};

              if(Array.isArray(resp[0])){
                cotizaciones = resp[0];
              }else if(typeof resp[0] === 'string'){
                cotizaciones = JSON.parse(resp[0] || '[]');
              }
            }else if(typeof resp === 'string'){
              const respuesta = JSON.parse(resp);

              if(Array.isArray(respuesta)){
                user = respuesta[1] || {};

                if(Array.isArray(respuesta[0])){
                  cotizaciones = respuesta[0];
                }else{
                  cotizaciones = JSON.parse(respuesta[0] || '[]');
                }
              }
            }else if(resp && typeof resp === 'object'){
              user = resp.user || {};
              cotizaciones = resp.cotizaciones || resp.data || [];
            }
          }catch(error){
            console.error('Respuesta inválida de cotizaciones:', error, resp);

            $("#listado_cotizaciones_historial").html(`
              <tr>
                <td colspan="6" class="historial-empty text-danger">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  La respuesta del servidor no tiene un formato válido.
                </td>
              </tr>
            `);
            return;
          }

          if(!Array.isArray(cotizaciones) || cotizaciones.length === 0){
            $("#listado_cotizaciones_historial").html(`
              <tr>
                <td colspan="6" class="historial-empty">
                  <i class="fa-solid fa-file-circle-xmark"></i>
                  No hay cotizaciones registradas.
                </td>
              </tr>
            `);

            $('#contador_cotizaciones_total').text('0');
            $('#contador_cotizaciones_visible').text('0');
            return;
          }

          let filas = '';

          cotizaciones.forEach(function(coti){
            const numero = Number(coti.num_cotizacion || 0);
            const fecha = formatear_fecha_historial(coti.fecha || '');
            const nombre = String(coti.nombre_cotizacion || 'SIN NOMBRE');
            const nombreCliente = String(coti.elcliente || 'NINGUNO');

            let estado = 'DESCONOCIDO';
            let claseEstado = 'procesado';

            if(Number(coti.activo) === 0){
              estado = 'RECHAZADO';
              claseEstado = 'rechazado';
            }else if(Number(coti.activo) === 1){
              estado = 'ESPERANDO';
              claseEstado = 'esperando';
            }else if(Number(coti.activo) === 2){
              estado = 'CONFIRMADO';
              claseEstado = 'confirmado';
            }

            const textoBusqueda = [
              numero,
              fecha,
              nombre,
              nombreCliente,
              estado
            ].join(' ');

            const botonCargar = estado === 'CONFIRMADO'
              ? `
                <button
                  type="button"
                  class="historial-action-btn historial-action-load"
                  onclick="cargar_cotizacion(${numero})"
                  title="Cargar cotización">
                  <i class="fa-solid fa-cart-shopping"></i>
                </button>
              `
              : '';

            const botonEliminar = Number(user.role_id) === 10
              ? `
                <button
                  type="button"
                  class="historial-action-btn historial-action-delete"
                  onclick="eliminar_cotizacion(${numero})"
                  title="Eliminar cotización">
                  <i class="fa-solid fa-trash"></i>
                </button>
              `
              : '';

            filas += `
              <tr data-search="${textoBusqueda.replace(/"/g, '&quot;')}">
                <td>
                  <a href="javascript:void(0)"
                     class="historial-number-link"
                     onclick="cargar_cotizacion(${numero})"
                     title="Cargar cotización N° ${numero}">
                    <i class="fa-solid fa-hashtag"></i>${numero}
                  </a>
                </td>

                <td>
                  <span class="historial-date">
                    <i class="fa-regular fa-calendar"></i>
                    ${fecha}
                  </span>
                </td>

                <td>
                  <span class="historial-name" title="${nombre.replace(/"/g, '&quot;')}">
                    ${nombre}
                  </span>
                </td>

                <td>
                  <span class="historial-client" title="${nombreCliente.replace(/"/g, '&quot;')}">
                    <i class="fa-regular fa-user mr-1"></i>${nombreCliente}
                  </span>
                </td>

                <td>
                  <span class="historial-status ${claseEstado}">
                    ${estado}
                  </span>
                </td>

                <td>
                  <div class="historial-actions">
                    ${botonCargar}

                    <button
                      type="button"
                      class="historial-action-btn historial-action-print"
                      onclick="imprimir_cotizacion(${numero})"
                      title="Imprimir cotización">
                      <i class="fa-solid fa-print"></i>
                    </button>

                    ${botonEliminar}
                  </div>
                </td>
              </tr>
            `;
          });

          $("#listado_cotizaciones_historial").html(filas);
          $('#contador_cotizaciones_total').text(cotizaciones.length);
          $('#contador_cotizaciones_visible').text(cotizaciones.length);
        },
        error: function(error){
          console.error(error.responseText || error);

          $("#listado_cotizaciones_historial").html(`
            <tr>
              <td colspan="6" class="historial-empty text-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                No fue posible cargar las cotizaciones.
              </td>
            </tr>
          `);

          Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText || 'No fue posible consultar las cotizaciones.'),
            icon: 'error'
          });
        }
      });
    }

    function eliminar_cotizacion(num_cot){
      // Como puedo mostrar un mensaje de confirmación antes de eliminar?
      // Si el usuario dice que si, entonces se elimina, si dice que no, no se elimina.
      // Si se elimina, se debe actualizar la tabla de consignaciones.
      // Si no se elimina, no se hace nada.
      Vue.swal({
          title: '¿Está seguro de eliminar la cotización?',
          text: "Esta acción no se puede deshacer.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'SI, ELIMINAR',
          cancelButtonText: 'NO, CANCELAR'
      }).then((result) => {
          if (result.isConfirmed) {
              eliminar_cotizacion_si(num_cot);
          }
      }, (dismiss) => {
          if (dismiss === 'cancel') {
              Vue.swal({
                  title: 'CANCELADO',
                  text: 'No se ha eliminado la cotización.',
                  icon: 'info',
              });
          }
      });
      console.log(num_cot);
    }

    function eliminar_cotizacion_si(num_cot){
      let url = "/ventas/eliminar_cotizacion/"+num_cot;
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
              //let r=JSON.parse(resp);
              if(resp.mensaje=='ok'){
                  Vue.swal({
                      title: 'EXITO',
                      text: 'Se ha eliminado con éxito la cotización.',
                      icon: 'success',
                  });
                  historial_cotizaciones();
              }else{
                  Vue.swal({
                      title: r.estado,
                      text: r.mensaje,
                      icon: 'error',
                  });
              }
          },
          error: function(error){
              console.log(error.responseText);
          }
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

  function cargar_consignacion(num_consignacion){
    var k=confirm("Si ACEPTA, se borrará el carrito actual");
    if(k)
    {
      var url="{{url('ventas/cargarconsignacion')}}"+"/"+num_consignacion;
      $.ajax({
       type:'GET',
       url:url,
       success:function(resp){
        //$("#modalHistorialConsignaciones").modal("hide");
        if(resp=='OK')
        {
          //Guardamos el numero de la consignacion como parametro para que cuando se genere la venta 
          //no se descuente del stock, ya que fue descontado al consignar

          $('#local_id_consignacion').val(num_consignacion);
          dame_carrito();
          cargar_vale_consignacion(num_consignacion);
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


function cargar_vale_consignacion(num_consignacion){
    let url = '/ventas/dame_vale_consignacion/'+num_consignacion;
    $.ajax({
        type:'get',
        url: url,
        success: function(resp){
            console.log(resp);
            //$('#exampleModal_historial').modal('hide');
            $('#detalle_consignacion').empty();
            $('#detalle_consignacion').append(resp);
        },
        error: function(error){
            console.log(error.responseText);
        }
    });
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
        $("#mostrar-cotizaciones-modal_historial").modal("hide");
        if(resp=='OK')
        {
          dame_carrito();
          document.body.style.paddingRight = "0px"; //para corregir error de bootstrap al cerrar modal
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
        console.log(resp);
            let r=JSON.parse(resp);
            if(r.estado=='OK'){
                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w=window.open(r.mensaje,'_blank',config);
                w.focus();
                
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
    let es_oferta = $('#es_oferta').val();
    //Debe afectar al carrito de compras
    var url='{{url("ventas/descuento_carrito")}}'+'/'+id_cliente;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#mensajes").html("Descuentos Clientes...");
          },
       url:url,
       success:function(resp){
        console.log(resp);
        //$("#mensajes").html("Cliente ID: "+resp);
        dame_carrito();
        
       },
        error: function(error){
          //$('#mensaje-cliente').html(formatear_error(error.responseText));
          console.log(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  function aplicar_descuentos_carrito_transferido(id_cliente, numero_carro)
  {
    
   
    let es_oferta = $('#es_oferta').val();
    //Debe afectar al carrito de compras
    var url='{{url("ventas/descuento_carrito_transferido")}}'+'/'+id_cliente+'/'+numero_carro;
    console.log(url);
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#mensajes").html("Descuentos Clientes...");
          },
       url:url,
       success:function(resp){
        console.log(resp);
        
        abrir_carrito_transferido(resp);
        
        
       },
        error: function(error){
          //$('#mensaje-cliente').html(formatear_error(error.responseText));
          console.log(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  function aplicar_descuentos_cliente_oferta(id_cliente)
  {
    let es_oferta = $('#es_oferta').val();
    //Debe afectar al carrito de compras
    var url='{{url("ventas/descuento_carrito")}}'+'/'+id_cliente;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#mensajes").html("Descuentos Clientes...");
          },
       url:url,
       success:function(resp){
        console.log('DESCONTANDO AL CLIENTE');
        //$("#mensajes").html("Cliente ID: "+resp);
        recargar_carrito();
       },
        error: function(error){
          //$('#mensaje-cliente').html(formatear_error(error.responseText));
          console.log(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  window.onload = function(e){
        var xpress_precio=document.getElementById("xpress-precio");
        
        Inputmask({"mask":"$ 999.999.999",numericInput: true}).mask(xpress_precio);

        if(window.$){ //para comprobar si esta cargado JQuery
            //console.log("JQuery cargado...");
//            Inputmask({"mask":"999.999.999"}).mask(xpress_precio);
            //$(xpress_precio).inputmask("999.999.999");
        }else{
            //console.log("JQuery NO cargado...");
        }

        $(document).ready(function(){ //cuando el documento html esté disponible
            //$('#xpress-precio').inputmask("999.999.999");  //static mask
            //$(selector).inputmask({"mask": "(999) 999-9999"}); //specifying options
            //$(selector).inputmask("9-a{1,3}9{1,3}"); //mask with dynamic syntax
            //console.log("xuxa...");
        });

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
      //aplicar_descuentos_carrito_transferido(0,0);
      
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

function escapar_html_resumen(valor){
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function texto_elemento_resumen(selector, valorDefecto=''){
    const elemento = document.querySelector(selector);

    if(!elemento){
        return valorDefecto;
    }

    return String(
        elemento.value !== undefined && elemento.value !== ''
            ? elemento.value
            : elemento.textContent
    ).trim() || valorDefecto;
}

function nombre_forma_pago_resumen(check){
    const id = check.id || '';
    const value = check.value || '';

    /*
     * Intenta recuperar el nombre desde el label vinculado al checkbox.
     */
    let label = id
        ? document.querySelector(`label[for="${id}"]`)
        : null;

    if(label){
        const texto = label.textContent.trim();

        if(texto){
            return texto;
        }
    }

    /*
     * Las formas de pago se cargan como tabla dinámica.
     * Normalmente el checkbox está en la primera celda de la fila.
     */
    const fila = check.closest('tr');

    if(fila){
        const celdas = fila.querySelectorAll('td');

        if(celdas.length > 0){
            const copia = celdas[0].cloneNode(true);

            copia.querySelectorAll('input, button, select, i').forEach(function(elemento){
                elemento.remove();
            });

            const textoFila = copia.textContent.replace(/\s+/g, ' ').trim();

            if(textoFila){
                return textoFila;
            }
        }
    }

    const nombresConocidos = {
        '1': 'Efectivo',
        '2': 'Tarjeta de crédito',
        '3': 'Cheque',
        '4': 'Transferencia bancaria',
        '5': 'Tarjeta de débito',
        '6': 'Giftcard',
        '7': 'Otro medio de pago'
    };

    return nombresConocidos[value] || `Forma de pago ${value}`;
}

function obtener_pagos_resumen(){
    const pagos = [];

    $('input[name="forma_pago"]:checked').each(function(){
        const idPago = String($(this).val() || '').trim();
        const inputMonto = document.getElementById('monto-' + idPago);
        const inputReferencia = document.getElementById('referencia-' + idPago);

        const monto = inputMonto
            ? limpiar_monto(inputMonto.value)
            : 0;

        const referencia = inputReferencia
            ? String(inputReferencia.value || '').trim()
            : '';

        pagos.push({
            nombre: nombre_forma_pago_resumen(this),
            monto: monto,
            referencia: referencia
        });
    });

    return pagos;
}

function construir_resumen_confirmacion(docu, mensaje){
    const formatoMoneda = new Intl.NumberFormat('es-CL');

    const totalCarrito = limpiar_monto(
        texto_elemento_resumen('#total_carrito', '0')
    );

    const totalPagos = obtener_pagos_resumen()
        .reduce((acumulado, pago) => acumulado + Number(pago.monto || 0), 0);

    const cantidadItems = Number(
        texto_elemento_resumen('#items_carrito', '0')
    ) || 0;

    const clienteNombre = texto_elemento_resumen(
        '#cliente_nombres',
        'Sin cliente seleccionado'
    )
        .replace(/^Cliente:\s*/i, '')
        .trim() || 'Sin cliente seleccionado';

    const clienteRut = texto_elemento_resumen('#cliente_rut', '')
        .replace(/^RUT:\s*/i, '')
        .trim();

    const idCliente = Number($('#id_cliente').val() || 0);
    const mostrarCliente = idCliente > 0;

    const modalidadSeleccionada = document.querySelector(
        'input[name="tipo_venta"]:checked'
    );

    const modalidad = modalidadSeleccionada
        ? modalidadSeleccionada.value
        : 'No definida';

    const descuento = limpiar_monto(
        texto_elemento_resumen('#formita-descuento', '0')
    );

    const vuelto = limpiar_monto(
        texto_elemento_resumen('#div_vuelto', '0')
    );

    const pagos = obtener_pagos_resumen();

    let documentoDetalle = mensaje;

    if(docu === 'COTIZACION'){
        const nombreCotizacion = texto_elemento_resumen(
            '#nombre_cotizacion',
            'Sin nombre'
        );

        documentoDetalle += ` · ${nombreCotizacion}`;
    }

    if(docu === 'CONSIGNACION'){
        const nombreConsignacion = texto_elemento_resumen(
            '#nombre_consignacion',
            'Sin nombre'
        );

        documentoDetalle += ` · ${nombreConsignacion}`;
    }

    let filasPagos = '';

    if(pagos.length > 0){
        filasPagos = pagos.map(function(pago){
            const referencia = pago.referencia
                ? `<div style="margin-top:2px;color:#64748b;font-size:11px;">
                       Ref: ${escapar_html_resumen(pago.referencia)}
                   </div>`
                : '';

            return `
                <div style="
                    display:flex;
                    align-items:flex-start;
                    justify-content:space-between;
                    gap:12px;
                    padding:8px 0;
                    border-bottom:1px solid #e2e8f0;
                ">
                    <div style="text-align:left;">
                        <div style="color:#334155;font-size:13px;font-weight:700;">
                            ${escapar_html_resumen(pago.nombre)}
                        </div>
                        ${referencia}
                    </div>

                    <strong style="color:#0f172a;white-space:nowrap;">
                        $${formatoMoneda.format(pago.monto)}
                    </strong>
                </div>
            `;
        }).join('');
    }else{
        filasPagos = `
            <div style="
                padding:10px;
                color:#64748b;
                background:#f8fafc;
                border-radius:8px;
                text-align:center;
                font-size:12px;
            ">
                No corresponde o no hay formas de pago seleccionadas.
            </div>
        `;
    }

    const alertaDiferencia = (
        (docu === 'BOLETA' || docu === 'FACTURA') &&
        Math.abs(totalPagos - totalCarrito) > 2
    )
        ? `
            <div style="
                margin-top:12px;
                padding:10px 12px;
                color:#92400e;
                background:#fffbeb;
                border:1px solid #fde68a;
                border-radius:8px;
                font-size:12px;
                text-align:left;
            ">
                <i class="fa-solid fa-triangle-exclamation"></i>
                La suma de los pagos es
                <strong>$${formatoMoneda.format(totalPagos)}</strong>
                y no coincide con el total del carrito.
            </div>
        `
        : '';

    return `
        <div style="
            margin-top:4px;
            padding:2px;
            color:#334155;
            font-family:inherit;
            text-align:left;
        ">
            <div style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:8px;
                margin-bottom:12px;
            ">
                <div style="
                    padding:10px;
                    background:#f8fafc;
                    border:1px solid #e2e8f0;
                    border-radius:9px;
                ">
                    <div style="
                        margin-bottom:3px;
                        color:#64748b;
                        font-size:10px;
                        font-weight:800;
                        text-transform:uppercase;
                    ">
                        Documento
                    </div>
                    <strong style="color:#1e3a8a;font-size:13px;">
                        ${escapar_html_resumen(documentoDetalle)}
                    </strong>
                </div>

                <div style="
                    padding:10px;
                    background:#f8fafc;
                    border:1px solid #e2e8f0;
                    border-radius:9px;
                ">
                    <div style="
                        margin-bottom:3px;
                        color:#64748b;
                        font-size:10px;
                        font-weight:800;
                        text-transform:uppercase;
                    ">
                        Modalidad
                    </div>
                    <strong style="color:#0f172a;font-size:13px;text-transform:capitalize;">
                        ${escapar_html_resumen(modalidad)}
                    </strong>
                </div>

                ${mostrarCliente
                    ? `
                        <div style="
                            grid-column:1 / -1;
                            padding:10px;
                            background:#f8fafc;
                            border:1px solid #e2e8f0;
                            border-radius:9px;
                        ">
                            <div style="
                                margin-bottom:3px;
                                color:#64748b;
                                font-size:10px;
                                font-weight:800;
                                text-transform:uppercase;
                            ">
                                Cliente
                            </div>
                            <strong style="color:#0f172a;font-size:13px;">
                                ${escapar_html_resumen(clienteNombre)}
                            </strong>
                            ${clienteRut
                                ? `<div style="margin-top:2px;color:#64748b;font-size:11px;">
                                       RUT: ${escapar_html_resumen(clienteRut)}
                                   </div>`
                                : ''}
                        </div>
                    `
                    : ''}
            </div>

            <div style="
                display:grid;
                grid-template-columns:repeat(3,1fr);
                gap:8px;
                margin-bottom:12px;
            ">
                <div style="
                    padding:10px;
                    background:#eff6ff;
                    border-radius:9px;
                    text-align:center;
                ">
                    <div style="color:#1d4ed8;font-size:10px;font-weight:800;text-transform:uppercase;">
                        Productos
                    </div>
                    <strong style="display:block;margin-top:3px;color:#1e3a8a;font-size:18px;">
                        ${cantidadItems}
                    </strong>
                </div>

                <div style="
                    padding:10px;
                    background:#fefce8;
                    border-radius:9px;
                    text-align:center;
                ">
                    <div style="color:#a16207;font-size:10px;font-weight:800;text-transform:uppercase;">
                        Descuento
                    </div>
                    <strong style="display:block;margin-top:3px;color:#854d0e;font-size:18px;">
                        ${descuento}%
                    </strong>
                </div>

                <div style="
                    padding:10px;
                    color:#fff;
                    background:linear-gradient(135deg,#166534,#15803d);
                    border-radius:9px;
                    text-align:center;
                ">
                    <div style="font-size:10px;font-weight:800;text-transform:uppercase;opacity:.85;">
                        Total
                    </div>
                    <strong style="display:block;margin-top:3px;font-size:18px;">
                        $${formatoMoneda.format(totalCarrito)}
                    </strong>
                </div>
            </div>

            <div style="
                margin-bottom:8px;
                color:#334155;
                font-size:12px;
                font-weight:800;
                text-transform:uppercase;
            ">
                Formas de pago
            </div>

            <div style="
                max-height:190px;
                overflow:auto;
                padding:0 11px;
                background:#fff;
                border:1px solid #e2e8f0;
                border-radius:9px;
            ">
                ${filasPagos}
            </div>

            ${vuelto > 0
                ? `
                    <div style="
                        display:flex;
                        justify-content:space-between;
                        margin-top:10px;
                        padding:9px 11px;
                        color:#075985;
                        background:#e0f2fe;
                        border-radius:8px;
                        font-size:12px;
                    ">
                        <span>Vuelto</span>
                        <strong>$${formatoMoneda.format(vuelto)}</strong>
                    </div>
                `
                : ''}

            ${alertaDiferencia}

            <div style="
                margin-top:12px;
                padding:10px 12px;
                color:#475569;
                background:#f8fafc;
                border-radius:8px;
                font-size:11px;
                text-align:center;
            ">
                Esta acción generará el documento y finalizará la venta.
            </div>
        </div>
    `;
}

function confirmar_documento(){

    oferta_confirmada_en_proceso = false;

    const documentoSeleccionado = $('input[name="tipo_documento"]:checked');

    if(documentoSeleccionado.length === 0){
        Vue.swal({
            icon: 'warning',
            title: 'Documento no seleccionado',
            text: 'Seleccione el tipo de documento antes de procesar.'
        });
        return false;
    }

    let docu = String(documentoSeleccionado.val() || '')
        .trim()
        .toUpperCase();

    let numeroGiftcard = $('#numero_giftcard_cargado').val();
    let mensaje = docu;

    if(numeroGiftcard && numeroGiftcard.trim() !== ''){
        mensaje = 'GIFTCARD';
    }

    const resumen = construir_resumen_confirmacion(docu, mensaje);

    const nombresDocumento = {
        BOLETA: 'BOLETA',
        FACTURA: 'FACTURA',
        COTIZACION: 'COTIZACIÓN',
        CONSIGNACION: 'CONSIGNACIÓN',
        GIFTCARD: 'GIFTCARD'
    };

    const nombreDocumento = nombresDocumento[mensaje.toUpperCase()]
        || mensaje.toUpperCase();

    Vue.swal({
        title: '¿DESEA PROCESAR ESTA ' + nombreDocumento + '?',
        html: resumen,
        icon: 'warning',
        width: 650,
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusCancel: true,
        confirmButtonColor: '#15803d',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-circle-check mr-1"></i> PROCESAR ' + nombreDocumento,
        cancelButtonText: '<i class="fa-solid fa-xmark mr-1"></i> CANCELAR',
        buttonsStyling: true
    }).then((result) => {

        if(result.isConfirmed){

            /*
             * No se bloquea aquí. Cada proceso bloquea el botón recién en el
             * beforeSend de su petición AJAX. Así, cualquier validación previa
             * puede terminar sin dejar el botón inutilizable.
             */

            if(docu === 'COTIZACION'){
                cotizar();
                return;
            }

            if(docu === 'CONSIGNACION'){
                consignar();
                return;
            }

            if(docu === 'BOLETA' || docu === 'FACTURA'){
                generar_xml();
                return;
            }

            habilitar_boton_procesar_dte();
        }

    });

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
      document.getElementById("nombre_consignacion").disabled=true;
      document.getElementById("nombre_consignacion").value="";
      //dame_formas_pago();
      document.getElementById("zona_formas_pago").disabled=false;
      //Si no existe el permiso de solo carritos transferidos se debe mostrar el boton de procesar
      let procesar = $('#procesar').val();
      if(procesar == 0){
        $('#btn_procesar_').removeClass('d-block');
        $('#btn_procesar_').addClass('d-none');
      }
       
    }

    if(tipo_doc=="cotizacion")
    {
      document.getElementById("nombre_cotizacion").disabled=false;
      document.getElementById("nombre_consignacion").disabled=true;
      document.getElementById("contado").disabled=true;
      document.getElementById("credito").disabled=true;
      document.getElementById("contado").checked=false;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=false;
      document.getElementById("nombre_cotizacion").focus();
      //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
      document.getElementById("zona_formas_pago").disabled=true;
      document.getElementById("nombre_consignacion").value="";
      let procesar = $('#procesar').val();
      if(procesar == 0){
        $('#btn_procesar_').removeClass('d-none');
        $('#btn_procesar_').addClass('d-block');
      }
    }

    if(tipo_doc=="consignacion"){
      document.getElementById("nombre_consignacion").disabled=false;
      document.getElementById("nombre_cotizacion").disabled=true;
      document.getElementById("contado").disabled=true;
      document.getElementById("credito").disabled=true;
      document.getElementById("contado").checked=false;
      document.getElementById("credito").checked=false;
      document.getElementById("dias_expira").disabled=false;
      document.getElementById("nombre_consignacion").focus();
      //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
      document.getElementById("zona_formas_pago").disabled=true;
      document.getElementById("nombre_cotizacion").value="";
      $('#btn_procesar_').removeClass('d-none');
      $('#btn_procesar_').addClass('d-block'); 
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

    if(tipo_doc=="consignacion")
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


function normalizar_respuesta_json(respuesta){
  if(respuesta === null || typeof respuesta === 'undefined'){
    return {};
  }

  if(typeof respuesta === 'object'){
    return respuesta;
  }

  if(typeof respuesta === 'string'){
    const texto = respuesta.trim();

    if(texto === ''){
      return {};
    }

    try{
      return JSON.parse(texto);
    }catch(error){
      console.error('Respuesta no válida recibida del servidor:', texto);
      return {
        estado: 'ERROR_RESPUESTA',
        mensaje: texto.substring(0, 500)
      };
    }
  }

  return {
    estado: 'ERROR_RESPUESTA',
    mensaje: 'El servidor devolvió una respuesta no reconocida.'
  };
}

function guardar_proceso_dte(proceso){
  if(!proceso || typeof proceso !== 'object'){
    return;
  }

  proceso_dte_actual = proceso;

  try{
    sessionStorage.setItem(
      'ventas_proceso_dte_actual',
      JSON.stringify(proceso)
    );
  }catch(error){
    console.warn('No se pudo guardar el proceso DTE en sessionStorage:', error);
  }
}

function obtener_proceso_dte(){
  if(proceso_dte_actual && typeof proceso_dte_actual === 'object'){
    return proceso_dte_actual;
  }

  try{
    const guardado = sessionStorage.getItem('ventas_proceso_dte_actual');

    if(guardado){
      proceso_dte_actual = JSON.parse(guardado);
      return proceso_dte_actual;
    }
  }catch(error){
    console.warn('No se pudo recuperar el proceso DTE:', error);
  }

  return null;
}

function limpiar_proceso_dte(){
  proceso_dte_actual = null;

  try{
    sessionStorage.removeItem('ventas_proceso_dte_actual');
  }catch(error){
    console.warn('No se pudo limpiar el proceso DTE:', error);
  }
}

function bloquear_boton_procesar(texto = 'PROCESANDO...'){
  const boton = $('#btn_procesar_');

  if(!boton.length){
    return;
  }

  if(!boton.data('texto-original')){
    boton.data('texto-original', boton.html());
  }

  boton
    .prop('disabled', true)
    .attr('aria-disabled', 'true')
    .attr('aria-busy', 'true')
    .addClass('disabled')
    .html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>' + texto);
}

function habilitar_boton_procesar_dte(){
  const boton = $('#btn_procesar_');

  if(!boton.length){
    return;
  }

  const textoOriginal = boton.data('texto-original');

  boton
    .prop('disabled', false)
    .removeAttr('disabled')
    .removeAttr('aria-disabled')
    .removeAttr('aria-busy')
    .removeClass('disabled');

  if(textoOriginal){
    boton.html(textoOriginal);
  }else{
    boton.html('<i class="fa-solid fa-circle-check mr-1"></i> PROCESAR VENTA');
  }
}

function marcar_dte_pendiente_envio(){
  const boton = $('#btn_procesar_');

  if(!boton.length){
    return;
  }

  boton
    .prop('disabled', true)
    .attr('aria-disabled', 'true')
    .removeAttr('aria-busy')
    .addClass('disabled')
    .html('<i class="fa-solid fa-file-invoice mr-1"></i> PENDIENTE DE ENVÍO AL SII');
}

function manejar_error_sesion_dte(xhr, respuesta){
  const datos = normalizar_respuesta_json(
    respuesta || (xhr ? xhr.responseJSON : null) || (xhr ? xhr.responseText : null)
  );

  const estadoHttp = xhr ? Number(xhr.status || 0) : 0;

  const sesionExpirada =
    estadoHttp === 401 ||
    estadoHttp === 419 ||
    datos.estado === 'SESION_EXPIRADA';

  if(!sesionExpirada){
    return false;
  }

  habilitar_boton_procesar_dte();

  if(alerta_sesion_dte_mostrada){
    return true;
  }

  alerta_sesion_dte_mostrada = true;

  Vue.swal({
    icon: 'warning',
    title: 'Sesión expirada',
    html: `
      <p>La sesión terminó por inactividad.</p>
      <p><strong>El proceso fue detenido para evitar duplicar la venta.</strong></p>
      <p>Debe iniciar sesión nuevamente.</p>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    confirmButtonText: 'Ir al inicio de sesión'
  }).then(() => {
    const loginUrl = datos.login_url || "{{ route('login') }}";
    window.location.href = loginUrl;
  });

  return true;
}

function mostrar_error_dte(respuesta, titulo='No fue posible procesar el documento'){
  const datos = normalizar_respuesta_json(respuesta);

  Vue.swal({
    icon: 'error',
    title: titulo,
    text: datos.mensaje || datos.detalle || 'Ocurrió un error desconocido.'
  });
}


/**
 * Devuelve las formas de pago que no pueden utilizarse cuando se mantiene
 * el precio de oferta. Se revisa tanto el checkbox como el monto ingresado.
 */
function obtener_pagos_no_permitidos_oferta()
{
  const formasNoPermitidas = [
    { id: 2, nombre: 'Tarjeta de crédito' },
    { id: 3, nombre: 'Cheque' },
    { id: 5, nombre: 'Tarjeta de débito' },
    { id: 6, nombre: 'Giftcard' },
    { id: 7, nombre: 'Otro medio de pago' }
  ];

  return formasNoPermitidas.filter(function(forma){
    const seleccionado = $('#formita-' + forma.id).is(':checked');
    const monto = limpiar_monto($('#monto-' + forma.id).val());
    return seleccionado || monto > 0;
  });
}

function limpiar_pagos_no_permitidos_oferta()
{
  if($('#formita-6').length && (
      $('#formita-6').is(':checked') ||
      limpiar_monto($('#monto-6').val()) > 0
  )){
    limpiar_giftcard();
  }

  $('#formita-2,#formita-3,#formita-5,#formita-6,#formita-7')
    .prop('checked', false)
    .prop('disabled', true);

  $('#monto-2,#monto-3,#monto-5,#monto-6,#monto-7')
    .val('')
    .prop('disabled', true);

  $('#referencia-2,#referencia-3,#referencia-5,#referencia-6,#referencia-7')
    .val('')
    .prop('disabled', true);
}

function pago_oferta_valido()
{
  const noPermitidos = obtener_pagos_no_permitidos_oferta();

  const efectivoSeleccionado = $('#formita-1').is(':checked') &&
      limpiar_monto($('#monto-1').val()) > 0;

  const transferenciaSeleccionada = $('#formita-4').is(':checked') &&
      limpiar_monto($('#monto-4').val()) > 0;

  return {
    valido: noPermitidos.length === 0 &&
      (efectivoSeleccionado || transferenciaSeleccionada),
    noPermitidos: noPermitidos,
    tienePagoPermitido: efectivoSeleccionado || transferenciaSeleccionada
  };
}

function marcar_decision_oferta(elementoOferta, decision)
{
  oferta_confirmada_en_proceso = true;

  if(elementoOferta && elementoOferta.length){
    elementoOferta.val('0').text('0');
  }

  $('#es_oferta').val('0');
  $('#confirmado').val(decision);
}

function aplicar_precio_normal_y_continuar(elementoOferta)
{
  const siCarritoTransferido = $('#es_transferido').val();

  if(siCarritoTransferido == 1){
    recargar_carrito_transferido();
  }else{
    recargar_carrito();
  }

  marcar_decision_oferta(elementoOferta, 'normal');

  setTimeout(function(){
    recalcular_montos_pago();
    generar_xml();
  }, 800);
}

function mantener_oferta_y_continuar(elementoOferta, cambiarSoloAEfectivo = false)
{
  limpiar_pagos_no_permitidos_oferta();

  const totalCarrito = limpiar_monto($('#total_carrito').val());

  /*
   * Cuando el usuario pulsa "CAMBIAR A EFECTIVO", la acción debe reemplazar
   * completamente la distribución anterior. No se conserva una transferencia
   * parcial, porque eso dejaría efectivo + transferencia por encima del total
   * o solamente la transferencia por debajo del total.
   */
  if(cambiarSoloAEfectivo){
    $('#formita-4').prop('checked', false);
    $('#monto-4').val('').prop('disabled', true);
    $('#referencia-4').val('').prop('disabled', true);

    $('#formita-1').prop('checked', true).prop('disabled', false);
    $('#monto-1').prop('disabled', false).val(totalCarrito);
    $('#referencia-1').prop('disabled', false).val('EFECTIVO');
  }else{
    const efectivoSeleccionado = $('#formita-1').is(':checked');
    const transferenciaSeleccionada = $('#formita-4').is(':checked');

    // Si todavía no existe un pago permitido, se utiliza efectivo por el total.
    if(!efectivoSeleccionado && !transferenciaSeleccionada){
      $('#formita-1').prop('checked', true).prop('disabled', false);
      $('#monto-1').prop('disabled', false).val(totalCarrito);
      $('#referencia-1').prop('disabled', false);

      if(String($('#referencia-1').val() || '').trim() === ''){
        $('#referencia-1').val('EFECTIVO');
      }
    }
  }

  calcular_sumatoria();
  marcar_decision_oferta(elementoOferta, 'ofertado');

  setTimeout(function(){
    generar_xml();
  }, 150);
}

function generar_xml()
{
  /*
   * El indicador #es_oferta viene dentro del HTML dinámico del carrito.
   * Se normaliza porque puede recibirse como 1, "1", true o "SI".
   */
  const elementoOferta = $('#carrito #es_oferta').first().length
      ? $('#carrito #es_oferta').first()
      : $('#es_oferta').first();

  const valorOferta = elementoOferta.length
      ? String(elementoOferta.val() ?? elementoOferta.text() ?? '').trim().toLowerCase()
      : '';

  const es_oferta = ['1', 'true', 'si', 'sí', 'oferta'].includes(valorOferta);
  const id_cliente = Number($('#id_cliente').val() || 0);

  console.log('Validación de oferta antes de procesar', {
      existeIndicador: elementoOferta.length > 0,
      valorOferta: valorOferta,
      esOferta: es_oferta,
      ofertaConfirmada: oferta_confirmada_en_proceso
  });

  // Solo se pregunta una vez por cada intento de procesamiento.
  if(es_oferta && !oferta_confirmada_en_proceso){
          Vue.swal({
                  title: '¿Desea mantener el precio de oferta?',
                  html: `
                    <div style="text-align:left;">
                      <p style="margin-bottom:10px;">
                        El carrito contiene uno o más productos con precio de oferta.
                      </p>
                      <div style="padding:10px 12px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb;color:#92400e;">
                        <strong>Importante:</strong> el precio de oferta solo se respeta pagando con
                        <strong>efectivo</strong> o <strong>transferencia bancaria</strong>.
                      </div>
                    </div>
                  `,
                  icon: 'question',
                  allowOutsideClick: false,
                  allowEscapeKey: false,
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: '<i class="fa-solid fa-tag mr-1"></i> MANTENER OFERTA',
                  cancelButtonText: '<i class="fa-solid fa-dollar-sign mr-1"></i> USAR PRECIO NORMAL'
                }).then((result) => {
                  if (result.isConfirmed) {
                    const pagosNoPermitidos = obtener_pagos_no_permitidos_oferta();

                    if(pagosNoPermitidos.length > 0){
                      const nombresPagos = pagosNoPermitidos
                        .map(function(forma){ return forma.nombre; })
                        .join(', ');

                      Vue.swal({
                        icon: 'warning',
                        title: 'Forma de pago no válida para la oferta',
                        html: `
                          <div style="text-align:left;">
                            <p>Actualmente tiene seleccionado o con monto ingresado:</p>
                            <p style="font-weight:700;color:#b91c1c;">${nombresPagos}</p>
                            <p>
                              Para mantener el precio de oferta debe pagar únicamente con
                              <strong>efectivo</strong> y/o <strong>transferencia bancaria</strong>.
                            </p>
                          </div>
                        `,
                        showCancelButton: true,
                        showDenyButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonColor: '#15803d',
                        denyButtonColor: '#2563eb',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa-solid fa-money-bill-wave mr-1"></i> CAMBIAR A EFECTIVO',
                        denyButtonText: '<i class="fa-solid fa-dollar-sign mr-1"></i> USAR PRECIO NORMAL',
                        cancelButtonText: '<i class="fa-solid fa-arrow-left mr-1"></i> VOLVER'
                      }).then(function(decisionPago){
                        if(decisionPago.isConfirmed){
                          mantener_oferta_y_continuar(elementoOferta, true);
                          return;
                        }

                        if(decisionPago.isDenied){
                          aplicar_precio_normal_y_continuar(elementoOferta);
                          return;
                        }

                        habilitar_boton_procesar_dte();
                      });

                      return;
                    }

                    const validacionPago = pago_oferta_valido();

                    if(!validacionPago.tienePagoPermitido){
                      Vue.swal({
                        icon: 'warning',
                        title: 'Seleccione una forma de pago válida',
                        text: 'Para mantener la oferta debe ingresar un monto en efectivo o transferencia bancaria.',
                        confirmButtonText: 'ENTENDIDO'
                      });
                      habilitar_boton_procesar_dte();
                      return;
                    }

                    mantener_oferta_y_continuar(elementoOferta);
                  }else{
                    aplicar_precio_normal_y_continuar(elementoOferta);
                  }

                });
          return false;
  }else{
    console.log('no tiene ofertas');
    
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
      habilitar_boton_procesar_dte();
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
      habilitar_boton_procesar_dte();
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
      habilitar_boton_procesar_dte();
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
            habilitar_boton_procesar_dte();
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
                habilitar_boton_procesar_dte();
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
                habilitar_boton_procesar_dte();
      return false; //sale del bucle jquery paguitos.each
            }

            id_forma_pago.push($(this).val());
            monto_forma_pago.push(monto);
            referencia_forma_pago.push(referencia_pago);
        }); // fin de paguitos

        if(error){
            return false; //sale de la funcion...
        }

        // Validar que el total pagado coincida con el total del carrito
        // Esto aplica para todas las ventas, incluyendo las que usan giftcard
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
            habilitar_boton_procesar_dte();
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

    let descuento = $('#formita-descuento').val();
    //Verificamos si esta en oferta
    let o = $('#es_oferta').val();
    let confirmado = $('#confirmado').val();
    let numero_giftcard = $('#numero_giftcard_cargado').val();

    /*
     * Última barrera antes de enviar la venta. Aunque algún evento reactive
     * una tarjeta u otro medio, una oferta nunca se procesa con ellos.
     */
    if(confirmado === 'ofertado'){
      const validacionFinalOferta = pago_oferta_valido();

      if(!validacionFinalOferta.valido){
        const nombresNoPermitidos = validacionFinalOferta.noPermitidos
          .map(function(forma){ return forma.nombre; })
          .join(', ');

        Vue.swal({
          icon: 'error',
          title: 'No se puede procesar la oferta',
          html: nombresNoPermitidos
            ? 'Elimine estas formas de pago: <strong>' + nombresNoPermitidos + '</strong>.<br>Solo se permite efectivo y/o transferencia.'
            : 'Debe ingresar el pago en <strong>efectivo</strong> y/o <strong>transferencia bancaria</strong>.',
          confirmButtonText: 'REVISAR PAGO'
        });

        habilitar_boton_procesar_dte();
        return false;
      }
    }

    let url="{{url('ventas/generarxml')}}";
    if((document.getElementById('vendedor_id')) !== null){
      var parametros={docu:docu,
                    idcliente:idc,
                    fmapago:venta,
                    cliente_id: document.getElementById('cliente_id').value,
                    vendedor_id: document.getElementById('vendedor_id').value,
                    ref1:JSON.stringify(ref1),
                    ref2:JSON.stringify(ref2),
                    ref3:JSON.stringify(ref3),
                    dcto: descuento,
                    oferta:o,
                    confirmado: confirmado,
                    numero_giftcard: numero_giftcard
                };
    }else{
      var parametros={docu:docu,
                    idcliente:idc,
                    fmapago:venta,
                    ref1:JSON.stringify(ref1),
                    ref2:JSON.stringify(ref2),
                    ref3:JSON.stringify(ref3),
                    dcto: descuento,
                    oferta:o,
                    confirmado: confirmado,
                    numero_giftcard: numero_giftcard
                };
    }

   
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
        type: 'POST',
        url: url,
        data: parametros,
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        beforeSend: function(){
          bloquear_boton_procesar('GENERANDO XML...');
          $("#mensajes").html("<b>Generando XML...</b>");
          $("#envio-txt").html(
            '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generando XML...'
          );
        },
        success: function(rs){
          const rpta = normalizar_respuesta_json(rs);

          console.log('Respuesta generar_xml:', rpta);

          if(rpta.estado === 'SESION_EXPIRADA'){
            manejar_error_sesion_dte(null, rpta);
            return;
          }

          if(rpta.estado === 'GENERADO'){
            const proceso = rpta.proceso_dte || {
              xml: rpta.xml || null,
              tipo_dte: rpta.tipo_dte || null,
              tipo_dte_nombre: rpta.tipo_dte_nombre || docu,
              folio_dte: rpta.folio_dte || null,
              idcliente: rpta.idcliente || idc
            };

            guardar_proceso_dte(proceso);

            $("#mensajes").html("Generando XML... <b>Listo</b>");
            $("#envio-txt").html(
              '<i class="fa-solid fa-circle-check text-success mr-1"></i>' +
              'XML generado correctamente.'
            );

            documento_procesado =
              rpta.mensaje ||
              ((String(docu).toUpperCase()) +
              (rpta.folio_dte ? ' N° ' + rpta.folio_dte : ''));

            document.getElementById("dcmto_en_envio").value = documento_procesado;

            abrir_procesar_envio();
            $('#btn-enviarsii').prop('disabled', false);

            /*
             * El carrito ya generó un DTE. Se mantiene protegido contra una
             * segunda generación, pero se informa claramente el motivo.
             */
            marcar_dte_pendiente_envio();
            return;
          }

          if(rpta.estado === 'GIFTCARD_OK'){
            limpiar_proceso_dte();

            $("#mensajes").html("<b>Pago con giftcard procesado.</b>");
            $("#envio-txt").html("<small>"+(rpta.mensaje || '')+"</small>");

            const carrito_id = $('#carrito_id').val();
            const cliente_id = $('#cliente_id').val();

            Vue.swal({
              icon: 'success',
              title: 'Giftcard procesada',
              html:
                (rpta.mensaje || 'Pago procesado correctamente.') +
                '<br><br><b>No se generó XML y no se enviará al SII.</b>',
              allowOutsideClick: false,
              allowEscapeKey: false
            });

            limpiar_cliente();

            if(carrito_id && cliente_id){
              borrar_carrito_transferido(cliente_id);
            }

            borrar_carrito('actual');

            if(document.getElementById("monto_efectivo")){
              document.getElementById("monto_efectivo").value = '';
            }

            reiniciar_descuento();
            limpiar_sesion();

            documento_procesado = "Giftcard";
            document.getElementById("dcmto_en_envio").value =
              "Giftcard sin XML/SII";

            habilitar_boton_procesar_dte();
            return;
          }

          habilitar_boton_procesar_dte();
          mostrar_error_dte(rpta, rpta.estado || 'Error al generar XML');
        },
        error: function(xhr){
          console.error('Error generar_xml:', xhr);

          if(manejar_error_sesion_dte(xhr)){
            return;
          }

          habilitar_boton_procesar_dte();

          const rpta = normalizar_respuesta_json(
            xhr.responseJSON || xhr.responseText
          );

          $("#envio-txt").html(
            '<span class="text-danger">' +
            (rpta.mensaje || 'No fue posible generar el XML.') +
            '</span>'
          );

          mostrar_error_dte(rpta, 'Error al generar XML');
        }
    });
  }
}

function recargar_carrito_transferido(){
  let cliente_carrito_transferido = $('#cliente_carrito_transferido').val();
  // Vue.swal({
  //   icon:'info',
  //   title:'EN CONSTRUCCION',
  //   text:'DISCULPE LAS MOLESTIAS'
  // });

  // return false;
   // Limpiar total
   $('#monto-1').val('');
    // Petición
    var url="{{url('ventas/recargar_carrito_transferido')}}"+"/"+cliente_carrito_transferido; //fragm.ventas_carrito_transferido
    
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#carrito").html("Buscando Carrito...");
          },
       url:url,
       success:function(carrito){
        console.log(carrito);
        $("#carrito").html(carrito);
       },
        error: function(error){
          $('#carrito').html(formatear_error(error.responseText));
        }

      }); //Fin petición
}

function recargar_carrito(){
  // Limpiar total
  $('#monto-1').val('');
  let id_cliente = $('#id_cliente').val();
    // Petición
    var url="{{url('ventas/recargar_carrito')}}"; //fragm.ventas_carrito
    
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

function enviar_sii(){

    if(document.getElementById("formita-6") && document.getElementById("formita-6").checked){
      Vue.swal({
        icon: 'info',
        title: 'Giftcard procesada',
        text: 'Esta venta fue pagada con giftcard. No corresponde generar XML ni enviar al SII.'
      });
      return false;
    }

    const formitaCredito = document.getElementById("formita-2");
    const formitaDebito = document.getElementById("formita-5");

    let marca_credito = formitaCredito ? formitaCredito.checked : false;
    let marca_debito = formitaDebito ? formitaDebito.checked : false;
    let consignacion = $('#local_id_consignacion').val() || '';

    let nfp_credito = 0;
    let nfp_debito = 0;

    if(marca_credito){
      nfp_credito =
        $('input:radio[name=flexRadioDefault_credito]:checked').val() || 0;
    }

    if(marca_debito){
      nfp_debito =
        $('input:radio[name=flexRadioDefault_debito]:checked').val() || 0;
    }

    let vendedor_id = $('#vendedor_id').val() || '';
    let carrito_id = $('#carrito_id').val() || '';
    let cliente_id = $('#cliente_id').val() || '';
    let num_abono = $('#input_num_abono').val() || '';

    const documentoSeleccionado =
      $('input[name="tipo_documento"]:checked');

    if(documentoSeleccionado.length === 0){
      Vue.swal({
        icon: 'warning',
        title: 'Documento no seleccionado',
        text: 'Debe seleccionar el documento antes de enviar al SII.'
      });
      return false;
    }

    let docu = String(documentoSeleccionado.val() || '').trim();
    let venta = '';

    if(docu !== 'cotizacion'){
      const ventaSeleccionada = $('input[name="tipo_venta"]:checked');

      if(ventaSeleccionada.length === 0){
        Vue.swal({
          icon: 'warning',
          title: 'Modalidad no seleccionada',
          text: 'Debe seleccionar la modalidad de venta.'
        });
        return false;
      }

      venta = String(ventaSeleccionada.val() || '').trim();
    }

    let id_forma_pago = [];
    let monto_forma_pago = [];
    let referencia_forma_pago = [];
    let error = false;
    let parametros = {};

    if(venta === 'contado'){
      const paguitos = $('input[name="forma_pago"]:checked');

      paguitos.each(function(){
        const idForma = String($(this).val() || '');
        const inputMonto = document.getElementById('monto-' + idForma);
        const inputReferencia = document.getElementById('referencia-' + idForma);

        const monto = inputMonto ? limpiar_monto(inputMonto.value) : 0;
        const referencia = inputReferencia
          ? String(inputReferencia.value || '').toUpperCase().trim()
          : '';

        let textoSeleccionado = 'FORMA DE PAGO';

        try{
          textoSeleccionado = nombre_forma_pago_resumen(this).toUpperCase();
        }catch(e){
          // Mantiene nombre genérico.
        }

        if(monto <= 0){
          Vue.swal({
            text: textoSeleccionado + ': monto vacío, cero o inválido.',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 4000
          });

          error = true;
          return false;
        }

        if(referencia.length === 0){
          Vue.swal({
            text: textoSeleccionado + ': debe ingresar una referencia.',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 4000
          });

          error = true;
          return false;
        }

        id_forma_pago.push(idForma);
        monto_forma_pago.push(monto);
        referencia_forma_pago.push(referencia);
      });

      if(error){
        return false;
      }

      const descuento = limpiar_monto($('#formita-descuento').val());

      parametros = {
        idcliente: $('#id_cliente').val() || cliente['id'] || 0,
        docu: docu,
        venta: venta,
        forma_pago: id_forma_pago,
        nfp_credito: nfp_credito,
        nfp_debito: nfp_debito,
        monto: monto_forma_pago,
        referencia: referencia_forma_pago,
        num_abono: num_abono,
        dcto: descuento,
        consignacion: consignacion
      };

      if(vendedor_id){
        parametros.vendedor_id = vendedor_id;
        parametros.carrito_id = carrito_id;
        parametros.cliente_id = cliente_id;
      }
    }else{
      parametros = {
        idcliente: $('#id_cliente').val() || cliente['id'] || 0,
        cliente_id: cliente_id,
        docu: docu,
        venta: venta,
        vendedor_id: vendedor_id
      };
    }

    /*
     * Se anexan los metadatos devueltos por generar_xml().
     * El controlador puede reconstruir el proceso si la sesión interna del
     * documento fue regenerada entre generar y enviar.
     */
    const proceso = obtener_proceso_dte();

    if(proceso){
      parametros.xml = proceso.xml || '';
      parametros.tipo_dte = proceso.tipo_dte || '';
      parametros.tipo_dte_nombre = proceso.tipo_dte_nombre || docu;
      parametros.folio_dte = proceso.folio_dte || '';
      parametros.idcliente = proceso.idcliente || parametros.idcliente;
    }

    let url = '{{url("ventas/enviarsii")}}';

    $.ajax({
      type: 'POST',
      url: url,
      data: parametros,
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      beforeSend: function(){
        $('#btn-enviarsii').prop('disabled', true);
        $("#mensajes").html("<b>Enviando al SII...</b>");
        $("#envio-txt").html(
          '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Enviando al SII...'
        );
      },
      success: function(rs){
        const rpta = normalizar_respuesta_json(rs);

        console.log('Respuesta enviar_sii:', rpta);

        if(rpta.estado === 'SESION_EXPIRADA'){
          manejar_error_sesion_dte(null, rpta);
          return;
        }

        if(rpta.estado === 'PROCESO_DTE_EXPIRADO'){
          $('#btn-enviarsii').prop('disabled', false);

          Vue.swal({
            icon: 'warning',
            title: 'Proceso DTE no disponible',
            html:
              '<p>'+(rpta.mensaje || 'No fue posible recuperar el XML generado.')+'</p>' +
              '<p><strong>No vuelva a enviar sin revisar si el folio ya fue utilizado.</strong></p>',
            confirmButtonText: 'Entendido'
          });

          $("#envio-txt").html(
            '<span class="text-warning">' +
            (rpta.mensaje || 'Proceso DTE expirado.') +
            '</span>'
          );
          return;
        }

        if(rpta.estado === 'OK'){
          $("#mensajes").html("Enviando al SII... <b>Recibido</b>");
          $("#envio-txt").html(
            "<small>" +
            (rpta.mensaje || 'Documento recibido por el SII.') +
            (rpta.trackid ? " TrackID: " + rpta.trackid : '') +
            "</small>"
          );

          const btnEstado = document.getElementById("btn-verestado");
          const btnImprimir = document.getElementById("btn-imprimir");

          if(btnEstado){
            btnEstado.disabled = false;
          }

          if(btnImprimir){
            btnImprimir.disabled = false;
          }

          TrackID = rpta.trackid || 0;
          limpiar_proceso_dte();
          limpiar_cliente();

          if(cliente_id){
            borrar_carrito_transferido(cliente_id);
          }

          borrar_carrito('actual');

          const efectivo = document.getElementById("monto_efectivo");

          if(efectivo){
            efectivo.value = '';
          }

          reiniciar_descuento();
          habilitar_boton_procesar_dte();

          if(consignacion !== ''){
            eliminar_consignacion_numero(consignacion);
          }

          const localConsignacion =
            document.getElementById("local_id_consignacion");

          if(localConsignacion){
            localConsignacion.value = '';
          }

          return;
        }

        $('#btn-enviarsii').prop('disabled', false);
        $("#mensajes").html(
          "Enviando al SII... <b>No recibido. Puede reintentar.</b>"
        );
        $("#envio-txt").html(
          "<small>" +
          (rpta.estado || 'ERROR') +
          ". " +
          (rpta.mensaje || 'No fue posible enviar el documento.') +
          "</small>"
        );

        TrackID = 0;
      },
      error: function(xhr){
        console.error('Error enviar_sii:', xhr);

        if(manejar_error_sesion_dte(xhr)){
          return;
        }

        $('#btn-enviarsii').prop('disabled', false);

        const rpta = normalizar_respuesta_json(
          xhr.responseJSON || xhr.responseText
        );

        $("#envio-txt").html(
          '<span class="text-danger">' +
          (rpta.mensaje || 'No fue posible enviar el documento al SII.') +
          '</span>'
        );

        mostrar_error_dte(rpta, 'Error al enviar al SII');
      }
    });
}

function limpiar_cliente(){
  $('#id_cliente').val(0);
  actualizar_visibilidad_cliente();
  $('#cliente_nombre').val('');
  $('#cliente_rut').val('');
  $('#cliente_direccion').val('');
  $('#cliente_giro').val('');
  limpiar_giftcard();
  limpiar_formas_pago();
}

function limpiar_giftcard(){
  $('#numero_giftcard_cargado').val('');
  $('#input_saldo_giftcard').val('');
  $('#input_codigo_giftcard').val('');
  $('#saldo_giftcard_valor').text('0');
  $('#div_saldo_giftcard').hide();
  $('#div_saldo_giftcard').removeClass('d-inline');
  // Limpiar forma de pago giftcard
  $('#formita-6').prop('checked', false);
  $('#monto-6').val('');
  $('#monto-6').prop('readonly', false);
  $('#monto-6').prop('disabled', true); // Deshabilitar cuando se limpia
  $('#referencia-6').val('');
  $('#referencia-6').prop('readonly', false);
  $('#referencia-6').prop('disabled', true); // Deshabilitar referencia también
  // Limpiar forma de pago efectivo también
  $('#formita-1').prop('checked', false);
  $('#monto-1').val('');
  // Ocultar botón de recalcular pagos giftcard y mostrar botón Sumar
  $('#btn_recalcular_giftcard').hide();
  $('#btn_sumar').show();
}

function limpiar_formas_pago(){
  // Limpiar todos los checks y montos de formas de pago
  $('input[name="forma_pago"]').each(function(){
    $(this).prop('checked', false);
    let id_forma = $(this).val();
    $('#monto-'+id_forma).val('');
    $('#monto-'+id_forma).prop('readonly', false);
    $('#referencia-'+id_forma).val('');
  });
}

function reiniciar_descuento(){
  $('#forma_descuento').prop('checked',false);
  $('#formita-descuento').val(0);
  $('#formita-descuento').attr('disabled','disabled');
  $('#btn_aplicar_dcto').attr('disabled','disabled');
}

function abrirModalPrecio(idrep){
  
  let url = '/repuesto/buscaridrep/'+idrep;

  $.ajax({
    type:'get',
    url: url,
    success: function(resp){
      
      let repuesto = resp;
      if(repuesto.precio_actualizado){
        var precio_real = repuesto.precio_actualizado;
      }else{
        var precio_real = repuesto.precio_venta - ((repuesto.porcentaje/100) * repuesto.precio_venta);
      }

      $('#exampleModal_precionormal').modal('show');
      $('#modal_body_info_repuesto').empty();
      $('#modal_body_info_repuesto').append(`
      <span class="badge badge-primary text-center" style="font-size: 15px;">Solo con efectivo o transferencia</span> <br>
      <span class="badge badge-secondary mt-2 mb-2" style="font-size: 14px;">Solo cambio o vale de mercaderia, sin devolución de dinero</span><br>
      <table class="table table-striped">
        <tr>
          <td>Descripción </td>
          <td>`+repuesto.descripcion+`</td>
        </tr>
        <tr>
          <td>Precio normal </td>
          <td>$ `+commaSeparateNumber(parseInt(repuesto.precio_venta).toFixed(0)) +`</td>
        </tr>
        <tr>
          <td>Precio ofertado </td>
          <td>$ `+commaSeparateNumber(parseInt(precio_real).toFixed(0)) +`</td>
        </tr>
        <tr>
          <td>Desde </td>
          <td>`+repuesto.desde+`</td>
        </tr>
        <tr>
          <td>Hasta </td>
          <td>`+repuesto.hasta+`</td>
        </tr>
      </table>
      `);
    },
    error: function(error){
      console.log(error.responseText);
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
                // document.getElementById("btn-enviarsii").disabled=true;
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

    var url='{{url("sii/verestado")}}'+"/"+tipoDTE+"&"+TrackID; //Controlador sii_controlador@ver_estadoUP
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
                // document.getElementById("btn-enviarsii").disabled=true;
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

  function borrar_item_carrito_transferido(item_id){
    var url='{{url("ventas")}}'+'/'+item_id+'/borrar_item_carrito_transferido'; //petición
      $.ajax({
       type:'GET',
       beforeSend: function () {
        //$("#carrito").html("Borrando Item...");
          },
       url:url,
       success:function(resp){
        
        let t=new Intl.NumberFormat('es-CL').format(resp);
        document.getElementsByName("forma_pago_monto")[0].value=t.replace(/\./g,"");
        let cliente_id = document.getElementById('cliente_id').value;
        abrir_carrito_transferido(cliente_id);
        //calcular_sumatoria();
       },
        error: function(error){
        $('#carrito').html(formatear_error(error.responseText));
        }

      }); //Fin petición
  }

  function abrir_carrito_transferido(cliente_id){
    //Se tienen que limpiar los descuentos al inicio
    
		let url = '/ventas/dame_carrito_transferido_cliente/'+cliente_id;
    $('#cliente_carrito_transferido').val(cliente_id);
    $.ajax({
      type:'get',
      url: url,
      success: function(html){
        //aplicar_descuentos_carrito_transferido(0,id_cliente);
        $('#btn_procesar_').removeClass('d-none');
        $('#btn_procesar_').addClass('d-block');
        //console.log(html);
        $('#carrito').empty();
        $('#carrito').append(html);
      },
      error: function(error){
        console.log(error.responseText);
      }
    })
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

  function agregar_abono_carrito(){
    let num_abono = $('#input_num_abono').val();
    if(num_abono == ''){
      Vue.swal({
        icon:'error',
        title:'error',
        text:'Ingrese codigo'
      });
      return false;
    }
   
    let url = '/ventas/dame_abono/'+num_abono;
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
       
        if(typeof(resp) === 'string'){
          Vue.swal({
            title:'Error',
            icon:'error',
            text:resp
          });
          return false;
        }
        let abono = resp[0];
        let abono_detalle = resp[1];
        abono_detalle.forEach(ab => {
         
          document.getElementById('input_cantidad_pedido').value = ab.cantidad;
          document.getElementById('input_saldopendiente_pedido').value = abono.saldo_pendiente;
          document.getElementById('input_total_pedido').value = abono.precio_lista;
          document.getElementById('input_repuesto_pedido').value = ab.id_repuesto;
          agregar_carrito(ab.id_repuesto,4);
        });
        
        // Vue.swal({
        //   icon:'info',
        //   title: 'En construcción',
        //   text:'El repuesto '+abono_detalle.descripcion+' se agregará al carrito',
        // });
        
      },
      error: function(error){
        console.log(error);
      }
    });
  }

  function dame_saldo_giftcard(){
      let codigo_giftcard = $('#input_codigo_giftcard').val();
      if(codigo_giftcard.trim() == 0 || codigo_giftcard == ''){
          Vue.swal({
              icon:'error',
              text:'Debe ingresar un código de giftcard'
          });
          return false;
      }

      let url = '/ventas/dame_saldo_giftcard/'+codigo_giftcard;
      $.ajax({
          type:'get',
          url: url,
          beforeSend: function(){
              espere('Cargando giftcard al carrito ...');
          },
          success: function(resp){
              console.log(resp);
              Vue.swal.close();

              if(typeof(resp) === 'string'){
                  Vue.swal({icon:'error', text:resp});
                  return false;
              }

              if(resp.success){
                  // Guardar el número del giftcard
                  $('#numero_giftcard_cargado').val(codigo_giftcard);

                  // Saldo real disponible de la giftcard
                  let saldo_disponible = limpiar_monto(resp.saldo_disponible);
                  let saldo_formateado = commaSeparateNumber(saldo_disponible);

                  // Total actual del carrito
                  let total_carrito = 0;
                  if($('#total_carrito').length){
                      total_carrito = limpiar_monto($('#total_carrito').val());
                  }

                  if(total_carrito <= 0){
                      Vue.swal({
                          icon:'error',
                          title:'Carrito vacío',
                          text:'Debe agregar productos al carrito antes de aplicar una giftcard.'
                      });
                      return false;
                  }

                  // Monto que realmente se aplicará: nunca debe superar el total de la venta
                  let monto_aplicado = saldo_disponible;
                  if(saldo_disponible > total_carrito){
                      monto_aplicado = total_carrito;
                  }

                  // Saldo restante estimado después de aplicar la giftcard
                  let saldo_restante = saldo_disponible - monto_aplicado;
                  if(saldo_restante < 0) saldo_restante = 0;

                  let monto_aplicado_formateado = commaSeparateNumber(monto_aplicado);
                  let saldo_restante_formateado = commaSeparateNumber(saldo_restante);

                  // Guardar y mostrar saldo disponible del vale de mercadería de forma permanente
                  $('#input_saldo_giftcard').val(saldo_disponible);
                  $('#saldo_giftcard_valor').text(saldo_formateado);
                  $('#div_saldo_giftcard').show();
                  $('#div_saldo_giftcard').addClass('d-inline');

                  // Activar forma de pago Giftcard (ID=6) y rellenar solo el monto aplicado a esta venta
                  $('#formita-6').prop('checked', true);
                  $('#monto-6').val(monto_aplicado);
                  $('#monto-6').prop('readonly', true); // No permitir editar el monto
                  $('#monto-6').prop('disabled', false); // Pero sí debe estar habilitado para enviar

                  // Habilitar referencia/descripción de la giftcard para que el cajero pueda editarla.
                  $('#referencia-6').prop('readonly', false);
                  $('#referencia-6').prop('disabled', false);
                  $('#referencia-6').val('Giftcard ' + codigo_giftcard);

                  // Diferencia a pagar en efectivo, si corresponde
                  let diferencia_efectivo = total_carrito - monto_aplicado;
                  if(diferencia_efectivo < 0) diferencia_efectivo = 0;

                  $('#monto-1').val(diferencia_efectivo);
                  $('#formita-1').prop('checked', diferencia_efectivo > 0);

                  // Mostrar botón de recalcular pagos giftcard y ocultar botón Sumar
                  $('#btn_sumar').hide();
                  $('#btn_recalcular_giftcard').show();

                  Vue.swal({
                      icon:'success',
                      title:'Giftcard cargada',
                      html:'<b>Saldo giftcard:</b> $'+saldo_formateado+
                           '<br><b>Total venta:</b> $'+commaSeparateNumber(total_carrito)+
                           '<br><b>Monto aplicado:</b> $'+monto_aplicado_formateado+
                           '<br><b>Saldo restante estimado:</b> $'+saldo_restante_formateado
                  });

                  $('#input_codigo_giftcard').val('');
              } else {
                  Vue.swal({icon:'error', text:'Error al cargar el vale'});
              }
          },
          error: function(error){
              Vue.swal.close();
              Vue.swal({
                  icon:'error',
                  text: error.responseText
              });
          }
      })
  }
  
    
  function agregar_consignacion_carrito(){
      let id_consignacion = $('#input_id_consignacion').val();
      if(id_consignacion == ''){
        Vue.swal({
          icon:'error',
          title:'error',
          text:'Ingrese codigo'
        });
        return false;
      }
      let url = '/ventas/dame_consignacion/'+id_consignacion;
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
       
        if(typeof(resp) === 'string'){
          Vue.swal({
            title:'Error',
            icon:'error',
            text:resp
          });
          return false;
        }
        let consignacion = resp[0];
        let detalle = resp[1];
        if(detalle.length > 0){
          console.log(detalle);
          detalle.forEach(d => {
          document.getElementById('cantidad_consignacion').value = d.cantidad;
          document.getElementById('local_id_consignacion').value = d.id_local;
          agregar_carrito(d.id_repuestos,5);
        });
        }else{
          Vue.swal({
            icon:'info',
            text:'Sus repuestos han sido devueltos o procesados',
            position:'top-end',
            toast: true,
            timer:3000,
            showConfirmButton: false
          });
        }
        
      },
      error: function(error){
        console.log(error);
      }
    });
    }

  function crear_vale_mercaderia(){
    // Limpiar el formulario
    $('#rut_cliente').val('');
    $('#nombre_cliente').val('');
    $('#telefono_cliente').val('');
    $('#descripcion').val('');
    $('#numero_documento').val('');
    $('#valor').val('');
    $('#vale_boleta').prop('checked', true);
    $('#vale_factura').prop('checked', false);
    
    // Abrir el modal
    $('#crear-vale-mercaderia-modal').modal('show');
  }

   function guardar_vale_mercaderia(){
    let rut = $('#rut_cliente').val().trim();
    let nombre = $('#nombre_cliente').val().trim();
    let telefono = $('#telefono_cliente').val().trim();
    let descripcion = $('#descripcion').val().trim();
    let tipo_doc = $('input[name="tipo_documento"]:checked').val();
    let numero_doc = $('#numero_documento').val().trim();
    let valor = $('#valor').val().trim();

    // Validar campos
    if(rut == ''){
      Vue.swal({icon:'error', text:'Ingrese RUT del cliente'});
      return false;
    }
    if(nombre == ''){
      Vue.swal({icon:'error', text:'Ingrese nombre del cliente'});
      return false;
    }
    if(descripcion == ''){
      Vue.swal({icon:'error', text:'Ingrese descripción del repuesto'});
      return false;
    }
    if(numero_doc == ''){
      Vue.swal({icon:'error', text:'Ingrese número de documento'});
      return false;
    }
    if(valor == '' || isNaN(valor) || Number(valor) <= 0){
      Vue.swal({icon:'error', text:'Ingrese un valor válido'});
      return false;
    }

    // Hacer la petición AJAX para guardar
    let url = '{{url("ventas/guardar_vale_mercaderia")}}';
    let parametros = {
      rut: rut,
      nombre: nombre,
      telefono: telefono,
      descripcion: descripcion,
      tipo_documento: tipo_doc,
      numero_documento: numero_doc,
      valor: valor
    };

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
      type:'POST',
      url: url,
      data: parametros,
      beforeSend: function(){
        espere('Guardando vale de mercadería...');
      },
      success: function(resp){
        Vue.swal.close();
        console.log(resp);
        if(typeof(resp) === 'string'){
          Vue.swal({icon:'error', text:resp});
          return false;
        }
        if(resp.success){
          Vue.swal({
            icon:'success',
            title:'Éxito',
            text:'Vale de mercadería creado: '+resp.numero_boucher
          });
          $('#crear-vale-mercaderia-modal').modal('hide');
        } else {
          Vue.swal({icon:'error', text:resp.mensaje || 'Error al guardar'});
        }
      },
      error: function(error){
        Vue.swal.close();
        Vue.swal({icon:'error', text: error.responseText});
      }
    });
  }

  function imprimir_boucher(){
    let numero_boucher = $('#numero_documento').val().trim();
    if(numero_boucher == ''){
      Vue.swal({icon:'error', text:'Primero debe guardar el vale para imprimirlo'});
      return false;
    }
    // Aquí iría la lógica para imprimir el boucher
    Vue.swal({icon:'info', text:'Imprimiendo boucher '+numero_boucher});
  }

  function ver_historial_vales(){
    // Aquí iría la lógica para ver el historial de vales
    let url = '{{url("ventas/historial_vales")}}';
    $.ajax({
      type:'GET',
      url: url,
      success: function(resp){
        console.log(resp);
      },
      error: function(error){
        console.log(error);
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

  function revisarCarrito(){
    console.log('revisando ...');
  }

  function agregar_detalle_credito(){
    let nfp_credito = $('input:radio[name=flexRadioDefault_credito]:checked').val();
    if(nfp_credito == '1'){
      $('#formita-2').append('Transbank');
    }  else{
      $('#formita-2').append('Getnet');
    } 
  }

  function agregar_detalle_debito(){
    let nfp_debito = $('input:radio[name=flexRadioDefault_debito]:checked').val();
    if(nfp_debito == '1'){
      $('#formita-5').append('Transbank')
    } else{
      $('#formita-5').append('Getnet');
    } 
  }

  function imprimir_cotizacion_venta(){
    Vue.swal({
      icon:'info',
      text:'EN CONSTRUCCION'
    });

    let url = "{{url('/')}}";
  }

function dame_vuelto(){
  let valor1 = $('#monto-1').val();
  let valor2 = $('#monto_efectivo').val();
  // validar valor2
  if(valor2 == ''){
    Vue.swal({
      icon:'error',
      title:'Error',
      text:'Ingrese monto efectivo'
    });
    return false;
  }
  // validar que valor 2 sea mayor que valor 1
  if(parseInt(valor2) < parseInt(valor1)){
    Vue.swal({
      icon:'error',
      title:'Error',
      text:'El monto efectivo debe ser mayor al total',
      position:'top-end',
      toast:true,
      timer:3000,
    });
    return false;
  }
console.log(valor1);
console.log(valor2);
  let result = parseInt(valor2) - parseInt(valor1);
  $('#div_vuelto').html('<span class="badge badge-success my-3" style="font-size: 16px; text-center">Su vuelto es de $'+commaSeparateNumber(result)+' pesos. </span>');
  $('#div_vuelto').fadeIn("slow");
  setTimeout(() => {
    $('#div_vuelto').fadeOut("slow");
  }, 5000);

}

  // ===== Mejoras UX: atajos y foco de trabajo =====
  function enfocarIngresoCodigo(){
    const input = document.getElementById('input_codigo');
    if(input && !input.disabled){
      input.focus();
      input.select();
    }else{
      $('#buscar-xpress-modal').modal('show');
    }
  }

  function abrirMasOpcionesVenta(){
    const panel = document.getElementById('acciones-secundarias-venta');
    if(!panel) return;
    panel.classList.toggle('d-none');
  }

  document.addEventListener('keydown', function(e){
    const tag = (e.target.tagName || '').toLowerCase();
    const escribiendo = ['input','textarea','select'].includes(tag);

    if(e.key === 'Escape'){
      $('.modal.show').modal('hide');
      return;
    }
    if(escribiendo) return;

    if(e.key === 'F2'){ e.preventDefault(); buscar_cliente(); }
    if(e.key === 'F3'){ e.preventDefault(); enfocarIngresoCodigo(); }
    if(e.key === 'F4'){ e.preventDefault(); $('#buscar-xpress-modal').modal('show'); }
    if(e.key === 'F5'){ e.preventDefault(); poner_nombre_carrito(); }
    if(e.key === 'F6'){ e.preventDefault(); confirmar_documento(); }
    if(e.key === 'F7'){ e.preventDefault(); nueva_venta(); }
  });

  $(document).ready(function(){
    // Ayuda contextual sin interrumpir el flujo.
    $('[data-toggle="tooltip"]').tooltip();

    // Foco inicial para vendedores que trabajan con teclado/lector de código.
    setTimeout(function(){
      const codigo = document.getElementById('input_codigo');
      if(codigo && !codigo.disabled) codigo.focus();
    }, 350);
  });
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



/* ===== Rediseño UX de la venta principal ===== */
:root{
  --venta-bg:#f4f6f9;
  --venta-card:#ffffff;
  --venta-border:#dfe3e8;
  --venta-primary:#2563eb;
  --venta-success:#15803d;
  --venta-warning:#f59e0b;
  --venta-text:#1f2937;
  --venta-muted:#6b7280;
}

body{ background:var(--venta-bg); }

.venta-shell{ padding:12px; }
.venta-toolbar{
  display:flex; flex-wrap:wrap; align-items:center; gap:8px;
  margin-bottom:12px; padding:10px; background:var(--venta-card);
  border:1px solid var(--venta-border); border-radius:12px;
  box-shadow:0 2px 8px rgba(15,23,42,.06);
}
.venta-toolbar .btn{ margin:0; border-radius:8px !important; border-width:1px !important; }
.venta-toolbar .acciones-principales, .venta-toolbar .acciones-secundarias{
  display:flex; flex-wrap:wrap; gap:6px; align-items:center;
}
.venta-toolbar .acciones-secundarias{ margin-left:auto; }
.venta-layout{ align-items:flex-start; }
.venta-sidebar, .venta-main{ padding-left:6px; padding-right:6px; }
.venta-panel{
  background:var(--venta-card) !important; border:1px solid var(--venta-border) !important;
  border-radius:12px !important; box-shadow:0 2px 8px rgba(15,23,42,.05);
}
.venta-sidebar-panel{
  position:sticky;
  top:8px;
  display:flex;
  flex-direction:column;
  height:var(--venta-sidebar-height, calc(100vh - 90px));
  max-height:var(--venta-sidebar-height, calc(100vh - 90px));
  min-height:520px;
  padding:10px !important;
  overflow:hidden;
}
.cliente-resumen{ margin-bottom:10px; padding:10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; }
.cliente-resumen .table{ margin:0; background:transparent; }
.cliente-resumen th{ color:var(--venta-muted); font-size:10px; text-transform:uppercase; border:0; }
.cliente-resumen td{ color:var(--venta-text); font-size:11px !important; font-weight:600; border:0; }
.documento-selector{ padding:10px; margin-bottom:10px; border:1px solid var(--venta-border); border-radius:10px; }
#zona_formas_pago{ display:block; padding-top:6px; }
#total_forma_pago{ font-size:1.55rem; font-weight:800; color:var(--venta-success); text-align:right; }
#btn_procesar_{ width:100%; min-height:46px; font-size:1rem; font-weight:700; border-radius:10px; }
#carrito{
  background:var(--venta-card) !important; min-height:430px !important; border:1px solid var(--venta-border) !important;
  border-radius:12px !important; padding:10px !important; box-shadow:0 2px 8px rgba(15,23,42,.05);
}
#carrito table tbody tr{ transition:background-color .15s ease, box-shadow .15s ease; }
#carrito table tbody tr:hover{ background:#eff6ff; box-shadow:inset 3px 0 var(--venta-primary); }
.accesos-rapidos{ background:transparent !important; border:0 !important; padding:0 !important; }
.accesos-rapidos .card{ background:var(--venta-card) !important; border:1px solid var(--venta-border) !important; border-radius:12px !important; }
.accesos-rapidos .form-control{ min-height:38px; background:#fff !important; border-color:#cbd5e1 !important; }
.accesos-rapidos .form-control:focus{ border-color:var(--venta-primary) !important; box-shadow:0 0 0 .18rem rgba(37,99,235,.15); }
.atajos-venta{ font-size:11px; color:var(--venta-muted); }
.atajos-venta kbd{ margin-right:3px; background:#334155; border-radius:4px; }
.modal-content{ border:0; border-radius:12px; overflow:hidden; }
.modal-header-80, .modal-header-40{ background:#1e40af; }
#zona_grilla{ background:#fff; overflow:auto; }
#panel-buscar{ background:#f8fafc !important; border-right:1px solid var(--venta-border); }

@media(max-width:991.98px){
  .venta-sidebar-panel{
    position:static;
    height:auto;
    max-height:none;
    min-height:0;
    overflow:visible;
    margin-bottom:12px;
  }
  .venta-toolbar .acciones-secundarias{ margin-left:0; }
}


/* ===== Sidebar de venta V2 ===== */
.venta-sidebar-panel{
  padding:10px !important;
  background:#f8fafc !important;
}

.venta-v2-stack{
  display:flex;
  flex:1 1 auto;
  min-height:0;
  flex-direction:column;
  gap:10px;
  overflow-y:auto;
  overflow-x:hidden;
  padding-right:5px;
  padding-bottom:4px;
  scrollbar-gutter:stable;
}

.venta-v2-stack::-webkit-scrollbar{
  width:7px;
}

.venta-v2-stack::-webkit-scrollbar-track{
  background:#e2e8f0;
  border-radius:10px;
}

.venta-v2-stack::-webkit-scrollbar-thumb{
  background:#94a3b8;
  border-radius:10px;
}

.venta-v2-stack::-webkit-scrollbar-thumb:hover{
  background:#64748b;
}

.venta-sidebar-footer{
  flex:0 0 auto;
  margin-top:10px;
  padding:10px;
  background:#fff;
  border:1px solid #dce3eb;
  border-radius:12px;
  box-shadow:0 -3px 12px rgba(15,23,42,.08);
}

.venta-sidebar-footer .venta-v2-total{
  margin-top:0;
}

.venta-sidebar-footer .venta-v2-process{
  margin-top:9px;
}

.venta-v2-card{
  flex:0 0 auto;
  background:#fff;
  border:1px solid #dce3eb;
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 2px 7px rgba(15,23,42,.05);
}

.venta-v2-card-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:10px 12px;
  background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);
  border-bottom:1px solid #e2e8f0;
  color:#1e293b;
  font-size:14px;
  font-weight:800;
}

.venta-v2-card-header i{
  width:18px;
  color:#2563eb;
  text-align:center;
}

.venta-v2-card-body{
  padding:11px;
}

.venta-v2-client-grid{
  display:grid;
  grid-template-columns:minmax(105px,.85fr) minmax(0,1.6fr);
  gap:8px;
}

.venta-v2-client-item{
  min-width:0;
  padding:8px 9px;
  background:#f8fafc;
  border:1px solid #e2e8f0;
  border-radius:8px;
}

.venta-v2-client-item.wide{
  grid-column:1 / -1;
}

.venta-v2-client-label{
  display:block;
  margin-bottom:3px;
  color:#64748b;
  font-size:10px;
  font-weight:800;
  letter-spacing:.04em;
  text-transform:uppercase;
}

.venta-v2-client-value{
  display:block;
  min-height:17px;
  color:#172033;
  font-size:12px !important;
  font-weight:700;
  line-height:1.35;
  text-transform:uppercase;
  overflow-wrap:anywhere;
}

.venta-v2-options{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:7px;
}

.venta-v2-option{
  position:relative;
  margin:0;
}

.venta-v2-option input[type="radio"]{
  position:absolute;
  opacity:0;
  pointer-events:none;
}

.venta-v2-option label{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  min-height:39px;
  margin:0;
  padding:7px 8px;
  border:1px solid #cbd5e1;
  border-radius:8px;
  background:#fff;
  color:#334155;
  cursor:pointer;
  font-size:13px;
  font-weight:600;
  text-align:center;
  transition:.16s ease;
}

.venta-v2-option label:hover{
  border-color:#60a5fa;
  background:#f8fbff;
}

.venta-v2-option input[type="radio"]:checked + label{
  color:#1d4ed8;
  border-color:#3b82f6;
  background:#eff6ff;
  box-shadow:0 0 0 2px rgba(59,130,246,.11);
}

.venta-v2-option input[type="radio"]:disabled + label{
  opacity:.5;
  cursor:not-allowed;
  background:#f1f5f9;
}

.venta-v2-option.full{
  grid-column:1 / -1;
}

.venta-v2-extra{
  display:grid;
  grid-template-columns:1fr;
  gap:7px;
  margin-top:8px;
}

.venta-v2-extra input{
  width:100% !important;
  min-height:37px;
  padding:6px 9px;
  border:1px solid #cbd5e1;
  border-radius:7px;
  background:#fff;
}

.venta-v2-extra input:focus{
  border-color:#3b82f6;
  outline:0;
  box-shadow:0 0 0 2px rgba(59,130,246,.12);
}

.venta-v2-payment-scroll{
  max-height:none;
  overflow:visible;
  border:1px solid #e2e8f0;
  border-radius:8px;
}

#formas_pago{
  margin:0 !important;
}

#formas_pago table{
  margin:0 !important;
  width:100%;
  background:#fff;
}

#formas_pago thead th,
#formas_pago > .col-12 > table thead th{
  position:sticky;
  top:0;
  z-index:2;
  padding:8px 5px !important;
  background:#d9f7ef !important;
  color:#123b34;
  border-top:0 !important;
  font-size:11px;
  font-weight:800;
  text-align:center;
}

#formas_pago td{
  padding:4px 3px !important;
  vertical-align:middle;
  font-size:12px;
}

#formas_pago input[type="text"],
#formas_pago input[type="number"]{
  max-width:100%;
  height:32px;
  padding:4px 6px;
  border:1px solid #cbd5e1;
  border-radius:6px;
}

.venta-v2-discount-row{
  display:grid;
  grid-template-columns:minmax(110px,1fr) 82px auto;
  gap:6px;
  align-items:center;
}

.venta-v2-discount-row label{
  margin:0;
  font-size:12px;
  font-weight:700;
}

.venta-v2-discount-row input[type="text"]{
  width:100% !important;
  height:34px;
  border:1px solid #cbd5e1;
  border-radius:6px;
  text-align:center;
}

.venta-v2-change{
  display:grid;
  grid-template-columns:1fr auto;
  gap:6px;
  align-items:center;
  margin-top:9px;
  padding:8px;
  background:#f8fafc;
  border:1px solid #e2e8f0;
  border-radius:8px;
}

.venta-v2-change input{
  width:100% !important;
  height:34px;
  border:1px solid #cbd5e1;
  border-radius:6px;
  padding:4px 7px;
}

.venta-v2-actions{
  display:grid;
  grid-template-columns:1fr;
  gap:6px;
  margin-top:9px;
}

.venta-v2-total{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  margin-top:9px;
  padding:11px 12px;
  color:#fff;
  background:linear-gradient(135deg,#166534,#15803d);
  border-radius:10px;
  box-shadow:0 4px 12px rgba(21,128,61,.18);
}

.venta-v2-total-label{
  font-size:12px;
  font-weight:700;
  opacity:.9;
  text-transform:uppercase;
}

.venta-v2-total #total_forma_pago{
  color:#fff !important;
  font-size:1.55rem !important;
  line-height:1;
}

.venta-v2-references{
  margin-top:8px;
  padding:8px 9px;
  background:#fffbeb;
  border:1px solid #fde68a;
  border-radius:8px;
  color:#854d0e;
  font-size:11px;
}

.venta-v2-process{
  margin-top:9px;
}

.venta-v2-process #btn_procesar_{
  min-height:46px;
  border:0;
  color:#111827;
  background:linear-gradient(135deg,#facc15,#f59e0b);
  box-shadow:0 4px 12px rgba(245,158,11,.22);
}

@media(max-width:1199.98px){
  .venta-v2-client-grid{
    grid-template-columns:1fr;
  }
  .venta-v2-client-item.wide{
    grid-column:auto;
  }
}

@media(max-width:991.98px){
  .venta-v2-stack{
    overflow:visible;
    padding-right:0;
  }

  .venta-sidebar-footer{
    margin-top:0;
  }

  .venta-v2-client-grid{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
  .venta-v2-client-item.wide{
    grid-column:1 / -1;
  }
}

@media(max-width:575.98px){
  .venta-v2-options,
  .venta-v2-client-grid{
    grid-template-columns:1fr;
  }
  .venta-v2-client-item.wide,
  .venta-v2-option.full{
    grid-column:auto;
  }
  .venta-v2-discount-row{
    grid-template-columns:1fr;
  }
}


/* ==========================================================
   HISTORIALES PROFESIONALES: COTIZACIONES Y CONSIGNACIONES
   ========================================================== */
.historial-modal .modal-dialog{
  max-width:1180px;
}

.historial-modal .modal-content{
  border:0;
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 24px 65px rgba(15,23,42,.22);
}

.historial-modal .modal-header{
  padding:18px 22px;
  color:#fff;
  border-bottom:0;
  background:linear-gradient(135deg,#0f172a,#1e3a8a);
}

.historial-modal.historial-consignaciones .modal-header{
  background:linear-gradient(135deg,#0f172a,#047857);
}

.historial-modal .modal-title{
  display:flex;
  align-items:center;
  gap:11px;
  margin:0;
  font-size:20px;
  font-weight:800;
  letter-spacing:.01em;
}

.historial-modal .modal-title-icon{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:38px;
  height:38px;
  border-radius:10px;
  background:rgba(255,255,255,.15);
}

.historial-modal .close{
  color:#fff;
  opacity:.8;
  text-shadow:none;
}

.historial-modal .close:hover{
  color:#fff;
  opacity:1;
}

.historial-toolbar{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  padding:15px 20px;
  background:#f8fafc;
  border-bottom:1px solid #e2e8f0;
}

.historial-toolbar-info{
  display:flex;
  align-items:center;
  gap:9px;
  color:#475569;
  font-size:13px;
}

.historial-counter{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:33px;
  height:28px;
  padding:0 10px;
  color:#1d4ed8;
  background:#dbeafe;
  border-radius:999px;
  font-weight:800;
}

.historial-consignaciones .historial-counter{
  color:#047857;
  background:#d1fae5;
}

.historial-search{
  position:relative;
  width:min(100%,390px);
}

.historial-search i{
  position:absolute;
  left:13px;
  top:50%;
  color:#94a3b8;
  transform:translateY(-50%);
  pointer-events:none;
}

.historial-search input{
  width:100%;
  height:39px;
  padding:7px 38px 7px 38px;
  border:1px solid #cbd5e1;
  border-radius:9px;
  background:#fff;
  font-size:13px;
  transition:.18s ease;
}

.historial-search input:focus{
  border-color:#3b82f6;
  outline:0;
  box-shadow:0 0 0 3px rgba(59,130,246,.12);
}

.historial-search-clear{
  position:absolute;
  top:50%;
  right:9px;
  border:0;
  color:#64748b;
  background:transparent;
  transform:translateY(-50%);
  cursor:pointer;
}

.historial-table-wrap{
  max-height:62vh;
  overflow:auto;
  background:#fff;
}

.historial-table{
  width:100%;
  margin:0;
  table-layout:fixed;
  border-collapse:separate;
  border-spacing:0;
}

.historial-table thead th{
  position:sticky;
  top:0;
  z-index:3;
  padding:11px 12px;
  color:#334155;
  background:#eef2ff;
  border-top:0;
  border-bottom:1px solid #cbd5e1;
  font-size:11px;
  font-weight:800;
  letter-spacing:.04em;
  text-transform:uppercase;
  white-space:nowrap;
}

.historial-consignaciones .historial-table thead th{
  background:#ecfdf5;
}

.historial-table tbody td{
  padding:11px 12px;
  color:#334155;
  border-top:0;
  border-bottom:1px solid #e2e8f0;
  vertical-align:middle;
  font-size:13px;
}

.historial-table tbody tr{
  transition:background .15s ease, box-shadow .15s ease;
}

.historial-table tbody tr:hover{
  background:#f8fafc;
  box-shadow:inset 3px 0 #3b82f6;
}

.historial-consignaciones .historial-table tbody tr:hover{
  box-shadow:inset 3px 0 #10b981;
}

.historial-number-link{
  display:inline-flex;
  align-items:center;
  gap:5px;
  color:#2563eb;
  font-weight:800;
  text-decoration:none;
}

.historial-number-link:hover{
  color:#1d4ed8;
  text-decoration:none;
}

.historial-date{
  display:inline-flex;
  align-items:center;
  gap:6px;
  white-space:nowrap;
  color:#475569;
  font-variant-numeric:tabular-nums;
}

.historial-name{
  display:block;
  overflow:hidden;
  color:#172033;
  font-weight:750;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.historial-client{
  display:block;
  overflow:hidden;
  color:#64748b;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.historial-status{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:5px 9px;
  border-radius:999px;
  font-size:10px;
  font-weight:850;
  letter-spacing:.03em;
  text-transform:uppercase;
  white-space:nowrap;
}

.historial-status::before{
  content:'';
  width:7px;
  height:7px;
  border-radius:50%;
  background:currentColor;
}

.historial-status.confirmado{
  color:#15803d;
  background:#dcfce7;
}

.historial-status.esperando,
.historial-status.pendiente{
  color:#b45309;
  background:#fef3c7;
}

.historial-status.rechazado{
  color:#b91c1c;
  background:#fee2e2;
}

.historial-status.procesado{
  color:#475569;
  background:#e2e8f0;
}

.historial-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:6px;
  white-space:nowrap;
}

.historial-action-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:34px;
  height:34px;
  padding:0;
  border:0;
  border-radius:8px;
  color:#fff;
  transition:transform .14s ease, filter .14s ease;
}

.historial-action-btn:hover{
  color:#fff;
  filter:brightness(.94);
  transform:translateY(-1px);
}

.historial-action-load{ background:#2563eb; }
.historial-action-print{ background:#16a34a; }
.historial-action-delete{ background:#dc2626; }

.historial-empty{
  padding:42px 15px !important;
  color:#64748b !important;
  text-align:center;
}

.historial-empty i{
  display:block;
  margin-bottom:10px;
  color:#94a3b8;
  font-size:28px;
}

.historial-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:12px 20px;
  color:#64748b;
  background:#f8fafc;
  border-top:1px solid #e2e8f0;
  font-size:12px;
}

.historial-detail{
  max-height:32vh;
  overflow:auto;
  padding:0 20px 14px;
  background:#fff;
}

.historial-detail:empty{
  display:none;
}

@media(max-width:767.98px){
  .historial-modal .modal-dialog{
    max-width:none;
    margin:8px;
  }

  .historial-toolbar{
    align-items:stretch;
  }

  .historial-search{
    width:100%;
  }

  .historial-table{
    min-width:850px;
  }

  .historial-table-wrap{
    max-height:68vh;
  }
}


/* ==========================================================
   MODAL BUSCAR REPUESTO XPRESS
   ========================================================== */
#buscar-xpress-modal .modal-dialog{
  max-width:1320px;
  width:calc(100% - 30px);
}

#buscar-xpress-modal .modal-content{
  border:0;
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 24px 65px rgba(15,23,42,.24);
}

#buscar-xpress-modal .modal-header{
  padding:18px 22px;
  color:#fff;
  border-bottom:0;
  background:linear-gradient(135deg,#0f172a,#1d4ed8);
}

#buscar-xpress-modal .modal-title{
  display:flex;
  align-items:center;
  gap:11px;
  margin:0;
  font-size:20px;
  font-weight:800;
}

.buscar-xpress-title-icon{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:40px;
  height:40px;
  border-radius:11px;
  background:rgba(255,255,255,.15);
}

#buscar-xpress-modal .close{
  color:#fff;
  opacity:.85;
  text-shadow:none;
}

#buscar-xpress-modal .close:hover{
  color:#fff;
  opacity:1;
}

.buscar-xpress-search{
  padding:18px 20px;
  background:#f8fafc;
  border-bottom:1px solid #e2e8f0;
}

.buscar-xpress-search-grid{
  display:grid;
  grid-template-columns:minmax(0,1fr) auto;
  gap:10px;
  align-items:end;
}

.buscar-xpress-label{
  display:block;
  margin-bottom:7px;
  color:#334155;
  font-size:12px;
  font-weight:800;
  letter-spacing:.04em;
  text-transform:uppercase;
}

.buscar-xpress-input-wrap{
  position:relative;
}

.buscar-xpress-input-wrap > i{
  position:absolute;
  top:50%;
  left:14px;
  color:#94a3b8;
  transform:translateY(-50%);
  pointer-events:none;
}

#repuesto-xpress-codigo{
  width:100%;
  min-height:44px;
  padding:9px 13px 9px 42px;
  border:1px solid #cbd5e1;
  border-radius:10px;
  background:#fff;
  font-size:15px;
  font-weight:650;
  transition:.18s ease;
}

#repuesto-xpress-codigo:focus{
  border-color:#3b82f6;
  outline:0;
  box-shadow:0 0 0 3px rgba(59,130,246,.13);
}

.buscar-xpress-btn{
  min-height:44px;
  padding:0 20px;
  border:0;
  border-radius:10px;
  color:#fff;
  background:linear-gradient(135deg,#2563eb,#1d4ed8);
  font-weight:800;
  box-shadow:0 4px 12px rgba(37,99,235,.2);
}

.buscar-xpress-btn:hover{
  color:#fff;
  filter:brightness(.96);
}

#mensaje_modal{
  min-height:20px;
  margin:8px 0 0;
  color:#64748b;
  font-size:12px;
}

.buscar-xpress-results{
  padding:18px 20px;
  background:#fff;
}

.buscar-xpress-table-card{
  border:1px solid #e2e8f0;
  border-radius:12px;
  overflow:hidden;
}

.buscar-xpress-table{
  width:100%;
  min-width:980px;
  margin:0;
  table-layout:fixed;
}

.buscar-xpress-table thead th{
  padding:10px 8px;
  border-top:0;
  border-bottom:1px solid #cbd5e1;
  background:#d9f7ef;
  color:#134e4a;
  font-size:10px;
  font-weight:850;
  letter-spacing:.03em;
  text-align:center;
  text-transform:uppercase;
  white-space:nowrap;
}

.buscar-xpress-table tbody td{
  padding:10px 8px;
  border-top:0;
  border-bottom:1px solid #e2e8f0;
  color:#334155;
  font-size:12px;
  vertical-align:middle;
}

.buscar-xpress-table tbody tr:hover{
  background:#f8fafc;
}


/* Ubicaciones agrupadas para evitar columnas apretadas */
.xpress-location-list{
  display:flex;
  flex-direction:column;
  gap:6px;
}

.xpress-location-item{
  display:grid;
  grid-template-columns:minmax(74px,.75fr) minmax(0,1.5fr) auto;
  align-items:center;
  gap:8px;
  min-height:34px;
  padding:6px 8px;
  border:1px solid #e2e8f0;
  border-radius:8px;
  background:#f8fafc;
}

.xpress-location-name{
  color:#1e293b;
  font-size:11px;
  font-weight:800;
  line-height:1.2;
}

.xpress-location-detail{
  min-width:0;
  color:#64748b;
  font-size:11px;
  line-height:1.25;
  overflow-wrap:anywhere;
}

.xpress-location-stock{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:38px;
  padding:3px 7px;
  border-radius:999px;
  color:#166534;
  background:#dcfce7;
  font-size:10px;
  font-weight:850;
  white-space:nowrap;
}

.xpress-location-stock.sin-stock{
  color:#991b1b;
  background:#fee2e2;
}

.xpress-location-stock.stock-bajo{
  color:#92400e;
  background:#fef3c7;
}

.buscar-xpress-description{
  color:#1e293b;
  font-weight:700;
  line-height:1.35;
}

.buscar-xpress-price{
  color:#0f172a;
  font-size:14px;
  font-weight:850;
  white-space:nowrap;
}

.buscar-xpress-table input,
.buscar-xpress-table select{
  width:100%;
  min-height:36px;
  padding:6px 8px;
  border:1px solid #cbd5e1;
  border-radius:8px;
  background:#fff;
}

.buscar-xpress-table input:focus,
.buscar-xpress-table select:focus{
  border-color:#3b82f6;
  outline:0;
  box-shadow:0 0 0 2px rgba(59,130,246,.12);
}

.buscar-xpress-empty{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:210px;
  padding:30px 20px;
  color:#64748b;
  text-align:center;
}

.buscar-xpress-empty-icon{
  display:flex;
  align-items:center;
  justify-content:center;
  width:60px;
  height:60px;
  margin-bottom:12px;
  border-radius:50%;
  color:#2563eb;
  background:#dbeafe;
  font-size:25px;
}

.buscar-xpress-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:14px 20px;
  background:#f8fafc;
  border-top:1px solid #e2e8f0;
}

.buscar-xpress-help{
  color:#64748b;
  font-size:12px;
}

#btn_agregar_repuesto_buscado{
  min-width:165px;
  min-height:40px;
  border:0;
  border-radius:9px;
  color:#fff;
  background:linear-gradient(135deg,#16a34a,#15803d);
  font-weight:800;
  box-shadow:0 4px 12px rgba(22,163,74,.18);
}

#btn_agregar_repuesto_buscado:disabled{
  cursor:not-allowed;
  opacity:.55;
  box-shadow:none;
}

@media(max-width:767.98px){
  #buscar-xpress-modal .modal-dialog{
    max-width:none;
    margin:8px;
  }

  .buscar-xpress-search-grid{
    grid-template-columns:1fr;
  }

  .buscar-xpress-btn{
    width:100%;
  }


  .buscar-xpress-results{
    padding:12px;
  }

  .xpress-location-item{
    grid-template-columns:68px minmax(0,1fr) auto;
  }

  .buscar-xpress-footer{
    align-items:stretch;
    flex-direction:column;
  }

  .buscar-xpress-footer-actions{
    display:grid !important;
    grid-template-columns:1fr 1fr;
  }
}



  .venta-carrito-vacio{
    min-height:310px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:45px 20px;
    border:1px dashed #cbd5e1;
    border-radius:16px;
    background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);
  }

  .venta-carrito-vacio__icono{
    width:78px;
    height:78px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:18px;
    border-radius:50%;
    background:#eff6ff;
    color:#2563eb;
    font-size:32px;
    box-shadow:0 8px 22px rgba(37,99,235,.12);
  }

  .venta-carrito-vacio__titulo{
    margin:0 0 8px;
    color:#1e293b;
    font-size:1.55rem;
    font-weight:800;
  }

  .venta-v2-client-search{
    min-height:46px;
    border-radius:10px;
    font-weight:700;
    box-shadow:0 5px 14px rgba(37,99,235,.16);
  }

  #cliente_seleccionado{
    animation:ventaClienteEntrada .18s ease-out;
  }

  @keyframes ventaClienteEntrada{
    from{opacity:0;transform:translateY(-4px);}
    to{opacity:1;transform:translateY(0);}
  }

  .venta-carrito-vacio__texto{
    max-width:480px;
    margin:0;
    color:#64748b;
    font-size:.98rem;
  }

  .venta-carrito-vacio__ayuda{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:10px 18px;
    margin-top:20px;
    color:#64748b;
    font-size:.86rem;
  }

  .venta-carrito-vacio__ayuda kbd{
    padding:3px 7px;
    border-radius:5px;
    background:#334155;
    color:#fff;
    box-shadow:none;
  }
</style>
@endsection

@section('contenido_titulo_pagina')
  <div class="row titulazo" style="width: 100%;">
  <div class="col-sm-11" style="width:95%"><center><h4 class="mb-0"><i class="fas fa-cash-register mr-2"></i>VENTAS</h4></center></div>
  <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-idea-web.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

  {{-- @section('mensajes')
    @include('fragm.mensajes')
  @endsection --}}
  @section('contenido_ingresa_datos')
<div class="container-fluid venta-shell">
    <div class="btn-toolbar venta-toolbar" role="toolbar">
        <div class="btn-group btn-group-sm acciones-principales" role="group">
          
            
            <button class="btn btn-danger" style="border: 1px solid black;border-radius: 10px;" onclick="nueva_venta()">Nueva Venta</button> 
            @if($value_cotizar == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-warning btn-sm" onclick="historial_cotizaciones()" style="border: 1px solid black;border-radius: 10px;"><small>Cotizaciones</small></button>@endif
            @if($value_consignar == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-secondary btn-sm" onclick="historial_consignaciones()" style="border: 1px solid black;border-radius: 10px;"><small>Consignaciones</small></button>@endif
            @if($value_busqueda == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#buscar-xpress-modal" style="border: 1px solid black;border-radius: 10px;">Busqueda Express <i class="fa-solid fa-magnifying-glass"></i></button>@endif
            
            @if(Auth::user()->rol->nombrerol === "vendedor" || Auth::user()->rol->nombrerol === "Administrador")
            {{-- <button type="button" class="btn btn-success btn-sm" onclick="buscar_repuesto()" >Buscar Repuesto</button> --}}
            @if($value_expres == 1 || Auth::user()->rol->nombrerol === "Administrador")
            <button type="button" class="btn btn-primary btn-sm" style="border: 1px solid black;border-radius: 10px;" data-toggle="modal" data-target="#repuesto-xpress-modal">Repuesto Xpress</button>
            @endif
            @elseif(Auth::user()->rol->nombrerol === "bodega-venta" || Auth::user()->rol->nombrerol === "Cajer@")
            @if($value_expres == 1 || Auth::user()->rol->nombrerol === "Administrador")<button type="button" class="btn btn-primary btn-sm" style="border: 1px solid black;border-radius: 10px;" data-toggle="modal" data-target="#repuesto-xpress-modal">Repuesto Xpress</button>@endif
            @endif
            {{-- @if(Auth::user()->rol->nombrerol !== "vendedor" && Auth::user()->rol->nombrerol !== "bodega-venta") <button type="button" class="btn btn-warning btn-sm" onclick="abrir_referencias()">Referencias</button> @endif --}}
            @if($value_referencias == 1 || Auth::user()->rol->nombrerol === "Administrador") <button type="button" class="btn btn-warning btn-sm" style="border: 1px solid black;border-radius: 10px;" onclick="abrir_referencias()">Referencias</button> @endif
        </div>
        <div class="btn-group btn-group-sm acciones-secundarias" role="group">
            @if($value_transferir_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-success" onclick="transferir_carrito()" style="border: 1px solid black;border-radius: 10px;"><small>Transferir Carrito</small></button>@endif
            @if($value_borrar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-danger"  onclick="borrar_carritos_guardados()" style="border: 1px solid black;border-radius: 10px;"><small>Borrar Carritos Guardados</small></button>@endif
            @if($value_recuperar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-info" onclick="mostrar_nombres_carrito()" style="border: 1px solid black;border-radius: 10px;" >Recuperar Carrito</button>@endif
            @if($value_guardar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-warning" onclick="poner_nombre_carrito()" style="border: 1px solid black;border-radius: 10px;" >Guardar Carrito</button>@endif
            @if($value_borrar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-danger" onclick="borrar_carrito('actual')" style="border: 1px solid black;border-radius: 10px;"><small>Borrar Carrito Actual</small></button>@endif
        </div>
        
    </div>

<div class="row venta-layout">
    <div class="col-lg-4 col-xl-3 venta-sidebar">
      <div class="venta-panel venta-sidebar-panel">
        <div class="venta-v2-stack">

          {{-- CLIENTE --}}
          <section class="venta-v2-card">
            <div class="venta-v2-card-header">
              <span><i class="fa-solid fa-user"></i> Cliente</span>
              <small class="text-muted">Datos de facturación</small>
            </div>

            <div class="venta-v2-card-body">
              <div id="mensaje-cliente"></div>
              <input type="hidden" id="id_cliente" value="0">

              @if($value_busqueda_cliente == 0 || Auth::user()->rol->nombrerol == "Administrador")
                <div id="cliente_sin_seleccionar">
                  <button type="button"
                          class="btn btn-primary btn-block venta-v2-client-search"
                          onclick="buscar_cliente()">
                    <i class="fa-solid fa-user-plus mr-1"></i>
                    Buscar y seleccionar cliente
                  </button>
                  <small class="d-block text-center text-muted mt-2">
                    Opcional para boleta y requerido para factura.
                  </small>
                </div>
              @endif

              <div id="cliente_seleccionado" style="display:none;">
                <div class="venta-v2-client-grid" id="el_cliente">
                  <div class="venta-v2-client-item">
                    <span class="venta-v2-client-label">RUT</span>
                    <span class="venta-v2-client-value" id="cliente_rut">Sin cliente</span>
                  </div>

                  <div class="venta-v2-client-item">
                    <span class="venta-v2-client-label">Nombre</span>
                    <span class="venta-v2-client-value" id="cliente_nombres">Seleccione un cliente</span>
                  </div>

                  <div class="venta-v2-client-item wide">
                    <span class="venta-v2-client-label">Dirección</span>
                    <span class="venta-v2-client-value" id="cliente_direccion">Sin dirección</span>
                  </div>

                  <div class="venta-v2-client-item wide">
                    <span class="venta-v2-client-label">Giro</span>
                    <span class="venta-v2-client-value" id="cliente_giro">Sin giro</span>
                  </div>
                </div>

                @if($value_busqueda_cliente == 0 || Auth::user()->rol->nombrerol == "Administrador")
                  <button type="button"
                          class="btn btn-outline-primary btn-sm btn-block mt-3"
                          onclick="buscar_cliente()">
                    <i class="fa-solid fa-user-pen mr-1"></i> Cambiar cliente
                  </button>
                @endif
              </div>
            </div>
          </section>

          {{-- DOCUMENTO --}}
          <section class="venta-v2-card">
            <div class="venta-v2-card-header">
              <span><i class="fa-solid fa-file-invoice"></i> Tipo de documento</span>
            </div>

            <div class="venta-v2-card-body">
              <div class="venta-v2-options">
                <div class="venta-v2-option">
                  <input type="radio" name="tipo_documento" id="radio_boleta" value="boleta"
                         onclick="que_documento('boleta')" checked>
                  <label for="radio_boleta">
                    <i class="fa-solid fa-receipt"></i> Boleta
                  </label>
                </div>

                <div class="venta-v2-option">
                  <input type="radio" name="tipo_documento" id="radio_factura" value="factura"
                         onclick="que_documento('factura')">
                  <label for="radio_factura">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Factura
                  </label>
                </div>

                <div class="venta-v2-option">
                  <input type="radio" name="tipo_documento" id="radio_cotizacion" value="cotizacion"
                         onclick="que_documento('cotizacion')">
                  <label for="radio_cotizacion">
                    <i class="fa-solid fa-file-signature"></i> Cotización
                  </label>
                </div>

                <div class="venta-v2-option">
                  <input type="radio" name="tipo_documento" id="radio_consignacion" value="consignacion"
                         onclick="que_documento('consignacion')">
                  <label for="radio_consignacion">
                    <i class="fa-solid fa-boxes-stacked"></i> Consignación
                  </label>
                </div>
              </div>

              <div class="venta-v2-extra">
                <input type="text" name="nombre_cotizacion" value="" id="nombre_cotizacion"
                       maxlength="100" placeholder="Nombre de la cotización" disabled>

                <input type="text" name="dias_expira" value="7" id="dias_expira"
                       maxlength="2" style="display:none;" disabled>

                <input type="text" name="nombre_consignacion" value="" id="nombre_consignacion"
                       maxlength="100" placeholder="Nombre de la consignación" disabled>

                <input type="text" name="dias_expira_consignacion" value="7"
                       id="dias_expira_consignacion" maxlength="2" style="display:none;" disabled>
              </div>
            </div>
          </section>

          {{-- MODALIDAD --}}
          <section class="venta-v2-card">
            <div class="venta-v2-card-header">
              <span><i class="fa-solid fa-store"></i> Modalidad de venta</span>
            </div>

            <div class="venta-v2-card-body">
              <div class="venta-v2-options">
                <div class="venta-v2-option">
                  <input type="radio" name="tipo_venta" value="contado" id="contado"
                         onclick="contado_credito(1)" checked>
                  <label for="contado">
                    <i class="fa-solid fa-money-bill-wave"></i> Contado
                  </label>
                </div>

                <div class="venta-v2-option">
                  <input type="radio" name="tipo_venta" value="credito" id="credito"
                         onclick="contado_credito(2)" disabled>
                  <label for="credito">
                    <i class="fa-solid fa-calendar-check"></i> Crédito
                  </label>
                </div>

                @if(Auth::user()->name == "Matias Alfaro" || Auth::user()->rol->nombrerol === "Administrador")
                <div class="venta-v2-option full">
                  <input type="radio" name="tipo_venta" value="delivery" id="delivery"
                         onclick="contado_credito(3)">
                  <label for="delivery">
                    <i class="fa-solid fa-truck"></i> Delivery
                  </label>
                </div>
                @endif
              </div>
            </div>
          </section>

          {{-- FORMAS DE PAGO --}}
          <section class="venta-v2-card">
            <div class="venta-v2-card-header">
              <span><i class="fa-solid fa-wallet"></i> Formas de pago</span>
              <small class="text-muted">Monto y referencia</small>
            </div>

            <div class="venta-v2-card-body">
              <fieldset id="zona_formas_pago">
                <div class="venta-v2-payment-scroll">
                  <div class="row row-15" id="formas_pago"></div>
                </div>
              </fieldset>
            </div>
          </section>

          {{-- DESCUENTO Y VUELTO --}}
          <section class="venta-v2-card">
            <div class="venta-v2-card-header">
              <span><i class="fa-solid fa-percent"></i> Ajustes de pago</span>
            </div>

            <div class="venta-v2-card-body">
              <fieldset id="zona_descuento">
                <div class="venta-v2-discount-row" id="zona_descuento_input">
                  <label for="forma_descuento">
                    <input type="checkbox" name="forma_descuento" id="forma_descuento"
                           onclick="activar_forma_pago(999)">
                    Aplicar descuento
                  </label>

                  <input type="text" id="formita-descuento" value="0"
                         placeholder="% DCTO" onkeypress="return soloNumeros(event)" disabled>

                  <button type="button" class="btn btn-primary btn-sm"
                          id="btn_aplicar_dcto" onclick="calcular_sumatoria()" disabled>
                    Aplicar
                  </button>
                </div>

                <input type="hidden" name="forma_descuento" id="total_venta_vuelto"
                       placeholder="Monto efectivo" readonly>

                <div class="venta-v2-change" id="zona_vuelto_efectivo">
                  <input type="text" name="monto_efectivo" id="monto_efectivo"
                         placeholder="Efectivo recibido"
                         onkeypress="return soloNumeros(event)">

                  <button type="button" class="btn btn-success btn-sm" onclick="dame_vuelto()">
                    Calcular vuelto
                  </button>
                </div>

                <div id="div_vuelto" style="display:none;"></div>

                <div class="venta-v2-actions">
                  <button type="button" class="btn btn-success btn-sm"
                          id="btn_sumar" onclick="calcular_sumatoria()">
                    <i class="fa-solid fa-calculator mr-1"></i>
                    Sumar formas de pago
                  </button>

                  <button type="button" class="btn btn-info btn-sm"
                          id="btn_recalcular_giftcard"
                          onclick="recalcular_pagos_giftcard()"
                          style="display:none;">
                    Recalcular pagos Giftcard
                  </button>
                </div>

                <div class="venta-v2-references" id="referencias">
                  <strong>Referencias:</strong> Ninguna
                </div>
              </fieldset>
            </div>
          </section>

        </div>

        <div class="venta-sidebar-footer">
          <div class="venta-v2-total">
            <span class="venta-v2-total-label">Total a pagar</span>
            <span id="total_forma_pago">0</span>
          </div>

          @if($value_solo_transferir == 1)
          <div class="venta-v2-process btn_procesar">
            <button type="button" class="btn btn-warning form-control-sm d-block"
                    onclick="confirmar_documento()" id="btn_procesar_">
              <i class="fa-solid fa-circle-check mr-1"></i>
              PROCESAR VENTA
            </button>
          </div>
          <input type="hidden" name="procesar" id="procesar" value="1">
          @else
          <div class="venta-v2-process btn_procesar">
            <button type="button" class="btn btn-warning form-control-sm d-none"
                    onclick="confirmar_documento()" id="btn_procesar_">
              <i class="fa-solid fa-circle-check mr-1"></i>
              PROCESAR VENTA
            </button>
          </div>
          <input type="hidden" name="procesar" id="procesar" value="0">
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-8 col-xl-9 venta-main">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Carrito de venta</h5>
          <div class="atajos-venta d-none d-md-block">
            <kbd>F2</kbd> Cliente&nbsp; <kbd>F3</kbd> Código&nbsp; <kbd>F4</kbd> Buscar&nbsp; <kbd>F6</kbd> Procesar&nbsp; <kbd>F7</kbd> Nueva
          </div>
        </div>
        <div id="carrito" style="background-color: #f2f4a9;min-height: 400px; border: 1px solid black; border-radius: 10px; padding: 4px;">
            <p class="d-flex justify-content-center">Obteniendo carritoooo...</p>
            <hr>
        </div>
        <fieldset class="mt-3 p-3 accesos-rapidos">
            <legend class="w-auto px-2 mb-3" style="font-size:1rem; font-weight:600; color:#202020;">
                Accesos rápidos de venta
            </legend>

            <div class="row w-100">
                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="card h-100 shadow-sm" style="background-color:#ffffd6; border:1px solid #d6d68a !important; border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3 pb-2" style="color:#1f2937; border-bottom:1px solid #dddd9a; font-weight:600;">
                                <i class="fas fa-barcode mr-2"></i>Ingreso por código
                            </h6>

                            <div class="form-row align-items-end">
                                <div class="col-md-6 mb-2">
                                    <label for="input_codigo" class="small font-weight-bold mb-1">Código</label>
                                    <input type="text"
                                           id="input_codigo"
                                           class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;"
                                           placeholder="Código interno"
                                           onkeyup="enter_codigo(event)"
                                           disabled>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label for="input_cantidad_footer" class="small font-weight-bold mb-1">Cantidad</label>
                                    <input type="text"
                                           id="input_cantidad_footer"
                                           class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;"
                                           placeholder="Cant."
                                           onKeyPress="return soloNumeros(event)"
                                           onKeyup="enter_cant(event)"
                                           disabled>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <button type="button"
                                            class="btn btn-primary btn-sm btn-block"
                                            onclick="agregar_carrito(id_repuesto_codigo,3)"
                                            disabled>
                                        <i class="fas fa-plus mr-1"></i>Agregar
                                    </button>
                                </div>
                            </div>

                            <div id="input_descripcion" class="small text-muted mt-1"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="card h-100 shadow-sm" style="background-color:#ffffd6; border:1px solid #d6d68a !important; border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3 pb-2" style="color:#1f2937; border-bottom:1px solid #dddd9a; font-weight:600;">
                                <i class="fas fa-box-open mr-2"></i>Consignación
                            </h6>

                            <div class="form-row align-items-end">
                                <div class="col-md-8 mb-2">
                                    <label for="input_id_consignacion" class="small font-weight-bold mb-1">Código de consignación</label>
                                    <input type="text"
                                           id="input_id_consignacion"
                                           class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;"
                                           placeholder="Código de consignación"
                                           onKeyPress="return soloNumeros(event)"
                                           onkeyup="soloNumeros(event)"
                                           disabled>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <button type="button"
                                            class="btn btn-primary btn-sm btn-block"
                                            onclick="agregar_consignacion_carrito()"
                                            disabled>
                                        <i class="fas fa-plus mr-1"></i>Agregar
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="local_id_consignacion" id="local_id_consignacion">
                            <input type="hidden" name="cantidad_consignacion" id="cantidad_consignacion">
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="card h-100 shadow-sm" style="background-color:#ffffd6; border:1px solid #d6d68a !important; border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3 pb-2" style="color:#1f2937; border-bottom:1px solid #dddd9a; font-weight:600;">
                                <i class="fas fa-gift mr-2"></i>Giftcard
                            </h6>

                            <div class="form-row align-items-end">
                                <div class="col-md-6 mb-2">
                                    <label for="input_codigo_giftcard" class="small font-weight-bold mb-1">Código de giftcard</label>
                                    <input type="text"
                                           id="input_codigo_giftcard"
                                           class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;"
                                           placeholder="Código de giftcard">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <button type="button"
                                            class="btn btn-success btn-sm btn-block"
                                            onclick="dame_saldo_giftcard()">
                                        <i class="fas fa-check mr-1"></i>Cargar
                                    </button>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <button type="button"
                                            class="btn btn-success btn-sm btn-block"
                                            onclick="crear_vale_mercaderia()">
                                        <i class="fas fa-plus-circle mr-1"></i>Crear vale
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="numero_giftcard_cargado" id="numero_giftcard_cargado" value="">
                            <input type="hidden" name="input_saldo_giftcard" id="input_saldo_giftcard" value="">

                            <div id="div_saldo_giftcard" class="mt-2" style="display:none;">
                                <span class="badge badge-success px-3 py-2" style="font-size:13px;">
                                    <i class="fas fa-wallet mr-1"></i>
                                    Saldo Giftcard: $<span id="saldo_giftcard_valor">0</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="card h-100 shadow-sm" style="background-color:#ffffd6; border:1px solid #d6d68a !important; border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3 pb-2" style="color:#1f2937; border-bottom:1px solid #dddd9a; font-weight:600;">
                                <i class="fas fa-receipt mr-2"></i>Abono
                            </h6>

                            <div class="form-row align-items-end">
                                <div class="col-md-8 mb-2">
                                    <label for="input_num_abono" class="small font-weight-bold mb-1">Código de abono</label>
                                    <input type="text"
                                           id="input_num_abono"
                                           class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;"
                                           placeholder="Código de abono"
                                           onKeyPress="return soloNumeros(event)"
                                           onkeyup="soloNumeros(event)">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <button type="button"
                                            class="btn btn-primary btn-sm btn-block"
                                            onclick="agregar_abono_carrito()">
                                        <i class="fas fa-plus mr-1"></i>Agregar
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="input_cantidad_pedido" id="input_cantidad_pedido">
                            <input type="hidden" name="input_saldopendiente_pedido" id="input_saldopendiente_pedido">
                            <input type="hidden" name="input_total_pedido" id="input_total_pedido">
                            <input type="hidden" name="input_repuesto_pedido" id="input_repuesto_pedido">
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>


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
        <div class="row align-items-center" style="width:100%; padding:4px 14px 12px; margin:0;">
            <div class="col-2 alin-der" style="padding-right:10px;color:white">Buscar:</div>
            <div class="col-10" style="padding:0 8px;">
                <input type="search" class="form-control" style="height:38px;padding:6px 14px;" name="txtDescripcion" id="buscar_por_descripcion" placeholder="¿Qué desea encontrar?" onkeyup="enter_press(event,'d')" onclick="clic_en(1)">
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
      <div class="modal-body modal-body-buscar"> <!-- CONTENIDO-->
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

<!-- VENTANA MODAL MOSTRAR COTIZACIONES"-->
<div role="dialog" tabindex="-1"
     class="modal fade historial-modal historial-cotizaciones"
     id="mostrar-cotizaciones-modal_historial">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <span class="modal-title-icon">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </span>
          Historial de cotizaciones
        </h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="historial-toolbar">
        <div class="historial-toolbar-info">
          <span class="historial-counter" id="contador_cotizaciones_visible">0</span>
          <span>
            de <strong id="contador_cotizaciones_total">0</strong> cotizaciones visibles
          </span>
        </div>

        <div class="historial-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text"
                 id="buscar_historial_cotizaciones"
                 placeholder="Buscar por número, fecha, nombre, cliente o estado..."
                 autocomplete="off"
                 oninput="filtrar_historial('cotizaciones')">

          <button type="button"
                  class="historial-search-clear"
                  onclick="limpiar_filtro_historial('cotizaciones')"
                  title="Limpiar búsqueda">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <div class="modal-body p-0">
        <div class="historial-table-wrap">
          <table class="table historial-table">
            <colgroup>
              <col style="width:11%">
              <col style="width:15%">
              <col style="width:29%">
              <col style="width:20%">
              <col style="width:13%">
              <col style="width:12%">
            </colgroup>

            <thead>
              <tr>
                <th>N° cotización</th>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>

            <tbody id="listado_cotizaciones_historial"></tbody>
          </table>
        </div>
      </div>

      <div class="historial-footer">
        <span>
          <i class="fa-solid fa-circle-info mr-1"></i>
          Seleccione el número o el carrito para recuperar una cotización.
        </span>

        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
          Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Busqueda Repuesto Xpress MOdal -->
<div class="modal fade"
     tabindex="-1"
     role="dialog"
     id="buscar-xpress-modal"
     aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <span class="buscar-xpress-title-icon">
            <i class="fa-solid fa-bolt"></i>
          </span>
          Búsqueda rápida de repuestos
        </h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="buscar-xpress-search">
        <div class="buscar-xpress-search-grid">
          <div>
            <label for="repuesto-xpress-codigo" class="buscar-xpress-label">
              Código interno del repuesto
            </label>

            <div class="buscar-xpress-input-wrap">
              <i class="fa-solid fa-barcode"></i>

              <input
                type="text"
                id="repuesto-xpress-codigo"
                placeholder="Ingrese o escanee el código interno"
                autocomplete="off"
                onkeyup="enter_buscar_repuesto_xpress(event)">
            </div>

            <p id="mensaje_modal">
              Ingrese un código para consultar descripción, stock y precio.
            </p>

            <div id="fecha_actualizacion_precio"></div>
          </div>

          <button
            type="button"
            class="buscar-xpress-btn"
            onclick="buscar_repuesto_xpress()">
            <i class="fa-solid fa-magnifying-glass mr-1"></i>
            Buscar repuesto
          </button>
        </div>
      </div>

      <div class="modal-body buscar-xpress-results">
        <div class="buscar-xpress-table-card">
          <div class="table-responsive">
            <table class="table buscar-xpress-table">
              <colgroup>
                <col style="width:22%">
                <col style="width:9%">
                <col style="width:37%">
                <col style="width:10%">
                <col style="width:13%">
                <col style="width:9%">
              </colgroup>

              <thead>
                <tr>
                  <th>Descripción</th>
                  <th>Actualización</th>
                  <th>Stock por ubicación</th>
                  <th>Cantidad</th>
                  <th>Procedencia</th>
                  <th>Precio venta</th>
                </tr>
              </thead>

              <tbody id="informacion_repuesto">
                <tr>
                  <td colspan="6">
                    <div class="buscar-xpress-empty">
                      <div class="buscar-xpress-empty-icon">
                        <i class="fa-solid fa-box-open"></i>
                      </div>

                      <strong>Sin resultados todavía</strong>
                      <span>Ingrese un código para mostrar la información del repuesto.</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="buscar-xpress-footer">
        <span class="buscar-xpress-help">
          <i class="fa-solid fa-circle-info mr-1"></i>
          Seleccione el local, ingrese la cantidad y agregue el producto.
        </span>

        <div class="d-flex buscar-xpress-footer-actions" style="gap:8px;">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
            Cancelar
          </button>

          <button
            type="button"
            id="btn_agregar_repuesto_buscado"
            disabled>
            <i class="fa-solid fa-cart-plus mr-1"></i>
            Agregar al carrito
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- FIN Busqueda Repuesto Xpress MOdal -->

<!-- Repuesto Xpress Modal -->
<div class="modal fade"
     tabindex="-1"
     role="dialog"
     id="repuesto-xpress-modal"
     aria-labelledby="repuesto-xpress-modal-title"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content"
         style="border:0;border-radius:16px;overflow:hidden;box-shadow:0 22px 60px rgba(15,23,42,.28);">

      <div class="modal-header"
           style="padding:18px 22px;border-bottom:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#f8fafc);">

        <div class="d-flex align-items-center" style="gap:12px;">
          <div class="d-flex align-items-center justify-content-center"
               style="width:46px;height:46px;flex:0 0 46px;border-radius:13px;color:#fff;background:linear-gradient(135deg,#2563eb,#0ea5e9);box-shadow:0 7px 18px rgba(37,99,235,.25);font-size:20px;">
            <i class="fa-solid fa-box-open"></i>
          </div>

          <div>
            <h5 class="modal-title mb-0"
                id="repuesto-xpress-modal-title"
                style="color:#172033;font-size:19px;font-weight:800;">
              Crear repuesto Xpress
            </h5>

            <small style="display:block;margin-top:3px;color:#64748b;font-size:12px;">
              Registro rápido para agregar un producto directamente al carrito.
            </small>
          </div>
        </div>

        <button type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Cerrar"
                style="width:38px;height:38px;margin:0;padding:0;border:1px solid #cbd5e1;border-radius:10px;color:#475569;background:#fff;opacity:1;">
          <span aria-hidden="true" style="font-size:24px;line-height:1;">&times;</span>
        </button>
      </div>

      <div class="modal-body" style="padding:22px;background:#fff;">

        <div class="alert alert-primary d-flex align-items-start mb-4"
             style="gap:10px;padding:11px 13px;border-radius:11px;font-size:12px;">
          <i class="fa-solid fa-circle-info mt-1"></i>
          <span>
            Complete la descripción, cantidad y precio unitario.
            El código de referencia es opcional.
          </span>
        </div>

        <div class="form-row">

          <div class="form-group col-md-8">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label for="xpress-codigo"
                     class="mb-0"
                     style="color:#334155;font-size:12px;font-weight:800;">
                Código de referencia
              </label>

              <span class="badge badge-light"
                    style="padding:4px 8px;border:1px solid #e2e8f0;color:#64748b;font-size:9px;">
                OPCIONAL
              </span>
            </div>

            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"
                      style="border-color:#cbd5e1;background:#f8fafc;color:#64748b;">
                  <i class="fa-solid fa-barcode"></i>
                </span>
              </div>

              <input type="text"
                     id="xpress-codigo"
                     class="form-control"
                     placeholder="Ej.: XP-001"
                     autocomplete="off"
                     maxlength="50"
                     style="height:44px;border-color:#cbd5e1;">
            </div>

            <small class="form-text text-muted">
              Puede utilizar el código informado por el proveedor.
            </small>
          </div>

          <div class="form-group col-md-4">
            <label for="xpress-cantidad"
                   class="mb-2"
                   style="color:#334155;font-size:12px;font-weight:800;">
              Cantidad
            </label>

            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"
                      style="border-color:#cbd5e1;background:#f8fafc;color:#64748b;">
                  <i class="fa-solid fa-boxes-stacked"></i>
                </span>
              </div>

              <input type="number"
                     id="xpress-cantidad"
                     class="form-control"
                     placeholder="1"
                     min="1"
                     step="1"
                     inputmode="numeric"
                     autocomplete="off"
                     style="height:44px;border-color:#cbd5e1;">
            </div>

            <small class="form-text text-muted">
              Unidades a agregar.
            </small>
          </div>

          <div class="form-group col-12">
            <label for="xpress-descripcion"
                   class="mb-2"
                   style="color:#334155;font-size:12px;font-weight:800;">
              Descripción del repuesto
            </label>

            <textarea id="xpress-descripcion"
                      class="form-control"
                      rows="3"
                      placeholder="Escriba una descripción clara del producto..."
                      autocomplete="off"
                      maxlength="255"
                      style="min-height:96px;border-color:#cbd5e1;border-radius:10px;resize:vertical;"></textarea>

            <small class="form-text text-muted">
              Incluya marca, modelo, medida u otra información útil para identificarlo.
            </small>
          </div>

          <div class="form-group col-12 mb-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label for="xpress-precio"
                     class="mb-0"
                     style="color:#334155;font-size:12px;font-weight:800;">
                Precio unitario
              </label>

              <span class="badge badge-success"
                    style="padding:4px 8px;font-size:9px;">
                IVA INCLUIDO
              </span>
            </div>

            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"
                      style="min-width:44px;justify-content:center;border-color:#86efac;background:#f0fdf4;color:#166534;font-weight:900;">
                  $
                </span>
              </div>

              <input type="text"
                     id="xpress-precio"
                     class="form-control"
                     placeholder="0"
                     inputmode="numeric"
                     autocomplete="off"
                     data-toggle="tooltip"
                     title="Ingrese solamente números, sin puntos ni comas"
                     style="height:46px;border-color:#86efac;color:#166534;font-size:17px;font-weight:800;">
            </div>

            <small class="form-text text-muted">
              Ingrese el valor final de venta por cada unidad.
            </small>
          </div>

        </div>
      </div>

      <div class="modal-footer d-flex align-items-center justify-content-between"
           style="padding:15px 22px;border-top:1px solid #e2e8f0;background:#f8fafc;">

        <small class="text-muted d-none d-md-flex align-items-center" style="gap:7px;">
          <i class="fa-solid fa-shield-halved"></i>
          Los campos obligatorios serán validados antes de guardar.
        </small>

        <div class="d-flex ml-auto" style="gap:9px;">
          <button type="button"
                  class="btn btn-outline-secondary"
                  data-dismiss="modal"
                  style="min-width:110px;border-radius:9px;font-weight:700;">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Cancelar
          </button>

          <button type="button"
                  class="btn btn-success"
                  onclick="agregar_repuesto_xpress()"
                  style="min-width:165px;border-radius:9px;font-weight:800;box-shadow:0 5px 14px rgba(21,128,61,.18);">
            <i class="fa-solid fa-cart-plus mr-1"></i>
            Crear y agregar
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- FIN Repuesto Xpress Modal -->

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
            <input class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;" type="text" id="dcmto" style="width:150px" maxlength="20">
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
                    <input class="form-control form-control-sm" style="background-color:#fffef0; border-color:#bfc0a0;" type="text" id="dcmto_en_envio" style="width:150px" maxlength="20" disabled>
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
<!-- VENTANA MODAL TRANSFERIR CARRITO"-->
<div role="dialog" tabindex="-1" class="modal fade" id="transferir-carrito-modal">
  <div class="modal-dialog modal-sm" role="document" >
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="elegir_carrito">
          <h5 style="text-align: center"><strong><p id="titulo_nombre_carrito">Transferir carrito</p></strong></h5>
          <div id="nombres-cajeros"></div>
       </div>
       <div id="botonera_carrito">
         <button class="btn btn-success btn-xs w-100" onclick="transferir_carrito_confirmar()">Enviar</button>
       </div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL ELEGIR CARRITO"-->
<!-- Modal -->
<div class="modal fade" id="modalNuevaFormaPagoCredito" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos Tarjeta Crédito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault_credito" id="flexRadioDefault1" value="1" >
          <label class="form-check-label" for="flexRadioDefault1">
            Banco Estado
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault_credito" id="flexRadioDefault2" value="2">
          <label class="form-check-label" for="flexRadioDefault2">
            Getnet
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal" onclick="agregar_detalle_credito()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modalNuevaFormaPagoDebito" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos Tarjeta Débito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault_debito" id="flexRadioDefault1" value="1" >
          <label class="form-check-label" for="flexRadioDefault1">
            Banco Estado
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault_debito" id="flexRadioDefault2" value="2">
          <label class="form-check-label" for="flexRadioDefault2">
            Getnet
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="agregar_detalle_debito()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal_precionormal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información Oferta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_info_repuesto">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade historial-modal historial-consignaciones"
     id="modalHistorialConsignaciones"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <span class="modal-title-icon">
            <i class="fa-solid fa-boxes-stacked"></i>
          </span>
          Historial de consignaciones
        </h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="historial-toolbar">
        <div class="historial-toolbar-info">
          <span class="historial-counter" id="contador_consignaciones_visible">0</span>
          <span>
            de <strong id="contador_consignaciones_total">0</strong> consignaciones visibles
          </span>
        </div>

        <div class="historial-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text"
                 id="buscar_historial_consignaciones"
                 placeholder="Buscar por número, fecha, descripción o estado..."
                 autocomplete="off"
                 oninput="filtrar_historial('consignaciones')">

          <button type="button"
                  class="historial-search-clear"
                  onclick="limpiar_filtro_historial('consignaciones')"
                  title="Limpiar búsqueda">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <div class="modal-body p-0">
        <div class="historial-table-wrap">
          <table class="table historial-table">
            <colgroup>
              <col style="width:13%">
              <col style="width:18%">
              <col style="width:45%">
              <col style="width:12%">
              <col style="width:12%">
            </colgroup>

            <thead>
              <tr>
                <th>N° vale</th>
                <th>Fecha</th>
                <th>Descripción y estado</th>
                <th colspan="2" class="text-right">Acciones</th>
              </tr>
            </thead>

            <tbody id="tbody_vales"></tbody>
          </table>
        </div>

        <div id="detalle_consignacion" class="historial-detail"></div>
      </div>

      <div class="historial-footer">
        <span>
          <i class="fa-solid fa-circle-info mr-1"></i>
          Puede cargar, imprimir o eliminar una consignación desde sus acciones.
        </span>

        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
          Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Modal info repuesto -->
<!-- Modal -->
<div class="modal fade" id="modalInfoRepuesto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información del Repuesto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBodyInfoRepuesto">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- VENTANA MODAL CREAR VALE DE MERCADERIA -->
<div role="dialog" tabindex="-1" class="modal fade" id="crear-vale-mercaderia-modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #5b60e9; color: white;">
        <h5 class="modal-title">CREAR VALE DE MERCADERÍA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="formulario_ingreso">
          <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_" style="max-width: 100px; display: block; margin: 0 auto 15px;">
          
          <div class="form-group">
            <label for="rut_cliente">Rut</label>
            <input type="text" class="form-control" id="rut_cliente" name="rut_cliente">
          </div>
          
          <div class="form-group">
            <label for="nombre_cliente">Nombre</label>
            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente">
          </div>
          
          <div class="form-group">
            <label for="telefono_cliente">Teléfono</label>
            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente">
          </div>
          
          <div class="form-group">
            <label for="descripcion">Descripción del repuesto</label>
            <input type="text" class="form-control" id="descripcion" name="descripcion">
          </div>
          
          <div class="form-group">
            <div class="row">
              <div class="col-md-4 d-flex mt-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo_documento" id="vale_boleta" value="bo" checked>
                  <label class="form-check-label" for="vale_boleta">
                    Boleta
                  </label>
                </div>
                <div class="form-check ml-3">
                  <input class="form-check-input" type="radio" name="tipo_documento" id="vale_factura" value="fa">
                  <label class="form-check-label" for="vale_factura">
                    Factura
                  </label>
                </div>
              </div>
              <div class="col-md-8">
                <label for="numero_documento">N° de Boleta / N° de Factura</label>
                <input type="text" class="form-control" id="numero_documento" name="numero_documento" onKeyPress="return soloNumeros(event)">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="valor">Valor</label>
            <input type="text" class="form-control" id="valor" name="valor" onKeyPress="return soloNumeros(event)">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-warning btn-sm" onclick="imprimir_boucher()">Imprimir</button>
        <button class="btn btn-success btn-sm" onclick="guardar_vale_mercaderia()">Guardar Vale</button>
        <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div> <!-- FIN VENTANA MODAL CREAR VALE DE MERCADERIA -->

<input type="hidden" name="oferta" id="oferta" value="0">
<input type="hidden" name="cliente_carrito_transferido" id="cliente_carrito_transferido">
</div> <!-- fin container-fluid -->
@endsection

<script>
(function () {
    function ajustarAlturaSidebarVenta() {
        var panel = document.querySelector('.venta-sidebar-panel');

        if (!panel || window.innerWidth < 992) {
            if (panel) {
                panel.style.removeProperty('--venta-sidebar-height');
            }
            return;
        }

        var posicionSuperior = panel.getBoundingClientRect().top;
        var margenInferior = 12;
        var alturaDisponible = Math.max(520, window.innerHeight - posicionSuperior - margenInferior);

        panel.style.setProperty('--venta-sidebar-height', alturaDisponible + 'px');
    }

    window.addEventListener('load', ajustarAlturaSidebarVenta);
    window.addEventListener('resize', ajustarAlturaSidebarVenta);
    document.addEventListener('DOMContentLoaded', ajustarAlturaSidebarVenta);
})();
</script>
