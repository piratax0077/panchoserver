@extends('plantillas.app')

@section('titulo','Arqueo de caja')

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

    function abrirModal(){
        $('#myModal').on('shown.bs.modal', function(){
            $('#myInput').trigger('focus');
        })
        $('#myModal').modal('show')
    }

    window.onload= function(e){
        const fecha = new Date();
        console.log(fecha);
        $('#fecha').append(fecha.toLocaleDateString());
        document.querySelectorAll("#dinero *").forEach(el => el.setAttribute("disabled", "true"));
        
        document.getElementById('avatar_cajero').style.display = 'none';
        var total=document.getElementById("total");
      
        abrirModal();
    }

    function verUsuario(evt){
        evt.preventDefault();
        $('a').on('click',function(event){

            let id = this.id;
            let url = '/usuarios/user/'+id;

            $('#cajero_id_hidden').val(id);
            
            $.ajax({

                type:'get',

                url: url,


                beforeSend: function(){

                    console.log('Buscando ...');

                },

            success: function(data){

              var cls = document.getElementsByClassName('btn');
              for(var i = 0; i < cls.length; i++) {
                cls[i].removeAttribute('disabled');
              }

              $('#cajero_id').empty();
              $('#cajero_id').append(data[0].id);

                $('#cajero_nombre').empty();
                $('#cajero_nombre').append( data[0].name);

                $('#cajero_rol').empty();
                $('#cajero_rol').append(data[1]);
            
                document.querySelectorAll("#dinero *").forEach(el => el.removeAttribute("disabled"));

            },

            error: function(err){

                console.log(err);

            },

            complete: function(){

                console.log('Completada');

            }

            });
        });
    }

    function enter_press(e)
    {

      var keycode = e.keyCode;

      if(keycode=='13')
      {
        pasarDinero();
      }

    }

    function pasarDinero(){
      console.log('cargando ...');
    }

    function continuar(){
      
      //Esta funcion me dará todos los datos del cajero 
      let id = $('#cajero_id_hidden').val();
      let url = '/usuarios/user/'+id;

      $.ajax({
          type:'get',
          url: url,
          beforeSend: function(){
              console.log('Buscando ...');
          },
          success: function(data){
            console.log(data);
            let image_path = data[0].image_path;
            let avatar_url = "/usuarios/avatar/"+image_path;
            let fecha = $('#fecha').val();
            $('#btn_imprimir').append(`<button class='btn btn-success btn-sm' onclick='imprimir()'>Imprimir </button>`);
            $('#cajero_nombre').val(data[0].name);
            $('#cajero_data').empty();
            $('#cajero_data').append(data[0].name);
            $('#rol_data').empty();
            $('#rol_data').append(data[1]);
            $('#telefono_data').empty();
            $('#telefono_data').append(data[0].telefono);
            $('#email_data').empty();
            $('#email_data').append(data[0].email);
            


            $('#avatar_cajero').attr('src',avatar_url);
            $('#avatar_cajero').css('display','block');
            $("#myModal").modal('hide');//ocultamos el modal
            detalle_ventas('bo',data[0].id);
          },

          error: function(err){

            console.log(err);

          },

          complete: function(){

            console.log('Completada');

          }

          });
      
    }

    function agregar_total_boletas(){
      
      let total_boletas = $('#total_boletas').val();
      if(total_boletas === ""){
        alert('ingrese cantidad de boletas');
        return false;
      }
      console.log(total_boletas);
      $('#total_boletas_detalle').empty();
      $('#total_boletas_detalle').append('El total de boletas es '+total_boletas);
      $('#detalle_boletas').addClass('desbloquear');
      // document.getElementById('detalle_boletas').className = 'desbloquear';
      $('#total_boletas').attr('disabled','true');
    }

    function ocultar(){
      $('#detalle_boletas').removeClass('desbloquear');
    }

    function validar_boletas(){
      let total_boletas = $('#total_boletas').val();
      let total_efectivo = $('#total_efectivo').val();
      let total_transbank = $('#total_transbank').val();

      let suma = parseInt(total_efectivo) + parseInt(total_transbank);

      console.log(total_boletas);
      console.log(suma);

      if(total_boletas != suma){
        alert('error en el calculo');
        return false;
      }

      alert('fine');
    }

    function detalle_ventas(doc,usu){
        let date=new Date().toISOString().slice(0, 10);;
        let fecha_falsa = '2021-11-06';

        let info=date+"&"+doc+"&"+usu;

        let url='{{url("/ventas/arqueo/detalle")}}'+'/'+info;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                $('#tabla_detalle_arqueo').html(resp);
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

    function imprimir(){
      
      let cajero_id = $('#cajero_id_hidden').val();
      let total_boletas = $('#total_boletas').val();
      let total_facturas = $('#total_facturas').val();
      let total_transbank = $('#total_transbank').val();
      let parametros = {
        cajero_id: cajero_id, 
        total_boletas: total_boletas, 
        total_facturas: total_facturas,
        total_transbank:total_transbank
        };
      
      let url='{{url("ventas/imprimir_arqueo")}}';
      $.ajaxSetup({
                  headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });

      $.ajax({
        type:'post',
        url: url,
        data: parametros,
        beforeSend: function(){
          Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
        },
        success: function(resp){
          Vue.swal.close();

          let r=JSON.parse(resp);
                if(r.estado=='OK'){
                    var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                    var w=window.open(r.mensaje,'_blank',config);
                    w.focus();
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error',
                    });
                }
        },
        error: function(error){
          console.log(error);
        }
      })
    }
</script>
@endsection

@section('style')
    <style>
        .seccion{
            height: auto; 
            text-align: center;
            width: 80%; 
            margin: 0px auto;
            padding: 10px;
        }
        
        .avatar_cajero{
          width: 100px;
          height: 100px;
          border-radius: 100px;
          margin: 5px;
        }

        

        .desbloquear{
          display: block !important;
          transition: all 300ms;
          border: 1px solid black;
          padding: 5px;
        }

        .informacion_cajero{
          background: #f2f4a9;
          border-radius: 10px;
          height: 150px;
          line-height: 100px;
        }

        .avatar_cajero_home{
          width: 150px;
          height: 140px;
          border-radius: 100px;
          margin: 5px;
        }

        .header_detalle{
          width: 80%;
          float: left;
        }
        .logo_{
          width: 150px;
        }
    </style>
@endsection

@section('contenido')
<h4 class="titulazo">Arqueo de caja</h4>

    <div class="container-fluid">
      <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_ mb-4 ml-3">
        
        <div class="informacion_cajero">
          <div class="row">
            <div class="col-md-3">
              <img src="" alt="" class="avatar_cajero_home" id="avatar_cajero">
            </div>
            <div class="col-md-3">
              <span id="cajero_data"></span>
            </div>
            <div class="col-md-2">
              <span id="rol_data"></span>
            </div>
            <div class="col-md-2">
              <span id="telefono_data"></span>
            </div>
            <div class="col-md-2">
              
              <span id="email_data"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div id="fecha">

            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div id="btn_imprimir">

            </div>
          </div>
        </div>
        
        <div id="tabla_detalle_arqueo">

        </div>
    </div>

        <div class="modal fade bd-example-modal-lg" id="myModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Ingreso</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                
                <div class="modal-body">
                    <div id="cajeros"  class="seccion">
                      <h3>Cajeros disponibles</h3>
                        <div class="row">
                        @foreach($cajeros as $cajero)
                          <div class="col-sm-12">
                            <a href="" class="ml-4" style="text-decoration: none !important;" id="{{$cajero->id}}" onclick="verUsuario(event)">

                                <img src="{{url('usuarios/avatar/'.$cajero->image_path)}}" alt="" class="avatar_cajero" >
        
                            </a>
                            </div>
                          
                        @endforeach
                      </div>
                    </div>
                    
                  
                  <div id="resumen" class="seccion">
                      <h3>Resumen</h3>
                      <table class="table">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Cajero</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Monto total</th>
                          </tr>
                        </thead>
                        <tbody id="t_body">
                            <tr>
                                <th id="cajero_id"></th>
                                <td id="cajero_nombre"></td>
                                <td id="cajero_rol"></td>
                                <td id="cajero_monto"></td>
                              </tr>
                        </tbody>
                      </table>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="continuar()" disabled>Continuar</button>
                  <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="location.href='/home'">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
        <!--Datos de vital importancia -->
        <input type="hidden" name="cajero_id_hidden" id="cajero_id_hidden">
@endsection