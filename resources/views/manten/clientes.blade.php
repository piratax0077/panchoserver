@extends('plantillas.app')
  @section('titulo','CLIENTES')
  @section('javascript')

<script type="text/javascript">
  var modifica=false;
  var elid=-1;
  var xDescuento=0;
  var xTipoDescuento=0;
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

  function tipo_cliente(c){

    if(c==0){
      document.getElementById("empresa").disabled=true;
      document.getElementById("nombres").disabled=false;
      document.getElementById("apellidos").disabled=false;
    }

    if(c==1){
      document.getElementById("empresa").disabled=false;
      document.getElementById("nombres").disabled=true;
      document.getElementById("apellidos").disabled=true;
    }

  }


  function limpiar_deuda_cero_clave(){
    Vue.swal({
        title: 'Ingrese Contraseña',
        input: 'password',
        confirmButtonText: 'Verificar',
        showConfirmButton: true,
        showCancelButton: true,
    }).then((result) => {
        if(result.isConfirmed){
            let clave=result.value;
            let url='{{url("/clave")}}';
            let parametros={
                clave:clave,
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
            success:function(resp){
                Vue.swal.close();
                if(resp=="OK"){
                    limpiar_deuda_cero();
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


    });
  }

  function limpiar_deuda_cero(){
    var url='{{url("clientes")}}'+'/'+elid+'/borrardeuda';

    $.ajax({
    type:'GET',
    beforeSend: function () {
        //$('#mensajes').html("Borrando Cliente...");
        espere("Limpiando Deuda Cero Cliente...");
    },
    url:url,
    success:function(resp){
        $("#listar_clientes").html(resp);
        Vue.swal.close();
        Vue.swal({
            text: 'Listo...',
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

  function buscar_clientes()
  {
    var url="{{url('clientes/buscar/')}}";
    var quien="clientes";
    var parametros={buscax:"nombres",buscado:document.getElementById("buscado").value,quien:quien};
    var bx=document.getElementById("buscaxrut").checked;

    if(bx) parametros=parametros={buscax:"rut",buscado:document.getElementById("buscado").value,quien:quien};

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Buscando...");
      espere("Buscando...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
      //$("#mensajes").html("Listado de Clientes...");
      $("#listar_clientes").html(resp);
      limpiar_controles();
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

  function cuenta_cliente(){
    if(!modifica){
        Vue.swal({
            title: 'ERROR',
            text: 'Debe Buscar y Elegir un Cliente',
            icon: 'error',
        });
    }else{
        var url='{{url("/clientes/dame_cuenta")}}'+'/'+elid;

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$('#mensajes').html("Borrando Cliente...");
          espere("Buscando cuenta del Cliente...");
        },
        url:url,
        success:function(resp){
            $("#listar_clientes").html(resp);
            Vue.swal.close();
          Vue.swal({
                text: 'Listo...',
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


    }
  }

  function borrar_cuenta_clave(idop){
    console.log(idop);
    return false;
    Vue.swal({
                title: 'Ingrese Contraseña',
                input: 'password',
                confirmButtonText: 'Verificar',
                showConfirmButton: true,
                showCancelButton: true,
            }).then((result) => {
                if(result.isConfirmed){
                    let clave=result.value;
                    //Pedir contraseña
                    let url='{{url("/clave")}}';
                    let parametros={
                        clave:clave,
                    };
                    $.ajaxSetup({
                        headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                    type:'POST',
                    beforeSend: function () {
                    //espere("Guardando Cuenta...");
                    },
                    url:url,
                    data:parametros,
                    success:function(resp){
                        Vue.swal.close();
                        if(resp=="OK"){
                            borrar_cuenta(idop);
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


            });
  }

  function borrar_cuenta(idop){
    let data=elid+"*"+idop;
    var url='{{url("/clientes/borrar_cuenta")}}'+'/'+data;

      $.ajax({
        type:'GET',
        beforeSend: function () {
          espere("Borrando cuenta del Cliente...");
        },
        url:url,
        success:function(resp){
            $("#listar_clientes").html(resp);
            Vue.swal.close();
          Vue.swal({
                text: 'Listo...',
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
  }

  function cargar_cliente(idc)
  {
    modifica=true;
    var url='{{url("clientes")}}'+'/'+idc+'/cargar';
    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Cargar Cliente...");
        espere("Cargando Cliente");
      },
      url:url,
      success:function(resp){
        //Llenar los datos del cliente en los controles.
        //viene en JSON desde el controlador
        console.log(resp);
        let giros = resp[2];
        console.log(giros);
        if(giros.length > 0){
          $('#div_giros_antiguos').removeClass('d-block');
          $('#div_giros_antiguos').addClass('d-none');
          $('#div_giros').removeClass('d-none');
          $('#select_giros').empty();
          giros.forEach(giro => {
            
            $('#select_giros').append(`
              <option value='`+giro.giro+`'>`+giro.giro+` </option>
            `);
          });
        }else{
          $('#div_giros_antiguos').addClass('d-block');
          $('#div_giros').addClass('d-none');
        }
        //Mostramos el boton de agregar cliente
        $('#btn_agregar_giro').removeClass('d-none');
        //$('#btn_agregar_giro').addClass('d-block');
        Vue.swal.close();
        var ccc=JSON.parse(resp[0]);

        document.getElementById("id_cliente").value=ccc.id;
        elid=ccc.id;
        document.getElementById("rut").value=ccc.rut;
        tipo_cliente(ccc.tipo_cliente);
        if(ccc.tipo_cliente==0){
            document.getElementById("cliente_natural").checked=true;
        }else{
            document.getElementById("cliente_empresa").checked=true;
        }
        document.getElementById("nombres").value=ccc.nombres;
        document.getElementById("apellidos").value=ccc.apellidos;
        document.getElementById("empresa").value=ccc.empresa;
        document.getElementById("giro").value=ccc.giro;
        document.getElementById("direccion").value=ccc.direccion;
        document.getElementById("comuna").value=ccc.direccion_comuna;
        document.getElementById("ciudad").value=ccc.direccion_ciudad;
        document.getElementById("telf1").value=ccc.telf1;
        document.getElementById("telf2").value=ccc.telf2;
        document.getElementById("email").value=ccc.email;
        document.getElementById("contacto").value=ccc.contacto;
        document.getElementById("telfc").value=ccc.telfc;

        if(ccc.credito==1)
        {
          document.getElementById("credito_check").checked=true;
          document.getElementById("select_limites").disabled=false;
          document.getElementById("select_limites").value=ccc.limite;
          document.getElementById("dias").disabled=false;
          document.getElementById("dias").value=ccc.dias;
        }else{
          document.getElementById("select_limites").disabled=true;
          document.getElementById("credito_check").checked=false;
          document.getElementById("dias").disabled=true;
        }

        if(ccc.descuento==1)
        {
          switch(ccc.tipo_descuento)
          {
            case 1:
              document.getElementById("desc_general").checked=true;
              document.getElementById("desc_porcentaje").value=ccc.porcentaje;
              document.getElementById("desc_porcentaje").disabled=false;
            break;
            case 2:
              document.getElementById("desc_alcosto").checked=true;
              document.getElementById("desc_porcentaje").value=ccc.porcentaje;
              document.getElementById("desc_porcentaje").disabled=false;
            break;
            case 3:
              document.getElementById("desc_familia").checked=true;
              document.getElementById("desc_porcentaje").disabled=true;
              //activar los demas controles...
              document.getElementById('zona_familia').style.visibility="visible";
              document.getElementById('Familia_porcentaje').value=0;
              cargar_descuentos(idc);
            break;
          }
          document.getElementById("desc_porcentaje").value=ccc.porcentaje;

        }else{ // Sin descuento
          document.getElementById("desc_ninguno").checked=true;
          document.getElementById("desc_porcentaje").disabled=true;
          document.getElementById("desc_porcentaje").value=0;
          document.getElementById('zona_familia').style.visibility="hidden";
          document.getElementById('Familia_porcentaje').value=0;
        }

        if(ccc.tipo_descuento!=3)
        {
          document.getElementById('zona_familia').style.visibility="hidden";
          document.getElementById('Familia_porcentaje').value=0;
          borrar_descuentos();
        }

        //$('#mensajes').html('Cliente cargado... : '+ccc.id);
        cargar_referencias_cliente(ccc.id);
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
    //console.log("cargar cliente, modifica: "+modifica)
  }

  function cargar_referencias_cliente(idc){
    let url='{{url("/clientes/dame_tipo_documentos")}}'+"/"+idc;
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        if(resp.length==0){
            $("#documentos_listado").html("Cliente sin Documentos de Referencia");
        }else{
            let listadito="<table class='table table-sm table-hover'><thead><th scope='col' width='300px'>Documento</th><th scope='col'></th></thead><tbody>";
            resp.forEach(function(doc){
                listadito+="<tr><td>"+doc.nombre_documento+"</td><td><button class='btn btn-danger btn-sm' onclick='borrar_documento_cliente("+doc.id+")'>X</button></td></tr>";
            });
            listadito+="</tbody></table>";
            $("#documentos_listado").html(listadito);
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

  function borrar_documento_cliente(iddoc){
    let idcliente=document.getElementById("id_cliente").value;
    if(idcliente==-1){
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
    let url='{{url("/clientes/borrar_documento_cliente")}}/'+idcliente+"&"+iddoc;
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        if(resp.length==0){
            $("#documentos_listado").html("Cliente sin Documentos de Referencia");
        }else{
            cargar_referencias_cliente(idcliente);
            Vue.swal({
                text: 'Documento Borrado',
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
    });
  }

  function cargar_documentos(){
    let url='{{url("/dame_tipo_documentos")}}';
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        $("#documentos_select option").remove();
        $('#documentos_select').append('<option value="0">Elija un Documento</option>');
        resp.forEach(function(doc){
            $("#documentos_select").append('<option value="'+doc.id+'">'+doc.nombre_documento+'</option>');
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
  }

  function cargar_descuentos(idc)
  {
    var url='{{url("clientes")}}'+'/'+idc+'/cargardescuentos';
    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Cargando Descuentos...");
      },
      url:url,
      success:function(resp){
        $("#familia_desc").html(resp);
        //$('#mensajes').html("Cliente con descuentos...");
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

function borrar_descuentos()
{
//Borrar la tabla temporal de descuentos.
    var url='{{url("clientes/borrarfamtodo")}}'; //petición
    //modifica=false;

    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Borrar temporal descuentos...");
        //espere("Borrando descuento...");
      },
      url:url,
      success:function(resp){
        //Vue.swal.close();
        //$('#mensajes').html("Borrar temporal descuentos...");
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

  function nuevo_cliente()
  {
    elid=-1;
    borrar_descuentos();
    location.href="{{url('clientes')}}";
  }

  function agregar_cuenta(c)
  {
    let monto=0;
    let pago=document.getElementById('pago').value.trim();
    let deuda=document.getElementById('deuda').value.trim();
    let referencia=document.getElementById('referencia').value.trim();
    if(c==1){ //pago
        if(pago.length==0 || isNaN(parseInt(pago)) || parseInt(pago)==0){
            Vue.swal({
                text: 'Ingrese un Pago entero mayor a 0',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("pago").value=0;
            return false;
        }
        monto=pago;
    }
    if(c==2){ //deuda
        if(deuda.length==0 || isNaN(parseInt(deuda)) || parseInt(deuda)==0){
            Vue.swal({
                text: 'Ingrese una Deuda entera mayor a 0',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("deuda").value=0;
            return false;
        }
        monto=deuda;
    }
    if(referencia.length==0){
        Vue.swal({
            text: 'Ingrese una Referencia',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    var url='{{url("clientes/agrega_cuenta")}}';
    var parametros={
        id_cliente:elid,
        tipo_operacion:c,
        monto:monto,
        referencia:referencia
    };


    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      espere("Guardando Cuenta...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        $("#listar_clientes").html(resp);
        Vue.swal({
            text: 'Cuenta Agregada...',
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


  }

  function agregar_documento(){
    let idcliente=document.getElementById("id_cliente").value;
    if(idcliente==-1){
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
    let docu=document.getElementById("documentos_select").value;
    if(docu==0){
        Vue.swal({
            text: 'Elija un Documento',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let url='{{url("clientes/agregar_documento")}}'+"/"+idcliente+"&"+docu;
    $.ajax({
        type:'GET',
        url:url,
        success:function(resp){
            if(resp==-1){
                Vue.swal({
                    text: 'Documento ya existe...',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }else if(resp==-2){
                $("#documentos_listado").html("Cliente sin Documentos de Referencia");
            }else{

                let listadito="";
                cargar_referencias_cliente(idcliente);
                Vue.swal({
                    text: 'Documento Agregado...',
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

    });

  }

  function revisarCredito()
  {
    var check=document.getElementById("credito_check");

    if(check.checked)
    {
      document.getElementById("select_limites").disabled=false;
      document.getElementById("dias").disabled=false;
    }else{
      document.getElementById("select_limites").disabled=true;
      document.getElementById("dias").disabled=true;
    }
  }

  function activa_descuento(elegido)
  {
    xTipoDescuento=elegido;
    if(elegido==0)
    {
      xDescuento=0;
    }else{
      xDescuento=1;
    }

    switch(elegido)
    {
      case 0:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=true;
        document.getElementById("desc_porcentaje").value=0;
      break;
      case 1:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=false;
      break;
      case 2:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=false;
      break;
      case 3:
        document.getElementById('zona_familia').style.visibility="visible";
        document.getElementById('desc_porcentaje').disabled=true;
        document.getElementById("desc_porcentaje").value=0;
        document.getElementById('Familia_porcentaje').value=0;
        //cargar descuentos si hay
      break;
      default:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=true;
      break;
    }
  }

  function agregar_familia()
  {
    var url='{{url("clientes/descfam")}}';
    var porc=document.getElementById("Familia_porcentaje").value;
    if(porc.trim().length==0 || isNaN(parseInt(porc)) || parseInt(porc)==0){
        Vue.swal({
            text: 'Ingrese porcentaje entero mayor a 0',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        document.getElementById("Familia_porcentaje").value=0;
        return false;
    }
    if(parseInt(porc)>10){
        Vue.swal({
            text: 'Descuento mayor a 10%',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        document.getElementById("Familia_porcentaje").value=0;
        return false;
    }
    var usu_id="{{Session::get('usuario_id')}}";
    var parametros={
        id_familia:document.getElementById("Familia_id").value,
        porcentaje:porc,
        usuarios_id:usu_id
    };


    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Guardando Descuento, espere por favor...");
      espere("Guardando Descuento...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        $("#familia_desc").html(resp);
        //$("#mensajes").html("Descuento Guardando");
        Vue.swal({
            text: 'Descuento Guardado...',
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


  }

  function quitar_familia(id)
  {

      var url='{{url("clientes")}}'+'/'+id+'/borrarfam'; //petición

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Borrando Descuento...");
          espere("Borrando Descuento");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
          $("#familia_desc").html(resp);
          Vue.swal({
                    text: 'Descuento por Familia Borrado',
                    position: 'top-end',
                    icon: 'warning',
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


  }


  function ver_cliente()
  {
      alert("ID: "+document.getElementById("id_cliente").value);
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

  function guardar_cliente() // En inventario.ventas_principal.blade.php también hay metodo javascript agregar_cliente_rapido()
  {
    //REVISAR RUT
    //le ponemos uppercase por si el último dígito es "k"
    var el_rut=document.getElementById("rut").value.toString().trim().toUpperCase();
    el_rut=el_rut.replace("-","");
    document.getElementById("rut").value=el_rut;

    if(el_rut.length<8)
    {
      Vue.swal({
        text: 'RUT debe tener mínimo 8 dígitos',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    if(!RUN_correcto(el_rut))
    {
      Vue.swal({
        text: 'RUT INVÁLIDO... DIGITO VERIFICADOR INVÁLIDO...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    let tipo_cliente=document.querySelector("input[name=tipo_cliente]:checked").value

    if(tipo_cliente==0){
        var el_nombre=document.getElementById("nombres").value.toString();
        if(el_nombre.length<2)
        {
        Vue.swal({
            text: 'Falta Nombre o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }

        var el_apellido=document.getElementById("apellidos").value.toString();
        if(el_nombre.length<2)
        {
        Vue.swal({
            text: 'Falta Apellido o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }
    }

    if(tipo_cliente==1){
        var razon_social=document.getElementById("empresa").value.toString();
        if(razon_social.length<5)
        {
        Vue.swal({
            text: 'Falta Razón Social o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }
    }

    var el_giro=document.getElementById("giro").value.toString();
    if(el_giro.length<5)
    {
      Vue.swal({
        text: 'Falta Giro o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    var la_direccion=document.getElementById("direccion").value.toString();
    if(la_direccion.length<10)
    {
      Vue.swal({
        text: 'Falta Dirección o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    var la_comuna=document.getElementById("comuna").value.toString();
    if(la_comuna.length<4)
    {
      Vue.swal({
        text: 'Falta Comuna o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    var la_ciudad=document.getElementById("ciudad").value.toString();
    if(la_ciudad.length<2)
    {
      Vue.swal({
        text: 'Falta Ciudad o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    var el_email=document.getElementById("email").value.toString();
    if(el_email.length<10)
    {
      Vue.swal({
        text: 'Falta Email o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }


    var url="{{url('clientes/guardar')}}";
    var credito_check=document.getElementById("credito_check");

    if(credito_check.checked)
    {
      var xLimite=document.getElementById("select_limites").value;
      var xDia=document.getElementById("dias").value;
      var xCredito=1;
    }else{
      var xLimite=0;
      var xDia=0;
      var xCredito=0;
    }

    if(document.getElementById("desc_ninguno").checked) xTipoDescuento=0;
    if(document.getElementById("desc_general").checked) xTipoDescuento=1;
    if(document.getElementById("desc_alcosto").checked) xTipoDescuento=2;
    if(document.getElementById("desc_familia").checked) xTipoDescuento=3;

    if(xTipoDescuento>0)
    {
      xDescuento=1;
    }else{
      xDescuento=0;
    }

    if(modifica)
    {
      var mdfk=1;
      elid=document.getElementById("id_cliente").value;
    }else{ //Nuevo Cliente
      var mdfk=0;
      elid=-1;
    }
    var parametros={
        id_cliente:elid,
        modifika:mdfk,
        rut:el_rut,
        tipo_cliente:tipo_cliente,
        nombres:el_nombre,
        apellidos:document.getElementById("apellidos").value,
        empresa:razon_social,
        giro:document.getElementById("giro").value,
        direccion:document.getElementById("direccion").value,
        direccion_comuna:document.getElementById("comuna").value,
        direccion_ciudad:document.getElementById("ciudad").value,
        telf1:document.getElementById("telf1").value,
        telf2:document.getElementById("telf2").value,
        email:document.getElementById("email").value,
        contacto:document.getElementById("contacto").value,
        telfc:document.getElementById("telfc").value,
        limite:xLimite,
        dia:xDia,
        credito:xCredito,
        descuento:xDescuento,
        tipodescuento:xTipoDescuento,
        porcentaje:document.getElementById("desc_porcentaje").value
      };

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Guardando, espere por favor...");
      espere("Guardando Cliente...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
      //Si el primer carácter no se puede convertir en número, parseInt devuelve NaN.
      var x=parseInt(resp);
      if(isNaN(x))
      {
        $("#mensajes").html(resp);
      }else{
        //devuelve el ID de la tabla cliente
        if(resp>0)
          document.getElementById("id_cliente").value=resp;
          //$("#mensajes").html("Cliente Guardado... ID: "+resp);
          Vue.swal({
                text: 'Cliente Guardado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            $("#listar_clientes").html("");
            limpiar_controles();
      }

    },
    error: function(error){
        $Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
      });

    }

    function guardar_cliente_xpress(){

    }

  window.onload=function() {
    document.getElementById("select_limites").disabled=true;
    document.getElementById("dias").disabled=true;
    document.getElementById('desc_porcentaje').disabled=true;
    document.getElementById('desc_porcentaje').value=0;
    document.getElementById("id_cliente").value=-1;
    //document.getElementById("zona_descuento").style.visibility="hidden";
    document.getElementById("zona_familia").style.visibility="hidden";
    document.getElementById('Familia_porcentaje').value=0;
    //IMPEDIR CTRL+V
    /*
    var rut = document.getElementById("rut");
    rut.onpaste = function(e)
    {
      e.preventDefault();
      alert("Digite el RUT...");
    }
    */
    cargar_documentos();
  }

  function nuevo_giro(){
    $('#div_nuevo_giro').fadeIn("slow");
  }

  function agregar_giro(){
    let nuevo_giro = $('#nuevo_giro').val();
    if(nuevo_giro.trim() == 0 || nuevo_giro == ''){
      Vue.swal({
        icon:'error',
        text:'Giro vacio'
      });
      return false;
    }

    if(elid !== -1){
      let data = {
        giro: nuevo_giro,
        id_cliente: elid
      }

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type:'post',
        data: data,
        url:'/clientes/agregar_giro',
        success: function(resp){
          if(resp == 'OK'){
              Vue.swal({
                text: 'Listo...',
                position: 'top-end',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          }
        },
        error: function(error){
          Vue.swal({
                text: error.responseText,
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }
      })
    }
  }

  function ocultar_nuevo_giro(){
    $('#div_nuevo_giro').fadeOut("slow");
  }

  function limpiar_controles()
  {
    $("#documentos_listado").html("SIN REFERENCIAS");
    document.getElementById("id_cliente").value=-1;
    document.getElementById("rut").value="";
    document.getElementById("nombres").value="";
    document.getElementById("apellidos").value="";
    document.getElementById("empresa").value="";
    document.getElementById("giro").value="";
    document.getElementById("direccion").value="";
    document.getElementById("comuna").value="ARICA";
    document.getElementById("ciudad").value="ARICA";
    document.getElementById("telf1").value="---";
    document.getElementById("telf2").value="---";
    document.getElementById("email").value="sinemail@gmail.com";
    document.getElementById("contacto").value="---";
    document.getElementById("telfc").value="---";

    document.getElementById("credito_check").checked=false;
    document.getElementById("select_limites").disabled=true;
    document.getElementById("select_limites").value=0;
    document.getElementById("dias").disabled=true;
    document.getElementById("dias").value=0;

    document.getElementById("desc_general").checked=false;
    document.getElementById("desc_alcosto").checked=false;
    document.getElementById("desc_familia").checked=false;
    document.getElementById("desc_ninguno").checked=true;
    document.getElementById("desc_porcentaje").value=0;
    document.getElementById("desc_porcentaje").disabled=true;

    document.getElementById('zona_familia').style.visibility="hidden";
    document.getElementById('Familia_porcentaje').value=0;
    document.getElementById("rut").focus();
    modifica=false;
    elid=-1;
    borrar_descuentos();

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
            
            #datos_generales{
              height: 100%;
              max-height: 900px;
            }

            .logo_{
              width: 120px;
            }

            .contenedor{
              background: #f2f4a9; 
              width:100%; 
              margin: 0px auto;
              border: 1px solid black;
              border-radius: 10px; 
            }

        </style>
    @endsection
  @section('contenido_titulo_pagina')
<center><h4 class="titulazo">Clientes</h4></center><br>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
  <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
@include('fragm.mensajes')

<div class="row mt-2 mb-2 p-3 contenedor" >

  <legend>Buscar Cliente</legend>
  <div class="col-sm-3">
    <input type="radio" name="buscapor" id="buscaxrut" checked="true">
    <label for="buscaxrut">RUT</label>
    &nbsp;&nbsp;&nbsp;
    <input type="radio" name="buscapor" id="buscaxnombres" >
    <label for="buscaxnombres">Nombres</label>
  </div>
  <div class="col-sm-3" style="padding-left:5px;padding-right:5px">
    <input type="text" placeholder="Ingrese búsqueda" id="buscado" onkeyup="enter_buscar(event)" style="width:100%;padding-left:5px;padding-right:5px">
  </div>
  <div class="col-sm-4">
    <button onclick="buscar_clientes()" class="btn btn-success btn-sm">Buscar</button>
  </div>

</div>
<div class="row contenedor"> <!-- Contiene a las 2 partes -->
<!-- INGRESAR/MODIFICAR CLIENTES -->
    <div class="col-sm-4" id="datos_generales" style="background-color: #e8ffff;">
      <div id="mensajes"></div>
      
        <legend>Datos Generales:</legend>
        <input type="hidden" id="id_cliente">
      <div class="row m-2">
        <div class="col-sm-12">
          RUT:<abbr title="Ingrese el RUT sin puntos ni guiones. Sólo números y letra K."><input type="text" id="rut" size="12" maxlength="10" onKeyPress="return soloNumeros(event)"></abbr>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-4"><button onclick="guardar_cliente()" id="btnGuardar" class="btn btn-success btn-sm">Guardar</button></div>
        <div class="col-sm-4"><button onclick="nuevo_cliente()" class="btn btn-info btn-sm">Nuevo</button></div>
        <div class="col-sm-4"><button onclick="cuenta_cliente()" class="btn btn-warning btn-sm">Cuenta</button></div>
      </div>
        

      
      <div class="row">
        <div class="col-sm-12" style="display:float; margin: 10px;">
          <input type="radio" name="tipo_cliente" value="0" id="cliente_natural" onclick="tipo_cliente(0)" checked="true">
          <label for="cliente_natural">Persona Natural</label>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="tipo_cliente" value="1" id="cliente_empresa" onclick="tipo_cliente(1)">
          <label for="cliente_empresa">Empresa</label>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6">Nombres:<br><input type="text" id="nombres" size="20"></div>
        <div class="col-sm-6">Apellidos:<br><input type="text" id="apellidos" size="20"></div>
      </div>
      <div class="row">
        <div class="col-sm-10">Razón Social:<br><input type="text" id="empresa" size="40" disabled></div>
      </div>
      <div class="row" id="div_giros_antiguos">
        <div class="col-sm-10">Giro:<br><input type="text" id="giro" size="40"><button class="btn btn-success btn-sm d-none" id="btn_agregar_giro" onclick="nuevo_giro()">+</button></div>

      </div>
      <div class="row d-none" id="div_giros">
        <div class="col-sm-10">Giros: <br>
          <select name="select_giros" id="select_giros"> 
            <option value="0">GIRO1</option>
            <option value="1">GIRO2</option>
          </select>
          <button class="btn btn-success btn-sm" id="btn_agregar_giro" onclick="nuevo_giro()">+</button>
        </div>

      </div>
      <div class="row" style="display: none;" id="div_nuevo_giro">
        <div class="col-sm-10">Nuevo Giro:<br><input type="text" id="nuevo_giro" size="40"><button class="btn btn-success btn-sm" onclick="agregar_giro()">+</button><button class="btn btn-danger btn-sm" onclick="ocultar_nuevo_giro()">Ocultar</button></div>
      </div>
      <div class="row">
        <div class="col-sm-10">Dirección:<br><input type="text" id="direccion" size="40"></div>
      </div>
      <div class="row">
        <div class="col-sm-10">Comuna:<br><input type="text" value="ARICA" id="comuna" size="40"></div>
      </div>
      <div class="row">
        <div class="col-sm-10">Ciudad:<br><input type="text" value="ARICA" id="ciudad" size="40"></div>
      </div>
      <div class="row">
        <div class="col-sm-10">Email:<br><input type="text" value="sinemail@gmail.com" id="email" size="40"></div>
      </div>
      <div class="row">
        <div class="col-sm-6">Telf1:<input type="text" value="---" id="telf1" size="20"></div>
        <div class="col-sm-6">Telf2:<input type="text" value="---" id="telf2" size="20"></div>
      </div>
      <div class="row">
        <div class="col-sm-6">Contacto:<br><input type="text" value="---" id="contacto" size="28"></div>
        <div class="col-sm-6">Telf. Contacto:<br><input type="text" value="---" id="telfc" size="15"></div>
      </div>

      <br>

      <div class="row" style="background-color:  #66ccff;">
        <div class="col-sm-3"><input type="checkbox" id="credito_check" onclick="revisarCredito()">Crédito</div>
        <div class="col-sm-5">
          Límite:
          @if($limites->count()>0)
          <select id = "select_limites">
            @foreach($limites as $limite)
               <option value = "{{$limite->valor}}">{{$limite->valor}}</option>
            @endforeach
          </select>
          @else
            <select id = "select_limites">
               <option value = "">Vacio</option>
          </select>
          @endif

        </div>
        <div class="col-sm-4">
          Días:
          @if($dias->count()>0)
          <select id = "dias">
            @foreach($dias as $dia)
               <option value = "{{$dia->valor}}">{{$dia->valor}}</option>
            @endforeach
          </select>
          @else
            <select id = "dias">
               <option value = "">Vacio</option>
          </select>
          @endif
        </div>
      </div>
      <br>
      <div class="row" style="display:block">
        <div class="row">
            <div class="col-sm-12">
                <h4>REFERENCIAS:</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8" style="margin-left:15px;">
                <select name="" id="documentos_select" class="form-control form-control-sm" style="width:90%; padding-left:5px">
                    <option value="0"></option>
                </select>
            </div>
            <div class="col-sm-2" style="padding:0px">
                <button class="btn btn-success form-control-sm" style="padding-top:2px" onclick="agregar_documento()">+</button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" id="documentos_listado">
                SIN REFERENCIAS
            </div>
        </div>
      </div>
<br>
      <div class="row">
        <fieldset id="zona_descuento">
            <div class="row">
                <div class="col-sm-12">
                    <h4>DESCUENTOS:</h4>
                    <input type="radio" name="descuento" value="0" id="desc_ninguno" onclick="activa_descuento(0)" checked="true">
                    <label for="desc_ninguno">Ninguno</label>
                    <br>
                    <input type="radio" name="descuento" value="1" id="desc_general" onclick="activa_descuento(1)">
                    <label for="desc_general">General</label>
                    &nbsp;&nbsp;
                    <input type="radio" name="descuento" value="2" id="desc_alcosto" onclick="activa_descuento(2)">
                    <label for="desc_alcosto">Al Costo</label>
                    &nbsp;&nbsp;
                    <input type="text" placeholder="Porcentaje" id="desc_porcentaje" size="10px" value="0">
                </div>
            </div>
            <div class="row">
              <div class="col-sm-10">
                <input type="radio" name="descuento" value="3" id="desc_familia" onclick="activa_descuento(3)">
                <label for="desc_familia">Por Familia</label>
              </div>
            </div>
        </fieldset>
      </div>


            <fieldset id="zona_familia" style="visibility: hidden;width:100%">
                <div class="row" style="height:250px">
                <div class="col-sm-8" id="familia_desc" style="padding:1px">
                <p>Sin descuentos agregados</p>
                </div>
                <div class="col-sm-4">
                <div class="row mb-1">
                    <label for="Familia_id">Familia:&nbsp;&nbsp;</label><br>
                    @if($familias->count()>0)
                        <select id = "Familia_id" style="width: 100%;">
                            @foreach($familias as $familia)
                                <option value = "{{$familia->id}}">{{strtoupper($familia->nombrefamilia)}}</option>
                            @endforeach
                        </select>
                    @else
                        <p>No hay familias definidas</p>
                    @endif
                </div>
                <div class="row mb-1">Porcentaje:&nbsp;
                    <input type="text" placeholder="porcentaje" id="Familia_porcentaje" style="width:30%" maxlength="2">
                </div>
                <div class="row"><button onclick="agregar_familia()" class="btn btn-primary btn-sm">Agregar</button></div>
                </div>
            </div>
            </fieldset>

    </div> <!-- fin col-sm-4 -->


<!-- BUSCAR CLIENTES -->
    <div class="col-sm-8" style="background: #e8ffff; border-radius:10px;">
     <div class="row">
       <div class="col-sm-12">
        <legend>Listado de clientes:</legend>
       </div>
     </div>
      <div class="row" id="listar_clientes">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" style="margin: 50px;">
      </div>
    </div> <!--Fin buscar clientes -->

</div> <!-- fin row de ambas col-sm -->
</div> <!-- container fluid -->
@endsection

