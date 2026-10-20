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
use App\formapago;
use App\marcavehiculo;
use App\carrito_virtual_detalle;
use App\carrito_transferido;
use App\retiro_tienda;
use App\repuesto_catalogo;
use App\repuesto_carrito;
use App\correlativo;
use App\cliente_modelo;
use App\boleta;
use App\boleta_detalle;
use App\factura;
use App\factura_detalle;
use App\fabricante;
use App\pago;
use App\oem;
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
use App\cotizacion;
use App\cotizacion_detalle;
use App\cliente_potencial;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Password;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;

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
                        // ->where('idmarcavehiculo','<>',38)
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

    public function dame_familias_repuestos($dato)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);
        try {
            $modelo = modelovehiculo::find($idmodelo);
            $quemodelo =$modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

            if ($op == 0) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                INNER JOIN familias ON repuestos.id_familia=familias.id
                WHERE repuestos.activo=1 AND repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                GROUP BY repuestos.id_familia
                ORDER BY nombrefamilia";
            }

            if ($op == 1) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 2) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 3) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            $familias = \DB::select($s);
            $total_repuestos = 0;
            foreach ($familias as $repuesto) {
                $total_repuestos += $repuesto->total;
            }

            return ['familias' => $familias, 'total_repuestos' => $total_repuestos, 'modelo' => $quemodelo];

        } catch (\Exception $error) {
            $debug = $error;
            return ['error' => $debug->getMessage()];
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

    public function damerepuestototem($id_repuesto)   {
        try {
            $repuesto_original = repuesto::select('repuestos.*',
                                        'marcarepuestos.marcarepuesto',
                                        'paises.nombre_pais',
                                        'paises.id as pais_id',
                                        'familias.nombrefamilia',
                                        DB::raw(\App\Services\StockPorLocalService::totalSql() . ' as stock_actual_total')
                                        )
                                        ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                                        ->join('familias','repuestos.id_familia','familias.id')
                                        ->where('repuestos.id',$id_repuesto)
                                        ->first();

            // Ordenar las medidas del repuesto por valor numérico (mayor a menor)
            if ($repuesto_original && $repuesto_original->medidas) {
                $porciones = preg_split('/[:,]/', $repuesto_original->medidas);
                
                // Ordenar las porciones por el valor numérico que contienen (de mayor a menor)
                usort($porciones, function($a, $b) {
                    // Extraer números de cada porción
                    preg_match_all('/\d+(?:[.,]\d+)?/', $a, $matches_a);
                    preg_match_all('/\d+(?:[.,]\d+)?/', $b, $matches_b);
                    
                    // Obtener el valor máximo de cada porción
                    $max_a = 0;
                    if (!empty($matches_a[0])) {
                        $numeros_a = array_map(function($num) {
                            return (float) str_replace(',', '.', $num);
                        }, $matches_a[0]);
                        $max_a = max($numeros_a);
                    }
                    
                    $max_b = 0;
                    if (!empty($matches_b[0])) {
                        $numeros_b = array_map(function($num) {
                            return (float) str_replace(',', '.', $num);
                        }, $matches_b[0]);
                        $max_b = max($numeros_b);
                    }
                    
                    // Ordenar de mayor a menor
                    return $max_b <=> $max_a;
                });
                
                // Reconstruir el string de medidas ordenadas
                $repuesto_original->medidas = implode(',', $porciones);
            }

            

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
                    if(!isset($foto)){
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                    }
                    if($dias<= 60){
                        $oferta_rec = oferta_pagina_web::where('id_repuesto',$repuesto->id)->first();
                        $descuento_rec = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_normal = $repuesto->precio_venta;
                        $rep->marcarepuesto = $repuesto->marcarepuesto;
                        $rep->nombrefamilia = $repuesto->nombrefamilia;
                        if($oferta_rec){
                            $rep->precio_venta = $oferta_rec->precio_actualizado;
                            $rep->oferta = 1;
                        }elseif($descuento_rec){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento_rec->porcentaje / 100) * $repuesto->precio_venta);
                            $rep->oferta = 1;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                            $rep->oferta = 0;
                        }
                        array_push($recomendados_,$rep);
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

    $descuento = descuento::where('activo',1)->where('id_familia',$repuesto_original->id_familia)->first();
    if($existe){
        $repuesto_original->oferta = 1;
        $repuesto_original->precio_normal = $repuesto_original->precio_venta;
        $repuesto_original->precio_venta = $existe->precio_actualizado;
        return [$repuesto_original,$fotos, $similares_,$recomendados_,$existe,$next,$previous,$rectificadores, $alternadores];
    }

    if($descuento){
        $repuesto_original->oferta = 1;
        $repuesto_original->precio_normal = $repuesto_original->precio_venta;
        $repuesto_original->precio_venta = $repuesto_original->precio_venta - (($descuento->porcentaje/100) * $repuesto_original->precio_venta);
    } else {
        $repuesto_original->oferta = 0;
    }

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

     
            //return repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')->join('oems', 'repuestos.id', 'oems.id_repuestos')->get();

    
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual','>',0)
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    // ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->leftjoin('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
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

    public function buscar_por_oem_totem($oem){
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

            //return repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')->join('oems', 'repuestos.id', 'oems.id_repuestos')->get();

            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
                ->where('repuestos.activo',1)
                ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' > 0')
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                    // ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->leftjoin('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
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
                    if(!isset($foto)){
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                    }
                    $oferta = oferta_pagina_web::where('id_repuesto', $repuesto->id)->first();
                    $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->first();
                    $rep->id = $repuesto->id;
                    $rep->descripcion = $repuesto->descripcion;
                    $rep->urlfoto = $foto->urlfoto;
                    $rep->marcarepuesto = $repuesto->marcarepuesto;
                    $rep->precio_normal = $repuesto->precio_venta;
                    $rep->stock_actual = \App\Services\StockPorLocalService::total($repuesto);
                    if($oferta){
                        $rep->precio_venta = $oferta->precio_actualizado;
                        $rep->oferta = 1;
                    }elseif($descuento){
                        $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje / 100) * $repuesto->precio_venta);
                        $rep->oferta = 1;
                    }else{
                        $rep->precio_venta = $repuesto->precio_venta;
                        $rep->oferta = 0;
                    }
                    array_push($repuestos_,$rep);
                    array_push($marcas_repuestos, $repuesto->marcarepuesto);
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

    public function agregar_carrito_totem(Request $req){
     
        $idrep = $req->idrep;
        $cantidad = $req->cantidad;
        $cliente_id = $req->cliente_id;
        $usuario_id = 16; // USUARIO FRANCISCO ROJO
        // if($usuario_id == '' || !$usuario_id){
        //     return response()->json([
        //         'message' => 'Le informamos que para poder agregar repuestos al carrito debe registrarse.',
        //     ], 500);
        // }
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
            // Los amortiguadores laterales se venden como máximo uno por lado y marca.
            $repuesto_amortiguador = repuesto::find($idrep);
            if ($repuesto_amortiguador && in_array((int) $repuesto_amortiguador->id_familia, [344, 345], true)) {
                if (!$this->cantidadAmortiguadorValida($repuesto_amortiguador, $cantidad)) {
                    return "amortiguador_unidad_lado";
                }

                $lado_amortiguador = $this->detectarLadoPar($repuesto_amortiguador->descripcion);

                if ($lado_amortiguador) {
                    $mismo_lado_en_carrito = carrito_virtual_detalle::where('carrito_numero', $carrito_existente->numero_carrito)
                        ->join('repuestos', 'carrito_virtual_detalle.repuesto_id', 'repuestos.id')
                        ->where('repuestos.id_familia', $repuesto_amortiguador->id_familia)
                        ->where('repuestos.id_marca_repuesto', $repuesto_amortiguador->id_marca_repuesto)
                        ->select('repuestos.descripcion')->get()
                        ->contains(function ($rep) use ($lado_amortiguador) {
                            return $this->detectarLadoPar($rep->descripcion) === $lado_amortiguador;
                        });

                    if ($mismo_lado_en_carrito) {
                        return "amortiguador_lado_duplicado";
                    }
                }
            }
            
            $pregunta = carrito_virtual_detalle::where('repuesto_id',$idrep)->where('carrito_numero',$carrito_existente->numero_carrito)->get();
          
            if($pregunta->count() > 0){
                // El mismo codigo completa el par cuando sirve para ambos lados.
                if ($repuesto_amortiguador && in_array((int) $repuesto_amortiguador->id_familia, [344, 345], true)
                    && $this->detectarLadoPar($repuesto_amortiguador->descripcion) === null) {
                    $detalle = $pregunta->first();
                    $cantidad_total = $detalle->cantidad + $cantidad;
                    if (!$this->cantidadAmortiguadorValida($repuesto_amortiguador, $cantidad_total)) {
                        return "amortiguador_unidad_lado";
                    }
                    if (\App\Services\StockPorLocalService::total($repuesto_amortiguador) < $cantidad_total) {
                        return response()->json(['message' => 'Cantidad solicitada sobrepasa stock actual'], 500);
                    }
                    $detalle->cantidad = $cantidad_total;
                    $detalle->subtotal_item = $detalle->pu * $cantidad_total;
                    $detalle->total_item = $detalle->subtotal_item - $detalle->descuento_item;
                    $detalle->save();
                    $carrito_existente->fecha_emision = Carbon::today()->toDateString();
                    $carrito_existente->save();
                    $this->revisar_carrito_virtual($usuario_id);
                    return response()->json(['message' => 'OK']);
                }
                return response()->json([
                    'message' => 'Repuesto ya existe en carrito!!',
                ], 500);
            }else{
              
                $repuesto = repuesto::find($idrep);
                $id_familia = $repuesto->id_familia;
                // verificar que si la familia es 24 (bujias) la cantidad minima a agregar sea 3
                if($id_familia == 24 || $id_familia == 29){
                    if($cantidad < 3){
                        return "bujia_minimo";
                    }
                }
                // verificar que si la familia es 532 (valvulas) la cantidad minima a agregar sea 4
                if($id_familia == 532 || $id_familia == 78){
                    if($cantidad < 4){
                        return "valvula_minimo";
                    }
                }

                // verificar que si la familia es 374 (disco freno delantero) o 375 (disco freno trasero) la cantidad sea multiplo de 2
                if($id_familia == 374 || $id_familia == 375){
                    if($cantidad % 2 != 0){
                        return "disco_freno_multiplo";
                    }
                }

                // verificar que si la familia es 362 (retenes de valvulas)  la cantidad minima a agregar sea 8
                if($id_familia == 362 || $id_familia == 392){
                    if($cantidad % 8 != 0){
                        return "retene_valvula_multiplo";
                    }
                }

                // verificamos que si la familia es 74 (culata) se debe acercar al meson
                if($id_familia == 74 || $id_familia == 271 || $id_familia == 306 || $id_familia == 285 || $id_familia == 32 || $id_familia == 269 || $id_familia == 156 || $id_familia == 153 || $id_familia == 12 || $id_familia == 11 || $id_familia == 33){
                    return "meson";
                }

                if($id_familia == 188){
                    if($cantidad < 4){
                        return "golilla_minimo";
                    }
                }

                if($id_familia == 92){
                    if($cantidad < 16){
                        return "taquie_valvula_minimo";
                    }
                }

                $stock_total = \App\Services\StockPorLocalService::total($repuesto);
                // preguntamos si existe la cantidad en el stock de bodega
                if($stock_total < $cantidad){
                    return response()->json([
                        'message' => 'Cantidad solicitada sobrepasa stock actual',
                    ], 500);
                }
              
                $o = oferta_pagina_web::where('id_repuesto',$idrep)->where('activo',1)->first();
            
                $dcto_familia = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
                $detalle = new carrito_virtual_detalle;
           
                $detalle->repuesto_id = $idrep;
                $detalle->cantidad = $cantidad;
                $detalle->carrito_numero = $carrito_existente->numero_carrito;
                $detalle->item = $this->dameultimoitem($carrito_existente->numero_carrito) + 1;
                //Si existe en oferta
                if(isset($o)){
                    $detalle->pu = $o->precio_actualizado; //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR, (ademas contiene el valor con el descuento en oferta)
                }elseif(!isset($o) && isset($dcto_familia) && ($dcto_familia->id_local <> 2)){
                    
                    $detalle->pu = $repuesto->precio_venta - (($dcto_familia->porcentaje/100) * $repuesto->precio_venta);
                    
                }else{
                    $detalle->pu = $repuesto->precio_venta;
                }
                
                $detalle->pu_neto=round($detalle->pu/(1+Session::get('PARAM_IVA')),2);
                $detalle->descuento_item = 0.00;
                $detalle->subtotal_item = $detalle->cantidad * $detalle->pu;
                $detalle->total_item = $detalle->subtotal_item - $detalle->descuento_item;
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

    private function dameultimoitem($numero_carrito)
    {
        $ultimo = carrito_virtual_detalle::where('carrito_numero', $numero_carrito)->max('item');//carrito_compra::where('usuarios_id', $id_usu)->latest()->value('item');
        if (is_null($ultimo)) {
            $ultimo = 0;
        }
        return $ultimo;
    }

    public function damecarritoexistente($usuario_id){
        $carro = carrito_virtual::where('usuario_id',$usuario_id)->where('activo',1)->first();
        return $carro;
    }

public function imprimir_ticket_totem($numero_carrito){
    try {
        // Intentar primero como carrito virtual
        $carrito_totem = carrito_virtual::where('numero_carrito', $numero_carrito)->first();

        if ($carrito_totem) {
            $carrito_virtual = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.*','carrito_virtual.numero_carrito')
                ->join('carrito_virtual','carrito_virtual.numero_carrito','carrito_virtual_detalle.carrito_numero')
                ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                ->where('carrito_virtual_detalle.carrito_numero', $carrito_totem->numero_carrito)
                ->get();
            return ['estado' => 'OK', 'carrito' => $carrito_totem, 'detalle' => $carrito_virtual];
        }

        // Si no es carrito, buscar como cotización
        $cotizacion = cotizacion::where('num_cotizacion', $numero_carrito)->first();
        if (!$cotizacion) {
            return ['estado' => 'ERROR', 'mensaje' => 'No se encontró el carrito o cotización'];
        }

        $detalle = cotizacion_detalle::select(
                'cotizaciones_detalle.precio_venta as precio_cotizado',
                'cotizaciones_detalle.cantidad',
                'cotizaciones_detalle.total as total_item',
                'repuestos.codigo_interno',
                'repuestos.precio_venta as precio_normal',
                'repuestos.descripcion',
                'repuestos.oferta'
            )
            ->join('repuestos', 'cotizaciones_detalle.id_repuestos', 'repuestos.id')
            ->where('cotizaciones_detalle.id_cotizacion', $cotizacion->id)
            ->get();

        return ['estado' => 'OK', 'carrito' => $cotizacion, 'detalle' => $detalle];

    } catch (\Exception $e) {
        return $e->getMessage();
    }
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
                            $rep->id_familia = $repuesto->id_familia;

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

   public function revisar_carrito_totem(){
    try {
        $usuario_id = 16; // USUARIO FRANCISCO ROJO
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
                    $descuento = descuento::where('activo',1)->where('id_familia',$repuesto->id_familia)->where('id_local','<>',1)->first();
                    // $similares = $this->damesimilares($repuesto->id);
                    $rep = new repuesto_carrito;
                    $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                    if(isset($foto)){
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_normal = intval($repuesto->precio_venta);
                        if(count($existe) > 0){
                            $rep->precio_venta = $existe[0]->precio_actualizado;
                            $rep->oferta = 1;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                            $rep->oferta = 1;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        
                        $rep->cantidad = $repuesto->cantidad;
                        $rep->numero_carrito = $repuesto->numero_carrito;
                        
                        
                        array_push($repuestos_,$rep);
                    }else{
                        $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                        $rep->id = $repuesto->id;
                        $rep->descripcion = $repuesto->descripcion;
                        $rep->urlfoto = $foto->urlfoto;
                        $rep->precio_normal = $repuesto->precio_venta;
                        if(count($existe) > 0){
                            $rep->precio_venta = $existe[0]->precio_actualizado;
                            $rep->oferta = 1;
                        }elseif($descuento){
                            $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje/100) * $repuesto->precio_venta);
                            $rep->oferta = 1;
                        }else{
                            $rep->precio_venta = $repuesto->precio_venta;
                        }
                        $rep->cantidad = $repuesto->cantidad;
                        $rep->numero_carrito = $repuesto->numero_carrito;

                        array_push($repuestos_,$rep);
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

   public function eliminar_item_carrito_totem($idrep,$carrito_numero){
    
    try {
        $usuario_id = 16;
        $carrito_virtual = carrito_virtual_detalle::where('repuesto_id',$idrep)->where('carrito_numero',$carrito_numero)->first();
        
        $carrito_virtual->delete();
        $items = $this->revisar_carrito_totem($usuario_id);
        return $items;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    
   }

   public function damemodelos_pormarca($idmarca)
    {
        $marca = marcavehiculo::where('idmarcavehiculo', $idmarca)->first();
        
        // Obtener modelos que tengan repuestos con stock en una sola query optimizada
        $modelos = modelovehiculo::where('modelovehiculos.activo', '=', 1)
            ->where('modelovehiculos.marcavehiculos_idmarcavehiculo', $idmarca)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('similares')
                    ->join('repuestos', 'similares.id_repuestos', '=', 'repuestos.id')
                    ->whereColumn('similares.id_modelo_vehiculo', 'modelovehiculos.id')
                    ->where('similares.activo', 1)
                    ->where('repuestos.activo', 1)
                    ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' > 0')
                    ->where('repuestos.id_familia', '<>', 312)
                    ->where('repuestos.id_marca_repuesto', '<>', 190);
            })
            ->orderBy('modelovehiculos.modelonombre')
            ->get();
        
        return [$modelos, $marca];
    }

    public function damemodelos_pormarca_totem($idmarca)
    {
        $marca = marcavehiculo::where('idmarcavehiculo', $idmarca)->first();
        
        // Obtener modelos que tengan repuestos con stock en una sola query optimizada
        $modelos = modelovehiculo::select('modelovehiculos.*')
            ->where('modelovehiculos.activo', '=', 1)
            ->where('modelovehiculos.marcavehiculos_idmarcavehiculo', $idmarca)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('similares')
                    ->join('repuestos', 'similares.id_repuestos', '=', 'repuestos.id')
                    ->whereColumn('similares.id_modelo_vehiculo', 'modelovehiculos.id')
                    ->where('similares.activo', 1)
                    ->where('repuestos.activo', 1)
                    ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' > 0')
                    ->where('repuestos.id_familia', '<>', 312)
                    ->where('repuestos.id_marca_repuesto', '<>', 190);
            })
            ->orderBy('modelovehiculos.modelonombre')
            ->get();
        
        return [$modelos, $marca];
    }

    public function formas_pago_totem(){
        $formas_pago = formapago::where('activo',1)->get();
        return $formas_pago;
    }

    public function damerepuestosfamilia_totem($idmodelo, $familia){
        try {
            $familia_completa = familia::where('nombrefamilia', $familia)->first();
            if(!$familia_completa){
                return response()->json([]);
            }

            $aplicaciones = similar::where('id_modelo_vehiculo', $idmodelo)
                            ->pluck('id_repuestos')
                            ->toArray();

            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
                ->where('repuestos.activo', 1)
                ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' > 0')
                ->where('repuestos.id_familia', $familia_completa->id)
                ->where('repuestos.id_marca_repuesto', '<>', 190)
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->select('repuestos.*', 'marcarepuestos.marcarepuesto', 'familias.nombrefamilia', 'paises.nombre_pais')
                ->orderBy('repuestos.descripcion')
                ->get();

            $repuestos_ = [];
            foreach($repuestos as $repuesto){
                $foto = repuestofoto::where('id_repuestos', '=', $repuesto->id)->first();
                if(!isset($foto)){
                    $foto = repuestofoto::where('id_repuestos', '=', 62816)->first();
                }

                $rep = new repuesto_catalogo;
                $rep->id = $repuesto->id;
                $rep->descripcion = $repuesto->descripcion;
                $rep->urlfoto = $foto->urlfoto;
                $rep->marcarepuesto = $repuesto->marcarepuesto;
                $rep->nombrefamilia = $repuesto->nombrefamilia;
                $rep->nombre_pais = $repuesto->nombre_pais;
                $rep->stock_actual = \App\Services\StockPorLocalService::total($repuesto);
                $rep->precio_normal = $repuesto->precio_venta;
                $rep->id_familia = $repuesto->id_familia;
                $rep->codigo_interno = $repuesto->codigo_interno;
                $rep->observaciones = $repuesto->observaciones;

                $oferta = oferta_pagina_web::where('id_repuesto', $repuesto->id)->first();
                $descuento = descuento::where('activo', 1)->where('id_familia', $repuesto->id_familia)->first();
                if($oferta){
                    $rep->precio_venta = $oferta->precio_actualizado;
                    $rep->oferta = 1;
                }elseif($descuento){
                    $rep->precio_venta = $repuesto->precio_venta - (($descuento->porcentaje / 100) * $repuesto->precio_venta);
                    $rep->oferta = 1;
                }else{
                    $rep->precio_venta = $repuesto->precio_venta;
                    $rep->oferta = 0;
                }

                array_push($repuestos_, $rep);
            }

            if ($this->familiaUsaPares($familia_completa)) {
                $repuestos_ = $this->armarParesDerechoIzquierdo($repuestos_);
            }

            return response()->json($repuestos_);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function armarParesDerechoIzquierdo($repuestos)
    {
        $derechos = [];
        $izquierdos = [];
        $otros = [];

        foreach ($repuestos as $rep) {
            $lado = $this->detectarLadoPar($rep->descripcion);

            if ($lado === 'derecho') {
                $key = $this->generarKeyPar($rep, 'derecho');
                $derechos[$key][] = $rep;
            } elseif ($lado === 'izquierdo') {
                $key = $this->generarKeyPar($rep, 'izquierdo');
                $izquierdos[$key][] = $rep;
            } else {
                $otros[] = $rep;
            }
        }

        $pares = [];
        $usadosIzq = [];

        foreach ($derechos as $key => $itemsDerechos) {
            foreach ($itemsDerechos as $derecho) {
                $izquierdo = null;

                if (isset($izquierdos[$key])) {
                    foreach ($izquierdos[$key] as $idx => $itemIzquierdo) {
                        if (!isset($usadosIzq[$itemIzquierdo->id])) {
                            $izquierdo = $itemIzquierdo;
                            $usadosIzq[$itemIzquierdo->id] = true;
                            break;
                        }
                    }
                }

                if (!$izquierdo) {
                    $izquierdo = $this->productoNoDisponible(
                        $derecho,
                        'No existe complemento izquierdo'
                    );
                }

                $pares[] = [
                    'derecho' => $derecho,
                    'izquierdo' => $izquierdo,
                ];
            }
        }

        // Si existen izquierdos sin derecho
        foreach ($izquierdos as $key => $itemsIzquierdos) {
            foreach ($itemsIzquierdos as $izquierdo) {
                if (isset($usadosIzq[$izquierdo->id])) {
                    continue;
                }

                $pares[] = [
                    'derecho' => $this->productoNoDisponible(
                        $izquierdo,
                        'No existe complemento derecho'
                    ),
                    'izquierdo' => $izquierdo,
                ];
            }
        }

        // Los amortiguadores sin lateralidad usan el mismo codigo en ambos lados.
        foreach ($otros as $otro) {
            if (in_array((int) $otro->id_familia, [344, 345], true)) {
                $pares[] = ['derecho' => clone $otro, 'izquierdo' => clone $otro];
            } else {
                $pares[] = $otro;
            }
        }

        return $pares;
    }

    private function generarKeyPar($rep, $lado)
    {
        $descripcion = strtoupper($rep->descripcion);

        if ($lado === 'derecho') {
            $descripcion = preg_replace('/\b(DERECHO|DERECHA|DER|RIGHT|RH)\b/u', ' ', $descripcion);
        } elseif ($lado === 'izquierdo') {
            $descripcion = preg_replace('/\b(IZQUIERDO|IZQUIERDA|IZQ|LEFT|LH)\b/u', ' ', $descripcion);
        }

        $descripcion = preg_replace('/\s+/', ' ', $descripcion);
        $descripcion = trim($descripcion);

        return $rep->id_familia . '|' . $rep->marcarepuesto . '|' . $descripcion;
    }

    private function familiaUsaPares($familia)
    {
        return in_array((int) $familia->id, [344, 345, 348, 374, 375, 50, 87], true)
            || preg_match('/\bBANDEJA\b.*\b(SUPERIOR|INFERIOR)\b/u', strtoupper($familia->nombrefamilia)) === 1;
    }

    private function cantidadAmortiguadorValida($repuesto, $cantidad)
    {
        $maximo = $this->detectarLadoPar($repuesto->descripcion) === null ? 2 : 1;
        return is_numeric($cantidad) && (float) $cantidad === (float) (int) $cantidad
            && (int) $cantidad >= 1 && (int) $cantidad <= $maximo;
    }

    private function detectarLadoPar($descripcion)
    {
        $descripcion = strtoupper($descripcion);

        $derecho = preg_match('/\b(DERECHO|DERECHA|DER|RIGHT|RH)\b/u', $descripcion);
        $izquierdo = preg_match('/\b(IZQUIERDO|IZQUIERDA|IZQ|LEFT|LH)\b/u', $descripcion);
        if ($derecho && $izquierdo) {
            return null;
        }
        if ($derecho) {
            return 'derecho';
        }

        if ($izquierdo) {
            return 'izquierdo';
        }

        return null;
    }

private function productoNoDisponible($base, $mensaje)
{
    $rep = new \stdClass();

    $rep->id = 0;
    $rep->descripcion = $mensaje;
    $rep->urlfoto = 'fotozzz/no-disponible.jpg';
    $rep->marcarepuesto = $base->marcarepuesto;
    $rep->nombrefamilia = $base->nombrefamilia;
    $rep->nombre_pais = $base->nombre_pais;
    $rep->stock_actual = 0;
    $rep->precio_normal = 0;
    $rep->id_familia = $base->id_familia;
    $rep->precio_venta = 0;
    $rep->oferta = 0;
    $rep->no_disponible = true;

    return $rep;
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

public function damemodelo_pormarca_totem($idmodelo){
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
            
            // op=0: todas las familias activas con similares para el modelo (sin filtro de medidas ni días)
            $familias_totem = DB::select("
                SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia, COUNT(repuestos.id_familia) as total
                FROM repuestos
                INNER JOIN familias ON repuestos.id_familia = familias.id
                WHERE repuestos.activo = 1
                AND (repuestos.stock_actual > 0 OR repuestos.stock_actual_dos > 0 OR repuestos.stock_actual_tres > 0)
                AND repuestos.id IN (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo = ?)
                GROUP BY repuestos.id_familia
                ORDER BY familias.nombrefamilia
            ", [$idmodelo]);

            return [$repuestos_,$modelo,array_values(array_unique($marcas_repuestos)),array_values(array_unique($familias_repuestos)), $familias_totem];
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

    public function tieneSimilares($id_repuesto)
    {
        return similar::where('id_repuestos', $id_repuesto)
            ->where('activo', 1)
            ->exists();
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
      try {
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
      } catch (\Exception $e) {
        //throw $th;
        return $e->getMessage();
      }
        
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

  

    public function dame_ofertas()
    {
        try {
            $fechaHoy = date('Y-m-d');
    
            $repuestos = repuesto::select(
                    'repuestos.*',
                    'ofertas_pagina_web.precio_actualizado',
                    'ofertas_pagina_web.descuento',
                    'familias.nombrefamilia',
                    DB::raw(\App\Services\StockPorLocalService::totalSql() . ' as stock_total')
                )
                ->join('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                ->join('familias','repuestos.id_familia','familias.id')
                ->where('ofertas_pagina_web.activo',1)
                ->whereIn('ofertas_pagina_web.local',[4,5,6])
                ->where('repuestos.id_familia','<>',312)
                ->where('repuestos.id_marca_repuesto','<>',190)
                ->whereRaw(\App\Services\StockPorLocalService::totalSql() . ' > 0')
                ->get();
        
            $repuestos_ = [];
        
            foreach($repuestos as $repuesto){
        
                // validar fecha precio <= 60 días
                $dias = now()->diffInDays($repuesto->fecha_actualiza_precio);
        
                if($dias > 60) continue;
        
                // validar similares
                if(!$this->tieneSimilares($repuesto->id)) continue;
        
                $rep = new oferta_catalogo;
        
                $foto = repuestofoto::where('id_repuestos',$repuesto->id)->first();
        
                if(!$foto){
                    $foto = repuestofoto::where('id_repuestos',62816)->first();
                }
        
                $rep->id = $repuesto->id;
                $rep->codigo_interno = $repuesto->codigo_interno;
                $rep->descripcion = $repuesto->descripcion;
                $rep->urlfoto = $foto->urlfoto;
                $rep->precio_venta = $repuesto->precio_actualizado ?? $repuesto->precio_venta;
                $rep->precio_actualizado = $repuesto->precio_actualizado;
                $rep->descuento = $repuesto->descuento;
                $rep->marcarepuesto = $repuesto->marcarepuesto;
                $rep->nombrefamilia = $repuesto->nombrefamilia;
                $rep->stock_actual = $repuesto->stock_total;
        
                $repuestos_[] = $rep;
            }
        
        
            $descuentos = descuento::select(
                    'descuentos.*',
                    'familias.nombrefamilia'
                )
                ->join('familias','descuentos.id_familia','familias.id')
                ->where('descuentos.activo',1)
                ->where('id_local','<>',1)
                ->get();
        
        
            $user = Auth::user();
        
            return [$repuestos_, $descuentos, $user];
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function dame_ofertas_totem(){
        $hoy = getdate();
        $dia = $hoy['mday'];
        $mes = $hoy['mon'];
        $year = $hoy['year'];
 
        $fecha = $year.'-'.$mes.'-'.$dia;
       
         $ofertas = oferta_pagina_web::select('ofertas_pagina_web.*','repuestos.descripcion','repuestos.fecha_actualiza_precio')
                                     ->join('repuestos','ofertas_pagina_web.id_repuesto','repuestos.id')
                                     ->where('ofertas_pagina_web.activo',1)
                                     ->whereIn('ofertas_pagina_web.local',[4,5,6])
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

    /**
     * Determina si un producto tiene características de ser complementario
     */
    private function esProductoComplementario($descripcion)
    {
        $descripcion_lower = mb_strtolower($descripcion);
        
        // Palabras clave que indican lateralidad o posición
        $palabras_clave = [
            'izquierdo', 'izquierda', 'izq', 'left', 'lh',
            'derecho', 'derecha', 'der', 'right', 'rh',
            'delantero', 'delantera', 'del', 'front', 'ant', 'anterior',
            'trasero', 'trasera', 'tras', 'rear', 'post', 'posterior',
            'superior', 'sup', 'upper', 'top',
            'inferior', 'inf', 'lower', 'bottom'
        ];
        
        foreach($palabras_clave as $palabra){
            if(strpos($descripcion_lower, $palabra) !== false){
                return true;
            }
        }
        
        return false;
    }

    /**
     * Busca el complemento de un producto (ej: si es izquierdo, busca el derecho)
     */
    private function buscarComplemento($repuesto, $todosRepuestos)
    {
        $descripcion = mb_strtolower($repuesto->descripcion);
        
        // Definir pares de complementos
        $pares = [
            ['izquierdo', 'derecho'],
            ['izquierda', 'derecha'],
            ['izq', 'der'],
            ['left', 'right'],
            ['lh', 'rh'],
            ['delantero', 'trasero'],
            ['delantera', 'trasera'],
            ['del', 'tras'],
            ['front', 'rear'],
            ['ant', 'post'],
            ['anterior', 'posterior'],
            ['superior', 'inferior'],
            ['sup', 'inf'],
            ['upper', 'lower'],
            ['top', 'bottom']
        ];
        
        $palabra_actual = null;
        $palabra_complemento = null;
        
        // Identificar qué palabra tiene y cuál sería su complemento
        foreach($pares as $par){
            if(strpos($descripcion, $par[0]) !== false){
                $palabra_actual = $par[0];
                $palabra_complemento = $par[1];
                break;
            }
            if(strpos($descripcion, $par[1]) !== false){
                $palabra_actual = $par[1];
                $palabra_complemento = $par[0];
                break;
            }
        }
        
        if(!$palabra_actual || !$palabra_complemento){
            return null;
        }
        
        // Crear la descripción esperada del complemento
        $descripcion_complemento = str_replace($palabra_actual, $palabra_complemento, $descripcion);
        
        // Buscar en todos los repuestos uno que coincida
        foreach($todosRepuestos as $posible_complemento){
            if($posible_complemento->id == $repuesto->id){
                continue; // Saltar el mismo producto
            }
            
            $desc_posible = mb_strtolower($posible_complemento->descripcion);
            
            // ============= CRITERIOS DE EMPAREJAMIENTO =============
            
            // CRITERIO 1: Misma marca de repuesto (obligatorio)
            if($posible_complemento->marcarepuesto != $repuesto->marcarepuesto){
                continue;
            }
            
            // CRITERIO 2: Misma familia de producto (obligatorio)
            if($posible_complemento->nombrefamilia != $repuesto->nombrefamilia){
                continue;
            }
            
            // CRITERIO 3: Debe contener la palabra complementaria (izq->der, del->tras, etc)
            if(strpos($desc_posible, $palabra_complemento) === false){
                continue;
            }
            
            // CRITERIO 4: Similitud en descripción (al menos 70%)
            // Removemos las palabras de posición para comparar el resto
            $desc_limpia_1 = str_replace([$palabra_actual, $palabra_complemento], '', $descripcion);
            $desc_limpia_2 = str_replace([$palabra_actual, $palabra_complemento], '', $desc_posible);
            
            similar_text($desc_limpia_1, $desc_limpia_2, $porcentaje);
            
            if($porcentaje < 70){
                continue;
            }
            
            // CRITERIO 5: Precio similar (±30% de diferencia) - opcional pero recomendado
            $precio_original = floatval($repuesto->precio_venta);
            $precio_complemento = floatval($posible_complemento->precio_venta);
            
            if($precio_original > 0 && $precio_complemento > 0){
                $diferencia_porcentual = abs($precio_original - $precio_complemento) / $precio_original * 100;
                
                // Si la diferencia de precio es mayor al 30%, es menos confiable pero lo aceptamos
                // (algunos complementos pueden tener pequeñas variaciones de precio)
                if($diferencia_porcentual > 30){
                    // Aún así podría ser válido, solo bajamos la confianza
                    // pero lo aceptamos si cumple los demás criterios
                }
            }
            
            // Si llegó aquí, cumple todos los criterios principales
            return $posible_complemento;
        }
        
        return null;
    }

    /**
     * Agrupa productos complementarios para mostrar en formato de 2 columnas
     * Retorna los repuestos con toda su información más propiedades de agrupamiento
     */
    private function agruparProductosComplementarios($repuestos)
    {
        $procesados = [];
        $resultado = [];
        
        foreach($repuestos as $index => $repuesto){
            // Si ya fue procesado, saltar
            if(in_array($repuesto->id, $procesados)){
                continue;
            }
            
            // Agregar propiedades por defecto
            $repuesto->tiene_complemento = false;
            $repuesto->id_complemento = null;
            $repuesto->posicion_en_par = null; // 'izquierdo', 'derecho', 'delantero', 'trasero', etc.
            
            // Verificar si es un producto complementario
            if($this->esProductoComplementario($repuesto->descripcion)){
                // Buscar su complemento
                $complemento = $this->buscarComplemento($repuesto, $repuestos);
                
                if($complemento){
                    // Marcar que tiene complemento
                    $repuesto->tiene_complemento = true;
                    $repuesto->id_complemento = $complemento->id;
                    $repuesto->posicion_en_par = $this->detectarPosicion($repuesto->descripcion);
                    
                    // Agregar el producto principal
                    $resultado[] = $repuesto;
                    
                    // Configurar y agregar el complemento
                    $complemento->tiene_complemento = true;
                    $complemento->id_complemento = $repuesto->id;
                    $complemento->posicion_en_par = $this->detectarPosicion($complemento->descripcion);
                    $resultado[] = $complemento;
                    
                    // Marcar ambos como procesados
                    $procesados[] = $repuesto->id;
                    $procesados[] = $complemento->id;
                } else {
                    // No se encontró complemento, agregar como individual
                    $resultado[] = $repuesto;
                    $procesados[] = $repuesto->id;
                }
            } else {
                // Producto sin características complementarias
                $resultado[] = $repuesto;
                $procesados[] = $repuesto->id;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Detecta la posición/lateralidad del producto
     */
    private function detectarPosicion($descripcion)
    {
        $descripcion_lower = mb_strtolower($descripcion);
        
        $posiciones = [
            'izquierdo' => ['izquierdo', 'izquierda', 'izq', 'left', 'lh'],
            'derecho' => ['derecho', 'derecha', 'der', 'right', 'rh'],
            'delantero' => ['delantero', 'delantera', 'del', 'front', 'ant', 'anterior'],
            'trasero' => ['trasero', 'trasera', 'tras', 'rear', 'post', 'posterior'],
            'superior' => ['superior', 'sup', 'upper', 'top'],
            'inferior' => ['inferior', 'inf', 'lower', 'bottom']
        ];
        
        foreach($posiciones as $nombre => $variantes){
            foreach($variantes as $variante){
                if(strpos($descripcion_lower, $variante) !== false){
                    return $nombre;
                }
            }
        }
        
        return null;
    }

    public function buscar_por_descripcion($dato){ //busqueda principal
        try {
         
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

            // Detectar si la búsqueda incluye un año (ej: "filtro aire accent 2012")
            $anio_buscado = null;
            foreach($terminos as $t){
                if(preg_match('/^(19|20)\d{2}$/', trim($t))){
                    $anio_buscado = trim($t);
                    break;
                }
            }
            
            $quien_busca=User::find(16)->name;
            $id_buscados=\DB::table('busquedas')->insertGetId(['buscado'=>$desc,'encontrados'=>"INICIO",'fecha_hora_buscado'=>date("Y-m-d H:i:s"),'quien_busca'=>$quien_busca]);
            $conteo=[];
            $conteo["codint"]=$this->buscar_en_codint($buscado_original);

            $conteo["codprov"]=$this->buscar_en_codprov($buscado_original);
            
            $conteo["codalt"]=$this->buscar_en_codAlternador($buscado_sin_guiones);
            
            $conteo["codrec"]=$this->buscar_en_codRectificador($buscado_sin_guiones);
  
          
            // $conteo["codmtr"]=$this->buscar_en_codmotor($buscado_sin_guiones);
            $conteo["codoem"]=$this->buscar_en_codoem($buscado_sin_guiones);
         
            $conteo["codfab"]=$this->buscar_en_codfab($buscado_original); // ERROR Malformed UTF-8 caracters, possibly incorrectly encoded exception. es por la función substr_replace que no admite caracteres UTF-8
            $conteo["descrip"]=$this->buscar_en_descrip($buscado_original,1);
 
            $conteo["solo_descrip"]=$this->buscar_solo_en_descrip($buscado_original);
            
            $repuestos=Collect(); //Colección que juntará todos los resultados a entregar
            $criterio="( ";
    
            
            if($conteo['codint']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codint']['repuestos']);
                $criterio.="codint:".$conteo["codint"]["repuestos"]->count()." ";
            }

            if($conteo['codoem']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codoem']['repuestos']);
                $criterio.="codoem:".$conteo["codoem"]["repuestos"]->count()." ";
            }

            if($conteo['codfab']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codfab']['repuestos']);
                $criterio.="codfab:".$conteo["codfab"]["repuestos"]->count()." ";
            }

            if($conteo['codprov']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codprov']['repuestos']);
                $criterio.="codprov:".$conteo["codprov"]["repuestos"]->count()." ";
            }

            if($conteo['codalt']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codalt']['repuestos']);
                $criterio.="codalt:".$conteo["codalt"]["repuestos"]->count()." ";
            }

            if($conteo['codrec']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['codrec']['repuestos']);
                $criterio.="codrec:".$conteo["codrec"]["repuestos"]->count()." ";
            }

            if($conteo['descrip']){
                $repuestos=$this->revisar($repuestos,$conteo['descrip']);
                $criterio.="algor nuevo:".$conteo["descrip"]->count()." ";
            }

            if($conteo['solo_descrip']['resultado']){
                $repuestos=$this->revisar($repuestos,$conteo['solo_descrip']['repuestos']);
                $criterio.="en_descrip:".$conteo["solo_descrip"]["repuestos"]->count()." ";
            }

            $repuestos_ = [];

            foreach($repuestos as $rep){
                $foto=repuestofoto::where('id_repuestos','=',$rep->id)->first();
                
                // Obtener el nombre del país
                $repuesto_completo = repuesto::select('repuestos.*', 'paises.nombre_pais')
                    ->join('paises', 'repuestos.id_pais', '=', 'paises.id')
                    ->where('repuestos.id', $rep->id)
                    ->first();
                
                if($repuesto_completo){
                    $rep->nombre_pais = $repuesto_completo->nombre_pais;
                }
                
                if(isset($foto)){
                    $rep->urlfoto = $foto->urlfoto;
                }else{
                    $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                    $rep->urlfoto = $foto->urlfoto;
                }

                if($rep->stock_actual > 0 || $rep->stock_actual_dos > 0 || $rep->stock_actual_tres > 0) array_push($repuestos_,$rep);
            }

            // Si la búsqueda incluyó un año, filtrar solo repuestos compatibles con ese año
            if($anio_buscado){
                $repuestos_ = array_values(array_filter($repuestos_, function($r) use ($anio_buscado){
                    return similar::where('id_repuestos', $r->id)
                                  ->where('activo', 1)
                                  ->where('anios_vehiculo', 'LIKE', '%'.$anio_buscado.'%')
                                  ->exists();
                }));
            }

            foreach ($repuestos_ as $r) {
                $oferta = oferta_pagina_web::where('id_repuesto',$r->id)->where('activo',1)->first();
                $familia_dcto = descuento::where('id_familia', $r->id_familia)->where('activo',1)->first();
                if(isset($oferta)){
                    $r->precio_normal = $r->precio_venta;
                    $r->precio_venta = $oferta->precio_actualizado;
                    
                }elseif(!isset($oferta) && isset($familia_dcto)){
                    $r->precio_normal = $r->precio_venta;
                    $r->precio_venta = $r->precio_venta - (($familia_dcto->porcentaje/100) * $r->precio_venta);
                    
                    $r->oferta = 2;
                }else{
                    $r->oferta = 0;
                }
            }
            
            // Agrupar productos complementarios para el tótem
            $repuestos_agrupados = $this->agruparProductosComplementarios($repuestos_);
            
            return $repuestos_agrupados;

            $criterio.=" )";
            if($op==7){
                return $repuestos->count();
            }
          
    
            //$repuestos=$repuestos->sortByDesc("id_familia");
            //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
            $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
                ->where('saldos.saldo', '>', 0)
                ->join('locales', 'saldos.id_local', 'locales.id')
                ->get();
    
    
    
            //Mandamos array con los id_repuestos que tengan fotos
            $tienen_foto=repuestofoto::select('id_repuestos')
                                        ->distinct()
                                        ->get()
                                        ->toArray();
    
            $arreglo = [];
           
            foreach ($repuestos as $repuesto) {
                    $primera_foto_repuesto = repuestofoto::select('repuestos_fotos.urlfoto','repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                                ->where('repuestos.id',$repuesto->id)
                                                ->join('repuestos','repuestos.id','repuestos_fotos.id_repuestos')
                                                ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                                ->join('paises','repuestos.id_pais','paises.id')
                                                ->get();
                    if($primera_foto_repuesto->count() > 0){
                        array_push($arreglo,$primera_foto_repuesto[0]);
                    }else{
                        try {
                            //Si no tiene foto buscamos el repuesto con sus principales datos y le agregamos una url personalizada.
                            $repuesto_ = repuesto::select('repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                                ->where('repuestos.id',$repuesto->id)
                                                ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                                ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                                ->join('paises','repuestos.id_pais','paises.id')
                                                ->first();
                            $repuesto_->urlfoto = 'fotozzz/sinfoto.png';
                            array_push($arreglo,$repuesto_);
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                        
                    }
                
            }
            
            $stocks_repuesto = [];
    
            foreach($repuestos as $repuesto){
                $stock_bodega = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local','responsables.name as responsable')
                                            ->join('locales', 'repuestos.local_id', 'locales.id')
                                            ->leftjoin('users as responsables','repuestos.id_responsable','responsables.id')
                                            ->where('repuestos.id',$repuesto->id)
                                            ->where('repuestos.local_id',1)
                                            ->first();
    
                $stock_bodega_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local','responsables.name as responsable')
                                            ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                            ->leftjoin('users as responsables','repuestos.id_responsable_dos','responsables.id')
                                            ->where('repuestos.id',$repuesto->id)
                                            ->where('repuestos.local_id_dos',1)
                                            ->first();
                                            
                $stock_tienda = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local','responsables.name as responsable')
                                            ->join('locales', 'repuestos.local_id', 'locales.id')
                                            ->leftjoin('users as responsables','repuestos.id_responsable','responsables.id')
                                            ->where('repuestos.id',$repuesto->id)
                                            ->where('repuestos.local_id',3)
                                            ->first();
       
    
                $stock_tienda_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local','responsables.name as responsable')
                                            ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                            ->leftjoin('users as responsables','repuestos.id_responsable_dos','responsables.id')
                                            ->where('repuestos.id',$repuesto->id)
                                            ->where('repuestos.local_id_dos',3)
                                            ->first();
    
                $stock_cm_tercera_ubicacion = repuesto::select('repuestos.stock_actual_tres as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local','responsables.name as responsable')
                                        ->join('locales', 'repuestos.local_id_tres', 'locales.id')
                                        ->leftjoin('users as responsables','repuestos.id_responsable_tres','responsables.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id_tres',4)
                                        ->first();
                
                array_push($stocks_repuesto,$stock_bodega,$stock_bodega_segunda_ubicacion,$stock_tienda, $stock_tienda_segunda_ubicacion,$stock_cm_tercera_ubicacion);
            }
    
    
            $desde = 'd';
            $permiso = permissions_detail::where('usuarios_id',16)->where('path_ruta','/repuesto/modificar-precio')->first();
                if($permiso){
                    $permiso_modificar = true;
                }else{
                    $permiso_modificar = false;
                }
    
            $permiso_ = permissions_detail::where('usuarios_id',16)->where('path_ruta','/actualizar-precio')->first();
                if($permiso_){
                    $permiso_actualizar = true;
                }else{
                    $permiso_actualizar = false;
                }

            $familias_descuento = descuento::all();
     
            foreach ($repuestos as $r) {
                $oferta = oferta_pagina_web::where('id_repuesto',$r->id)->where('activo',1)->first();
                //El idlocal 2 corresponde a ofertas para el sitio web
                $familia_dcto = descuento::where('id_familia', $r->id_familia)->where('id_local','<>',2)->where('activo',1)->first();
                if(isset($oferta)){
                    $r->precio_venta = $oferta->precio_actualizado;
                }elseif(!isset($oferta) && isset($familia_dcto)){
                    
                    $r->precio_venta = $r->precio_venta - (($familia_dcto->porcentaje/100) * $r->precio_venta);
                    $r->oferta = 2;
                }else{
                    $r->oferta = 0;
                }
                
                //Llevamos a la vista la ultima fecha de modificación de alguno de los 3 stock que tenga el repuesto.
                if($r->fecha_actualizacion_stock > $r->fecha_actualizacion_stock_dos && $r->fecha_actualizacion_stock > $r->fecha_actualizacion_stock_tres){
                        $fecha_ultima = $r->fecha_actualizacion_stock;
                        $r->fecha_ultima = $fecha_ultima;
                }elseif($r->fecha_actualizacion_stock_dos > $r->fecha_actualizacion_stock && $r->fecha_actualizacion_stock_dos > $r->fecha_actualizacion_stock_tres){
                        $fecha_ultima = $r->fecha_actualizacion_stock_dos;
                        $r->fecha_ultima = $fecha_ultima;
                }elseif($r->fecha_actualizacion_stock_tres > $r->fecha_actualizacion_stock && $r->fecha_actualizacion_stock_tres > $r->fecha_actualizacion_stock_dos){
                        $fecha_ultima = $r->fecha_actualizacion_stock_tres;
                        $r->fecha_ultima = $fecha_ultima;
                }else{
                        $fecha_ultima = $r->fecha_actualizacion_stock;
                        $r->fecha_ultima = $fecha_ultima;
                }
            }
            
            foreach ($arreglo as $r) {
                $oferta = oferta_pagina_web::where('id_repuesto',$r->id)->where('activo',1)->first();
                $familia_dcto = descuento::where('id_familia', $r->id_familia)->where('activo',1)->first();
                if(isset($oferta)){
                    $r->precio_venta = $oferta->precio_actualizado;
                }elseif(!isset($oferta) && isset($familia_dcto)){
                    
                    $r->precio_venta = $r->precio_venta - (($familia_dcto->porcentaje/100) * $r->precio_venta);
                    $r->oferta = 2;
                }else{
                    $r->oferta = 0;
                }
            }

            
            return $repuestos;
    
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

public function buscar_solo_en_descrip($buscado){
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $encontrados=Collect();

        $sql="";
        for($i=0;$i<count($terminos);$i++){
            $sql.="(repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ?) AND ";
        }
        $sql=$sql." repuestos.activo=?";

        $param=[];
        for($i=0;$i<count($terminos);$i++){
            $al_inicio=$terminos[$i]." %";
            $en_medio="% ".$terminos[$i]." %";
            $al_final="% ".$terminos[$i];
            array_push($param,$al_inicio);
            array_push($param,$en_medio);
            array_push($param,$al_final);
        }
        array_push($param,1);
        $encontrados=repuesto::whereRaw($sql,$param)
                        ->where('repuestos.codigo_OEM_repuesto','<>','XPRESS')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;

    }

public function buscar_en_codint($buscado){
    $resp['resultado']=false;
    $terminos=explode(" ",$buscado);
    /*

    $terminos_encontrados=[];
    for($i=0;$i<count($terminos);$i++){
        $hay=repuesto::where('repuestos.codigo_interno',$terminos[$i])
        ->where('repuestos.activo',1)
        ->count();
        if($hay>0){
            array_push($terminos_encontrados,$terminos[$i]);
        }
    }
    if(count($terminos_encontrados)>0){
        $sql="";
        for($i=0;$i<count($terminos_encontrados);$i++){
            $sql.="repuestos.codigo_interno=? AND ";
        }
        $sql=$sql." repuestos.activo=?";
        $param=[];
        for($i=0;$i<count($terminos_encontrados);$i++){
            $buscado="%".$terminos_encontrados[$i]."%";
            array_push($param,$buscado);
        }
        array_push($param,1);

        $encontrados=repuesto::whereRaw($sql,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        */
        $encontrados=Collect();
        if(count($terminos)==1){
            $encontrados=repuesto::where('repuestos.codigo_interno',$buscado)
                        ->where('repuestos.activo',1)
                        ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

    return $resp;
}

public function buscar_en_codprov($buscado){
    $buscado=trim($buscado);
    $resp['resultado']=false;
    $terminos=explode(" ",$buscado);


    $encontrados=Collect();
    if(count($terminos)==1){
        $encontrados=repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
    }
    if($encontrados->count()>0){
        $resp['resultado']=true;
        $resp['repuestos']=$encontrados;
    }

    return $resp;
    /*
    $terminos_encontrados=[];
    for($i=0;$i<count($terminos);$i++){
        $hay=repuesto::where('cod_repuesto_proveedor',$terminos[$i])
        ->where('repuestos.activo',1)
        ->count();
        if($hay>0){
            array_push($terminos_encontrados,$terminos[$i]);
        }
    }
    if(count($terminos_encontrados)>0){
        $sql="";
        for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="repuestos.cod_repuesto_proveedor LIKE ? AND ";
        }
        $sql=$sql." repuestos.activo=?";
        $param=[];
        for($i=0;$i<count($terminos_encontrados);$i++){
            $buscado="%".$terminos_encontrados[$i]."%";
            array_push($param,$buscado);
        }
        array_push($param,1);

        $encontrados=repuesto::whereRaw($sql,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
    }
    */

}
    
public function buscar_en_codAlternador($buscado){
    $resp['resultado']=false;
    $buscado=str_replace("-","",$buscado);
    $terminos=explode(" ",$buscado);
   
    $encontrados=Collect();
    if(count($terminos)==1){
        $data = regulador_voltaje::select('regulador_voltaje.id_repuesto')
                    ->where('regulador_voltaje.alternador','LIKE','%'.$buscado.'%')
                    ->where('regulador_voltaje.activo',1)
                    ->get()
                    ->toArray();
                   
        if(count($data)>0){
            $encontrados=repuesto::wherein('repuestos.id',$data)
                ->where('repuestos.activo',1)
                ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                ->leftjoin('paises', 'repuestos.id_pais', 'paises.id')
                ->leftjoin('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->leftjoin('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();

        }
    }
    if($encontrados->count()>0){
        $resp['resultado']=true;
        $resp['repuestos']=$encontrados;
    }
    return $resp;
}

public function buscar_en_codRectificador($buscado){
    $resp['resultado']=false;
    $buscado=str_replace("-","",$buscado);
    $terminos=explode(" ",$buscado);
    
    $encontrados=Collect();
    if(count($terminos)==1){
        $data = regulador_voltaje::select('id_repuesto')
                    ->where('regulador_voltaje.rectificador','LIKE','%'.$buscado.'%')
                    ->where('regulador_voltaje.activo',1)
                    ->get()
                    ->toArray();
        if(count($data)>0){
            $encontrados=repuesto::wherein('repuestos.id',$data)
                ->where('repuestos.activo',1)
                ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();

        }
    }
    if($encontrados->count()>0){
        $resp['resultado']=true;
        $resp['repuestos']=$encontrados;
    }
    return $resp;
}

public function buscar_en_codoem($buscado){
    try {

        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);
        /*
    
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=oem::where('codigo_oem',$terminos[$i])->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
    
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="oems.codigo_oem LIKE ? AND ";
            }
            $sql=$sql." oems.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);
    
            $oem = oem::select('id_repuestos')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
    
            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }
    
    
    
        }
        */
        $encontrados=Collect();
        if(count($terminos)==1){
            $oem = oem::select('id_repuestos')
                        ->where('oems.codigo_oem','LIKE','%'.$buscado.'%')
                        ->where('oems.activo',1)
                        ->get()
                        ->toArray();
            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
    
            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    } catch (\Exception $e) {
        //throw $th;
        return $e->getMessage();
    }

}

public function buscar_en_codfab($buscado){
    //repuestos_fabricantes.codigo_fab

    $encontró=false;
    $resp['resultado']=false;
    $terminos=explode(" ",$buscado);
    $encontrados=Collect();
    //Asumiendo que ingresó solo un término, lo buscamos con el algoritmo del guion
    if(count($terminos)==1){
        $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                            ->where('repuestos.activo',1)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                            ->get();
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
            $encontró=true;
        }else{
            $guion="-";
            $buscado_original=$buscado;
            $buscado_sin_guiones= str_replace("-","",$buscado);
            $nf=strpos($buscado_original,$guion);
            if($nf===false){ //no hay guión, le va poniendo el guión en diferentes posiciones para buscarlo
                for($i=1;$i<strlen($buscado_original);$i++){
                    $buskado = substr_replace($buscado_original, $guion, $i, 0); //ERROR al usar caracteres UTF-8 en las búsquedas, ejm PIÑ10
                    $numfil=fabricante::where('repuestos_fabricantes.codigo_fab','LIKE','%'.$buskado.'%')
                                        ->where('repuestos_fabricantes.activo',1)
                                        ->count();
                    if($numfil>0){
                        $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buskado. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
                        if($encontrados->count()>0){
                            $resp['resultado']=true;
                            $resp['repuestos']=$encontrados;
                            $encontró=true;
                        }
                        break;
                    }
                }

            }else{ //hay guión o guiones, lo busca tal cual
                $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                    $encontró=true;
                }else{ //no hay tal cual, lo busca sin guiones
                    $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
                    if($encontrados->count()>0){
                        $resp['resultado']=true;
                        $resp['repuestos']=$encontrados;
                        $encontró=true;
                    }
                }
            }

        }



    } // fin 1 término

    return $resp;

    /*
    if($encontró) return $resp;

    //segunda parte: la búsqueda tiene más de 1 término
    $terminos_encontrados=[];
    for($i=0;$i<count($terminos);$i++){
        $hay=fabricante::where('repuestos_fabricantes.codigo_fab','%'.$terminos[$i].'%')
                        ->where('repuestos_fabricantes.activo',1)
                        ->count();
        if($hay>0){
            array_push($terminos_encontrados,$terminos[$i]);
        }
    }
    if(count($terminos_encontrados)>0){
        $sql="";
        for($i=0;$i<count($terminos_encontrados);$i++){
            $sql.="repuestos_fabricantes.codigo_fab LIKE ? AND ";
        }
        $sql=$sql." repuestos_fabricantes.activo=?";
        $param=[];
        for($i=0;$i<count($terminos_encontrados);$i++){
            $buscado="%".$terminos_encontrados[$i]."%";
            array_push($param,$buscado);
        }
        array_push($param,1);

        $fab = fabricante::select('id_repuestos')
                ->whereRaw($sql,$param) //->toSql();
                ->get()
                ->toArray();
        if(count($fab)>0){
            $encontrados=repuesto::wherein('repuestos.id',$fab)
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }
        }



    }

    return $resp;
    */
}

private function revisar($repuestos,$encontrados){
    $huy=$encontrados->where('codigo_interno','CZA50')->count();
    if($repuestos->count()==0){
        $repuestos=$encontrados;
    }else{
        $repuestos=$repuestos->merge($encontrados);
    }
    //return $repuestos->unique();
    return $repuestos;
}

public function transferir_carrito_completo_totem($cliente_id, $numero_carrito, $nombre_cliente = null, $rut = null, $telefono = null)
{
    try {

        $carrito_compra = carrito_virtual_detalle::where('carrito_numero', $numero_carrito)->get();

        $el_id_usuario = 5;
        $id = 407; // cajero totem

        // Si viene nombre de cliente, es una cotización personalizada
        if($nombre_cliente) {
            $titulo = 'COTIZACIÓN: '.$nombre_cliente;
        } else {
            $titulo = 'TOTEM '.$numero_carrito;
        }

        // Calcular totales del carrito para la cotización
        $total_carrito = $carrito_compra->sum('total_item');
        $neto_carrito  = (int) round($total_carrito / 1.19);
        $iva_carrito   = $total_carrito - $neto_carrito;

        // Si es cotización, crear cabecera
        $cotizacion_id = null;
        $num_cot = null;
        if ($nombre_cliente) {
            $num_cot = $this->dame_correlativo('cotizacion') + 1;

            $c = new cotizacion;
            $c->num_cotizacion    = $num_cot;
            $c->nombre_cotizacion = $nombre_cliente;
            $c->fecha_emision     = Carbon::today()->toDateString();
            $c->fecha_expira      = Carbon::today()->addDays(21)->toDateString();
            $c->id_cliente        = 0; // cliente potencial no registrado
            $c->neto              = $neto_carrito;
            $c->iva               = $iva_carrito;
            $c->total             = $total_carrito;
            $c->activo            = 2;
            $c->usuarios_id       = $el_id_usuario;
            $c->save();

            $cotizacion_id = $c->id;
            $this->actualizar_correlativo('cotizacion', $num_cot);

            // Guardar cliente potencial
            $cp = new cliente_potencial;
            $cp->rut          = $rut ?? '';
            $cp->nombre       = $nombre_cliente;
            $cp->telefono     = $telefono;
            $cp->cotizacion_id = $cotizacion_id;
            $cp->save();
        }

        foreach($carrito_compra as $cc)
        {
            $rep = repuesto::find($cc->repuesto_id);

            // 🔹 determinar local con mayor stock
            $stock1 = $rep->stock_actual ?? 0;
            $stock2 = $rep->stock_actual_dos ?? 0;
            $stock3 = $rep->stock_actual_tres ?? 0;

            $mayor = max($stock1, $stock2, $stock3);

            if ($mayor == $stock1) {
                $id_local = 1;
            } elseif ($mayor == $stock2) {
                $id_local = 3;
            } else {
                $id_local = 4;
            }

            // Si NO es cotización, guardar carrito transferido
            if (!$cotizacion_id) {
                $ct = new carrito_transferido;
                $ct->usuarios_id = $el_id_usuario;
                $ct->cajeros_id = $id;
                $ct->cliente_id = $cliente_id;
                $ct->item = $cc->item;
                $ct->id_repuestos = $cc->repuesto_id;
                $ct->id_local = $id_local; // 👈 aquí cambia
                $ct->id_unidad_venta = $rep->id_unidad_venta;
                $ct->cantidad = $cc->cantidad;
                $ct->pu = $cc->pu;
                $ct->subtotal_item = $cc->subtotal_item;
                $ct->descuento_item = $cc->descuento_item;
                $ct->total_item = $cc->total_item;
                $ct->titulo = $titulo; // 👈 Ahora usa el que se generó arriba
                $ct->save();
            }

            // Si es cotización, guardar detalle
            if ($cotizacion_id) {
                $cd = new cotizacion_detalle;
                $cd->id_cotizacion   = $cotizacion_id;
                $cd->id_repuestos    = $cc->repuesto_id;
                $cd->id_unidad_venta = $rep->id_unidad_venta;
                $cd->id_local        = $id_local;
                $cd->precio_venta    = $cc->pu;
                $cd->cantidad        = $cc->cantidad;
                $cd->subtotal        = $cc->subtotal_item;
                $cd->descuento       = $cc->descuento_item;
                $cd->total           = $cc->total_item;
                $cd->activo          = 1;
                $cd->usuarios_id     = $el_id_usuario;
                $cd->save();
            }
        }

        if ($cotizacion_id) {
            $oferta = $this->es_oferta_totem($cotizacion_id);
            return json_encode(['estado' => 'OK', 'cotizacion_id' => $cotizacion_id, 'num_cotizacion' => $num_cot, 'oferta' => $oferta]);
        }

        return 'OK';

    } catch (\Exception $e) {

        return $e->getMessage();

    }
}

private function es_oferta_totem($cotizacion_id)
{
    $det = cotizacion_detalle::select(
                'repuestos.id as idrepuesto',
                'repuestos.oferta',
                'cotizaciones_detalle.*',
                'repuestos.id_familia'
            )
            ->where('cotizaciones_detalle.id_cotizacion', $cotizacion_id)
            ->join('repuestos', 'cotizaciones_detalle.id_repuestos', 'repuestos.id')
            ->get();

    foreach ($det as $d) {
        if ($d->oferta == 1) {
            $oferta = oferta_pagina_web::where('id_repuesto', $d->idrepuesto)->first();
            if ($oferta) {
                $oferta->hasta = Carbon::parse($oferta->hasta)->format('d-m-Y');
                return $oferta;
            }
        }
    }

    foreach ($det as $d) {
        $rep = repuesto::find($d->idrepuesto);
        $descuento = descuento::where('id_familia', $rep->id_familia)->first();
        if ($descuento) {
            $descuento->desde = Carbon::parse($descuento->desde)->format('d-m-Y');
            $descuento->hasta = Carbon::parse($descuento->hasta)->format('d-m-Y');
            return $descuento;
        }
    }

    return false;
}

public function borrar_carrito_totem($cual,$numero_carrito){
    if($cual == 'actual'){
        $carrito = carrito_virtual::where('numero_carrito',$numero_carrito)->first();
        $detalle = carrito_virtual_detalle::where('carrito_numero',$numero_carrito)->get();
        $carrito->activo = 0;
        
        if($carrito->save()){
            return 'OK';
        }

    }
}

public function actualizar_cantidad_carrito_totem(Request $request){
  
    $item = $request->input('idrep');
    $cantidad = $request->input('cantidad');
    $numero_carrito = $request->input('numero_carrito');

    // revisar si existe stock suficiente para la cantidad solicitada
    $repuesto = repuesto::find($item);
    $id_familia = $repuesto->id_familia;
    if (in_array((int) $id_familia, [344, 345], true) && !$this->cantidadAmortiguadorValida($repuesto, $cantidad)) {
        return "amortiguador_unidad_lado";
    }
    // verificar que si la familia es 24 (bujias) la cantidad minima a agregar sea 3
    if($id_familia == 24 || $id_familia == 29){
        if($cantidad < 3){
            return "bujia_minimo";
        }
    }
    // verificar que si la familia es 532 (valvulas) la cantidad minima a agregar sea 4
    if($id_familia == 532 || $id_familia == 78){
        if($cantidad < 4){
            return "valvula_minimo";
        }
    }

    // verificar que si la familia es 374 (disco freno delantero) o 375 (disco freno trasero) la cantidad sea multiplo de 2
    if($id_familia == 374 || $id_familia == 375){
        if($cantidad % 2 != 0){
            return "disco_freno_multiplo";
        }
    }

    // verificar que si la familia es 362 (retenes de valvulas)  la cantidad minima a agregar sea 8
    if($id_familia == 362 || $id_familia == 392){
        if($cantidad % 8 != 0){
            return "retene_valvula_multiplo";
        }
    }

    // verificar que si la familia es 92 (TAQUIE VALVULA) la cantidad minima a agregar sea 16
    if($id_familia == 92){
        if($cantidad < 16){
            return "taquie_valvula_minimo";
        }
    }
    $stock_bodega = $repuesto->stock_actual ?? 0;
    $stock_bodega_dos = $repuesto->stock_actual_dos ?? 0;
    $stock_bodega_tres = $repuesto->stock_actual_tres ?? 0; 

    $mayor = max($stock_bodega, $stock_bodega_dos, $stock_bodega_tres);


    if ($mayor == $stock_bodega) {
        // preguntamos si la cantidad es mayor al stock disponible
        if ($cantidad > $stock_bodega) {
            return 'STOCK_INSUFICIENTE';
        }
    } elseif ($mayor == $stock_bodega_dos) {
        if ($cantidad > $stock_bodega_dos) {
            return 'STOCK_INSUFICIENTE';
        }
    } else {
        if ($cantidad > $stock_bodega_tres) {
            return 'STOCK_INSUFICIENTE';
        }   
    }

    $detalle = carrito_virtual_detalle::where('carrito_numero', $numero_carrito)->where('repuesto_id', $item)->first();

    if($detalle){
        $detalle->cantidad = $cantidad;
        $detalle->subtotal_item = $detalle->pu * $cantidad;
        $detalle->total_item = $detalle->subtotal_item - $detalle->descuento_item;
        if($detalle->save()){
            return 'OK';
        }
    }

    return 'ERROR';

}
}


