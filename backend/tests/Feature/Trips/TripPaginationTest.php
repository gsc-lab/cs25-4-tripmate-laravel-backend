<?php

namespace Tests\Feature\Trips;

use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TripPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(): array
    {
        $user = User::factory()->create([
            'email_norm' => 'paging@example.com',
            'password_hash' => Hash::make('password1234!'),
            'name' => 'PagingUser',
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => 'paging@example.com',
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function makeRegion(): Region
    {
        return Region::create([
            'name' => 'Busan',
            'country_code' => 'KR',
        ]);
    }

    public function test_trip_list_pagination(): void
    {
        $headers = $this->authHeader();
        $region = $this->makeRegion();

        for ($i = 1; $i <= 25; $i++) {
            $this->withHeaders($headers)->postJson('/api/v2/trips', [
                'title' => "Trip {$i}",
                'region_id' => $region->region_id,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-02',
            ])->assertStatus(201);
        }

        $res = $this->withHeaders($headers)->getJson('/api/v2/trips?page=2&size=10');

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);

        $items = $res->json('data.items');
        $this->assertCount(10, $items);

        $res->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 25)
            ->assertJsonPath('data.pagination.last_page', 3);
    }

    public function test_trip_list_filter_by_region_id(): void
    {
        $headers = $this->authHeader();
        $regionA = Region::create(['name' => 'A', 'country_code' => 'KR']);
        $regionB = Region::create(['name' => 'B', 'country_code' => 'KR']);

        foreach ([1, 2, 3] as $i) {
            $this->withHeaders($headers)->postJson('/api/v2/trips', [
                'title' => "A{$i}",
                'region_id' => $regionA->region_id,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-02',
            ])->assertStatus(201);
        }

        foreach ([1, 2] as $i) {
            $this->withHeaders($headers)->postJson('/api/v2/trips', [
                'title' => "B{$i}",
                'region_id' => $regionB->region_id,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-02',
            ])->assertStatus(201);
        }

        $res = $this->withHeaders($headers)
            ->getJson("/api/v2/trips?regionId={$regionA->region_id}&page=1&size=20");

        $res->assertOk()->assertJsonPath('success', true);

        $items = $res->json('data.items');
        $this->assertCount(3, $items);

        foreach ($items as $it) {
            $this->assertSame($regionA->region_id, $it['region_id']);
        }
    }
}
