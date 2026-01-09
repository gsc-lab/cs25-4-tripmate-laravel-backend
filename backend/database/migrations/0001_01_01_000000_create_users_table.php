<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');

            $table->string('email_norm', 255)->unique()
                ->comment('정규화 이메일(소문자/trim)');

            $table->string('password_hash', 255)
                ->comment('비밀번호 해시값');

            $table->string('name', 50)
                ->comment('표시용 이름(nickname)');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * DB에 users 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
