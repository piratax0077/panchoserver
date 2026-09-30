@extends('plantillas.app')
@section('titulo', 'Rendimiento mensual')

@section('javascript')
<script>
    $(document).ready(function () {
        $('#btn_mostrar').on('click', cargarRendimiento);

        $('#periodo').on('change', function () {
            cargarRendimiento();
        });

        cargarRendimiento();
    });

    function cargarRendimiento() {
        const periodo = $('#periodo').val();

        if (!periodo) {
            Vue.swal({
                title: 'Seleccione un período',
                text: 'Debe seleccionar el mes que desea consultar.',
                icon: 'warning'
            });
            return;
        }

        const $boton = $('#btn_mostrar');
        $boton.prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Consultando');

        $('#estado_reporte').show();
        $('#resultado_rendimiento').hide();

        $.ajax({
            type: 'GET',
            url: '{{ url("/reportes/totales_mensuales") }}/' + periodo,
            success: function (respuesta) {
                resumen_mensual_desbloqueado = false;

                $('#resultado_rendimiento')
                    .html(respuesta)
                    .fadeIn(150);
            },
            error: function (error) {
                let mensaje = 'No fue posible generar el reporte mensual.';

                if (error.responseText) {
                    mensaje = error.responseText;
                }

                $('#resultado_rendimiento').html(
                    '<div class="alert alert-danger">' + mensaje + '</div>'
                ).show();
            },
            complete: function () {
                $('#estado_reporte').hide();
                $boton.prop('disabled', false)
                    .html('<i class="fa fa-search"></i> Consultar');
            }
        });
    }

    let resumen_mensual_desbloqueado = false;

    function solicitar_clave_resumen_mensual() {
        Vue.swal({
            title: 'Contenido protegido',
            text: 'Ingrese su contraseña para visualizar los indicadores del mes.',
            input: 'password',
            inputPlaceholder: 'Contraseña',
            confirmButtonText: '<i class="fa fa-unlock"></i> Desbloquear',
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'current-password'
            },
            inputValidator: function (valor) {
                if (!valor || !valor.trim()) {
                    return 'Debe ingresar su contraseña.';
                }
            },
            preConfirm: function (clave) {
                return $.ajax({
                    type: 'POST',
                    url: '{{ url("/clave") }}',
                    data: {
                        clave: clave
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                            .attr('content')
                    }
                }).then(function (respuesta) {
                    if (respuesta !== 'OK') {
                        throw new Error(
                            'La contraseña ingresada no es válida.'
                        );
                    }

                    return respuesta;
                }).catch(function (error) {
                    let mensaje =
                        'No fue posible validar la contraseña.';

                    if (error && error.message) {
                        mensaje = error.message;
                    }

                    Vue.swal.showValidationMessage(mensaje);
                });
            }
        }).then(function (resultado) {
            if (!resultado.isConfirmed) {
                return;
            }

            resumen_mensual_desbloqueado = true;

            $('#bloque_resumen_mensual')
                .stop(true, true)
                .slideDown(200);

            $('#btn_ver_resumen_mensual')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fa fa-lock"></i> Ocultar')
                .attr(
                    'onclick',
                    'bloquear_resumen_mensual()'
                );

            Vue.swal({
                icon: 'success',
                title: 'Indicadores desbloqueados',
                timer: 1200,
                showConfirmButton: false
            });
        });
    }

    function bloquear_resumen_mensual() {
        resumen_mensual_desbloqueado = false;

        $('#bloque_resumen_mensual').hide();

        $('#btn_ver_resumen_mensual')
            .removeClass('btn-outline-danger')
            .addClass('btn-outline-secondary')
            .html('<i class="fa fa-key"></i> Ver indicadores')
            .attr(
                'onclick',
                'solicitar_clave_resumen_mensual()'
            );
    }

</script>
@endsection

@section('style')
<style>
    .rendimiento-header {
        background: #6495ed;
        color: #fff;
        padding: 12px 16px;
        border-radius: 4px 4px 0 0;
    }

    .rendimiento-filtros {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-top: 0;
        padding: 16px;
        margin-bottom: 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .periodo-control {
        max-width: 330px;
    }

    .estado-reporte {
        display: none;
        padding: 35px 15px;
        text-align: center;
        color: #6c757d;
        font-weight: 600;
    }
</style>
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    <div class="rendimiento-header">
        <h4 class="mb-0">
            <i class="fa fa-line-chart"></i>
            Rendimiento mensual
        </h4>
        <small>
            Ventas, costos, utilidad y participación por vendedor.
        </small>
    </div>

    <div class="rendimiento-filtros">
        <div class="row align-items-end">
            <div class="col-md-4 col-sm-7">
                <label for="periodo"><strong>Mes del informe</strong></label>
                <select id="periodo" class="form-control periodo-control">
                    @php
                        $periodoActual = \Carbon\Carbon::now()->startOfMonth();
                    @endphp

                    @for($i = 0; $i < 36; $i++)
                        @php
                            $fechaOpcion = $periodoActual->copy()->subMonths($i);
                            $valorOpcion = $fechaOpcion->format('Y-m');
                            $textoOpcion = ucfirst(
                                $fechaOpcion->locale('es')->translatedFormat('F Y')
                            );
                        @endphp
                        <option value="{{ $valorOpcion }}">
                            {{ $textoOpcion }}
                        </option>
                    @endfor
                </select>
                <small class="text-muted">
                    Se muestran los últimos 36 meses disponibles.
                </small>
            </div>

            <div class="col-md-3 col-sm-5 mt-3 mt-sm-0">
                <button type="button"
                        id="btn_mostrar"
                        class="btn btn-success">
                    <i class="fa fa-search"></i> Consultar
                </button>
            </div>
        </div>
    </div>

    <div id="estado_reporte" class="estado-reporte">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
        <div class="mt-2">Calculando el rendimiento del mes...</div>
    </div>

    <div id="resultado_rendimiento"></div>
</div>
@endsection
