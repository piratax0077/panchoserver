@extends('plantillas.app')

@section('titulo','Estadisticas')

@section('javascript')
<script>
    function mostrar(){
        let fechainicial = document.getElementById("ifechainicial").value;
        let fechafinal = document.getElementById("ifechafinal").value;
        let horarioinicial = document.getElementById("ihorarioinicial").value;
        let horariofinal = document.getElementById("ihorariofinal").value;
        if(fechainicial.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Inicial',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
        if(fechafinal.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        if(horarioinicial.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Horario Inicial',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        if(horariofinal.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Horario Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        // validar el horario

        if(fechainicial>fechafinal){
            Vue.swal({
                text: 'Fecha Inicial debe ser menor o igual a Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
            let url='{{url("/ventas/estadisticas_resumen")}}'+'/'+fechainicial+"&"+fechafinal+"&"+horarioinicial+"&"+horariofinal;
            $.ajax({
                type:'GET',
                url:url,
                beforeSend: function(){
                    Vue.swal({
                        icon:'info',
                        text:'Cargando'
                    });
                },
                success:function(resp){
                    Vue.swal.close();
                    $('#resumen').html(resp);
                  
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
      </script>  
@section('style')
<style>
       
       .contenedor{
            background: #eee;
            padding: 10px;
        }
    #btn_mostrar{
        display: flex;
        justify-content: center;
        
    }

    #btn_imprimir{
        align-self: center;
        justify-self: center;
        display:grid;
        grid-column: 6/7;
    }

    #cuerpo{
        grid-column:1/7;
        grid-row:3/4;
        /* display:grid; */
        grid-template-columns: 50% 50%;
        grid-auto-flow: column;
    }
    #resumen{
        grid-column:1/2;
    }
    #detalle{
        grid-column:2/3;
    }
    .transbank{
        color:grey;
    }

    #fechainicial{
        align-self: center;
        justify-self: center;
        grid-column: 1/2;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: unset;
    }
    #fechafinal{
        align-self: center;
        justify-self: center;
        grid-column: 2/3;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: unset;
    }
</style>
@endsection
@section('contenido')
<div class="titulazo">
    <center><h4>Estadisticas</h4></center>
</div>
<div class="contenedor">
    @php
        $año_actual=date("Y");
    @endphp
    
            <div id="botones" class="row w-100">
                <div class="col-md-3">
                    <div id="fechainicial">
                        <label for="fechainicial">Fecha Inicial:</label>
                        <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="fechafinal">
                        <label for="fechafinal">Fecha Final:</label>
                        <input type="date" name="ifechafinal" value='@php echo date("Y-m-d"); @endphp'  id="ifechafinal" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-2">
                    <div id="horario_estadisticas_inicial">
                        <label for="horario_estadisticas">Horario Inicial:</label>
                        <input type="time" class="form-control" id="ihorarioinicial" value="10:05 AM" />
                          
                    </div>
                </div>
                <div class="col-md-2">
                    <div id="horario_estadisticas_final">
                        <label for="horario_estadisticas">Horario Final:</label>
                        <input type="time" class="form-control" id="ihorariofinal" value="10:05 AM" />
                          
                    </div>
                </div>
                <div class="col-md-2">
                    <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
                </div>
                    
                    
                
                
                
            </div>
       
    
    

</div>
<hr>
<div id="cuerpo">
    <div id="resumen"></div>
    <div id="detalle"></div>
</div>


@endsection