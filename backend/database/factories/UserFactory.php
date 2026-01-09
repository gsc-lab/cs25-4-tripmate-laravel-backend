<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * @return array{email_norm: string, name: string, password_hash: string}
     */
    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();

        return [
            'email_norm' => strtolower(trim($email)),
            'password_hash' => Hash::make('password1234!'), // 테스트 기본 비번
            'name' => fake()->name(),
        ];
    }
}
