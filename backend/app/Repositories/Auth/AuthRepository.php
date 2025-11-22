<?php
    namespace App\Repositories\Auth;

    use App\Repositories\BaseRepository;
    use App\Models\User;

    class AuthRepository extends BaseRepository 
    {
        /**
         * 부모에게 User 테이블 모델 전달하는 생성자
         * @param User $userModel
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
            return $this->model->newQuery()->where("email_norm", $email)->first();
        }
    }