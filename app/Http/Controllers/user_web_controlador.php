<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\User;
use App\User_web;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class user_web_controlador extends Controller
{

    public function index(){
        return 'HOLA MUNDO';
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


    public function register(Request $request)
    {
        
try {
  
   
    // $validator = Validator::make($request->all(), [
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|max:255|unique:users',
    //     'password' => 'required|string|min:6|confirmed',
    // ]);

    

    // if($validator->fails()){
    //         return response()->json($validator->errors()->toJson(),400);
    // }

   

    $user = User_web::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

  
    $token = JWTAuth::fromUser($user);

    return response()->json(compact('user','token'),201);
} catch (\Exception $e) {
    return $e->getMessage();
}
       
    }
}

