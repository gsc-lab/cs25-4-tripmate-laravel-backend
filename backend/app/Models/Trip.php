<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'trips';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'trip_id';

    /**
     * mass assignment 설정 (지금은 모두 허용)
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 속성 타입 캐스팅
     *
     * @var array
     */
    protected $casts = [
        'trip_id' => 'integer',
        'user_id' => 'integer',
        'region_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'day_count' => 'integer',   // 생성 컬럼
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Trip N:1 Region (nullable)
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    // Trip N:1 User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Trip 1:N TripDay
    public function tripDays()
    {
        return $this->hasMany(TripDay::class, 'trip_id', 'trip_id');
    }

    // Trip 1:N ScheduleItem (TripDay를 경유한 편의 관계)
    public function scheduleItems()
    {
        return $this->hasManyThrough(
            ScheduleItem::class,
            TripDay::class,
            'trip_id',        // TripDay에서 Trip을 가리키는 FK
            'trip_day_id',    // ScheduleItem에서 TripDay를 가리키는 FK
            'trip_id',        // Trip의 PK
            'trip_day_id'     // TripDay의 PK
        );
    }
}
