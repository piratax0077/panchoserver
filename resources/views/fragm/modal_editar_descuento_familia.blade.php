

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
        <td><input type="number" min="0.01" max="99.99" step="0.01" name="porcentaje" id="porcentaje"  class="form-control" value="{{$descuento->porcentaje}}"></td>
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
          <option value="1" {{ (int) $descuento->id_local === 1 ? 'selected' : '' }}>Solo Local</option>
              <option value="2" {{ (int) $descuento->id_local === 2 ? 'selected' : '' }}>Solo Web</option>
              <option value="4" {{ (int) $descuento->id_local === 4 ? 'selected' : '' }}>Solo Totem</option>
              <option value="3" {{ (int) $descuento->id_local === 3 ? 'selected' : '' }}>Local + Web</option>
              <option value="5" {{ (int) $descuento->id_local === 5 ? 'selected' : '' }}>Local + Totem</option>
              <option value="6" {{ (int) $descuento->id_local === 6 ? 'selected' : '' }}>Web + Tótem</option>
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