<?php

namespace App\Services\Auth;

use App\Repositories\Auth\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function currentUser()
    {
        // UserId를 가져와 정보 반환
        if (Auth::user() == null) {
            throw ValidationException::withMessages([
                'email' => ['유저 정보를 찾을 수 없습니다.'],
            ]);
        }

        $userId = Auth::id();
        $user = $this->userRepository->findById($userId, ['user_id', 'email_norm', 'name']);

        return $user;
    }

    /**
     * Delete User Service
     */
    public function deleteUser(int $userId, string $password): void
    {
        // 유저 확인
        $user = $this->userRepository->findById($userId);
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['유저 정보를 찾을 수 없습니다.'],
            ]);
        }

        // 비밀번호 검증
        if (! Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'password' => ['비밀번호가 일치하지 않습니다.'],
            ]);
        }

        // 유저 삭제
        $result = $this->userRepository->deleteById($userId);

        if (! $result) {
            throw ValidationException::withMessages([
                'email' => ['회원 탈퇴에 실패하였습니다.'],
            ]);
        }
    }
}
