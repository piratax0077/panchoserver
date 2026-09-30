
@if($repuestos->count()>0)

<table class="table mt-3">

    <h3 class="mt-3">Detalle</h3>

    <thead class="thead-dark">
      <tr>
        <th>Cod. Int</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Enviado por</th>
      </tr>
    </thead>

    <tbody>

        @foreach($repuestos as $repuesto)

        <tr>

            <td>
                <a href="javascript:void(0)"
                   onclick="dameInfoRepuestoModal({{$repuesto->idrepuesto}})"
                   data-target="#modalInfoRepuesto"
                   data-toggle="modal">

                   {{$repuesto->codigo_interno}}

                </a>
            </td>

            <td>{{$repuesto->descripcion}}</td>

            <td>{{$repuesto->cantidad}}</td>

            <td>{{$repuesto->name}}</td>

        </tr>


        {{-- ✅ FILA DE SUSTITUTOS --}}
        @if(isset($productos_sustitutos[$repuesto->idrepuesto]) 
            && count($productos_sustitutos[$repuesto->idrepuesto]) > 0)

        <tr style="background:#f8f9fa">

            <td colspan="4">

                <b>Sustitutos disponibles:</b>

                <div class="mt-1">

                    @foreach($productos_sustitutos[$repuesto->idrepuesto] as $s)

                        <span class="badge badge-info mr-1">

                           <a href="#" class="text-white" onclick="dameInfoRepuestoModal({{$s->id}})" data-target="#modalInfoRepuesto" data-toggle="modal"> {{$s->codigo_interno}} </a>
                            -
                            {{$s->empresa_nombre_corto}} 
                            - 
                            {{$s->nombre_pais}}

                        </span>

                    @endforeach

                </div>

            </td>

        </tr>

        @endif


        @endforeach

    </tbody>

</table>
  <div class="modal fade" id="modalInfoRepuesto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Información del Repuesto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modalBodyInfoRepuesto">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  @else
  <p class="text-danger">No hay más repuestos</p>
  @endif

  {{-- <button class="btn btn-outline-success btn-sm" onclick="transferir_carrito_totem()">Transferir Carrito Totem</button> --}}

  <input type="hidden" name="num_nc" id="num_nc" value="{{$num_nc}}">

  <script>
    function transferir_carrito_totem(){
      let id_carrito_totem = $('#num_nc').val();
      let url = '/aceptar_totem/'+id_carrito_totem;

      $.ajax({
        type:'get',
        url: url,
        success: function(resp){
          console.log(resp);
          if(resp == 'OK'){
            Vue.swal({
              title:'Exito',
              text:'Su carrito fue enviado correctamente',
              icon:'success'
            });
          }else{
            Vue.swal({
              title:'Exito',
              text:'Ha ocurrido un error',
              icon:'error'
            });
          }
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }
    function dameInfoRepuestoModal(idRepuesto){
    
        var nuevaurl = "/repuesto/buscaridrep_html_carrito/"+idRepuesto;
        $.ajax({
            type:'get',
            url:nuevaurl,
            beforeSend: function(){
              espere('CARGANDO ...');
            },
            success: function(html){
              console.log(html);
                Vue.swal.close();
                $('#modalBodyInfoRepuesto').empty();
                $('#modalBodyInfoRepuesto').append(html);
            },
            error: function(error){
                console.log(error);
            }
        });
    }
  </script>