<?php

namespace Tests\Feature\ScheduleItems;

use App\Models\Place;
use App\Models\PlaceCategory;
use App\Models\Region;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScheduleItemReorderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(string $email = 'reorderuser@example.com'): array
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
            'name' => 'Seoul',
            'country_code' => 'KR',
        ]);

        $res = $this->withHeaders($headers)->postJson('/api/v2/trips', [
            'title' => 'ScheduleItem Reorder Trip',
            'region_id' => $region->region_id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ])->assertStatus(201);

        return (int) $res->json('data.trip_id');
    }

    private function getTripDayId(int $tripId, int $dayNo = 1): int
    {
        $tripDayId = TripDay::where('trip_id', $tripId)
            ->where('day_no', $dayNo)
            ->value('trip_day_id');

        $this->assertNotEmpty($tripDayId, "TripDay(day_no={$dayNo}) 자동 생성이 되어 있어야 합니다.");

        return (int) $tripDayId;
    }

    private function createPlace(): int
    {
        $category = PlaceCategory::create([
            'code' => 'FOOD',
            'name' => 'Food',
        ]);

        $place = Place::create([
            'category_id' => $category->category_id,
            'name' => 'Test Place',
            'address' => 'Seoul, Korea',
            'lat' => 37.5665,
            'lng' => 126.9780,
            'external_provider' => 'google',
            'external_ref' => null,
        ]);

        return (int) $place->place_id;
    }

    public function test_scheduleitem_reorder_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $dayNo = 1;
        $tripDayId = $this->getTripDayId($tripId, $dayNo);
        $placeId = $this->createPlace();

        // 1~3 생성 (seq_no 기반 URL, 하지만 reorder payload는 schedule_item_id 필요)
        $ids = [];
        for ($seq = 1; $seq <= 3; $seq++) {
            $r = $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days/{$dayNo}/items", [
                'place_id' => $placeId,
                'seq_no' => $seq,
                'memo' => "memo {$seq}",
            ])->assertStatus(201);

            $ids[] = (int) $r->json('data.schedule_item_id');
        }

        // reorder: 3,1,2 순서로 재배치
        // routes/api.php 기준: PUT /items/reorder
        $this->withHeaders($headers)
            ->putJson("/api/v2/trips/{$tripId}/days/{$dayNo}/items/reorder", [
                'orders' => [
                    [
                        'trip_day_id' => $tripDayId,
                        'item_ids' => [$ids[2], $ids[0], $ids[1]],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 재배치에 성공했습니다')
            ->assertJsonPath('data', null);

        // DB에서 seq_no 정규화 결과 확인: ids[2]가 seq_no=1
        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $ids[2],
            'trip_day_id' => $tripDayId,
            'seq_no' => 1,
        ]);
        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $ids[0],
            'trip_day_id' => $tripDayId,
            'seq_no' => 2,
        ]);
        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $ids[1],
            'trip_day_id' => $tripDayId,
            'seq_no' => 3,
        ]);
    }
}
