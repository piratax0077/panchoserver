@extends('plantillas.app')

@section('titulo','Buscar Repuestos')

@section('contenido_titulo_pagina')
    <center><h3 class="titulazo">Buscar Repuestos</h3></center>
 @endsection

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

  var repuestos = [];

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

  function enter_cant_dos(e,id_repuesto_codigo){
    if(e.key=='Enter' || e.key=='Tab'){ //enter o tab(no funciona)

      agregar_carrito(id_repuesto_codigo,4);

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

    $("#cliente_nombres").html("Cliente:");

    $("#total_forma_pago").html("0");

    contado_credito(1); //para que reactive las formas de pago

    document.getElementById("credito").checked=false;

    document.getElementById("credito").disabled=true;

    document.getElementById("contado").checked=true;

    document.getElementById("nombre_cotizacion").value="";

    document.getElementById("nombre_cotizacion").disabled=true;

    documento_procesado="Ninguno";

    document.getElementById("input_codigo").value="";

    document.getElementById("input_cantidad").value="";

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

  function buscar_sinborrarlateral(){
    if(!modifikar_precio_panel)
    {
        if(mostrar_panel_buscar==false)
        {
          console.log('hola');
            // $("#panel-buscar").fadeOut(300,function(){
            //     document.getElementById("grilla").className = "col-sm-12";
            // });
            // $("#txtBotonPanel").html("Mostrar Marcas y Modelos");
        }else{
          console.log('adios');
            // document.getElementById("grilla").className = "col-sm-9";
            // $("#panel-buscar").fadeIn("slow");
            // $("#txtBotonPanel").html("<p>Ocultar Marcas y Modelos</p>");
            // clic_en(5);
        }
        mostrar_panel_buscar=!mostrar_panel_buscar;
    }
  }


function buscar_imagenes(data){
  
  $("#tabla_info_completa").css('display','none');
  $('#tabla_info_imagenes').css('display','block');
}

function buscar_info(){
  $("#tabla_info_completa").css('display','block');
  $('#tabla_info_imagenes').css('display','none');
}


  function clic_en(valor)

  {

    // $("#zona_familia").html("");

    // $("#zona_grilla").html("");

    $("#zona_fotos").html("");

    $("#zona_similares").html("");

    $("#zona_oem").html("");

    $("#zona_fab").html("");

    //$("#mensajes-modal").html("");

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

      console.log(q);
        // let formulario = document.querySelector('#buscar_por_descripcion');
        // let resultado = '';
        // $('#zona_grilla').empty();
        // dametodorepuestos();
        // let nombre = formulario.value.toLowerCase();
       
        // for (let repuesto of repuestos[0]) {
        //  let desc = repuesto.descripcion.toLowerCase();
        //  if(desc.indexOf(nombre) !== -1){
        //   $('#zona_grilla').append(`
        //     <li>`+repuesto.descripcion+` </li>
        //   `);
        //  }
        // }
 
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

    function dametodorepuestos(){
      let url = '{{url("repuesto/dametodorepuestos")}}';
      

      $.ajax({
        async: false,
        type:'get',
        url: url,
        success: function(resp){
          repuestos.push(resp);
        },
        error: function(err){
          console.log(err);
        }
      })
    }



    function enter_anio_similar(e,id_simi){

        var keycode = e.keyCode;

        if(keycode=='13')

        {

            guardar_anio_similares(id_simi);

        }

    }


    function traspasar_mercaderia(id){
      console.log(id);
      $('#id_repuesto').val(id);
        let url = '/inventario/damerepuesto/'+id;
        $.ajax({
            type:'get',
            url: url,
            success: function(data){
              
               let repuesto = data[0];
               if(repuesto.local_id === 1){
                console.log(repuesto);
                $('#stock_bodega_actual').val(repuesto.stock_actual);
                $('#stock_tienda_actual').val(repuesto.stock_actual_dos);
                $('#repuesto_id').empty();
                $('#repuesto_id').append(repuesto.codigo_interno);
                $('#repuesto_descripcion').empty();
                $('#repuesto_descripcion').append(repuesto.descripcion);
                if(repuesto.stock_actual_dos === null){
                  $('#repuesto_stock_actual_dos').empty();
                  $('#repuesto_stock_actual_dos').append(0);
                }else{
                  $('#repuesto_stock_actual_dos').empty();
                  $('#repuesto_stock_actual_dos').append(repuesto.stock_actual_dos);
                }
                
                $('#repuesto_stock_actual').empty();
                $('#repuesto_stock_actual').append(repuesto.stock_actual);
                $('#repuesto_empresa_nombre').empty();
                $('#repuesto_empresa_nombre').append(repuesto.empresa_nombre);
                $('#cantidad_transferir').attr('max',repuesto.stock_actual);

               }else{
                
                $('#stock_bodega_actual').val(repuesto.stock_actual_dos);
                $('#stock_tienda_actual').val(repuesto.stock_actual);
                $('#repuesto_id').empty();
                $('#repuesto_id').append(repuesto.codigo_interno);
                $('#repuesto_descripcion').empty();
                $('#repuesto_descripcion').append(repuesto.descripcion);
                $('#repuesto_stock_actual_dos').empty();
                $('#repuesto_stock_actual_dos').append(repuesto.stock_actual);
                $('#repuesto_stock_actual').empty();
                $('#repuesto_stock_actual').append(repuesto.stock_actual_dos);
                $('#repuesto_empresa_nombre').empty();
                $('#repuesto_empresa_nombre').append(repuesto.empresa_nombre);
                $('#cantidad_transferir').attr('max',repuesto.stock_actual_dos);
               }
                
            },
            error: function(){

            }

        })

        $('#buscar-repuesto-modal').on('shown.bs.modal', function () {
            $("#repuesto_id").focus();
        });

        $("#buscar-repuesto-modal").modal("show");

    }

    function confirmar(){
        let stock_bodega = $('#stock_bodega_actual').val();
        let stock_tienda = $('#stock_tienda_actual').val();
        
        if(stock_bodega !== ''){
          
          let id_repuesto = $('#id_repuesto').val();
          let cantidad = $('#cantidad_transferir').val();
          let url = '/inventario/traslado';
          let data = {id_repuesto: id_repuesto, cantidad: cantidad}

          if(cantidad > stock_bodega){
            Vue.swal({
              text: 'No puede pedir mas de lo que hay',
              position: 'top-end',
              icon: 'warning',
              toast: true,
              showConfirmButton: false,
              timer: 3000,
              });
          return false;
        }else if(cantidad === ''){
          Vue.swal({
            text: 'Debe ingresar una cantidad',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
          return false;
        }else if(cantidad < 0){
          Vue.swal({
            text: 'Debe ingresar una cantidad valida',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
            return false;
        }

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
          });

        $.ajax({
            type:'POST',
            url: url,
            data: data,
            success: function(resp){
              console.log(resp);
              
              if(resp ==='OK'){
                Vue.swal({
                title: 'Perfecto',
                text: "Solicitud exitosa",
                icon: 'success',
                });
              }else{
                Vue.swal({
                title: 'Fallo',
                text: "Ha ocurrido un error",
                icon: 'error',
            });
              }
              

            },
            error: function(e){
                console.log(e.statusText);
            }

        })
        }else{
          console.log('stock vacio');
          Vue.swal({
                title: 'Fallo',
                text: "No hay stock en bodega",
                icon: 'error',
            });
          return false;
        }
        
        
    }


    function buscar_por_descripcion()
    {
      // verificar_carrito_transferido();
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

        console.log(resp);
        // return false;
      
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

            $("#input_cantidad").focus();

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

      console.log(url_buscar);

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
        Vue.swal({
          icon:'info',
          position:'center',
          text:'CARGANDO ...',
          showConfirmButton:false,
          allowOutsideClick: false,
          allowEscapeKey: false,
        });
      },

      url:url_buscar,

      success:function(resp){
        console.log('exito');
        Vue.swal.close();
        $("#mensajes-modal").html("Listo...");

        $("#zona_grilla").html(resp);

        damefamilias(modelo);
        // verificar_carrito_transferido();
      },

      error: function(error){
        console.log('error');
        $("#busca-repuesto-modal").modal("hide");

        Vue.swal({

            title: 'ERROR',

            text: formatear_error(error.responseText),

            icon: 'error',

            });
            // imprimir el error en un div en la vista
            $('#zona_grilla').html(formatear_error(error.responseText));
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

          // verificar_carrito_transferido();

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
    }
    if(cual==3){ //desde ingreso por código
        cant=document.getElementById("input_cantidad").value.trim();
    }
    if(cual==4){//desde busqueda normal pero con imagenes, le puse dos solo para diferenciarlo
        var idc="cant-dos-"+id_rep;
        var idl="local-dos-"+id_rep;
        var local=document.getElementById(idl);
        var texto_local=local.options[local.selectedIndex].text;
        var ids="stock-dos-"+id_rep;
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
    console.log('La cantidad es '+cant);
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
      // verificar_carrito_transferido();
      // si el input de tipo hidden es_transferido es distinto a 0 es porque se esta transfiriendo
      var es_transferido = document.getElementById("es_transferido").value;
      if(es_transferido != 0){
          var url="{{url('ventas/agregar_carrito_transferido')}}";
          var parametros = {idrep: id_rep, idlocal: idlocal, cantidad: cant, cliente_id: es_transferido, carrito_id: 0};
          
        }else{
          var url="{{url('ventas/agregar_carrito')}}";
          var parametros={idcliente:idcliente,idrep:id_rep,idlocal:idlocal,cantidad:cant};
        }
        console.log(url);
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
         if(resp == 'viejo'){
          $("#mensajes-modal").html("Upss!!!");
            Vue.swal({
                    text: 'Repuesto no actualizado',
                    position: 'center',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
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
            return false;
          }
            let t=new Intl.NumberFormat('es-CL').format(resp);
            $("#mensajes-modal").html("Total: "+t);
            // document.getElementsByName("forma_pago_monto")[0].value=t.replace(/\./g,"");
            document.getElementById("buscar_por_descripcion").value="";
            dame_carrito();
            clic_en(1);
            // $("#buscar-repuesto-modal").modal("hide");
            Vue.swal({
                text: 'Agregado',
                position: 'center',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            // document.getElementById("input_codigo").value="";
            // document.getElementById("input_cantidad").value="";
            $("#input_descripcion").html("Descripción");
          
            // verificar_carrito_transferido();
            },
        error: function(error){
          
            Vue.swal({
            title: 'ERROR',
            text: formatear_error(error.responseText),
            icon: 'error'
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

            let precio_a_modificar=document.getElementById("precio_a_modificar");
            Inputmask({"mask":"$ 9.999.999",numericInput: true}).mask(precio_a_modificar);


            mostrar_panel_buscar=true;
            buscar_sinborrarlateral();
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
            guardar_precio_venta(new_val,old_val);
        }
        // alert('No esta permitido disminuir el precio');
        // return false;
    }

    if(new_val>old_val)

    {

        //console.log("nuevo valor > antiguo valor");

        guardar_precio_venta(new_val, old_val);

    }

}

function resetear_tiempo_precio(){
  var url='{{url("repuesto/resetear_tiempo_precio")}}'+'/'+id_precio_a_modifikar;
  
$.ajax({

    type:'GET',

    beforeSend: function () {

        $("#mensajes-modal").html("Reseteando contador de días...");

    },

    url:url,

    success:function(rpta){
      console.log(rpta);
      let repuesto = rpta;
      $('#dias_precio-'+repuesto.id).html("<b>0</b>");
      $('#dias_precio-'+repuesto.id).css('background','green');
      $('#dias_precio-'+repuesto.id).css('color','white');
      $("#mensajes-modal").html("Contador de días vuelve a 0");

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

function guardar_precio_venta(nuevo_precio,antiguo_precio)

{

    let guardó_ok=false;

    let dato=id_precio_a_modifikar+"&"+nuevo_precio+"&"+antiguo_precio;

    var url='{{url("repuesto/guardar_precio_venta")}}'+'/'+dato;

    $.ajax({

        type:'GET',

        beforeSend: function () {

            $("#mensajes-modal").html("Guardando Nuevo Precio...");

        },

        url:url,

        success:function(rpta){
          console.log(rpta);
          
            if(rpta[0]!="XUXA")

            {

                $("#ppv-"+id_precio_a_modifikar).html("<b>"+rpta[0]+"</b>");

                $("#pv-"+id_precio_a_modifikar).val(rpta[0]);

                $('#dias_precio-'+id_precio_a_modifikar).html("<b>"+rpta[1]+"</b>");

                $("#mensajes-modal").html("Nuevo Precio Guardado...");

                //actualizar el precio de venta del carrito

                //Mejor avisar en la modificación de precios

                //dame_carrito();

                id_precio_a_modifikar=0;

                cerrar_panel_modifikar();

            }else{

                $("#mensajes-modal").html("ERROR...NO TIENE PERMISOS PARA BAJAR EL PRECIO");

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
    dame_repuesto(id_repuesto);

    dame_fotos(id_repuesto);

    dame_similares(id_repuesto);

    dame_oems(id_repuesto);

    dame_fabricantes(id_repuesto);

    dame_regulador_voltaje(id_repuesto);

  }

  function dame_repuesto(id_repuesto){
    var url='{{url("relacionadoprincipal")}}'+'/'+id_repuesto;
      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes-modal").html("Buscando...");
          },
       url:url,
       success:function(repuesto){
        $("#mensajes-modal").html("Listo...");
        if(modal_relacionado)
        {
          $("#info_repuesto_detalle").html(repuesto);
        }else{
          $("#info_repuesto_detalle").html(repuesto);
        }
       },
        error: function(error){
          $('#info_repuesto_detalle').html(formatear_error(error.responseText));
        }
      }); //Fin petición
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

  function dame_fotos_presentacion(id_repuesto){
    
  }



  function abrir_foto_modal(id_repuesto)

  {

    var url='/repuesto/dame_fotos_repuesto/'+id_repuesto;

    $.ajax({
      type:'get',
      url: url,
      success: function(fotos){
        console.log(fotos);
        
        $("#aqui_foto").html(fotos);
      },
      error: function(error){
          console.log(error);
      }
    })

    //$("#aqui_foto").html("<img src='"+enlace+"' width='100%' />");


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
        if(modal_relacionado){
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

  function dame_regulador_voltaje(id_repuesto){
    var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damereguladorvoltaje';
      $.ajax({
       type:'GET',
       beforeSend: function () {
        console.log('buscando ...');
          },
       url:url,
       success:function(rv){
        console.log(rv);
        if(rv){
          // al div con id zona_similares le cambiamos la clase a col-2 y le quitamos la clase d-none y la cambiamos por d-block al div con id zona_similares
          $("#zona_rv").removeClass('d-none');
          $("#zona_rv").addClass('d-block');
          $('#zona_similares').removeClass('col-5');
          $('#zona_similares').addClass('col-3');
          $("#zona_rv").html(rv);
        }else{
          $("#zona_rv").removeClass('d-block');
          $("#zona_rv").addClass('d-none');
          $('#zona_similares').removeClass('col-3');
          $('#zona_similares').addClass('col-5');
          $("#zona_rv").html('');
        }
          
        
       },
        error: function(error){
          $('#zona_rv').html(formatear_error(error.responseText));
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



    // function mostrar_cotizaciones(){

    //     let html='<table> \

    //     <thead> \

    //         <th></th> \

    //         <th></th> \

    //     <thead> \

    //     <tbody> \

    //         <tr> \

    //             <td><button  class="btn btn-info form-control-sm" onclick="mostrar_cotizaciones_mes()">BUSCAR</button></td> \

    //             <td><input type="text" style="width:80%" id="nombre_cotizacion_buscar" placeholder="nombre a buscar"></td> \

    //         </tr> \

    //         <tr> \

    //             <td><button  class="btn btn-warning form-control-sm" onclick="mostrar_cotizaciones_cliente()">CLIENTE</button></td> \

    //             <td>Buscar por cliente</td> \

    //         </tr> \

    //         <tr> \

    //             <td><button  class="btn btn-success form-control-sm" onclick="mostrar_cotizaciones_numero()">BUSCAR</button></td> \

    //             <td><input type="text" style="width:80%" id="numero_cotizacion_buscar" placeholder="número a buscar"></td> \

    //         </tr> \

    //     </tbody> \

    // </table>';



    //     Vue.swal({

    //         title: 'COTIZACIONES',

    //         html:html,

    //         showConfirmButton:false,

    //         showCancelButton:true,

    //         cancelButtonText:"CERRAR"

    //     });

    // }



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

    buscar_repuesto();

        //Para que el panel de busqueda lateral inicie oculto

        mostrar_panel_buscar=false;

        buscar();

        verificar_carrito_transferido();

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



    // let bb=document.getElementById("radio_boleta");

    // let ff=document.getElementById("radio_factura");

    // bb.checked=true;

    // ff.checked=false;



}

function verificar_carrito_transferido(){
      let url='{{url("ventas/verificar_carrito_transferido")}}';
      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Descuentos Clientes...");
        },
        url:url,
        success:function(resp){
          console.log(resp);
          if(resp.length > 0){
            // crear un swal para que seleccione que carro transferido quiere cargarle los repuestos
            let html = '<span>Seleccione el carrito que desea cargarle los repuestos</span>';
              html+= '<select class="form-control" id="carrito_transferido">';
              html+= '<option value="0">Carrito Personal</option>';
            resp.forEach(element => {
              html += '<option value="'+element.cliente_id+'">'+element.titulo+' de '+element.name+'</option>';
            });
            html += '</select>';
            Vue.swal({
              title: 'Carrito Transferido',
              html: html,
              confirmButtonText: 'Cargar',
              showCancelButton: true,
              cancelButtonText: 'Cancelar',
              showLoaderOnConfirm: true,
              preConfirm: () => {
                let id_carrito = document.getElementById("carrito_transferido").value;
                if(id_carrito != 0){
                  // guardamos el id del carrito transferido en un input de tipo hidden para luego cargarlo
                  document.getElementById("es_transferido").value = id_carrito;
                  // le quitamos la clase d-none al boton de cargar carrito y le agregamos la clase d-block
                  document.getElementById("btn_cargar_carrito").classList.remove("d-none");
                  document.getElementById("btn_cargar_carrito").classList.add("d-block");
                  return console.log(id_carrito);
                }
                
              },
              allowOutsideClick: () => !Vue.swal.isLoading()
            });
          }else{
            // si no hay carritos transferidos, le agregamos la clase d-none al boton de cargar carrito y le quitamos la clase d-block
            document.getElementById("btn_cargar_carrito").classList.remove("d-block");
            document.getElementById("btn_cargar_carrito").classList.add("d-none");
            document.getElementById("es_transferido").value = 0;
          }
  
        },
  
          error: function(error){
  
            $('#mensaje-cliente').html(formatear_error(error.responseText));
  
          }
        }); //Fin petición
}

// function confirmar_documento(){

//     let docu = $('input[name="tipo_documento"]:checked').val().trim();

//     docu=docu.toUpperCase();

//     Vue.swal({

//         text: "Desea continuar con "+docu+ ' ?',

//         icon: 'warning',

//         showCancelButton: true,

//         confirmButtonColor: '#3085d6',

//         cancelButtonColor: '#d33',

//         confirmButtonText: 'CONTINUAR',

//         cancelButtonText: 'CANCELAR'

//         }).then((result) => {

//         if (result.isConfirmed) {

//             if(docu=='COTIZACION'){

//                 cotizar();

//             }

//             if(docu=='BOLETA' || docu=='FACTURA'){

//                 generar_xml();

//             }

//         }else{

//             /*

//             let bb=document.getElementById("radio_boleta");

//             let ff=document.getElementById("radio_factura");

//             bb.checked=true;

//             ff.checked=false;

//             */

//         }

//         })

// }


function generate_codebar(id_repuesto){
  
  let url = "/generate-barcode/"+id_repuesto;
  
  //  window.open(url,'_blank');
  // window.location.href = "/generate-barcode/"+id_repuesto;

  $.ajax({
    type:'get',
    url:url,
    beforeSend: function(){
      Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
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
    error: function(err){
      console.log(err);
    }
  })
}


function abrirModalPrecio(idrep){
  
  let url = '/repuesto/buscaridrep/'+idrep;

  $.ajax({
    type:'get',
    url: url,
    success: function(resp){
      console.log(resp);
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
      <p>Descripción: `+repuesto.descripcion+` </p>
      <p>Precio normal: $ `+parseInt(repuesto.precio_venta).toFixed(0)+` </p>
      <p>Precio ofertado: $ `+parseInt(precio_real).toFixed(0)+` </p>
      <p>Desde: `+repuesto.desde+` </p>
      <p>Hasta: `+repuesto.hasta+` </p>
      `);
    },
    error: function(error){
      console.log(error.responseText);
    }
  });
}

function detalleStockMinimo(id_rep){
  let url = '/repuesto/detalle_pedido/'+id_rep;
  $.ajax({
    type:'get',
    url: url,
    success: function(resp){
      console.log(resp);
      let repuesto = resp;
      if(repuesto){
        let stock = repuesto.stock_actual + repuesto.stock_actual_dos + repuesto.stock_actual_tres;
        $('#modal_body_info_repuesto_stockminimo').empty();
      $('#modal_body_info_repuesto_stockminimo').append(`
      <p>Descripción: `+repuesto.descripcion+` </p>
      <p>Proveedor: `+repuesto.empresa_nombre_corto+` </p>
      <p>Cantidad: `+repuesto.cantidad+` </p>
      `);
      }else{
        $('#modal_body_info_repuesto_stockminimo').empty();
        $('#modal_body_info_repuesto_stockminimo').append(`
        <p>No hay detalle definido para este repuesto</p>
        `);
      }
      
    },
    error: function(error){
      console.log(error.responseText);
    }
  });

}

</script>

@endsection



 @section('contenido_ingresa_datos')

<!-- Ventana modal BUSCAR REPUESTO -->

<div role="dialog" tabindex="-1" class="modal fade" id="buscar-repuesto-modal">

    <div class="modal-dialog modal-xl modal-ventas" role="document" >

      <div class="modal-content">

 

        <div class="modal-header modal-header-80" style="background: #000;"> <!-- CABECERA -->

         <div class="row" style="width:100%">

             <div class="col-2" style="padding-left:2px">

                 <button class="btn btn-warning btn-sm" onclick="buscar()" style="height: 20px"><p id="txtBotonPanel">Ocultar Marcas y Modelos</p></button>
                <button class="btn btn-primary btn-sm d-none" onclick="verificar_carrito_transferido()" id="btn_cargar_carrito" style="height: 20px"><p id="txtBotonCarritoTransferido">Cambiar Carrito Transferido</p></button>
             </div>

             <div class="col-9"><p class="d-flex justify-content-center" id="buscar-repuesto-modal-titulo" style="color:white;font-weight:bold">BUSCAR REPUESTO</p></div>

             <div class="col-1" style="padding-right: 1px"><button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding-right: 1px"><span aria-hidden="true">×</span></button></div>

         </div>

         <div class="row" style="width:90%">

             <div class="col-2 alin-der" style="padding-right:2px;color:white">Buscar:</div>

             <div class="col-10" style="margin-bottom:5px;padding-left:2px">

                 <input type="search" class="form-control" style="height:25px;" name="txtDescripcion" id="buscar_por_descripcion" placeholder="¿Qué desea encontrar?" onkeyup="enter_press(event,'d')" onclick="clic_en(1)" >

             </div>

         </div>

        </div> <!-- FIN CABECERA -->

 

        <div class="modal-body modal-body-alto" style="padding-top: 0px; background-color: rgb(242, 245, 169);"> <!-- CONTENIDO -->

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

                 <div id="zona_grilla"  style="height:500px;background-color: #F2F5A9;"></div> <!-- fragm.ventas_repuestos.blade -->

                 {{-- <div class="row row-cero-margen d-none" id="zona_detalle">

                     <div class="row row-cero-margen" id="zona_detalle_fieldset" style="width:100%">

                         <div class="col-3" id="zona_fotos" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px"></div>

                         <div class="col-5" id="zona_similares" style="background-color: #b3e6ff;padding-left: 1px;padding-right: 1px"></div>

                         <div class="col-2" id="zona_oem" style="background-color: #81BEF7; padding-left: 1px;padding-right: 1px">
                          
                        </div>

                         
                         
                         <div class="col-2" id="zona_fab" style="background-color: #96dafe; padding-left: 1px;padding-right: 1px"></div>

                     </div>

                 </div> --}}

 

                 </div><!-- FIN Columna 2 : grilla y detalles  -->

 

          </div> <!-- FIN de row principal -->

       </div> <!-- FIN DE modal-body CONTENIDO -->

 

        <div class="modal-footer" style="flex-direction:column; align-content: start; background: black;
        color: white;"> <!-- PIE -->

             <div class="row" style="width:90%">

                 <div class="col-sm-8 col-offset-4" id="mensajes-modal"></div>

             </div>

        </div> <!-- FIN MODAL-FOOTER -->

         </div> <!-- modal-content -->

 

 

    </div> <!-- modal-dialog -->

 </div> <!-- Fin ventana modal -->



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

  <div role="dialog" tabindex="-1" class="modal fade bd-example-modal-lg" id="foto-modal" style="z-index: 2000;">

    <div class="modal-dialog modal-lg" role="document"  >

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body"> <!-- CONTENIDO -->
         
            <div id="aqui_foto"></div>
          
       </div> <!-- FIN DE modal-body -->

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->

  </div> <!-- FIN VENTANA MODAL FOTO GRANDE"-->

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" srcset="" class="logoHeader">
        <h5 class="modal-title" id="exampleModalLabel">Traspaso de repuesto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <table class="table">
            <thead class="thead-dark">
                <tr>
                <th scope="col">Cod Int</th>
                <th scope="col">Descripcion</th>
                <th scope="col">Stock Tienda</th>
                <th scope="col">Stock Bodega</th>
                <th scope="col">Empresa</th>
                <th scope="col">Cantidad</th>
                </tr>
            </thead>

            <tbody>
                <tr style="font-size: 13px;">
                    <td id="repuesto_id"></td>
                    <td id="repuesto_descripcion"></td>
                    <td id="repuesto_stock_actual_dos"></td>
                    <td id="repuesto_stock_actual"></td>
                    <td id="repuesto_empresa_nombre"></td>
                    <td> <input type="number" style="width: 60px;" class="form-control" max="" min="1" id="cantidad_transferir">  </td>
                </tr>
            </tbody>
            </table>
        
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_confirmar_traspaso" onclick="confirmar()">Notificar a bodega</button>
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
<div class="modal fade bd-example-modal-xl" id="detalleModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title " id="exampleModalLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-cero-margen" >
          <div class="col-12" id="info_repuesto_detalle" style="
          margin-bottom: 10px;
          padding: 10px;">

          </div>
        </div>
        <div class="row row-cero-margen" id="zona_detalle">

          <div class="row row-cero-margen" id="zona_detalle_fieldset" style="width:100%">

              <div class="col-3" id="zona_fotos" style=" padding-left: 1px;padding-right: 1px"></div>

              <div class="col-5" id="zona_similares" style="padding-left: 1px;padding-right: 1px"></div>

            <div class="col-2 d-none" id="zona_rv" style="padding-left: 1px; padding-right: 10x;"></div>

              <div class="col-2" id="zona_oem" style=" padding-left: 1px;padding-right: 1px">
               
             </div>

              
              
              <div class="col-2" id="zona_fab" style=" padding-left: 1px;padding-right: 1px"></div>

          </div>

      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalStockMinimo" tabindex="1" role="dialog" aria-labelledby="modalStockMinimoLabel" aria-hidden="true">
  <div class="modal-dialog " role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title " id="modalStockMinimoLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_info_repuesto_stockminimo">
        <h1>...</h1>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        
      </div>
    </div>
  </div>
</div>

<!--DATOS DE VITAL IMPOTANCIA PARA SOLICITAR REPUESTO A BODEGA -->
<input type="hidden" name="" id="id_repuesto" value="">
<input type="hidden" name="" id="stock_bodega_actual" value="">
<input type="hidden" name="" id="stock_tienda_actual" value="">

<input type="hidden" name="" id="es_transferido" value="0">
 @endsection