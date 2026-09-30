<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\User;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        // Mantener los errores de validación junto al campo de correo.
        $this->validateEmail($request);

        try {
            $email = $request->input('email'); // Obtener el correo electrónico del usuario desde la solicitud

            //Rol de usuarios que son clientes en la página web.
            $rol_usuario_web = 20;

            $user = User::where('email', $email)->where('role_id','<>',$rol_usuario_web)->first(); // Buscar al usuario por su correo electrónico
        
            if (!$user) {
                // Si el usuario no existe, puedes manejar el error de acuerdo a tus necesidades
                return redirect('login')->with('error', 'Correo electrónico no encontrado. No se puede reestablecer la contraseña.');
            }

            // Generar un nuevo token de restablecimiento de contraseña
            $token = app('auth.password.broker')->createToken($user);
            
            $correo_destino = $request->input('email');

            //Se le envia el token a la clase mail con el token del usuario
            Mail::send(new ResetPasswordMail($correo_destino, $token));

            return redirect('login')->with(
                'status',
                'Enviamos el enlace de recuperación. Revise su bandeja de entrada y la carpeta de spam.'
            );
        } catch (\Throwable $e) {
            Log::error('No fue posible enviar el correo de recuperación de contraseña.', [
                'exception' => $e,
            ]);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'No fue posible enviar el correo de recuperación. Inténtelo nuevamente más tarde.');
        }
        
    }
}
