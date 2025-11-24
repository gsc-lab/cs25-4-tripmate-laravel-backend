<?php
namespace App\Repositories\Trip;

use App\Models\TripDay;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * TripDay 전용 Repository
 */
class TripDayRepository extends BaseRepository
{
  // TripDay Model 인스턴스 주입
  public function __construct(TripDay $model) 
  {
    parent::__construct($model);
  }

  /**
   * 1. 특정 Trip의 TripDay 목록 조회 (페이지네이션)
   * @param int $tripId
   * @param int $page
   * @param int $size
   * @return LengthAwarePaginator
   */
  public function paginateByTripDay(
    int $tripId,
    int $page,
    int $size
  ): LengthAwarePaginator {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->orderBy('day_no', 'asc')
      ->paginate($size, ['*'], 'page', $page);
  }

  /**
   * 2. 특정 Trip의 TripDay 전체 목록 조회 (페이지네이션 없음)
   * @param int $tripId
   * @return Collection
   */
  public function getByTripId(int $tripId): Collection
  {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->orderBy('day_no', 'asc')
      ->get();
  }

  /**
   * 3. 해당 Trip 안에 day_no가 이미 존재하는지 확인
   * @param int $tripId
   * @param int $dayNo
   * @return bool
   */
  public function existDayNo(int $tripId, int $dayNo): bool
  {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', $dayNo)
      ->exists();
  }

  /**
   * 4. 해당 Trip에서 가장 큰 day_no를 반환
   * - 아무것도 없으면 0 반환
   * @param int $tripId
   * @return int
   */
  public function getMaxDayNo(int $tripId): int
  {
    return (int)$this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->max('day_no');
  }

  /**
   * 5. 중간에 일차를 삽입하기 위한 메섣
   * - 해당 dateNo 이후의 day_number 들을 +1 씩 증가시킨다
   * @param int $tripId
   * @param int $fromDayNo  // 이 일차부터 증가
   * @return int            // 영향을 받은 row 수
   */
  public function incrementDayNo(int $tripId, int $fromDayNo): int
  {
    // 해당 일차 이후의 row 들 조회 
    $rows = $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', '>=', $fromDayNo)
      ->orderByDesc('day_no')
      ->get();

    // 각 row 들의 day_no 증가
    foreach ($rows as $row) {
      $row->increment('day_no');
    }

    return $rows->count();
  }

  /**
   * 6. 특정 day_no를 삭제 한 후 뒤의 일차들을 -1 씩 감소를 위한 메서드
   * - 연속성 유지를 위해 사용
   * @param int $tripId
   * @param int $deleteDayNo  // 삭제 된 day_no
   * @return int              // 영향을 받은 row 수
   */
  public function decrementDayNoAfter(int $tripId, int $fromDayNo): int
  {
    // 해당 일차 이후의 row 들 조회
    $rows = $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', '>', $fromDayNo)
      ->orderBy('day_no', 'asc')
      ->get();

    // 각 row 들의 day_no 감소
    foreach ($rows as $row) {
      $row->decrement('day_no');
    }

    return $rows->count();
  }

  /**
   * 7. Tripday 단건 조회
   * @param int $tripId
   * @param int $dayNo
   * @return TripDay|null
   */
  public function findByTripAndDayNo(
    int $tripId,
    int $dayNo
  ): ?TripDay {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', $dayNo)
      ->firstOrFail();
  }

  /**
   * 8. trip_day_id 조회
   * @param int $tripId
   * @param int $dayNo
   * @return int|null
   */
  public function getTripDayId(
    int $tripId,
    int $dayNo
  ): ?int {
    
    // TripDay 조회
    $row = $this->findByTripAndDayNo($tripId, $dayNo);

    return $row?->trip_day_id;
  }

  /**
   * 9. memo 수정
   * @param int $tripId
   * @param int $dayNo
   * @param string|null $memo
   * @return int  // 영향을 받은 row 수
   */
  public function updateMemo(
    int $tripId,
    int $dayNo,
    ?string $memo
  ): int {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', $dayNo)
      ->update(['memo' => $memo]);
  }

  /**
   * 10. 해당 Trip에 속한 TripDay 개수 반환
   * - day_count 동기화 용도
   * @param int $tripId
   * @return int
   */
  public function countByTripId(int $tripId): int
  {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->count();
  }

  /**
   * 11. 단일 일차 번호 변경
   * @param int $tripId
   * @param int $oldDayNo // 변경 전 일차 번호
   * @param int $newDayNo // 변경 후 일차 번호
   * @return int          // 영향을 받은 row 수
   */
  public function updateDayNo(
    int $tripId,
    int $oldDayNo,
    int $newDayNo
  ): int {
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', $oldDayNo)
      ->update(['day_no' => $newDayNo]);
  }

  /**
   * 12. 재배치용 일차 번호 변경 메서드
   * - oldDayNo < newDayNo
   * - oldDayNo , newDayNo 사이의 일차 번호들을 -1 씩 감소
   * @param int $tripId
   * @param int $oldDayNo
   * @param int $newDayNo
   * @return int
   */
  public function shiftDownRange(
    int $tripId,
    int $oldDayNo,
    int $newDayNo
  ): int {

    // 조건이 맞지 않으면 아무 작업도 하지 않음
    if ($oldDayNo >= $newDayNo) {
      return 0;
    }

    // 해당 범위의 일차 번호들을 -1 씩 감소
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', '>', $oldDayNo)
      ->where('day_no', '<=', $newDayNo)
      ->decrement('day_no');
  }

  /**
   * 13. 재배치용 일차 번호 변경 메서드
   * - oldDayNo > newDayNo
   * - oldDayNo , newDayNo 사이의 일차 번호들을 +1 씩 증가
   * @param int $tripId
   * @param int $oldDayNo
   * @param int $newDayNo
   * @return int
   */
  public function shiftUpRange(
    int $tripId,
    int $oldDayNo,
    int $newDayNo
  ): int {

    // 조건이 맞지 않으면 아무 작업도 하지 않음
    if ($oldDayNo <= $newDayNo) {
      return 0;
    }

    // 해당 범위의 일차 번호들을 +1 씩 증가
    return $this->model
      ->newQuery()
      ->where('trip_id', $tripId)
      ->where('day_no', '>=', $newDayNo)
      ->where('day_no', '<', $oldDayNo)
      ->increment('day_no');
  }
  
}