@extends('plantillas.app')
@section('titulo','Modificar Repuesto')
@section('javascript')
  <script type="text/javascript">

var ampliar_aplicacionez=false;
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

function ampliar()
    {
        if(ampliar_aplicacionez==false)
        {
            document.getElementById("aplicacionez").className = "col-sm-9";
            document.getElementById("OEMz").style.visibility="hidden";
            document.getElementById("FABz").style.visibility="hidden";
            $("#ampliar").html("<<");
        }else{
            document.getElementById("aplicacionez").className = "col-sm-5";
            document.getElementById("OEMz").style.visibility="visible";
            document.getElementById("FABz").style.visibility="visible";
            $("#ampliar").html(">>");
        }
        ampliar_aplicacionez=!ampliar_aplicacionez;
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
          @php
            if($repuesto->count()>0){
                $cr=$repuesto->codigo_interno;
                echo "buscarRepuesto(1);";
            }else{
                $cr="";
            }
         @endphp
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
         
          cargar_marca_repuesto();
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

  function dame_medidas(value)
  {
     let familia = document.getElementById('familia').value;
     let dato = value || familia;
     var url_medidas = '{{url("factuprodu")}}'+'/'+dato+'/medidas';
      
     $.ajax({

        type:'get',

        url: url_medidas,

        beforeSend: function(){

          console.log('buscando...');

        },

        success: function(resp){
          console.log(resp);
          $('#rec_alt_rep').empty();
          $('#result').empty();
          $('#bendix').empty();
         
          if(resp.length == 0){

            $('#span_medidas').empty();

            $('#span_medidas').append(`

            <label for="medidas">Medidas:</label>

                      <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>
                      
            `);

          }else if(resp == "bendix"){
            $('#rec_alt_rep').addClass('d-none');
            
            $('#bendix').append(`
            <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
            
            <div class='form-group'>
                <label>MOTOR DE PARTIDA</label>
                <input class='form-control' id="motorpartida" type='text' placeholder ='Motor de partida' />
            </div>
            
            
              <input type="button" role="button" class='btn btn-success btn-sm mb-3' onclick="guardar_motor()" id="btn_motor" value="Guardar datos" disabled="true" /> 
            </div>
            </div>
            `);
            // hacer que el atributo disabled sea false del boton btn_motor
          document.getElementById("btn_motor").disabled=false;
          }else if(resp == 'rv'){
          
            $('#rec_alt_rep').removeClass('d-none');
            $('#rec_alt_rep').addClass('d-block');
            $('#rec_alt_rep').append(`
            
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
              <input type="button" role="button" class='btn btn-success btn-sm mb-4' onclick="guardar_rec_alt()" value="Guardar datos" /> 
            </div>
            </div>
            `);
          
          }else if(resp[0] == 'pd'){
            let medidas_disco = resp[1];
            let medidas_prensa = resp[2];
            let medidas_respaldo = $('#medidas_respaldo').val();
            // seperar las medidas_respaldo por los caracteres ,,
            let array_medidas_respaldo = medidas_respaldo.split(',,');
            let respaldo_disco = array_medidas_respaldo[0];
            // eliminar el primer caracter de la cedena respaldo_disco que es una coma
            respaldo_disco = respaldo_disco.substring(1);
            
            let respaldo_prensa = array_medidas_respaldo[1];
            let array_respaldo_disco = respaldo_disco.split(',');
            // se elimina el primer registro del array que es un espacio en blanco
            array_respaldo_disco.shift();
            let array_respaldo_prensa = respaldo_prensa.split(',');
            // se elimina el primer registro del array que es un espacio en blanco
            array_respaldo_prensa.shift();
            // se elimina la cadena DISCO: y PRENSA: de los registros del array
            array_respaldo_disco = array_respaldo_disco.map(function(item){
              return item.replace('DISCO:','');
            });
            array_respaldo_prensa = array_respaldo_prensa.map(function(item){
              return item.replace('PRENSA:','');
            });

            console.log(array_respaldo_disco);

            $('#span_medidas').empty();
            $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);
            let button_html = `
            <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" disabled >+</button>
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
                    <th scope='row'><input type="text" class="form-control dato" id="medida1_pd" value="`+respaldo_disco+`"  /></th> 
                    <th scope='row'> 
                      <button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal_pd' >Seleccionar medidas </button> <input type="hidden" id="medida_value" />
                    </th>
              </tr>
              <tr>
                    <th scope='row'>PRENSA</th> 
                    <th scope='row'><input type="text" class="form-control dato" id="medida2_pd" value="`+respaldo_prensa+`"  /></th> 
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
                  e.descripcion_ = e.descripcion+'-';
                  array_respaldo_disco.forEach(m => {
                    // si m hasta el caracter - es igual a e.descripcion se le asigna el valor de m a e.descripcion_
                    if(m.substring(0, m.indexOf('-')) == e.descripcion){
                      
                      e.descripcion_ = m;
                    }
                  });
                  html_+= `
                  <tr>
                    <th scope='row'>`+e.descripcion+ `</th> 
                    <th scope='row'> <input type="text" class="form-control dato_disco" id="medida_`+cont_+`" value="`+e.descripcion_+`" disabled /> <input type="hidden" id="medida_value_" /></th> 
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
                  e.descripcion_ = e.descripcion+'-';
                  array_respaldo_prensa.forEach(m => {
                    // si m hasta el caracter - es igual a e.descripcion se le asigna el valor de m a e.descripcion_
                    if(m.substring(0, m.indexOf('-')) == e.descripcion){
                      
                      e.descripcion_ = m;
                    }
                  });
                  html_dos+= `
                  <tr>
                    <th scope='row'>`+e.descripcion+ `</th> 
                    <th scope='row'> <input type="text" class="form-control dato_prensa" id="medida_dos`+cont_dos+`" value="`+e.descripcion_+`" disabled /> <input type="hidden" id="medida_value_" /></th> 
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
          }
          
          else{

            $('#span_medidas').empty();

            $('#span_medidas').append(`<label class='float-left' for='medidas'>Medidas: </label>`);

            let button_html = `

            <button onclick="agregar_medida_familia()" class="btn btn-success btn-sm float-left" >+</button>

            `;

            let btn_ver_medidas = `<button class='btn btn-info btn-sm float-left' style='clear: both;' data-toggle='modal' data-target='#verMedidasModal' >Seleccionar medidas </button>`;

            // Se le agrega el textarea de id medidas que esta escondido para que se pueda agregar la lista de medidas al nuevo repuesto

            let textarea = `

            <label for="medidas" style="display:none;">Medidas:</label>

                      <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo" style='display: none;'></textarea>

            `;

            $('#span_medidas').append(btn_ver_medidas);

            // $('#span_medidas').append(button_html);

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
                <th scope="col">Eliminar </th>
              </tr>

            </thead>

            <tbody>`;

            var cont = 0;
            var array_descripciones = [];
            var medidas_respaldo = $('#medidas_respaldo').val();
            // seperar las medidas por el caracter ,
            var array_medidas_respaldo = medidas_respaldo.split(',');
            // eliminar el primer elemento del array que es vacio
            array_medidas_respaldo.shift();
              
                resp.forEach(e => {
                  
                 
                  cont++;
                  e.descripcion_ = e.descripcion+'-';
                  e.disabledx = 'disabled';
                  array_medidas_respaldo.forEach(m => {
                    // si m hasta el caracter - es igual a e.descripcion se le asigna el valor de m a e.descripcion_
                    if(m.substring(0, m.indexOf('-')) == e.descripcion){
                      
                      e.descripcion_ = m;
                      e.checkedx = 'checked';
                      e.disabledx = '';
                    }
                  });
                  
                 
                  html+= `
                    <tr>

                      <th scope='row'>`+e.descripcion+ `</th> 
                      <th scope='row'> <input type="text" class="form-control dato" id="medida`+cont+`" value="`+e.descripcion_+`" `+e.disabledx+` /> <input type="hidden" id="medida_value" /></th> 
                      <th scope='row'> 

                        <div class='form-check'>

                          <input class='form-check-input' type='checkbox' id='chkbx`+cont+`' onclick="abrirCampoMedidas(`+cont+`)" value='`+e.descripcion+`' `+e.checkedx+`> 

                        </div>  

                      </th>
                      <th> <button class='btn btn-danger btn-sm' role="button" onclick="eliminar_medida_familia(`+e.id+`)">X</button> </th>

                    </tr>`;
                    
                  });

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
            console.log(utilidad);
            
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

    function abrirCampoMedidas(value){
      console.log(value);
      var check = document.getElementById('chkbx'+value);
      var medida = document.getElementById('medida'+value);
      if(check.checked){

        medida.disabled = false;

      }else{

        medida.disabled = true;

      }

    }

    function cargar_marca_repuesto()
    {
      $('#MarcaRepuesto option').remove();
      var url='{{url("marcarepuestoJSON")}}';
      $.ajax({
        type:'GET',
        beforeSend: function () {

        },
        url:url,
        success:function(marks){ //Viene en formato json
          var marcas=JSON.parse(marks);
          $('#MarcaRepuesto').append('<option value="">Elija una Marca de Repuesto</option>');
          marcas.forEach(function(marca){
            $('#MarcaRepuesto').append('<option value="'+marca.id+'">'+marca.marcarepuesto.toUpperCase()+'</option>');
          });

          document.getElementById("MarcaRepuesto").selectedIndex=0;
          cargar_pais();
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
          cargar_fabricantes();
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


    function soloNumeros(e)
    {
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8))
    }



    function guardarDatos() //en btnGuardarDatos
    {
      //DEBE SER UN UPDATE...

      var id_repuesto=document.getElementById("id_repuesto").value;
      var url="{{url('repuesto/modificado')}}";
      //Valores del item de repuesto
      var idFamilia=document.getElementById("familia").value;
      if(idFamilia==0)
      {
        Vue.swal({
            text: 'Elija un Familia...',
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

      var pu = document.getElementById("pu").value.trim();

      if(pu==0 || pu==0.00 || pu.length==0 || isNaN(pu))
      {
        Vue.swal({
                    text: 'Ingrese Precio de Compra...',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      var preciosug=document.getElementById("preciosug").value.trim();

      if(preciosug==0 || preciosug==0.00 || preciosug.length==0 || isNaN(preciosug))
      {
        Vue.swal({
                    text: 'Ingrese Precio Sugerido...',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        return false;
      }

      let activo=0;
      if(document.getElementById("repuesto_activo").checked==true){
        activo=1;
      }

      var parametros={idrep:id_repuesto,
        idFamilia:idFamilia,
        idMarcaRepuesto:idMarcaRepuesto,
        idPais:idPais,
        activo:activo,
        descripcion:descripcion,
        observaciones:observaciones,
        medidas:medidas,
        cod_repuesto_proveedor:cod_repuesto_proveedor.toUpperCase(),
        stockmin:stockmin,
        stockmax:stockmax,
        codbar:codbar,
        pu:pu,
        preciosug:preciosug
      };

     
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
       type:'POST',
       beforeSend: function () {
        espere("Guardando...");
      },
      url:url,
      data:parametros,
      success:function(resp){ //devuelve el ID del repuesto modificado
        
        Vue.swal.close();
        var id=parseInt(resp);
        if(Number.isInteger(id))
        {
          if(id>0)
          {
            $("#mensajes").html("<i>Datos Guardados. Puede Modificar Fotos, Aplicaciones, OEMs y Cod. Fabricantes</i>");
            Vue.swal({
                    title:'Datos Guardados',
                    text: 'Puede Modificar Fotos, Aplicaciones, OEMs y Cod. Fabricantes',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }else{
            $("#mensajes").html("<i>XUXA... no guardóoooo....</i>");
            Vue.swal({
                    text: 'Upss!! NO GUARDÓ',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
          }
        }else{
          //$("#mensajes").html("Nuevo Código Interno: "+resp);
          document.getElementById("txtBuscar").value=resp;
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
                text: 'Elija una Imagen',
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
        Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        $('#fotos_msje').html(error.responseText);
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
        }
          },
      error: function(error){
        Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
            $('#aplicaciones_msje').html(error.responseText);
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


    function limpiar_info(event)
    {
        if(event.target.id=="txtBuscar")
          document.getElementById("txtBuscar").focus();
        if(event.target.id=="txtBuscarCodProveedor")
          document.getElementById("txtBuscarCodProveedor").focus();
        document.getElementById("txtBuscar").value="";
        document.getElementById("txtBuscarCodProveedor").value="";
        document.getElementById("id_repuesto").value=0;
        document.getElementById("familia").value=0;
        document.getElementById("MarcaRepuesto").value=0;
        document.getElementById("Pais").value=0;
        document.getElementById("repuesto_activo").checked=false;
        document.getElementById("descripcion").value="";
        document.getElementById("observaciones").value="";
        document.getElementById("medidas").value="";
        document.getElementById("cod_repuesto_proveedor").value="";
        $("#proveedor").html("<strong>Proveedor:</strong>");
        document.getElementById("stock").value=0;
        document.getElementById("pu").value=0.00;
        document.getElementById("preciosug").value=0.00;
        document.getElementById("stock_minimo").value=0;
        document.getElementById("stock_maximo").value=0;
        document.getElementById("codigo_barras").value=0;
        document.getElementById("zona_ingreso_repuesto").disabled=true;
        document.getElementById("zona_fotos").disabled=true;
        $("#fotos_rep").html("");
        document.getElementById("zona_similares").disabled=true;
        $("#similares_rep").html("");
        document.getElementById("zona_OEMs").disabled=true;
        $("#oems_rep").html("");
        document.getElementById("zona_FABs").disabled=true;
        $("#fabs_rep").html("");
        $("#mensajes").html("&nbsp");
    }

    function press_enter1(e)
    {
      var keycode = e.keyCode;
      if(keycode=='13')
      {
        buscarRepuesto(1);
      }
    }

    function press_enter2(e)
    {
      var keycode = e.keyCode;
      if(keycode=='13')
      {
        buscarRepuesto(2);
      }
    }

    function buscarRepuesto(quien)
    {

      var codigo="";
      if(quien==1) //Codigo Interno
      {
        codigo=document.getElementById("txtBuscar").value.trim();
        if(codigo.length==0)
        {
            Vue.swal({
                text: 'Código Interno Vacio...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          return false;
        }
      }

      if(quien==2) //Codigo Proveedor
      {
        codigo=document.getElementById("txtBuscarCodProveedor").value.trim();
        if(codigo.length==0)
        {
            Vue.swal({
                text: 'Código Proveedor Vacío...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          return false;
        }
      }


      var url_buscar='{{url("repuesto/buscarcodigo")}}'+'/'+quien+codigo;

      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mensajes").html("Buscando "+codigo+" espere por favor...");

        espere("Buscando "+codigo);
      },
      url:url_buscar,
      success:function(resp){ //viene JSON si encuentra
        Vue.swal.close();
        
        if(resp=='-1')
        {
          $("#mensajes").html("No existe...");
          Vue.swal({
                text: 'Código '+codigo+' No existe...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }else{
         
          var repuesto=JSON.parse(resp[0]);
          console.log(repuesto[0]);
          var dias = resp[1];
          //Como es un único elemento, podemos leerlo como array anteponiendo el [0]
          document.getElementById("id_repuesto").value=repuesto[0].id;
          document.getElementById("familia").value=repuesto[0].id_familia;
          document.getElementById("MarcaRepuesto").value=repuesto[0].id_marca_repuesto;
          document.getElementById("Pais").value=repuesto[0].id_pais;

          if(repuesto[0].activo==1){
            document.getElementById("repuesto_activo").checked=true;
          } else{
            document.getElementById("repuesto_activo").checked=false;
          }

          let fecha_actualizacion = repuesto[0].updated_at;
          let nueva_fecha = fecha_actualizacion.split("T");
          let hora = nueva_fecha[1].split(".");
          document.getElementById("txtBuscar").value=repuesto[0].codigo_interno;
          $("#proveedor").html("<strong>Proveedor:</strong> "+repuesto[0].empresa_nombre);
          $("#ultimo_usuario").html("<strong>Autor:</strong> "+repuesto[0].name);
          
          // if(dias <= 30){
          //   $('#dias').html("<span class='bg_verde'>Hace "+dias+" dias</span>");
          // }else if(dias > 30 && dias <= 60){
          //   $('#dias').html("<span class='bg_amarillo'>Hace "+dias+" dias</span>");
          // }else{
          //   $('#dias').html("<span class='bg_rojo'>Hace "+dias+" dias</span>");
          // }
          
          document.getElementById("descripcion").value=repuesto[0].descripcion;
          if(repuesto[0].observaciones=="@@@"){
            document.getElementById("observaciones").value="";
          }else{
            document.getElementById("observaciones").value=repuesto[0].observaciones;
          }
          document.getElementById("medidas").value=repuesto[0].medidas;
          // guardar las medidas en el input de tipo hidden medidas_respaldo
          document.getElementById("medidas_respaldo").value=repuesto[0].medidas;
      
          document.getElementById("cod_repuesto_proveedor").value=repuesto[0].cod_repuesto_proveedor;
          document.getElementById("stock").value=repuesto[0].stock_actual;
          document.getElementById("pu").value=repuesto[0].precio_compra;
          document.getElementById("preciosug").value=repuesto[0].precio_venta;
          document.getElementById("stock_minimo").value=repuesto[0].stock_minimo;
          document.getElementById("stock_maximo").value=repuesto[0].stock_maximo;
          document.getElementById("codigo_barras").value=repuesto[0].codigo_barras;

          //cargar fotos, aplicaciones(similares), oems y cod fabricantes (también esta en ventas_principal.blade.php mas_detalle(id_repuesto))
          id_repuesto=repuesto[0].id;

          dame_fotos(id_repuesto);
          dame_similares(id_repuesto);
          dame_oems(id_repuesto);
          dame_fabricantes(id_repuesto);
          
          $("#mensajes").html("ID Rep: "+id_repuesto);

          //Activar zonas para poder editar
          document.getElementById("zona_ingreso_repuesto").disabled=false;
          document.getElementById("zona_fotos").disabled=false;
          document.getElementById("zona_similares").disabled=false;
          document.getElementById("zona_OEMs").disabled=false;
          document.getElementById("zona_FABs").disabled=false;

          let idf = document.getElementById("familia").value;
          if(idf == 206 || idf == 282){
            $('#rec_alt_rep').empty();
            $('#rec_alt_rep').append(`
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
            <input type="button" role="button" class='btn btn-success btn-sm mb-4' onclick="guardar_rec_alt()" value="Guardar datos" /> 
          </div>
          </div>
          `);
          dame_regulador_voltaje(id_repuesto);
          }else if(idf == 166){
            console.log('es un bendix');
            
            $('#bendix').empty();
        $('#result').empty();
        //MOSTRAMOS FORMULARIO
        $('#bendix').removeClass('d-none');
          $('#result').addClass('d-block');
          $('#bendix').append(`
          <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm d-none" maxlength="500" placeholder="500 caratéres máximo" style="width: 80%; float: left;" readonly></textarea>
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
          // hacer que el atributo disabled sea false del boton btn_motor
          document.getElementById("btn_motor").disabled=false;
          dame_motor_partida(id_repuesto);
          }

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

    function borrarmotor(id){
      var url = '/repuesto/borrarmotor/'+id;
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

    function dame_motor_partida(idrep){
      var url = '/repuesto/damemp/'+idrep;
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

    function guardar_rec_alt(){
      var idrep=document.getElementById("id_repuesto").value;
      var rectificador = document.getElementById("rectificador").value;
      var alternador = document.getElementById("alternador").value;
      
      alternador=alternador.replace(/-/g,"");
      alternador=alternador.toUpperCase();

      rectificador = rectificador.replace(/-/g,"");
      rectificador = rectificador.toUpperCase();

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

      if(rectificador.trim() == 0 || idrep == ''){
        rectificador = '---';
      }

      if(alternador.trim() == 0 || idrep == ''){
        alternador = '---';
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
          console.log(html);
          if(html == "existe"){
            return Vue.swal({
              icon:'info',
              text:'Ya existe un registro con esos datos ...',
              position:'top-end',
              timer: 3000,
              toast: true,
              showConfirmButton: false
            });
          }
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
      });
    }

    function dame_fotos(id_repuesto)
    {
        var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefotos_modificar';
        $.ajax({
        type:'GET',
        beforeSend: function () {
          $("#mensajes").html("Cargando fotos...");
            },
        url:url,
        success:function(fotos){
          $("#fotos_rep").html(fotos);
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

    function dame_oems(id_repuesto)
    {
      // var url='{{url("repuesto")}}'+'/'+id_repuesto+'/dameoems_modificar';
      //   $.ajax({
      //   type:'GET',
      //   beforeSend: function () {
      //     $("#mensajes").html("Cargando OEMs...");
      //       },
      //   url:url,
      //   success:function(oems){
      //     $("#oems_rep").html(oems);
      //   },
      //     error: function(error){
      //       $('#mensajes').html(error.responseText);
      //       Vue.swal({
      //           title: 'ERROR',
      //           text: error.responseText,
      //           icon: 'error',
      //       });
      //     }

      //   }); //Fin petición
      // hacer lo mismo pero con fetch
      var url = '/repuesto/'+id_repuesto+'/dameoems_modificar';
      fetch(url)
      .then(res => res.text())
      .then(data => {
        $('#oems_rep').html(data);
      })
      .catch(error => {
        console.log(error);
      });

    }

    function dame_similares(id_repuesto)
    {
        var url = '/repuesto/'+id_repuesto+'/damesimilares_modificar';
        fetch(url)
        .then(res => res.text())
        .then(data => {
          $('#similares_rep').html(data);
        })
        .catch(error => {
          console.log(error.responseText);
        });
    }

    function dame_fabricantes(id_repuesto)
    {
      // var url='{{url("repuesto")}}'+'/'+id_repuesto+'/damefabricantes_modificar';
      //   $.ajax({
      //   type:'GET',
      //   beforeSend: function () {
      //     $("#mensajes").html("Cargando Código Fabricantes...");
      //       },
      //   url:url,
      //   success:function(fabs){
      //     $("#fabs_rep").html(fabs);
      //   },
      //     error: function(error){
      //       $('#mensajes').html(error.responseText);
      //       Vue.swal({
      //           title: 'ERROR',
      //           text: error.responseText,
      //           icon: 'error',
      //       });
      //     }

      //   }); //Fin petición
      // hacer lo mismo pero con fetch
      var url = '/repuesto/'+id_repuesto+'/damefabricantes_modificar';
      fetch(url)
      .then(res => res.text())
      .then(data => {
        $('#fabs_rep').html(data);
      })
      .catch(error => {
        console.log(error);
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


//poner onload windows y cargar familias y demas combos
window.onload = function(e){

    cargar_familia(); //dentro de este método cargan los demás datos
   
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

    document.getElementById('txtBuscar').focus();
    
}

function eliminar_repuesto(){
  var id_repuesto=document.getElementById("id_repuesto").value;
  var url = '/repuesto/eliminar/'+id_repuesto;

  Vue.swal({
        title:'¿Estás seguro?',
        text:'El repuesto se eliminará del sistema, sin poder recuperarlo',
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Eliminar!'
      }).then(result => {
        if(result.isConfirmed){
          $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
              Vue.swal({
                icon:'info',
                title:'Eliminando ...'
              })
            },
            success: function(resp){
              Vue.swal.close();
              if(resp == 'OK'){
                Vue.swal({
                  icon:'success',
                  title:'Exito',
                  text:'Repuesto eliminado con éxito'
                });
              }
            },
            error: function(err){
              Vue.swal({
                icon:'error',
                text: err.responseText,

              });
            }
            });
          }
        });
      }
    
  function cambiarProveedor(){
    let url = '/proveedor/dameproveedores_array';
    $.ajax({
      type:'get',
      url: url,
      beforeSend: function(){
            $('#modal_body_proveedores').empty();
            $('#modal_body_proveedores').append('CARGANDO ...');
      },
      success: function(resp){
       
            $('#modal_body_proveedores').empty();
            $('#modal_body_proveedores').append(resp);
         
        
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
  }

  function guardarProveedor(){
    let idproveedor = $('#idproveedor').val();
    let idrepuesto = $('#id_repuesto').val();
    let url = '/proveedor/'+idrepuesto+'/guardarproveedor/'+idproveedor;
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
        
        if(resp[0] == 'OK'){
          Vue.swal({
            icon:'success',
            text:'Cambio de proveedor exitoso'
          });
        }
        $('#proveedor').empty();
        $('#proveedor').append('<strong>Proveedor:</strong> '+resp[1].empresa_nombre);
        
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
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
        dame_medidas(familia);
        $('#verMedidasModal').modal('hide');
        //espere('EN CONSTRUCCION');
      },
      error: function(error){
        console.log(error.responseText);
      }
    });
    
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

.margen-0{
    margin-left:0px;
    margin-right: 0px;
}

.bg_verde{
  background: green;
}

.bg_amarillo{
  background: yellow;
}

.bg_rojo{
  background: red;
  color: white;
}
</style>
@endsection
@section('contenido_titulo_pagina')
<div class="container-fluid">
<div class="row">
    <div class="col-4 col-offset-4">
        <center><h4>MODIFICAR REPUESTO</h4></center>
    </div>
    <div class="col-4" style="text-align: right;">
    </div>
</div>
</div>
@endsection
@section('contenido_ingresa_datos')

  @include('fragm.mensajes')

    <!-- CAMPOS OCULTOS  -->
    <input type="hidden" id="id_repuesto">
    <input type="hidden" id="elegir" value="NO">
    <input type="hidden" name="medidas_respaldo" id="medidas_respaldo" value="">
    <!-- FIN DE CAMPOS OCULTOS -->

<div class="container-fluid">
  
<div id="mensajes"></div>
<div id="buscar_repuesto">
    <div class="row">
        <div class="col-2">
            <input type="text" class="form-control form-control-sm" id="txtBuscar" placeholder="Ingrese Código Interno" onkeyup="press_enter1(event)" onclick="limpiar_info(event)" value=@php echo $cr;@endphp>
        </div>
        <div class="col-2">
            <input type="submit" id="btnBuscarRepuesto" onclick="buscarRepuesto(1)" name="btnBuscarRepuesto" value="<== Buscar" class="btn btn-warning btn-sm" style="margin-top: 2px"/>
        </div>
        <div class="col-2">
          <input type="text" class="form-control form-control-sm" id="txtBuscarCodProveedor" placeholder="Ingrese Código Proveedor" onkeyup="press_enter2(event)" onclick="limpiar_info(event)">
        </div>
        <div class="col-2">
            <input type="submit" id="btnBuscarRepuestoCodProveedor" onclick="buscarRepuesto(2)" name="btnBuscarRepuestoCodProveedor" value="<== Buscar" class="btn btn-success btn-sm" style="margin-top: 2px"/>
        </div>
      </div>
</div>
<fieldset id="zona_ingreso_repuesto" disabled>
        <div class="row" style="background-color: #F2F5A9;">
          <div class="row">

            <div class="col-3">

              <table>
                <tr><td>
                    <label for="familia">Familia:</label>
              <select name="cboFamilia" class="form-control form-control-sm" id="familia" onchange="dame_medidas(this.value)">
                  <option value="">Sin Familias</option>
              </select>
            </td>
              <td style="vertical-align: bottom"><button class="btn btn-success btn-sm" onclick="agregar_familia()">+</button></td>
            </tr>
            </table>
            </div>

           <div class="col-3">
                <table>
                    <tr><td>
              <label for="MarcaRepuesto">Marca:</label>
              <select name="cboMarcaRepuesto" class="form-control form-control-sm" id="MarcaRepuesto" onchange="pasar_a_FAB();">
                  <option value="">Elija Marca de Repuesto</option>
              </select>
            </td>
            <td style="vertical-align: bottom"><button class="btn btn-success btn-sm" onclick="agregar_marca_repuesto()">+</button></td>
          </tr>
          </table>
            </div>

            <div class="col-3">
                <table>
                    <tr><td>
            <label for="Pais">Origen:</label>
              <select name="cboPais" class="form-control form-control-sm" id="Pais">
                  <option value="">Elija País de Origen</option>
              </select>
            </td>
            <td style="vertical-align: bottom"><button class="btn btn-success btn-sm" onclick="agregar_pais()">+</button></td>
          </tr>
          </table>
            </div>

            <div class="col-2" style="margin-top:20px">
                <input type="checkbox" name="" id="repuesto_activo">
                <label for="repuesto_activo">Activo:</label>
                <button class="btn btn-sm btn-danger" value="Eliminar" onclick="eliminar_repuesto()">Eliminar</button>
            </div>

          </div>

          <div class="row" style="margin-top:10px; width: 100%;">
            <div class="col-3">
              <label for="descripcion">Descripción:</label>
              <input type="text" name="descripcion" value="{{old('descripcion')}}" id="descripcion" class="form-control form-control-sm" maxlength="100" placeholder="100 caratéres máximo">
            </div>
            <div class="col-3">
                <label for="observaciones">Observaciones:</label>
                <textarea name="observaciones" value="" wrap="hard" cols="11" rows="2" id="observaciones" class="form-control form-control-sm" maxlength="400" placeholder="400 caratéres máximo"></textarea>
            </div>
            <div class="col-2">
              <span id="span_medidas">

                <label for="medidas">Medidas:</label>

                <textarea name="medidas" value="" wrap="hard" cols="11" rows="3" id="medidas" class="form-control form-control-sm" maxlength="500" placeholder="500 caratéres máximo"></textarea>
                <button class="btn btn-success btn-sm" onclick="dame_medidas()">Agregar medidas</button>
            </span>
            </div>
            <div class="col-2">
              <label for="cod_repuesto_proveedor"><small>Cód. Repuesto: <i style="color:blue">Puede ingresar varios</i></small></label>
              <textarea name="cod_repuesto_proveedor" maxlength="2000" wrap="hard" value="" id="cod_repuesto_proveedor" cols="11" rows="2" placeholder="Es el código interno del proveedor" class="form-control form-control-sm"></textarea>
            </div>
            <div class="col-2">
              <p id="proveedor"><strong>Proveedor:</strong></p>
              <p id="ultimo_usuario"><strong>Autor:</strong></p>
              <p id="fecha_actualizacion"><strong>Fecha de actualización:</strong> </p>
              <p>{{$fecha_actualizacion}}</p>
              <p id="dias"></p>
            </div>

          </div> <!--FIN DEL CUARTO ROW COL 1 -->

          <div class="row" style="margin-top:10px; width: 100%;">
            <div class="col-1">
                <label for="stock">Stock:</label>
                <input type="text" name="cantidad" value="0"  id="stock" class="form-control form-control-sm" disabled>
              </div>
              <div class="col-2">
                <label for="pu">Precio Compra:</label>
                <input type="text" name="pu" value="0.00"  id="pu" class="form-control form-control-sm">
              </div>
              <div class="col-1" style="padding-left:5px;padding-right:5px">
                <label for="preciosug">Precio Venta</label>
                <input type="text" name="preciosug" value="0.00"  id="preciosug" class="form-control form-control-sm">
              </div>
            <div class="col-2">
              <label for="stock_minimo">Stock Mínimo:</label>
              <input type="text" name="stock_minimo"  id="stock_minimo" class="form-control form-control-sm">
            </div>
            <div class="col-2">
              <label for="stock_maximo">Stock Máximo:</label>
              <input type="text" name="stock_maximo" value="0" id="stock_maximo" class="form-control form-control-sm">
            </div>
            <div class="col-2">
              <label for="codigo_barras">Código de Barras:</label>
              <input type="text" name="codigo_barras" value="0" id="codigo_barras" class="form-control form-control-sm">
            </div>
              <div class="col-1" style="padding-left:5px;padding-right:5px">
                <button class="btn btn-success btn-sm" style="margin-top:10px;" id="btnGuardarDatos" onclick="guardarDatos()">Guardar Datos</button>
                
              </div>
              <div class="col-1" style="padding-left:5px;padding-right:5px">
                <button class="btn btn-primary btn-sm h-auto" style="margin-top:10px;" id="btnEditarProveedor" data-toggle="modal" data-target="#cambiarProveedorModal" onclick="cambiarProveedor()">Cambiar Proveedor</button>
              </div>
          </div> <!--FIN INGRESAR DATOS -->



        </div>
</fieldset>
</div>



<div class="container-fluid">
<div class="row" style="margin-top:10px"> <!-- FILA GENERAL PARA LAS 4 COLUMNAS: FOTOS, SIMILARES, OEMs y FABRICANTES-->

  <div class="col-3" style="background-color: rgb(218, 250, 250);padding-left:3px;padding-right:2px"> <!-- zona_fotos INICIO-->
    <fieldset id="zona_fotos" disabled>
          <div class="row margen-0" style="margin-top:10px">
            <p id="fotos_msje"><b>FOTOS:</b></p>
            <div class="col-12">
              <label>Subir Foto (jpg,jpeg,png):</label>
              <input type="file" name="archivo" id="archivo" class="form-control-file">
            </div>
          </div>

          <div class="row margen-0" style="margin-top:10px">
            <div class="col-4">
              <input type="submit" name="btnGuardarFoto" id="btnGuardarFoto" value="Agregar Foto" class="btn btn-primary btn-sm form-control-sm" onclick="guardarfoto()"/>
            </div>
          </div>

          <div class="row margen-0" id="fotos_rep" style="margin-top:10px">

          </div>
    </fieldset>
  </div> <!-- zona_fotos FIN -->


  <div class="col-5" id="aplicacionez" style="padding-left:2px;padding-right:2px"> <!-- zona_similares APLICACIONES INICIO-->
    <fieldset id="zona_similares" disabled>
            <div class="row" style="margin-left:0px;margin-right:0px">
                <div class="col-10">
                    <p id="aplicaciones_msje"><b>APLICACIONES:</b></p>
                </div>
                <div class="col-2" style="text-align:right"><button id="ampliar" class="btn btn-sm btn-success" onclick="ampliar()">>></button></div>
            </div>
            <div class="row margen-0">
                <div class="col-4" style="padding-left:2px;padding-right:2px">
                <label for="MarcaSim">Marca:</label>
                <select name="cboMarcaSim" class="form-control form-control-sm" id="MarcaSim" onchange="cargarModelosSimilares()" size="10">
                    <option value="">Elija una marca</option>
                    @foreach ($marcas as $marca)
                    <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
                    @endforeach
                </select>

                </div>
                <div class="col-8" style="padding-left:2px;padding-right:2px">
                <label for="modelo">Modelo:</label>
                <input type="text" class="form-control form-control-sm" id="filtrar_modelo" placeholder="filtrar modelo" style="width:40%">
                <select name="cboModeloSim" id="ModeloSim" class="form-control form-control-sm" onchange="ubicarse_en_anios()" size="10">
                    <option value="">Sin modelos</option>
                </select>
                </div>
            </div>


          <div class="row margen-0" style="margin-top:10px">
              <div class="col-12">
                  <p id="nombre_modelo"><b>Modelo:</b></p>
              </div>
          </div>

          <div class="row margen-0" style="margin-top:10px">
            <div class="col-3">
              <label for="anios_vehiculo">Años:</label>
              <input type="text" name="anios_vehiculo_sim" value="" id="anios_vehiculo_sim" class="form-control form-control-sm">
            </div>

            <div class="col-6">
              <input type="submit" name="btnGuardarSimilar" id="btnGuardarSimilar" value="Agregar Aplicación" class="btn btn-primary btn-sm form-control-sm" onclick="guardarsimilar()" style="margin-top: 20px"/>
            </div>
          </div>
          <div class="row margen-0" id="similares_rep" style="margin-top: 15px">
              <!--zona para relacionados -->
          </div>
    </fieldset>
  </div> <!-- zona_similares APLICACIONES FIN-->

  <div class="col-2" id="OEMz" style="background-color: rgb(218, 250, 250);padding-left:2px;padding-right:2px"> <!-- zona_OEMs INICIO-->
    <fieldset id="zona_OEMs" disabled>
      <div class="row margen-0" style="margin-top: 10px">
        <div class="row margen-0"><p id="oems_msje"><b>OEMs:</b></p></div>
        <div class="row margen-0">
            <div class="col-6" style="padding-left:2px;padding-right:2px">
            <label for="codigos_OEM">Códigos:</label>
            <input type="text" name="codigos_OEM" id="codigos_OEM" class="form-control form-control-sm" onkeyup="enter_text_oem(event)">
            </div>
            <div class="col-4">
            <input type="submit" name="btnGuardarOEM" id="btnGuardarOEM" value="Agregar OEM" class="btn btn-primary btn-sm form-control-sm" onclick="guardarOEM()" style="margin-top: 20px"/>
            </div>
        </div>
      </div>

      <div class="row margen-0" id="oems_rep" style="margin-top: 15px;padding-left:2px;padding-right:2px">

      </div>
      <div id="rec_alt_rep">

      </div>
      <div id="bendix"></div>
      <div id="result">

      </div>
    </fieldset>
  </div> <!-- zona_OEMs FIN-->

  <div class="col-2" id="FABz" style="padding-left:2px;padding-right:2px"> <!-- zona_FABs INICIO-->
    <fieldset id="zona_FABs" disabled>
      <div class="row margen-0" style="margin-top: 10px">
            <p id="fabricantes_msje" class="w-100"><b>FABRICANTES:</b></p>
            <div class="col-6" style="padding-left:2px;padding-right:2px">
              <label for="fabricante">Elegir:</label>
              <select name="cboFabricante" id="cboFabricante" class="form-control form-control-sm">
                <option value="">Sin Fabricantes</option>
              </select>
            </div>
            <div class="col-6" style="padding-left:2px;padding-right:2px">
              <label for="codigo_FAB">Código:</label>
              <input type="text" name="codigo_FAB" id="codigo_FAB" class="form-control form-control-sm" onkeyup="enter_text_FAB(event)">
            </div>
      </div>
      <div class="row margen-0" style="margin-left:0px;margin-right:0px">
        <div class="col-10" >
          <input type="submit" name="btnGuardarFAB" id="btnGuardarFAB" value="Agregar FAB" class="btn btn-primary btn-sm form-control-sm" onclick="guardarFAB()" style="margin-top: 20px"/>
        </div>
      </div>
      <div class="row margen-0" id="fabs_rep" style="margin-top: 15px">

      </div>
    </fieldset>
  </div> <!-- zona_FABs FIN-->

</div> <!-- FIN FILA GENERAL PARA LAS 3COLUMNAS: FOTOS, SIMILARES Y OEMs -->



</div> <!--FIN container fluid -->

    @endsection

    @section('contenido_ver_datos')

<!-- VENTANA MODAL AGREGAR FAMILIA"-->
<div role="dialog" tabindex="-1" class="modal fade" id="agregar-familia-modal">
    <div class="modal-dialog" role="document" >
      <div class="modal-content">
          <div class="modal-header"> <!-- CABECERA -->
            <h3 class="text-center modal-title">AGREGAR FAMILIA</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           </div> <!-- FIN CABECERA -->
        <div class="modal-body"> <!-- CONTENIDO -->

          <div class="row">
              <input type="hidden" name="donde" value="factuprodu">
              <div class="row">
                <div class="col-4">
                  <label for="nombrefamilia">Nombre de la Familia:</label>
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
          <div class="modal-header"> <!-- CABECERA -->
            <h3 class="text-center modal-title">AGREGAR MARCA REPUESTO</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           </div> <!-- FIN CABECERA -->
        <div class="modal-body"> <!-- CONTENIDO -->

          <div class="row">
              <div class="col-6  col-md-6 col-lg-6">
                <label for="marcarepuesto">Marca del Repuesto:</label>
                  <input type="text" name="marcarepuesto" id="marcarepuesto" size="20" maxlength="20" value="" class="form-control" style="width:100%">
              </div>

              <div class="col-6  col-md-6 col-lg-6">
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
          <div class="modal-header"> <!-- CABECERA -->
            <h3 class="text-center modal-title">AGREGAR PAIS ORIGEN</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

           </div> <!-- FIN CABECERA -->
        <div class="modal-body"> <!-- CONTENIDO -->

          <div class="row">
            <div class="col-6  col-md-6 col-lg-6">
              <label for="pais">Nombre del País de Origen:</label>
                <input type="text" name="pais" id="pais" size="20" maxlength="20" value="{{old('pais')}}" class="form-control" style="width:100%">
            </div>
            <div class="col-6  col-md-6 col-lg-6">
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
<div class="modal fade" id="cambiarProveedorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_proveedores">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="guardarProveedor()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<input type="hidden" name="utilidad" id="utilidad" value="">
  @endsection
