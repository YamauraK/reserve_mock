<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_displays_attached_store_names(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['name' => 'テスト店舗']);
        $product = Product::factory()->create(['name' => 'テスト商品']);
        $product->stores()->attach($store);

        $response = $this->actingAs($user)->get('/products');

        $response->assertOk();
        $response->assertSee('テスト商品');
        $response->assertSee('テスト店舗');
    }
}
