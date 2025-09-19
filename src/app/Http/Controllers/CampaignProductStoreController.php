<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignProductStoreController extends Controller
{
    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => ['required', Rule::exists('stores', 'id')],
            'product_id' => ['required', Rule::exists('products', 'id')],
            'planned_quantity' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
        ]);

        if (!$campaign->stores()->whereKey($data['store_id'])->exists()) {
            return back()->withErrors(['store_id' => '選択した店舗は企画に参加していません。'])->withInput();
        }

        $productAvailable = Product::whereKey($data['product_id'])
            ->availableForStore($data['store_id'])
            ->exists();

        if (!$productAvailable) {
            return back()->withErrors(['product_id' => '選択された商品は店舗で取り扱っていません。'])->withInput();
        }

        $exists = CampaignProductStore::where([
            'campaign_id' => $campaign->id,
            'store_id' => $data['store_id'],
            'product_id' => $data['product_id'],
        ])->exists();

        if ($exists) {
            return back()->withErrors(['product_id' => '既に設定済みの組み合わせです。'])->withInput();
        }

        CampaignProductStore::create([
            'campaign_id' => $campaign->id,
            'store_id' => $data['store_id'],
            'product_id' => $data['product_id'],
            'planned_quantity' => $data['planned_quantity'] ?? 0,
            'reserved_quantity' => 0,
            'is_available' => (bool)$data['is_available'],
        ]);

        return back()->with('status', '企画の商品設定を追加しました');
    }

    public function update(Request $request, Campaign $campaign, CampaignProductStore $productStore): RedirectResponse
    {
        abort_unless($productStore->campaign_id === $campaign->id, 404);

        $data = $request->validate([
            'planned_quantity' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
        ]);

        $productStore->update([
            'planned_quantity' => $data['planned_quantity'],
            'is_available' => (bool)$data['is_available'],
        ]);

        return back()->with('status', '企画の商品設定を更新しました');
    }

    public function destroy(Campaign $campaign, CampaignProductStore $productStore): RedirectResponse
    {
        abort_unless($productStore->campaign_id === $campaign->id, 404);

        $productStore->delete();

        return back()->with('status', '企画の商品設定を削除しました');
    }
}
