@extends('maestro')
@section('titulo','3 Columnas')

@section('javascript')
@endsection

@section('contenido_titulo_pagina')
<center><h2>3 Columnas</h2></center><br>
@endsection

@section('contenido_ingresa_datos')
@include('fragm.mensajes')
@if(empty($guardado))
	@php $guardado='NO';@endphp
@endif

<div class="container-fluid">
	<div class="row">
	  <div class="col-md-6" style="background-color:#FFFF7F;">
	  	<form name="frmDatos" method="post" action="{{ url('3columnas/guardardatos') }}">
	      	{{ csrf_field() }}
			<div class="row" style="margin-bottom:10px;">
		        <div class="col-md-6">  
		          <label for="valor1">Valor 1:</label>
		          @if($guardado=='SI')
		          	<input type="text" name="valor1" id="valor1" size="20" maxlength="20" value="{{$datoz['nombre']}}" class="form-control" readonly>
				  @else
					nada
				  @endif
		        </div>
		        <div class="col-md-6">  
		          <label for="valor2">Valor 2:</label>
		          <input type="text" name="valor2" id="valor2" size="20" maxlength="20" value="{{$datoz['apellido']}}" class="form-control">
		        </div>
			</div>

			<div class="row">
		        <div class="col-md-6">  
		          <label for="valor3">Valor 3:</label>
		          <p>{{$datoz['nombrefamilia']}}</p>
		        </div>
		        <div class="col-md-6">  
		          <input type="text" name="valor4" id="valor4" size="20" maxlength="20" value="{{old('valor4')}}" placeholder="Valor 4" class="form-control">
		        </div>
			</div>
			<div class="row">
				<div class="col-md-4" style="margin-top:20px;">
		          @if($guardado=='NO')
			          <input type="submit" name="btnGuardarFrmDatos" id="button" value="Guardar" class="btn btn-primary btn-md"/>
				  @endif
		        </div>
			</div>
	    </form>
	  </div>

	  <div class="col-md-3" style="background-color:#ACCAFF;" >
	  	<form name="frmSimilares" method="post" action="{{ url('3columnas/guardarsimilares') }}">
	      	{{ csrf_field() }}
			<div class="row">
		        <div class="col-md-6">  
		          <label for="valor5">Valor 5:</label>
		          <input type="text" name="valor5" id="valor5" size="20" maxlength="20" value="{{old('valor6')}}"class="form-control">
		        </div>
		        <div class="col-md-6">  
		          <label for="valor6">Valor 6:</label>
		          <input type="text" name="valor6" id="valor6" size="20" maxlength="20" value="{{old('valor6')}}"class="form-control">
		        </div>
			</div>
			<div class="row">
				<div class="col-md-4" style="margin-top:20px;">
		          <input type="submit" name="btnGuardarFrmSimilares" id="button" value="Guardar" class="btn btn-success btn-md"/>
		        </div>
			</div>
	    </form>
	  </div>
	  
	  <div class="col-md-3" style="background-color:#20FF9E;">
	  	<form name="frmFotos" method="post" action="{{ url('3columnas/guardarfotos') }}">
	      	{{ csrf_field() }}
			<div class="row">
		        <div class="col-md-6">  
		          <input type="text" name="valor7" id="valor7" size="20" maxlength="20" value="{{old('valor7')}}" placeholder="Valor 7" class="form-control">
		        </div>
		        <div class="col-md-6">  
		          <input type="text" name="valor8" id="valor8" size="20" maxlength="20" value="{{old('valor8')}}" placeholder="Valor 8" class="form-control">
		        </div>
			</div>
			<div class="row">
				<div class="col-md-4" style="margin-top:20px;">
		          <input type="submit" name="btnGuardarFrmSimilares" id="button" value="Guardar" class="btn btn-warning btn-md"/>
		        </div>
			</div>
	    </form>
	  </div>
	</div>


</div>
@endsection

@section('contenido_ver_datos')
@endsection