@if($mes->count()>0)

    @php $total_mes=0; @endphp
    <table class="table table-sm table-hover table-bordered" style="width:50%">
        <thead>
            <th class="text-center" width="150px">FECHA</th>
            <th class="text-center" width="70px">OPER</th>
        </thead>
        <tbody>
        @foreach($mes as $dia)
        <tr>
            <td class="text-center"><a href="javascript:void(0)" onclick="dame_detalle('{{$dia->fecha}}')">{!!\Carbon\Carbon::parse($dia->fecha)->format("d-m-Y")!!}</a></td>
            <td class="text-center">{{$dia->num_oper}}</td>
        </tr>
           
        @endforeach
        
        </tbody>
    </table>

@endif
