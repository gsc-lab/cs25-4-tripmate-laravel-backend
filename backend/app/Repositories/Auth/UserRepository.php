<?php
    namespace App\Repositories\Auth;

    use App\Repositories\BaseRepository;
    use App\Models\User; 

    class UserRepository extends BaseRepository
    {
        public function __construct(User $user)
        {
            return parent::__construct($user);
        }
    }