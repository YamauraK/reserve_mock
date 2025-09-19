<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCampaignConstraintTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(Campaign $campaign, Store $store, Product $product): array
    {
        return [
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'channel' => 'store',
            'customer_name' => 'テスト太郎',
            'phone' => '09012345678',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];
    }

    public function test_store_must_belong_to_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::create([
            'name' => 'テスト企画',
            'start_date' => now()->toDateString(),
            'is_active' => true,
        ]);
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $product->stores()->attach($store);

        $response = $this->actingAs($user)->from('/reservations/create')->post('/reservations', $this->validPayload($campaign, $store, $product));

        $response->assertSessionHasErrors('store_id');
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_products_must_be_linked_via_campaign_store(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::create([
            'name' => 'テスト企画',
            'start_date' => now()->toDateString(),
            'is_active' => true,
        ]);
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $product->stores()->attach($store);
        $campaign->stores()->attach($store);

        $response = $this->actingAs($user)->from('/reservations/create')->post('/reservations', $this->validPayload($campaign, $store, $product));
        $response->assertSessionHasErrors('items');

        CampaignProductStore::create([
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'product_id' => $product->id,
            'planned_quantity' => 5,
            'reserved_quantity' => 0,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->from('/reservations/create')->post('/reservations', $this->validPayload($campaign, $store, $product));
        $response->assertRedirect('/reservations');
        $this->assertDatabaseHas('reservations', [
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'customer_name' => 'テスト太郎',
        ]);
        $this->assertDatabaseHas('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'product_id' => $product->id,
            'reserved_quantity' => 1,
        ]);
    }
}
