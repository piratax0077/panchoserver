@extends('plantillas.app')

@section('contenido')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
            <div class="card-header">CAMBIAR CLAVE </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('cambiarclave') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="antiguaclave" class="col-md-4 col-form-label text-md-right">{{ __('Clave Anterior') }}</label>

                            <div class="col-md-6">
                                <input id="antiguaclave" type="password" class="form-control @error('antiguaclave') is-invalid @enderror" name="antiguaclave">

                                @error('antiguaclave')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nuevaclave" class="col-md-4 col-form-label text-md-right">{{ __('Nueva Clave') }}</label>

                            <div class="col-md-6">
                                <input id="nuevaclave" type="password" class="form-control @error('nuevaclave') is-invalid @enderror" name="nuevaclave">

                                @error('nuevaclave')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="repetirnuevaclave" class="col-md-4 col-form-label text-md-right">{{ __('Repetir Nueva Clave') }}</label>

                            <div class="col-md-6">
                                <input id="repetirnuevaclave" type="password" class="form-control @error('repetirnuevaclave') is-invalid @enderror" name="repetirnuevaclave" required>

                                @error('repetirnuevaclave')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Proceder al Cambio') }}
                                </button>
                                <p style="color:green">{{$mensaje}}</p>
                            </div>
                        </div>

                    </form>




                </div>
            </div>
        </div>
    </div>
    <hr>
</div>
@endsection
