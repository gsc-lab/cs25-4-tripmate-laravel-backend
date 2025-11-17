<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Backend\App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/api/v1/auth/signup', [AuthController::class, 'signup']);
