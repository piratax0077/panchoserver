@extends('plantillas.app')

@section('titulo','Historial de cotizaciones')

@section('javascript')
<script>
    function buscar_cotizacion(){
        let nombre_cotizacion = document.getElementById('nombre_cotizacion').value;
        if(nombre_cotizacion == '' || nombre_cotizacion.trim() == 0){
            Vue.swal({
                title:'info',
                text:'Debe ingresar nombre de cotización',
                icon:'error',
                position:'top-end',
                timer: 3000,
                toast: true
            });
            return false;
        }

        let url = '/ventas/buscar_cotizacion/'+nombre_cotizacion;

        $.ajax({
            type:'get',
            url: url,
            success: function(response){
                $('#panel_resultado_solicitudes').html(response);
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }
</script>
@endsection


@section('style')
<style>
    .panel_busqueda{
            display: block;
            width: 100%;
            margin: 20px 0px 20px 0px;
            border: 1px solid black;
            padding: 20px;
            border-radius: 10px;
        }
    .cantidad_traspasos{
        float: right;
        font-style: italic;
        font-weight: bold;
    }
</style>
@endsection

@section('contenido')
<p class="titulazo">Historial de cotizaciones</p>
<div class="container-fluid">
    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
    <div class="row" style="width: 100%; ">
        <div class="col-md-4">
            <div class="panel_busqueda" style="background: #f2f4a9;">
                <div class="form-group">
                  <label for="num_solicitud">Nombre de la cotización</label>
                  <input type="text" class="form-control" placeholder="Ingrese Nombre de cotización" id="nombre_cotizacion">
                </div>
                <input type="button" value="Buscar" onclick="buscar_cotizacion()" class="btn btn-success btn-sm">
                <p class="cantidad_traspasos" id="cantidad_traspasos_total"></p>
              </div>
              <div class="panel_busqueda" style="background: rgb(207, 255, 255);">
                <div class="form-group">
                    <label for="">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control">
                </div>
                <input type="button" value="Buscar" onclick="buscar_solicitud_fecha()" class="btn btn-success btn-sm">
                <p class="cantidad_traspasos" id="cantidad_traspasos"></p>
              </div>
              <div class="panel_busqueda" style="background: rgb(207, 255, 255);">
                <div class="form-group">
                    <label for="">N° de Solicitud</label>
                    <input type="text" name="num_solicitud_busqueda" id="num_solicitud_busqueda" class="form-control">
                </div>
                <input type="button" value="Buscar" onclick="buscar__numero_solicitud()" class="btn btn-success btn-sm">
              </div>
        </div>
        <div class="col-md-8">
            <div id="panel_resultado_solicitudes">
    
            </div>
        </div>
    </div>
</div>
@endsection