<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = Campaign::with(['productStores.store:id,name'])
            ->orderBy('start_date', 'desc')
            ->paginate(20);
        return view('masters.campaigns.index', compact('rows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $storeProductSelections = [];
        $selectionData = $this->prepareStoreSelectionData($storeProductSelections);

        return view('masters.campaigns.form', [
            'campaign' => new Campaign(),
            'stores' => $selectionData['stores'],
            'storeProductOptions' => $selectionData['storeProductOptions'],
            'selectedStoreIds' => [],
            'storeProductSelections' => $storeProductSelections,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'description'=>['nullable','string'],
            'start_date'=>['nullable','date'],
            'end_date'=>['nullable','date','after_or_equal:start_date'],
            'is_active'=>['required','boolean'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
            'store_products' => ['nullable', 'array'],
            'store_products.*' => ['array'],
            'store_products.*.*' => ['integer', Rule::exists('products', 'id')],
        ]);
        $campaignData = Arr::only($data, ['name', 'description', 'start_date', 'end_date', 'is_active']);
        $storeIds = $this->normalizeStoreIds($data['store_ids'] ?? []);
        $storeProducts = $this->normalizeStoreProducts($data['store_products'] ?? [], $storeIds);

        if ($this->hasStoreWithoutProducts($storeIds, $storeProducts)) {
            return back()
                ->withErrors(['store_products' => '参加店舗ごとに企画対象の商品を選択してください。'])
                ->withInput();
        }

        $campaign = Campaign::create($campaignData);
        $this->syncStoreProducts($campaign, $storeProducts);
        return redirect()->route('campaigns.index')->with('status','企画を作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        $campaign->load('productStores');
        $storeProductSelections = $campaign->productStores
            ->groupBy('store_id')
            ->map(fn($rows) => $rows->pluck('product_id')->all())
            ->toArray();

        $selectionData = $this->prepareStoreSelectionData($storeProductSelections);

        return view('masters.campaigns.form', [
            'campaign' => $campaign,
            'stores' => $selectionData['stores'],
            'storeProductOptions' => $selectionData['storeProductOptions'],
            'selectedStoreIds' => array_keys($storeProductSelections),
            'storeProductSelections' => $storeProductSelections,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'description'=>['nullable','string'],
            'start_date'=>['nullable','date'],
            'end_date'=>['nullable','date','after_or_equal:start_date'],
            'is_active'=>['required','boolean'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
            'store_products' => ['nullable', 'array'],
            'store_products.*' => ['array'],
            'store_products.*.*' => ['integer', Rule::exists('products', 'id')],
        ]);
        $campaignData = Arr::only($data, ['name', 'description', 'start_date', 'end_date', 'is_active']);
        $storeIds = $this->normalizeStoreIds($data['store_ids'] ?? []);
        $storeProducts = $this->normalizeStoreProducts($data['store_products'] ?? [], $storeIds);

        if ($this->hasStoreWithoutProducts($storeIds, $storeProducts)) {
            return back()
                ->withErrors(['store_products' => '参加店舗ごとに企画対象の商品を選択してください。'])
                ->withInput();
        }

        $campaign->update($campaignData);
        $this->syncStoreProducts($campaign, $storeProducts);
        return redirect()->route('campaigns.index')->with('status','企画を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('status','企画を削除しました');
    }

    /**
     * @param  array<int,int|string>  $storeIds
     * @return array<int,int>
     */
    private function normalizeStoreIds(array $storeIds): array
    {
        return collect($storeIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int|string, mixed>  $storeProducts
     * @param  array<int,int>  $allowedStoreIds
     * @return array<int,array<int,int>>
     */
    private function normalizeStoreProducts(array $storeProducts, array $allowedStoreIds): array
    {
        $allowed = collect($allowedStoreIds);

        return collect($storeProducts)
            ->filter(fn($value) => is_array($value))
            ->mapWithKeys(function ($productIds, $storeId) use ($allowed) {
                $storeId = (int) $storeId;
                if ($storeId === 0 || !$allowed->contains($storeId)) {
                    return [];
                }

                $ids = collect($productIds)
                    ->map(fn($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [$storeId => $ids];
            })
            ->all();
    }

    /**
     * @param  array<int,int>  $storeIds
     * @param  array<int,array<int,int>>  $storeProducts
     */
    private function hasStoreWithoutProducts(array $storeIds, array $storeProducts): bool
    {
        foreach ($storeIds as $storeId) {
            if (empty($storeProducts[$storeId] ?? [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int,array<int,int>>  $storeProducts
     */
    private function syncStoreProducts(Campaign $campaign, array $storeProducts): void
    {
        $existing = $campaign->productStores()
            ->get()
            ->keyBy(fn(CampaignProductStore $row) => $row->store_id . '-' . $row->product_id);

        $desiredKeys = collect();
        foreach ($storeProducts as $storeId => $productIds) {
            foreach ($productIds as $productId) {
                $desiredKeys->push($storeId . '-' . $productId);
            }
        }

        $keysToDelete = $existing->keys()->diff($desiredKeys);
        if ($keysToDelete->isNotEmpty()) {
            $ids = $keysToDelete->map(fn($key) => $existing[$key]->id)->values();
            CampaignProductStore::whereIn('id', $ids)->delete();
        }

        $keysToCreate = $desiredKeys->diff($existing->keys());
        if ($keysToCreate->isNotEmpty()) {
            $records = $keysToCreate
                ->map(function ($key) {
                    [$storeId, $productId] = array_map('intval', explode('-', $key));

                    return [
                        'store_id' => $storeId,
                        'product_id' => $productId,
                        'planned_quantity' => 0,
                        'reserved_quantity' => 0,
                        'is_available' => true,
                    ];
                })
                ->values()
                ->all();

            $campaign->productStores()->createMany($records);
        }
    }

    /**
     * @param  array<int,array<int,int>>  $storeProductSelections
     * @return array{stores: \Illuminate\Support\Collection<int,\App\Models\Store>, storeProductOptions: array<int,array<int,array{id:int,name:string}>>>}
     */
    private function prepareStoreSelectionData(array $storeProductSelections): array
    {
        $stores = Store::with(['products' => fn($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);

        $commonProducts = Product::query()
            ->where('is_all_store', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedProductIds = collect($storeProductSelections)
            ->flatten()
            ->filter()
            ->unique()
            ->values();

        $selectedProducts = $selectedProductIds->isEmpty()
            ? collect()
            : Product::whereIn('id', $selectedProductIds)->get(['id', 'name']);

        $selectedProductsById = $selectedProducts->keyBy('id');

        $storeProductOptions = $stores->mapWithKeys(function (Store $store) use (
            $commonProducts,
            $selectedProductsById,
            $storeProductSelections
        ) {
            $selectedForStore = collect($storeProductSelections[$store->id] ?? [])
                ->map(fn($id) => $selectedProductsById->get($id))
                ->filter();

            $availableProducts = $commonProducts
                ->concat($store->products)
                ->concat($selectedForStore)
                ->unique('id')
                ->sortBy('name')
                ->values();

            $options = $availableProducts
                ->map(fn(Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                ])
                ->all();

            return [$store->id => $options];
        })->all();

        return [
            'stores' => $stores,
            'storeProductOptions' => $storeProductOptions,
        ];
    }
}
