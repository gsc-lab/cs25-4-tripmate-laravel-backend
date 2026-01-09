<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * User Register
     * - 성공 시 201 Created 응답 반환
     */
    public function registerUser(AuthRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->authService->registerUser($data);

        return $this->respondCreated(null, '회원가입이 완료되었습니다.');
    }

    /**
     * User Login
     * - 엑세스 토큰 및 만료시간 반환 (status 200)
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $token = $this->authService->loginUser($data['email'], $data['password']);

        return $this->respondSuccess([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60,
        ], '로그인에 성공하였습니다.');
    }

    /**
     * User logout
     * - 성공 시 204 NoContent 반환
     */
    public function logout(): Response
    {
        $this->authService->logoutUser();

        return $this->respondNoContent();
    }
}
