<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/v2/users/me')
            ->assertStatus(401);
    }

    public function test_me_success_returns_user_resource(): void
    {
        $user = User::factory()->create([
            'email_norm' => 'me@example.com',
            'password_hash' => Hash::make('password1234!'),
            'name' => '내정보',
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => 'me@example.com',
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v2/users/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success', 'code', 'message',
                'data' => ['user_id', 'email', 'nickname'],
            ])
            ->assertJsonPath('data.email', 'me@example.com')
            ->assertJsonPath('data.nickname', '내정보');
    }
}
