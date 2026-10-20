<div class="panel-libro">
    <div class="panel-libro__header">
        <h5 class="panel-libro__titulo">{{ $documento }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm tabla-libro">
            <thead>
                <tr>
                    <th scope='col' width='20px' class="text-center">Folio</th>
                    <th scope='col' width='130px' class="text-center">Fecha</th>
                    <th scope='col' width='100px' class="text-center">Total</th>
                    <th scope='col' width='100px' class="text-center"></th><!--imprimir-->
                </tr>
            </thead>
            <tbody>
                @foreach($docus as $d)
                <tr>
                    <td class="text-center">{{$d->num_doc}}</td>
                    <td class="text-center">{!!\Carbon\Carbon::parse($d->fecha_doc)->format("d-m-Y")!!}</td>
                    <td class="text-right">{{number_format($d->total_doc,0,',','.')}}</td>
                    <td class="text-center"><button class="btn btn-warning btn-sm" onclick="imprimir_xml('{{$d->xml}}')"><i class="fa fa-print"></i> Imprimir</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

