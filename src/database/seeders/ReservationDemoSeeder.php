<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReservationDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaign = Campaign::first();
        $store = Store::first();
        $p1 = Product::where('sku','OC-001')->first();
        $p2 = Product::where('sku','OC-002')->first();

        $r = Reservation::create([
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'channel' => 'store',
            'customer_name' => '山田 太郎',
            'customer_kana' => 'ヤマダ タロウ',
            'phone' => '090-1111-2222',
            'zip' => '100-0001',
            'address1' => '東京都千代田区1-1',
            'address2' => '〇〇マンション101',
            'pickup_date' => $campaign->end_date,
            'pickup_time_slot' => '10:00-12:00',
            'total_amount' => 0,
            'status' => 'confirmed',
            'notes' => 'サンプル',
        ]);

        $i1 = ReservationItem::create([
            'reservation_id' => $r->id,
            'product_id' => $p1->id,
            'unit_price' => $p1->price,
            'quantity' => 1,
            'subtotal' => $p1->price,
        ]);
        $i2 = ReservationItem::create([
            'reservation_id' => $r->id,
            'product_id' => $p2->id,
            'unit_price' => $p2->price,
            'quantity' => 2,
            'subtotal' => $p2->price * 2,
        ]);

        $r->update(['total_amount' => $i1->subtotal + $i2->subtotal]);
    }
}
