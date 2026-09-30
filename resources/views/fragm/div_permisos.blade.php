<div class="erp-permission-options">
    @foreach($opciones as $opcion)
    <div class="form-check erp-permission-option">
        <input class="form-check-input" type="checkbox" value="{{ $opcion }}" name="permisos" id="permisoOpcion{{ $id }}_{{ $loop->index }}">
        <label class="form-check-label" for="permisoOpcion{{ $id }}_{{ $loop->index }}">{{ $opcion }}</label>
    </div>
    @endforeach
</div>
