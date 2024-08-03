<div class="col-sm-12 tabla-scroll-y-300" style="padding:0px">
        @if($clientes->count()>0)
        <table class="table table-bordered table-hover table-sm">
            <thead class="letra-chica">
            <th width="18%" scope="col">RUT</th>
            <th width="42%" scope="col">RAZON SOCIAL</th>
            <th width="40%" scope="col">NOMBRES</th>

        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                @if(substr($c->rut,0,4)=='6666')
                    <td style="text-align: right;padding-right:8px"><small>{{$c->rut}}</small></td>
                    <td colspan="2"><small>Para boletas y cotizaciones sin especificar cliente</small></td>
                @else
                    <!-- $c envia todo el array asociativo a ventas_principal.blade para poder acceder a todos los datos del cliente-->
                    <td style="text-align: right;padding-right:8px"><small><a href="javascript:void(0);" onclick="cargar_cliente({{$c}})" id="rut{{$c->rut}}"><script>formatear_rut('{!!$c->rut!!}')</script></a></small></td>
                    <td><small>{{$c->empresa}}</small></td>
                    <td><small>{{$c->nombres}} {{$c->apellidos}}</small></td>
                @endif
            </tr>
            @endforeach
        </tbody>
        </table>
        @else
        <div class="col-sm-12">
            <br>
            <h3 style="text-align: center;color:red">No se encontraron clientes</h3>
        <!--
            <h4>¿ Agregar cliente rápido ?:</h4>
            <table>
            <th width="20%" scope="col"></th><th width="40%" scope="col"></th>
            <tr>
                <td>RUT:</td><td><abbr title="Ingrese el RUT sin puntos ni guiones. Sólo números y letra K."><input type="text" id="rut_cliente_rapido" size="12" maxlength="10" onKeyPress="return soloNumeros(event)" placeholder="OBLIGATORIO"></abbr></td>
            </tr>
            <tr>
                <td>NOMBRES:</td><td><input type="text" id="nombres_cliente_rapido" size="20" placeholder="OBLIGATORIO"></td>
            </tr>
            <tr>
                <td>APELLIDOS:</td><td><input type="text" id="apellidos_cliente_rapido" size="20" placeholder="OBLIGATORIO"></td>
            </tr>
            <tr>
                <td></td><td><button class="btn btn-success btn-sm" onclick="agregar_cliente_rapido()">AGREGAR</button></td>
            </tr>
            </table>
            <p id="mensajes_cliente_rapido"></p>
        -->
        </div>
        @endif
</div>
