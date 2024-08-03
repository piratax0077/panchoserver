@extends('plantillas.app')
  @section('titulo','Crear Modelo Vehículo')
  @section('javascript')
    <script type="text/javascript">
      var modifica=false;
      var elid=0;

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

      function nombre()
      {
        //var nom=$('#archivo')[0].files[0].name.trim();
        //document.getElementById('modelovehiculo').value = nom.substring(0,nom.length-4); //le quitamos la extensión
      }

      function eliminarmodelo(idModelo)
      {
        if (confirm('Esta seguro de eliminar el registro?')==true)
        {
          var url='{{url("modelovehiculo")}}'+'/'+idModelo+'/eliminar';

          $.ajax({
            type:'GET',
            beforeSend: function () {
              //$("#mensajes").html("Eliminando modelo...");
              espere("Eliminando modelo...");
            },
            url:url,
            success:function(modelos){
                Vue.swal.close();
                Vue.swal({
                text: 'Modelo Eliminado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });

              document.getElementById("cboMarcaVehiculo").disabled=false;
             document.getElementById("modelovehiculo").disabled=false;
             document.getElementById("zofri").disabled=false;
             document.getElementById("aniosvehiculo").disabled=false;
             document.getElementById("archivo").disabled=false;
             document.getElementById("btnVer").disabled=false;
             document.getElementById("btnGuardarModelo").disabled=false;
            document.getElementById("modelovehiculo").value="";
            document.getElementById("zofri").checked=false;
            document.getElementById("archivo").value="";
            $("#fotito").html("");
            modifica=false;
            elid=0;

              $('#vermodelos').html(modelos);
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


      }

      function modificarmodelo(id)
      {
        modifica=true;
        elid=id;
        var url='{{url("modelovehiculo/dameuno")}}'+'/'+id;

        $.ajax({
        type:'GET',
        beforeSend: function () {

            },
        url:url,
        success:function(datos){
          $("#mensajes").html("Puede Modificar y Guardar...");
          var d=JSON.parse(datos);

          document.getElementById("cboMarcaVehiculo").value=d.marcavehiculos_idmarcavehiculo;
          document.getElementById("modelovehiculo").value=d.modelonombre;
          document.getElementById("aniosvehiculo").value=d.anios_vehiculo;
          document.getElementById("zofri").checked=d.zofri==1 ? true:false;
          var fot="<img src='"+"{{asset('storage')}}"+"/"+d.urlfoto+"' width='60px'>";
          $("#fotito").html(fot);
        },
          error: function(error){
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
          }

          }); //Fin ajax

      }

      function guardarmodelo()
        {

          var url="{{url('modelovehiculo/guardar')}}";
          var modelovehiculo=document.getElementById("modelovehiculo").value;
            if(modelovehiculo.length==0){
                Vue.swal({
                text: 'Ingrese Modelo de Vehículo...',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;

            }

          var combo=document.getElementById("cboMarcaVehiculo");

          var zofri=document.getElementById("zofri").checked;
          var marcavehiculo=combo.options[combo.selectedIndex].text;
          var archivo=$('#archivo')[0].files[0];
          var cboMarcaVehiculo=combo.value;

          var av=document.getElementById("aniosvehiculo").value
          var anveh=av.trim();
          var anios=anveh;
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



          var datos=new FormData();
          datos.append('modelovehiculo',modelovehiculo);
          datos.append('zofri',zofri);
          datos.append('aniosvehiculo',anveh);
          datos.append('archivo',archivo);
          datos.append('cboMarcaVehiculo',cboMarcaVehiculo);

          if(modifica)
          {
            datos.append('modifika',1);
            datos.append('ide',elid);
          }else{
            datos.append('modifika',0);
          }


          $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
          });

          $.ajax({
           type:'POST',
           beforeSend: function () {
             document.getElementById("cboMarcaVehiculo").disabled=true;
             document.getElementById("modelovehiculo").disabled=true;
             document.getElementById("zofri").disabled=true;
             document.getElementById("archivo").disabled=true;
             document.getElementById("btnVer").disabled=true;
             document.getElementById("btnGuardarModelo").disabled=true;
            espere("Guardando Modelo...");
          },
          url:url,
          data:datos,
          cache:false,
          contentType:false,
          processData:false,
          success:function(modelos){
              Vue.swal.close();
            $('#vermodelos').html(modelos);
            $("#mensajes").html("Guardado <strong>"+marcavehiculo+"</strong>  "+modelovehiculo);
            Vue.swal({
                text: 'Guardado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("cboMarcaVehiculo").disabled=false;
             document.getElementById("modelovehiculo").disabled=false;
             document.getElementById("aniosvehiculo").disabled=false;
             document.getElementById("zofri").disabled=false;
             document.getElementById("archivo").disabled=false;
             document.getElementById("btnVer").disabled=false;
             document.getElementById("btnGuardarModelo").disabled=false;
            document.getElementById("modelovehiculo").value="";
            document.getElementById("aniosvehiculo").value="";
            document.getElementById("zofri").checked=false;
            document.getElementById("archivo").value="";
            $("#fotito").html("");
            modifica=false;
            elid=0;
          },
          error: function(error){
            document.getElementById("cboMarcaVehiculo").disabled=false;
             document.getElementById("modelovehiculo").disabled=false;
             document.getElementById("zofri").disabled=false;
             document.getElementById("archivo").disabled=false;
             document.getElementById("btnVer").disabled=false;
             document.getElementById("btnGuardarModelo").disabled=false;
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
        }

      function ver()
      {

        //al iniciar la página, no cargará los modelos automáticamente ya que son demasiados y ralentizan la página
        // por eso se cargarán solo los que se desean ver según la marca

        var idMarca=document.getElementById("cboMarcaVehiculo").value;
        var url='{{url("modelovehiculo")}}'+'/'+idMarca+'/ver';

      $.ajax({
        type:'GET',
        beforeSend: function () {

        },
        url:url,
        success:function(modelos){
          $("#mensajes").html("Modelos cargados...");
          $('#vermodelos').html(modelos);

        },
        error: function(error){
          Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });

        }

      }); //Fin ajax

      }

    </script>
  @endsection
  @section('style')
    <style>
      hr{
        margin-top:5px;
        margin-bottom:5px;
      }
    </style>
  @endsection
  @section('contenido_titulo_pagina')
    <center><h4 class="titulazo">Crear Modelo de Vehículo</h4></center><br>
  @endsection
@section('contenido_ingresa_datos')

<div class="container-fluid">
  @include('fragm.mensajes')

      <div class="row">
        <div class="col-1 col-sm-1 col-md-1">
          <label for="sel1">Marca:</label>
          <select name="cboMarcaVehiculo" class="form-control form-control-sm" id="cboMarcaVehiculo">
            @foreach ($marcas as $marca)
               <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}</option>
            @endforeach
          </select>

        </div>
        <div class="col-2 col-md-2" style="display:flex;align-items: center;justify-content: center;">
          <label for="zofri">Es Zofri?</label>
            <input type="checkbox" name="zofri" value=""  id="zofri" class="form-control form-control-sm" style="height:20px;width:30%">
        </div>
        <div class="col-3 col-sm-3 col-md-3">
          <label>Modelo del Vehículo:
            <input type="text" name="modelovehiculo" value=""  id="modelovehiculo" class="form-control form-control-sm" size="100%">
          </label>
        </div>
        <div class="col-1 col-sm-1 col-md-1" style="padding-left: 2px; padding-right:2px">
            <label>Años:
            <input type="text" name="aniosvehiculo" value=""  id="aniosvehiculo" class="form-control form-control-sm" size="100%">
            </label>
        </div>
        <div class="col-5 col-sm-5 col-md-5">
            <label>Subir Foto (jpg,jpeg,png):</label>
            <input type="file" name="archivo" id="archivo" class="form-control-file" style="width:100%" onchange="nombre()">
        </div>
      </div>

      <br>
      <div class="row">
        <div class="col-4 col-sm-4 col-md-4 col-lg-4">
          <button class="btn btn-success btn-sm" id="btnVer" onclick="ver()">Ver Modelos</button>
        </div>
        <div class="col-2 col-sm-2 col-md-2 col-lg-2">
            <button class="btn btn-primary btn-sm" onclick="guardarmodelo()" id="btnGuardarModelo">Guardar</button>
        </div>
        <div class="col-4 col-sm-4 col-md-4 col-lg-4" id="fotito">

          </div>
      </div>
</div>
  @endsection

  @section('contenido_ver_datos')
  <hr>
  <div id="vermodelos"></div>

  @endsection
