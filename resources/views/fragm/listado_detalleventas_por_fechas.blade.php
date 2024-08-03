<table class="table">
    <thead class="thead-dark">
      <tr>
        <th scope="col">Numero de @if($tipodte == 33) factura @elseif($tipodte == 39) boleta @elseif($tipodte == 61) nota cred. @endif</th>
        <th scope="col">Fecha</th>
        
        <th scope="col">Creador</th>
      </tr>
    </thead>
    <tbody>
            @if($dtes->count() > 0)
            @foreach($dtes as $dte)
                <tr>
                    <td><a href="javascript:cargar_detalle('{{$dte->identificador}}','{{$tipodte}}')">@if($tipodte == 33) {{$dte->num_factura}} @elseif($tipodte == 39) {{$dte->num_boleta}} @elseif($tipodte == 61) {{$dte->num_nota_credito}} @endif</a> </td>
                    <td>{{$dte->created_at}}</td>
                    <td>{{$dte->name}}</td>
                </tr>
            @endforeach
            @else
                <p class="alert-danger">No existen documentos</p>
            @endif
            
        
        
    </tbody>
  </table>
