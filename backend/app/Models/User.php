<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // 로그인용
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * 기본키 컬럼명
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * 기본키 타입
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * 자동 증가 여부
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * mass assignment 설정 (지금은 모두 허용)
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * JSON 응답에서 숨길 필드
     *
     * @var array
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * 속성 타입 캐스팅
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // User 1:N Trip
    public function trips()
    {
        return $this->hasMany(Trip::class, 'user_id', 'user_id');
    }
}
