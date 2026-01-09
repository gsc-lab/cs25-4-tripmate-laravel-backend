<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'regions';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'region_id';

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
        'region_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Region 1:N Trip
    public function trips()
    {
        return $this->hasMany(Trip::class, 'region_id', 'region_id');
    }
}
