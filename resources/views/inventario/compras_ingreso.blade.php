@extends('plantillas.app')
  @section('titulo','Ingreso por Compras')
  @section('javascript')

    <script type="text/javascript">

    function calculasubtotalitem()
    {
      /* FALTA AGREGAR EL PORCENTAJE DE UTILIDAD SEGÚN LA FAMILIA. ESTA EN LA TABLA FAMILIAS*/
      var cantidad = document.getElementById("cantidad").value;
      var pu = document.getElementById("pu").value;
      document.getElementById("subtotalitem").value=cantidad*pu;
    }

    function calculapreciosug()
    {
      var pu=parseInt(document.getElementById("pu").value);
      var iva=0.19; // Leer de la tabla parámetros
      var flete=parseInt(document.getElementById("costos").value);
      dameUtilidad();
      var utilidad=document.getElementById("utilidad").value/100;
      document.getElementById("preciosug").value=pu*(1+utilidad)*(1+iva)+flete;
    }

    function dameUtilidad()
    {
      // campos id y porcentaje de la tabla familias
      var idFamilia=document.getElementById("id_familia").value;
      //Petición AJAX para obtener el porcentaje respectivo

      var url_familia='{{url("factuprodu")}}'+'/'+idFamilia+'/utilidad'; //petición

        $.ajax({
          type:'GET',
          beforeSend: function () {
            $("#mensajes").html("Obteniendo Utilidad...");
          },
          url:url_familia,
          success:function(utilidad){
            document.getElementById("utilidad").value=utilidad;
            $("#mensajes").html("Listo...");
          },
          error: function(xhr, status, error){
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            $('#mensajes').html(errorMessage);
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
          $("#compras_det").html("Eliminando Item...");
            },
         url:url_item,
         success:function(datos){
          $("#mensajes").html(datos);
          dameItems(idFactura); //Carga los items ESTA PARTE NO  FUNCIONA...

         },
          error: function(xhr, status, error){
        var errorMessage = xhr.status + ': ' + xhr.statusText;
              $('#compras_det').html(errorMessage);
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
        $("#tbl_items").DataTable({
                    "scrollY":        "300px",
                    "scrollCollapse": true,
                    "paging":false,
                    "searching":false,
                    "info":false
                  });
       },
        error: function(xhr, status, error){
      var errorMessage = xhr.status + ': ' + xhr.statusText;
            $('#compras_det').html(errorMessage);
        }

        }); //Fin ajax items



    }

    function guardarItem()
    {
        var url="{{url('compras/guardaritem')}}";

        var idFactura=document.getElementById("id_factura_cab").value;
        var idRepuesto=document.getElementById("id_repuesto").value;
        var cantidad = document.getElementById("cantidad").value;
        var pu = document.getElementById("pu").value;
        var subtotalitem=document.getElementById("subtotalitem").value;
        var costos=document.getElementById("costos").value;
        var costosdesc="Flete";
        var preciosug=document.getElementById("preciosug").value;
        var idLocal=document.getElementById("locales").value;

        var parametros={idFactura:idFactura,
                        idrepuesto:idRepuesto,
                        cantidad:cantidad,
                        pu:pu,
                        subtotalitem:subtotalitem,
                        costos:costos,
                        costosdesc:costosdesc,
                        preciosug:preciosug,
                        idLocal:idLocal
                      };

        $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        $.ajax({
               type:'POST',
               beforeSend: function () {
                $("#mensajes").html("Guardando Item, espere por favor...");
                  },
               url:url,
               data:parametros,
               success:function(resp){
                  $("#mensajes").html(resp);
                  dameItems(idFactura); //Carga los items

                //Limpiar campos

                        document.getElementById("codigo_interno").value="";
                        document.getElementById("cantidad").value="";
                        document.getElementById("pu").value="";
                        document.getElementById("subtotalitem").value="";
                        document.getElementById("costos").value="";
                        document.getElementById("costosdesc").value="";
                        document.getElementById("preciosug").value="";
               },
                  error: function(xhr, status, error){
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    $('#mensajes').html(errorMessage);
                }

        });
    }

    function elegir(id_repuesto,codint,descr,id_familia)
    {
      document.getElementById("id_repuesto").value=id_repuesto;
      document.getElementById("codigo_interno").value=codint;
      document.getElementById("id_familia").value=id_familia;
      $("#descripcion_repuesto").html(descr);
      $('#busca-repuesto-modal').modal('hide');
      //El focus no funciona porque al cerrar el modal, bootstrap autoenfoca
      //en el elemento que abrió el modal...
      document.getElementById("cantidad").focus();
      document.getElementById("cantidad").select();
    }

    function buscarRepuestoProveedor()
    {
      var crp=document.getElementById("cod_rep_prov");
      if(crp.value.trim().length==0)
      {
        alert("Código Repuesto Proveedor Vacio");
        crp.focus();
        return false;
      }

      var url='{{url("compras")}}'+'/buscarepuestosprov/'+crp.value.trim();

      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#mostrar_repuestos").html("Buscando Repuesto por Código Proveedor...");
          },
       url:url,
       success:function(datos){
        $('#mostrar_repuestos').html(datos);
        $("#tbl_repuestos").DataTable({
                    "scrollY":        "300px",
                    "scrollCollapse": true,
                    "paging":false,
                    "searching":false,
                    "info":false
                  });

       },
        error: function(error){
        $('#mostrar_repuestos').html(error.responseText);
        }

        }); //Fin ajax

    }

    function buscarRepuesto()
    {
        var url="{{url('compras/buscarepuestos')}}";
        var idFamilia=document.getElementById("familia").value;
        var idMarca = document.getElementById("Marca").value;
        var idModelo = document.getElementById("Modelo").value;
        var parametros={idFa:idFamilia,idMa:idMarca,idMo:idModelo};

        $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        $.ajax({
               type:'POST',
               beforeSend: function () {
                $("#mostrar_repuestos").html("Buscando, espere por favor...");
                  },
               url:url,
               data:parametros,
               success:function(resp){
                  $("#mostrar_repuestos").html(resp);

                  $("#tbl_repuestos").DataTable({
                    "scrollY":        "300px",
                    "scrollCollapse": true,
                    "paging":false,
                    "searching":false,
                    "info":false
                  });

               },
                error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText;
                    $('#mostrar_repuestos').html(errorMessage);
                }

        });

    }

    function cargarModelos()
    {
      $('#Modelo option').remove();
      var xMarca=document.getElementById('Marca').value;

      @foreach($modelos as $modelo)
        var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
        if(idMarca==xMarca)
        {
          $('#Modelo').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
        }
      @endforeach
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

    function ver_id()
    {
      var x=document.getElementById("id_factura_cab").value;
      alert(x);

    }

    window.onload = (function()
    {

      var url="{{url('compras/guardarcabecera')}}";

      $("#btnGuardarCabecera").click(function(e){
        e.preventDefault();

        var boton=document.getElementById("btnGuardarCabecera");
        var botonbuscar=document.getElementById("btnBuscarRepuesto");
        var botonguardaritem=document.getElementById("btnGuardarItem");

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
                $("#mensajes").html("Guardando Cabecera de Factura...");
                  },
               url:url,
               data:parametros,
               success:function(resp){
                $("#mensajes").html(resp);
                  if(resp>0)
                  {
                    $("#mensajes").html("Cabecera de Factura Guardada... Ingrese los Items");
                    boton.disabled=true;
                    document.getElementById("campos_repuestos").disabled=false;
                    document.getElementById("id_factura_cab").value=resp; //Contiene el id de la cabecera
                  }else{
                    //$("#mensajes").html("No se pudo guardar Cabecera de Factura...");
                    $("#mensajes").html(resp);
                  }


               },
                error: function(xhr, status, error){
              var errorMessage = xhr.status + ': ' + xhr.statusText;
                    $('#mensajes').html(errorMessage);
                }

        });

      }); // fin button
    }); // fin document


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

    </script>

  @endsection
  @section('style')
    <style>
    .modal-header-40 {
      background-color: #75b4e8;
      color: white;
      height: 40px;
    }
    </style>
  @endsection
  @section('contenido_titulo_pagina')
<div class="row">
  <div class="col-sm-10">
  <center><h2>Ingreso por Compras</h2></center>
  </div>
  <div class="col-sm-2">
      <a href={{url('compras/crear')}} class="btn btn-info btn-sm" style="margin-top: 20px">NUEVA FACTURA</a>
      </div>
</div>
@endsection
  @section('contenido_ingresa_datos')
   @include('fragm.mensajes')
   <div class="container-fluid">
    <div class="row">
      <div id="mensajes"></div>
    </div>
    <div class="row" style="background-color: #D0FFFF;">
      <input type="hidden" id="id_factura_cab">
      <input type="hidden" id="id_familia">
      <input type="hidden" id="utilidad">
      <div class="col-sm-2">
        <label for="proveedor">Proveedor:</label>
        <select name="proveedor" class="form-control form-control-sm" id="proveedor">
          @foreach ($proveedores as $proveedor)
            <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-2">
        <label for="numerofactura">Factura Num:</label>
        <input type="text" name="numerofactura" value="{{old('numerofactura')}}"  id="numerofactura" class="form-control form-control-sm">
      </div>
      <div class="col-sm-2">
        <label for="fechafactura">Factura Fecha:</label>
        <input type="date" name="fechafactura" value="{{old('fechafactura')}}"  id="fechafactura" class="form-control form-control-sm">
      </div>
      <div class="col-sm-2">
        <div class="form-group form-control-sm">
        <input type="checkbox" class="form-check-input form-control-sm" id="credito" onclick="revisarCredito()">
        <label class="form-check-label" for="credito">Compra a Crédito</label>
        </div>
      </div>
      <div class="col-sm-2">
        <label for="vencefactura">Factura Fecha Vencim.:</label>
        <input type="date" name="vencefactura" value="{{old('vencefactura')}}"  id="vencefactura" class="form-control form-control-sm" disabled>
      </div>
      <div class="col-sm-2">
        <input type="submit" id="btnGuardarCabecera" name="btnGuardarCabecera" value="Guardar Cabecera" class="btn btn-primary btn-sm" style="margin-top: 20px"/>
      </div>


    </div> <!--Fin fila 2 -->

<!-- INGRESO DE REPUESTOS -->
<fieldset disabled id="campos_repuestos">
    <div class="row">
      <div class="col-sm-2">
        <label for="codigo_interno">Código Interno:</label>
        <input type="text" value="{{old('codigo_interno')}}"  id="codigo_interno" class="form-control form-control-sm" readonly>
        <input type="hidden" name="id_repuesto" value="{{old('id_repuesto')}}"  id="id_repuesto" class="form-control">
      </div>
      <div class="col-sm-2">
        <input type="submit" id="btnBuscarRepuesto" name="btnBuscarRepuesto" value="Elegir Repuesto" class="btn btn-success btn-md" style="margin-top: 20px" data-toggle="modal" data-target="#busca-repuesto-modal"/>
      </div>
      <div class="col-sm-6" style="margin-top: 25px">
        <div id="descripcion_repuesto" ></div>
      </div>
    </div> <!--Fin fila 3 -->

    <div class="row" style="margin-top: 10px">
      <div class="col-sm-2">
        <label for="cantidad">Cantidad:</label>
        <input type="text" name="cantidad" value="{{old('cantidad')}}"  id="cantidad" class="form-control form-control-sm">
      </div>
      <div class="col-sm-2">
        <label for="pu">Precio Unitario:</label>
        <input type="text" name="pu" value="{{old('pu')}}"  id="pu" class="form-control form-control-sm" onfocusout="calculasubtotalitem()">
      </div>
      <div class="col-sm-2">
        <label for="subtotalitem">Subtotal Item:</label>
        <input type="text" name="subtotalitem" value="{{old('subtotalitem')}}"  id="subtotalitem" class="form-control form-control-sm" readonly>
      </div>
      <div class="col-sm-2">
        <label for="costos">Flete:</label>
        <input type="text" name="costos" value="{{old('costos')}}"  id="costos" class="form-control form-control-sm" onfocusout="calculapreciosug()">
      </div>
      <div class="col-sm-2">
        <label for="preciosug">Precio Sugerido</label>
        <input type="text" name="preciosug" value="{{old('preciosug')}}"  id="preciosug" class="form-control form-control-sm" readonly>
      </div>
    </div> <!--Fin fila 5 -->

    <div class="row" style="margin-top: 10px">
      <div class="col-sm-4">
        <label for="locales">Local:</label>
        <select name="locales" class="form-control form-control-sm" id="locales">
          @foreach ($locales as $local)
            <option value="{{$local->id}}">{{$local->local_nombre}} en {{$local->local_direccion}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-2" style="margin-top: 25px">
        <div id="localelegido" >&Longleftarrow; Elija un Local</div>
      </div>
      <div class="col-sm-4">
        <input type="submit" id="btnGuardarItem" name="btnGuardarItem" class="btn btn-warning btn-md" style="margin-top: 25px" value="Guardar Item" onclick="guardarItem()"/>
      </div>
    </div> <!--Fin fila 6 -->

  </fieldset>


    </div> <!--FIN container fluid 1 -->

  @endsection

  @section('contenido_ver_datos')

  <!-- Serán asíncronas con AJAX usando fragmentos-->
  <div class="container-fluid" id="compras_det" style="margin-top: 30px">
  </div>





<!-- VENTANA MODAL BUSCAR REPUESTOS -->
<div role="dialog" tabindex="-1" class="modal fade" id="busca-repuesto-modal">
   <div class="modal-dialog modal-lg" role="document" >
     <div class="modal-content">
       <div class="modal-header modal-header-40">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <p class="text-center" id="mod_titulo_header">Buscar Repuesto</p>
        </div>

       <div class="modal-body"> <!-- CONTENIDO -->

        <!-- BUSCAR -->
        <div class="container-fluid">
          <div class="row">
              <div class="col-md-6">
                Código Repuesto Proveedor:&nbsp;<input type="text" id="cod_rep_prov" value="" >
              </div>
              <div class="col-md-3">
                  <button onclick="buscarRepuestoProveedor()" id="btnBuscarRepuestoProveedor" class="btn btn-info btn-md">Buscar</button>
                </div>
                <div class="col-md-3">

                </div>
          </div>
              <div class="row" style="margin-top:10px">
                  <div class="col-md-3">
                    <label for="familia">Familia:</label>
                      <select name="cFa" class="form-control" id="familia">
                        @foreach ($familias as $familia)
                          <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                        @endforeach
                      </select>
                  </div>
                  <div class="col-md-3">
                    <label for="Marca">Marca:</label>
                    <select name="cMa" class="form-control" id="Marca" onchange="cargarModelos()">
                      @foreach ($marcas as $marca)
                        <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">

                    <label for="Modelo">Modelo:</label>
                      <select name="cMo" id="Modelo" class="form-control">
                        <option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>
                      </select>
                  </div>
                  <div class="col-md-3">
                    <input type="submit" id="btnBuscar" name="btnBuscarRepuesto" value="Buscar" class="btn btn-primary btn-md" style="margin-top: 25px" onclick="buscarRepuesto()"/>
                  </div>
                </div> <!--FIN DEL PRIMER ROW COL 1 -->
        </div>
        <div class="container-fluid" id="mostrar_repuestos"></div>



      </div> <!-- FIN DE modal-body -->
     </div> <!-- modal-content -->
   </div> <!-- modal-dialog -->
</div>


  @endsection
