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
        $definitions = [
            'OC-001' => [
                'campaign' => 'おせち2025',
                'planned' => 60,
            ],
            'OC-002' => [
                'campaign' => 'おせち2025',
                'planned' => 45,
            ],
            'EH-001' => [
                'campaign' => '恵方巻2026',
                'planned' => 80,
            ],
        ];

        $campaigns = Campaign::whereIn('name', collect($definitions)->pluck('campaign')->unique())->get()->keyBy('name');
        $products = Product::whereIn('sku', array_keys($definitions))->get()->keyBy('sku');
        $stores = Store::all();

        foreach ($stores as $store) {
            foreach ($definitions as $sku => $config) {
                $campaign = $campaigns->get($config['campaign']);
                $product = $products->get($sku);

                if (!$campaign || !$product) {
                    throw new \RuntimeException('Seed prerequisite missing for campaign-product-store seeding.');
                }

                CampaignProductStore::updateOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                    ],
                    [
                        'planned_quantity' => $config['planned'],
                        'reserved_quantity' => 0,
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
