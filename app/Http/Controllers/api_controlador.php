<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\User;
use App\User_web;
use App\compra_transbank;
use App\repuesto;
use App\repuestofoto;
use Illuminate\Support\Facades\Validator;
use App\despacho_domicilio;
use Carbon\Carbon; // para tratamiento de fechas
use App\marcarepuesto;
use App\modelovehiculo;
use App\similar;
use App\carrito_virtual;
use App\familia;
use App\marcavehiculo;
use App\carrito_virtual_detalle;
use App\retiro_tienda;
use App\repuesto_catalogo;
use App\repuesto_carrito;
use App\correlativo;
use App\cliente_modelo;
use App\boleta;
use App\boleta_detalle;
use App\factura;
use App\factura_detalle;
use App\pago;
use App\proveedor;
use App\oferta_pagina_web;
use App\oferta_catalogo;
use App\pais;
use App\rol;
use App\regulador_voltaje;
use Session;
use App\servicios_sii\ClsSii_online;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Auto;
use App\servicios_sii\Sii;
use App\Mail\EnviarCorreo;
use App\descuento;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Password;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use App\Mail\EnviarCorreo_comentario;
use App\Mail\ResetPasswordMail;
use App\Mail\ResetPasswordMail_web;

use Illuminate\Support\Facades\Auth;

class api_controlador extends Controller
{
    use SendsPasswordResetEmails;

    // Método para enviar el correo con el enlace de restablecimiento de contraseña
    public function sendResetLinkEmail(Request $request)
    {
        try {
            // Validar los datos del formulario de solicitud de restablecimiento de contraseña
            $this->validateEmail($request);

            $email = $request->input('email'); // Obtener el correo electrónico del usuario desde la solicitud

            //Rol de usuarios que son clientes en la página web.
            $rol_usuario_web = 20;

            $user = User::where('email', $email)->where('role_id',$rol_usuario_web)->first(); // Buscar al usuario por su correo electrónico
            
            if (empty($user)) {
                // Si el usuario no existe, puedes manejar el error de acuerdo a tus necesidades
                return response()->json([
                    'message' => 'Correo electrónico no encontrado. No se puede reestablecer la contraseña.',
                ], 500);
            }

            // Generar un nuevo token de restablecimiento de contraseña
            $token = app('auth.password.broker')->createToken($user);
            //Guardamos el token de seguridad
            $user->remember_token = $token;
            $user->save();
            // Obtener el email del formulario
            $correo_origen = 'administrador@panchorepuestos.cl';
            
            $correo_destino = $request->input('email');
            
            //Se le envia el token a la clase mail con el token del usuario
            $correo=new ResetPasswordMail_web($correo_origen,$correo_destino,$token);
            
            \Mail::send($correo); //Devuelve void
            $rpta=$correo_destino." enviado. Revise su bandeja de entrada.";
                    
                    if( count(\Mail::failures()) > 0 ) {

                        $rpta= "Error al enviar: <br>";
                        
                        foreach(\Mail::failures() as $email_malo) {
                            $rpta.=" - $email_malo <br>";
                        }
                        //Respuesta con errores.
                        return response()->json([
                            'message' => $rpta,
                        ], 500);
                    }

            //Respuesta satisfactoria.
            return response()->json([
                'message' => $rpta,
            ], 200);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
    
    public function prueba(){
      
        $m=marcavehiculo::where('activo','=',1)
                        ->select('idmarcavehiculo','marcanombre','urlfoto')
                        ->where('idmarcavehiculo','<>',60)
                        ->where('idmarcavehiculo','<>',2)
                        ->where('idmarcavehiculo','<>',3)
                        ->where('idmarcavehiculo','<>',4)
                        ->where('idmarcavehiculo','<>',8)
                        ->where('idmarcavehiculo','<>',12)
                        ->where('idmarcavehiculo','<>',17)
                        ->where('idmarcavehiculo','<>',18)
                        ->where('idmarcavehiculo','<>',20)
                        ->where('idmarcavehiculo','<>',21)
                        ->where('idmarcavehiculo','<>',23)
                        ->where('idmarcavehiculo','<>',24)
                        ->where('idmarcavehiculo','<>',25)
                        ->where('idmarcavehiculo','<>',29)
                        ->where('idmarcavehiculo','<>',30)
                        ->where('idmarcavehiculo','<>',33)
                        ->where('idmarcavehiculo','<>',34)
                        ->where('idmarcavehiculo','<>',36)
                        ->where('idmarcavehiculo','<>',38)
                        ->where('idmarcavehiculo','<>',39)
                        ->where('idmarcavehiculo','<>',40)
                        ->where('idmarcavehiculo','<>',43)
                        ->where('idmarcavehiculo','<>',45)
                        ->where('idmarcavehiculo','<>',47)
                        ->where('idmarcavehiculo','<>',49)
                        ->where('idmarcavehiculo','<>',50)
                        ->where('idmarcavehiculo','<>',54)
                        ->where('idmarcavehiculo','<>',56)
                        ->where('idmarcavehiculo','<>',57)
                        ->where('idmarcavehiculo','<>',58)
                        ->where('idmarcavehiculo','<>',59)
                        ->where('idmarcavehiculo','<>',61)
                        ->where('idmarcavehiculo','<>',63)
                        ->where('idmarcavehiculo','<>',67)
                        ->where('idmarcavehiculo','<>',65)
                        ->where('idmarcavehiculo','<>',66)
                        ->orderBy('marcanombre')
                        ->get();
        return $m;
    }

    public function repuestos_index(){
        return 0;
    }

    public function authenticate(Request $request)
    {
    
      //Indicamos que solo queremos recibir email y password de la request
      $credentials = $request->only('email', 'password');
     
      
      //Validaciones
      $validator = Validator::make($credentials, [
          'email' => 'required|email',
          'password' => 'required|string|min:6|max:50'
      ]);

      
      //Devolvemos un error de validación en caso de fallo en las verificaciones
      if ($validator->fails()) {
          return response()->json(['error' => $validator->messages()], 400);
      }
      //Intentamos hacer login
      try {
      
          if (!$token = JWTAuth::attempt($credentials)) {
            
              //Credenciales incorrectas.
              return response()->json([
                  'message' => 'Login failed!',
              ], 401);
          }
      } catch (JWTException $e) {
          //Error chungo
         
          return response()->json([
              'message' => 'Error',
          ], 500);
      }

      
      //Devolvemos el token
      return response()->json([
          'token' => $token,
          'user' => Auth::user()
      ]);
    }

    public function register(Request $request)
    {
       try {
            // Definir mensajes de validación personalizados
            $messages = [
                'name.required' => 'El nombre es requerido.',
                'name.min' => 'El nombre debe tener mínimo 3 caracteres.',
                'apellidos.required' => 'Apellidos es requerido.',
                'apellidos.min' => 'Los apellidos deben tener mínimo 4 caracteres.',
                'email.required' => 'El correo electrónico es requerido.',
                'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
                'email.unique' => 'El correo electrónico ya está en uso.',
                'telefono.required' => 'El telefono es requerido.',
                'telefono.regex' => 'El formato del teléfono no es válido.',
                'password.required' => 'La password es requerida.',
                'password.string' => 'La password debe ser una cadena de texto.',
                'password.min' => 'La password debe tener al menos :min caracteres.',
                'confirm_password.required' => 'El campo confirmación de contraseña es requerido.',
                'cemail.max' => 'El correo electrónico no puede tener más de :max caracteres.',
                'onfirm_password.same' => 'El campo confirmación de contraseña debe ser igual a la contraseña.'
            ];

            // Validar los datos del formulario con los mensajes personalizados
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3',
                'apellidos' => 'required|string|min:4',
                'email' => 'required|email|max:255|unique:users',
                'telefono' => 'required|regex:/^[\d+\-\s()]*$/|string|max:255',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|same:password' // Validar que la confirmación del password sea requerida y sea igual al password
            ], $messages);

            // Manejar errores de validación
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $name = $request->input('name').' '.$request->input('apellidos');
            $rut = $request->input('rut');
            
            $telefono = $request->input('telefono');
            $email = $request->input('email');
            $password = $request->input('password');
            //$image_path = $request->file('avatar');
            $role_id = $request->input('role');

            $password_encrypt = Hash::make($password);
           
            $user =  User::create([
                'name' => $name,
                'rut' => '11111111-1',
                'telefono' => $telefono,
                'email' => $email,
                'password' => $password_encrypt,
                'image_path' => '---',
                'role_id' => $role_id
            ]);

            $token = JWTAuth::fromUser($user);
            $user->remember_token = $token;
            $user->update();
            return response()->json(compact('user','token'),201); 
       } catch (\Exception $e) {
        return $e->getMessage();
       }
           
    }
    
    public function update_user(Request $request){
        // Definir mensajes de validación personalizados
        $messages = [
            'name.required' => 'El nombre es requerido.',
            'name.min' => 'El nombre debe tener mínimo 3 caracteres.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'telefono.required' => 'El telefono es requerido.',
            'telefono.regex' => 'El formato del teléfono no es válido.',
            'password.required' => 'La password es requerida.',
            'password.string' => 'La password debe ser una cadena de texto.',
            'password.min' => 'La password debe tener al menos :min caracteres.',
            'confirm_password.required' => 'El campo confirmación de contraseña es requerido.',
            'cemail.max' => 'El correo electrónico no puede tener más de :max caracteres.',
            'onfirm_password.same' => 'El campo confirmación de contraseña debe ser igual a la contraseña.'
        ];

        // Validar los datos del formulario con los mensajes personalizados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'email'=>'required|email',
            'telefono' => 'required|regex:/^[\d+\-\s()]*$/|string|max:255',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password' // Validar que la confirmación del password sea requerida y sea igual al password
        ], $messages);

        // Manejar errores de validación
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id_usuario = $request->input('id_usuario');
        $name = $request->input('name');
        $email = $request->input('email');
        $telefono = $request->input('telefono');
        $password = $request->input('password');
        //$image_path = $request->file('avatar');
        $role_id = $request->input('role');

        $password_encrypt = Hash::make($password);
        $user = User::find($id_usuario);
        $user->name = $name;
        $user->email = $email;
        $user->telefono = $telefono;
        $user->password = $password_encrypt;
        $user->save();
        return 'OK';
    }

    public function damefamilias()
    {
        //$f=familia::orderBy('nombrefamilia')->get();
        try{
            $s="SELECT repuestos.id_familia, familias.id, familias.nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos inner join familias on repuestos.id_familia=familias.id  GROUP by repuestos.id_familia order by familias.nombrefamilia";
            $familias=\DB::select($s);
            return $familias;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function damerepuestos(){
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->orderBy('repuestos.descripcion','asc')
                                ->get();
      
        $repuestos_= [];
        $marcas_repuestos = [];
        $familias_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);

                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            //Se busca el valor en oferta del repuesto
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        array_push($familias_repuestos,$repuesto->nombrefamilia);
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        array_push($familias_repuestos,$repuesto->nombrefamilia);
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return [$repuestos_, array_values(array_unique($marcas_repuestos)),array_values(array_unique($familias_repuestos))];
    }


    public function damerepuesto($id_repuesto)
        {
            try {
                $repuesto_original = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto','paises.nombre_pais','paises.id as pais_id','familias.nombrefamilia')
                                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                                            ->join('familias','repuestos.id_familia','familias.id')
                                            ->where('repuestos.id',$id_repuesto)
                                            ->first();

                

            // get next repuesto id
            try {
                // Obtener la fecha actual
                $fechaActual = date('Y-m-d');

                // Restar 60 días
                $fechaRestada = strtotime('-60 days', strtotime($fechaActual));

                // Convertir la fecha restada de UNIX timestamp a formato de fecha
                $fechaRestadaFormateada = date('Y-m-d', $fechaRestada);
            
                // El id del repuesto siguiente debe tener un máximo de fecha de actualización hasta 60 días
                $next = repuesto::where('id', '>', $repuesto_original->id)
                ->where('stock_actual','>',0)
                ->where('id_familia','<>',312)
                ->where('fecha_actualiza_precio','>',$fechaRestadaFormateada)
                ->where('activo',1)
                ->min('id');

                // El id del repuesto anterior tambien debe tener un máximo de fecha de actualización hasta 60 días
                $previous = repuesto::where('id','<',$repuesto_original->id)
                ->where('stock_actual','>',0)
                ->where('id_familia','<>',312)
                ->where('fecha_actualiza_precio','>',$fechaRestadaFormateada)
                ->where('activo',1)
                ->max('id');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
                            
            $fotos=repuestofoto::where('id_repuestos','=',$id_repuesto)->get();
            

            $recomendados = $this->damerecomendados($id_repuesto);
            
            $similares_ = $this->damesimilares($id_repuesto);
            $similares = similar::where('similares.id_repuestos',$id_repuesto)
                                        ->join('repuestos','similares.id_repuestos','repuestos.id')
                                        ->get();
            
            $idmodelos = [];
            foreach ($similares as $similar) {
                array_push($idmodelos,$similar->id_modelo_vehiculo);
            }
            
            
            if(count($idmodelos) > 0){
                $re = similar::where('id_modelo_vehiculo',$idmodelos[0])->get();
            }else{
                $re = [];
            }
            
            $repuestos_prueba = [];
            foreach ($re as $r) {
                $rep = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->where('repuestos.id',$r->id_repuestos)
                                ->first();
                
                array_push($repuestos_prueba,$rep);
            }
            
            $recomendados_ = [];
        
            foreach($repuestos_prueba as $repuesto){
                if(is_null($repuesto) ){

                }else{
                    if($repuesto->stock_actual > 0 && $repuesto->id_familia == $repuesto_original->id_familia && $repuesto->id !== $repuesto_original->id ){
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');
            
                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
            
                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
            
                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if($dias<= 60){
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($recomendados_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($recomendados_,$rep);
                            }
                        }
                    }
                }
                
                
                
            
        }

        // buscamos los rectificadores del repuesto
        $rectificadores = regulador_voltaje::select('rectificador')
        ->where('id_repuesto',$id_repuesto)
        ->where('rectificador','<>','---')
        ->get();
        // buscamos los alternadors del repuesto
        $alternadores = regulador_voltaje::select('alternador')
        ->where('id_repuesto',$id_repuesto)
        ->where('alternador','<>','---')
        ->get();

        //Verificamos si existe el repuesto en oferta
        $existe = oferta_pagina_web::where('id_repuesto',$id_repuesto)->first();
        $flag = 0;
    
        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto_original->id_familia)->where('id_local','<>',1)->first();
        if($existe) return [$repuesto_original,$fotos, $similares_,$recomendados_,$existe,$next,$previous,$rectificadores, $alternadores];    
        

        if($descuento) $repuesto_original->precio_venta = $repuesto_original->precio_venta - (($descuento->porcentaje/100) * $repuesto_original->precio_venta); 

        

        return [$repuesto_original,$fotos, $similares_,$recomendados_,0,$next,$previous, $rectificadores, $alternadores]; 
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function damerepuesto_demo($id_repuesto)
        {
            try {
                $repuesto_original = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto','paises.nombre_pais','paises.id as pais_id','familias.nombrefamilia')
                                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                                            ->join('familias','repuestos.id_familia','familias.id')
                                            ->where('repuestos.id',$id_repuesto)
                                            ->first();

                

            // get next repuesto id
            try {
                // Obtener la fecha actual
                $fechaActual = date('Y-m-d');

                // Restar 60 días
                $fechaRestada = strtotime('-60 days', strtotime($fechaActual));

                // Convertir la fecha restada de UNIX timestamp a formato de fecha
                $fechaRestadaFormateada = date('Y-m-d', $fechaRestada);
            
                // El id del repuesto siguiente debe tener un máximo de fecha de actualización hasta 60 días
                $next = repuesto::where('id', '>', $repuesto_original->id)
                ->where('stock_actual','>',0)
                ->where('id_familia','<>',312)
                ->where('fecha_actualiza_precio','>',$fechaRestadaFormateada)
                ->where('activo',1)
                ->min('id');

                // El id del repuesto anterior tambien debe tener un máximo de fecha de actualización hasta 60 días
                $previous = repuesto::where('id','<',$repuesto_original->id)
                ->where('stock_actual','>',0)
                ->where('id_familia','<>',312)
                ->where('fecha_actualiza_precio','>',$fechaRestadaFormateada)
                ->where('activo',1)
                ->max('id');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
                            
            $fotos=repuestofoto::where('id_repuestos','=',$id_repuesto)->get();
            

            $recomendados = $this->damerecomendados($id_repuesto);
            
            $similares_ = $this->damesimilares($id_repuesto);
            $similares = similar::where('similares.id_repuestos',$id_repuesto)
                                        ->join('repuestos','similares.id_repuestos','repuestos.id')
                                        ->get();
            
            $idmodelos = [];
            foreach ($similares as $similar) {
                array_push($idmodelos,$similar->id_modelo_vehiculo);
            }
            
            
            if(count($idmodelos) > 0){
                $re = similar::where('id_modelo_vehiculo',$idmodelos[0])->get();
            }else{
                $re = [];
            }
            
            $repuestos_prueba = [];
            foreach ($re as $r) {
                $rep = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->where('repuestos.id',$r->id_repuestos)
                                ->first();
                
                array_push($repuestos_prueba,$rep);
            }
            
            $recomendados_ = [];
        
            foreach($repuestos_prueba as $repuesto){
                if(is_null($repuesto) ){

                }else{
                    if($repuesto->stock_actual > 0 && $repuesto->id_familia == $repuesto_original->id_familia && $repuesto->id !== $repuesto_original->id ){
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');
            
                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
            
                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
            
                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if($dias<= 60){
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($recomendados_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($recomendados_,$rep);
                            }
                        }
                    }
                }
                
                
                
            
        }
        //Verificamos si existe el repuesto en oferta
        $existe = oferta_pagina_web::where('id_repuesto',$id_repuesto)->first();
        $flag = 0;
    
        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto_original->id_familia)->where('id_local','<>',1)->first();
        if($existe) return [$repuesto_original,$fotos, $similares_,$recomendados_,$existe,$next,$previous];    
        

        if($descuento) $repuesto_original->precio_venta = $repuesto_original->precio_venta - (($descuento->porcentaje/100) * $repuesto_original->precio_venta); 
        
        return [$repuesto_original,$fotos, $similares_,$recomendados_,0,$next,$previous]; 
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dameproveedores_demo(){
        try {
            $proveedores = proveedor::where('activo',1)->orderBy('empresa_nombre','asc')->get();
            $marcas = marcavehiculo::orderBy('marcanombre','asc')->get();
            $familias = familia::orderBy('nombrefamilia','asc')->get();
            $marcasrepuestos = marcarepuesto::orderBy('marcarepuesto','asc')->get();
            $paises = pais::orderBy('nombre_pais','asc')->get();
            return [$proveedores,$marcas,$familias, $marcasrepuestos,$paises];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function damepormarca_demo($idMarca){
        try {
            // $this->validaSesion();
            $mc = new modelovehiculocontrolador;
            $modelos=$mc->dame_modelos_por_marca($idMarca);
            return $modelos->toJson();
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function dameroles_demo(){
        try {
            $roles = rol::all();
            return $roles->toJson();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function damemodelos($idmarca){
        $modelos = modelovehiculo::where('marcavehiculos_idmarcavehiculo',$idmarca)->orderBy('modelonombre')->get();
        return $modelos;
    }

    public function dameaniosvehiculo($idmodelo){
        $modelo = modelovehiculo::where('id',$idmodelo)->first();
        return $modelo;
    }

    private function damerecomendados($id_repuesto){
        $repuesto = repuesto::where('id',$id_repuesto)->first();
        $id_familia = $repuesto->id_familia;
        $similares = $this->damesimilares($id_repuesto);
        $recomendados = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto','familias.nombrefamilia')
                                ->where('repuestos.id_familia',$id_familia)
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id','!=',$id_repuesto)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->orderBy('repuestos.precio_venta','desc')
                                ->take(8)
                                ->get();
        return $recomendados;
    }

    public function buscar_por_modelo($idmodelo,$idfamilia){
        $familia = familia::where('id',$idfamilia)->first();
        $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();
       
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.id_familia',$idfamilia)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','familias.id','repuestos.id_familia')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();

                $repuestos_= [];
                $marcas_repuestos = [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){
                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    //Se busca el valor en oferta del repuesto
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;

                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    //Se busca el valor en oferta del repuesto
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;

                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                            }
                        }
                        
                    }
                        
                
                    return [$repuestos_,$modelo, array_values(array_unique($marcas_repuestos)),$familia];


    }

    public function buscar_por_oem($oem){
        try {
          
            $pos=strpos($oem,"-");
            if($pos===false)
            {
                $buscado_sin_guion=$oem;
                $buscado_con_guion=substr($oem,0,5)."-".substr($oem,5);
            }else{
                $buscado_sin_guion=str_replace("-","",$oem); //quitar guion
                $buscado_con_guion=$oem;
            }

     
    
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                    ->groupBy('repuestos.id')
                    ->get();
    
                    $repuestos_= [];
                    $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
                }
                
            }
                
        
        return [$repuestos_, array_values(array_unique($marcas_repuestos))];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }

    public function agregar_carrito_virtual(Request $req){
       
        $idrep = $req->idrep;
        $cantidad = $req->cantidad;
        $usuario_id = Auth::user()->id;
        if($usuario_id == '' || !$usuario_id){
            return response()->json([
                'message' => 'Le informamos que para poder agregar repuestos al carrito debe registrarse.',
            ], 500);
        }
        try {
            $correlativo = correlativo::find(11);
      
            $existe = carrito_virtual::where('usuario_id',$usuario_id)->where('activo',1)->first();
            
            if(!$existe){
                $carrito_virtual = new carrito_virtual;
                
                $carrito_virtual->fecha_emision = Carbon::today()->toDateString();
                $carrito_virtual->numero_carrito = intval($correlativo->correlativo) + 1;
                
                $carrito_virtual->activo = 1;
                $carrito_virtual->usuario_id = $usuario_id;
                $carrito_virtual->save();

                $correlativo->correlativo = $carrito_virtual->numero_carrito;
                $correlativo->save();
            }
            $carrito_existente = $this->damecarritoexistente($usuario_id);
            
            
            $pregunta = carrito_virtual_detalle::where('repuesto_id',$idrep)->where('carrito_numero',$carrito_existente->numero_carrito)->get();
            if($pregunta->count() > 0){
                return response()->json([
                    'message' => 'Repuesto ya existe en carrito!!',
                ], 500);
            }else{
              
                $repuesto = repuesto::find($idrep);
                $existe_oferta = oferta_pagina_web::where('id_repuesto',$idrep)->get();
                $detalle = new carrito_virtual_detalle;
           
                $detalle->repuesto_id = $idrep;
                $detalle->cantidad = $cantidad;
                $detalle->carrito_numero = $carrito_existente->numero_carrito;
                if(count($existe_oferta) > 0){
                    $detalle->pu = intval($existe_oferta[0]->precio_actualizado); //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR
                }else{
                    $detalle->pu = intval($repuesto->precio_venta); //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR
                }
                
                $detalle->pu_neto=round($detalle->pu/(1+Session::get('PARAM_IVA')),2);
                $detalle->descuento_item = 0.00;
                $detalle->subtotal_item = $detalle->cantidad * $detalle->pu;
                $detalle->save();
                $carrito_existente->fecha_emision = Carbon::today()->toDateString();
                // Guardamos la fecha de ingreso del ultimo item del carrito
                $carrito_existente->save();

                $items = $this->revisar_carrito_virtual($usuario_id);
     
    
                return response()->json([
                    'message' => 'OK'
                ]);
            }
            
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
        
    }

    public function damecarritoexistente($usuario_id){
        $carro = carrito_virtual::where('usuario_id',$usuario_id)->where('activo',1)->first();
        return $carro;
    }

   public function revisar_carrito_virtual(){
   
    try {
        $usuario_id = Auth::user()->id;
        if(!$usuario_id){
            return response()->json([
                'repuestos' => []
            ]);
        }
        $carrito = carrito_virtual::where('usuario_id',$usuario_id)
                                            ->where('activo',1)
                                        ->first();
        if($carrito){
            $carrito_virtual = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.*','carrito_virtual.numero_carrito')
            ->join('carrito_virtual','carrito_virtual.numero_carrito','carrito_virtual_detalle.carrito_numero')
            ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
            
            ->where('carrito_virtual_detalle.carrito_numero',$carrito->numero_carrito)
            ->get();
            $repuestos_= [];
            foreach($carrito_virtual as $repuesto){
                    $existe = oferta_pagina_web::where('id_repuesto',$repuesto->id)->get();
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0){
                        $rep = new repuesto_carrito;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if(count($existe) > 0){
                                $rep->precio_venta = $existe[0]->precio_actualizado;
                                $rep->oferta = 1;
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->oferta = 0;
                            }
                            
                            $rep->cantidad = $repuesto->cantidad;
                            $rep->numero_carrito = $repuesto->numero_carrito;
                            
                            
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if(count($existe) > 0){
                                $rep->precio_venta = $existe[0]->precio_actualizado;
                                $rep->oferta = 1;
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->oferta = 0;
                            }
                            $rep->cantidad = $repuesto->cantidad;
                            $rep->numero_carrito = $repuesto->numero_carrito;

                            array_push($repuestos_,$rep);
                        }
                    }
                    
            }
                                
                        //Devolvemos el token
                    return response()->json([
                        'repuestos' => $repuestos_
                    ]);
            
        }else{
            
            return response()->json([
                'repuestos' => []
            ]);
        }
        
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    
   }

   public function eliminar_item_carrito($idrep,$carrito_numero){
    
    try {
        $usuario_id = Auth::user()->id;
        $carrito_virtual = carrito_virtual_detalle::where('repuesto_id',$idrep)->where('carrito_numero',$carrito_numero)->first();
        
        $carrito_virtual->delete();
        $items = $this->revisar_carrito_virtual($usuario_id);
        return $items;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    
   }

   public function damemodelos_pormarca($idmarca)
    {
        $marca = marcavehiculo::where('idmarcavehiculo', $idmarca)->first();
        //usando auth en el grupo de rutas. ver web.php
        $modelos = modelovehiculo::where('activo', '=', 1)
            ->where('marcavehiculos_idmarcavehiculo', $idmarca)
            ->orderBy('modelonombre')
            ->get();

        //Creamos una nueva variable que contendrá los modelos que solo tengan repuestos
        $nuevos_modelos = [];
        foreach($modelos as $m){
            $c = $this->damemodelo_pormarca($m->id);
            $repuestos = $c[0];
            //Si la cantidad de repuestos es de al menos 1 
            if(count($repuestos) > 0){
                //Guardamos el nuevo modelo con la cantidad de repuestos
                $m->cantidad_repuestos = count($repuestos);
                //Guardamos el nuevo modelo confirmando que hay al menos 1 repuesto
                //$m->cantidad_repuestos = 1;
                //Guardamos el modelo en el nuevo arreglo
                array_push($nuevos_modelos,$m);
            }
            

        }

        
        
        return [$nuevos_modelos,$marca];
    }

    public function damemodelo_pormarca($idmodelo){
        try {
            $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.descripcion')
                ->get();
                $repuestos_= [];
                $marcas_repuestos = [];
                $familias_repuestos = [];
                foreach($repuestos as $repuesto){
                    
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);

                        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                               //Revisamos si viene en oferta para respetar el precio oferta
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }elseif($descuento){
                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                                array_push($familias_repuestos, $repuesto->nombrefamilia);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                //Revisamos si viene en oferta para respetar el precio oferta
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }elseif($descuento){
                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                                array_push($familias_repuestos, $repuesto->nombrefamilia);
                            }
                        }
                        
                    }
                
                return [$repuestos_,$modelo,array_values(array_unique($marcas_repuestos)),array_values(array_unique($familias_repuestos))];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function damesimilares($id_repuesto)
    {
        $s=similar::select('marcavehiculos.marcanombre','marcavehiculos.urlfoto','modelovehiculos.modelonombre','modelovehiculos.zofri','similares.id','similares.anios_vehiculo','marcavehiculos.idmarcavehiculo','modelovehiculos.id as id_modelo')
        ->where('similares.id_repuestos',$id_repuesto)
        ->where('similares.activo',1)
        ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
        ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
        ->orderBy('marcavehiculos.marcanombre','ASC')
        ->get();
        return $s;
    }

    public function ordenar($id){
        // 1: Menor a Mayor 2: Mayor a Menor
       try {
        if($id == 1){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
        $repuestos_= [];
        foreach($repuestos as $repuesto){

                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                         $rep->marcarepuesto = $repuesto->marcarepuesto;
                         $rep->nombrefamilia = $repuesto->nombrefamilia;
                         $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                         
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
            return $repuestos_;
        
        }else if($id == 2){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->orderBy('repuestos.precio_venta','desc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }else if($id == 3){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->orderBy('repuestos.descripcion','asc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }else{
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->orderBy('repuestos.descripcion','desc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }elseif($descuento){
                                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }
       } catch (\Exception $e) {
            return $e->getMessage();
       }
        
    }

    public function ordenar_con_familia($id,$familia){
        // 1: Menor a Mayor 
        // 2: Mayor a Menor
       try {
        $familia_completa = familia::where('nombrefamilia',$familia)->first();
        if($id == 1){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);

                //Buscamos si el repuesto es de una familia que tenga descuentos
                $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                         $rep->marcarepuesto = $repuesto->marcarepuesto;
                         $rep->nombrefamilia = $repuesto->nombrefamilia;
                         $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                         
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
            return $repuestos_;
        
        }else if($id == 2){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->orderBy('repuestos.precio_venta','desc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }else if($id == 3){
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->orderBy('repuestos.descripcion','asc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }else{
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->orderBy('repuestos.descripcion','desc')
                                ->get();
            $repuestos_= [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                                            $rep = new repuesto_catalogo;
                                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                        }
                }
            }
                                            
                                    
            return $repuestos_;
        }
       } catch (\Exception $e) {
            return $e->getMessage();
       }
        
    }

    public function ordenar_modelo($id,$idmodelo){
        try {
            $aplicaciones = similar::select('id_repuestos')
                                        ->where('id_modelo_vehiculo', $idmodelo)
                                        ->get()
                                        ->toArray();

            if($id == 1){
                $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_familia','<>',312)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta', 'asc')
                    ->get();
                $repuestos_= [];
               
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->get();
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if($repuesto->oferta == 1){
                                $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                $rep->precio_venta = $v->precio_actualizado;
                            }elseif($descuento){
                                $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                            }
                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                             $rep->marcarepuesto = $repuesto->marcarepuesto;
                             $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if($repuesto->oferta == 1){
                                $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                $rep->precio_venta = $v->precio_actualizado;
                            }elseif($descuento){
                                $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                            }
                             $rep->nombrefamilia = $repuesto->nombrefamilia;
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                        }
                    }
                    
                }
                    
                return $repuestos_;
            
            }else{
                $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_familia','<>',312)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta', 'desc')
                    ->get();
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);

                        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->get();

                        if($similares->count() > 0 && $dias <= 60){
                                                $rep = new repuesto_catalogo;
                                                $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                                                    $rep->id = $repuesto->id;
                                                    $rep->descripcion = $repuesto->descripcion;
                                                    $rep->urlfoto = $foto->urlfoto;
                                                    if($repuesto->oferta == 1){
                                                        $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                        $rep->precio_venta = $v->precio_actualizado;
                                                    }elseif($descuento){
                                                        $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                    }else{
                                                        $rep->precio_venta = $repuesto->precio_venta;
                                                    }
                                                    
                                                    $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                    $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                    $rep->stock_actual = $repuesto->stock_actual;
                                                    array_push($repuestos_,$rep);
                        }else{
                                                    $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                    $rep->id = $repuesto->id;
                                                    $rep->descripcion = $repuesto->descripcion;
                                                    $rep->urlfoto = $foto->urlfoto;
                                                    if($repuesto->oferta == 1){
                                                        $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                        $rep->precio_venta = $v->precio_actualizado;
                                                    }elseif($descuento){
                                                        $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                                    }else{
                                                        $rep->precio_venta = $repuesto->precio_venta;
                                                    }
                                                    $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                    $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                    $rep->stock_actual = $repuesto->stock_actual;
                                                    array_push($repuestos_,$rep);
                            }
                    }
                }
                                                
                                        
                return $repuestos_;
            }
           } catch (\Exception $e) {
                return $e->getMessage();
           }
    }

    public function ordenar_modelo_con_familia($id,$idmodelo,$familia){
        try {
            $familia_completa = familia::where('nombrefamilia',$familia)->first();
            $aplicaciones = similar::select('id_repuestos')
                                        ->where('id_modelo_vehiculo', $idmodelo)
                                        ->get()
                                        ->toArray();
            if($id == 1){
                $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_familia','<>',312)
                    ->where('repuestos.id_familia',$familia_completa->id)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta', 'asc')
                    ->get();
                $repuestos_= [];
               
            foreach($repuestos as $repuesto){
                //Revisamos si viene en oferta para respetar el precio oferta
                if($repuesto->oferta == 1){
                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                    $repuesto->precio_venta = $v->precio_actualizado;
                }

                $descuento = descuento::where('activo',1)->where('id_familia', $repuesto->id_familia)->where('id_local','<>',1)->first();
                if($descuento) $repuesto->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');

                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                             $rep->marcarepuesto = $repuesto->marcarepuesto;
                             $rep->stock_actual = $repuesto->stock_actual;
                             $rep->nombrefamilia = $repuesto->nombrefamilia;
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                             
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->stock_actual = $repuesto->stock_actual;
                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                            array_push($repuestos_,$rep);
                        }
                    }
                    
                }
                    
                return $repuestos_;
            
            }else{
                $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_familia','<>',312)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_familia',$familia_completa->id)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta', 'desc')
                    ->get();
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Revisamos si viene en oferta para respetar el precio oferta
                    if($repuesto->oferta == 1){
                        $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                        $repuesto->precio_venta = $v->precio_actualizado;
                    }
                    $descuento = descuento::where('activo',1)->where('id_familia', $repuesto->id_familia)->where('id_local','<>',1)->first();
                    if($descuento) $repuesto->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){
                                                $rep = new repuesto_catalogo;
                                                $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                                                    $rep->id = $repuesto->id;
                                                    $rep->descripcion = $repuesto->descripcion;
                                                    $rep->urlfoto = $foto->urlfoto;
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                    $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                    $rep->stock_actual = $repuesto->stock_actual;
                                                    $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                    array_push($repuestos_,$rep);
                        }else{
                                                    $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                    $rep->id = $repuesto->id;
                                                    $rep->descripcion = $repuesto->descripcion;
                                                    $rep->urlfoto = $foto->urlfoto;
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                    $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                    $rep->stock_actual = $repuesto->stock_actual;
                                                    $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                    array_push($repuestos_,$rep);
                            }
                    }
                }
                                                
                                        
                return $repuestos_;
            }
           } catch (\Exception $e) {
                return $e->getMessage();
           }
    }

    public function buscador($tag){
        
        return $tag;
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.descripcion','like','%'.$tag.'%')
                                ->orderBy('repuestos.descripcion','asc')
                                ->get();

        $repuestos_= [];

        foreach($repuestos as $repuesto){
            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);
            $similares = $this->damesimilares($repuesto->id);
            if($similares->count() > 0 && $dias <= 60){
                                    $rep = new repuesto_catalogo;
                                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
            if(isset($foto)){
                                        $rep->id = $repuesto->id;
                                        $rep->descripcion = $repuesto->descripcion;
                                        $rep->urlfoto = $foto->urlfoto;
                                        $rep->precio_venta = $repuesto->precio_venta;
                                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                                        $rep->stock_actual = $repuesto->stock_actual;
                                        array_push($repuestos_,$rep);
            }else{
                                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                        $rep->id = $repuesto->id;
                                        $rep->descripcion = $repuesto->descripcion;
                                        $rep->urlfoto = $foto->urlfoto;
                                        $rep->precio_venta = $repuesto->precio_venta;
                                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                                        $rep->stock_actual = $repuesto->stock_actual;
                                        array_push($repuestos_,$rep);
                }
        }
    }

    return $repuestos_;
    }

    public function buscador_completo($dato){
        
        $op = substr($dato, 0, 1);
        $desc = $dato;
       
        $de = array(" de ", " DE ", " dE ", " De ");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("  "," ",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=$descripcion;
        $descripcion_sin_guiones= str_replace("-","",$descripcion);
        $buscado_original=trim($descripcion_original);
        
        $buscado_sin_guiones=$descripcion_sin_guiones;
        $terminos=explode(" ",$descripcion);

      
        $repuestos_=[];
        $marcas_repuestos = [];
        $repuestos=$this->buscar_en_descrip($buscado_original,0);
       
        foreach($repuestos as $repuesto){
            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);
            $similares = $this->damesimilares($repuesto->id);
            if($similares->count() > 0 && $dias <= 60){
                                    $rep = new repuesto_catalogo;
                                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
            if(isset($foto)){
                $rep->id = $repuesto->id;
                $rep->descripcion = $repuesto->descripcion;
                $rep->urlfoto = $foto->urlfoto;
                if($repuesto->oferta == 1){
                    //Se busca el valor en oferta del repuesto
                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                    $rep->precio_venta = $v->precio_actualizado;
                }else{
                    $d = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
                    if($d){
                        $rep->precio_venta = $repuesto->precio_venta - (($d->porcentaje/100)) * $repuesto->precio_venta;
                    }else{
                        $rep->precio_venta = $repuesto->precio_venta;
                    }
                                            
                }
                                        
                $rep->marcarepuesto = $repuesto->marcarepuesto;
                $rep->nombrefamilia = $repuesto->nombrefamilia;
                $rep->medidas = $repuesto->medidas;
                $rep->stock_actual = $repuesto->stock_actual;
                array_push($repuestos_,$rep);
                array_push($marcas_repuestos, $repuesto->marcarepuesto);
            }else{
                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                $rep->id = $repuesto->id;
                $rep->descripcion = $repuesto->descripcion;
                $rep->urlfoto = $foto->urlfoto;
                if($repuesto->oferta == 1){
                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                    $rep->precio_venta = $v->precio_actualizado;
                }else{
                    $d = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
                    if($d){
                                                $rep->precio_venta = $repuesto->precio_venta - (($d->porcentaje/100)) * $repuesto->precio_venta;
                                            }else{
                                                $rep->precio_venta = $repuesto->precio_venta;
                                            }
                                        }
                                        $rep->precio_venta = $repuesto->precio_venta;
                                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                                        $rep->medidas = $repuesto->medidas;
                                        $rep->stock_actual = $repuesto->stock_actual;
                                        array_push($repuestos_,$rep);
                                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }
        return [$repuestos_, array_values(array_unique($marcas_repuestos))];
    
       
    }

    public function buscador_completo_demo($dato){
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De ");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("  "," ",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=$descripcion;
        $descripcion_sin_guiones= str_replace("-","",$descripcion);
        $buscado_original=trim($descripcion_original);
        $buscado_sin_guiones=$descripcion_sin_guiones;
        $terminos=explode(" ",$descripcion);

       
        $repuestos_=[];
        $marcas_repuestos = [];
        $repuestos=$this->buscar_en_descrip($buscado_original,0);
       
        foreach($repuestos as $repuesto){
            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);
            $similares = $this->damesimilares($repuesto->id);
            if($similares->count() > 0 && $dias <= 60){
                                    $rep = new repuesto_catalogo;
                                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
            if(isset($foto)){
                                        $rep->id = $repuesto->id;
                                        $rep->descripcion = $repuesto->descripcion;
                                        $rep->urlfoto = $foto->urlfoto;
                                        if($repuesto->oferta == 1){
                                            //Se busca el valor en oferta del repuesto
                                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                            $rep->precio_venta = $v->precio_actualizado;
                                        }else{
                                            $d = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
                                            if($d){
                                                $rep->precio_venta = $repuesto->precio_venta - (($d->porcentaje/100)) * $repuesto->precio_venta;
                                            }else{
                                                $rep->precio_venta = $repuesto->precio_venta;
                                            }
                                            
                                        }
                                        
                                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                                        $rep->stock_actual = $repuesto->stock_actual;
                                        array_push($repuestos_,$rep);
                                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
            }else{
                                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                        $rep->id = $repuesto->id;
                                        $rep->descripcion = $repuesto->descripcion;
                                        $rep->urlfoto = $foto->urlfoto;
                                        if($repuesto->oferta == 1){
                                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                            $rep->precio_venta = $v->precio_actualizado;
                                        }else{
                                            $d = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
                                            if($d){
                                                $rep->precio_venta = $repuesto->precio_venta - (($d->porcentaje/100)) * $repuesto->precio_venta;
                                            }else{
                                                $rep->precio_venta = $repuesto->precio_venta;
                                            }
                                        }
                                        $rep->precio_venta = $repuesto->precio_venta;
                                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                                        $rep->stock_actual = $repuesto->stock_actual;
                                        array_push($repuestos_,$rep);
                                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }
        return [$repuestos_, array_values(array_unique($marcas_repuestos))];
    
       
    }

    public function desactivar_carrito_virtual($numero_carrito){
        try {
            $carrito_virtual = carrito_virtual::where('numero_carrito', $numero_carrito)->where('activo',1)->first();
            $carrito_virtual->activo = 0;
            $carrito_virtual->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }


    public function buscar_en_descrip($buscado,$orden){
      
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $buscado=trim($buscado);
        $terminos=explode(" ",trim($buscado));
        $encontrados=Collect();

        //PRIMERO Determinar que familia de repuestos esta buscando...
        $n_familia="";
        $n_terminos_encontrados=[];
        $decide_fam=[];
        $id_fam=0;
       
        for($i=0;$i<count($terminos);$i++){


            $familys=familia::select('id','nombrefamilia')
                            ->where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->get();

            $n_hay=$familys->count();
            if($n_hay>0){
                array_push($n_terminos_encontrados,$terminos[$i]);
            }

            //qué id familia tiene más frecuencia
            foreach($familys as $famy)
            {
                if(!isset($decide_fam[$famy->id])) $decide_fam[$famy->id]=0;
                $decide_fam[$famy->id]++;
            }
        }

        $terer="";
        for($j=0;$j<count($n_terminos_encontrados);$j++) $terer.=$n_terminos_encontrados[$j]." ";
        $max_cant=0;
        $max_id_fam=0;
        foreach($decide_fam as $id_fam=>$cant){
            if($cant>$max_cant){
                $max_cant=$cant;
                $max_id_fam=$id_fam;
            }
        }
        $ids_max_fam=[];
        foreach($decide_fam as $id_fam=>$cant){
            if($cant==$max_cant) array_push($ids_max_fam,$id_fam);
        }

        $terminos2=[];

        //quitar los términos anteriores encontrados para familias
        $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
        foreach($terminos_temp as $j) array_push($terminos2,$j);


        if(count($terminos2)==0){ //no encontró repuesto para familias, y copia los terminos
            $terminos2=$terminos;
        }

        if(!isset($familias_encontradas)) $familias_encontradas=[];

        //buscar marca de vehículo
        $n_marca_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos2);$i++){
            if(strlen($terminos2[$i])>0){
                $n_hay=marcavehiculo::where('marcanombre','LIKE','%'.$terminos2[$i].'%')
                                    ->where('marcavehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos2[$i]);
                    $n_marca_vehiculo.=$terminos2[$i]." ";
                }
            }
        }

        $n_marca_vehiculo_buscado=trim($n_marca_vehiculo);
        $terminos3=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE MARCA VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="(marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ?) AND ";
            }
            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $al_inicio=$n_terminos_encontrados[$i]." %";
                $en_medio="% ".$n_terminos_encontrados[$i]." %";
                $al_final="% ".$n_terminos_encontrados[$i];
                $unico=$n_terminos_encontrados[$i];
                array_push($param,$al_inicio);
                array_push($param,$en_medio);
                array_push($param,$al_final);
                array_push($param,$unico);
            }
            array_push($param,1);
            $marca_vehiculo_encontrados=marcavehiculo::select('marcavehiculos.idmarcavehiculo')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos2,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos3,$j);
        }
        if(count($terminos3)==0){
            $terminos3=$terminos2;
        }

        if(!isset($marca_vehiculo_encontrados)) $marca_vehiculo_encontrados=[];

        //buscar modelo de vehículo
        //En terminos3 puede llegar el cilindraje del motor en formato 2.5 por ejemplo
        //y debe convertirse en 2500
        $cil_punto=[0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5,2.6,2.7,2.8,2.9,3.0,3.1,3.2,3.3,3.4,3.5,3.6,3.7,3.8,3.9,4.0,4.1,4.2,4.3,4.4,4.5,4.6,4.7,4.8,4.9,5.0,5.1,5.2,5.3,5.4,5.5,5.6,5.7,5.8,5.9,6.0,6.1,6.2,6.3,6.4,6.5,6.6,6.7,6.8,6.9,7.0];
        //$cil_coma=['0,6','0,7','0,8','0,9','1,1','1,1','1,2','1,3','1,4','1,5','1,6','1,7','1,8','1,9','2,0','2,1','2,2','2,3','2,4','2,5','2,6','2,7','2,8','2,9','3,0','3,1','3,2','3,3','3,4','3,5','3,6','3,7','3,8','3,9','4,0','4,1','4,2','4,3','4,4','4,5','4,6','4,7','4,8','4,9','5,0','5,1','5,2','5,3','5,4','5,5','5,6','5,7','5,8','5,9','6,0','6,1','6,2','6,3','6,4','6,5','6,6','6,7','6,8','6,9','7,0'];
        $cil_normal=[600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000,4100,4200,4300,4400,4500,4600,4700,4800,4900,5000,5100,5200,5300,5400,5500,5600,5700,5800,5900,6000,6100,6200,6300,6400,6500,6600,6700,6800,6900,7000];

        $n_modelo_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos3);$i++){
            if(strlen($terminos3[$i])>0){
                if(strpos($terminos3[$i],",")>0){
                    $terminos3[$i]=str_replace(',','.',$terminos3[$i]);
                }
                if(is_numeric($terminos3[$i])){
                    $valnum=$terminos3[$i];
                    settype($valnum,"float");
                    $i_punto=array_search($valnum,$cil_punto,true); //si no encuentra devuelve false, caso contrario el indice
                    if($i_punto===false){

                    }else{
                        $terminos3[$i]=$cil_normal[$i_punto];
                    }
                }


                $n_hay=modelovehiculo::where('modelonombre','LIKE','%'.$terminos3[$i].'%')
                                    ->where('modelovehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos3[$i]);
                    $n_modelo_vehiculo.=$terminos3[$i]." ";
                }
            }
        }

        $n_modelo_vehiculo_buscado=trim($n_modelo_vehiculo);
        $terminos4=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE modelo VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado="%".$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $modelo_vehiculo_encontrados=modelovehiculo::select('modelovehiculos.id')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos3,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos4,$j);

        }
        if(count($terminos4)==0){
            $terminos3=$terminos3;
        }

        if(!isset($modelo_vehiculo_encontrados)) $modelo_vehiculo_encontrados=[];
        if($id_fam>0){
            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)==0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)==0){
                // Menor a Mayor
                if($orden == 1){
                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }else{
                if($orden == 1){
                    //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }

        }

        return $encontrados;
    }

    public function buscar_en_descrip_marca($buscado,$orden,$marca){
      //$marca_completa = marcarepuesto::where('marcarepuesto',$marca)->first();
        //repuestos.descripcion, repuesto->id

        $marca_completa = marcarepuesto::where('marcarepuesto',$marca)->first();

        $resp['resultado']=false;
        $buscado=trim($buscado);
        $terminos=explode(" ",trim($buscado));
        $encontrados=Collect();

        //PRIMERO Determinar que familia de repuestos esta buscando...
        $n_familia="";
        $n_terminos_encontrados=[];
        $decide_fam=[];
        $id_fam=0;
       
        for($i=0;$i<count($terminos);$i++){


            $familys=familia::select('id','nombrefamilia')
                            ->where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->get();

            $n_hay=$familys->count();
            if($n_hay>0){
                array_push($n_terminos_encontrados,$terminos[$i]);
            }

            //qué id familia tiene más frecuencia
            foreach($familys as $famy)
            {
                if(!isset($decide_fam[$famy->id])) $decide_fam[$famy->id]=0;
                $decide_fam[$famy->id]++;
            }
        }

        $terer="";
        for($j=0;$j<count($n_terminos_encontrados);$j++) $terer.=$n_terminos_encontrados[$j]." ";
        $max_cant=0;
        $max_id_fam=0;
        foreach($decide_fam as $id_fam=>$cant){
            if($cant>$max_cant){
                $max_cant=$cant;
                $max_id_fam=$id_fam;
            }
        }
        $ids_max_fam=[];
        foreach($decide_fam as $id_fam=>$cant){
            if($cant==$max_cant) array_push($ids_max_fam,$id_fam);
        }

        $terminos2=[];

        //quitar los términos anteriores encontrados para familias
        $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
        foreach($terminos_temp as $j) array_push($terminos2,$j);


        if(count($terminos2)==0){ //no encontró repuesto para familias, y copia los terminos
            $terminos2=$terminos;
        }

        if(!isset($familias_encontradas)) $familias_encontradas=[];

        //buscar marca de vehículo
        $n_marca_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos2);$i++){
            if(strlen($terminos2[$i])>0){
                $n_hay=marcavehiculo::where('marcanombre','LIKE','%'.$terminos2[$i].'%')
                                    ->where('marcavehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos2[$i]);
                    $n_marca_vehiculo.=$terminos2[$i]." ";
                }
            }
        }

        $n_marca_vehiculo_buscado=trim($n_marca_vehiculo);
        $terminos3=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE MARCA VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="(marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ?) AND ";
            }
            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $al_inicio=$n_terminos_encontrados[$i]." %";
                $en_medio="% ".$n_terminos_encontrados[$i]." %";
                $al_final="% ".$n_terminos_encontrados[$i];
                $unico=$n_terminos_encontrados[$i];
                array_push($param,$al_inicio);
                array_push($param,$en_medio);
                array_push($param,$al_final);
                array_push($param,$unico);
            }
            array_push($param,1);
            $marca_vehiculo_encontrados=marcavehiculo::select('marcavehiculos.idmarcavehiculo')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos2,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos3,$j);
        }
        if(count($terminos3)==0){
            $terminos3=$terminos2;
        }

        if(!isset($marca_vehiculo_encontrados)) $marca_vehiculo_encontrados=[];

        //buscar modelo de vehículo
        //En terminos3 puede llegar el cilindraje del motor en formato 2.5 por ejemplo
        //y debe convertirse en 2500
        $cil_punto=[0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5,2.6,2.7,2.8,2.9,3.0,3.1,3.2,3.3,3.4,3.5,3.6,3.7,3.8,3.9,4.0,4.1,4.2,4.3,4.4,4.5,4.6,4.7,4.8,4.9,5.0,5.1,5.2,5.3,5.4,5.5,5.6,5.7,5.8,5.9,6.0,6.1,6.2,6.3,6.4,6.5,6.6,6.7,6.8,6.9,7.0];
        //$cil_coma=['0,6','0,7','0,8','0,9','1,1','1,1','1,2','1,3','1,4','1,5','1,6','1,7','1,8','1,9','2,0','2,1','2,2','2,3','2,4','2,5','2,6','2,7','2,8','2,9','3,0','3,1','3,2','3,3','3,4','3,5','3,6','3,7','3,8','3,9','4,0','4,1','4,2','4,3','4,4','4,5','4,6','4,7','4,8','4,9','5,0','5,1','5,2','5,3','5,4','5,5','5,6','5,7','5,8','5,9','6,0','6,1','6,2','6,3','6,4','6,5','6,6','6,7','6,8','6,9','7,0'];
        $cil_normal=[600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000,4100,4200,4300,4400,4500,4600,4700,4800,4900,5000,5100,5200,5300,5400,5500,5600,5700,5800,5900,6000,6100,6200,6300,6400,6500,6600,6700,6800,6900,7000];

        $n_modelo_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos3);$i++){
            if(strlen($terminos3[$i])>0){
                if(strpos($terminos3[$i],",")>0){
                    $terminos3[$i]=str_replace(',','.',$terminos3[$i]);
                }
                if(is_numeric($terminos3[$i])){
                    $valnum=$terminos3[$i];
                    settype($valnum,"float");
                    $i_punto=array_search($valnum,$cil_punto,true); //si no encuentra devuelve false, caso contrario el indice
                    if($i_punto===false){

                    }else{
                        $terminos3[$i]=$cil_normal[$i_punto];
                    }
                }


                $n_hay=modelovehiculo::where('modelonombre','LIKE','%'.$terminos3[$i].'%')
                                    ->where('modelovehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos3[$i]);
                    $n_modelo_vehiculo.=$terminos3[$i]." ";
                }
            }
        }

        $n_modelo_vehiculo_buscado=trim($n_modelo_vehiculo);
        $terminos4=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE modelo VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado="%".$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $modelo_vehiculo_encontrados=modelovehiculo::select('modelovehiculos.id')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos3,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos4,$j);

        }
        if(count($terminos4)==0){
            $terminos3=$terminos3;
        }

        if(!isset($modelo_vehiculo_encontrados)) $modelo_vehiculo_encontrados=[];
        if($id_fam>0){
            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)==0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)==0){
                // Menor a Mayor
                if($orden == 1){
                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }else{
                if($orden == 1){
                    //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->where('repuestos.id_marca_repuesto',$marca_completa->id)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }

        }

        return $encontrados;
    }

    public function detalle_venta($numero_carrito){
        try {
            $carrito_virtual = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.descripcion','repuestos.id','repuestos.precio_venta')
                                                ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')            
                                                ->where('carrito_virtual_detalle.carrito_numero',$numero_carrito)
                                                ->get();
            $repuestos_= [];
            foreach($carrito_virtual as $repuesto){
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0){
                        $rep = new repuesto_carrito;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        $marca_repuesto = repuesto::select('marcarepuestos.marcarepuesto')
                                                    ->where('repuestos.id',$repuesto->id)
                                                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                                    ->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                            $rep->cantidad = $repuesto->cantidad;
                            $rep->numero_carrito = $repuesto->numero_carrito;
                            $rep->marcarepuesto = $marca_repuesto->marcarepuesto;
                        
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                            $rep->cantidad = $repuesto->cantidad;
                            $rep->numero_carrito = $repuesto->numero_carrito;
                            $rep->marcarepuesto = $marca_repuesto->marcarepuesto;
                            array_push($repuestos_,$rep);
                        }
                    }
                    
            }
                                
                        
            return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function retiro_tienda_guardar(Request $req){
        
        try {
            $usuario = Auth::user();
            $retiro_tienda = new retiro_tienda;
            $retiro_tienda->numero_carrito = intval($req->numero_carrito);
            $retiro_tienda->nombre = $usuario->name;

            $retiro_tienda->save();

           
                //Devolvemos el token
                return response()->json([
                    'message' => 'OK'
                ]);
        } catch (\Exception $e) {
            //Devolvemos el token
            return response()->json([
                'message' => $e->getMessage
            ]);
        }
    }

    public function despacho_domicilio_guardar(Request $req){
        
        try {
            $region = $req->region;
            $comuna = $req->comuna;
            $numero_carrito = $req->numero_carrito;
            $direccion_despacho = $req->direccion_despacho;
            $telefono = $req->telefono_despacho;
            $persona = $req->persona_quien_retira;
            $referencia = $req->referencia_despacho;

            $despacho_domicilio = new despacho_domicilio;
            
            $despacho_domicilio->region = $region;
            $despacho_domicilio->comuna = $comuna;
            $despacho_domicilio->numero_carrito = $numero_carrito;
            $despacho_domicilio->direccion_despacho = $direccion_despacho;
            $despacho_domicilio->telefono_despacho = $telefono;
            $despacho_domicilio->persona = $persona;
            $despacho_domicilio->referencia = $referencia;
            
            $despacho_domicilio->save();

            //Devolvemos el token
            return response()->json([
                'message' => 'OK'
            ]);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function iniciar_pago(Request $request){
        $usuario_id = Auth::user()->id;
        
       try {
            $msg = $this->generar_xml_online($request);
            //Ojo con esto, se guarda la compra sin saber el resultado del pago
            $nueva_compra = new compra_transbank;
            $nueva_compra->session_id = rand(1,1000);
            $nueva_compra->total = intval($request->total);
            $nueva_compra->status = 1;
            $nueva_compra->usuario_id = $usuario_id;
            $nueva_compra->numero_carrito = $request->numero_carrito;
            $nueva_compra->token_ws = '---';
            $nueva_compra->save();

            return $msg;
       } catch (\Exception $e) {
            return $e->getMessage();
       }
       
        
    }

    public function confirmar_pago($pago_id){
        $compra = compra_transbank::where('numero_carrito',$pago_id)->first();
        $compra->status = 2;
        $compra->save();
        return 'OK';
    }
    
    public function dame_solicitudes_compra(){
        try {
            $usuario_id = Auth::user()->id;
            $solicitudes = carrito_virtual::where('usuario_id',$usuario_id)->get();
            $compras = compra_transbank::where('usuario_id',$usuario_id)->get();
    
            return [$solicitudes, $compras];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_detalle_retiro($numero_carrito){
        $entrega = retiro_tienda::where('numero_carrito',$numero_carrito)->first();
        $despacho = despacho_domicilio::where('numero_carrito',$numero_carrito)->first();

        $carrito = carrito_virtual::where('numero_carrito',$numero_carrito)->first();

        if($entrega){
            $dato = $entrega;
        }

        if($despacho){
            $dato = $despacho;
        }

        return $dato;
    }

    public function ordenar_buscador($id, $tag){
        // 1: Menor a Mayor 2: Mayor a Menor
        try {

        $repuestos = $this->buscar_en_descrip($tag, $id);
       
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
            return $repuestos_;
        
        
    } catch (\Exception $e) {
            return $e->getMessage();
    }
    }

    public function ordenar_buscador_con_marca($id, $tag,$marca){
        // 1: Menor a Mayor 2: Mayor a Menor
        try {
               
                $repuestos = $this->buscar_en_descrip_marca($tag, $id,$marca);
               
                $repuestos_= [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');
        
                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
        
                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
        
                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                             $rep->marcarepuesto = $repuesto->marcarepuesto;
                             $rep->nombrefamilia = $repuesto->nombrefamilia;
                             $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                             
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                        }
                    }
                    
                }
                    
                return $repuestos_;
            
            
                } catch (\Exception $e) {
                        return $e->getMessage();
                }
            }
public function buscar_por_medida($medida){
   
    try {
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                            ->join('familias','repuestos.id_familia','familias.id')
                            ->where('repuestos.stock_actual','>',0)
                            ->where('repuestos.id_marca_repuesto','<>',190)
                            ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                            ->where('repuestos.id_familia','<>',312)
                            ->get();

        $repuestos_= [];
        $marcas_repuestos = [];
        $familias_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                if(isset($foto)){
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                                            array_push($familias_repuestos, $repuesto->nombrefamilia);
                }else{
                                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                                            array_push($familias_repuestos, $repuesto->nombrefamilia);
                    }
            }
        }

        return [$repuestos_, array_values(array_unique($marcas_repuestos)),array_values(array_unique($familias_repuestos))];
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    
}

    public function guardar_cliente_web(Request $r)
    {

        try{
            if($r->modifika==0)
            {
                //Verificar que el rut no exista
                $hay=cliente_modelo::where('rut','LIKE',$r->rut)->first();
                if(!is_null($hay))
                    return $hay->id;
                $c=new cliente_modelo;
            }else{
                $c=cliente_modelo::find($r->id_cliente);
            }
            $c->rut=$r->rut;
            $c->tipo_cliente=$r->tipo_cliente;


            if($r->tipo_cliente==0){ //cliente natural
                $nnn=strip_tags($r->nombres);
                $aaa=strip_tags($r->apellidos);
                $eee="---";
            }

            if($r->tipo_cliente==1){ //cliente empresa
                $nnn="---";
                $aaa="---";
                $eee=strip_tags($r->empresa);
            }
           
            $ggg=strip_tags($r->giro);
            $ddd=strip_tags($r->direccion);
            $ddco=strip_tags($r->direccion_comuna);
            $ddci=strip_tags($r->direccion_ciudad);
            $te1=strip_tags($r->telf1);
            $ema=strip_tags($r->email);
            $ccc=strip_tags($r->contacto);


            $c->nombres=strlen($nnn)>0 ? $nnn : "---";
            $c->apellidos=strlen($aaa)>0 ? $aaa : "---";
            $c->empresa=strlen($eee)>0 ? $eee : "---";

            $c->razon_social=strlen($eee)>0 ? $eee : "---";
            $c->giro=strlen($ggg)>0 ? $ggg : "---";
            $c->direccion=strlen($ddd)>0 ? $ddd : "---";
            
            $c->direccion_comuna=strlen($ddco)>0 ? $ddco : "---";
            $c->direccion_ciudad=strlen($ddci)>0 ? $ddci : "---";
            $c->telf1=strlen($te1)>0 ? $te1 : "---";
            $c->telf2="---";
            $c->email=strlen($ema)>0 ? $ema : "---";
            $c->contacto=strlen($ccc)>0 ? $ccc : "---";
            $c->telfc="---";
            
            $c->credito=1;
            $c->limite=1500000;
            $c->dias=60;
            $c->descuento=1;
            $c->tipo_descuento=1;
            $c->porcentaje=5;
            // el campo veces_buscado tiene por defecto = 0;
            $c->activo=1;
            //A nombre de Mauricio Eguren
            $c->usuarios_id=17;
           
            $c->save();

            if($r->modifika==1)
            {
                $id_cliente=$c->id;
            }else{
                $id_cliente=$c->id;
            }

            return $id_cliente;

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function generar_xml_online(Request $r)
    {
        
        $usuario_id = Auth::user()->id;
        //Recibimos el descuento, si viene vacio será 0
        $descuento = intval($r->dcto);

        $referencias=[];
        
        
        $Datos['referencias']=$referencias;

        if($r->fmapago=='contado'){
            $Datos['FmaPago']=1;
        }
        if($r->fmapago=='credito' || $r->fmapago=='delivery'){
            $Datos['FmaPago']=2;
        }

        if($r->docu=='cotizacion'){
            //FALTA: Revisar código de guardar_venta para empezar.

            return "algo";
        }

       
       
        //PREPARAMOS LOS DATOS A ENTREGAR A CLSSII

        //Correlativo
        $nume=$this->dame_correlativo($r->docu);
       
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $docu=strtoupper($r->docu);
            $estado=['estado'=>'ERROR_CAF','mensaje'=>$nume.": No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++; //siguiente correlativo
            $Datos['folio_dte']=$nume;
            if($r->docu=='boleta') $Datos['tipo_dte']='39';
            if($r->docu=='factura') $Datos['tipo_dte']='33';
            if($r->docu=='notacredito') $Datos['tipo_dte']='61';
            if($r->docu=='notadebito') $Datos['tipo_dte']='56';
        }
    
        //Obtener cliente

        if($r->idcliente==0){ //no se eligió cliente
            $idcliente=$this->dame_cliente_sii();
        }else{
            $idcliente=$r->idcliente;
        }

        $cliente=cliente_modelo::find($idcliente);
        
      
        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);

        //$rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
        if($cliente->tipo_cliente==0){ //persona natural
            $rz=$cliente->nombres." ".$cliente->apellidos;
        }
        if($cliente->tipo_cliente==1){ //empresa
            $rz=$cliente->razon_social;
        }

        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                            'RznSocRecep'=>$rz,
                            'GiroRecep'=>$cliente->giro,
                            'DirRecep'=>$cliente->direccion,
                            'CmnaRecep'=>$cliente->direccion_comuna,
                            'CiudadRecep'=>$cliente->direccion_ciudad
                        ];
  
        //Obtener detalle del carrito
        $Detalle=[];
        
        $carrito_virtual =carrito_virtual::where('usuario_id',$usuario_id)->where('activo',1)->first();
  
        $ct = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                        ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                                        ->where('carrito_virtual_detalle.carrito_numero',$carrito_virtual->numero_carrito)
                                        ->get();
        
            foreach($ct as $i){
                //$precio_neto_item=$i->pu_neto; //round($i->pu/(1+Session::get('PARAM_IVA')),0);
                if($Datos['tipo_dte']=='39'){ //boleta
                    if($i->descuento_item>0){
                        $item=array('NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2),//intval($i->pu),
                            'DescuentoMonto'=>round($i->descuento_item,2) //intval($i->descuento_item)
                        );
                    }else{
                        $item=array('NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2)
                        );
                    }
                }else{ // factura
                    if($i->descuento_item>0){
                        $item=array('NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                    'DescuentoMonto'=>round($i->descuento_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                    );
                    }else{
                        $item=array('NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>round($i->pu,2)//$i->pu_neto //ya llega redondeado desde el carrito,
                                    );
                    }
                }

                array_push($Detalle,$item);
            }
            //Cambiamos el estado del carrito virtual a inactivo para que no se mezclen los carritos
            $carrito_virtual->activo = 0;

            $carrito_virtual->save();

        $estado = ClsSii_online::generar_xml($Receptor,$Detalle,$Datos);
       
     
        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre',$r->docu);
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente',$idcliente);
            $this->actualizar_correlativo($r->docu, $nume);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
        }

        return [json_encode($estado), json_encode($Datos)];
    }

    private function actualizar_correlativo($docu, $num)
    {
        $co = correlativo::where('documento', $docu)
            ->where('id_local', 1)
            ->first();
        $co->correlativo = $num;
        $s=$co->save();
    }

    public function enviar_sii_online(Request $r){
        $id_cliente=$r->idcliente==0 ? $this->dame_cliente_sii() : $r->idcliente; //para guardar la boleta y factura

        $dcto = intval($r->dcto);
        
        if($r->xml==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        //Esto queda constante debido a que no he podido recuperar la sesión
        $RutEnvia = str_replace(".","",'13.412.179-3');
        $RutEmisor = str_replace(".","",'76.881.221-7');
        $d=$r->xml;
        $tipo_dte=$r->tipo_dte;
        
        if($tipo_dte=='39')
        {
            $doc=base_path().'/xml/generados/boletas/'.$d;
        }
        if($tipo_dte=='33')
        {
            $doc=base_path().'/xml/generados/facturas/'.$d;
        }
        if($tipo_dte=='61')
        {
            $doc=base_path().'/xml/generados/notas_de_credito/'.$d;
        }
        if($tipo_dte=='56')
        {
            $doc=base_path().'/xml/generados/notas_de_debito/'.$d;
        }

        $tipo_docu="nada";
        $num_docu=0;
        $id_documento_pago = 0;
       //Recuperar el XML Generado para enviar
        
        try {
            $envio=file_get_contents($doc);
            
            if($tipo_dte=='39'){
                $rs=ClsSii_online::enviar_sii_boleta($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
             
                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                }else{
                    return json_encode($rs);
                }


                $b = new boleta;
                $b->num_boleta = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $b->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $b->es_credito=0;
                $b->es_delivery=0;
                $b->id_cliente = $id_cliente;
                $b->estado = $estado;
                $b->estado_sii=$estado_sii;
                $b->resultado_envio=$resultado_envio;
                $b->trackid=$TrackID;
                $b->url_xml=$d;
                $b->total = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);// round(intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal)*(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP); //incluye el iva
                $b->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);//intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
                $b->exento = 0;
                $b->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA); //round($b->neto*Session::get('PARAM_IVA'),0,PHP_ROUND_HALF_UP);
                $b->activo = 1;
                //Las ventas online se asignan a Mauricio Eguren
                $b->usuarios_id = 17;
                
                $b->save();
                //hay que sacar lo que falta, pero el tema de montos sacarlos del XML enviado

                $carrito_virtual =carrito_virtual::where('usuario_id',Auth::user()->id)->where('activo',1)->first();
  
                $c = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                        ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                                        ->where('carrito_virtual_detalle.carrito_numero',$carrito_virtual->numero_carrito)
                                        ->get();

                foreach($c as $i){
                        $bd = new boleta_detalle;
                        $bd->id_boleta = $b->id;
                        $bd->id_repuestos = $i->repuesto_id;
                        $bd->id_unidad_venta = 0;
                        $bd->id_local = 1;
                        $bd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem);//*(1+Session::get('PARAM_IVA')); //$i->pu;
                        $bd->pu_neto = round($bd->precio_venta/(1+1.19),2);
                        $bd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem);//*(1+Session::get('PARAM_IVA'));
                        $bd->subtotal = $sb;//$i->subtotal_item;
                        $bd->descuento = $i->descuento_item;
                        $bd->total = $sb-$i->descuento_item;
                        $bd->activo = 1;
                       
                        $bd->usuarios_id = 17;
                        
                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->repuesto_id);
                        $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                        //$repuesto->save();

                        // Actualizar tabla saldos considerando el local
                        // id_repuestos,id_local,saldo,activo,usuarios_id
                        $bd->save();
                }
                    
                
                
                $tipo_docu="boleta";
                $num_docu=$b->num_boleta;
                $id_documento_pago = $b->id;

            } // fin DE BOLETA

            if($tipo_dte=='33'){ //FACTURA
                
                $rs=ClsSii_online::enviar_sii($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
                
                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];
                    
                }else{
                    return json_encode($rs);
                    //$TrackID="---";
                    //$estado_sii=$rs['estado'];
                }
                $f = new factura;
                $f->num_factura = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $f->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $f->es_credito=0;
                $f->es_delivery=0;
                $f->id_cliente = $id_cliente;
                $f->estado = $estado;
                $f->estado_sii=$estado_sii;
                $f->resultado_envio=$resultado_envio;
                $f->trackid=$TrackID;
                $f->url_xml=$d;
                $f->total =intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
                $f->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
                $f->exento = 0;
                $f->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
                $f->activo = 1;
                
                $f->usuarios_id = 17;
                
                $f->save();
                $carrito_virtual =carrito_virtual::where('usuario_id',Auth::user()->id)->where('activo',1)->first();
  
                $c = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                        ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                                        ->where('carrito_virtual_detalle.carrito_numero',$carrito_virtual->numero_carrito)
                                        ->get();
                
                    foreach($c as $i){
                        $fd = new factura_detalle;
                        $fd->id_factura = $f->id;
                        $fd->id_repuestos = $i->repuesto_id;
                        $fd->id_unidad_venta = 0;
                        $fd->id_local = 1;
                        $fd->precio_venta = $xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem;//round($fd->pu_neto*(1+Session::get('PARAM_IVA')),2,PHP_ROUND_HALF_UP);
                        $fd->pu_neto = round($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem/(1+1.19),2);
                        $fd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $fd->subtotal = $fd->precio_venta*$fd->cantidad;
                        //if hay descuento en el xml?? poner, sino 0;
                        if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                            $fd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                        }else{
                            $fd->descuento=0;
                        }
    
                        $fd->total = $fd->subtotal-$fd->descuento;
                        $fd->activo = 1;
                       
                        $fd->usuarios_id = 17;
                        
                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->repuesto_id);
                        //Descontamos el stock de acuerdo al local
                        $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                        //$repuesto->save();
                        $fd->save();
                    }
                
                $tipo_docu="factura";
                $num_docu=$f->num_factura;
                $id_documento_pago = $f->id;
            } // fin de FACTURA
            
            $p = new pago;
            $p->tipo_doc = substr($r->docu, 0, 2); //factura, boleta
            $p->id_doc = $id_documento_pago; // Es el id del documento factura o boleta guardado más arriba
            $p->id_cliente = $id_cliente;
            $p->id_forma_pago = 2; // Tarjeta de crédito
            $p->referencia_pago = 1; // Transbank
            $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $p->monto = $r->monto - $dcto;
            $p->referencia = 0;
            $p->activo = 1;
                   
            $p->usuarios_id = 17;
                    
            $p->save();

            //Cambiamos el estado del carrito virtual a inactivo para que no se mezclen los carritos
            $carrito_virtual->activo = 0;

            $carrito_virtual->save();
             

        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
            return json_encode($estado);
        }
        return json_encode($rs);
    }


    private function dame_correlativo($tip_doc)
    {
        
        $num=-1;
        $id_local = 1; // es el local donde se ejecuta el terminal
        if($tip_doc=='cotizacion')
        {
            $fila = correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();
            if(!is_null($fila))
            {
                $corr=$fila->correlativo; //0
                $max_folio=$fila->hasta; //0
                if($max_folio>=($corr+1)) $num=$corr;
            }
        }else{
            $fila=correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();

            if(!is_null($fila))
            {
                $corr=$fila->correlativo;
                $max_folio=$fila->hasta;
                $el_siguiente=$corr+1;
                if($max_folio>=($el_siguiente)) $num=$corr;
                //FALTA:VERIFICAR EN LA TABLA RESPECTIVA (boletas o facturas) si hay existe ese número
                //esto debido a que A VECES en un determinado instante COINCIDE la elección del siguiente correlativo.

            }
        }
        return $num;
    }

    private function dame_cliente_sii()
    {
        $rpta=-1; //No esta definido el rut del sii
        $c0=cliente_modelo::where('rut','60803000K')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    public function ordenar_medida($value, $medida){
        if($value == 1){            
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                            ->join('familias','repuestos.id_familia','familias.id')
                            ->where('repuestos.stock_actual','>',0)
                            ->where('repuestos.id_marca_repuesto','<>',190)
                            ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                            ->where('repuestos.id_familia','<>',312)
                            ->orderBy('repuestos.precio_venta','asc')
                            ->get();

        $repuestos_= [];
        $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                if(isset($foto)){
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                }else{
                                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }

        return $repuestos_;
        }else{
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                            ->join('familias','repuestos.id_familia','familias.id')
                            ->where('repuestos.stock_actual','>',0)
                            ->where('repuestos.id_marca_repuesto','<>',190)
                            ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                            ->where('repuestos.id_familia','<>',312)
                            ->orderBy('repuestos.precio_venta','desc')
                            ->get();

        $repuestos_= [];
        $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                if(isset($foto)){
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                }else{
                                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }

        return $repuestos_;
        }
    }

    public function ordenar_medida_con_familia($value, $medida,$familia){
        $familia_completa = familia::where('nombrefamilia',$familia)->first();
        if($value == 1){            
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                            ->join('familias','repuestos.id_familia','familias.id')
                            ->where('repuestos.stock_actual','>',0)
                            ->where('repuestos.id_marca_repuesto','<>',190)
                            ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                            ->where('repuestos.id_familia','<>',312)
                            ->where('repuestos.id_familia',$familia_completa->id)
                            ->orderBy('repuestos.precio_venta','asc')
                            ->get();

        $repuestos_= [];
        $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                if(isset($foto)){
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                }else{
                                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }

        return $repuestos_;
        }else{
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                            ->join('familias','repuestos.id_familia','familias.id')
                            ->where('repuestos.stock_actual','>',0)
                            ->where('repuestos.id_marca_repuesto','<>',190)
                            ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                            ->where('repuestos.id_familia','<>',312)
                            ->where('repuestos.id_familia',$familia_completa->id)
                            ->orderBy('repuestos.precio_venta','desc')
                            ->get();

        $repuestos_= [];
        $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                if(isset($foto)){
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                }else{
                                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                            $rep->id = $repuesto->id;
                                            $rep->descripcion = $repuesto->descripcion;
                                            $rep->urlfoto = $foto->urlfoto;
                                            $rep->precio_venta = $repuesto->precio_venta;
                                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                                            $rep->stock_actual = $repuesto->stock_actual;
                                            array_push($repuestos_,$rep);
                                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
            }
        }

        return $repuestos_;
        }
    }

    public function ordenar_oem($value, $oem){
        $pos=strpos($oem,"-");
            if($pos===false)
            {
                $buscado_sin_guion=$oem;
                $buscado_con_guion=substr($oem,0,5)."-".substr($oem,5);
            }else{
                $buscado_sin_guion=str_replace("-","",$oem); //quitar guion
                $buscado_con_guion=$oem;
            }
        if($value == 1){
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                    ->groupBy('repuestos.id')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
    
                    $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
        }else{
              
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                    ->groupBy('repuestos.id')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
    
                    $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
        }
    }

    public function ordenar_modelo_marca($marca, $idmodelo){
        $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
            ->where('marcarepuestos.marcarepuesto', $marca)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.descripcion')
                ->get();
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
    }

    public function ordenar_modelo_familia($familia, $idmodelo){
        
            $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();
            

            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
            ->where('familias.nombrefamilia', $familia)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.descripcion')
                ->get();
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);

                        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }elseif($descuento){
                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }elseif($descuento){
                                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
    }

    public function ordenar_buscador_marca($tag, $marca){
     
        $repuestos = $this->buscar_en_descrip($tag, 0);
        $marca = marcarepuesto::where('marcarepuesto',$marca)->first();
       $repuestos_ = [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60 && $repuesto->id_marca_repuesto == $marca->id){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                array_push($repuestos_,$rep);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
    }

    public function ordenar_marca($marca){
        $marca = marcarepuesto::where('marcarepuesto',$marca)->first();
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('id_marca_repuesto',$marca->id)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
      
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_familia($familia){
        $familia = familia::where('nombrefamilia',$familia)->first();
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('id_familia',$familia->id)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
      
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);

                $descuento = descuento::where('id_familia',$repuesto->id_familia)
                                        ->where('activo',1)
                                        ->where('id_local',3)
                                        ->orWhere('id_local',2)
                                        ->first();
               
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }elseif($descuento != null){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        $rep->medidas = $repuesto->medidas;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }elseif($descuento != null){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        $rep->medidas = $repuesto->medidas;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_marca_medida($marca,$medida){
        $marca = marcarepuesto::where('marcarepuesto',$marca)->first();
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_marca_repuesto',$marca->id)
                                ->where('repuestos.medidas','like','%'.$medida.'%')
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
      
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_familia_medida($familia,$medida){
        $familia_completa = familia::where('nombrefamilia',$familia)->first();
        $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->where('repuestos.medidas','like','%'.$medida.'%')
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
      
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function getAuthenticatedUser()
    {
        try {
          if (!$user = JWTAuth::parseToken()->authenticate()) {
                  return response()->json(['user_not_found'], 404);
          }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    public function ordenar_busqueda_modelo_marca($marca, $idmodelo, $idfamilia){
        $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia',$idfamilia)
            ->where('repuestos.id_marca_repuesto','<>',190)
            ->where('marcarepuestos.marcarepuesto', $marca)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
        return [$marca, $modelo, $familia];
    }

    public function ordenar_rango_precio($min, $max){
        $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais','familias.nombrefamilia', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.precio_venta','asc')
                ->get();

        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_rango_precio_con_familia($min, $max,$familia){
        $familia_completa = familia::where('nombrefamilia',$familia)->first();
        $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
            ->where('repuestos.id_familia',$familia_completa->id)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais','familias.nombrefamilia', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.precio_venta','asc')
                ->get();

        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_tag_rango_precio($min, $max,$tag){
        try {
            $buscado = $tag;
            $orden = 1;
            //repuestos.descripcion, repuesto->id
            $resp['resultado']=false;
            $buscado=trim($buscado);
            $terminos=explode(" ",trim($buscado));
            $encontrados=Collect();

        //PRIMERO Determinar que familia de repuestos esta buscando...
        $n_familia="";
        $n_terminos_encontrados=[];
        $decide_fam=[];
        $id_fam=0;
       
        for($i=0;$i<count($terminos);$i++){


            $familys=familia::select('id','nombrefamilia')
                            ->where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->get();

            $n_hay=$familys->count();
            if($n_hay>0){
                array_push($n_terminos_encontrados,$terminos[$i]);
            }

            //qué id familia tiene más frecuencia
            foreach($familys as $famy)
            {
                if(!isset($decide_fam[$famy->id])) $decide_fam[$famy->id]=0;
                $decide_fam[$famy->id]++;
            }
        }

        $terer="";
        for($j=0;$j<count($n_terminos_encontrados);$j++) $terer.=$n_terminos_encontrados[$j]." ";
        $max_cant=0;
        $max_id_fam=0;
        foreach($decide_fam as $id_fam=>$cant){
            if($cant>$max_cant){
                $max_cant=$cant;
                $max_id_fam=$id_fam;
            }
        }
        $ids_max_fam=[];
        foreach($decide_fam as $id_fam=>$cant){
            if($cant==$max_cant) array_push($ids_max_fam,$id_fam);
        }

        $terminos2=[];

        //quitar los términos anteriores encontrados para familias
        $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
        foreach($terminos_temp as $j) array_push($terminos2,$j);


        if(count($terminos2)==0){ //no encontró repuesto para familias, y copia los terminos
            $terminos2=$terminos;
        }

        if(!isset($familias_encontradas)) $familias_encontradas=[];

        //buscar marca de vehículo
        $n_marca_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos2);$i++){
            if(strlen($terminos2[$i])>0){
                $n_hay=marcavehiculo::where('marcanombre','LIKE','%'.$terminos2[$i].'%')
                                    ->where('marcavehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos2[$i]);
                    $n_marca_vehiculo.=$terminos2[$i]." ";
                }
            }
        }

        $n_marca_vehiculo_buscado=trim($n_marca_vehiculo);
        $terminos3=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE MARCA VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="(marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ?) AND ";
            }
            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $al_inicio=$n_terminos_encontrados[$i]." %";
                $en_medio="% ".$n_terminos_encontrados[$i]." %";
                $al_final="% ".$n_terminos_encontrados[$i];
                $unico=$n_terminos_encontrados[$i];
                array_push($param,$al_inicio);
                array_push($param,$en_medio);
                array_push($param,$al_final);
                array_push($param,$unico);
            }
            array_push($param,1);
            $marca_vehiculo_encontrados=marcavehiculo::select('marcavehiculos.idmarcavehiculo')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos2,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos3,$j);
        }
        if(count($terminos3)==0){
            $terminos3=$terminos2;
        }

        if(!isset($marca_vehiculo_encontrados)) $marca_vehiculo_encontrados=[];

        //buscar modelo de vehículo
        //En terminos3 puede llegar el cilindraje del motor en formato 2.5 por ejemplo
        //y debe convertirse en 2500
        $cil_punto=[0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5,2.6,2.7,2.8,2.9,3.0,3.1,3.2,3.3,3.4,3.5,3.6,3.7,3.8,3.9,4.0,4.1,4.2,4.3,4.4,4.5,4.6,4.7,4.8,4.9,5.0,5.1,5.2,5.3,5.4,5.5,5.6,5.7,5.8,5.9,6.0,6.1,6.2,6.3,6.4,6.5,6.6,6.7,6.8,6.9,7.0];
        //$cil_coma=['0,6','0,7','0,8','0,9','1,1','1,1','1,2','1,3','1,4','1,5','1,6','1,7','1,8','1,9','2,0','2,1','2,2','2,3','2,4','2,5','2,6','2,7','2,8','2,9','3,0','3,1','3,2','3,3','3,4','3,5','3,6','3,7','3,8','3,9','4,0','4,1','4,2','4,3','4,4','4,5','4,6','4,7','4,8','4,9','5,0','5,1','5,2','5,3','5,4','5,5','5,6','5,7','5,8','5,9','6,0','6,1','6,2','6,3','6,4','6,5','6,6','6,7','6,8','6,9','7,0'];
        $cil_normal=[600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000,4100,4200,4300,4400,4500,4600,4700,4800,4900,5000,5100,5200,5300,5400,5500,5600,5700,5800,5900,6000,6100,6200,6300,6400,6500,6600,6700,6800,6900,7000];

        $n_modelo_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos3);$i++){
            if(strlen($terminos3[$i])>0){
                if(strpos($terminos3[$i],",")>0){
                    $terminos3[$i]=str_replace(',','.',$terminos3[$i]);
                }
                if(is_numeric($terminos3[$i])){
                    $valnum=$terminos3[$i];
                    settype($valnum,"float");
                    $i_punto=array_search($valnum,$cil_punto,true); //si no encuentra devuelve false, caso contrario el indice
                    if($i_punto===false){

                    }else{
                        $terminos3[$i]=$cil_normal[$i_punto];
                    }
                }


                $n_hay=modelovehiculo::where('modelonombre','LIKE','%'.$terminos3[$i].'%')
                                    ->where('modelovehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos3[$i]);
                    $n_modelo_vehiculo.=$terminos3[$i]." ";
                }
            }
        }

        $n_modelo_vehiculo_buscado=trim($n_modelo_vehiculo);
        $terminos4=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE modelo VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado="%".$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $modelo_vehiculo_encontrados=modelovehiculo::select('modelovehiculos.id')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos3,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos4,$j);

        }
        if(count($terminos4)==0){
            $terminos3=$terminos3;
        }

        if(!isset($modelo_vehiculo_encontrados)) $modelo_vehiculo_encontrados=[];
        if($id_fam>0){
            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)==0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)==0){
                // Menor a Mayor
                if($orden == 1){
                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }else{
                if($orden == 1){
                    //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','asc')
                    ->get();
                }else if($orden == 2){
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->orderBy('repuestos.precio_venta','desc')
                    ->get();
                }else{
                    $encontrados=repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                    ->where('repuestos.id_familia',$max_id_fam)
                    ->wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.stock_actual','>',0)
                    ->where('repuestos.id_marca_repuesto','<>',190)
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('familias','repuestos.id_familia','familias.id')
                    ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                    ->get();
                }
                

            }

        }


        $repuestos_= [];
        foreach($encontrados as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta= $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function ordenar_modelo_rango_precio($min, $max,$idmodelo){
        try {
            $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();

           
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();
           
            $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
            ->wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.precio_venta')
                ->get();
                $repuestos_= [];
                $marcas_repuestos = [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function ordenar_modelo_rango_precio_con_familia($min, $max,$idmodelo,$familia){
        try {
            $familia_completa = familia::where('nombrefamilia',$familia)->first();
            $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();

           
            $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();
           
            $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
            ->wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia','<>',312)
            ->where('repuestos.id_familia',$familia_completa->id)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.precio_venta')
                ->get();
                $repuestos_= [];
                $marcas_repuestos = [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                                array_push($marcas_repuestos, $rep->marcarepuesto);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function ordenar_rango_precio_oem($min, $max, $oem){
        try {
          
            $pos=strpos($oem,"-");
            if($pos===false)
            {
                $buscado_sin_guion=$oem;
                $buscado_con_guion=substr($oem,0,5)."-".substr($oem,5);
            }else{
                $buscado_sin_guion=str_replace("-","",$oem); //quitar guion
                $buscado_con_guion=$oem;
            }

     
    
            $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                    ->groupBy('repuestos.id')
                    ->get();
    
                    $repuestos_= [];
                    $marcas_repuestos = [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                        array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }
                }
                
            }
                
        
        return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function ordenar_medida_rango_precio($min, $max, $medida){
        try {
            $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                                ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                                ->where('repuestos.id_familia','<>',312)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
    
            $repuestos_= [];
            $marcas_repuestos = [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');
    
                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
    
                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
    
                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                $rep->precio_venta = $repuesto->precio_venta;
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                $rep->precio_venta = $repuesto->precio_venta;
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        }
                }
            }
    
            return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function ordenar_medida_rango_precio_con_familia($min, $max, $medida,$familia){
        try {
            $familia_completa = familia::where('nombrefamilia',$familia)->first();
            $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])
                                ->select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia')
                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                ->join('familias','repuestos.id_familia','familias.id')
                                ->where('repuestos.stock_actual','>',0)
                                ->where('repuestos.id_marca_repuesto','<>',190)
                                ->where('repuestos.medidas','LIKE','%'.$medida.'%')
                                ->where('repuestos.id_familia','<>',312)
                                ->where('repuestos.id_familia',$familia_completa->id)
                                ->orderBy('repuestos.precio_venta','asc')
                                ->get();
    
            $repuestos_= [];
            $marcas_repuestos = [];
            foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                    $firstDate = $repuesto->fecha_actualiza_precio;
                    $secondDate = date('d-m-Y H:i:s');
    
                    $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));
    
                    $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                    $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));
    
                    $minutos = floor(abs($dateDifference / 60));
                    $horas = floor($minutos / 60);
                    $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                    }else{
                                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                                $rep->id = $repuesto->id;
                                                $rep->descripcion = $repuesto->descripcion;
                                                $rep->urlfoto = $foto->urlfoto;
                                                if($repuesto->oferta == 1){
                                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                                    $rep->precio_venta = $v->precio_actualizado;
                                                }else{
                                                    $rep->precio_venta = $repuesto->precio_venta;
                                                }
                                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                                $rep->stock_actual = $repuesto->stock_actual;
                                                array_push($repuestos_,$rep);
                                                array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        }
                }
            }
    
            return $repuestos_;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function ordenar_marca_oem($marca, $oem){

        $marca = marcarepuesto::where('marcarepuesto',$marca)->first();
        $pos=strpos($oem,"-");
            if($pos===false)
            {
                $buscado_sin_guion=$oem;
                $buscado_con_guion=substr($oem,0,5)."-".substr($oem,5);
            }else{
                $buscado_sin_guion=str_replace("-","",$oem); //quitar guion
                $buscado_con_guion=$oem;
            }
        
        

        $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                ->where('repuestos.id_marca_repuesto',$marca->id)
                    ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                    ->groupBy('repuestos.id')
                    ->get();
      
        $repuestos_= [];
        foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $similares = $this->damesimilares($repuesto->id);
                if($similares->count() > 0 && $dias <= 60){
                    $rep = new repuesto_catalogo;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        if($repuesto->oferta == 1){
                            $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                            $rep->precio_venta = $v->precio_actualizado;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        $rep->stock_actual = $repuesto->stock_actual;
                        array_push($repuestos_,$rep);
                    }
                }
                
            }
                
        
        return $repuestos_;
    }

    public function ordenar_busquedamodelo_rango_precio($min, $max, $idmodelo, $idfamilia){
        $aplicaciones = similar::select('id_repuestos')
        ->where('id_modelo_vehiculo', $idmodelo)
        ->get()
        ->toArray();
   
        $repuestos = repuesto::whereBetween('repuestos.precio_venta', [$min, $max])->wherein('repuestos.id', $aplicaciones)
        ->where('repuestos.activo',1)
        ->where('repuestos.id_familia',$idfamilia)
        ->where('repuestos.stock_actual','>',0)
        ->where('repuestos.id_familia','<>',312)
        ->where('repuestos.id_marca_repuesto','<>',190)
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->join('familias','familias.id','repuestos.id_familia')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->orderBy('repuestos.descripcion')
            ->get();

            $repuestos_= [];
            $marcas_repuestos = [];
            foreach($repuestos as $repuesto){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                    $similares = $this->damesimilares($repuesto->id);
                    if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if($repuesto->oferta == 1){
                                $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                $rep->precio_venta = $v->precio_actualizado;
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                            }
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if($repuesto->oferta == 1){
                                $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                $rep->precio_venta = $v->precio_actualizado;
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                            }
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($repuestos_,$rep);
                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                        }
                    }
                    
                }
                    
            
                return $repuestos_;
    }

    public function ordenar_buscador_modelo($idfamilia, $idmodelo, $value){
        $modelo = modelovehiculo::select('modelovehiculos.*','marcavehiculos.marcanombre')
                                        ->join('marcavehiculos','modelovehiculos.marcavehiculos_idmarcavehiculo','marcavehiculos.idmarcavehiculo')
                                        ->where('modelovehiculos.id',$idmodelo)
                                        ->first();
        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();
        if($value == 1){
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia',$idfamilia)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.precio_venta','asc')
                ->get();
        }else{
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia',$idfamilia)
            ->where('repuestos.id_marca_repuesto','<>',190)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('familias','repuestos.id_familia','familias.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*','familias.nombrefamilia')
                ->orderBy('repuestos.precio_venta','desc')
                ->get();
        }
        

            
                $repuestos_= [];
                foreach($repuestos as $repuesto){
                    //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new repuesto_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }
                        }
                        
                    }
                        
                
                return $repuestos_;
        return [$marca, $modelo, $familia];
    }

    public function dame_ofertas(){
       $hoy = getdate();
       $dia = $hoy['mday'];
       $mes = $hoy['mon'];
       $year = $hoy['year'];

       $fecha = $year.'-'.$mes.'-'.$dia;
      
        $ofertas = oferta_pagina_web::select('ofertas_pagina_web.*','repuestos.descripcion','repuestos.fecha_actualiza_precio')
                                    ->join('repuestos','ofertas_pagina_web.id_repuesto','repuestos.id')
                                    ->where('ofertas_pagina_web.activo',1)
                                    ->get();
        $repuestos = repuesto::select('repuestos.*','ofertas_pagina_web.precio_actualizado','ofertas_pagina_web.descuento','familias.nombrefamilia','ofertas_pagina_web.desde','ofertas_pagina_web.hasta')
                                    ->where('repuestos.stock_actual','>',0)
                                    ->where('repuestos.id_familia','<>',312)
                                    ->where('repuestos.id_marca_repuesto','<>',190)
                                    ->where('ofertas_pagina_web.activo',1)
                                    ->join('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->join('familias','repuestos.id_familia','familias.id')
                                    ->get();

       
         $repuestos_= [];
                foreach($repuestos as $repuesto){
                  
                    //Revisamos si hay oferta disponible el dia de hoy
                
                        //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares->count() > 0 && $dias <= 60){

                            $rep = new oferta_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->codigo_interno = $repuesto->codigo_interno;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }
                        }
                   
                    
                        
                }
                    // El id local 1 corresponde a las ofertas solo en local
                    // El id local 2 y el id local 3 corresponde a las ofertas web
                $descuentos = descuento::select('descuentos.*','familias.nombrefamilia')
                                        ->join('familias','descuentos.id_familia','familias.id')
                                        ->where('descuentos.activo',1)
                                        ->where('id_local','<>',1)
                                        ->get();

                $user = Auth::user();
                
                return [$repuestos_,$descuentos,$user];
    }

    public function enviar_correo(Request $req){
        try {
            $nombre = $req->nombre;
            $apellidos = $req->apellidos;
            $email = $req->email;
            $telefono = $req->telefono;
            $comentario = $req->comentario;

            $correo_destino = 'ventas_online@panchorepuestos.cl';

            $correo=new EnviarCorreo_comentario($correo_destino,$nombre,$apellidos,$email,$telefono, $comentario);

            \Mail::send($correo); //Devuelve void
            $rpta="Gracias por su comentario. Le responderemos a la brevedad.";
                        
            if( count(\Mail::failures()) > 0 ) {

                $rpta= "Error al enviar: <br>";

                    foreach(\Mail::failures() as $email_malo) {
                        $rpta.=" - $email_malo <br>";
                    }

            }

            return $rpta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        


        //$correo=new EnviarCorreo($r->correo_destino,$archivo_ruta,$archivo_xml_ruta,$docu_nombre,$r->num_doc);
    }

    public function restablecer_password(Request $req){
        $usuario = User::where('remember_token',$req->token)->first();
        
        if(empty($usuario)){
            return response()->json(['errors' => ['messages'=>['No existe el usuario.']]], 422);
        }
        // Definir mensajes de validación personalizados
        $messages = [
            'password.required' => 'La password es requerida.',
            'password.string' => 'La password debe ser una cadena de texto.',
            'password.min' => 'La password debe tener al menos :min caracteres.',
            'confirm_password.required' => 'El campo confirmación de contraseña es requerido.',
            'confirm_password.same' => 'El campo confirmación de contraseña debe ser igual a la contraseña.'
        ];

        // Validar los datos del formulario con los mensajes personalizados
        $validator = Validator::make($req->all(), [
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password' // Validar que la confirmación del password sea requerida y sea igual al password
        ], $messages);

        // Manejar errores de validación
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $password_encrypt = Hash::make($req->password);
        $usuario->password = $password_encrypt;
        //Eliminamos el token por seguridad.
        $usuario->remember_token = '---';
        $usuario->save();

        return 'OK';
    }

    public function buscar_medida_familia($familia, $medidas){
        try {
            $familia = familia::where('nombrefamilia',$familia)->first();
            $idfamilia = $familia->id;
            $repuestos = repuesto::select('repuestos.*','familias.nombrefamilia','marcarepuestos.marcarepuesto')
                                    ->where('repuestos.medidas','like','%'.$medidas.'%')
                                    ->where('repuestos.stock_actual','>',0)
                                    ->where('repuestos.id_familia',$idfamilia)
                                    ->join('familias','repuestos.id_familia','familias.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->get();
            if($repuestos->count() == 0){
                // en la variable medidas agregar un espacio despues del caracter -
                $medidas_ = str_replace('-','- ',$medidas);

                $repuestos = repuesto::select('repuestos.*','familias.nombrefamilia','marcarepuestos.marcarepuesto')
                                    ->where('repuestos.medidas','like','%'.$medidas_.'%')
                                    ->where('repuestos.stock_actual','>',0)
                                    ->where('repuestos.id_familia',$idfamilia)
                                    ->join('familias','repuestos.id_familia','familias.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->get();
            }

            // devolvemos solos los repuestos que la fecha de actualizacion del stock sea menor a 60 dias
            $repuestos_ = [];
            foreach ($repuestos as $repuesto) {
                $fecha_actualizacion = $repuesto->updated_at;
                $fecha_actual = date('Y-m-d');
                $dias = $this->diferencia_dias($fecha_actualizacion,$fecha_actual);
                if($dias <= 60){
                    $rep = new oferta_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->codigo_interno = $repuesto->codigo_interno;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->medidas = $repuesto->medidas;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                if($repuesto->oferta == 1){
                                    $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                    $rep->precio_venta = $v->precio_actualizado;
                                }else{
                                    $rep->precio_venta = $repuesto->precio_venta;
                                }
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                $rep->medidas = $repuesto->medidas;
                                array_push($repuestos_,$rep);
                            }
                }
            }
            return $repuestos_;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function diferencia_dias($fecha_actualizacion, $fecha_actual){
        $fecha1 = new \DateTime($fecha_actualizacion);
        $fecha2 = new \DateTime($fecha_actual);
        $diferencia = $fecha1->diff($fecha2);
        return $diferencia->days;
    }

    public function familias_medidas(){
        try {
            $repuestos = repuesto::select('marcarepuestos.marcarepuesto','repuestos.*','familias.nombrefamilia','paises.nombre_pais')
            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
            ->join('familias','repuestos.id_familia','familias.id')
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->where('repuestos.stock_actual','>',0)
            ->where('repuestos.id_familia',2)
            ->orWhere('repuestos.id_familia',3)
            ->orWhere('repuestos.id_familia',4)
            ->orWhere('repuestos.id_familia',5)
            ->orWhere('repuestos.id_familia',6)
            ->orWhere('repuestos.id_familia',25)
            ->orWhere('repuestos.id_familia',26)
            ->orWhere('repuestos.id_familia',27)
            ->orWhere('repuestos.id_familia',28)
            ->orWhere('repuestos.id_familia',38)
            ->orWhere('repuestos.id_familia',49)
            ->orWhere('repuestos.id_familia',51)
            ->orWhere('repuestos.id_familia',62)
            ->orWhere('repuestos.id_familia',64)
            ->orWhere('repuestos.id_familia',66)
            ->orWhere('repuestos.id_familia',68)
            ->orWhere('repuestos.id_familia',112)
            ->orWhere('repuestos.id_familia',113)
            ->orWhere('repuestos.id_familia',114)
            ->orWhere('repuestos.id_familia',120)
            ->orWhere('repuestos.id_familia',126)
            ->orWhere('repuestos.id_familia',154)
            ->orWhere('repuestos.id_familia',166)
            ->orWhere('repuestos.id_familia',171)
            ->orWhere('repuestos.id_familia',206)
            ->orWhere('repuestos.id_familia',208)
            ->orWhere('repuestos.id_familia',256)
            ->orWhere('repuestos.id_familia',282)
            ->orWhere('repuestos.id_familia',288)
            ->orWhere('repuestos.id_familia',333)
            ->orWhere('repuestos.id_familia',353)
            ->orWhere('repuestos.id_familia',354)
            ->orWhere('repuestos.id_familia',355)
            ->orWhere('repuestos.id_familia',356)
            ->orWhere('repuestos.id_familia',358)
            ->orWhere('repuestos.id_familia',359)
            ->orWhere('repuestos.id_familia',363)
            ->orWhere('repuestos.id_familia',364)
            ->orWhere('repuestos.id_familia',366)
            ->orWhere('repuestos.id_familia',369)
            ->orWhere('repuestos.id_familia',370)
            ->orWhere('repuestos.id_familia',374)
            ->orWhere('repuestos.id_familia',375)
            ->orWhere('repuestos.id_familia',393)
            ->orWhere('repuestos.id_familia',400)
            ->orWhere('repuestos.id_familia',402)
            ->orWhere('repuestos.id_familia',423)
            ->orWhere('repuestos.id_familia',424)
            ->orWhere('repuestos.id_familia',455)
            ->orWhere('repuestos.id_familia',470)
            ->where('repuestos.id_marca_repuesto','<>',190)
            ->orderBy('repuestos.descripcion','asc')
            ->get();

            $repuestos_= [];
            $marcas_repuestos = [];
            $familias_repuestos = [];
            foreach($repuestos as $repuesto){
                        //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);

                        //Buscamos si el repuesto es de una familia que tenga descuentos
                        $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();

                        if($similares->count() > 0 && $dias <= 60){
                        $rep = new repuesto_catalogo;
                        $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                        if(isset($foto)){
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            if($repuesto->oferta == 1){
                                //Se busca el valor en oferta del repuesto
                                $v = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                                //$rep->precio_venta = $v->precio_actualizado;
                            }elseif($descuento){
                                $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                            }else{
                                $rep->precio_venta = $repuesto->precio_venta;
                            }
                            
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                            array_push($familias_repuestos,$repuesto->nombrefamilia);
                            array_push($repuestos_,$rep);
                        }else{
                            $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                            $rep->id = $repuesto->id;
                            $rep->descripcion = $repuesto->descripcion;
                            $rep->urlfoto = $foto->urlfoto;
                            $rep->precio_venta = $repuesto->precio_venta;
                            $rep->marcarepuesto = $repuesto->marcarepuesto;
                            $rep->nombrefamilia = $repuesto->nombrefamilia;
                            $rep->stock_actual = $repuesto->stock_actual;
                            array_push($marcas_repuestos, $repuesto->marcarepuesto);
                            array_push($familias_repuestos,$repuesto->nombrefamilia);
                            array_push($repuestos_,$rep);
                        }
            }

        }


            return [[], array_values(array_unique($marcas_repuestos)),array_values(array_unique($familias_repuestos))];
        
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
    }

    public function dame_un_repuesto($id)
    {
        try {
            $repuesto=repuesto::where('repuestos.id',$id)
            ->join('familias','repuestos.id_familia','familias.id')
            ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
            ->get();

            return $repuesto;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}


