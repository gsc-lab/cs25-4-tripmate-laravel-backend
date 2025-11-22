<?php
    namespace App\Services\Auth;

    use Illuminate\Validation\ValidationException;
    use App\Repositories\Auth\AuthRepository;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Database\Eloquent\Model;

    class AuthService
    {
        private AuthRepository $authRepository;
    
        public function __construct(AuthRepository $authRepository)
        {
            $this->authRepository = $authRepository;
        }

        /**
         * Usre Register Service
         * @param array $data
         * @return Model
         */
        public function registerUser(array $data):Model
        {
            // 비밀번호 해싱
            $data["password_hash"] = Hash::make($data["password"]);
            unset($data["password"]);

            return $this->authRepository->create($data);
        }

        /**
         * User Login Service
         * @param array $data
         */
        public function loginUser(string $email, string $password)
        {
            // 유저 조회
            $user = $this->authRepository->findUserEmail($email);

            // 해싱
            if (!$user || !Hash::check($password, $user->password_hash)) {
                throw ValidationException::withMessages([
                'email' => ['아이디 또는 비밀번호가 일치하지 않습니다.'],
                ]);
            }

            // 토큰 발급
            $token = $user->createToken("Tripmate_auth_token");

            return $token->plainTextToken; // db모델과 토큰 값-> token 값
        }

        /**
         * Delete User Service
         */
        public function deleteUser(int $userId, string $password)
        {
            // 유저 확인
            $user = $this->authRepository->findById($userId);
            if (!$user) {
                throw ValidationException::withMessages([  
                    "email" => ["유저 정보를 찾을 수 없습니다."]
                ]);
            }

            // 비밀번호 검증
            if (!Hash::check($password, $user->password_hash)) {
                throw ValidationException::withMessages([
                    "password" => ["비밀번호가 일치하지 않습니다."]
                ]); 
            }

            // 유저 삭제
            $result = $this->authRepository->deleteById($userId);

            if (!$result) {
                throw ValidationException::withMessages([
                    "email"=> ["회원 탈퇴에 실패하였습니다."]
                ]);
            }
        }
    }

