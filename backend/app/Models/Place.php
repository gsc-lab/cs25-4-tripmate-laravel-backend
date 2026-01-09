<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'places';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'place_id';

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
        'place_id' => 'integer',
        'category_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // PlaceCategory N:1 Place
    public function category()
    {
        return $this->belongsTo(PlaceCategory::class, 'category_id', 'category_id');
    }

    // PlaceCategory 1:N ScheduleItem
    public function scheduleItems()
    {
        return $this->hasMany(ScheduleItem::class, 'place_id', 'place_id');
    }
}
