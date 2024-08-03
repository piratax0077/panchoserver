<p class="font-weight-bold text-center">{{$compras_cab->empresa_nombre}} con fecha {{$compras_cab->factura_fecha}} ingresada por {{!empty($compras_cab->name) ? $compras_cab->name : 'Ex usuario'}}</p>
<table class="table">
    <thead>
      <tr>
        <th scope="col">Factura</th>
        <th scope="col"><span class="font-italic text-muted">Nuevo número</span> </th>
        <th scope="col" class="text-center">Descripción</th>
        <th scope="col" class="text-center">Cantidad</th>
        <th scope="col"><span class="font-italic text-muted">Nueva cantidad</span></th>
        <th scope="col" class="text-center">P.U.</th>
        <th scope="col"><span class="font-italic text-muted">Nuevo P.U.</span></th>
        <th scope="col" class="text-center">P.S.</th>
        <th scope="col"><span class="font-italic text-muted">Nuevo P.S.</span></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td width="10%" class="letra_pequeña">{{$repuesto->factura_numero}}</td>
        <td width="10%" class="text-center"><input type="text" onkeypress="return soloNumeros(event)" style="width: 80px; text-align: center;" id="nuevo_numero_factura" value="{{$repuesto->factura_numero}}"></td></td>
        <td width="20%" class="letra_pequeña text-center">{{$repuesto->descripcion}}</td>
        <td width="10%" class="text-center">{{$repuesto->cantidad}}</td>
        <td width="10%" class="text-center"><input type="text" onkeypress="return soloNumeros(event)" min="0" style="width: 70px; text-align: center;" id="nueva_cantidad_item" value="{{$repuesto->cantidad}}"></td>
        <td width="10%" class="text-center">{{number_format($repuesto->pu)}}</td>
        <td width="10%"><input type="text" onkeypress="return soloNumeros(event)" style="width: 70px; text-align: center;" id="nuevo_pu" value="{{$repuesto->pu}}" ></td>
        <td width="10%" class="text-center">{{number_format($repuesto->precio_sugerido)}}</td>
        <td width="10%"><input type="text" onkeypress="return soloNumeros(event)" style="width: 70px; text-align: center;" id="nuevo_ps" value="{{$repuesto->precio_sugerido}}" ></td>
      </tr>
    </tbody>
  </table>
  <button class="btn btn-success btn-sm" style="float: right;" onclick="calcular_precio_sugerido()">Calcular</button>
  <input type="hidden" name="factura_numero" id="factura_id" value="{{$repuesto->id_factura_cab}}">
  <input type="hidden" name="repuedto_id" id="repuesto_id" value="{{$repuesto->id}}">
  <input type="hidden" name="utilidad" id="utilidad" value="{{$repuesto->porcentaje}}">
  <input type="hidden" name="flete" id="flete" >