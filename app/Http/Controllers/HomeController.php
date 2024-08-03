<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Debugbar;
use Session;
use Auth;
use App\User;
use App\user_server;
use App\parametro;
use App\oem;
use App\Imports\OemsImport;
use App\Imports\VehiclesImport;
use Illuminate\Support\Facades\Hash;
use MaatWebsite\Excel\Facade\Excel;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            
            $this->cargar_parametros_sistema();
            $ventas_controlador = new ventas_controlador();
            
            //Solo los administradores pueden ver los pedidos pendientes
            if(Auth::user()->rol->nombrerol == "Administrador"){
                $pedidos = $ventas_controlador->revisar_pedidos();
                $terrestres = $pedidos[0];
                $aereos = $pedidos[1];
                $fechaLimiteTerrestre = $pedidos[2];
                $fechaLimiteAereo = $pedidos[3];
            }
            
            
            $clientes_controlador = new clientes_controlador();
            
            $consignaciones = $clientes_controlador->dame_vales_consignacion();
            $rc = [];
            $hoy = Carbon::now()->format('d-m-Y');
           
            foreach($consignaciones as $c){
                
                $fecha = Carbon::parse($c->fecha_emision);
                $diferenciaEnDias = $fecha->diffInDays($hoy);
               
                if($diferenciaEnDias < 7 ){
                    array_push($rc,$c);
                }
            }
            
            if(Auth::user()->rol->nombrerol == "Administrador"){
                return view('home',[
                    'terrestres' => $terrestres,
                    'aereos' => $aereos,
                    'consignaciones' => $rc,
                    'fechaLimiteTerrestre' => Carbon::parse($fechaLimiteTerrestre)->format('d-m-Y'),
                    'fechaLimiteAereo' => Carbon::parse($fechaLimiteAereo)->format('d-m-Y'),
                    'hoy' => $hoy
                ]);
            }else{
                return view('home');
            }
            
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
    }

    public function login_server(){
        return view('index');
    }

    public function dame_sesion()
    {
       return Session::all();
    }

    public function autenticado(){
        return Auth::check();
    }

    public function dame_clave(Request $r){
        if($r->clave=='pan831'){
            return "OK";
        }else{
            return "NEGATIVO";
        }
    }

    public function dame_clave_descuento(Request $r){
        if($r->clave=='rique831'){
            return "OK";
        }else{
            return "NEGATIVO";
        }
    }

    public function cambiar_passwords(){
        return view('cambiar_claves');
    }

    public function form_cambiar_clave()
    {
        return view('auth.passwords.cambiar')->with('mensaje','');
    }

    public function form_cambiar_clave_usuario($id){
        $user = User::find($id);

        return view('auth.passwords.cambiarusuario',['user' => $user,'mensaje' => '']);
    }

    public function cambiar_clave(Request $r)
    {
        $reglas=['antiguaclave'=>'required|password',
                        'nuevaclave'=>'required|min:5',
                       'repetirnuevaclave'=>'same:nuevaclave'
                    ];

        $mensajes=['antiguaclave.required'=>'Ingrese su clave anterior',
                            'antiguaclave.password'=>'No es la clave actual',
                            'nuevaclave.required'=>'Ingrese su nueva clave',
                            'nuevaclave.min'=>'Ingrese mínimo 5 caracteres',
                            'repetirnuevaclave.same'=>'Nueva clave vacia o no coincide'
        ];

        $this->validate($r,$reglas,$mensajes);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($r->nuevaclave)]);

        return view('auth.passwords.cambiar')->with('mensaje','Cambio de Clave Correcto...');
    }

    public function cambiar_clave_usuario(Request $r)
    {
        $reglas=['nuevaclave'=>'required|min:5',
                'repetirnuevaclave'=>'same:nuevaclave'];

        $mensajes=['nuevaclave.required'=>'Ingrese su nueva clave',
                   'nuevaclave.min'=>'Ingrese mínimo 5 caracteres',
                   'repetirnuevaclave.same'=>'Nueva clave vacia o no coincide'];

            $this->validate($r,$reglas,$mensajes);

            User::find($r->userId)->update(['password'=> Hash::make($r->nuevaclave)]);

            return redirect('home');
        

        
    }
    

    private function cargar_parametros_sistema()
    {
        $user = Auth::user();
        Session::put('usuario_id',$user->id);
        Session::put('local',1); //FALTA: tabla locales id = 1; El usuario debe estar "amarrado" a un local,modificar despues.
        Session::put('acceso','SI');
        Session::put('usuario_nombre',$user->name);
        $email=Auth::user()->email;
        Session::put('usuario_email',$email);
        if($email=='josefranciscott@gmail.com' || $email=='jesus@gmail.com'){
            Session::put('rol','S');
        }else if($email=='llancor.ltda@gmail.com'){
            Session::put('rol','C');
        }else if($email=='maralfa14@gmail.com' || $email=='gsus@gsus.cl'){
            Session::put('rol','J');
        }else{
            Session::put('rol','Z');
        }

        if(Auth::user()->dame_permisos_inventario()->count() > 0){
            foreach (Auth::user()->dame_permisos_inventario() as $permiso) {
                if($permiso->path_ruta == '/ofertas_web'){
                    Session::put('ofertas','SI');
                }
            }
        }

        Session::put('token',Auth::user()->token);
        $parametros=parametro::select('codigo','valor')->get();
        foreach($parametros as $p)
        {
            Session::put($p->codigo,$p->valor);
        }
    }
    
    public function importExcel(Request $request){
        $file = $request->file('excel');
        return var_dump($file);
        Excel::import(new OemsImport, $file);
        
        return back()->with('message','Importancion completada con éxito');
    }

    public function prueba(){
        return view('manten.prueba');
    }

    public function validar_usuario(){
        $user = user_server::all();
        return var_dump($user);
    }

}
