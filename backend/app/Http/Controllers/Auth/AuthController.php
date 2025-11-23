<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // 기본 Request 임포트

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
    public function register(AuthRegisterRequest $request) 
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
                "expires_in" => 43200
                ]
            ]
        );
    }

    /**
     * User delete - 회원삭제
     */
    public function logout(AuthVerificationRequest $request) 
    {
        $data = $request->validated();

        $userId = Auth::id();

        $this->authService->deleteUser($userId, $data["password"]);

        return response()->noContent();
    }
}
