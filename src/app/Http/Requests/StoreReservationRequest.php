<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn($item) => (int)($item['quantity'] ?? 0) > 0)
            ->map(fn($item) => [
                'product_id' => (int)($item['product_id'] ?? 0),
                'quantity' => (int)($item['quantity'] ?? 0),
            ])
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'channel' => ['required', 'in:store,tokushimaru'],
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'zip' => ['nullable', 'string', 'max:10'],
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['nullable', 'date'],
            'pickup_time_slot' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->fails()) {
                return;
            }

            $storeId = (int)$this->input('store_id');
            if (!$storeId) {
                return;
            }

            $items = collect($this->input('items', []));
            if ($items->isEmpty()) {
                return;
            }

            $productIds = $items->pluck('product_id')->unique()->filter()->all();
            if (empty($productIds)) {
                return;
            }

            $availableIds = Product::whereIn('id', $productIds)
                ->availableForStore($storeId)
                ->pluck('id')
                ->all();

            $diff = array_diff($productIds, $availableIds);
            if (!empty($diff)) {
                $validator->errors()->add('items', '選択された商品はこの店舗では利用できません。');
                return;
            }

            $campaignId = (int)$this->input('campaign_id');
            if (!$campaignId) {
                return;
            }

            $isParticipating = Campaign::whereKey($campaignId)
                ->whereHas('stores', fn($q) => $q->where('stores.id', $storeId))
                ->exists();

            if (!$isParticipating) {
                $validator->errors()->add('store_id', '選択した店舗はこの企画に参加していません。');
                return;
            }

            $campaignProductIds = CampaignProductStore::where('campaign_id', $campaignId)
                ->where('store_id', $storeId)
                ->where('is_available', true)
                ->pluck('product_id')
                ->all();

            if (empty($campaignProductIds)) {
                $validator->errors()->add('items', 'この企画では選択できる商品が設定されていません。');
                return;
            }

            $diffCampaign = array_diff($productIds, $campaignProductIds);
            if (!empty($diffCampaign)) {
                $validator->errors()->add('items', '選択された商品はこの企画では利用できません。');
            }
        }];
    }
}
