<?php

namespace Tests\Feature\TripDays;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Region;
use App\Models\Trip;
use App\Models\TripDay;

class TripDayCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(string $email = 'dayuser@example.com'): array
    {
        User::factory()->create([
            'email_norm' => $email,
            'password_hash' => Hash::make('password1234!'),
            'name' => 'DayUser',
        ]);

        $login = $this->postJson('/api/v2/auth/login', [
            'email' => $email,
            'password' => 'password1234!',
        ])->assertOk();

        $token = $login->json('data.access_token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function createTrip(array $headers): int
    {
        $region = Region::create([
            'name' => 'Seoul',
            'country_code' => 'KR',
        ]);

        $res = $this->withHeaders($headers)->postJson('/api/v2/trips', [
            'title' => 'TripDay Test Trip',
            'region_id' => $region->region_id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ])->assertStatus(201);

        return (int) $res->json('data.trip_id');
    }

    public function test_tripday_endpoints_require_auth(): void
    {
        $this->getJson('/api/v2/trips/1/days')->assertStatus(401);
        $this->postJson('/api/v2/trips/1/days', ['day_no' => 1])->assertStatus(401);
        $this->getJson('/api/v2/trips/1/days/1')->assertStatus(401);
        $this->patchJson('/api/v2/trips/1/days/1', ['memo' => 'x'])->assertStatus(401);
        $this->deleteJson('/api/v2/trips/1/days/1')->assertStatus(401);
        $this->postJson('/api/v2/trips/1/days/reorder', ['day_ids' => [1]])->assertStatus(401);
    }

    public function test_tripday_store_show_updateMemo_destroy_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        // 1) store (day 1)
        $store = $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days", [
            'day_no' => 1,
            'memo' => '첫날 메모',
        ]);

        $store->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 생성에 성공했습니다')
            ->assertJsonPath('data.day_no', 1);

        $tripDayId = $store->json('data.trip_day_id');
        if (!$tripDayId) {
            $tripDayId = TripDay::where('trip_id', $tripId)->where('day_no', 1)->value('trip_day_id');
        }

        $this->assertDatabaseHas('trip_days', [
            'trip_day_id' => $tripDayId,
            'trip_id' => $tripId,
            'day_no' => 1,
        ]);

        // 2) show
        $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days/1")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 단건 조회에 성공했습니다')
            ->assertJsonPath('data.day_no', 1);

        // 3) updateMemo
        $this->withHeaders($headers)
            ->patchJson("/api/v2/trips/{$tripId}/days/1", [
                'memo' => '메모 수정됨',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 메모 수정에 성공했습니다')
            ->assertJsonPath('data.day_no', 1)
            ->assertJsonPath('data.memo', '메모 수정됨');

        $this->assertDatabaseHas('trip_days', [
            'trip_id' => $tripId,
            'day_no' => 1,
            'memo' => '메모 수정됨',
        ]);

        // 4) destroy
        $this->withHeaders($headers)
            ->deleteJson("/api/v2/trips/{$tripId}/days/1")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 삭제에 성공했습니다')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('trip_days', [
            'trip_id' => $tripId,
            'day_no' => 1,
        ]);
    }

    public function test_tripday_index_pagination_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        // day 1~5 생성
        for ($d = 1; $d <= 5; $d++) {
            $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days", [
                'day_no' => $d,
                'memo' => "memo {$d}",
            ])->assertStatus(201);
        }

        // page=2 size=2 -> items 2개, total 5, last_page 3
        $res = $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days?page=2&size=2");

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    'items',
                    'pagination' => ['page', 'size', 'total', 'last_page'],
                ],
            ]);

        $items = $res->json('data.items');
        $this->assertCount(2, $items);

        $res->assertJsonPath('data.pagination.page', 2)
            ->assertJsonPath('data.pagination.size', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3);
    }
}