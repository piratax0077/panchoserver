<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Debugbar;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $msg="Error desconocido...";
        $status=-1;

        if ($exception instanceof \Illuminate\Session\TokenMismatchException)  {
            $msg="Mucho tiempo inactivo y por eso su sesión ha expirado. Debe Iniciar Sesión Nuevamente. (419)";
            $status=419;
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException){
            $msg="No esta Autenticado. Debe Iniciar Sesión. (401)";
            $status=401;
        }

        if($request->ajax())
        {
            //Debugbar::info("Exceptions Handler: es ajax");
            if($status==-1)
            {
                return parent::render($request, $exception);
            }
            return response($msg,$status); //Debe ser una instancia de response siempre
        }else{
            //Debugbar::info("Exceptions Handler: NO es ajax");
            if($status==419) return redirect("/sesionexpiro");
            if($status==401) return redirect("/noautenticado");
            if($status==-1) return parent::render($request, $exception);
        }



    }

    /*
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
                    ? response()->json(['message' => $exception->getMessage()], 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
    */
}
