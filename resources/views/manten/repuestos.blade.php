@extends('plantillas.app')
  @section('titulo','Listar Repuestos')
  @section('javascript')
    <script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <script type="text/javascript">

    // Variables globales compartidas entre la búsqueda AJAX y los modales
    var filtrosActuales = {
      id_proveedor: 0,
      id_marca: 0,
      id_marca_vehiculo: 0,
      id_familia: 0,
      texto: ''
    };
    var paginaActual = 1;
    var idRepuestoModificando = null;
    var precioActualModificando = 0;
    var timeoutBusquedaTexto = null;

    // Función para cargar repuestos (disponible en todo el script)
    function cargarRepuestos(idProveedor, idMarca, idMarcaVehiculo, idFamilia, page, texto) {
        page = page || 1;
        paginaActual = page;

        filtrosActuales.id_proveedor = idProveedor || 0;
        filtrosActuales.id_marca = idMarca || 0;
        filtrosActuales.id_marca_vehiculo = idMarcaVehiculo || 0;
        filtrosActuales.id_familia = idFamilia || 0;
        filtrosActuales.texto = texto || '';

        var url = "{{url('repuesto/buscar_filtros')}}";

        var parametros = {
          id_proveedor: filtrosActuales.id_proveedor,
          id_marca: filtrosActuales.id_marca,
          id_marca_vehiculo: filtrosActuales.id_marca_vehiculo,
          id_familia: filtrosActuales.id_familia,
          texto: filtrosActuales.texto,
          page: page
        };

        $.ajax({
               type:'GET',
               cache:false, // evita que el navegador sirva un HTML viejo en caché
               beforeSend: function () {
                $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show'><i class='fa fa-spinner fa-spin'></i> Buscando repuestos, espere...</div>");
                $("#mostrar_repuestos").html("<div style='text-align:center; padding: 40px;'><i class='fa fa-spinner fa-spin fa-3x' style='color: #667eea;'></i></div>");
                $('html, body').animate({ scrollTop: $("#mostrar_repuestos").offset().top - 100 }, 300);
                  },
               url:url,
               data:parametros,
               success:function(resp){
                $("#mensajes").html("<div class='alert alert-success alert-dismissible fade show'><i class='fa fa-check-circle'></i> Búsqueda completada</div>");
                $("#mostrar_repuestos").html(resp);
               },
                error: function(error){
                    $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show'><i class='fa fa-exclamation-circle'></i> Error en la búsqueda</div>");
                    $('#mostrar_repuestos').html("<div class='alert alert-danger' style='margin-top: 20px;'><strong>Error:</strong> "+error.responseText+"</div>");
                    console.log(error.responseText);
                }

        });
    }

    function modificar_precio(idrep)
    {

    }

    // Abre el modal de modificar precio. Recibe el id del repuesto
    // y el precio actual (viene como data-attribute del botón, ya que
    // el listado ya no usa inputs ocultos por fila).
    function abrir_modal_modificar_precio(idrep, precioActual)
    {
        idRepuestoModificando = idrep;
        precioActualModificando = parseInt(precioActual) || 0;

        document.getElementById("precio_actual_mostrar").innerText = "$ " + precioActualModificando.toLocaleString('es-CL');
        document.getElementById("precio_modificado").value = precioActualModificando;
        $("#mensajes-modal-precio").html("");
        $("#modificar-precio-modal").modal("show");
    }

    // Guarda el nuevo precio usando el endpoint ya existente
    // repuesto/guardar_precio_venta/{id}&{nuevo}&{antiguo}
    function guardar_nuevo_precio()
    {
        if(!idRepuestoModificando){
            return;
        }

        var nuevoPrecio = parseInt(String(document.getElementById("precio_modificado").value).replace(/\D/g,'')) || 0;
        var antiguoPrecio = precioActualModificando;

        if(nuevoPrecio <= 0){
            $("#mensajes-modal-precio").html("<span style='color:#e74c3c; font-weight:700;'>Ingresa un precio válido.</span>");
            return;
        }

        if(nuevoPrecio === antiguoPrecio){
            $("#modificar-precio-modal").modal("hide");
            return;
        }

        if(nuevoPrecio < antiguoPrecio){
            if(!confirm("El nuevo precio ($ "+nuevoPrecio.toLocaleString('es-CL')+") es menor al actual ($ "+antiguoPrecio.toLocaleString('es-CL')+"). ¿Deseas continuar?")){
                return;
            }
        }

        var dato = idRepuestoModificando + "&" + nuevoPrecio + "&" + antiguoPrecio;
        var url = "{{url('repuesto/guardar_precio_venta')}}" + "/" + dato;

        $.ajax({
            type: 'GET',
            cache:false,
            beforeSend: function(){
                $("#mensajes-modal-precio").html("<i class='fa fa-spinner fa-spin'></i> Guardando...");
            },
            url: url,
            success: function(resp){
                if(resp && resp[0] && resp[0] !== 'XUXA'){
                    $("#mensajes-modal-precio").html("");
                    $("#modificar-precio-modal").modal("hide");
                    $("#mensajes").html("<div class='alert alert-success alert-dismissible fade show'><i class='fa fa-check-circle'></i> Precio actualizado a $ "+resp[0]+"</div>");
                    cargarRepuestos(filtrosActuales.id_proveedor, filtrosActuales.id_marca, filtrosActuales.id_marca_vehiculo, filtrosActuales.id_familia, paginaActual, filtrosActuales.texto);
                }else{
                    $("#mensajes-modal-precio").html("<span style='color:#e74c3c; font-weight:700;'>No tienes permisos para bajar este precio.</span>");
                }
            },
            error: function(error){
                $("#mensajes-modal-precio").html("<span style='color:#e74c3c; font-weight:700;'>Error al guardar el precio.</span>");
                console.log(error.responseText);
            }
        });
    }

    function abrir_foto_modal(id_repuesto)
    {
      var url='{{url("repuesto/dame_fotos_repuesto")}}'+'/'+id_repuesto;
      $("#aqui_foto").html("<div style='text-align:center; padding: 30px;'><i class='fa fa-spinner fa-spin fa-2x'></i></div>");
      $("#foto-modal").modal("show");
      $.ajax({
        type:'GET',
        cache:false,
        url:url,
        success:function(fotos){
          $("#aqui_foto").html(fotos);
        },
        error: function(error){
          $("#aqui_foto").html("<div class='text-danger text-center'>No fue posible cargar la imagen.</div>");
        }
      });
    }

    function abrir_modal(ide)
    {

      var url_repuesto='{{url("repuesto")}}'+'/'+ide+'/damerepuesto'; //petición

      $.ajax({
       type:'GET',
       cache:false,
       beforeSend: function () {
        $("#datos").html("Obteniendo datos...");
          },
       url:url_repuesto,
       success:function(datos){
        $('#datos').html(datos); // coloca el view renderizado en el controlador
        var tituloEl = document.getElementById('titulo_detalle');
        if(tituloEl){
          $('#mod_titulo_header').html(tituloEl.value);
        }else{
          $('#mod_titulo_header').html('Detalle del Repuesto');
        }
       },
        error: function(error){
            $('#datos').html(error.responseText);
        }

        }); //Fin ajax repuestos

      //Traer SIMILARES
      var url_similares='{{url("repuesto")}}'+'/'+ide+'/damesimilares';
      $.ajax({
       type:'GET',
       cache:false,
       beforeSend: function () {
        $("#similares").html("Obteniendo Aplicaciones...");
          },
       url:url_similares,
       success:function(similares){
        $("#similares").html(similares);
       },
        error: function(error){
            $('#similares').html(error.responseText);
        }

        }); //Fin ajax similares

      //Traer FOTOS
      var url_fotos='{{url("repuesto")}}'+'/'+ide+'/damefotos';
      $.ajax({
       type:'GET',
       cache:false,
       beforeSend: function () {
        $("#fotos").html("Obteniendo Fotos...");
          },
       url:url_fotos,
       success:function(fotos){
        $("#fotos").html(fotos);
       },
        error: function(error){
            $('#fotos').html(error.responseText);
        }

      }); //Fin ajax fotos

      //Abrir el modal

      $("#detalle-modal").modal("toggle");
    } //Fin funcion abrir_modal

    $(document).ready(function()
    {
      // Delegación de eventos para la paginación: se registra UNA sola vez
      // sobre el contenedor fijo #mostrar_repuestos (su contenido interno
      // cambia con cada búsqueda, pero el contenedor en sí nunca se destruye)
      $('#mostrar_repuestos').on('click', 'a.pagination-link', function(e){
        e.preventDefault();
        var pageNumber = $(this).data('page');
        if(pageNumber){
          cargarRepuestos(filtrosActuales.id_proveedor, filtrosActuales.id_marca, filtrosActuales.id_marca_vehiculo, filtrosActuales.id_familia, pageNumber, filtrosActuales.texto);
        }
      });

      // Cargar repuestos al iniciar la página
      cargarRepuestos(0, 0, 0, 0, 1, '');

      // Evento del botón Buscar
      $("#btnBuscar").click(function(e){
        e.preventDefault();
        var idProveedor = document.getElementById("proveedor").value;
        var idMarca = document.getElementById("marca").value;
        var idMarcaVehiculo = document.getElementById("marca_vehiculo").value;
        var idFamilia = document.getElementById("familia").value;
        var texto = document.getElementById("buscar_texto").value;

        cargarRepuestos(idProveedor, idMarca, idMarcaVehiculo, idFamilia, 1, texto);
      }); // fin btnBuscar

      // Búsqueda en vivo estilo DataTable: al escribir en el campo de texto,
      // espera 400ms sin teclear y dispara la búsqueda automáticamente.
      $("#buscar_texto").on('keyup', function(e){
        clearTimeout(timeoutBusquedaTexto);
        var texto = this.value;
        if(e.key === 'Enter'){
          var idProveedor = document.getElementById("proveedor").value;
          var idMarca = document.getElementById("marca").value;
          var idMarcaVehiculo = document.getElementById("marca_vehiculo").value;
          var idFamilia = document.getElementById("familia").value;
          cargarRepuestos(idProveedor, idMarca, idMarcaVehiculo, idFamilia, 1, texto);
          return;
        }
        timeoutBusquedaTexto = setTimeout(function(){
          var idProveedor = document.getElementById("proveedor").value;
          var idMarca = document.getElementById("marca").value;
          var idMarcaVehiculo = document.getElementById("marca_vehiculo").value;
          var idFamilia = document.getElementById("familia").value;
          cargarRepuestos(idProveedor, idMarca, idMarcaVehiculo, idFamilia, 1, texto);
        }, 400);
      }); // fin buscar_texto

      // Evento del botón Limpiar Filtros
      $("#btnLimpiarFiltros").click(function(e){
        e.preventDefault();
        // Resetear todos los inputs
        document.getElementById("proveedor").value = 0;
        document.getElementById("marca").value = 0;
        document.getElementById("marca_vehiculo").value = 0;
        document.getElementById("familia").value = 0;
        document.getElementById("buscar_texto").value = '';

        // Mostrar mensaje
        $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show'><i class='fa fa-check-circle'></i> Filtros limpios - Mostrando todos los repuestos</div>");

        // Cargar todos los repuestos (sin filtros)
        cargarRepuestos(0, 0, 0, 0, 1, '');
      }); // fin btnLimpiarFiltros

      $("#btnDescargarExcel").click(function(e){
        e.preventDefault();
        var idProveedor = document.getElementById("proveedor").value;
        var idMarca = document.getElementById("marca").value;
        var idMarcaVehiculo = document.getElementById("marca_vehiculo").value;
        var idFamilia = document.getElementById("familia").value;
        var texto = document.getElementById("buscar_texto").value;

        // Construir URL con parámetros
        var url = "{{url('reportes/descargar_repuestos_filtrados')}}?id_proveedor=" + idProveedor +
                  "&id_marca=" + idMarca + "&id_marca_vehiculo=" + idMarcaVehiculo + "&id_familia=" + idFamilia + "&texto=" + encodeURIComponent(texto);

        window.location.href = url;
      }); // fin btnDescargarExcel

      $("#btnBuscarProveedor").click(function(e){
        e.preventDefault();
        var id_prov=document.getElementById("proveedor").value;
        var url='{{url("repuesto/proveedor")}}'+"/"+id_prov;
        console.log(url);
        location.href=url;
      }); // fin btnBuscarProveedor





    }); // fin document


      //Eliminar registro
        function confirmacion(){
          if (confirm('Esta seguro de eliminar el registro?')==true) {
            //alert('El registro ha sido  eliminado  correctamente!!!');
            return true;
          }else{
            //alert('Cancelo la eliminacion');
            return false;
          }
        }

        function excel(){
          alert('PRONTO');
        }
    </script>

  @endsection
  @section('style')
  <style>
  /* Tamaño del modal MAS DETALLE en respuestos.blade.php */

  .modal-header {
      background-color: #37474f;
      color: white;
      height: auto;
      padding: 18px 25px;
  }

  .modal-dialog,
  .modal-content {
      /* 95% of window height */
      height: 95%;
  }

  .modal-content {
      border-radius: 12px;
      overflow: hidden;
      border: none;
  }

  .modal-body {
      /* 100% = dialog height, 120px = header + footer */
      max-height: calc(100% - 120px);
      overflow-y: auto;
      background: #f4f6f8;
      padding: 0;
  }

  .modal-footer {
      background-color: #f4f6f8;
      border-top: 1px solid #e0e4e8;
      height: auto;
      padding: 12px 25px;
  }

  /* Pills de navegación dentro del modal de detalle */
  .detalle-repuesto-pills {
    display: flex;
    flex-wrap: wrap;
    background: #ffffff;
    border-bottom: 1px solid #e0e4e8;
    margin: 0;
    padding: 10px 20px 0 20px;
  }

  .detalle-repuesto-pills .nav-item {
    margin-right: 4px;
  }

  .detalle-repuesto-pills .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    color: #78909c;
    font-weight: 600;
    font-size: 13.5px;
    padding: 12px 20px;
    transition: background-color 0.15s ease, color 0.15s ease;
  }

  .detalle-repuesto-pills .nav-link i {
    margin-right: 6px;
  }

  .detalle-repuesto-pills .nav-link:hover {
    background-color: #eceff1;
    color: #37474f;
  }

  .detalle-repuesto-pills .nav-link.active {
    background-color: #37474f;
    color: #ffffff;
  }

  /* Bloque de datos del repuesto dentro del modal de detalle */
  .detalle-repuesto-datos {
    background: #ffffff;
    padding: 20px 25px;
  }

  .detalle-repuesto-datos table.table th {
    width: 42%;
    color: #546e7a;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-top: 1px solid #eceff1;
    vertical-align: middle;
  }

  .detalle-repuesto-datos table.table td {
    color: #263238;
    font-size: 14px;
    border-top: 1px solid #eceff1;
    vertical-align: middle;
  }

  .detalle-repuesto-datos table.table tr:first-child th,
  .detalle-repuesto-datos table.table tr:first-child td {
    border-top: none;
  }

  /* Bloque de fotos y aplicaciones (similares) */
  .detalle-repuesto-extra {
    background: #eceff1;
    padding: 20px 25px;
    border-top: 6px solid #cfd8dc;
  }

  svg{
    width: 100px;
  }
</style>
  @endsection
  @section('contenido_titulo_pagina')
<h4 class="titulazo">Catálogo de Repuestos</h4><br>
@endsection
  @section('contenido_ingresa_datos')
   @include('fragm.mensajes')
  @endsection

  @section('contenido_ver_datos')
  <div class="container-fluid" style="padding: 0; background: linear-gradient(135deg, #f5f7fa 0%, #e0e6f6 100%); margin-top: -15px; padding-top: 30px; padding-bottom: 30px; min-height: 100vh;">

    <!-- FILTROS SECTION -->
    <div class="container-lg" style="max-width: 1400px;">
      <div class="row mb-4">
        <div class="col-12">
          <div style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); padding: 30px; border-left: 5px solid #667eea;">
            <h5 style="font-weight: 700; margin-bottom: 20px; color: #2c3e50;"><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>

            <div class="row">
              <div class="col-lg-3 col-md-6 mb-3">
                <label for="proveedor" style="font-weight: 700; color: #2c3e50; font-size: 13px;">Proveedor:</label>
                <select name="proveedor" class="form-control form-control-sm" id="proveedor" style="border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s; height: 40px;">
                  <option value="0">- Todos los Proveedores -</option>
                  @foreach ($proveedores as $proveedor)
                    @if(isset($id_prov) && $proveedor->id==$id_prov)
                      <option value="{{$proveedor->id}}" selected>{{$proveedor->empresa_nombre}}</option>
                    @else
                      <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre}}</option>
                    @endif
                  @endforeach
                </select>
              </div>

              <div class="col-lg-3 col-md-6 mb-3">
                <label for="marca" style="font-weight: 700; color: #2c3e50; font-size: 13px;">Marca:</label>
                <select name="marca" class="form-control form-control-sm" id="marca" style="border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s; height: 40px;">
                  <option value="0">- Todas las Marcas -</option>
                  @foreach ($marcarepuestos as $marca)
                    <option value="{{$marca->id}}">{{strtoupper($marca->marcarepuesto)}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-3 col-md-6 mb-3">
                <label for="marca_vehiculo" style="font-weight: 700; color: #2c3e50; font-size: 13px;">Marca Vehículo:</label>
                <select name="marca_vehiculo" class="form-control form-control-sm" id="marca_vehiculo" style="border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s; height: 40px;">
                  <option value="0">- Todas las Marcas de Vehículo -</option>
                  @foreach ($marcasvehiculo as $mv)
                    <option value="{{$mv->idmarcavehiculo}}">{{strtoupper($mv->marcanombre)}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-3 col-md-6 mb-3">
                <label for="familia" style="font-weight: 700; color: #2c3e50; font-size: 13px;">Familia:</label>
                <select name="cFa" class="form-control form-control-sm" id="familia" style="border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s; height: 40px;">
                  <option value="0">- Todas las Familias -</option>
                  @foreach ($familias as $familia)
                    <option value="{{$familia->id_familia}}">{{strtoupper($familia->nombrefamilia)}} ({{$familia->total}})</option>
                  @endforeach
                </select>
              </div>

            </div>

            <div class="row mt-3">
              <div class="col-12" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button id="btnBuscar" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; padding: 10px 30px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);"><i class="fa fa-search"></i> Buscar</button>
                <button id="btnLimpiarFiltros" class="btn btn-secondary" style="border-radius: 8px; font-weight: 700; padding: 10px 30px; box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);"><i class="fa fa-times"></i> Limpiar Filtros</button>
                <button id="btnDescargarExcel" class="btn btn-success" style="border-radius: 8px; font-weight: 700; padding: 10px 30px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);"><i class="fa fa-file-excel"></i> Descargar Excel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MENSAJES -->
      <div id="mensajes" class="row mb-3">
      </div>

      <!-- RESULTADOS SECTION -->
      <div class="row mb-4">
        <div class="col-12">
          <div style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); padding: 18px 25px; margin-bottom: 15px; border-left: 5px solid #11998e;">
            <label for="buscar_texto" style="font-weight: 700; color: #2c3e50; font-size: 13px; margin-bottom: 8px; display: block;"><i class="fa fa-search"></i> Buscar dentro de los resultados:</label>
            <input type="text" id="buscar_texto" class="form-control form-control-sm" placeholder="Buscar por código interno, descripción o código de proveedor..." style="border-radius: 8px; border: 2px solid #e0e0e0; height: 40px;">
            <small style="color: #95a5a6;">Esta búsqueda filtra únicamente los resultados listados abajo, no los filtros de arriba.</small>
          </div>
          <div id="mostrar_repuestos" style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); overflow: hidden;">
            <!-- Los resultados se cargan aquí -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- VENTANA MODAL MAS DETALLE style="width:80%"-->
<!-- ejemplos de modales:  https://mdbootstrap.com/docs/jquery/modals/basic/ -->
<div role="dialog" tabindex="-1" class="modal fade" id="detalle-modal">
   <div class="modal-dialog modal-lg" role="document" >
     <div class="modal-content">
       <div class="modal-header"> <!-- CABECERA -->
        <p class="text-left" id="mod_titulo_header" style="margin: 0;"></p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.85;"><span aria-hidden="true">×</span></button>
       </div> <!-- FIN CABECERA -->

       <!-- PILLS DE NAVEGACIÓN -->
       <ul class="nav nav-pills detalle-repuesto-pills" id="detalle-repuesto-tabs" role="tablist">
         <li class="nav-item">
           <a class="nav-link active" id="pill-info-tab" data-toggle="pill" href="#pill-info" role="tab" aria-controls="pill-info" aria-selected="true"><i class="fa fa-circle-info"></i> Información General</a>
         </li>
         <li class="nav-item">
           <a class="nav-link" id="pill-fotos-tab" data-toggle="pill" href="#pill-fotos" role="tab" aria-controls="pill-fotos" aria-selected="false"><i class="fa fa-images"></i> Imágenes</a>
         </li>
         <li class="nav-item">
           <a class="nav-link" id="pill-similares-tab" data-toggle="pill" href="#pill-similares" role="tab" aria-controls="pill-similares" aria-selected="false"><i class="fa fa-car"></i> Aplicaciones</a>
         </li>
       </ul>

       <div class="modal-body"> <!-- CONTENIDO -->

        <div class="tab-content" id="detalle-repuesto-tabs-content">
          <!-- PILL: Información General -->
          <div class="tab-pane fade show active" id="pill-info" role="tabpanel" aria-labelledby="pill-info-tab">
            <div class="detalle-repuesto-datos">
              <div class="row" id="datos"></div>
            </div>
          </div>

          <!-- PILL: Imágenes -->
          <div class="tab-pane fade" id="pill-fotos" role="tabpanel" aria-labelledby="pill-fotos-tab">
            <div class="detalle-repuesto-extra">
              <div class="row" id="fotos"></div>
            </div>
          </div>

          <!-- PILL: Aplicaciones -->
          <div class="tab-pane fade" id="pill-similares" role="tabpanel" aria-labelledby="pill-similares-tab">
            <div class="detalle-repuesto-extra">
              <div class="row" id="similares"></div>
            </div>
          </div>
        </div>

      </div> <!-- FIN DE modal-body -->

       <div class="modal-footer"> <!-- PIE -->
       <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cerrar</button>
       </div>

     </div> <!-- modal-content -->


   </div> <!-- modal-dialog -->
</div>

<!-- VENTANA MODAL FOTO GRANDE"-->
<div role="dialog" tabindex="-1" class="modal fade" id="foto-modal">
   <div class="modal-dialog modal-lg" role="document" >
     <div class="modal-content">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
       <div class="modal-body"> <!-- CONTENIDO -->
        <div id="aqui_foto"></div>
      </div> <!-- FIN DE modal-body -->
     </div> <!-- modal-content -->
   </div> <!-- modal-dialog -->
</div>

<!-- VENTANA MODAL MODIFICAR PRECIO"-->
<div role="dialog" tabindex="-1" class="modal fade" id="modificar-precio-modal">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document" >
      <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
         <div class="modal-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); height: auto; padding: 15px 20px;">
            <h6 style="margin:0; color: white; font-weight: 700;"><i class="fa fa-edit"></i> Modificar Precio</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;"><span aria-hidden="true">×</span></button>
         </div>
        <div class="modal-body" style="padding: 20px;">
         <label style="font-weight: 700; color: #2c3e50; font-size: 13px;">Precio Actual:</label>
         <p id="precio_actual_mostrar" style="font-size: 18px; font-weight: 700; color: #27ae60; margin-bottom: 15px;">$ 0</p>

         <label for="precio_modificado" style="font-weight: 700; color: #2c3e50; font-size: 13px;">Nuevo Precio:</label>
         <input class="form-control" type="number" min="0" id="precio_modificado" value="0" style="border-radius: 8px; border: 2px solid #e0e0e0;">

         <div id="mensajes-modal-precio" style="margin-top: 10px; font-size: 13px;"></div>

         <button class="btn btn-success btn-block mt-3" onclick="guardar_nuevo_precio()" style="border-radius: 8px; font-weight: 700;"><i class="fa fa-save"></i> Guardar Nuevo Precio</button>
       </div> <!-- FIN DE modal-body -->

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->
 </div>
@endsection