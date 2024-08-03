@extends('plantillas.app')
@section('titulo','Repuestos actualizados')

@section('contenido_ingresa_datos')
<div class="container">
    <p>Se han encontrado {{count($repuestos)}} repuestos actualizados desde Junio del 2022 hasta hoy.</p>
    @if (session('msg'))
                <div class="alert alert-success fade show" role="alert">
                    {{ session('msg') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span>&times;</span> </button>
                </div>
      @endif
    <a href="/reportes/repuestos_actualizados" class="btn btn-success btn-sm">Excel</a>
    <table class="table">
        <thead>
          <tr>
            <th scope="col">Código interno</th>
            <th scope="col">Descripción</th>
            <th scope="col">Stock total</th>
            <th scope="col">Precio venta</th>
          </tr>
        </thead>
        <tbody>
            @foreach($repuestos as $r)
            <tr>
                <th scope="row">{{$r->codigo_interno}}</th>
                <td>{{$r->descripcion}}</td>
                <td>{{number_format($r->stock_actual + $r->stock_actual_dos + $r->stock_actual_tres)}}</td>
                <td>$ {{number_format($r->precio_venta)}}</td>
              </tr>
            @endforeach
          
        </tbody>
      </table>
    
</div>

@endsection