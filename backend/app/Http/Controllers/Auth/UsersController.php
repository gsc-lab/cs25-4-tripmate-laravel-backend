<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthVerificationRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Users', description: '회원 정보 관리 (마이페이지)')]
class UsersController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * User Mypage
     * - 성공 시 200 및 user_id, email, nickname 반환
     */
    #[OA\Get(
        path: '/api/v2/users/me',
        summary: '내 정보 조회 (마이페이지)',
        description: '현재 로그인한 사용자의 정보를 조회합니다.',
        tags: ['Users'],
        security: [['bearerAuth' => []]], // 토큰 필요
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'nickname', type: 'string', example: '트립메이트'),
                                new OA\Property(property: 'profile_image', type: 'string', nullable: true, example: 'http://...'),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ]
    )]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->userService->currentUser();

        return $this->respondSuccess(new UserResource($user));
    }

    /**
     * User delete
     * - 성공 시 204 NoContent 반환
     */
    #[OA\Delete(
        path: '/api/v2/users/me',
        summary: '회원 탈퇴',
        description: '비밀번호 검증 후 회원을 탈퇴 처리합니다.',
        tags: ['Users'],
        security: [['bearerAuth' => []]], // 토큰 필요
        requestBody: new OA\RequestBody(
            description: '본인 확인을 위한 비밀번호',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthVerificationRequest')
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'No Content (탈퇴 성공)'
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(
                response: 422, 
                description: 'Validation Error (비밀번호 불일치 등)',
                content: new OA\JsonContent(ref: '#/components/responses/ValidationError')
            ),
        ]
    )]
    public function deleteCurrentUser(AuthVerificationRequest $request): Response
    {
        $data = $request->validated();

        $userId = $request->user()->user_id;

        $this->userService->deleteUser($userId, $data['password']);

        return $this->respondNoContent();
    }
}