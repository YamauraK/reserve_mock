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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['store', 'tokushimaru', 'web'])->default('store'); // 受付チャネル
// 顧客情報（Phase1：従業員入力）
            $table->string('customer_name');
            $table->string('customer_kana')->nullable();
            $table->string('phone');
            $table->string('zip')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
// 受取希望
            $table->date('pickup_date')->nullable();
            $table->string('pickup_time_slot')->nullable(); // 例: 10:00-12:00
            $table->unsignedInteger('total_amount');
            $table->enum('status', ['draft','confirmed','cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['campaign_id','store_id']);
            $table->index(['phone','customer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
