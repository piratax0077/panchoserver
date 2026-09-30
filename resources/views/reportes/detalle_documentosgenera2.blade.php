@if(count($respuesta)>0)
    <div>
        <table class="table table-sm table-hover table-bordered table-wrapper">
            <thead>
                <th scope='col' width="5%" class="text-center">ORDEN</th>
                <th scope='col' width="20%" class="text-center">DOCUMENTO</th>
                <th scope='col' width="10%" class="text-center">FECHA</th>
                <th scope='col' width="25%" class="text-center">REFERENCIA</th>
                <th scope='col' width="10%" class="text-center">MONTO</th>
                <th scope='col' width="5%" class="text-center">ACTIVO</th>
            </thead>
            <tbody>
                @foreach($respuesta as $r)
                    @if($r['orden']%100==0)
                        @if($r['activo']==0)
                            <tr style="background-color:rgb(255, 126, 126)">
                        @else
                            <tr style="background-color:rgb(251, 255, 0)">
                        @endif

                        <td class="text-center">{{$r['orden']/100}}</td>
                    @else
                        @if($r['activo']==0)
                            <tr style="background-color:rgb(255, 126, 126)">
                        @else
                            <tr>
                        @endif
                        <td>
                            @if(isset($r['es_pago']))
                                @if($r['es_pago']>0)
                                    <button class="btn btn-primary btn-sm" onclick="cargar_pago({{$r['es_pago']}})">EDITAR</button>
                                @else
                                    <button class="btn btn-secondary btn-sm" onclick="agregar_pago({{$r['id_doc']}}, '{{$r['tipo_doc']}}')">AGREGAR</button>
                                @endif
                            @endif
                        </td>
                    @endif

                        <td>
                            @if(isset($r['es_pago']))
                                @if($r['es_pago']==0)
                                    @if(isset($r['xml']))
                                        @php $xml=$r['xml']; @endphp
                                        <a href="javascript:void(0);" onclick="imprimir_xml('{{$xml}}')">{{$r['documento']}}</a>
                                    @else
                                        {{$r['documento']}}
                                    @endif
                                @else
                                    {{$r['documento']}}
                                @endif
                            @endif
                        </td>
                        <td class="text-center">{{\Carbon\Carbon::parse($r['fecha'])->format("d-m-Y")}}</td>
                        <td>{{$r['referencia']}}</td>
                        <td class="text-right">{{number_format($r['monto'],0,',','.')}}</td>
                        <td class="text-center">
                            @if($r['activo']==1)
                                SI
                            @else
                                NO
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
<!-- Modal -->
<div class="modal fade" id="formCabFac" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PanchoRepuestos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="formasPago">

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="pagada()">Pagada</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="id_doc" id="id_doc" value="">
<input type="hidden" name="tipo_doc" id="tipo_doc" value="">
@else
    <center><h3 style="color:red">NO HAY DATOS</h3></center>
@endif

<script>
    function agregar_pago(id_doc, tipo_doc){
        console.log("Agregar pago para documento: " + id_doc + ", tipo: " + tipo_doc);
        // Set the hidden input values
        $('#id_doc').val(id_doc);
        $('#tipo_doc').val(tipo_doc);
        // abrir modal y cargar formas de pago
        $('#formCabFac').modal('show');
        dameFormasPago();
    }

    function dameFormasPago(){
        let url = '{{url("ventas/dame_forma_pago")}}';
       
        $.get({
          type:'get',
          url: url,
          success: function(resp){
            $('#formasPago').empty();
            $('#formasPago').append(resp);
          },
          error: function(error){
            console.log(error.responseText);
          }
        });
      }

      function pagada(){
        let id_doc = $('#id_doc').val();
        let tipo_doc = $('#tipo_doc').val();
        let url = '{{url("ventas/guardar_pago")}}';
        let formas_pago = [];
        $('input[name="forma_pago"]:checked').each(function(){
            let id_forma = $(this).val();
            let monto = $('#monto-' + id_forma).val();
            let referencia = $('#referencia-' + id_forma).val();
            formas_pago.push({id_forma: id_forma, monto: monto, referencia: referencia});
        });

        console.log("ID Doc: " + id_doc + ", Tipo Doc: " + tipo_doc + ", Formas de Pago: ", formas_pago);

        $.post({
          type:'post',
          url: url,
          data: {
            _token: '{{csrf_token()}}',
            id_doc: id_doc,
            tipo_doc: tipo_doc,
            formas_pago: formas_pago
          },
          success: function(resp){
            if(resp.success){
                alert('Pago registrado correctamente');
                location.reload();
            } else {
                alert('Error al registrar el pago');
            }
          },
          error: function(error){
            console.log(error.responseText);
          }
        });
      }

       function activar_forma_pago(id){

            let marca=document.getElementById("formita-"+id).checked;

            document.getElementById("monto-"+id).disabled=!marca;

            document.getElementById("referencia-"+id).disabled=!marca;

        }
</script>
