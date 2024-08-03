@extends('plantillas.app')

@section('titulo','Ingreso por Compras')

@section('javascript')

  <script type="text/javascript">

    var clonando=false;

    var existe_factura="NO";

    var ampliar_aplicacionez=false;

    var cantidad_repuestos_factura=0;



    function ir_a_boton_guardaritem(){

        document.getElementById("btnGuardarItem").focus();

    }

    function espere(mensaje)

    {

        Vue.swal({

                title: mensaje,

                icon: 'info',

                showConfirmButton: true,

                showCancelButton: false,

                allowOutsideClick:false,

            });

    }



    function enter_press(e)

    {

      var keycode = e.keyCode;

      if(keycode=='13')

      {

        buscarRepuesto();

      }

    }



    function ampliar()

    {

        if(ampliar_aplicacionez==false)

        {

            /* este

                Cuando una propiedad contiene un guión, al acceder a ella con JavaScript se utiliza camel case:

                se quita el guión y la siguiente letra se hace mayúscula.

                Así, en CSS es z-index pero en JS es zIndex, igual ocurre con border-color (borderColor),

                grid-column (gridColumn) o grid-row (gridRow).

                FUENTE: https://es.stackoverflow.com/questions/340390/cambiar-el-valor-de-la-propiedad-grid-column-row-de-css-con-javascript

            */

            document.getElementById("zona_similares").style.gridColumn="2/5"; //este

            document.getElementById("zona_OEMs").style.visibility="hidden";

            document.getElementById("zona_FABs").style.visibility="hidden";

            $("#ampliar").html("<<");

        }else{

            document.getElementById("zona_similares").style.gridColumn="2/3"; //este

            document.getElementById("zona_OEMs").style.visibility="visible";

            document.getElementById("zona_FABs").style.visibility="visible";

            $("#ampliar").html(">>");

        }

        ampliar_aplicacionez=!ampliar_aplicacionez;

    }



    function clonar()

    {

        clonando=true;

        //console.log("clonar() clonando es ");

        //console.log(clonando?"SI":"NO");

        let codigo_interno=document.getElementById("codigo_interno").value;

        if(codigo_interno.trim().length==0)

        {

            Vue.swal({

                text: 'Ingrese código interno a clonar...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

            return false;

        }



        //verificar q existe codigo_interno

        let url='{{url("/repuesto/buscarcodint")}}'+'/'+codigo_interno;



        $.ajax({

        type:'GET',

        beforeSend: function () {

          espere("Clonando Repuesto... espere");

        },

        url:url,

        success:function(rpta){ //Viene en formato json

            Vue.swal.close();

            let r=JSON.parse(rpta);

            if(r.estado=='ERROR'){

                Vue.swal({

                    title: r.estado,

                    text: r.mensaje,

                    icon: 'error',

                });

            }

            if(r.estado=='OK'){

                elegir_repuesto(r.repuesto[0]);

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



    function dame_detalle(idrep){

        dame_fotos(idrep);

        dame_aplicaciones(idrep);

        dame_oems(idrep);

        dame_fabs(idrep);

    }



    function dame_fotos(idrep){

        let url='{{url("/repuesto")}}'+'/'+idrep+'/damefotos_modificar';

        $.ajax({

            type:'GET',

            url:url,

            success:function(resp){

                $('#fotos_rep').html(resp);

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



    function dame_aplicaciones(idrep){

        let url='{{url("/repuesto")}}'+'/'+idrep+'/damesimilares_modificar';

        $.ajax({

            type:'GET',

            url:url,

            success:function(resp){

                $('#similares_rep').html(resp);

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



    function dame_oems(idrep){

        let url='{{url("/repuesto")}}'+'/'+idrep+'/dameoems_modificar';

        $.ajax({

            type:'GET',

            url:url,

            success:function(resp){

                $('#oems_rep').html(resp);

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



    function dame_fabs(idrep){

        let url='{{url("/repuesto")}}'+'/'+idrep+'/damefabricantes_modificar';

        $.ajax({

            type:'GET',

            url:url,

            success:function(resp){

                $('#fabs_rep').html(resp);

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



    function cargar_fabricantes()

    {

      $('#cboFabricante option').remove();

      var url='{{url("marcarepuestoJSON")}}';

      $.ajax({

        type:'GET',

        beforeSend: function () {

          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Cargando...");

        },

        url:url,

        success:function(marks){ //Viene en formato json

          var marcas=JSON.parse(marks);

          $('#cboFabricante').append('<option value="">Elegir...</option>');

          marcas.forEach(function(marca){

            $('#cboFabricante').append('<option value="'+marca.id+'">'+marca.marcarepuesto.toUpperCase()+'</option>');

          });



          document.getElementById("cboFabricante").selectedIndex=0;

          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Listo...");

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



    function cargar_familia()

    {

      $('#familia option').remove();

      var url='{{url("familiasJSON")}}';

      $.ajax({

        type:'GET',

        beforeSend: function () {

          //$("#mensajes").html("Cargando Familias...");

        },

        url:url,

        success:function(familys){ //Viene en formato json

          var familias=JSON.parse(familys);

          $('#familia').append('<option value="">Elija una Familia</option>');

          familias.forEach(function(familia){

            //console.log(item.modelonombre);

            $('#familia').append('<option value="'+familia.id+'">'+familia.nombrefamilia.toUpperCase()+'</option>');

          });



          document.getElementById("familia").selectedIndex=0;

          //$("#mensajes").html("Familias cargadas...");

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



    function ubicarse_en_anios()

    {

      var modelito=document.getElementById("ModeloSim");

      var texto=modelito.options[modelito.selectedIndex].text.trim();

      $("#nombre_modelo").html("<b>Modelo: </b>"+texto);

      var ini="(";

      var fin=")";

      var años=texto.substring(texto.indexOf(ini)+1,texto.indexOf(fin));

      document.getElementById("anios_vehiculo_sim").value=años;

      document.getElementById("anios_vehiculo_sim").focus();

      document.getElementById("anios_vehiculo_sim").select();

    }



    function cargar_marca_repuesto()

    {

      $('#MarcaRepuesto option').remove();

      var url='{{url("marcarepuestoJSON")}}';

      $.ajax({

        type:'GET',

        beforeSend: function () {

          $("#aplicaciones_msje").html("<b>APLICACIONES: Cargando...</b>");

        },

        url:url,

        success:function(marks){ //Viene en formato json

          var marcas=JSON.parse(marks);

          $('#MarcaRepuesto').append('<option value="">Elija una Marca de Repuesto</option>');

          marcas.forEach(function(marca){

            $('#MarcaRepuesto').append('<option value="'+marca.id+'">'+marca.marcarepuesto.toUpperCase()+'</option>');

          });



          document.getElementById("MarcaRepuesto").selectedIndex=0;

          $("#aplicaciones_msje").html("<b>APLICACIONES: Listo...</b>");

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



    function cargar_pais()

    {

      $('#Pais option').remove();

      var url='{{url("paisJSON")}}';

      $.ajax({

        type:'GET',

        beforeSend: function () {

          //$("#mensajes").html("Cargando Familias...");

        },

        url:url,

        success:function(paizes){ //Viene en formato json

          var paises=JSON.parse(paizes);

          $('#Pais').append('<option value="">Elija País de Origen</option>');

          paises.forEach(function(pais){

            $('#Pais').append('<option value="'+pais.id+'">'+pais.nombre_pais+'</option>');

          });



          document.getElementById("Pais").selectedIndex=0;

          //$("#mensajes").html("Familias cargadas...");

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



    function cargar_medidas(){

      var id = document.getElementById('familia').value;

      var url = '{{url("medidasJSON")}}'+'/'+id;

      $.ajax({

        type:'get',

        url: url,

        beforeSend: function(){

          console.log('Enviando ...');

        },

        success: function(resp){

          console.log(resp);

          

          $('#span_medidas').empty();

          $('#modal_body_medidas').empty();

          let html = `

          <div class='clearfix'> </div>

          <table class="table">

            <thead>

            <tr>

              <th scope="col">Descripción</th>

            </tr>

          </thead>

          <tbody>`;

              resp.forEach(e => {

                html+= '<tr><th scope="row">'+e.descripcion+' </th></tr>';

              })

          html+= `</tbody>

          </table>

          `;

          $('#modal_body_medidas').append(html);

          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);

          let button_html = `

          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>

          `;

          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;width: 80%;/* float: left; */padding: 20px;line-height: 0px;' data-toggle='modal' data-target='#verMedidasModal' >Ver medidas </button>`;

          $('#span_medidas').append(btn_ver_medidas);

          $('#span_medidas').append(button_html);



        },

        error: function(error){

          console.log(error.responseText);

        }

      })

    }





    function agregar_familia()

    {

      $("#agregar-familia-modal").modal("show");

    }



    function agregar_marca_repuesto()

    {

      $("#agregar-marca-repuesto-modal").modal("show");

    }



    function agregar_medida_familia(){

      $('#agregar-medida-familia-modal').modal("show");

    }



    function agregar_pais()

    {

      $("#agregar-pais-modal").modal("show");

    }



    function guardar_familia()

    {

      var url="{{url('familia')}}";

      var nombrefamilia=document.getElementById("nombre_fam").value;

      if(nombrefamilia.trim().length==0){

        Vue.swal({

                text: 'Ingrese nombre de familia...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }

      var porcentaje=document.getElementById("porcentaje_fam").value;

      if(porcentaje.trim().length==0){

        Vue.swal({

                text: 'Ingrese porcentaje...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

            return false;

      }

      var prefijo=document.getElementById("prefijo_fam").value;

      if(prefijo.trim().length==0){

        Vue.swal({

                text: 'Ingrese prefijo...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

            return false;

      }

      var donde="factuprodu";





      var parametros={nombrefamilia:nombrefamilia,

        porcentaje:porcentaje,

        prefijo:prefijo,

        donde:donde,

        btnGuardarFamilia:"ajo"

      };



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        espere("Agregando Familia...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="OK"){

          cargar_familia();

          Vue.swal({

            text: 'Familia Agregada...',

            position: 'top-end',

            icon: 'info',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        }else{

            Vue.swal({

                text: 'No guardó Familia',

                position: 'top-end',

                icon: 'error',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }



          },

          error: function(error){

            Vue.swal.close();

            var errores=JSON.parse(error.responseText);

            var salida="";

            for(var indice in errores)

            {

              salida=salida+errores[indice]+"<br>";

            }

            $('#mensajes').html("<p style='color:red'>"+salida+"</p>");

            Vue.swal({

                title: 'ERROR',

                text: salida,

                icon: 'error',

            });

          }

      });



      $("#agregar-familia-modal").modal("hide");



    }



    function guardar_marca_repuesto()

    {

      var url="{{url('marcarepuesto')}}";

      var marcarepuesto=document.getElementById("marcarepuesto").value;

      if(marcarepuesto.trim().length==0)

      {

        Vue.swal({

            text: 'Ingrese Marca de Repuesto...',

            position: 'top-end',

            icon: 'error',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        return false;

      }



      var donde="factuprodu";

      var parametros={marcarepuesto:marcarepuesto,

        donde:donde,

        btnGuardarMarcaRepuesto:"ajo"

      };



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        espere("Agregando Marca");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="OK"){

          cargar_marca_repuesto();

          cargar_fabricantes();

          Vue.swal({

            text: 'Marca de Repuesto Agregada...',

            position: 'top-end',

            icon: 'info',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        }else{

            Vue.swal({

                text: 'Uy!!! No guardó...',

                position: 'top-end',

                icon: 'error',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }



          },

          error: function(error){

            Vue.swal.close();

            var errores=JSON.parse(error.responseText);

            var salida="";

            for(var indice in errores)

            {

              salida=salida+errores[indice]+"<br>";

            }

            $('#mensajes').html("<p style='color:red'>"+salida+"</p>");

            Vue.swal({

                title: 'ERROR',

                text: salida,

                icon: 'error',

            });

          }

      });



      $("#agregar-marca-repuesto-modal").modal("hide");



    }



    function guardar_pais()

    {

      var url="{{url('pais')}}";

      var pais=document.getElementById("pais").value;

      if(pais.trim().length==0){

        Vue.swal({

            text: 'Escriba un País...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        return false;

      }

      var donde="factuprodu";

      var parametros={pais:pais,

        donde:donde,

        btnGuardarPais:"ajo"

      };



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        espere("Agregando País...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="OK"){

          cargar_pais();

          Vue.swal({

                text: 'País Agregado...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{

            Vue.swal({

                text: 'Upss!!! no guardó el país...',

                position: 'top-end',

                icon: 'error',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }



          },

          error: function(error){

            Vue.swal.close();

            var errores=JSON.parse(error.responseText);

            var salida="";

            for(var indice in errores)

            {

              salida=salida+errores[indice]+"<br>";

            }

            $('#mensajes').html("<p style='color:red'>"+salida+"</p>");

            Vue.swal({

                title: 'ERROR',

                text: salida,

                icon: 'error',

            });

          }

      });



      $("#agregar-pais-modal").modal("hide");



    }



    function guardar_medida(){

      let medida = document.getElementById('medida').value;

      let familia = document.getElementById('familia').value;



      var url="{{url('medidas')}}";



      console.log(familia);

      if(medida.trim().length==0){

        Vue.swal({

            text: 'Escriba una medida...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        return false;

      }



      let data = {medida: medida, id_familia: familia, btnGuardarMedida: 'ajo'};



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

        type:'post',

        url:url,

        data:data,

        beforeSend: function(){

          espere("Agregando Medida...");

        },

        success: function(resp){

          if(resp == 'OK'){

            Vue.swal.close();

            cargar_medidas();

          

            Vue.swal({

                text: 'Medida Agregada...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{

            Vue.swal({

                text: 'Upss!!! no guardó la medida...',

                position: 'top-end',

                icon: 'error',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }

          

        },

        error: function(error){

          console.log(error.responseText);

        }

      })



    }





    function soloNumeros(e)

    {

      var key = window.Event ? e.which : e.keyCode

      return ((key >= 48 && key <= 57) || (key==8))

    }



    function validarCabeceraFactura()

    {

        cantidad_repuestos_factura=0;

      var prov=document.getElementById("proveedor").value;

      if(prov==0)

      {

        Vue.swal({

            text: 'Elija un Proveedor...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        return false;

      }



      var f=document.getElementById("numerofactura").value;

      if(f.trim().length==0)

      {

        Vue.swal({

                    text: 'Ingrese Núm de Factura',

                    position: 'top-end',

                    icon: 'warning',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

        return false;

      }



      var fechafactura = document.getElementById("fechafactura").value;

      if(fechafactura.trim().length==0)

      {

        Vue.swal({

                    text: 'Ingrese Fecha de Factura',

                    position: 'top-end',

                    icon: 'warning',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

        return false;

      }



      var vencefactura = document.getElementById("vencefactura").value;

      var check=document.getElementById("credito");

      if(check.checked && vencefactura.trim().length==0)

      {

        Vue.swal({

                    text: 'Ingrese Fecha de Vencimiento de Factura a Crédito',

                    position: 'top-end',

                    icon: 'warning',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

        return false;

      }



      var proveedor=document.getElementById("proveedor");

      var idproveedor=proveedor.value;

      var elproveedor=proveedor.options[proveedor.selectedIndex].text;

      var f=document.getElementById("numerofactura").value;



      var url='{{url("factuprodu/verificafactura")}}'+'/'+idproveedor+"*"+f;

      $.ajax({

        type:'GET',

        beforeSend: function () {



        },

        url:url,

        success:function(resp){

          var r=resp.split("*");



          if(r[0]=="EXISTE")

          {



            //Llega desde el controlador: resultado, id, numfac,fecfac... kaka

            var x=confirm("EXISTE FACTURA N° "+r[2]+" con fecha "+r[3]+"\n \nDesea Agregar más Items??");

            if(x)

            {

                $("#total_repuestos_factura").html("Factura: "+r[4]);

                cantidad_repuestos_factura=parseInt(r[4]);

                if(r[5]==0){

                    $("#total_repuestos_ot").html("No hay OT");

                }else{

                    $("#total_repuestos_ot").html("OT: "+r[5]);

                }



                //Ponemos el total de repuestos digitados

                Vue.swal({

                    text: 'Ingrese Item...',

                    position: 'top-end',

                    icon: 'info',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

              //r[1] tiene el id de compras_cab

              habilitar_ingreso_items(r[1]);



            }else{

                Vue.swal({

                    text: 'Ingrese otra Factura',

                    position: 'top-end',

                    icon: 'info',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

            }

          }else{ //No existe factura para ese proveedor

            //Llega desde el controlador: resultado,0,0

            guardarCabeceraFactura();

            $("#total_repuestos_factura").html("Factura: "+r[3]);

            cantidad_repuestos_factura=parseInt(r[4]);

            if(r[4]==0){

                $("#total_repuestos_ot").html("No hay OT");

            }else{

                $("#total_repuestos_ot").html("OT: "+r[4]);

            }

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



  function calculasubtotalitem()

  {

    var cantidad = document.getElementById("cantidad").value;

    var pu = document.getElementById("pu").value;

    if(cantidad>0 && pu>0){

        document.getElementById("subtotalitem").value=cantidad*pu;

    }else{

        Vue.swal({

            text: 'Cantidad y Precio Unitario deben ser mayor a 0',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

    }



  }



  function calculapreciosug()

  {

    let cboFAM=document.getElementById("familia").value;

    if(cboFAM=="0" || cboFAM.trim().length==0)

    {

        Vue.swal({

                text: 'Elija una familia...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

      return false;

    }



    var cant=document.getElementById("cantidad").value;

    if(cant=="0" || cant.length==0 || isNaN(cant) || Number(cant)<1)

    {

        Vue.swal({

                text: 'Ingrese Cantidad válida...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

      document.getElementById("cantidad").focus();

      return false;

    }

    var pu=document.getElementById("pu").value;



    if(pu=="0.00" || pu.length==0 || isNaN(pu) || Number(pu)<1)

    {

        Vue.swal({

                text: 'Elija Precio Unitario Válido...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

      document.getElementById("pu").focus();

      return false;

    }



    var fle=document.getElementById("flete").value;

    var flete=0;



    if(isNaN(fle) || Number(fle)<0 || fle.length==0 )

    {

        document.getElementById("flete").value=0;

        /*

        Vue.swal({

                text: 'Ingrese Flete Válido o presione el bóton CALCULAR',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        document.getElementById("flete").focus();

      return false;

      */

    }else{

      flete=parseInt(fle);



    }



    var pu=parseInt(document.getElementById("pu").value);

    var iva=0.19; // Leer de la tabla parámetros

    var utilidad=parseFloat(document.getElementById("utilidad").value)/100.0;

    var ps=document.getElementById("preciosug");

    var elvalor=pu*(1+utilidad)*(1+iva)+flete;



    /* REDONDEO



    //aplicar redondeo divisor por 100

    var divisor=elvalor/100;

    var entero=Math.trunc(divisor);

    var entero_x_100=entero*100;

    var residuo=elvalor%100;

    var valor_redondeado=0;

    if(residuo>50)

    {

      valor_redondeado=entero_x_100+100;

    }else{

      valor_redondeado=entero_x_100;

    }



    if(flete==0 && ps.value>0) //calcula el flete

    {

        flete=ps.value-valor_redondeado;

        document.getElementById("flete").value=flete;

    }



    if((flete>0 && ps.value==0) || (flete==0 && ps.value==0))

    {

        ps.value=valor_redondeado;

    }

*/

    if(flete==0 && ps.value>0) //calcula el flete

    {

        flete=ps.value-elvalor;

        document.getElementById("flete").value=flete;

    }

    //ps.select();

  }



  function dameUtilidad(value)

  {



    console.log(value);

    var url_medidas = '{{url("factuprodu")}}'+'/'+value+'/medidas';



    $.ajax({

      type:'get',

      url: url_medidas,

      beforeSend: function(){

        console.log('buscando...');

      },

      success: function(resp){

        if(resp.length == 0){

          $('#span_medidas').empty();

          $('#span_medidas').append(`

          <label for="medidas">Medidas:</label>

                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>

          `);

        }else{

          

          $('#span_medidas').empty();

          

          // let html = `

          // <label style='float:left;'>Medidas: </label>

          // <div class='clearfix'> </div>

          // <div id='global'>

          //   <ul>`;

          //     resp.forEach(e => {

          //       html+= '<li>'+e.descripcion+' </li>';

          //     })

          // html+= `</ul>

          // </div>`;

          // $('#span_medidas').append(html);

          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);

          let button_html = `

          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>

          `;

          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;width: 80%;/* float: left; */padding: 20px;line-height: 0px;' data-toggle='modal' data-target='#verMedidasModal' >Ver medidas </button>`;

          $('#span_medidas').append(btn_ver_medidas);

          $('#span_medidas').append(button_html);

          $('#modal_body_medidas').empty();

          let html = `

          <div class='clearfix'> </div>

          <table class="table">

            <thead>

            <tr>

              <th scope="col">Descripción</th>

            </tr>

          </thead>

          <tbody>`;

              resp.forEach(e => {

                html+= '<tr><th scope="row">'+e.descripcion+' </th></tr>';

              })

          html+= `</tbody>

          </table>

          `;

          $('#modal_body_medidas').append(html);

          }



        

      }

    });

   

    // campos id y porcentaje de la tabla familias

    var idFamilia=document.getElementById("familia").value;

    if(idFamilia=="0" || idFamilia.trim().length==0)

    {

        return false;

    }

    //Petición AJAX para obtener el porcentaje respectivo



    var url_familia='{{url("factuprodu")}}'+'/'+idFamilia+'/utilidad'; //petición



      $.ajax({

        type:'GET',

        beforeSend: function () {

          //$("#mensajes").html("Obteniendo Utilidad...");

        },

        url:url_familia,

        success:function(utilidad){

          document.getElementById("utilidad").value=utilidad;

        },

        error: function(error){

          Vue.swal.close();

          $('#mensajes').html(error.responseText);

          Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

        }



      }); //Fin ajax dameUtilidad



  }



  function eliminaritem(id)

  {

    if(confirm('Desea eliminar Item?')==true)

    {

        var url_item='{{url("compras")}}'+'/'+id+'/eliminar'; //petición

        var idFactura=document.getElementById("id_factura_cab").value;

        $.ajax({

         type:'GET',

         beforeSend: function () {

            espere("Eliminando Item...");

        },

        url:url_item,

        success:function(datos){

        Vue.swal.close();

          $("#mensajes").html(datos);

            dameItems(idFactura); //Carga los items ESTA PARTE NO  FUNCIONA...



          },

          error: function(error){

            Vue.swal.close();

            Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }



          }); //Fin ajax eliminar item

        return true;

      } // fin if confirmacion

    }



    function dameItems(id_factura)

    {



      var url_items='{{url("compras")}}'+'/'+id_factura+'/dameitems'; //petición



      $.ajax({

       type:'GET',

       beforeSend: function () {

        $("#compras_det").html("Obteniendo Items...");

      },

      url:url_items,

      success:function(datos){

        $("#compras_det").html(datos);

        /*

        $("#tbl_items").DataTable({

          "scrollY":        "300px",

          "scrollCollapse": true,

          "paging":false,

          "searching":false,

          "info":false

        });

        */

      },

      error: function(error){

        $('#compras_det').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        }); //Fin ajax items







    }



    function existe_en_factura(cod){

        let rpta=false;

        let idFactura=document.getElementById("id_factura_cab").value;

        let idProveedor=document.getElementById("proveedor").value;

        let url='{{url("factuprodu")}}'+'/'+cod+'/proveedor/'+idProveedor+'/factura/'+idFactura; //petición

        $.ajax({

            type:'GET',

            url:url,

            async: false,

            cache: false,

            success:function(resp){

                if(resp=="EXISTE") rpta=true;



            },

            error: function(error){

                Vue.swal({

                        title: 'ERROR',

                        text: error.responseText,

                        icon: 'error',

                    });

            }



        }); //Fin ajax items

        return rpta;

    }



    function verificar_cod_prov()

    {

        var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;

        if(cod_repuesto_proveedor.trim().length==0)

        {

            Vue.swal({

                    text: 'Ingrese Código de Repuesto...',

                    position: 'top-end',

                    icon: 'warning',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                });

            return false;

        }



        if(existe_en_factura(cod_repuesto_proveedor)){

            document.getElementById("cod_repuesto_proveedor").focus();

            document.getElementById("cod_repuesto_proveedor").select();

            Vue.swal({

                text: 'Código de Repuesto ya existe para la Factura y Proveedor elegidos..',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{

            guardarItem();

        }





      /*

      var idFactura=document.getElementById("id_factura_cab").value;

      var idProveedor=document.getElementById("proveedor").value;

      var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;

      if(cod_repuesto_proveedor.trim().length==0)

      {

        Vue.swal({

                text: 'Ingrese Código de Repuesto...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }



      var url_items='{{url("factuprodu")}}'+'/'+cod_repuesto_proveedor+'/proveedor/'+idProveedor+'/factura/'+idFactura; //petición



      $.ajax({

       type:'GET',

       beforeSend: function () {

        $("#mensajes").html("Verificando Código Repuesto...");

      },

      url:url_items,

      success:function(resp){





        if(resp=="NO EXISTE")

        {

          guardarItem();

        }else{

          document.getElementById("cod_repuesto_proveedor").focus();

          document.getElementById("cod_repuesto_proveedor").select();

          Vue.swal({

                text: 'Código de Repuesto ya existe para la Factura y Proveedor elegidos..',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }



      },

      error: function(error){

        $('#mensajes').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        }); //Fin ajax items

*/





    }



    function guardarItem() //kaka

    {



      //Valores del item de repuesto

      var idFamilia=document.getElementById("familia").value;

      if(idFamilia==0)

      {

        Vue.swal({

            text: 'Seleccione una Familia',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false;

      }

      var idMarcaRepuesto=document.getElementById("MarcaRepuesto").value;

      if(idMarcaRepuesto==0)

      {

        Vue.swal({

            text: 'Seleccione una marca de Repuesto',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false;

      }

      var idProveedor=document.getElementById("proveedor").value;

      var idPais=document.getElementById("Pais").value;

      if(idPais==0)

      {

        Vue.swal({

            text: 'Seleccione un País de Origen...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false;

      }

      var descripcion=document.getElementById("descripcion").value;

      if(descripcion.trim().length==0)

      {

        Vue.swal({

                    text: 'Ingrese una descripción',

                    position: 'top-end',

                    icon: 'warning',

                    toast: true,

                    showConfirmButton: false,

                    timer: 3000,

                    });

        return false;

      }

      var observaciones=document.getElementById("observaciones").value;

      var medidas=document.getElementById("medidas").value;

      if(medidas.trim().length==0) medidas="No Definidas";

      var cod_oem="---"; //document.getElementById("codigo_oem").value;

      var cod_repuesto_proveedor=document.getElementById("cod_repuesto_proveedor").value;

      if(cod_repuesto_proveedor.trim().length==0)

      {

        Vue.swal({

            text: 'Ingrese código de repuesto...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false;

      }



      //var cod2_repuesto_proveedor=document.getElementById("cod2_repuesto_proveedor").value;

      var stockmin=document.getElementById("stock_minimo").value;

      var stockmax=document.getElementById("stock_maximo").value;

      var codbar=document.getElementById("codigo_barras").value;





      //Valores del item de factura

      var idFactura=document.getElementById("id_factura_cab").value;

      var cantidad = document.getElementById("cantidad").value.trim();

      if(cantidad==0 || cantidad==0.00 || cantidad.length==0 || isNaN(cantidad))

      {

        Vue.swal({

            text: 'Ingrese Cantidad...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false

      }

      var pu = document.getElementById("pu").value.trim();



      if(pu==0 || pu==0.00 || pu.length==0 || isNaN(pu))

      {

        Vue.swal({

            text: 'Ingrese precio unitario...',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false

      }



      var subtotalitem=document.getElementById("subtotalitem").value;

      calculapreciosug();

      var flete=document.getElementById("flete").value;

      if(flete<0) flete=0;

      var preciosug=document.getElementById("preciosug").value.trim();



      if(preciosug==0 || preciosug==0.00 || preciosug.length==0 || isNaN(preciosug))

      {

        Vue.swal({

            text: 'Ingrese o Calcule Precio Sugerido',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

            });

        return false

      }



      var idLocal=document.getElementById("locales").value;

      var nuevo="SI";



      if(document.getElementById("elegir").value=="SI") nuevo="NO";

      var id_repuesto=document.getElementById("id_repuesto").value;

      var id_repuesto_clonado=0;

      if(clonando){

        id_repuesto_clonado=document.getElementById("id_repuesto_clonado").value;

      }



      var parametros={nuevo:nuevo,

        idrep:id_repuesto,

        idrep_clonado:id_repuesto_clonado,

        idFamilia:idFamilia,

        idMarcaRepuesto:idMarcaRepuesto,

        idProveedor:idProveedor,

        idPais:idPais,

        descripcion:descripcion,

        observaciones:observaciones,

        medidas:medidas,

        cod_oem:cod_oem.toUpperCase(),

        cod_repuesto_proveedor:cod_repuesto_proveedor.toUpperCase(),

        stockmin:stockmin,

        stockmax:stockmax,

        codbar:codbar,

        idFactura:idFactura,

        cantidad:cantidad,

        pu:pu,

        subtotalitem:subtotalitem,

        flete:flete,

        preciosug:preciosug,

        idLocal:idLocal

      };



      var url="{{url('factuprodu/guardaritem')}}";



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        espere("Guardando Item...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        var r=resp.split("*");

        cantidad_repuestos_factura+=parseInt(cantidad);

        /*

        console.log("guardaritem clonando es ");

        console.log(clonando?"SI":"NO");

        */

        if(r[0]==1) //1: Todo OK, NUEVO

        {

          if(clonando){

            dame_detalle(r[2]);

          }

          document.getElementById("codigo_interno").value=r[1]; //el último repuesto guardado siempre será clonable

          document.getElementById("id_repuesto").value=r[2];

          //$("#zona_ingreso_repuesto *").prop('disabled',true); //con JQuery

          document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.setAttribute("disabled", "true")); //con JS

          document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.setAttribute("disabled", "true"));

          document.getElementById("btnNuevoItem").disabled=false;

          //Habilitar el ingreso de fotos, similares y otros OEMs

          document.querySelectorAll("#zona_fotos *").forEach(el => el.removeAttribute("disabled"));

          document.querySelectorAll("#zona_similares *").forEach(el => el.removeAttribute("disabled"));

          document.querySelectorAll("#zona_OEMs *").forEach(el => el.removeAttribute("disabled"));

          document.querySelectorAll("#zona_FABs *").forEach(el => el.removeAttribute("disabled"));

          Vue.swal({

            text: 'Item Guardado...',

            position: 'top-end',

            icon: 'info',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

          location.href="#zona_fotos";

        }





        if(r[0]==2) //1: Existe el código... // Consultado con pancho el 22jun2020: guardar siempre el precio mayor

        {

          //var respu=confirm("Ya existe "+r[1]+": "+r[2]+" con \n precio compra: "+r[3]+" \n precio venta:"+r[4]+"\n \nDesea actualizar con \n precio compra: "+pu+" ?\n precio venta: "+preciosug+" ?");

            /*

          if(respu==true)

          {

               modificar_precio_repuesto(pu,preciosug,r[5]);

          }

          */

          limpiar_campos();

          $("#mensajes").html("Item Guardado con precio modificado...");

          location.href="#mensajes";

        }



        if(r[0]==3) //existe pero no se modificó el precio

        {

            limpiar_campos();

            $("#mensajes").html("Item Guardado sin novedad...");

            location.href="#mensajes";

        }



        if(r[0]=="xuxa")

        {

          $("#mensajes").html(r[1]);

        }



        $("#total_repuestos_factura").html("Factura: "+parseInt(cantidad_repuestos_factura));

          },

          error: function(error){

            Vue.swal.close();

            //$('#mensajes').html(error.responseText);

            Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }

      });



    }



    function modificar_precio_repuesto(precio_compra,precio_venta,id_repu)

    {

      var url='{{url("repuesto")}}'+'/'+id_repu+'/cambiaprecio/'+precio_compra+"/"+precio_venta;

      $.ajax({

       type:'GET',

      url:url,

      success:function(repu){



        Vue.swal.close();

        if(repu=="OK")

        {

            Vue.swal({

                text: 'Precio Modificado',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{

            Vue.swal({

                text: 'No se pudo modificar el precio',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

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



        }); //Fin ajax

    }



    function guardarfoto()

    {

      var url="{{url('factuprodu/guardarfoto')}}";

      var archivo=$("#archivo")[0].files[0];

      if(archivo.size > 510000)

      {

        Vue.swal({

                text: 'Imagen muy grande, no se puede guardar',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }



      var archi=document.getElementById("archivo");

      var archivo_nombre=archi.value;



      if(archivo_nombre.length==0)

      {

        Vue.swal({

                text: 'Elija una imagen...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }





      var idrep=document.getElementById("id_repuesto").value;

      var datos=new FormData();

      datos.append('idrep',idrep);

      datos.append('archivo',archivo);



      $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

       }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        $("#fotos_msje").html("<b>FOTOS:</b> Guardando Foto, espere por favor...");

        espere("Subiendo Imagen...");

      },

      url:url,

      data:datos,

      cache:false,

      contentType: false,

      processData: false,

      //timeout:15000, // 15 segundos

      success:function(resp){

        Vue.swal.close();

        if(resp=="EXISTE")

        {

          $("#fotos_msje").html("<b style='font-size:large;color:red'>FOTOS: Imagen ya existe...</b>");

          Vue.swal({

                text: 'Imagen ya existe...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{



          document.getElementById("archivo").value="";

          $("#fotos_msje").html("<b>FOTOS:</b> Foto Agregada...");

          Vue.swal({

                text: 'Foto Agregada...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#fotos_rep").html(resp);

        }





          },

      error: function(error){

        Vue.swal.close();

        $('#fotos_msje').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }

        });



    }



    function borrarfoto(idfoto)

    {



      var idrep=document.getElementById("id_repuesto").value;

      var url='{{url("factuprodu")}}'+'/'+idfoto+'/borrarfoto/'+idrep;

      $.ajax({

       type:'GET',

       beforeSend: function () {

        espere("Borrando Foto");

        $("#fotos_rep").html("");

      },

      url:url,

      success:function(resp){

          Vue.swal.close();

          $("#fotos_msje").html("<b>FOTOS:</b> Foto Borrada...");

          Vue.swal({

                text: 'Foto Borrada...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#fotos_rep").html(resp);

      },

      error: function(error){

        $('#mensajes').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        }); //Fin ajax

    }



    function guardarsimilar()

    {

      var url="{{url('factuprodu/guardarsimilar')}}";

      var idrep=document.getElementById("id_repuesto").value;

      var idMarca=document.getElementById("MarcaSim").value;

      var idModelo=document.getElementById("ModeloSim").value;

      var anios=document.getElementById("anios_vehiculo_sim").value.trim();

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



      var año_actual=(new Date).getFullYear();

      var año_inicial=anios.substring(0,4)*1;

      var año_final=anios.substring(5)*1;

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



      var parametros={idrep:idrep,idMarca:idMarca,idModelo:idModelo,anios:anios};



      $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

       }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {



        $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Guardando...");

        espere("Guardando Aplicación...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="EXISTE")

        {

          $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Aplicación ya fue Agregada...");

          Vue.swal({

                text: 'Aplicación ya fue agregada...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        }else{

          document.getElementById("anios_vehiculo_sim").value="";

          $("#nombre_modelo").html("<b>Modelo: </b>");

          $("#aplicaciones_msje").html("<B>APLICACIONES:</B> Aplicación Agregada...");

          Vue.swal({

                text: 'Aplicación Agregada...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#similares_rep").html(resp);

/*

          let filtro=document.getElementById("filtrar_modelo");

          filtro.value="";

          filtro.dispatchEvent(new KeyboardEvent('keyup',{'key':'Shift'}));

*/

        }

          },

      error: function(error){

        Vue.swal.close();

            $('#aplicaciones_msje').html(error.responseText);

            Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }

        });



    }



    function borrarsimilar(idsimilar)

    {

      var idrep=document.getElementById("id_repuesto").value;

      var url='{{url("factuprodu")}}'+'/'+idsimilar+'/borrarsimilar/'+idrep;

      $.ajax({

       type:'GET',

       beforeSend: function () {

        espere("Borrando aplicación...");

        $("#similares_rep").html("");

      },

      url:url,

      success:function(resp){

          Vue.swal.close();

          $("#aplicaciones_msje").html("<b>APLICACIONES:</b> Aplicación Borrada...");

          Vue.swal({

                text: 'Aplicación Borrada...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#similares_rep").html(resp);

      },

      error: function(error){

          Vue.swal.close();

        $('#mensajes').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        }); //Fin ajax

    }



    function enter_text_oem(event)

    {

      var keycode = event.keyCode;

      if(keycode=='13')

      {

        guardarOEM();

      }

    }



    function guardarOEM()

    {

      var url="{{url('factuprodu/guardaroem')}}";

      var idrep=document.getElementById("id_repuesto").value;

      var cod_oem=document.getElementById("codigos_OEM").value;

      cod_oem=cod_oem.replace(/-/g,"");

      cod_oem=cod_oem.toUpperCase();

      if(cod_oem.trim().length==0)

      {

        Vue.swal({

            text: 'Ingrese Código OEM',

            position: 'top-end',

            icon: 'warning',

            toast: true,

            showConfirmButton: false,

            timer: 3000,

        });

        return false;

      }



      var parametros={cod_oem:cod_oem.toUpperCase(),idrep:idrep};



      $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

       }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        $("#oems_msje").html("<b>OEMs:</b> Guardando OEM, espere por favor...");

        espere("Guardando OEM...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="EXISTE")

        {

          $("#oems_msje").html("<b>OEMs:</b> OEM ya fue agregado...");

          Vue.swal({

                text: 'OEM ya fue Agregado...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          document.getElementById("codigos_OEM").select();

        }else{

          $("#oems_msje").html("<b>OEMs:</b> OEM Agregado...");

          Vue.swal({

                text: 'OEM Agregado...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#oems_rep").html(resp);

          document.getElementById("codigos_OEM").value="";



        }

        document.getElementById("codigos_OEM").focus();

          },

      error: function(error){

        Vue.swal.close();

          $('#oems_msje').html(error.responseText);

          Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }

        });

    }



    function borraroem(idoem)

    {

      var idrep=document.getElementById("id_repuesto").value;

      var url='{{url("factuprodu")}}'+'/'+idoem+'/borraroem/'+idrep;

      $.ajax({

       type:'GET',

       beforeSend: function () {

        $("#oems_msje").html("<b>OEMs:</b> Borrando OEM, espere por favor...");

        espere("Borrando OEM...");

        $("#oems_rep").html("");

      },

      url:url,

      success:function(resp){

          Vue.swal.close();

          $("#oems_msje").html("<b>OEMs:</b> OEM Borrado...");

          Vue.swal({

                text: 'OEM borrado',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#oems_rep").html(resp);

      },

      error: function(error){

          Vue.swal.close();

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        }); //Fin ajax

    }



    function enter_text_FAB(event)

    {

      var keycode = event.keyCode;

      if(keycode=='13')

      {

        guardarFAB();

      }

    }



    function pasar_a_FAB()

    {

      document.getElementById("cboFabricante").value=document.getElementById("MarcaRepuesto").value;

    }



    function guardarFAB()

    {

      var idfab=document.getElementById("cboFabricante").value;

      if(idfab==0)

      {

        Vue.swal({

                text: 'Elija un Fabricante',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }

      var cod_fab=document.getElementById("codigo_FAB").value;

      if(cod_fab=="")

      {

        Vue.swal({

                text: 'Escriba Código de Fabricante',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        document.getElementById("codigo_FAB").focus();

        return false;

      }



      var url="{{url('factuprodu/guardarfab')}}";

      var idrep=document.getElementById("id_repuesto").value;

      var parametros={cod_fab:cod_fab.toUpperCase(),idrep:idrep,idfab:idfab};

      $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

       }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        $("#fabricantes_msje").html("<b>FABRICANTES:</b> Guardando Código, espere por favor...");

        espere("Guardando Código Fabricante...");

      },

      url:url,

      data:parametros,

      success:function(resp){

        Vue.swal.close();

        if(resp=="EXISTE")

        {

          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código ya fue agregado...");

          Vue.swal({

                text: 'Código ya fue Agregado...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          document.getElementById("codigo_FAB").select();

        }else{

          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código Agregado...");

          Vue.swal({

                text: 'Código Agregado...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#fabs_rep").html(resp);

          document.getElementById("codigo_FAB").value="";

          document.getElementById("cboFabricante").selectedIndex =0;

        }

        document.getElementById("codigo_FAB").focus();

          },

      error: function(error){

        Vue.swal.close();

          $('#fabricantes_msje').html(error.responseText);

          Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

          }

        });

    }



    function borrarfab(idfab)

    {

      var idrep=document.getElementById("id_repuesto").value;

      var url='{{url("factuprodu")}}'+'/'+idfab+'/borrarfab/'+idrep;

      $.ajax({

       type:'GET',

       beforeSend: function () {

        espere("Borrando Código...");

        $("#fabs_rep").html("");

      },

      url:url,

      success:function(resp){

          Vue.swal.close();

          $("#fabricantes_msje").html("<b>FABRICANTES:</b> Código Borrado...");

          Vue.swal({

                text: 'Código Borrado...',

                position: 'top-end',

                icon: 'info',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

          $("#fabs_rep").html(resp);

      },

      error: function(error){

        $('#fabricantes_msje').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



        });

    }



    function nuevo_item()

    {

      limpiar_campos();

    }



    function limpiar_campos()

    {

      clonando=false;

      //document.getElementById("proveedor").selectedIndex=0;

      document.getElementById("familia").selectedIndex=0;

      document.getElementById("MarcaSim").selectedIndex=0;

      $('#ModeloSim option').remove();

      $('#ModeloSim').append('<option value="">Elija una marca</option>');

      $("#nombre_modelo").html("<b>Modelo: </b>");

      $("#aplicaciones_msje").html("<B>APLICACIONES:</B>");

      $("#fotos_msje").html("<B>FOTOS:</B>");

      $("#oems_msje").html("<B>OEMs:</B>");

      $("#fabricantes_msje").html("<B>FABRICANTES:</B>");



      document.getElementById("ModeloSim").selectedIndex=0;

      document.getElementById("MarcaRepuesto").selectedIndex=0;

      document.getElementById("Pais").selectedIndex=0;



      document.getElementById("txtBuscar").value="";



      document.getElementById("descripcion").value="";

      document.getElementById("observaciones").value="";

      document.getElementById("medidas").value="";

      document.getElementById("cod_repuesto_proveedor").value="";

      //document.getElementById("codigo_oem").value="";

      document.getElementById("stock_minimo").value="3";

      document.getElementById("stock_maximo").value="0";

      document.getElementById("codigo_barras").value="0";



      document.getElementById("cantidad").value="0";

      document.getElementById("pu").value="0.00";

      document.getElementById("subtotalitem").value="0.00";

      document.getElementById("flete").value="0.00";

      document.getElementById("preciosug").value="0.00";



      document.getElementById("id_repuesto").value="";

      document.getElementById("utilidad").value="";

      document.getElementById("elegir").value="NO";



      //habilitar reIngreso de Items

      document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.removeAttribute("disabled"));

      document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.removeAttribute("disabled"));







      //Deshabilitar el ingreso de fotos, similares y otros OEMs

      document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));

      document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));

      document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));

      document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));



      //Limpiar las fotos, similares y OEMs

      $("#fotos_rep").html("");

      $("#similares_rep").html("");

      $("#oems_rep").html("");

      $("#fabs_rep").html("");

      document.getElementById("archivo").value="";

      //document.getElementById("anios_vehiculo_sim").value="";

      document.getElementById("codigos_OEM").value="";

      document.getElementById("txtBuscar").focus();





    }



    function elegir_repuesto(repuesto)

    {

      $('#busca-repuesto-modal').modal('hide');

      document.getElementById("id_repuesto").value=repuesto.id;

      document.getElementById("familia").value=repuesto.id_familia;

      dameUtilidad();

      document.getElementById("MarcaRepuesto").value=repuesto.id_marca_repuesto;

      document.getElementById("Pais").value=repuesto.id_pais;

      document.getElementById("descripcion").value=repuesto.descripcion;

      if(repuesto.observaciones=="@@@"){

        document.getElementById("observaciones").value="";

        }else{

        document.getElementById("observaciones").value=repuesto.observaciones;

    }

      document.getElementById("observaciones").value=repuesto.observaciones;

      document.getElementById("medidas").value=repuesto.medidas;

      console.log("elegir_repuesto clonando es ");

        console.log(clonando?"SI":"NO");

      if(clonando){

        document.getElementById("id_repuesto_clonado").value=repuesto.id;

        document.getElementById("codigo_interno").value=0;

        document.getElementById("cod_repuesto_proveedor").value="";

        document.getElementById("pu").value=0;

        document.getElementById("elegir").value="NO";

      }else{

        document.getElementById("id_repuesto_clonado").value=0;

        document.getElementById("codigo_interno").value=repuesto.codigo_interno;

        document.getElementById("cod_repuesto_proveedor").value=repuesto.cod_repuesto_proveedor;

        document.getElementById("pu").value=repuesto.precio_compra;

        document.getElementById("elegir").value="SI";

      }



      document.getElementById("stock_minimo").value=repuesto.stock_minimo;

      document.getElementById("stock_maximo").value=repuesto.stock_maximo;

      document.getElementById("codigo_barras").value=repuesto.codigo_barras;

      document.getElementById("cantidad").value=0;

      document.getElementById("flete").value=0;

      document.getElementById("preciosug").value=0;





      //Cargar fotos



      //Cargar similares



      //Cargar oems



      //cargar fabricantes

      // 26 nov 2019, le pregunté a pancho y me dijo que no era necesario cargarlos luego de la primera vez







      //console.log(repuesto);

      //El focus no funciona porque al cerrar el modal, bootstrap autoenfoca

      //en el elemento que abrió el modal...

      //document.getElementById("id_repuesto").value=id_repuesto;

      //document.getElementById("cantidad").select();

    }



    function buscarRepuesto()

    {

      var codigo=document.getElementById("txtBuscar").value;

      if(codigo.trim().length==0){

        Vue.swal({

                text: 'Ingrese Código Repuesto...',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }

      let ef=existe_en_factura(codigo);

      if(ef){

        Vue.swal({

                text: 'Código de Repuesto ya existe en la Factura y Proveedor elegidos..',

                position: 'top-end',

                icon: 'warning',

                toast: true,

                showConfirmButton: false,

                timer: 3000,

            });

        return false;

      }

      document.getElementById("cod_repuesto_proveedor").value=codigo;

      var id_prov=document.getElementById("proveedor").value;

      var bb=$('input[name="buscarpor"]:checked').val().trim();

      if(bb=="oem")

      {

        codigo="0"+codigo;

      }else{

        codigo="1"+codigo;

      }



      var url_buscar='{{url("factuprodu")}}'+'/'+codigo+'/buscarepuesto/'+id_prov; //petición



      $.ajax({

       type:'GET',

      url:url_buscar,

      success:function(resp){

        //Vue.swal.close();

        $("#mostrar_repuestos").html(resp);

      //$("#mostrar_repuestos").html(resp);

      // resp[0].codigo_interno CUANDO se recibe una colección desde laravel;

      // resp.codigo_interno CUANDO se recibe un array desde laravel;



      //var ci="Cód. Interno: "+resp.codigo_interno;



        //Mostrar los datos en los campos respectivos

        /*

        if(resp==0)

        {

          alert("No existe el codigo "+codigo);

          $("#mensajes").html("No existe el codigo "+codigo);

        }else{

          var ci="Cód. Interno: "+resp[0].codigo_interno; // resp[0].codigo_interno;

          $("#codigo_interno").html(ci);

          $("#familia").val(resp[0].id_familia);

          $("#familia").change();







          //$("#mensajes").html("Encontrado...");

        }

        */







        $("#busca-repuesto-modal").modal("show");



      },

      error: function(error){

        $("#busca-repuesto-modal").modal("hide");

        $('#mensajes').html(error.responseText);

        Vue.swal({

                title: 'ERROR',

                text: error.responseText,

                icon: 'error',

            });

      }



    });



    }



//APLICACIONES

    function cargarModelosSimilares()

    {

        var idMarcaSim=document.getElementById("MarcaSim").value;



        if(idMarcaSim!="")

        {

          document.getElementById("anios_vehiculo_sim").value="";

            var url='{{url("modelovehiculo/damepormarca")}}'+'/'+idMarcaSim;



            $.ajax({

              type:'GET',

              beforeSend: function () {

                $("#aplicaciones_msje").html("Cargando Marcas de Vehículos...");

                $("#nombre_modelo").html("<b>Modelo: </b>");

                $('#ModeloSim option').remove();

                $('#ModeloSim').append('<option value="">Buscando...</option>');

              },

              url:url,

              success:function(models){ //Viene en formato json

                $('#ModeloSim option').remove();

                var modelos=JSON.parse(models);

                $('#ModeloSim').append('<option value="">Elija un modelo</option>');

                modelos.forEach(function(modelo){

                    if(modelo.zofri==1){

                        $('#ModeloSim').append('<option value="'+modelo.id+'">ZOFRI - '+modelo.modelonombre+' \('+modelo.anios_vehiculo.trim()+'\)</option>');

                    }else{

                        $('#ModeloSim').append('<option value="'+modelo.id+'">'+modelo.modelonombre+' \('+modelo.anios_vehiculo.trim()+'\)</option>');

                    }

                });

                document.getElementById("ModeloSim").selectedIndex=0;

                $("#aplicaciones_msje").html("Listo...");

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

        }else{

          $('#ModeloSim option').remove();

          $('#ModeloSim').append('<option value="">Elija una Marca</option>');

        }

    }







    function revisarCredito()

    {

      var check=document.getElementById("credito");

      var fechavenc=document.getElementById("vencefactura");

      if(check.checked)

      {

        fechavenc.disabled=false;

      }else{

        //fechavenc.value="";

        fechavenc.disabled=true;

      }

    }



    function habilitar_ingreso_items(id_compras_cab)

    {



        document.getElementById("id_factura_cab").value=id_compras_cab; //Contiene el id de compras_cab



        document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.removeAttribute("disabled"));

        document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.removeAttribute("disabled"));



        //desactivar cabecera factura

        document.getElementById("proveedor").disabled=true;

        document.getElementById("numerofactura").disabled=true;

        document.getElementById("fechafactura").disabled=true;

        document.getElementById("credito").disabled=true;

        document.getElementById("vencefactura").disabled=true;

        document.getElementById("btnGuardarCabeceraFactura").disabled=true;



        location.href="#ingreso_datos_titulo";

        document.getElementById("txtBuscar").focus();



    }



    function guardarCabeceraFactura()

    {



      var url="{{url('compras/guardarcabecera')}}";

      //var botonguardaritem=document.getElementById("btnGuardarItem");



      var idProveedor=document.getElementById("proveedor").value;

      var numerofactura = document.getElementById("numerofactura").value;

      var fechafactura = document.getElementById("fechafactura").value;

      var esCredito=document.getElementById("credito").checked;

      var vencefactura = document.getElementById("vencefactura").value;



      var parametros={idproveedor:idProveedor,

        numerofactura:numerofactura,

        fechafactura:fechafactura,

        escredito:esCredito,

        vencefactura:vencefactura

      };



      $.ajaxSetup({

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });



      $.ajax({

       type:'POST',

       beforeSend: function () {

        espere("Guardando Factura...");

      },

      url:url,

      data:parametros,

      success:function(id_compras_cab){

        Vue.swal.close();



        if(id_compras_cab>0)

        {

          habilitar_ingreso_items(id_compras_cab);

        }else{

          Vue.swal({

                title: 'ERROR',

                text: "NO GUARDÓ CABECERA DE FACTURA (compras_cab)",

                icon: 'error',

            });

        }



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









      //Puede usarse para borrar un item

      function confirmacion(){

        if (confirm('Esta seguro de eliminar el registro?')==true) {

          //alert('El registro ha sido  eliminado correctamente!!!');

          return true;

        }else{

          //alert('Cancelo la eliminacion');

          return false;

        }

      }



//poner onload windows y cargar familias y demas combos

window.onload = function(e){

    // document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.setAttribute("disabled", "true"));

    // document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.setAttribute("disabled", "true"));

    // document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));

    // document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));

    // document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));

    // document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));





    cargar_familia();

    cargar_fabricantes();

    cargar_marca_repuesto();

    cargar_pais();



    $('#filtrar_modelo').keyup(function() {

        let filtro = $(this).val().toUpperCase();

        let opciones = $('#ModeloSim').find('option');

        opciones.each(function() {

            if ($(this).text().indexOf(filtro) != -1) {

                $(this).show();

            } else {

                $(this).hide();

            }

            $('#ModeloSim').val(filtro);

        })

    });

};



  </script>

@endsection

@section('style')

<style>

.form-control-sm{

    padding:2px;

    font-size:12px;

    height: 24px;

}



.btn-sm{

    padding: 1px 5px;

    font-size:12px;

    height: 24px;

}



 .enlinea{

     display:flex;

     flex-direction: row;

     justify-content: start;

     align-items:start;

 }



/* ALINEACION HORIZONTAL DEL CHECKBOX Y SU LABEL, VER EN factu_produ.blade.php */

input[type="checkbox"],

label {

    float: left;

    line-height: 1.6em;

    height: 1.6em;

    margin: 0px 2px;

    padding: 0px;

    font-size: inherit;

}





.contenedor{

    display:grid;

    grid-template-columns: 24% 40% 18% 18%;

    grid-template-rows: 50px 80px 240px 90px 400px;

    height: 100%;

    row-gap: 5px;

    column-gap: 2px;

    align-items: start;

}



.g-titulo{

    grid-column: 1/5;

    display: grid;

    grid-template-columns: 80% 20%;

    grid-template-rows: 2fr 1fr;

}



.g-cabecera{

    grid-column: 1/5;

    background-color: #D0FFFF;

    display:grid;

    grid-template-columns: repeat(6,1fr);

    grid-template-rows: 1fr 2fr;

    column-gap: 2px;

    /* justify-items: center;

    align-items:end; */



}

#titulo-factura{

    grid-column: 1/6;

    text-align: center;

}

#btnGuardarCabeceraFactura-div{

    text-align: right;

}



#titulo-factura-fila2{

    grid-row:2/3;

}

.g-repuestos{

    grid-column: 1/5;

    background-color: #F2F5A9;

    display:grid;

    grid-template-columns: repeat(6,1fr);

    grid-template-rows: 1fr 9fr;

    column-gap: 2px;

}



.contenedor .g-repuestos  .g-repuestos-titulo{

    grid-column: 1/7;

    display:grid;

    grid-template-columns: 9fr 1fr;

    grid-template-rows: 1fr;

    align-items:start;

    align-content:start;

}



#zona_ingreso_repuesto{

    grid-column: 1/7;

    display:grid;

    grid-template-columns: repeat(6,1fr);

    grid-template-rows: 30px 30px 90px 50px;

    align-items: start;

    align-content:start;

}



#zona_ingreso_repuesto .g-repuestos-busqueda{

    grid-column:1/7;

    display:grid;

    grid-template-columns: 1fr 1fr 1fr 2fr;

    grid-template-rows: 1fr;



   /* grid-template-rows: 1fr;*/

}



#ing-cod-rep{

    display:grid;

    justify-content: end;



}



#txtBuscar{

    width: 95%;

}



#zona_ingreso_repuesto .g-repuestos-familia{

    grid-column: 1/7;

    display:grid;

    grid-template-columns: repeat(5,1fr);

    grid-gap:5px;

    align-items:start;

    align-content:start;

}



#zona_ingreso_repuesto .g-repuestos-descripcion{

    grid-column: 1/7;

    display:grid;

    grid-template-columns: repeat(5,1fr);

    grid-gap:5px;

    align-items:start;

    align-content:start;

}

#zona_ingreso_repuesto .g-repuestos-stock{

    grid-column: 1/7;

    display:grid;

    grid-template-columns: repeat(5,1fr);

    grid-gap:5px;

    align-items:start;

    align-content:start;

}





.g-items{

    grid-column: 1/5;

    display:grid;

    grid-template-columns: 1fr 1fr 1fr 1fr 2fr 3fr ;

    grid-template-rows: 30px 60px;

}



#titulo-item-factura{

    grid-column:4/6;

    display:grid;

    grid-template-columns: 80% 20%;

    justify-items:center;

    align-items:start;

    align-content:start;

}



#btnGuardarItem-div{

    text-align: left;

}



.g-fotos{

    background-color: #D0FFFF;

    display:grid;

    grid-template-columns: 75% 25%;

    grid-template-rows: 30px 60px 30px 400px;

}

.fotos_msje{

    grid-column:1/3;

}

#fotos_subir{

    grid-column: 1/3;

}

#fotos_submit{

    grid-column: 1/3;

}

#fotos_rep{

    grid-column:1/3;

    grid-row: 4/5;

}



.g-aplicaciones{

    display:grid;

    grid-template-columns: 1fr 1fr 1fr;

    grid-template-rows: 40px 200px 40px 50px 300px;

}



.aplicaciones_msje{

    grid-column:1/3;

}

.aplicaciones_modelos{

    grid-column:2/4;

    grid-row:2/3;

}

.aplicaciones_nombre_modelo{

    grid-column:1/4;

    grid-row:3/4;

}

.aplicaciones_anios_vehiculo{

    grid-column: 1/4;

    grid-row:4/5;

}

#similares_rep{

    grid-column:1/4;

    grid-row:5;

}



.g-oems{

    background-color: #D0FFFF;

    display:grid;

    grid-template-columns: 1fr 1fr;

    grid-template-rows:1fr 1fr 3fr;

    align-items: start;

}



.oems_msje{

    grid-column: 1/3;

}



#oems_rep{

    grid-column:1/3;

    grid-row:3/4;

}



.g-fabricantes{

    display:grid;

    grid-template-columns: 1fr 1fr;

    grid-template-rows:1fr 1fr 1fr 3fr;

}

.fabricantes_msje{

    grid-column: 1/3;

}

.fabricantes_elegir{

    grid-column: 1/2;

    grid-row:2/3;

}

.fabricantes_codigo{

    grid-column: 2/3;

    grid-row:2/3;

}

.btnGuardarFAB{

    grid-column: 1/3;

    grid-row:3/4;

}

#fabs_rep{

    grid-column: 1/3;

    grid-row:4/5;

}



#global {

	height: 60px;

	width: 85%;

  float: left;

	border: 1px solid #ddd;

	background: #f1f1f1;

	overflow-y: scroll;

}





</style>

@endsection

@section('contenido_ingresa_datos')

<div class="contenedor">

    <div class="g-titulo">

            <div>

                <center><h4>Ingreso por Compras</h4></center>

            </div>

            <div style="text-align: right;">

                <a href="{{url('factuprodu/crear')}}" id="btnNuevaFactura" class="btn btn-success btn-sm" >Nueva Factura</a>

            </div>

            <div id="mensajes"></div>

    </div>    {{--  FIN de titulo --}}



        <!-- CAMPOS OCULTOS -->

        <input type="hidden" id="id_factura_cab">

        <input type="hidden" id="id_repuesto">

        <input type="hidden" id="id_repuesto_clonado">

        <input type="hidden" id="elegir" value="NO">

        <input type="hidden" id="utilidad">

        <!-- <input type="hidden" id="codigo_interno" value="0"> -->





        <!-- FIN DE CAMPOS OCULTOS -->



    <div class="g-cabecera">

        <div id="titulo-factura"><strong>FACTURA</strong></div>

        <div id="btnGuardarCabeceraFactura-div">

            <input type="submit" id="btnGuardarCabeceraFactura" onclick="validarCabeceraFactura()" name="btnGuardarCabeceraFactura" value="Guardar Cabecera" class="btn btn-primary btn-sm"/>

        </div>



        <div id="titulo-factura-fila2">

            <label for="proveedor">Proveedor:</label>

            <select name="proveedor" class="form-control form-control-sm" id="proveedor">

                <option value="0">Elija un Proveedor</option>

                @foreach ($proveedores as $proveedor)

                    <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>

                @endforeach

            </select>

        </div>

        <div style="margin-left:10px;margin-right:10px">

            <label for="numerofactura">Número:</label>

            <input type="text" name="numerofactura" value=""  id="numerofactura" class="form-control form-control-sm" onKeyPress="return soloNumeros(event)" >

        </div>

        <div style="margin-left:10px;margin-right:10px">

            <label for="fechafactura">Fecha:</label>

            <input type="date" name="fechafactura" value=""  id="fechafactura" class="form-control  form-control-sm">

        </div>

        <div class="form-group" style="margin-top: 25px;margin-left:50px;margin-right:10px">

                <input type="checkbox" class="form-check-input form-control-sm" id="credito" onclick="revisarCredito()">

                <label class="form-check-label" for="credito" style="margin-left:20px">Crédito?</label>

        </div>

        <div style="margin-left:10px;margin-right:10px">

            <label for="vencefactura">Fecha Venc.</label>

            <input type="date" name="vencefactura" value=""  id="vencefactura" class="form-control form-control-sm" disabled>

        </div>

        <div style="margin-left:10px;margin-right:10px">

            <label for="total_repuestos">Cantidad Repuestos</label><br>

            <div id="total_repuestos" style="display: inline-flex;width:100%">

                <div id="total_repuestos_factura" class="form-control form-control-sm text-center" style="width:50%">Factura: 0</div>

                <div id="total_repuestos_ot" class="form-control form-control-sm text-center" style="width:50%">OT: 0</div>

            </div>



        </div>



    </div> <!--Fin CABECERA FACTURA-->



    <div class="g-repuestos">

        <div class="g-repuestos-titulo">

            <p class="text-center" id="ingreso_datos_titulo"><b>INGRESO DE DATOS GENERALES DEL REPUESTO</b></p>

            <input type="submit" id="btnNuevoItem" name="btnNuevoItem" class="btn btn-success btn-sm" value="Nuevo Item" onclick="nuevo_item()" disabled />

        </div>

        <div id="zona_ingreso_repuesto">

            <div class="g-repuestos-busqueda">

                    <span>

                        Búsqueda: &nbsp; &nbsp;

                        <input type="radio"  name="buscarpor" value="proveedor" checked>Proveedor&nbsp; &nbsp;

                        <input type="radio"  name="buscarpor" value="oem" style="visibility: hidden">

                    </span>

                    <div id="ing-cod-rep">

                        <input type="text" class="form-control form-control-sm" id="txtBuscar" placeholder="Ingrese Código de Repuesto" onkeyup="enter_press(event)">

                    </div>

                    <div>

                        <button id="btnBuscarRepuesto" onclick="buscarRepuesto()" name="btnBuscarRepuesto" class="btn btn-warning btn-sm">Buscar</button>

                    </div>

                    <div class="enlinea">

                        <button id="clonar" onclick="clonar()" class="btn btn-info btn-sm">CLONAR</button>&nbsp;<label for="codigo_interno">Cód. Interno:</label>

                        <input type="text" maxlength="10" value="0" id="codigo_interno" style="width:80px">

                    </div>



            </div>

            <div class="g-repuestos-familia">

                <span class="enlinea">

                    <label for="familia">Familia:</label>

                    <select name="cboFamilia" class="form-control form-control-sm" id="familia" onchange="dameUtilidad(this.value)" style="width:70%">

                        <option value="">Sin Familias</option>

                    </select>

                    <button class="btn btn-success btn-sm" onclick="agregar_familia()">+</button>

                </span>

                <span class="enlinea">

                    <label for="MarcaRepuesto">Marca:</label>

                    <select name="cboMarcaRepuesto" class="form-control form-control-sm" id="MarcaRepuesto" onchange="pasar_a_FAB();">

                        <option value="">Elija Marca de Repuesto</option>

                    </select>

                    <button class="btn btn-success btn-sm" onclick="agregar_marca_repuesto()">+</button>

                </span>

                <span class="enlinea">

                    <label for="Pais">Origen:</label>

                    <select name="cboPais" class="form-control form-control-sm" id="Pais">

                        <option value="">Elija País de Origen</option>

                    </select>

                    <button class="btn btn-success btn-sm" onclick="agregar_pais()" style="display:inline-block">+</button>

                </span>

            </div>

            <div class="g-repuestos-descripcion">

                <span>

                    <label for="descripcion">Descripción:</label>

                    <input type="text" name="descripcion" value="{{old('descripcion')}}" id="descripcion" class="form-control form-control-sm" maxlength="200" placeholder="200 caratéres máximo">

                </span>

                <span>

                    <label for="observaciones">Observaciones:</label>

                    <textarea name="observaciones" value="" wrap="hard" cols="11" rows="2" id="observaciones" class="form-control form-control-sm" maxlength="400" placeholder="400 caratéres máximo"></textarea>

                </span>

                <span id="span_medidas">

                    <label for="medidas">Medidas:</label>

                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>

                </span>

                <span>

                    <label for="cod_repuesto_proveedor">Cód. Repuesto: </label>

                    <textarea name="cod_repuesto_proveedor" maxlength="2000" wrap="hard" value="" id="cod_repuesto_proveedor" cols="11" rows="2" placeholder="Es el código interno del proveedor" class="form-control form-control-sm"></textarea>

                </span>

            </div>

            <div class="g-repuestos-stock">

                <span>

                    <label for="stock_minimo">Stock Mínimo:</label>

                    <input type="text" name="stock_minimo" value="3" id="stock_minimo" class="form-control form-control-sm">

                </span>

                <span>

                    <label for="stock_maximo">Stock Máximo:</label>

                    <input type="text" name="stock_maximo" value="0" id="stock_maximo" class="form-control form-control-sm">

                </span>

                <span>

                    <label for="codigo_barras">Código de Barras:</label>

                    <input type="text" name="codigo_barras" value="0" id="codigo_barras" class="form-control form-control-sm">

                </span>

            </div>

        </div>



    </div> {{-- FIN g-repuestos --}}



        <div class="g-items" id="zona_ingreso_items_factura">

            <div id="titulo-item-factura">

                <p><b>INGRESO DE ITEMS DE LA FACTURA</b></p>

            </div>

            <div id="btnGuardarItem-div">

                <input type="submit" id="btnGuardarItem" name="btnGuardarItem" class="btn btn-success btn-sm" value="Guardar Item" onclick="verificar_cod_prov()"/>

            </div>

            <span>

                <label for="cantidad">Cantidad:</label>

                <input type="text" name="cantidad" value="0"  id="cantidad" class="form-control form-control-sm" onfocusout="calculasubtotalitem()"  style="width:80%">

            </span>

            <span>

                <label for="pu">Precio Unitario:</label>

                <input type="text" name="pu" value="0.00"  id="pu" class="form-control form-control-sm" onfocusout="calculasubtotalitem()" style="width:80%">

            </span>

            <span>

                <label for="subtotalitem">Subtotal:</label>

                <input type="text" name="subtotalitem" value="0.00"  id="subtotalitem" class="form-control form-control-sm" style="width:80%" readonly>

            </span>

        <!--

            <span>

                <label for="flete">Flete:</label><br>

                <input type="text" name="flete" value="0"  id="flete" class="form-control form-control-sm" style="width:80%">

            </span>

        -->

                <input type="hidden" value="0" id="flete">

            <span>

                <label for="preciosug">Precio Sugerido</label>

                <span>

                    <input type="text" name="preciosug" value="0.00"  id="preciosug" class="form-control form-control-sm" style="display:inline-block;width:50%;margin-right:4px">

                    <button class="btn btn-success btn-sm" onclick="calculapreciosug()" onfocusout="ir_a_boton_guardaritem()">Calcular</button>

                </span>

            </span>

            <span>

                <label for="locales">Local:</label><br>

                <span class="enlinea">

                    <select name="locales" class="form-control form-control-sm" id="locales" style="width:80%">

                        @foreach ($locales as $local)

                        <option value="{{$local->id}}">{{$local->local_nombre}} en {{$local->local_direccion}}</option>

                        @endforeach

                    </select>

                </span>

            </span>



        </div>{{-- FIN g-items --}}







<!-- zona_fotos INICIO-->

        <div class="g-fotos" id="zona_fotos">

            <div class="fotos_msje">

                <p id="fotos_msje"><b>FOTOS:</b></p>

            </div>

            <div id="fotos_subir">

                <label>Subir Foto (jpg,jpeg,png):</label>

                <input type="file" name="archivo" id="archivo" class="form-control-file">

            </div>

            <div id="fotos_submit">

                <input type="submit" name="btnGuardarFoto" id="btnGuardarFoto" value="Agregar Foto" class="btn btn-primary btn-sm" onclick="guardarfoto()" style="margin-left:2px"/>

            </div>

            <div  id="fotos_rep"></div>

        </div><!-- zona_fotos FIN -->







<!-- zona_similares APLICACIONES INICIO-->

        <div class="g-aplicaciones" id="zona_similares" >

            <div class="aplicaciones_msje">

                <p id="aplicaciones_msje"><b>APLICACIONES:</b></p>

            </div>

            <div style="text-align:right">

                <button id="ampliar" class="btn btn-sm btn-success" onclick="ampliar()">>></button>

            </div>



            <div style="padding-left:2px;padding-right:2px">

                <label for="MarcaSim">Marca:</label>

                <select name="cboMarcaSim" class="form-control form-control-sm" id="MarcaSim" onchange="cargarModelosSimilares()" size="10">

                    <option value="">Elija una marca</option>

                    @foreach ($marcas as $marca)

                        <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>

                    @endforeach

                </select>

            </div>



            <div class="aplicaciones_modelos" style="padding-left:2px;padding-right:2px">

                <label for="modelo">Modelo:</label>

                <input type="text" class="form-control form-control-sm" id="filtrar_modelo" placeholder="filtrar modelo" style="width:40%">

                <select name="cboModeloSim" id="ModeloSim" class="form-control form-control-sm" onchange="ubicarse_en_anios()" size="10">

                    <option value="">Sin modelos</option>

                </select>

            </div>



            <div  class="aplicaciones_nombre_modelo" >

                <p id="nombre_modelo" class="letra-chica"><b>Modelo:</b></p>

            </div>



            <div  class="aplicaciones_anios_vehiculo">

                <label for="anios_vehiculo">Años:</label><br>

                <span class="enlinea">

                    <input type="text" name="anios_vehiculo_sim" value="" id="anios_vehiculo_sim" class="form-control form-control-sm" style="width:150px;margin-right:10px">

                    <input type="submit" name="btnGuardarSimilar" id="btnGuardarSimilar" value="Agregar Aplicación" class="btn btn-primary btn-sm form-control-sm" onclick="guardarsimilar()"/>

                </span>

            </div>



            <div  id="similares_rep" >

                <!--zona para aplicaciones -->

            </div>

        </div>

     <!-- zona_similares APLICACIONES FIN-->



    <!-- zona_OEMs INICIO-->

        <div class="g-oems" id="zona_OEMs">

            <div class="oems_msje">

                <p id="oems_msje"><b>OEMs:</b></p>

            </div>

            <div>

                <label for="codigos_OEM">Códigos:</label>

                <input type="text" name="codigos_OEM" id="codigos_OEM" class="form-control form-control-sm" onkeyup="enter_text_oem(event)">

            </div>

            <div>

                <input type="submit" name="btnGuardarOEM" id="btnGuardarOEM" value="Agregar OEM" class="btn btn-primary btn-sm form-control-sm" onclick="guardarOEM()" style="margin-left:10px;margin-top:20px"/>

            </div>

            <div  id="oems_rep" >

            </div>

        </div>

 <!-- zona_OEMs FIN-->



<!-- zona_FABs INICIO-->

        <div class="g-fabricantes" id="zona_FABs">

            <div class="fabricantes_msje">

                <p id="fabricantes_msje"><b>FABRICANTES:</b></p>

            </div>

            <div class="fabricantes_elegir">

                <label for="fabricante">Elegir:</label>

                <select name="cboFabricante" id="cboFabricante" class="form-control form-control-sm">

                    <option value="">Sin Fabricantes</option>

                </select>

            </div>

            <div class="fabricantes_codigo" style="margin-left:10px">

                <label for="codigo_FAB">Código:</label>

                <input type="text" name="codigo_FAB" id="codigo_FAB" class="form-control form-control-sm" onkeyup="enter_text_FAB(event)" style="width:80%">

            </div>

            <div class="btnGuardarFAB">

                <input type="submit" name="btnGuardarFAB" id="btnGuardarFAB" value="Agregar FAB" class="btn btn-primary btn-sm form-control-sm" onclick="guardarFAB()" style="margin-top: 20px"/>

            </div>

            <div  id="fabs_rep" >

            </div>

        </div>

<!-- zona_FABs FIN-->





</div> <!-- FIN de clase contenedor css grid -->

    @endsection



    @section('contenido_ver_datos')



<!-- VENTANA MODAL BUSCAR REPUESTO"-->

<div role="dialog" tabindex="-1" class="modal fade" id="busca-repuesto-modal">

  <div class="modal-dialog modal-lg" role="document" >

    <div class="modal-content">

        <div class="modal-header" style="height: 35px"> <!-- CABECERA -->

          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>

          <p class="text-center modal-title" id="mod_titulo_header_buscar">ELEGIR REPUESTO</p>

         </div> <!-- FIN CABECERA -->

      <div class="modal-body" style="height: 530px"> <!-- CONTENIDO -->

       <div id="mostrar_repuestos"> <!-- factuprodu_buscado.blade.php -->

         <p>VISTA CONTENIDO MODAL</p>



       </div>

     </div> <!-- FIN DE modal-body -->

     <div class="modal-footer" style="height: 40px">

        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

      </div>

    </div> <!-- modal-content -->

  </div> <!-- modal-dialog -->

</div> <!-- FIN VENTANA MODAL"-->





<!-- VENTANA MODAL AGREGAR FAMILIA"-->

<div role="dialog" tabindex="-1" class="modal fade" id="agregar-familia-modal">

    <div class="modal-dialog" role="document" >

      <div class="modal-content">

           <div class="modal-header">

            <h5 class="modal-title">Agregar Familia</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span>

            </button>

          </div>



        <div class="modal-body"> <!-- CONTENIDO -->



          <div >

              <input type="hidden" name="donde" value="factuprodu">

              <div class="row">

                <div class="col-4">

                  <label for="nombrefamilia">Nombre:</label>

                    <input type="text" name="nombrefamilia" value=""  id="nombre_fam" class="form-control form-control-sm">

                </div>

                <div class="col-3">

                  <label for="porcentaje">Porcentaje:</label>

                    <input maxlength="2" type="text" name="porcentaje" value="" id="porcentaje_fam" class="form-control form-control-sm">

                </div>

                <div class="col-2">

                  <label for="prefijo">Prefijo:</label>

                    <input maxlength="4" type="text" value="" name="prefijo" id="prefijo_fam" class="form-control form-control-sm">

                </div>

                <div class="col-3">

                  <input type="submit" onclick="guardar_familia()" name="btnGuardarFamilia" id="button" value="Agregar" class="btn btn-primary btn-sm" style="margin-top:20px"/>

                </div>

              </div>

          </div>



       </div> <!-- FIN DE modal-body CONTENIDO -->

       <div class="modal-footer">

          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

        </div>

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->

  </div> <!-- FIN VENTANA MODAL"-->



<!-- VENTANA MODAL AGREGAR MARCA REPUESTO"-->

<div role="dialog" tabindex="-1" class="modal fade" id="agregar-marca-repuesto-modal">

    <div class="modal-dialog" role="document" >

      <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">Agregar Marca Repuesto</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span>

            </button>

          </div>

        <div class="modal-body"> <!-- CONTENIDO -->



          <div class="row">

              <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                <label for="marcarepuesto">Marca del Repuesto:</label>

                  <input type="text" name="marcarepuesto" id="marcarepuesto" size="20" maxlength="20" value="" class="form-control" style="width:100%">

              </div>



              <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                <input type="submit" onclick="guardar_marca_repuesto()" name="btnGuardarMarcaRepuesto" id="button" value="Guardar" class="btn btn-primary btn-md" style="margin-top:20px"/>

              </div>

         </div>

       </div> <!-- FIN DE modal-body CONTENIDO -->

       <div class="modal-footer">

          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

        </div>

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->

  </div> <!-- FIN VENTANA MODAL"-->





  <!-- VENTANA MODAL AGREGAR PAIS"-->

<div role="dialog" tabindex="-1" class="modal fade" id="agregar-pais-modal">

    <div class="modal-dialog" role="document" >

      <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">Agregar País de Origen</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span>

            </button>

          </div>

        <div class="modal-body"> <!-- CONTENIDO -->



          <div class="row">

            <div class="col-6 col-sm-6 col-md-6 col-lg-6">

              <label for="pais">Nombre del País de Origen:</label>

                <input type="text" name="pais" id="pais" size="20" maxlength="20" value="{{old('pais')}}" class="form-control" style="width:100%">

            </div>

            <div class="col-6 col-sm-6 col-md-6 col-lg-6">

              <input type="submit" name="btnGuardarPais" onclick="guardar_pais()" id="button" value="Guardar" class="btn btn-primary btn-md" style="margin-top:20px"/>

            </div>

          </div>



       </div> <!-- FIN DE modal-body CONTENIDO -->

       <div class="modal-footer">

          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

        </div>

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->

  </div> <!-- FIN VENTANA MODAL"-->



  <!-- VENTANA MODAL AGREGAR MEDIDA FAMILIA"-->

    <div class="modal fade" tabindex="-1" role="dialog" id="agregar-medida-familia-modal">

      <div class="modal-dialog" role="document">

        <div class="modal-content">

          <div class="modal-header">

            <h5 class="modal-title">Agregar medida</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span>

            </button>

          </div>

          <div class="modal-body">

            <div class="row">

              <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                <label for="medida">Nombre de la medida:</label>

                  <input type="text" name="medida" id="medidas" size="20" maxlength="20" value="{{old('medidas')}}" class="form-control" style="width:100%">

              </div>

              <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                <input type="submit" name="btnGuardarMedida" onclick="guardar_medida()" id="button" value="Guardar" class="btn btn-primary btn-md" style="margin-top:20px"/>

              </div>

            </div>

          </div>

          <div class="modal-footer">

            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

          </div>

        </div>

      </div>

    </div>



    <!-- Modal -->

<div class="modal fade" id="verMedidasModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">

    <div class="modal-content">

      <div class="modal-header">

        <h5 class="modal-title" id="exampleModalLabel">Medidas</h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">

          <span aria-hidden="true">&times;</span>

        </button>

      </div>

      <div class="modal-body" id="modal_body_medidas">

        ...

      </div>

      <div class="modal-footer">

        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">cerrar</button>

      </div>

    </div>

  </div>

</div>

  

  @endsection

