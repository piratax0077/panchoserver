@extends('maestro')
  @section('titulo','Listar Repuestos')
  @section('javascript')
	<script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

	<script>

	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        	}
	    });

	$(document).ready(function()
	{
		var url="{{url('cargardiv/cargar')}}";

		$("button").click(function(e){
			e.preventDefault();
			var idcombomarca = document.getElementById("MarcasDeVehiculos").value;
			var tesi = document.getElementById("tesito").value;
			$.ajaxSetup({
		        headers: {
		            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        	}
			    });


			$.ajax({
	           type:'POST',
	           beforeSend: function () {
                        alert(idcombomarca);
                        //$("#contenido").html("Procesando, espere por favor...");
                },
	           url:url,
	           data:{idmarcavehiculo:idcombomarca,nombre:tesi},
	           success:function(resp){
	              $("#contenido").html(resp);
	           },
	          	error: function(xhr, status, error){
         		var errorMessage = xhr.status + ': ' + xhr.statusText
	                $('#contenido').html(errorMessage); 
	            }

			});

		}); // fin button
	}); // fin document

	</script>
	@endsection

  @section('contenido_ingresa_datos')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3">
			<label for="Marcas">Marcas:</label>
            <select name="cbomarcas" class="form-control" id="MarcasDeVehiculos">
            	@foreach ($marcas as $marca)
	            <option value="{{$marca->idmarcavehiculo}}">{{$marca->marcanombre}}({{$marca->idmarcavehiculo}})</option>
            	@endforeach
        	</select>
        	<label for="tesito">Nombre:</label>
        	<input type="text" name="tesito" class="form-control" id="tesito"/>
        </div>
		<div class="col-md-3">
        	<button>GO!</button>
    	</div>
		<div class="col-md-6" id="contenido" style="background-color:#FFFF7F;">
			<p>CONTENIDO NORMAL:</p>
			@foreach ($repuestos as $repuesto)
	            <p>{{$repuesto->codigo_interno}}</p>
            @endforeach
		</div>
	</div>
	
</div>
@endsection