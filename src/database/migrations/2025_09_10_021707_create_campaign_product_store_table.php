<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaign_product_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('planned_quantity')->default(0); // 計画数量
            $table->unsignedInteger('reserved_quantity')->default(0); // 集計用（トリガーせず更新処理で加算）
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['campaign_id','product_id','store_id']);
        });
        // 在庫強制ガード：現在は超過時に警告のみ → 保存前にバリデーションエラーへ昇格可
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_product_store');
    }
};
