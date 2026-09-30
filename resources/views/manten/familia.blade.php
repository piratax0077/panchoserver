@extends('plantillas.app')

@section('titulo', 'Familias de Repuestos')

@section('javascript')
<script type="text/javascript">
    function confirmacion() {
        return confirm('¿Está seguro de eliminar esta familia de repuestos?');
    }

    function modificar(idFamilia) {
        document.getElementById('modificando').value = 1;
        document.getElementById('id_familia').value = idFamilia;
        document.getElementById('nombrefamilia').value = document.getElementById('nf-' + idFamilia).value;
        document.getElementById('prefijo').value = document.getElementById('pr-' + idFamilia).value;

        document.getElementById('titulo-formulario').innerText = 'Modificar familia';
        document.getElementById('button').value = 'Actualizar familia';
        document.getElementById('button').classList.remove('btn-primary');
        document.getElementById('button').classList.add('btn-warning');
        document.getElementById('cancelar-edicion').classList.remove('d-none');

        document.getElementById('nombrefamilia').focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cancelarEdicion() {
        document.getElementById('form-familia').reset();
        document.getElementById('modificando').value = 0;
        document.getElementById('id_familia').value = 0;
        document.getElementById('titulo-formulario').innerText = 'Nueva familia';
        document.getElementById('button').value = 'Guardar familia';
        document.getElementById('button').classList.remove('btn-warning');
        document.getElementById('button').classList.add('btn-primary');
        document.getElementById('cancelar-edicion').classList.add('d-none');
    }
</script>
@endsection

@section('style')
<style>
    body {
        background-color: rgb(255, 253, 243);
    }

    .familia-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
    }

    .familia-card .card-header {
        background: #fff;
        border-bottom: 1px solid #eeeeee;
    }

    .tabla-familias th,
    .tabla-familias td {
        vertical-align: middle;
    }

    .acciones-familia {
        white-space: nowrap;
    }
</style>
@endsection

@section('contenido_titulo_pagina')
<div class="text-center mb-3">
    <h4 class="titulazo mb-1">Familias de Repuestos</h4>
    <small class="text-muted">
        Utilice las familias solamente para clasificar y organizar los repuestos.
        Los precios de venta se definirán durante la importación.
    </small>
</div>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    @include('fragm.mensajes')

    <div class="card familia-card mb-4">
        <div class="card-header">
            <h5 id="titulo-formulario" class="mb-0">Nueva familia</h5>
        </div>

        <div class="card-body">
            <form
                id="form-familia"
                class="form-horizontal"
                name="familia"
                method="post"
                action="{{ url('familia') }}"
            >
                {{ csrf_field() }}

                <input type="hidden" name="donde" value="familia">
                <input type="hidden" name="modificando" id="modificando" value="0">
                <input type="hidden" name="id_familia" id="id_familia" value="0">

                {{-- Se mantienen por compatibilidad con el controlador y la base de datos. --}}
                <input type="hidden" name="porcentaje" value="0">
                <input type="hidden" name="porcentaje_flete" value="0">

                <div class="row align-items-end">
                    <div class="col-md-6 col-lg-5 mb-3">
                        <label for="nombrefamilia">
                            Nombre de la familia <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="nombrefamilia"
                            value="{{ old('nombrefamilia') }}"
                            id="nombrefamilia"
                            class="form-control"
                            maxlength="100"
                            placeholder="Ejemplo: Frenos, Motor, Suspensión"
                            required
                        >
                    </div>

                    <div class="col-md-3 col-lg-2 mb-3">
                        <label for="prefijo">Prefijo</label>
                        <input
                            type="text"
                            name="prefijo"
                            value="{{ old('prefijo') }}"
                            id="prefijo"
                            class="form-control text-uppercase"
                            maxlength="5"
                            placeholder="Ej: FRE"
                        >
                        <small class="form-text text-muted">Opcional, máximo 5 caracteres.</small>
                    </div>

                    <div class="col-md-3 col-lg-5 mb-3">
                        <input
                            type="submit"
                            name="btnGuardarFamilia"
                            id="button"
                            value="Guardar familia"
                            class="btn btn-primary"
                        >

                        <button
                            type="button"
                            id="cancelar-edicion"
                            class="btn btn-secondary d-none"
                            onclick="cancelarEdicion()"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('contenido_ver_datos')
<div class="container-fluid">
    @if($familias->count() > 0)
        <div class="card familia-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Familias registradas</h5>
                <span class="badge badge-primary">{{ $familias->count() }}</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive tabla-scroll-y-500">
                    <table class="table table-hover tabla-familias mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="8%">ID</th>
                                <th>Familia</th>
                                <th width="18%">Prefijo</th>
                                <th width="30%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($familias as $familia)
                                <tr>
                                    <input
                                        type="hidden"
                                        id="nf-{{ $familia->id }}"
                                        value="{{ $familia->nombrefamilia }}"
                                    >
                                    <input
                                        type="hidden"
                                        id="pr-{{ $familia->id }}"
                                        value="{{ $familia->prefijo }}"
                                    >

                                    <td>{{ $familia->id }}</td>
                                    <td>
                                        <strong>{{ $familia->nombrefamilia }}</strong>
                                    </td>
                                    <td>
                                        @if(!empty($familia->prefijo))
                                            <span class="badge badge-secondary">
                                                {{ strtoupper($familia->prefijo) }}
                                            </span>
                                        @else
                                            <span class="text-muted">Sin prefijo</span>
                                        @endif
                                    </td>
                                    <td class="text-center acciones-familia">
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm"
                                            onclick="modificar('{{ $familia->id }}')"
                                            title="Modificar familia"
                                        >
                                            <i class="fa fa-edit"></i> Modificar
                                        </button>

                                        <a
                                            href="{{ url('familia/'.$familia->id.'/eliminar') }}"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirmacion()"
                                            title="Eliminar familia"
                                        >
                                            <i class="fa fa-trash"></i> Eliminar
                                        </a>

                                        <a
                                            href="{{ url('repuesto/descargar-repuestos-familia/'.$familia->id) }}"
                                            class="btn btn-success btn-sm"
                                            title="Descargar repuestos de la familia"
                                        >
                                            <i class="fa-solid fa-file-excel"></i> Exportar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Todavía no existen familias de repuestos. Cree la primera utilizando el formulario superior.
        </div>
    @endif
</div>
@endsection
