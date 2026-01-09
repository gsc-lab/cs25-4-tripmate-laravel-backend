<?php

namespace Tests\Feature\Trips;

use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TripCrudTest extends TestCase
{
    use RefreshDatabase;

    private function loginHeader(string $email): array
    {
        User::factory()->create([
            'email_norm' => $email,
            'password_hash' => Hash::make('password1234!'),
            'name' => 'User',
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => $email,
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function makeRegion(): Region
    {
        return Region::create([
            'name' => 'Seoul',
            'country_code' => 'KR',
        ]);
    }

    public function test_trip_crud_success(): void
    {
        $headers = $this->loginHeader('tripuser@example.com');
        $region = $this->makeRegion();

        // CREATE
        $create = $this->withHeaders($headers)->postJson('/api/v2/trips', [
            'title' => '오사카 3박 4일',
            'region_id' => $region->region_id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-04',
        ])->assertStatus(201);

        $tripId = $create->json('data.trip_id');

        // SHOW
        $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.trip_id', $tripId);

        // UPDATE (PATCH)
        $this->withHeaders($headers)
            ->patchJson("/api/v2/trips/{$tripId}", [
                'title' => '오사카 수정',
                'region_id' => $region->region_id,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-04',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', '오사카 수정');

        $this->assertDatabaseHas('trips', [
            'trip_id' => $tripId,
            'title' => '오사카 수정',
        ]);

        // DELETE
        $this->withHeaders($headers)
            ->deleteJson("/api/v2/trips/{$tripId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('trips', [
            'trip_id' => $tripId,
        ]);
    }
}
