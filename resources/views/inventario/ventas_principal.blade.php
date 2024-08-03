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
          $('#mensaje_modal').html('Buscando ...');
        },
        success: function(resp){
          $('#mensaje_modal').html('Listo');
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
            $('#informacion_repuesto').append(`
            <tr> 
                  <td class='letra_pequeña'>`+e.descripcion+` </td>
                  <td class='letra_pequeña `+clase+` text-center'>`+dias+` </td>
                  <td class='letra_pequeña'>`+e.ubicacion+`(`+e.stock_actual+`) </td>
                  
                  <td class='letra_pequeña'>`+e.ubicacion_dos+`(`+e.stock_actual_dos+`) </td>
                  
                  <td class='letra_pequeña'>`+e.ubicacion_tres+`(`+e.stock_actual_tres+`) </td>
                  <td class='letra_pequeña'><input type='text' placeholder='Cantidad' id='input_cantidad' onkeypress="return soloNumeros(event)" onkeyup="soloNumeros(event)"  /></td>
                  <td><select id='select_locales'>
                    
                  </select></td>
                  <td class='letra_pequeña'>$ `+Number(e.precio_venta).toFixed(0)+`</td>
                </tr>
            `);
            $('#btn_agregar_repuesto_buscado').attr('onclick','agregar_repuesto_buscado('+e.id+')');
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

    if(cual==3){ //desde ingreso por código
        cant=document.getElementById("input_cantidad").value.trim();
        
        idlocal=$('#select_locales').val();
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
          
          if(resp == 'OK'){
            Vue.swal({
              icon:'info',
              title:'En construccion'
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
            
            $("#mensajes-modal").html("Total: "+t);
            document.getElementsByName("forma_pago_monto")[0].value=t.replace(/\./g,"");
            document.getElementById("buscar_por_descripcion").value="";
            dame_carrito();
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
    
    // Limpiar total
    // $('#monto-1').val('');
    // // Petición
    // var url="{{url('ventas/dame_carrito')}}"; //fragm.ventas_carrito
    
    //   $.ajax({
    //    type:'GET',
    //    beforeSend: function () {
    //     //$("#carrito").html("Buscando Carrito...");
    //       },
    //    url:url,
    //    success:function(carrito){

    //     $("#carrito").html(carrito);
    //    },
    //     error: function(error){
    //       $('#carrito').html(formatear_error(error.responseText));
    //     }

    //   }); //Fin petición
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
          $('#btn_procesar_').removeClass('d-block');
          $('#btn_procesar_').addClass('d-none');
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
        if(resp=='transferidos')
            $('#carrito').html("<h4 class='alert alert-info d-flex justify-content-center'>Carrito Vacio</h4><input type='hidden' id='items_carrito' value='0'>");
            $('#total_forma_pago').html("0");
            dame_formas_pago();
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
  let cliente_id = Math.floor(Math.random() * 1000);
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
            precio_a_modifikar=parseInt(document.getElementById("pv-"+idrep).value.replace(/\./g,""));
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
        new_val=parseInt(new_val_tmp.value.replace(/\./g,""));
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
            agregar_carrito_transferido(idrep);
          }else {
            agregar_carrito(idrep,3);
          }
          
          $('#informacion_repuesto').empty();
          $('#repuesto-xpress-codigo').val('');
          $('#repuesto-xpress-codigo').focus();
          $('#buscar-xpress-modal').modal('hide');
    }
    
  }

  function agregar_carrito_transferido(idrep){
    let url = '{{url("ventas/agregar_carrito_transferido")}}';
    let idlocal=$('#select_locales').val();
    let cant=document.getElementById("input_cantidad").value.trim();
    let cliente_id = $('#cliente_id').val();
    let carrito_id = $('#carrito_id').val();
    let data = {idrep: idrep, idlocal: idlocal, cantidad: cant, cliente_id: cliente_id, carrito_id: carrito_id};
    // headings para el ajax
    let headers = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};
    $.ajax({
      type:'POST',
      headers: headers,
      url:url,
      data:data,
      success:function(rpta){
        // return console.log(rpta);
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
    var dcto = Number(document.getElementById(d).value.replace(/\./g,""));
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
      monto=Number(document.getElementById(m).value.replace(/\./g,""));
      
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
    let dcto = Number(document.getElementById('formita-descuento').value); 
    let total_carrito=Number(document.getElementById("total_carrito").value);
    // El descuento se divide por 100 para sacar el porcentaje y ese resultado se multiplica por el total del carrito.
    // Con esto calculamos el valor a restar con el descuento aplicado.
    // El resultado anterior se resta al total del carrito.
    let total_carrito_descuento = Number(total_carrito) - Number((dcto/100) * Number(total_carrito)); 
    let total_pagado=Number(total_pago);
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
      $('#modalNuevaFormaPagoCredito').modal('show'); // abrir
    }

    if((document.getElementById("formita-5").checked) && id==5 )
    {
      $('#modalNuevaFormaPagoDebito').modal('show'); // abrir
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

            },
            url:url,
            data:parametros,
            success:function(resp){
                console.log(resp);
               
                let r=JSON.parse(resp);
                if(r.estado='OK'){
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
                    title: 'ERRORRR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });
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

            },
            url:url,
            data:parametros,
            success:function(resp){
                console.log(resp);
                let r=JSON.parse(resp);
                if(r.estado='OK'){
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

    function historial_consignaciones(){
      Vue.swal.close();
      let id_cliente=document.getElementById("id_cliente").value;
      $('#modalHistorialConsignaciones').modal("show");
      let url = "/clientes/dame_vales_consignacion";
      $.ajax({
          type:'get',
          url: url,
          success: function(vales){
              console.log(vales);
              $('#tbody_vales').empty();
              let d=new Date();
              let hoy="'"+d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate()+"'";
              if(vales.length > 0){
                
                vales.forEach(v => {
                  //la fecha la ponemos en formato YYYY-MM-DD
                let s=v.fecha_emision.split("-");
                let fec="'"+s[2]+"-"+s[1]+"-"+s[0]+"'";
                let dd=diferencia_dias(hoy,fec);
                if(dd>21){

                }else{
                  let state;
                  let clase;
                  if(v.activo == 0){
                      state = "PROCESADO";
                  }else{
                      state = "PENDIENTE";
                  }
                  $('#tbody_vales').append(`
                                  <tr>
                                      <td> <a href="javascript:void(0)" onclick="cargar_consignacion(`+v.num_consignacion+`)"> `+v.num_consignacion+` </a></td>
                                      <td>`+v.fecha_emision+` </td>
                                      <td>`+v.nombre_consignacion+` </td>
                                      <td><button class='btn btn-success btn-sm' onclick='imprimir_vale_consignacion_historial("`+v.url_pdf+`")'><i class="fa-solid fa-print"></i> </button> </td>
                                      <td><button class='btn btn-danger btn-sm' onclick='eliminar_consignacion(`+v.id+`)'>X</button></td>
                                  </tr>
                                  
              `);
                }
                  
              });
              }else{
                $('#tbody_vales').append(`
                <tr>
                  <td>No hay consignaciones. </td>
                  </tr>
                `);
              }
              
          },
          error: function(error){
              console.log(error.responseText);
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
      let id_cliente=document.getElementById("id_cliente").value;
    //nombre_cotizacion_buscar=document.getElementById("nombre_cotizacion_buscar").value;
    
    let url='{{url("/ventas/damehistorialcotizaciones")}}'+'/'+id_cliente;

      $.ajax({
       type:'GET',
       url:url,
       success:function(resp){
        var user = resp[1];
        var cotizaciones = resp[0];
        var dato=cotizaciones.substr(0,2);
        //REVISAR: Mejorar esto para que no tenga conflictos en las llaves
        if(dato=="[{") //viene un json
        {
          var c=JSON.parse(cotizaciones);
          if(c.length>0)
          {
            //abrir modal mostrando los números de cotización y clientes para luego elegir y cargarla al carrito.
            var cotiz="<strong>Fecha y Nombre</strong><br>";
            var cotiz_ = "";

            $("#subtitulo-listado-cotizaciones").html("(HISTORIAL)");
            let d=new Date();
            let hoy="'"+d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate()+"'";
            c.forEach(function(coti)
            {
                //la fecha la ponemos en formato YYYY-MM-DD
                let s=coti.fecha.split("-");
                let fec="'"+s[2]+"-"+s[1]+"-"+s[0]+"'";
                let dd=diferencia_dias(hoy,fec);
                if(dd>21){
                    //cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:red\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+" cli:"+(coti.elcliente==undefined ? " Ninguno": coti.elcliente)+"</a><br>";
                }else{
                  if(coti.elcliente == undefined){
                    var cli = 'NINGUNO';
                  }else{
                    var cli = coti.elcliente;
                  }

                  var estado = "";
                  if(coti.activo == 0) estado = "RECHAZADO";
                  if(coti.activo == 1) estado = "ESPERANDO";
                  if(coti.activo == 2) estado = "CONFIRMADO";
                  if(user.role_id == 10){
                    var boton_html = `<button class="btn btn-danger btn-sm" onclick="eliminar_cotizacion(`+coti.num_cotizacion+`)"><i class="fa-solid fa-trash"></i></button>`;
                  }else{
                    var boton_html = "";
                  }
                  
                  if(estado == "CONFIRMADO"){
                    cotiz_ += `
                      <a href='javascript:void(0)' onclick='cargar_cotizacion(`+coti.num_cotizacion+`)'>
                        <tr>
                          <td><a href="javascript:void(0)" onclick="cargar_cotizacion(`+coti.num_cotizacion+`)">`+coti.num_cotizacion+` </a> </td>
                          <td>`+coti.fecha+` </td>
                          <td>`+coti.nombre_cotizacion+` </td>
                          <td>`+cli+`</td>
                          <td>`+estado+` </td>
                          <td>
                            <button class="btn btn-success btn-sm" onclick="imprimir_cotizacion(`+coti.num_cotizacion+`)"><i class="fa-solid fa-print"></i></button> 
                            `+boton_html+`
                          </td>
                        </tr>
                      </a>
                    `;
                  }else{
                    cotiz_ += `
                    <a href='javascript:void(0)'>
                      <tr>
                        <td><a href="javascript:void(0)">`+coti.num_cotizacion+` </a> </td>
                        <td>`+coti.fecha+` </td>
                        <td>`+coti.nombre_cotizacion+` </td>
                        <td>`+cli+`</td>
                        <td>`+estado+` </td>
                        <td><button class="btn btn-success btn-sm" onclick="imprimir_cotizacion(`+coti.num_cotizacion+`)"><i class="fa-solid fa-print"></i></button> </td>
                      </tr>
                    </a>
                    `;
                  }
                  
                    cotiz+="<a href=\"javascript:void(0);\" onclick=\"cargar_cotizacion("+coti.num_cotizacion+")\" style=\"color:blue\">N°: "+coti.num_cotizacion+" Fech:"+coti.fecha+" Nom:"+coti.nombre_cotizacion+" cli:"+(coti.elcliente==undefined ? " Ninguno": coti.elcliente)+"</a><br>";
                  
                    
                }
                
            });
            
            $("#listado_cotizaciones_historial").html(cotiz_);
            $("#mostrar-cotizaciones-modal_historial").modal("show");
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
            if(docu=='CONSIGNACION'){
                consignar();
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

function generar_xml()
{
  //Se crea un div con id es_oferta donde se guarda si tiene algun repuesto en oferta. 
  let es_oferta = $('#es_oferta').val();
  let id_cliente = $('#id_cliente').val();
//Si es oferta se debe consultar si desea mantener la oferta o colocar el precio normal.
  if(es_oferta == 1){
          Vue.swal({
                  title: 'Oferta, ¿Como desea Cancelar?',
                  text: "¡Solo si paga con efectivo o transferencia se respeta el precio oferta!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Precio Oferta',
                  cancelButtonText: 'Precio Normal'
                }).then((result) => {
                  if (result.isConfirmed) {
                    //Dejamos disabled las formas de pago que no sean efectivo o transferencia
                    $('#formita-2').attr('disabled','disabled');
                    $('#formita-3').attr('disabled','disabled');
                    $('#formita-5').attr('disabled','disabled');
                    $('#formita-6').attr('disabled','disabled');
                    $('#es_oferta').val('0');
                    $('#confirmado').val('ofertado');
                    //Volvemos la oferta a 0 ya que se selecciono alguna opción.
                    es_oferta = 0;
                  }else{
                    //Funcion creada para cambiarle el valor al carrito de compras y quitarle el precio oferta
                    let si_carrito_transferido = $('#es_transferido').val();
                    if(si_carrito_transferido == 1){
                      recargar_carrito_transferido();
                    }else{
                      recargar_carrito();
                    
                    }
                    es_oferta = 0;
                    $('#confirmado').val('normal');
                      
                  }

                });
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

    let descuento = $('#formita-descuento').val();
    //Verificamos si esta en oferta
    let o = $('#es_oferta').val();
    let confirmado = $('#confirmado').val();

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
                    confirmado: confirmado
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
                    confirmado: confirmado
                };
    }

   
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
        type:'POST',
        beforeSend: function () {
        $("#mensajes").html("<b>Generando XML...</b>");
        $("#envio-txt").html("<b>Generando XML...</b>");
        },
        url:url,
        data:parametros,
        success:function(rs){ //llega un JSON
            console.log(rs);
         
            let rpta=JSON.parse(rs);
            
            if(rpta.estado=='GENERADO')
            {
                $("#mensajes").html("Generando XML...<b> Listo!!!</b>");
                $("#envio-txt").html("<b>Generando XML...</b> Listo!!!");
                documento_procesado=rpta.mensaje; //tiene el tipo y número de documento que se está procesando.
                document.getElementById("dcmto_en_envio").value=documento_procesado;
               
                abrir_procesar_envio();
                
                // Vue.swal({
                //     title: '<h3><i><strong>PROCESANDO '+documento_procesado.toUpperCase()+'</strong></i></h3>',
                //     html:'<h3 id="envio-txt">Generando XML...</h3>'+
                //             '<button  class="btn btn-info form-control-sm" onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>'+
                //             '<button  class="btn btn-warning form-control-sm" disabled onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>'+
                //             '<button  class="btn btn-success form-control-sm" disabled onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                //     allowOutsideClick:false,
                //     allowEscapeKey:false,
                //     showConfirmButton:false,
                //     showCancelButton:true,
                //     cancelButtonText:"CERRAR" //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estado()
                //     });


                    
                   
            }else{
                Vue.swal({
                    title: rpta.estado,
                    text: rpta.mensaje,
                    icon: 'error',
                    });
            }
        },
        error: function(error){
          console.log(error.responseText);
            // Vue.swal({
            //     title: 'ERROR',
            //     text: formatear_error(error.responseText),
            //     icon: 'error',
            //     });
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

    let marca_credito=document.getElementById("formita-2").checked;
    let marca_debito=document.getElementById("formita-5").checked;
    let consignacion = $('#local_id_consignacion').val();

    var nfp_credito = 0;
    var nfp_debito = 0;
    if(marca_credito){
      nfp_credito = $('input:radio[name=flexRadioDefault_credito]:checked').val();
    }

    if(marca_debito){
      nfp_debito = $('input:radio[name=flexRadioDefault_debito]:checked').val();
    }
    
    if(nfp_credito == undefined) nfp_credito = 0;
    if(nfp_debito == undefined)  nfp_debito = 0;

    document.getElementById("btn-enviarsii").disabled=true;
    let vendedor_id = $('#vendedor_id').val();
    let carrito_id = $('#carrito_id').val();
    let cliente_id = $('#cliente_id').val();
    let num_abono = $('#input_num_abono').val();
    
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

      let descuento = $('#formita-descuento').val();

      if(!vendedor_id){
        parametros={
          idcliente:cliente['id'],
          docu:docu,venta:venta,
          forma_pago:id_forma_pago,
          nfp_credito: 2,
          nfp_debito: 2,
          monto:monto_forma_pago,
          referencia:referencia_forma_pago,
          num_abono:num_abono,
          dcto: descuento,
          consignacion: consignacion};
      }else{
        parametros={
          idcliente:cliente['id'],
          docu:docu,venta:venta,
          forma_pago:id_forma_pago,
          nfp_credito: 2,
          nfp_debito: 2,
          monto:monto_forma_pago,
          referencia:referencia_forma_pago,
          vendedor_id: vendedor_id, 
          carrito_id: carrito_id, 
          cliente_id: cliente_id,
          dcto: descuento};
      }
        
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
          console.log(rs);
           let carrito_id = $('#carrito_id').val();
           let cliente_id = $('#cliente_id').val();
           let consignacion = $('#local_id_consignacion').val();
            let rpta=JSON.parse(rs);
           
            if(rpta.estado=='OK')
            {
                $("#mensajes").html("Enviando al SII...<b>Recibido !!!</b>");
                $("#envio-txt").html("<small>"+rpta.mensaje+" TrackID: "+rpta.trackid+"</small>");
                document.getElementById("btn-enviarsii").disabled=true;
                document.getElementById("btn-verestado").disabled=false;
                document.getElementById("btn-imprimir").disabled=false;
                
                TrackID=rpta.trackid;
                
                borrar_carrito_transferido(cliente_id);
                //limpiar_todo();
                borrar_carrito('actual');
                // Limpiamos el input de monto_efectivo
                document.getElementById("monto_efectivo").value='';
                reiniciar_descuento();
                if(consignacion !== ''){
                  //console.log('eliminando consignacion');
                  eliminar_consignacion_numero(consignacion);
                }
                //Limpiamos el parametro que indica si es consignación
                document.getElementById("local_id_consignacion").value='';
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
  <div class="row titulazo" style="width: 100%;">
  <div class="col-sm-11" style="width:95%"><center><h4>VENTAS</h4></center></div>
  <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-idea-web.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

  @section('mensajes')
    @include('fragm.mensajes')
  @endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
    <div class="btn-toolbar" role="toolbar" style="margin-bottom:3px;background-color:peachpuff;
    border: 1px solid black;
    padding: 5px;
    border-radius: 10px;">
        <div class="btn-group btn-group-sm mr-10" role="group">
          
            
            <button class="btn btn-danger" style="border: 1px solid black;border-radius: 10px;" onclick="nueva_venta()">Nueva Venta</button> 
            @if($value_busqueda_cliente == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-primary btn-sm" style="border: 1px solid black;border-radius: 10px;" onclick="buscar_cliente()">Buscar Cliente</button>@endif
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
        <div class="btn-group btn-group-sm ml-auto" role="group">
            @if($value_transferir_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-success" onclick="transferir_carrito()" style="border: 1px solid black;border-radius: 10px;"><small>Transferir Carrito</small></button>@endif
            @if($value_borrar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-danger"  onclick="borrar_carritos_guardados()" style="border: 1px solid black;border-radius: 10px;"><small>Borrar Carritos Guardados</small></button>@endif
            @if($value_recuperar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-info" onclick="mostrar_nombres_carrito()" style="border: 1px solid black;border-radius: 10px;" >Recuperar Carrito</button>@endif
            @if($value_guardar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-warning" onclick="poner_nombre_carrito()" style="border: 1px solid black;border-radius: 10px;" >Guardar Carrito</button>@endif
            @if($value_borrar_carrito == 0 || Auth::user()->rol->nombrerol == "Administrador")<button type="button" class="btn btn-danger" onclick="borrar_carrito('actual')" style="border: 1px solid black;border-radius: 10px;"><small>Borrar Carrito Actual</small></button>@endif
        </div>
        
    </div>

<div class="row">
    <div class="col-sm-3" style="background-color: #f2f4a9;;height:60%; padding-bottom: 30px; border: 1px solid black; border-radius: 10px;">
        <div class="row row-15" id="el_cliente">
            <div id="mensaje-cliente"></div>
                <div class="col-sm-12" style="margin:0px; padding:0px;">
                    <input type="hidden" id="id_cliente" value="0">
                    {{-- <p id="cliente_rut" class="font-weight-bold font-italic" style="margin-bottom:1px">RUT: </p>
                    <p id="cliente_nombres" class="font-weight-bold font-italic" style="margin-bottom:1px">Cliente:</p>
                    <p id="cliente_direccion" class="font-weight-bold font-italic" style="margin-bottom: 1px">Dirección:</p>
                    <p id="cliente_giro" class="font-weight-bold font-italic" style="margin-bottom: 1px">Giro:</p> --}}
                    <table class="table">
                      <tr>
                        <th>Rut</th>
                        <th>Nombre</th>
                        <th>Direccion</th>
                        <th>Giro</th>
                      </tr>
                      <tr>
                        <td id="cliente_rut" style="font-size: 10px; width: 50px; text-transform: uppercase;"></td>
                        <td id="cliente_nombres" style="font-size: 10px; width: 50px;text-transform: uppercase;"></td>
                        <td id="cliente_direccion" style="font-size: 10px; width: 50px;text-transform: uppercase;"></td>
                        <td id="cliente_giro" style="font-size: 10px; width: 50px;text-transform: uppercase;"></td>
                      </tr>
                      
                    </table>
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
        <hr>
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
        <hr>
        <div class="row row-cero-margen">
          <div class="col-sm-4" class="form-check form-check-inline">
              <input type="radio" class="form-check-input" name="tipo_documento" id="radio_consignacion" value="consignacion" onclick="que_documento('consignacion')">
              <label for="radio_consignacion" class="form-check-label">Consignación</label>
          </div>
          <div class="col-sm-8" style="padding-left:2px">
          <input type="text" name="nombre_consignacion" value="" id="nombre_consignacion" maxlength="100" class="form-control-sm" style="width:90%" placeholder="Nombre Consignación" disabled>
          <input type="text" name="dias_expira" value="7" id="dias_expira" maxlength="2" class="form-control-sm" style="width:15%;display:none;" disabled>
          </div>
      </div>
      <hr>
        <div class="row">
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><strong>Venta:</strong></div>
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="contado" id="contado" onclick="contado_credito(1)" checked><small>Contado</small></div>
            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="credito" id="credito" onclick="contado_credito(2)" disabled><small>Crédito</small></div>
            @if(Auth::user()->name == "Matias Alfaro" || Auth::user()->rol->nombrerol === "Administrador") <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input type="radio" name="tipo_venta" value="delivery" id="delivery" onclick="contado_credito(3)"><small>Delivery</small></div> @endif
        </div>
        <fieldset id="zona_formas_pago">
            <div class="row row-15" id="formas_pago"> </div>
        </fieldset>

        </fieldset>
<hr>

        <fieldset id="zona_descuento" class="my-2">
          
            <div class="row row-15" id="zona_descuento_input"> 
              <div class="col-md-5">
                <input type="checkbox" name="forma_descuento" id="forma_descuento" onclick="activar_forma_pago(999)" > Descuento
              </div>
              <div class="col-md-4">
                <input type="text" name="" id="formita-descuento" class="" style="width: 100%;" value="0" placeholder="% DCTO" onkeypress="return soloNumeros(event)" disabled>
              </div>
              <div class="col-md-3">
                <button class="btn btn-primary btn-sm" id="btn_aplicar_dcto" onclick="calcular_sumatoria()" disabled>Aplicar</button>
              </div>
              
              
            </div>
            <div class="row row-15 p-2 text-center" id="zona_vuelto_efectivo" style="margin-top: 10px; margin-bottom: 10px; border: 1px solid black;"> 
              
          
                <input type="hidden" style="width: 70px" name="forma_descuento" id="total_venta_vuelto"  placeholder="Monto efectivo" readonly> 
            
          
              <div class="col-md-12">
                <input type="text" name="monto_efectivo" id="monto_efectivo" style="width: 70px;" onKeyPress="return soloNumeros(event)">
                <button class="btn btn-success btn-sm" onclick="dame_vuelto()">Vuelto efectivo</button>
              </div>
              
              
            </div>
            <div class="row" style="display: none;" id="div_vuelto">
              
            </div>
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
        @if($value_solo_transferir == 1)
        <div class="btn_procesar">
            <button  class="btn btn-warning form-control-sm d-block" onclick="confirmar_documento()" id="btn_procesar_">PROCESAR</button>
        </div>
        <input type="hidden" name="procesar" id="procesar" value="1">
        @else
        <div class="btn_procesar">
          <button  class="btn btn-warning form-control-sm d-none" onclick="confirmar_documento()" id="btn_procesar_">PROCESAR</button>
      </div>
      <input type="hidden" name="procesar" id="procesar" value="0">
        @endif

    </div>

    <div class="col-sm-9">
        <div  id="carrito" style="background-color: #f2f4a9;min-height: 400px; border: 1px solid black; border-radius: 10px; padding: 4px;">
            <p class="d-flex justify-content-center">Obteniendo carritoooo...</p>
            <hr>
        </div>
        <fieldset style="background-color: #f2f4a9; padding: 10px; border: 1px solid black; border-radius: 10px; margin-top: 10px;" >
            <div class="mt-3" style="display:inline">
                Ingreso por Código:
                <input type="text" id="input_codigo" placeholder="Código" style="width:100px" onkeyup="enter_codigo(event)" disabled>
                <input type="text" id="input_cantidad_footer" placeholder="Cant." style="width:50px" onKeyPress="return soloNumeros(event)" onKeyup="enter_cant(event)" disabled>
                <button class="btn btn-primary btn-sm" onclick="agregar_carrito(id_repuesto_codigo,3)" disabled>AGREGAR</button>
                <span id="input_descripcion"></span>
                Consignación: &nbsp;&nbsp;
                <input type="text" id="input_id_consignacion" placeholder="Código de consignación" style="width:155px" onKeyPress="return soloNumeros(event)" onkeyup="soloNumeros(event)" disabled>
                
                <button class="btn btn-primary btn-sm" onclick="agregar_consignacion_carrito()" disabled>AGREGAR</button>
                <span id="input_descripcion"></span>
                <input type="hidden" name="local_id_consignacion" id="local_id_consignacion">
                <input type="hidden" name="cantidad_consignacion" id="cantidad_consignacion">
            </div>
            <div class="mt-3" style="display:block">
              Código de abono: &nbsp;&nbsp;
              <input type="text" id="input_num_abono" placeholder="Código de giftcard" style="width:155px" onKeyPress="return soloNumeros(event)" onkeyup="soloNumeros(event)">
              
              <button class="btn btn-primary btn-sm" onclick="agregar_abono_carrito()">AGREGAR</button>
              <span id="input_descripcion"></span>
              <input type="hidden" name="input_cantidad_pedido" id="input_cantidad_pedido">
              <input type="hidden" name="input_saldopendiente_pedido" id="input_saldopendiente_pedido">
              <input type="hidden" name="input_total_pedido" id="input_total_pedido">
              <input type="hidden" name="input_repuesto_pedido" id="input_repuesto_pedido">
          </div>
          <div class="mt-3" style="display:block">
            
            
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
<div role="dialog" tabindex="-1" class="modal fade" id="mostrar-cotizaciones-modal_historial">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-body"> <!-- CONTENIDO -->
       <div id="mostrar_cotizaciones">
          <h4 style="text-align: center; color:blue"><strong><p id="titulo-listado-cotizaciones">COTIZACIONES VIGENTES</p></strong></h4>
          <h5 style="text-align: center; color:slategrey "><strong><p id="subtitulo-listado-cotizaciones"></p></strong></h5>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">N° Cotizacion</th>
                <th scope="col">Fecha</th>
                <th scope="col">Nombre</th>
                <th scope="col">Cliente</th>
                <th scope="col">Estado</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody id="listado_cotizaciones_historial">
              
            </tbody>
          </table>
       </div>
     </div> <!-- FIN DE modal-body -->
    </div> <!-- modal-content -->
  </div> <!-- modal-dialog -->
</div> <!-- FIN VENTANA MODAL MOSTRAR COTIZACIONES"-->

<!-- Busqueda Repuesto Xpress MOdal -->
<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="buscar-xpress-modal">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #000; color: white;">
        <h5 class="modal-title">Busqueda de Repuestos Xpress</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="background: rgb(242, 244, 169)">
          <div class="row form-group-sm">
              <div class="col-md-6">
                  <input type="text" class="form-control" name="" onkeyup="enter_buscar_repuesto_xpress(event)" id="repuesto-xpress-codigo" placeholder="Cód. Ref." style="width:100%">&nbsp;
              </div>
              <div class="col-md-6">
                <input type="button" value="Buscar" class="btn btn-warning btn-sm" onclick="buscar_repuesto_xpress()">
                <div id="fecha_actualizacion_precio"></div>
              </div>
          </div>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">Descripción</th>
                <th scope="col">Actualización Precio</th>
                <th scope="col">Ubicación</th>
                
                <th scope="col">Ubicación</th>
                
                <th scope="col">Ubicación</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Procedencia</th>
                <th scope="col">Precio Venta</th>
              </tr>
            </thead>
            <tbody id="informacion_repuesto">
              
            </tbody>
          </table>
          
      </div>
      <div class="modal-footer" style="background: #000;">
        
        <button type="button" id="btn_agregar_repuesto_buscado" class="btn btn-primary" >Agregar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
      </div>
      <hr>
      <p id="mensaje_modal" class="ml-3" >Esperando ...</p>
    </div>
  </div>
</div> <!-- FIN Repuesto Xpress MOdal -->

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
                    <input type="text" name="" id="xpress-precio" placeholder="Precio Unitario" style="width:50%" data-toggle="tooltip" title="Sin puntos ni comas, sólo números">&nbsp;<p style="display:inline;color:blue;font-style:italic;font-size:12px">incluye IVA</p>
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
<div class="modal fade bd-example-modal-lg" id="modalHistorialConsignaciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Historial Consignaciones Vigentes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">N°</th>
              <th scope="col">Fecha</th>
              <th scope="col">Descripción</th>
              <th scope="col">Imprimir</th>
              <th scope="col">Eliminar</th>
            </tr>
          </thead>
          <tbody id="tbody_vales">
          </tbody>
        </table>
      </div>
      <hr>
      <div id="detalle_consignacion" class="container-fluid">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
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
<input type="hidden" name="oferta" id="oferta" value="0">
<input type="hidden" name="cliente_carrito_transferido" id="cliente_carrito_transferido">
</div> <!-- fin container-fluid -->
@endsection
