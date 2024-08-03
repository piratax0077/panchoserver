@extends('plantillas.app')
  @section('titulo','CLIENTES XPRESS')
  @section('javascript')

<script type="text/javascript">
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

  function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
  }

  function activar_cliente_xpress(id_cliente){
    console.log("activar cliente: "+id_cliente);
  }

  function enviar_correo(id_cliente){
    console.log("enviar correo: "+id_cliente);
  }

  function enviar_sms(id_cliente){
    console.log("enviar sms: "+id_cliente);
  }

  function listar_todos()
  {

      var url='{{url("clientes/xpress_listar_todos")}}';

      $.ajax({
        type:'GET',
        beforeSend: function () {
          espere("Listando");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
          $("#listado_clientes_xpress").html(resp);

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

  function borrar_cliente(idc){
    if (confirm('Esta seguro de eliminar el registro '+idc+' ?')==true)
    {

      var url='{{url("clientes")}}'+'/'+idc+'/borrar';

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$('#mensajes').html("Borrando Cliente...");
          espere("Borrando Cliente...");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
          location.href="{{url('clientes')}}";
          //$('#mensajes').html("Cliente Borrado - "+resp);
          Vue.swal({
                text: 'Cliente Borrado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
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
        return true;
    }
  }

  function enter_buscar(e){
    let keycode = e.keyCode;
    if(keycode=='13'){
        buscar_clientes();
    }
  }

  function RUN_correcto(run)
  {
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
    console.log("X8: "+x[8]+" digito: "+digito);
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

     function guardar_cliente_xpress(){

    }

    function enviar_correo(id_cliente_xpress){
        let estado=document.getElementById("estado-"+id_cliente_xpress).value;
        if(estado.substring(0,1)=="C"){
            Vue.swal({
                    title: 'ERROR',
                    text: 'Ya se envió Correo',
                    icon: 'error',
                });
            return false;
        }

        let correo=document.getElementById("correo-"+id_cliente_xpress).value;
        let documento=document.getElementById("documento-"+id_cliente_xpress).value;
        let tipo_doc=0;
        let num_doc=0;
        if(documento.substring(0,3)=='Bol') tipo_doc=39;
        if(documento.substring(0,3)=='Fac') tipo_doc=33;
        if(tipo_doc==0){
            Vue.swal({
                    title: 'ERROR',
                    text: 'No hay documento definido para enviar',
                    icon: 'error',
                });
            return false;
        }
        if(correo=='---'){
            Vue.swal({
                    title: 'ERROR',
                    text: 'No hay correo definido para enviar',
                    icon: 'error',
                });
            return false;
        }
        num_doc=documento.substring(documento.indexOf("°")+2);
        //FALTA: implementar luego caso para envío de notas de crédito por correo

        var url="{{url('/enviarcorreo')}}";
        var parametros={correo_destino:correo,tipo_doc:tipo_doc,num_doc:num_doc};

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            beforeSend: function () {
                Vue.swal({
                    text: 'Enviando...',
                    position: 'center-right',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 2000,
                });
                actualiza_estado_envio_correo_xpress(id_cliente_xpress+"&C&1");
            },
            url:url,
            data:parametros,
            success:function(resp){
                Vue.swal({
                    text: resp,
                    position: 'center-right',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });

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

    function actualiza_estado_envio_correo_xpress(dato){
        let url='{{url("/clientes/xpress_actualizar_estado_envio")}}'+'/'+dato;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log("estado correo actualizado");
                listar_todos();
            },
            error: function(error){
                console.log(error.responseText);
            }

        });
    }

  window.onload=function() {

  }

  function limpiar_controles()
  {


  }

</script>

  @endsection
    @section('style')
        <style>
            label{
                margin-bottom: 0px;
            }

            .table>tbody>tr>td, .table thead tr th{
                padding:0.5px;
            }
        </style>
    @endsection
  @section('contenido_titulo_pagina')
<center><h3>CLIENTES XPRESS</h3></center><br>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
@include('fragm.mensajes')
<div class="row"> <!-- Contiene a las 2 partes -->
<!-- BUSCAR CLIENTES -->
    <div class="col-sm-5">
      <div class="row">
          <legend>Buscar Cliente Xpress (FALTA)</legend>
          <div class="col-sm-12">
            <input type="radio" name="buscapor" id="buscaxrut" checked="true">
            <label for="buscaxrut">RUT</label>
            &nbsp;&nbsp;&nbsp;
            <input type="radio" name="buscapor" id="buscaxnombres" >
            <label for="buscaxnombres">Nombres/Apellidos/Empresa</label>
            &nbsp;&nbsp;&nbsp;
            <input type="radio" name="buscapor" id="buscaxcelular" >
            <label for="buscaxcelular">Celular</label>
            &nbsp;&nbsp;&nbsp;
            <input type="radio" name="buscapor" id="buscaxemail" >
            <label for="buscaxemail">Email</label>
            &nbsp;&nbsp;&nbsp;
            <input type="radio" name="buscapor" id="buscaxdocumento" >
            <label for="buscaxemail">Documento</label>
          </div>
      </div>
      <div class="row mt-2">
        <div class="col-sm-5" style="padding-left:5px;padding-right:5px">
            <input type="text" placeholder="Ingrese búsqueda" id="buscado" onkeyup="enter_buscar(event)" style="width:100%;padding-left:5px;padding-right:5px">
          </div>
          <div class="col-sm-1" style="padding-left:5px;padding-right:5px">
            <button onclick="buscar_clientes()" class="btn btn-success btn-sm">Buscar</button>
          </div>
      </div>
      <div class="row mt-3" style="background-color: #fcffd0">
        <legend>Editar Cliente Xpress (FALTA)</legend>
        <div class="col-sm-12">
          <input class="form-control form-control-sm" type="text" id="rut_xpress" placeholder="rut" style="width:100px">
          <input class="form-control form-control-sm" type="text" id="nombres_xpress" placeholder="nombres / empresa" style="width:250px">
          <input class="form-control form-control-sm" type="text" id="apellidos_xpress" placeholder="apellidos" style="width:150px">
          <input class="form-control form-control-sm" type="text" id="celular_xpress" placeholder="celular" style="width:100px">
          <input class="form-control form-control-sm" type="text" id="correo_xpress" placeholder="correo" style="width:150px">
          <input class="form-control form-control-sm" type="text" id="documento_xpress" placeholder="Documento" style="width:150px">
          <button class="btn btn-info btn-sm mt-2">Guardar</button>
        </div>
    </div>
    </div> <!--Fin buscar clientes -->

    <!-- listado CLIENTES xpress-->
    <div class="col-sm-7" style="background-color: #e8ffff;padding-left:20px">
        <div class="row mb-2">
            <button onclick="listar_todos()" class="btn btn-success btn-sm">Listar Todos</button>
        </div>
        <div class="row" id="listado_clientes_xpress">
            LISTADO CLIENTES XPRESS O RESULTADO DE BÚSQUEDA
        </div>


    </div> <!-- fin listado clientes xpress -->

</div> <!-- fin row de ambas col-sm -->
</div> <!-- container fluid -->
@endsection

