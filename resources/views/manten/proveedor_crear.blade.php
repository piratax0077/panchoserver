@extends('plantillas.app')
  @section('titulo','Crear Proveedores')
  @section('javascript')
  @endsection
  @section('contenido_titulo_pagina')
<center><h2>Crear Proveedores</h2></center><br>
@endsection
  @section('contenido_ingresa_datos')
<div class="container-fluid">
  @include('fragm.mensajes')
    <form name="proveedor" method="post" action="{{ url('proveedor') }}">
      {{ csrf_field() }}
      <div class="row">
        <div class="col-md-2">
            <label>Es Transportista:</label>
            <input type="checkbox" name="empresa_transportista"  id="empresa_transportista" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Código de la Empresa (RUT):</label>
            <input type="text" name="empresa_codigo" value="{{old('empresa_codigo')}}" id="empresa_codigo" placeholder="99.999.999-9" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Nombre de la Empresa:</label>
            <input type="text" name="empresa_nombre" value="{{old('empresa_nombre')}}" id="empresa_nombre" class="form-control" maxlength="100">
        </div>
        <div class="col-md-2">
            <label>Nombre Corto de la Empresa:</label>
              <input type="text" name="empresa_nombre_corto" value="{{old('empresa_nombre_corto')}}" id="empresa_nombre_corto" class="form-control" placeholder="Máximo 100 caracteres" maxlength="100">
          </div>
		<div class="col-md-3">
          <label>Dirección de la Empresa:</label>
            <input type="text" name="empresa_direccion" value="{{old('empresa_direccion')}}" id="empresa_direccion" class="form-control">
        </div>
		<div class="col-md-3">
          <label>Web de la Empresa:</label>
            <input type="text" name="empresa_web" value="{{old('empresa_web')}}" id="empresa_web" class="form-control">
        </div>
		<div class="col-md-3">
          <label>Teléfono de la Empresa:</label>
            <input type="text" name="empresa_telefono" value="{{old('empresa_telefono')}}" id="empresa_telefono" class="form-control">
        </div>
        <div class="col-md-3">
          <label>correo de la Empresa:</label>
            <input type="text" name="empresa_correo" value="{{old('empresa_correo')}}" id="empresa_correo" class="form-control">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-3">
          <label>Nombres del Vendedor:</label>
            <input type="text" name="vendedor_nombres" value="{{old('vendedor_nombres')}}" id="vendedor_nombres" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Correo del Vendedor:</label>
            <input type="text" name="vendedor_correo" value="{{old('vendedor_correo')}}" id="vendedor_correo" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Telefono del Vendedor:</label>
            <input type="text" name="vendedor_telefono" value="{{old('vendedor_telefono')}}" id="vendedor_telefono" class="form-control">
        </div>
      <br>
      <div class="row">
        <div class="col-md-2">
          <input type="submit" name="btnGuardarProveedor" id="button" value="Guardar" class="btn btn-primary btn-md"/>
        </div>
      </div>
    </form>
</div>
<p>TODOS LOS CAMPOS SON OBLIGATORIOS. Si no tiene un dato ingrese ---</p>
  @endsection

  @section('contenido_ver_datos')

  @endsection
