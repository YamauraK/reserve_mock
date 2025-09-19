<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = Campaign::orderBy('start_date','desc')->paginate(20);
        return view('masters.campaigns.index', compact('rows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('masters.campaigns.form', [
            'campaign' => new Campaign(['is_active' => true]),
            'stores' => Store::orderBy('name')->get(),
            'selectedStoreIds' => [],
            'productOptions' => Product::orderBy('name')->where('is_active', true)->get(),
            'productStores' => collect(),
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
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
        ]);

        $storeIds = collect($data['store_ids'])->unique()->all();
        unset($data['store_ids']);

        $campaign = Campaign::create($data);
        $campaign->stores()->sync($storeIds);

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
        $campaign->load(['stores:id']);

        return view('masters.campaigns.form', [
            'campaign' => $campaign,
            'stores' => Store::orderBy('name')->get(),
            'selectedStoreIds' => $campaign->stores->pluck('id')->all(),
            'productOptions' => Product::orderBy('name')->where('is_active', true)->get(),
            'productStores' => CampaignProductStore::with(['store','product'])
                ->where('campaign_id', $campaign->id)
                ->orderBy('store_id')
                ->orderBy('product_id')
                ->get(),
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
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
        ]);

        $storeIds = collect($data['store_ids'])->unique()->all();
        unset($data['store_ids']);

        $campaign->update($data);
        $campaign->stores()->sync($storeIds);

        CampaignProductStore::where('campaign_id', $campaign->id)
            ->whereNotIn('store_id', $storeIds)
            ->delete();

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
}
