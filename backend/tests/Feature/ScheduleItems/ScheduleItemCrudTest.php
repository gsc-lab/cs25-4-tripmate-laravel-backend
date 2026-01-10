<?php

namespace Tests\Feature\ScheduleItems;

use App\Models\Place;
use App\Models\PlaceCategory;
use App\Models\Region;
use App\Models\ScheduleItem;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScheduleItemCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(string $email = 'itemuser@example.com'): array
    {
        User::factory()->create([
            'email_norm' => $email,
            'password_hash' => Hash::make('password1234!'),
            'name' => 'ItemUser',
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
            'title' => 'ScheduleItem Test Trip',
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

    public function test_scheduleitem_endpoints_require_auth(): void
    {
        $this->getJson('/api/v2/trips/1/days/1/schedule-items')->assertStatus(401);

        $this->postJson('/api/v2/trips/1/days/1/schedule-items', [
            'place_id' => 1,
            'seq_no' => 1,
        ])->assertStatus(401);

        $this->getJson('/api/v2/trips/1/days/1/schedule-items/1')->assertStatus(401);

        $this->patchJson('/api/v2/trips/1/days/1/schedule-items/1', ['memo' => 'x'])->assertStatus(401);
        $this->putJson('/api/v2/trips/1/days/1/schedule-items/1', ['memo' => null, 'visit_time' => null])->assertStatus(401);

        $this->deleteJson('/api/v2/trips/1/days/1/schedule-items/1')->assertStatus(401);

        $this->postJson('/api/v2/trips/1/days/1/schedule-items/reorder', [
            'orders' => [
                ['trip_day_id' => 1, 'item_ids' => [1]],
            ],
        ])->assertStatus(401);
    }

    public function test_scheduleitem_store_show_patch_put_destroy_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $tripDayId = $this->getTripDayId($tripId, 1);
        $placeId = $this->createPlace();

        // store
        $store = $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items", [
            'place_id' => $placeId,
            'seq_no' => 1,
            'visit_time' => '10:30',
            'memo' => '첫 일정 메모',
        ]);

        $store->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 생성에 성공했습니다')
            ->assertJsonPath('data.seq_no', 1)
            ->assertJsonPath('data.place_id', $placeId);

        $scheduleItemId = (int) $store->json('data.schedule_item_id');
        $this->assertNotEmpty($scheduleItemId);

        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $scheduleItemId,
            'trip_day_id' => $tripDayId,
            'seq_no' => 1,
            'place_id' => $placeId,
            'memo' => '첫 일정 메모',
            'visit_time' => '10:30:00',
        ]);

        // show (PK 기준)
        $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items/{$scheduleItemId}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 단건 조회에 성공했습니다')
            ->assertJsonPath('data.schedule_item_id', $scheduleItemId)
            ->assertJsonPath('data.seq_no', 1);

        // PATCH: 전달된 값만 업데이트
        $this->withHeaders($headers)
            ->patchJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items/{$scheduleItemId}", [
                'memo' => '메모만 수정',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 부분 수정에 성공했습니다')
            ->assertJsonPath('data.memo', '메모만 수정');

        // visit_time은 유지되어야 함 
        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $scheduleItemId,
            'trip_day_id' => $tripDayId,
            'seq_no' => 1,
            'memo' => '메모만 수정',
            'visit_time' => '10:30:00',
        ]);

        // PUT: 전체 덮어쓰기 (nullable 가능)
        $this->withHeaders($headers)
            ->putJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items/{$scheduleItemId}", [
                'visit_time' => '11:40',
                'memo' => null,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 전체 수정에 성공했습니다');

        $this->assertDatabaseHas('schedule_items', [
            'schedule_item_id' => $scheduleItemId,
            'trip_day_id' => $tripDayId,
            'seq_no' => 1,
            'memo' => null,
            'visit_time' => '11:40:00',
        ]);

        // destroy (PK 기준)
        $this->withHeaders($headers)
            ->deleteJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items/{$scheduleItemId}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 삭제에 성공했습니다')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('schedule_items', [
            'schedule_item_id' => $scheduleItemId,
        ]);
    }

    public function test_scheduleitem_index_pagination_success(): void
    {
        $headers = $this->authHeaders();
        $tripId = $this->createTrip($headers);

        $tripDayId = $this->getTripDayId($tripId, 1);
        $placeId = $this->createPlace();

        $initialTotal = ScheduleItem::where('trip_day_id', $tripDayId)->count();

        for ($seq = 1; $seq <= 5; $seq++) {
            $this->withHeaders($headers)->postJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items", [
                'place_id' => $placeId,
                'seq_no' => $seq,
                'visit_time' => '10:00',
                'memo' => "memo {$seq}",
            ])->assertStatus(201);
        }

        $expectedTotal = $initialTotal + 5;

        $res = $this->withHeaders($headers)
            ->getJson("/api/v2/trips/{$tripId}/days/{$tripDayId}/schedule-items?page=2&size=2");

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 'SUCCESS')
            ->assertJsonPath('message', '일정 아이템 목록 조회에 성공했습니다')
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    'items',
                    'pagination' => ['page', 'size', 'total', 'last_page'],
                    'detail',
                    'latlng',
                ],
            ]);

        $items = $res->json('data.items');
        $this->assertCount(2, $items);

        $expectedLastPage = (int) ceil($expectedTotal / 2);

        $res->assertJsonPath('data.pagination.page', 2)
            ->assertJsonPath('data.pagination.size', 2)
            ->assertJsonPath('data.pagination.total', $expectedTotal)
            ->assertJsonPath('data.pagination.last_page', $expectedLastPage);
    }
}