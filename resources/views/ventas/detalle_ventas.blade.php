@extends('plantillas.app')
@section('titulo','Detalle de ventas')
@section('style')
<style>
    
    .periodo{
        background-color: #f2f4a9;
        grid-column: 1/4;
        grid-row:2/3;
        display:grid;
        grid-template-columns: 2fr 2fr 1fr;
        grid-auto-flow: column;
        margin: 10px 0px 30px 0px;
    }

    #tipo_dte{
        background-color: khaki;
        grid-column: 1/4;
        display:grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-auto-flow: column;
    }
    #fechainicial{
        align-self: center;
        justify-self: center;
        grid-column: 1/2;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: column;
    }
    #fechafinal{
        align-self: center;
        justify-self: center;
        grid-column: 2/3;
        grid-row: 2/3;
        display:grid;
        grid-auto-flow: column;
    }
    #boton_buscar{
        align-self: center;
        justify-self: center;
        grid-column: 3/4;
        grid-row: 2/3;
    }
   
    p{
        margin-bottom: 0px;
    }

    #listado_detalle_ventas{
        width: 45%;
        float: left;
        overflow-y: scroll;
        height: 350px;
    }

    #detalle_boleta{
        width: 45%;
        float: right;
    }

</style>
@endsection
@section('javascript')
    <script>
        function listar_detalle_ventas(){
            let fechainicial = $('#ifechainicial').val();
            let fechafinal = $('#ifechafinal').val();
            let tipodte=$('input[name="tipo_dte"]:checked').val().trim();

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
        let url='{{url("/ventas/dameventasporfechas")}}'+'/'+tipodte+"/"+fechainicial+'/'+fechafinal;
        
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                
                $('#listado_detalle_ventas').html(resp);
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

        function enter_buscar_documento(){
            if(event.key === 'Enter'){
                cargar_documento();
            }
        }

        function cargar_detalle_num_doc(num_doc,tipodte){
            let url = '{{url("/ventas/damedetalleboleta_num_doc")}}'+"/"+tipodte+"/"+num_doc;
            console.log(url);
            $('#numero_dte').val(num_doc);
            $('#tipo_dte').val(tipodte);
            $.ajax({
                type:'GET',
                url: url,
                beforeSend: function(){
                    $('#detalle_boleta').html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>');
                },
                success: function(resp){
                    
                    $('#detalle_boleta').html(resp);  
                    // valor del input con id es_credito
                    let acredito = $('#es_credito').val();
                    // si acredito es 1 entonces el checkbox con id acredito se chequea
                    if(acredito == 1){
                        $('#acredito').prop('checked', true);
                    }else{
                        $('#acredito').prop('checked', false);
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function cargar_detalle(num_boleta,tipodte){
            let url = '{{url("/ventas/damedetalleboleta")}}'+"/"+tipodte+"/"+num_boleta;
            $('#numero_dte').val(num_boleta);
            $('#tipo_dte').val(tipodte);
            $.ajax({
                type:'GET',
                url: url,
                success: function(resp){
                    
                    $('#detalle_boleta').html(resp);  
                    // valor del input con id es_credito
                    let acredito = $('#es_credito').val();
                    // si acredito es 1 entonces el checkbox con id acredito se chequea
                    if(acredito == 1){
                        $('#acredito').prop('checked', true);
                    }else{
                        $('#acredito').prop('checked', false);
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function modificarDte(){
            let num_dte = $('#numero_dte').val();
            let tipo_dte = $('#tipo_dte').val();
            // verificar si el checkbox con id acredito esta chequeado
            let acredito = $('#acredito').is(':checked');
            // si acredito esta seleccionado entonces el valor de acredito es 1, sino es 0
            let valor_acredito = acredito ? 1 : 0;

            let url = '{{url("/ventas/modificarDte")}}'+"/"+valor_acredito+"/"+num_dte+"/"+tipo_dte;
            
            $.ajax({
                type:'GET',
                url: url,
                success: function(resp){
                    if(resp == 'OK'){
                        Vue.swal({
                            title: 'OK',
                            text: 'DTE modificado correctamente',
                            icon: 'success',
                            });
                            // esconder el modal
                            $('#modalModificarBoleta').modal('hide');
                            // esconder el div con id detalle_boleta
                            $('#detalle_boleta').html('');
                            listar_detalle_ventas();
                    }else{
                        Vue.swal({
                            title: 'ERROR',
                            text: resp,
                            icon: 'error',
                            });
                    } 
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function soloNumeros(e)
        {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
        }

        function cargar_documento()
        {
            // obtener el valor del input de tipo radio con nombre tipo_dte
            let tipo_dte = $('input[name="tipo_dte"]:checked').val();
            // obtener el valor del input con id num_documento
            let num_documento = $('#num_documento').val();
            // si el valor de num_documento es igual a 0 entonces mostrar mensaje de error
            if(num_documento == 0 || num_documento.trim().length == 0){
                Vue.swal({
                    title: 'ERROR',
                    text: 'Ingrese número de documento',
                    icon: 'error',
                    });
                return false;
            }
            // si el valor de num_documento es igual al valor del input con id num_documento_h entonces mostrar mensaje de error
            if(num_documento == $('#num_documento_h').val()){
                Vue.swal({
                    title: 'ERROR',
                    text: 'Ingrese número de documento diferente',
                    icon: 'error',
                    });
                return false;
            }

            console.log('tipo_dte: '+tipo_dte);
            console.log('num_documento: '+num_documento);

            cargar_detalle_num_doc(num_documento,tipo_dte);

        }

    </script>
@endsection
@section('contenido')
    <h4 class="titulazo">Detalle de ventas</h4>
    
    <div class="container-fluid">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
        <div class="row">
            <div class="col-md-9">
                <div class="periodo">
                    <div id="tipo_dte">
                        <div style="padding-left:2px"><strong>Documento:</strong></div>
            
                        <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_boleta" value="39" checked><label for="radio_boleta">Boleta</label></div>
                        <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_factura" value="33"><label for="radio_factura">Factura</label></div>
                        <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notacredito" value="61"><label for="radio_notacredito">Nota Cred.</label></div>
                        <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_notadebito" value="56" disabled><label for="radio_notadebito">Nota Deb.</label></div>
                        <div style="padding-left:2px"><input type="radio" name="tipo_dte" id="radio_guiadespacho" value="52"><label for="radio_guiadespacho">Guía Desp.</label></div>
                    </div>
                    <div id="fechainicial">
                        <label for="fechainicial">Fecha Inicial:</label>
                        <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control form-control-sm">
                    </div>
                    <div id="fechafinal">
                        <label for="fechafinal">Fecha Final:</label>
                        <input type="date" name="ifechafinal" value='@php echo date("Y-m-d"); @endphp'  id="ifechafinal" class="form-control form-control-sm">
                    </div>
                    <div id="boton_buscar">
                        <button class="btn btn-success btn-lg" onclick="listar_detalle_ventas()">Buscar</button>
                    </div>
                   
                
                </div>
            </div>
            <div class="col-md-3 border pt-3" style="background-color: #f2f4a9;">
                <!-- fila 1 -->
                <div class="row ml-1 w-100">
                            
                    <div class="col-md-9" >
                        <input type="text" id="num_documento" class="form-control" placeholder="Ingrese Número" maxlength="15" size="13" onkeyup="enter_buscar_documento()" onKeyPress="return soloNumeros(event)">
                        <input type="hidden" id="num_documento_h" value="0">
                        <input type="hidden" id="total_nc" value="0">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm" onclick="cargar_documento()">Buscar</button>
                    </div>
                </div>  <!-- FIN fila 1 -->
            </div>
        </div>
        
    
        
        
        <div id="listado_detalle_ventas">
            
        </div>
        <div id="detalle_boleta">

        </div>
    </div>
    <!-- DATOS DE VITAL IMPORTANCIA -->
    <input type="hidden" name="numero_dte" id="numero_dte">
    <input type="hidden" name="tipo_dte" id="tipo_dte">
    <!-- Modal -->
<div class="modal fade" id="modalModificarBoleta" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modificar DTE</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="acredito">¿Es crédito?</label>
            <input type="checkbox" name="acredito" id="acredito" class="form-control form-control-sm">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success btn-sm" onclick="modificarDte()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
@endsection