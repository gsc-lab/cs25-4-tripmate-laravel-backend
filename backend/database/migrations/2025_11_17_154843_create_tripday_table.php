<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * trip_day 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('trip_days', function (Blueprint $table) {
            $table->bigIncrements('trip_day_id');

            $table->unsignedBigInteger('trip_id');
            $table->integer('day_no')->comment('1..day_count');
            $table->string('memo', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // FK: trip_id → Trip(trip_id)
            $table->foreign('trip_id', 'fk_trip_day_trip')
                ->references('trip_id')->on('trips')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // UNIQUE KEY uq_trip_day (trip_id, day_no)
            $table->unique(['trip_id', 'day_no'], 'uq_trip_day');

            // INDEX idx_trip_day_trip (trip_id)
            $table->index('trip_id', 'idx_trip_day_trip');
        });
    }

    /**
     * DB에 trip_day 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_days');
    }
};
