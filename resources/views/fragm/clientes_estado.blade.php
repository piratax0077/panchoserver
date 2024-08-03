<script>
	function estado_cuenta_cliente(idc){
    elid = idc;
    var url='{{url("/clientes/dame_cuenta")}}'+'/'+elid;

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$('#mensajes').html("Borrando Cliente...");
          espere("Buscando cuenta del Cliente...");
        },
        url:url,
        success:function(resp){
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
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

      });


    }
</script>

<div class="col-sm-12 tabla-scroll-y-400">
@if($clientes->count()>0)
<table class="table table-sm table-bordered table-hover">
	<thead>
	<th width="5%" scope="col"></th>
	<th width="10%" scope="col">RUT</th>
	<th width="20%" scope="col">NOMBRES</th>
	<th width="25%" scope="col">RAZÓN SOCIAL</th>
    <th width="15%" scope="col">EMAIL</th>
    <th width="15%" scope="col">TELF1</th>
	<th width="20%" scope="col">CONTACTO</th>

</thead>
<tbody>
	@foreach($clientes as $c)
	<tr>
		@if(substr($c->rut,0,4)=='6666')
			<td></td>
			<td>{{$c->rut}}</td>
			<td colspan="5">Para boletas y cotizaciones sin especificar cliente</td>
		@else
			<td><button class="btn btn-danger btn-sm" style="line-height:12px" onclick="borrar_cliente({{$c->id}})">X</button></td>
			<td><a href="javascript:void(0);" onclick="estado_cuenta_cliente({{$c->id}})">{{$c->rut}}</a></td>
			<td>{{$c->nombres}} {{$c->apellidos}}</td>
            <td>{{$c->empresa}}</td>
            <td>{{$c->email}}</td>
			<td>{{$c->telf1}}</td>
			<td>{{$c->contacto}}</td>

		@endif



	</tr>
	@endforeach
</tbody>
</table>
@else
	<p>No se encontraron clientes</p>
@endif
</div>
