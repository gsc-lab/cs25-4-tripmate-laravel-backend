<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Requests\Auth\AuthLoginRequest;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * User Register
     */
    public function registerUser(AuthRegisterRequest $request) 
    {
        $data = $request->validated();

        $this->authService->registerUser($data);

        return response()->noContent();
    }

    /**
     * User Login
     */
    public function login(AuthLoginRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->loginUser($data["email"], $data["password"]);
        
        return response()->json([
            'success' => true,
            'data'=> [
                'access_token' => $result,
                "token_type" => "Bearer",
                "expires_in" => config('sanctum.expiration') * 60
                ]
            ]
        );
    }

    /**
     * User logout 
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $this->authService->logoutUser();

        return response()->noContent();
    }

    
}
