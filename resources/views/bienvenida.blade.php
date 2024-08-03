@extends('plantillas.app')
@section('titulo','Bienvenida')
@section('javascript')
<script type="text/javascript">
    function hola()
    {
        console.log("HOLA");
        $("#saludo").html("HOLA ESTOY SALUDANDO...");
        Vue.swal({
                    text: 'Se guardó correctamente',
                    position: 'top-end',
                    icon:'success',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
    }
</script>
@endsection

@section('style')
    <style>

    </style>
@endsection



@section('contenido_titulo_pagina')
  <center><h2>Bienvenida</h2></center><br>
@endsection

  @section('contenido_ver_datos')
    @include('fragm.mensajes')
    bienvenida
    <p id="saludo">SALUDO INICIAL</p>
    <button class="btn btn-success btn-sm" onclick="hola()">HOLA</button>
    <br>
    <br>
    <a class="btn btn-primary btn-sm" href="{{url('sii')}}">S I I</a>
    <hr><h4>RUTAS</h4>
    encerrar entre llaves dobles<br>
    base_path(): {{base_path()}}<br>
    resource_path('mis_letras'): {{resource_path('mis_letras')}}<br>
    app_path(): {{app_path()}}<br>
    public_path(): {{public_path()}}<br>
    storage_path(): {{storage_path()}}<br>
    url("/"): {{url("/")}}<br>
    asset("/storage/pdf"): {{asset("/storage/pdf")}};<br>
    miruta: {{asset("/public/storage/pdf")}};<br>
    <a href="{{asset('/storage/pdf')}}/boleta_1584658092.pdf">abrir PDF</a>
    <hr>
    <div class="container-fluid">
        <div class="row">titulo</div>
        <div class="row">
            <div class="col-4">col-4
                <div class="row" style="background-color: sandybrown">
                    menu cliente
                </div>
                <div class="row" style="background-color: skyblue">
                    tipos de documentos
                </div>
                <div class="row" style="background-color: teal">
                    familias
                </div>
                <div class="row" style="background-color: yellowgreen">
                    botoneras
                </div>
            </div>
            <div class="col-8">col-8
                <div class="row">titulo</div>
                <div class="row" style="background-color: blueviolet">detalle

                </div>
                <div class="row">
                    <div class="col-sm-4" style="background-color:mediumspringgreen">fotos<br>
                        Lorem Ipsum es simplemente el texto de relleno de las imprentas y archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar de las industrias desde el año 1500, cuando un impresor (N. del T. persona que se dedica a la imprenta) desconocido usó una galería de textos y los mezcló de tal manera que logró hacer un libro de textos especimen. No sólo sobrevivió 500 años, sino que tambien ingresó como texto de relleno en documentos electrónicos, quedando esencialmente igual al original. Fue popularizado en los 60s con la creación de las hojas "Letraset", las cuales contenian pasajes de Lorem Ipsum, y más recientemente con software de autoedición, como por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem Ipsum.
                    </div>
                    <div class="col-sm-4" style="background-color: crimson">aplicaciones<br>
                        Lorem Ipsum es simplemente el texto de relleno de las imprentas y archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar de las industrias desde el año 1500, cuando un impresor (N. del T. persona que se dedica a la imprenta) desconocido usó una galería de textos y los mezcló de tal manera que logró hacer un libro de textos especimen. No sólo sobrevivió 500 años, sino que tambien ingresó como texto de relleno en documentos electrónicos, quedando esencialmente igual al original. Fue popularizado en los 60s con la creación de las hojas "Letraset", las cuales contenian pasajes de Lorem Ipsum, y más recientemente con software de autoedición, como por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem Ipsum.
                    </div>
                    <div class="col-sm-4" style="background-color:bisque">oems<br>
                        Lorem Ipsum es simplemente el texto de relleno de las imprentas y archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar de las industrias desde el año 1500, cuando un impresor (N. del T. persona que se dedica a la imprenta) desconocido usó una galería de textos y los mezcló de tal manera que logró hacer un libro de textos especimen. No sólo sobrevivió 500 años, sino que tambien ingresó como texto de relleno en documentos electrónicos, quedando esencialmente igual al original. Fue popularizado en los 60s con la creación de las hojas "Letraset", las cuales contenian pasajes de Lorem Ipsum, y más recientemente con software de autoedición, como por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem Ipsum.
                    </div>
                </div>

            </div>
        </div>
    </div>
  @endsection
