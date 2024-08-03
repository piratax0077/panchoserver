@extends('plantillas.login')

@section('contenido')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header alert alert-info text-center">
                    <h3>PANCHO REPUESTOS</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row justify-content-center alert alert-danger">
                        <h4>SU SESION HA EXPIRADO POR INACTIVIDAD.</h4>
                    </div>
                    <div class="form-group row justify-content-center">
                            <a class="btn btn-info" href="{{url('/login')}}">LOGIN</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

