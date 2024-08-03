@extends('plantillas.login')

@section('javascript')
<script>
    function eliminar_token(){
        console.log('eliminando token');
        localStorage.removeItem('token');
        localStorage.removeItem('opt');
        localStorage.removeItem('user');

        // Simulate a mouse click:
        window.location.href = "/";

    }
</script>
@endsection

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
                        <h4>SU SESIÓN EXPIRÓ.</h4>
                    </div>
                    <div class="form-group row justify-content-center">
                            <a class="btn btn-info" href="javascript:void(0)" onclick="eliminar_token()">LOGIN</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


