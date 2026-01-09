<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private function loginAndGetToken(User $user, string $plainPassword): string
    {
        $res = $this->postJson('/api/v2/auth/login', [
            'email' => $user->email_norm,
            'password' => $plainPassword,
        ]);

        $res->assertOk();

        return $res->json('data.access_token');
    }

    private function resetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }

    public function test_logout_success_and_token_invalidated(): void
    {
        $user = User::factory()->create([
            'email_norm' => 'logout@example.com',
            'password_hash' => Hash::make('password1234!'),
        ]);

        $token = $this->loginAndGetToken($user, 'password1234!');

        [$tokenId] = explode('|', $token, 2);

        // 로그아웃
        $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v2/auth/logout')
            ->assertNoContent(); // 204

        // 토큰이 DB에서 삭제되었는지 확인
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) $tokenId,
        ]);

        // guard 캐시 리셋 후 다시 요청
        $this->resetAuthGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v2/users/me')
            ->assertStatus(401);
    }
}
