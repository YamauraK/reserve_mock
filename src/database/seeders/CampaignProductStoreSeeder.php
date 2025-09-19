<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampaignProductStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $osechi = Campaign::where('name','おせち2025')->first();
        $ehomaki = Campaign::where('name','恵方巻2026')->first();
        $stores = Store::all();

        foreach ($stores as $s) {
            foreach (Product::all() as $p) {
                $campaign = str_starts_with($p->sku,'OC-') ? $osechi : $ehomaki;
                CampaignProductStore::create([
                    'campaign_id' => $campaign->id,
                    'product_id'  => $p->id,
                    'store_id'    => $s->id,
                    'planned_quantity' => rand(20, 100),
                    'reserved_quantity' => 0,
                    'is_available' => true,
                ]);
            }
        }
    }
}
