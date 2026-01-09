<?php

namespace Tests\Feature\TripDays;

use App\Models\Region;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

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

    private function tripDayUsesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(TripDay::class), true);
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

    public function test_tripday_store_show_update_memo_destroy_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $initialMaxDayNo = (int) (TripDay::where('trip_id', $tripId)->max('day_no') ?? 0);
        $dayNo = $initialMaxDayNo + 1;

        // 1) store
        $store = $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days", [
            'day_no' => $dayNo,
            'memo' => '첫날 메모',
        ]);

        $store->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 생성에 성공했습니다')
            ->assertJsonPath('data.day_no', $dayNo);

        $tripDayId = $store->json('data.trip_day_id');
        if (! $tripDayId) {
            $tripDayId = TripDay::where('trip_id', $tripId)->where('day_no', $dayNo)->value('trip_day_id');
        }

        $this->assertDatabaseHas('trip_days', [
            'trip_day_id' => $tripDayId,
            'trip_id' => $tripId,
            'day_no' => $dayNo,
        ]);

        // 2) show
        $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days/{$dayNo}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 단건 조회에 성공했습니다')
            ->assertJsonPath('data.day_no', $dayNo);

        // 3) updateMemo
        $this->withHeaders($headers)
            ->patchJson("/api/v2/trips/{$tripId}/days/{$dayNo}", [
                'memo' => '메모 수정됨',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 메모 수정에 성공했습니다')
            ->assertJsonPath('data.day_no', $dayNo)
            ->assertJsonPath('data.memo', '메모 수정됨');

        $this->assertDatabaseHas('trip_days', [
            'trip_id' => $tripId,
            'day_no' => $dayNo,
            'memo' => '메모 수정됨',
        ]);

        // 4) destroy
        $this->withHeaders($headers)
            ->deleteJson("/api/v2/trips/{$tripId}/days/{$dayNo}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', 'Trip Day 삭제에 성공했습니다')
            ->assertJsonPath('data', null);

        if ($this->tripDayUsesSoftDeletes()) {
            $this->assertSoftDeleted('trip_days', [
                'trip_day_id' => $tripDayId,
            ]);
        } else {
            $this->assertDatabaseMissing('trip_days', [
                'trip_day_id' => $tripDayId,
            ]);
        }
    }

    public function test_tripday_index_pagination_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $initialTotal = TripDay::where('trip_id', $tripId)->count();
        $maxDayNo = (int) (TripDay::where('trip_id', $tripId)->max('day_no') ?? 0);

        for ($i = 1; $i <= 5; $i++) {
            $maxDayNo++;

            $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days", [
                'day_no' => $maxDayNo,
                'memo' => "memo {$i}",
            ])->assertStatus(201);
        }

        $expectedTotal = $initialTotal + 5;

        $page = 2;
        $size = 2;
        $expectedLastPage = (int) ceil($expectedTotal / $size);

        $res = $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days?page={$page}&size={$size}");

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

        $res->assertJsonPath('data.pagination.page', $page)
            ->assertJsonPath('data.pagination.size', $size)
            ->assertJsonPath('data.pagination.total', $expectedTotal)
            ->assertJsonPath('data.pagination.last_page', $expectedLastPage);
    }
}
