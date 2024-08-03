

  <table class="table">
          
    <tbody>
      <tr>
        <td>Familia</td>
        <td>
            <select class="form-select form-control" name="" id="" disabled>
                <option value="">{{$descuento->nombrefamilia}}</option>
            </select>
        </td>
      </tr>
      <tr>
        <td>%</td>
        <td><input type="number" min="1" max="100" name="porcentaje" id="porcentaje" maxlength="2" onkeypress="return soloNumeros(event)" onkeyup="soloNumeros(event)" class="form-control" value="{{$descuento->porcentaje}}"></td>
      </tr>
      <tr>
        <td>Fecha Inicio</td>
        <td><input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{$descuento->desde}}"></td>
      </tr>
      <tr>
        <td>Fecha fín</td>
        <td><input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{$descuento->hasta}}"></td>
      </tr>
      <tr>
        <td>Local</td>
        <td><select name="local" id="local" class="form-control">
          <option value="1">Solo Local</option>
          <option value="2">Solo Web</option>
          <option value="3">Local y Web</option>
        </select>
      </td>
      </tr>
      <tr>
        <td>Imagen Referencia</td>
        <td><input type="file" name="" id="referenciaImagen" class="form-control"></td>
      </tr>
    </tbody>
  </table>

  <input type="hidden" name="idfamilia_editar" id="idfamilia_editar" value="{{$familia}}">