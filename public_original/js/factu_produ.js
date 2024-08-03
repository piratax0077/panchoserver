$(document).ready(function()
{

  function calculasubtotalitem()
  {
    /* FALTA AGREGAR EL PORCENTAJE DE UTILIDAD SEGÚN LA FAMILIA. ESTA EN LA TABLA FAMILIAS */
    var cantidad = document.getElementById("cantidad").value;
    var pu = document.getElementById("pu").value;
    document.getElementById("subtotalitem").value=cantidad*pu;
  }

  function calculapreciosug()
  {
    var pu=parseInt(document.getElementById("pu").value);
    var valor_iva=pu*0.19;
    var costos=parseInt(document.getElementById("costos").value);
    document.getElementById("preciosug").value=pu+valor_iva+costos;
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
            var errorMessage = xhr.status + ': ' + xhr.statusText
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
        var errorMessage = xhr.status + ': ' + xhr.statusText
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
      var costosdesc=document.getElementById("costosdesc").value;
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
  var errorMessage = xhr.status + ': ' + xhr.statusText
  $('#mensajes').html(errorMessage); 
}

});
    }

    function elegir(id_repuesto,codint,descr)
    {
      document.getElementById("id_repuesto").value=id_repuesto;
      document.getElementById("codigo_interno").value=codint;
      $("#descripcion_repuesto").html(descr);
      $('#busca-repuesto-modal').modal('hide');
      //El focus no funciona porque al cerrar el modal, bootstrap autoenfoca
      //en el elemento que abrió el modal...
      document.getElementById("cantidad").focus();
      document.getElementById("cantidad").select();
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
        var errorMessage = xhr.status + ': ' + xhr.statusText
        $('#mostrar_repuestos').html(errorMessage); 
      }

    });

    }

    function cargarModelos()
    {
      $('#modelo option').remove();
      var xMarca=document.getElementById('Marca').value;
      
      @foreach($modelos as $modelo)
      var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
      if(idMarca==xMarca)
      {
        $('#modelo').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
      }
      @endforeach
    }

    function cargarModelosSimilares()
    {
      $('#ModeloSim option').remove();
      var xMarca=document.getElementById('MarcaSim').value;
      
      @foreach($modelos as $modelo)
      var idMarca='{{$modelo->marcavehiculos_idmarcavehiculo}}';
      if(idMarca==xMarca)
      {
        $('#ModeloSim').append('<option value="{{$modelo->id}}">{{$modelo->modelonombre}}</option>');
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

    
    
    function guardarCabeceraFactura()
    {  
      var url="{{url('compras/guardarcabecera')}}";


      var boton=document.getElementById("btnGuardarCabeceraFactura");
      var zona_repuestos=document.getElementById("#zona_ingreso_repuesto");
      var zona_item_factura=document.getElementById("#zona_ingreso_items_factura");
      var zona_fotos=document.getElementById("#zona_fotos");
      var zona_similares=document.getElementById("#zona_similares");
      var zona_OEMs=document.getElementById("#zona_OEMs");


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
                  var errorMessage = xhr.status + ': ' + xhr.statusText + ': '+ error;
                  $('#mensajes').html(errorMessage); 
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

}); // fin document ready