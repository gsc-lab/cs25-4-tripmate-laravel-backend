<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA; 

#[OA\Tag(name: 'Auth', description: '회원 인증/인가 관리')]
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * User Register
     * - 성공 시 201 Created 응답 반환
     */
    #[OA\Post(
        path: '/api/v2/users',
        summary: '회원가입',
        description: '새로운 사용자를 등록합니다.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthRegisterRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthRegisterResponse')
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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
    #[OA\Post(
        path: '/api/v2/auth/login',
        summary: '로그인',
        description: '이메일과 비밀번호로 로그인하여 액세스 토큰을 발급받습니다.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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
    #[OA\Post(
        path: '/api/v2/auth/logout',
        summary: '로그아웃',
        description: '현재 로그인된 사용자의 토큰을 만료시킵니다.',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 204,
                description: 'No Content'
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ]
    )]
    public function logout(): Response
    {
        $this->authService->logoutUser();

        return $this->respondNoContent();
    }
}
