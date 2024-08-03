@extends('plantillas.app')
@section('javascript')
<script>
    function mostrar(){
        let mes=document.getElementById('periodo_mes').value;
            let año=document.getElementById('periodo_año').value;
            let url='{{url("/reportes/ventas_online_detalle")}}'+'/'+mes+"&"+año;
           
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
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

    function dame_detalle(fecha){
        let url='{{url("/reportes/ventas_online_dia")}}'+'/'+fecha;
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                    $('#detalle').html(resp);
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

     function confirmar_envio(numero_carrito, opcion){
        var estado_envio;
        if(opcion == 1){
            estado_envio = $( "#estado_envio_select option:selected" ).val();
        }else{
            estado_envio = $( "#estado_envio_select_retiro option:selected" ).val();
        }
     
     
        let params = {'numero_carrito': numero_carrito, 'estado' : estado_envio,'opcion' : opcion}
       
        var url = '/reportes/confirmar_envio';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            data: params,
            url: url,
            success: function(resp){
                
                let respuesta = resp[0];
                let despacho = resp[1];
                let opcion = resp[2];

                

                if(opcion == 1){
                    if(respuesta == 'OK'){
                    Vue.swal({
                        icon:'success',
                        text:'Estado cambiado exitosamente'
                    });
                }
                $('#estado_envio').empty();
                if(despacho.estado == 0){
                    $('#estado_envio').append('En espera');
                }else if(despacho.estado == 1){
                    $('#estado_envio').append('Enviado');
                }else{
                    $('#estado_envio').append('Entregado');
                }
                }else{
                    if(respuesta == 'OK'){
                    Vue.swal({
                        icon:'success',
                        text:'Estado cambiado exitosamente'
                    });
                }
                $('#estado_retiro').empty();
                if(despacho.estado == 0){
                    $('#estado_retiro').append('En espera');
                }else{
                    $('#estado_retiro').append('Entregado');
                }
                }

                
                
                
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

     function descontar_stock_bodega(codigo_interno, cantidad,numero_carrito){
        let params = {'codigo_interno': codigo_interno, 'cantidad' : cantidad,'numero_carrito': numero_carrito}

        var url = '/reportes/descontar_stock_carrito_virtual';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(resp){
                console.log(resp);
                let respuesta = resp[0];
                let repuesto = resp[1];
                if(respuesta == 'OK'){
                    Vue.swal({
                        icon:'success',
                        text:'Stock descontado de bodega'
                    });
                }
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
    }

    function dame_detalle_carrito_virtual(numero_carrito){
        let url='{{url("/reportes/detalle_carrito_virtual")}}'+'/'+numero_carrito;
            $.ajax({
                type:'GET',
                url:url,
                success:function(resp){
                  
                    $('#modal_body_detalle_carrito').html(resp);
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
@endsection
@section('style')
<style>
.contenedor{
    margin: 0px;
    display:grid;
    grid-template-columns: repeat(6,1fr);
    grid-template-rows: 30px 40px auto;
    grid-gap: 5px;
}
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
#periodo{
    grid-column: 1/2;
    align-self: center;
    justify-self: center;
    display:grid;
    grid-auto-flow: column;

}
#periodo_mes{

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
    grid-column: 6/7;
}

#cuerpo{
    grid-column:2/7;
    grid-row:3/4;
    display:grid;
    grid-template-columns: 50% 50%;
    grid-auto-flow: column;
    margin-left: 10px;
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
</style>
@endsection

@section('contenido_ingresa_datos')
<div class="contenedor">
    @php
        $año_actual=date("Y");
    @endphp
    <div class="titulazo">
        <center><h4>Ventas Online</h4></center>
    </div>
    <div id="botones">
        <div id="periodo">
            <label for="periodo_mes">Periodo:</label>
            <select name="periodo_mes" id="periodo_mes" class="form-control form-control-sm">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
            <select name="periodo_año" id="periodo_año" class="form-control form-control-sm">
                @for($an=2020;$an<=$año_actual;$an++)
                @if($an==$año_actual)
                    <option value="{{$an}}" selected>{{$an}}</option>
                @else
                    <option value="{{$an}}">{{$an}}</option>
                @endif
            @endfor
            </select>
        </div>
        <div id="btn_mostrar"><button class="btn btn-sm btn-success" onclick="mostrar()">MOSTRAR</button></div>
        <div id="btn_procesar">
            @if(Session::get('rol')=='S')
                <button class="btn btn-sm btn-primary" onclick="procesar()" style="display:none">PROCESAR</button>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-12 tabla-scroll-y-300">
            <table class="table">
                <thead>
                    <th>Nombre</th>
                    <th>Email</th>
                    
                </thead>
                <tbody>
                    @foreach($usuarios as $u)
                    <tr>
                        <td>{{$u->name}}</td>
                        <td>{{$u->email}}</td>
                        
                    </tr>
                    @endforeach
                    
                </tbody>
            </table>
        </div>
    </div>
    <div id="cuerpo">
        <div id="resumen">RESUMEN MENSUAL</div>
        <div id="detalle">RESUMEN POR DIA</div>
    </div>

</div>
@endsection