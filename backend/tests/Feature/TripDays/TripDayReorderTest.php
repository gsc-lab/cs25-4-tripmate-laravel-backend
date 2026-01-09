<?php
namespace Tests\Feature\TripDays;

use App\Models\Region;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TripDayReorderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(string $email = 'reorder@example.com'): array
    {
        User::factory()->create([
            'email_norm' => $email,
            'password_hash' => Hash::make('password1234!'),
            'name' => 'ReorderUser',
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
            'name' => 'Busan',
            'country_code' => 'KR',
        ]);

        $res = $this->withHeaders($headers)->postJson('/api/v2/trips', [
            'title' => 'Reorder Trip',
            'region_id' => $region->region_id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ])->assertStatus(201);

        return (int) $res->json('data.trip_id');
    }

    public function test_tripday_reorder_success_updates_day_no_sequence(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);


        $ids = TripDay::where('trip_id', $tripId)
            ->orderBy('day_no')
            ->pluck('trip_day_id')
            ->all();

        $this->assertCount(3, $ids);

        $payload = [
            'day_ids' => array_reverse($ids),
        ];

        $this->withHeaders($headers)
            ->postJson("/api/v2/trips/{$tripId}/days/reorder", $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 재배치에 성공했습니다')
            ->assertJsonPath('data', null);

        $newFirstId = $payload['day_ids'][0];

        $this->assertDatabaseHas('trip_days', [
            'trip_id' => $tripId,
            'trip_day_id' => $newFirstId,
            'day_no' => 1,
        ]);
    }

    public function test_tripday_reorder_validation_fail_distinct(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $id = TripDay::where('trip_id', $tripId)
            ->orderBy('day_no')
            ->value('trip_day_id');

        // 중복 id 넣기 -> distinct 룰로 422
        $this->withHeaders($headers)
            ->postJson("/api/v2/trips/{$tripId}/days/reorder", [
                'day_ids' => [$id, $id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['day_ids.1']);
    }
}