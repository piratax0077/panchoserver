<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use App\Mail\EnviarCorreo_comentario; 
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
        try {
            // Validar los datos del formulario de solicitud de restablecimiento de contraseña
            $this->validateEmail($request);

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
            
            // Obtener el email del formulario
            $correo_origen = 'ventas_online@panchorepuestos.cl';
            $usuario = 'Francisco';
            $a = 'ROJO GALLARDO';
            $correo_destino = $request->input('email');
            $telefono = '133';
            $comentario = 'pruieba';
            //Se le envia el token a la clase mail con el token del usuario
            $correo=new ResetPasswordMail($correo_origen,$correo_destino,$token);
          
            \Mail::send($correo); //Devuelve void
            $rpta=$correo_destino." enviado. Revise su bandeja de entrada.";
                    
                    if( count(\Mail::failures()) > 0 ) {

                        $rpta= "Error al enviar: <br>";

                        foreach(\Mail::failures() as $email_malo) {
                            $rpta.=" - $email_malo <br>";
                        }

                    }
            return redirect('login')->with('status', $rpta);;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
