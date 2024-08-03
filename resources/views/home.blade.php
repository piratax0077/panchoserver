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

    function dameultimosrepuestos(){
      let url='{{url("repuesto/dameultimosrepuestos")}}';
        $.ajax({
          type:'get',
          url:url,
          beforeSend: function(){

          },
          success: function(resp){
            console.log(resp);
            if(resp == "0"){
              console.log("No es ADMIN o no tiene permisos");
              return false;
            }
            if(resp[0].length > 0){
              $('#modal_body_ultimos_repuestos').empty();
              var contador = 0;
              resp[0].forEach(e => {
                let precio_format = Number(e.precio_venta).toFixed(0);
                precio_total = precio_format;
                $('#modal_body_ultimos_repuestos').append(`
                <div class="col-md-3">
                  
                  <img src="/storage/`+resp[1][contador]+`" style="width: 200px; height: 200px;"/><br>
                  <span>`+e.descripcion+`</span><br>
                  <span>`+e.codigo_interno+`</span><br>
                  <span class="font-weight-bold badge badge-info text-white" style="font-size: 16px;">$ `+commaSeparateNumber(precio_total)+` </span>
                </div>
                
                `);
                contador = contador + 1;
                
              });
              $("#ultimos-repuestos-modal").modal("show");
            }            
            

          },
          error: function(error){
            console.log(error.responseText);
          }
        });
    }

    function mostrar_bajo_stock(){
        
        let url='{{url("reportes/bajo_stock_home")}}';
        $.ajax({
          type:'get',
          url:url,
          beforeSend: function(){

          },
          success: function(resp){
            if(resp == "0"){
              console.log("No hay repuestos o no es admin");
              return false;
            }
            if(resp[0].length > 0){
              $('#modal_body_bajo_stock').empty();
              resp[1].forEach(e => {
                $('#modal_body_bajo_stock').append(`
                  <a href="/reportes/bajo_stock/`+e.id+`" class="btn btn-primary" onclick="bajoStock(`+e.id+`)" target="_blank">`+e.local_nombre+` </a>
              `);
              });
              $("#bajo-stock-modal").modal("show");
            }            
            

          },
          error: function(error){
            console.log(error.responseText);
          }
        });
    }

    function bajoStock(local_id){
      $("#bajo-stock-modal").modal("hide");
    }
    window.onload = function(e){
            mostrar_deliverys_pendientes();
            dameultimosrepuestos();
            indicadoresEconomicos();
            //mostrar_bajo_stock();
            // $('#saludo').modal('show');
    }

    function indicadoresEconomicos(){
      let url = "https://mindicador.cl/api";

      $.ajax({
        type:'get',
        url:url,
        success: function(resp){
          console.log(resp);
          let valorUf = resp.uf.valor;
          let valorUtm = resp.utm.valor;
          let valorDolar = resp.dolar.valor;
          // agregar separador de miles
          valorUf = Number(valorUf).toFixed(0);
          valorUf = commaSeparateNumber(valorUf);
          valorUtm = Number(valorUtm).toFixed(0);
          valorUtm = commaSeparateNumber(valorUtm);
          valorDolar = Number(valorDolar).toFixed(0);
          valorDolar = commaSeparateNumber(valorDolar);

          $('#valorUf').html('$'+valorUf);
          $('#valorUtm').html('$'+valorUtm);
          $('#valorDolar').html('$'+valorDolar);
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function cargar_pedido(pedido_id){
      
            let url = '/ventas/cargar_pedido/'+pedido_id;
            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                  $('#modalInfoPedidoTerrestre').modal('show');
                    
                    $('#modalBodyInfoPedidoTerrestre').empty();
                    let cliente = resp[0];
                    console.log(cliente);
                    $('#nombre_cliente').html(cliente.nombre_cliente);
                    $('#numero_pedido').html(cliente.num_abono);
                    $('#fecha_emision_pedido').html(cliente.fecha_emision);
                    $('#hora_emision').html(cliente.hora_emision);
                    let repuestos_pedidos = resp[1];
                    repuestos_pedidos.forEach(repuesto => {
                            // al precio unitario de los repuestos agregarle el separador de miles
                            let precio_unitario = Number(repuesto.precio_unitario).toFixed(0);
                            precio_unitario = commaSeparateNumber(precio_unitario);
                            // al precio total de los repuestos agregarle el separador de miles
                            let total = Number(repuesto.total).toFixed(0);
                            total = commaSeparateNumber(total);

                            $('#modalBodyInfoPedidoTerrestre').append(`
                            <tr>
                                <td>`+repuesto.cantidad+`</td>
                                <td>`+repuesto.descripcion+` </td>
                                <td>$`+precio_unitario+` </td>
                                <td>$`+total+` </td>
                                
                            </tr>
                            `);
                    });
                },
                error: function(err){   
                    console.log(err);
                }
            })
    }

    function eliminar_abono_modal(id_abono){
    Vue.swal({ 
                    title: '¿Estás seguro?',
                    text: "Se eliminará el pedido permantemente",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
            }).then((result) => {
                    if(result.isConfirmed){
                        let url = '/ventas/eliminar_abono_modal/'+id_abono;
                        
                        //   var url='{{url("ventas/imprimir_pedido")}}';
                        //   let parametros = {id_abono: id_abono};

                        $.ajaxSetup({
                                headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                        $.ajax({
                            type:'get',
                            url: url,
                            success: function(resp){
                                
                                if(resp.length > 0){
                                        let pedidos_terrestre = resp[0];
                                        let pedidos_aereos = resp[1];
                                        $('#tbody_revision_pedidos_terrestres').empty();
                                        $('#tbody_revision_pedidos_aereos').empty();
                                        pedidos_terrestre.forEach( pedido => {
                                            $('#tbody_revision_pedidos_terrestres').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+` </a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });

                                        pedidos_aereos.forEach( pedido => {
                                            $('#tbody_revision_pedidos_aereos').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+`</a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });
                                    }
                            },
                            
                            error: function(err){
                                console.log(err.responseText);
                            }
                        });
                    }
                })
        
  }

  function cargar_consignacion(num_consignacion){
    let url = '/ventas/cargar_consignacion_home/'+num_consignacion;
    console.log(url);
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
        console.log(resp);
        $('#modalInfoConsignacion').modal('show');
          
          $('#modalBodyInfoConsignacion').empty();
          let consignacion = resp[0];
          $('#nombre_cliente_consignacion').html(consignacion.nombre_consignacion);
          $('#numero_consignacion').html(consignacion.num_consignacion);
        
          $('#fecha_emision_consignacion').html(consignacion.fecha_emision);
          let repuestos_pedidos = resp[1];
          repuestos_pedidos.forEach(repuesto => {
            // convertir el precio_venta del repuesto a entero y multiplicarlo por la cantidad
            let t = parseInt(repuesto.precio_venta) * repuesto.cantidad;
                  // al precio unitario de los repuestos agregarle el separador de miles
                  let precio_unitario = Number(parseInt(repuesto.precio_venta)).toFixed(0);
                  precio_unitario = commaSeparateNumber(precio_unitario);
                  let precio_normal = Number(parseInt(repuesto.precio_normal)).toFixed(0);
                  precio_normal = commaSeparateNumber(precio_normal);
                  // al precio total de los repuestos agregarle el separador de miles
                  let total = Number(t).toFixed(0);
                  total = commaSeparateNumber(total);

                  $('#modalBodyInfoConsignacion').append(`
                  <tr>
                      <td>`+repuesto.cantidad+`</td>
                      <td>`+repuesto.descripcion+` </td>
                      <td>$`+precio_unitario+` </td>
                      <td>$`+precio_normal+` </td>
                      
                      <td>$`+total+` </td>
                      
                  </tr>
                  `);
          });
      },
      error: function(err){   
          console.log(err);
      }
  })
  }

  function renovar_abono(id_abono){
    Vue.swal({ 
                    title: '¿Estás seguro?',
                    text: "Se renovará el pedido",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
            }).then((result) => {
                    if(result.isConfirmed){
                        let url = '/ventas/renovar_abono/'+id_abono;
                        
                        //   var url='{{url("ventas/imprimir_pedido")}}';
                        //   let parametros = {id_abono: id_abono};

                        $.ajaxSetup({
                                headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                        $.ajax({
                            type:'get',
                            url: url,
                            success: function(resp){
                                console.log(resp);
                                if(resp.length > 0){
                                        let pedidos_terrestre = resp[0];
                                        let pedidos_aereos = resp[1];
                                        $('#tbody_revision_pedidos_terrestres').empty();
                                        $('#tbody_revision_pedidos_aereos').empty();
                                        pedidos_terrestre.forEach( pedido => {
                                            $('#tbody_revision_pedidos_terrestres').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+` </a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                                <td><button class='btn btn-secondary btn-sm' onclick='renovar_abono(`+pedido.id+`)'><i class="fa-solid fa-repeat"></i></button> </td>
                                            </tr>
                                            `);
                                        });

                                        pedidos_aereos.forEach( pedido => {
                                            $('#tbody_revision_pedidos_aereos').append(`
                                            <tr> 
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+`</a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                                <td><button class='btn btn-secondary btn-sm' onclick='renovar_abono(`+pedido.id+`)'><i class="fa-solid fa-repeat"></i></button> </td>
                                            </tr>
                                            `);
                                        });
                                      }
                                    }
                                  });
                                }
                              });
  }
</script>
@endsection
@section('style')
<style>
    #mensajes{
        background-color: #000;
        position: relative;
        bottom: 22px;
        left:0px;
        width: 100%;
        height: 60px;
        color: white;
    }
    #titulo_bienvenida{
      
      margin-top: 10px;
      overflow: hidden;
      animation: example 5s infinite;
    }

    .imagen{
      height: 560px; 
      width: 100%; 
      margin-top: 10px; 
      border-radius: 50px;
    }

    .contenedor_imagen {overflow:hidden; border-radius: 50px; }

    @keyframes example {
      0%   {background-color: black; color: white;}
      25%  {background-color: #d6cb1b; color: #000;}
      50%  {background-color: black; color: white; border-radius: 10px;}
      75% {background: #d6cb1b; color: #000}
      100% {background-color: #000; color: white;}
    }

</style>

@endsection
@section('contenido')
<div class="container-fluid">
  <h1 class="title text-center" id="titulo_bienvenida">Bienvenidos</h1>
  
  @php
    $mensaje = Session::get('PARAM_MSG');
  @endphp
  @if(!empty($mensaje))
  <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
    <h1> <strong> {{$mensaje}}</strong></h1>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  @endif
    <div class="row justify-content-center">
      @if(Auth::user()->rol->nombrerol === "Administrador")
        <div class="col-md-9 contenedor_imagen">
          <div class="text-center">
            <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" id="logo_banner">
          </div>
          <img src="{{asset('storage/'.Session::get('PARAM_IMG_INDEX'))}}" alt="" srcset="" class="imagen" >
        </div>
     
        <div class="col-md-3">
          <div class="card card-primary card-outline ">
            <div class="card-header text-center">
              <h3 class="card-title">Agenda Diaria</h3>
              <p class="text-center">{{$hoy}}</p>
              
            </div>
              <div class="card-body">
                <div id="indicadoresEconomicos">
                  <p>Indicadores económicos</p>
                  <table class="table" style="font-size: 12px;">
                    <thead>
                      <tr>
                        <th scope="col">Descripción</th>
                        <th scope="col">Valor</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>UF</td>
                        <td><span id="valorUf"></span></td>
                      </tr>
                      <tr>
                        <td>UTM</td>
                        <td><span id="valorUtm"></span></td>
                      </tr>
                      <tr>
                        <td>DOLAR</td>
                        <td><span id="valorDolar"></span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <hr>
                <p>Pedidos Terrestres hace 21 días</p>
                <table class="table table-striped" style="font-size: 12px;">
                  <thead>
                    <tr>
                      <th scope="col">N°</th>
                      <th scope="col">Cliente</th>
                      <th scope="col">Fecha</th>
                      <th scope="col"></th>
                      <th scope="col"></th>
                    </tr>
                  </thead>
                  <tbody id="tbody_revision_pedidos_terrestres">
                    @foreach($terrestres as $t)
                    @php
                      if($t->fecha_emision == $fechaLimiteTerrestre){
                        $color = "text-danger";
                      }else{
                        $color = "";
                      }
                    @endphp
                    <tr class="{{$color}}">
                      <td><a href="javascript:void(0)" onclick="cargar_pedido({{$t->num_abono}})">{{$t->num_abono}}</a> </td>
                      <td>{{$t->nombre_cliente}}</td>
                      <td>{{$t->fecha_emision}} @if($t->fecha_emision == $fechaLimiteTerrestre)@endif</td>
                      <td><button class="btn btn-danger btn-sm" onclick="eliminar_abono_modal({{$t->id}})">X</button></td>
                      <td><button class="btn btn-secondary btn-sm" onclick="renovar_abono({{$t->id}})"><i class="fa-solid fa-repeat"></i></button></td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>

                <p>Pedidos Aereos hace 14 días</p>
                <table class="table table-striped" style="font-size: 12px;">
                  <thead>
                    <tr>
                      <th scope="col">N°</th>
                      <th scope="col">Cliente</th>
                      <th scope="col">Fecha</th>
                      <th scope="col"></th>
                      <th scope="col"></th>
                    </tr>
                  </thead>
                  <tbody id="tbody_revision_pedidos_aereos">
                    @foreach($aereos as $t)
                    @php
                      if($t->fecha_emision == $fechaLimiteAereo){
                        $color = "text-danger";
                      }else{
                        $color = "";
                      }
                    @endphp
                    <tr class="{{$color}}">
                      <td><a href="javascript:void(0)" onclick="cargar_pedido({{$t->num_abono}})">{{$t->num_abono}}</a></td>
                      <td>{{$t->nombre_cliente}}</td>
                      <td>{{$t->fecha_emision}} </td>
                      <td><button class="btn btn-danger btn-sm" onclick="eliminar_abono_modal({{$t->id}})">X</button></td>
                      <td><button class="btn btn-secondary btn-sm" onclick="renovar_abono({{$t->id}})"><i class="fa-solid fa-repeat"></i></button></td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>

                <p>Consignaciones pendientes hace 7 días</p>
                <table class="table table-striped" style="font-size: 12px;">
                  <thead>
                    <tr>
                      <th scope="col">N° Pedido</th>
                      <th scope="col">Desc</th>
                      <th scope="col">Fecha</th>
                    </tr>
                  </thead>
                  <tbody id="tbody_revision_consignaciones">
                    @foreach($consignaciones as $c)
                    <tr>
                      <td><a href="javascript:void(0)" onclick="cargar_consignacion({{$c->num_consignacion}})">{{$c->num_consignacion}}</a> </td>
                      <td>{{$c->nombre_consignacion}}</td>
                      <td>{{$c->fecha_emision}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="card-footer p-3">
                <a href="{{url('reportes/bajo_stock')}}" class="btn btn-danger w-100">Stock mínimo</a>
              </div>
          </div>
      @else
        <div class="col-md-12 contenedor_imagen">
          <div class="text-center">
            <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" id="logo_banner">
          </div>
          <img src="{{asset('storage/'.Session::get('PARAM_IMG_INDEX'))}}" alt="" srcset="" class="imagen" >
        </div>
      @endif
    </div>
    <footer id="mensajes" style="text-align:center">
        <img src="{{asset('storage/imagenes/logo_pos.png')}}" width='100px'><br>
        @php
            $entorno = App::environment();
            echo "Entorno: ".$entorno."<br><br>";
        @endphp

    </footer>
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

  <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="bajo-stock-modal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">BAJO STOCK</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" id="modal_body_bajo_stock">
          
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
      </div>
      </div>
    </div>
  </div>

  <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="ultimos-repuestos-modal">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">ULTIMOS REPUESTOS</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" >
          <div class="row" id="modal_body_ultimos_repuestos">

          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
      </div>
      </div>
    </div>
  </div>

  <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="saludo">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Saludos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <img src="{{asset('storage/imagenes/saludo.jpeg')}}" alt="" srcset="" width="100%">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
<div class="modal fade" id="modalInfoPedidoTerrestre" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información Pedido</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>El cliente <span id="nombre_cliente"></span> ha realizado un pedido con el numero <span id="numero_pedido"></span> con fecha <span id="fecha_emision_pedido"></span> con hora <span id="hora_emision"></span></p>
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Cant.</th>
              <th scope="col">Descripción</th>
              <th scope="col">P.U.</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody id="modalBodyInfoPedidoTerrestre" style="font-size: 13px;">
            
            
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

 <!-- Modal -->
 <div class="modal fade" id="modalInfoConsignacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información Consignacion</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>El cliente <span id="nombre_cliente_consignacion"></span> ha realizado una consignacion con el numero <span id="numero_consignacion"></span> con fecha <span id="fecha_emision_consignacion"></span></p>
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Cant.</th>
              <th scope="col">Descripción</th>
              <th scope="col">P.U.</th>
              <th scope="col">P.Normal</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody id="modalBodyInfoConsignacion" style="font-size: 13px;">
            
            
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endsection
