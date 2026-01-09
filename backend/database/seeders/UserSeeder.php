<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/users.json'));
        $users = json_decode($json, true);

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email_norm' => $user['email_norm']],
                [
                    'password_hash' => Hash::make($user['password']),
                    'name' => $user['name'] ?? null,
                ]
            );
        }
    }
}
