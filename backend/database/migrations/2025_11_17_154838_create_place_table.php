<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * place 테이블 migration 실행
     */
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->bigIncrements('place_id');

            $table->unsignedBigInteger('category_id');
            $table->string('name', 255);
            $table->string('address', 255);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('external_provider', 32)
                ->default('google')
                ->comment('외부 제공자');
            $table->string('external_ref', 128)->nullable()
                ->comment('외부 참조 ID');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            // FK: category_id → place_category(category_id)
            $table->foreign('category_id', 'fk_place_category')
                ->references('category_id')->on('place_categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            // UNIQUE KEY uq_place_provider_ref (external_provider, external_ref)
            $table->unique(
                ['external_provider', 'external_ref'],
                'uq_place_provider_ref'
            );

            // INDEX idx_place_category (category_id)
            $table->index('category_id', 'idx_place_category');
        });
    }

    /**
     * db에 place 테이블이 존재할 경우 삭제
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
