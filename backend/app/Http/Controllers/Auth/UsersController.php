<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Services\Auth\UserService;
    use App\Http\Requests\Auth\AuthVerificationRequest;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Resources\UserResource;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Response;

    class UsersController extends Controller
    {
        private UserService $userService;
        public function __construct(UserService $userService)
        {
            $this->userService = $userService;
        }

        /**
         * User Mypage
         * - 성공 시 200 및 user_id, email, nickname 반환
         * @return JsonResponse
         */
        public function getCurrentUser(): JsonResponse 
        {
            $user = $this->userService->currentUser();

            return $this->respondSuccess(new UserResource($user));
        }

        /**
         * User delete
         * - 성공 시 204 NoContent 반환
         * @param AuthVerificationRequest $request
         * @return Response
         */
        public function deleteCurrentUser(AuthVerificationRequest $request): Response 
        {
            $data = $request->validated();

            $userId = Auth::id();

            $this->userService->deleteUser($userId, $data["password"]);

            return $this->respondNoContent();
        }

    }
