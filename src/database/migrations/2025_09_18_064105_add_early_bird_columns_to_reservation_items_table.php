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
        Schema::table('reservation_items', function (Blueprint $t) {
            if (!Schema::hasColumn('reservation_items','unit_price')) {
                $t->unsignedInteger('unit_price')->after('product_id'); // 当時の単価（定価）
            }
            if (!Schema::hasColumn('reservation_items','discount_amount')) {
                $t->unsignedInteger('discount_amount')->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('reservation_items','early_bird_discount_id')) {
                $t->unsignedBigInteger('early_bird_discount_id')->nullable()->after('discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_items', function (Blueprint $t) {
            if (Schema::hasColumn('reservation_items','early_bird_discount_id')) {
                $t->dropColumn('early_bird_discount_id');
            }
            if (Schema::hasColumn('reservation_items','discount_amount')) {
                $t->dropColumn('discount_amount');
            }
            if (Schema::hasColumn('reservation_items','unit_price')) {
                $t->dropColumn('unit_price');
            }
        });
    }
};
