<?php

namespace App\Http\Requests;

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
            'channel' => ['required', 'in:store,tokushimaru,web'],
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
            }
        }];
    }
}
