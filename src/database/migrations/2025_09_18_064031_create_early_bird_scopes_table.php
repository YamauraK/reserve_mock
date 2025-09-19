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
        Schema::create('early_bird_scopes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('early_bird_discount_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // null=全商品
            $t->foreignId('store_id')->nullable()->constrained()->nullOnDelete();   // null=全店舗
            $t->unique(['early_bird_discount_id','product_id','store_id'], 'ebd_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_bird_scopes');
    }
};
