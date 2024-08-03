<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\user_server;
use App\registro_login;

class servidorlocalcontrolador extends Controller
{
    public function login_server(){
        return view('index');
    }

    public function setUser(Request $req){
        $usuario = $req->user;
        $password = $req->password;
    
        $data = [];
        try {
            //code...
            $user = user_server::where('user',$usuario)->first();
            //return $user;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        
        $users = user_server::where('activo','1')->get();
        $hash = $user->password;
        $verify = password_verify($password, $hash);
        if($verify){
            if($usuario === 'meguren'|| $usuario === 'frojo' || $usuario == 'jtroncoso'){
                try {
                    
                    //Cada vez que se inicie sesión se guarda el registro
                        $registro_login = new registro_login;
                        $registro_login->usuario_id_servidor = $user->id;
                        $registro_login->fecha_login = date("Y-m-d H:i:s");
                        $registro_login->fecha_ingreso = date("Y-m-d");
                        $registro_login->direccion_ip = $req->ip();
                        $registro_login->save();
                    
    
                    session(['usuario' => 'admin']);
                    return redirect()->route('login');
                    
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
                
            }else{
                try {
                    foreach($users as $u){
                        if($u->user == $usuario){
                            $hash = $u->password;
                            $verify = password_verify($password, $hash);
                            
                            if($verify){
                                //Cada vez que se inicie sesión se guarda el registro
                                    $registro_login = new registro_login;
                                    $registro_login->usuario_id_servidor = $user->id;
                                    $registro_login->fecha_ingreso = date("Y-m-d");
                                    $registro_login->fecha_login = date("Y-m-d H:i:s");
                                    $registro_login->direccion_ip = $req->ip();
                                    $registro_login->save();
                                
                               
                                session(['usuario' => 'normal']);
                                array_push($data,$user,"OK");
                                return redirect('/login');
                            }
                        }
                    }
                    return redirect('/')->with('status', 'Usuario incorrecto');
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
         
        }else{
            return redirect('/')->with('status', 'Usuario incorrecto');
        }
        

        
    }

    function habilitar_usuarios(){
        $users= user_server::where('activo',0)->get();
        try {
            foreach($users as $u){
                if($u->user !== 'frojo' && $u->user !== 'meguren' && $u->user !== 'jtroncoso' && $u->user !== 'malbarracin'){
                    $u->activo = 1;
                    $u->save(); 
                }
                
            }
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }

    function deshabilitar_usuarios(){
        $users= user_server::where('activo',1)->get();
        try {
            foreach($users as $u){
                if($u->user !== 'frojo' && $u->user !== 'meguren' && $u->user !== 'jtroncoso' && $u->user !== 'malbarracin'){
                    $u->activo = 0;
                    $u->save(); 
                }     
            }
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function habilitar_jose(){
        try {
            $jose = user_server::find(3);
            $jose->activo = 1;
            $jose->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    function deshabilitar_jose(){
        try {
            $jose = user_server::find(3);
            $jose->activo = 0;
            $jose->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    
    
}
