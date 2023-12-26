<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    public function updateUserSettings(Request $req,$id){
        $theshold=$req['theshold'];
        $receive_notification=$req['receive_notification'];
        $receive_email=$req['receive_email'] ?? '';

        $update=DB::table('users')->where('id','=',$id)->update(['theshold' => $theshold, 'receive_notification'=>$receive_notification,"receive_email"=>$receive_email]);

        if($update){
            return response()->json(['message'=>'update Success'],200);
        }
        return response()->json(['message'=>'Not Update'],404);
    }
}
