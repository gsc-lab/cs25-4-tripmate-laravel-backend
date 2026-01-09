<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * trip 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('trip_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('region_id')->nullable(); // region_id NULL 허용
            $table->string('title', 100);
            $table->date('start_date');
            $table->date('end_date');

            // day_count INT GENERATED ALWAYS AS (DATEDIFF(end_date, start_date) + 1) STORED
            $table->integer('day_count')
                ->storedAs('DATEDIFF(end_date, start_date) + 1');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // FK: user_id → Users(user_id)
            $table->foreign('user_id', 'fk_trip_user')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // FK: region_id → Region(region_id)
            $table->foreign('region_id', 'fk_trip_region')
                ->references('region_id')->on('regions')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // INDEX idx_trip_user_date (user_id, start_date)
            $table->index(['user_id', 'start_date'], 'idx_trip_user_date');
        });
    }

    /**
     * DB에 trip 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
