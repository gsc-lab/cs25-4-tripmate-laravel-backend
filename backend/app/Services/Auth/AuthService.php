<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Usre Register Service
     */
    public function registerUser(array $data): Model
    {
        // 비밀번호 해싱
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        return $this->authRepository->create($data);
    }

    /**
     * User Login Service
     *
     * @param  array  $data
     */
    public function loginUser(string $email, string $password): mixed
    {
        // 유저 조회
        $user = $this->authRepository->findUserEmail($email);

        // 해싱
        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['아이디 또는 비밀번호가 일치하지 않습니다.'],
            ]);
        }

        // 토큰 발급
        $token = $user->createToken('Tripmate_auth_token');

        return $token->plainTextToken; // db모델과 토큰 값-> token 값
    }

    /**
     * User Logout Service
     */
    public function logoutUser(): void
    {
        /**
         * @var App\Models\User
         */
        $user = Auth::user();

        // 사용자 식별 후 토큰 삭제
        if ($user === null) {
            throw ValidationException::withMessages([
                'user' => ['사용자 정보를 찾을 수 없습니다.'],
            ]);
        }

        $user->currentAccessToken()->delete();
    }
}
