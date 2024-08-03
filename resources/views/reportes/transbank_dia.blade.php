@if($dia->count()>0)
Operaciones del día {!!\Carbon\Carbon::parse($fecha)->format("d-m-Y")!!}; monto: {{number_format(intval($total_dia),0,',','.')}} <a href="/reportes/imprimir_detalle_dia/{{$fecha}}" class="btn btn-success btn-sm" ><i class="fa-solid fa-file-excel"></i></a><br><br>
<table class="table table-sm table-hover table-bordered" style="width:90%">
    <thead>
        <th class="text-center" width="100px">HORA</th>
        <th class="text-center" width="180px">DOCUMENTO</th>
        <th class="text-center" width="180px">MONTO</th>
        <th class="text-center" width="200px">OPERACION</th>
    </thead>
    <tbody>
        @foreach($dia as $op)
        <tr>
            <td class="text-center" >{{$op->hora}}</td>
            <td>{{$op->tipo_doc}} N° {{$op->num_doc}}</td>
            <td class="text-right">{{number_format(intval($op->total),0,',','.')}}</td>
            <td class="text-center" >{{$op->referencia}}</td>
        </tr>
        @endforeach
    </tbody>
</table>



@endif
