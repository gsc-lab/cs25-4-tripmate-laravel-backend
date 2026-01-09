<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\BaseRepository;

class AuthRepository extends BaseRepository
{
    /**
     * 부모에게 User 테이블 모델 전달하는 생성자
     */
    public function __construct(User $userModel)
    {
        parent::__construct($userModel);
    }

    /**
     * User 로그인
     */
    public function findUserEmail(string $email)
    {
        return $this->model->newQuery()->where('email_norm', $email)->first();
    }
}
