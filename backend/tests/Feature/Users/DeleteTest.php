<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private function resetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }

    public function test_delete_me_success(): void
    {
        $user = User::factory()->create([
            'email_norm' => 'del@example.com',
            'password_hash' => Hash::make('password1234!'),
            'name' => '삭제유저',
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => 'del@example.com',
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v2/users/me', [
                'password' => 'password1234!',
            ])
            ->assertNoContent(); // 204

        $this->assertDatabaseMissing('users', [
            'user_id' => $user->user_id,
        ]);

        $this->resetAuthGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v2/users/me')
            ->assertStatus(401);
    }

    public function test_delete_me_fail_wrong_password(): void
    {
        User::factory()->create([
            'email_norm' => 'del2@example.com',
            'password_hash' => Hash::make('password1234!'),
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => 'del2@example.com',
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v2/users/me', [
                'password' => 'wrong',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
