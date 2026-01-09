<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleItem extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'schedule_items';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'schedule_item_id';

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
        'schedule_item_id' => 'integer',
        'trip_day_id' => 'integer',
        'place_id' => 'integer',
        'seq_no' => 'integer',
        'visit_time' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ScheduleItem N:1 TripDay
    public function tripDay()
    {
        return $this->belongsTo(TripDay::class, 'trip_day_id', 'trip_day_id');
    }

    // ScheduleItem N:1 Place
    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id', 'place_id');
    }
}
