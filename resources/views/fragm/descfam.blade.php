<div class="col-12 tabla-scroll-y-200" style="padding:0px;">
    @if($descuentosfam->count()>0)
        @if($existe=="SI")
            <p style="color:red">Familia ya fue agregada</p>
        @endif
        <table class="table table-bordered">
            <thead>
            <th width="5%" scope="col"></th>
            <th width="75%" scope="col">Familia</th>
            <th width="20%" scope="col">%</th>
        </thead>
        <tbody>
            @foreach($descuentosfam as $d)
            <tr>
                <td><button class="btn btn-danger btn-sm" onclick="quitar_familia({{$d->id}})" style="line-height: 10px">X</button></td>
                <td><small>{{strtoupper($d->nombrefamilia)}}</small></td>
                <td>{{$d->porcentaje}}</td>
            </tr>
            @endforeach
        </tbody>
        </table>
    @else
        <p>Sin descuentos agregados</p>
    @endif




</div>
