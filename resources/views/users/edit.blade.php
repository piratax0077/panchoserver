@extends('plantillas.app')

@section('titulo', 'Editar usuario')

@section('style')
<style>
    .user-edit-page {
        --user-primary: #2563eb;
        --user-primary-dark: #1d4ed8;
        --user-border: #e2e8f0;
        --user-muted: #64748b;
        min-height: calc(100vh - 190px);
        padding: 38px 15px 64px;
        background: #f6f8fb;
    }

    .user-edit-shell {
        width: 100%;
        max-width: 920px;
        margin: 0 auto;
    }

    .user-edit-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: #475569;
        font-weight: 600;
        text-decoration: none;
    }

    .user-edit-back:hover {
        color: var(--user-primary);
        text-decoration: none;
    }

    .user-edit-card {
        overflow: hidden;
        border: 1px solid var(--user-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
    }

    .user-edit-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--user-border);
        background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
    }

    .user-edit-header-icon {
        display: flex;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #dbeafe;
        color: var(--user-primary);
        font-size: 18px;
    }

    .user-edit-header h1 {
        margin: 0 0 3px;
        color: #0f172a;
        font-size: 22px;
        font-weight: 700;
    }

    .user-edit-header p {
        margin: 0;
        color: var(--user-muted);
        font-size: 14px;
    }

    .user-edit-body {
        padding: 28px;
    }

    .user-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 22px;
    }

    .user-field label,
    .user-avatar-label {
        display: block;
        height: auto;
        margin: 0 0 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }

    .user-field .form-control {
        height: 46px;
        padding: 10px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        color: #1e293b;
        box-shadow: none;
    }

    .user-field .form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .user-avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 24px;
        padding: 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
    }

    .user-avatar-preview {
        display: flex;
        flex: 0 0 82px;
        width: 82px;
        height: 82px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        box-shadow: 0 0 0 1px #cbd5e1;
        font-size: 28px;
    }

    .user-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar-control {
        min-width: 0;
        flex: 1;
    }

    .user-avatar-control small {
        display: block;
        margin-top: 7px;
        color: var(--user-muted);
    }

    .user-avatar-control .custom-file-label {
        overflow: hidden;
        border-color: #cbd5e1;
        border-radius: 9px;
        color: #64748b;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .user-edit-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid var(--user-border);
    }

    .user-edit-actions .btn {
        display: inline-flex;
        min-height: 44px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 0;
        padding: 10px 18px;
        border-radius: 9px;
        font-weight: 600;
    }

    .user-save-btn {
        border-color: var(--user-primary);
        background: var(--user-primary);
    }

    .user-save-btn:hover {
        border-color: var(--user-primary-dark);
        background: var(--user-primary-dark);
    }

    @media (max-width: 767.98px) {
        .user-edit-page { padding-top: 22px; }
        .user-edit-header, .user-edit-body { padding: 20px; }
        .user-edit-grid { grid-template-columns: 1fr; }
        .user-avatar-section { align-items: flex-start; }
        .user-edit-actions { align-items: stretch; flex-direction: column-reverse; }
        .user-edit-actions .btn { width: 100%; }
    }
</style>
@endsection

@section('contenido')
<main class="user-edit-page">
    <div class="user-edit-shell">
        <a href="{{ url('usuarios') }}" class="user-edit-back">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Volver a usuarios</span>
        </a>

        <section class="user-edit-card" aria-labelledby="user-edit-title">
            <header class="user-edit-header">
                <span class="user-edit-header-icon" aria-hidden="true"><i class="fas fa-user-pen"></i></span>
                <div>
                    <h1 id="user-edit-title">Editar usuario</h1>
                    <p>Actualiza los datos personales y la imagen de perfil.</p>
                </div>
            </header>

            <div class="user-edit-body">
                <form method="POST" action="{{ route('user.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="user-edit-grid">
                        <div class="user-field">
                            <label for="name">Nombre completo</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="user-field">
                            <label for="rut">RUT</label>
                            <input id="rut" type="text" class="form-control @error('rut') is-invalid @enderror"
                                   name="rut" value="{{ old('rut', $user->rut) }}" required autocomplete="off" placeholder="12.345.678-9">
                            @error('rut')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="user-field">
                            <label for="telefono">Teléfono</label>
                            <input id="telefono" type="tel" class="form-control @error('telefono') is-invalid @enderror"
                                   name="telefono" value="{{ old('telefono', $user->telefono) }}" required autocomplete="tel" placeholder="+56 9 1234 5678">
                            @error('telefono')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="user-field">
                            <label for="email">Correo electrónico</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        @if($user->rol->nombrerol == 'Administrador')
                            <div class="user-field">
                                <label for="roles">Rol</label>
                                <select name="roles" id="roles" class="form-control">
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}" {{ (string) old('roles', $user->role_id) === (string) $rol->id ? 'selected' : '' }}>
                                            {{ $rol->nombrerol }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="user-avatar-section">
                        <div class="user-avatar-preview" aria-hidden="true">
                            @if($user->image_path)
                                <img src="{{ url('usuarios/avatar/'.$user->image_path) }}" alt="" id="avatar-preview-image">
                            @else
                                <i class="fas fa-user" id="avatar-preview-placeholder"></i>
                                <img src="" alt="" id="avatar-preview-image" style="display:none;">
                            @endif
                        </div>

                        <div class="user-avatar-control">
                            <label for="avatar" class="user-avatar-label">Imagen de perfil</label>
                            <div class="custom-file">
                                <input type="file" id="avatar" class="custom-file-input @error('avatar') is-invalid @enderror"
                                       name="avatar" accept="image/png,image/jpeg,image/webp">
                                <label class="custom-file-label" for="avatar" data-browse="Buscar">Seleccionar una imagen</label>
                                @error('avatar')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <small>Déjalo vacío para conservar la imagen actual. Formatos: JPG, PNG o WEBP.</small>
                        </div>
                    </div>

                    <div class="user-edit-actions">
                        <a href="{{ url('cambiarclave/'.$user->id) }}" class="btn btn-outline-warning">
                            <i class="fas fa-key" aria-hidden="true"></i>
                            Cambiar clave
                        </a>
                        <button type="submit" class="btn btn-primary user-save-btn">
                            <i class="fas fa-save" aria-hidden="true"></i>
                            Guardar cambios
                        </button>
                    </div>

                    <input type="hidden" name="userId" value="{{ $user->id }}">
                </form>
            </div>
        </section>
    </div>
</main>
@endsection

@section('javascript_footer')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var avatarInput = document.getElementById('avatar');
        if (!avatarInput) return;

        avatarInput.addEventListener('change', function (event) {
            var file = event.target.files && event.target.files[0];
            var label = document.querySelector('label[for="avatar"].custom-file-label');
            if (label) label.textContent = file ? file.name : 'Seleccionar una imagen';
            if (!file || !file.type.match(/^image\//)) return;

            var preview = document.getElementById('avatar-preview-image');
            var placeholder = document.getElementById('avatar-preview-placeholder');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        });
    });
</script>
@endsection
