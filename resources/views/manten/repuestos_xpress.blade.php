@extends('plantillas.app')
@section('titulo','Repuestos Express')
@section('javascript')
    <script>
        window.onload = function(){
            // mostrar el modal exampleModal
            $('#exampleModal').modal('show');

            // focus en el input descripcionRepuesto usando jquery
            $('#descripcionRepuesto').focus();
        }

        function buscarRepuesto(){
            var valor = 0;
            var _descripcion=document.getElementById("descripcionRepuesto");
            var texto=_descripcion.value.trim();
                if(texto.length==0){
                    Vue.swal({
                        title: 'SI SERÁS SI SERÁS...',
                        text: "Escribe algo para buscar poooh!!",
                        icon: 'error',
                    });
                    return false;
                }

            var _donde=_descripcion.placeholder;
            var descripcion=valor+texto;
            if(descripcion.indexOf("/")){
                descripcion=descripcion.replace(/\//g,"_&_");
            }

            var url = "/repuesto/buscarRepuestoExpress/"+descripcion;

            // crear headers para peticion ajax post
            var headers = {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }

            // crear objeto FormData para enviar por ajax
            $.ajax({
                type: "get",
                url: url,
                beforeSend: function(){
                    Vue.swal({
                        title: 'Cargando...',
                        text: 'Espere un momento por favor',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        onOpen: () => {
                            Vue.swal.showLoading();
                        }
                    })
                },
                success: function(resp){
                    Vue.swal.close();
                  
                    $('#resultado').html(resp);
                },
                error: function(error){
                    console.log(error);
                }
            })
        }

        function enter_press(e)
        {    
            var keycode = e.keyCode;
            if(keycode=='13')
            {
                buscarRepuesto();
            }
        }
    </script>
@endsection

@section('style')
<style>
    .scrollable-modal-body {
        max-height: 400px; /* Altura máxima del modal-body */
        overflow-y: auto; /* Mostrar barra de scroll vertical si el contenido excede la altura */
    }
</style>
@endsection

@section('contenido')
<h4 class="titulazo">Repuestos express</h4>
<!--Modals -->
@include('modals.repuesto_modal')
@include('modals.loader')
<!--Fin Modals -->
@endsection