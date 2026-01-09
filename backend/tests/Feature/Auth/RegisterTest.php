<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success(): void
    {
        $payload = [
            'name' => '테스트유저',
            'email_norm' => 'test@example.com',
            'password' => 'password1234!',
        ];

        $res = $this->postJson('/api/v2/users', $payload);

        $res->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '회원가입이 완료되었습니다.')
            ->assertJsonPath('data', null);

        $this->assertDatabaseHas('users', [
            'email_norm' => 'test@example.com',
            'name' => '테스트유저',
        ]);
    }

    public function test_register_validation_fail_missing_password(): void
    {
        $payload = [
            'name' => '테스트유저',
            'email_norm' => 'test2@example.com',
            // password 없음
        ];

        $res = $this->postJson('/api/v2/users', $payload);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
