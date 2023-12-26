<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    public function register_user(Request $req){
        $req->validate([
            'email'=>'required',
            'password'=>'required',
            'name'=>'required',
        ]);
        $user=DB::table('users')->select('id')->where('email',$req['email'])->first();

        if(!empty($user)){
            return response()->json(['message'=>'User Already Found'],404);
        }
        $crediental=["name"=>$req['name'],
        "email"=>$req['email'],
        "password"=>bcrypt($req['password']),
        "created_at"=>new \DateTime(),
        "updated_at"=>new \DateTime()];
        DB::table('users')->insert($crediental);

        return response()->json(['message'=>'User Registered'],200);
    }

    public function login_user(Request $req){
        $req->validate([
            'email'=>'required',
            'password'=>'required',
        ]);
        $credentials = [
            'email' => $req['email'],
            'password' => $req['password'],
        ];

        $user = DB::table('users')->select('*')->where('email', '=', $credentials['email'])->first();

        if(empty($user)){
            return response()->json(['message'=>'User Not Found'],404);
        }

        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['user' => $user, 'token' => $token,'message'=>'User LogIn Success'], 201);
    }

    public function refresh_token(Request $req, $id)
    {
    $token = $req->header('Authorization');

    try {
        $user = JWTAuth::parseToken($token)->authenticate();

        $userData = [
            "name" => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'theshold'=>$user->theshold,
            'receive_notification'=>$user->receive_notification,
            "receive_email"=>$user->receive_email
        ];

        return response()->json(['user' => $userData, 'token' => $token], 200);
    } catch (\Exception $e) {
        // Assuming the password is encrypted using Laravel's Encrypter
        $user = DB::table('users')->select('*')->where('id', '=', $id)->first();

        $credentials = [
            'email' => $user->email,
            'password' => Crypt::decrypt($user->password)
        ];

        $newToken = JWTAuth::attempt($credentials);
        if (!$newToken) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $userData = [
            "name" => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'theshold'=>$user->theshold,
            'receive_notification'=>$user->receive_notification,
            "receive_email"=>$user->receive_email
        ];
        return response()->json(['user' => $userData, 'token' => $newToken], 201);
    }
}
}
