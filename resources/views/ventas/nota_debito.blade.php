@extends('plantillas.app')
@section('titulo','Nota de Débito')
@section('javascript')
    <script>
        function buscar_nd(){
            let numero_nd = $('#numero_nd').val();
            alert(numero_nd);
        }
    </script>
@endsection
@section('contenido')
    <h2>NOTA DE DÉBITO</h2>
    <span>Ingrese Núm de Nota de Débito: </span>
    <input type="number" id="numero_nd" name="numero_nd">
    <br>
    <button class="btn btn-success btn-sm mt-3" onclick="buscar_nd()">Buscar</button> <br>
    <span>ANULACIÓN DE NOTA DE DÉBITO</span><br>
    <button class="btn btn-danger">GENERAR NOTA DE DEBITO</button> <br>
    <span style="font-weight: bold;">Nota de Débito: </span> <span id="nc_number">0</span> <br>
    <span style="font-weight: bold;">Fecha de Emisión: </span> <span id="nc_fecha"></span> <br>
    <span style="font-weight: bold;">Total: </span> <span id="nc_total">0</span> <br>
    <span style="font-weight: bold;">Corrección: </span> <span id="nc_correccion"></span> <br>
    <table class="table">
        <thead>
          <tr>
            <th scope="col" style="width: 16%">Código interno</th>
            <th scope="col" style="width: 50%">Descripción</th>
            <th scope="col" style="width: 16%">Cantidad</th>
            <th scope="col" style="width: 16%">Total</th>
          </tr>
        </thead>
        <tbody id="tbody_nd">
          
        </tbody>
      </table>
    @endsection
