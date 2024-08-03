@extends('plantillas.app')
@section('titulo','Rendimiento')
@section('javascript')
<script>
    
    function mostrar(){
        let fecha=document.getElementById("fecha").value;
        console.log(fecha);
        let url='{{url("/reportes/totales_mensuales")}}'+'/'+fecha;
        console.log(url);
        
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'CREANDO REPORTE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                console.log(resp);
                Vue.swal.close();
                $('#totales').html(resp);
                
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

    window.onload = function(){
        mostrar();
    }
    function imprimir(){
        Vue.swal({
                    title: 'Info',
                    text: 'P R O N T O',
                    icon: 'info',
                });
    }

    function graficar(){
        Vue.swal({
                    title: 'Info',
                    text: 'P R O N T O',
                    icon: 'info',
                });
    }
</script>
@endsection

@section('style')
<style>
    #titulo{
        grid-column: 1/7;
        background-color: cornflowerblue;
    }
    #botones{
        grid-column: 1/7;
        grid-row: 2/3;
        background-color:gainsboro;
        display:grid;
        grid-template-columns: repeat(6,1fr);
        grid-auto-flow: column;
    }
    #fecha_reporte{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-auto-flow: column;
        grid-column: 1/2;
    }
    #btn_mostrar{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 2/3;
    }
    #btn_imprimir{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 3/4;
    }
    #btn_grafico{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 4/5;
    }

</style>
@endsection

@section('contenido_ingresa_datos')
<div class="titulazo">
    <center><h4>Reporte Ventas mensuales</h4></center>
</div>
    <div class="container-fluid">
        
        <div id="botones">
            <div id="fecha_reporte">
                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" value='<?php echo date("Y-m-d"); ?>' id="fecha" class="form-control  form-control-sm">
            </div>
            <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
            <div id="btn_imprimir"><button class="btn btn-sm btn-primary" onclick="imprimir()">IMPRIMIR</button></div>
            
        </div>
        <div id="totales">

        </div>
        
            
    </div>
    
@endsection