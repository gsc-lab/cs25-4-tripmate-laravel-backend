<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * schedule_item 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('schedule_items', function (Blueprint $table) {
            $table->bigIncrements('schedule_item_id');

            $table->unsignedBigInteger('trip_day_id');
            $table->unsignedBigInteger('place_id')->nullable();

            $table->integer('seq_no')->comment('일차 내 순번(1..n)');
            $table->time('visit_time')->nullable();
            $table->string('memo', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // FK: trip_day_id → TripDay(trip_day_id)
            $table->foreign('trip_day_id', 'fk_schedule_item_tripday')
                ->references('trip_day_id')->on('trip_days')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // FK: place_id → Place(place_id)
            $table->foreign('place_id', 'fk_schedule_item_place')
                ->references('place_id')->on('places')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // UNIQUE KEY uq_schedule_item_seq (trip_day_id, seq_no)
            $table->unique(['trip_day_id', 'seq_no'], 'uq_schedule_item_seq');

            // INDEX idx_schedule_item_tripday (trip_day_id)
            $table->index('trip_day_id', 'idx_schedule_item_trip_day');
        });
    }

    /**
     *  DB에 schedule_item 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_items');
    }
};
