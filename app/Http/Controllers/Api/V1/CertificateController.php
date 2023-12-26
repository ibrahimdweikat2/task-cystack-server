<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CertificateController extends Controller
{
    //
    public function getAllCertificates(Request $req){
        $search=$req->header('search');
        if(!$search){
            return response()->json(['message'=>'Invalid search'],404);
        }

        $jsonResponse=Http::get('https://crt.sh/?q='.$search.'&output=json');
        $certificates=$jsonResponse->json();

        return response()->json(['certificates' => $certificates,'message'=>"Get all certificates Successfully"],200);
    }

    public function storageAllCertificates(Request $req,$id){
        $certificates=$req['certificates'];
        $arrayCertificates=json_decode($certificates,true);
        $domain=$req['domain'];

        $alreadyFoundDomain = DB::table('certificates as c')
        ->join('users as u', 'c.user_id', '=', 'u.id')
        ->where('u.id', '=', $id)
        ->select('c.*')
        ->get();

        foreach($alreadyFoundDomain as $domain1){
            if($domain1->domain == $domain){
                return response()->json(['message'=>"You Already Have This Domain {$domain1->domain}"],404);
            }
        }

        foreach($arrayCertificates as $certificate){
            $credential=[
                "certificate_id"=>$certificate['id'],
                "domain"=>$domain,
                "common_name"=>$certificate['common_name'],
                "not_before"=>$certificate['not_before'],
                "not_after"=>$certificate['not_after'],
                "user_id"=>$id,
                "issuer_name"=>$certificate['issuer_name'],
            ];

            DB::table('certificates')->insert($credential);
        }

        return response()->json(['message'=>"Storage Certificates Successfully"],200);
    }

    public function getAllCertificatesByUserId($id){
        $certificates=DB::table('certificates as c')
        ->join('users as u', 'c.user_id', '=', 'u.id')
        ->where('u.id', '=', $id)
        ->select('*')
        ->get();

        if($certificates){
            return response()->json(['certificates'=>$certificates],200);
        }
        return '';
    }
}
