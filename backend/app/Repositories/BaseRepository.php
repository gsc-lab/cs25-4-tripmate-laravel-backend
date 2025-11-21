<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * base repository class
 * 모든 Eloquent model repository의 공통 부모 class
 * 반드시 도메이별 repository가 이 클래스를 상속받아야 사용
 */ 
abstract class BaseRepository 
{
  /**
   * Eloquent model 인스턴스
   * @var \Illuminate\Database\Eloquent\Model
   */
  protected Model $model;
  
  /**
   * 생성자에서 모델을 주입받아 저장
   * @param \Illuminate\Database\Eloquent\Model $model
   */
  public function __construct(Model $model) 
  {
    $this->model = $model;
  }

  /**
   * 내부에서 쓰는 모델 인스턴스를 반환
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function getModel(): Model
  {
    return $this->model;
  }

  /**
   * 전체 목록 조회
   * 데이터가 많지 않을때 사용 권장
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function all(array $columns = ['*']): Collection
  {
    return $this->model->newQuery()->get($columns);
  }

  /**
   * 페이지네이션 조회 (기본 20개)
   * @param int $perPage
   * @param array $columns
   * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
   */
  public function paginate(
    int $perPage = 20, 
    array $columns = ['*']
  ): LengthAwarePaginator{
    return $this->model->newQuery()->paginate($perPage, $columns);
  }

  /**
   * PK(id)로 단일 조회
   * 없으면 null 반환
   * @param int $id
   * @param array $columns
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function findById(
    int $id, 
    array $columns = ['*']
  ): ?Model {
    return $this->model->newQuery()->find($id, $columns);
  }

  /**
   * PK(id)로 단일 조회
   * 없으면 예외 발생 (ModelNotFoundException)
   * @param int $id
   * @param array $columns
   * @return \Illuminate\Database\Eloquent\Model
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   */
  public function findOrFail(
    int $id, 
    array $columns = ['*']
  ): Model {
    return $this->model->newQuery()->findOrFail($id, $columns);
  }

  /**
   * 새 레코드 생성
   * @param array $data
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function create(array $data): Model
  {
    return $this->model->newQuery()->create($data);
  }

  /**
   * PK(id) 기준 부분 업데이트
   * - 없으면 예외 발생 (ModelNotFoundException)
   * - 있으면 해당 레코드 업데이트 후 반환
    * @param int $id
    * @param array $data 
    * @return \Illuminate\Database\Eloquent\Model
    * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   */
  public function updateById(
    int $id, 
    array $data
  ): Model {
    $model = $this->findOrFail($id);

    $model->fill($data);
    $model->save();

    return $model;
  }

  /**
   * PK(id) 기준 삭제
   * - soft delete 지원 모델인 경우 소프트 삭제 수행
   * - 아니면 실제 삭제 수행
   * @param int $id
   * @return bool
   */
  public function deleteById(int $id): bool
  {
    $model = $this->findOrFail($id);

    return (bool)$model->delete();
  }

}