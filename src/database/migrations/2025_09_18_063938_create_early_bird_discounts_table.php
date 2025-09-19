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
        Schema::create('early_bird_discounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained()->cascadeOnDelete(); // 企画にぶら下げ
            $t->string('name');                      // 表示名（例：早割10/31まで）
            $t->date('starts_at')->nullable();      // 任意：開始日
            $t->date('cutoff_date');                // 締切（予約日がこの日まで）
            $t->enum('discount_type', ['percent','amount']); // 率 or 額
            $t->unsignedInteger('discount_value');  // 5（%）or 500（円）
            $t->json('channels')->nullable();       // ["store"] / ["store","web"] など
            $t->boolean('stackable')->default(false); // 併用可否（基本 false）
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_bird_discounts');
    }
};
