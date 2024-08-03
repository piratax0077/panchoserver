<select name="idproveedor" id="idproveedor" class="form-control">
    @foreach($proveedores as $p)
    <option value="{{$p->id}}">{{$p->empresa_nombre_corto}}</option>
    @endforeach
</select>