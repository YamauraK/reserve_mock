<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_attach_store_products(): void
    {
        $user = User::factory()->create();
        $stores = Store::factory()->count(2)->create();
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => '秋のセール',
            'description' => 'テスト企画',
            'is_active' => 1,
            'store_ids' => [$stores[0]->id, $stores[1]->id],
            'store_products' => [
                $stores[0]->id => [$products[0]->id],
                $stores[1]->id => [$products[1]->id, $products[2]->id],
            ],
        ]);

        $response->assertRedirect(route('campaigns.index'));

        $campaign = Campaign::first();
        $this->assertNotNull($campaign);

        $this->assertDatabaseHas('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $stores[0]->id,
            'product_id' => $products[0]->id,
        ]);
        $this->assertDatabaseHas('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $stores[1]->id,
            'product_id' => $products[1]->id,
        ]);
        $this->assertDatabaseHas('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $stores[1]->id,
            'product_id' => $products[2]->id,
        ]);
    }

    public function test_update_replaces_store_products(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();
        $products = Product::factory()->count(2)->create();
        $campaign = Campaign::create([
            'name' => '冬のキャンペーン',
            'description' => null,
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
        ]);

        $campaign->productStores()->create([
            'store_id' => $store->id,
            'product_id' => $products[0]->id,
            'planned_quantity' => 0,
            'reserved_quantity' => 0,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->put(route('campaigns.update', $campaign), [
            'name' => '冬のキャンペーン',
            'description' => null,
            'is_active' => 1,
            'store_ids' => [$store->id],
            'store_products' => [
                $store->id => [$products[1]->id],
            ],
        ]);

        $response->assertRedirect(route('campaigns.index'));

        $this->assertDatabaseMissing('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'product_id' => $products[0]->id,
        ]);
        $this->assertDatabaseHas('campaign_product_store', [
            'campaign_id' => $campaign->id,
            'store_id' => $store->id,
            'product_id' => $products[1]->id,
        ]);
    }

    public function test_validation_requires_products_for_selected_store(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $response = $this->actingAs($user)->from(route('campaigns.create'))->post(route('campaigns.store'), [
            'name' => 'エラー企画',
            'description' => null,
            'is_active' => 1,
            'store_ids' => [$store->id],
            'store_products' => [],
        ]);

        $response->assertRedirect(route('campaigns.create'));
        $response->assertSessionHasErrors('store_products');
        $this->assertDatabaseMissing('campaigns', ['name' => 'エラー企画']);
    }

    public function test_edit_form_displays_selected_store_products(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['name' => '表示店舗']);
        $product = Product::factory()->create(['name' => '表示商品']);
        $campaign = Campaign::create([
            'name' => '表示キャンペーン',
            'description' => null,
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
        ]);

        $campaign->productStores()->create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'planned_quantity' => 0,
            'reserved_quantity' => 0,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->get(route('campaigns.edit', $campaign));

        $response->assertOk();
        $response->assertSee('表示店舗');
        $response->assertSee('表示商品');
        $response->assertSee('option value="' . $product->id . '" selected', false);
    }
}
