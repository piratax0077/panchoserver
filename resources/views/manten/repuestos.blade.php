@extends('plantillas.app')
  @section('titulo','Listar Repuestos')
  @section('javascript')
    <script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <script type="text/javascript">

    function modificar_precio(idrep)
    {

    }

    function abrir_modal_modificar_precio(idrep)
    {
        document.getElementById("precio_modificado").value=document.getElementById("precio_venta_"+idrep).value;
        $("#modificar-precio-modal").modal("show");

    }

    function aplicar_aumento()
    {
        var porcentaje=document.getElementById("porcentaje").value;
        if(porcentaje>100) porcentaje=100;
        if(porcentaje<0) porcentaje=0;
        alert("Pronto aumento del "+porcentaje+"%");
    }

    function abrir_foto_modal(link)
    {
      var base='{{asset("storage/")}}';
      var enlace=base+'/'+link;
      $("#aqui_foto").html("<img src='"+enlace+"' width='600px'>");
      $("#foto-modal").modal("show");
    }

    function abrir_modal(ide)
    {

      var url_repuesto='{{url("repuesto")}}'+'/'+ide+'/damerepuesto'; //petición

      $.ajax({
       type:'GET',
       beforeSend: function () {
        $("#datos").html("Obteniendo datos...");
          },
       url:url_repuesto,
       success:function(datos){        
        $('#datos').html(datos); // coloca el view renderizado en el controlador
        var d=document.getElementById('titulo_detalle').value;
        $('#mod_titulo_header').html(d);
       },
        error: function(error){
            $('#datos').html(error.responseText);
        }

        }); //Fin ajax repuestos

      //Traer SIMILARES
      var url_similares='{{url("repuesto")}}'+'/'+ide+'/damesimilares';
      $.ajax({
       type:'GET',
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
/*
        $("#tbl_repuestos").DataTable({
            "scrollY": "300px",
            "scrollCollapse": true,
            "paging":false,
            "pagingType":"numbers",
            "searching":false,
            "info":false
            });
*/
      $("#btnBuscar").click(function(e){
        e.preventDefault();
        var url="{{url('repuesto/buscarepuestos')}}";
        var idFamilia=document.getElementById("familia").value;
        //var idMarca = document.getElementById("Marca").value;
        //var idModelo = document.getElementById("Modelo").value;
        //var parametros={idFa:idFamilia,idMa:idMarca,idMo:idModelo};
        var parametros={idFa:idFamilia};

        $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        $.ajax({
               type:'POST',
               beforeSend: function () {
                $("#mensajes").html("Buscando por familia, espere por favor...");
                $("#mostrar_repuestos").html();
                  },
               url:url,
               async: false,
               data:parametros,
               success:function(resp){
                $("#mensajes").html("Repuestos por Familia");
                  $("#mostrar_repuestos").html(resp);
/*
                  $("#tbl_repuestos").DataTable({
                    "scrollY":        "300px",
                    "scrollCollapse": true,
                    "paging":false,
                    "searching":false,
                    "info":false
                  });
                  */
               },
                error: function(error){
                    $('#mostrar_repuestos').html(error.responseText);
                }

        });

      }); // fin btnBuscar

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
      background-color: #4146D8;
      color: white;
      height: 70px;
  }

  .modal-dialog,
  .modal-content {
      /* 95% of window height */
      height: 95%;
  }


  .modal-body {
      /* 100% = dialog height, 120px = header + footer */
      max-height: calc(100% - 120px);
      overflow-y: scroll;
  }



  .modal-footer {
      background-color: #414cff;
      height: 50px;
  }

  .modal-dalog
  {
    background-color: #fb3ca0;
      height: 95px;
  }

 .modal-cantent {
    background-color: #6bf145;
     height: auto;
  }

  .modal-bady{
    background-color: #d5e03f;
    max-height: 90px;
    overflow-y:hidden;
}
  .modal-fater {
      background-color: #b7baff;
      height: 40px;
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
  <!-- BUSCAR -->
<div class="container-fluid" style="line-height: 14px">
  <div class="row mb-3" style="width: 100%; background: beige; padding: 5px;border: 1px solid black; border-radius: 10px; ">
    <div class="col-md-4">
      <label for="proveedor">Proveedor:</label>
            <select name="proveedor" class="form-control form-control-sm" id="proveedor">
                <option value="0">Elija un Proveedor</option>
              @foreach ($proveedores as $proveedor)
                @if(isset($id_prov))
                    @if($proveedor->id==$id_prov)
                        <option value="{{$proveedor->id}}" selected>{{$proveedor->empresa_nombre}}</option>
                    @else
                        <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre}}</option>
                    @endif
                @else
                    <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre}}</option>
                @endif
              @endforeach
            </select>
            <input type="submit" id="btnBuscarProveedor" name="btnBuscarProveedor" value="Buscar" class="btn btn-success btn-sm" style="margin-top: 20px"/>
    </div>
    <div class="col-md-4">
      <label for="familia">Familia:</label>
      <select name="cFa" class="form-control form-control-sm" id="familia">
        @foreach ($familias as $familia)
          <option value="{{$familia->id}}">{{strtoupper($familia->nombrefamilia)}} ({{$familia->total}})</option>
        @endforeach
      </select>
      <input type="submit" id="btnBuscar" name="btnBuscarRepuesto" value="Buscar" class="btn btn-primary btn-sm" style="margin-top: 20px"/>
    </div>
    <div class="col-md-4">
            <label for="porcentaje">% de Aumento</label>
            <input type="number" step="0.1" min="0.1" max="100.0" id="porcentaje" value="0" class="form-control form-control-sm" style="width:80px">
            <button class="btn btn-warning btn-sm"  style="margin-top: 20px" onclick="aplicar_aumento()">Aplicar</button>
          </div>
      {{-- <div class="row" style="margin-top:10px">
          

          <!-- http://www.marghoobsuleman.com/jquery-image-dropdown -->
          <div class="col-md-2">
            
          </div>
          <div class="col-md-1">
            
            </div>
          <div class="col-md-1">
            
        </div>
         <div class="col-md-1">
            
         </div>
    </div><!-- row  --> --}}
</div>

<div id="mostrar_repuestos">
@if(isset($repuestos))
  
  
    @if($repuestos->count()>0)
    <a href="/reportes/imprimir_repuestos_por_proveedor/{{$id_prov}}" class="btn btn-success btn-sm" ><i class="fa-solid fa-file-excel"></i></a>
    <div class="row mt-2">
        <div class="col-md-12" style="padding-right:15px;padding-left:2px;">
          <table id="tbl_repuestos" class="display compact table">
            <thead class="thead-dark">
              <th width="4%" scope="col">Id</th>
              <th width="4%" scope="col">Cod Int</th>
              <th width="10%" scope="col">Cod Rep Prov</th>
              <th width="12%" scope="col">Proveedor</th>
              <th width="35%" scope="col">Descripción</th>
              <th width="10%" scope="col">Marca Repuesto</th>
              <th width="10%" scope="col">Origen</th>
              <th width="7%" scope="col">Pr.Venta</th>
              <th width="7%"></th> <!-- VER DETALLE en ventana MODAL-->
              <th width="7%"></th>
            </thead>
            <tbody>
            @foreach ($repuestos as $repuesto)
            <tr>
                    <td>{{$repuesto->id}}</td>
                    <td>{{$repuesto->codigo_interno}}</td>
                    <td>{{$repuesto->cod_repuesto_proveedor}}</td>
                    <td>{{$repuesto->proveedor->empresa_nombre}}</td>
                    <td>{{$repuesto->descripcion}} Med: {{$repuesto->medidas}}</td>
                    <td>{{strtoupper($repuesto->marcarepuesto->marcarepuesto)}}</td>
                    <td>{{$repuesto->pais->nombre_pais}}</td>
                    <td>
                      <strong>
                        <p id="precio_venta_p_{!!$repuesto->id!!}">$ {{str_replace(',','.',number_format($repuesto->precio_venta))}}</p>
                        <input type="hidden"  id="precio_venta_{!!$repuesto->id!!}" value={!!$repuesto->precio_venta!!}>
                      </strong>
                    </td>

    <td><button class="btn btn-warning btn-sm" onclick="abrir_modal({{$repuesto->id}})">Más Detalle</button>
    <!--
                  <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#detalle-modal">Más Detalle</a>
    -->
              </td>
              <td>
                <button onclick="abrir_modal_modificar_precio({{$repuesto->id}})" class="btn btn-primary btn-sm">Modificar</button>
              </td>
            </tr>
            @endforeach
            </tbody>
          </table>
        </div>
    </div>
    {{$repuestos->links()}}
    @else
    <div class="row">
    <div class="alert alert-info">
        <h4><center>Sin resultados.</center></h4>
    </div>
    </div>
    @endif
@endif
</div>

<!-- VENTANA MODAL MAS DETALLE style="width:80%"-->
<!-- ejemplos de modales:  https://mdbootstrap.com/docs/jquery/modals/basic/ -->
<div role="dialog" tabindex="-1" class="modal fade" id="detalle-modal">
   <div class="modal-dialog modal-lg" role="document" >
     <div class="modal-content">
       <div class="modal-header"> <!-- CABECERA -->
        <p class="text-left" id="mod_titulo_header"></p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
       </div> <!-- FIN CABECERA -->

       <div class="modal-body"> <!-- CONTENIDO -->

<!-- Columna 1 : Datos -->
        <div class="col-sm-12" style="background-color: #F2F5A9;">
          <div class="row" id="datos"></div>
        </div> <!-- FIN Columna 1 : Datos -->

<!-- Columna 2 : fotos y similares -->
        <div class="col-sm-12" style="background-color: #81BEF7;">
          <div class="row" id="fotos"></div>
          <div><p></p></div>
          <div class="row" id="similares"></div>

        </div><!-- FIN Columna 2 : fotos y similares -->

      </div> <!-- FIN DE modal-body -->

       <div class="modal-footer"> <!-- PIE -->
       <button class="btn btn-danger btn-sm" type="button" data-dismiss="modal">Cerrar </button>
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
    <div class="modal-dialog modal-dalog modal-sm" role="document" >
      <div class="modal-content modal-cantent">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <div class="modal-body modal-bady"> <!-- CONTENIDO -->
         Precio Actual: <br>
         <input class="form-control form-control-sm" type="text" id="precio_modificado" value="0" style="width:100px">
         <button class="btn btn-sm btn-success">Modificar Precio</button>
       </div> <!-- FIN DE modal-body -->

      </div> <!-- modal-content -->

    </div> <!-- modal-dialog -->
 </div>
@endsection
