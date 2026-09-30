
<style>
    #cliente{
        margin-left: 30px;
        font-size: 23px;
        font-weight: bold;
        margin: 10px;
    }
    .box-info-cuenta{
        margin: 15px;
    }
    #cliente_empresa{
        margin-left: 30px;
        font-size: 17px;
        margin: 10px;
    }
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

    <script>


let totalDeuda = 0;

$(document).on('click', '.col-deuda', function(){

    let deuda = parseFloat($(this).data('deuda')) || 0;
    let fila = $(this).closest('tr');

    // Si ya estaba seleccionada → RESTAR
    if (fila.hasClass('fila-seleccionada')) {

        totalDeuda -= deuda;
        fila.removeClass('fila-seleccionada table-warning');

    } 
    // Si no estaba seleccionada → SUMAR
    else {

        totalDeuda += deuda;
        fila.addClass('fila-seleccionada table-warning');
    }

    // Evitar negativos por seguridad
    if (totalDeuda < 0) {
        totalDeuda = 0;
    }

    $('#total-deuda-seleccionada').text(
        totalDeuda.toLocaleString('es-CL')
    );

});



    function abrirModal(num){
        let url = '{{url("/factuprodu/damefactura")}}'+'/'+num;
        $.ajax({
            type:'get',
            url: url,
            success: function(data){
                console.log(data);
                $("#exampleModalLong").modal("show");
                $('#modal_body_factura').empty();
                // data.forEach(element => {
                //     $('#modal_body_factura').append(`
                // <p>Estado SII: `+element.estado_sii+` </p>
                // <p>Fecha de emisión:`+element.fecha_emision+`</p>
                // <p>Num factura:`+element.num_factura+`</p>
                // <p>Resultado envío:`+element.resultado_envio+`</p>
                // <p>Total:`+element.total.toFixed(3)+`</p>
                // `);
                // });
                $('#modal_body_factura').append(`
                <table style="width: 100%;">
                    <thead>
                    <tr>
                        <th scope="col">Estado SII</th>
                        <th scope="col">Fecha de emisión</th>
                        <th scope="col">Numero de factura</th>
                        <th scope="col">Resultado de envío</th>
                        <th scope="col">IVA</th>
                        <th scope="col">Neto</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">`+data[0].estado_sii+`</th>
                        <td>`+data[0].fecha_emision+`</td>
                        <td>`+data[0].num_factura+`</td>
                        <td>`+data[0].resultado_envio+`</td>
                        <td>`+data[0].iva+`</td>
                        <td>`+data[0].neto+`</td>
                        <td>`+data[0].total+` </td>
                    </tr>
                </tbody>
                </table>
                `);
            },
            error: function(error){
                console.log(error.responseText);
            }
        });
        
    }

    function imprimir_xml(xml){
        let url='{{url("imprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
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
                Vue.swal({
                    title: 'ERROR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });

            }

        });

    }

//     function borrar_cuenta_clave(idop){
//         Vue.swal({
//                 title: 'Ingrese Contraseña',
//                 input: 'password',
//                 confirmButtonText: 'Verificar',
//                 showConfirmButton: true,
//                 showCancelButton: true,
//         }).then((result) => {
//             if(result.isConfirmed){
//                 let clave=result.value;
//                 //Pedir contraseña
//                 let url='{{url("/clave")}}';
//                 let parametros={
//                     clave:clave,
//                 };
                
//                 $.ajaxSetup({
//                     headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                     }
//                 });

//                 $.ajax({
//                 type:'POST',
//                 beforeSend: function () {
//                 //espere("Guardando Cuenta...");
//                 },
//                 url:url,
//                 data:parametros,
//                 success:function(resp){
                    
//                     Vue.swal.close();
//                     if(resp=="OK"){
//                         borrar_cuenta(idop);
//                     }else{
//                         console.log('esa no es la contraseña correcta');
//                     }
//                 },
//                 error: function(error){
//                     Vue.swal.close();
//                         Vue.swal({
//                             title: 'ERROR',
//                             text: error.responseText,
//                             icon: 'error',
//                         });
//                 }
//                 });



//             }


//         });
//     }

//   function borrar_cuenta(idop){
//     let data=elid+"*"+idop;
//     var url='{{url("/clientes/borrar_cuenta")}}'+'/'+data;
//       $.ajax({
//         type:'GET',
//         beforeSend: function () {
//           espere("Borrando cuenta del Cliente...");
//         },
//         url:url,
//         success:function(resp){
            
//             $("#listar_clientes").html(resp);
//             Vue.swal.close();
//           Vue.swal({
//                 text: 'Listo...',
//                 position: 'top-end',
//                 icon: 'info',
//                 toast: true,
//                 showConfirmButton: false,
//                 timer: 3000,
//             });
//         },
//         error: function(error){
//             Vue.swal.close();
//             Vue.swal({
//                 title: 'ERROR',
//                 text: error.responseText,
//                 icon: 'error',
//             });
//         }
//       });
//   }

function borrar_cuentas_clave() {
    // Obtener los IDs seleccionados
    let seleccionados = [];
    document.querySelectorAll('.registro-checkbox:checked').forEach((checkbox) => {
        seleccionados.push(checkbox.value);
    });

    if (seleccionados.length === 0) {
        Vue.swal({
            title: 'Advertencia',
            text: 'Debe seleccionar al menos un registro para eliminar.',
            icon: 'warning',
        });
        return;
    }

    Vue.swal({
        title: 'Ingrese Contraseña',
        input: 'password',
        confirmButtonText: 'Verificar',
        showConfirmButton: true,
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            let clave = result.value;
            // Pedir contraseña
            let url = '{{url("/clave")}}';
            let parametros = {
                clave: clave,
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            });

            $.ajax({
                type: 'POST',
                beforeSend: function () {
                    //espere("Verificando contraseña...");
                },
                url: url,
                data: parametros,
                success: function(resp) {
                    Vue.swal.close();
                    if (resp == "OK") {
                        borrar_cuentas(seleccionados); // Llamar a la función para borrar múltiples registros
                    } else {
                        console.log('Esa no es la contraseña correcta');
                    }
                },
                error: function(error) {
                    Vue.swal.close();
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                },
            });
        }
    });
}

// function borrar_cuentas(ids) {
//     let url = '{{url("/borrar-cuentas")}}'; // Ruta para eliminar múltiples registros
//     let parametros = {
//         ids: ids, // Enviar los IDs seleccionados
//     };

//     $.ajax({
//         type: 'POST',
//         url: url,
//         data: parametros,
//         success: function(resp) {
//             if (resp == "OK") {
//                 ids.forEach((id) => {
//                     document.querySelector(`input[value="${id}"]`).closest('tr').remove();
//                 });
//                 Vue.swal({
//                     title: 'Éxito',
//                     text: 'Registros eliminados correctamente.',
//                     icon: 'success',
//                 });
//             } else {
//                 Vue.swal({
//                     title: 'Error',
//                     text: 'No se pudieron eliminar los registros.',
//                     icon: 'error',
//                 });
//             }
//         },
//         error: function(error) {
//             Vue.swal({
//                 title: 'ERROR',
//                 text: error.responseText,
//                 icon: 'error',
//             });
//         },
//     });
// }

function borrar_cuentas(ids) {
    // Verificar si el array de IDs está vacío
    if (ids.length === 0) {
        Vue.swal({
            title: 'Advertencia',
            text: 'Debe seleccionar al menos un registro para eliminar.',
            icon: 'warning',
        });
        return;
    }

    // Convertir el array de IDs en un string separado por comas (si es necesario)
    let data = elid + "*" + ids.join(',');

    // URL para enviar los datos
    var url = '{{url("/clientes/borrar_cuenta")}}' + '/' + data;

    $.ajax({
        type: 'GET',
        beforeSend: function () {
            espere("Borrando cuentas del Cliente...");
        },
        url: url,
        success: function(resp) {
            console.log(resp);
            // Actualizar la vista con la respuesta del servidor
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
        error: function(error) {
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }
    });
}

    function abrir_modal_detalle_boleta(num_boleta){
        let tipodte = 39; //boleta
        let url = '{{url("/ventas/damedetalleboleta")}}'+"/"+tipodte+"/"+num_boleta;
        $.ajax({
            type:'GET',
            url: url,
            success: function(resp){
                $('#exampleModal_boleta').modal('show');
                $('#modal_body_detalle_boleta').html(resp);  
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function abrir_modal_detalle_nc(num_nc){
        let tipodte = 61; //nota de crédito
        let url = '{{url("/ventas/damedetalleboleta")}}'+"/"+tipodte+"/"+num_nc;
        $.ajax({
            type:'GET',
            url: url,
            success: function(resp){
                $('#exampleModal_boleta').modal('show');
                $('#modal_body_detalle_boleta').html(resp);  
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function abrir_modal_detalle_factura(num_factura){
        let tipodte = 33; //factura
            let url = '{{url("/ventas/damedetalleboleta")}}'+"/"+tipodte+"/"+num_factura;
            $.ajax({
                type:'GET',
                url: url,
                success: function(resp){
                    $('#exampleModal_boleta').modal('show');
                    $('#modal_body_detalle_boleta').html(resp);  
                },
                error: function(error){
                    console.log(error.responseText);
                }
            })
    }

  function prueba(){
      alert('hola');
  }

  function mostrar(){
    Vue.swal({
        title: 'En construcción...',
        icon: 'info',
    });
  }
</script>

<div id="ccuenta" class="col-sm-12 tabla-scroll-y-400 mt-3">
    <div class="row" id="cliente">
     <p>{{$cliente->nombres}} {{$cliente->apellidos}} </p>
    </div>
    <div class="row" id="cliente_empresa">
        @if($cliente->empresa !== '---')
        <p>{{$cliente->empresa}}</p>  
        @else 
        <p>Sin empresa registrada</p>
        @endif
    </div>
    
    <nav>
        <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist" style="background-color:beige">
            <a class="nav-item nav-link" id="nav-resumen-tab" data-toggle="tab" href="#tab-resumen" role="tab" aria-controls="nav-home" aria-selected="true">Resumen</a>
            <a class="nav-item nav-link active" id="nav-cuenta-tab" data-toggle="tab" href="#tab-cuenta" role="tab" aria-controls="nav-profile" aria-selected="false">Cuenta</a>
            <a class="nav-item nav-link" id="nav-facturas-tab" data-toggle="tab" href="#tab-facturas" role="tab" aria-controls="nav-profile" aria-selected="false">Facturas Emitidas</a>
            <a class="nav-item nav-link" id="nav-boletas-tab" data-toggle="tab" href="#tab-boletas" role="tab" aria-controls="nav-contact" aria-selected="false">Boletas Emitidas</a>
            <a class="nav-item nav-link" id="nav-nc-tab" data-toggle="tab" href="#tab-nc" role="tab" aria-controls="nav-contact" aria-selected="false">Notas Crédito Emitidas</a>
            <a class="nav-item nav-link" id="nav-ingreso_egreso-tab" data-toggle="tab" href="#tab-ingreso_egreso" role="tab" aria-controls="nav-contact" aria-selected="false">Ingresos vs Egresos</a>
        </div>
    </nav>
    <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
        <div class="tab-pane fade" id="tab-resumen" role="tabpanel" aria-labelledby="nav-resumen-tab">
            <strong>Total Pagos:</strong>&nbsp;{{number_format($total_pagos,0,',','.')}} <br> <strong>Total Deuda:</strong>&nbsp;{{number_format($total_deuda,0,',','.')}} <br> <strong>Diferencia:</strong>
            @if($diferencia>=0)
                        {{number_format($diferencia,0,',','.')}}
                    @else
                        {{number_format(abs($diferencia),0,',','.')}} A favor
                    @endif
            <br><br>
            <strong>Total Facturas Emitidas:</strong>&nbsp;{{number_format($facturas_suma,0,',','.')}}<br>
            <strong>Total Boletas Emitidas:</strong>&nbsp;{{number_format($boletas_suma,0,',','.')}}<br>
        </div>
        <div class="tab-pane fade show active" id="tab-cuenta" role="tabpanel" aria-labelledby="nav-cuenta-tab">
            
            <div id="ccuenta_cabecera" class="row">
                <div class="form-group">
                    <label for="pago">Pago:</label>
                    <input type="text" id="pago" value="0" maxlength="15" style="width:100px"><button class="btn btn-sm btn-success" onclick="agregar_cuenta(1)">Agregar</button>&nbsp;&nbsp;
                    <label for="deuda">Deuda:</label>
                    <input type="text" id="deuda" value="0" maxlength="15" style="width:100px"><button class="btn btn-sm btn-success" onclick="agregar_cuenta(2)">Agregar</button>&nbsp;&nbsp;
                    <label for="referencia">Referencia:</label>
                    <input type="text" id="referencia" maxlength="200" style="width:300px"><br>
                    <div class="box-info-cuenta">
                        <p><strong>Total Pagos:</strong>{{number_format($total_pagos,0,',','.')}} </p>  <p><strong>Total Deuda:</strong>{{number_format($total_deuda,0,',','.')}} </p><p> <strong>Total Notas Crédito:</strong>{{number_format($total_nc,0,',','.')}}</p>  <p><strong>Diferencia:</strong> 
                            @if($diferencia>=0)
                                {{number_format($diferencia,0,',','.')}}
                            @else
                                {{number_format(abs($diferencia),0,',','.')}} A favor
                            @endif
                        </p>
                    </div>
                    @if($cuenta->count()>0)
                    @php
                        //Fecha ultima actualización del precio del repuesto
                        $firstDate = $cuenta[0]->fecha_operacion;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                    @endphp

                        @if($dias > 15)
                        <span class="badge badge-warning">Hace {{$dias}} @if($dias > 1) días @else día  @endif</span>
                        @elseif($dias == 0)
                        <span class="badge badge-success"> Hoy</span>
                        @else
                        <span class="badge badge-success">Hace {{$dias}} @if($dias > 1) días @else día  @endif</span>
                        @endif
                    @endif
                    
                    
                    
                </div>
                @if($diferencia==0 && $cuenta->count()>0)
                    <div style="text-align:right;width:100%">
                        <button class="btn btn-danger btn-sm" onclick="limpiar_deuda_cero_clave()">Limpiar Deuda Cero</button>
                    </div>
                @endif
            </div> <!-- fin ccuenta_cabecera -->
            <p><strong>Total Deuda Seleccionada:</strong> 
                <span id="total-deuda-seleccionada">0</span>
            </p>
            @if($cuenta->count()>0)
                <div id="ccuenta_detalle" class="row mt-2">
                    <table class="table table-sm table-hover">
                        <thead>
                            <th scope="col" width="10%">Fecha</th>
                            <th scope="col" width="20%">Pago</th>
                            <th scope="col" width="20%">Deuda</th>
                            <th scope="col" width="40%">Referencia</th>
                            <th scope="col" width="5%">Usuario</th>
                            <th scope="col" width="5%"></th> <!-- para borrar -->
                        </thead>
                        <tbody>
                            @foreach($cuenta as $i)
                                <tr>
                                    <td>{{$i->fecha_operacion}}</td>
                                    <td>{{number_format($i->pago,0,',','.')}}</td>
                                    <td class="col-deuda" data-deuda="{{$i->deuda}}" style="cursor:pointer;">
                                        {{number_format($i->deuda,0,',','.')}}
                                    </td>
                                    <td>
                                        {{$i->referencia}}
                                        @if(isset($i->nota_credito))
                                            <br>
                                            <span class="text-success">Nota de Crédito: {{$i->nota_credito->num_nota_credito}} - ${{number_format($i->nota_credito->total,0,',','.')}}</span>
                                        @endif
                                    </td>
                                    <td>{{$i->usuario}}</td>
                                    <td>
                                        <input type="checkbox" class="registro-checkbox" value="{{$i->id}}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button id="eliminar-seleccionados" class="btn btn-danger" onclick="borrar_cuentas_clave()">Eliminar Seleccionados</button>
                </div> <!-- fin ccuenta_detalle -->
            @else
                <h4>Sin datos en la cuenta</h4>
            @endif
        </div>
        <div class="tab-pane fade" id="tab-facturas" role="tabpanel" aria-labelledby="nav-facturas-tab">
            @if($facturas->count()>0)
                <table class="table table-bordered table-hover" style="width:50%">
                    <thead>
                        <th class="text-center" scope="col" style="width:100px">Fecha</th>
                        <th class="text-center" scope="col" style="width:50px">N°</th>
                        <th class="text-center" scope="col" style="width: 50px;">Nota de credito</th>
                        <th class="text-center" scope="col" style="width:50px">Estado</th>
                        <th class="text-center" scope="col" style="width:80px">Total</th>
                    </thead>
                    <tbody>
                        @foreach($facturas as $factura)
                        <tr>
                            <td class="text-center">{{\Carbon\Carbon::parse($factura->fecha_emision)->format("d-m-Y")}}</td>
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_factura('{{$factura->id}}')">{{$factura->num_factura}}</a> </td>
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_nc('{{$factura->nota_credito ? $factura->nota_credito->id : ''}}')">{{ $factura->nota_credito ? $factura->nota_credito->num_nota_credito : '' }}</a> </td>
                            @switch($factura->estado)

                                @case(0)
                                    <td class="text-center">Rechazado</td>
                                 @break
                                @case(1)
                                    <td class="text-center">Procesado</td>
                                 @break

                                @case(2)
                                <td class="text-center">Anulada</td>
                                @break

                                @default
                                <td class="text-center">Indefinido</td>
                            @endswitch
                            
                            <td class="text-right">{{number_format($factura->total,0,',','.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h3 style="color:red">NO HAY FACTURAS EMITIDAS</h3>
            @endif
        </div>
        <div class="tab-pane fade" id="tab-boletas" role="tabpanel" aria-labelledby="nav-boletas-tab">
            @if($boletas->count()>0)
                <table class="table table-bordered table-hover" style="width:50%">
                    <thead>
                        <th class="text-center" scope="col" style="width:100px">Fecha</th>
                        <th class="text-center" scope="col" style="width:50px">N°</th>
                        <th class="text-center" scope="col" style="width: 50px;">Nota de credito</th>
                        <th class="text-center" scope="col" style="width:80px">Total</th>
                    </thead>
                    <tbody>
                        @foreach($boletas as $boleta)
                        <tr>
                            <td class="text-center">{{\Carbon\Carbon::parse($boleta->fecha_emision)->format("d-m-Y")}}</td>
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_boleta('{{$boleta->id}}')">{{$boleta->num_boleta}}</a> </td>
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_nc('{{$boleta->nota_credito ? $boleta->nota_credito->id : ''}}')">{{ $boleta->nota_credito ? $boleta->nota_credito->num_nota_credito : '' }}</a> </td>
                            <td class="text-right">{{number_format($boleta->total,0,',','.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h3 style="color:red">NO HAY BOLETAS EMITIDAS</h3>
            @endif
        </div>

        <div class="tab-pane fade" id="tab-nc" role="tabpanel" aria-labelledby="nav-nc-tab">
            @if(isset($notas_credito))
            @if($notas_credito->count()>0)
                <table class="table table-bordered table-hover" style="width:50%">
                    <thead>
                        <th class="text-center" scope="col" style="width:100px">Fecha</th>
                        <th class="text-center" scope="col" style="width:50px">N°</th>
                        <th class="text-center" scope="col" style="width: 50px">Documento</th>
                        <th class="text-center" scope="col" style="width:80px">Total</th>
                    </thead>
                    <tbody>
                        {{-- @foreach($notas_credito as $nc)
                        <tr>
                            <td class="text-center">{{\Carbon\Carbon::parse($nc->fecha_emision)->format("d-m-Y")}}</td>
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_nc('{{$nc->id}}')">{{$nc->num_nota_credito}}</a> </td>
                            @if($nc->tipo_documento == 'factura')
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_factura({{$nc->doc->id}})">{{$nc->tipo_documento}} {{ $nc->num_doc }}</a></td>
                            @elseif($nc->tipo_documento == 'boleta')
                            <td class="text-center"><a href="javascript:abrir_modal_detalle_boleta({{$nc->doc->id}})">{{$nc->tipo_documento}} {{ $nc->num_doc }}</a></td>
                            @else
                            <td class="text-center">{{$nc->tipo_documento}} {{ $nc->num_doc }}</td>
                            @endif
                            <td class="text-right">{{number_format($nc->total,0,',','.')}}</td>
                        </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            @else
                <h3 style="color:red">NO HAY NC EMITIDAS</h3>
            @endif
            @endif
        </div>

        <div class="tab-pane fade" id="tab-ingreso_egreso" role="tabpanel" aria-labelledby="nav-ingreso_egreso-tab">
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
    </div>

</div> <!-- fin ccuenta -->

<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
            <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logoHeader">
          <h5 class="modal-title" id="exampleModalLongTitle">Detalle de factura</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_factura" >
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="exampleModal_boleta" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Detalle</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body" id="modal_body_detalle_boleta">
            ...
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
</div>
