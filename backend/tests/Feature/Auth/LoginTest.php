<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_returns_token(): void
    {
        User::factory()->create([
            'email_norm' => 'login@example.com',
            'password_hash' => Hash::make('password1234!'),
            'name' => '로그인유저',
        ]);

        $res = $this->postJson('/api/v2/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password1234!',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '로그인에 성공하였습니다.')
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => ['access_token', 'token_type', 'expires_in'],
            ]);

        $this->assertNotEmpty($res->json('data.access_token'));
    }

    public function test_login_fail_wrong_password(): void
    {
        User::factory()->create([
            'email_norm' => 'login2@example.com',
            'password_hash' => Hash::make('password1234!'),
            'name' => '로그인유저2',
        ]);

        $res = $this->postJson('/api/v2/auth/login', [
            'email' => 'login2@example.com',
            'password' => 'wrong-password',
        ]);

        // 서비스에서 ValidationException(email) 던짐 → 422
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
