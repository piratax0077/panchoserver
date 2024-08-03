@extends('plantillas.app')
@section('titulo','SIN STOCK PROVEEDOR')

@section('javascript')
    <script>
        function dameRepuestosProveedorSinStockProveedor(){
            Vue.swal({
                title: 'Cargando',
                text: 'Espere por favor',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    Vue.swal.showLoading()
                }
            });
        }

        function dameRepuestosFamiliaSinStockProveedor(){
            Vue.swal({
                title: 'Cargando',
                text: 'Espere por favor',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    Vue.swal.showLoading()
                }
            });
        }
    </script>
@endsection

@section('contenido_ingresa_datos')
<h4 class="titulazo">Repuestos sin stock proveedor</h4>
@php
            $año_actual=date("Y");
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <h4>Filtros</h4>
            <div class="form-group">
                <label for="proveedores" class="form-label">Proveedores</label>
                <select name="proveedores" id="proveedores" class="form-select form-control" onchange="dameRepuestosProveedorSinStockProveedor()">
                    <option value="0">Seleccione proveedor</option>
                    @foreach($proveedores as $proveedor)
                    <option value="{{$proveedor->id}}">{{$proveedor->empresa_nombre_corto}}</option>
                    @endforeach
                </select>
            </div>
            <p id="mensaje"></p>
            <div class="form-group">
                <label for="proveedores" class="form-label">Familias</label>
                <select name="familias" id="familias" class="form-select form-control" onchange="dameRepuestosFamiliaSinStockProveedor()">
                    <option value="0">Seleccione familia</option>
                    @foreach($familias as $familia)
                    <option value="{{$familia->id}}">{{$familia->nombrefamilia}}</option>
                    @endforeach
                </select>
            </div>
            <p id="mensaje"></p>
            
            <hr>
            {{-- <div class="form-group d-flex">
                
                <input type="date" name="fecha_busqueda" id="fecha_busqueda" value="<?php echo date("Y-m-d"); ?>" class="form-control">
                <button class="btn btn-sm btn-success" onclick="buscar_por_fecha()">Buscar</button>
            </div>
            <div id="tabla_repuestos" style="font-size: 13px;">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($repuestos as $r)
                        <tr>
                            <td>{{$r->codigo_interno}}</td>
                            <td>{{$r->fecha_emision}}</td>
                            <td>{{$r->estado}}</td>
                            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id_repuesto}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div> --}}
            
        </div>
        <div class="col-md-9">
            <div id="resultado" class="tabla-scroll-y-500">
                <table class="table letra_pequeña">
                    <thead>
                      <tr>
                        <th scope="col">Cod Int</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Proveedor</th>
                        <th scope="col">Familia</th>
                        <th scope="col">Estado</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($repuestos as $r)
                        <tr>
                            <td>{{$r->codigo_interno}}</td>
                            <td>{{$r->descripcion}}</td>
                            <td>{{$r->empresa_nombre_corto}}</td>
                            <td>{{$r->nombrefamilia}}</td>
                            <td>{{$r->estado}}</td>
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection