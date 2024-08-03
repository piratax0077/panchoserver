@extends('plantillas.app')
@section('javascript')
    
@endsection
@section('style')
<style>
    .icon_barcode{
        border: 1px solid #eee;
        padding: 5px;
    }
</style>
@endsection
@section('contenido')
<script type="text/javascript">
    $('#modalArticulos').on('shown.bs.modal', function () {
        $('#codigoEscaneado').focus();
    });

    function buscarArticulo(){
        let codigo_escaneado = $('#codigoEscaneado').val();
        let url = '{{url("/buscar-barcode")}}';

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });
        $.ajax({
            type:'POST',
            url: url,
            data: {'codigo_escaneado': codigo_escaneado},
            beforeSend: function(){
                console.log('Enviando');
            },
            success: function(resp){
                
                $('#tbody_repuesto_escaneado').html(resp);
            },
            error: function(err){
                console.log(err.statusText);
            }
        })
    }
    
    function agregar(){
        console.log('Agregando');
    }
</script>
<button class="btn btn-success" type="button" id="btnNuevo" data-toggle="modal" data-target="#modalArticulos" data-keyboard="false" data-backdrop="static"><i class="fa fa-plus"></i> Nuevo Bulto</button>
<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="modalArticulos" tabindex="-1" role="dialog" aria-labelledby="modalArticulosLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
                <h4 class="modal-title" id="modalArticulosLabel">Ingreso de Artículos</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Escanear Código de Barras</label>
					<div class="input-group">
						<div class="input-group-addon icon_barcode"><i class="fa fa-barcode"></i></div>
						<input type="text" class="form-control producto" name="codigoEscaneado" id="codigoEscaneado" autocomplete="off" onchange="buscarArticulo();">
					</div>
				</div>
				<div>
					<table class="table table-striped" id="tablaAgregarArticulos">
						<thead>	
							<tr>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Familia</th>
                                <th>Observaciones</th>
                                <th>Pais</th>
                                <th>Empresa</th>
                                <th>Precio Venta</th>
							</tr>
						</thead>
						<tbody id="tbody_repuesto_escaneado">
						
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" id="btnCerrarModal">Cerrar</button>
				<button type="button" class="btn btn-primary" id="btnAgregar" onclick="agregar();">Agregar</button>
			</div>
		</div>
	</div>
</div>
@endsection