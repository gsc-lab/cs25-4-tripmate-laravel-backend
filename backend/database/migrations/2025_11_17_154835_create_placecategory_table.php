<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * place_categories 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('place_categories', function (Blueprint $table) {
            $table->bigIncrements('category_id');

            $table->string('code', 64)->unique();
            $table->string('name', 100);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * DB에 place_categories 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('place_categories');
    }
};
