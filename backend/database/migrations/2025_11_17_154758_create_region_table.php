<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * region 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->bigIncrements('region_id');

            $table->string('name', 100);
            $table->char('country_code', 2)
                ->comment('ISO-3166-1 alpha-2');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            $table->index('country_code', 'idx_region_country_code');
        });
    }

    /**
     * DB에 region 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
