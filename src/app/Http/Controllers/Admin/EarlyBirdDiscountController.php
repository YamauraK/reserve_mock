<?php

namespace App\Http\Controllers\Admin;

use App\Models\Campaign;
use App\Models\EarlyBirdDiscount;
use App\Models\EarlyBirdScope;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class EarlyBirdDiscountController extends Controller
{
    public function index()
    {
        $rows = EarlyBirdDiscount::with('campaign')->latest()->paginate(20);
        return view('early_birds.index', compact('rows'));
    }

    public function create()
    {
        $model = new EarlyBirdDiscount();
        $campaigns = Campaign::select('id','name')->orderBy('name')->get();
        $products  = Product::select('id','name')->orderBy('name')->get();
        $stores    = Store::select('id','name')->orderBy('name')->get();

        return view('early_birds.create', compact('model','campaigns','products','stores'));
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $model = EarlyBirdDiscount::create($data);

        $this->syncScopes($model, $r->input('product_ids', []), $r->input('store_ids', []));

        return redirect()->route('early-birds.index')->with('success','作成しました');
    }

    public function edit(EarlyBirdDiscount $early_bird)
    {
        $model = $early_bird->load('scopes');
        $campaigns = Campaign::select('id','name')->orderBy('name')->get();
        $products  = Product::select('id','name')->orderBy('name')->get();
        $stores    = Store::select('id','name')->orderBy('name')->get();

        $selectedProductIds = $model->scopes()->whereNotNull('product_id')->pluck('product_id')->unique()->all();
        $selectedStoreIds   = $model->scopes()->whereNotNull('store_id')->pluck('store_id')->unique()->all();

        return view('early_birds.edit', compact(
            'model','campaigns','products','stores','selectedProductIds','selectedStoreIds'
        ));
    }

    public function update(Request $r, EarlyBirdDiscount $early_bird)
    {
        $data = $this->validated($r);
        $early_bird->update($data);

        $this->syncScopes($early_bird, $r->input('product_ids', []), $r->input('store_ids', []));

        return redirect()->route('early-birds.index')->with('success','更新しました');
    }

    public function destroy(EarlyBirdDiscount $early_bird)
    {
        $early_bird->delete();
        return back()->with('success','削除しました');
    }

    private function validated(Request $r): array
    {
        $channels = $r->input('channels', null);
        return $r->validate([
            'campaign_id'   => ['required','exists:campaigns,id'],
            'name'          => ['required','string','max:100'],
            'starts_at'     => ['nullable','date'],
            'cutoff_date'   => ['required','date','after_or_equal:starts_at'],
            'discount_type' => ['required', Rule::in(['percent','amount'])],
            'discount_value'=> ['required','integer','min:1','max:1000000'],
            'channels'      => ['nullable','array'],
            'channels.*'    => [Rule::in(['store','tokushimaru','web'])],
            'stackable'     => ['nullable','boolean'],
            'is_active'     => ['nullable','boolean'],
        ], [], [
            'campaign_id' => '企画',
            'cutoff_date' => '締切日',
            'discount_type' => '割引種別',
            'discount_value' => '割引値',
        ]);
    }

    /** 商品×店舗の全組み合わせで scope を作成（未選択は null として1件） */
    private function syncScopes(EarlyBirdDiscount $model, array $productIds, array $storeIds): void
    {
        $model->scopes()->delete();

        $productIds = array_values(array_unique($productIds));
        $storeIds   = array_values(array_unique($storeIds));

        if (count($productIds) === 0) $productIds = [null];
        if (count($storeIds)   === 0) $storeIds   = [null];

        $rows = [];
        foreach ($productIds as $pid) {
            foreach ($storeIds as $sid) {
                $rows[] = [
                    'early_bird_discount_id' => $model->id,
                    'product_id' => $pid,
                    'store_id' => $sid,
                ];
            }
        }
        if (!empty($rows)) {
            EarlyBirdScope::insert($rows);
        }
    }
}
