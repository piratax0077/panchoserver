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



            document.getElementById("zona_similares").style.gridColumn="1/-1"; //este
            document.getElementById("zona_OEMs").style.visibility="hidden";
            document.getElementById("zona_FABs").style.visibility="hidden";

            $("#ampliar").html("<<");

        }else{

            document.getElementById("zona_similares").style.gridColumn=""; //este
            document.getElementById("zona_OEMs").style.visibility="visible";
            document.getElementById("zona_FABs").style.visibility="visible";

            $("#ampliar").html(">>");
        }

        ampliar_aplicacionez=!ampliar_aplicacionez;
    }

function guardar_rec_alt(){
  var idrep=document.getElementById("id_repuesto").value;
  var rectificador = document.getElementById("rectificador").value;
  var alternador = document.getElementById("alternador").value;

  if(idrep.trim() == 0 || idrep == ''){
    Vue.swal({
      icon:'info',
      text:'Primero debe guardar Item ...',
      position:'top-end',
      timer: 3000,
      toast: true,
      showConfirmButton: false
    });
    return false;
  }

  alternador=alternador.replace(/-/g,"");
  alternador=alternador.toUpperCase();

  rectificador = rectificador.replace(/-/g,"");
  rectificador = rectificador.toUpperCase();

  if(rectificador.trim() == 0 || idrep == ''){
    rectificador = "---";
  }

  if(alternador.trim() == 0 || idrep == ''){
    alternador = "---";
  }

  var parametros = {idrep: idrep, rec: rectificador, alt: alternador};
  var url = '/repuesto/guardaRecAlt';

  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $.ajax({
    type:'post',
    data: parametros,
    url: url,
    success: function(html){
      
      document.getElementById("rectificador").value = "";
      document.getElementById("alternador").value = "";
      // $('#rectificador').html("");
      // $('#alternador').html("");
      $('#result').empty();
      $('#result').append(html);
    },
    error: function(error){
      Vue.swal({
        icon:'error',
        text: error.responseText
      });
    }
  })
}

function guardar_motor(){
  var idrep=document.getElementById("id_repuesto").value;
  let motor = document.getElementById("motorpartida").value;
  if(idrep.trim() == 0 || idrep == ''){
    Vue.swal({
      icon:'info',
      text:'Primero debe guardar Item ...',
      position:'top-end',
      timer: 3000,
      toast: true,
      showConfirmButton: false
    });
    return false;
  }

  if(motor.trim() == 0  || motor == ''){
    Vue.swal({
      icon:'info',
      text:'Debe ingresar motor de partida ...',
      position:'top-end',
      timer: 3000,
      toast: true,
      showConfirmButton: false
    });
    return false;
  }

  let data = {
    idrep: idrep,
    motor: motor
  }

  let url = '/repuesto/guardaMotor';
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $.ajax({
    type:'post',
    data: data,
    url: url,
    success: function(html){
      if(html == 'existe'){
        return Vue.swal({
          icon:'info',
          text:'Motor de partida ya existe ...',
          position:'top-end',
          timer: 3000,
          toast: true,
          showConfirmButton: false
        })
      }
      console.log(html);
      document.getElementById("motorpartida").value = "";
      $('#result').empty();
      $('#result').append(html);
    },
    error: function(error){
      Vue.swal({
        icon:'error',
        text: error.responseText
      });
    }
  })
}


    function agregar_ubicacion(){
      let piso = $('#piso_ubicacion').val();
      let bandeja = $('#bandeja_ubicacion').val();
      let estanteria = $('#estanteria_ubicacion').val();
      let pasillo = $('#pasillo_ubicacion').val();

      document.getElementById('ubicacion_repuesto').value = piso + ' - '+bandeja+' - '+estanteria+' - ';
      console.log($('#ubicacion_repuesto').val());
    }

    // FUNCIONES PARA FORMATEAR NÚMEROS CON FORMATO CHILENO (1.234.567,89)
    function formatNumberChile(value) {
      if (value === '' || value === null || value === undefined) return '';
      let str = String(value);

      // Separar parte entera y decimal por la coma
      let hasComma = str.indexOf(',') !== -1;
      let parts    = str.split(',');

      // Limpiar parte entera: solo dígitos
      let intPart = parts[0].replace(/\D/g, '');
      if (intPart.length > 1) intPart = intPart.replace(/^0+/, '') || '0';

      // Agregar separadores de miles
      intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      if (hasComma) {
        // Parte decimal: máximo 2 dígitos, solo números
        let decPart = (parts[1] || '').replace(/\D/g, '').substring(0, 2);
        return intPart + ',' + decPart;
      }
      return intPart;
    }

    // Limpiar número formateado chileno para obtener valor numérico
    function cleanNumberChile(value) {
      if (value === '' || value === null || value === undefined) return 0;
      let str = String(value).trim();
      if (str === '') return 0;
      // Quitar puntos de miles y cambiar coma decimal por punto
      str = str.replace(/\./g, '').replace(',', '.');
      return parseFloat(str) || 0;
    }

    // Aplicar máscara a input mientras se tipea
    function formatEnteroChile(value) {
      return Math.round(Number(value) || 0).toLocaleString("es-CL");
    }

    function applyMaskChile(input) {
      // Bloquear teclas no permitidas
      input.addEventListener('keydown', function(e) {
        const navKeys = [8, 9, 13, 27, 46, 37, 38, 39, 40, 35, 36];
        if (navKeys.indexOf(e.keyCode) !== -1) return;
        if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].indexOf(e.keyCode) !== -1) return;
        if (!/^[\d,]$/.test(e.key)) { e.preventDefault(); return; }
        // Solo una coma
        if (e.key === ',' && this.value.indexOf(',') !== -1) e.preventDefault();
      });

      // Formatear mientras se escribe (solo miles, sin forzar decimales)
      input.addEventListener('input', function() {
        let pos    = this.selectionStart;
        let oldLen = this.value.length;
        let formatted = formatNumberChile(this.value);
        if (formatted !== this.value) {
          this.value = formatted;
          let diff   = formatted.length - oldLen;
          let newPos = Math.max(0, pos + diff);
          this.setSelectionRange(newPos, newPos);
        }
      });

      // Al salir del campo: mostrar entero con separadores de miles
      input.addEventListener('blur', function() {
        if (this.value.trim() === '') return;
        let num = cleanNumberChile(this.value);
        if (!isNaN(num) && num > 0) {
          this.value = formatEnteroChile(num);
        }
      });
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
                if(r.repuesto[0].id_familia == '206'){
                  console.log('es un regulador de voltaje');
                  $('#rec_alt_rep').addClass('d-block');
                  $('#rec_alt_rep').removeClass('d-none');
                  $('#bendix').addClass('d-none');
                  $('#bendix').removeClass('d-block');
                  dame_regulador_voltaje(r.repuesto[0].id);
                }else if(r.repuesto[0].id_familia == '166'){
                  console.log('es un bendix asfa');
                  $('#bendix').removeClass('d-none');
                  $('#bendix').addClass('d-block');
                  
                  $('#rec_alt_rep').addClass('d-none');
                  $('#rec_alt_rep').removeClass('d-block');
                }else{
                  console.log('no es regulador de voltaje');
                  $('#rec_alt_rep').addClass('d-none');
                  $('#rec_alt_rep').removeClass('d-block');
                  $('#bendix').addClass('d-none');
                  $('#bendix').removeClass('d-block');
                }
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

    function dame_regulador_voltaje(idrep){
      var url = '/repuesto/damerv/'+idrep;
      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          $('#result').empty();
          $('#result').append(resp);
        },  
        error: function(error){
          console.log(error.responseText);
        }
      })
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
      console.log(id);
      var url = '{{url("medidasJSON")}}'+'/'+id;
      $.ajax({
        type:'get',
        url: url,
        beforeSend: function(){
          console.log('Enviando ...');
        },
        success: function(resp){
          
          var cont = 0;
          $('#span_medidas').empty();
           // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
           let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar </th>
              <th scope="col"> </th>
              <th scope="col"> </th>
            </tr>
          </thead>
          <tbody>`;
              resp.forEach(e => {
                cont++;
                html+= `
                <tr>
                  <th scope="row">`+e.descripcion+` </th> 
                  <th scope="row"> 
                    <input type="text" class="form-control dato" value="`+e.descripcion+`-" id="medida`+cont+`" disabled />  
                  </th> 
                  <th scope="row"> 
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" onclick="abrirCampoMedidas(`+cont+`)" id="chkbx`+cont+`"> 
                    </div>  
                  </th>
                  <th>
                    <button class="btn btn-danger btn-sm" onclick="eliminar_medida_familia(`+e.id+`)">X</button>
                    </th>
                    <th>
                      <button class="btn btn-info btn-sm" onclick="editar_medida_familia(`+e.id+`)">Editar</button>
                      </th>
                </tr>`;
              })
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas').append(html);
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          let lista_medidas = `
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);

        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }



    function abrirCampoMedidas(value){
      var check = document.getElementById('chkbx'+value);

      var medida = document.getElementById('medida'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }

    function abrirCampoMedidas_pd(value){
      var check = document.getElementById('chkbx_'+value);

      var medida = document.getElementById('medida_'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }

    function abrirCampoMedidas_pd_dos(value){
      var check = document.getElementById('chkbx_dos'+value);

      var medida = document.getElementById('medida_dos'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }


    function agregar_familia()



    {



      $("#agregar-familia-modal").modal("show");



    }







    function agregar_marca_repuesto()
    {
      $("#agregar-marca-repuesto-modal").modal("show");
    }


    function agregar_pais()
    {
      $("#agregar-pais-modal").modal("show");
    }

    function agregar_medida_familia(){
      $("#agregar-medida-familia-modal").modal("show");
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
        console.log(resp);
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
            console.log(errores.errors.marcarepuesto);
            var salida="";
            for(var indice in errores)
            {
              salida=salida+errores[indice];
            }
            // $('#mensajes').html("<p style='color:red'>"+salida+"</p>");
            Vue.swal({
                title: 'ERROR',
                text: errores.errors.marcarepuesto[0],
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
        console.log(resp);
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
          console.log(resp[1].descripcion);
          $('#medidas').val(resp[1].descripcion);
          if(resp[0] == 'OK'){
            Vue.swal.close();
            cargar_medidas();

            Vue.swal({
                text: 'Medida Agregada, ahora debe seleccionarla',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,


            });

            // cerrar la ventana modal
            $("#agregar-medida-familia-modal").modal("hide");

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

    function guardar_lista_medidas(){
      let lista = '';
      $('.dato:enabled').each(
        function() {

          let val = $(this).val();

          lista+=','+val;

        }

    );

    console.log(lista);
    $('#medidas').val(lista);
    }

    function guardar_lista_medidas_disco(){
      let lista = '';
      $('.dato_disco:enabled').each(
        function() {

          let val = $(this).val();

          lista+=val+',';

        }

    );

    console.log(lista);
    $('#medida1_pd').val('DISCO:'+lista);
    }

    function guardar_lista_medidas_prensa(){
      let lista = '';
      $('.dato_prensa:enabled').each(
        function() {

          let val = $(this).val();

          lista+=val+',';

        }

    );

    console.log(lista);
    $('#medida2_pd').val('PRENSA:'+lista);
    }







    function soloNumeros(e)
    {
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8))
    }







    function validarCabeceraFactura()
    {
        cantidad_repuestos_factura=0;
        var id_compras_cab = document.getElementById('id_cab_factura').value;
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
      if(id_compras_cab === ''){
        var url='{{url("factuprodu/verificafactura")}}'+'/'+idproveedor+"*"+f;
        $.ajax({
        type:'GET',
        beforeSend: function () {
        },
        url:url,
        success:function(resp){

          var r=resp.split("*");
          console.log(r[1]);
          $('#id_cab_factura').val(r[1]);
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
                var porcentajeFleteFactura = Number(r[6] || 0);
                if(Number.isFinite(porcentajeFleteFactura) && porcentajeFleteFactura > 0) {
                  document.getElementById("porcentaje_flete").value = porcentajeFleteFactura.toLocaleString("es-CL", {maximumFractionDigits:4});
                  Vue.swal({
                    text: 'Se recuperó el flete de la factura: '+porcentajeFleteFactura.toLocaleString("es-CL", {maximumFractionDigits:2})+'%',
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                  });
                }
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
      }else{
        guardarCabeceraFactura();
      }
     
      







    }







  function calculasubtotalitem()



  {



    var cantidad = cleanNumberChile(document.getElementById("cantidad").value);



    var pu = cleanNumberChile(document.getElementById("pu").value);



    if(cantidad>0 && pu>0){



        var subtotal = cantidad * pu;

        document.getElementById("subtotalitem").value = formatEnteroChile(subtotal);



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







  function calcularReferenciaFlete()
  {
    var total=cleanNumberChile(document.getElementById("ref_total_factura").value);
    var flete=cleanNumberChile(document.getElementById("ref_total_flete").value);
    var getnet=cleanNumberChile(document.getElementById("ref_getnet").value);
    var ppm=cleanNumberChile(document.getElementById("ref_ppm").value);
    var valido=Number.isFinite(total) && total>0 && Number.isFinite(flete) && flete>=0
        && Number.isFinite(getnet) && getnet>=0 && Number.isFinite(ppm) && ppm>=0;
    var porcentaje=valido ? flete/total*100 : null;
    var formato=new Intl.NumberFormat("es-CL", {maximumFractionDigits:2});
    document.getElementById("ref_porcentaje_flete").value=valido ? formato.format(porcentaje)+" %" : "";
    document.getElementById("ref_porcentaje_total").value=valido ? formato.format(porcentaje+getnet+ppm)+" %" : "";
  }

  function calculapreciosug(forzar=false)
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

    var cant = cleanNumberChile(document.getElementById("cantidad").value);
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
    var pu = cleanNumberChile(document.getElementById("pu").value);

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

    var pctFlete = cleanNumberChile(document.getElementById("porcentaje_flete").value);
    if(isNaN(pctFlete) || Number(pctFlete)<0) pctFlete=0;

    var iva=0.19;
    var utilidad=cleanNumberChile(document.getElementById("utilidad").value)/100;
    if(!Number.isFinite(utilidad) || utilidad<0 || utilidad>=1) {
        Vue.swal({
            text: 'El margen de utilidad debe ser mayor o igual a 0 y menor a 100%.',
            icon: 'warning',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
        });
        document.getElementById("utilidad").focus();
        return false;
    }

    // Margen sobre la venta neta, incluyendo el flete en el costo.
    // Conservar los decimales del flete hasta calcular el precio final.
    var flete=pu*(pctFlete/100);
    document.getElementById("flete").value=formatEnteroChile(flete);
    document.getElementById("flete").dataset.valorExacto=String(flete);
    var elvalor=(pu+flete)/(1-utilidad)*(1+iva);
    var ps=document.getElementById("preciosug");
    var precioActual=cleanNumberChile(ps.value);
    var precioAutomatico=Number(ps.dataset.precioAutomatico);
    if(forzar || precioActual==0 || precioActual===precioAutomatico) {
        ps.value=formatEnteroChile(elvalor);
        ps.dataset.precioAutomatico=String(Math.round(elvalor));
    }
    // Respetar un precio manual; el botón Calcular permite reemplazarlo.
    return true;
  }

  function guardarficha(){
    Vue.swal({
      icon:'info',
      text:'En construcción'
    });
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
        console.log(resp);
        $('#rec_alt_rep').empty();
        $('#bendix').empty();
        $('#result').empty();
        $('#span_medidas').empty();
        $('#fotos_ficha_tecnica').empty();
        if(resp == 'bendix'){
          $('#rec_alt_rep').addClass('d-none');
          $('#bendix').removeClass('d-none');
          $('#bendix').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" readonly></textarea>
          
          <div class='form-group'>
              <label>MOTOR DE PARTIDA</label>
              <input class='form-control' id="motorpartida" type='text' placeholder ='Motor de partida' />
          </div>
          
          
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_motor()" id="btn_motor" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
        }else if(resp == 'rv'){
          $('#rec_alt_rep').removeClass('d-none');
          $('#rec_alt_rep').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" readonly></textarea>
          <div>
          <div class='form-group'>
              <label>RECTIFICADOR </label>
              <input class='form-control' id="rectificador" type='text' placeholder ='Rectificador' />
          </div>
          <div class='form-group'>
              <label>ALTERNADOR </label>
              <input class='form-control' id="alternador" type='text' placeholder ='Alternador' />
          </div>
          <div>
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_rec_alt()" id="btn_guardar_rec_alt" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
        }else if(resp == 'aceite'){
          $('#fotos_ficha_tecnica').empty();
          $('#fotos_ficha_tecnica').append(`
          <label>Subir Ficha técnica (pdf):</label>
          <input type="file" name="ficha_tecnica" id="ficha_tecnica" class="form-control-file">
          <div id="ficha_tecnica_submit">
                <input type="submit" name="btnGuardarFicha" id="btnGuardarFicha" value="Agregar Ficha Técnica" class="btn btn-primary btn-sm" onclick="guardarficha()" style="margin-left:2px"/>
            </div>
          `);
          $('#span_medidas').empty();
          $('#span_medidas').append(`
          <label for="medidas">Medidas:</label>
          <div class="clearfix"></div>
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          <input type="hidden" id="familia_length" />
          `);
        }else if(resp[0] == 'pd'){
          let medidas_disco = resp[1];
          let medidas_prensa = resp[2];
          $('#span_medidas').empty();
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
          let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>
            <tr>
                  <th scope='row'>DISCO</th> 
                  <th scope='row'><input type="text" class="form-control dato" id="medida1_pd" value="DISCO-"  /></th> 
                  <th scope='row'> 
                    <button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal_pd' >Seleccionar medidas </button> <input type="hidden" id="medida_value" />
                  </th>
            </tr>
            <tr>
                  <th scope='row'>PRENSA</th> 
                  <th scope='row'><input type="text" class="form-control dato" id="medida2_pd" value="PRENSA-"  /></th> 
                  <th scope='row'> 
                    <button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal_pd_dos' >Seleccionar medidas </button> <input type="hidden" id="medida_value" />
                  </th>
            </tr>
            </tbody>`;
            
          $('#modal_body_medidas').append(html);
          $('#modal_body_medidas_pd').empty();
          let html_ = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>`;
          var cont_ = 0;
            medidas_disco.forEach(e => {
                cont_++;
                html_+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato_disco" id="medida_`+cont_+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value_" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx_`+cont_+`' onclick="abrirCampoMedidas_pd(`+cont_+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                </tr>`;
              });
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas_pd').append(html_);

          $('#modal_body_medidas_pd_dos').empty();
          let html_dos = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
            </tr>
          </thead>
          <tbody>`;
          var cont_dos = 0;
            medidas_prensa.forEach(e => {
                cont_dos++;
                html_dos+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato_prensa" id="medida_dos`+cont_dos+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value_" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx_dos`+cont_dos+`' onclick="abrirCampoMedidas_pd_dos(`+cont_dos+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                </tr>`;
              });
          html_dos+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas_pd_dos').append(html_dos);
        }else {
          
          if(resp.length == 0){
        
          $('#span_medidas').empty();
          $('#span_medidas').append(`
          <label for="medidas">Medidas:</label>
          <div class="clearfix"></div>
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          <input type="hidden" id="familia_length" />
          `);
        }else{
          $('#span_medidas').empty();
          $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
          let button_html = `
          <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>
          `;
          let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;
          // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto
          let textarea = `
          <label for="medidas" style='display: none;'>Medidas:</label>
                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>
          `;
          $('#span_medidas').append(btn_ver_medidas);
          $('#span_medidas').append(button_html);
          $('#span_medidas').append(textarea);
          $('#modal_body_medidas').empty();
          let html = `
          <div class='clearfix'> </div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col">Descripción</th>
              <th scope="col">Medidas</th>
              <th scope="col">Seleccionar</th>
              <th scope="col"></th>
              <th scope="col"> </th>
            </tr>
          </thead>
          <tbody>`;
          var cont = 0;
              resp.forEach(e => {
                cont++;
                html+= `
                <tr>
                  <th scope='row'>`+e.descripcion+ `</th> 
                  <th scope='row'> <input type="text" class="form-control dato" id="medida`+cont+`" value="`+e.descripcion+`-" disabled /> <input type="hidden" id="medida_value" /></th> 
                  <th scope='row'> 
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' id='chkbx`+cont+`' onclick="abrirCampoMedidas(`+cont+`)" value='`+e.descripcion+`'> 
                    </div>  
                  </th>
                  <th>
                    <button class="btn btn-danger btn-sm" onclick="eliminar_medida_familia(`+e.id+`)">X</button>
                  </th>
                  <th>
                    <button class="btn btn-info btn-sm" onclick="editar_medida_familia(`+e.id+`)">Editar</button>
                  </th>
                </tr>`;
              });
          html+= `</tbody>
          </table>
          `;
          $('#modal_body_medidas').append(html);
          }
      }
        }
        
    });

    // La utilidad se conserva para todos los productos de la factura.

  }



function eliminar_medida_familia(idmedida){
  let familia = $('#familia').val();
  let params = {
    idmedida: idmedida,
    idfamilia: familia
  }
  let url = '{{url("factuprodu")}}'+'/'+'eliminar_medida_familia'; //petición
  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
  });

  $.ajax({
    type:'post',
    data: params,
    url: url,
    success: function(resp){
      console.log(resp);
      dameUtilidad(familia);
      $('#verMedidasModal').modal('hide');
      //espere('EN CONSTRUCCION');
    },
    error: function(error){
      console.log(error.responseText);
    }
  });
  
}

function editar_medida_familia(idmedida){
    $('#modalEditarMedida').modal('show');
    // no mostrar el modal verMedidasModal
    $('#verMedidasModal').modal('hide');
    let url = "{{url('medidas')}}"+'/'+idmedida+'/editar';
    $.ajax({
      type: 'get',
      url: url,
      success: function(resp){
        console.log(resp);
        $('#medida_nombre').val(resp.descripcion);
        $('#medida_id').val(resp.id);
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
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
      console.log(cod);
        let rpta=false;
        let idFactura=document.getElementById("id_factura_cab").value;
        let idProveedor=document.getElementById("proveedor").value;
        console.log(idProveedor);
        let url='{{url("factuprodu")}}'+'/'+cod+'/proveedor/'+idProveedor+'/factura/'+idFactura; //petición
        console.log(url);
        $.ajax({
            type:'GET',
            url:url,
            async: false,
            cache: false,
            success:function(resp){
              console.log(resp);
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
      // Leer y limpiar valores con formato chileno
      var cantidad = cleanNumberChile(document.getElementById("cantidad").value);
      if(cantidad==0 || isNaN(cantidad) || cantidad < 1)
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

      /* 
      
        El stock máximo se debe respetar, no le puedo colocar una cantidad mayor al stock máximo
      */
     if(cantidad > cleanNumberChile(stockmax)){
      Vue.swal({
            text: 'La cantidad no puede ser mayor que el stock máximo',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false
     }
      var pu = cleanNumberChile(document.getElementById("pu").value);

      if(pu==0 || isNaN(pu) || pu < 1)
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

      var subtotalitem = cleanNumberChile(document.getElementById("subtotalitem").value);
      if(calculapreciosug()===false) return false;
      var flete = Number(document.getElementById("flete").dataset.valorExacto);
      if(flete<0) flete=0;
      var preciosug = cleanNumberChile(document.getElementById("preciosug").value);

      if(preciosug==0 || isNaN(preciosug) || preciosug < 1)
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
      if(idLocal === null){
        idLocal = 1;
      }
      var ubi = document.getElementById("ubicacion_repuesto").value;
      if(ubi === '' || ubi.trim().length==0){
        ubi = "SIN INFORMACION";
      }
      var nuevo="SI";

      if(document.getElementById("elegir").value=="SI") nuevo="NO";
      var id_repuesto=document.getElementById("id_repuesto").value;
      var id_repuesto_clonado=0;
      if(clonando){
        id_repuesto_clonado=document.getElementById("id_repuesto_clonado").value;
      }
/*
     console.log(pu, preciosug);

      if(pu > preciosug){
        Vue.swal({
            text: 'El precio de venta no puede ser menor al precio de compra',
            position: 'top-end',
            
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
      }
*/
      
      // LIMPIAR VALORES DE STOCK (solo estos todavía no se han limpiado)
      var stockmin_cleaned = cleanNumberChile(stockmin);
      var stockmax_cleaned = cleanNumberChile(stockmax);

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
        stockmin:stockmin_cleaned,
        stockmax:stockmax_cleaned,
        codbar:codbar,
        idFactura:idFactura,
        cantidad:cantidad,
        pu:pu,
        subtotalitem:subtotalitem,
        flete:flete,
        preciosug:preciosug,
        idLocal:idLocal,
        ubicacion: ubi
      };

      // console.log(parametros);

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
        console.log(resp);
        
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
          // si existe document.getElementById("btn_guardar_rec_alt") remover el atributo disabled
          if(document.getElementById('btn_guardar_rec_alt')!=null){
            document.getElementById('btn_guardar_rec_alt').removeAttribute('disabled');
          }
          // si existe document.getElementById("btn_motor") remover el atributo disabled
          if(document.getElementById('btn_motor')!=null){
            document.getElementById('btn_motor').removeAttribute('disabled');
          }
          
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
      if(archivo.size > 1020000)
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





    function borrarrv(idrv){
      var idrep=document.getElementById("id_repuesto").value;
      var url='{{url("factuprodu")}}'+'/'+idrv+'/borrarrv/'+idrep;
      
      $.ajax({
        type:'get',
        url:url,
        beforeSend: function(){
          
          espere("Borrando RV...");
          
        },
        success: function(resp){
          Vue.swal.close();
          Vue.swal({
                text: 'RV borrado',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          $('#result').empty();
          $('#result').append(resp);
        },
        error: function(error){
          console.log(error.responseText);
        }
      })
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

    function mostrarModalPrincipal(){
      $("#verMedidasModal").modal("show");
      $('#modalEditarMedida').modal('hide');
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
      document.getElementById("stock_minimo").value="0";
      document.getElementById("stock_maximo").value="0";
      document.getElementById("codigo_barras").value="0";
      document.getElementById("cantidad").value="0";
      document.getElementById("pu").value="0";
      document.getElementById("subtotalitem").value="0";
      // Conservar los porcentajes de utilidad y flete para el siguiente ítem.
      document.getElementById("flete").value="0";
      delete document.getElementById("flete").dataset.valorExacto;
      document.getElementById("preciosug").value="0";
      document.getElementById("preal").value="";
      document.getElementById("poferta").value="";

      document.getElementById("id_repuesto").value="";
      document.getElementById("elegir").value="NO";

      //habilitar reIngreso de Items

      document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.removeAttribute("disabled"));
      document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.removeAttribute("disabled"));

      //Deshabilitar el ingreso de fotos, similares y otros OEMs
      document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));
      document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));

      //Limpiamos en caso de que sea una caja reguladora
      

      //Limpiar las fotos, similares, OEMs y Cajas reguladoras

      $("#fotos_rep").html("");
      $("#similares_rep").html("");
      $("#oems_rep").html("");
      $("#fabs_rep").html("");
      $('#rec_alt_rep').html("");
      $('#bendix').html("");
      $('#result').html("");


      document.getElementById("archivo").value="";
      //document.getElementById("anios_vehiculo_sim").value="";
      document.getElementById("codigos_OEM").value="";
      document.getElementById("txtBuscar").focus();


    }


    function elegir_repuesto(repuesto)
    {
      console.log(repuesto);
      // if(repuesto.oferta == 1){
      //   $('#preal').removeClass('d-none');
      //   $('#preal').addClass('d-block');
      // }else{
      //   $('#preal').removeClass('d-block');
      //   $('#preal').addClass('d-none');
      // }
      $('#busca-repuesto-modal').modal('hide');
      document.getElementById("id_repuesto").value=repuesto.id;
      // Establecer el valor de familia sin disparar el evento onchange
      let familiaSelect = document.getElementById("familia");
      let oldOnChange = familiaSelect.onchange;
      familiaSelect.onchange = null;
      familiaSelect.value = repuesto.id_familia;
      familiaSelect.onchange = oldOnChange;
      if(repuesto.id_familia == '206'){
        console.log('regulador de voltaje');
        $('#rec_alt_rep').empty();
        $('#result').empty();
        //MOSTRAMOS FORMULARIO
        $('#rec_alt_rep').removeClass('d-none');
          $('#rec_alt_rep').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" readonly></textarea>
          
          <div class='form-group'>
              <label>RECTIFICADOR </label>
              <input class='form-control' id="rectificador" type='text' placeholder ='Rectificador' />
          </div>
          <div class='form-group'>
              <label>ALTERNADOR </label>
              <input class='form-control' id="alternador" type='text' placeholder ='Alternador' />
          </div>
          
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_rec_alt()" id="btn_guardar_rec_alt" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
      }else if(repuesto.id_familia == '166'){
        console.log('es un bendix');
        $('#bendix').empty();
        $('#result').empty();
        //MOSTRAMOS FORMULARIO
        $('#bendix').removeClass('d-none');
          $('#result').addClass('d-block');
          $('#bendix').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" readonly></textarea>
          <div>
          <div class='form-group'>
              <label>MOTOR DE PARTIDA</label>
              <input class='form-control' id="motorpartida" type='text' placeholder ='Motor de partida' />
          </div>
          
          <div>
            <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_motor()" id="btn_motor" value="Guardar datos" disabled="true" /> 
          </div>
          </div>
          `);
      }else{
        console.log('no regulador de voltaje ni bendix');
        $('#rec_alt_rep').empty();
        $('#bendix').empty();
        $('#result').empty();
        //OCULTAMOS LOS FORMULARIOS
        $('#rec_alt_rep').removeClass('d-block');
        $('#rec_alt_rep').addClass('d-none');
        $('#bendix').removeClass('d-block');
        $('#bendix').addClass('d-none');
      }
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
        document.getElementById("stock_minimo").value=repuesto.stock_minimo;
        document.getElementById("stock_maximo").value=repuesto.stock_maximo;
        document.getElementById("elegir").value="NO";
        document.getElementById("preal").value = repuesto.precio_real;
        document.getElementById("poferta").value = Number(repuesto.precio_venta || 0).toLocaleString("es-CL", {maximumFractionDigits:0});
      }else{
        document.getElementById("id_repuesto_clonado").value=0;
        document.getElementById("codigo_interno").value=repuesto.codigo_interno;
        document.getElementById("cod_repuesto_proveedor").value=repuesto.cod_repuesto_proveedor;
        // Asignar precio de compra con formato chileno (miles con punto, decimales con coma)
        (function(){
          var puNum = parseFloat(repuesto.precio_compra) || 0;
          // Formatear sin decimales y con separador de miles
          document.getElementById("pu").value = formatEnteroChile(puNum);
          // Disparar evento input para que cualquier máscara aplicada reevalúe estado del campo
          var ev = new Event('input', { bubbles: true });
          document.getElementById("pu").dispatchEvent(ev);
        })();
        document.getElementById("elegir").value="SI";
        document.getElementById("preal").value = repuesto.precio_real;
        document.getElementById("poferta").value = Number(repuesto.precio_venta || 0).toLocaleString("es-CL", {maximumFractionDigits:0});
      }




      document.getElementById("stock_minimo").value=repuesto.stock_minimo;
      document.getElementById("stock_maximo").value=repuesto.stock_maximo;
      document.getElementById("codigo_barras").value=repuesto.codigo_barras;
      document.getElementById("cantidad").value=0;
      document.getElementById("flete").value=0;
      document.getElementById("preciosug").value=0;
      document.getElementById("ubicacion_repuesto").value = repuesto.ubicacion !== undefined ? repuesto.ubicacion : 'SIN UBICACIÓN';

      //Cargar fotos




      //Cargar similares


      //Cargar oems


      //cargar fabricantes



      // 26 nov 2019, le pregunté a pancho y me dijo que no era necesario cargarlos luego de la primera vez
      //console.log(repuesto);
      //El focus no funciona porque al cerrar el modal, bootstrap autoenfoca
      //en el elemento que abrió el modal...
      //document.getElementById("id_repuesto").value=id_repuesto
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

      console.log(url_buscar);


      $.ajax({
       type:'GET',
      url:url_buscar,
      success:function(resp){
        console.log(resp);
        
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
        //Habilitar boton de editar la cabecera
        document.getElementById('btnEditarCabeceraFactura').removeAttribute('disabled');
        //desactivar cabecera factura
        document.getElementById("proveedor").disabled=true;
        document.getElementById("numerofactura").disabled=true;
        document.getElementById("fechafactura").disabled=true;
        document.getElementById("credito").disabled=true;
        document.getElementById("vencefactura").disabled=true;
        document.getElementById("btnGuardarCabeceraFactura").disabled=true;

        var campoUtilidad=document.getElementById("utilidad");
        campoUtilidad.disabled=false;
        campoUtilidad.focus({preventScroll:true});
        campoUtilidad.select();
        document.getElementById("costos_importacion_factura").scrollIntoView({
            behavior:"smooth",
            block:"center"
        });

    }







    function guardarCabeceraFactura()
    {
      var id_compras_cab = document.getElementById('id_cab_factura').value;
      var idProveedor=document.getElementById("proveedor").value;
      var numerofactura = document.getElementById("numerofactura").value;
      var fechafactura = document.getElementById("fechafactura").value;
      var esCredito=document.getElementById("credito").checked;
      var vencefactura = document.getElementById("vencefactura").value;
      var url="{{url('compras/guardarcabecera')}}";
    
      if(id_compras_cab !== ''){
        
       parametros={
            idproveedor:idProveedor,
            numerofactura:numerofactura,
            fechafactura:fechafactura,
            escredito:esCredito,
            vencefactura:vencefactura,
            id_cabecera: id_compras_cab
        };

        console.log(parametros);
     
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
          console.log(id_compras_cab);
          $('#btnEditarCabeceraFactura').removeAttr('disabled');
          $('#id_cab_factura').val(id_compras_cab);
          document.getElementById('btn_calcular_preciosug').removeAttribute('disabled');
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
      }else{
        
       parametros={
            idproveedor:idProveedor,
            numerofactura:numerofactura,
            fechafactura:fechafactura,
            escredito:esCredito,
            vencefactura:vencefactura
        };

        console.log(parametros);
        return false;

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
          console.log(id_compras_cab);
          $('#btnEditarCabeceraFactura').removeAttr('disabled');
          $('#id_cab_factura').val(id_compras_cab);
          document.getElementById('btn_calcular_preciosug').removeAttribute('disabled');
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
      
      //var botonguardaritem=document.getElementById("btnGuardarItem");

      


    }

      function editarCabeceraFactura(){
        var url="{{url('compras/guardarcabecera')}}";
      //var botonguardaritem=document.getElementById("btnGuardarItem");
      document.getElementById('proveedor').removeAttribute('disabled');
      document.getElementById("numerofactura").disabled=false;
      document.getElementById("credito").disabled=false;
      document.getElementById("fechafactura").removeAttribute('disabled');
      document.getElementById("vencefactura").removeAttribute('disabled');
      document.getElementById('btnGuardarCabeceraFactura').removeAttribute('disabled');

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
    document.querySelectorAll("#zona_ingreso_repuesto *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_ingreso_items_factura *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_fotos *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_similares *").forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll("#zona_OEMs *").forEach(el => el.setAttribute("disabled", "true"));
    // document.getElementById("#oems_rep").setAttribute("disabled", "true");
    document.querySelectorAll("#zona_FABs *").forEach(el => el.setAttribute("disabled", "true"));
    document.getElementById('btnEditarCabeceraFactura').setAttribute("disabled","true");
    document.querySelectorAll('#rec_alt_rep').forEach(el => el.setAttribute("disabled", "true"));
    document.querySelectorAll('#bendix').forEach(el => el.setAttribute("disabled", "true"));
    cargar_familia();
    cargar_fabricantes();
    cargar_marca_repuesto();
    cargar_pais();

    // APLICAR MÁSCARAS DE FORMATO CHILENO A INPUTS
    const inputsConMascara = ['cantidad', 'pu', 'subtotalitem', 'stock_minimo', 'stock_maximo', 'preciosug', 'preal', 'poferta', 'ref_total_factura', 'ref_total_flete'];
    inputsConMascara.forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        applyMaskChile(input);
        // Formatear valor inicial si existe
        if (input.value && input.value !== '0' && input.value !== '0.00') {
          input.value = formatEnteroChile(cleanNumberChile(input.value));
        }
      }
    });

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

function editarNombreMedida(){
    let medida_nombre = document.getElementById('medida_nombre').value;
    let id_medida = document.getElementById('medida_id').value;

    if(medida_nombre.length>0){
        let url="{{url('factuprodu/guardar_edicion_medida')}}";
        let parametros={id_medida:id_medida,medida_nombre:medida_nombre};
      // enviar header de laravel para que ajax lo acepte
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

        $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            success:function(data){
              console.log(data);
                if(data==1){
                    Vue.swal({
                        title: 'OK',
                        text: "Se editó la medida correctamente",
                        icon: 'success',
                    });
                    document.getElementById('medida_nombre').value="";
                    document.getElementById('medida_id').value="";
                    $('#modalEditarMedida').modal('hide');
                    cargar_medidas();
                }else{
                    Vue.swal({
                        title: 'ERROR',
                        text: "No se pudo editar la medida",
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
}else{
    Vue.swal({
        title: 'ERROR',
        text: "Debe ingresar un nombre para la medida",
        icon: 'error',
    }); 
  }
}

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
    grid-template-columns: repeat(4,minmax(0,1fr));
    grid-template-rows: auto;
    height: auto;
    row-gap: 0;
    column-gap: 8px;
    align-items: start;
    max-width: 100%;
}


.g-titulo{
    grid-column: 1/5;
    display: grid;
    grid-template-columns: 80% 20%;
    grid-template-rows: 2fr 1fr;
}


.g-cabecera{
    grid-column: 1/5;
    background-color: #fff;
    display:grid;
    grid-template-columns: repeat(6,1fr);
    grid-template-rows: 1fr 2fr;
    column-gap: 2px;
    border: 1px solid #dee2e6;
    border-radius: 4px;



    /* justify-items: center;



    align-items:end; */







}



#titulo-factura{



    grid-column: 1/6;



    text-align: center;



}



#btnGuardarCabeceraFactura-div{
    text-align: right;
    display: block;
    width: 235px;
}

#btnEditarCabeceraFactura{
  float: left;
}

#titulo-factura-fila2{



    grid-row:2/3;



}



.g-repuestos{



    grid-column: 1/5;



    background-color: #fff;



    display:grid;



    grid-template-columns: repeat(6,1fr);



    grid-template-rows: 1fr 9fr;



    column-gap: 2px;

    border: 1px solid #dee2e6;


}







.contenedor .g-repuestos .g-repuestos-titulo{
    grid-column:1/7;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding-bottom:10px;
    margin-bottom:12px;
    border-bottom:1px solid #e9ecef;
}

#ingreso_datos_titulo{
    margin:0;
    flex:1;
    text-align:center !important;
}

#zona_ingreso_repuesto{
    grid-column:1/7;
    display:grid;
    grid-template-columns:minmax(0,1fr);
    gap:14px;
}

#zona_ingreso_repuesto > div{
    grid-column:1;
    display:grid;
    gap:12px 18px;
    align-items:start;
}

#zona_ingreso_repuesto .g-repuestos-busqueda{
    grid-template-columns:auto minmax(180px,360px) auto auto;
    justify-content:center;
    align-items:center;
    padding-bottom:12px;
    border-bottom:1px solid #e9ecef;
}

#ing-cod-rep{
    display:block;
    min-width:0;
}

#txtBuscar{
    width:100%;
}

#zona_ingreso_repuesto .g-repuestos-busqueda .enlinea{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

#zona_ingreso_repuesto .g-repuestos-busqueda label{
    margin:0;
}

#zona_ingreso_repuesto .g-repuestos-familia,
#zona_ingreso_repuesto .g-repuestos-stock{
    grid-template-columns:repeat(3,minmax(0,1fr));
}

#zona_ingreso_repuesto .g-repuestos-descripcion{
    grid-template-columns:repeat(4,minmax(0,1fr));
}

#zona_ingreso_repuesto .g-repuestos-familia > span,
#span_medidas{
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    gap:0 6px;
    align-items:start;
}

#zona_ingreso_repuesto .g-repuestos-familia label,
#span_medidas label{
    grid-column:1/-1;
}

#zona_ingreso_repuesto label{
    display:block;
    font-size:13px;
    margin-bottom:5px;
}

#zona_ingreso_repuesto span{
    min-width:0;
}

#zona_ingreso_repuesto .g-repuestos-familia select{
    width:100%;
    min-width:0;
}

#span_medidas .clearfix{
    display:none;
}

#zona_ingreso_repuesto .g-repuestos-descripcion textarea{
    height:64px;
    resize:vertical;
}

#span_medidas textarea{
    width:100%;
    float:none;
}

@media (max-width:1100px){
    #zona_ingreso_repuesto .g-repuestos-descripcion{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    #zona_ingreso_repuesto .g-repuestos-busqueda{
        grid-template-columns:auto minmax(0,360px) auto;
    }
    #zona_ingreso_repuesto .g-repuestos-busqueda .enlinea{
        grid-column:1/-1;
    }
}

@media (max-width:640px){
    .contenedor .g-repuestos .g-repuestos-titulo{
        flex-wrap:wrap;
    }
    #zona_ingreso_repuesto .g-repuestos-familia,
    #zona_ingreso_repuesto .g-repuestos-descripcion,
    #zona_ingreso_repuesto .g-repuestos-stock{
        grid-template-columns:minmax(0,1fr);
    }
    #zona_ingreso_repuesto .g-repuestos-busqueda{
        grid-template-columns:minmax(0,1fr) auto;
    }
    #zona_ingreso_repuesto .g-repuestos-busqueda > span{
        grid-column:1/-1;
    }
}

.g-items{
    grid-column: 1/5;
    background:#fff;
    border:1px solid #dee2e6;
    border-radius:8px;
    padding:12px 15px 15px 15px;
}

.contenedor .titulazo{
    background:transparent;
    color:#343a40;
}

.g-cabecera,
.g-repuestos{
    padding:10px 15px;
}

.g-repuestos{
    grid-template-rows:auto auto;
    border-radius:4px;
}

#titulo-factura,
#ingreso_datos_titulo,
#titulo-item-factura,
#costos_importacion_factura .grupo-titulo{
    color:#495057;
    font-size:14px;
    font-weight:600;
}

.g-items-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin-bottom:10px;
    padding-bottom:8px;
    border-bottom:1px solid #dee2e6;
}

#titulo-item-factura p{
    margin:0;
}

.grupo-campos{
    margin-bottom:12px;
    padding-bottom:12px;
    border-bottom:1px solid #e9ecef;
}

.grupo-campos:last-of-type{
    border-bottom:none;
    margin-bottom:0;
    padding-bottom:0;
}

.grupo-titulo{
    font-size:11px;
    font-weight:bold;
    text-transform:uppercase;
    color:#495057;
    letter-spacing:.5px;
    margin-bottom:6px;
}

.grupo-fila{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap:10px 15px;
}

.destino-acciones{
    display:flex;
    justify-content:center;
    align-items:flex-end;
    gap:16px;
    flex-wrap:wrap;
    padding:6px 0;
}

.destino-acciones > .campo{
    width:360px;
    max-width:100%;
    min-width:0;
}

#zona_ingreso_items_factura .destino-acciones #locales{
    width:100%;
}

.grupo-fila.col-1{
    grid-template-columns: 1fr;
}

.campo label{
    display:block;
    font-size:12px;
    margin-bottom:2px;
}

.campo .ayuda{
    display:block;
    font-size:10px;
    color:#6c757d;
    margin-top:3px;
}

.campo-flete-resultado{
    background:#f3f4f5 !important;
    font-weight:bold;
}

.campo-referencia{
    background:#eef3f8 !important;
    color:#4a5b6b;
}

.campo-precio-sugerido{
    display:flex;
    gap:6px;
}

.campo-precio-sugerido input{
    flex:1;
}

.panel-ficha{
    background:#fff;
    border:1px solid #dee2e6;
    padding:12px 15px 15px 15px;
    display:flex;
    flex-direction:column;
    gap:10px;
    min-height:420px;
}

.panel-ficha-titulo{
    font-size:11px;
    font-weight:bold;
    text-transform:uppercase;
    color:#495057;
    letter-spacing:.5px;
    margin:0 0 2px 0;
    padding-bottom:8px;
    border-bottom:1px solid #dee2e6;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* Tonos suaves para identificar cada sección sin recargar los campos. */
.contenedor .g-cabecera{
    --seccion-fondo:#f2f6fa;
    --seccion-borde:#b8c9d9;
}

#referencia_costos_factura{
    --seccion-fondo:#f2f6f6;
    --seccion-borde:#bccdcd;
}

.referencia-ayuda{
    color:#59666b;
    font-size:13px;
    margin:0 0 12px;
}

@media (max-width:640px){
    #referencia_costos_factura .grupo-fila{
        grid-template-columns:minmax(0,1fr);
    }
}

#costos_importacion_factura{
    --seccion-fondo:#f8f5ef;
    --seccion-borde:#d4c5aa;
}

.contenedor .g-repuestos{
    --seccion-fondo:#f1f7f3;
    --seccion-borde:#b6cebf;
}

#zona_ingreso_items_factura{
    --seccion-fondo:#f5f3f9;
    --seccion-borde:#c6bdd7;
}

#zona_fotos{
    --seccion-fondo:#f1f6f7;
    --seccion-borde:#b7ccd0;
}

#zona_similares{
    --seccion-fondo:#f5f6ef;
    --seccion-borde:#c8cdaf;
}

#zona_OEMs{
    --seccion-fondo:#faf3f1;
    --seccion-borde:#d8beb7;
}

#zona_FABs{
    --seccion-fondo:#f3f4f6;
    --seccion-borde:#c2c8d0;
}

.contenedor > .g-cabecera,
.contenedor > .g-repuestos,
.contenedor > .g-items,
.contenedor > .panel-ficha{
    background:var(--seccion-fondo);
    border-color:var(--seccion-borde);
    border-left-width:3px;
}

.g-fotos{
    border-top-right-radius:8px;
    border-bottom-right-radius:8px;
}

.g-fabricantes{
    border-top-left-radius:8px;
    border-bottom-left-radius:8px;
}

.campo-linea{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.campo-linea .campo{
    flex:1;
    min-width:140px;
}

#global {
	height: 60px;
	width: 85%;
  float: left;
	border: 1px solid #ddd;
	background: #f1f1f1;
	overflow-y: scroll;
}





.dato{

  font-size: 11px;

}





/* Distribución y títulos centrados de las secciones de ingreso. */
.contenedor > .panel-ficha{
    grid-column:span 2;
    min-width:0;
    border-radius:8px;
    margin-top:10px;
    align-self:stretch;
}
#titulo-item-factura{
    width:100%;
    text-align:center;
}
.grupo-titulo{
    text-align:center;
}
.panel-ficha-titulo{
    position:relative;
    display:block;
    text-align:center;
}
#zona_similares .panel-ficha-titulo{
    padding-left:40px;
    padding-right:40px;
    min-height:32px;
}
.panel-ficha-titulo #ampliar{
    position:absolute;
    right:0;
    top:0;
}
#zona_similares .campo-linea{
    display:grid;
    grid-template-columns:minmax(0, 2fr) minmax(0, 3fr);
    align-items:start;
}
#zona_similares .campo-linea .campo{
    min-width:0;
    display:flex;
    flex-direction:column;
}
#zona_similares #MarcaSim{
    height:300px;
}
#zona_similares #ModeloSim{
    height:266px;
}
#zona_similares #MarcaSim,
#zona_similares #ModeloSim{
    width:100%;
}
@media (max-width:640px){
    #zona_similares .campo-linea{
        grid-template-columns:minmax(0, 1fr);
    }
}
@media (max-width:1100px){
    #zona_ingreso_repuesto .g-repuestos-busqueda .enlinea{
        justify-content:center;
    }
}
@media (max-width:640px){
    .contenedor > .panel-ficha{
        grid-column:1/-1;
    }
    #zona_ingreso_repuesto .g-repuestos-busqueda > span{
        text-align:center;
    }
}
</style>



@endsection



@section('contenido_ingresa_datos')



<div class="contenedor">



    <div class="g-titulo">



            <div class="w-100">



                <center><h4 class="titulazo">Ingreso por Compras</h4></center>



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



        <!-- <input type="hidden" id="codigo_interno" value="0"> -->











        <!-- FIN DE CAMPOS OCULTOS -->







    <div class="g-cabecera">



        <div id="titulo-factura"><strong>FACTURA</strong></div>



        <div id="btnGuardarCabeceraFactura-div">



            <input type="submit" id="btnGuardarCabeceraFactura" onclick="validarCabeceraFactura()" name="btnGuardarCabeceraFactura" value="Guardar Cabecera" class="btn btn-primary btn-sm"/>
            <input type="submit" id="btnEditarCabeceraFactura" onclick="editarCabeceraFactura()" name="btnEditarCabeceraFactura" value="Editar Cabecera" class="btn btn-warning btn-sm"/>


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

    <div class="g-items" id="referencia_costos_factura">
        <div class="g-items-header text-center justify-content-center">
            <strong class="text-center">REFERENCIA DE FLETE Y OTROS PORCENTAJES</strong>
        </div>
        <p class="referencia-ayuda">Calculadora de referencia. Puedes elegir libremente el % Flete que aplicarás en la sección siguiente.</p>
        <div class="grupo-campos">
            <div class="grupo-fila">
                <span class="campo">
                    <label for="ref_total_factura">Total de la factura ($):</label>
                    <input type="text" id="ref_total_factura" class="form-control form-control-sm" inputmode="numeric" placeholder="Ingrese el total" oninput="calcularReferenciaFlete()" onblur="calcularReferenciaFlete()">
                </span>
                <span class="campo">
                    <label for="ref_total_flete">Total de flete ($):</label>
                    <input type="text" id="ref_total_flete" class="form-control form-control-sm" inputmode="numeric" placeholder="Ingrese el flete total" oninput="calcularReferenciaFlete()" onblur="calcularReferenciaFlete()">
                </span>
                <span class="campo">
                    <label for="ref_porcentaje_flete">% Flete de referencia:</label>
                    <input type="text" id="ref_porcentaje_flete" class="form-control form-control-sm campo-flete-resultado" readonly placeholder="Se calcula con los totales">
                    <span class="ayuda">Total de flete ÷ total de factura × 100</span>
                </span>
            </div>
        </div>
        <div class="grupo-campos">
            <div class="grupo-fila">
                <span class="campo">
                    <label for="ref_getnet">% Getnet:</label>
                    <input type="text" id="ref_getnet" class="form-control form-control-sm" inputmode="decimal" value="0" oninput="calcularReferenciaFlete()">
                </span>
                <span class="campo">
                    <label for="ref_ppm">% PPM:</label>
                    <input type="text" id="ref_ppm" class="form-control form-control-sm" inputmode="decimal" value="0" oninput="calcularReferenciaFlete()">
                </span>
                <span class="campo">
                    <label for="ref_porcentaje_total">% Combinado de referencia:</label>
                    <input type="text" id="ref_porcentaje_total" class="form-control form-control-sm campo-flete-resultado" readonly placeholder="Flete + Getnet + PPM">
                    <span class="ayuda">Suma de porcentajes. No modifica automáticamente el % Flete aplicado.</span>
                </span>
            </div>
        </div>
    </div>

    <div class="g-items" id="costos_importacion_factura">
            <div class="grupo-campos">
                <div class="grupo-titulo">Costos de importación de la factura</div>
                <div class="grupo-fila">
                    <span class="campo">
                        <label for="utilidad">% Utilidad (margen):</label>
                        <input type="text" name="utilidad" value="0" id="utilidad" class="form-control form-control-sm" onfocusout="calculapreciosug()">
                        <span class="ayuda">Se mantiene para todos los productos de esta factura hasta que lo cambie.</span>
                    </span>
                    <span class="campo">
                        <label for="porcentaje_flete">% Flete:</label>
                        <input type="text" name="porcentaje_flete" value="0" id="porcentaje_flete" class="form-control form-control-sm" onfocusout="calculapreciosug()">
                        <span class="ayuda">Sobre el precio unitario</span>
                    </span>
                    <span class="campo">
                        <label for="flete">Flete (calculado):</label>
                        <input type="text" name="flete" value="0" id="flete" class="form-control form-control-sm campo-flete-resultado" readonly>
                    </span>
                </div>
            </div>
    </div>








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



                    <select name="cboFamilia" class="form-control form-control-sm" id="familia" onchange="dameUtilidad(this.value)">



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

                    <div class="clearfix"></div>

                    <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>
                    
                    <button class="btn btn-success btn-sm" onclick="agregar_medida_familia()">+</button>
                    <input type="hidden" id="familia_length" />

                </span>



                <span>



                    <label for="cod_repuesto_proveedor">Cód. Repuesto: </label>



                    <textarea name="cod_repuesto_proveedor" maxlength="2000" wrap="hard" value="" id="cod_repuesto_proveedor" cols="11" rows="2" placeholder="Es el código interno del proveedor" class="form-control form-control-sm"></textarea>



                </span>



            </div>



            <div class="g-repuestos-stock">
                <span>
                    <label for="stock_minimo">Stock Mínimo:</label>
                    <input type="text" name="stock_minimo" value="" id="stock_minimo" class="form-control form-control-sm">
                </span>
                <span>
                    <label for="stock_maximo">Stock Máximo:</label>
                    <input type="text" name="stock_maximo" value="10"  id="stock_maximo" class="form-control form-control-sm">
                </span>
                <span>
                    <label for="codigo_barras">Código de Barras:</label>
                    <input type="text" name="codigo_barras" value="0" id="codigo_barras" class="form-control form-control-sm">
                </span>
            </div>
        </div>

    </div> {{-- FIN g-repuestos --}}

        <div class="g-items" id="zona_ingreso_items_factura">
            <div class="g-items-header">
                <div id="titulo-item-factura">
                    <p><b>INGRESO DE ITEMS DE LA FACTURA</b></p>
                </div>

            </div>

            <div class="grupo-campos">
                <div class="grupo-titulo">1. Datos de la compra</div>
                <div class="grupo-fila">
                    <span class="campo">
                        <label for="cantidad">Cantidad:</label>
                        <input type="text" name="cantidad" value="0" id="cantidad" class="form-control form-control-sm" onfocusout="calculasubtotalitem()">
                    </span>
                    <span class="campo">
                        <label for="pu">Precio Unitario:</label>
                        <input type="text" name="pu" value="0" id="pu" class="form-control form-control-sm" onfocusout="calculasubtotalitem()">
                    </span>
                    <span class="campo">
                        <label for="subtotalitem">Subtotal:</label>
                        <input type="text" name="subtotalitem" value="0" id="subtotalitem" class="form-control form-control-sm campo-flete-resultado" readonly>
                    </span>
                </div>
            </div>


            <div class="grupo-campos">
                <div class="grupo-titulo">2. Precio de venta</div>
                <div class="grupo-fila">
                    <span class="campo">
                        <label for="preal">Precio REAL (referencia):</label>
                        <input type="text" name="preal" id="preal" class="form-control form-control-sm campo-referencia" placeholder="Precio REAL">
                    </span>
                    <span class="campo">
                        <label for="poferta">Precio OFERTA (referencia):</label>
                        <input type="text" name="poferta" id="poferta" class="form-control form-control-sm campo-referencia" placeholder="Precio OFERTA">
                    </span>
                    <span class="campo">
                        <label for="preciosug">Precio Sugerido:</label>
                        <span class="campo-precio-sugerido">
                            <input type="text" name="preciosug" value="0" id="preciosug" class="form-control form-control-sm">
                            <button class="btn btn-success btn-sm" id="btn_calcular_preciosug" onclick="calculapreciosug(true)" onfocusout="ir_a_boton_guardaritem()" readonly>Calcular</button>
                        </span>
                    </span>
                </div>
            </div>

            <div class="grupo-campos">
                <div class="grupo-titulo">3. Destino</div>
                <div class="destino-acciones">
                    <span class="campo">
                        <label for="locales">Local:</label>
                        <select name="locales" class="form-control form-control-sm" id="locales">
                            @foreach ($locales as $local)
                            <option value="{{$local->id}}">{{$local->local_nombre}} en {{$local->local_direccion}}</option>
                            @endforeach
                        </select>
                    </span>
                <div id="btnGuardarItem-div">
                    <input type="submit" id="btnGuardarItem" name="btnGuardarItem" class="btn btn-success btn-sm" value="Guardar Item" onclick="verificar_cod_prov()"/>
                </div>
                </div>
            </div>

            <span class="d-none">
              <label for="preciosug">Ubicación</label><br>
              <span>
                  <input type="text" name="ubicacion" value=""  id="ubicacion_repuesto" class="form-control form-control-sm" style="display:inline-block;width:50%;margin-right:4px" disabled> 
                  <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exampleModal">Ubicación</button>
              </span>
            </span>
        </div>{{-- FIN g-items --}}
















<!-- zona_fotos INICIO-->
        <div class="g-fotos panel-ficha" id="zona_fotos">
            <p id="fotos_msje" class="panel-ficha-titulo">FOTOS:</p>

            <div class="campo">
                <label for="archivo">Subir foto (jpg, jpeg, png):</label>
                <input type="file" name="archivo" id="archivo" class="form-control-file form-control-sm">
                <span class="ayuda">Se guardará asociada a este repuesto.</span>
            </div>

            <div>
                <input type="submit" name="btnGuardarFoto" id="btnGuardarFoto" value="Agregar Foto" class="btn btn-primary btn-sm" onclick="guardarfoto()"/>
            </div>

            <div id="fotos_ficha_tecnica"></div>
            <div id="fotos_rep"></div>
        </div><!-- zona_fotos FIN -->

<!-- zona_similares APLICACIONES INICIO-->

        <div class="g-aplicaciones panel-ficha" id="zona_similares" >
            <p class="panel-ficha-titulo">
                <span id="aplicaciones_msje">APLICACIONES:</span>
                <button type="button" id="ampliar" class="btn btn-sm btn-outline-secondary" onclick="ampliar()" title="Ampliar / contraer">&gt;&gt;</button>
            </p>

            <div class="campo-linea">
                <span class="campo">
                    <label for="MarcaSim">Marca:</label>
                    <select name="cboMarcaSim" class="form-control form-control-sm" id="MarcaSim" onchange="cargarModelosSimilares()" size="14">
                        <option value="">Elija una marca</option>
                        @foreach ($marcas as $marca)
                            <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
                        @endforeach
                    </select>
                </span>
                <span class="campo">
                    <label for="filtrar_modelo">Modelo:</label>
                    <input type="text" class="form-control form-control-sm mb-1" id="filtrar_modelo" placeholder="Filtrar modelo">
                    <select name="cboModeloSim" id="ModeloSim" class="form-control form-control-sm" onchange="ubicarse_en_anios()" size="12">
                        <option value="">Sin modelos</option>
                    </select>
                </span>
            </div>

            <p id="nombre_modelo" class="ayuda mb-0"><b>Modelo:</b></p>

            <div class="campo">
                <label for="anios_vehiculo_sim">Años de aplicación:</label>
                <div class="campo-precio-sugerido">
                    <input type="text" name="anios_vehiculo_sim" value="" id="anios_vehiculo_sim" class="form-control form-control-sm" placeholder="Ej: 2010-2015">
                    <input type="submit" name="btnGuardarSimilar" id="btnGuardarSimilar" value="Agregar" class="btn btn-primary btn-sm" onclick="guardarsimilar()"/>
                </div>
                <span class="ayuda">Elija marca y modelo, luego indique el rango de años.</span>
            </div>

            <div id="similares_rep">
                <!--zona para aplicaciones -->
            </div>
        </div>
     <!-- zona_similares APLICACIONES FIN-->

    <!-- zona_OEMs INICIO-->
        <div class="g-oems panel-ficha" id="zona_OEMs">
            <p id="oems_msje" class="panel-ficha-titulo">OEMs:</p>

            <div class="campo">
                <label for="codigos_OEM">Código OEM:</label>
                <div class="campo-precio-sugerido">
                    <input type="text" name="codigos_OEM" id="codigos_OEM" class="form-control form-control-sm" onkeyup="enter_text_oem(event)">
                    <input type="submit" name="btnGuardarOEM" id="btnGuardarOEM" value="Agregar" class="btn btn-primary btn-sm" onclick="guardarOEM()"/>
                </div>
                <span class="ayuda">Código original del fabricante de equipo.</span>
            </div>

            <div id="oems_rep"></div>
            <div id="rec_alt_rep"></div>
            <div id="bendix"></div>
            <div id="result"></div>
        </div>
 <!-- zona_OEMs FIN-->

<!-- zona_FABs INICIO-->
        <div class="g-fabricantes panel-ficha" id="zona_FABs">
            <p id="fabricantes_msje" class="panel-ficha-titulo">FABRICANTES:</p>

            <div class="campo">
                <label for="cboFabricante">Fabricante:</label>
                <select name="cboFabricante" id="cboFabricante" class="form-control form-control-sm">
                    <option value="">Sin Fabricantes</option>
                </select>
            </div>

            <div class="campo">
                <label for="codigo_FAB">Código:</label>
                <div class="campo-precio-sugerido">
                    <input type="text" name="codigo_FAB" id="codigo_FAB" class="form-control form-control-sm" onkeyup="enter_text_FAB(event)">
                    <input type="submit" name="btnGuardarFAB" id="btnGuardarFAB" value="Agregar" class="btn btn-primary btn-sm" onclick="guardarFAB()"/>
                </div>
                <span class="ayuda">Código del fabricante alternativo seleccionado.</span>
            </div>

            <div id="fabs_rep"></div>
        </div>
<!-- zona_FABs FIN-->

</div> <!-- FIN de clase contenedor css grid -->



    @endsection







    @section('contenido_ver_datos')







<!-- VENTANA MODAL BUSCAR REPUESTO"-->



<div role="dialog" tabindex="-1" class="modal fade bd-example-modal-xl" id="busca-repuesto-modal">
  <div class="modal-dialog modal-xl" role="document" >
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



                  <input type="text" name="marcarepuesto" id="marcarepuesto" size="20" maxlength="40" value="" class="form-control" style="width:100%">



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
                  <input type="text" name="medida" id="medida" size="20" maxlength="40" value="{{old('medida')}}" class="form-control" style="width:100%">
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
  <div class="modal-dialog modal-lg" role="document">
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="guardar_lista_medidas()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="verMedidasModal_pd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Medidas Disco</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_medidas_pd">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="guardar_lista_medidas_disco()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="verMedidasModal_pd_dos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Medidas Prensa</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_medidas_pd_dos">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="guardar_lista_medidas_prensa()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Guardar ubicación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_ubicacion_dos">
        
          <span>Ingrese nueva ubicación</span>
              <div class="select-ubicacion">
              
                  <select name="piso" id="piso_ubicacion" class="form-control">
                      <option value="p1">Piso 1</option>
                      <option value="p2">Piso 2</option>
                      <option value="p3">Piso 3</option>
                  </select>
                  <select name="estanteria" id="estanteria_ubicacion" class="form-control">
                      <option value="0">Estanteria</option>
                      <option value="A">A</option>
                      <option value="B">B</option>
                      <option value="C">C</option>
                      <option value="D">D</option>
                      <option value="E">E</option>
                      <option value="F">F</option>
                      <option value="G">G</option>
                      <option value="H">H</option>
                      <option value="I">I</option>
                      <option value="J">J</option>
                      <option value="K">K</option>
                      <option value="L">L</option>
                      <option value="M">M</option>
                      <option value="N">N</option>
                      <option value="Ñ">Ñ</option>
                      <option value="O">O</option>
                      <option value="P">P</option>
                      <option value="Q">Q</option>
                      <option value="R">R</option>
                      <option value="S">S</option>
                      <option value="T">T</option>
                      <option value="U">U</option>
                      <option value="V">V</option>
                      <option value="W">W</option>
                      <option value="X">X</option>
                      <option value="Y">Y</option>
                      <option value="Z">Z</option>
                  </select>
                  <select name="bandeja" id="bandeja_ubicacion" class="form-control">
                      <option value="b0">Bandeja</option>
                      <option value="b1">Bandeja 1</option>
                      <option value="b2">Bandeja 2</option>
                      <option value="b3">Bandeja 3</option>
                      <option value="b4">Bandeja 4</option>
                      <option value="b5">Bandeja 5</option>
                      <option value="b6">Bandeja 6</option>
                      <option value="b7">Bandeja 7</option>
                      <option value="b8">Bandeja 8</option>
                      <option value="b9">Bandeja 9</option>
                      <option value="b10">Bandeja 10</option>
                  </select>
                  <select name="pasillo" id="pasillo_ubicacion" class="form-control">
                    <option value="p0">Pasillo</option>
                    <option value="p1">Pasillo 1</option>
                    <option value="p2">Pasillo 2</option>
                    <option value="p3">Pasillo 3</option>
                    <option value="p4">Pasillo 4</option>
                    <option value="p5">Pasillo 5</option>
                    <option value="p6">Pasillo 6</option>
                    <option value="p7">Pasillo 7</option>
                    <option value="p8">Pasillo 8</option>
                    <option value="p9">Pasillo 9</option>
                    <option value="p10">Pasillo 10</option>
                    <option value="p11">Pasillo 11</option>
                    <option value="p12">Pasillo 12</option>
                    <option value="p13">Pasillo 13</option>
                    <option value="p14">Pasillo 14</option>
                    <option value="p15">Pasillo 15</option>
                    <option value="p16">Pasillo 16</option>
                    <option value="p17">Pasillo 17</option>
                    <option value="p18">Pasillo 18</option>
                    <option value="p19">Pasillo 19</option>
                    <option value="p20">Pasillo 20</option>
                  </select>
              </div>
          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" data-dismiss="modal" onclick="agregar_ubicacion()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalEditarMedida">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Editar Medida</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            
            <label for="medida">Medida</label>
            <input type="text" name="medida" id="medida_nombre" class="form-control">
            <input type="hidden" name="id_medida" id="medida_id">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success btn-sm" onclick="editarNombreMedida()">Guardar</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="mostrarModalPrincipal()">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  <!--DATOS DE VITAL IMPORTANCIA -->
<input type="hidden" id="id_cab_factura">



  @endsection



