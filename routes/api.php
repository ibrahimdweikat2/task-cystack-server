<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'v1'],function(){
    // User API
    Route::post('/register',[AuthController::class,'register_user']);
    Route::post('/login',[AuthController::class,'login_user']);
    Route::get('/refresh-token/{id}',[AuthController::class,'refresh_token']);

    Route::put('/update_user_settings/{id}',[UserController::class,'updateUserSettings']);

    // certificate API
    Route::get('/get_all_certificates',[CertificateController::class,'getAllCertificates']);
    Route::post('/storage/{id}',[CertificateController::class,'storageAllCertificates']);
    Route::get('/certificates-by-userId/{id}',[CertificateController::class,'getAllCertificatesByUserId']);
});
