<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDay extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'trip_days';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'trip_day_id';

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
        'trip_day_id' => 'integer',
        'trip_id' => 'integer',
        'day_no' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Trip N:1 TripDay
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'trip_id');
    }

    // TripDay 1:N ScheduleItem
    public function scheduleItems()
    {
        return $this->hasMany(ScheduleItem::class, 'trip_day_id', '
trip_day_id');
    }
}
