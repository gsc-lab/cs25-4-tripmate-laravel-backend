<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthVerificationRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * User Mypage
     * - 성공 시 200 및 user_id, email, nickname 반환
     */
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->userService->currentUser();

        return $this->respondSuccess(new UserResource($user));
    }

    /**
     * User delete
     * - 성공 시 204 NoContent 반환
     */
    public function deleteCurrentUser(AuthVerificationRequest $request): Response
    {
        $data = $request->validated();

        $userId = $request->user()->user_id;

        $this->userService->deleteUser($userId, $data['password']);

        return $this->respondNoContent();
    }
}
