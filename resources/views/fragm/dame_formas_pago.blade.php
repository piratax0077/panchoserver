@if($formas->count()>0)

    <div class="row" >
        <div class="col-sm-12">
            <table border="0" class="table table-sm letra-chica">
                <thead>
                    <th width="50%" scope="col"><center>Forma de Pago</center></th>
                    <th width="22%" scope="col"><center>Monto</center></th>
                    <th width="28%" scope="col"><center>Referencia</center></th>
                </thead>
                <tbody>
            @foreach ($formas as $forma)
                @if(strtolower($forma->formapago)=="efectivo")
                <tr>
                    <td>
                        <input type="checkbox" name="forma_pago" id="formita-{{$forma->id}}" value="{{$forma->id}}" checked>{{$forma->formapago}}
                    </td>
                    <td><input type="text" name="forma_pago_monto" id="monto-{{$forma->id}}" value="" placeholder="monto" style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right"></td>
                    <td><input type="hidden" value="Efectivo" id="referencia-{{$forma->id}}"></td>
                    <!-- para hacer mas bonito podría usarse en el input text: onkeyup='total_pago()' -->
                </tr>
                @else
                <tr>
                    <td>
                        <input type="checkbox" name="forma_pago" id="formita-{{$forma->id}}" value="{{$forma->id}}" onclick="activar_forma_pago({{$forma->id}})">{{$forma->formapago}}
                    </td>
                    <td>
                        <input type="text" name="forma_pago_monto" id="monto-{{$forma->id}}" value="" placeholder="monto" style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right" disabled>
                    </td>
                    <td>
                        @if($forma->formapago=="Cheque")
                            <input type="text"  value="" id="referencia-{{$forma->id}}" placeholder="N° de Cheque"style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right" disabled>
                        @elseif($forma->formapago=="Transferencia Banco")
                            <input type="text"  value="" id="referencia-{{$forma->id}}" placeholder="Banco y N° de Oper." style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right" disabled>
                        @elseif($forma->formapago=="Otra")
                            <input type="text"  value="" id="referencia-{{$forma->id}}" placeholder="Especifique" style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right" disabled>
                        @else
                            <input type="text"  value="" id="referencia-{{$forma->id}}" placeholder="N° de Operación" style="width:90%;padding-top:0px;padding-bottom:0px;text-align:right" disabled>
                        @endif
                    </td>
                </tr>
                @endif
            @endforeach

            </tbody>
        </table>

        </div>
    </div>
@else
    <div class="row">
    <div class="alert alert-info">
        <h4><center>No hay Formas de Pago en la BD...</center></h4>
    </div>
    </div>
@endif
