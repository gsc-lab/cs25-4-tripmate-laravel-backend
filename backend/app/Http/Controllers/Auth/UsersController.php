<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Services\Auth\UserService;
    use App\Http\Requests\Auth\AuthVerificationRequest;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Resources\UserResource;

    class UsersController extends Controller
    {
        private UserService $userService;
        public function __construct(UserService $userService)
        {
            $this->userService = $userService;
        }

        /**
         * User Mypage
         */
        public function getCurrentUser() 
        {
            $user = $this->userService->currentUser();

            return response()->json([
                'success' => true,
                'data'=> UserResource::collection($user)
            ]);
        }

        /**
         * User delete - 회원삭제
         */
        public function deleteCurrentUser(AuthVerificationRequest $request) 
        {
            $data = $request->validated();

            $userId = Auth::id();

            $this->userService->deleteUser($userId, $data["password"]);

            return response()->noContent();
        }

    }
