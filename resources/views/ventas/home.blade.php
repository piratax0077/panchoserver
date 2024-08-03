@extends('plantillas.app')
@section('titulo','HOME')
@section('javascript')
<script type="text/javascript">
    function mostrar_deliverys_pendientes(){
        let f=new Date();
        let fecha_hoy= f.getFullYear() + "-" + (f.getMonth() +1) + "-" + f.getDate();
        let url='{{url("reportes/deliverys_pendientes")}}/'+fecha_hoy;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                //$('#mensajes').html("Cargando Formas de Pago...");
                },
            url:url,
            success:function(html){
                if(html=="0")
                {
                    console.log(html);
                    console.log("no hay deliverys pendientes o no es admin");
                }else{
                    $("#mostrar_pendientes").html(html);
                    $("#delivery-modal").modal("show");
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
    }
    window.onload = function(e){
            mostrar_deliverys_pendientes();
    }
</script>
@endsection
@section('style')
<style>
    #mensajes{
        background-color: #000;
        position: absolute;
        bottom: 22px;
        left:0px;
        width: 100%;
        height: 60px;
        color: white;
    }
</style>

@endsection
@section('contenido')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <img src="{{asset('storage/imagenes/home2.jpg')}}" alt="" srcset="">
        </div>
    </div>
    <div id="mensajes" style="text-align:center">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" width='100px'><br>
        @php
            $entorno = App::environment();
            echo "Entorno: ".$entorno."<br><br>";
        @endphp

    </div>
</div>
<!-- MODAL AVISAR DELIVERYS -->
<div style="margin-top:100px" class="modal fade" id="delivery-modal" tabindex="-1" role="dialog" aria-labelledby="pagar-delivery-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">AVISO</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="mostrar_pendientes"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
        </div>
      </div>
    </div>
  </div>

@endsection
